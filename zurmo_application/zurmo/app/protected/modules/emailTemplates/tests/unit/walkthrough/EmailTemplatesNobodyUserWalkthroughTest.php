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

    /**
     * EmailTemplates Module Nobody User Walkthrough.
     */
    class EmailTemplatesNobodyUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected static $templateOwnedBySuper;

        protected $user;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $maker  = new EmailTemplatesDefaultDataMaker();
            $maker->make();
            $demoDataHelper = new DemoDataHelper();
            $demoDataHelper->setRangeByModelName('User', 1, 10);
            $groupsDemoDataMaker = new GroupsDemoDataMaker();
            $groupsDemoDataMaker->makeAll($demoDataHelper);
            ReadPermissionsOptimizationUtil::rebuild();
            // Setup test data owned by the super user.
            static::$templateOwnedBySuper = EmailTemplateTestHelper::create('Test Name1',
                'Test Subject1',
                'Contact',
                'Test HtmlContent1',
                'Test TextContent1');
            UserTestHelper::createBasicUser('nobody');
            ReadPermissionsOptimizationUtil::rebuild();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
        }

        public function testAllDefaultControllerActions()
        {
            $this->user->setRight('ContactsModule', ContactsModule::getAccessRight());
            $this->assertTrue($this->user->save());
            $emailTemplate = EmailTemplateTestHelper::create('Test Name Regular 01', 'Test Subject Regular 01',
                'Contact',
                'Test HtmlContent Regular 01',
                'Test TextContent Regular 01');

            $this->runControllerShouldResultInAccessFailureAndGetContent('emailTemplates/default');
            $this->runControllerShouldResultInAccessFailureAndGetContent('emailTemplates/default/index');
            $this->runControllerShouldResultInAccessFailureAndGetContent('emailTemplates/default/listForMarketing');
            $this->runControllerShouldResultInAccessFailureAndGetContent('emailTemplates/default/listForWorkflow');
            $this->setGetArray(array('type' => EmailTemplate::TYPE_CONTACT));
            $this->runControllerShouldResultInAccessFailureAndGetContent('emailTemplates/default/create');
            $this->setGetArray(array('id' => $emailTemplate->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('emailTemplates/default/edit');
            $this->runControllerShouldResultInAccessFailureAndGetContent('emailTemplates/default/details');
            $this->runControllerShouldResultInAccessFailureAndGetContent('emailTemplates/default/delete');
            $this->resetGetArray();

            $this->user->setRight('EmailTemplatesModule', EmailTemplatesModule::getAccessRight());
            $this->assertTrue($this->user->save());
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default');
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/listForMarketing');
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/listForWorkflow');
            $this->setGetArray(array('id' => $emailTemplate->id));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/details');
            $this->resetGetArray();

            $this->user->setRight('EmailTemplatesModule', EmailTemplatesModule::getCreateRight());
            $this->assertTrue($this->user->save());
            $this->setGetArray(array('type' => EmailTemplate::TYPE_CONTACT,
                'builtType' => EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/create');
            $this->setGetArray(array('type' => EmailTemplate::TYPE_WORKFLOW,
                'builtType' => EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/create');
            $this->setGetArray(array('id' => $emailTemplate->id));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/edit');

            $this->user->setRight('EmailTemplatesModule', EmailTemplatesModule::getDeleteRight());
            $this->assertTrue($this->user->save());
            $this->runControllerWithRedirectExceptionAndGetUrl('emailTemplates/default/delete');

            $this->setGetArray(array('id' => static::$templateOwnedBySuper->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('emailTemplates/default/edit');
            RedBeanModel::forgetAll();
            $this->runControllerShouldResultInAccessFailureAndGetContent('emailTemplates/default/details');
            RedBeanModel::forgetAll();
            $this->runControllerShouldResultInAccessFailureAndGetContent('emailTemplates/default/delete');
        }

        public function testUserWithNotReadPermissionForEmailTemplatesModuleStillCanSeePredefinedTemplates()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $everyone   = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $item       = NamedSecurableItem::getByName('EmailTemplatesModule');
            $item->addPermissions($everyone, Permission::READ, Permission::DENY);
            $this->assertTrue($item->save());
            ReadPermissionsOptimizationUtil::rebuild();
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            $emailTemplate2 = EmailTemplateTestHelper::create('Test Name Regular 02', 'Test Subject Regular 02',
                'Contact', 'Test HtmlContent Regular 01', 'Test TextContent Regular 01',
                EmailTemplate::TYPE_CONTACT, 0, EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE);

            $this->setGetArray(array('id' => $emailTemplate2->id,
                                     'filterBy' => SelectBaseTemplateElement::FILTER_BY_PREDEFINED_TEMPLATES,
                                     'ajax' => 'BuilderEmailTemplateWizardForm_baseTemplateId_list_view'));
            $content = @$this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/edit');
            $this->assertContains('<div class="summary">Displaying 1-6 of 6 results.</div><ul class="template-list clearfix">', $content);
            $emailTemplate2->delete();
        }
    }
?>