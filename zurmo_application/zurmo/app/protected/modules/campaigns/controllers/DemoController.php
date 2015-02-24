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

    Yii::import('application.modules.campaigns.controllers.DefaultController', true);
    class CampaignsDemoController extends CampaignsDefaultController
    {
        /**
         * Special method to load a campaign with all types of campaignItemActivity
         */
        public function actionLoadCampaignWithAllItemActivityTypes()
        {
            if (!Group::isUserASuperAdministrator(Yii::app()->user->userModel))
            {
                throw new NotSupportedException();
            }
            $emailBox = EmailBoxUtil::getDefaultEmailBoxByUser(Yii::app()->user->userModel);

            $marketingList                  = new MarketingList();
            $marketingList->name            = 'Demo Marketing List';
            $marketingList->save();
            $campaign                       = new Campaign();
            $campaign->marketingList        = $marketingList;
            $campaign->name                 = 'Campaign with all campaignItemActivity';
            $campaign->subject              = 'Demo for all types of campaignItemActivities';
            $campaign->status               = Campaign::STATUS_COMPLETED;
            $campaign->sendOnDateTime       = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $campaign->supportsRichText     = true;
            $campaign->htmlContent          = 'Demo content';
            $campaign->fromName             = 'Zurmo';
            $campaign->fromAddress          = 'zurmo@zurmo.org';
            $campaign->enableTracking       = true;
            $saved                          = $campaign->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }

            $contacts                       = Contact::getAll();

            //Awaiting queue
            $contact                        = $contacts[0];
            $campaignItem                   = new CampaignItem();
            $campaignItem->processed        = true;
            $campaignItem->campaign         = $campaign;
            $campaignItem->contact          = $contact;
            $campaignItem->unrestrictedSave();

            //Contact is not subscribed
            $contact                        = $contacts[1];
            $marketingList->addNewMember($contact->id, true);
            $campaignItem                   = new CampaignItem();
            $campaignItem->processed        = true;
            $campaignItem->campaign         = $campaign;
            $campaignItem->contact          = $contact;
            $activity                       = new CampaignItemActivity();
            $activity->person               = $contact;
            $activity->campaignItem         = $campaignItem;
            $activity->quantity             = 1;
            $activity->type                 = CampaignItemActivity::TYPE_SKIP;
            $activity->save();
            $campaignItem->unrestrictedSave();

            //Skipped, both primary and secondary are opted out
            $contact                                = $contacts[2];
            $contact->primaryEmail->emailAddress    = $contact->firstName . '1@zurmo.org';
            $contact->primaryEmail->optOut          = true;
            $contact->secondaryEmail->emailAddress  = $contact->firstName . '2@zurmo.org';
            $contact->secondaryEmail->optOut        = true;
            $contact->save();
            $marketingList->addNewMember($contact->id);
            $campaignItem                    = new CampaignItem();
            $campaignItem->processed         = true;
            $campaignItem->campaign          = $campaign;
            $campaignItem->contact           = $contact;
            $activity                        = new CampaignItemActivity();
            $activity->person                = $contact;
            $activity->campaignItem          = $campaignItem;
            $activity->quantity              = 1;
            $activity->type                  = CampaignItemActivity::TYPE_SKIP;
            $activity->save();
            $campaignItem->unrestrictedSave();

            //Skipped, primary is opted out but secondary is not
            $contact                                = $contacts[3];
            $contact->primaryEmail->emailAddress    = $contact->firstName . '1@zurmo.org';
            $contact->primaryEmail->optOut          = true;
            $contact->secondaryEmail->emailAddress  = $contact->firstName . '2@zurmo.org';
            $contact->secondaryEmail->optOut        = false;
            $contact->save();
            $marketingList->addNewMember($contact->id);
            $campaignItem                    = new CampaignItem();
            $campaignItem->processed         = true;
            $campaignItem->campaign          = $campaign;
            $campaignItem->contact           = $contact;
            $activity                        = new CampaignItemActivity();
            $activity->person                = $contact;
            $activity->campaignItem          = $campaignItem;
            $activity->quantity              = 1;
            $activity->type                  = CampaignItemActivity::TYPE_SKIP;
            $activity->save();
            $campaignItem->unrestrictedSave();

            //Skipped, primary and secondary not set
            $contact                         = $contacts[4];
            $contact->primaryEmail           = null;
            $contact->secondaryEmail         = null;
            $contact->save();
            $marketingList->addNewMember($contact->id);
            $campaignItem                    = new CampaignItem();
            $campaignItem->processed         = true;
            $campaignItem->campaign          = $campaign;
            $campaignItem->contact           = $contact;
            $activity                        = new CampaignItemActivity();
            $activity->person                = $contact;
            $activity->campaignItem          = $campaignItem;
            $activity->quantity              = 1;
            $activity->type                  = CampaignItemActivity::TYPE_SKIP;
            $activity->save();
            $campaignItem->unrestrictedSave();

            //Skipped, primary not set but secondary is set
            $contact                                = $contacts[5];
            $contact->primaryEmail                  = null;
            $contact->secondaryEmail->emailAddress  = $contact->firstName . '@zurmo.org';
            $contact->secondaryEmail->optOut        = false;
            $contact->save();
            $marketingList->addNewMember($contact->id);
            $campaignItem                    = new CampaignItem();
            $campaignItem->processed         = true;
            $campaignItem->campaign          = $campaign;
            $campaignItem->contact           = $contact;
            $activity                        = new CampaignItemActivity();
            $activity->person                = $contact;
            $activity->campaignItem          = $campaignItem;
            $activity->quantity              = 1;
            $activity->type                  = CampaignItemActivity::TYPE_SKIP;
            $activity->save();
            $campaignItem->unrestrictedSave();

            //Queued
            $contact                             = $contacts[6];
            $campaignItem                        = new CampaignItem();
            $campaignItem->processed             = true;
            $campaignItem->campaign              = $campaign;
            $campaignItem->contact               = $contact;
            $activity                            = new CampaignItemActivity();
            $activity->person                    = $contact;
            $activity->campaignItem              = $campaignItem;
            $activity->quantity                  = 1;
            $activity->save();

            $emailMessage              = new EmailMessage();
            $emailMessage->setScenario('importModel');
            $emailMessage->owner       = $contact->owner;
            $emailMessage->subject     = 'Subject';
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My First Message';
            $emailContent->htmlContent = 'Some fake HTML content';
            $emailMessage->content     = $emailContent;
            //Sending is current user (super)
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'zurmo@zurmo.org';
            $sender->fromName          = 'Zurmo';
            $sender->personsOrAccounts->add(Yii::app()->user->userModel);
            $emailMessage->sender      = $sender;
            //Recipient is BobMessage
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = 'bob.message@zurmotest.com';
            $recipient->toName          = strval($contact);
            $recipient->personsOrAccounts->add($contact);
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $emailMessage->recipients->add($recipient);
            $emailMessage->folder       = EmailFolder::getByBoxAndType($emailBox, EmailFolder::TYPE_OUTBOX);
            $emailMessage->createdDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $emailMessage->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            $campaignItem->emailMessage = $emailMessage;
            $campaignItem->unrestrictedSave();

            //Queued with error
            $contact                             = $contacts[7];
            $campaignItem                        = new CampaignItem();
            $campaignItem->processed             = true;
            $campaignItem->campaign              = $campaign;
            $campaignItem->contact               = $contact;
            $activity                            = new CampaignItemActivity();
            $activity->person                    = $contact;
            $activity->campaignItem              = $campaignItem;
            $activity->quantity                  = 1;
            $activity->save();

            $emailMessage                   = new EmailMessage();
            $emailMessage->setScenario('importModel');
            $emailMessage->owner            = $contact->owner;
            $emailMessage->subject          = 'Subject';
            $emailContent                   = new EmailMessageContent();
            $emailContent->textContent      = 'My First Message';
            $emailContent->htmlContent      = 'Some fake HTML content';
            $emailMessage->content          = $emailContent;
            //Sending is current user (super)
            $sender                         = new EmailMessageSender();
            $sender->fromAddress            = 'zurmo@zurmo.org';
            $sender->fromName               = 'Zurmo';
            $sender->personsOrAccounts->add(Yii::app()->user->userModel);
            $emailMessage->sender           = $sender;
            //Recipient is BobMessage
            $recipient                      = new EmailMessageRecipient();
            $recipient->toAddress           = 'bob.message@zurmotest.com';
            $recipient->toName              = strval($contact);
            $recipient->personsOrAccounts->add($contact);
            $recipient->type                = EmailMessageRecipient::TYPE_TO;
            $emailMessage->recipients->add($recipient);
            $emailMessage->folder           = EmailFolder::getByBoxAndType($emailBox, EmailFolder::TYPE_OUTBOX_ERROR);
            $emailMessage->createdDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $emailMessage->sendAttempts = 2;
            $emailMessageError = new EmailMessageSendError();
            $emailMessageError->serializedData = serialize(array('code' => '0001', 'message' => 'Error Message'));
            $emailMessage->error = $emailMessageError;
            $emailMessage->createdDateTime  = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $emailMessage->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            $campaignItem->emailMessage = $emailMessage;
            $campaignItem->unrestrictedSave();

            //Failure
            $contact                             = $contacts[8];
            $campaignItem                        = new CampaignItem();
            $campaignItem->processed             = true;
            $campaignItem->campaign              = $campaign;
            $campaignItem->contact               = $contact;
            $activity                            = new CampaignItemActivity();
            $activity->person                    = $contact;
            $activity->campaignItem              = $campaignItem;
            $activity->quantity                  = 1;
            $activity->save();

            $emailMessage                   = new EmailMessage();
            $emailMessage->setScenario('importModel');
            $emailMessage->owner            = $contact->owner;
            $emailMessage->subject          = 'Subject';
            $emailContent                   = new EmailMessageContent();
            $emailContent->textContent      = 'My First Message';
            $emailContent->htmlContent      = 'Some fake HTML content';
            $emailMessage->content          = $emailContent;
            //Sending is current user (super)
            $sender                         = new EmailMessageSender();
            $sender->fromAddress            = 'zurmo@zurmo.org';
            $sender->fromName               = 'Zurmo';
            $sender->personsOrAccounts->add(Yii::app()->user->userModel);
            $emailMessage->sender           = $sender;
            //Recipient is BobMessage
            $recipient                      = new EmailMessageRecipient();
            $recipient->toAddress           = 'bob.message@zurmotest.com';
            $recipient->toName              = strval($contact);
            $recipient->personsOrAccounts->add($contact);
            $recipient->type                = EmailMessageRecipient::TYPE_TO;
            $emailMessage->recipients->add($recipient);
            $emailMessage->folder           = EmailFolder::getByBoxAndType($emailBox, EmailFolder::TYPE_OUTBOX_FAILURE);
            $emailMessage->createdDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $emailMessage->sendAttempts = 3;
            $emailMessageError = new EmailMessageSendError();
            $emailMessageError->serializedData = serialize(array('code' => '0001', 'message' => 'Error Message'));
            $emailMessage->error = $emailMessageError;
            $emailMessage->createdDateTime  = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $emailMessage->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            $campaignItem->emailMessage = $emailMessage;
            $campaignItem->unrestrictedSave();

            //Sent, open, click, bounce
            $contact                             = $contacts[9];
            $campaignItem                        = new CampaignItem();
            $campaignItem->processed             = true;
            $campaignItem->campaign              = $campaign;
            $campaignItem->contact               = $contact;
            $activity                            = new CampaignItemActivity();
            $activity->person                    = $contact;
            $activity->campaignItem              = $campaignItem;
            $activity->type                      = CampaignItemActivity::TYPE_CLICK;
            $activity->quantity                  = rand(1, 50);
            $activity->latestDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + rand(100, 1000));
            $activity->latestSourceIP            = '10.11.12.13';
            $activity->save();
            $activity                            = new CampaignItemActivity();
            $activity->person                    = $contact;
            $activity->campaignItem              = $campaignItem;
            $activity->type                      = CampaignItemActivity::TYPE_OPEN;
            $activity->quantity                  = rand(1, 50);
            $activity->latestDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + rand(100, 1000));
            $activity->latestSourceIP            = '10.11.12.13';
            $activity->save();
            $activity                            = new CampaignItemActivity();
            $activity->person                    = $contact;
            $activity->campaignItem              = $campaignItem;
            $activity->type                      = CampaignItemActivity::TYPE_BOUNCE;
            $activity->quantity                  = rand(1, 50);
            $activity->latestDateTime            = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + rand(100, 1000));
            $activity->latestSourceIP            = '10.11.12.13';
            $activity->save();

            $emailMessage              = new EmailMessage();
            $emailMessage->setScenario('importModel');
            $emailMessage->owner       = $contact->owner;
            $emailMessage->subject     = 'Subject';
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'My First Message';
            $emailContent->htmlContent = 'Some fake HTML content';
            $emailMessage->content     = $emailContent;
            //Sending is current user (super)
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'zurmo@zurmo.org';
            $sender->fromName          = 'Zurmo';
            $sender->personsOrAccounts->add(Yii::app()->user->userModel);
            $emailMessage->sender      = $sender;
            //Recipient is BobMessage
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = 'bob.message@zurmotest.com';
            $recipient->toName          = strval($contact);
            $recipient->personsOrAccounts->add($contact);
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $emailMessage->recipients->add($recipient);
            $emailMessage->folder       = EmailFolder::getByBoxAndType($emailBox, EmailFolder::TYPE_SENT);
            $emailMessage->sentDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $emailMessage->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }

            $emailMessage->save();
            $campaignItem->emailMessage = $emailMessage;
            $campaignItem->unrestrictedSave();
        }
    }
?>
