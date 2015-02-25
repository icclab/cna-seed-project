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

    class InQueueUtilTest extends WorkflowBaseTest
    {
        protected static $savedWorkflow;

        protected static $testModel;

        public static function getDependentTestModelClassNames()
        {
            return array('WorkflowModelTestItem', 'ByTimeWorkflowInQueueForTest');
        }

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $savedWorkflow                  = new SavedWorkflow();
            $savedWorkflow->name            = 'some workflow';
            $savedWorkflow->description     = 'description';
            $savedWorkflow->moduleClassName = 'moduleClassName';
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = 'some type';
            $savedWorkflow->serializedData  = serialize(array('something'));
            $saved                          = $savedWorkflow->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            self::$savedWorkflow            = $savedWorkflow;
            $model           = new WorkflowModelTestItem();
            $model->lastName = 'Green';
            $model->string   = 'string';
            $saved           = $model->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            self::$testModel = $model;
        }

        public function testResolveToAddJobToQueueAfterSaveOfModelIsNotANewModelAndProcessDateTimeHasNotChanged()
        {
            $byTimeWorkflowInQueue                  = new ByTimeWorkflowInQueue();
            $byTimeWorkflowInQueue->modelClassName  = get_class(self::$testModel);
            $byTimeWorkflowInQueue->modelItem       = self::$testModel;
            $byTimeWorkflowInQueue->processDateTime = '2007-02-02 00:00:00';
            $byTimeWorkflowInQueue->savedWorkflow   = self::$savedWorkflow;
            $saved = $byTimeWorkflowInQueue->save();
            $this->assertTrue($saved);

            Yii::app()->jobQueue->deleteAll();
            $this->assertCount(0, Yii::app()->jobQueue->getAll());
            InQueueUtil::resolveToAddJobToQueueAfterSaveOfModel($byTimeWorkflowInQueue, 'abc');
            $this->assertCount(0, Yii::app()->jobQueue->getAll());
        }

        public function testResolveToAddJobToQueueAfterSaveOfModelWithNewModel()
        {
            //First test with a null processDaetTime
            $byTimeWorkflowInQueue                  = new ByTimeWorkflowInQueueForTest();
            $byTimeWorkflowInQueue->modelClassName  = get_class(self::$testModel);
            $byTimeWorkflowInQueue->modelItem       = self::$testModel;
            $byTimeWorkflowInQueue->processDateTime = '0000-00-00 00:00:00';
            $byTimeWorkflowInQueue->savedWorkflow   = self::$savedWorkflow;
            $byTimeWorkflowInQueue->setIsNewModel(true); //simulates beforeSave behavior

            $this->assertCount(0, Yii::app()->jobQueue->getAll());
            InQueueUtil::resolveToAddJobToQueueAfterSaveOfModel($byTimeWorkflowInQueue, 'abc');
            $jobs = Yii::app()->jobQueue->getAll();
            $this->assertCount(1, $jobs);
            $this->assertEquals('abc', $jobs[5][0]['jobType']);

            //now test with processDateTime set
            Yii::app()->jobQueue->deleteAll();
            $this->assertCount(0, Yii::app()->jobQueue->getAll());
            $byTimeWorkflowInQueue                  = new ByTimeWorkflowInQueueForTest();
            $byTimeWorkflowInQueue->modelClassName  = get_class(self::$testModel);
            $byTimeWorkflowInQueue->modelItem       = self::$testModel;
            $byTimeWorkflowInQueue->processDateTime = '2037-02-02 00:00:00';
            $byTimeWorkflowInQueue->savedWorkflow   = self::$savedWorkflow;
            $byTimeWorkflowInQueue->setIsNewModel(true); //simulates beforeSave behavior

            $this->assertCount(0, Yii::app()->jobQueue->getAll());
            InQueueUtil::resolveToAddJobToQueueAfterSaveOfModel($byTimeWorkflowInQueue, 'abc');
            $jobs = Yii::app()->jobQueue->getAll();
            $this->assertCount(1, $jobs);
            $this->assertTrue(key($jobs) > 315360000); //Greater than 10 years in future confirms it is not soon...
            $firstKey = key($jobs);

            //now change processDateTime, and make sure the job is different.
            $saved = $byTimeWorkflowInQueue->save();
            $this->assertTrue($saved);
            $byTimeWorkflowInQueue->processDateTime = '2037-03-02 00:00:00';
            Yii::app()->jobQueue->deleteAll();
            $this->assertCount(0, Yii::app()->jobQueue->getAll());
            InQueueUtil::resolveToAddJobToQueueAfterSaveOfModel($byTimeWorkflowInQueue, 'abc');
            $jobs = Yii::app()->jobQueue->getAll();
            $this->assertCount(1, $jobs);
            $this->assertTrue(key($jobs) > 315360000); //Greater than 10 years in future confirms it is not soon...
            $this->assertNotEquals($firstKey, key($jobs));
        }
    }
?>