<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    class CalendarItemsDataProviderTest extends DataProviderBaseTest
    {
        protected $super;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $account = AccountTestHelper::createAccountByNameForOwner('anAccount', $super);
            MeetingTestHelper::createCategories();
            MeetingTestHelper::createMeetingWithOwnerAndRelatedAccount('aMeeting', $super, $account);
            $savedCalendar                      = new SavedCalendar();
            $savedCalendar->name                = 'aSavedCalendar';
            $savedCalendar->timeZone            = 'America/Chicago';
            $savedCalendar->location            = 'Newyork';
            $savedCalendar->moduleClassName     = 'MeetingsModule';
            $savedCalendar->startAttributeName  = 'startDateTime';
            $savedCalendar->endAttributeName    = 'endDateTime';
            $savedCalendar->color               = '#c05d91';
            $savedCalendar->owner               = $super;
            $savedCalendar->save();
        }

        public function setUp()
        {
            $this->super = User::getByUsername('super');
            Yii::app()->user->userModel = $this->super;
        }

        public function testMeetingsThanSpanForMoreThanOneMonth()
        {
            $savedCalendars = SavedCalendar::getByName('aSavedCalendar');
            $savedCalendar  = $savedCalendars[0];
            $meetings = Meeting::getByName('aMeeting');
            $meeting  = $meetings[0];
            $meeting->startDateTime = '2014-04-29 01:00:00';
            $meeting->endDateTime   = '2014-05-09 01:00:00';
            $this->assertTrue($meeting->save());
            $savedCalendarSubscriptions = SavedCalendarSubscriptions::makeByUser($this->super, (string) $savedCalendar->id);
            $dataProvider = new CalendarItemsDataProvider($savedCalendarSubscriptions,
                                                          array('startDate'     => '2014-05-01 01:00:00',
                                                                'endDate'       => '2014-05-31 01:00:00',
                                                                'dateRangeType' => SavedCalendar::DATERANGE_TYPE_MONTH));
            $data = $dataProvider->getData();
            $this->assertCount(1, $data);
            $this->assertEquals('aMeeting', $data[0]->getTitle());
            $meeting->startDateTime = '2014-04-29 01:00:00';
            $meeting->endDateTime   = '2014-06-09 01:00:00';
            $this->assertTrue($meeting->save());
            $savedCalendarSubscriptions = SavedCalendarSubscriptions::makeByUser($this->super, (string) $savedCalendar->id);
            $dataProvider = new CalendarItemsDataProvider($savedCalendarSubscriptions,
                                                          array('startDate'     => '2014-05-01 01:00:00',
                                                                'endDate'       => '2014-05-31 01:00:00',
                                                                'dateRangeType' => SavedCalendar::DATERANGE_TYPE_MONTH));
            $data = $dataProvider->getData();
            $this->assertCount(1, $data);
            $this->assertEquals('aMeeting', $data[0]->getTitle());
            $meeting->startDateTime = '2014-05-29 01:00:00';
            $meeting->endDateTime   = '2014-06-09 01:00:00';
            $this->assertTrue($meeting->save());
            $savedCalendarSubscriptions = SavedCalendarSubscriptions::makeByUser($this->super, (string) $savedCalendar->id);
            $dataProvider = new CalendarItemsDataProvider($savedCalendarSubscriptions,
                                                         array('startDate'     => '2014-05-01 01:00:00',
                                                               'endDate'       => '2014-05-31 01:00:00',
                                                               'dateRangeType' => SavedCalendar::DATERANGE_TYPE_MONTH));
            $data = $dataProvider->getData();
            $this->assertCount(1, $data);
            $this->assertEquals('aMeeting', $data[0]->getTitle());
            //Meeting start and ends before calendar start/end dates
            $savedCalendarSubscriptions = SavedCalendarSubscriptions::makeByUser($this->super, (string) $savedCalendar->id);
            $dataProvider = new CalendarItemsDataProvider($savedCalendarSubscriptions,
                array('startDate'     => '2014-07-01 01:00:00',
                    'endDate'       => '2014-08-31 01:00:00',
                    'dateRangeType' => SavedCalendar::DATERANGE_TYPE_MONTH));
            $data = $dataProvider->getData();
            $this->assertCount(0, $data);
            //Meeting start and ends after calendar start/end dates
            $savedCalendarSubscriptions = SavedCalendarSubscriptions::makeByUser($this->super, (string) $savedCalendar->id);
            $dataProvider = new CalendarItemsDataProvider($savedCalendarSubscriptions,
                array('startDate'     => '2014-01-01 01:00:00',
                    'endDate'       => '2014-03-31 01:00:00',
                    'dateRangeType' => SavedCalendar::DATERANGE_TYPE_MONTH));
            $data = $dataProvider->getData();
            $this->assertCount(0, $data);
        }
    }
?>