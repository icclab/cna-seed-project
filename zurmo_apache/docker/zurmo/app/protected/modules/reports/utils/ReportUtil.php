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
     * Helper class for working with Report objects
     */
    class ReportUtil
    {
        /**
         * @param $type
         * @return null | string
         */
        public static function renderNonEditableTypeStringContent($type)
        {
            assert('is_string($type)');
            $typesAndLabels = Report::getTypeDropDownArray();
            if (isset($typesAndLabels[$type]))
            {
                return $typesAndLabels[$type];
            }
        }

        /**
         * @param $moduleClassName
         * @return null | string
         */
        public static function renderNonEditableModuleStringContent($moduleClassName)
        {
            assert('is_string($moduleClassName)');
            $modulesAndLabels = Report::getReportableModulesAndLabelsForCurrentUser();
            if (isset($modulesAndLabels[$moduleClassName]))
            {
                return $modulesAndLabels[$moduleClassName];
            }
        }

        /**
         * @param array $attributesData
         * @return array
         */
        public static function makeDataAndLabelsForSeriesOrRange(Array $attributesData)
        {
            $dataAndLabels = array();
            foreach ($attributesData as $attribute => $data)
            {
                $dataAndLabels[$attribute] = $data['label'];
            }
            return $dataAndLabels;
        }

        /**
         * Validates report wizard form against the post data
         * @param type $postData
         * @param ReportWizardForm $model
         * @throws NotSupportedException
         */
        public static function validateReportWizardForm($postData, ReportWizardForm $model)
        {
            if (isset($postData['validationScenario']) && $postData['validationScenario'] != null)
            {
                $model->setScenario($postData['validationScenario']);
            }
            else
            {
                throw new NotSupportedException();
            }
            $model->validate();
            $errorData = array();
            foreach ($model->getErrors() as $attribute => $errors)
            {
                $errorData[ZurmoHtml::activeId($model, $attribute)] = $errors;
            }
            return $errorData;
        }

        /**
         * Process attribute addition from tree and than renders it.
         *
         * @param string $nodeId
         * @param string $treeType
         * @param Report $report
         * @param int $rowNumber
         * @param bool $trackableStructurePosition
         */
        public static function processAttributeAdditionFromTree($nodeId, $treeType, Report $report, $rowNumber, $trackableStructurePosition)
        {
            $nodeIdWithoutTreeType              = ReportRelationsAndAttributesToTreeAdapter::
                                                     removeTreeTypeFromNodeId($nodeId, $treeType);
            $moduleClassName                    = $report->getModuleClassName();
            $modelClassName                     = $moduleClassName::getPrimaryModelName();
            $form                               = new WizardActiveForm();
            $form->id                           = 'edit-form';
            $form->enableAjaxValidation         = true; //ensures error validation populates correctly

            $wizardFormClassName                = ReportToWizardFormAdapter::getFormClassNameByType($report->getType());
            $model                              = ComponentForReportFormFactory::makeByComponentType($moduleClassName,
                                                      $modelClassName, $report->getType(), $treeType);
            $form->modelClassNameForError       = $wizardFormClassName;
            $attribute                          = ReportRelationsAndAttributesToTreeAdapter::
                                                      resolveAttributeByNodeId($nodeIdWithoutTreeType);
            $model->attributeIndexOrDerivedType = ReportRelationsAndAttributesToTreeAdapter::
                                                      resolveAttributeByNodeId($nodeIdWithoutTreeType);
            $inputPrefixData                    = ReportRelationsAndAttributesToTreeAdapter::
                                                      resolveInputPrefixData($wizardFormClassName,
                                                      $treeType, (int)$rowNumber);
            $adapter                            = new ReportAttributeToElementAdapter($inputPrefixData, $model,
                                                      $form, $treeType);
            $view                               = new AttributeRowForReportComponentView($adapter,
                                                      (int)$rowNumber, $inputPrefixData, $attribute,
                                                      (bool)$trackableStructurePosition, true, $treeType);
            $content                            = $view->render();
            $form->renderAddAttributeErrorSettingsScript($view::getFormId());
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }
    }
?>