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
    class AccountAccountAffiliationsDesignerSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static $activateDefaultLanguages = true;

        protected static $account;

        protected static $account2;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            Currency::makeBaseCurrency();

            //Setup test data owned by the super user.
            self::$account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            self::$account2 = AccountTestHelper::createAccountByNameForOwner('superAccount2', $super);
        }

        public function testSuperUserAccountAccountAffiliationDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load AccountAccountAffiliation Modules Menu.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountAccountAffiliationsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/modulesMenu');

            //Load AttributesList for AccountAccountAffiliation module.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountAccountAffiliationsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/attributesList');

            //Load ModuleLayoutsList for AccountAccountAffiliation module.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountAccountAffiliationsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleLayoutsList');

            //Load ModuleEdit view for each applicable module.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountAccountAffiliationsModule'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/moduleEdit');

            //Now validate save with failed validation.
            $this->setGetArray(array('moduleClassName' => 'AccountAccountAffiliationsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'AccountAccountAffiliationsModuleForm' => $this->createModuleEditBadValidationPostData()));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');

            //Now validate save with successful validation.
            $this->setGetArray(array('moduleClassName' => 'AccountAccountAffiliationsModule'));
            $this->setPostArray(array('ajax' => 'edit-form',
                'AccountAccountAffiliationsModuleForm' => $this->createModuleEditGoodValidationPostData('acc new name')));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/moduleEdit');
            $this->assertEquals('[]', $content);

            //Load LayoutEdit for each applicable module and applicable layout
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountAccountAffiliationsModule',
                                     'viewClassName'   => 'AccountAccountAffiliationEditAndDetailsView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
            $this->setGetArray(array('moduleClassName' => 'AccountAccountAffiliationsModule',
                                     'viewClassName'   => 'AccountAccountAffiliationsRelatedListView'));
            $this->runControllerWithNoExceptionsAndGetContent('designer/default/LayoutEdit');
        }

        /**
         * @depends testSuperUserAccountAccountAffiliationDefaultControllerActions
         */
        public function testSuperUserCustomFieldsWalkthroughForAccountAccountAffiliationsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test create field list.
            $this->resetPostArray();
            $this->setGetArray(array('moduleClassName' => 'AccountAccountAffiliationsModule'));

            //View creation screen, then create custom field for each custom field type.
            $this->createCheckBoxCustomFieldByModule            ('AccountAccountAffiliationsModule', 'checkbox');
            $this->createDecimalCustomFieldByModule             ('AccountAccountAffiliationsModule', 'decimal');
            $this->createDropDownCustomFieldByModule            ('AccountAccountAffiliationsModule', 'picklist');
            $this->createDependentDropDownCustomFieldByModule   ('AccountAccountAffiliationsModule', 'countrylist');
            $this->createDependentDropDownCustomFieldByModule   ('AccountAccountAffiliationsModule', 'statelist');
            $this->createDependentDropDownCustomFieldByModule   ('AccountAccountAffiliationsModule', 'citylist');
            $this->createMultiSelectDropDownCustomFieldByModule ('AccountAccountAffiliationsModule', 'multiselect');
            $this->createTagCloudCustomFieldByModule            ('AccountAccountAffiliationsModule', 'tagcloud');
            $this->createDropDownDependencyCustomFieldByModule  ('AccountAccountAffiliationsModule', 'dropdowndep');
            $this->createDropDownDependencyCustomFieldByModule  ('AccountAccountAffiliationsModule', 'dropdowndep2');
            $this->createIntegerCustomFieldByModule             ('AccountAccountAffiliationsModule', 'integer');
            $this->createPhoneCustomFieldByModule               ('AccountAccountAffiliationsModule', 'phone');
            $this->createRadioDropDownCustomFieldByModule       ('AccountAccountAffiliationsModule', 'radio');
            $this->createTextCustomFieldByModule                ('AccountAccountAffiliationsModule', 'text');
            $this->createTextAreaCustomFieldByModule            ('AccountAccountAffiliationsModule', 'textarea');
            $this->createUrlCustomFieldByModule                 ('AccountAccountAffiliationsModule', 'url');
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForAccountAccountAffiliationsModule
         */
        public function testSuperUserAddCustomFieldsToLayoutsForAccountAccountAffiliationsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Add custom fields to AccountAccountAffiliationEditAndDetailsView.
            $this->setGetArray(array('moduleClassName' => 'AccountAccountAffiliationsModule',
                                     'viewClassName'   => 'AccountAccountAffiliationEditAndDetailsView'));
            $layout = AccountAccountAffiliationsDesignerWalkthroughHelperUtil::getAccountAccountAffiliationEditAndDetailsViewLayoutWithAllCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout,
                                      'LayoutPanelsTypeForm' => array('type' => FormLayout::PANELS_DISPLAY_TYPE_ALL)));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertContains('Layout saved successfully', $content);

            //Add all fields to AccountsRelatedListView.
            $this->setGetArray(array('moduleClassName' => 'AccountsModule',
                                     'viewClassName'   => 'AccountsRelatedListView'));
            $layout = AccountAccountAffiliationsDesignerWalkthroughHelperUtil::getAccountAccountAffiliationsRelatedListViewLayoutWithAllStandardAndCustomFieldsPlaced();
            $this->setPostArray(array('save'  => 'Save', 'layout' => $layout));
            $content = $this->runControllerWithExitExceptionAndGetContent('designer/default/LayoutEdit');
            $this->assertContains('Layout saved successfully', $content);
        }

        /**
         * @depends testSuperUserCustomFieldsWalkthroughForAccountAccountAffiliationsModule
         */
        public function testLayoutsLoadOkAfterCustomFieldsPlacedForAccountAccountAffiliationsModule()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            //Load create, edit views.
            $this->runControllerWithNoExceptionsAndGetContent('accountAccountAffiliations/default/create');
            $accountAccountAffiliations = AccountAccountAffiliation::getAll();
            $this->assertEquals(0, count($accountAccountAffiliations));

            //Create a new AccountAccountAffiliation from a related account.
            $this->setGetArray(array(   'relationAttributeName' => 'primaryAccount',
                                        'relationModelId'       => self::$account2->id,
                                        'relationModuleId'      => 'accounts',
                                        'redirectUrl'           => 'someRedirect'));
            $this->setPostArray(array('AccountAccountAffiliation' => array(
                                        'secondaryAccount'   => array('id' => self::$account->id))));
            $this->runControllerWithRedirectExceptionAndGetContent('accountAccountAffiliations/default/createFromRelation');

            $accountAccountAffiliations = AccountAccountAffiliation::getAll();
            $this->assertEquals(1, count($accountAccountAffiliations));
            $this->setGetArray(array('id' => $accountAccountAffiliations[0]->id));
            $this->runControllerWithRedirectExceptionAndGetContent('accountAccountAffiliations/default/edit');
        }
    }
?>