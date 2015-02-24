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
     * Adapter class to generate table schema when provided an model name
     */
    abstract class RedBeanModelToTableSchemaAdapter
    {
        /**
         * Generates Table schema for a model class using its metadata of members, relations, mixins and indexes.
         * @param string $modelClassName
         * @param $messageLogger
         * @return array|bool
         */
        public static function resolve($modelClassName, & $messageLogger)
        {
            if (empty($modelClassName) || !@class_exists($modelClassName) || !$modelClassName::getCanHaveBean())
            {
                return false;
            }
            $metadata                       = $modelClassName::getMetadata();
            $modelMetadata                  = array();
            if (isset($metadata[$modelClassName]))
            {
                $modelMetadata                  = $metadata[$modelClassName];
            }
            $memberColumns                  = array();
            $relationColumns                = array();
            $indexes                        = array();
            $uniqueIndexesFromValidators    = array();
            $parentColumnName               = null;

            if (isset($modelMetadata['members']))
            {
                if (!isset($modelMetadata['rules']))
                {
                    $errorMessage = Zurmo::t('Core', '{{model}} must have both, members and rules, set.',
                                                array('{{model}}' => $modelClassName));
                    $messageLogger->addErrorMessage($errorMessage);
                    throw new CException($errorMessage);
                }
                $memberColumns      = RedBeanModelMemberRulesToColumnsAdapter::resolve($modelClassName,
                                                                                        $modelMetadata['members'],
                                                                                        $modelMetadata['rules'],
                                                                                        $messageLogger);
                $uniqueIndexesFromValidators = RedBeanModelMemberRulesToColumnAdapter::
                                                                resolveUniqueIndexesFromValidator($modelClassName);
            }
            if (isset($modelMetadata['relations']))
            {
                $relationColumns    = RedBeanModelRelationsToColumnsAdapter::resolve($modelClassName,
                                                                                        $modelMetadata['relations'],
                                                                                        $messageLogger);
            }
            if (isset($modelMetadata['indexes']) || !empty($uniqueIndexesFromValidators))
            {
                $indexesMetadata        = $uniqueIndexesFromValidators;
                if (!empty($modelMetadata['indexes']))
                {
                    if (!empty($indexesMetadata))
                    {
                        $indexesMetadata = CMap::mergeArray($indexesMetadata, $modelMetadata['indexes']);
                    }
                    else
                    {
                        $indexesMetadata    = $modelMetadata['indexes'];
                    }
                }
                if (!empty($indexesMetadata))
                {
                    $indexes            = RedBeanModelMemberIndexesMetadataAdapter::resolve($modelClassName,
                                                                                            $indexesMetadata,
                                                                                            $messageLogger);
                }
            }
            $parentColumnName   = RedBeanModelChildParentRelationshipToColumnAdapter::resolve($modelClassName);
            if ($parentColumnName)
            {
                $memberColumns[] = $parentColumnName;
            }
            $mixinColumns       = RedBeanModelMixinsToColumnsAdapter::resolve($modelClassName, $messageLogger);
            $columns            = CMap::mergeArray($memberColumns, $mixinColumns, $relationColumns);
            $tableName          = $modelClassName::getTableName();
            $schemaDefinition   = array($tableName => array('columns' => $columns, 'indexes' => $indexes));
            return $schemaDefinition;
      }
    }
?>