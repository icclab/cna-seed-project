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

    class AutoresponderDefaultControllerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected $user;

        protected static $marketingListId;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            EmailTemplateTestHelper::create('EmailTemplate 01', 'Subject 01', 'Contact', 'Html Content 01',
                                            'Text Content 01');
            EmailTemplateTestHelper::create('EmailTemplate 02', 'Subject 02', 'Contact', 'Html Content 02',
                                            'Text Content 02');
            EmailTemplateTestHelper::create('EmailTemplate 03', 'Subject 03', 'Contact', 'Html Content 03',
                                            'Text Content 03');
            EmailTemplateTestHelper::create('EmailTemplate 04', 'Subject 04', 'Contact', 'Html Content 04',
                                            'Text Content 04');
            EmailTemplateTestHelper::create('EmailTemplate 05', 'Subject 05', 'Contact', 'Html Content 05',
                                            'Text Content 05');

            $marketingList = MarketingListTestHelper::createMarketingListByName('MarketingListName',
                                                                                        'MarketingList Description');
            static::$marketingListId = $marketingList->id;
            AutoresponderTestHelper::createAutoresponder('Subject 01', 'This is text Content 01',
                            'This is html Content 01', 10, Autoresponder::OPERATION_SUBSCRIBE, true, $marketingList);
            AutoresponderTestHelper::createAutoresponder('Subject 02', 'This is text Content 02',
                        'This is html Content 02', 5, Autoresponder::OPERATION_UNSUBSCRIBE, false, $marketingList);
            AllPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $this->user;
        }

        /**
         * @expectedException CHttpException
         * @expectedMessage Your request is invalid.
         */
        public function testSuperUserCreateActionWithoutParameters()
        {
            $content = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/create');
        }

        /**
         * @depends testSuperUserCreateActionWithoutParameters
         */
        public function testFlashMessageShowsUpIfJobsDidntRun()
        {
            $redirectUrl    = 'http://www.zurmo.com/';
            $this->setGetArray(array('marketingListId' => static::$marketingListId , 'redirectUrl' => $redirectUrl));
            $content = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/create');
            $compareContent = 'Autoresponders will not run properly until scheduled jobs are set up. Contact your administrator.';
            $this->assertContains($compareContent, $content);
        }

        /**
         * @depends testFlashMessageShowsUpIfJobsDidntRun
         */
        public function testFlashMessageDoesNotShowUpIfJobsHaveRun()
        {
            $jobLog                = new JobLog();
            $jobLog->type          = 'AutoresponderQueueMessagesInOutbox';
            $jobLog->startDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $jobLog->endDateTime   = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $jobLog->status        = JobLog::STATUS_COMPLETE_WITHOUT_ERROR;
            $jobLog->isProcessed   = false;
            $this->assertTrue($jobLog->save());

            $jobLog                = new JobLog();
            $jobLog->type          = 'ProcessOutboundEmail';
            $jobLog->startDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $jobLog->endDateTime   = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $jobLog->status        = JobLog::STATUS_COMPLETE_WITHOUT_ERROR;
            $jobLog->isProcessed   = false;
            $this->assertTrue($jobLog->save());

            $redirectUrl    = 'http://www.zurmo.com/';
            $this->setGetArray(array('marketingListId' => static::$marketingListId , 'redirectUrl' => $redirectUrl));
            $content = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/create');
            $compareContent = 'Autoresponders will not run properly until scheduled jobs are set up. Contact your administrator.';
            $this->assertNotContains($compareContent, $content);
        }

        /**
         * @expectedException CHttpException
         * @expectedMessage Your request is invalid.
         * @depends testFlashMessageDoesNotShowUpIfJobsHaveRun
         */
        public function testSuperUserCreateActionWithoutRedirectUrl()
        {
            $this->setGetArray(array('marketingListId' => static::$marketingListId ));
            $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/create');
        }

        /**
         * @depends testSuperUserCreateActionWithoutRedirectUrl
         */
        public function testSuperUserCreateActionWithParameters()
        {
            // test create page
            $redirectUrl    = 'http://www.zurmo.com/';
            $this->setGetArray(array('marketingListId' => static::$marketingListId , 'redirectUrl' => $redirectUrl));
            $content = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/create');
            $this->assertContains('marketing/default/index">Marketing</a> &#47; <a href=', $content);
            $this->assertContains('marketingLists/default/list">Lists</a> &#47; <a href=', $content);
            $this->assertContains('marketingLists/default/details?id=' . static::$marketingListId .
                                  '">MarketingListName</a> &#47; <span>Create</span></div>', $content);
            $this->assertContains('Create Autoresponder', $content);
            $this->assertContains('<label for="Autoresponder_operationType_value" class="required">' .
                                  'Triggered By <span class="required">*</span></label>', $content);
            $this->assertContains('<label for="Autoresponder_fromOperationDurationInterval" class="required">' .
                                  'Send After <span class="required">*</span></label>', $content);
            $this->assertContains('<label for="Autoresponder_subject" class="required">Subject ' .
                                  '<span class="required">*</span></label>', $content);
            $this->assertContains('<input id="ytAutoresponder_enableTracking" type="hidden" ' .
                                  'value="0" name="Autoresponder[enableTracking]"', $content);
            $this->assertContains('<select name="Autoresponder[operationType]" ' .
                                  'id="Autoresponder_operationType_value">', $content);
            $this->assertContains('<option value="1">Subscription to list</option>', $content);
            $this->assertContains('<option value="2">Unsubscribed from list</option>', $content);
            $this->assertContains('<input id="Autoresponder_subject" name="Autoresponder[subject]" ' .
                                  'type="text" maxlength="255"', $content);
            $this->assertContains('<tr><th><label for="Autoresponder_contactEmailTemplateNames_name">Select a template</label></th>', $content);
            $this->assertContains('<td colspan="1"><div class="has-model-select">' .
                                  '<input name="" id="Autoresponder_contactEmailTemplateNames_id"' .
                                  ' value="" type="hidden" />', $content);
            $this->assertContains('<input onblur="clearIdFromAutoCompleteField($(this).val(), &#039;' .
                                  'Autoresponder_contactEmailTemplateNames_id&#039;);" id="Autoresponder_contact' .
                                  'EmailTemplateNames_name" type="text" value="" ' .
                                  'name="" />', $content);
            $this->assertContains('<a id="Autoresponder_contactEmailTemplateNames_SelectLink" href="#">' .
                                  '<span class="model-select-icon"></span><span class="z-spinner">' .
                                  '</span></a></div></td></tr>', $content);
            $this->assertContains('<a href="#tab1">Text Content</a>', $content);
            $this->assertContains('<a class="active-tab" href="#tab2">Html Content</a>', $content);
            $this->assertContains('class="simple-link" ' .
                                  'href="#">MergeTag Guide</a>', $content);
            $this->assertContains('<textarea id="Autoresponder_textContent" ' .
                                  'name="Autoresponder[textContent]" rows="6" cols="50"', $content);
            $this->assertContains('<textarea id="Autoresponder_htmlContent" ' .
                                  'name="Autoresponder[htmlContent]"', $content);
            $this->assertContains('<label>Attachments</label>', $content);
            $this->assertContains('<strong class="add-label">Add Files</strong>', $content);
            $this->assertContains('<input id="Autoresponder_files" multiple="multiple" type="file" ' .
                                  'name="Autoresponder_files"', $content);
            $this->assertContains('<span class="z-label">Cancel</span>', $content);
            $this->assertContains('<span class="z-label">Save</span>', $content);

            // test all required fields
            $this->setPostArray(array('Autoresponder' => array(
                                                            'operationType'             => '',
                                                            'fromOperationDurationInterval'      => '',
                                                            'fromOperationDurationType'      => '',
                                                            'subject'                   => '',
                                                            'enableTracking'            => '',
                                                            'contactEmailTemplateNames' => '',
                                                            'textContent'               => '',
                                                            'htmlContent'               => '',
                                                        )));
            $content = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/create');
            $this->assertContains('Please fix the following input errors:', $content);
            $this->assertContains('Subject cannot be blank.', $content);
            $this->assertContains('Please provide at least one of the contents field.', $content);
            $this->assertContains('Send After cannot be blank.', $content);
            $this->assertContains('Triggered By cannot be blank.', $content);
            $this->assertContains('<input id="Autoresponder_subject" name="Autoresponder[subject]" type="text" maxlength="255" value="" class="error"', $content);
            $this->assertContains('<select name="Autoresponder[operationType]" ' .
                                  'id="Autoresponder_operationType_value" class="error">', $content);

            // try with invalid merge tags
            $this->setPostArray(array('Autoresponder' => array(
                                                            'operationType'             => 2,
                                                            'fromOperationDurationInterval'  => 60*60*4,
                                                            'fromOperationDurationType'      => TimeDurationUtil::DURATION_TYPE_DAY,
                                                            'subject'                   => 'Subject 04',
                                                            'enableTracking'            => 0,
                                                            'contactEmailTemplateNames' => '',
                                                            'textContent'               => '[[TEXT^CONTENT]] 04',
                                                            'htmlContent'               => '[[HTML^CONTENT]] 04',
                                                        )));
            $content = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/create');
            $this->assertContains('Please fix the following input errors:', $content);
            $this->assertContains('Text Content: Invalid MergeTag(TEXT^CONTENT) used.', $content);
            $this->assertContains('Html Content: Invalid MergeTag(HTML^CONTENT) used.', $content);

            // try saving with valid data.
            $this->setPostArray(array('Autoresponder' => array(
                                                            'operationType'             => 2,
                                                            'fromOperationDurationInterval'      => 60*60*4,
                                                            'fromOperationDurationType'      => TimeDurationUtil::DURATION_TYPE_DAY,
                                                            'subject'                   => 'Subject 04',
                                                            'enableTracking'            => 0,
                                                            'contactEmailTemplateNames' => '',
                                                            'textContent'               => 'Text Content 04',
                                                            'htmlContent'               => 'Html Content 04',
                                                        )));

            $resolvedRedirectUrl    = $this->runControllerWithRedirectExceptionAndGetUrl('autoresponders/default/create');
            $autoresponders  = Autoresponder::getByName('Subject 04');
            $this->assertEquals(1, count($autoresponders));
            $this->assertTrue  ($autoresponders[0]->id > 0);
            $this->assertEquals(2, $autoresponders[0]->operationType);
            $this->assertEquals(60*60*4, $autoresponders[0]->fromOperationDurationInterval);
            $this->assertEquals(TimeDurationUtil::DURATION_TYPE_DAY, $autoresponders[0]->fromOperationDurationType);
            $this->assertEquals('Subject 04', $autoresponders[0]->subject);
            $this->assertEquals(0, $autoresponders[0]->enableTracking);
            $this->assertEquals('Text Content 04', $autoresponders[0]->textContent);
            $this->assertEquals('Html Content 04', $autoresponders[0]->htmlContent);
            $this->assertEquals($redirectUrl, $resolvedRedirectUrl);
            $autoresponders = Autoresponder::getAll();
            $this->assertEquals(3, count($autoresponders));
        }

        /**
         * @expectedException CHttpException
         * @expectedMessage Your request is invalid.
         * @depends testSuperUserCreateActionWithParameters
         */
        public function testSuperUserDetailsActionWithoutParameters()
        {
            $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/details');
        }

        /**
         * @expectedException CHttpException
         * @expectedMessage Your request is invalid.
         * @depends testSuperUserDetailsActionWithoutParameters
         */
        public function testSuperUserDetailsActionWithoutRedirectUrl()
        {
            $autoresponderId = self::getModelIdByModelNameAndName('Autoresponder', 'Subject 04');
            $this->setGetArray(array('id' => $autoresponderId));
            $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/details');
        }

        /**
         * @depends testSuperUserDetailsActionWithoutRedirectUrl
         */
        public function testSuperUserDetailsActionWithRedirectUrl()
        {
            $autoresponderId = self::getModelIdByModelNameAndName('Autoresponder', 'Subject 04');
            $redirectUrl     = 'http://www.zurmo.com/';
            $this->setGetArray(array('id' => $autoresponderId, 'redirectUrl' => $redirectUrl));
            $content = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/details');
            $this->assertContains('<div class="breadcrumbs">', $content);
            $this->assertContains('marketing/default/index">Marketing</a> &#47; <a href=', $content);
            $this->assertContains('marketingLists/default/list">Lists</a> &#47; <a href=', $content);
            $this->assertContains('marketingLists/default/details?id=' . static::$marketingListId .
                                  '">MarketingListName</a> &#47; <span>Subject 04</span></div>', $content);
            $this->assertContains('Subject 04', $content);
            $this->assertEquals(3, substr_count($content, 'Subject 04'));
            $this->assertContains('<span class="ellipsis-content">Subject 04</span>', $content);
            $this->assertContains('<span>Options</span>', $content);
            $this->assertContains('autoresponders/default/edit?id=' . $autoresponderId, $content);
            $this->assertContains('autoresponders/default/delete?id=' . $autoresponderId, $content);
            $this->assertContains('<th>Triggered By</th><td colspan="1">Unsubscribed from list</td>', $content);
            $this->assertContains('<th>Send After</th><td colspan="1">14400 Day(s)</td>', $content);
            $this->assertContains('<th>Subject</th><td colspan="1">Subject 04</td>', $content);
            $this->assertContains('<th>Enable Tracking</th>', $content);
            $this->assertContains('<input id="ytAutoresponder_enableTracking" type="hidden" value="0" '.
                                  'name="Autoresponder[enableTracking]"', $content);
            $this->assertContains('<label class="hasCheckBox disabled">' .
                                  '<input id="Autoresponder_enableTracking" ' .
                                  'name="Autoresponder[enableTracking]" disabled="disabled" value="1" ' .
                                  'type="checkbox"', $content);
            $this->assertContains('<th>Attachments</th>', $content);
            $this->assertContains('<a href="#tab1">Text Content</a>', $content);
            $this->assertContains('<a class="active-tab" href="#tab2">Html Content</a>', $content);
            $this->assertContains('Text Content 04', $content);
            $this->assertContains('iframe', $content); //Now Html is in an iframe
        }

        /**
         * @expectedException CHttpException
         * @expectedMessage Your request is invalid.
         * @depends testSuperUserCreateActionWithParameters
         */
        public function testSuperUserEditActionWithoutParameters()
        {
            $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/edit');
        }

        /**
         * @expectedException CHttpException
         * @expectedMessage Your request is invalid.
         * @depends testSuperUserEditActionWithoutParameters
         */
        public function testSuperUserEditActionWithoutRedirectUrl()
        {
            $autoresponderId = self::getModelIdByModelNameAndName('Autoresponder', 'Subject 04');
            $this->setGetArray(array('id' => $autoresponderId));
            $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/edit');
        }

        /**
         * @depends testSuperUserCreateActionWithParameters
         */
        public function testSuperUserEditAction()
        {
            $autoresponderId = self::getModelIdByModelNameAndName('Autoresponder', 'Subject 04');
            $redirectUrl     = 'http://www.zurmo.com/';
            $this->setGetArray(array('id' => $autoresponderId, 'redirectUrl' => $redirectUrl));
            $content = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/edit');
            $this->assertContains('<div class="breadcrumbs">', $content);
            $this->assertContains('marketing/default/index">Marketing</a> &#47; <a href=', $content);
            $this->assertContains('marketingLists/default/list">Lists</a> &#47; <a href=', $content);
            $this->assertContains('marketingLists/default/details?id=' . static::$marketingListId .
                                  '">MarketingListName</a> &#47; <span>Subject 04</span></div>', $content);
            $this->assertContains('Subject 04', $content);
            $this->assertEquals(3, substr_count($content, 'Subject 04'));
            $this->assertContains('<span class="ellipsis-content">Subject 04</span>', $content);
            $this->assertContains('<label for="Autoresponder_operationType_value" class="required">' .
                                  'Triggered By <span class="required">*</span></label>', $content);
            $this->assertContains('<label for="Autoresponder_fromOperationDurationInterval" class="required">' .
                                  'Send After <span class="required">*</span></label>', $content);
            $this->assertContains('<label for="Autoresponder_subject" class="required">Subject ' .
                                  '<span class="required">*</span></label>', $content);
            $this->assertContains('<input id="ytAutoresponder_enableTracking" type="hidden" ' .
                                  'value="0" name="Autoresponder[enableTracking]"', $content);
            $this->assertContains('<select name="Autoresponder[operationType]" ' .
                                  'id="Autoresponder_operationType_value">', $content);
            $this->assertContains('<option value="1">Subscription to list</option>', $content);
            $this->assertContains('<option value="2" selected="selected">Unsubscribed from list</option>', $content);
            $this->assertContains('<input id="Autoresponder_subject" name="Autoresponder[subject]" ' .
                                  'type="text" maxlength="255" value="Subject 04"', $content);
            $this->assertContains('<tr><th><label for="Autoresponder_contactEmailTemplateNames_name">Select a template</label></th>', $content);
            $this->assertContains('<td colspan="1"><div class="has-model-select"><input name=""' .
                                  ' id="Autoresponder_contactEmailTemplateNames_id"' .
                                  ' value="" type="hidden" />', $content);
            $this->assertContains('<div class="has-model-select">', $content);
            $this->assertContains('<input onblur="clearIdFromAutoCompleteField($(this).val(), &#039;' .
                                  'Autoresponder_contactEmailTemplateNames_id&#039;);" id="Autoresponder_contact' .
                                  'EmailTemplateNames_name" type="text" value="" ' .
                                  'name="" />', $content);
            $this->assertContains('<a id="Autoresponder_contactEmailTemplateNames_SelectLink" href="#">' .
                                  '<span class="model-select-icon"></span><span class="z-spinner">' .
                                  '</span></a></div></td></tr>', $content);
            $this->assertContains('<a href="#tab1">Text Content</a>', $content);
            $this->assertContains('<a class="active-tab" href="#tab2">Html Content</a>', $content);
            $this->assertContains('class="simple-link" ' .
                                  'href="#">MergeTag Guide</a>', $content);
            $this->assertContains('<textarea id="Autoresponder_textContent" ' .
                                  'name="Autoresponder[textContent]" rows="6" cols="50"', $content);
            $this->assertContains('<textarea id="Autoresponder_htmlContent" ' .
                                  'name="Autoresponder[htmlContent]"', $content);
            $this->assertContains('<label>Attachments</label>', $content);
            $this->assertContains('<strong class="add-label">Add Files</strong>', $content);
            $this->assertContains('<input id="Autoresponder_files" multiple="multiple" type="file" ' .
                                  'name="Autoresponder_files"', $content);
            $this->assertContains('<span class="z-label">Cancel</span>', $content);
            $this->assertContains('<span class="z-label">Save</span>', $content);
            $this->assertContains('<span class="z-label">Delete</span>', $content);

            // modify everything:
            $this->setPostArray(array('Autoresponder' => array(
                                                            'operationType'             => 1,
                                                            'fromOperationDurationInterval'      => 60*60*24,
                                                            'fromOperationDurationType'      => TimeDurationUtil::DURATION_TYPE_DAY,
                                                            'subject'                   => 'Subject 040',
                                                            'enableTracking'            => 1,
                                                            'contactEmailTemplateNames' => '',
                                                            'textContent'               => 'Text Content 040',
                                                            'htmlContent'               => 'Html Content 040',
                                                        )));
            $resolvedRedirectUrl    = $this->runControllerWithRedirectExceptionAndGetUrl('autoresponders/default/edit');
            $autoresponders  = Autoresponder::getByName('Subject 040');
            $this->assertEquals(1, count($autoresponders));
            $this->assertTrue  ($autoresponders[0]->id > 0);
            $this->assertEquals(1, $autoresponders[0]->operationType);
            $this->assertEquals(60*60*24, $autoresponders[0]->fromOperationDurationInterval);
            $this->assertEquals(TimeDurationUtil::DURATION_TYPE_DAY, $autoresponders[0]->fromOperationDurationType);
            $this->assertEquals('Subject 040', $autoresponders[0]->subject);
            $this->assertEquals(1, $autoresponders[0]->enableTracking);
            $this->assertEquals('Text Content 040', $autoresponders[0]->textContent);
            $this->assertEquals('Html Content 040', $autoresponders[0]->htmlContent);
            $this->assertEquals($redirectUrl, $resolvedRedirectUrl);
            $autoresponders = Autoresponder::getAll();
            $this->assertEquals(3, count($autoresponders));

            // Now test same with file attachment
            $fileNames              = array('testImage.png', 'testZip.zip', 'testPDF.pdf');
            $files                  = array();
            $filesIds               = array();
            foreach ($fileNames as $index => $fileName)
            {
                $file                       = ZurmoTestHelper::createFileModel($fileName);
                $files[$index]['name']      = $fileName;
                $files[$index]['type']      = $file->type;
                $files[$index]['size']      = $file->size;
                $files[$index]['contents']  = $file->fileContent->content;
                $filesIds[]                 = $file->id;
            }
            $this->setPostArray(array('Autoresponder' => array(
                                                            'operationType'             => 1,
                                                            'fromOperationDurationInterval'      => 60*60*24,
                                                            'fromOperationDurationType'      => TimeDurationUtil::DURATION_TYPE_DAY,
                                                            'subject'                   => 'Subject 040',
                                                            'enableTracking'            => 1,
                                                            'contactEmailTemplateNames' => '',
                                                            'textContent'               => 'Text Content 040',
                                                            'htmlContent'               => 'Html Content 040'),
                                    'filesIds'      => $filesIds,
                                    ));
            $resolvedRedirectUrl    = $this->runControllerWithRedirectExceptionAndGetUrl('autoresponders/default/edit');
            $autoresponders  = Autoresponder::getByName('Subject 040');
            $this->assertEquals(1, count($autoresponders));
            $this->assertTrue  ($autoresponders[0]->id > 0);
            $this->assertEquals(1, $autoresponders[0]->operationType);
            $this->assertEquals(60*60*24, $autoresponders[0]->fromOperationDurationInterval);
            $this->assertEquals(TimeDurationUtil::DURATION_TYPE_DAY, $autoresponders[0]->fromOperationDurationType);
            $this->assertEquals('Subject 040', $autoresponders[0]->subject);
            $this->assertEquals(1, $autoresponders[0]->enableTracking);
            $this->assertEquals('Text Content 040', $autoresponders[0]->textContent);
            $this->assertEquals('Html Content 040', $autoresponders[0]->htmlContent);
            $this->assertEquals($redirectUrl, $resolvedRedirectUrl);
            $this->assertNotEmpty($autoresponders[0]->files);
            $this->assertCount(count($files), $autoresponders[0]->files);
            foreach ($files as $index => $file)
            {
                $this->assertEquals($files[$index]['name'], $autoresponders[0]->files[$index]->name);
                $this->assertEquals($files[$index]['type'], $autoresponders[0]->files[$index]->type);
                $this->assertEquals($files[$index]['size'], $autoresponders[0]->files[$index]->size);
                $this->assertEquals($files[$index]['contents'], $autoresponders[0]->files[$index]->fileContent->content);
            }
            $autoresponders = Autoresponder::getAll();
            $this->assertEquals(3, count($autoresponders));
        }

        /**
         * @depends testSuperUserCreateActionWithParameters
         */
        public function testSuperUserDeleteAction()
        {
            $autoresponders = Autoresponder::getAll();
            $this->assertNotEmpty($autoresponders);
            $this->assertCount(3, $autoresponders);
            $autoresponderId = $autoresponders[0]->id;
            $this->setGetArray(array('id' => $autoresponderId));
            $content = $this->runControllerWithNoExceptionsAndGetContent('autoresponders/default/delete', true);
            $this->assertEmpty($content);

            $autoresponders = Autoresponder::getAll();
            $this->assertNotEmpty($autoresponders);
            $this->assertCount(2, $autoresponders);
            $autoresponderId = $autoresponders[0]->id;
            $redirectUrl = 'http://www.zurmo.com/';
            $this->setGetArray(array('id' => $autoresponderId, 'redirectUrl' => $redirectUrl));
            $resolvedRedirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('autoresponders/default/delete');
            $this->assertEquals($redirectUrl, $resolvedRedirectUrl);
            $autoresponders = Autoresponder::getAll();
            $this->assertNotEmpty($autoresponders);
            $this->assertCount(1, $autoresponders);
        }
    }
?>