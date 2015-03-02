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
     * Extends the KanbanBoardExtendedGridView to provide a 'stacked' Kanban Board format for viewing lists of data.
     */
    class TaskKanbanBoardExtendedGridView extends KanbanBoardExtendedGridView
    {
        public $relatedModelId;

        public $relatedModelClassName;

        public $columnsData;

        /**
         * Heals sortOrder for kanbanItems if they are wrong.  It can be wrong if tasks are created from workflow actions
         * because during that task creation, it doesn't know what project or other activityItem it is part of.
         * This will at least heal the sortOrder for display. Then upon subsequent saves of the board, it will properly
         * set the sortOrder in the database
         * @return array
         */
        protected function resolveDataIntoKanbanColumns()
        {
            $this->makeColumnsDataAndStructure();
            $kanbanItemsArray           = array();
            $kanbanItems                = array();
            $maximumKanbanItemSortOrder = 0;
            foreach ($this->dataProvider->getData() as $notUsed => $task)
            {
                $kanbanItem  = KanbanItem::getByTask($task->id);
                if ($kanbanItem == null)
                {
                    //Create KanbanItem here
                    $kanbanItem = TasksUtil::createKanbanItemFromTask($task);
                }
                $kanbanItems[] = $kanbanItem;
                if($kanbanItem->sortOrder > $maximumKanbanItemSortOrder)
                {
                    $maximumKanbanItemSortOrder = $kanbanItem->sortOrder;
                }
            }
            foreach ($kanbanItems as $kanbanItem)
            {
                if(isset($kanbanItemsArray[$kanbanItem->type]) &&
                   isset($kanbanItemsArray[$kanbanItem->type][intval($kanbanItem->sortOrder)]))
                {
                    $sortOrder                  = $maximumKanbanItemSortOrder + 1;
                    $maximumKanbanItemSortOrder = $sortOrder;
                }
                else
                {
                    $sortOrder = intval($kanbanItem->sortOrder);
                }
                $kanbanItemsArray[$kanbanItem->type][$sortOrder] = $kanbanItem->task;
            }
            foreach ($kanbanItemsArray as $type => $kanbanData)
            {
                ksort($kanbanData, SORT_NUMERIC);
                foreach ($kanbanData as $sort => $item)
                {
                    if (isset($this->columnsData[$type]))
                    {
                        $this->columnsData[$type][] = $item;
                    }
                }
            }
            $this->registerKanbanColumnScripts();
            return $this->columnsData;
        }

        /**
         * Resolve order by type
         * @param array $columnsData
         * @param int $type
         * @return int
         */
        protected function resolveOrderByType($columnsData, $type)
        {
            if (isset($columnsData[$type]))
            {
                return count($columnsData[$type]) + 1;
            }
            return 1;
        }

        /**
         * @return array
         */
        protected function makeColumnsDataAndStructure()
        {
            $columnsData = array();
            foreach ($this->groupByAttributeVisibleValues as $value)
            {
                $columnsData[$value] = array();
            }
            $this->columnsData = $columnsData;
        }

        /**
         * Creates ul tag for kanban column
         * @param array $listItems
         * @param string $attributeValue
         * @return string
         */
        protected function renderUlTagForKanbanColumn($listItems, $attributeValue = null)
        {
            return ZurmoHtml::tag('ul id="task-sortable-rows-' . $attributeValue . '" class="connectedSortable"' ,
                                  array(), $listItems);
        }

        /**
         * Override script registration
         */
        protected function registerScripts()
        {

        }

        /**
         * Register Kanban Column Scripts
         */
        protected function registerKanbanColumnScripts()
        {
            Yii::app()->clientScript->registerScript('task-sortable-data', static::registerKanbanColumnSortableScript());
            $url = Yii::app()->createUrl('tasks/default/updateStatusInKanbanView', array());
            $this->registerKanbanColumnStartActionScript('action-type-start', Zurmo::t('Core', 'Finish'), Task::STATUS_IN_PROGRESS, $url);
            $this->registerKanbanColumnStartActionScript('action-type-restart', Zurmo::t('Core', 'Finish'), Task::STATUS_IN_PROGRESS, $url);
            $this->registerKanbanColumnFinishActionScript(Zurmo::t('Core', 'Accept'),
                        Zurmo::t('Core', 'Reject'), Task::STATUS_AWAITING_ACCEPTANCE, $url);
            $this->registerKanbanColumnAcceptActionScript('', Task::STATUS_COMPLETED, $url);
            $this->registerKanbanColumnRejectActionScript(Zurmo::t('Core', 'Restart'), Task::STATUS_REJECTED, $url);
            TasksUtil::registerSubscriptionScript();
            TasksUtil::registerUnsubscriptionScript();
        }

        /**
         * Registers kanban column sortable script. Also called to use on refresh of kanban board
         * @return string
         */
        public static function registerKanbanColumnSortableScript()
        {
            $url = Yii::app()->createUrl('tasks/default/updateStatusOnDragInKanbanView');
            return "setUpTaskKanbanSortable('{$url}');";
        }

        /**
         * Registers kanban column start action script
         * @param string $label
         * @param int $targetStatus
         * @param string $url
         */
        protected function registerKanbanColumnStartActionScript($sourceButtonClass, $label, $targetStatus, $url)
        {
            $script = $this->registerButtonActionScript($sourceButtonClass, KanbanItem::TYPE_IN_PROGRESS,
                                                        $label, 'action-type-finish', $url, $targetStatus);
            Yii::app()->clientScript->registerScript($sourceButtonClass . '-action-script', $script);
        }

        /**
         * Registers kanban column finish action script
         * @param string $label
         * @param int $targetStatus
         * @param string $url
         */
        protected function registerKanbanColumnFinishActionScript($labelAccept, $labelReject, $targetStatus, $url)
        {
            $acceptanceStatusLabel = ZurmoHtml::encode(Task::getStatusDisplayName(Task::STATUS_AWAITING_ACCEPTANCE));
            $acceptanceStatus      = Task::STATUS_AWAITING_ACCEPTANCE;
            $inProgressKanbanType  = KanbanItem::TYPE_IN_PROGRESS;
            // Begin Not Coding Standard
            $script = "$(document).on('click','.action-type-finish',function()
                            {
                                var element = $(this).parent().parent().parent().parent();
                                var ulelement = $(element).parent();
                                var id = $(element).attr('id');
                                var idParts = id.split('_');
                                var taskId = parseInt(idParts[1]);
                                var rejectLinkElement = $(this).clone();
                                var parent = $(this).parent();
                                $(this).find('.button-label').html('" . $labelAccept . "');
                                $(this).removeClass('action-type-finish').addClass('action-type-accept');
                                $(rejectLinkElement).appendTo($(parent));
                                $(rejectLinkElement).find('.button-label').html('" . $labelReject . "');
                                $(rejectLinkElement).removeClass('action-type-finish').addClass('action-type-reject');
                                $(element).find('.task-status').html('{$acceptanceStatusLabel}');
                                $.ajax(
                                    {
                                        type : 'GET',
                                        data : {'targetStatus':'{$acceptanceStatus}', 'taskId':taskId, 'sourceKanbanType':'{$inProgressKanbanType}'},
                                        url  : '" . $url . "',
                                        beforeSend : function(){
                                          $('.ui-overlay-block').fadeIn(50);
                                          $(this).makeLargeLoadingSpinner(true, '.ui-overlay-block'); //- add spinner to block anything else
                                        },
                                        success: function(data){
                                            $(this).makeLargeLoadingSpinner(false, '.ui-overlay-block');
                                            $('.ui-overlay-block').fadeOut(50);
                                         }
                                    }
                                );
                            }
                        );";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('finish-action-script', $script);
        }

        /**
         * @return string
         */
        protected function getRowClassForTaskKanbanColumn($data)
        {
            if ($data->status == Task::STATUS_COMPLETED)
            {
                return 'kanban-card item-to-place ui-state-disabled';
            }
            else
            {
                return 'kanban-card item-to-place';
            }
        }

        /**
         * Creates task item for kanban column
         * @param array $data
         * @param int $row
         * @return string
         */
        protected function createTaskItemForKanbanColumn($data, $row)
        {
            return ZurmoHtml::tag('li', array('class' => $this->getRowClassForTaskKanbanColumn($data),
                                              'id' => 'items_' . $data->id),
                                              ZurmoHtml::tag('div', array('class' => 'clearfix'),
                                                  $this->renderTaskCardDetailsContent($data, $row)));
        }

        /**
         * Get list items by attribute value and data
         * @param array $attributeValueAndData
         * @return array
         */
        protected function getListItemsByAttributeValueAndData($attributeValueAndData)
        {
            $listItems = '';
            foreach ($attributeValueAndData as $key => $data)
            {
                $listItems .= $this->createTaskItemForKanbanColumn($data, $key + 1);
            }

            return $listItems;
        }

        /**
         * Register button action script
         * @param string $sourceActionButtonClass
         * @param int $targetKanbanItemType
         * @param string $label
         * @param string $targetButtonClass
         * @param string $url
         * @param int $targetStatus
         * @return string
         */
        protected function registerButtonActionScript($sourceActionButtonClass, $targetKanbanItemType, $label,
                                                      $targetButtonClass, $url, $targetStatus)
        {
            $rejectStatusLabel       = Task::getStatusDisplayName(Task::STATUS_REJECTED);
            $inProgressStatusLabel   = Task::getStatusDisplayName(Task::STATUS_IN_PROGRESS);
            $completedStatusLabel    = Task::getStatusDisplayName(Task::STATUS_COMPLETED);
            $completedStatus         = Task::STATUS_COMPLETED;
            $rejectedStatusClass     = 'status-' . Task::STATUS_REJECTED;
            $currentUserLoggedInName = '(' . Yii::app()->user->userModel->getFullName() . ')';
            // Begin Not Coding Standard
            return "$(document).on('click','." . $sourceActionButtonClass . "',
                        function()
                        {
                            var element = $(this).parent().parent().parent().parent();
                            var ulelement = $(element).parent();
                            var id = $(element).attr('id');
                            var ulid = $(ulelement).attr('id');
                            var ulidParts = ulid.split('-');
                            var idParts = id.split('_');
                            var taskId = parseInt(idParts[1]);
                            var columnType = parseInt(ulidParts[3]);
                            if(parseInt('{$targetKanbanItemType}') != columnType)
                            {
                                $('#task-sortable-rows-{$targetKanbanItemType}').append(element);
                                $('#task-sortable-rows-' + columnType).remove('#' + id);
                            }
                            if('{$targetStatus}' != '{$completedStatus}')
                            {
                                var linkTag = $(element).find('.{$sourceActionButtonClass}');
                                $(linkTag).find('.button-label').html('" . $label . "');
                                $(linkTag).removeClass('" . $sourceActionButtonClass . "').addClass('" . $targetButtonClass . "');
                                if('{$sourceActionButtonClass}' == 'action-type-reject')
                                {
                                    $(element).find('.action-type-accept').remove();
                                    $(element).find('.task-status').html('{$rejectStatusLabel}');
                                    $(element).find('.task-status').parent().addClass('{$rejectedStatusClass}');
                                }
                                else
                                {
                                    $(element).find('.task-status').parent().removeClass('{$rejectedStatusClass}');
                                }
                                if('{$sourceActionButtonClass}' == 'action-type-restart')
                                {
                                    $(element).find('.task-status').html('{$inProgressStatusLabel}');
                                }
                                if('{$sourceActionButtonClass}' == 'action-type-start')
                                {
                                    $(element).find('h4 .task-owner').html('{$currentUserLoggedInName}');
                                }
                            }
                            else
                            {
                                $(element).find('.button-label').remove();
                                $(element).find('.task-action-toolbar').remove();
                                $(element).addClass('ui-state-disabled');
                                $(element).find('.task-status').html('{$completedStatusLabel}');
                            }
                            $.ajax(
                            {
                                type : 'GET',
                                data : {'targetStatus':'{$targetStatus}', 'taskId':taskId, 'sourceKanbanType':columnType},
                                url  : '{$url}',
                                dataType : 'json',
                                beforeSend : function(){
                                          $('.ui-overlay-block').fadeIn(50);
                                          $(this).makeLargeLoadingSpinner(true, '.ui-overlay-block'); //- add spinner to block anything else
                                        },
                                success: function(data){
                                            $(element).find('.task-subscribers').html(data.subscriptionContent);
                                            $(this).makeLargeLoadingSpinner(false, '.ui-overlay-block');
                                            $('.ui-overlay-block').fadeOut(50);
                                         }
                            }
                            );
                        }
                    );";
            // End Not Coding Standard
        }

        /**
         * Register kanban column accept action script
         * @param string $label
         * @param int $targetStatus
         * @param string $url
         */
        protected function registerKanbanColumnAcceptActionScript($label, $targetStatus, $url)
        {
            $script = $this->registerButtonActionScript('action-type-accept', KanbanItem::TYPE_COMPLETED,
                      $label, 'task-complete-action ui-state-disabled', $url, $targetStatus);
            Yii::app()->clientScript->registerScript('accept-action-script', $script);
        }

        /**
         * Register kanban column reject action script
         * @param string $label
         * @param int $targetStatus
         * @param string $url
         */
        protected function registerKanbanColumnRejectActionScript($label, $targetStatus, $url)
        {
            $script = $this->registerButtonActionScript('action-type-reject', KanbanItem::TYPE_IN_PROGRESS,
                      $label, 'action-type-restart', $url, $targetStatus);
            Yii::app()->clientScript->registerScript('reject-action-script', $script);
        }

        /**
         * @param Task $task
         * @param $row
         * @return string
         */
        protected function renderTaskCardDetailsContent(Task $task, $row)
        {
            $statusClass = 'status-' . $task->status;

            $content  = $this->renderCardDataContent($this->cardColumns['completionBar'], $task, $row);
            $content .= ZurmoHtml::openTag('div', array('class' => 'task-details clearfix ' . $statusClass));
            $content .= ZurmoHtml::tag('span', array('class' => 'task-status'), Task::getStatusDisplayName($task->status));
            $content .= $this->resolveAndRenderTaskCardDetailsDueDateContent($task);
            $content .= ZurmoHtml::closeTag('div');

            $content .= ZurmoHtml::openTag('div', array('class' => 'task-content clearfix'));
            $content .= $this->resolveAndRenderTaskCardDetailsStatusContent($task, $row);
            $content .= Yii::app()->custom->renderExtraAttributesWithNameInKanbanCard($this->cardColumns, $task, $row);
            $content .= ZurmoHtml::openTag('h4');
            $content .= $this->renderCardDataContent($this->cardColumns['name'], $task, $row);
            $content .= ZurmoHtml::closeTag('h4');
            if ($task->description != null)
            {
                $description = $task->description;
                if (strlen($description) > TaskKanbanBoard::TASK_DESCRIPTION_LENGTH)
                {
                    $description = substr($description, 0, TaskKanbanBoard::TASK_DESCRIPTION_LENGTH) . '...';
                }
                $content .= ZurmoHtml::tag('p', array(), $description);
            }
            $content .= ZurmoHtml::closeTag('div');

            $content .= ZurmoHtml::openTag('div', array('class' => 'task-subscribers'));
            $content .= TasksUtil::resolveAndRenderTaskCardDetailsSubscribersContent($task);
            $content .= $this->renderCardDataContent($this->cardColumns['subscribe'], $task, $row);
            $content .= ZurmoHtml::closeTag('div');

            return $content;
        }

        protected function resolveAndRenderTaskCardDetailsDueDateContent(Task $task)
        {
            if ($task->dueDateTime != null)
            {
                $content = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                           $task->dueDateTime, DateTimeUtil::DATETIME_FORMAT_DATE_WIDTH, null);
                return ZurmoHtml::tag('span', array('class' => 'task-due-date'), $content);
            }
        }

        protected function resolveAndRenderTaskCardDetailsStatusContent(Task $task, $row)
        {
            $statusContent = $this->renderCardDataContent($this->cardColumns['status'], $task, $row);
            if ($statusContent != null)
            {
                $content  = ZurmoHtml::openTag('div', array('class' => 'task-action-toolbar pillbox'));
                $content .= $this->renderCardDataContent($this->cardColumns['status'], $task, $row);
                $content .= ZurmoHtml::closeTag('div');
                return $content;
            }
        }

        /**
         * Checks if max count has to be validated in the kanban view
         * @return boolean
         */
        protected function isMaxCountCheckRequired()
        {
            return false;
        }
    }
?>
