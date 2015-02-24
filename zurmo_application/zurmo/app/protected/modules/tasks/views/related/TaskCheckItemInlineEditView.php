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
     * An inline edit view for a task check item.
     *
     */
    class TaskCheckItemInlineEditView extends InlineEditView
    {
        /**
         * @return string
         */
        public function getFormName()
        {
            return "task-check-item-inline-edit-form";
        }

        /**
         * @return string
         */
        protected static function getFormId()
        {
            return "task-check-item-inline-edit-form";
        }

        public function renderFormLayout($form = null)
        {
            $nameElement = new TextElement($this->getModel(), 'name', $form);
            $nameElement->editableTemplate = '{content}{error}';
            $taskInput = ZurmoHtml::tag('div', array('class' => 'task-input'), $nameElement->render());
            $params = array('label' => Zurmo::t('TasksModule', 'Add'));
            $element  = new SaveButtonActionElement($this->controllerId, $this->moduleId, $this->modelId, $params);
            $addButton = ZurmoHtml::tag('div', array('class' => 'task-add-button'), $element->render());
            return $taskInput . $addButton;
        }

        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = array();
            return $metadata;
        }

        /**
         * Override to change the editableTemplate to place the label above the input.
         * @see DetailsView::resolveElementDuringFormLayoutRender()
         */
        protected function resolveElementDuringFormLayoutRender(& $element)
        {
            $element->editableTemplate = '<td colspan="{colspan}">{content}{error}</td>';
        }

        /**
         * Override to allow the comment thread, if it exists to be refreshed.
         * (non-PHPdoc)
         * @see InlineEditView::renderConfigSaveAjax()
         */
        protected function renderConfigSaveAjax($formName)
        {
            // Begin Not Coding Standard
            return ZurmoHtml::ajax(array(
                    'type' => 'POST',
                    'data' => 'js:$("#' . $formName . '").serialize()',
                    'url'  =>  $this->getValidateAndSaveUrl(),
                    'update' => '#' . $this->uniquePageId,
                    'complete' => "function(XMLHttpRequest, textStatus){
                        //find if there is a check list item thread to refresh
                        $('.hiddenCheckListItemRefresh').click();}"
                ));
            // End Not Coding Standard
        }

        protected function doesLabelHaveOwnCell()
        {
            return false;
        }

        /**
         * Renders action element content
         * @param string $content
         * @return string
         */
        protected function renderActionElementContent($actionElementContent)
        {
            $content = $actionElementContent;
            return $content;
        }

        /**
         * Render form start html
         * @return string
         */
        protected function renderFormStartHtml()
        {
            return '';
        }

        /**
         * Render form end html
         * @return null
         */
        protected function renderFormEndHtml()
        {
            return null;
        }

        /**
         * Render modal container
         * @return null
         */
        protected function renderModalContainer()
        {
            return null;
        }

        public static function getDesignerRulesType()
        {
            return null;
        }

        protected function renderContentStartFormDiv()
        {
            return null;
        }

        protected function renderContentEndFormDiv()
        {
            return null;
        }

        protected function wrapFormLayoutContent($content)
        {
            return $content;
        }
    }
?>
