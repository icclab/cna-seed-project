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

    class JobQueueTest extends BaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
        }

        public function testAddAndGetAll()
        {
            $this->assertCount(0, Yii::app()->jobQueue->getAll());
            Yii::app()->jobQueue->add('aJob', 13);
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertCount(1, $queuedJobs[13]);
            $this->assertEquals('aJob', $queuedJobs[13][0]['jobType']);
            //Try to add it again
            Yii::app()->jobQueue->add('aJob', 13);
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertCount(1, $queuedJobs[13]);

            Yii::app()->jobQueue->add('aJob', 10);
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertCount(1, $queuedJobs[10]);

            // And add it with tolerance
            Yii::app()->jobQueue->add('aJob', 15);
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertCount(1, $queuedJobs[15]);
            $this->assertFalse(isset($queuedJobs[13]));
            $this->assertEquals('aJob', $queuedJobs[15][0]['jobType']);

            // Now add same job, but with delay that is much higher then existing job with noise
            Yii::app()->jobQueue->add('aJob', 100);
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertCount(1, $queuedJobs[15]);
            $this->assertCount(1, $queuedJobs[100]);
            $this->assertEquals('aJob', $queuedJobs[100][0]['jobType']);

            //Try to add a new job
            Yii::app()->jobQueue->add('bJob', 15);
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertCount(2, $queuedJobs[15]);
            $this->assertEquals('aJob', $queuedJobs[15][0]['jobType']);
            $this->assertEquals('bJob', $queuedJobs[15][1]['jobType']);
            //Add an immediate job
            Yii::app()->jobQueue->add('cJob');
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertCount(1, $queuedJobs[0]);
            $this->assertEquals('cJob', $queuedJobs[0][0]['jobType']);
        }

        /**
         * @depends testAddAndGetAll
         */
        public function testDeleteAll()
        {
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertCount(4, $queuedJobs);
            $this->assertCount(1, $queuedJobs[10]);
            $this->assertCount(2, $queuedJobs[15]);
            $this->assertCount(1, $queuedJobs[100]);
            $this->assertCount(1, $queuedJobs[0]);
            Yii::app()->jobQueue->deleteAll();
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertCount(0, $queuedJobs);
        }
    }
?>
