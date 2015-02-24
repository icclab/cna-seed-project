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
     * Displays the standard boolean field
     * rendered as a check box.
     */
    class TaskCheckListItemsListElement extends Element implements DerivedElementInterface
    {
        /**
         * Render A standard text input.
         * @return The element's content as a string.
         */
        protected function renderControlEditable()
        {
            if ($this->getDisabledValue())
            {
                $htmlOptions             = array();
                $htmlOptions['id']       = $this->getEditableInputId();
                $htmlOptions['disabled'] = 'disabled';
                return ZurmoHtml::checkBox($this->getEditableInputName(), $this->model->{$this->attribute}, $htmlOptions);
            }
        }

        /**
         * Renders the attribute from the model.
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            $content  = $this->getFormattedAttributeLabel();
            $content .= $this->renderTaskCheckListItems();
            $content .= $this->renderTaskCreateCheckItem();
            $content  = ZurmoHtml::tag('div', array('class' => 'check-list'), $content);
            return $content;
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element. For this element, there are no attributes from the model.
         * @return array - empty
         */
        public static function getModelAttributeNames()
        {
            return array();
        }

        /**
         * @return string
         */
        protected function renderLabel()
        {
            return null;
        }

        /**
         * Gets formatted attribute label
         * @return string
         */
        protected function getFormattedAttributeLabel()
        {
            return '<h3>' . Zurmo::t('TasksModule', 'Check List') . '</h3>';
        }

        /**
         * Renders task create check item
         * @return string
         */
        protected function renderTaskCreateCheckItem()
        {
            $content            = null;
            $taskCheckListItem  = new TaskCheckListItem();
            $uniquePageId       = 'TaskCheckItemInlineEditForModelView';
            $redirectUrl        = Yii::app()->createUrl('/tasks/taskCheckItems/inlineCreateTaskCheckItemFromAjax',
                                                    array('id' => $this->model->id,
                                                          'uniquePageId' => $uniquePageId));
            $urlParameters      = array('relatedModelId'           => $this->model->id,
                                        'relatedModelClassName'    => 'Task',
                                        'relatedModelRelationName' => 'checkListItems',
                                        'redirectUrl'              => $redirectUrl); //After save, the url to go to.

            $inlineView         = new TaskCheckItemInlineEditView($taskCheckListItem, 'taskCheckItems', 'tasks',
                                      'inlineCreateTaskCheckItemSave', $urlParameters, $uniquePageId);
            $content            .= $inlineView->render();
            $htmlOptions = array('id' => 'TaskCheckItemInlineEditForModelView', 'class' => 'add-task-input');
            return ZurmoHtml::tag('div', $htmlOptions, $content);
        }

        /**
         * Renders task check list items
         * @return string
         */
        protected function renderTaskCheckListItems()
        {
            $getParams      = array('relatedModelId'         => $this->model->id,
                                  'relatedModelClassName'    => get_class($this->model),
                                  'relatedModelRelationName' => 'checkListItems');
            $taskCheckListItem = TaskCheckListItem::getByTask($this->model->id);
            $view              = new TaskCheckListItemsForTaskView('taskCheckItems', 'tasks', $taskCheckListItem,
                                 $this->model, $this->form, $getParams);
            $content           = $view->render();
            return $content;
        }
    }
?>
