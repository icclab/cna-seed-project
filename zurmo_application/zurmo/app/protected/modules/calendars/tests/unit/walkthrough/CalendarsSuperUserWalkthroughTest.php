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

    class CalendarsSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            ProductTestHelper::createProductByNameForOwner('My First Prod', Yii::app()->user->userModel);
            ProductTestHelper::createProductByNameForOwner('My Second Prod', Yii::app()->user->userModel);
            CalendarTestHelper::createSavedCalendarByName("My Cal 1", '#315AB0');
            CalendarTestHelper::createSavedCalendarByName("My Cal 2", '#66367b');
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $this->runControllerWithRedirectExceptionAndGetContent('calendars/default/details');
            $this->runControllerWithNoExceptionsAndGetContent('calendars/default/combinedDetails');

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $calendars          = SavedCalendar::getAll();
            $this->assertEquals(2, count($calendars));
            $superCalId     = self::getModelIdByModelNameAndName('SavedCalendar', 'My Cal 1');
            $superCalId2    = self::getModelIdByModelNameAndName('SavedCalendar', 'My Cal 2');

            $this->setGetArray(array('id' => $superCalId));
            $this->runControllerWithNoExceptionsAndGetContent('calendars/default/edit');
            //Save Calendar
            SavedCalendar::getById($superCalId);
            $this->setPostArray(array('SavedCalendar' => array('name' => 'My New Cal 1')));

            //Test having a failed validation on the saved calendar during save.
            $this->setGetArray (array('id'      => $superCalId));
            $this->setPostArray(array('SavedCalendar' => array('name' => ''),
                                      'ajax' => 'edit-form',
                                      'RowsAndColumnsReportWizardForm' => array('filtersStructure' => '',
                                                                                'Filters' => array(
                                                                                                    array('attributeIndexOrDerivedType' => 'createdDateTime',
                                                                                                    'structurePosition'  => '1',
                                                                                                    'valueType'          => 'After',
                                                                                                    'value'              => '2/18/2014',
                                                                                                    'availableAtRunTime' => '0')
                                                                                                  ))));
            $content = $this->runControllerWithExitExceptionAndGetContent('calendars/default/edit');
            $this->assertContains('Name cannot be blank', $content);

            //Filter validation
            $this->setGetArray (array('id'      => $superCalId));
            $this->setPostArray(array('SavedCalendar' => array('name' => 'Test'),
                                      'ajax' => 'edit-form',
                                      'RowsAndColumnsReportWizardForm' => array('filtersStructure' => '1',
                                                                                'Filters' => array(
                                                                                                    array('attributeIndexOrDerivedType' => 'createdDateTime',
                                                                                                    'structurePosition'  => '1',
                                                                                                    'valueType'          => 'After',
                                                                                                    'value'              => '',
                                                                                                    'availableAtRunTime' => '0')
                                                                                                  ))));
            $content = $this->runControllerWithExitExceptionAndGetContent('calendars/default/edit');
            $this->assertContains('Value cannot be blank', $content);

            //Valid case
            $this->setGetArray (array('id'      => $superCalId));
            $this->setPostArray(array('SavedCalendar' => array('name' => 'My New Cal 1'),
                                      'ajax' => 'edit-form',
                                      'RowsAndColumnsReportWizardForm' => array('filtersStructure' => '1',
                                                                                'Filters' => array(
                                                                                                    array('attributeIndexOrDerivedType' => 'createdDateTime',
                                                                                                    'structurePosition'  => '1',
                                                                                                    'valueType'          => 'After',
                                                                                                    'value'              => '2/18/2014',
                                                                                                    'availableAtRunTime' => '0')
                                                                                                  ))));
            $this->runControllerWithExitExceptionAndGetContent('calendars/default/edit');

            //Load Model Detail Views
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('calendars/default/details');

            $this->resetGetArray();
            $this->runControllerWithNoExceptionsAndGetContent('calendars/default/modalList');

            //Month view
            $this->setGetArray (array('selectedMyCalendarIds'      => $superCalId . ',' . $superCalId2, // Not Coding Standard
                                      'selectedSharedCalendarIds'  => null,
                                      'startDate'                  => CalendarUtil::getStartDate(SavedCalendar::DATERANGE_TYPE_MONTH),
                                      'endDate'                    => CalendarUtil::getEndDate(SavedCalendar::DATERANGE_TYPE_MONTH),
                                      'dateRangeType'              => SavedCalendar::DATERANGE_TYPE_MONTH));
            $content = $this->runControllerWithNoExceptionsAndGetContent('calendars/default/getEvents');
            $this->assertContains('My First Prod', $content);

            //Week view
            $this->setGetArray (array('selectedMyCalendarIds'      => $superCalId . ',' . $superCalId2, // Not Coding Standard
                                      'selectedSharedCalendarIds'  => null,
                                      'startDate'                  => CalendarUtil::getStartDate(SavedCalendar::DATERANGE_TYPE_WEEK),
                                      'endDate'                    => CalendarUtil::getEndDate(SavedCalendar::DATERANGE_TYPE_WEEK),
                                      'dateRangeType'              => SavedCalendar::DATERANGE_TYPE_WEEK));
            $content = $this->runControllerWithNoExceptionsAndGetContent('calendars/default/getEvents');
            $this->assertContains('My First Prod', $content);

            //Day view
            $this->setGetArray (array('selectedMyCalendarIds'      => $superCalId . ',' . $superCalId2, // Not Coding Standard
                                      'selectedSharedCalendarIds'  => null,
                                      'startDate'                  => CalendarUtil::getStartDate(SavedCalendar::DATERANGE_TYPE_DAY),
                                      'endDate'                    => CalendarUtil::getEndDate(SavedCalendar::DATERANGE_TYPE_DAY),
                                      'dateRangeType'              => SavedCalendar::DATERANGE_TYPE_DAY));
            $this->runControllerWithNoExceptionsAndGetContent('calendars/default/getEvents');
            $this->runControllerWithNoExceptionsAndGetContent('calendars/default/getDayEvents');

            $this->setGetArray (array('modelClass'  => 'Product', // Not Coding Standard
                                      'modelId'     => $superCalId));
            $this->runControllerWithNoExceptionsAndGetContent('calendars/default/getCalendarItemDetail');
            //Add subscribed calendar
            $user = UserTestHelper::createBasicUser('jim');
            $subscribedCalendar = CalendarTestHelper::createSavedCalendarByName("My Subscribed Cal", '#315AB0');
            $subscribedCalendar->owner = $user;
            $subscribedCalendar->save();
            $this->setGetArray (array('id' => $subscribedCalendar->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('calendars/default/addSubsriptionForCalendar');
            $this->assertContains('My Subscribed Cal', $content);

            $subscribedCalendars = CalendarUtil::getUserSubscribedCalendars($super);
            $this->setGetArray (array('id' => $subscribedCalendars[0]->id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('calendars/default/unsubscribe');
            $this->assertNotContains('My Subscribed Cal', $content);
        }

        public function testSuperUserDeleteAction()
        {
            $super                      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $super;

            $calendar                   = CalendarTestHelper::createSavedCalendarByName("My Cal 3", '#66367b');

            //Delete a calendar
            $this->setGetArray(array('id' => $calendar->id));
            $this->resetPostArray();
            $calendars                  = SavedCalendar::getAll();
            $this->assertEquals(4, count($calendars));
            $this->runControllerWithNoExceptionsAndGetContent('calendars/default/delete');
            $calendars                  = SavedCalendar::getAll();
            $this->assertEquals(3, count($calendars));
            try
            {
                SavedCalendar::getById($calendar->id);
                $this->fail();
            }
            catch (NotFoundException $e)
            {
                //success
            }
        }
    }
?>