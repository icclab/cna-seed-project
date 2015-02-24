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

    class UpdateModelsLatestActivityDateTimeByMeetingJobTest extends ZurmoBaseTest
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

        public function createMeetingWithRelatedContact($firstName, $meetingName, $usePastStartDateTime = false)
        {
            $contact = ContactTestHelper::createContactByNameForOwner($firstName, Yii::app()->user->userModel);
            $this->assertNull($contact->latestActivityDateTime);
            $meeting    = MeetingTestHelper::createMeetingByNameForOwner($meetingName, Yii::app()->user->userModel);
            $meeting->activityItems->add($contact);
            if ($usePastStartDateTime)
            {
                $meeting->startDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - 10000);
            }
            $this->assertTrue($meeting->save());
            $this->assertNull($meeting->activityItems[0]->latestActivityDateTime);
            $meetingId = $meeting->id;
            $contactId = $contact->id;
            $meeting->forget();
            $contact->forget();
            return array($meetingId, $contactId);
        }

        public function testRunWhereStartDateTimesAreInTheFutureForAContact()
        {
            $meetingIdAndContactId1 = $this->createMeetingWithRelatedContact('first name1', 'meeting 1');
            $meetingIdAndContactId2 = $this->createMeetingWithRelatedContact('first name2', 'meeting 2');
            $meetingIdAndContactId3 = $this->createMeetingWithRelatedContact('first name3', 'meeting 3');
            $contact1 = Contact::getById($meetingIdAndContactId1[1]);
            $contact2 = Contact::getById($meetingIdAndContactId2[1]);
            $contact3 = Contact::getById($meetingIdAndContactId3[1]);
            $this->assertNull($contact1->latestActivityDateTime);
            $this->assertNull($contact2->latestActivityDateTime);
            $this->assertNull($contact3->latestActivityDateTime);

            $models = UpdateModelsLatestActivityDateTimeByMeetingJob::getModelsToProcess(20);
            $this->assertEquals(0, count($models));

            $job = new UpdateModelsLatestActivityDateTimeByMeetingJob();
            $this->assertTrue($job->run());
            $this->assertNull($contact1->latestActivityDateTime);
            $this->assertNull($contact2->latestActivityDateTime);
            $this->assertNull($contact3->latestActivityDateTime);
        }

        public function testRunWhereStartDateTimesAreInThePastForAContact()
        {
            $meetingIdAndContactId4 = $this->createMeetingWithRelatedContact('first name4', 'meeting 4', true);
            $meetingIdAndContactId5 = $this->createMeetingWithRelatedContact('first name5', 'meeting 5', true);
            $meetingIdAndContactId6 = $this->createMeetingWithRelatedContact('first name6', 'meeting 6', true);
            $meeting4  = Meeting::getById($meetingIdAndContactId4[0]);
            $meeting5  = Meeting::getById($meetingIdAndContactId5[0]);
            $meeting6  = Meeting::getById($meetingIdAndContactId6[0]);
            $this->assertTrue($meeting4->processedForLatestActivity == false);
            $this->assertTrue($meeting5->processedForLatestActivity == false);
            $this->assertTrue($meeting6->processedForLatestActivity == false);
            $contact4 = Contact::getById($meetingIdAndContactId4[1]);
            $contact5 = Contact::getById($meetingIdAndContactId5[1]);
            $contact6 = Contact::getById($meetingIdAndContactId6[1]);
            $this->assertNull($contact4->latestActivityDateTime);
            $this->assertNull($contact5->latestActivityDateTime);
            $this->assertNull($contact6->latestActivityDateTime);

            $models = UpdateModelsLatestActivityDateTimeByMeetingJob::getModelsToProcess(20);
            $this->assertEquals(3, count($models));
            $dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $job      = new UpdateModelsLatestActivityDateTimeByMeetingJob();
            $this->assertTrue($job->run());
            $this->assertTrue($meeting4->processedForLatestActivity == true);
            $this->assertTrue($meeting5->processedForLatestActivity == true);
            $this->assertTrue($meeting6->processedForLatestActivity == true);
            $this->assertTrue(!empty($contact4->latestActivityDateTime));
            $this->assertTrue(!empty($contact5->latestActivityDateTime));
            $this->assertTrue(!empty($contact6->latestActivityDateTime));
            $this->assertTrue($contact4->latestActivityDateTime < $dateTime);
            $this->assertTrue($contact5->latestActivityDateTime < $dateTime);
            $this->assertTrue($contact6->latestActivityDateTime < $dateTime);
        }

        public function createMeetingWithRelatedAccount($firstName, $meetingName, $usePastStartDateTime = false)
        {
            $account = AccountTestHelper::createAccountByNameForOwner($firstName, Yii::app()->user->userModel);
            $this->assertNull($account->latestActivityDateTime);
            $meeting    = MeetingTestHelper::createMeetingByNameForOwner($meetingName, Yii::app()->user->userModel);
            $meeting->activityItems->add($account);
            if ($usePastStartDateTime)
            {
                $meeting->startDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - 10000);
            }
            $this->assertTrue($meeting->save());
            $this->assertNull($meeting->activityItems[0]->latestActivityDateTime);
            $meetingId = $meeting->id;
            $accountId = $account->id;
            $meeting->forget();
            $account->forget();
            return array($meetingId, $accountId);
        }

        public function testRunWhereStartDateTimesAreInTheFutureForAnAccount()
        {
            $meetingIdAndAccountId1 = $this->createMeetingWithRelatedAccount('first name1', 'meeting 1');
            $meetingIdAndAccountId2 = $this->createMeetingWithRelatedAccount('first name2', 'meeting 2');
            $meetingIdAndAccountId3 = $this->createMeetingWithRelatedAccount('first name3', 'meeting 3');
            $account1 = Account::getById($meetingIdAndAccountId1[1]);
            $account2 = Account::getById($meetingIdAndAccountId2[1]);
            $account3 = Account::getById($meetingIdAndAccountId3[1]);
            $this->assertNull($account1->latestActivityDateTime);
            $this->assertNull($account2->latestActivityDateTime);
            $this->assertNull($account3->latestActivityDateTime);

            $models = UpdateModelsLatestActivityDateTimeByMeetingJob::getModelsToProcess(20);
            $this->assertEquals(0, count($models));

            $job = new UpdateModelsLatestActivityDateTimeByMeetingJob();
            $this->assertTrue($job->run());
            $this->assertNull($account1->latestActivityDateTime);
            $this->assertNull($account2->latestActivityDateTime);
            $this->assertNull($account3->latestActivityDateTime);
        }

        public function testRunWhereStartDateTimesAreInThePastForAnAccount()
        {
            $meetingIdAndAccountId4 = $this->createMeetingWithRelatedAccount('first name4', 'meeting 4', true);
            $meetingIdAndAccountId5 = $this->createMeetingWithRelatedAccount('first name5', 'meeting 5', true);
            $meetingIdAndAccountId6 = $this->createMeetingWithRelatedAccount('first name6', 'meeting 6', true);
            $meeting4  = Meeting::getById($meetingIdAndAccountId4[0]);
            $meeting5  = Meeting::getById($meetingIdAndAccountId5[0]);
            $meeting6  = Meeting::getById($meetingIdAndAccountId6[0]);
            $this->assertTrue($meeting4->processedForLatestActivity == false);
            $this->assertTrue($meeting5->processedForLatestActivity == false);
            $this->assertTrue($meeting6->processedForLatestActivity == false);
            $account4 = Account::getById($meetingIdAndAccountId4[1]);
            $account5 = Account::getById($meetingIdAndAccountId5[1]);
            $account6 = Account::getById($meetingIdAndAccountId6[1]);
            $this->assertNull($account4->latestActivityDateTime);
            $this->assertNull($account5->latestActivityDateTime);
            $this->assertNull($account6->latestActivityDateTime);

            $models = UpdateModelsLatestActivityDateTimeByMeetingJob::getModelsToProcess(20);
            $this->assertEquals(3, count($models));
            $dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $job      = new UpdateModelsLatestActivityDateTimeByMeetingJob();
            $this->assertTrue($job->run());
            $this->assertTrue($meeting4->processedForLatestActivity == true);
            $this->assertTrue($meeting5->processedForLatestActivity == true);
            $this->assertTrue($meeting6->processedForLatestActivity == true);
            $this->assertTrue(!empty($account4->latestActivityDateTime));
            $this->assertTrue(!empty($account5->latestActivityDateTime));
            $this->assertTrue(!empty($account6->latestActivityDateTime));
            $this->assertTrue($account4->latestActivityDateTime < $dateTime);
            $this->assertTrue($account5->latestActivityDateTime < $dateTime);
            $this->assertTrue($account6->latestActivityDateTime < $dateTime);
        }
    }
?>