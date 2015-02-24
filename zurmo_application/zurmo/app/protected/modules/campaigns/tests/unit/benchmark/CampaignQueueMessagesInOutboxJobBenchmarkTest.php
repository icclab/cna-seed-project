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
    class CampaignQueueMessagesInOutboxJobBenchmarkTest extends AutoresponderOrCampaignBaseTest
    {
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
            CampaignItem::deleteAll();
            Contact::deleteAll();
            MarketingList::deleteAll();
        }

        public function testSingleItem()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(1, 2);
        }

        /**
         * @depends testSingleItem
         */
        public function testFiveItems()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(5, 5);
        }

        /**
         * @depends testFiveItems
         */
        public function testTenItems()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(10, 9);
        }

        /**
         * @depends testTenItems
         */
        public function testFiftyItems()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(50, 46);
        }

        /**
         * @depends testFiftyItems
         */
        public function testHundredItems()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(100, 95);
        }

        /**
         * @depends testHundredItems
         */
        public function testTwoFiftyItems()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(250, 240);
        }

        /**
         * @depends testTwoFiftyItems
         */
        public function testFiveHundredItems()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(500, 490);
        }

        /**
         * @depends testFiveHundredItems
         */
        public function testThousandItems()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(1000, 950);
        }

        protected function ensureTimeSpentIsLessOrEqualThanExpectedForCount($count, $expectedTime)
        {
            $timeSpent  = $this->generateAndProcessCampaignItems($count);
            echo PHP_EOL. $count . ' items took ' . $timeSpent . ' seconds';
            $this->assertLessThanOrEqual($expectedTime, $timeSpent);
        }

        public function generateAndProcessCampaignItems($count)
        {
            $contacts                   = array();
            $emails                     = array();
            for ($i = 0; $i < $count; $i++)
            {
                $emails[$i]                 = new Email();
                $emails[$i]->emailAddress   = "demo$i@zurmo.com";
                $account                    = AccountTestHelper::createAccountByNameForOwner('account ' . $i, $this->user);
                $contact                    = ContactTestHelper::createContactWithAccountByNameForOwner('contact ' . $i , $this->user, $account);
                $contact->primaryEmail      = $emails[$i];
                $this->assertTrue($contact->save());
                $contacts[$i]               = $contact;
            }
            $content                    = <<<MTG
[[COMPANY^NAME]]
[[CREATED^DATE^TIME]]
[[DEPARTMENT]]
[[DESCRIPTION]]
[[FIRST^NAME]]
[[GOOGLE^WEB^TRACKING^ID]]
[[INDUSTRY]]
[[JOB^TITLE]]
[[LAST^NAME]]
[[LATEST^ACTIVITY^DATE^TIME]]
[[MOBILE^PHONE]]
[[MODIFIED^DATE^TIME]]
[[OFFICE^FAX]]
[[OFFICE^PHONE]]
[[TITLE]]
[[SOURCE]]
[[STATE]]
[[WEBSITE]]
[[MODEL^URL]]
[[BASE^URL]]
[[APPLICATION^NAME]]
[[CURRENT^YEAR]]
[[LAST^YEAR]]
[[OWNERS^AVATAR^SMALL]]
[[OWNERS^AVATAR^MEDIUM]]
[[OWNERS^AVATAR^LARGE]]
[[OWNERS^EMAIL^SIGNATURE]]
[[UNSUBSCRIBE^URL]]
[[MANAGE^SUBSCRIPTIONS^URL]]
[[PRIMARY^EMAIL__EMAIL^ADDRESS]]
[[PRIMARY^EMAIL__EMAIL^ADDRESS]]
[[SECONDARY^ADDRESS__CITY]]
[[SECONDARY^ADDRESS__COUNTRY]]
[[SECONDARY^ADDRESS__INVALID]]
[[SECONDARY^ADDRESS__LATITUDE]]
[[SECONDARY^ADDRESS__LONGITUDE]]
[[SECONDARY^ADDRESS__POSTAL^CODE]]
[[SECONDARY^ADDRESS__STATE]]
[[SECONDARY^ADDRESS__STREET1]]
[[SECONDARY^ADDRESS__STREET2]]
[[OWNER__DEPARTMENT]]
[[OWNER__FIRST^NAME]]
[[OWNER__IS^ACTIVE]]
[[OWNER__MOBILE^PHONE]]
[[OWNER__LAST^LOGIN^DATE^TIME]]
[[OWNER__LAST^NAME]]
[[CREATED^BY^USER__FIRST^NAME]]
[[CREATED^BY^USER__LAST^NAME]]
[[CREATED^BY^USER__MOBILE^PHONE]]
[[CREATED^BY^USER__TITLE]]
[[CREATED^BY^USER__USERNAME]]
[[ACCOUNT__ANNUAL^REVENUE]]
[[ACCOUNT__INDUSTRY]]
[[ACCOUNT__NAME]]
[[ACCOUNT__WEBSITE]]
[[ACCOUNT__BILLING^ADDRESS__COUNTRY]]
[[ACCOUNT__BILLING^ADDRESS__CITY]]
[[ACCOUNT__OWNER__FIRST^NAME]]
 ' " ` " '
MTG;
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList Test',
                                                                                                'description goes here',
                                                                                                'fromName',
                                                                                                'from@domain.com');
            $campaign                   = CampaignTestHelper::createCampaign('campaign Test',
                                                                                'subject',
                                                                                $content,
                                                                                $content,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                Campaign::STATUS_PROCESSING,
                                                                                null,
                                                                                null,
                                                                                $marketingList,
                                                                                false);
            $fileNames                  = array('testImage.png', 'testZip.zip', 'testPDF.pdf');
            $files                      = array();
            foreach ($fileNames as $index => $fileName)
            {
                $file                       = ZurmoTestHelper::createFileModel($fileName);
                $files[$index]['name']      = $fileName;
                $files[$index]['type']      = $file->type;
                $files[$index]['size']      = $file->size;
                $files[$index]['contents']  = $file->fileContent->content;
                $campaign->files->add($file);
            }
            $this->assertTrue($campaign->save(false));
            $processed                  = 0;
            foreach ($contacts as $contact)
            {
                CampaignItemTestHelper::createCampaignItem($processed, $campaign, $contact);
            }
            AutoresponderOrCampaignBatchSizeConfigUtil::setBatchSize($count);
            Yii::app()->jobQueue->deleteAll();
            ForgetAllCacheUtil::forgetAllCaches();
            $job                        = new CampaignQueueMessagesInOutboxJob();
            $this->assertCount(0, Yii::app()->jobQueue->getAll());
            $startedAt      = microtime(true);
            $this->assertTrue($job->run());
            $timeTaken      = microtime(true) - $startedAt;

            ForgetAllCacheUtil::forgetAllCaches();
            $campaignItemsCountExpected = $count;
            $campaignItemsCountAfter    = CampaignItem::getCount();
            $this->assertEquals($campaignItemsCountExpected, $campaignItemsCountAfter);
            $campaignItemsProcessed = CampaignItem::getByProcessedAndCampaignId(1, $campaign->id);
            $this->assertCount($count, $campaignItemsProcessed);
            foreach ($campaignItemsProcessed as $i => $campaignItem)
            {
                $contact                    = $contacts[$i];
                $email                      = $emails[$i];
                $emailMessage               = $campaignItem->emailMessage;
                $this->assertEquals($marketingList->owner->id, $emailMessage->owner->id);
                $marketingListPermissions   = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($marketingList);
                $emailMessagePermissions    = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($emailMessage);
                $this->assertEquals($marketingListPermissions, $emailMessagePermissions);
                $this->assertEquals($campaign->subject, $emailMessage->subject);
                $this->assertNotEquals($campaign->textContent, $emailMessage->content->textContent);
                $this->assertNotEquals($campaign->htmlContent, $emailMessage->content->htmlContent);
                $this->assertEquals(2, substr_count($emailMessage->content->textContent, '/marketingLists/external/'));
                $this->assertContains('/marketingLists/external/', $emailMessage->content->htmlContent);
                $this->assertEquals(2, substr_count($emailMessage->content->htmlContent, '/marketingLists/external/'));
                $this->assertEquals('support@zurmo.com', $emailMessage->sender->fromAddress);
                $this->assertEquals('Support Team',      $emailMessage->sender->fromName);
                $this->assertEquals(1, $emailMessage->recipients->count());
                $recipients                 = $emailMessage->recipients;
                $this->assertEquals(strval($contact), $recipients[0]->toName);
                $this->assertEquals($email->emailAddress, $recipients[0]->toAddress);
                $this->assertEquals(EmailMessageRecipient::TYPE_TO, $recipients[0]->type);
                // TODO: @Shoaibi/@Sergio: Critical0: come back and fix it.
                //$this->assertEquals($contact, $recipients[0]->personsOrAccounts[0]);
                $this->assertNotEmpty($emailMessage->files);
                $this->assertCount(count($files), $emailMessage->files);
                foreach ($campaign->files as $index => $file)
                {
                    $this->assertEquals($file->name, $emailMessage->files[$index]->name);
                    $this->assertEquals($file->type, $emailMessage->files[$index]->type);
                    $this->assertEquals($file->size, $emailMessage->files[$index]->size);
                    //CampaingItem should share the Attachments content from Campaign
                    $this->assertEquals($file->fileContent->content, $emailMessage->files[$index]->fileContent->content);
                    $this->assertEquals($file->fileContent->id, $emailMessage->files[$index]->fileContent->id);
                }
                $headersArray               = array('zurmoItemId' => $campaignItem->id,
                                                    'zurmoItemClass' => get_class($campaignItem),
                                                    'zurmoPersonId' => $contact->getClassId('Person'));
                $expectedHeaders            = serialize($headersArray);
                $this->assertEquals($expectedHeaders, $emailMessage->headers);
            }
            return $timeTaken;
        }
    }
?>