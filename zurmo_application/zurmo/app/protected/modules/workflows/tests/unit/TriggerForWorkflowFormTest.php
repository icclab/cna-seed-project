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

    class TriggerForWorkflowFormTest extends WorkflowBaseTest
    {
        public static function getDependentTestModelClassNames()
        {
            return array('WorkflowModelTestItem');
        }

        public function testValidateValueForUserNameIdAttributeWhenOperatorIsChanged()
        {
            $trigger     = new TriggerForWorkflowForm('AccountsModule', 'Account', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'owner__User';
            $trigger->setOperator(OperatorRules::TYPE_CHANGES);
            $validated   = $trigger->validate();
            $this->assertTrue($validated);
            $this->assertCount(0, $trigger->getErrors());
        }

        public function testValidateAttributeWithUniqueValidator()
        {
            $trigger     = new TriggerForWorkflowForm('AccountsModule', 'Account', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'owner___username';
            $trigger->setOperator(OperatorRules::TYPE_CHANGES);
            $trigger->value = 'jason';
            $validated   = $trigger->validate();
            $this->assertTrue($validated);
            $this->assertCount(0, $trigger->getErrors());
        }

        public function testValidateThirdValues()
        {
            $trigger     = new TriggerForWorkflowForm('AccountsModule', 'Account', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'createdDateTime';
            $trigger->value                       = null;
            $trigger->secondValue                 = null;
            $trigger->thirdValueDurationInterval  = 5;
            $trigger->thirdValueDurationType      = TimeDurationUtil::DURATION_TYPE_DAY;
            $trigger->operator                    = null;
            $trigger->currencyIdForValue          = null;
            $trigger->valueType                   = 'At Least X After Triggered Date';
            $validated   = $trigger->validate();
            $this->assertTrue($validated);
            $this->assertCount(0, $trigger->getErrors());

            $trigger     = new TriggerForWorkflowForm('AccountsModule', 'Account', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'createdDateTime';
            $trigger->value                       = null;
            $trigger->secondValue                 = null;
            $trigger->thirdValueDurationInterval  = 5;
            $trigger->thirdValueDurationType      = null;
            $trigger->operator                    = null;
            $trigger->currencyIdForValue          = null;
            $trigger->valueType                   = 'At Least X After Triggered Date';
            $validated   = $trigger->validate();
            $this->assertFalse($validated);
            $this->assertCount(1, $trigger->getErrors());

            $trigger     = new TriggerForWorkflowForm('AccountsModule', 'Account', Workflow::TYPE_ON_SAVE);
            $trigger->attributeIndexOrDerivedType = 'createdDateTime';
            $trigger->value                       = null;
            $trigger->secondValue                 = null;
            $trigger->thirdValueDurationInterval  = 'asd'; //should be integer
            $trigger->thirdValueDurationType      = null;
            $trigger->operator                    = null;
            $trigger->currencyIdForValue          = null;
            $trigger->valueType                   = 'At Least X After Triggered Date';
            $validated   = $trigger->validate();
            $this->assertFalse($validated);
            $this->assertCount(2, $trigger->getErrors());
        }

        public function testRun()
        {
            $model       = WorkflowTestHelper::createWorkflowModelTestItem('Green', '514');
            $timeTrigger = array('attributeIndexOrDerivedType' => 'string',
                                 'operator'                    => OperatorRules::TYPE_EQUALS,
                                 'value'                       => '514',
                                 'durationInterval'             => '5');
            $actions     = array(array('type' => ActionForWorkflowForm::TYPE_UPDATE_SELF,
                                       ActionForWorkflowForm::ACTION_ATTRIBUTES =>
                                            array('string' => array('shouldSetValue'    => '1',
                                                  'type'   => WorkflowActionAttributeForm::TYPE_STATIC,
                                                  'value'  => 'jason'))));
            $savedWorkflow         = WorkflowTestHelper::createByTimeSavedWorkflow($timeTrigger, array(), $actions);
            WorkflowTestHelper::createExpiredByTimeWorkflowInQueue($model, $savedWorkflow);

            $this->assertEquals(1, ByTimeWorkflowInQueue::getCount());
            $job = new ByTimeWorkflowInQueueJob();
            $this->assertTrue($job->run());
            $this->assertEquals(0, ByTimeWorkflowInQueue::getCount());
            $this->assertEquals('jason', $model->string);
        }
    }
?>