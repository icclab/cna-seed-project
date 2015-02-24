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
     * AccountAccountAffiliations Module Walkthrough.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class AccountAccountAffiliationsSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        protected static $account;

        protected static $account2;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            self::$account = AccountTestHelper::createAccountByNameForOwner('superAccount', $super);
            self::$account2 = AccountTestHelper::createAccountByNameForOwner('superAccount2', $super);
        }

        public function testSuperUserDefaultPortletControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $this->setGetArray(array('id' => self::$account->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                'AccountDetailsAndRelationsView', $super->id, array());
            $this->assertEquals (4, count($portlets[2]));
            $this->setGetArray(array(
                'modelId'    => self::$account->id,
                'portletType'    => 'AccountAccountAffiliationsRelatedList',
                'uniqueLayoutId' => 'AccountDetailsAndRelationsView'));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('accounts/defaultPortlet/add');
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                        'AccountDetailsAndRelationsView', $super->id, array());
            $this->assertEquals (5, count($portlets[2]));

            //Load Details View again to make sure everything is ok after the layout change.
            $this->setGetArray(array('id' => self::$account->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accounts/default/details');
        }

        public function testSuperUserCreateFromRelationAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
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
            $this->assertTrue($accountAccountAffiliations[0]->primaryAccount->isSame(self::$account2));
            $this->assertTrue($accountAccountAffiliations[0]->secondaryAccount->isSame(self::$account));

            //actionAuditEventsModalList
            $this->setGetArray(array('id' => $accountAccountAffiliations[0]->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accountAccountAffiliations/default/auditEventsModalList');
        }

        public function testSuperUserDeleteAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $accountAccountAffiliations = AccountAccountAffiliation::getAll();
            $this->assertEquals(1, count($accountAccountAffiliations));

            //Delete an AccountAccountAffiliation model.
            $this->setGetArray(array('id' => $accountAccountAffiliations[0]->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('accountAccountAffiliations/default/delete', true);
            $accountAccountAffiliations = AccountAccountAffiliation::getAll();
            $this->assertEquals(0, count($accountAccountAffiliations));
        }
    }
?>