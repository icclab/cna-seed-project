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

    class SavedCalendarTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateAndGetSavedCalendarById()
        {
            $calendar              = CalendarTestHelper::createSavedCalendarByName('My first calendar', '#ccccc');

            $id                    = $calendar->id;
            $calendar->forget();
            unset($calendar);
            $calendar              = SavedCalendar::getById($id);
            $this->assertEquals('My first calendar', $calendar->name);
            $this->assertEquals('America/Chicago', $calendar->timeZone);
            $this->assertEquals('Newyork', $calendar->location);
            $this->assertEquals('ProductsModule', $calendar->moduleClassName);
            $this->assertEquals('createdDateTime', $calendar->startAttributeName);
            $this->assertEquals('#ccccc', $calendar->color);
        }

        /**
         * @depends testCreateAndGetSavedCalendarById
         */
        public function testGetSavedCalendarsByName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $savedCalendars             = SavedCalendar::getByName('My first calendar');
            $this->assertEquals(1, count($savedCalendars));
            $this->assertEquals('My first calendar', $savedCalendars[0]->name);
        }

        /**
         * @depends testCreateAndGetSavedCalendarById
         */
        public function testGetLabel()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $savedCalendars             = SavedCalendar::getByName('My first calendar');
            $this->assertEquals(1, count($savedCalendars));
            $this->assertEquals('Calendar',  $savedCalendars[0]::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Calendars', $savedCalendars[0]::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testGetSavedCalendarsByName
         */
        public function testGetSavedCalendarsByNameForNonExistentName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $savedCalendars           = SavedCalendar::getByName('Red Widget 1');
            $this->assertEquals(0, count($savedCalendars));
        }

        /**
         * @depends testCreateAndGetSavedCalendarById
         */
        public function testGetAll()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $savedCalendars           = SavedCalendar::getAll();
            $this->assertEquals(1, count($savedCalendars));
        }

        /**
         * @covers SavedCalendarToReportAdapter::makeReportBySavedCalendar
         */
        public function testMakeReportBySavedCalendar()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $savedCalendars           = SavedCalendar::getAll();
            $savedCalendar            = $savedCalendars[0];
            $savedCalendar->serializedData = 'a:2:{s:7:"Filters";a:1:{i:0;a:5:{s:17:"structurePosition";s:1:"1";s:27:"attributeIndexOrDerivedType";s:15:"createdDateTime";s:9:"valueType";s:5:"After";s:5:"value";s:10:"2014-01-18";s:18:"availableAtRunTime";s:1:"0";}}s:16:"filtersStructure";s:1:"1";}';
            $report = SavedCalendarToReportAdapter::makeReportBySavedCalendar($savedCalendars[0]);
            $this->assertEquals($report->getDescription(), $savedCalendars[0]->description);
            $this->assertEquals($report->getName(), $savedCalendars[0]->name);
            $this->assertEquals($report->getModuleClassName(), $savedCalendars[0]->moduleClassName);
            $this->assertEquals($report->getOwner(), $savedCalendars[0]->owner);
            $this->assertEquals($report->getType(), Report::TYPE_ROWS_AND_COLUMNS);
            $this->assertEquals($report->getFiltersStructure(), "1");
        }

        public function testDeleteSavedCalendar()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $savedCalendars           = SavedCalendar::getAll();
            $this->assertEquals(1, count($savedCalendars));
            $savedCalendars[0]->delete();

            $savedCalendars           = SavedCalendar::getAll();
            $this->assertEquals(0, count($savedCalendars));
        }

        public function testGetAllWhenThereAreNone()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $savedCalendars           = SavedCalendar::getAll();
            $this->assertEquals(0, count($savedCalendars));
        }

        /**
         * @covers CalendarItemsDataProviderFactory::getDataProviderByDateRangeType
         * @covers CalendarItemsDataProvider::fetchData
         * @covers CalendarRowsAndColumnsReportDataProvider::makeSelectQueryAdapter
         * @covers CalendarRowsAndColumnsReportDataProvider::resolveSqlQueryAdapterForCount
         * @covers CalendarRowsAndColumnsReportDataProvider::getData
         */
        public function testGetDataProviderByDateRangeType()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $product                    = ProductTestHelper::createProductByNameForOwner('Test Product', Yii::app()->user->userModel);
            $savedCalendar              = CalendarTestHelper::createSavedCalendarByName('Test Cal', '#315AB0');
            $currentDateTime            = new DateTime('NOW');
            $currentDateTime->add(new DateInterval('P1D'));
            $savedCalendarSubscriptions = SavedCalendarSubscriptions::makeByUser(Yii::app()->user->userModel, (string)$savedCalendar->id);
            $dp                         = CalendarItemsDataProviderFactory::getDataProviderByDateRangeType($savedCalendarSubscriptions,
                                                                                                           '2014-01-01',
                                                                                                           $currentDateTime->format('Y-m-d'),
                                                                                                           SavedCalendar::DATERANGE_TYPE_MONTH);
            $this->assertInstanceOf('CalendarItemsDataProvider', $dp);
            $this->assertEquals('2014-01-01', $dp->getStartDate());
            $this->assertEquals($currentDateTime->format('Y-m-d'), $dp->getEndDate());
            $this->assertEquals(SavedCalendar::DATERANGE_TYPE_MONTH, $dp->getDateRangeType());
            $savedCalendarsData         = $dp->getSavedCalendarSubscriptions()->getMySavedCalendarsAndSelected();
            $keys                       = array_keys($savedCalendarsData);
            $this->assertEquals($savedCalendar->id, $keys[0]);
            $items                      = CalendarUtil::getFullCalendarItems($dp);
            $this->assertCount(1, $items);
            $this->assertEquals($product->id, $items[0]['modelId']);
        }
    }
?>