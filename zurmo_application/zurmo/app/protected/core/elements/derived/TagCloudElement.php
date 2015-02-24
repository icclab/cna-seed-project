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

    class TagCloudElement extends MultiSelectRelatedModelsAutoCompleteElement
    {
        protected $dataAndLabels = null;

        //Not used, we override getEditableInputId and getEditableInputName
        protected function getFormName()
        {
            return null;
        }

        protected function assertModelType()
        {
            assert('$this->model->{$this->attribute} instanceof MultipleValuesCustomField');
        }

        protected function getWidgetSourceUrl()
        {
            return Yii::app()->createUrl('zurmo/default/autoCompleteCustomFieldData/',
                                            array('name' => $this->model->{$this->attribute}->data->name));
        }

        protected function getUnqualifiedIdForIdField()
        {
            return '_' . $this->attribute . '_values';
        }

        protected function getUnqualifiedNameForIdField()
        {
            return '[' . $this->attribute . '][values]';
        }

        protected function getWidgetHintText()
        {
            return Zurmo::t('Core', 'Type to find a tag');
        }

        protected function getRelationName()
        {
            return null; // we override getRelatedRecords instead.
        }

        protected function getRelatedRecords()
        {
            $multipleValuesCustomField = $this->model->{$this->attribute};
            $relatedRecords     = $multipleValuesCustomField->values;
            return $relatedRecords;
        }

        protected function resolveIdAndNameByModel(RedBeanModel $customFieldValue)
        {
            if (!isset($this->dataAndLabels))
            {
                $multipleValuesCustomField  = $this->model->{$this->attribute};
                $this->dataAndLabels        = CustomFieldDataUtil::getDataIndexedByDataAndTranslatedLabelsByLanguage(
                                                                $multipleValuesCustomField->data, Yii::app()->language);
            }
            if ($customFieldValue->value != null)
            {
                return array('id'   => $customFieldValue->value,
                             'name' => $this->dataAndLabels[$customFieldValue->value]);
            }
        }

        protected function getFormattedAttributeLabel()
        {
            return Yii::app()->format->text($this->model->getAttributeLabel($this->attribute));
        }

        protected function getEditableInputId($attributeName = null, $relationAttributeName = null)
        {
            $inputPrefix = $this->resolveInputIdPrefix();
            return $inputPrefix . $this->getUnqualifiedIdForIdField();
        }

        protected function getEditableInputName($attributeName = null, $relationAttributeName = null)
        {
            $inputPrefix = $this->resolveInputNamePrefix();
            return $inputPrefix . $this->getUnqualifiedNameForIdField();
        }
    }
?>