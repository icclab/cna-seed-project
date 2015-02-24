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
    class CampaignGenerateDueCampaignItemsJobBenchmarkTest extends AutoresponderOrCampaignBaseTest
    {
        protected $user;

        protected $singleItemExpectedTime   = 0.007;

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
            MarketingListMember::deleteAll();
        }

        public function testSingleItem()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(1);
        }

        /**
         * @depends testSingleItem
         */
        public function testTenItems()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(10);
        }

        /**
         * @depends testTenItems
         */
        public function testFiftyItems()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(50);
        }

        /**
         * @depends testFiftyItems
         */
        public function testHundredItems()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(100);
        }

        /**
         * @depends testHundredItems
         */
        public function testTwoFiftyItems()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(250);
        }

        /**
         * @depends testTwoFiftyItems
         */
        public function testFiveHundredItems()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(500);
        }

        /**
         * @depends testFiveHundredItems
         */
        public function testThousandItems()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(1000);
        }

        protected function ensureTimeSpentIsLessOrEqualThanExpectedForCount($count)
        {
            $timeSpent      = $this->generateCampaignItemsForDueCampaigns($count);
            echo PHP_EOL. $count . ' items took ' . $timeSpent . ' seconds';
            // no need to multiply by $count
            // this is all sql with no php in the core logic of generation so the time spent
            // remains constant for the most part.
            $this->assertLessThanOrEqual($this->singleItemExpectedTime, $timeSpent);
        }

        public function generateCampaignItemsForDueCampaigns($count)
        {
            $marketingList              = MarketingListTestHelper::createMarketingListByName('marketingList Test',
                                                                                                'description goes here',
                                                                                                'fromName',
                                                                                                'from@domain.com');
            $marketingListId    = $marketingList->id;
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
                MarketingListMemberTestHelper::createMarketingListMember(0, $marketingList, $contact);
            }
            $marketingList->forgetAll();

            $marketingList      = MarketingList::getById($marketingListId);
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

            $sendOnDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time() - 600);
            $campaign                   = CampaignTestHelper::createCampaign('campaign Test',
                                                                                'subject',
                                                                                $content,
                                                                                $content,
                                                                                null,
                                                                                null,
                                                                                null,
                                                                                Campaign::STATUS_ACTIVE,
                                                                                $sendOnDateTime,
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
            $campaignId         = $campaign->id;
            $campaign->forgetAll();
            $campaignItems      = CampaignItem::getByProcessedAndCampaignId(0, $campaignId);
            $this->assertEmpty($campaignItems);
            //Process open campaigns.
            Yii::app()->jobQueue->deleteAll();

            AutoresponderOrCampaignBatchSizeConfigUtil::setBatchSize($count);
            Yii::app()->jobQueue->deleteAll();
            $this->assertCount(0, Yii::app()->jobQueue->getAll());
            ForgetAllCacheUtil::forgetAllCaches();
            $job            = new CampaignGenerateDueCampaignItemsJob();
            $startedAt      = microtime(true);
            $this->assertTrue($job->run());
            $timeTaken      = microtime(true) - $startedAt;
            ForgetAllCacheUtil::forgetAllCaches();
            $campaign->forgetAll();
            unset($campaign);
            $campaign           = Campaign::getById($campaignId);
            $this->assertNotNull($campaign);
            $this->assertEquals(Campaign::STATUS_PROCESSING, $campaign->status);
            $campaignItems      = CampaignItem::getByProcessedAndCampaignId(0, $campaignId);
            $this->assertNotEmpty($campaignItems);
            $this->assertCount($count, $campaignItems);
            return $timeTaken;
        }
    }
?>