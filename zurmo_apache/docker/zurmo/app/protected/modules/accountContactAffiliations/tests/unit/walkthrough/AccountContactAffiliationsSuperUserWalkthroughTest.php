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
     * AccountContactAffiliations Module Walkthrough.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class AccountContactAffiliationsSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected static $account;

        protected static $account2;

        protected static $contact;

        protected static $contact2;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            self::$account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            self::$account2 = AccountTestHelper::createAccountByNameForOwner('superAccount2', $super);
            self::$contact = ContactTestHelper::createContactWithAccountByNameForOwner('superContact', $super, self::$account);
            self::$contact2 = ContactTestHelper::createContactByNameForOwner('superContact2', $super);
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $accountContactAffiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(1, count($accountContactAffiliations));

            //Default Controller actions requiring some sort of parameter via POST or GET
            $this->setGetArray(array('id' => $accountContactAffiliations[0]->id));
            $this->runControllerWithNoExceptionsAndGetContent('accountContactAffiliations/default/edit');
            //Save accountContactAffiliation.
            $this->assertEquals(null, $accountContactAffiliations[0]->role->value);
            $this->setPostArray(array('AccountContactAffiliation' => array('role' => array('value' => 'Technical'))));
            $this->runControllerWithRedirectExceptionAndGetContent('accountContactAffiliations/default/edit');
            $accountContactAffiliation = AccountContactAffiliation::getById($accountContactAffiliations[0]->id);
            $this->assertEquals('Technical', $accountContactAffiliation->role->value);

            //actionAuditEventsModalList
            $this->setGetArray(array('id' => $accountContactAffiliation->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accountContactAffiliations/default/auditEventsModalList');
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserDefaultPortletControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            $this->setGetArray(array('id' => self::$contact->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/details');
            //Add the portlet in for the account and contact detailview. then load up the details to make sure it is ok
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                                    'ContactDetailsAndRelationsView', $super->id, array());
            $this->assertEquals (3, count($portlets[2]));
            $this->setGetArray(array(
                'modelId'    => self::$contact->id,
                'portletType'    => 'AccountAffiliationsForContactRelatedList',
                'uniqueLayoutId' => 'ContactDetailsAndRelationsView'));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('contacts/defaultPortlet/add');
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                                    'ContactDetailsAndRelationsView', $super->id, array());
            $this->assertEquals (4, count($portlets[2]));

            //Now add portlet in on account detailview
            $this->setGetArray(array('id' => self::$account->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                'AccountDetailsAndRelationsView', $super->id, array());
            $this->assertEquals (4, count($portlets[2]));
            $this->setGetArray(array(
                'modelId'    => self::$account->id,
                'portletType'    => 'ContactAffiliationsForAccountRelatedList',
                'uniqueLayoutId' => 'AccountDetailsAndRelationsView'));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/defaultPortlet/add');
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                'AccountDetailsAndRelationsView', $super->id, array());
            $this->assertEquals (5, count($portlets[2]));

            //Load Details View again to make sure everything is ok after the layout change.
            $this->setGetArray(array('id' => self::$contact->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('contacts/default/details');
            $this->setGetArray(array('id' => self::$account->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
        }

        public function testSuperUserCreateFromRelationAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $accountContactAffiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(1, count($accountContactAffiliations));

            //Create a new AccountContactAffiliation from a related account.
            $this->setGetArray(array(   'relationAttributeName' => 'account',
                                        'relationModelId'       => self::$account2->id,
                                        'relationModuleId'      => 'accounts',
                                        'redirectUrl'           => 'someRedirect'));
            $this->setPostArray(array('AccountContactAffiliation' => array(
                                        'role'      => array('value' => 'TechnicalX'),
                                        'contact'   => array('id' => self::$contact2->id))));
            $this->runControllerWithRedirectExceptionAndGetContent('accountContactAffiliations/default/createFromRelation');
            $accountContactAffiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(2, count($accountContactAffiliations));
            $this->assertEquals('TechnicalX', $accountContactAffiliations[1]->role->value);
            $this->assertTrue($accountContactAffiliations[1]->account->isSame(self::$account2));
            $this->assertTrue($accountContactAffiliations[1]->contact->isSame(self::$contact2));
        }

        public function testSuperUserDeleteAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $accountContactAffiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(2, count($accountContactAffiliations));

            //Delete an AccountContactAffiliation model.
            $this->setGetArray(array('id' => $accountContactAffiliations[0]->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accountContactAffiliations/default/delete', true);
            $accountContactAffiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(1, count($accountContactAffiliations));
        }
    }
?>