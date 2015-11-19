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

    abstract class RedBeanModelMemberToColumnUtil
    {
        public static function resolve($memberName)
        {
            return strtolower($memberName);
        }

        public static function resolveForeignKeyColumnMetadata($name, $relatedModelClass = null)
        {
            if (!isset($name))
            {
                if (isset($relatedModelClass))
                {
                    $name       = static::resolveForeignKeyNameByModelName($relatedModelClass);
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            // this is foreign key, we force it to be assumed unsigned.
            $unsigned   = DatabaseCompatibilityUtil::resolveUnsignedByHintType('integer', false, $name);
            return static::resolveColumnMetadataByHintType($name, 'integer', null, $unsigned);
        }

        public static function resolveColumnMetadataByHintType($name, $hintType = 'string', $length = 255,
                                                                $unsigned = null, $notNull = 'NULL', // Not Coding Standard
                                                                $default = 'DEFAULT NULL', $collation = null, // Not Coding Standard
                                                                $resolveName = true)
        {
            // TODO: @Shoaibi: Critical: write tests for: integer, smallint, tinyint, blob, date, datetime, double, string, text, email, url
            //  with and without column ending with _id, check collation, unsigned, type, default
            if ($resolveName)
            {
                $name           = static::resolve($name);
            }
            // map reasonable default values
            $defaults           = array(
                'hintType'      => 'string',
                'length'        => 255,
                'notNull'       => 'NULL', // Not Coding Standard
                'default'       => 'DEFAULT NULL', // Not Coding Standard
                'unsigned'      => 'eval:DatabaseCompatibilityUtil::resolveUnsignedByHintType($hintType, ' .
                                            RedBeanModelMemberRulesToColumnAdapter::ASSUME_SIGNED .", '{$name}');",
                'collation'     => 'eval:DatabaseCompatibilityUtil::resolveCollationByHintType($hintType);',
            );

            foreach ($defaults as $key => $defaultValue)
            {
                if (!isset($$key))
                {
                    MetadataUtil::resolveEvaluateSubString($defaultValue, 'hintType', $hintType);
                    $$key   = $defaultValue;
                }
            }
            // field is set to be NOT NULL in db, its default can't be 'NULL', unsetting variable. // Not Coding Standard
            if ($notNull !== 'NULL') // Not Coding Standard
            {
                $default    = null;
            }
            // resolve hint type to db type.
            $type               = DatabaseCompatibilityUtil::mapHintTypeIntoDatabaseColumnType($hintType, $length);
            $column             = compact('name', 'type', 'unsigned', 'notNull', 'collation', 'default');
            return $column;
        }

        public static function resolveColumnNamesArrayFromColumnSchemaDefinition($columns)
        {
            $columnNames = array_map("static::extractNameFromColumnSchemaDefinition", $columns);
            return $columnNames;
        }

        protected static function extractNameFromColumnSchemaDefinition($column)
        {
            return $column['name'];
        }

        protected static function resolveForeignKeyNameByModelName($className)
        {
            return $className::getTableName() . '_id';
        }
    }
?>