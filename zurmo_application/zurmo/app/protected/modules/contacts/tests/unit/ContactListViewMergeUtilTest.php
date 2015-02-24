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

    class ContactListViewMergeUtilTest extends ListViewMergeUtilBaseTest
    {
        public $modelClass = 'Contact';

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ContactsModule::loadStartingData();
            UserTestHelper::createBasicUser('Steven');
        }

        public function testSetPrimaryModelForListViewMerge()
        {
            $this->processSetPrimaryModelForListViewMerge();
        }

        public function testProcessCopyRelationsAndDeleteNonPrimaryModelsInMerge()
        {
            $this->runProcessCopyRelationsAndDeleteNonPrimaryModelsInMerge();
        }

        public function testResolveFormLayoutMetadataForOneColumnDisplay()
        {
            $this->processResolveFormLayoutMetadataForOneColumnDisplay();
        }

        protected function setFirstModel()
        {
            $user                                   = User::getByUsername('steven');
            $contact                                = ContactListViewMergeTestHelper::getFirstModel($user);
            $this->selectedModels[]                 = $contact;
        }

        protected function setSecondModel()
        {
            $user                                   = User::getByUsername('steven');
            $contact                                = ContactListViewMergeTestHelper::getSecondModel($user);
            $this->selectedModels[]                 = $contact;
        }

        protected function setRelatedModels()
        {
            $this->addProject('contacts');
            $this->addProduct('contact');
            $this->addOpportunity();
            $this->addTask();
            $this->addNote();
            $this->addMeeting();
        }

        protected function validatePrimaryModelData()
        {
            $this->assertEmpty(Contact::getByName('shozin shozinson'));
            $this->validateProject();
            $this->validateProduct();
            $this->validateOpportunity();
            $this->validateTask('firstName', 'shozin');
            $this->validateTask('lastName', 'shozinson');
            $this->validateNote('firstName', 'shozin');
            $this->validateNote('lastName', 'shozinson');
            $this->validateMeeting('firstName', 'shozin');
            $this->validateMeeting('lastName', 'shozinson');
        }

        private function addOpportunity()
        {
            $primaryModel = $this->getPrimaryModel();
            $this->assertEquals(0, count($primaryModel->opportunities));
            $opportunity = OpportunityTestHelper::createOpportunityByNameForOwner('UI Services', Yii::app()->user->userModel);
            $opportunity->contacts->add($this->selectedModels[1]);
            $opportunity->save();
        }

        private function validateOpportunity()
        {
            $primaryModel = $this->getPrimaryModel();
            $this->assertEquals(1, count($primaryModel->opportunities));
            $opportunity = $primaryModel->opportunities[0];
            $this->assertEquals('UI Services', $opportunity->name);
        }

        protected function setSelectedModels()
        {
            $contacts = Contact::getByName('Super Man');
            $this->selectedModels[] = $contacts[0];

            $contacts = Contact::getByName('shozin shozinson');
            $this->selectedModels[] = $contacts[0];
        }

        public function testEmailCopyActivity()
        {
            $this->markTestSkipped();
            Yii::app()->user->userModel = User::getByUsername('super');
            $this->setFirstModel();
            $this->setSecondModel();
            $emailMessage = EmailMessageTestHelper::createDraftSystemEmail('Subject 1', Yii::app()->user->userModel);
            $this->assertTrue($emailMessage->save());
            $emailMessageId = $emailMessage->id;
            $emailMessage->forgetAll();

            $emailMessage                 = EmailMessage::getById($emailMessageId);
            $newSender                    = new EmailMessageSender();
            $newSender->fromAddress       = $this->selectedModels[1]->primaryEmail->emailAddress;
            $newSender->fromName          = strval($this->selectedModels[1]);
            $newSender->personsOrAccounts->add($this->selectedModels[1]);
            $emailMessage->sender         = $newSender;
            $emailMessage->save();
            ListViewMergeUtil::processCopyEmailActivity($this->selectedModels[0], $this->selectedModels[1]);

            $emailMessage                 = EmailMessage::getById($emailMessageId);
            $this->assertEquals(strval($this->selectedModels[0]), $emailMessage->sender->fromName);
            $this->assertEquals('test@yahoo.com', $emailMessage->sender->fromAddress);

            //For recipient
            $emailMessage = EmailMessageTestHelper::createDraftSystemEmail('Subject 2', Yii::app()->user->userModel);
            $this->assertTrue($emailMessage->save());
            $emailMessageId = $emailMessage->id;
            $emailMessage->forgetAll();

            $emailMessage                 = EmailMessage::getById($emailMessageId);
            $recipient                    = new EmailMessageRecipient();
            $recipient->toAddress         = $this->selectedModels[1]->primaryEmail->emailAddress;
            $recipient->toName            = strval($this->selectedModels[1]);
            $recipient->type              = EmailMessageRecipient::TYPE_TO;
            $recipient->personsOrAccounts->add($this->selectedModels[1]);
            $emailMessage->recipients->add($recipient);
            $this->assertTrue($emailMessage->save());

            ListViewMergeUtil::processCopyEmailActivity($this->selectedModels[0], $this->selectedModels[1]);

            $emailMessage                 = EmailMessage::getById($emailMessageId);
            $recipients                   = $emailMessage->recipients;
            $this->assertCount(2, $recipients);
            $this->assertEquals(strval($this->selectedModels[0]), $recipients[1]->toName);
            $this->assertEquals('test@yahoo.com', $recipients[1]->toAddress);
            $this->assertCount(1, $recipients[1]->personsOrAccounts);
        }
    }
?>