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

    class ZurmoMySqlQueryWriter extends RedBean_QueryWriter_MySQL
    {
        /**
         * Returns true/false depending on if the supplied tableName exists
         * @param $tableName
         * @return bool
         */
        public function doesTableExist($tableName)
        {
            $tableName  = strtolower($tableName);
            $result     = $this->adapter->get("SHOW TABLES LIKE '$tableName'");
            return (count($result) > 0);
        }

        /**
         * Returns true/false depending on if the supplied columnName exists in table.
         * @param $tableName
         * @param $columnName
         * @return bool
         */
        public function doesColumnExist($tableName, $columnName)
        {
            $tableName  = $this->safeTable($tableName);
            $result     = $this->adapter->get("SHOW COLUMNS FROM $tableName LIKE '$columnName'");
            return (count($result) > 0);
        }

        /**
         * Generates an array with column details such as not null, type, etc.
         * @param $tableName
         * @return array
         */
        public function getColumnsWithDetails($tableName)
        {
            $columns    = array();
            $tableName  = $this->safeTable($tableName);
            $columnsRaw = $this->adapter->get("DESCRIBE $tableName");
            foreach ($columnsRaw as $r)
            {
                $columns[$r['Field']]   =   $r;
            }
            return $columns;
        }

        /**
         * Returns array of indexes for provided tableName
         * @param $tableName
         * @return array
         */
        public function getIndexes($tableName)
        {
            $indexes    = array();
            $tableName  = $this->safeTable($tableName);
            $indexesRaw = $this->adapter->get("SHOW KEYS FROM $tableName");
            foreach ($indexesRaw as $index)
            {
                $indexName  = $index['Key_name'];
                $column     = $index['Column_name'];
                $unique     = (!(bool)$index['Non_unique']);
                $indexes[$indexName]['unique']  = $unique;
                if (isset($indexes[$indexName]['columns']))
                {
                    $indexes[$indexName]['columns'][] = $column;
                }
                else
                {
                    $indexes[$indexName]['columns'] = array($column);
                }
            }
            return $indexes;
        }

        /**
         * Gets the count of how many columns there are in a table minus the initial 'id' column.
         * @param string $tableName
         * @param bool $excludeIdColumn
         * @return integer
         */
        public function getColumnCountByTableName($tableName, $excludeIdColumn = true)
        {
            $columns = $this->getColumns($tableName);
            $count = count($columns);
            if ($excludeIdColumn)
            {
                $count--;
            }
            return $count;
        }

        /**
         * Get the first row of a table.  if no rows exist, an NoRowsInTableException is thrown.
         * @param string $tableName
         */
        public function getFirstRowByTableName($tableName)
        {
            $tableName  = $this->safeTable($tableName);
            $sql = 'select * from ' . $tableName . ' limit 1';
            try
            {
                $data = $this->adapter->getRow($sql); // we don't really need getRow here as we have used 'limit 1', still...
            }
            catch (RedBean_Exception_SQL $e)
            {
                throw new NoRowsInTableException();
            }
            return $data;
        }

        /**
         * Drops a table by the given table name.
         * @param string $tableName
         */
        public function dropTableByTableName($tableName)
        {
            $tableName  = $this->safeTable($tableName);
            $this->adapter->exec("drop table if exists $tableName");
        }

        /**
         * Do everything that needs to be done to format a table name.
         * @param string $name of table
         * @return string table name
         */
        public function safeTable($name, $noQuotes = false)
        {
            assert('is_string($name)');
            return parent::safeTable(strtolower($name), $noQuotes);
        }
    }
?>