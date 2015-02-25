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
     * Task module walkthrough tests.
     */
    class TaskAjaxSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected static $super;

        protected static $myUser;

        protected static $sally;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            self::$super  = User::getByUsername('super');
            self::$myUser = UserTestHelper::createBasicUser('myuser');
            self::$sally = UserTestHelper::createBasicUser('sally');
            Yii::app()->user->userModel = self::$super;
            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', self::$super);
        }

        public function testInlineCreateCommentFromAjax()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $task = new Task();
            $task->name = 'aTest';
            $nowStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($task->save());

            $this->setGetArray(array('id' => $task->id, 'uniquePageId' => 'CommentInlineEditForModelView'));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/inlineCreateCommentFromAjax');
        }

        /**
         * @depends testInlineCreateCommentFromAjax
         */
        public function testUpdateDueDateTimeViaAjax()
        {
            $super  = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $tasks  = Task::getByName('aTest');
            $task   = $tasks[0];
            $taskId = $task->id;
            $this->setGetArray(array('id' => $task->id, 'dateTime' => '7/23/13 12:00 am'));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/updateDueDateTimeViaAjax', true);
            $task   = Task::getById($taskId);
            $displayDateTime = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($task->dueDateTime);
            $this->assertEquals('7/23/13 12:00 AM', $displayDateTime);
        }

        /**
         * @depends testInlineCreateCommentFromAjax
         */
        public function testAddAndRemoveSubscriberViaAjaxAsSuperUser()
        {
            //Login with super and check subscribe unsubscribe from modal detail view when super
            //is not owner or requested by user
            $super              = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $task               = new Task();
            $task->name         = 'SubscriberTask';
            $task->owner        = self::$sally;
            $task->requestedByUser = self::$myUser;
            $this->assertTrue($task->save());
            $this->assertEquals(2, $task->notificationSubscribers->count());
            $this->setGetArray(array('id' => $task->id));
            $this->assertFalse($task->doNotificationSubscribersContainPerson($super));
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/addSubscriber', false);
            $this->assertContains('gravatar', $content);
            $this->assertContains('users/default/details', $content);
            $this->assertContains($super->getFullName(), $content);
            $this->assertEquals(3, $task->notificationSubscribers->count());

            $this->setGetArray(array('id' => $task->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/removeSubscriber', false);
            $this->assertNotContains($super->getFullName(), $content);
            $this->assertEquals(2, $task->notificationSubscribers->count());
            $task->owner        = $super;
            $this->assertTrue($task->save());
            $this->assertEquals(3, $task->notificationSubscribers->count());

            //Super user is owner so even if it is removed, it would be restored
            $this->setGetArray(array('id' => $task->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/removeSubscriber', false);
            $this->assertContains($super->getFullName(), $content);
            $this->assertEquals(3, $task->notificationSubscribers->count());
        }

        public function testAddAndRemoveSubscriberViaAjaxWithNormalUser()
        {
            //Adk Jason as why permission error is coming up here
            $sally              = $this->logoutCurrentUserLoginNewUserAndGetByUsername('sally');
            $task               = new Task();
            $task->name         = 'NewSubscriberTask';
            $task->owner        = $sally;
            $task->requestedByUser = self::$myUser;
            $this->assertTrue($task->save());
            $this->setGetArray(array('id' => $task->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/removeSubscriber');
            $this->setGetArray(array('id' => $task->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/addSubscriber');

            //Now test peon with elevated rights to accounts
            $sally->setRight('TasksModule', TasksModule::RIGHT_ACCESS_TASKS);
            $sally->setRight('TasksModule', TasksModule::RIGHT_CREATE_TASKS);
            $sally->setRight('TasksModule', TasksModule::RIGHT_DELETE_TASKS);
            $this->assertTrue($sally->save());
            $task->addPermissions($sally, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($task->save());
            AllPermissionsOptimizationUtil::securableItemGivenPermissionsForUser($task, $sally);

            //Test nobody with elevated rights.
            Yii::app()->user->userModel = User::getByUsername('sally');

            $this->setGetArray(array('id' => $task->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/removeSubscriber', false);
            $this->assertContains($sally->getFullName(), $content);
            $this->assertEquals(2, $task->notificationSubscribers->count());

            //Now super user would be added as a subscriber as he becomes the owner
            $task->owner        = self::$super;
            $this->assertTrue($task->save());

            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/removeSubscriber', false);
            $this->assertNotContains($sally->getFullName(), $content);
            $this->assertEquals(2, $task->notificationSubscribers->count());

            $isSallyFound = $this->checkIfUserFoundInSubscribersList($task, $sally->id);
            $this->assertFalse($isSallyFound);

            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/addSubscriber', false);
            $this->assertContains($sally->getFullName(), $content);
            $this->assertEquals(3, $task->notificationSubscribers->count());

            $isSallyFound = $this->checkIfUserFoundInSubscribersList($task, $sally->id);
            $this->assertTrue($isSallyFound);
        }

        public function testAddAndRemoveKanbanSubscriberViaAjaxAsSuperUser()
        {
            //Login with super and check subscribe unsubscribe from modal detail view when super
            //is not owner or requested by user
            $super              = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $task               = new Task();
            $task->name         = 'KanbanSubscriberTask';
            $task->owner        = self::$sally;
            $task->requestedByUser = self::$myUser;
            $this->assertTrue($task->save());
            $this->assertEquals(2, $task->notificationSubscribers->count());
            $this->setGetArray(array('id' => $task->id));
            $this->assertFalse($task->doNotificationSubscribersContainPerson($super));
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/addKanbanSubscriber', false);
            $this->assertContains('gravatar', $content);
            $this->assertContains('users/default/details', $content);
            $this->assertContains($super->getFullName(), $content);
            $this->assertEquals(3, $task->notificationSubscribers->count());

            $this->setGetArray(array('id' => $task->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/removeKanbanSubscriber', false);
            $this->assertNotContains($super->getFullName(), $content);
            $this->assertEquals(2, $task->notificationSubscribers->count());
            $task->owner        = $super;
            $this->assertTrue($task->save());
            $this->assertEquals(3, $task->notificationSubscribers->count());

            //Super user is owner so even if it is removed, it would be restored
            $this->setGetArray(array('id' => $task->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/removeKanbanSubscriber', false);
            $this->assertContains($super->getFullName(), $content);
            $this->assertEquals(3, $task->notificationSubscribers->count());
        }

        public function testAddAndRemoveKanbanSubscriberViaAjaxWithNormalUser()
        {
            //Adk Jason as why permission error is coming up here
            $myuser              = $this->logoutCurrentUserLoginNewUserAndGetByUsername('myuser');
            $task               = new Task();
            $task->name         = 'NewKanbanSubscriberTask';
            $task->owner        = $myuser;
            $task->requestedByUser = self::$sally;
            $this->assertTrue($task->save());
            $this->setGetArray(array('id' => $task->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/removeKanbanSubscriber');
            $this->setGetArray(array('id' => $task->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('tasks/default/addKanbanSubscriber');

            //Now test peon with elevated rights to accounts
            $myuser->setRight('TasksModule', TasksModule::RIGHT_ACCESS_TASKS);
            $myuser->setRight('TasksModule', TasksModule::RIGHT_CREATE_TASKS);
            $myuser->setRight('TasksModule', TasksModule::RIGHT_DELETE_TASKS);
            $this->assertTrue($myuser->save());
            $task->addPermissions($myuser, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($task->save());
            AllPermissionsOptimizationUtil::securableItemGivenPermissionsForUser($task, $myuser);

            //Test nobody with elevated rights.
            Yii::app()->user->userModel = User::getByUsername('myuser');

            $this->setGetArray(array('id' => $task->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/removeKanbanSubscriber', false);
            $this->assertContains($myuser->getFullName(), $content);
            $this->assertEquals(2, $task->notificationSubscribers->count());

            //Now super user would be added as a subscriber as he becomes the owner
            $task->owner        = self::$super;
            $this->assertTrue($task->save());

            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/removeKanbanSubscriber', false);
            $this->assertNotContains($myuser->getFullName(), $content);
            $this->assertEquals(2, $task->notificationSubscribers->count());
            $isMyUserFound = $this->checkIfUserFoundInSubscribersList($task, $myuser->id);
            $this->assertFalse($isMyUserFound);

            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/addKanbanSubscriber', false);
            $this->assertContains($myuser->getFullName(), $content);
            $this->assertEquals(3, $task->notificationSubscribers->count());
            $isMyUserFound = $this->checkIfUserFoundInSubscribersList($task, $myuser->id);
            $this->assertTrue($isMyUserFound);
        }

        /**
         * @depends testInlineCreateCommentFromAjax
         */
        public function testSuperUserModalAllDefaultFromRelationAction()
        {
            $super              = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $accountId          = self::getModelIdByModelNameAndName('Account', 'superAccount');
            $this->setGetArray(array(
                                      'relationAttributeName'   => 'Account',
                                      'relationModelId'         => $accountId,
                                      'relationModuleId'        => 'accounts',
                                      'modalId'                 => 'relatedModalContainer-tasks',
                                      'portletId'               => '12',
                                      'uniqueLayoutId'          => 'AccountDetailsAndRelationsView_12'
                                    ));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/modalCreateFromRelation');
            $tasks              = Task::getAll();
            $this->assertEquals(5, count($tasks));
            $this->setGetArray(array(
                                      'relationAttributeName'   => 'Account',
                                      'relationModelId'         => $accountId,
                                      'relationModuleId'        => 'accounts',
                                      'portletId'               => '12',
                                      'uniqueLayoutId'          => 'AccountDetailsAndRelationsView_12'
                                    ));
            $this->setPostArray(array(
                                       'Task'   => array('name'              => 'Task for test cases'),
                                       'ActivityItemForm' => array('Account' => array('id' => $accountId))
            ));

            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/modalSaveFromRelation');
            $this->assertContains('Task for test cases', $content);
            $tasks              = Task::getAll();
            $this->assertEquals(6, count($tasks));

            $this->setGetArray(array(
                                    'id' => $tasks[5]->id
                                    )
                              );
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/modalDetails');
            $this->assertContains('Task for test cases', $content);

            $this->setGetArray(array(
                                    'id'  => $tasks[5]->id
                              ));
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/modalEdit');

            $this->setGetArray(array(
                                    'id'  => $tasks[5]->id
                              ));
            unset($_POST['Task']);
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/modalCopy');
            $this->assertContains('Task for test cases', $content);
        }

        public function testUpdateStatusOnDragInKanbanView()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $project = ProjectTestHelper::createProjectByNameForOwner('a new project', $super);
            $task = TaskTestHelper::createTaskByNameForOwner('My Kanban Task', Yii::app()->user->userModel);
            $task->project = $project;
            $task->status = Task::STATUS_IN_PROGRESS;
            $taskId = $task->id;
            $this->assertTrue($task->save());

            $task1 = TaskTestHelper::createTaskByNameForOwner('My Kanban Task 1', Yii::app()->user->userModel);
            $task1->project = $project;
            $task1->status = Task::STATUS_NEW;
            $this->assertTrue($task1->save());
            $task1Id = $task1->id;
            $taskArray = array($task, $task1);

            foreach ($taskArray as $row => $data)
            {
                $kanbanItem  = KanbanItem::getByTask($data->id);
                if ($kanbanItem == null)
                {
                    //Create KanbanItem here
                    $kanbanItem = TasksUtil::createKanbanItemFromTask($data);
                }
                $kanbanItemsArray[] = $kanbanItem;
            }
            $this->assertEquals(KanbanItem::TYPE_SOMEDAY, $kanbanItemsArray[1]->type);
            $this->assertEquals(1, $kanbanItemsArray[1]->sortOrder);
            $this->assertEquals(1, $kanbanItemsArray[0]->sortOrder);

            $this->setGetArray(array('items' => array($task1->id, $task->id), 'type' => KanbanItem::TYPE_IN_PROGRESS));
            $content = $this->runControllerWithNoExceptionsAndGetContent('tasks/default/updateStatusOnDragInKanbanView', false);
            $contentArray = CJSON::decode($content);
            $this->assertContains('Finish', $contentArray['button']);
            $task1 = Task::getById($task1Id);
            $this->assertEquals(Task::STATUS_IN_PROGRESS, $task1->status);
            $kanbanItem = KanbanItem::getByTask($task1Id);
            $this->assertEquals(KanbanItem::TYPE_IN_PROGRESS, $kanbanItem->type);

            $kanbanItem = KanbanItem::getByTask($taskId);
            $this->assertEquals(2, $kanbanItem->sortOrder);
        }

        /**
         * @depends testUpdateStatusOnDragInKanbanView
         */
        public function testUpdateStatusInKanbanView()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $tasks = Task::getByName('My Kanban Task');
            $task  = $tasks[0];
            $taskId = $task->id;
            $this->setGetArray(array('targetStatus' => Task::STATUS_AWAITING_ACCEPTANCE,
                                     'taskId' => $task->id,
                                     'sourceKanbanType' => KanbanItem::TYPE_IN_PROGRESS));
            $this->runControllerWithNoExceptionsAndGetContent('tasks/default/updateStatusInKanbanView', false);
            $task = Task::getById($taskId);
            $this->assertEquals(Task::STATUS_AWAITING_ACCEPTANCE, $task->status);
        }

        private function checkIfUserFoundInSubscribersList($task, $compareId)
        {
            $isUserFound = false;
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('User');
            foreach ($task->notificationSubscribers as $subscriber)
            {
                $user     = $subscriber->person->castDown(array($modelDerivationPathToItem));
                if ($user->id == $compareId)
                {
                    $isUserFound = true;
                }
            }
            return $isUserFound;
        }
   }
?>