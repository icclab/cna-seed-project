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
     * Modal window for creating and editing a task
     */
    class TaskModalEditView extends SecuredEditView
    {
        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'        => 'SaveButton'),
                            array('type'        => 'ModalCancelLink',
                                  'htmlOptions' => 'eval:static::resolveHtmlOptionsForCancel()'
                            )
                        ),
                    ),
                    'derivedAttributeTypes' => array(
                        'TasksForModalActivityItems',
                        'DerivedExplicitReadWriteModelPermissions',
                    ),
                    'nonPlaceableAttributeNames' => array(
                        'latestDateTime',
                        'completed',
                        'completedDateTime'
                    ),
                    'panelsDisplayType' => FormLayout::PANELS_DISPLAY_TYPE_FIRST,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'name', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'description', 'type' => 'TextArea'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'status', 'type' => 'TaskStatusDropDown'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'requestedByUser', 'type' => 'User'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'owner', 'type' => 'User'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'dueDateTime', 'type' => 'DateTime'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'TasksForModalActivityItems'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'project', 'type' => 'Project'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null',
                                                      'type' => 'DerivedExplicitReadWriteModelPermissions'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

         /**
          * @return string
          */
         protected function getNewModelTitleLabel()
         {
             return null;
         }

        /**
         * @return string
         */
        protected static function getFormId()
        {
            return 'task-modal-edit-form';
        }

        /**
         * @return array
         */
        protected static function resolveHtmlOptionsForCancel()
        {
            return array(
                'onclick' => '$("#ModalView").parent().dialog("close");'
            );
        }

        protected function resolveModalIdFromGet()
        {
            $modalId             = Yii::app()->request->getParam('modalId');
            if ($modalId == null)
            {
                $modalId = TasksUtil::getModalContainerId();
            }
            return $modalId;
        }

        /**
         * Resolves ajax validation option for save button
         * @return array
         */
        protected function resolveActiveFormAjaxValidationOptions()
        {
            //Would be used from kanban board
            $sourceKanbanBoardId = Yii::app()->request->getParam('sourceKanbanBoardId');

            //Would be used from other source
            $sourceId         = Yii::app()->request->getParam('sourceId');
            $modalId          = $this->resolveModalIdFromGet();
            $relationModelId  = Yii::app()->request->getParam('relationModelId');
            $copyAction       = Yii::app()->request->getParam('action', null);
            $action           = TasksUtil::resolveModalSaveActionNameForByRelationModelId($relationModelId, $copyAction);
            $url              = Yii::app()->createUrl('tasks/default/' . $action, GetUtil::getData());
            // Begin Not Coding Standard
            return array('enableAjaxValidation' => true,
                        'clientOptions' => array(
                            'beforeValidate'    => 'js:$(this).beforeValidateAction',
                            'afterValidate'     => 'js:function(form, data, hasError){
                                if(hasError)
                                {
                                    form.find(".attachLoading:first").removeClass("loading");
                                    form.find(".attachLoading:first").removeClass("loading-ajax-submit");
                                }
                                else
                                {
                                ' . $this->renderConfigSaveAjax($this->getFormId(), $url, $sourceKanbanBoardId, $modalId, $sourceId) . '
                                }
                                return false;
                            }',
                            'validateOnSubmit'  => true,
                            'validateOnChange'  => false,
                            'inputContainer'    => 'td'
                        )
            );
            // End Not Coding Standard
        }

        protected function renderConfigSaveAjax($formId, $url, $sourceKanbanBoardId, $modalId, $sourceId)
        {
            // Begin Not Coding Standard
            if ($sourceId == null)
            {
                $kanbanRefreshScript = TasksUtil::resolveExtraCloseScriptForModalAjaxOptions($sourceKanbanBoardId);
            }
            else
            {
                $kanbanRefreshScript = TasksUtil::resolveExtraCloseScriptForModalAjaxOptions($sourceId);
            }
            $title   = TasksUtil::getModalDetailsTitle();
            // Begin Not Coding Standard
            $options = array(
                'type' => 'POST',
                'data' => 'js:$("#' . $formId . '").serialize()',
                'url'  =>  $url,
                'update' => '#' . $modalId,
                'complete' => "function(XMLHttpRequest, textStatus){
                                    $('#" . $modalId .  "').dialog('option', 'title', '" . $title . "');
                                    " . $kanbanRefreshScript . "}"
            );
            // End Not Coding Standard
            return ZurmoHtml::ajax($options);
        }

        protected function renderRightSideFormLayoutForEdit($form)
        {
            return null;
        }

        public static function getDesignerRulesType()
        {
            return 'TaskModalEditView';
        }

        /**
         * Override set the description row size
         */
        protected function resolveElementInformationDuringFormLayoutRender(& $elementInformation)
        {
            parent::resolveElementInformationDuringFormLayoutRender($elementInformation);
            if ($elementInformation['attributeName'] == 'description')
            {
                $elementInformation['rows'] = 2;
            }
        }

        /**
         * Gets form layout unique id
         * @return null
         */
        protected function getFormLayoutUniqueId()
        {
            return 'task-modal-edit-form-layout';
        }
    }
?>