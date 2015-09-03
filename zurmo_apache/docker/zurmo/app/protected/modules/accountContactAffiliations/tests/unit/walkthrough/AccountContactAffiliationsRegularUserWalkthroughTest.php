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
     *
     * Walkthrough for a peon user.  The peon user at first will have no granted
     * rights or permissions.  Most attempted actions will result in an ExitException
     * and a access failure view.  After this, we elevate the user with added tab rights
     * so that some of the actions will result in success and no exceptions being thrown.
     * There will still be some actions they cannot get too though because of the lack of
     * elevated permissions.  Then we will elevate permissions to allow the user to access
     * other owner's records.
     */
    class AccountContactAffiliationsRegularUserWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        protected static $account;

        protected static $contact;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            Yii::app()->user->userModel = User::getByUsername('nobody');

            self::$account = AccountTestHelper::createAccountByNameForOwner('superAccount', Yii::app()->user->userModel);
            self::$contact = ContactTestHelper::
                             createContactWithAccountByNameForOwner('superContact', Yii::app()->user->userModel, self::$account);
        }

        public function testRegularUserHasIncorrectRightsAndThenElevates()
        {
            Yii::app()->user->userModel = User::getByUsername('nobody');

            //Now test access failure with incorrect access
            $this->setGetArray(array(   'relationAttributeName' => 'account',
                'relationModelId'       => self::$account->id,
                'relationModuleId'      => 'accounts',
                'redirectUrl'           => 'someRedirect'));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accountContactAffiliations/default/createFromRelation');

            //Now add rights to AccountContactAffiliationsModule
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Now test peon with elevated rights to contacts
            $nobody = User::getByUsername('nobody');
            $nobody->setRight('AccountContactAffiliationsModule', AccountContactAffiliationsModule::RIGHT_ACCESS_ACCOUNT_CONTACT_AFFILIATIONS);
            $this->assertTrue($nobody->save());

            //Should still failure because missing access to accounts and contacts
            Yii::app()->user->userModel = User::getByUsername('nobody');
            $this->setGetArray(array(   'relationAttributeName' => 'account',
                'relationModelId'       => self::$account->id,
                'relationModuleId'      => 'accounts',
                'redirectUrl'           => 'someRedirect'));
            $this->runControllerShouldResultInAccessFailureAndGetContent('accountContactAffiliations/default/createFromRelation');

            //Now add rights to accounts and contacts
            $nobody = User::getByUsername('nobody');
            $nobody->setRight('ContactsModule', ContactsModule::RIGHT_ACCESS_CONTACTS);
            $nobody->setRight('AccountsModule', AccountsModule::RIGHT_ACCESS_ACCOUNTS);
            $this->assertTrue($nobody->save());

            //Now nobody user should be able to get to the action ok
            Yii::app()->user->userModel = User::getByUsername('nobody');
            $this->setGetArray(array(   'relationAttributeName' => 'account',
                'relationModelId'       => self::$account->id,
                'relationModuleId'      => 'accounts',
                'redirectUrl'           => 'someRedirect'));
            $this->runControllerWithNoExceptionsAndGetContent('accountContactAffiliations/default/createFromRelation');
        }
    }
?>
