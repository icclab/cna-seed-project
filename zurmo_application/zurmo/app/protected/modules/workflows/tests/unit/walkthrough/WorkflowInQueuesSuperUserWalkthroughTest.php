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
     * Workflow in queue walkthrough tests for super users.
     */
    class WorkflowInQueuesSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        private $super;

        private $savedWorkflow;

        private $model;

        private $workflowMessageInQueueIds;

        private $byTimeWorkflowInQueueIds;

        public static function getDependentTestModelClassNames()
        {
            return array('WorkflowModelTestItem');
        }

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            $account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            AccountTestHelper::createAccountByNameForOwner('superAccount2', $super);
            ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, $account);

            WorkflowTestHelper::createWorkflowModelTestItem('Jason', 'Green');
            $savedWorkflow                  = new SavedWorkflow();
            $savedWorkflow->name            = 'some workflow';
            $savedWorkflow->description     = 'description';
            $savedWorkflow->moduleClassName = 'WorkflowsTestModule';
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = Workflow::TYPE_BY_TIME;
            $savedWorkflow->serializedData  = serialize(array(ComponentForWorkflowForm::TYPE_TRIGGERS       => array(),
                                                              ComponentForWorkflowForm::TYPE_ACTIONS        => array(),
                                                              ComponentForWorkflowForm::TYPE_EMAIL_MESSAGES => array(),
                                                        ));
            $savedWorkflow->save();
        }

        public function setUp()
        {
            parent::setUp();
            $this->super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $savedWorkflows = SavedWorkflow::getAll();
            $this->savedWorkflow = $savedWorkflows[0];

            $models = WorkflowModelTestItem::getAll();
            $this->model = $models[0];
        }

        public function teardown()
        {
            $models = ByTimeWorkflowInQueue::getAll();
            foreach ($models as $model)
            {
                $model->delete();
            }
            $models = WorkflowMessageInQueue::getAll();
            foreach ($models as $model)
            {
                $model->delete();
            }
        }

        public function testInQueuesAutoCompleteAction()
        {
            Yii::app()->user->userModel = $this->super;

            $this->setGetArray(array('term'          => 'abc',
                                     'formClassName' => 'WorkflowMessageInQueuesSearchForm',
                                     'WorkflowMessageInQueuesSearchForm' =>
                                        array('anyMixedAttributesScope' => array('All'))));
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/default/inQueuesAutoComplete');
            $compareContent = '[{"itemId":null,"modelClassName":null,"value":null,"label":"No Results Found","iconClass":""}]'; // Not Coding Standard
            $this->assertEquals($compareContent, $content);

            $this->setGetArray(array('term'          => 'abc',
                                     'formClassName' => 'ByTimeWorkflowInQueueSearchForm',
                                     'ByTimeWorkflowInQueueSearchForm' =>
                                     array('anyMixedAttributesScope' => array('All'))));
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/default/inQueuesAutoComplete');
            $compareContent = '[{"itemId":null,"modelClassName":null,"value":null,"label":"No Results Found","iconClass":""}]'; // Not Coding Standard
            $this->assertEquals($compareContent, $content);
        }

        public function testSuperUserListAction()
        {
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/defaultTimeQueue/list');
            $this->assertContains('No results found', $content);
            WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/defaultTimeQueue/list');
            $this->assertContains('1 result(s)', $content);

            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/defaultMessageQueue/list');
            $this->assertContains('No results found', $content);
            WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/defaultMessageQueue/list');
            $this->assertContains('1 result(s)', $content);
        }

        public function testMassDeleteActionsForSelectedIdsForByTimeWorkflowInQueue()
        {
            //MassDelete for selected Record Count
            $byTimeWorkflowInQueue01  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue02  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue03  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue04  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue05  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue06  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue07  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue08  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue09  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue10  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue11  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue12  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue13  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue14  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue15  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue16  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue17  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue18  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue19  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue20  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);

            //Load Model MassDelete Views.
            //MassDelete view for single selected ids
            $selectedIds = implode(',', array($byTimeWorkflowInQueue05->id, // Not Coding Standard
                                              $byTimeWorkflowInQueue06->id,
                                              $byTimeWorkflowInQueue07->id,
                                              $byTimeWorkflowInQueue08->id));
            $this->setGetArray(array('selectedIds' => $selectedIds, 'selectAll' => ''));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/defaultTimeQueue/massDelete');
            $this->assertContains('<strong>4</strong>&#160;Time Queue Items selected for removal', $content);

            //MassDelete view for all result selected ids
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/defaultTimeQueue/massDelete');
            $this->assertContains('<strong>20</strong>&#160;Time Queue Items selected for removal', $content);
            //MassDelete for selected ids
            $selectedIds = implode(',', array($byTimeWorkflowInQueue02->id, // Not Coding Standard
                                              $byTimeWorkflowInQueue03->id,
                                              $byTimeWorkflowInQueue20->id));
            $this->setGetArray(array('selectedIds' => $selectedIds,
                                     'selectAll' => '',
                                     'ByTimeWorkflowInQueue_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 3));
            $this->runControllerWithRedirectExceptionAndGetContent('workflows/defaultTimeQueue/massDelete');

            //MassDelete for selected Record Count
            $models = ByTimeWorkflowInQueue::getAll();
            $this->assertEquals(17, count($models));

            //MassDelete for selected ids for paged scenario
            $selectedIds = implode(',', array($byTimeWorkflowInQueue12->id, // Not Coding Standard
                                              $byTimeWorkflowInQueue13->id,
                                              $byTimeWorkflowInQueue14->id,
                                              $byTimeWorkflowInQueue15->id,
                                              $byTimeWorkflowInQueue16->id,
                                              $byTimeWorkflowInQueue17->id,
                                              $byTimeWorkflowInQueue18->id,
                                              $byTimeWorkflowInQueue19->id));

            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            //MassDelete for selected ids for page 1
            $this->setGetArray(array(
                'selectedIds'                 => $selectedIds,
                'selectAll'                   => '',
                'massDelete'                  => '',
                'ByTimeWorkflowInQueue_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 8));
            $this->runControllerWithExitExceptionAndGetContent('workflows/defaultTimeQueue/massDelete');

            //MassDelete for selected Record Count
            $models = ByTimeWorkflowInQueue::getAll();
            $this->assertEquals(12, count($models));

            //MassDelete for selected ids for page 2
            $this->setGetArray(array(
                'selectedIds'  => $selectedIds,
                'selectAll'    => '',
                'ByTimeWorkflowInQueue_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 8));
            $this->runControllerWithNoExceptionsAndGetContent('workflows/defaultTimeQueue/massDeleteProgress');

            //MassDelete for selected Record Count
            $models = ByTimeWorkflowInQueue::getAll();
            $this->assertEquals(9, count($models));
        }

        public function testMassDeletePagesProperlyAndRemovesAllSelectedForByTimeWorkflowInQueue()
        {
            //MassDelete for selected Record Count
            $byTimeWorkflowInQueue01  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue02  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue03  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue04  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue05  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue06  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue07  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue08  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue09  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);
            $byTimeWorkflowInQueue10  = WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($this->model, $this->savedWorkflow);

            //save Model MassDelete for entire search result
            $this->setGetArray(array(
                'selectAll' => '1',           // Not Coding Standard
                'ByTimeWorkflowInQueue_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 10));
            //Run Mass Delete using progress save for page1.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithExitExceptionAndGetContent('workflows/defaultTimeQueue/massDelete');

            //check for previous mass delete progress
            $models = ByTimeWorkflowInQueue::getAll();
            $this->assertEquals(5, count($models));

            $this->setGetArray(array(
                'selectAll' => '1',           // Not Coding Standard
                'ByTimeWorkflowInQueue_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 10));
            //Run Mass Delete using progress save for page2.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithNoExceptionsAndGetContent('workflows/defaultTimeQueue/massDeleteProgress');

            //calculating count
            $models = ByTimeWorkflowInQueue::getAll();
            $this->assertEquals(0, count($models));
        }

        public function testMassDeleteActionsForSelectedIdsForWorkflowMessageInQueue()
        {
            //MassDelete for selected Record Count
            $workflowMessageInQueue01  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue02  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue03  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue04  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue05  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue06  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue07  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue08  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue09  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue10  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue11  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue12  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue13  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue14  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue15  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue16  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue17  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue18  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue19  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue20  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);

            //Load Model MassDelete Views.
            //MassDelete view for single selected ids
            $selectedIds = implode(',', array($workflowMessageInQueue05->id, // Not Coding Standard
                                              $workflowMessageInQueue06->id,
                                              $workflowMessageInQueue07->id,
                                              $workflowMessageInQueue08->id));
            $this->setGetArray(array('selectedIds' => $selectedIds, 'selectAll' => ''));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/defaultMessageQueue/massDelete');
            $this->assertContains('<strong>4</strong>&#160;Message Queue Items selected for removal', $content);

            //MassDelete view for all result selected ids
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('workflows/defaultMessageQueue/massDelete');
            $this->assertContains('<strong>20</strong>&#160;Message Queue Items selected for removal', $content);
            //MassDelete for selected ids
            $selectedIds = implode(',', array($workflowMessageInQueue02->id, // Not Coding Standard
                                              $workflowMessageInQueue03->id,
                                              $workflowMessageInQueue20->id));
            $this->setGetArray(array('selectedIds' => $selectedIds,
                                     'selectAll' => '',
                                     'WorkflowMessageInQueue_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 3));
            $this->runControllerWithRedirectExceptionAndGetContent('workflows/defaultMessageQueue/massDelete');

            //MassDelete for selected Record Count
            $models = WorkflowMessageInQueue::getAll();
            $this->assertEquals(17, count($models));

            //MassDelete for selected ids for paged scenario
            $selectedIds = implode(',', array($workflowMessageInQueue12->id, // Not Coding Standard
                                              $workflowMessageInQueue13->id,
                                              $workflowMessageInQueue14->id,
                                              $workflowMessageInQueue15->id,
                                              $workflowMessageInQueue16->id,
                                              $workflowMessageInQueue17->id,
                                              $workflowMessageInQueue18->id,
                                              $workflowMessageInQueue19->id));

            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            //MassDelete for selected ids for page 1
            $this->setGetArray(array(
                                'selectedIds'                 => $selectedIds,
                                'selectAll'                   => '',
                                'massDelete'                  => '',
                                'WorkflowMessageInQueue_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 8));
            $this->runControllerWithExitExceptionAndGetContent('workflows/defaultMessageQueue/massDelete');

            //MassDelete for selected Record Count
            $models = WorkflowMessageInQueue::getAll();
            $this->assertEquals(12, count($models));

            //MassDelete for selected ids for page 2
            $this->setGetArray(array(
                                'selectedIds'  => $selectedIds,
                                'selectAll'    => '',
                                'WorkflowMessageInQueue_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 8));
            $this->runControllerWithNoExceptionsAndGetContent('workflows/defaultMessageQueue/massDeleteProgress');

            //MassDelete for selected Record Count
            $models = WorkflowMessageInQueue::getAll();
            $this->assertEquals(9, count($models));
        }

        public function testMassDeletePagesProperlyAndRemovesAllSelectedForWorkflowMessageInQueue()
        {
            //MassDelete for selected Record Count
            $workflowMessageInQueue01  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue02  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue03  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue04  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue05  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue06  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue07  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue08  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue09  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);
            $workflowMessageInQueue10  = WorkflowTestHelper::createExpiredWorkflowMessageInQueue($this->model, $this->savedWorkflow);

            //save Model MassDelete for entire search result
            $this->setGetArray(array(
                'selectAll' => '1',           // Not Coding Standard
                'WorkflowMessageInQueue_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 10));
            //Run Mass Delete using progress save for page1.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithExitExceptionAndGetContent('workflows/defaultMessageQueue/massDelete');

            //check for previous mass delete progress
            $models = WorkflowMessageInQueue::getAll();
            $this->assertEquals(5, count($models));

            $this->setGetArray(array(
                'selectAll' => '1',           // Not Coding Standard
                'WorkflowMessageInQueue_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 10));
            //Run Mass Delete using progress save for page2.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithNoExceptionsAndGetContent('workflows/defaultMessageQueue/massDeleteProgress');

            //calculating count
            $models = WorkflowMessageInQueue::getAll();
            $this->assertEquals(0, count($models));
        }
    }
?>