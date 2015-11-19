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
    * Test ExportItemToCsvFileUtil functions.
    */
    class ExportItemTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
            // Delete item from jobQueue, that is created when new user is created
            Yii::app()->jobQueue->deleteAll();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateAndEditExportItem()
        {
            $idsToExport = array(1, 2, 3);
            $exportItem = new ExportItem();
            $exportItem->isCompleted = 0;
            $exportItem->exportFileType = 'csv';
            $exportItem->exportFileName = 'test';
            $exportItem->modelClassName = 'Account';
            $exportItem->serializedData = serialize($idsToExport);
            $this->assertEquals(0, count(Yii::app()->jobQueue->getAll()));
            $this->assertTrue($exportItem->save());
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs));
            $this->assertEquals('Export', $queuedJobs[5][0]['jobType']);

            //Now edit existing exportItem. The queuedJobs should not change
            $exportItemId = $exportItem->id;
            $exportItem->forget();
            $exportItem   = ExportItem::getById($exportItemId);
            $this->assertTrue($exportItem->save());
            $this->assertEquals(1, count($queuedJobs));
            $this->assertEquals('Export', $queuedJobs[5][0]['jobType']);

            //Now create a new export item that is already complete, should not create a queue job.
            $idsToExport = array(1, 2, 3);
            $exportItem = new ExportItem();
            $exportItem->isCompleted = 1;
            $exportItem->exportFileType = 'csv';
            $exportItem->exportFileName = 'test';
            $exportItem->modelClassName = 'Account';
            $exportItem->serializedData = serialize($idsToExport);
            $this->assertTrue($exportItem->save());
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs));
            $this->assertEquals('Export', $queuedJobs[5][0]['jobType']);
        }

        public function testCreateExportItemWithLargeData()
        {
            // anything larger than 99999 will have memcache complain about objects being too big to cache
            // why? because ExportItem isCacheable() is true(implicitly, inherited from RedBeanModel.php).
            // so during every save its re-cached: RedBeanModel.php:2017
            $idsToExport = range(0, 66500);
            $exportItem = new ExportItem();
            $exportItem->isCompleted = 0;
            $exportItem->exportFileType = 'csv';
            $exportItem->exportFileName = 'test';
            $exportItem->modelClassName = 'Account';
            $exportItem->serializedData = serialize($idsToExport);
            $this->assertTrue($exportItem->save());

            $exportItemId = $exportItem->id;
            $exportItem->forget();
            $exportItem   = ExportItem::getById($exportItemId);
            $this->assertEquals($idsToExport, unserialize($exportItem->serializedData));
        }
    }
?>