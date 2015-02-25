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

    class WorkflowEmailMessageProcessingHelperTest extends WorkflowBaseTest
    {
        protected static $superUserId;

        protected static $bobbyUserId;

        protected static $sarahUserId;

        protected static $emailTemplate;

        protected static $alphaGroup;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = User::getByUsername('super');
            $super = User::getByUsername('super');
            $super->primaryEmail = new Email();
            $super->primaryEmail->emailAddress = 'super@zurmo.com';
            assert($super->save()); // Not Coding Standard

            //Create alpha group
            $group = new Group();
            $group->name = 'Alpha';
            $saved = $group->save();
            assert($saved); // Not Coding Standard
            self::$alphaGroup = $group;

            //Now set default permissions to owner and users in group Alpha
            $form = UserConfigurationFormAdapter::makeFormFromUserConfigurationByUser($super);
            $form->defaultPermissionSetting         = UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER_AND_USERS_IN_GROUP;
            $form->defaultPermissionGroupSetting    = $group->id;
            UserConfigurationFormAdapter::setConfigurationFromForm($form, $super);
            $bobby = UserTestHelper::createBasicUserWithEmailAddress('bobby');
            $sarah = UserTestHelper::createBasicUserWithEmailAddress('sarah');
            self::$superUserId = $super->id;
            self::$bobbyUserId = $bobby->id;
            self::$sarahUserId = $sarah->id;
            $file = ZurmoTestHelper::createFileModel();

            $emailTemplate                 = new EmailTemplate();
            $emailTemplate->builtType      = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $emailTemplate->modelClassName = 'WorkflowModelTestItem';
            $emailTemplate->type           = 1;
            $emailTemplate->name           = 'some template';
            $emailTemplate->subject        = 'some subject [[LAST^NAME]]';
            $emailTemplate->htmlContent    = 'html content [[STRING]]';
            $emailTemplate->textContent    = 'text content [[PHONE]]';
            $emailTemplate->files->add($file);
            $saved = $emailTemplate->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            self::$emailTemplate = $emailTemplate;

            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            assert($everyoneGroup->save()); // Not Coding Standard
        }

        public static function getDependentTestModelClassNames()
        {
            return array('WorkflowModelTestItem');
        }

        public function testProcessWithDefaultSender()
        {
            $message               = new EmailMessageForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $recipients = array(array('type'             => WorkflowEmailMessageRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                                      'audienceType'     => EmailMessageRecipient::TYPE_TO,
                                      'dynamicUserType'  => DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::
                                      DYNAMIC_USER_TYPE_CREATED_BY_USER));
            $message->emailTemplateId = self::$emailTemplate->id;
            $message->sendFromType    = EmailMessageForWorkflowForm::SEND_FROM_TYPE_DEFAULT;
            $message->setAttributes(array(EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS => $recipients));

            $model           = new WorkflowModelTestItem();
            $model->lastName = 'the lastName';
            $model->string   = 'the string';
            $model->phone    = 'the phone';
            $saved = $model->save();
            $this->assertTrue($saved);
            $helper = new WorkflowEmailMessageProcessingHelper($message, $model, Yii::app()->user->userModel);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $helper->process();
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $emailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertEquals('some subject the lastName',  $emailMessages[0]->subject);
            $this->assertEquals('text content the phone',     $emailMessages[0]->content->textContent);
            $this->assertEquals('html content the string',    $emailMessages[0]->content->htmlContent);
            $this->assertEquals('System User',      $emailMessages[0]->sender->fromName);
            $this->assertEquals('notification@zurmoalerts.com', $emailMessages[0]->sender->fromAddress);
            $this->assertEquals(1,                 $emailMessages[0]->recipients->count());
            $this->assertEquals('super@zurmo.com', $emailMessages[0]->recipients[0]->toAddress);
            $this->assertEquals(self::$emailTemplate->files[0]->fileContent->content, $emailMessages[0]->files[0]->fileContent->content);

            //Assert explicit permissions are correct
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                 makeBySecurableItem($emailMessages[0]);
            $this->assertTrue($explicitReadWriteModelPermissions instanceof ExplicitReadWriteModelPermissions);
            $readWritePermitables = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->assertEquals(1, count($readWritePermitables));
            $this->assertEquals(self::$alphaGroup, $readWritePermitables[self::$alphaGroup->getClassId('Permitable')]);

            $emailMessages[0]->delete();
        }

        /**
         * @depends testProcessWithDefaultSender
         */
        public function testProcessWithCustomSender()
        {
            $message               = new EmailMessageForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $recipients = array(array('type'             => WorkflowEmailMessageRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                                      'audienceType'     => EmailMessageRecipient::TYPE_TO,
                                      'dynamicUserType'  => DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::
                                      DYNAMIC_USER_TYPE_CREATED_BY_USER));
            $message->emailTemplateId = self::$emailTemplate->id;
            $message->sendFromType    = EmailMessageForWorkflowForm::SEND_FROM_TYPE_CUSTOM;
            $message->sendFromAddress = 'someone@zurmo.com';
            $message->sendFromName    = 'Jason';
            $message->setAttributes(array(EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS => $recipients));

            $model           = new WorkflowModelTestItem();
            $model->lastName = 'the lastName';
            $model->string   = 'the string';
            $model->phone    = 'the phone';
            $saved = $model->save();
            $this->assertTrue($saved);
            $helper = new WorkflowEmailMessageProcessingHelper($message, $model, Yii::app()->user->userModel);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $helper->process();
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $emailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertEquals('some subject the lastName',   $emailMessages[0]->subject);
            $this->assertEquals('text content the phone',      $emailMessages[0]->content->textContent);
            $this->assertEquals('html content the string',     $emailMessages[0]->content->htmlContent);
            $this->assertEquals('Jason',             $emailMessages[0]->sender->fromName);
            $this->assertEquals('someone@zurmo.com', $emailMessages[0]->sender->fromAddress);
            $this->assertEquals(1,                   $emailMessages[0]->recipients->count());
            $this->assertEquals('super@zurmo.com',   $emailMessages[0]->recipients[0]->toAddress);
            $this->assertEquals(self::$emailTemplate->files[0]->fileContent->content, $emailMessages[0]->files[0]->fileContent->content);
            $emailMessages[0]->delete();
        }

        /**
         * @depends testProcessWithDefaultSender
         */
        public function testProcessWithTriggeredModelOwnerSender()
        {
            $message               = new EmailMessageForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $recipients = array(array('type'             => WorkflowEmailMessageRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                                      'audienceType'     => EmailMessageRecipient::TYPE_TO,
                                      'dynamicUserType'  => DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::
                                      DYNAMIC_USER_TYPE_CREATED_BY_USER));
            $message->emailTemplateId = self::$emailTemplate->id;
            $message->sendFromType    = EmailMessageForWorkflowForm::SEND_FROM_TYPE_TRIGGERED_MODEL_OWNER;
            $message->setAttributes(array(EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS => $recipients));

            $model           = new WorkflowModelTestItem();
            $model->lastName = 'the lastName';
            $model->string   = 'the string';
            $model->phone    = 'the phone';
            $saved = $model->save();
            $this->assertTrue($saved);
            $helper = new WorkflowEmailMessageProcessingHelper($message, $model, Yii::app()->user->userModel);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $helper->process();
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $emailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertEquals('some subject the lastName',  $emailMessages[0]->subject);
            $this->assertEquals('text content the phone',     $emailMessages[0]->content->textContent);
            $this->assertEquals('html content the string',    $emailMessages[0]->content->htmlContent);
            $this->assertEquals('Clark Kent',      $emailMessages[0]->sender->fromName);
            $this->assertEquals('super@zurmo.com', $emailMessages[0]->sender->fromAddress);
            $this->assertEquals(1,                 $emailMessages[0]->recipients->count());
            $this->assertEquals('super@zurmo.com', $emailMessages[0]->recipients[0]->toAddress);
            $this->assertEquals(self::$emailTemplate->files[0]->fileContent->content, $emailMessages[0]->files[0]->fileContent->content);
            $emailMessages[0]->delete();

            //Now test a user that doesn't have an email address
            $sally = UserTestHelper::createBasicUser('sally');
            $this->assertNull($sally->primaryEmail->emailAddress);

            $model           = new WorkflowModelTestItem();
            $model->lastName = 'the lastName';
            $model->string   = 'the string';
            $model->phone    = 'the phone';
            $model->owner    = $sally;
            $saved = $model->save();
            $this->assertTrue($saved);
            $helper = new WorkflowEmailMessageProcessingHelper($message, $model, Yii::app()->user->userModel);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $helper->process();
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $emailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertEquals('some subject the lastName',  $emailMessages[0]->subject);
            $this->assertEquals('text content the phone',     $emailMessages[0]->content->textContent);
            $this->assertEquals('html content the string',    $emailMessages[0]->content->htmlContent);
            $this->assertEquals('System User',      $emailMessages[0]->sender->fromName);
            $this->assertEquals('notification@zurmoalerts.com', $emailMessages[0]->sender->fromAddress);
            $this->assertEquals(1,                 $emailMessages[0]->recipients->count());
            $this->assertEquals('super@zurmo.com', $emailMessages[0]->recipients[0]->toAddress);
            $this->assertEquals(self::$emailTemplate->files[0]->fileContent->content, $emailMessages[0]->files[0]->fileContent->content);
            $emailMessages[0]->delete();
        }

        /**
         * @depends testProcessWithTriggeredModelOwnerSender
         */
        public function testInvalidMergeTagsReturnOriginalContent()
        {
            self::$emailTemplate->subject     = 'bad subject [[LASTNAME]]';
            self::$emailTemplate->textContent = 'bad text [[LASTNAME]]';
            self::$emailTemplate->htmlContent = 'bad html [[LASTNAME]]';

            $message               = new EmailMessageForWorkflowForm('WorkflowModelTestItem', Workflow::TYPE_ON_SAVE);
            $recipients = array(array('type'             => WorkflowEmailMessageRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                'audienceType'     => EmailMessageRecipient::TYPE_TO,
                'dynamicUserType'  => DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::
                DYNAMIC_USER_TYPE_CREATED_BY_USER));
            $message->emailTemplateId = self::$emailTemplate->id;
            $message->sendFromType    = EmailMessageForWorkflowForm::SEND_FROM_TYPE_CUSTOM;
            $message->sendFromAddress = 'someone@zurmo.com';
            $message->sendFromName    = 'Jason';
            $message->setAttributes(array(EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS => $recipients));

            $model           = new WorkflowModelTestItem();
            $model->lastName = 'the lastName';
            $model->string   = 'the string';
            $model->phone    = 'the phone';
            $saved = $model->save();
            $this->assertTrue($saved);
            $helper = new WorkflowEmailMessageProcessingHelper($message, $model, Yii::app()->user->userModel);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $helper->process();
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $emailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertEquals('bad subject [[LASTNAME]]',   $emailMessages[0]->subject);
            $this->assertEquals('bad text [[LASTNAME]]',      $emailMessages[0]->content->textContent);
            $this->assertEquals('bad html [[LASTNAME]]',      $emailMessages[0]->content->htmlContent);
            $this->assertEquals('Jason',             $emailMessages[0]->sender->fromName);
            $this->assertEquals('someone@zurmo.com', $emailMessages[0]->sender->fromAddress);
            $this->assertEquals(1,                   $emailMessages[0]->recipients->count());
            $this->assertEquals('super@zurmo.com',   $emailMessages[0]->recipients[0]->toAddress);
            $this->assertEquals(self::$emailTemplate->files[0]->fileContent->content, $emailMessages[0]->files[0]->fileContent->content);
            $emailMessages[0]->delete();
        }

        public function testProcessForActivityItems()
        {
            $account            = AccountTestHelper::createAccountByNameForOwner('testAccount', Yii::app()->user->userModel);
            $task               = TaskTestHelper::createTaskWithOwnerAndRelatedAccount('testTask', Yii::app()->user->userModel, $account);
            $meeting            = MeetingTestHelper::createMeetingWithOwnerAndRelatedAccount('testMeetig', Yii::app()->user->userModel, $account);
            $contact            = ContactTestHelper::createContactByNameForOwner('testContact', Yii::app()->user->userModel);
            $opportunity        = OpportunityTestHelper::createOpportunityByNameForOwner('testOpportunity', Yii::app()->user->userModel);
            $task->activityItems->add($contact);
            $task->activityItems->add($opportunity);
            $meeting->activityItems->add($contact);
            $meeting->activityItems->add($opportunity);
            $this->assertTrue($task->save());
            $this->assertTrue($meeting->save());

            $emailTemplate                 = new EmailTemplate();
            $emailTemplate->builtType      = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $emailTemplate->modelClassName = 'Task';
            $emailTemplate->type           = 1;
            $emailTemplate->name           = 'some template';
            $emailTemplate->subject        = 'some subject [[NAME]]';
            $emailTemplate->htmlContent    = 'Account: [[ACCOUNT__NAME]] Contact: [[CONTACT__FIRST^NAME]] Opportunity: [[OPPORTUNITY__NAME]]';
            $emailTemplate->textContent    = 'Account: [[ACCOUNT__NAME]] Contact: [[CONTACT__FIRST^NAME]] Opportunity: [[OPPORTUNITY__NAME]]';
            $this->assertTrue($emailTemplate->save());
            $message               = new EmailMessageForWorkflowForm('Task', Workflow::TYPE_ON_SAVE);

            $recipients = array(array('type'             => WorkflowEmailMessageRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                                      'audienceType'     => EmailMessageRecipient::TYPE_TO,
                                      'dynamicUserType'  => DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::
                                                                DYNAMIC_USER_TYPE_CREATED_BY_USER));
            $message->emailTemplateId = $emailTemplate->id;
            $message->sendFromType    = EmailMessageForWorkflowForm::SEND_FROM_TYPE_CUSTOM;
            $message->sendFromAddress = 'someone@zurmo.com';
            $message->sendFromName    = 'Jason';
            $message->setAttributes(array(EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS => $recipients));

            $helper = new WorkflowEmailMessageProcessingHelper($message, $task, Yii::app()->user->userModel);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $helper->process();
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $emailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertEquals('some subject testTask',   $emailMessages[0]->subject);
            $this->assertEquals('Account: testAccount Contact: testContact Opportunity: testOpportunity',
                                $emailMessages[0]->content->textContent);
            $this->assertEquals('Account: testAccount Contact: testContact Opportunity: testOpportunity',
                                $emailMessages[0]->content->htmlContent);
            $this->assertEquals('Jason',             $emailMessages[0]->sender->fromName);
            $this->assertEquals('someone@zurmo.com', $emailMessages[0]->sender->fromAddress);
            $this->assertEquals(1,                   $emailMessages[0]->recipients->count());
            $this->assertEquals('super@zurmo.com',   $emailMessages[0]->recipients[0]->toAddress);

            $taskId = $task->id;
            $task->forgetAll();
            $task = Task::getById($taskId);
            $message               = new EmailMessageForWorkflowForm('Task', Workflow::TYPE_ON_SAVE);

            $recipients = array(array('type'             => WorkflowEmailMessageRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                'audienceType'     => EmailMessageRecipient::TYPE_TO,
                'dynamicUserType'  => DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::
                DYNAMIC_USER_TYPE_CREATED_BY_USER));
            $message->emailTemplateId = $emailTemplate->id;
            $message->sendFromType    = EmailMessageForWorkflowForm::SEND_FROM_TYPE_CUSTOM;
            $message->sendFromAddress = 'someone@zurmo.com';
            $message->sendFromName    = 'Jason';
            $message->setAttributes(array(EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS => $recipients));

            $helper = new WorkflowEmailMessageProcessingHelper($message, $task, Yii::app()->user->userModel);
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $helper->process();
            $this->assertEquals(2, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $emailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertEquals('some subject testTask',   $emailMessages[1]->subject);
            $this->assertEquals('Account: testAccount Contact: testContact Opportunity: testOpportunity',
                $emailMessages[1]->content->textContent);
            $this->assertEquals('Account: testAccount Contact: testContact Opportunity: testOpportunity',
                $emailMessages[1]->content->htmlContent);
            $this->assertEquals('Jason',             $emailMessages[1]->sender->fromName);
            $this->assertEquals('someone@zurmo.com', $emailMessages[1]->sender->fromAddress);
            $this->assertEquals(1,                   $emailMessages[1]->recipients->count());
            $this->assertEquals('super@zurmo.com',   $emailMessages[1]->recipients[0]->toAddress);

            $emailMessages[0]->delete();
            $emailMessages[1]->delete();
        }

        /**
         * To try to show failure of https://www.pivotaltracker.com/story/show/81571830
         * Using trademark symbol to demonstrate merge tag resolution working correctly and it properly printing
         * text after the trademark symbol
         */
        public function testNonAlphaNumericSymbolsAsMergeTagFields()
        {
            $account                       = AccountTestHelper::createAccountByNameForOwner('Candyland™', Yii::app()->user->userModel);
            $emailTemplate                 = new EmailTemplate();
            $emailTemplate->builtType      = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $emailTemplate->modelClassName = 'Account';
            $emailTemplate->type           = 1;
            $emailTemplate->name           = 'some template';
            $emailTemplate->subject        = 'some subject [[NAME]] is great';
            $emailTemplate->htmlContent    = 'Account HTML: [[NAME]] after the merge tag is resolved';
            $emailTemplate->textContent    = 'Account Text: [[NAME]] after the merge tag is resolved';
            $this->assertTrue($emailTemplate->save());
            $message                       = new EmailMessageForWorkflowForm('Account', Workflow::TYPE_ON_SAVE);

            $recipients = array(array('type'             => WorkflowEmailMessageRecipientForm::TYPE_DYNAMIC_TRIGGERED_MODEL_USER,
                                      'audienceType'     => EmailMessageRecipient::TYPE_TO,
                                      'dynamicUserType'  => DynamicTriggeredModelUserWorkflowEmailMessageRecipientForm::
                                        DYNAMIC_USER_TYPE_CREATED_BY_USER));
            $message->emailTemplateId = $emailTemplate->id;
            $message->sendFromType    = EmailMessageForWorkflowForm::SEND_FROM_TYPE_CUSTOM;
            $message->sendFromAddress = 'someone@zurmo.com';
            $message->sendFromName    = 'Jason';
            $message->setAttributes(array(EmailMessageForWorkflowForm::EMAIL_MESSAGE_RECIPIENTS => $recipients));

            $helper = new WorkflowEmailMessageProcessingHelper($message, $account, Yii::app()->user->userModel);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $helper->process();
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $emailMessages = EmailMessage::getAllByFolderType(EmailFolder::TYPE_OUTBOX);
            $this->assertEquals('some subject Candyland™ is great',   $emailMessages[0]->subject);
            $this->assertEquals('Account Text: Candyland™ after the merge tag is resolved',
                $emailMessages[0]->content->textContent);
            $this->assertEquals('Account HTML: Candyland™ after the merge tag is resolved',
                $emailMessages[0]->content->htmlContent);
        }
    }
?>