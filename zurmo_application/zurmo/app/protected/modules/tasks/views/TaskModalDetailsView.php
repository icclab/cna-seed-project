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
     * Modal window for viewing a task
     */
    class TaskModalDetailsView extends SecuredDetailsView
    {
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'  => 'TaskModalEditFromModalDetailsLink'),
                            array('type'  => 'TaskModalCloneFromModalDetailsLink'),
                            array('type'  => 'TaskAuditEventsModalListLink'),
                            array('type'  => 'TaskDeleteLink',
                                  'sourceViewId' => 'eval:$this->getSourceViewId()'),
                        ),
                    ),
                    'derivedAttributeTypes' => array(
                        'TasksForModalActivityItems',
                        'DerivedExplicitReadWriteModelPermissions',
                    ),
                    'nonPlaceableAttributeNames' => array(
                        'latestDateTime',
                    ),
                    'panelsDisplayType' => FormLayout::PANELS_DISPLAY_TYPE_FIRST,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                            array('attributeName' => null, 'type' => 'Null'), // Not Coding Standard
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
                                                array('attributeName' => 'id', 'type' => 'Text'),
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
                                                array('attributeName' => 'project', 'type' => 'ProjectForTask'),
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
         * Gets form id for the right side form
         * @return string
         */
        protected static function getRightSideFormId()
        {
            return 'task-right-column-form-data';
        }

        /**
         * Gets form id for the left side form
         * @return string
         */
        protected static function getLeftSideFormId()
        {
            return 'task-left-column-form-data';
        }

        /**
         * Gets title
         * @return string
         */
        public function getTitle()
        {
            return $this->model->name;
        }

        /**
         * Renders content for a view including a layout title, form toolbar,
         * and form layout.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $content      = $this->resolveAndRenderActionElementMenu();
            $content     .= '<div class="details-table clearfix">'; //todo: we should probably call this something else?
            $content     .= $this->renderLeftSideContent();
            $content     .= $this->renderRightSideContent();
            $content     .= '</div>';
            $content     .= $this->renderAfterDetailsTable();
            return $content;
        }

        protected function renderLeftSideContent()
        {
            $content  = $this->renderLeftSideTopContent();
            $content .= $this->renderLeftSideBottomContent();
            return ZurmoHtml::tag('div', array('class' => 'left-column'), $content);
        }

        protected function renderLeftSideTopContent()
        {
            $content    = null;
            $content   .= '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                'ZurmoActiveForm',
                array_merge(
                    array('id'    => static::getLeftSideFormId(),
                          'action' => '#'
                    ),
                    $this->resolveActiveFormAjaxValidationOptions()
                )
            );
            $content .= $formStart;
            $nameElement = new TextElement($this->getModel(), 'name', $form);
            $nameElement->editableTemplate = '{content}{error}';
            $content .= $nameElement->render();
            $descriptionElement = new TextAreaElement($this->getModel(), 'description', $form, array('rows' => 2));
            $content .= $descriptionElement->render();
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= $this->renderModalContainer();
            $content .= $this->renderAuditTrailModalContainer();
            $content .= '</div>';
            return ZurmoHtml::tag('div', array('class' => 'left-side-edit-view-panel'), $content);
        }

        /**
         * Need to define validationUrl in order to ensure the task id is populated. If it is a new task, then
         * the task id would not be in the GET
         * @return array
         */
        protected function resolveActiveFormAjaxValidationOptions()
        {
            $relationModelId  = Yii::app()->request->getParam('relationModelId');
            $action           = TasksUtil::resolveModalSaveActionNameForByRelationModelId($relationModelId);
            $validationUrl    = Yii::app()->createUrl('tasks/default/' . $action,
                                array_merge(GetUtil::getData(), array('id' => $this->getModel()->id)));
            return array('enableAjaxValidation' => true,
                'clientOptions' => array(
                    'validateOnSubmit'  => true,
                    'validateOnChange'  => true,
                    'validationUrl' => $validationUrl
                ),
            );
        }

        protected function renderLeftSideBottomContent()
        {
            $content  = $this->renderTaskCheckListItemsListContent();
            $content .= $this->renderTaskCommentsContent();
            return $content;
        }

        /**
         * Renders right side content
         * @param string $form
         * @return string
         */
        protected function renderRightSideContent($form = null)
        {
            $content  = $this->renderRightSideTopContent();
            $content .= $this->renderRightBottomSideContent();
            $content  = ZurmoHtml::tag('div', array('class' => 'right-side-edit-view-panel'), $content);
            $content  = ZurmoHtml::tag('div', array('class' => 'right-column'), $content);
            return $content;
        }

        protected function renderRightSideTopContent()
        {
            $content    = null;
            $content   .= ZurmoHtml::openTag('div', array('class' => 'wide form'));
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                'ZurmoActiveForm',
                array_merge(
                    array('id' => static::getRightSideFormId()),
                    $this->resolveActiveFormAjaxValidationOptions()
                )
            );
            $content .= $formStart;
            $content .= $this->renderStatusContent($form);
            $content .= $this->renderOwnerContent($form);
            $content .= $this->renderRequestedByUserContent($form);
            $content .= $this->renderDueDateTimeContent($form);
            $content .= $this->renderNotificationSubscribersContent();
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= ZurmoHtml::closeTag('div');
            return $content;
        }

        protected function renderRightBottomSideContent()
        {
            return ZurmoHtml::tag('div', array('class' => 'right-side-details-view-panel'), $this->renderFormLayout());
        }

        /**
         * Renders check items list
         * @return string
         */
        protected function renderTaskCheckListItemsListContent()
        {
            $checkItemsListElement = new TaskCheckListItemsListElement($this->getModel(), 'null');
            return $checkItemsListElement->render();
        }

        /**
         * Renders task comments
         * @return string
         */
        protected function renderTaskCommentsContent()
        {
            $commentsElement = new TaskCommentsElement($this->getModel(), 'null', null, array('moduleId' => 'tasks'));
            return $commentsElement->render();
        }

        /**
         * Renders owner box
         * @param string $form
         * @return string
         */
        protected function renderOwnerContent($form)
        {
            $element  = new TaskModalUserElement($this->getModel(), 'owner', $form);
            $element->editableTemplate = '<div class="owner-box">{label}{content}{error}</div>';
            return $element->render();
        }

        /**
         * Renders requested by user box
         * @param string $form
         * @return string
         */
        protected function renderRequestedByUserContent($form)
        {
            $element  = new TaskModalUserElement($this->getModel(), 'requestedByUser', $form);
            $element->editableTemplate = '<div class="owner-box">{label}{content}{error}</div>';
            return $element->render();
        }

        /**
         * Renders due date time
         * @param string $form
         * @return string
         */
        protected function renderDueDateTimeContent($form)
        {
            $element  = new DateTimeElement($this->getModel(), 'dueDateTime', $form);
            $element->editableTemplate = '{label}{content}{error}';
            return $element->render();
        }

        /**
         * Renders notification subscribers
         * @param string $form
         * @return string
         */
        protected function renderNotificationSubscribersContent()
        {
            $task = Task::getById($this->model->id);
            $content = '<div id="task-subscriber-box">';
            $content .= ZurmoHtml::tag('h4', array(), Zurmo::t('TasksModule', 'Who is receiving notifications'));
            $content .= '<div id="subscriberList" class="clearfix">';
            if ($task->notificationSubscribers->count() > 0)
            {
                $content .= TasksUtil::getTaskSubscriberData($task);
            }
            $content .= TasksUtil::getDetailSubscriptionLink($task, 0);
            $content .= '</div>';
            $content .= '</div>';
            TasksUtil::registerSubscriptionScript($this->model->id);
            TasksUtil::registerUnsubscriptionScript($this->model->id);
            return $content;
        }

        /**
         * Resolves Subscribe Url
         * @return string
         */
        protected function resolveSubscribeUrl()
        {
            return Yii::app()->createUrl('tasks/default/addSubscriber', array('id' => $this->model->id));
        }

        /**
         * Resolve subscriber ajax options
         * @return array
         */
        protected function resolveSubscriberAjaxOptions()
        {
            return array(
                'type'     => 'GET',
                'dataType' => 'html',
                'data'     => array(),
                'success'  => 'function(data)
                              {
                                $("#subscribe-task-link").hide();
                                $("#subscriberList").replaceWith(data);
                              }'
            );
        }

        /**
         * Renders owner box
         * @param string $form
         * @return string
         */
        protected function renderStatusContent($form)
        {
            $content  = '<div id="status-box">';
            $element  = new TaskStatusDropDownElement($this->getModel(), 'status', $form);
            $content .= $element->render();
            $content .= '<span id="completionDate">';
            if ($this->model->status == Task::STATUS_COMPLETED)
            {
                $content .= TasksUtil::renderCompletionDateTime($this->model);;
            }
            $content .= '</span>';
            $content .= '</div>';
            return $content;
        }

        public static function getDesignerRulesType()
        {
            return 'TaskModalDetailsView';
        }

        protected function getSourceViewId()
        {
            $getData = GetUtil::getData();
            return ArrayUtil::getArrayValue($getData, 'sourceKanbanBoardId');
        }

        protected function renderModalContainer()
        {
            return ZurmoHtml::tag('div', array(
                'id' => ModelElement::MODAL_CONTAINER_PREFIX . '-' . $this->getRightSideFormId()
            ), '');
        }

        /**
         * Override to change the nonEdditableTemplate to place the label above the input.
         * @see DetailsView::resolveElementDuringFormLayoutRender()
         */
        protected function resolveElementDuringFormLayoutRender(& $element)
        {
            if (get_class($element) == 'NullElement')
            {
                $element->nonEditableTemplate = '';
            }
            elseif ($element->getAttribute() == 'id')
            {
                $element->nonEditableTemplate = '<td colspan="{colspan}"><label>{label}</label><strong>{content}</strong></td>';
            }
            else
            {
                $element->nonEditableTemplate = '<td colspan="{colspan}">{label}<strong>{content}</strong></td>';
            }
        }

        /**
         * @return bool|true
         */
        protected function doesLabelHaveOwnCell()
        {
            return false;
        }

        /**
         * @return string
         */
        protected function renderAuditTrailModalContainer()
        {
            return ZurmoHtml::tag('div', array('id' => 'AuditEventsModalContainer'), '');
        }

        /**
         * Gets the options menu class
         * @return string
         */
        protected static function getOptionsMenuCssClass()
        {
            return 'task-modal-details-options-menu';
        }

        protected function getFormLayoutUniqueId()
        {
            return 'task-details-view-form';
        }
    }
?>