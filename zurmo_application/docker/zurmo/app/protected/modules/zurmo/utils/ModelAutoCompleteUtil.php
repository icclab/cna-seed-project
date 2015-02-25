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
     * Helper class to convert a model search into
     * an Jui AutoComplete ready array.  There are
     * three types of searches, Generic, User, and Person.
     * Person and User utilize fullName instead of name
     * while User adds the additional usage of username
     * in the resulting label
     */
    class ModelAutoCompleteUtil extends BaseModelAutoCompleteUtil
    {
        /**
         * @param $modelClassName
         * @param $partialName
         * @param $pageSize
         * @return array
         * @throws NotImplementedException
         * @throws NotSupportedException
         */
        public static function getByPartialName($modelClassName, $partialName, $pageSize, $autoCompleteOptions = null)
        {
            assert('is_string($modelClassName)');
            assert('is_string($partialName)');
            assert('is_int($pageSize)');
            if ($modelClassName == 'User')
            {
                return ModelAutoCompleteUtil::getUserResults($partialName, $pageSize, $autoCompleteOptions);
            }
            elseif ($modelClassName == 'Contact')
            {
                throw new NotSupportedException();
            }
            elseif ($modelClassName == 'Person')
            {
                throw new NotImplementedException();
            }
            else
            {
                return ModelAutoCompleteUtil::getGenericResults($modelClassName, $partialName, $pageSize, $autoCompleteOptions);
            }
        }

        /**
         * Given a partial term, search across modules that support global search.
         * @param $partialTerm
         * @param $pageSize
         * @param User $user
         * @param null $scopeData
         * @return array
         */
        public static function getGlobalSearchResultsByPartialTerm($partialTerm, $pageSize, User $user, $scopeData = null)
        {
            assert('is_string($partialTerm)');
            assert('is_int($pageSize)');
            assert('$user->id > 0');
            assert('$scopeData == null || is_array($scopeData)');
            $modelClassNamesAndSearchAttributeData = static::makeModelClassNamesAndSearchAttributeData($partialTerm, $user, $scopeData);
            if (empty($modelClassNamesAndSearchAttributeData))
            {
                return array(static::makeNoResultsFoundResultsData());
            }
            $dataProvider = new RedBeanModelsDataProvider('anId', null, false, $modelClassNamesAndSearchAttributeData,
                                                          array('pagination' => array('pageSize' => $pageSize)));
            $data = $dataProvider->getData();
            if (empty($data))
            {
                return array(static::makeNoResultsFoundResultsData());
            }
            $autoCompleteResults = array();
            foreach ($data as $model)
            {
                $autoCompleteResults[] = static::makeModelResultsData($model);
            }
            return $autoCompleteResults;
        }

        /**
         * Given a name of a customFieldData object and a term to search on return a JSON encoded
         * array of autocomplete search results.
         * @param $customFieldDataName
         * @param $partialName
         * @return array
         */
        public static function getCustomFieldDataByPartialName($customFieldDataName, $partialName)
        {
            assert('is_string($customFieldDataName)');
            assert('is_string($partialName)');
            $customFieldData     = CustomFieldData::getByName($customFieldDataName);
            $dataAndLabels       = CustomFieldDataUtil::
                getDataIndexedByDataAndTranslatedLabelsByLanguage($customFieldData, Yii::app()->language);
            $autoCompleteResults = array();
            foreach ($dataAndLabels as $data => $label)
            {
                if (stripos($label, $partialName) === 0)
                {
                    $autoCompleteResults[] = array(
                        'id'   => $data,
                        'name' => $label,
                    );
                }
            }
            return $autoCompleteResults;
        }

        protected static function makeNoResultsFoundResultsData()
        {
            return array('href' => '', 'label' => Zurmo::t('Core', 'No Results Found'), 'iconClass' => '');
        }

        protected static function makeModelResultsData(RedBeanModel $model)
        {
            $moduleClassName = ModelStateUtil::resolveModuleClassNameByStateOfModel($model);
            $route           = Yii::app()->createUrl($moduleClassName::getDirectoryName()
                                                     . '/default/details/', array('id' => $model->id));
            return array('href'      => $route,
                         'label'     => strval($model),
                         'iconClass' => 'autocomplete-icon-' . $moduleClassName);
        }

        protected static function makeModelClassNamesAndSearchAttributeData($partialTerm, User $user, $scopeData)
        {
            assert('is_string($partialTerm)');
            assert('$user->id > 0');
            assert('$scopeData == null || is_array($scopeData)');
            $modelClassNamesAndSearchAttributeData = array();
            $modules = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                $globalSearchFormClassName = $module::getGlobalSearchFormClassName();
                if (GlobalSearchUtil::resolveIfModuleShouldBeGloballySearched($module) &&
                    $globalSearchFormClassName != null &&
                    RightsUtil::canUserAccessModule(get_class($module), $user) &&
                    ($scopeData == null || in_array($module->getName(), $scopeData)))
                {
                    $modelClassName                = $module::getPrimaryModelName();
                    $searchAttributes              = MixedTermSearchUtil::
                                                     getGlobalSearchAttributeByModuleAndPartialTerm($module,
                                                                                                    $partialTerm);
                    if (!empty($searchAttributes))
                    {
                        $model                         = new $modelClassName(false);
                        assert('$model instanceof RedBeanModel');
                        $searchForm                    = new $globalSearchFormClassName($model);
                        assert('$searchForm instanceof SearchForm');
                        $metadataAdapter               = new SearchDataProviderMetadataAdapter(
                                                         $searchForm, $user->id, $searchAttributes);
                        $metadata                      = $metadataAdapter->getAdaptedMetadata(false);
                        $stateMetadataAdapterClassName = $module::getStateMetadataAdapterClassName();
                        if ($stateMetadataAdapterClassName != null)
                        {
                            $stateMetadataAdapter = new $stateMetadataAdapterClassName($metadata);
                            $metadata = $stateMetadataAdapter->getAdaptedDataProviderMetadata();
                        }
                        $modelClassNamesAndSearchAttributeData[$globalSearchFormClassName] =
                        array($modelClassName => $metadata);
                    }
                }
            }
            return $modelClassNamesAndSearchAttributeData;
        }

        protected static function getGenericResults($modelClassName, $partialName, $pageSize, $autoCompleteOptions)
        {
            $autoCompleteResults = array();
            $joinTablesAdapter = null;
            static::sanitizeSearchTerm($partialName);
            $where = "name like lower('{$partialName}%')";
            static::handleAutoCompleteOptions($joinTablesAdapter, $where, $autoCompleteOptions);
            $models = $modelClassName::getSubset($joinTablesAdapter, null, $pageSize, $where, 'name');
            foreach ($models as $model)
            {
                $autoCompleteResults[] = array(
                    'id'    => $model->id,
                    'value' => strval($model),
                    'label' => strval($model)
                );
            }
            return $autoCompleteResults;
        }

        protected static function getUserResults($partialName, $pageSize, $autoCompleteOptions = null)
        {
            $autoCompleteResults  = array();
            $users                = UserSearch::getUsersByPartialFullName($partialName, $pageSize, $autoCompleteOptions);
            foreach ($users as $user)
            {
                $autoCompleteResults[] = array(
                    'id'    => $user->id,
                    'value' => strval($user),
                    'label' => strval($user) .' (' . $user->username . ')'
                );
            }
            return $autoCompleteResults;
        }
    }
?>