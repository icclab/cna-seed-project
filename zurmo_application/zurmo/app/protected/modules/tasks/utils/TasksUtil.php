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
     * Helper class for working with tasks
     */
    class TasksUtil
    {
        /**
         * Get task subscriber data
         * @param Task $task
         * @return string
         */
        public static function getTaskSubscriberData(Task $task)
        {
            $content = null;
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('User');
            $alreadySubscribedUsers = array();
            foreach ($task->notificationSubscribers as $subscriber)
            {
                $user     = $subscriber->person->castDown(array($modelDerivationPathToItem));
                //Take care of duplicates if any
                if (!in_array($user->id, $alreadySubscribedUsers))
                {
                    $content .= static::renderSubscriberImageAndLinkContent($user);
                    $alreadySubscribedUsers[] = $user->id;
                }
            }

            return $content;
        }

        /**
         * Renders subscriber image and link content
         * @param User $user
         * @param int $imageSize
         * @param string $class
         * @return string
         */
        public static function renderSubscriberImageAndLinkContent(User $user, $imageSize = 36, $class = null)
        {
            assert('is_int($imageSize)');
            assert('is_string($class) || $class === null');
            $htmlOptions = array('title' => strval($user));
            if ($class != null)
            {
                $htmlOptions['class'] = $class;
            }
            $userUrl     = Yii::app()->createUrl('/users/default/details', array('id' => $user->id));
            return ZurmoHtml::link($user->getAvatarImage($imageSize), $userUrl, $htmlOptions);
        }

        /**
         * Gets task participant
         * @param Task $task
         * @return array
         */
        public static function getTaskSubscribers(Task $task)
        {
            $subscribers = array();
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('User');
            foreach ($task->notificationSubscribers as $subscriber)
            {
                $subscribers[] = $subscriber->person->castDown(array($modelDerivationPathToItem));
            }
            return $subscribers;
        }

        /**
         * Gets url to task detail view
         * @param Task $model
         * @return string
         */
        public static function getUrlToEmail($model)
        {
            assert('$model instanceof Task');
            return Yii::app()->createAbsoluteUrl('tasks/default/details/', array('id' => $model->id));
        }

        /**
         * Resolve explicit permissions of the requested by user for the task
         * @param Task $task
         * @param Permitable $origRequestedByUser
         * @param Permitable $requestedByUser
         * @param ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions
         */
        public static function resolveExplicitPermissionsForRequestedByUser(Task $task, Permitable $origRequestedByUser, Permitable $requestedByUser, ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions)
        {
            $explicitReadWriteModelPermissions->addReadWritePermitableToRemove($origRequestedByUser);
            $explicitReadWriteModelPermissions->addReadWritePermitable($requestedByUser);
            ExplicitReadWriteModelPermissionsUtil::
                                        resolveExplicitReadWriteModelPermissions($task, $explicitReadWriteModelPermissions);
        }

        /**
         * Given a task and a user, mark that the user has read or not read the latest changes as a task
         * owner, requested by user or subscriber
         * @param Task $task
         * @param User $user
         * @param Boolean $hasReadLatest
         */
        public static function markUserHasReadLatest(Task $task, User $user, $hasReadLatest = true)
        {
            assert('$task->id > 0');
            assert('$user->id > 0');
            assert('is_bool($hasReadLatest)');
            $save = false;
            foreach ($task->notificationSubscribers as $position => $subscriber)
            {
                if ($subscriber->person->getClassId('Item') ==
                                            $user->getClassId('Item') && $subscriber->hasReadLatest != $hasReadLatest)
                {
                    $task->notificationSubscribers[$position]->hasReadLatest = $hasReadLatest;
                    $save                                                    = true;
                }
            }

            if ($save)
            {
                $task->save();
            }
        }

        /**
         * @return string
         */
        public static function getModalDetailsTitle()
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            $title = Zurmo::t('TasksModule', 'Collaborate On This TasksModuleSingularLabel', $params);
            return $title;
        }

        /**
         * @return string
         */
        public static function getModalEditTitle()
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            $title = Zurmo::t('TasksModule', 'Edit TasksModuleSingularLabel', $params);
            return $title;
        }

        /**
         * Gets modal title for create task modal window
         * @param string $renderType
         * @return string
         */
        public static function getModalTitleForCreateTask($renderType = "Create")
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            if ($renderType == "Create")
            {
                $title = Zurmo::t('TasksModule', 'Create TasksModuleSingularLabel', $params);
            }
            elseif ($renderType == "Copy")
            {
                $title = Zurmo::t('TasksModule', 'Copy TasksModuleSingularLabel', $params);
            }
            elseif ($renderType == "Details")
            {
                $title = static::getModalDetailsTitle();
            }
            else
            {
                $title = Zurmo::t('TasksModule', 'Edit TasksModuleSingularLabel', $params);
            }
            return $title;
        }

        /**
         * Resolves ajax options for create link
         * @return array
         */
        public static function resolveAjaxOptionsForCreateMenuItem()
        {
            return static::resolveAjaxOptionsForModalView('Create');
        }

        /**
         * @return string
         */
        public static function getModalContainerId()
        {
            return ModalContainerView::ID;
        }

        /**
         * @param $renderType
         * @param string|null $sourceKanbanBoardId
         * @return array
         */
        public static function resolveAjaxOptionsForModalView($renderType, $sourceKanbanBoardId = null)
        {
            assert('is_string($renderType)');
            $title = self::getModalTitleForCreateTask($renderType);
            return   ModalView::getAjaxOptionsForModalLink($title, self::getModalContainerId(), 'auto', 600,
                     'center top+25', $class = "'task-dialog'", // Not Coding Standard
                     static::resolveExtraCloseScriptForModalAjaxOptions($sourceKanbanBoardId));
        }

        public static function resolveExtraCloseScriptForModalAjaxOptions($sourceId = null)
        {
            assert('is_string($sourceId) || $sourceId == null');
            if ($sourceId != null)
            {
                return "$('#{$sourceId}').yiiGridView('update');";
            }
        }

        /**
         * Get link for going to the task modal detail view
         * @param Task $task
         * @param $controllerId
         * @param $moduleId
         * @param $moduleClassName
         * @return null|string
         */
        public static function getModalDetailsLink(Task $task,
                                                   $controllerId,
                                                   $moduleId,
                                                   $moduleClassName,
                                                   $isOwnerRequiredInDisplay = true)
        {
            assert('is_string($controllerId) || $controllerId === null');
            assert('is_string($moduleId)  || $moduleId === null');
            assert('is_string($moduleClassName)');
            if ($isOwnerRequiredInDisplay)
            {
                $label       = $task->name . ZurmoHtml::tag('span', array('class' => 'task-owner'), '(' . strval($task->owner) . ')');
            }
            else
            {
                $label       = $task->name;
            }
            $params      = array('label' => $label, 'routeModuleId' => 'tasks',
                                 'wrapLabel' => false,
                                 'htmlOptions' => array('id' => 'task-' . $task->id)
                                );
            $goToDetailsFromRelatedModalLinkActionElement = new GoToDetailsFromRelatedModalLinkActionElement(
                                                                    $controllerId, $moduleId, $task->id, $params);
            $linkContent = $goToDetailsFromRelatedModalLinkActionElement->render();
            $string      = TaskActionSecurityUtil::resolveViewLinkToModelForCurrentUser($task, $moduleClassName, $linkContent);
            return $string;
        }

        /**
         * Resolve action button for task by status
         * @param string $statusId
         * @param string $controllerId
         * @param string $moduleId
         * @param string $taskId
         * @return string
         */
        public static function resolveActionButtonForTaskByStatus($statusId, $controllerId, $moduleId, $taskId)
        {
            assert('is_string($statusId) || is_int($statusId)');
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_int($taskId)');
            $type = self::resolveKanbanItemTypeForTaskStatus(intval($statusId));
            $route = Yii::app()->createUrl('tasks/default/updateStatusInKanbanView');
            switch(intval($statusId))
            {
                case Task::STATUS_NEW:
                     $element = new TaskStartLinkActionElement($controllerId, $moduleId, $taskId,
                                                                                            array('route' => $route));
                    break;
                case Task::STATUS_IN_PROGRESS:

                     $element = new TaskFinishLinkActionElement($controllerId, $moduleId, $taskId,
                                                                                            array('route' => $route));
                    break;
                case Task::STATUS_REJECTED:

                     $element = new TaskRestartLinkActionElement($controllerId, $moduleId, $taskId,
                                                                                            array('route' => $route));
                    break;
                case Task::STATUS_AWAITING_ACCEPTANCE:

                     $acceptLinkElement = new TaskAcceptLinkActionElement($controllerId, $moduleId, $taskId,
                                                                                            array('route' => $route));
                     $rejectLinkElement = new TaskRejectLinkActionElement($controllerId, $moduleId, $taskId,
                                                                                            array('route' => $route));
                     return $acceptLinkElement->render() . $rejectLinkElement->render();
                case Task::STATUS_COMPLETED:
                     return null;
                default:
                     $element = new TaskStartLinkActionElement($controllerId, $moduleId, $taskId,
                                                                                            array('route' => $route));
                    break;
            }
            return $element->render();
        }

        /**
         * Maps task status to kanban item type
         * @return array
         */
        public static function getTaskStatusMappingToKanbanItemTypeArray()
        {
            return array(
                            Task::STATUS_NEW                   => KanbanItem::TYPE_SOMEDAY,
                            Task::STATUS_IN_PROGRESS           => KanbanItem::TYPE_IN_PROGRESS,
                            Task::STATUS_AWAITING_ACCEPTANCE   => KanbanItem::TYPE_IN_PROGRESS,
                            Task::STATUS_REJECTED              => KanbanItem::TYPE_IN_PROGRESS,
                            Task::STATUS_COMPLETED             => KanbanItem::TYPE_COMPLETED
                        );
        }

        /**
         * Resolve kanban item type for task status
         * @param string $status
         * @return int
         */
        public static function resolveKanbanItemTypeForTaskStatus($status)
        {
            if ($status == null)
            {
                return KanbanItem::TYPE_SOMEDAY;
            }
            $data = self::getTaskStatusMappingToKanbanItemTypeArray();
            return $data[intval($status)];
        }

        /**
         * Some task status's are ok for multiple kanban item types.
         * @param $kanbanItemType
         * @param $taskStatus
         * @return true if the task status is ok for the current kanbanItemType
         */
        public static function isKanbanItemTypeValidBasedOnTaskStatus($kanbanItemType, $taskStatus)
        {
            if($taskStatus == null && $kanbanItemType == KanbanItem::TYPE_SOMEDAY)
            {
                return true;
            }
            elseif($taskStatus == null)
            {
                return false;
            }
            if ($taskStatus == Task::STATUS_NEW)
            {
                if($kanbanItemType == KanbanItem::TYPE_SOMEDAY || $kanbanItemType == KanbanItem::TYPE_TODO)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            $data = self::getTaskStatusMappingToKanbanItemTypeArray();
            if($data[intval($taskStatus)] == $kanbanItemType)
            {
                return true;
            }
            return false;
        }

        /**
         * Resolves Subscribe Url
         * @param int $taskId
         * @return string
         */
        public static function resolveSubscribeUrl($taskId)
        {
            return Yii::app()->createUrl('tasks/default/addSubscriber', array('id' => $taskId));
        }

        /**
         * Resolve subscriber ajax options
         * @return array
         */
        public static function resolveSubscriberAjaxOptions()
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
         * Register subscription script
         * @param int $taskId
         */
        public static function registerSubscriptionScript($taskId = null)
        {
            $title  = Zurmo::t('Core', 'Unsubscribe');
            $unsubscribeLink = ZurmoHtml::tag('i', array('class' => 'icon-unsubscribe', 'title' => $title), '');

            if ($taskId == null)
            {
                $url     = Yii::app()->createUrl('tasks/default/addKanbanSubscriber');
                $script  = self::getKanbanSubscriptionScript($url, 'subscribe-task-link', 'unsubscribe-task-link', $unsubscribeLink);
                Yii::app()->clientScript->registerScript('kanban-subscribe-task-link-script', $script);
            }
            else
            {
                $url     = Yii::app()->createUrl('tasks/default/addSubscriber', array('id' => $taskId));
                $script  = self::getDetailSubscriptionScript($url, 'detail-subscribe-task-link', 'detail-unsubscribe-task-link', $unsubscribeLink, $taskId);
                Yii::app()->clientScript->registerScript('detail-subscribe-task-link-script', $script);
            }
        }

        /**
         * Register unsubscription script
         * @param int $taskId
         */
        public static function registerUnsubscriptionScript($taskId = null)
        {
            $title  = Zurmo::t('Core', 'Subscribe');
            $subscribeLink = ZurmoHtml::tag('i', array('class' => 'icon-subscribe', 'title' => $title), '');

            if ($taskId == null)
            {
                $url    = Yii::app()->createUrl('tasks/default/removeKanbanSubscriber');
                $script = self::getKanbanSubscriptionScript($url, 'unsubscribe-task-link', 'subscribe-task-link', $subscribeLink);
                Yii::app()->clientScript->registerScript('kanban-unsubscribe-task-link-script', $script);
            }
            else
            {
                $url    = Yii::app()->createUrl('tasks/default/removeSubscriber', array('id' => $taskId));
                $script = self::getDetailSubscriptionScript($url, 'detail-unsubscribe-task-link', 'detail-subscribe-task-link', $subscribeLink, $taskId);
                Yii::app()->clientScript->registerScript('detail-unsubscribe-task-link-script', $script);
            }
        }

        /**
         * Get subscription script
         * @param string $url
         * @param string $sourceClass
         * @param string $targetClass
         * @param string $link
         * @return string
         */
        public static function getKanbanSubscriptionScript($url, $sourceClass, $targetClass, $link)
        {
            // Begin Not Coding Standard
            return "$('body').on('click', '." . $sourceClass . "', function()
                                                    {
                                                        var element     = $(this).parent().parent().parent();
                                                        var id          = $(element).attr('id');
                                                        var idParts     = id.split('_');
                                                        var taskId      = parseInt(idParts[1]);
                                                        var linkParent  = $(this).parent();
                                                        console.log(linkParent);
                                                        $.ajax(
                                                        {
                                                            type : 'GET',
                                                            data : {'id':taskId},
                                                            url  : '" . $url . "',
                                                            beforeSend : function(){
                                                              $('.ui-overlay-block').fadeIn(50);
                                                              $(this).makeLargeLoadingSpinner(true, '.ui-overlay-block');
                                                            },
                                                            success : function(data)
                                                                      {
                                                                        $(linkParent).html(data);
                                                                        $(this).makeLargeLoadingSpinner(false, '.ui-overlay-block');
                                                                        $('.ui-overlay-block').fadeOut(100);
                                                                      }
                                                        }
                                                        );
                                                    }
                                                );";
            // End Not Coding Standard
        }

        /**
         * Get subscription script
         * @param string $url
         * @param string $sourceClass
         * @param string $targetClass
         * @param string $link
         * @return string
         */
        public static function getDetailSubscriptionScript($url, $sourceClass, $targetClass, $link, $taskId)
        {
            // Begin Not Coding Standard
            return "$('body').on('click', '." . $sourceClass . "', function()
                                                    {
                                                        $.ajax(
                                                        {
                                                            type : 'GET',
                                                            url  : '" . $url . "',
                                                            beforeSend : function(){
                                                              $('#subscriberList').html('');
                                                              $(this).makeLargeLoadingSpinner(true, '#subscriberList');
                                                            },
                                                            success : function(data)
                                                                      {
                                                                        $(this).html('" . $link . "');
                                                                        $(this).attr('class', '" . $targetClass . "');
                                                                        if (data == '')
                                                                        {
                                                                            $('#subscriberList').html('');
                                                                        }
                                                                        else
                                                                        {
                                                                            $('#subscriberList').html(data);
                                                                        }
                                                                        $(this).makeLargeLoadingSpinner(false, '#subscriberList');
                                                                      }
                                                        }
                                                        );
                                                    }
                                                );";
            // End Not Coding Standard
        }

        /**
         * Get kanban subscription link for the task. This would be in kanban view for a related model
         * for e.g Project
         * @param Task $task
         * @param int $row
         * @return string
         */
        public static function getKanbanSubscriptionLink(Task $task, $row)
        {
            return self::resolveSubscriptionLink($task, 'subscribe-task-link', 'unsubscribe-task-link');
        }

        /**
         * Get subscription link on the task detail view
         * @param Task $task
         * @param int $row
         * @return string
         */
        public static function getDetailSubscriptionLink(Task $task, $row)
        {
            return self::resolveSubscriptionLink($task, 'detail-subscribe-task-link', 'detail-unsubscribe-task-link');
        }

        /**
         * Resolve subscription link for detail and kanban view
         * @param Task $task
         * @param string $subscribeLinkClass
         * @param string $unsubscribeLinkClass
         * @return string
         */
        public static function resolveSubscriptionLink(Task $task, $subscribeLinkClass, $unsubscribeLinkClass)
        {
            assert('is_string($subscribeLinkClass)');
            assert('is_string($unsubscribeLinkClass)');
            if ($task->owner->id == Yii::app()->user->userModel->id ||
                            $task->requestedByUser->id == Yii::app()->user->userModel->id)
            {
                return null;
            }
            if ($task->doNotificationSubscribersContainPerson(Yii::app()->user->userModel) === false)
            {
                $label       = Zurmo::t('Core', 'Subscribe');
                $class       = $subscribeLinkClass;
                $iconContent = ZurmoHtml::tag('i', array('class' => 'icon-subscribe'), '');
            }
            else
            {
                $label       = Zurmo::t('Core', 'Unsubscribe');
                $class       = $unsubscribeLinkClass;
                $iconContent = ZurmoHtml::tag('i', array('class' => 'icon-unsubscribe'), '');
            }
            return ZurmoHtml::link($iconContent, '#', array('class' => $class, 'title' => $label)) ;
        }

        /**
         * Get task completion percentage
         * @param int $id
         * @return float
         */
        public static function getTaskCompletionPercentage(Task $task)
        {
            $checkListItemsCount = count($task->checkListItems);
            if ($checkListItemsCount == 0)
            {
                return 0;
            }
            else
            {
                $completedItemsCount = self::getTaskCompletedCheckListItems($task);
            }
            $completionPercent = ($completedItemsCount/$checkListItemsCount) * 100;
            return $completionPercent;
        }

        /**
         * Maps task status to kanban item type
         * @return array
         */
        public static function getKanbanItemTypeToDefaultTaskStatusMappingArray()
        {
            return array(
                            KanbanItem::TYPE_TODO                   => Task::STATUS_NEW,
                            KanbanItem::TYPE_SOMEDAY                => Task::STATUS_NEW,
                            KanbanItem::TYPE_IN_PROGRESS            => Task::STATUS_IN_PROGRESS,
                            KanbanItem::TYPE_COMPLETED              => Task::STATUS_COMPLETED
                        );
        }

        /**
         * Gets default task status for kanban item type
         * @param int $kanbanItemType
         */
        public static function getDefaultTaskStatusForKanbanItemType($kanbanItemType)
        {
            assert('is_int(intval($kanbanItemType))');
            $mappingArray = self::getKanbanItemTypeToDefaultTaskStatusMappingArray();
            return $mappingArray[intval($kanbanItemType)];
        }

        /**
         * Saves the kanban item from task
         * @param type array
         */
        public static function createKanbanItemFromTask(Task $task)
        {
            $kanbanItem                     = new KanbanItem();
            $kanbanItem->type               = TasksUtil::resolveKanbanItemTypeForTaskStatus($task->status);
            $kanbanItem->task               = $task;
            if ($task->project->id > 0)
            {
                $kanbanItem->kanbanRelatedItem  = $task->project;
            }
            else
            {
                $kanbanItem->kanbanRelatedItem  = $task->activityItems->offsetGet(0);
            }
            $sortOrder = self::resolveAndGetSortOrderForTaskOnKanbanBoard($kanbanItem->type, $task);
            $kanbanItem->sortOrder          = $sortOrder;
            $kanbanItem->save();
            return $kanbanItem;
        }

        /**
         * Render completion progress bar
         * @param Task $task
         * @return string
         */
        public static function renderCompletionProgressBarContent(Task $task)
        {
            $checkListItemsCount = count($task->checkListItems);
            if ( $checkListItemsCount == 0)
            {
                return null;
            }
            $percentageComplete = ceil(static::getTaskCompletionPercentage($task));
            return ZurmoHtml::tag('div', array('class' => 'completion-percentage-bar', 'style' => 'width:' . $percentageComplete . '%'),
                                  $percentageComplete . '%');
        }

        /**
         * Get task completed check list items
         * @param Task $task
         * @return int
         */
        public static function getTaskCompletedCheckListItems(Task $task)
        {
            $completedItemsCount = 0;
            foreach ($task->checkListItems as $checkListItem)
            {
                    if ((bool)$checkListItem->completed)
                    {
                        $completedItemsCount++;
                    }
            }
            return $completedItemsCount;
        }

        /**
         * Resolve task kanban view for relation
         * @param RedBeanModel $model
         * @param string $moduleId
         * @param ZurmoModuleController $controller
         * @param TasksForRelatedKanbanView $kanbanView
         * @param ZurmoDefaultPageView $pageView
         * @return ZurmoDefaultPageView
         */
        public static function resolveTaskKanbanViewForRelation($model,
                                                                $moduleId, $controller,
                                                                $kanbanView, $pageView)
        {
            assert('$model instanceof RedBeanModel');
            assert('is_string($moduleId)');
            assert('$controller instanceof ZurmoModuleController');
            assert('is_string($kanbanView)');
            assert('is_string($pageView)');
            $breadCrumbLinks = array(StringUtil::getChoppedStringContent(strval($model), 25));
            $kanbanItem                 = new KanbanItem();
            $kanbanBoard                = new TaskKanbanBoard($kanbanItem, 'type', $model, get_class($model));
            $kanbanBoard->setIsActive();
            $params['relationModel']    = $model;
            $params['relationModuleId'] = $moduleId;
            $params['redirectUrl']      = null;
            $listView                   = new $kanbanView($controller->getId(), 'tasks', 'Task', null,
                                                            $params, null, array(), $kanbanBoard);
            $view                       = new $pageView(ZurmoDefaultViewUtil::
                                                             makeViewWithBreadcrumbsForCurrentUser(
                                                                    $controller, $listView, $breadCrumbLinks, 'KanbanBoardBreadCrumbView'));
            return $view;
        }

        /**
         * Register script for task detail link. This would be called from both kanban and open task portlet
         * @param string $sourceId
         */
        public static function registerTaskModalDetailsScript($sourceId)
        {
            assert('is_string($sourceId)');
            $modalId = TasksUtil::getModalContainerId();
            $url = Yii::app()->createUrl('tasks/default/modalDetails');
            $ajaxOptions = TasksUtil::resolveAjaxOptionsForModalView('Details', $sourceId);
            $ajaxOptions['beforeSend'] = new CJavaScriptExpression($ajaxOptions['beforeSend']);
            $script = " $(document).off('click.taskDetailLink', '#{$sourceId} .task-kanban-detail-link');
                        $(document).on('click.taskDetailLink',  '#{$sourceId} .task-kanban-detail-link', function()
                        {
                            var id = $(this).attr('id');
                            var idParts = id.split('-');
                            var taskId = parseInt(idParts[1]);
                            $.ajax(
                            {
                                'type' : 'GET',
                                'url'  : '{$url}' + '?id=' + taskId,
                                'beforeSend' : {$ajaxOptions['beforeSend']},
                                'update'     : '{$ajaxOptions['update']}',
                                'success': function(html){jQuery('#{$modalId}').html(html)}
                            });
                            return false;
                          }
                        );";
             Yii::app()->clientScript->registerScript('taskModalDetailsScript' . $sourceId, $script);
        }

        /**
         * Register script for special task detail link. This is from a redirect of something like
         * tasks/default/details and it should open up the task immediately.
         * @param int $taskId
         * @param string $sourceId
         */
        public static function registerOpenToTaskModalDetailsScript($taskId, $sourceId)
        {
            assert('is_int($taskId)');
            assert('is_string($sourceId)');
            $modalId = TasksUtil::getModalContainerId();
            $url     = Yii::app()->createUrl('tasks/default/modalDetails', array('id' => $taskId));
            $ajaxOptions = TasksUtil::resolveAjaxOptionsForModalView('Details', $sourceId);
            $options = array('type'       => 'GET',
                             'url'        => $url,
                             'beforeSend' => $ajaxOptions['beforeSend'],
                             'update'     => $ajaxOptions['update'],
                             'success'    => "function(html){jQuery('#{$modalId}').html(html)}");
            $script  = ZurmoHtml::ajax($options);
            Yii::app()->clientScript->registerScript('openToTaskModalDetailsScript' . $sourceId, $script);
        }

        /**
         * Resolves the related project or first related activityItem string value
         * @param Task $task
         * @return null|string
         */
        public static function resolveFirstRelatedModelStringValue(Task $task)
        {
            $modelOrNull = static::resolveFirstRelatedModel($task);
            if ($modelOrNull === null)
            {
                return null;
            }
            return strval($modelOrNull);
        }

        /**
         * Resolves the related project or first related activityItem model
         * @param Task $task
         * @return null|RedBeanModel $model
         */
        public static function resolveFirstRelatedModel(Task $task)
        {
            if ($task->project->id > 0)
            {
                return $task->project;
            }
            elseif ($task->activityItems->count() > 0)
            {
                try
                {
                    $castedDownModel = TasksUtil::castDownActivityItem($task->activityItems[0]);
                    return $castedDownModel;
                }
                catch (NotFoundException $e)
                {
                }
            }
            return null;
        }

        public static function castDownActivityItem(Item $activityItem)
        {
            $relationModelClassNames = ActivitiesUtil::getActivityItemsModelClassNames();
            foreach ($relationModelClassNames as $relationModelClassName)
            {
                try
                {
                    $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($relationModelClassName);
                    return $activityItem->castDown(array($modelDerivationPathToItem));
                }
                catch (NotFoundException $e)
                {
                }
            }
        }

        /**
         * Renders completion date time content for the task
         * @param Task $task
         * @return string
         */
        public static function renderCompletionDateTime(Task $task)
        {
            if ($task->completedDateTime == null)
            {
                $task->completedDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            }
            return '<p>' . Zurmo::t('TasksModule', 'Completed On') . ': ' .
                                 DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($task->completedDateTime) . '</p>';
        }

        /**
         * @param $relationModelId
         * @return string
         */
        public static function resolveModalSaveActionNameForByRelationModelId($relationModelId, $copyAction = null)
        {
            assert('is_string($relationModelId) || is_int($relationModelId) ||$relationModelId == null');
            assert('is_string($copyAction) || $copyAction == null');
            if ($copyAction == 'copy')
            {
                return 'modalCopyFromRelation';
            }
            else
            {
                if ($relationModelId != null)
                {
                    return 'modalSaveFromRelation';
                }
                else
                {
                    return 'modalSave';
                }
            }
        }

        /**
         * Add subscriber to the task
         * @param User $user
         * @param Task $task
         * @param bool $hasReadLatest
         */
        public static function addSubscriber(User $user, Task $task, $hasReadLatest = false)
        {
            assert('is_bool($hasReadLatest)');
            if ($task->doNotificationSubscribersContainPerson($user) === false)
            {
                $notificationSubscriber = new NotificationSubscriber();
                $notificationSubscriber->person = $user;
                $notificationSubscriber->hasReadLatest = $hasReadLatest;
                $task->notificationSubscribers->add($notificationSubscriber);
            }
        }

        /**
         * Process kanban item update on button click on kanban board
         * @param int $targetStatus
         * @param int $taskId
         * @param int $sourceKanbanType
         */
        public static function processKanbanItemUpdateOnButtonAction($targetStatus, $taskId, $sourceKanbanType)
        {
            assert('is_int($targetStatus)');
            assert('is_int($taskId)');
            assert('is_int($sourceKanbanType)');
            $task = Task::getById($taskId);
            $kanbanItem = KanbanItem::getByTask($taskId);
            $targetKanbanType = null;
            if ($sourceKanbanType == KanbanItem::TYPE_SOMEDAY || $sourceKanbanType == KanbanItem::TYPE_TODO)
            {
                $targetKanbanType = KanbanItem::TYPE_IN_PROGRESS;
            }
            elseif ($sourceKanbanType == KanbanItem::TYPE_IN_PROGRESS)
            {
                if ($targetStatus == Task::STATUS_AWAITING_ACCEPTANCE ||
                                       $targetStatus == Task::STATUS_REJECTED ||
                                           $targetStatus == Task::STATUS_IN_PROGRESS)
                {
                    $targetKanbanType = KanbanItem::TYPE_IN_PROGRESS;
                }
                elseif (intval($targetStatus) == Task::STATUS_COMPLETED)
                {
                    $targetKanbanType = KanbanItem::TYPE_COMPLETED;
                }
            }

            //If kanbantype is changed, do the sort
            if ($sourceKanbanType != $targetKanbanType)
            {
                //Set the sort and type for target
                $sortOrder = self::resolveAndGetSortOrderForTaskOnKanbanBoard($targetKanbanType, $task);
                $kanbanItem->sortOrder = $sortOrder;
                $kanbanItem->type      = $targetKanbanType;
                if (!$kanbanItem->save())
                {
                    throw new FailedToSaveModelException();
                }
                //Resort the source one
                if ($task->project->id > 0)
                {
                    TasksUtil::sortKanbanColumnItems($sourceKanbanType, $task->project);
                }
                else
                {
                    TasksUtil::sortKanbanColumnItems($sourceKanbanType, $task->activityItems->offsetGet(0));
                }
            }
        }

        /**
         * Returns sortorder
         * @param Task $task
         * @param int $targetKanbanType
         * @return int
         */
        public static function resolveAndGetSortOrderForTaskOnKanbanBoard($targetKanbanType, Task $task)
        {
            if ($task->project->id > 0)
            {
                $sortOrder = KanbanItem::getMaximumSortOrderByType(intval($targetKanbanType), $task->project);
            }
            elseif ($task->activityItems->count() > 0)
            {
                $sortOrder = KanbanItem::getMaximumSortOrderByType(intval($targetKanbanType), $task->activityItems->offsetGet(0));
            }
            else
            {
                $sortOrder = 1;
            }
            return $sortOrder;
        }

        /**
         * Reset the sortoder for kanban type for the associated to it
         * @param Task $task
         * @param int $kanbanType
         * @param Item $childObjectOfItem
         * @return int
         */
        public static function sortKanbanColumnItems($kanbanType, Item $childObjectOfItem)
        {
            $models = KanbanItem::getAllTasksByType(intval($kanbanType), $childObjectOfItem);
            foreach ($models as $index => $model)
            {
                $model->sortOrder = $index + 1;
                $model->save();
            }
        }

        /**
         * Check kanban type for status and update if it is required, it is required
         * when user is changing the status from modal detail view
         * @param $task Task
         */
        public static function checkKanbanTypeByStatusAndUpdateIfRequired(Task $task)
        {
            $kanbanItem = KanbanItem::getByTask($task->id);
            //It should be created here but check for create as well here
            if ($kanbanItem == null)
            {
                TasksUtil::createKanbanItemFromTask($task);
            }
            else
            {
                if (!TasksUtil::isKanbanItemTypeValidBasedOnTaskStatus($kanbanItem->type, $task->status))
                {
                    $kanbanTypeByStatus = TasksUtil::resolveKanbanItemTypeForTaskStatus($task->status);
                    if ($kanbanItem->type != $kanbanTypeByStatus)
                    {
                        $sourceKanbanItemType = $kanbanItem->type;
                        //put the item at the end
                        $kanbanItem->sortOrder = TasksUtil::resolveAndGetSortOrderForTaskOnKanbanBoard($kanbanTypeByStatus, $task);
                        $kanbanItem->type = $kanbanTypeByStatus;
                        $kanbanItem->save();
                        //Resort the source column
                        if ($task->project->id > 0)
                        {
                            TasksUtil::sortKanbanColumnItems($sourceKanbanItemType, $task->project);
                        }
                        elseif ($task->activityItems->count() > 0)
                        {
                            TasksUtil::sortKanbanColumnItems($sourceKanbanItemType, $task->activityItems->offsetGet(0));
                        }
                    }
                }
            }
        }

        /**
         * Resolve and render task card details subscribers content
         * @param Task $task
         * @return type
         */
        public static function resolveAndRenderTaskCardDetailsSubscribersContent(Task $task)
        {
            $content         = null;
            $subscribedUsers = TasksUtil::getTaskSubscribers($task);
            foreach ($subscribedUsers as $user)
            {
                if ($user->isSame($task->owner))
                {
                    $content .= TasksUtil::renderSubscriberImageAndLinkContent($user, 20, 'task-owner');
                    break;
                }
            }
            //To take care of the case of duplicates
            $addedSubscribers = array();
            foreach ($subscribedUsers as $user)
            {
                if (!$user->isSame($task->owner))
                {
                    if (!in_array($user->id, $addedSubscribers))
                    {
                        $content .= TasksUtil::renderSubscriberImageAndLinkContent($user, 20);
                        $addedSubscribers[] = $user->id;
                    }
                }
            }
            return $content;
        }

        /**
         * Register task modal edit script
         * @param string $sourceId
         * @param array $routeParams
         */
        public static function registerTaskModalEditScript($sourceId, $routeParams)
        {
            assert('is_string($sourceId)');
            assert('is_array($routeParams)');
            $modalId     = TasksUtil::getModalContainerId();
            $url         = Yii::app()->createUrl('tasks/default/modalEdit', $routeParams);
            $script      = self::registerTaskModalScript("Edit", $url, '.edit-related-open-task', $sourceId);
            Yii::app()->clientScript->registerScript('taskModalEditScript', $script, ClientScript::POS_END);
        }

        /**
         * Register task modal copy script
         * @param string $sourceId
         * @param array $routeParams
         */
        public static function registerTaskModalCopyScript($sourceId, $routeParams)
        {
            assert('is_string($sourceId)');
            assert('is_array($routeParams)');
            $modalId     = TasksUtil::getModalContainerId();
            $url         = Yii::app()->createUrl('tasks/default/modalCopy',
                                                    array_merge($routeParams, array('action' => 'copy')));
            $script      = self::registerTaskModalScript("Copy", $url, '.copy-related-open-task', $sourceId);
            Yii::app()->clientScript->registerScript('taskModalCopyScript', $script, ClientScript::POS_END);
        }

        /**
         * Get task modal script
         * @param string $type
         * @param string $url
         * @param string $selector
         * @param mixed $sourceId
         * @return string
         */
        public static function registerTaskModalScript($type, $url, $selector, $sourceId = null)
        {
            assert('is_string($type)');
            assert('is_string($url)');
            assert('is_string($selector)');
            assert('is_string($sourceId) || $sourceId == null');
            $modalId     = TasksUtil::getModalContainerId();
            $ajaxOptions = TasksUtil::resolveAjaxOptionsForModalView($type, $sourceId);
            $ajaxOptions['beforeSend'] = new CJavaScriptExpression($ajaxOptions['beforeSend']);
            return "$(document).on('click', '{$selector}', function()
                         {
                            var id = $(this).attr('id');
                            var idParts = id.split('-');
                            var taskId = parseInt(idParts[1]);
                            $.ajax(
                            {
                                'type' : 'GET',
                                'url'  : '{$url}' + '&id=' + taskId,
                                'beforeSend' : {$ajaxOptions['beforeSend']},
                                'update'     : '{$ajaxOptions['update']}',
                                'success': function(html){jQuery('#{$modalId}').html(html)}
                            });
                          }
                        );";
        }

        /**
         * Register task modal delete script
         * @param string $sourceId
         */
        public static function registerTaskModalDeleteScript($sourceId)
        {
            assert('is_string($sourceId)');
            $url = Yii::app()->createUrl('tasks/default/delete');
            $params = LabelUtil::getTranslationParamsForAllModules();
            $confirmTitle  = Zurmo::t('Core', 'Are you sure you want to delete this {modelLabel}?',
                                                        array('{modelLabel}' => Zurmo::t('TasksModule', 'TasksModuleSingularLabel', $params)));
            $script = "$(document).on('click', '.delete-related-open-task', function()
                         {
                            if (!confirm('{$confirmTitle}'))
                            {
                                return false;
                            }
                            var id = $(this).attr('id');
                            var idParts = id.split('-');
                            var taskId = parseInt(idParts[3]);
                            $.ajax(
                            {
                                'type' : 'GET',
                                'url'  : '{$url}' + '?id=' + taskId,

                                'success': function(data)
                                           {
                                             $.fn.yiiGridView.update('{$sourceId}');
                                           }
                            });
                          }
                        );";
             Yii::app()->clientScript->registerScript('taskModalDeleteScript', $script, ClientScript::POS_END);
        }

        /**
         * Resolve that should task be opened in modal detail view
         */
        public static function resolveShouldOpenToTask($gridId)
        {
            $getData = GetUtil::getData();
            if (null != $taskId = ArrayUtil::getArrayValue($getData, 'openToTaskId'))
            {
                Yii::app()->custom->registerOpenToTaskModalDetailsScript((int)$taskId, $gridId);
            }
        }

        /**
         * Gets full calendar item data.
         * @return string
         */
        public function getCalendarItemData()
        {
            $name                      = $this->name;
            $status                    = self::getStatusDisplayName($this->status);
            $requestedByUser           = $this->requestedByUser->getFullName();
            $owner                     = $this->owner->getFullName();
            $language                  = Yii::app()->languageHelper->getForCurrentUser();
            $translatedAttributeLabels = self::translatedAttributeLabels($language);
            return array(Zurmo::t('Core',        'Name',    array(), null, $language)          => $name,
                         Zurmo::t('ZurmoModule', 'Status',  array(), null, $language)       => $status,
                         $translatedAttributeLabels['requestedByUser']                      => $requestedByUser,
                         $translatedAttributeLabels['owner']                                => $owner);
        }

        /**
         * Resolve that should a task be opened on details and relations view.
         */
        public static function resolveShouldOpenToTaskForDetailsAndRelationsView()
        {
            $getData = GetUtil::getData();
            //This would be required in case edit of task navigates to a new page and not modal
            if (null != $gridId = ArrayUtil::getArrayValue($getData, 'sourceId'))
            {
                TasksUtil::resolveShouldOpenToTask($gridId);
            }
        }

        /**
         * Resolve redirect url in case of open task actions on details and relations view.
         * This is required else same params get added to create url.
         * @param string redirect url
         */
        public static function resolveOpenTasksActionsRedirectUrlForDetailsAndRelationsView($redirectUrl)
        {
            if ($redirectUrl != null)
            {
                $routeData      = explode('?', $redirectUrl);
                if (count($routeData) > 1)
                {
                    $queryData      = explode('&', $routeData[1]);
                    foreach ($queryData as $val)
                    {
                        if (strpos($val, 'id=') !== false)
                        {
                            $routeData[1] = $val;
                            break;
                        }
                    }
                }
                $redirectUrl = implode('?', $routeData);
            }
            return $redirectUrl;
        }
    }
?>