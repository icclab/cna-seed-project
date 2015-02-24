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
     * Helper class for list view merge functionality
     */
    class ListViewMergeUtil
    {
        /**
         * Resolve element during form layout render for list view merge
         * @param Element $element
         * @param string $preContentViewClass
         * @param array $selectedModels
         * @param RedBeanModel $primaryModel
         * @param string $modelAttributeAndElementDataToMergeItemClass
         */
        public static function resolveElementDuringFormLayoutRenderForListViewMerge( & $element,
                                                                                     $preContentViewClass,
                                                                                     $selectedModels,
                                                                                     $primaryModel,
                                                                                     $modelAttributeAndElementDataToMergeItemClass
                                                                                    )
        {
            assert('is_string($preContentViewClass)');
            assert('is_array($selectedModels)');
            assert('$primaryModel instanceof RedBeanModel');
            assert('is_string($modelAttributeAndElementDataToMergeItemClass)');

            if ($element->getAttribute() != 'null')
            {
                $attributes       = array($element->getAttribute());
            }
            else
            {
                $elementClassName = get_class($element);
                $attributes       = $elementClassName::getModelAttributeNames();
            }
            $preContent = Yii::app()->getController()->widget(
                                                                $preContentViewClass,
                                                                array(
                                                                    'selectedModels' => $selectedModels,
                                                                    'attributes'     => $attributes,
                                                                    'primaryModel'   => $primaryModel,
                                                                    'element'        => $element,
                                                                    'modelAttributeAndElementDataToMergeItemClass'
                                                                            => $modelAttributeAndElementDataToMergeItemClass
                                                                ),
                                                            true);
            $element->editableTemplate = '<th>{label}</th><td colspan="{colspan}">' . $preContent . '{content}{error}</td>';
        }

        /**
         * Sets primary model for the merge
         * @param ModelsListDuplicateMergedModelForm $model
         * @param array $getData data from $_GET
         */
        public static function setPrimaryModelForListViewMerge($model, $getData)
        {
            assert('$model instanceof ModelsListDuplicateMergedModelForm');
            assert('is_array($getData)');
            $modelsList   = $model->selectedModels;
            if (isset($getData['primaryModelId']))
            {
                $model->primaryModel = $modelsList[$getData['primaryModelId']];
            }
            else
            {
                $models = array_values($modelsList);
                if (!empty($models))
                {
                    $model->primaryModel = $models[0];
                }
            }
        }

        /**
         * Gets selected models for merge
         * @param string $modelClassName
         * @param array $getData data from $_GET
         * @return array
         */
        public static function getSelectedModelsListForMerge($modelClassName, $getData)
        {
            assert('is_string($modelClassName)');
            assert('is_array($getData)');
            $modelsList = array();
            if (isset($getData['selectedIds']) && $getData['selectedIds'] != null)
            {
                $selectedIds = explode(',', $getData['selectedIds']); // Not Coding Standard
                foreach ($selectedIds as $id)
                {
                    $model = $modelClassName::getById(intval($id));
                    $modelsList[$id] = $model;
                }
            }
            return $modelsList;
        }

        /**
         * Processes copying relations from non primary models to primary model and than deleting them
         * @param RedBeanModel $primaryModel
         * @param array $getData data from $_GET
         */
        public static function processCopyRelationsAndDeleteNonPrimaryModelsInMerge($primaryModel, $getData)
        {
            assert('$primaryModel instanceof RedBeanModel');
            assert('is_array($getData)');
            $modelClassName     = get_class($primaryModel);
            $selectedModelsList = self::getSelectedModelsListForMerge($modelClassName, $getData);
            self::processAssignRelationsToMergedModelFromModelsToBeDeleted($selectedModelsList, $primaryModel);
            foreach ($selectedModelsList as $selectedModel)
            {
                if ($selectedModel->id != $primaryModel->id
                    &&(get_class($selectedModel) == get_class($primaryModel)))
                {
                    ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($selectedModel);
                    $selectedModel->delete();
                }
            }
        }

        /**
         * Process assignment of relations.
         * @param array $selectedModelsList
         * @param RedBeanModel $primaryModel
         */
        protected static function processAssignRelationsToMergedModelFromModelsToBeDeleted($selectedModelsList, $primaryModel)
        {
            assert('is_array($selectedModelsList)');
            foreach ($selectedModelsList as $selectedModel)
            {
                if ($selectedModel->getClassId('Item') != $primaryModel->getClassId('Item') &&
                        (get_class($selectedModel) == get_class($primaryModel)))
                {
                    self::processNonDerivedRelationsAssignment($primaryModel, $selectedModel);
                    self::processDerivedRelationsAssignment($primaryModel, $selectedModel);
                    if ($primaryModel instanceof Account || $primaryModel instanceof Contact)
                    {
                        self::processCopyEmailActivity($primaryModel, $selectedModel);
                    }
                }
            }
            $primaryModel->save();
        }

        /**
         * Process non derived relations assignment
         * @param RedBeanModel $primaryModel
         * @param RedBeanModel $selectedModel
         */
        protected static function processNonDerivedRelationsAssignment($primaryModel, $selectedModel)
        {
            assert('$primaryModel instanceof RedBeanModel');
            assert('$selectedModel instanceof RedBeanModel');
            $modelClassName = get_class($primaryModel);
            foreach ($selectedModel->attributeNames() as $attribute)
            {
                if ($attribute == 'owner')
                {
                        continue;
                }
                if ($modelClassName::isRelation($attribute) &&
                                !$modelClassName::isOwnedRelation($attribute) &&
                                    !$primaryModel->isAttributeReadOnly($attribute))
                {
                    //Has one
                    if ($modelClassName::isRelationTypeAHasOneVariant($attribute))
                    {
                        $primaryModel->$attribute = $selectedModel->$attribute;
                    }
                    //Has many || Many many
                    if (($modelClassName::isRelationTypeAHasManyVariant($attribute) ||
                        $modelClassName::isRelationTypeAManyManyVariant($attribute)) &&
                        ($modelClassName::getRelationType($attribute) != RedBeanModel::HAS_MANY_BELONGS_TO)
                      )
                    {
                        foreach ($selectedModel->$attribute as $offset => $relatedModel)
                        {
                            if (!$primaryModel->$attribute->contains($relatedModel))
                            {
                                $primaryModel->$attribute->add($relatedModel);
                            }
                        }
                    }
                }
            }
        }

        /**
         * Process derived relations assignment
         * @param RedBeanModel $primaryModel
         * @param RedBeanModel $selectedModel
         */
        protected static function processDerivedRelationsAssignment($primaryModel, $selectedModel)
        {
            assert('$primaryModel instanceof RedBeanModel');
            assert('$selectedModel instanceof RedBeanModel');
            $metadata   = $selectedModel->getMetadata();
            foreach ($metadata as $modelClassName => $modelClassMetadata)
            {
                if (isset($metadata[$modelClassName]["derivedRelationsViaCastedUpModel"]))
                {
                    foreach ($metadata[$modelClassName]["derivedRelationsViaCastedUpModel"] as $relation => $derivedRelationData)
                    {
                        $opposingModelClassName = $derivedRelationData[1];
                        $opposingRelation       = $derivedRelationData[2];
                        if ($opposingRelation == 'activityItems' &&
                                    is_subclass_of($opposingModelClassName, 'Activity'))
                        {
                            $opposingModels = $opposingModelClassName::getByActivityItemsCastedDown($selectedModel->getClassId('Item'));
                            if ($opposingModels != null)
                            {
                                foreach ($opposingModels as $opposingModel)
                                {
                                    $opposingModel->activityItems->add($primaryModel);
                                    $opposingModel->save();
                                }
                            }
                        }
                    }
                }
            }
        }

        /**
         * Resolves form layout metadata for one column display
         * @param array $metadata
         * @return array
         */
        public static function resolveFormLayoutMetadataForOneColumnDisplay($metadata)
        {
            $modifiedElementsData = array();
            foreach ($metadata['global']['panels'] as $panel)
            {
                foreach ($panel['rows'] as $row)
                {
                    foreach ($row['cells'] as $cell)
                    {
                        foreach ($cell['elements'] as $elementData)
                        {
                            if ($elementData['attributeName'] == 'null' && !class_exists($elementData['type'] . 'Element'))
                            {
                                continue;
                            }
                            elseif ($elementData['type'] == 'TitleFullName')
                            {
                                $modifiedElementsData[] = array('attributeName' => 'title', 'type' => 'DropDown', 'addBlank' => true);
                                $modifiedElementsData[] = array('attributeName' => 'firstName', 'type' => 'Text');
                                $modifiedElementsData[] = array('attributeName' => 'lastName', 'type' => 'Text');
                            }
                            else
                            {
                                $modifiedElementsData[] = $elementData;
                            }
                        }
                    }
                }
            }
            //Prepare panels data
            $panelsData = array();
            foreach ($modifiedElementsData as $row => $elementData)
            {
                $panelsData[0]['rows'][$row]['cells'][0]['elements'][0] = $elementData;
            }
            $metadata['global']['panels'] = $panelsData;
            return $metadata;
        }

        /**
         * Process copy email activity.
         *
         * @param RedBeanModel $primaryModel
         * @param RedBeanModel $selectedModel
         */
        public static function processCopyEmailActivity($primaryModel, $selectedModel)
        {
            $searchAttributesData = LatestActivitiesUtil::
                                        getSearchAttributesDataByModelClassNamesAndRelatedItemIds(array('EmailMessage'),
                                                                                                  array($selectedModel->getClassId('Item')),
                                                                                                  LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL);

            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('EmailMessage');
            $where               = RedBeanModelDataProvider::makeWhere('EmailMessage', $searchAttributesData[0]['EmailMessage'], $joinTablesAdapter);
            $models              = EmailMessage::getSubset($joinTablesAdapter, null, null, $where, null);
            if (isset($searchAttributesData[1]['EmailMessage']))
            {
                $where  = RedBeanModelDataProvider::makeWhere('EmailMessage', $searchAttributesData[1]['EmailMessage'], $joinTablesAdapter);
                $models = array_merge($models, EmailMessage::getSubset($joinTablesAdapter, null, null, $where, null));
            }
            foreach ($models as $model)
            {
                //Resolve sender
                if ($model->sender->personsOrAccounts->contains($selectedModel))
                {
                    $model->sender->personsOrAccounts->remove($selectedModel);
                    if (!$model->sender->personsOrAccounts->contains($primaryModel))
                    {
                        $model->sender->personsOrAccounts->add($primaryModel);
                    }
                }
                //recipients
                foreach ($model->recipients as $key => $unused)
                {
                    if ($model->recipients[$key]->personsOrAccounts->contains($selectedModel))
                    {
                        $model->recipients[$key]->personsOrAccounts->remove($selectedModel);
                        if (!$model->recipients[$key]->personsOrAccounts->contains($primaryModel))
                        {
                            $model->recipients[$key]->personsOrAccounts->add($primaryModel);
                        }
                    }
                }
                $model->save();
            }
        }
    }
?>