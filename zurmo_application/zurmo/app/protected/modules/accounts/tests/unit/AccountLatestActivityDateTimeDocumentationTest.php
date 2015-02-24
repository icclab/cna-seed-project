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
     * You can account Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address account@zurmo.com.
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
     * Class AccountLatestActivityDateTimeDocumentationTest
     * @see AccountLatestActivityDateTimeObserverTest
     */
    class AccountLatestActivityDateTimeDocumentationTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function createTaskWithRelatedAccount($firstName, $taskName)
        {
            $account = AccountTestHelper::createAccountByNameForOwner($firstName, Yii::app()->user->userModel);
            $this->assertNull($account->latestActivityDateTime);
            $task    = TaskTestHelper::createTaskByNameForOwner($taskName, Yii::app()->user->userModel);
            $task->activityItems->add($account);
            $this->assertTrue($task->save());
            $this->assertNull($task->activityItems[0]->latestActivityDateTime);
            $taskId = $task->id;
            $accountId = $account->id;
            $task->forget();
            $account->forget();
            return array($taskId, $accountId);
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testUpdateLatestActivityDateTimeWhenATaskIsCompleted()
        {
            $taskIdAndAccountId = $this->createTaskWithRelatedAccount('account1', 'task1');
            $task               = Task::getById($taskIdAndAccountId[0]);
            //update task status to STATUS_AWAITING_ACCEPTANCE, it should not update related account
            $task->status = Task::STATUS_AWAITING_ACCEPTANCE;
            $this->assertTrue($task->save());
            $account            = Account::getById($taskIdAndAccountId[0]);
            $this->assertNull($account->latestActivityDateTime);
            //update task status to STATUS_COMPLETED, now it should update the related account
            $task->status = Task::STATUS_COMPLETED;
            $this->assertTrue($task->save());
            $account            = Account::getById($taskIdAndAccountId[0]);
            $this->assertNotNull($account->latestActivityDateTime);
            $dateTimeAMinuteAgo     = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - 60);
            $dateTimeAMinuteFromNow = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 60);
            $this->assertTrue($account->latestActivityDateTime > $dateTimeAMinuteAgo);
            $this->assertTrue($account->latestActivityDateTime < $dateTimeAMinuteFromNow);
        }

        public function testUpdateLatestActivityDateTimeWhenANoteIsCreated()
        {
            $account = AccountTestHelper::createAccountByNameForOwner('account2', Yii::app()->user->userModel);
            $this->assertNull($account->latestActivityDateTime);
            $note = new Note();
            $note->owner               = Yii::app()->user->userModel;
            $note->description         = 'aNote';
            $note->activityItems->add($account);
            $this->assertTrue($note->save());
            $this->assertNotNull($note->activityItems[0]->latestActivityDateTime);
            $dateTimeAMinuteAgo     = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - 60);
            $dateTimeAMinuteFromNow = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 60);
            $this->assertTrue($note->activityItems[0]->latestActivityDateTime > $dateTimeAMinuteAgo);
            $this->assertTrue($note->activityItems[0]->latestActivityDateTime < $dateTimeAMinuteFromNow);

            //Change note name, and confirm the latestActivityDateTime does not update
            $oldDateTime       = $note->activityItems[0]->latestActivityDateTime;
            $note->description = 'aNoteAlso';
            $this->assertTrue($note->save());
            $this->assertEquals($oldDateTime, $note->activityItems[0]->latestActivityDateTime);
        }

        public function testUpdateLatestActivityDateTimeWhenAnEmailIsSentOrArchived()
        {
            $emailMessage = EmailMessageTestHelper::createDraftSystemEmail('subject 1', Yii::app()->user->userModel);
            $account3     = AccountTestHelper::createAccountByNameForOwner('account3', Yii::app()->user->userModel);
            $account4     = AccountTestHelper::createAccountByNameForOwner('account4', Yii::app()->user->userModel);
            $account5     = AccountTestHelper::createAccountByNameForOwner('account4', Yii::app()->user->userModel);
            $dateTime     = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $account5->setLatestActivityDateTime($dateTime);
            $this->assertTrue($account5->save());
            $account3Id   = $account3->id;
            $account4Id   = $account4->id;
            $account5Id   = $account5->id;
            $this->assertNull($account3->latestActivityDateTime);
            $this->assertNull($account4->latestActivityDateTime);
            $this->assertEquals($dateTime, $account5->latestActivityDateTime);
            $emailMessage->sender->personsOrAccounts->add($account3);
            $emailMessage->recipients[0]->personsOrAccounts->add($account4);
            $emailMessage->recipients[0]->personsOrAccounts->add($account5);
            $this->assertTrue($emailMessage->save());
            $this->assertNull($account3->latestActivityDateTime);
            $this->assertNull($account4->latestActivityDateTime);
            $this->assertEquals($dateTime, $account5->latestActivityDateTime);
            $emailMessageId = $emailMessage->id;
            $emailMessage->forget();
            $account3->forget();
            $account4->forget();
            $account5->forget();

            //Retrieve email message and set sentDateTime, at this point the accounts should update with this value
            $sentDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - 86400);
            $emailMessage = EmailMessage::getById($emailMessageId);
            $emailMessage->sentDateTime = $sentDateTime;
            $this->assertTrue($emailMessage->save());
            $account3 = Account::getById($account3Id);
            $account4 = Account::getById($account4Id);
            $account5 = Account::getById($account5Id);
            $this->assertEquals($sentDateTime, $account3->latestActivityDateTime);
            $this->assertEquals($sentDateTime, $account4->latestActivityDateTime);
            $this->assertEquals($dateTime, $account5->latestActivityDateTime);
        }

        public function testUpdateLatestActivityDateTimeWhenAMeetingIsInThePast()
        {
            $meeting = new Meeting();
            $meeting->name = 'my meeting';
            $meeting->startDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - 86400);
            $this->assertNull($meeting->processedForLatestActivity);
            $meeting->processedForLatestActivity = true;
            $this->assertTrue($meeting->save());
            $this->assertTrue($meeting->processedForLatestActivity == false);
            $meeting->processedForLatestActivity = true;
            $this->assertTrue($meeting->save());
            $this->assertTrue($meeting->processedForLatestActivity == true);
        }
    }
?>
