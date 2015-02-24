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
     * Utility to generate or update a table in database when provided with a database schema in array format
     */
    abstract class CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil
    {
        const CACHE_KEY = 'CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil_processedTableNames';

        /**
         * Returns table schema definition
         * @param $tableName
         * @param array $columns
         * @param array $indexes
         * @return array $schema
         */
        public static function getTableSchema($tableName, $columns = array(), $indexes = array())
        {
            $schema     = array(
                $tableName    => array(
                    'columns' => $columns,
                    'indexes' => $indexes,
                )
            );
            return $schema;
        }

        /**
         * Provide a schema definition array queries to create/update database schema are executed.
         * @param array $schemaDefinition
         * @param $messageLogger
         * @param $validate
         * @throws CException
         * @throws Exception|RedBean_Exception_SQL
         */
        public static function generateOrUpdateTableBySchemaDefinition(array $schemaDefinition, & $messageLogger, $validate = true)
        {
            $tableName          = strtolower(key($schemaDefinition));
            if ($validate)
            {
                $schemaValidation  = static::validateSchemaDefinition($schemaDefinition);
                if (!$schemaValidation['isValid'])
                {
                    $errorMessage   = Zurmo::t('Core', 'Invalid Schema definition received for {{tableName}}.',
                                                array('{{tableName}}' => $tableName));
                    $errorMessage   .= ' ' . $schemaValidation['message'];
                    $messageLogger->addErrorMessage($errorMessage);
                    throw new CException($errorMessage);
                }
            }

            $columnsAndIndexes  = reset($schemaDefinition);
            if (ProcessedTableCache::isProcessed($tableName, static::CACHE_KEY) && Yii::app()->params['isFreshInstall'])
            {
                // we don't skip if running under updateSchema as we might have multiple requests to update same table.
                return;
            }

            $messageLogger->addInfoMessage(Zurmo::t('Core', 'Creating/Updating schema for {{tableName}}',
                                                                        array('{{tableName}}' => $tableName)));
            $existingFields     = array();
            // only check for table existence if we are not on fresh install
            if (!isset(Yii::app()->params['isFreshInstall']) || !Yii::app()->params['isFreshInstall'])
            {
                try
                {
                    $existingFields     = ZurmoRedBean::$writer->getColumnsWithDetails($tableName);
                }
                catch (RedBean_Exception_SQL $e)
                {
                    //42S02 - Table does not exist.
                    if (!in_array($e->getSQLState(), array('42S02')))
                    {
                        throw $e;
                    }
                }
            }

            if (empty($existingFields))
            {
                $query = static::resolveCreateTableQuery($tableName, $columnsAndIndexes);
            }
            else
            {
                $existingIndexes    = ZurmoRedBean::$writer->getIndexes($tableName);
                $query  = static::resolveAlterTableQuery($tableName,
                                                            $columnsAndIndexes,
                                                            $existingFields,
                                                            $existingIndexes);
            }
            if ($query)
            {
                ZurmoRedBean::exec($query);
            }
            ProcessedTableCache::setAsProcessed($tableName, static::CACHE_KEY);
        }

        /**
         * Returns an array of processed tables.
         * @return array
         */
        public static function resolveProcessedTables()
        {
            // this is only used by tests
            return ProcessedTableCache::resolveProcessedTableNames(static::CACHE_KEY);
        }

        protected static function validateSchemaDefinition(array $schemaDefinition)
        {
            if (count($schemaDefinition) == 1)
            {
                $columnsAndIndexes = reset($schemaDefinition);
                $tableName          = key($schemaDefinition);
                if (!is_string($tableName))
                {
                    return static::returnSchemaValidationResult(
                                                        Zurmo::t('Core', 'Table name: {{tableName}} is not string',
                                                                    array('{{tableName}}' => $tableName)));
                }
                if (count($columnsAndIndexes) != 2)
                {
                    return static::returnSchemaValidationResult(
                                            Zurmo::t('Core', 'Table schema should always contain 2 sub-definitions'));
                }
                if (!isset($columnsAndIndexes['columns'], $columnsAndIndexes['indexes']))
                {
                    return static::returnSchemaValidationResult(
                                                Zurmo::t('Core', 'Table schema is either missing columns or indexes'));
                }
                $columnValidation = static::validateColumnDefinitionsFromSchema($columnsAndIndexes['columns']);
                if (!$columnValidation['isValid'])
                {
                    return $columnValidation;
                }
                return static::validateIndexDefinitionsFromSchema($columnsAndIndexes['indexes'],
                                                                    $columnsAndIndexes['columns']);
            }
            return static::returnSchemaValidationResult(
                                                Zurmo::t('Core', 'More than one table definitions defined in schema'));
        }

        protected static function validateColumnDefinitionsFromSchema(array $columns)
        {
            $keys   = array('name', 'type', 'unsigned', 'notNull', 'collation', 'default');
            foreach ($columns as $column)
            {
                if (count($column) != 6)
                {
                    return static::returnSchemaValidationResult(
                                        Zurmo::t('Core', 'Column: {{columnName}} definition should always have 6 clauses',
                                                    array('{{columnName}}' => $column['name'])));
                }
                foreach ($keys as $key)
                {
                    if (!ArrayUtil::isValidArrayIndex($key, $column))
                    {
                        return static::returnSchemaValidationResult(
                                                            Zurmo::t('Core', 'Column: {{columnName}} missing {{key}} clause',
                                                                    array('{{columnName}}' => $column['name'],
                                                                        '{{key}}' => $key)));
                    }
                }
            }
            return static::returnSchemaValidationResult(null, true);
        }

        protected static function validateIndexDefinitionsFromSchema(array $indexes, array $columns)
        {
            $columnNames = RedBeanModelMemberToColumnUtil::resolveColumnNamesArrayFromColumnSchemaDefinition($columns);
            foreach ($indexes as $indexName => $index)
            {
                $indexNameLength = strlen($indexName);
                if (!is_string($indexName))
                {
                    return static::returnSchemaValidationResult(
                                                        Zurmo::t('Core', 'Index Name: {{indexName}} is not a string',
                                                            array('{{indexName}}' => $indexName)));
                }
                if ($indexNameLength > 64)
                {
                    return static::returnSchemaValidationResult(
                            Zurmo::t('Core',
                                'Index Name: {{indexName}} is {{length}} characters, {{over}} characters over limit(64).',
                                array('{{indexName}}' => $indexName,
                                        '{{length}}' => $indexNameLength,
                                        '{{over}}' => $indexNameLength - 64)));
                }
                if (count($index) != 2)
                {
                    return static::returnSchemaValidationResult(
                                                Zurmo::t('Core', 'Index: {{indexName}} does not have 2 clauses',
                                                    array('{{indexName}}' => $indexName)));
                }
                if (!ArrayUtil::isValidArrayIndex('columns', $index))
                {
                    return static::returnSchemaValidationResult(
                                    Zurmo::t('Core', 'Index: {{indexName}} does not have indexed column names',
                                        array('{{indexName}}' => $indexName)));
                }
                if (!ArrayUtil::isValidArrayIndex('unique', $index))
                {
                    return static::returnSchemaValidationResult(
                                Zurmo::t('Core', 'Index: {{indexName}} does not have index uniqueness clause defined',
                                    array('{{indexName}}' => $indexName)));
                }
                if (!is_array($index['columns']))
                {
                    return static::returnSchemaValidationResult(
                                            Zurmo::t('Core', 'Index: {{indexName}} column definition is not an array',
                                                array('{{indexName}}' => $indexName)));
                }
                foreach ($index['columns'] as $column)
                {
                    list($column) = explode('(', $column);
                    if (!in_array($column, $columnNames))
                    {
                        return static::returnSchemaValidationResult(
                            Zurmo::t('Core', 'Index: {{indexName}} column: {{columnName}} does not exist' .
                                                ' in current schema definition provided. Columns: {{columns}}',
                                            array('{{indexName}}' => $indexName,
                                                    '{{columnName}}' => $column,
                                                    '{{columns}}' => print_r($columnNames, true))));
                    }
                }
            }
            return static::returnSchemaValidationResult(null, true);
        }

        protected static function resolveAlterQueryForColumn($column)
        {
            $columnDefinition   = $column['columnDefinition'];
            $statement          = strtoupper($column['method']) . ' ';
            $isAddition         = ($column['method'] == 'add');
            $statement          .= static::resolveColumnStatementFromDefinition($columnDefinition, $isAddition);
            return $statement;
        }

        protected static function resolveColumnUpgradeQueries($columns, $existingFields)
        {
            $columnsNeedingUpgrade      = array();
            $columnUpgradeStatements    = array();
            foreach ($columns as $column)
            {
                if ($upgradeDefinition = static::resolveColumnUpgradeDefinition($column, $existingFields))
                {
                    $columnsNeedingUpgrade[] = $upgradeDefinition;
                }
            }
            foreach ($columnsNeedingUpgrade as $columnNeedingUpgrade)
            {
                $columnUpgradeStatements[] = static::resolveAlterQueryForColumn($columnNeedingUpgrade);
            }
            return $columnUpgradeStatements;
        }

        protected static function doesIndexNeedUpgrade($indexMetadata, $existingIndexes)
        {
            $needsUpgrade   = true;
            $indexColumns   = $indexMetadata['columns'];
            sort($indexColumns);
            // get rid of (767) and other prefixes for
            // Begin Not Coding Standard
            $indexColumns   = array_map(function ($column)
                                        {
                                            $parenthesisStart = strpos($column, '(');
                                            if ($parenthesisStart !== false)
                                            {
                                                return substr($column, 0, $parenthesisStart);
                                            }
                                            return $column;
                                        }, $indexColumns);
            // End Not Coding Standard
            foreach ($existingIndexes as $existingIndexMetadata)
            {
                $existingIndexColumns = $existingIndexMetadata['columns'];
                sort($existingIndexColumns);
                if ($indexMetadata['unique'] === $existingIndexMetadata['unique'] && $indexColumns === $existingIndexColumns)
                {
                    $needsUpgrade = false;
                    break;
                }
            }
            return $needsUpgrade;
        }

        protected static function resolveIndexUpgradeQueries($indexes, $existingIndexes)
        {
            $indexesNeedingUpgrade      = array();
            $indexUpgradeStatements     = array();
            foreach ($indexes as $indexName => $indexMetadata)
            {
                if (static::doesIndexNeedUpgrade($indexMetadata, $existingIndexes))
                {
                    $indexesNeedingUpgrade[$indexName] = $indexMetadata;
                }
            }
            foreach ($indexesNeedingUpgrade as $indexName => $indexMetadata)
            {
                $indexUpgradeStatements[] = static::resolveIndexStatementCreation($indexName, $indexMetadata, true);
            }
            return $indexUpgradeStatements;
        }

        protected static function resolveAlterTableQuery($tableName, $columnsAndIndexes, $existingFields, $existingIndexes)
        {
            $upgradeStatements       = array();
            $columnUpgradeStatements = static::resolveColumnUpgradeQueries($columnsAndIndexes['columns'], $existingFields);
            $indexUpgradeStatements  = static::resolveIndexUpgradeQueries($columnsAndIndexes['indexes'], $existingIndexes);
            $upgradeStatements       = CMap::mergeArray($columnUpgradeStatements, $indexUpgradeStatements);
            if (!empty($upgradeStatements))
            {
                $upgradeStatements  = join(',' . PHP_EOL, $upgradeStatements); // Not Coding Standard
                $query              = "ALTER TABLE `${tableName}` " . PHP_EOL .
                                        $upgradeStatements . ";";
                return $query;
            }
            return false;
        }

        protected static function resolveColumnUpgradeDefinition($column, $existingFields)
        {
            if (!in_array($column['name'], array_keys($existingFields)))
            {
                return array('columnDefinition' => $column, 'method' => 'add');
            }
            elseif (static::doesColumnNeedUpgrade($column, $existingFields[$column['name']]))
            {
                return array('columnDefinition' => $column, 'method' => 'change');
            }
            return null;
        }

        protected static function doesColumnNeedUpgrade($column, $existingField)
        {
            if (static::isColumnTypeSameAsExistingFieldOrSmaller($column, $existingField) &&
                static::isColumnNullSameAsExistingField($column, $existingField) &&
                static::isColumnDefaultValueSameAsExistingField($column, $existingField))
            {
                return false;
            }
            return true;
        }

        protected static function isColumnTypeSameAsExistingFieldOrSmaller($column, $existingField)
        {
            if (!static::isColumnTypeSameAsExistingField($column, $existingField))
            {
                // column type doesn't match field type exactly, lets try to figure out which part it doesn't match.
                $fieldType          = null;
                $fieldLength        = null;
                $fieldUnsigned      = null;
                list($fieldType, $fieldLength, $fieldUnsigned) = static::resolveExistingFieldTypeAndLengthAndUsigned(
                                                                                                        $existingField);
                list($columnType, $columnLength)            = static::resolveColumnTypeAndLength($column['type']);
                if ($columnType == $fieldType)
                {
                    // type is same, unsigned or length might be different.
                    return static::isColumnLengthShrinkOrConversionToUnsigned($columnLength, $fieldLength,
                                                                                $column['unsigned'], $fieldUnsigned);
                }
                return static::isColumnTypeSmallerThanExistingFieldType($columnType, $fieldType);
            }
            return false;
        }

        protected static function isColumnLengthShrinkOrConversionToUnsigned($columnLength, $fieldLength,
                                                                                $columnUnsigned, $fieldUnsigned)
        {
            //($fieldUnsigned !== $columnUnsigned && $columnUnsigned = 'UNSIGNED') ||
            // we do not check for signed/unsigned checks as again, we do not know if it would be safe.
            // changing unsigned to signed might lose some data thats beyond signed range for that column
            // changing signed to unsigned might lose some signed data;
            if ($columnLength < $fieldLength)
            {
                return true;
            }
            return false;
        }

        protected static function isColumnTypeSmallerThanExistingFieldType($columnType, $fieldType)
        {
            return false;
            // We have intentionally kept this here but not implemented it.
            // Design wise its bit tough decision with regard to what to allow and what not to.
            // For example, do we allow change from a text type to mediumblob?
            // mediumblob is smaller in size but may be user did that knowingly and he wants us to do in db.
            // and if we don't then his binary data won't be stored as he expects it to be.
            // What now?
            // TODO: @Shoaibi: Low: Implement
            $integerTypes   = array_keys(DatabaseCompatibilityUtil::resolveIntegerMaxAllowedValuesByType());
            // TODO: @Shoaibi: Low: Shouldn't these types also come from DbCU
            $floatTypes     = array('float', 'double', 'decimal');
            $stringTypes    = array('char', 'varchar', 'tinytext', 'mediumtext', 'text', 'longtext');
            $blogTypes      = array('tinyblob', 'mediumblob', 'blob', 'longblob');
            //DatabaseCompatibilityUtil::resolveIntegerMaxAllowedValuesByType
            // type is different, check if we are switching to higher type or not.
            // check int types
            // check float types
            // check string types(blob inclusive)
            // if types from totally different domain, then what? int < float < text < blob?
        }

        protected static function resolveExistingFieldTypeAndLengthAndUsigned(array $existingField)
        {
            $existingFieldType          = $existingField['Type'];
            $existingFieldUnsigned      = null;
            if (strpos($existingField['Type'], ' ') !== false)
            {
                list($existingFieldType, $existingFieldUnsigned) = explode(' ', $existingField['Type']);
            }
            list($existingFieldType, $existingFieldLength) = static::resolveColumnTypeAndLength($existingFieldType);
            return array($existingFieldType, $existingFieldLength, $existingFieldUnsigned);
        }

        protected static function resolveColumnTypeAndLength($columnTypeAndLength)
        {
            $columnType                 = $columnTypeAndLength;
            $columnLength               = null;
            $openingBracePosition       = strpos($columnType, '(');
            $closingBracePosition       = strpos($columnType, ')');
            if  ($openingBracePosition && $closingBracePosition)
            {
                $columnLength    = substr($columnType, $openingBracePosition + 1,
                                            $closingBracePosition - $openingBracePosition -1);
                $columnType      = strtolower(substr($columnType, 0, $openingBracePosition));
            }
            return array($columnType, $columnLength);
        }

        protected static function isColumnTypeSameAsExistingField($column, $existingField)
        {
            $resolvedType       = $column['type'];
            if ($column['unsigned'])
            {
                $resolvedType .= ' ' . $column['unsigned'];
            }
            $resolvedType       = strtolower($resolvedType);
            $existingFieldType  = strtolower($existingField['Type']);
            return ($resolvedType == $existingFieldType);
        }

        protected static function isColumnNullSameAsExistingField($column, $existingField)
        {
            $notNull = 'NOT NULL'; // Not Coding Standard
            if ($existingField['Null'] == 'YES') // Not Coding Standard
            {
                $notNull = 'NULL'; // Not Coding Standard
            }
            return ($column['notNull'] == $notNull);
        }

        protected static function isColumnDefaultValueSameAsExistingField($column, $existingField)
        {
            $default = null;
            if ($column['default'] != 'DEFAULT NULL') // Not Coding Standard
            {
                $default = substr($column['default'], strpos($column['default'], 'DEFAULT '));
            }
            return ($default == $existingField['Default']);
        }

        protected static function resolveCreateTableQuery($tableName, $columnsAndIndexesSchema)
        {
            $columns        = array('`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT'); // Not Coding Standard
            $indexes        = array('PRIMARY KEY (id)');
            foreach ($columnsAndIndexesSchema['columns'] as $column)
            {
                $columns[]    = static::resolveColumnStatementFromDefinition($column, true);
            }
            foreach ($columnsAndIndexesSchema['indexes'] as $indexName => $indexMetadata)
            {
                $indexes[]    = static::resolveIndexStatementCreation($indexName, $indexMetadata, false);
            }
            // PHP_EOLs below are purely for readability, sql would work just fine without it.
            $tableMetadata  = CMap::mergeArray($columns, $indexes);
            $tableMetadata  = join(',' . PHP_EOL, $tableMetadata); // Not Coding Standard
            $query          = "CREATE TABLE `${tableName}` (" . PHP_EOL .
                                $tableMetadata . PHP_EOL .
                                " ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;"; // Not Coding Standard
            return $query;
        }

        protected static function resolveColumnStatementFromDefinition($column, $isAddition = true)
        {
            extract($column);
            if ($isAddition)
            {
                $clause = "`${name}` ${type} ${unsigned} ${notNull} ${collation} {$default}";
            }
            else
            {
                $clause = "`${name}` `${name}` ${type} ${unsigned} ${collation} ${notNull} {$default}";
            }
            return $clause;
        }

        protected static function resolveIndexStatementCreation($indexName, $indexMetadata, $alterTable = false)
        {
            $clause = "KEY ${indexName} (" . join(',', $indexMetadata['columns']) . ")"; // Not Coding Standard
            if ($indexMetadata['unique'])
            {
                $clause = 'UNIQUE ' . $clause;
            }
            if ($alterTable)
            {
                $clause = 'ADD ' . $clause;
            }
            return $clause;
        }

        protected static function returnSchemaValidationResult($message, $isValid = false)
        {
            return compact('isValid', 'message');
        }
    }
?>