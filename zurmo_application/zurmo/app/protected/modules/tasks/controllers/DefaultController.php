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

    class TasksDefaultController extends ActivityModelsDefaultController
    {
        public function filters()
        {
            return array_merge(parent::filters(),
                array(
                    array(
                        ZurmoBaseController::REQUIRED_ATTRIBUTES_FILTER_PATH . ' + modalCreateFromRelation, modalCreate,
                                            ModalEdit',
                        'moduleClassName' => get_class($this->getModule()),
                        'viewClassName'   => 'TaskModalEditView',
                        'isModal'         => true,
                   ),
                    array(
                        ZurmoBaseController::REQUIRED_ATTRIBUTES_FILTER_PATH . ' + modalDetails',
                        'moduleClassName' => get_class($this->getModule()),
                        'viewClassName'   => 'TaskModalDetailsView',
                   ),
               )
            );
        }

        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $task                           = new Task(false);
            $searchForm                     = new TasksSearchForm($task);
            $listAttributesSelector         = new ListAttributesSelector('TasksListView', get_class($this->getModule()));
            $searchForm->setListAttributesSelector($listAttributesSelector);

            $dataProvider  = $this->resolveSearchDataProvider(
                                                        $searchForm,
                                                        $pageSize,
                                                        null,
                                                        'TasksSearchView'
                                                    );
            if ((isset($_GET['ajax']) && $_GET['ajax'] == 'list-view'))
            {
                if (isset($_GET['openToTaskId']))
                {
                    unset($_GET['openToTaskId']);
                }
                $mixedView  = $this->makeListView(
                            $searchForm,
                            $dataProvider
                        );
                $view       = new TasksPageView($mixedView);
            }
            else
            {
                $mixedView  = $this->makeActionBarSearchAndListView($searchForm, $dataProvider,
                                   'SecuredActionBarForTasksSearchAndListView',
                                    null, null, null);
                $view       = new TasksPageView(ZurmoDefaultViewUtil::
                                                    makeStandardViewForCurrentUser(
                                                        $this, $mixedView));
            }
            echo $view->render();
        }

        public function actionDetails($id, $redirectUrl = null)
        {
            $task = static::getModelAndCatchNotFoundAndDisplayError('Task', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($task);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($task), 'TasksModule'), $task);
            if ($task->project->id > 0)
            {
                $this->redirect(Yii::app()->createUrl('projects/default/details',
                                                      array('id' => $task->project->id, 'openToTaskId' => $task->id)));
            }
            elseif ($task->activityItems->count() > 0)
            {
                try
                {
                    $castedDownModel = TasksUtil::castDownActivityItem($task->activityItems[0]);
                    $moduleClassName = StateMetadataAdapter::resolveModuleClassNameByModel($castedDownModel);
                    $this->redirect(Yii::app()->createUrl($moduleClassName::getDirectoryName() . '/default/details',
                        array('id' => $castedDownModel->id, 'kanbanBoard' => true, 'openToTaskId' => $task->id)));
                }
                catch (NotFoundException $e)
                {
                    //Something is missing or deleted. Fallback to home page
                    $this->redirect(Yii::app()->createUrl('home/default/index'));
                }
            }
            else
            {
                $this->redirect(Yii::app()->createUrl('tasks/default/list',
                                                      array('openToTaskId' => $id)));
            }
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $task = Task::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($task);
            if ($task->project->id > 0)
            {
                $this->redirect(Yii::app()->createUrl('projects/default/details',
                                                      array('id' => $task->project->id, 'openToTaskId' => $task->id)));
            }
            elseif ($task->activityItems->count() > 0)
            {
                try
                {
                    $castedDownModel = TasksUtil::castDownActivityItem($task->activityItems[0]);
                    $moduleClassName = StateMetadataAdapter::resolveModuleClassNameByModel($castedDownModel);
                    $this->redirect(Yii::app()->createUrl($moduleClassName::getDirectoryName() . '/default/details',
                        array('id' => $castedDownModel->id, 'kanbanBoard' => true, 'openToTaskId' => $task->id)));
                }
                catch (NotFoundException $e)
                {
                    //Something is missing or deleted. Fallback to home page
                    $this->redirect(Yii::app()->createUrl('home/default/index'));
                }
            }
            else
            {
                $this->redirect(Yii::app()->createUrl('tasks/default/list',
                                                      array('openToTaskId' => $id)));
            }
        }

        /**
         * Close task
         * @param $id
         * @throws NotSupportedException
         */
        public function actionCloseTask($id)
        {
            $task                    = Task::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($task);
            $task->status            = Task::STATUS_COMPLETED;
            $saved                   = $task->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            TasksNotificationUtil::submitTaskNotificationMessage($task,
                                                                 TasksNotificationUtil::TASK_STATUS_BECOMES_COMPLETED,
                                                                 Yii::app()->user->userModel);
        }

        /**
         * Create comment via ajax for task
         * @param type $id
         * @param string $uniquePageId
         */
        public function actionInlineCreateCommentFromAjax($id, $uniquePageId)
        {
            $comment       = new Comment();
            $redirectUrl   = Yii::app()->createUrl('/tasks/default/inlineCreateCommentFromAjax',
                                                    array('id'           => $id,
                                                          'uniquePageId' => $uniquePageId));
            $urlParameters = array('relatedModelId'           => (int)$id,
                                   'relatedModelClassName'    => 'Task',
                                   'relatedModelRelationName' => 'comments',
                                   'redirectUrl'              => $redirectUrl); //After save, the url to go to.
            $uniquePageId  = 'CommentInlineEditForModelView';
            echo             ZurmoHtml::tag('h2', array(), Zurmo::t('CommentsModule', 'Add Comment'));
            $inlineView    = new CommentInlineEditView($comment, 'default', 'comments', 'inlineCreateSave',
                                                       $urlParameters, $uniquePageId);
            $view          = new AjaxPageView($inlineView);
            echo $view->render();
        }

        /**
         * Update due data time using ajas
         * @param int $id
         * @param int $dateTime
         */
        public function actionUpdateDueDateTimeViaAjax($id, $dateTime)
        {
            $task         = Task::getById(intval($id));
            $dateTime     = strtotime($dateTime);
            $dueDateTime  = DateTimeUtil::convertTimestampToDbFormatDateTime($dateTime);
            $task->dueDateTime = $dueDateTime;
            $task->save();
        }

        /**
         * Add subscriber for task
         * @param int $id
         */
        public function actionAddSubscriber($id)
        {
            $task    = $this->processSubscriptionRequest($id);
            $content = TasksUtil::getTaskSubscriberData($task);
            $content .= TasksUtil::resolveSubscriptionLink($task, 'detail-subscribe-task-link', 'detail-unsubscribe-task-link');
            echo $content;
        }

        /**
         * Remove subscriber for task
         * @param int $id
         */
        public function actionRemoveSubscriber($id)
        {
            $task    = $this->processUnsubscriptionRequest($id);
            $content = TasksUtil::getTaskSubscriberData($task);
            $content .= TasksUtil::resolveSubscriptionLink($task, 'detail-subscribe-task-link', 'detail-unsubscribe-task-link');
            if ($content == null)
            {
                echo "";
            }
            else
            {
                echo $content;
            }
        }

        /**
         * Add kanban subscriber
         * @param string $id
         */
        public function actionAddKanbanSubscriber($id)
        {
            $task = $this->processSubscriptionRequest($id);
            $content = TasksUtil::resolveAndRenderTaskCardDetailsSubscribersContent($task);
            $content .= TasksUtil::resolveSubscriptionLink($task, 'subscribe-task-link', 'unsubscribe-task-link');
            echo $content;
        }

        /**
         * Unsubscribe the user from the task
         * @param string $id
         */
        public function actionRemoveKanbanSubscriber($id)
        {
            $task = $this->processUnsubscriptionRequest($id);
            $content = TasksUtil::resolveAndRenderTaskCardDetailsSubscribersContent($task);
            $content .= TasksUtil::resolveSubscriptionLink($task, 'subscribe-task-link', 'unsubscribe-task-link');
            if ($content == null)
            {
                echo "";
            }
            else
            {
                echo $content;
            }
        }

        /**
         * Create task from related view
         * @param null $relationAttributeName
         * @param null $relationModelId
         * @param null $relationModuleId
         */
        public function actionModalCreateFromRelation($relationAttributeName = null, $relationModelId = null,
                                                      $relationModuleId = null)
        {
            $task  = new Task();
            if ($relationAttributeName == 'project' && $relationModelId != null)
            {
                $project = Project::getById((int)$relationModelId);
                $task->project = $project;
            }
            else
            {
                $task  = $this->resolveNewModelByRelationInformation($task, $relationAttributeName,
                        (int)$relationModelId, $relationModuleId);
            }
            $this->processTaskEdit($task);
        }

        /**
         * Create task from top menu
         */
        public function actionModalCreate()
        {
            $task = new Task();
            $this->processTaskEdit($task);
        }

        /**
         * Saves task in the modal view
         * @param string $relationAttributeName
         * @param string $relationModelId
         * @param string $relationModuleId
         */
        public function actionModalSaveFromRelation($relationAttributeName, $relationModelId, $relationModuleId, $id = null)
        {
            if ($id == null)
            {
                $task  = new Task();
                if ($relationAttributeName == 'project' && $relationModelId != null)
                {
                    $project = Project::getById((int)$relationModelId);
                    $task->project = $project;
                }
                else
                {
                    $task  = $this->resolveNewModelByRelationInformation( $task, $relationAttributeName,
                                                                                 (int)$relationModelId,
                                                                                 $relationModuleId);
                }
            }
            else
            {
                $task   = Task::getById(intval($id));
            }
            $task       = $this->attemptToSaveModelFromPost($task, null, false);
            //Log event for project audit
            if ($relationAttributeName == 'project')
            {
                ProjectsUtil::logAddTaskEvent($task);
            }
            $this->actionModalDetails($task->id);
        }

        /**
         * Saves task in the modal view
         */
        public function actionModalSave($id = null)
        {
            if ($id == null)
            {
                $task = new Task();
            }
            else
            {
                $task = Task::getById(intval($id));
            }
            $this->attemptToValidateAndSaveFromModalDetails($task);
            $task = $this->attemptToSaveModelFromPost($task, null, false);
            $this->processModalDetails($task);
        }

        /**
         * Copy task
         * @param string $id
         */
        public function actionModalCopy($id)
        {
            $task = Task::getById((int)$id);
            $this->processTaskEdit($task);
        }

        /**
         * Copy task in the modal view
         * @param string $id
         */
        public function actionModalCopyFromRelation($id)
        {
            $copyToTask = $this->processTaskCopy($id);
            //Log event for project audit
            if ($copyToTask->project->id > 0)
            {
                ProjectsUtil::logAddTaskEvent($copyToTask);
            }
            $this->actionModalDetails($copyToTask->id);
        }

        /**
         * Loads modal view from related view
         * @param string $id
         */
        public function actionModalDetails($id)
        {
            $task = Task::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($task);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($task), 'TasksModule'), $task);
            $this->attemptToValidateAndSaveFromModalDetails($task);
            $this->processModalDetails($task);
        }

        protected function processModalDetails(Task $task)
        {
            TasksUtil::markUserHasReadLatest($task, Yii::app()->user->userModel);
            echo ModalEditAndDetailsControllerUtil::setAjaxModeAndRenderModalDetailsView($this, 'TaskModalDetailsView',
                $task,
                'Details');
        }

        /**
         * Edit task from related view
         * @param string $id
         */
        public function actionModalEdit($id)
        {
            $task = Task::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($task);
            $this->processTaskEdit($task);
        }

        /**
         * Process Task Edit
         * @param Task $task
         */
        protected function processTaskEdit(Task $task)
        {
            $isNewModel = $task->isNewModel;
            if (RightsUtil::canUserAccessModule('TasksModule', Yii::app()->user->userModel))
            {
                if (isset($_POST['ajax']) && $_POST['ajax'] == 'task-modal-edit-form')
                {
                    $controllerUtil   = static::getZurmoControllerUtil();
                    $controllerUtil->validateAjaxFromPost($task, 'Task');
                    if ($isNewModel)
                    {
                        TasksNotificationUtil::makeAndSubmitNewTaskNotificationMessage($task);
                    }
                    Yii::app()->getClientScript()->setToAjaxMode();
                    Yii::app()->end(0, false);
                }
                else
                {
                    echo ModalEditAndDetailsControllerUtil::setAjaxModeAndRenderModalEditView($this,
                                                                                            'TaskModalEditView',
                                                                                            $task);
                }
            }
        }

        /**
         * Should support in addition to custom field as well
         * @param string $id
         * @param string $attribute
         * @param string $value
         * @throws FailedToSaveModelException
         */
        public function actionUpdateAttributeValue($id, $attribute, $value)
        {
            $modelClassName = $this->getModule()->getPrimaryModelName();
            $model          = $modelClassName::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($model);
            $model->{$attribute}->value = $value;
            $saved                      = $model->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
        }

        /**
         * Update status for the task when dragging in the kanban view
         */
        public function actionUpdateStatusOnDragInKanbanView($type)
        {
            $getData = GetUtil::getData();
            $counter = 1;
            $response = array();
            if (count($getData['items']) > 0)
            {
                foreach ($getData['items'] as $taskId)
                {
                    if ($taskId != '')
                    {
                        $kanbanItem  = KanbanItem::getByTask(intval($taskId));
                        $task        = Task::getById(intval($taskId));
                        //if kanban type is completed
                        if ($type == KanbanItem::TYPE_COMPLETED)
                        {
                            //kanban update has to be done first
                            $kanbanItem->sortOrder = TasksUtil::resolveAndGetSortOrderForTaskOnKanbanBoard($type, $task);
                            $kanbanItem->type = intval($type);
                            $kanbanItem->save();
                            //set the scenario
                            $task->setScenario('kanbanViewDrag');
                            $this->processStatusUpdateViaAjax($task, Task::STATUS_COMPLETED, false);
                            $response['button'] = '';
                            $response['status'] = Task::getStatusDisplayName($task->status);
                            $response['owner']  = $task->owner->getFullName();
                            $subscriptionContent = TasksUtil::resolveAndRenderTaskCardDetailsSubscribersContent($task);
                            $subscriptionContent .= TasksUtil::resolveSubscriptionLink($task, 'subscribe-task-link', 'unsubscribe-task-link');
                            $response['subscriptionContent']  = $subscriptionContent;
                        }
                        else
                        {
                            //When in the same column
                            if ($type == $kanbanItem->type)
                            {
                                $kanbanItem->sortOrder = $counter;
                                $kanbanItem->save();
                            }
                            else
                            {
                                //This would be the one which is dragged across column
                                $targetStatus = TasksUtil::getDefaultTaskStatusForKanbanItemType(intval($type));
                                $this->processStatusUpdateViaAjax($task, $targetStatus, false);
                                $content = TasksUtil::resolveActionButtonForTaskByStatus($targetStatus,
                                                                                        $this->getId(),
                                                                                        $this->getModule()->getId(),
                                                                                        intval($taskId));
                                $kanbanItem->sortOrder = $counter;
                                $kanbanItem->type = intval($type);
                                $kanbanItem->save();
                                $subscriptionContent = TasksUtil::resolveAndRenderTaskCardDetailsSubscribersContent($task);
                                $subscriptionContent .= TasksUtil::resolveSubscriptionLink($task, 'subscribe-task-link', 'unsubscribe-task-link');
                                $response['button'] = $content;
                                $response['status'] = Task::getStatusDisplayName($task->status);
                                $response['owner']  = $task->owner->getFullName();
                                $response['subscriptionContent']  = $subscriptionContent;
                            }
                        }
                        $counter++;
                    }
                }
            }
            echo CJSON::encode($response);
        }

       /**
        * Update task status in kanban view
        * @param int $targetStatus
        * @param int $taskId
        */
        public function actionUpdateStatusInKanbanView($targetStatus, $taskId, $sourceKanbanType)
        {
           $response = array();
           //Run update queries for update task staus and update type and sort order in kanban column
           $task = Task::getById(intval($taskId));
           //set the scenario
           $task->setScenario('kanbanViewButtonClick');
           $this->processStatusUpdateViaAjax($task, $targetStatus, false);
           TasksUtil::processKanbanItemUpdateOnButtonAction(intval($targetStatus), intval($taskId), intval($sourceKanbanType));
           $subscriptionContent = TasksUtil::resolveAndRenderTaskCardDetailsSubscribersContent($task);
           $subscriptionContent .= TasksUtil::resolveSubscriptionLink($task, 'subscribe-task-link', 'unsubscribe-task-link');
           $response['subscriptionContent']  = $subscriptionContent;
           echo CJSON::encode($response);
        }

        /**
         * Process status update via ajax
         * @param int $id
         * @param int $status
         * @param bool $showCompletionDate whether to show completion date
         */
        protected function processStatusUpdateViaAjax(Task $task, $status, $showCompletionDate = true)
        {
            $currentStatus = $task->status;
            $task->status = intval($status);
            //check for owner in case a user start the task
            if ($currentStatus == Task::STATUS_NEW && $currentStatus != $task->status)
            {
                $task->owner = Yii::app()->user->userModel;
            }

            if (intval($status) == Task::STATUS_COMPLETED)
            {
                foreach ($task->checkListItems as $checkItem)
                {
                    $checkItem->completed = true;
                    $checkItem->unrestrictedSave();
                }
                $task->status            = Task::STATUS_COMPLETED;
                $task->completedDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                $task->completed         = true;
                $task->save();
                if ($showCompletionDate)
                {
                    echo TasksUtil::renderCompletionDateTime($task);
                }
            }
            else
            {
                $task->completedDateTime = null;
                $task->completed         = false;
                $task->save();
            }
            if ($task->project->id > 0)
            {
                ProjectsUtil::logTaskStatusChangeEvent($task,
                                                       Task::getStatusDisplayName(intval($currentStatus)),
                                                       Task::getStatusDisplayName(intval($status)));
            }
        }

        /**
         * Process subscription request for task
         * @param int $id
         */
        protected function processSubscriptionRequest($id)
        {
            $task = Task::getById(intval($id));
            if (!$task->doNotificationSubscribersContainPerson(Yii::app()->user->userModel))
            {
                $notificationSubscriber = new NotificationSubscriber();
                $notificationSubscriber->person = Yii::app()->user->userModel;
                $notificationSubscriber->hasReadLatest = false;
                $task->notificationSubscribers->add($notificationSubscriber);
            }
            $task->save();
            return $task;
        }

        /**
         * Process unsubscription request for task
         * @param int $id
         * @throws FailedToSaveModelException
         * @return Task $task
         */
        protected function processUnsubscriptionRequest($id)
        {
            $task = Task::getById(intval($id));
            foreach ($task->notificationSubscribers as $notificationSubscriber)
            {
                if ($notificationSubscriber->person->getClassId('Item') == Yii::app()->user->userModel->getClassId('Item'))
                {
                    $task->notificationSubscribers->remove($notificationSubscriber);
                    break;
                }
            }
            $saved = $task->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            return $task;
        }

        /**
         * Gets zurmo controller util for task
         */
        protected static function getZurmoControllerUtil()
        {
            return new TaskZurmoControllerUtil('activityItems', 'TaskActivityItemForm');
        }

        /**
         * @param $id
         * @param null $redirectUrl
         * @throws FailedToDeleteModelException
         */
        public function actionDelete($id, $redirectUrl = null)
        {
            $task              = Task::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($task);
            if (!$task->delete())
            {
                throw new FailedToDeleteModelException();
            }
        }

        /**
         * Validates and save from modal details
         * @param Task $task
         */
        protected function attemptToValidateAndSaveFromModalDetails(Task $task)
        {
            if (isset($_POST['ajax']) &&
                ($_POST['ajax'] == 'task-left-column-form-data' || $_POST['ajax'] == 'task-right-column-form-data'))
            {
                $task = $this->attemptToSaveModelFromPost($task, null, false);
                $errorData = ZurmoActiveForm::makeErrorsDataAndResolveForOwnedModelAttributes($task);
                echo CJSON::encode($errorData);
                Yii::app()->end(0, false);
            }
        }

        public function actionExport()
        {
            $this->export('TasksSearchView');
        }

        /**
         * @return string
         */
        protected static function getSearchFormClassName()
        {
            return 'TasksSearchForm';
        }

        /**
         * Action for displaying a mass edit form and also action when that form is first submitted.
         * When the form is submitted, in the event that the quantity of models to update is greater
         * than the pageSize, then once the pageSize quantity has been reached, the user will be
         * redirected to the makeMassEditProgressView.
         * In the mass edit progress view, a javascript refresh will take place that will call a refresh
         * action, usually massEditProgressSave.
         * If there is no need for a progress view, then a flash message will be added and the user will
         * be redirected to the list view for the model.  A flash message will appear providing information
         * on the updated records.
         * @see Controler->makeMassEditProgressView
         * @see Controller->processMassEdit
         * @see
         */
        public function actionMassEdit()
        {
            $pageSize           = Yii::app()->pagination->resolveActiveForCurrentUserByType('massEditProgressPageSize');
            $task               = new Task(false);
            $activeAttributes   = $this->resolveActiveAttributesFromMassEditPost();
            $dataProvider       = $this->getDataProviderByResolvingSelectAllFromGet(
                                            new TasksSearchForm($task),
                                            $pageSize,
                                            Yii::app()->user->userModel->id,
                                            null,
                                            'TasksSearchView');
            $selectedRecordCount = $this->getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $task                = $this->processMassEdit(
                                        $pageSize,
                                        $activeAttributes,
                                        $selectedRecordCount,
                                        'TasksPageView',
                                        $task,
                                        TasksModule::getModuleLabelByTypeAndLanguage('Plural'),
                                        $dataProvider
                                    );
            $massEditView       = $this->makeMassEditView(
                                        $task,
                                        $activeAttributes,
                                        $selectedRecordCount,
                                        TasksModule::getModuleLabelByTypeAndLanguage('Plural')
                                       );
            $view               = new TasksPageView(ZurmoDefaultViewUtil::
                                                    makeStandardViewForCurrentUser($this, $massEditView));
            echo $view->render();
        }

        /**
         * Action called in the event that the mass edit quantity is larger than the pageSize.
         * This action is called after the pageSize quantity has been updated and continues to be
         * called until the mass edit action is complete.  For example, if there are 20 records to update
         * and the pageSize is 5, then this action will be called 3 times.  The first 5 are updated when
         * the actionMassEdit is called upon the initial form submission.
         */
        public function actionMassEditProgressSave()
        {
            $pageSize       = Yii::app()->pagination->resolveActiveForCurrentUserByType('massEditProgressPageSize');
            $task           = new Task(false);
            $dataProvider   = $this->getDataProviderByResolvingSelectAllFromGet(
                                            new TasksSearchForm($task),
                                            $pageSize,
                                            Yii::app()->user->userModel->id,
                                            null,
                                            'TasksSearchView'
                                        );
            $this->processMassEditProgressSave(
                        'Task',
                        $pageSize,
                        TasksModule::getModuleLabelByTypeAndLanguage('Plural'),
                        $dataProvider
                    );
        }

        /**
         * Action for displaying a mass delete form and also action when that form is first submitted.
         * When the form is submitted, in the event that the quantity of models to delete is greater
         * than the pageSize, then once the pageSize quantity has been reached, the user will be
         * redirected to the makeMassDeleteProgressView.
         * In the mass delete progress view, a javascript refresh will take place that will call a refresh
         * action, usually makeMassDeleteProgressView.
         * If there is no need for a progress view, then a flash message will be added and the user will
         * be redirected to the list view for the model.  A flash message will appear providing information
         * on the delete records.
         * @see Controller->makeMassDeleteProgressView
         * @see Controller->processMassDelete
         * @see
         */
        public function actionMassDelete()
        {
            $params          = LabelUtil::getTranslationParamsForAllModules();
            $title           = Zurmo::t('TasksModule', 'Mass Delete TasksModulePluralLabel', $params);
            $breadCrumbLinks = array(
                 $title,
            );
            $pageSize           = Yii::app()->pagination->resolveActiveForCurrentUserByType('massDeleteProgressPageSize');
            $task            = new Task(false);

            $activeAttributes   = $this->resolveActiveAttributesFromMassDeletePost();
            $dataProvider       = $this->getDataProviderByResolvingSelectAllFromGet(
                                            new TasksSearchForm($task),
                                            $pageSize,
                                            Yii::app()->user->userModel->id,
                                            null,
                                            'TasksSearchView');
            $selectedRecordCount = $this->getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $task             = $this->processMassDelete(
                                                            $pageSize,
                                                            $activeAttributes,
                                                            $selectedRecordCount,
                                                            'TasksPageView',
                                                            $task,
                                                            TasksModule::getModuleLabelByTypeAndLanguage('Plural'),
                                                            $dataProvider
                                                          );
            $massDeleteView     = $this->makeMassDeleteView(
                                                             $task,
                                                             $activeAttributes,
                                                             $selectedRecordCount,
                                                             TasksModule::getModuleLabelByTypeAndLanguage('Plural')
                                                            );
            $view               = new TasksPageView(ZurmoDefaultViewUtil::
                                                    makeStandardViewForCurrentUser($this, $massDeleteView));
            echo $view->render();
        }

        /**
         * Action called in the event that the mass delete quantity is larger than the pageSize.
         * This action is called after the pageSize quantity has been delted and continues to be
         * called until the mass delete action is complete.  For example, if there are 20 records to delete
         * and the pageSize is 5, then this action will be called 3 times.  The first 5 are updated when
         * the actionMassDelete is called upon the initial form submission.
         */
        public function actionMassDeleteProgress()
        {
            $pageSize       = Yii::app()->pagination->resolveActiveForCurrentUserByType('massDeleteProgressPageSize');
            $task        = new Task(false);
            $dataProvider   = $this->getDataProviderByResolvingSelectAllFromGet(
                                          new TasksSearchForm($task),
                                          $pageSize,
                                          Yii::app()->user->userModel->id,
                                          null,
                                          'TasksSearchView'
                                        );
            $this->processMassDeleteProgress(
                                                'Task',
                                                $pageSize,
                                                TasksModule::getModuleLabelByTypeAndLanguage('Plural'),
                                                $dataProvider
                                             );
        }

        /**
         * Process task copy
         * @param string $id
         * @return Task
         */
        private function processTaskCopy($id)
        {
            $copyToTask   = new Task();
            $task   = Task::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($task);
            TaskActivityCopyModelUtil::copy($task, $copyToTask);
            $copyToTask   = $this->attemptToSaveModelFromPost($copyToTask, null, false);
            return $copyToTask;
        }
    }
?>