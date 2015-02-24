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

    class TasksUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            TaskTestHelper::createTaskByNameForOwner('My Task', $super);
            AccountTestHelper::createAccountByNameForOwner('anAccount', $super);
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        /**
         * @covers markUserHasReadLatest
         */
        public function testMarkUserHasReadLatest()
        {
            $super                     = User::getByUsername('super');
            $steven                    = UserTestHelper::createBasicUser('steven');

            $task = new Task();
            $task->name = 'MyTest';
            $nowStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($task->save());

            $task = Task::getById($task->id);
            $user = Yii::app()->user->userModel;
            $notificationSubscriber = new NotificationSubscriber();
            $notificationSubscriber->person = $steven;
            $notificationSubscriber->hasReadLatest = false;
            $task->notificationSubscribers->add($notificationSubscriber);
            $this->assertTrue($task->save());

            $id = $task->id;
            $task->forget();
            unset($task);

            $task = Task::getById($id);
            $this->assertEquals(0, $task->notificationSubscribers->offsetGet(0)->hasReadLatest);
            $this->assertEquals(0, $task->notificationSubscribers->offsetGet(1)->hasReadLatest);
            //After running for super, nothing will change.
            TasksUtil::markUserHasReadLatest($task, $steven);
            TasksUtil::markUserHasReadLatest($task, $super);
            $id = $task->id;
            $task->forget();
            unset($task);

            $task = Task::getById($id);
            foreach ($task->notificationSubscribers as $position => $subscriber)
            {
                $this->assertEquals(1, $subscriber->hasReadLatest);
            }
        }

        /**
         * @covers getTaskSubscriberData
         * @covers renderSubscriberImageAndLinkContent
         */
        public function testGetTaskSubscriberData()
        {
            $user  = User::getByUsername('steven');

            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];

            $content = TasksUtil::getTaskSubscriberData($task);
            $this->assertContains('gravatar', $content);
            $this->assertContains('users/default/details', $content);
        }

        /**
         * @covers getTaskSubscribers
         */
        public function testGetTaskSubscribers()
        {
            $user  = User::getByUsername('steven');

            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];

            $subscribers = TasksUtil::getTaskSubscribers($task);
            $found = false;
            foreach ($subscribers as $subscriber)
            {
                if ($subscriber->id == $user->id)
                {
                    $found = true;
                }
            }
            $this->assertTrue($found);
        }

        /**
         * @covers resolveExplicitPermissionsForRequestedByUser
         */
        public function testResolveExplicitPermissionsForRequestedByUser()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $mark                       = UserTestHelper::createBasicUser('mark');
            $user                       = User::getByUsername('steven');

            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];

            $task->requestedByUser = $user;
            $this->assertTrue($task->save());
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($task);
            $this->assertEquals($explicitReadWriteModelPermissions->getReadWritePermitablesCount(), 0);
            TasksUtil::resolveExplicitPermissionsForRequestedByUser($task, $mark, $user, $explicitReadWriteModelPermissions);
            $this->assertEquals($explicitReadWriteModelPermissions->getReadWritePermitablesCount(), 1);
            $this->assertEquals($explicitReadWriteModelPermissions->getReadWritePermitablesToRemoveCount(), 1);
        }

        /**
         * @covers getModalDetailsTitle
         */
        public function testGetModalDetailsTitle()
        {
            $title = TasksUtil::getModalDetailsTitle();
            $this->assertEquals('Collaborate On This Task', $title);
        }

        /**
         * @covers getModalTitleForCreateTask
         */
        public function testGetModalTitleForCreateTask()
        {
            $title = TasksUtil::getModalTitleForCreateTask();
            $this->assertEquals('Create Task', $title);

            $title = TasksUtil::getModalTitleForCreateTask("Edit");
            $this->assertEquals('Edit Task', $title);

            $title = TasksUtil::getModalTitleForCreateTask("Copy");
            $this->assertEquals('Copy Task', $title);
        }

        /**
         * @covers getModalEditTitle
         */
        public function testGetModalEditTitle()
        {
            $title = TasksUtil::getModalEditTitle();
            $this->assertEquals('Edit Task', $title);
        }

        /**
         * @covers resolveKanbanItemTypeForTaskStatus
         */
        public function testResolveKanbanItemTypeForTaskStatus()
        {
            $kanbanItemType = TasksUtil::resolveKanbanItemTypeForTaskStatus(Task::STATUS_AWAITING_ACCEPTANCE);
            $this->assertEquals(KanbanItem::TYPE_IN_PROGRESS, $kanbanItemType);

            $kanbanItemType = TasksUtil::resolveKanbanItemTypeForTaskStatus(Task::STATUS_NEW);
            $this->assertEquals(KanbanItem::TYPE_SOMEDAY, $kanbanItemType);
        }

        /**
         * @covers resolveSubscribeUrl
         */
        public function testResolveSubscriptionLink()
        {
            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];
            $sally  = UserTestHelper::createBasicUser('sally');
            $maggi  = UserTestHelper::createBasicUser('maggi');
            $task->owner = $sally;
            $task->requestedByUser = $maggi;
            $task->save();
            if ($task->doNotificationSubscribersContainPerson(Yii::app()->user->userModel) === false)
            {
                $notificationSubscriber = new NotificationSubscriber();
                $notificationSubscriber->person = Yii::app()->user->userModel;
                $task->notificationSubscribers->add($notificationSubscriber);
                $task->save();
            }
            $link = TasksUtil::getKanbanSubscriptionLink($task, 0);
            $this->assertContains('unsubscribe-task-link', $link);
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('User');
            foreach ($task->notificationSubscribers as $notificationSubscriber)
            {
                $user = $notificationSubscriber->person->castDown(array($modelDerivationPathToItem));
                if ($user->id == Yii::app()->user->userModel->id)
                {
                    $task->notificationSubscribers->remove($notificationSubscriber);
                }
            }
            $task->save();
            $link = TasksUtil::getKanbanSubscriptionLink($task, 0);
            $this->assertContains('subscribe-task-link', $link);
        }

        /**
         * @covers resolveDetailSubscribeUrl
         */
        public function testResolveDetailSubscriptionLink()
        {
            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];
            if ($task->doNotificationSubscribersContainPerson(Yii::app()->user->userModel) === false)
            {
                $notificationSubscriber = new NotificationSubscriber();
                $notificationSubscriber->person = Yii::app()->user->userModel;
                $task->notificationSubscribers->add($notificationSubscriber);
                $task->save();
            }
            $link = TasksUtil::getDetailSubscriptionLink($task, 0);
            $this->assertContains('detail-unsubscribe-task-link', $link);
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('User');
            foreach ($task->notificationSubscribers as $index => $notificationSubscriber)
            {
                $user = $notificationSubscriber->person->castDown(array($modelDerivationPathToItem));
                if ($user->id == Yii::app()->user->userModel->id)
                {
                    $task->notificationSubscribers->remove($notificationSubscriber);
                }
            }
            $task->save();
            $link = TasksUtil::getDetailSubscriptionLink($task, 0);
            $this->assertContains('detail-subscribe-task-link', $link);
        }

        /**
         * @covers getTaskCompletionPercentage
         */
        public function testTaskCompletionPercentage()
        {
            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];
            $checkListItem = new TaskCheckListItem();
            $checkListItem->name = 'Test Item 1';
            $this->assertTrue($checkListItem->unrestrictedSave());
            $task->checkListItems->add($checkListItem);
            $task->save(false);

            $checkListItem = new TaskCheckListItem();
            $checkListItem->name = 'Test Item 2';
            $checkListItem->completed = true;
            $this->assertTrue($checkListItem->unrestrictedSave());
            $task->checkListItems->add($checkListItem);
            $task->save(false);

            $this->assertEquals(2, count($task->checkListItems));
            $percent = TasksUtil::getTaskCompletionPercentage($task);
            $this->assertEquals(50, $percent);
        }

        /**
         * @covers getDefaultTaskStatusForKanbanItemType
         */
        public function testGetDefaultTaskStatusForKanbanItemType()
        {
            $status = TasksUtil::getDefaultTaskStatusForKanbanItemType(KanbanItem::TYPE_SOMEDAY);
            $this->assertEquals(Task::STATUS_NEW, $status);
        }

        /**
         * @covers createKanbanItemFromTask
         */
        public function testCreateKanbanItemFromTask()
        {
            $task = TaskTestHelper::createTaskByNameForOwner('My Kanban Task', Yii::app()->user->userModel);
            $task->status = Task::STATUS_IN_PROGRESS;
            $accounts = Account::getByName('anAccount');
            $task->activityItems->add($accounts[0]);
            $this->assertTrue($task->save());
            $kanbanItem = TasksUtil::createKanbanItemFromTask($task);
            $this->assertEquals($kanbanItem->type, KanbanItem::TYPE_IN_PROGRESS);
        }

        /**
         * @covers renderCompletionProgressBarContent
         */
        public function testRenderCompletionProgressBarContent()
        {
            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];
            $this->assertEquals(2, count($task->checkListItems));
            $content = TasksUtil::renderCompletionProgressBarContent($task);
            $this->assertContains('completion-percentage-bar', $content);
        }

        /**
         * @covers getTaskCompletedCheckListItems
         */
        public function testGetTaskCompletedCheckListItems()
        {
            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];
            $this->assertEquals(2, count($task->checkListItems));
            $count = TasksUtil::getTaskCompletedCheckListItems($task);
            $this->assertEquals(1, $count);
        }

        /**
         * @covers renderCompletionDateTime
         */
        public function testRenderCompletionDateTime()
        {
            $tasks  = Task::getByName('MyTest');
            $task   = $tasks[0];
            $content = TasksUtil::renderCompletionDateTime($task);
            $this->assertContains('Completed On:', $content);
        }

        /**
         * @covers resolveFirstRelatedModel
         */
        public function testResolveFirstRelatedModel()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $accounts = Account::getByName('anAccount');

            $user                   = UserTestHelper::createBasicUser('Tilly');
            $dueStamp               = DateTimeUtil::convertTimestampToDbFormatDateTime(time()  + 10000);
            $task                   = new Task();
            $task->name             = 'MyFirstRelatedTask';
            $task->owner            = $user;
            $task->requestedByUser  = $user;
            $task->dueDateTime      = $dueStamp;
            $task->activityItems->add($accounts[0]);
            $this->assertTrue($task->save());
            $id = $task->id;
            unset($task);
            $task = Task::getById($id);
            $model = TasksUtil::resolveFirstRelatedModel($task);
            $this->assertEquals('anAccount', $model->name);
        }

        /**
         * @covers resolveFirstRelatedModel
         * @covers resolveFirstRelatedModelStringValue
         * @covers castDownActivityItem
         */
        public function testResolveFirstRelatedModelForProject()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $project = ProjectTestHelper::createProjectByNameForOwner('MyRelatedProject', Yii::app()->user->userModel);
            $dueStamp               = DateTimeUtil::convertTimestampToDbFormatDateTime(time()  + 10000);
            $task                   = new Task();
            $task->name             = 'MyFirstRelatedTask';
            $task->dueDateTime      = $dueStamp;
            $task->project          = $project;
            $this->assertTrue($task->save());
            $id = $task->id;
            unset($task);
            $task = Task::getById($id);
            $model = TasksUtil::resolveFirstRelatedModel($task);
            $this->assertEquals('MyRelatedProject', $model->name);
            $content = TasksUtil::resolveFirstRelatedModelStringValue($task);
            $this->assertEquals('MyRelatedProject', $content);
        }

        /**
         * @covers addSubscriber
         */
        public function testAddSubscriberToTask()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $user = User::getByUsername('tilly');
            $task = new Task();
            $task->name = 'MyTest';
            $task->owner = $user;
            $nowStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($task->save());
            $this->assertEquals($user, $task->owner);

            //There would be two here as default subscribers are added
            $this->assertEquals(2, count($task->notificationSubscribers));
            $user = Yii::app()->user->userModel;
            TasksUtil::addSubscriber($user, $task);
            $task->save();
            $this->assertEquals(2, count($task->notificationSubscribers));
        }

        /**
         * @covers sortKanbanColumnItems
         * @covers checkKanbanTypeByStatusAndUpdateIfRequired
         */
        public function testSortKanbanColumnItems()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $project                = ProjectTestHelper::createProjectByNameForOwner('MyKanbanProject', Yii::app()->user->userModel);
            $dueStamp               = DateTimeUtil::convertTimestampToDbFormatDateTime(time()  + 10000);

            //First kanban task
            $task                   = TaskTestHelper::createTaskByNameWithProjectAndStatus('MyFirstKanbanTask',
                                                                              Yii::app()->user->userModel,
                                                                              $project,
                                                                              Task::STATUS_IN_PROGRESS);
            $kanbanItem1            = KanbanItem::getByTask($task->id);
            $this->assertEquals(KanbanItem::TYPE_IN_PROGRESS, $kanbanItem1->type);
            $this->assertEquals($task->project->id, $kanbanItem1->kanbanRelatedItem->id);
            $task2                  = TaskTestHelper::createTaskByNameWithProjectAndStatus('MySecondKanbanTask',
                                                                              Yii::app()->user->userModel,
                                                                              $project,
                                                                              Task::STATUS_IN_PROGRESS);
            $kanbanItem2            = KanbanItem::getByTask($task2->id);
            $this->assertEquals(KanbanItem::TYPE_IN_PROGRESS, $kanbanItem2->type);
            $this->assertEquals($task2->project->id, $kanbanItem2->kanbanRelatedItem->id);
            $task3                  = TaskTestHelper::createTaskByNameWithProjectAndStatus('MyThirdKanbanTask',
                                                                              Yii::app()->user->userModel,
                                                                              $project,
                                                                              Task::STATUS_IN_PROGRESS);
            $kanbanItem3            = KanbanItem::getByTask($task3->id);
            $this->assertEquals(KanbanItem::TYPE_IN_PROGRESS, $kanbanItem3->type);
            $this->assertEquals($task3->project->id, $kanbanItem3->kanbanRelatedItem->id);
            $sourceKanbanType       = TasksUtil::resolveKanbanItemTypeForTaskStatus(Task::STATUS_IN_PROGRESS);
            TasksUtil::sortKanbanColumnItems($sourceKanbanType, $task->project);
            $kanbanItem             = KanbanItem::getByTask($task->id);
            $kanbanItem2            = KanbanItem::getByTask($task2->id);
            $kanbanItem3            = KanbanItem::getByTask($task3->id);
            $this->assertEquals($task->id, $kanbanItem->task->id);
            $this->assertEquals(1, $kanbanItem->sortOrder);
            $this->assertEquals($task2->id, $kanbanItem2->task->id);
            $this->assertEquals(2, $kanbanItem2->sortOrder);
            $this->assertEquals($task3->id, $kanbanItem3->task->id);
            $this->assertEquals(3, $kanbanItem3->sortOrder);

            //Update status and check checkKanbanTypeByStatusAndUpdateIfRequired
            $task->status          = Task::STATUS_NEW;
            $this->assertTrue($task->save());
            TasksUtil::checkKanbanTypeByStatusAndUpdateIfRequired($task);

            $kanbanItem             = KanbanItem::getByTask($task->id);
            $this->assertEquals(KanbanItem::TYPE_SOMEDAY, $kanbanItem->type);
            $kanbanItem2            = KanbanItem::getByTask($task2->id);
            $this->assertEquals(KanbanItem::TYPE_IN_PROGRESS, $kanbanItem2->type);
            $kanbanItem3            = KanbanItem::getByTask($task3->id);
            $this->assertEquals(KanbanItem::TYPE_IN_PROGRESS, $kanbanItem3->type);

            $this->assertEquals($task->id, $kanbanItem->task->id);
            $this->assertEquals(1, $kanbanItem->sortOrder);
            $this->assertEquals($task2->id, $kanbanItem2->task->id);
            $this->assertEquals(1, $kanbanItem2->sortOrder);
            $this->assertEquals($task3->id, $kanbanItem3->task->id);
            $this->assertEquals(2, $kanbanItem3->sortOrder);
        }

        /**
         * @covers processKanbanItemUpdateOnButtonAction
         */
        public function testProcessKanbanItemUpdateWithSourceKanbanTypeAsSomeDay()
        {
            $tasks          = Task::getByName('MyFirstKanbanTask');
            $task           = $tasks[0];
            $tasks          = Task::getByName('MySecondKanbanTask');
            $task2          = $tasks[0];
            $tasks          = Task::getByName('MyThirdKanbanTask');
            $task3          = $tasks[0];
            $kanbanItem     = KanbanItem::getByTask($task->id);
            $task->setScenario('kanbanViewButtonClick');
            TasksUtil::processKanbanItemUpdateOnButtonAction(Task::STATUS_IN_PROGRESS, $task->id, $kanbanItem->type);
            $kanbanItem             = KanbanItem::getByTask($task->id);
            $kanbanItem2            = KanbanItem::getByTask($task2->id);
            $kanbanItem3            = KanbanItem::getByTask($task3->id);
            $this->assertEquals($task->id, $kanbanItem->task->id);
            $this->assertEquals(3, $kanbanItem->sortOrder);
            $this->assertEquals($task2->id, $kanbanItem2->task->id);
            $this->assertEquals(1, $kanbanItem2->sortOrder);
            $this->assertEquals($task3->id, $kanbanItem3->task->id);
            $this->assertEquals(2, $kanbanItem3->sortOrder);
        }

        /**
         * @covers processKanbanItemUpdateOnButtonAction
         */
        public function testProcessKanbanItemUpdateWithSourceKanbanTypeAsInProgress()
        {
            $tasks          = Task::getByName('MyFirstKanbanTask');
            $task           = $tasks[0];
            $tasks          = Task::getByName('MySecondKanbanTask');
            $task2          = $tasks[0];
            $tasks          = Task::getByName('MyThirdKanbanTask');
            $task3          = $tasks[0];
            $kanbanItem2     = KanbanItem::getByTask($task2->id);
            $task2->setScenario('kanbanViewButtonClick');
            //Check for target status waiting for acceptance(should not change sort order)
            TasksUtil::processKanbanItemUpdateOnButtonAction(Task::STATUS_AWAITING_ACCEPTANCE, $task2->id, $kanbanItem2->type);
            $kanbanItem             = KanbanItem::getByTask($task->id);
            $kanbanItem2            = KanbanItem::getByTask($task2->id);
            $kanbanItem3            = KanbanItem::getByTask($task3->id);
            $this->assertEquals($task->id, $kanbanItem->task->id);
            $this->assertEquals(3, $kanbanItem->sortOrder);
            $this->assertEquals($task2->id, $kanbanItem2->task->id);
            $this->assertEquals(1, $kanbanItem2->sortOrder);
            $this->assertEquals($task3->id, $kanbanItem3->task->id);
            $this->assertEquals(2, $kanbanItem3->sortOrder);

            $task2->setScenario('kanbanViewButtonClick');
            //Check for target status rejected(should not change sort order)
            TasksUtil::processKanbanItemUpdateOnButtonAction(Task::STATUS_REJECTED, $task2->id, $kanbanItem2->type);
            $kanbanItem             = KanbanItem::getByTask($task->id);
            $kanbanItem2            = KanbanItem::getByTask($task2->id);
            $kanbanItem3            = KanbanItem::getByTask($task3->id);
            $this->assertEquals($task->id, $kanbanItem->task->id);
            $this->assertEquals(3, $kanbanItem->sortOrder);
            $this->assertEquals($task2->id, $kanbanItem2->task->id);
            $this->assertEquals(1, $kanbanItem2->sortOrder);
            $this->assertEquals($task3->id, $kanbanItem3->task->id);
            $this->assertEquals(2, $kanbanItem3->sortOrder);

            $task2->setScenario('kanbanViewButtonClick');
            //Check for target status in progress(should not change sort order)
            TasksUtil::processKanbanItemUpdateOnButtonAction(Task::STATUS_IN_PROGRESS, $task2->id, $kanbanItem2->type);
            $kanbanItem             = KanbanItem::getByTask($task->id);
            $kanbanItem2            = KanbanItem::getByTask($task2->id);
            $kanbanItem3            = KanbanItem::getByTask($task3->id);
            $this->assertEquals($task->id, $kanbanItem->task->id);
            $this->assertEquals(3, $kanbanItem->sortOrder);
            $this->assertEquals($task2->id, $kanbanItem2->task->id);
            $this->assertEquals(1, $kanbanItem2->sortOrder);
            $this->assertEquals($task3->id, $kanbanItem3->task->id);
            $this->assertEquals(2, $kanbanItem3->sortOrder);

            $task2->setScenario('kanbanViewButtonClick');
            //Check for target status completed(should change sort order)
            TasksUtil::processKanbanItemUpdateOnButtonAction(Task::STATUS_COMPLETED, $task2->id, $kanbanItem2->type);
            $kanbanItem             = KanbanItem::getByTask($task->id);
            $kanbanItem2            = KanbanItem::getByTask($task2->id);
            $kanbanItem3            = KanbanItem::getByTask($task3->id);
            $this->assertEquals($task->id, $kanbanItem->task->id);
            $this->assertEquals(2, $kanbanItem->sortOrder);
            $this->assertEquals($task2->id, $kanbanItem2->task->id);
            $this->assertEquals(KanbanItem::TYPE_COMPLETED, $kanbanItem2->type);
            $this->assertEquals(1, $kanbanItem2->sortOrder);
            $this->assertEquals($task3->id, $kanbanItem3->task->id);
            $this->assertEquals(1, $kanbanItem3->sortOrder);
        }

        /**
         * @covers resolveAndRenderTaskCardDetailsSubscribersContent
         */
        public function testResolveAndRenderTaskCardDetailsSubscribersContent()
        {
            $hellodear      = UserTestHelper::createBasicUser('hellodear');
            $task           = new Task();
            $task->name     = 'MyCardTest';
            $task->owner    = $hellodear;
            $this->assertTrue($task->save());

            $task = Task::getById($task->id);
            $user = Yii::app()->user->userModel;
            TasksUtil::addSubscriber($hellodear, $task);
            $this->assertTrue($task->save());
            $content = TasksUtil::resolveAndRenderTaskCardDetailsSubscribersContent($task);
            $this->assertContains('gravatar', $content);
            $this->assertContains('users/default/details', $content);
            $this->assertContains('hellodear', $content);
            $this->assertContains('task-owner', $content);
        }
    }
?>