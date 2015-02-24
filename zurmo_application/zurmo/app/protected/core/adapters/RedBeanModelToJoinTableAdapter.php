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
     * Adapter class to generate junction table for MANY_MANY relations
     */
    abstract class RedBeanModelToJoinTableAdapter
    {
        const CACHE_KEY = 'RedBeanModelToJoinTableAdapter_processedManyManyTableNames';

        /**
         * Generates junction table for a MANY_MANY relationship
         * @param string $modelClassName
         * @param array $relationMetadata
         * @param $messageLogger
         */
        public static function resolve($modelClassName, array $relationMetadata, & $messageLogger)
        {
            if (empty($modelClassName) || !@class_exists($modelClassName) || empty($relationMetadata) ||
                           count($relationMetadata) < 2 || $relationMetadata[0] != RedBeanModel::MANY_MANY ||
                                                                                !@class_exists($relationMetadata[1]))
            {
                return;
            }
            $relatedModelClassName  = $relationMetadata[1];
            $relationLinkName       = null;
            if (isset($relationMetadata[4]))
            {
                $relationLinkName       = $relationMetadata[4];
            }
            $tableName              = RedBeanManyToManyRelatedModels::getTableNameByModelClassNames($modelClassName,
                                                                                                $relatedModelClassName,
                                                                                                $relationLinkName);

            if (ProcessedTableCache::isProcessed($tableName, static::CACHE_KEY))
            {
                return;
            }
            $columns            = array();
            $columns[]          = RedBeanModelMemberToColumnUtil::resolveForeignKeyColumnMetadata(null,
                                                                                                    $modelClassName);
            $columns[]          = RedBeanModelMemberToColumnUtil::resolveForeignKeyColumnMetadata(null,
                                                                                                $relatedModelClassName);
            $indexes            = static::resolveIndexesByColumnNames($columns);
            $schemaDefinition   = array($tableName => array(
                                                        'columns'   => $columns,
                                                        'indexes'   => $indexes,
                                                        ),
                                                    );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::
                                                generateOrUpdateTableBySchemaDefinition($schemaDefinition, $messageLogger);
            ProcessedTableCache::setAsProcessed($tableName, static::CACHE_KEY);
        }

        public static function resolveProcessedTableNames()
        {
            return ProcessedTableCache::resolveProcessedTableNames(static::CACHE_KEY);
        }

        protected static function resolveIndexesByColumnNames($columns)
        {
            $indexes                = array();
            $compositeIndexKey      = null;
            $compositeIndexValues   = array('columns' => array(), 'unique' => true);
            foreach ($columns as $column)
            {
                $columnName             = $column['name'];
                $indexName              = RedBeanModelMemberIndexMetadataAdapter::resolveRandomIndexName($columnName, false);
                $indexes[$indexName]    = array('columns'     => array($columnName),
                                                'unique'      => false,
                                            );
                if (!empty($compositeIndexKey))
                {
                    $compositeIndexKey .= '_';
                }
                $compositeIndexKey  .= $columnName;
                $compositeIndexValues['columns'][] = $columnName;
            }
            $indexName              = RedBeanModelMemberIndexMetadataAdapter::resolveRandomIndexName($compositeIndexKey, true);
            $indexes[$indexName]    = $compositeIndexValues;
            return $indexes;
        }
    }
?>