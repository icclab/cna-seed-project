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
    class CampaignTest extends AutoresponderOrCampaignBaseTest
    {
        public static $marketingList;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
            // Delete item from jobQueue, that is created when new user is created
            Yii::app()->jobQueue->deleteAll();
            self::$marketingList = MarketingListTestHelper::createMarketingListByName('a new list');
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testGetStatusDropDownArray()
        {
            $statusDropDownArray    = Campaign::getStatusDropDownArray();
            $this->assertNotEmpty($statusDropDownArray);
            $this->assertEquals('Paused',       $statusDropDownArray[1]);
            $this->assertEquals('Active',       $statusDropDownArray[2]);
            $this->assertEquals('Processing',   $statusDropDownArray[3]);
            $this->assertEquals('Completed',    $statusDropDownArray[4]);
        }

        public function testCreateAndGetCampaignListById()
        {
            $campaign                   = new Campaign();
            $campaign->name             = 'Test Campaign Name';
            $campaign->supportsRichText = 1;
            $campaign->status           = Campaign::STATUS_PAUSED;
            $campaign->fromName         = 'Test From Name';
            $campaign->fromAddress      = 'from@zurmo.com';
            $campaign->subject          = 'Test Subject';
            $campaign->htmlContent      = 'Test Html Content';
            $campaign->textContent      = 'Test Text Content';
            $campaign->fromName         = 'From Name';
            $campaign->fromAddress      = 'from@zurmo.com';
            $campaign->sendOnDateTime   = '0000-00-00 00:00:00';
            $campaign->marketingList    = self::$marketingList;
            $this->assertCount(0, Yii::app()->jobQueue->getAll());
            $this->assertTrue($campaign->save());
            $jobs = Yii::app()->jobQueue->getAll();
            $this->assertCount(1, $jobs);
            $this->assertEquals('CampaignGenerateDueCampaignItems', $jobs[5][0]['jobType']);
            $id                         = $campaign->id;
            unset($campaign);
            $campaign                   = Campaign::getById($id);
            $this->assertEquals('Test Campaign Name',                       $campaign->name);
            $this->assertEquals(1,                                          $campaign->supportsRichText);
            $this->assertEquals(Campaign::STATUS_PAUSED,                    $campaign->status);
            $this->assertEquals('From Name',                                $campaign->fromName);
            $this->assertEquals('from@zurmo.com',                           $campaign->fromAddress);
            $this->assertEquals('Test Subject',                             $campaign->subject);
            $this->assertEquals('Test Html Content',                        $campaign->htmlContent);
            $this->assertEquals('Test Text Content',                        $campaign->textContent);
            $this->assertEquals('0000-00-00 00:00:00',                      $campaign->sendOnDateTime);
            $this->assertEquals(self::$marketingList->id,                   $campaign->marketingList->id);
        }

        /**
         * @depends testCreateAndGetCampaignListById
         */
        public function testRequiredAttributes()
        {
            $campaign                   = new Campaign();
            $this->assertFalse($campaign->save());
            $errors                     = $campaign->getErrors();
            $this->assertNotEmpty($errors);
            $this->assertCount(7, $errors);
            $this->assertArrayHasKey('name', $errors);
            $this->assertEquals('Name cannot be blank.', $errors['name'][0]);
            $this->assertArrayHasKey('supportsRichText', $errors);
            $this->assertEquals('Supports HTML cannot be blank.', $errors['supportsRichText'][0]);
            $this->assertArrayHasKey('subject', $errors);
            $this->assertEquals('Subject cannot be blank.', $errors['subject'][0]);
            $this->assertArrayHasKey('fromName', $errors);
            $this->assertEquals('From Name cannot be blank.', $errors['fromName'][0]);
            $this->assertArrayHasKey('fromAddress', $errors);
            $this->assertEquals('From Address cannot be blank.', $errors['fromAddress'][0]);
            $this->assertArrayHasKey('textContent', $errors);
            $this->assertEquals("You choose not to support HTML but didn't set any text content.", $errors['textContent'][0]);
            $this->assertEquals('Please provide at least one of the contents field.', $errors['textContent'][1]);
            $this->assertArrayHasKey('marketingList', $errors);
            $this->assertEquals('Marketing List cannot be blank.', $errors['marketingList'][0]);

            $campaign->name             = 'Test Campaign Name2';
            $campaign->supportsRichText = 0;
            $campaign->status           = Campaign::STATUS_ACTIVE;
            $campaign->fromName         = 'From Name2';
            $campaign->fromAddress      = 'from2@zurmo.com';
            $campaign->subject          = 'Test Subject2';
            $campaign->htmlContent      = 'Test Html Content2';
            $campaign->textContent      = 'Test Text Content2';
            $campaign->fromName         = 'From Name2';
            $campaign->fromAddress      = 'from2@zurmo.com';
            $campaign->marketingList    = self::$marketingList;
            $this->assertTrue($campaign->save());
            $id                         = $campaign->id;
            unset($campaign);
            $campaign                   = Campaign::getById($id);

            $this->assertEquals('Test Campaign Name2',                      $campaign->name);
            $this->assertEquals(0,         $campaign->supportsRichText);
            $this->assertEquals(Campaign::STATUS_ACTIVE,                    $campaign->status);
            $this->assertEquals('From Name2',                               $campaign->fromName);
            $this->assertEquals('from2@zurmo.com',                          $campaign->fromAddress);
            $this->assertEquals('Test Subject2',                            $campaign->subject);
            $this->assertEquals('Test Html Content2',                       $campaign->htmlContent);
            $this->assertEquals('Test Text Content2',                       $campaign->textContent);
            $this->assertTrue((time() + 15) > DateTimeUtil::convertDbFormatDateTimeToTimestamp($campaign->sendOnDateTime));
        }

        /**
         * @depends testCreateAndGetCampaignListById
         */
        public function testDummyHtmlContentThrowsValidationErrorWhenTextContentIsEmpty()
        {
            $campaign                                  = new Campaign();
            $campaign->name                            = 'Another Test Campaign Name';
            $campaign->supportsRichText                = 1;
            $campaign->status                          = Campaign::STATUS_ACTIVE;
            $campaign->fromName                        = 'Another From Name';
            $campaign->fromAddress                     = 'anotherfrom@zurmo.com';
            $campaign->subject                         = 'Another Test Subject';
            $campaign->textContent                     = '';
            $campaign->htmlContent                     = "<html>\n<head>\n</head>\n<body>\n</body>\n</html>";
            $campaign->marketingList                   = self::$marketingList;
            $this->assertFalse($campaign->save());
            $errorMessages = $campaign->getErrors();
            $this->assertEquals(1, count($errorMessages));
            $this->assertTrue(array_key_exists('textContent', $errorMessages));
            $this->assertEquals(1, count($errorMessages['textContent']));
            $this->assertEquals('Please provide at least one of the contents field.', $errorMessages['textContent'][0]);

            $campaign->htmlContent                     = 'Text Content';
            $this->assertTrue($campaign->save());
            $id                         = $campaign->id;
            unset($campaign);
            $campaign                   = Campaign::getById($id);
            $this->assertEquals('Another Test Campaign Name',   $campaign->name);
            $this->assertEquals(1,                              $campaign->supportsRichText);
            $this->assertEquals(Campaign::STATUS_ACTIVE,        $campaign->status);
            $this->assertEquals('Another From Name',            $campaign->fromName);
            $this->assertEquals('anotherfrom@zurmo.com',        $campaign->fromAddress);
            $this->assertEquals('Another Test Subject',         $campaign->subject);
            $this->assertEquals(null,                           $campaign->textContent);
            $this->assertEquals('Text Content',                 $campaign->htmlContent);
            $this->assertEquals(self::$marketingList->id,       $campaign->marketingList->id);
        }

        /**
         * @depends testCreateAndGetCampaignListById
         */
        public function testHtmlContentGetsSavedCorrectly()
        {
            $randomData                     = ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames(
                                                                            'EmailTemplatesModule', 'EmailTemplate');
            $htmlContent                    = $randomData['htmlContent'][count($randomData['htmlContent']) -1];
            $campaign                                   = new Campaign();
            $campaign->name                             = 'Another Test Campaign Name';
            $campaign->supportsRichText                 = 0;
            $campaign->status                           = Campaign::STATUS_ACTIVE;
            $campaign->fromName                         = 'Another From Name';
            $campaign->fromAddress                      = 'anotherfrom@zurmo.com';
            $campaign->fromName                         = 'From Name2';
            $campaign->fromAddress                      = 'from2@zurmo.com';
            $campaign->subject                          = 'Another Test subject';
            $campaign->textContent                      = 'Text Content';
            $campaign->htmlContent                      = $htmlContent;
            $campaign->marketingList                    = self::$marketingList;
            $this->assertTrue($campaign->save());
            $campaignId = $campaign->id;
            $campaign->forgetAll();
            $campaign = Campaign::getById($campaignId);
            $this->assertEquals($htmlContent, $campaign->htmlContent);
        }

        /**
         * @depends testCreateAndGetCampaignListById
         */
        public function testGetCampaignByName()
        {
            $campaigns = Campaign::getByName('Test Campaign Name');
            $this->assertEquals(1, count($campaigns));
            $this->assertEquals('Test Campaign Name', $campaigns[0]->name);
            $this->assertEquals(1,               $campaigns[0]->supportsRichText);
            $this->assertEquals(Campaign::STATUS_PAUSED,                    $campaigns[0]->status);
            $this->assertEquals('From Name',                                $campaigns[0]->fromName);
            $this->assertEquals('from@zurmo.com',                           $campaigns[0]->fromAddress);
            $this->assertEquals('Test Subject',                             $campaigns[0]->subject);
            $this->assertEquals('Test Html Content',                        $campaigns[0]->htmlContent);
            $this->assertEquals('Test Text Content',                        $campaigns[0]->textContent);
        }

        /**
         * @depends testCreateAndGetCampaignListById
         */
        public function testGetLabel()
        {
            $campaigns = Campaign::getByName('Test Campaign Name');
            $this->assertEquals(1, count($campaigns));
            $this->assertEquals('Campaign',  $campaigns[0]::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Campaigns', $campaigns[0]::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testCreateAndGetCampaignListById
         */
        public function testToString()
        {
            $campaigns = Campaign::getByName('Test Campaign Name');
            $this->assertEquals(1, count($campaigns));
            $this->assertEquals('Test Campaign Name', strval($campaigns[0]));
        }

        /**
         * @depends testCreateAndGetCampaignListById
         */
        public function testGetByStatus()
        {
            $totalCampaigns     = Campaign::getAll();
            $this->assertNotEmpty($totalCampaigns);
            $this->assertCount(4, $totalCampaigns);
            $dueActiveCampaigns = Campaign::getByStatus(Campaign::STATUS_ACTIVE);
            $this->assertNotEmpty($dueActiveCampaigns);
            $this->assertCount(3, $dueActiveCampaigns);
            $campaign = $dueActiveCampaigns[0];
            $this->assertEquals('Test Campaign Name2',                      $campaign->name);
            $this->assertEquals(0,         $campaign->supportsRichText);
            $this->assertEquals('From Name2',                               $campaign->fromName);
            $this->assertEquals('from2@zurmo.com',                          $campaign->fromAddress);
            $this->assertEquals('Test Subject2',                            $campaign->subject);
            $this->assertEquals('Test Html Content2',                       $campaign->htmlContent);
            $this->assertEquals('Test Text Content2',                       $campaign->textContent);

            $duePausedCampaigns = Campaign::getByStatus(Campaign::STATUS_PAUSED);
            $this->assertNotEmpty($duePausedCampaigns);
            $this->assertCount(1, $duePausedCampaigns);
            $campaign = $duePausedCampaigns[0];
            $this->assertEquals('Test Campaign Name',                       $campaign->name);
            $this->assertEquals(1,               $campaign->supportsRichText);
            $this->assertEquals(Campaign::STATUS_PAUSED,                    $campaign->status);
            $this->assertEquals('From Name',                                $campaign->fromName);
            $this->assertEquals('from@zurmo.com',                           $campaign->fromAddress);
            $this->assertEquals('Test Subject',                             $campaign->subject);
            $this->assertEquals('Test Html Content',                        $campaign->htmlContent);
            $this->assertEquals('Test Text Content',                        $campaign->textContent);
        }

        /**
         * @depends testGetByStatus
         */
        public function testGetByStatusAndSendingTime()
        {
            $totalCampaigns     = Campaign::getAll();
            $this->assertNotEmpty($totalCampaigns);
            $this->assertCount(4, $totalCampaigns);
            $dueActiveCampaigns = Campaign::getByStatusAndSendingTime(Campaign::STATUS_ACTIVE, time() + 100);
            $this->assertNotEmpty($dueActiveCampaigns);
            $this->assertCount(3, $dueActiveCampaigns);
            $campaign = $dueActiveCampaigns[0];
            $this->assertEquals('Test Campaign Name2',                      $campaign->name);
            $this->assertEquals(0,         $campaign->supportsRichText);
            $this->assertEquals('From Name2',                               $campaign->fromName);
            $this->assertEquals('from2@zurmo.com',                          $campaign->fromAddress);
            $this->assertEquals('Test Subject2',                            $campaign->subject);
            $this->assertEquals('Test Html Content2',                       $campaign->htmlContent);
            $this->assertEquals('Test Text Content2',                       $campaign->textContent);

            $duePausedCampaigns = Campaign::getByStatusAndSendingTime(Campaign::STATUS_PAUSED);
            $this->assertNotEmpty($duePausedCampaigns);
            $this->assertCount(1, $duePausedCampaigns);
            $campaign = $duePausedCampaigns[0];
            $this->assertEquals('Test Campaign Name',                       $campaign->name);
            $this->assertEquals(1,               $campaign->supportsRichText);
            $this->assertEquals(Campaign::STATUS_PAUSED,                    $campaign->status);
            $this->assertEquals('From Name',                                $campaign->fromName);
            $this->assertEquals('from@zurmo.com',                           $campaign->fromAddress);
            $this->assertEquals('Test Subject',                             $campaign->subject);
            $this->assertEquals('Test Html Content',                        $campaign->htmlContent);
            $this->assertEquals('Test Text Content',                        $campaign->textContent);
        }

        /**
         * @depends testRequiredAttributes
         */
        public function testDeleteCampaign()
        {
            $campaigns = Campaign::getAll();
            $this->assertEquals(4, count($campaigns));

            CampaignItemTestHelper::createCampaignItem(0, $campaigns[0]);
            $campaignItems = CampaignItem::getAll();
            $this->assertCount(1, $campaignItems);

            $campaignItemActivity                           = new CampaignItemActivity();
            $campaignItemActivity->type                     = CampaignItemActivity::TYPE_CLICK;
            $campaignItemActivity->quantity                 = 1;
            $campaignItemActivity->campaignItem             = $campaignItems[0];
            $campaignItemActivity->latestSourceIP           = '121.212.122.112';
            $this->assertTrue($campaignItemActivity->save());

            $emailMessage   = EmailMessageTestHelper::createOutboxEmail(Yii::app()->user->userModel, 'subject',
                                                                        'html', 'text', 'from', 'from@zurmo.com',
                                                                        'to', 'to@zurmo.com');
            $campaignItems[0]->emailMessage = $emailMessage;
            $this->assertTrue($campaignItems[0]->unrestrictedSave());

            $this->assertEquals(1, CampaignItemActivity::getCount());
            $this->assertEquals(1, EmailMessage::getCount());
            $campaigns[0]->delete();

            $this->assertEquals(3, Campaign::getCount());
            $this->assertEquals(0, CampaignItem::getCount());
            $this->assertEquals(0, CampaignItemActivity::getCount());
            $this->assertEquals(1, EmailMessage::getCount());
        }
    }
?>