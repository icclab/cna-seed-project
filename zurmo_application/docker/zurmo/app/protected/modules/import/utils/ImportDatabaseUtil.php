<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class for working with import data tables.
     */
    abstract class ImportDatabaseUtil
    {
        const ALLOWED_ENCODINGS_FOR_CONVERSION = 'UTF-8, UTF-7, ASCII, CP1252, EUC-JP, SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP';

        const MAX_IMPORT_COLUMN_COUNT   = 99;

        const BULK_INSERT_COUNT         = 500;

        protected static $temporaryFileName = null;

        protected static $importDataRowCount = null;

        /**
         * Given a file resource, convert the file into a database table based on the table name provided.
         * Assumes the file is a csv.
         * @param object $fileHandle
         * @param string $tableName
         * @param string $delimiter
         * @param string $enclosure
         * @param bool $firstRowIsHeaderRow
         * @return bool
         */
        public static function makeDatabaseTableByFileHandleAndTableName($fileHandle, $tableName, $delimiter = ',', // Not Coding Standard
                                                                         $enclosure = "'", $firstRowIsHeaderRow = false)
        {
            assert('gettype($fileHandle) == "resource"');
            assert('is_string($tableName)');
            assert('$tableName == strtolower($tableName)');
            assert('$delimiter != null && is_string($delimiter)');
            assert('$enclosure != null && is_string($enclosure)');
            static::createTableByTableNameAndImportCsvIntoTable($fileHandle, $tableName, $delimiter,
                                                                    $enclosure, $firstRowIsHeaderRow);
            return true;
        }

        protected static function createTableByTableNameAndImportCsvIntoTable($fileHandle, $tableName, $delimiter,
                                                                                $enclosure, $firstRowIsHeaderRow)
        {
            $maxLengths         = array();
            $columns            = array();
            $importArray        = array();
            static::determineMaximumColumnLengthAndPopulateImportArray($fileHandle, $delimiter, $enclosure,
                                                                        $maxLengths, $importArray, $firstRowIsHeaderRow);
            if (!empty($maxLengths))
            {
                $columnCount        = static::resolveColumnsByMaximumColumnLengths($maxLengths, $columns);
                static::safeValidateColumnCountAndCreateTable($tableName, $columnCount, $columns);
                if (static::databaseSupportsLoadLocalInFile())
                {
                    array_walk($importArray, 'static::prependEmptyStringToAllImportRows');
                    static::convertImportArrayAndWriteToTemporaryFile($importArray, $firstRowIsHeaderRow);
                    static::loadDataFromTemporaryFileToTable($tableName);
                }
                else
                {
                    $columnNames    = RedBeanModelMemberToColumnUtil::resolveColumnNamesArrayFromColumnSchemaDefinition($columns);
                    static::importArrayIntoTable($tableName, $importArray, $columnNames);
                }
            }
            else
            {
                // we need this here so even is there are nothing else to do, we clear the table, else few tests would fail.
                ZurmoRedBean::$writer->dropTableByTableName($tableName);
            }
        }

        /**
         * Populates maxLengths with the max lengths of columns and importArray with converted utf8 data
         * @param $fileHandle
         * @param $delimiter
         * @param $enclosure
         * @param $maxLengths
         * @param $importArray
         * @param $firstRowIsHeaderRow
         */
        protected static function determineMaximumColumnLengthAndPopulateImportArray($fileHandle, $delimiter,
                                                                                     $enclosure, array & $maxLengths,
                                                                                     array & $importArray,
                                                                                     $firstRowIsHeaderRow)
        {
            rewind($fileHandle);
            while (($data = fgetcsv($fileHandle, 0, $delimiter, $enclosure)) !== false)
            {
                if (count($data) > 1 || (count($data) == 1 && trim($data['0']) != ''))
                {
                    $importData       = array();
                    foreach ($data as $k => $v)
                    {
                        static::convertCurrentValueToUtf8AndPopulateImportDataArray($k, $v, $importData);
                        static::updateMaxLengthForKey($k, $v, $maxLengths);
                    }
                    $importArray[] = $importData;
                }
            }
        }

        /**
         * Convert current value to utf8, in place. Populates values into provided array at k position
         * @param $k
         * @param $v
         * @param $importData
         */
        protected static function convertCurrentValueToUtf8AndPopulateImportDataArray($k, & $v, array & $importData)
        {
            // Convert characterset to UTF-8
            $currentCharset = mb_detect_encoding($v, static::ALLOWED_ENCODINGS_FOR_CONVERSION);
            if (!empty($currentCharset) && $currentCharset != "UTF-8")
            {
                $v = mb_convert_encoding($v, "UTF-8");
            }
            $importData[$k] = $v;
        }

        /**
         * Pad the sourceArray upto newSize with provided value
         * @param $sourceArray
         * @param $newSize
         * @param int $value
         */
        protected static function padEmptyKeys(array & $sourceArray, $newSize, $value = 1)
        {
            if ($newSize > count($sourceArray))
            {
                // we are either at start or at a row that has more columns than before ones.
                $sourceArray = array_pad($sourceArray, $newSize, $value);
            }
        }

        /**
         * Updates maxLengths array for k if provided v is of greater length
         * @param $k
         * @param $v
         * @param $maxLengths
         */
        protected static function updateMaxLengthForKey($k, $v, array & $maxLengths)
        {
            $currentValueLength = strlen($v);
            if (!isset($maxLengths[$k]) || $maxLengths[$k] < $currentValueLength)
            {
                $maxLengths[$k] = $currentValueLength;
            }
        }

        /**
         * Unsets key if the provided value is not empty
         * @param $k
         * @param $v
         * @param $emptyKeys
         */
        protected static function unsetEmptyKeysForKeyIfValueNotEmpty($k, $v, array & $emptyKeys)
        {
            if (($v !== null || $v !== false || strlen($v) != 0) && // value is not empty
                isset($emptyKeys[$k])) // and we already have it as empty key
            {
                unset($emptyKeys[$k]);
            }
        }

        /**
         * Unsets the keys that always remain empty from importArray and maxLengths
         * @param $clearEmptyColumns
         * @param $emptyKeys
         * @param $maxLengths
         * @param $importArray
         */
        protected static function unsetEmptyKeysFromMaxLengthAndImportArray($clearEmptyColumns, array $emptyKeys,
                                                                                & $maxLengths, array & $importArray)
        {
            if ($clearEmptyColumns && !empty($emptyKeys))
            {
                foreach ($emptyKeys as $emptyKey => $notUsed)
                {
                    unset($maxLengths[$emptyKey]);
                    foreach ($importArray as $importRow)
                    {
                        unset($importRow[$emptyKey]);
                    }
                }
            }
        }

        /**
         * Prepends an empty string to provided $val
         * @param $val
         * @throws NotSupportedException
         */
        protected static function prependEmptyStringToAllImportRows(array &$val)
        {
            if (!is_array($val))
            {
                throw new NotSupportedException();
            }
            array_unshift($val, '');
        }

        /**
         * Check whether db supports load local infile or not.
         * public due to usage in benchmarks
         * @return bool
         */
        public static function databaseSupportsLoadLocalInFile()
        {
            list($databaseType, $databaseHostname, $databasePort) = array_values(
                                    RedBeanDatabase::getDatabaseInfoFromDsnString(Yii::app()->db->connectionString));
            return InstallUtil::checkDatabaseLoadLocalInFile($databaseType,
                                                                $databaseHostname,
                                                                Yii::app()->db->username,
                                                                Yii::app()->db->password,
                                                                $databasePort);
        }

        /**
         * Resolves string columns for given max lengths
         * @param $maxLengths
         * @param $columns
         * @return int column count
         */
        protected static function resolveColumnsByMaximumColumnLengths(array $maxLengths, array & $columns)
        {
            $columnCount = 0;
            foreach ($maxLengths as $currentValueLength)
            {
                $columnName         = 'column_' . $columnCount;
                $type               = null;
                $length             = null;
                RedBeanModelMemberRulesToColumnAdapter::resolveStringTypeAndLengthByMaxLength($type, $length,
                                                                                                $currentValueLength);
                $columns[]    = RedBeanModelMemberToColumnUtil::resolveColumnMetadataByHintType($columnName, $type,
                                                                                                    $length);
                $columnCount++;
            }
            return $columnCount;
        }

        /**
         * Validates if columnCount is within allowed range, and creates table if its not.
         * @param $tableName
         * @param $columnCount
         * @param $columns
         * @throws TooManyColumnsFailedException
         */
        protected static function safeValidateColumnCountAndCreateTable($tableName, $columnCount, array $columns)
        {
            if ($columnCount > 0)
            {
                if ($columnCount > static::MAX_IMPORT_COLUMN_COUNT)
                {
                    throw new TooManyColumnsFailedException(
                        Zurmo::t('ImportModule', 'The file has too many columns. The maximum is 100'));
                }
                static::createTableByTableNameAndImportColumns($tableName, $columns);
            }
        }

        /**
         * Writes provided array as csv to a temporary file
         * @param $importArray
         * @param $firstRowIsHeaderRow
         * @throws NotSupportedException
         */
        protected static function convertImportArrayAndWriteToTemporaryFile(array $importArray, $firstRowIsHeaderRow)
        {
            static::$temporaryFileName      = tempnam(sys_get_temp_dir(), 'csv_import_');
            static::$importDataRowCount     = count($importArray);
            $csv = static::convertImportArrayToCsv($importArray, $firstRowIsHeaderRow);
            if ($csv === null || strlen(trim($csv)) === 0)
            {
                throw new NotSupportedException("Unable to convert importArray to csv for writing to {${static::$importDataRowCount}}");
            }
            static::writeCsvToTemporaryFile($csv);
            static::fixPermissionsOnTemporaryFile();
        }

        /**
         * Converts import array to csv
         * @param $importArray
         * @param $firstRowIsHeaderRow
         * @return string
         */
        protected static function convertImportArrayToCsv(array $importArray, $firstRowIsHeaderRow)
        {
            $headerArray    = array();
            if ($firstRowIsHeaderRow)
            {
                $headerArray    = array_shift($importArray);
            }
            $csv = ExportItemToCsvFileUtil::export($importArray, $headerArray, '', false, true, true);
            return $csv;
        }

        /**
         * Writes csv data to temporary file while ensuring utf-8 special characters remain unchanged
         * @param $csv
         * @throws NotSupportedException
         */
        protected static function writeCsvToTemporaryFile($csv)
        {
            $temporaryFileHandle    = fopen(static::$temporaryFileName, 'wb');
            $bytesWritten           = fwrite($temporaryFileHandle, $csv);
            fclose($temporaryFileHandle);
            if ($bytesWritten === false)
            {
                throw new NotSupportedException("Unable to write to {${static::$importDataRowCount}}");
            }
        }

        /**
         * Fixes permissions on temporary file to 777
         * @throws NotSupportedException
         */
        protected static function fixPermissionsOnTemporaryFile()
        {
            // to ensure that mysql can read this file.
            if (!chmod(static::$temporaryFileName, 0777))
            {
                throw new NotSupportedException("Unable to fix permissions on temporary import file");
            }
        }

        /**
         * loads data from provided csv file to mysql using LOAD DATA INFILE
         * @param $tableName
         * @throws NotSupportedException
         */
        protected static function loadDataFromTemporaryFileToTable($tableName)
        {
            $queryParameters    = array(
                'characterSet'         => 'utf8',  // 'binary' would also work, actually that is
                                                    // kind of better as we already converted data to utf8
                'delimiter'            => ExportItemToCsvFileUtil::DEFAULT_DELIMITER,
                'enclosure'            => ExportItemToCsvFileUtil::DEFAULT_ENCLOSURE,
                'temporaryFileName'    => static::$temporaryFileName,
            );
            $tableName      = ZurmoRedBean::$writer->safeTable($tableName);
            $query          = "LOAD DATA LOCAL INFILE :temporaryFileName REPLACE INTO TABLE ${tableName}";
            $query          .= " CHARACTER SET :characterSet FIELDS TERMINATED BY :delimiter";
            $query          .= " ENCLOSED BY :enclosure";
            try
            {
                $affectedRows   = ZurmoRedBean::exec($query, $queryParameters);
            }
            catch (RedBean_Exception_SQL $e)
            {
                if (strpos($e->getMessage(), ' 1148 ') !== false)
                {
                    $e = new NotSupportedException("Please enable LOCAL INFILE in mysql config. Add local-infile=1 to [mysqld] and [mysql] sections."); // Not Coding Standard
                }
                throw $e;
            }
            if (static::$importDataRowCount != $affectedRows)
            {
                throw new NotSupportedException("Unable to import all data: ${affectedRows}/{${static::$importDataRowCount}}");
            }
            unlink(static::$temporaryFileName);
        }

        /**
         * Imports data from array to table
         * @param $tableName
         * @param $importArray
         * @param $columnNames
         * @throws NotSupportedException
         */
        protected static function importArrayIntoTable($tableName, array & $importArray, array $columnNames)
        {
            assert('is_string($tableName)');
            assert('$tableName == strtolower($tableName)');
            assert('is_array($columnNames)');
            assert('is_array($importArray)');
            do
            {
                $importSubset       = ArrayUtil::chopArray($importArray, static::BULK_INSERT_COUNT);
                // bulkInsert needs every subarray to have same number of columns as columnNames, pad with empty strings
                static::padSubArrays($importSubset, count($columnNames));
                DatabaseCompatibilityUtil::bulkInsert($tableName, $importSubset, $columnNames, static::BULK_INSERT_COUNT, true);
            } while (count($importSubset) > 0);
        }

        /**
         * Pads subArrays with given value
         * @param array $array
         * @param $padSize
         * @param string $value
         */
        protected static function padSubArrays(array & $array, $newSize, $value = '')
        {
            $paddedArray = array();
            foreach ($array as $key => $subArray)
            {
                $subArray = array_pad($subArray, $newSize, $value);
                $paddedArray[$key] = $subArray;
            }
            if (!empty($paddedArray))
            {
                $array = $paddedArray;
            }
        }

        /**
         * Given a table name, count, and offset get an array of beans.
         * @param string $tableName
         * @param integer $count
         * @param integer $offset
         * @return array of RedBean_OODBBean beans.
         */
        public static function getSubset($tableName, $where = null, $count = null, $offset = null)
        {
            assert('is_string($tableName)');
            assert('$offset  === null || is_integer($offset)  && $offset  >= 0');
            assert('$offset  === null || is_integer($count)   && $count   >= 1');
            $sql = 'select id from ' . $tableName;
            if ($where != null)
            {
                $sql .= ' where ' . $where;
            }
            if ($count !== null)
            {
                $sql .= " limit $count";
            }
            if ($offset !== null)
            {
                $sql .= " offset $offset";
            }
            $ids   = ZurmoRedBean::getCol($sql);
            return ZurmoRedBean::batch ($tableName, $ids);
        }

        /**
         * Get the row count in a given table.
         * @param string $tableName
         * @return integer
         */
        public static function getCount($tableName, $where = null)
        {
            if ($where === null)
            {
                return ZurmoRedBean::$writer->count($tableName);
            }
            else
            {
                $sql    = 'select count(id) count from ' . $tableName;
                $sql    .= ' where ' . $where;

                $count = ZurmoRedBean::getCell($sql);
                if ($count === null)
                {
                    $count = 0;
                }
                return $count;
            }
        }

        /**
         * Update the row in the table with status and message information after the row is attempted or successfully
         * imported.
         * @param string         $tableName
         * @param integer        $id
         * @param integer        $status
         * @param string or null $serializedMessages
         */
        public static function updateRowAfterProcessing($tableName, $id, $status, $serializedMessages)
        {
            assert('is_string($tableName)');
            assert('is_int($id)');
            assert('is_int($status)');
            assert('is_string($serializedMessages) || $serializedMessages == null');

            $bean = ZurmoRedBean::findOne($tableName, "id = :id", array('id' => $id));
            if ($bean == null)
            {
                throw new NotFoundException();
            }
            $bean->status             = $status;
            $bean->serializedMessages = $serializedMessages;
            $storedId = ZurmoRedBean::store($bean);
            if ($storedId != $id)
            {
                throw new FailedToSaveModelException("Id of updated record does not match the id used in finding it.");
            }
        }

        /**
         * Update the row value in the table with a new value
         * @param string        $tableName
         * @param integer       $id
         * @param string        $attribute
         * @param string|null   $newValue
         * @throws NotFoundException
         * @throws FailedToSaveModelException
         */
        public static function updateRowValue($tableName, $id, $attribute, $newValue)
        {
            assert('is_string($tableName)');
            assert('is_int($id)');
            assert('is_string($attribute)');
            assert('is_string($newValue) || $newValue == null');

            extract(static::geColumnData($tableName, $attribute));
            $newDbType      = null;
            $newDbLength    = null;
            RedBeanModelMemberRulesToColumnAdapter::resolveStringTypeAndLengthByMaxLength($newDbType, $newDbLength, strlen($newValue));
            $update = false;
            if ($newDbType == 'string')
            {
                if ($columnType == 'varchar' && $newDbLength > $columnLength)
                {
                    $update = true;
                }
            }
            elseif ($newDbType != $columnType)
            {
                if ($newDbType == 'longtext')
                {
                    $update = true;
                }
                elseif ($newDbType == 'text' && $columnType == 'varchar')
                {
                    $update = true;
                }
            }
            if ($update)
            {
                $column         = RedBeanModelMemberToColumnUtil::resolveColumnMetadataByHintType($attribute, $newDbType, $newDbLength);
                $schema         = CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::getTableSchema($tableName, array($column));
                $messageLogger  = new ImportMessageLogger();
                CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema, $messageLogger, false);
            }
            $bean = ZurmoRedBean::findOne($tableName, "id = :id", array('id' => $id));
            if ($bean == null)
            {
                throw new NotFoundException();
            }
            $bean->$attribute         = $newValue;
            $storedId = ZurmoRedBean::store($bean);
            if ($storedId != $id)
            {
                throw new FailedToSaveModelException("Id of updated record does not match the id used in finding it.");
            }
        }

        protected static function geColumnData($tableName, $column)
        {
            $columnsWithDetails = ZurmoRedBean::$writer->getColumnsWithDetails($tableName);
            $columnDetails      = $columnsWithDetails[$column];
            preg_match('/([a-z]*)(\(\d*\))?/', $columnDetails['Type'], $results);
            $columnType   = strtolower($results[1]);
            $columnLength = isset($results[2]) ? trim($results[2], '()') : null;
            return compact('columnType', 'columnLength');
        }

        /**
         * For the temporary import tables, some of the columns are reserved and not used by any of the import data
         * coming from a csv.
         * @return array of column names.
         */
        public static function getReservedColumnNames()
        {
            return array('analysisStatus', 'id', 'serializedAnalysisMessages', 'serializedMessages', 'status');
        }

        protected static function getReservedColumnMetadata()
        {
            $columns    = array();
            $reservedColumnsTypes           = array(
                'status'                        => 'integer',
                'serializedMessages'            => 'string',
                'analysisStatus'                => 'integer',
                'serializedAnalysisMessages'    => 'string',
            );
            foreach ($reservedColumnsTypes as $columnName => $type)
            {
                $length     = null;
                $unsigned   = null;
                if ($type === 'string')
                {
                    // populate the proper type given it would be 1024 char string depending on db type.
                    RedBeanModelMemberRulesToColumnAdapter::resolveStringTypeAndLengthByMaxLength($type, $length, 1024);
                }
                else
                {
                    // forcing integers to be unsigned
                    $unsigned = DatabaseCompatibilityUtil::resolveUnsignedByHintType($type, false);
                }
                // last argument is false because we do not want these column names to be resolved to lower characters
                $columns[]  = RedBeanModelMemberToColumnUtil::resolveColumnMetadataByHintType($columnName, $type,
                                                                            $length, $unsigned, null, null, null, false);
            }
            return $columns;
        }

        /**
         * Returns table schema definition for temporary import table provided name and columns, implicitly
         * adds reserved columns too
         * @param $tableName
         * @param $columns
         * @apram $withReservedColumns
         * @return array
         */
        protected static function getTableSchemaByNameAndImportColumns($tableName, array $columns, $withReservedColumns = true)
        {
            if ($withReservedColumns)
            {
                $columns = CMap::mergeArray($columns, static::getReservedColumnMetadata());
            }
            return CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::getTableSchema($tableName, $columns);
        }

        /**
         * Creates table in db give table name and import columns
         * Public due to import/DemoController
         * @param $tableName
         * @param $columns
         */
        public static function createTableByTableNameAndImportColumns($tableName, array $columns)
        {
            // this dropTable is here just because as fail-safe for direct invocations from other classes.
            ZurmoRedBean::$writer->dropTableByTableName($tableName);
            $schema = static::getTableSchemaByNameAndImportColumns($tableName, $columns);
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition(
                                                                                                $schema,
                                                                                                new MessageLogger());
        }
    }
?>