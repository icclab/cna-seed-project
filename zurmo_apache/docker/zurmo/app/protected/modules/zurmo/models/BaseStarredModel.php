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

    class BaseStarredModel extends RedBeanModel
    {
        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'relations' => array(
                    'user'     => array(static::HAS_ONE,  'User'),
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getModuleClassName()
        {
            return 'ZurmoModule';
        }

        /**
         * Returns the display name for the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('ZurmoModule', 'Base Starred Model', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('ZurmoModule', 'Base Starred Models', array(), null, $language);
        }

        public static function getCountByUserIdAndModelId($userId, $modelId)
        {
            return static::getByUserIdAndModelId($userId, $modelId, true);
        }

        public static function getByUserIdAndModelId($userId, $modelId, $countOnly = false)
        {
            assert('is_int($userId) || is_string($userId) || $userId === null');
            assert('is_int($modelId) || is_string($modelId)');
            $relationName = static::getRelationName();
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => $relationName,
                    'relatedAttributeName'      => 'id',
                    'operatorType'              => 'equals',
                    'value'                     => intval($modelId),
                ),
            );
            $searchAttributeData['structure'] = '1';
            if ($userId)
            {
                $searchAttributeData['clauses'][] = array(
                                                    'attributeName'             => 'user',
                                                    'relatedAttributeName'      => 'id',
                                                    'operatorType'              => 'equals',
                                                    'value'                     => intval($userId),
                                                );
                $searchAttributeData['structure'] = '(1 and 2)';
            }
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            if ($countOnly)
            {
                return self::getCount($joinTablesAdapter, $where, get_called_class(), true);
            }
            return self::getSubset($joinTablesAdapter, null, null, $where, null);
        }

        public static function markModelAsStarredByUserIdAndModelId($userId, $modelId)
        {
            $className                      = get_called_class();
            $relationName                   = static::getRelationName();
            $relatedModeClassName           = static::getRelatedModelClassName();
            $relatedModel                   = $relatedModeClassName::getById(intval($modelId));
            $starredModel                   = new $className();
            $starredModel->user             = User::getById($userId);
            $starredModel->$relationName    = $relatedModel;
            if (!$starredModel->save())
            {
                throw new FailedToSaveModelException();
            }
            return true;
        }

        public static function unmarkModelAsStarredByUserIdAndModelId($userId, $modelId)
        {
            $models = static::getByUserIdAndModelId($userId, $modelId);
            foreach ($models as $model)
            {
                if (!$model->delete())
                {
                    throw new NotSupportedException("Unable to delete id: " . $model->id);
                }
            }
            return true;
        }

        protected static function getRelationName()
        {
            throw new NotImplementedException();
        }

        protected static function getRelatedModelClassName()
        {
            return ucfirst(static::getRelationName());
        }

        protected static function getIndexesDefinition()
        {
            $relatedModelClassName = static::getRelatedModelClassName();
            $relatedColumnName = $relatedModelClassName::getTableName() . '_id';
            // can't use self:: here as getTableName() uses get_called_class
            $baseStarredColumnName = BaseStarredModel::getTableName() . '_id';
            return array($baseStarredColumnName . '_' . $relatedColumnName => array(
                                                'members' => array($baseStarredColumnName, $relatedColumnName),
                                                'unique' => true,
                                                )
                                            );
        }
    }
?>