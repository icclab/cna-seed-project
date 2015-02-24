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
    * Designer Module Walkthrough of accounts.
    * Walkthrough for the super user of all possible controller actions.
    * Since this is a super user, he should have access to all controller actions
    * without any exceptions being thrown.
    * This also test the creation of the customfileds, addition of custom fields to all the layouts including the search
    * views.
    * This also test creation, search, edit and delete of the account based on the custom fields.
    */
    class AccountContactAffiliationsDesignerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static $activateDefaultLanguages = true;

        protected static $account;

        protected static $contact;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            Currency::makeBaseCurrency();

            //Setup test data owned by the super user.
            self::$account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            self::$contact = ContactTestHelper::createContactByNameForOwner('superContact2', $super);
        }

        public function testSuperUserAccountContactAffiliationDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load AccountContactAffiliation Modules Menu.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountContactAffiliationsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/modulesMenu');

            //Load AttributesList for AccountContactAffiliation module.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountContactAffiliationsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributesList');

            //Load ModuleLayoutsList for AccountContactAffiliation module.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountContactAffiliationsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');

            //Load ModuleEdit view for each applicable module.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountContactAffiliationsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleEdit');

            //Now validate save with failed validation.
            $this->setGetArray(array('moduleClassName' => 'AccountContactAffiliationsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'AccountsModuleForm' => $this->createModuleEditBadValidationPostData()));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');

            //Now validate save with successful validation.
            $this->setGetArray(array('moduleClassName' => 'AccountContactAffiliationsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'AccountsModuleForm' => $this->createModuleEditGoodValidationPostData('acc new name')));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertEquals('[]', $content);

            //Load LayoutEdit for each applicable module and applicable layout
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountContactAffiliationsModule',
                                     'viewClassName'   => 'AccountContactAffiliationEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'AccountContactAffiliationsModule',
                                     'viewClassName'   => 'AccountAffiliationsForContactRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'AccountContactAffiliationsModule',
                                     'viewClassName'   => 'ContactAffiliationsForAccountRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
        }

        /**
         * @depends testSuperUserAccountContactAffiliationDefaultControllerActions
         */
        public function testSuperUserCustomFieldsWalkthroughForAccountContactAffiliationsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountContactAffiliationsModule'));

            //View creation screen, then create custom field for each custom field type.
            $this->createCheckBoxCustomFieldByModule            ('AccountContactAffiliationsModule', 'checkbox');
            $this->createDecimalCustomFieldByModule             ('AccountContactAffiliationsModule', 'decimal');
            $this->createDropDownCustomFieldByModule            ('AccountContactAffiliationsModule', 'picklist');
            $this->createDependentDropDownCustomFieldByModule   ('AccountContactAffiliationsModule', 'countrylist');
            $this->createDependentDropDownCustomFieldByModule   ('AccountContactAffiliationsModule', 'statelist');
            $this->createDependentDropDownCustomFieldByModule   ('AccountContactAffiliationsModule', 'citylist');
            $this->createMultiSelectDropDownCustomFieldByModule ('AccountContactAffiliationsModule', 'multiselect');
            $this->createTagCloudCustomFieldByModule            ('AccountContactAffiliationsModule', 'tagcloud');
            $this->createDropDownDependencyCustomFieldByModule  ('AccountContactAffiliationsModule', 'dropdowndep');
            $this->createDropDownDependencyCustomFieldByModule  ('AccountContactAffiliationsModule', 'dropdowndep2');
            $this->createIntegerCustomFieldByModule             ('AccountContactAffiliationsModule', 'integer');
            $this->createPhoneCustomFieldByModule               ('AccountContactAffiliationsModule', 'phone');
            $this->createRadioDropDownCustomFieldByModule       ('AccountContactAffiliationsModule', 'radio');
            $this->createTextCustomFieldByModule                ('AccountContactAffiliationsModule', 'text');
            $this->createTextAreaCustomFieldByModule            ('AccountContactAffiliationsModule', 'textarea');
            $this->createUrlCustomFieldByModule                 ('AccountContactAffiliationsModule', 'url');
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForAccountContactAffiliationsModule
         */
        public function testSuperUserAddCustomFieldsToLayoutsForAccountContactAffiliationsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to AccountContactAffiliationEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'AccountContactAffiliationsModule',
                                     'viewClassName'   => 'AccountContactAffiliationEditAndDetailsView'));
            $layout = AccountContactAffiliationsDesignerWalkthroughHelperUtil::getAccountContactAffiliationEditAndDetailsViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertContains('Layout saved successfully', $content);

            //Add all fields to ContactAffiliationsForAccountRelatedListView.
            $this->setGetArray(array('moduleClassName' => 'AccountContactAffiliationsModule',
                                     'viewClassName'   => 'ContactAffiliationsForAccountRelatedListView'));
            $layout = AccountContactAffiliationsDesignerWalkthroughHelperUtil::getContactAffiliationsForAccountRelatedListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertContains('Layout saved successfully', $content);

            //Add all fields to AccountAffiliationsForContactRelatedListView.
            $this->setGetArray(array('moduleClassName' => 'AccountContactAffiliationsModule',
                                     'viewClassName'   => 'AccountAffiliationsForContactRelatedListView'));
            $layout = AccountContactAffiliationsDesignerWalkthroughHelperUtil::getContactAffiliationsForAccountRelatedListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertContains('Layout saved successfully', $content);
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForAccountContactAffiliationsModule
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForAccountContactAffiliationsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Load create, edit views.
            $this->runControllerWithNoExceptionsAndGetContent('accountContactAffiliations/default/create');
            $accountContactAffiliation = AccountContactAffiliation::getAll();
            $this->assertEquals(0, count($accountContactAffiliation));

            //Create a new AccountContactAffiliation from a related account.
            $this->setGetArray(array(   'relationAttributeName' => 'account',
                                        'relationModelId'       => self::$account->id,
                                        'relationModuleId'      => 'accounts',
                                        'redirectUrl'           => 'someRedirect'));
            $this->setPostArray(array('AccountContactAffiliation' => array(
                                        'role'      => array('value' => 'TechnicalX'),
                                        'contact'   => array('id' => self::$contact->id))));
            $this->runControllerWithRedirectExceptionAndGetContent('accountContactAffiliations/default/createFromRelation');

            $accountContactAffiliation = AccountContactAffiliation::getAll();
            $this->assertEquals(1, count($accountContactAffiliation));
            $this->setGetArray(array('id' => $accountContactAffiliation[0]->id));
            $this->runControllerWithRedirectExceptionAndGetContent('accountContactAffiliations/default/edit');
        }
    }
?>