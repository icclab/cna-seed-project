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

    class TaskTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            AccountTestHelper::createAccountByNameForOwner('anAccount', $super);
        }

        public static function getDependentTestModelClassNames()
        {
            return array('TestManyManyRelationToItemModel');
        }

        public function testCreateTaskWithZerosStampAndEditAgain()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $task                       = new Task();
            $task->name                 = 'My Task';
            $task->owner                = Yii::app()->user->userModel;
            $task->requestedByUser      = Yii::app()->user->userModel;
            $task->completedDateTime    = '0000-00-00 00:00:00';
            $saved = $task->save();
            $this->assertTrue($saved);
            $taskId = $task->id;
            $task->forget();
            unset($task);

            $task       = Task::getById($taskId);
            $task->name ='something new';
            $saved      = $task->save();
            $this->assertTrue($saved);

            $task->delete();
        }

        /**
         * @depends testCreateTaskWithZerosStampAndEditAgain
         */
        public function testCreateAndGetTaskById()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $accounts = Account::getByName('anAccount');

            $user                   = UserTestHelper::createBasicUser('Billy');
            $dueStamp               = DateTimeUtil::convertTimestampToDbFormatDateTime(time()  + 10000);
            $task                   = new Task();
            $task->name             = 'MyTask';
            $task->owner            = $user;
            $task->requestedByUser  = $user;
            $task->dueDateTime      = $dueStamp;
            $task->status           = Task::STATUS_COMPLETED;
            $task->description      = 'my test description';
            $taskCheckListItem      = new TaskCheckListItem();
            $taskCheckListItem->name = 'Test Check List Item';
            $task->checkListItems->add($taskCheckListItem);
            $task->activityItems->add($accounts[0]);
            $this->assertTrue($task->save());
            $id = $task->id;
            unset($task);
            $task = Task::getById($id);
            $this->assertEquals('MyTask',              $task->name);
            $this->assertEquals($dueStamp,             $task->dueDateTime);
            $this->assertNotNull($task->completedDateTime);
            $this->assertEquals('my test description', $task->description);
            $this->assertEquals($user,                 $task->owner);
            $this->assertEquals($user,                 $task->requestedByUser);
            $this->assertEquals(1, $task->activityItems->count());
            $this->assertEquals($accounts[0], $task->activityItems->offsetGet(0));
            $this->assertEquals($taskCheckListItem, $task->checkListItems->offsetGet(0));
            foreach ($task->activityItems as $existingItem)
            {
                $castedDownModel = $existingItem->castDown(array('Account')); //this should not fail
            }
        }

        /**
         * @depends testCreateAndGetTaskById
         */
        public function testAddingActivityItemThatShouldCastDownAndThrowException()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $accounts = Account::getByName('anAccount');
            $accountId = $accounts[0]->id;
            $accounts[0]->forget();
            $task = new Task();
            $task->activityItems->add(Account::getById($accountId));
            foreach ($task->activityItems as $existingItem)
            {
                try
                {
                    $castedDownModel = $existingItem->castDown(array(array('SecurableItem', 'OwnedSecurableItem', 'Account'))); //this should not fail
                }
                catch (NotFoundException $e)
                {
                    $this->fail();
                }
            }
            foreach ($task->activityItems as $existingItem)
            {
                try
                {
                    $castedDownModel = $existingItem->castDown(array(array('SecurableItem', 'OwnedSecurableItem', 'Person', 'Contact'))); //this should fail
                    $this->fail();
                }
                catch (NotFoundException $e)
                {
                }
            }
        }

        /**
         * @depends testCreateAndGetTaskById
         */
        public function testGetLabel()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $tasks = Task::getByName('MyTask');
            $this->assertEquals(1, count($tasks));
            $this->assertEquals('Task',   $tasks[0]::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Tasks',  $tasks[0]::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testGetLabel
         */
        public function testGetTasksByNameForNonExistentName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $tasks = Task::getByName('Test Task 69');
            $this->assertEquals(0, count($tasks));
        }

        /**
         * @depends testCreateAndGetTaskById
         */
        public function testUpdateTaskFromForm()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $user = User::getByUsername('billy');
            $tasks = Task::getByName('MyTask');
            $task = $tasks[0];
            $this->assertEquals($task->name, 'MyTask');
            $postData = array(
                'owner' => array(
                    'id' => $user->id,
                ),
                'name' => 'New Name',
                'dueDateTime' => '', //setting dueDate to a blank value.
            );
            $sanitizedPostData = PostUtil::sanitizePostByDesignerTypeForSavingModel($task, $postData);
            $task->setAttributes($sanitizedPostData);
            $this->assertTrue($task->save());
            $id = $task->id;
            unset($task);
            $task = Task::getById($id);
            $this->assertEquals('New Name', $task->name);
            $this->assertEquals(null,     $task->dueDateTime);

            //create new task from scratch where the DateTime attributes are not populated. It should let you save.
            $task = new Task();
            $postData = array(
                'owner' => array(
                    'id' => $user->id,
                ),
                'name' => 'Lamazing',
                'dueDateTime' => '', //setting dueDate to a blank value.
            );
            $sanitizedPostData = PostUtil::sanitizePostByDesignerTypeForSavingModel($task, $postData);
            $task->setAttributes($sanitizedPostData);
            $this->assertTrue($task->save());
            $id = $task->id;
            unset($task);
            $task = Task::getById($id);
            $this->assertEquals('Lamazing', $task->name);
            $this->assertEquals(null,     $task->dueDateTime);
        }

        /**
         * @depends testUpdateTaskFromForm
         */
        public function testDeleteTask()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $tasks = Task::getAll();
            $this->assertEquals(2, count($tasks));
            $tasks[0]->delete();
            $tasks = Task::getAll();
            $this->assertEquals(1, count($tasks));
        }

        public function testManyToManyRelationInTheMiddleOfTheInheritanceHierarchy()
        {
           Yii::app()->user->userModel = User::getByUsername('super');
            $accounts = Account::getByName('anAccount');

            $possibleDerivationPaths = array(
                                           array('SecurableItem', 'OwnedSecurableItem', 'Account'),
                                           array('SecurableItem', 'OwnedSecurableItem', 'Person', 'Contact'),
                                           array('SecurableItem', 'OwnedSecurableItem', 'Opportunity'),
                                       );

            $model = new TestManyManyRelationToItemModel();
            $model->items->add($accounts[0]);
            $this->assertTrue($model->save());

            $item = Item::getById($model->items[0]->getClassId('Item'));
            $this->assertTrue ($item instanceof Item);
            $this->assertFalse($item instanceof Account);
            $this->assertTrue ($item->isSame($accounts[0]));
            $account2 = $item->castDown($possibleDerivationPaths);
            $this->assertTrue ($account2->isSame($accounts[0]));

            $id = $model->id;
            unset($model);
            RedBeanModel::forgetAll();

            $model = TestManyManyRelationToItemModel::getById($id);
            $this->assertEquals(1, $model->items->count());
            $this->assertTrue ($model->items[0] instanceof Item);
            $this->assertFalse($model->items[0] instanceof Account);
            $this->assertTrue ($model->items[0]->isSame($accounts[0]));
            $account3 = $model->items[0]->castDown($possibleDerivationPaths);
            $this->assertTrue ($account3->isSame($accounts[0]));
        }

        /**
         * @depends testDeleteTask
         * @covers beforeSave::resolveStatusAndSetCompletedFields
         */
        public function testAutomatedCompletedDateTimeAndLatestDateTimeChanges()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            //Creating a new task that is not completed. LatestDateTime should default to now, and
            //completedDateTime should be null.
            $task = new Task();
            $task->name = 'aTest';
            $nowStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($task->save());
            $this->assertEquals(null, $task->completedDateTime);

            //Modify the task. Complete the task. The CompletedDateTime should show as now.
            $task = Task::getById($task->id);
            $this->assertFalse((bool)$task->completed);
            $this->assertEquals(null, $task->completedDateTime);
            $task->status = Task::STATUS_COMPLETED;
            $this->assertTrue($task->save());
            $this->assertTrue((bool)$task->completed);
            $this->assertNotNull($task->completedDateTime);
        }

        public function testGetModelClassNames()
        {
            $modelClassNames = TasksModule::getModelClassNames();
            $this->assertEquals(2, count($modelClassNames));
            $this->assertEquals('Task', $modelClassNames[0]);
        }

        /**
         * @covers beforeSave::resolveAndSetDefaultSubscribers
         */
        public function testAddSubscriberToTask()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $user = User::getByUsername('billy');
            $task = new Task();
            $task->name = 'MyTest';
            $task->owner = $user;
            $nowStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($task->save());
            $this->assertEquals($user, $task->owner);

            //There would be two here as default subscribers are added
            $this->assertEquals(2, count($task->notificationSubscribers));
            $user = Yii::app()->user->userModel;
            $notificationSubscriber = new NotificationSubscriber();
            $notificationSubscriber->person = $user;
            $notificationSubscriber->hasReadLatest = false;
            $task->notificationSubscribers->add($notificationSubscriber);
            $this->assertTrue($task->save());

            $task = Task::getById($task->id);
            $subscriber = $task->notificationSubscribers->offsetGet(0);
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('User');
            $subscribedUser = $subscriber->person->castDown(array($modelDerivationPathToItem));
            $this->assertEquals($user, $subscribedUser);
            $this->assertEquals(3, count($task->notificationSubscribers));
        }

        public function testAddCheckListItemsToTask()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $task = new Task();
            $task->name = 'MyTest1';
            $nowStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($task->save());

            $taskCheckListItem            = new TaskCheckListItem();
            $taskCheckListItem->name      = 'Test Check List Item1';
            $taskCheckListItem->completed = true;
            $task->checkListItems->add($taskCheckListItem);

            $taskCheckListItem2       = new TaskCheckListItem();
            $taskCheckListItem2->name = 'Test Check List Item2';
            $task->checkListItems->add($taskCheckListItem2);
            $this->assertTrue($task->save());

            $task = Task::getById($task->id);
            $this->assertEquals(2, $task->checkListItems->count());
            $fetchedCheckListItem = $task->checkListItems[0];
            $this->assertEquals('Test Check List Item1', $fetchedCheckListItem->name);
            $this->assertTrue((bool)$fetchedCheckListItem->completed);
            $task->checkListItems->remove($taskCheckListItem2);
            $this->assertTrue($task->save());
            $task = Task::getById($task->id);
            $this->assertEquals(1, $task->checkListItems->count());
        }

        public function testAddCommentsToTask()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $task = new Task();
            $task->name = 'MyTest2';
            $nowStamp = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $this->assertTrue($task->save());

            $comment                = new Comment();
            $comment->description   = 'My Description';
            $task->comments->add($comment);
            $this->assertTrue($task->save());

            $task = Task::getById($task->id);
            $this->assertEquals(1, $task->comments->count());
            $fetchedComment = $task->comments[0];
            $this->assertEquals('My Description', $fetchedComment->description);
        }

        /**
         * @covers doNotificationSubscribersContainPerson
         */
        public function testDoNotificationSubscribersContainPerson()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $tasks = Task::getByName('MyTest');
            $isContained = $tasks[0]->doNotificationSubscribersContainPerson(Yii::app()->user->userModel);
            $this->assertTrue($isContained);
        }

        public function testProjectValidationWithCustomPickList()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            //Create a required picklist for projects
            ModulesSearchWithDataProviderTestHelper::createDropDownAttribute(new Project(), 'pick', true);
            $task = new Task();
            $task->name = 'MyTestWithCustomPickList';
            $this->assertTrue($task->save());
            $this->assertCount(0, $task->getErrors());
        }

        public function testAfterSaveForImportModel()
        {
            $testUser                   = UserTestHelper::createBasicUserWithEmailAddress('jimmy');
            UserConfigurationFormAdapter::setValue($testUser, false, 'turnOffEmailNotifications');
            Yii::app()->user->userModel = User::getByUsername('super');
            $this->assertCount(0, EmailMessage::getAll());
            $task                       = new Task();
            $task->name                 = 'MyTaskWithoutImport';
            $task->owner                = $testUser;
            $task->requestedByUser      = Yii::app()->user->userModel;
            $this->assertTrue($task->save());
            $this->assertEquals(2, count($task->notificationSubscribers));
            $this->assertCount(1, EmailMessage::getAll());

            //With import
            $task                       = new Task();
            $task->setScenario('importModel');
            $task->name                 = 'MyTaskWithImport';
            $task->owner                = $testUser;
            $task->requestedByUser      = Yii::app()->user->userModel;
            $this->assertTrue($task->save());
            $this->assertEquals(2, count($task->notificationSubscribers));
            $this->assertCount(1, EmailMessage::getAll());
        }
    }
?>
