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
     * Class ContactLatestActivityDateTimeObserverTest
     * @see LatestActivityDateTimeDocumentationTest for more related tests
     */
    class ContactLatestActivityDateTimeObserverTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testResolveItemToContactAndPopulateLatestActivityDateTime()
        {
            $contact = ContactTestHelper::createContactByNameForOwner('abc', Yii::app()->user->userModel);
            $this->assertNull($contact->latestActivityDateTime);
            $task    = TaskTestHelper::createTaskByNameForOwner('task1', Yii::app()->user->userModel);
            $task->activityItems->add($contact);
            $this->assertTrue($task->save());
            $this->assertNull($task->activityItems[0]->latestActivityDateTime);
            $taskId = $task->id;
            $contactId = $contact->id;
            $task->forget();
            $contact->forget();

            //Retrieve the task, so the related activity item is an Item and needs to be casted down
            $task = Task::getById($taskId);
            $item = $task->activityItems[0];
            $dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            ContactLatestActivityDateTimeObserver::resolveItemToModelAndPopulateLatestActivityDateTime(
                            $item, $dateTime, 'Contact');
            $item->forget();

            $contact = Contact::getById($contactId);
            $this->assertEquals($dateTime, $contact->latestActivityDateTime);
        }

        /**
         * Test with a related account as the activity item, in which case nothing will get updated
         */
        public function testResolveItemToContactAndPopulateLatestActivityDateTimeWithRelatedAccount()
        {
            $account = AccountTestHelper::createAccountByNameForOwner('Account 1', Yii::app()->user->userModel);
            $task    = TaskTestHelper::createTaskByNameForOwner('task2', Yii::app()->user->userModel);
            $task->activityItems->add($account);
            $this->assertTrue($task->save());
            $this->assertNull($task->activityItems[0]->latestActivityDateTime);
            $taskId = $task->id;
            $task->forget();
            $account->forget();

            //Retrieve the task, so the related activity item is an Item and needs to be casted down
            $task = Task::getById($taskId);
            $item = $task->activityItems[0];
            $dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            ContactLatestActivityDateTimeObserver::resolveItemToModelAndPopulateLatestActivityDateTime(
                            $item, $dateTime, 'Contact');
        }

        public function testResolveRelatedContactsAndSetLatestActivityDateTime()
        {
            $contact = ContactTestHelper::createContactByNameForOwner('contact2', Yii::app()->user->userModel);
            $this->assertNull($contact->latestActivityDateTime);
            $contact2 = ContactTestHelper::createContactByNameForOwner('contact3', Yii::app()->user->userModel);
            $this->assertNull($contact2->latestActivityDateTime);
            $task    = TaskTestHelper::createTaskByNameForOwner('task3', Yii::app()->user->userModel);
            $task->activityItems->add($contact);
            $task->activityItems->add($contact2);
            $this->assertTrue($task->save());
            $this->assertNull($task->activityItems[0]->latestActivityDateTime);
            $this->assertNull($task->activityItems[1]->latestActivityDateTime);
            $taskId = $task->id;
            $contactId = $contact->id;
            $contact2Id = $contact2->id;
            $task->forget();
            $contact->forget();
            $contact2->forget();

            //Retrieve the task, so the related activity item is an Item and needs to be casted down
            $task = Task::getById($taskId);
            $dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            ContactLatestActivityDateTimeObserver::resolveRelatedModelsAndSetLatestActivityDateTime(
                            $task->activityItems, $dateTime, 'Contact');
            $task->forget();

            $contact = Contact::getById($contactId);
            $this->assertEquals($dateTime, $contact->latestActivityDateTime);
            $contact2 = Contact::getById($contact2Id);
            $this->assertEquals($dateTime, $contact2->latestActivityDateTime);
        }
    }
?>
