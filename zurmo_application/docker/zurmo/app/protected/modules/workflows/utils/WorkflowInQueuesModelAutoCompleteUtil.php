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
     * Class WorkflowInQueuesModelAutoCompleteUtil
     * Helper class for handling searching on model items for workflow in queues such as by time queue and message queue
     * search / list views.
     */
    class WorkflowInQueuesModelAutoCompleteUtil extends ModelAutoCompleteUtil
    {
        protected static function makeModelClassNamesAndSearchAttributeData($partialTerm, User $user, $scopeData)
        {
            assert('is_string($partialTerm)');
            assert('$user->id > 0');
            assert('$scopeData == null || is_array($scopeData)');
            $modelClassNamesAndSearchAttributeData = array();
            $modelNamesAndLabels = WorkflownQueuesSearchForm::getInQueueSearchableModelNamesAndLabels();
            foreach ($modelNamesAndLabels as $modelClassName => $notUsed)
            {
                $moduleClassName = $modelClassName::getModuleClassName();
                $module          = Yii::app()->findModule($moduleClassName::getDirectoryName());
                $globalSearchFormClassName = $moduleClassName::getGlobalSearchFormClassName();
                if ($globalSearchFormClassName != null &&
                    RightsUtil::canUserAccessModule(get_class($module), $user) &&
                    ($scopeData == null || in_array($modelClassName, $scopeData)))
                {
                    $searchAttributes = MixedTermSearchUtil::
                                        getGlobalSearchAttributeByModuleAndPartialTerm($module, $partialTerm);
                    if (!empty($searchAttributes))
                    {
                        $model                         = new $modelClassName(false);
                        assert('$model instanceof RedBeanModel');
                        $searchForm                    = new $globalSearchFormClassName($model);
                        assert('$searchForm instanceof SearchForm');
                        $metadataAdapter               = new SearchDataProviderMetadataAdapter(
                                                         $searchForm, $user->id, $searchAttributes);
                        $metadata                      = $metadataAdapter->getAdaptedMetadata(false);
                        $modelClassNamesAndSearchAttributeData[$globalSearchFormClassName] =
                            array($modelClassName => $metadata);
                    }
                }
            }
            return $modelClassNamesAndSearchAttributeData;
        }

        protected static function makeNoResultsFoundResultsData()
        {
            return array(  'itemId'         => null,
                           'modelClassName' => null,
                           'value'          => null,
                           'label'          => Zurmo::t('Core', 'No Results Found'),
                           'iconClass'      => '');
        }

        protected static function makeModelResultsData(RedBeanModel $model)
        {
            $moduleClassName = ModelStateUtil::resolveModuleClassNameByStateOfModel($model);
            return array(  'itemId'         => $model->getClassId('Item'),
                'modelClassName' => get_class($model),
                'value'          => strval($model),
                'label'          => strval($model),
                'iconClass'      => 'autocomplete-icon-' . $moduleClassName);
        }
    }
?>