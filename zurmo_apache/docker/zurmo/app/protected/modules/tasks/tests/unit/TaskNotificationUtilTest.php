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

    class TaskNotificationUtilTest extends ZurmoBaseTest
    {
        protected static $super;

        protected static $steve;

        protected static $sally;

        protected static $katie;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            Yii::app()->user->userModel = SecurityTestHelper::createSuperAdmin();
            self::$super = Yii::app()->user->userModel;
            self::$steve = UserTestHelper::createBasicUserWithEmailAddress('steve');
            self::$sally = UserTestHelper::createBasicUserWithEmailAddress('sally');
            self::$katie = UserTestHelper::createBasicUserWithEmailAddress('katie');
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
            EmailMessage::deleteAll();
        }

        /**
         * Owner should not receive email because owner is the current user
         */
        public function testTaskNewNotificationWhenOwnerIsCurrentUser()
        {
            $task                       = new Task();
            $task->name                 = 'My Task';
            $task->owner                = Yii::app()->user->userModel;
            $task->requestedByUser      = Yii::app()->user->userModel;
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertTrue($task->save());
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
        }

        public function testTaskNewNotificationWhenOwnerIsNotCurrentUser()
        {
            $task                       = new Task();
            $task->name                 = 'Her Task';
            $task->owner                = self::$sally;
            $task->requestedByUser      = Yii::app()->user->userModel;
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertTrue($task->save());
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $emailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertCount(1, $emailMessages);
            $this->assertEquals(1,                   $emailMessages[0]->recipients->count());
            $this->assertEquals('sally@zurmo.com',   $emailMessages[0]->recipients[0]->toAddress);
            $this->assertContains('configurationEdit?id=' . self::$sally->id . '">Manage your email preferences', $emailMessages[0]->content->htmlContent);
        }

        public function testTaskStatusBecomesInProgressNotificationWhenUsersAreNotCurrentUser()
        {
            $task                       = new Task();
            $task->name                 = 'Her Task';
            $task->owner                = self::$sally;
            $task->requestedByUser      = self::$katie;
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertTrue($task->save());
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            EmailMessage::deleteAll();
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            //Now change status
            $task->status               = Task::STATUS_IN_PROGRESS;
            $this->assertTrue($task->save());
            //No emails should be queued up
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
        }

        public function testTaskStatusBecomesAwaitingAcceptanceNotificationWhenRequestedByUserIsCurrentUser()
        {
            $task                       = new Task();
            $task->name                 = 'Her Task';
            $task->owner                = self::$sally;
            $task->requestedByUser      = Yii::app()->user->userModel;
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertTrue($task->save());
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            EmailMessage::deleteAll();
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            //Now change status
            $task->status               = Task::STATUS_AWAITING_ACCEPTANCE;
            $this->assertTrue($task->save());
            //No emails should be queued up
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
        }

        public function testTaskStatusBecomesAwaitingAcceptanceNotificationWhenRequestedByUserIsNotCurrentUser()
        {
            $task                       = new Task();
            $task->name                 = 'Her Task';
            $task->owner                = self::$sally;
            $task->requestedByUser      = self::$katie;
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertTrue($task->save());
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            EmailMessage::deleteAll();
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            //Now change status
            $task->status               = Task::STATUS_AWAITING_ACCEPTANCE;
            $this->assertTrue($task->save());
            //One email should be queued up
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $emailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertCount(1, $emailMessages);
            $this->assertEquals(1,                   $emailMessages[0]->recipients->count());
            $this->assertEquals('katie@zurmo.com',   $emailMessages[0]->recipients[0]->toAddress);
        }

        public function testTaskStatusBecomesAcceptedWithNoExtraSubscribers()
        {
            $task                       = new Task();
            $task->name                 = 'Her Task';
            $task->owner                = self::$sally;
            $task->requestedByUser      = self::$katie;
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertTrue($task->save());
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            EmailMessage::deleteAll();
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            //Now change status
            $task->status               = Task::STATUS_COMPLETED;
            $this->assertTrue($task->save());
            $this->assertEquals(2, Yii::app()->emailHelper->getQueuedCount());
            $emailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertCount(2, $emailMessages);
            $this->assertTrue('katie@zurmo.com' == $emailMessages[0]->recipients[0]->toAddress ||
                              'katie@zurmo.com' == $emailMessages[1]->recipients[0]->toAddress);
            $this->assertTrue('sally@zurmo.com' == $emailMessages[0]->recipients[0]->toAddress ||
                              'sally@zurmo.com' == $emailMessages[1]->recipients[0]->toAddress);
        }

        public function testTaskStatusBecomesAcceptedWithExtraSubscribers()
        {
            $task                       = new Task();
            $task->name                 = 'Her Task';
            $task->owner                = self::$sally;
            $task->requestedByUser      = self::$katie;
            $notificationSubscriber = new NotificationSubscriber();
            $notificationSubscriber->person = self::$steve;
            $notificationSubscriber->hasReadLatest = false;
            $task->notificationSubscribers->add($notificationSubscriber);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertTrue($task->save());
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            EmailMessage::deleteAll();
            $taskId = $task->id;
            $task->forget();
            $task = Task::getById($taskId);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            //Now change status
            $task->status               = Task::STATUS_COMPLETED;
            $this->assertTrue($task->save());
            $this->assertEquals(3, Yii::app()->emailHelper->getQueuedCount());
            $emailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertCount(3, $emailMessages);
            $this->assertTrue('katie@zurmo.com' == $emailMessages[0]->recipients[0]->toAddress ||
                              'katie@zurmo.com' == $emailMessages[1]->recipients[0]->toAddress ||
                              'katie@zurmo.com' == $emailMessages[2]->recipients[0]->toAddress);
            $this->assertTrue('sally@zurmo.com' == $emailMessages[0]->recipients[0]->toAddress ||
                              'sally@zurmo.com' == $emailMessages[1]->recipients[0]->toAddress ||
                              'sally@zurmo.com' == $emailMessages[2]->recipients[0]->toAddress);
            $this->assertTrue('steve@zurmo.com' == $emailMessages[0]->recipients[0]->toAddress ||
                              'steve@zurmo.com' == $emailMessages[1]->recipients[0]->toAddress ||
                              'steve@zurmo.com' == $emailMessages[2]->recipients[0]->toAddress);
        }

        public function testTaskStatusBecomesAcceptedWhenOwnerIsCurrentUser()
        {
            $task                       = new Task();
            $task->name                 = 'My Acceptance Task';
            $task->owner                = self::$sally;
            $task->requestedByUser      = self::$katie;
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertTrue($task->save());
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            EmailMessage::deleteAll();
            $taskId = $task->id;
            $task->forget();
            $task = Task::getById($taskId);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            //Now change the logged in user
            Yii::app()->user->userModel = self::$sally;
            //Now change status
            $task->status               = Task::STATUS_COMPLETED;
            $this->assertTrue($task->save());
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $emailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertCount(1, $emailMessages);
            $this->assertTrue('katie@zurmo.com' == $emailMessages[0]->recipients[0]->toAddress ||
                              'katie@zurmo.com' == $emailMessages[1]->recipients[0]->toAddress ||
                              'katie@zurmo.com' == $emailMessages[2]->recipients[0]->toAddress);
        }

        public function testTaskAddCommentWithExtraSubscribers()
        {
            $task                       = new Task();
            $task->name                 = 'Her Task';
            $task->owner                = self::$sally;
            $task->requestedByUser      = self::$katie;
            $notificationSubscriber = new NotificationSubscriber();
            $notificationSubscriber->person = self::$steve;
            $notificationSubscriber->hasReadLatest = false;
            $task->notificationSubscribers->add($notificationSubscriber);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertTrue($task->save());
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            EmailMessage::deleteAll();
            $taskId = $task->id;
            $task->forget();
            $task = Task::getById($taskId);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            //Now add comment
            $comment = new Comment();
            $comment->description = 'some comment';
            $task->comments->add($comment);
            $this->assertTrue($task->save());
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            TasksNotificationUtil::submitTaskNotificationMessage($task, TasksNotificationUtil::TASK_NEW_COMMENT,
                                                                 self::$super, $comment);

            $this->assertEquals(3, Yii::app()->emailHelper->getQueuedCount());
            $emailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertCount(3, $emailMessages);
            $this->assertTrue('katie@zurmo.com' == $emailMessages[0]->recipients[0]->toAddress ||
                              'katie@zurmo.com' == $emailMessages[1]->recipients[0]->toAddress ||
                              'katie@zurmo.com' == $emailMessages[2]->recipients[0]->toAddress);
            $this->assertTrue('sally@zurmo.com' == $emailMessages[0]->recipients[0]->toAddress ||
                              'sally@zurmo.com' == $emailMessages[1]->recipients[0]->toAddress ||
                              'sally@zurmo.com' == $emailMessages[2]->recipients[0]->toAddress);
            $this->assertTrue('steve@zurmo.com' == $emailMessages[0]->recipients[0]->toAddress ||
                              'steve@zurmo.com' == $emailMessages[1]->recipients[0]->toAddress ||
                              'steve@zurmo.com' == $emailMessages[2]->recipients[0]->toAddress);
        }

        public function testTaskStatusBecomesRejectedNotificationWhenOwnerIsCurrentUser()
        {
            $task                       = new Task();
            $task->name                 = 'Reject Task';
            $task->requestedByUser      = self::$sally;
            $task->owner                = Yii::app()->user->userModel;
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertTrue($task->save());
            //Now change status
            $task->status               = Task::STATUS_REJECTED;
            $this->assertTrue($task->save());
            //No emails should be queued up
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
        }

        public function testTaskStatusBecomesRejectedNotificationWhenOwnerIsNotCurrentUser()
        {
            $task                       = new Task();
            $task->name                 = 'RejectOwner Task';
            $task->requestedByUser      = self::$sally;
            $task->owner                = self::$katie;
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertTrue($task->save());
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            EmailMessage::deleteAll();
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            //Now change status
            $task->status               = Task::STATUS_REJECTED;
            $this->assertTrue($task->save());
            //One email should be queued up
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $emailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertCount(1, $emailMessages);
            $this->assertEquals(1,                   $emailMessages[0]->recipients->count());
            $this->assertEquals('katie@zurmo.com',   $emailMessages[0]->recipients[0]->toAddress);
        }
    }
?>