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
    class CampaignItemsUtilTest extends AutoresponderOrCampaignBaseTest
    {
        // We don't need to add separate tests for tracking scenarios here because we have already gained more than
        //  sufficient coverage in CampaignItemActivityUtilTest and EmailMessageActivityUtilTest for those.
        protected $user;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user                 = User::getByUsername('super');
            Yii::app()->user->userModel = $this->user;
            Campaign::deleteAll();
        }

        /**
         * Do not throw exception. That means it passes
         */
        public function testProcessDueCampaignItemThrowsExceptionWhenContentHasInvalidMergeTagsForOnlyHtml()
        {
            $html = "[[FIRST^NAME]], You are receiving this email";
            $text = null;
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 01', $this->user);
            $marketingList              = MarketingListTestHelper::populateMarketingListByName('marketingList 01');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 01',
                                                                                'subject 01',
                                                                                $text,
                                                                                $html,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList,
                                                                                false);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
        }

        /**
         * @depends testProcessDueCampaignItemThrowsExceptionWhenContentHasInvalidMergeTagsForOnlyHtml
         * Do not throw exception. That means it passes
         */
        public function testProcessDueCampaignItemThrowsExceptionWhenContentHasInvalidMergeTagsForOnlyText()
        {
            $text = "[[FIRST^NAME]], You are receiving this email";
            $html = null;
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 01', $this->user);
            $marketingList              = MarketingListTestHelper::populateMarketingListByName('marketingList 01');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 01',
                                                                                'subject 01',
                                                                                $text,
                                                                                $html,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList,
                                                                                false);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
        }

        /**
         * @depends testProcessDueCampaignItemThrowsExceptionWhenContentHasInvalidMergeTagsForOnlyText
         * @expectedException NotFoundException
         */
        public function testProcessDueCampaignItemThrowsExceptionWhenNoContactIsAvailable()
        {
            $campaignItem          = new CampaignItem();
            $this->processDueItem($campaignItem);
        }

        /**
         * @depends testProcessDueCampaignItemThrowsExceptionWhenNoContactIsAvailable
         * @expectedException NotSupportedException
         * @expectedExceptionMessage Provided content contains few invalid merge tags
         */
        public function testProcessDueCampaignItemThrowsExceptionWhenContentHasInvalidMergeTags()
        {
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 01', $this->user);
            $marketingList              = MarketingListTestHelper::populateMarketingListByName('marketingList 01');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 01',
                                                                                'subject 01',
                                                                                '[[TEXT^CONTENT]]',
                                                                                '[[HTML^CONTENT]]',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList,
                                                                                false);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
        }

        /**
         * @depends testProcessDueCampaignItemThrowsExceptionWhenContentHasInvalidMergeTags
         */
        public function testProcessDueCampaignItemDoesNotThrowExceptionWhenContactHasNoPrimaryEmail()
        {
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 02', $this->user);
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 02');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 02',
                                                                                'subject 02',
                                                                                'text content',
                                                                                'html content',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                0,
                                                                                $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
            $this->assertEquals(1, $campaignItem->processed);
            $emailMessage               = $campaignItem->emailMessage;
            $this->assertEquals($marketingList->owner->id, $emailMessage->owner->id);
            $marketingListPermissions   = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($marketingList);
            $emailMessagePermissions    = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($emailMessage);
            $this->assertEquals($marketingListPermissions, $emailMessagePermissions);
            $this->assertNull($emailMessage->subject);
            $this->assertNull($emailMessage->content->textContent);
            $this->assertNull($emailMessage->content->htmlContent);
            $this->assertNull($emailMessage->sender->fromAddress);
            $this->assertNull($emailMessage->sender->fromName);
            $this->assertEquals(0, $emailMessage->recipients->count());

            //Test with empty primary email address
            $contact->primaryEmail->emailAddress = '';
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
            $this->assertEquals(1, $campaignItem->processed);
            $emailMessage               = $campaignItem->emailMessage;
            $this->assertEquals($marketingList->owner->id, $emailMessage->owner->id);
            $marketingListPermissions   = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($marketingList);
            $emailMessagePermissions    = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($emailMessage);
            $this->assertEquals($marketingListPermissions, $emailMessagePermissions);
            $this->assertNull($emailMessage->subject);
            $this->assertNull($emailMessage->content->textContent);
            $this->assertNull($emailMessage->content->htmlContent);
            $this->assertNull($emailMessage->sender->fromAddress);
            $this->assertNull($emailMessage->sender->fromName);
            $this->assertEquals(0, $emailMessage->recipients->count());
        }

        /**
         * @depends testProcessDueCampaignItemDoesNotThrowExceptionWhenContactHasNoPrimaryEmail
         */
        public function testProcessDueCampaignItemDoesNotThrowExceptionWhenContactHasPrimaryEmail()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 03', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 03');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 03',
                                                                                'subject 03',
                                                                                'text content',
                                                                                'html content',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList);

            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
            $this->assertEquals(1, $campaignItem->processed);
            $emailMessage               = $campaignItem->emailMessage;
            $this->assertEquals($marketingList->owner->id, $emailMessage->owner->id);
            $marketingListPermissions   = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($marketingList);
            $emailMessagePermissions    = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($emailMessage);
            $this->assertEquals($marketingListPermissions, $emailMessagePermissions);
            $this->assertEquals($campaign->subject, $emailMessage->subject);
            $this->assertContains($campaign->textContent, $emailMessage->content->textContent);
            $this->assertContains('/marketingLists/external/', $emailMessage->content->textContent);
            $this->assertEquals(2, substr_count($emailMessage->content->textContent, '/marketingLists/external/'));
            $this->assertContains($campaign->htmlContent, $emailMessage->content->htmlContent);
            $this->assertContains('/marketingLists/external/', $emailMessage->content->htmlContent);
            $this->assertEquals(2, substr_count($emailMessage->content->htmlContent, '/marketingLists/external/'));
            $this->assertEquals('support@zurmo.com', $emailMessage->sender->fromAddress);
            $this->assertEquals('Support Team',      $emailMessage->sender->fromName);
            $this->assertEquals(1, $emailMessage->recipients->count());
            $recipients                 = $emailMessage->recipients;
            $this->assertEquals(strval($contact), $recipients[0]->toName);
            $this->assertEquals($email->emailAddress, $recipients[0]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[0]->type);
            $this->assertEquals($contact->getClassId('Item'), $recipients[0]->personsOrAccounts[0]->id);
            $headersArray               = array('zurmoItemId' => $campaignItem->id,
                                                'zurmoItemClass' => get_class($campaignItem),
                                                'zurmoPersonId' => $contact->getClassId('Person'));
            $expectedHeaders            = serialize($headersArray);
            $this->assertEquals($expectedHeaders, $emailMessage->headers);
        }

        /**
         * @depends testProcessDueCampaignItemDoesNotThrowExceptionWhenContactHasNoPrimaryEmail
         */
        public function testProcessDueCampaignItemWithCustomFromAddressAndFromName()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 04', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 04',
                                                                                            'description',
                                                                                            'CustomFromName',
                                                                                            'custom@from.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 04',
                                                                                'subject 04',
                                                                                'text content',
                                                                                'html content',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                0,
                                                                                $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
            $this->assertEquals(1, $campaignItem->processed);
            $emailMessage               = $campaignItem->emailMessage;
            $this->assertEquals($marketingList->owner->id, $emailMessage->owner->id);
            $marketingListPermissions   = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($marketingList);
            $emailMessagePermissions    = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($emailMessage);
            $this->assertEquals($marketingListPermissions, $emailMessagePermissions);
            $this->assertEquals($campaign->subject, $emailMessage->subject);
            $this->assertContains($campaign->textContent, $emailMessage->content->textContent);
            $this->assertContains('/marketingLists/external/', $emailMessage->content->textContent);
            $this->assertEquals(2, substr_count($emailMessage->content->textContent, '/marketingLists/external/'));
            $this->assertContains($campaign->htmlContent, $emailMessage->content->htmlContent);
            $this->assertContains('/marketingLists/external/', $emailMessage->content->htmlContent);
            $this->assertEquals(2, substr_count($emailMessage->content->htmlContent, '/marketingLists/external/'));
            $this->assertEquals('support@zurmo.com', $emailMessage->sender->fromAddress);
            $this->assertEquals('Support Team',      $emailMessage->sender->fromName);
            $this->assertEquals(1, $emailMessage->recipients->count());
            $recipients                 = $emailMessage->recipients;
            $this->assertEquals(strval($contact), $recipients[0]->toName);
            $this->assertEquals($email->emailAddress, $recipients[0]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[0]->type);
            $this->assertEquals($contact->getClassId('Item'), $recipients[0]->personsOrAccounts[0]->id);
            $headersArray               = array('zurmoItemId' => $campaignItem->id,
                                                'zurmoItemClass' => get_class($campaignItem),
                                                'zurmoPersonId' => $contact->getClassId('Person'));
            $expectedHeaders            = serialize($headersArray);
            $this->assertEquals($expectedHeaders, $emailMessage->headers);
        }

        /**
         * @depends testProcessDueCampaignItemWithCustomFromAddressAndFromName
         */
        public function testProcessDueCampaignItemWithValidMergeTags()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 05', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 05',
                                                                                            'description',
                                                                                            'CustomFromName',
                                                                                            'custom@from.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 05',
                                                                                'subject 05',
                                                                                'Dr. [[FIRST^NAME]] [[LAST^NAME]]',
                                                                                '<b>[[LAST^NAME]]</b>, [[FIRST^NAME]]',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
            $this->assertEquals(1, $campaignItem->processed);
            $emailMessage               = $campaignItem->emailMessage;
            $this->assertEquals($marketingList->owner->id, $emailMessage->owner->id);
            $marketingListPermissions   = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($marketingList);
            $emailMessagePermissions    = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($emailMessage);
            $this->assertEquals($marketingListPermissions, $emailMessagePermissions);
            $this->assertEquals($campaign->subject, $emailMessage->subject);
            $this->assertNotEquals($campaign->textContent, $emailMessage->content->textContent);
            $this->assertNotEquals($campaign->htmlContent, $emailMessage->content->htmlContent);
            $this->assertContains('Dr. contact 05 contact 05son', $emailMessage->content->textContent);
            $this->assertContains('/marketingLists/external/', $emailMessage->content->textContent);
            $this->assertEquals(2, substr_count($emailMessage->content->textContent, '/marketingLists/external/'));
            $this->assertContains('<b>contact 05son</b>, contact 05', $emailMessage->content->htmlContent);
            $this->assertContains('/marketingLists/external/', $emailMessage->content->htmlContent);
            $this->assertEquals(2, substr_count($emailMessage->content->htmlContent, '/marketingLists/external/'));
            $this->assertEquals('support@zurmo.com', $emailMessage->sender->fromAddress);
            $this->assertEquals('Support Team',      $emailMessage->sender->fromName);
            $this->assertEquals(1, $emailMessage->recipients->count());
            $recipients                 = $emailMessage->recipients;
            $this->assertEquals(strval($contact), $recipients[0]->toName);
            $this->assertEquals($email->emailAddress, $recipients[0]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[0]->type);
            $this->assertEquals($contact->getClassId('Item'), $recipients[0]->personsOrAccounts[0]->id);
            $headersArray               = array('zurmoItemId' => $campaignItem->id,
                                                'zurmoItemClass' => get_class($campaignItem),
                                                'zurmoPersonId' => $contact->getClassId('Person'));
            $expectedHeaders            = serialize($headersArray);
            $this->assertEquals($expectedHeaders, $emailMessage->headers);
        }

        /**
         * @//depends testProcessDueCampaignItemWithValidMergeTags
         */
        public function testProcessDueCampaignItemWithAttachments()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 06', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 06',
                                                                                                    'description',
                                                                                                    'CustomFromName',
                                                                                                    'custom@from.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 06',
                                                                                'subject 06',
                                                                                'Dr. [[FIRST^NAME]] [[LAST^NAME]]',
                                                                                '<b>[[LAST^NAME]]</b>, [[FIRST^NAME]]',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            $fileNames              = array('testImage.png', 'testZip.zip', 'testPDF.pdf');
            $files                  = array();
            foreach ($fileNames as $index => $fileName)
            {
                $file                       = ZurmoTestHelper::createFileModel($fileName);
                $files[$index]['name']      = $fileName;
                $files[$index]['type']      = $file->type;
                $files[$index]['size']      = $file->size;
                $files[$index]['contents']  = $file->fileContent->content;
                $campaign->files->add($file);
            }
            $this->assertTrue($campaign->save());
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
            $this->assertEquals(1, $campaignItem->processed);
            $emailMessage               = $campaignItem->emailMessage;
            $this->assertEquals($marketingList->owner->id, $emailMessage->owner->id);
            $marketingListPermissions   = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($marketingList);
            $emailMessagePermissions    = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($emailMessage);
            $this->assertEquals($marketingListPermissions, $emailMessagePermissions);
            $this->assertEquals($campaign->subject, $emailMessage->subject);
            $this->assertNotEquals($campaign->textContent, $emailMessage->content->textContent);
            $this->assertNotEquals($campaign->htmlContent, $emailMessage->content->htmlContent);
            $this->assertContains('Dr. contact 06 contact 06son', $emailMessage->content->textContent);
            $this->assertContains('/marketingLists/external/', $emailMessage->content->textContent);
            $this->assertEquals(2, substr_count($emailMessage->content->textContent, '/marketingLists/external/'));
            $this->assertContains('<b>contact 06son</b>, contact 06', $emailMessage->content->htmlContent);
            $this->assertContains('/marketingLists/external/', $emailMessage->content->htmlContent);
            $this->assertEquals(2, substr_count($emailMessage->content->htmlContent, '/marketingLists/external/'));
            $this->assertEquals('support@zurmo.com', $emailMessage->sender->fromAddress);
            $this->assertEquals('Support Team',      $emailMessage->sender->fromName);
            $this->assertEquals(1, $emailMessage->recipients->count());
            $recipients                 = $emailMessage->recipients;
            $this->assertEquals(strval($contact), $recipients[0]->toName);
            $this->assertEquals($email->emailAddress, $recipients[0]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[0]->type);
            $this->assertEquals($contact->getClassId('Item'), $recipients[0]->personsOrAccounts[0]->id);
            $this->assertNotEmpty($emailMessage->files);
            $this->assertCount(count($files), $emailMessage->files);
            foreach ($campaign->files as $index => $file)
            {
                $this->assertEquals($file->name, $emailMessage->files[$index]->name);
                $this->assertEquals($file->type, $emailMessage->files[$index]->type);
                $this->assertEquals($file->size, $emailMessage->files[$index]->size);
                //CampaingItem should share the Attachments content from Campaign
                $this->assertEquals($file->fileContent->content, $emailMessage->files[$index]->fileContent->content);
            }
            $headersArray               = array('zurmoItemId' => $campaignItem->id,
                                                'zurmoItemClass' => get_class($campaignItem),
                                                'zurmoPersonId' => $contact->getClassId('Person'));
            $expectedHeaders            = serialize($headersArray);
            $this->assertEquals($expectedHeaders, $emailMessage->headers);
        }

        /**
         * @depends testProcessDueCampaignItemWithAttachments
         */
        public function testGenerateCampaignItemsForDueCampaigns()
        {
            $marketingList      = MarketingListTestHelper::createMarketingListByName('marketingList 07');
            $marketingListId    = $marketingList->id;
            for ($i = 0; $i < 5; $i++)
            {
                $contact    = ContactTestHelper::createContactByNameForOwner('campaignContact ' . $i, $this->user);
                MarketingListMemberTestHelper::createMarketingListMember($i % 2, $marketingList, $contact);
            }
            $marketingList->forgetAll();

            $marketingList      = MarketingList::getById($marketingListId);
            $campaign           = CampaignTestHelper::createCampaign('campaign 07',
                                                                        'subject 07',
                                                                        'text 07',
                                                                        'html 07',
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        null,
                                                                        $marketingList);
            $this->assertNotNull($campaign);
            $campaign->forgetAll();
            $campaignId         = $campaign->id;
            $campaignItems      = CampaignItem::getByProcessedAndCampaignId(0, $campaignId);
            $this->assertEmpty($campaignItems);
            //Process open campaigns.
            Yii::app()->jobQueue->deleteAll();
            $this->assertCount(0, Yii::app()->jobQueue->getAll());
            $this->assertTrue(CampaignItemsUtil::generateCampaignItemsForDueCampaigns());
            $jobs = Yii::app()->jobQueue->getAll();
            $this->assertCount(1, $jobs);
            $this->assertEquals('CampaignQueueMessagesInOutbox', $jobs[5][0]['jobType']);
            $campaign           = Campaign::getById($campaignId);
            $this->assertNotNull($campaign);
            $this->assertEquals(Campaign::STATUS_PROCESSING, $campaign->status);
            $campaignItems      = CampaignItem::getByProcessedAndCampaignId(0, $campaignId);
            $this->assertNotEmpty($campaignItems);
            $this->assertCount(5, $campaignItems);
            // TODO: @Shoaibi: Low: Add tests for the other campaign type.
        }

        /**
         * @depends testGenerateCampaignItemsForDueCampaigns
         */
        public function testProcessDueCampaignItemWithOptout()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $email->optOut              = true;
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 08', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 08',
                                                                                                'description',
                                                                                                'CustomFromName',
                                                                                                'custom@from.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 08',
                                                                                'subject 08',
                                                                                'Dr. [[FIRST^NAME]] [[LAST^NAME]]',
                                                                                '<b>[[LAST^NAME]]</b>, [[FIRST^NAME]]',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
            $this->assertEquals(1, $campaignItem->processed);
            $personId                   = $contact->getClassId('Person');
            $activities                 = CampaignItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                                                                                CampaignItemActivity::TYPE_SKIP,
                                                                                $campaignItem->id,
                                                                                $personId);
            $this->assertNotEmpty($activities);
            $this->assertCount(1, $activities);
        }

        /**
         * @depends testProcessDueCampaignItemWithOptout
         */
        public function testProcessDueCampaignItemWithReturnPathHeaders()
        {
            ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', 'bounceReturnPath', 'bounce@zurmo.com');
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 09', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 09',
                                                                                                'description',
                                                                                                'CustomFromName',
                                                                                                'custom@from.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 09',
                                                                                'subject 09',
                                                                                'Dr. [[FIRST^NAME]] [[LAST^NAME]]',
                                                                                '<b>[[LAST^NAME]]</b>, [[FIRST^NAME]]',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
            $this->assertEquals(1, $campaignItem->processed);
            $emailMessage               = $campaignItem->emailMessage;
            $this->assertEquals($marketingList->owner->id, $emailMessage->owner->id);
            $marketingListPermissions   = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($marketingList);
            $emailMessagePermissions    = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($emailMessage);
            $this->assertEquals($marketingListPermissions, $emailMessagePermissions);
            $this->assertEquals($campaign->subject, $emailMessage->subject);
            $this->assertNotEquals($campaign->textContent, $emailMessage->content->textContent);
            $this->assertNotEquals($campaign->htmlContent, $emailMessage->content->htmlContent);
            $this->assertContains('Dr. contact 09 contact 09son', $emailMessage->content->textContent);
            $this->assertContains('/marketingLists/external/', $emailMessage->content->textContent);
            $this->assertEquals(2, substr_count($emailMessage->content->textContent, '/marketingLists/external/'));
            $this->assertContains('<b>contact 09son</b>, contact 09', $emailMessage->content->htmlContent);
            $this->assertContains('/marketingLists/external/', $emailMessage->content->htmlContent);
            $this->assertEquals(2, substr_count($emailMessage->content->htmlContent, '/marketingLists/external/'));
            $this->assertEquals('support@zurmo.com', $emailMessage->sender->fromAddress);
            $this->assertEquals('Support Team',      $emailMessage->sender->fromName);
            $this->assertEquals(1, $emailMessage->recipients->count());
            $recipients                 = $emailMessage->recipients;
            $this->assertEquals(strval($contact), $recipients[0]->toName);
            $this->assertEquals($email->emailAddress, $recipients[0]->toAddress);
            $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[0]->type);
            $this->assertEquals($contact->getClassId('Item'), $recipients[0]->personsOrAccounts[0]->id);
            $headersArray               = array('zurmoItemId' => $campaignItem->id,
                                                'zurmoItemClass' => get_class($campaignItem),
                                                'zurmoPersonId' => $contact->getClassId('Person'),
                                                'Return-Path' => 'bounce@zurmo.com');
            $expectedHeaders            = serialize($headersArray);
            $this->assertEquals($expectedHeaders, $emailMessage->headers);
        }

        /**
         * @depends testProcessDueCampaignItemWithReturnPathHeaders
         */
        public function testProcessDueCampaignItemWithoutHtmlContent()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo10@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 10', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 10',
                                                                                            'description',
                                                                                            'CustomFromName',
                                                                                            'custom@from.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 10',
                                                                             'subject 10',
                                                                             'Dr. [[FIRST^NAME]] [[LAST^NAME]]',
                                                                             null,
                                                                             null,
                                                                             null,
                                                                             false,
                                                                             null,
                                                                             null,
                                                                             null,
                                                                             $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
            $emailMessage               = $campaignItem->emailMessage;
            $this->assertNotNull($emailMessage->content->textContent);
            $this->assertNull   ($emailMessage->content->htmlContent);
        }

        /**
         * @depends testProcessDueCampaignItemWithoutHtmlContent
         */
        public function testProcessDueCampaignItemWithoutTextContent()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo11@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 11', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 11',
                                                                                            'description',
                                                                                            'CustomFromName',
                                                                                            'custom@from.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 11',
                                                                             'subject 11',
                                                                             null,
                                                                             '<b>[[LAST^NAME]]</b>, [[FIRST^NAME]]',
                                                                             null,
                                                                             null,
                                                                             null,
                                                                             null,
                                                                             null,
                                                                             null,
                                                                             $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
            $emailMessage               = $campaignItem->emailMessage;
            $this->assertNull   ($emailMessage->content->textContent);
            $this->assertNotNull($emailMessage->content->htmlContent);
        }

        /**
         * @depends testProcessDueCampaignItemWithoutTextContent
         */
        public function testProcessDueCampaignItemWithoutRichTextSupport()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo12@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 12', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 12',
                                                                                            'description',
                                                                                            'CustomFromName',
                                                                                            'custom@from.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 12',
                                                                             'subject 12',
                                                                             'Dr. [[FIRST^NAME]] [[LAST^NAME]]',
                                                                             '<b>[[LAST^NAME]]</b>, [[FIRST^NAME]]',
                                                                             null,
                                                                             null,
                                                                             false,
                                                                             null,
                                                                             null,
                                                                             null,
                                                                             $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
            $emailMessage               = $campaignItem->emailMessage;
            $this->assertNotNull($emailMessage->content->textContent);
            $this->assertNull   ($emailMessage->content->htmlContent);
        }

        /**
         * @depends testProcessDueCampaignItemWithoutRichTextSupport
         */
        public function testProcessDueCampaignItemWithModelUrlMergeTag()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo13@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 13', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 13',
                                                                                                'description',
                                                                                                'CustomFromName',
                                                                                                'custom@from.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign 13',
                                                                                'subject 13',
                                                                                'Url: [[MODEL^URL]]',
                                                                                'Click <a href="[[MODEL^URL]]">here</a>',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
            $emailMessage               = $campaignItem->emailMessage;
            $this->assertNotNull   ($emailMessage->content->textContent);
            $this->assertNotNull($emailMessage->content->htmlContent);
            $this->assertContains('/contacts/default/details?id=' . $contact->id, $emailMessage->content->textContent);
            $this->assertContains('/contacts/default/details?id=' . $contact->id, $emailMessage->content->htmlContent);
        }

        /**
         * @depends testProcessDueCampaignItemWithModelUrlMergeTag
         */
        public function testProcessDueCampaignItemSenderIsSetFromCampaign()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo14@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 14', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 14',
                                                                                            'description',
                                                                                            null,
                                                                                            null);
            $campaign                   = CampaignTestHelper::createCampaign('campaign 12',
                                                                             'subject 12',
                                                                             'Dr. [[FIRST^NAME]] [[LAST^NAME]]',
                                                                             '<b>[[LAST^NAME]]</b>, [[FIRST^NAME]]',
                                                                             'testFromName',
                                                                             'test@zurmo.com',
                                                                             null,
                                                                             null,
                                                                             null,
                                                                             null,
                                                                             $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
            $emailMessage               = $campaignItem->emailMessage;
            $this->assertEquals('testFromName',   $emailMessage->sender->fromName);
            $this->assertEquals('test@zurmo.com', $emailMessage->sender->fromAddress);
        }

        /**
         * @depends testProcessDueCampaignItemSenderIsSetFromCampaign
         */
        public function testProcessDueCampaignItemWithUnsubscribeUrlMergeTag()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo15@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 15', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 15',
                                                                                                'description',
                                                                                                null,
                                                                                                null);
            $campaign                   = CampaignTestHelper::createCampaign('campaign 13',
                                                                                'subject 13',
                                                                                GlobalMarketingFooterUtil::resolveUnsubscribeUrlMergeTag(),
                                                                                GlobalMarketingFooterUtil::resolveUnsubscribeUrlMergeTag(),
                                                                                'testFromName',
                                                                                'test@zurmo.com',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
            $textContent                = $campaignItem->emailMessage->content->textContent;
            $htmlContent                = $campaignItem->emailMessage->content->htmlContent;
            $this->assertNotEquals($campaign->textContent, $textContent);
            $this->assertNotEquals($campaign->htmlContent, $htmlContent);
            $this->assertContains('localhost', $textContent);
            $this->assertEquals(1, substr_count($textContent, '/marketingLists/external/unsubscribe?hash='));
            $this->assertContains('localhost', $htmlContent);
            $this->assertEquals(1, substr_count($htmlContent, '/marketingLists/external/unsubscribe?hash='));
            $this->assertContains('<img width="1" height="1" src="localhost', $htmlContent);
            $this->assertContains('/tracking/default/track?id=', $htmlContent);
            $this->assertNotContains('/marketingLists/external/manageSubscriptions', $htmlContent);
        }

        /**
         * @depends testProcessDueCampaignItemWithUnsubscribeUrlMergeTag
         */
        public function testProcessDueCampaignItemWithManageSubscriptionsUrlMergeTag()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo16@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 16', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 16',
                                                                                                'description',
                                                                                                null,
                                                                                                null);
            $campaign                   = CampaignTestHelper::createCampaign('campaign 14',
                                                                                'subject 14',
                                                                                GlobalMarketingFooterUtil::resolveManageSubscriptionsMergeTag(),
                                                                                GlobalMarketingFooterUtil::resolveManageSubscriptionsMergeTag(),
                                                                                'testFromName',
                                                                                'test@zurmo.com',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
            $textContent                = $campaignItem->emailMessage->content->textContent;
            $htmlContent                = $campaignItem->emailMessage->content->htmlContent;
            $this->assertNotEquals($campaign->textContent, $textContent);
            $this->assertNotEquals($campaign->htmlContent, $htmlContent);
            $this->assertContains('localhost', $textContent);
            $this->assertEquals(1, substr_count($textContent, '/marketingLists/external/manageSubscriptions?hash='));
            $this->assertContains('localhost', $htmlContent);
            $this->assertEquals(1, substr_count($htmlContent, '/marketingLists/external/manageSubscriptions?hash='));
            $this->assertContains('<img width="1" height="1" src="localhost', $htmlContent);
            $this->assertContains('/tracking/default/track?id=', $htmlContent);
            $this->assertNotContains('/marketingLists/external/unsubscribe', $htmlContent);
        }

        /**
         * @depends testProcessDueCampaignItemWithManageSubscriptionsUrlMergeTag
         */
        public function testProcessDueCampaignItemWithUnsubscribeAndManageSubscriptionsUrlMergeTags()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo17@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 17', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 17',
                                                                                                'description',
                                                                                                null,
                                                                                                null);
            $campaign                   = CampaignTestHelper::createCampaign('campaign 15',
                                                                                'subject 15',
                                                                                GlobalMarketingFooterUtil::resolveUnsubscribeUrlMergeTag() . ', ' . // Not Coding Standard
                                                                                GlobalMarketingFooterUtil::resolveManageSubscriptionsMergeTag(),
                                                                                GlobalMarketingFooterUtil::resolveUnsubscribeUrlMergeTag() . ', ' . // Not Coding Standard
                                                                                GlobalMarketingFooterUtil::resolveManageSubscriptionsMergeTag(),
                                                                                'testFromName',
                                                                                'test@zurmo.com',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
            $textContent                = $campaignItem->emailMessage->content->textContent;
            $htmlContent                = $campaignItem->emailMessage->content->htmlContent;
            $this->assertNotEquals($campaign->textContent, $textContent);
            $this->assertNotEquals($campaign->htmlContent, $htmlContent);
            $this->assertContains('localhost', $textContent);
            $this->assertEquals(1, substr_count($textContent, '/marketingLists/external/unsubscribe?hash='));
            $this->assertContains('localhost', $htmlContent);
            $this->assertEquals(1, substr_count($htmlContent, '/marketingLists/external/unsubscribe?hash='));
            $this->assertContains('<img width="1" height="1" src="localhost', $htmlContent);
            $this->assertContains('/tracking/default/track?id=', $htmlContent);
            $this->assertContains(', localhost', $textContent);
            $this->assertEquals(1, substr_count($textContent, '/marketingLists/external/manageSubscriptions?hash='));
            $this->assertContains(', localhost', $htmlContent);
            $this->assertEquals(1, substr_count($htmlContent, '/marketingLists/external/manageSubscriptions?hash='));
            $this->assertContains('<img width="1" height="1" src="localhost', $htmlContent);
            $this->assertContains('/tracking/default/track?id=', $htmlContent);
        }

        /**
         * @depends testProcessDueCampaignItemWithUnsubscribeAndManageSubscriptionsUrlMergeTags
         */
        public function testProcessDueCampaignItemWithoutUnsubscribeAndManageSubscriptionsUrlMergeTags()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo18@zurmo.com';
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 18', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 18',
                                                                                                'description',
                                                                                                null,
                                                                                                null);
            $campaign                   = CampaignTestHelper::createCampaign('campaign 16',
                                                                                'subject 16',
                                                                                'Plain Text',
                                                                                'HTML',
                                                                                'testFromName',
                                                                                'test@zurmo.com',
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
            $textContent                = $campaignItem->emailMessage->content->textContent;
            $htmlContent                = $campaignItem->emailMessage->content->htmlContent;
            $this->assertNotEquals($campaign->textContent, $textContent);
            $this->assertNotEquals($campaign->htmlContent, $htmlContent);
            $this->assertContains('Plain Text', $textContent);
            $this->assertContains('/marketingLists/external/unsubscribe?hash=', $textContent);
            $this->assertEquals(1, substr_count($textContent, '/marketingLists/external/unsubscribe?hash='));
            $this->assertContains('/marketingLists/external/manageSubscriptions?hash=', $textContent);
            $this->assertEquals(1, substr_count($textContent, '/marketingLists/external/manageSubscriptions?hash='));
            $this->assertContains('HTML<br /><br /><a href="localhost/', $htmlContent);
            $this->assertContains('<img width="1" height="1" src="localhost', $htmlContent);
            $this->assertContains('/tracking/default/track?id=', $htmlContent);
            $this->assertEquals(1, substr_count($htmlContent, '/tracking/default/track?id='));
            $this->assertContains('/marketingLists/external/unsubscribe?hash=', $htmlContent);
            $this->assertEquals(1, substr_count($htmlContent, '/marketingLists/external/unsubscribe?hash='));
            $this->assertEquals(2, substr_count($htmlContent, '<br /><a href="localhost/'));
            $this->assertContains('/marketingLists/external/manageSubscriptions?hash=', $htmlContent);
            $this->assertEquals(1, substr_count($htmlContent, '/marketingLists/external/manageSubscriptions?hash='));
        }

        public function testProcessDueCampaignItemContactUnsubscribed()
        {
            $email                      = new Email();
            $email->emailAddress        = 'demo@zurmo.com';
            $email->optOut              = false;
            $contact                    = ContactTestHelper::createContactByNameForOwner('contact 17', $this->user);
            $contact->primaryEmail      = $email;
            $this->assertTrue($contact->save());
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList 17',
                                                                                                'description',
                                                                                                'CustomFromName',
                                                                                                'custom@from.com');
            MarketingListMemberTestHelper::createMarketingListMember(true, $marketingList, $contact);
            $campaign                   = CampaignTestHelper::createCampaign('campaign 17',
                                                                             'subject 17',
                                                                             'Dear. Sir',
                                                                             'Dear. Sir',
                                                                             null,
                                                                             null,
                                                                             null,
                                                                             null,
                                                                             null,
                                                                             null,
                                                                             $marketingList);
            $processed                  = 0;
            $campaignItem               = CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            $this->processDueItem($campaignItem);
            $this->assertEquals(1, $campaignItem->processed);
            $personId                   = $contact->getClassId('Person');
            $activities                 = CampaignItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                                                                                CampaignItemActivity::TYPE_SKIP,
                                                                                $campaignItem->id,
                                                                                $personId);
            $this->assertNotEmpty($activities);
            $this->assertCount(1, $activities);
        }
    }
?>