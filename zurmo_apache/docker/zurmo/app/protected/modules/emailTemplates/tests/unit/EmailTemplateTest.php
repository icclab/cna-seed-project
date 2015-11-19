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
    class EmailTemplateTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testGetTypeDropDownArray()
        {
            $types  = EmailTemplate::getTypeDropDownArray();
            $this->assertCount(2, $types);
            $this->assertArrayHasKey(EmailTemplate::TYPE_CONTACT, $types);
            $this->assertArrayHasKey(EmailTemplate::TYPE_WORKFLOW, $types);
        }

        public function testGetBuiltTypeDropDownArray()
        {
            $builtTypes = EmailTemplate::getBuiltTypeDropDownArray();
            $this->assertCount(3, $builtTypes);
            $this->assertArrayHasKey(EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY, $builtTypes);
            $this->assertArrayHasKey(EmailTemplate::BUILT_TYPE_PASTED_HTML, $builtTypes);
            $this->assertArrayHasKey(EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE, $builtTypes);
        }

        public function testRenderNonEditableTypeStringContent()
        {
            $type                   = EmailTemplate::TYPE_CONTACT;
            $types                  = EmailTemplate::getTypeDropDownArray();
            $expectedTypeString     = $types[$type];
            $resolvedTypeString     = EmailTemplate::renderNonEditableTypeStringContent($type);
            $this->assertEquals($expectedTypeString, $resolvedTypeString);
            $type                   = 99;
            $resolvedTypeString     = EmailTemplate::renderNonEditableTypeStringContent($type);
            $this->assertNull($resolvedTypeString);
        }

        public function testGetGamificationRulesType()
        {
            $expectedRuleType       = 'EmailTemplateGamification';
            $resolvedRuleType       = EmailTemplate::getGamificationRulesType();
            $this->assertEquals($expectedRuleType, $resolvedRuleType);
        }

        public function testCreateAndGetEmailTemplateById()
        {
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $emailTemplate->subject         = 'Test subject';
            $emailTemplate->modelClassName  = 'Contact';
            $emailTemplate->name            = 'Test Email Template';
            $emailTemplate->htmlContent     = 'Test html Content';
            $emailTemplate->textContent     = 'Test text Content';
            $this->assertTrue($emailTemplate->save());
            $id             = $emailTemplate->id;
            unset($emailTemplate);
            $emailTemplate  = EmailTemplate::getById($id);
            $this->assertEquals(EmailTemplate::TYPE_CONTACT,                $emailTemplate->type);
            $this->assertEquals(EmailTemplate::BUILT_TYPE_PASTED_HTML,      $emailTemplate->builtType);
            $this->assertEquals('Test subject',                             $emailTemplate->subject);
            $this->assertEquals('Test Email Template',                      $emailTemplate->name);
            $this->assertEquals('Test html Content',                        $emailTemplate->htmlContent);
            $this->assertEquals('Test text Content',                        $emailTemplate->textContent);
            $this->assertEquals(0,                                          $emailTemplate->isDraft);
            $this->assertEmpty($emailTemplate->serializedData);
            $this->assertEquals(1, EmailTemplate::getCount());
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testRequiredValidators()
        {
            $emailTemplate          = new EmailTemplate();
            $validated              = $emailTemplate->validate(null, false, true);
            $this->assertFalse($validated);
            $errors                 = $emailTemplate->getErrors();
            $this->assertCount(6, $errors);
            $this->assertArrayHasKey('type', $errors);
            $this->assertCount(1, $errors['type']);
            $this->assertEquals($errors['type'][0], 'Type cannot be blank.');
            $this->assertArrayHasKey('builtType', $errors);
            $this->assertCount(1, $errors['builtType']);
            $this->assertEquals($errors['builtType'][0], 'Built Type cannot be blank.');
            $this->assertArrayHasKey('modelClassName', $errors);
            $this->assertCount(1, $errors['modelClassName']);
            $this->assertEquals($errors['modelClassName'][0], 'Module cannot be blank.');
            $this->assertArrayHasKey('name', $errors);
            $this->assertCount(1, $errors['name']);
            $this->assertEquals($errors['name'][0], 'Name cannot be blank.');
            $this->assertArrayHasKey('subject', $errors);
            $this->assertCount(1, $errors['subject']);
            $this->assertEquals($errors['subject'][0], 'Subject cannot be blank.');
            $this->assertArrayHasKey('textContent', $errors);
            $this->assertCount(1, $errors['textContent']);
            $this->assertEquals($errors['textContent'][0], 'Please provide at least one of the contents field.');
        }

        /**
         * @depends testRequiredValidators
         */
        public function testTypeNumericalValidator()
        {
            $emailTemplate          = new EmailTemplate();
            $emailTemplate->type    = 'A string';
            $validated              = $emailTemplate->validate(null, false, true);
            $this->assertFalse($validated);
            $error                  = $emailTemplate->getError('type');
            $this->assertEquals("Type must be integer.", $error);
        }

        /**
         * @depends testTypeNumericalValidator
         */
        public function testBuiltTypeNumericalValidator()
        {
            $emailTemplate              = new EmailTemplate();
            $emailTemplate->builtType   = 'A string';
            $validated                  = $emailTemplate->validate(null, false, true);
            $this->assertFalse($validated);
            $error                      = $emailTemplate->getError('builtType');
            $this->assertEquals("Built Type must be integer.", $error);
        }

        /**
         * @depends testBuiltTypeNumericalValidator
         */
        public function testNameLengthValidator()
        {
            $emailTemplate              = new EmailTemplate();
            $emailTemplate->name        = str_repeat('a', 100);
            $validated                  = $emailTemplate->validate(null, false, true);
            $this->assertFalse($validated);
            $error                      = $emailTemplate->getError('name');
            $this->assertEquals("Name is too long (maximum is 64 characters).", $error);
        }

        /**
         * @depends testNameLengthValidator
         */
        public function testSubjectLengthValidator()
        {
            $emailTemplate              = new EmailTemplate();
            $emailTemplate->subject     = str_repeat('a', 260);
            $validated                  = $emailTemplate->validate(null, false, true);
            $this->assertFalse($validated);
            $error                      = $emailTemplate->getError('subject');
            $this->assertEquals("Subject is too long (maximum is 255 characters).", $error);
        }

        /**
         * @depends testSubjectLengthValidator
         */
        public function testLanguageLengthValidator()
        {
            $emailTemplate              = new EmailTemplate();
            $emailTemplate->language    = str_repeat('a', 100);
            $validated                  = $emailTemplate->validate(null, false, true);
            $this->assertFalse($validated);
            $error                      = $emailTemplate->getError('language');
            $this->assertEquals("Language is too long (maximum is 2 characters).", $error);
        }

        /**
         * @depends testRequiredValidators
         */
        public function testIsDraftDefaultValueForNoBuiltType()
        {
            $emailTemplate              = new EmailTemplate();
            $validated                  = $emailTemplate->validate(null, false, true);
            $this->assertFalse($validated);
            $this->assertEquals(0, $emailTemplate->isDraft);
        }

        /**
         * @depends testIsDraftDefaultValueForNoBuiltType
         */
        public function testIsDraftDefaultValueForPlainTextBuiltType()
        {
            $emailTemplate              = new EmailTemplate();
            $emailTemplate->builtType   = EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY;
            $validated                  = $emailTemplate->validate(null, false, true);
            $this->assertFalse($validated);
            $this->assertEquals(0, $emailTemplate->isDraft);
        }

        /**
         * @depends testIsDraftDefaultValueForPlainTextBuiltType
         */
        public function testIsDraftDefaultValueForPastedHtmlBuiltType()
        {
            $emailTemplate              = new EmailTemplate();
            $emailTemplate->builtType   = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $validated                  = $emailTemplate->validate(null, false, true);
            $this->assertFalse($validated);
            $this->assertEquals(0, $emailTemplate->isDraft);
        }

        /**
         * @depends testIsDraftDefaultValueForPastedHtmlBuiltType
         */
        public function testIsDraftDefaultValueForBuilderBuiltType()
        {
            $emailTemplate              = new EmailTemplate();
            $emailTemplate->builtType   = EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE;
            $validated                  = $emailTemplate->validate(null, false, true);
            $this->assertFalse($validated);
            $this->assertEquals(1, $emailTemplate->isDraft);
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testSerializedDataValidatorForEmpty()
        {
            $emailTemplate                  = new EmailTemplate();
            $validated                      = $emailTemplate->validate(null, false, true);
            $this->assertFalse($validated);
        }

        /**
         * @depends testSerializedDataValidatorForEmpty
         */
        public function testSerializedDataValidatorForInvalidJson()
        {
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->serializedData  = 'somethingNotJson';
            $validated                      = $emailTemplate->validate(null, false, true);
            $this->assertFalse($validated);
            $error                          = $emailTemplate->getError('serializedData');
           $this->assertEquals('Unable to decode serializedData.', $error);
        }

        /**
         * @depends testSerializedDataValidatorForInvalidJson
         */
        public function testSerializedDataValidatorForInvalidScheme()
        {
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->serializedData  = CJSON::encode(array('key' => 'value'));
            $validated                      = $emailTemplate->validate(null, false, true);
            $this->assertFalse($validated);
            $error                          = $emailTemplate->getError('serializedData');
            $this->assertEquals('serializedData contains invalid scheme.', $error);
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testDefaultLanguageGetsPopulated()
        {
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->subject         = 'Test subject For Language';
            $emailTemplate->modelClassName  = 'Contact';
            $emailTemplate->name            = 'Test Email Template For Language';
            $emailTemplate->htmlContent     = 'Test html Content For Language';
            $emailTemplate->textContent     = 'Test text Content For Language';
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $emailTemplate->language        = "";
            $validated                      = $emailTemplate->validate(null, false, true);
            $this->assertTrue($validated);
            $this->assertTrue($emailTemplate->save());
            $id             = $emailTemplate->id;
            unset($emailTemplate);
            $emailTemplate  = EmailTemplate::getById($id);
            $this->assertEquals(EmailTemplate::TYPE_CONTACT,                            $emailTemplate->type);
            $this->assertEquals('Test subject For Language',                            $emailTemplate->subject);
            $this->assertEquals('Test Email Template For Language',                     $emailTemplate->name);
            $this->assertEquals('Test html Content For Language',                       $emailTemplate->htmlContent);
            $this->assertEquals('Test text Content For Language',                       $emailTemplate->textContent);
            $this->assertEquals(Yii::app()->languageHelper-> getForCurrentUser(),       $emailTemplate->language);
            $this->assertEquals(2, EmailTemplate::getCount());
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testAtLeastOneContentFieldIsRequired()
        {
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->subject         = 'Another Test subject';
            $emailTemplate->modelClassName  = 'Contact';
            $emailTemplate->name            = 'Another Test Email Template';
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $validated                      = $emailTemplate->validate(null, false, true);
            $this->assertFalse($validated);
            $errorMessages = $emailTemplate->getErrors();
            $this->assertEquals(1, count($errorMessages));
            $this->assertTrue(array_key_exists('textContent', $errorMessages));
            $this->assertEquals(1, count($errorMessages['textContent']));
            $this->assertEquals('Please provide at least one of the contents field.', $errorMessages['textContent'][0]);
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testModelClassNameExists()
        {
            // test against a class name that doesn't exist
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->subject         = 'Another Test subject';
            $emailTemplate->name            = 'Another Test Email Template';
            $emailTemplate->textContent     = 'Text Content';
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $emailTemplate->modelClassName  = 'RaNdOmTeXt';
            $this->assertFalse($emailTemplate->save());
            $errorMessages = $emailTemplate->getErrors();
            $this->assertEquals(1, count($errorMessages));
            $this->assertTrue(array_key_exists('modelClassName', $errorMessages));
            $this->assertEquals(1, count($errorMessages['modelClassName']));
            $this->assertEquals('Provided class name does not exist.', $errorMessages['modelClassName'][0]);
            // test against a class name thats not a model
            $emailTemplate->modelClassName  = 'TestSuite';
            $validated                      = $emailTemplate->validate(null, false, true);
            $this->assertFalse($validated);
            $errorMessages = $emailTemplate->getErrors();
            $this->assertEquals(1, count($errorMessages));
            $this->assertTrue(array_key_exists('modelClassName', $errorMessages));
            $this->assertEquals(1, count($errorMessages['modelClassName']));
            $this->assertEquals('Provided class name is not a valid Model class.', $errorMessages['modelClassName'][0]);
            // test against a model that is indeed a class
            $emailTemplate->modelClassName  = 'Contact';
            $validated                      = $emailTemplate->validate(null, false, true);
            $this->assertTrue($validated);
            $this->assertTrue($emailTemplate->save());
            $this->assertEmpty($emailTemplate->getErrors());
            $this->assertEquals(3, EmailTemplate::getCount());
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testValidationErrorForInaccessibleModule()
        {
            // test against a user who doesn't have access for provided model's modulename
            $nobody                        = UserTestHelper::createBasicUser('nobody');
            Yii::app()->user->userModel     = $nobody;
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_WORKFLOW;
            $emailTemplate->subject         = 'Another Test subject';
            $emailTemplate->name            = 'Another Test Email Template';
            $emailTemplate->textContent     = 'Text Content';
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $emailTemplate->modelClassName  = 'Contact';
            $validated                      = $emailTemplate->validate(null, false, true);
            $this->assertFalse($validated);
            $errorMessages = $emailTemplate->getErrors();
            $this->assertEquals(1, count($errorMessages));
            $this->assertTrue(array_key_exists('modelClassName', $errorMessages));
            $this->assertEquals(1, count($errorMessages['modelClassName']));
            $this->assertEquals('Provided class name access is prohibited.', $errorMessages['modelClassName'][0]);

            // grant him access, now save should work
            $nobody->setRight('ContactsModule', ContactsModule::getAccessRight());
            $this->assertTrue($nobody->save());
            $validated                      = $emailTemplate->validate(null, false, true);
            $this->assertTrue($validated);
            $this->assertEmpty($emailTemplate->getErrors());
            $this->assertTrue($emailTemplate->save());
            $this->assertEquals(1, EmailTemplate::getCount()); // this is his only template
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testMergeTagsValidation()
        {
            // test against a invalid merge tags
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->subject         = 'Another Test subject';
            $emailTemplate->name            = 'Another Test Email Template';
            $emailTemplate->textContent     = 'Text Content [[TEXT__INVALID^MERGE^TAG]]';
            $emailTemplate->htmlContent     = 'Html Content [[HTMLINVALIDMERGETAG]]';
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $emailTemplate->modelClassName  = 'Contact';
            $validated                      = $emailTemplate->validate(null, false, true);
            $this->assertFalse($validated);
            $errorMessages = $emailTemplate->getErrors();
            $this->assertEquals(2, count($errorMessages));
            $this->assertTrue(array_key_exists('textContent', $errorMessages));
            $this->assertTrue(array_key_exists('htmlContent', $errorMessages));
            $this->assertEquals(1, count($errorMessages['textContent']));
            $this->assertEquals(1, count($errorMessages['htmlContent']));
            $this->assertContains('TEXT__INVALID^MERGE^TAG', $errorMessages['textContent'][0]);
            $this->assertContains('HTMLINVALIDMERGETAG', $errorMessages['htmlContent'][0]);
            // test with no merge tags
            $emailTemplate->textContent    = 'Text Content without tags';
            $emailTemplate->htmlContent    = 'Html Content without tags';
            $validated                      = $emailTemplate->validate(null, false, true);
            $this->assertTrue($validated);
            $this->assertEmpty($emailTemplate->getErrors());
            $this->assertTrue($emailTemplate->save());
            $this->assertEquals(5, EmailTemplate::getCount());
            // test with valid merge tags
            $emailTemplate->textContent    = 'Name : [[FIRST^NAME]] [[LAST^NAME]]';
            $emailTemplate->htmlContent    = '<b>Name : [[FIRST^NAME]] [[LAST^NAME]]</b>';
            $this->assertTrue($emailTemplate->save());
            $this->assertEmpty($emailTemplate->getErrors());
            $this->assertEquals(5, EmailTemplate::getCount());
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testUnsubscribeAndManageSubscriptionsMergeTagsValidation()
        {
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->subject         = 'Another Test subject';
            $emailTemplate->name            = 'Another Test Email Template';
            $emailTemplate->textContent     = GlobalMarketingFooterUtil::resolveUnsubscribeUrlMergeTag() . ', ' .
                                                GlobalMarketingFooterUtil::resolveManageSubscriptionsMergeTag();
            $emailTemplate->htmlContent     = GlobalMarketingFooterUtil::resolveUnsubscribeUrlMergeTag() . ', ' .
                                                GlobalMarketingFooterUtil::resolveManageSubscriptionsMergeTag();
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $emailTemplate->modelClassName  = 'Contact';
            $validated                      = $emailTemplate->validate(null, false, true);
            $this->assertTrue($validated);
            $this->assertEmpty($emailTemplate->getErrors());
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testDummyHtmlContentThrowsValidationErrorWhenTextContentIsEmpty()
        {
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $emailTemplate->subject         = 'Another Test subject';
            $emailTemplate->name            = 'Another Test Email Template';
            $emailTemplate->textContent     = '';
            $emailTemplate->htmlContent     = "<html>\n<head>\n</head>\n<body>\n</body>\n</html>";
            $emailTemplate->modelClassName  = 'Contact';
            $validated                      = $emailTemplate->validate(null, false, true);
            $this->assertFalse($validated);
            $errorMessages = $emailTemplate->getErrors();
            $this->assertEquals(1, count($errorMessages));
            $this->assertTrue(array_key_exists('textContent', $errorMessages));
            $this->assertEquals(1, count($errorMessages['textContent']));
            $this->assertEquals('Please provide at least one of the contents field.', $errorMessages['textContent'][0]);

            $emailTemplate->textContent         = 'Text Content';
            $validated                      = $emailTemplate->validate(null, false, true);
            $this->assertTrue($validated);
            $this->assertTrue($emailTemplate->save());
            $this->assertEquals(6, EmailTemplate::getCount());
            $id             = $emailTemplate->id;
            unset($emailTemplate);
            $emailTemplate  = EmailTemplate::getById($id);
            $this->assertEquals(EmailTemplate::TYPE_CONTACT,    $emailTemplate->type);
            $this->assertEquals('Another Test subject',                 $emailTemplate->subject);
            $this->assertEquals('Another Test Email Template',          $emailTemplate->name);
            $this->assertEquals(null,            $emailTemplate->htmlContent);
            $this->assertEquals('Text Content',            $emailTemplate->textContent);
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testHtmlContentGetsSavedCorrectly()
        {
            $randomData                     = ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames('EmailTemplatesModule',
                                                                                                        'EmailTemplate');
            $htmlContent                    = $randomData['htmlContent'][count($randomData['htmlContent']) -1];
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = EmailTemplate::TYPE_CONTACT;
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_PASTED_HTML;
            $emailTemplate->subject         = 'Another Test subject';
            $emailTemplate->name            = 'Another Test Email Template';
            $emailTemplate->textContent     = 'Text Content';
            $emailTemplate->htmlContent     = $htmlContent;
            $emailTemplate->modelClassName  = 'Contact';
            $this->assertTrue($emailTemplate->save());
            $emailTemplateId = $emailTemplate->id;
            $emailTemplate->forgetAll();
            $emailTemplate = EmailTemplate::getById($emailTemplateId);
            $this->assertEquals($htmlContent, $emailTemplate->htmlContent);
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testGetEmailTemplateByName()
        {
            $emailTemplate = EmailTemplate::getByName('Test Email Template');
            $this->assertEquals(1, count($emailTemplate));
            $this->assertEquals('Test Email Template', $emailTemplate[0]->name);
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testGetLabel()
        {
            $emailTemplate = EmailTemplate::getByName('Test Email Template');
            $this->assertEquals(1, count($emailTemplate));
            $this->assertEquals('Email Template',  $emailTemplate[0]::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Email Templates', $emailTemplate[0]::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testToString()
        {
            $emailTemplate                  = EmailTemplate::getByName('Test Email Template');
            $expectedStringValue            = $emailTemplate[0]->name;
            $resolvedStringValue            = strval($emailTemplate[0]);
            $this->assertEquals($expectedStringValue, $resolvedStringValue);
            $expectedStringValue            = "(Unnamed)";
            $resolvedStringValue            = strval(new EmailTemplate());
            $this->assertEquals($expectedStringValue, $resolvedStringValue);

            // now try with nobody user
            $nobody                         = User::getByUsername('nobody');
            Yii::app()->user->userModel     = $nobody;
            $resolvedStringValue            = strval($emailTemplate[0]);
            $this->assertEmpty($resolvedStringValue);
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testDeleteEmailTemplate()
        {
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(7, count($emailTemplates));
            $emailTemplates[0]->delete();
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(6, count($emailTemplates));
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testGetByType()
        {
            EmailTemplate::deleteAll();
            // create 2 predefined non-builder templates for each type
            EmailTemplateTestHelper::create('predefined 01', 'subject 01', null);
            EmailTemplateTestHelper::create('predefined 02', 'subject 02', null);
            EmailTemplateTestHelper::create('predefined 03', 'subject  03', null, 'html', 'text',
                                            EmailTemplate::TYPE_WORKFLOW);
            EmailTemplateTestHelper::create('predefined 04', 'subject  04', null, 'html', 'text',
                                            EmailTemplate::TYPE_WORKFLOW);

            // create 2 contact type, 1 draft 1 non draft
            EmailTemplateTestHelper::create('contact 01', 'subject 01', 'Contact', 'html', 'text');
            EmailTemplateTestHelper::create('contact 02', 'subject 02', 'Contact', 'html', 'text',
                                            EmailTemplate::TYPE_CONTACT, 1);
            // create 2 workflow type, 1 draft 1 non draft
            EmailTemplateTestHelper::create('workflow 01', 'subject 01', 'Note', 'html', 'text',
                                            EmailTemplate::TYPE_WORKFLOW);
            EmailTemplateTestHelper::create('workflow 02', 'subject 02', 'Note', 'html', 'text',
                                            EmailTemplate::TYPE_WORKFLOW, 1);

            // a- contact, exclude draft
            $nonDraftContactTemplates   = EmailTemplate::getByType(EmailTemplate::TYPE_CONTACT);
            $this->assertCount(1, $nonDraftContactTemplates);
            $this->assertEquals('contact 01', $nonDraftContactTemplates[0]->name);
            // b- contact, include drafts
            $nonDraftContactTemplates   = EmailTemplate::getByType(EmailTemplate::TYPE_CONTACT, true);
            $this->assertCount(2, $nonDraftContactTemplates);
            $this->assertEquals('contact 01', $nonDraftContactTemplates[0]->name);
            $this->assertEquals('contact 02', $nonDraftContactTemplates[1]->name);

            // c- workflow, exclude draft
            $nonDraftContactTemplates   = EmailTemplate::getByType(EmailTemplate::TYPE_WORKFLOW);
            $this->assertCount(1, $nonDraftContactTemplates);
            $this->assertEquals('workflow 01', $nonDraftContactTemplates[0]->name);
            // d- workflow, include drafts
            $nonDraftContactTemplates   = EmailTemplate::getByType(EmailTemplate::TYPE_WORKFLOW, true);
            $this->assertCount(2, $nonDraftContactTemplates);
            $this->assertEquals('workflow 01', $nonDraftContactTemplates[0]->name);
            $this->assertEquals('workflow 02', $nonDraftContactTemplates[1]->name);
        }

        /**
         * @depends testGetByType
         */
        public function testGetPredefinedBuilderTemplates()
        {
            $predefinedBuilderTemplates = EmailTemplate::getPredefinedBuilderTemplates();
            // empty because the ones we created in above test had built type set to pasted html
            $this->assertEmpty($predefinedBuilderTemplates);

            EmailTemplateTestHelper::create('predefined 03', 'subject 03', null, 'html', 'text',
                                            EmailTemplate::TYPE_CONTACT, 0, EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE);
            EmailTemplateTestHelper::create('predefined 04', 'subject 04', null, 'html', 'text',
                                            EmailTemplate::TYPE_CONTACT, 0, EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE);
            $predefinedBuilderTemplates = EmailTemplate::getPredefinedBuilderTemplates();
            $this->assertCount(2, $predefinedBuilderTemplates);
            $this->assertEquals('predefined 03', $predefinedBuilderTemplates[0]->name);
            $this->assertEquals('predefined 04', $predefinedBuilderTemplates[1]->name);
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testGetPreviouslyCreatedBuilderTemplates()
        {
            $previouslyCreated              = EmailTemplate::getPreviouslyCreatedBuilderTemplates();
            $this->assertEmpty($previouslyCreated);
            $previouslyCreatedWithDrafts    = EmailTemplate::getPreviouslyCreatedBuilderTemplates(null, true);
            $this->assertEmpty($previouslyCreatedWithDrafts);

            $previouslyCreatedContactTemplates              = EmailTemplate::getPreviouslyCreatedBuilderTemplates(
                                                                                                            'Contact');
            $this->assertEmpty($previouslyCreatedContactTemplates);
            $previouslyCreatedContactTemplatesWithDrafts    = EmailTemplate::getPreviouslyCreatedBuilderTemplates(
                                                                                                        'Contact', true);
            $this->assertEmpty($previouslyCreatedContactTemplatesWithDrafts);

            // create 2 predefined builder templates
            EmailTemplateTestHelper::create('predefined builder 01', 'subject 01', null, 'html', 'text',
                                            EmailTemplate::TYPE_CONTACT, 0, EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE);
            EmailTemplateTestHelper::create('predefined builder 02', 'subject 02', null, 'html', 'text',
                                            EmailTemplate::TYPE_CONTACT, 0, EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE);

            // create 2 contact type builder, 1 draft 1 non draft
            EmailTemplateTestHelper::create('contact 03', 'subject 03', 'Contact', 'html', 'text',
                                            EmailTemplate::TYPE_CONTACT, 0, EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE);
            EmailTemplateTestHelper::create('contact 04', 'subject 04', 'Contact', 'html', 'text',
                                            EmailTemplate::TYPE_CONTACT, 1, EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE);

            // create 2 note type builder, 1 draft 1 non draft
            EmailTemplateTestHelper::create('note 01', 'subject 01', 'Note', 'html', 'text',
                                            EmailTemplate::TYPE_WORKFLOW, 0, EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE);
            EmailTemplateTestHelper::create('note 02', 'subject 02', 'Note', 'html', 'text',
                                            EmailTemplate::TYPE_WORKFLOW, 1, EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE);

            // list all previously created templates without drafts
            $previouslyCreated      = EmailTemplate::getPreviouslyCreatedBuilderTemplates();
            $this->assertCount(2, $previouslyCreated);
            $this->assertEquals('contact 03', $previouslyCreated[0]->name);
            $this->assertEquals('note 01', $previouslyCreated[1]->name);

            // list all previously created templates with drafts
            $previouslyCreated      = EmailTemplate::getPreviouslyCreatedBuilderTemplates(null, true);
            $this->assertCount(4, $previouslyCreated);
            $this->assertEquals('contact 03', $previouslyCreated[0]->name);
            $this->assertEquals('contact 04', $previouslyCreated[1]->name);
            $this->assertEquals('note 01', $previouslyCreated[2]->name);
            $this->assertEquals('note 02', $previouslyCreated[3]->name);

            // list all contact model previously created templates without drafts
            $previouslyCreated      = EmailTemplate::getPreviouslyCreatedBuilderTemplates('Contact');
            $this->assertCount(1, $previouslyCreated);
            $this->assertEquals('contact 03', $previouslyCreated[0]->name);

            // list all contact model previously created templates with drafts
            $previouslyCreated      = EmailTemplate::getPreviouslyCreatedBuilderTemplates('Contact', true);
            $this->assertCount(2, $previouslyCreated);
            $this->assertEquals('contact 03', $previouslyCreated[0]->name);
            $this->assertEquals('contact 04', $previouslyCreated[1]->name);

            // list all note model previously created templates without drafts
            $previouslyCreated      = EmailTemplate::getPreviouslyCreatedBuilderTemplates('Note');
            $this->assertCount(1, $previouslyCreated);
            $this->assertEquals('note 01', $previouslyCreated[0]->name);

            // list all note model previously created templates with drafts
            $previouslyCreated      = EmailTemplate::getPreviouslyCreatedBuilderTemplates('Note', true);
            $this->assertCount(2, $previouslyCreated);
            $this->assertEquals('note 01', $previouslyCreated[0]->name);
            $this->assertEquals('note 02', $previouslyCreated[1]->name);
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testSetInitializingHtmlContentForBuilderTemplates()
        {
            $defaultDataMaker  = new EmailTemplatesDefaultDataMaker();
            $defaultDataMaker->make();
            $predefinedTemplate = EmailTemplate::getByName('3 Columns with Hero');
            $this->assertNotEmpty($predefinedTemplate);
            $emailTemplate  = EmailTemplateTestHelper::populate('set Test', 'setTest', 'Contact', null, null,
                                                                EmailTemplate::TYPE_CONTACT, 0,
                                                                EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE,
                                                                $predefinedTemplate[0]->serializedData);
            $this->assertNotEmpty($emailTemplate->htmlContent);
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testIsContactTemplate()
        {
            $emailTemplate  = EmailTemplateTestHelper::populate('contact 05', 'subject');
            $this->assertTrue($emailTemplate->isContactTemplate());
            $this->assertTrue($emailTemplate->isPastedHtmlTemplate());
            $this->assertFalse($emailTemplate->isWorkflowTemplate());
            $this->assertFalse($emailTemplate->isPlainTextTemplate());
            $this->assertFalse($emailTemplate->isBuilderTemplate());
            $this->assertFalse($emailTemplate->isPredefinedBuilderTemplate());
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testIsWorkflowTemplate()
        {
            $emailTemplate  = EmailTemplateTestHelper::populate('task 01', 'subject 01', 'Task', 'html', 'text',
                                                                EmailTemplate::TYPE_WORKFLOW);
            $this->assertTrue($emailTemplate->isPastedHtmlTemplate());
            $this->assertTrue($emailTemplate->isWorkflowTemplate());
            $this->assertFalse($emailTemplate->isContactTemplate());
            $this->assertFalse($emailTemplate->isPlainTextTemplate());
            $this->assertFalse($emailTemplate->isBuilderTemplate());
            $this->assertFalse($emailTemplate->isPredefinedBuilderTemplate());
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testIsPlainTextTemplate()
        {
            $emailTemplate  = EmailTemplateTestHelper::populate('task 02', 'subject 01', 'Task', null, 'text',
                                            EmailTemplate::TYPE_WORKFLOW, 0, EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY);
            $this->assertTrue($emailTemplate->isWorkflowTemplate());
            $this->assertTrue($emailTemplate->isPlainTextTemplate());
            $this->assertFalse($emailTemplate->isContactTemplate());
            $this->assertFalse($emailTemplate->isPastedHtmlTemplate());
            $this->assertFalse($emailTemplate->isBuilderTemplate());
            $this->assertFalse($emailTemplate->isPredefinedBuilderTemplate());
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testIsPastedHtmlTemplate()
        {
            $emailTemplate  = EmailTemplateTestHelper::populate('task 03', 'subject 01', 'Task', null, 'text',
                                                                EmailTemplate::TYPE_WORKFLOW);
            $this->assertTrue($emailTemplate->isWorkflowTemplate());
            $this->assertTrue($emailTemplate->isPastedHtmlTemplate());
            $this->assertFalse($emailTemplate->isContactTemplate());
            $this->assertFalse($emailTemplate->isPlainTextTemplate());
            $this->assertFalse($emailTemplate->isBuilderTemplate());
            $this->assertFalse($emailTemplate->isPredefinedBuilderTemplate());
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testIsBuilderTemplate()
        {
            $emailTemplate  = EmailTemplateTestHelper::populate('task 04', 'subject 01', 'Task', null, 'text',
                                            EmailTemplate::TYPE_WORKFLOW, 0, EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE);
            $this->assertTrue($emailTemplate->isWorkflowTemplate());
            $this->assertTrue($emailTemplate->isBuilderTemplate());
            $this->assertFalse($emailTemplate->isPlainTextTemplate());
            $this->assertFalse($emailTemplate->isPastedHtmlTemplate());
            $this->assertFalse($emailTemplate->isContactTemplate());
            $this->assertFalse($emailTemplate->isPredefinedBuilderTemplate());
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testIsPredefinedBuilderTemplate()
        {
            $emailTemplate  = EmailTemplateTestHelper::populate('task 05', 'subject 01', null, null, 'text',
                                        EmailTemplate::TYPE_WORKFLOW, 0, EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE);
            $this->assertTrue($emailTemplate->isWorkflowTemplate());
            $this->assertTrue($emailTemplate->isBuilderTemplate());
            $this->assertTrue($emailTemplate->isPredefinedBuilderTemplate());
            $this->assertFalse($emailTemplate->isPlainTextTemplate());
            $this->assertFalse($emailTemplate->isPastedHtmlTemplate());
            $this->assertFalse($emailTemplate->isContactTemplate());
        }

        /**
         * @depends testCreateAndGetEmailTemplateById
         */
        public function testGetDataAndLabelsByType()
        {
            $type           = EmailTemplate::TYPE_CONTACT;
            $dataAndLabels  = EmailTemplate::getDataAndLabelsByType($type);
            $this->assertCount(2, $dataAndLabels);
            $this->assertContains('contact 01', $dataAndLabels);
            $this->assertContains('contact 03', $dataAndLabels);

            $type           = EmailTemplate::TYPE_WORKFLOW;
            $dataAndLabels  = EmailTemplate::getDataAndLabelsByType($type);
            $this->assertCount(2, $dataAndLabels);
            $this->assertContains('note 01', $dataAndLabels);
            $this->assertContains('workflow 01', $dataAndLabels);
            // do another call to ensure this time cache is served:
            $dataAndLabels  = EmailTemplate::getDataAndLabelsByType($type);
        }
    }
?>