<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/
     /**
      * Renders content before the element.
      */
    class ModelAttributeElementPreContentView extends ZurmoWidget
    {
        /**
         * Models selected for merge.
         * @var array
         */
        public $selectedModels;

        /**
         * Attributes associated to the element. This would include multiple attributes in case
         * of derived ones.
         * @var array
         */
        public $attributes;

        /**
         * Primary model associated to the merged item.
         * @var RedBeanModel
         */
        public $primaryModel;

        /**
         * Element associated to the merged item.
         * @var string
         */
        public $element;

        /**
         * @var ModelAttributeAndElementDataToMergeItem
         */
        public $modelAttributeAndElementDataToMergeItemClass;

        /**
         * Runs the widget
         */
        public function run()
        {
            $attributes = $this->attributes;
            $content = null;
            if ($this->modelAttributeAndElementDataToMergeItemClass == null)
            {
                $modelAttributeAndElementDataToMergeItemClass = 'ModelAttributeAndElementDataToMergeItem';
            }
            else
            {
                $modelAttributeAndElementDataToMergeItemClass = $this->modelAttributeAndElementDataToMergeItemClass;
            }
            foreach ($attributes as $attribute)
            {
                $attributeContent = null;
                $position = 1;
                foreach ($this->selectedModels as $model)
                {
                    $modelAttributeAndElementDataToMergeItem = new $modelAttributeAndElementDataToMergeItemClass(
                                                                $model, $attribute, $this->element, $this->primaryModel, $position++);

                    $attributeContent .= $modelAttributeAndElementDataToMergeItem->getAttributeRenderedContent();
                }
                $content .= ZurmoHtml::tag('div', array('class' => 'hasPossibles'), $attributeContent);
            }
            Yii::app()->clientScript->registerScript('preContentSelectScript', $this->registerScriptForAttributeReplacement());
            echo $content;
        }

        /**
         * Registers script for attribute replacement
         * @return string
         */
        protected function registerScriptForAttributeReplacement()
        {
            // Begin Not Coding Standard
            $script = "$('.attributePreElementContent').click(function(){
                                                                $('#' + $(this).data('id')).val($(this).data('value'));
                                                                $('#' + $(this).data('id')).focus();
                                                                $(this).siblings('a').removeClass('selected');
                                                                $(this).addClass('selected');
                                                                return false;
                                                            });";

            $script .= "$('.attributePreElementContentModelElement').click(function(){
                                                                $('#' + $(this).data('id')).val($(this).data('value'));
                                                                $('#' + $(this).data('hiddenid')).val($(this).data('hiddenvalue'));
                                                                $(this).siblings('a').removeClass('selected');
                                                                $(this).addClass('selected');
                                                                return false;
                                                            });";
            // End Not Coding Standard
            return $script;
        }
    }
?>