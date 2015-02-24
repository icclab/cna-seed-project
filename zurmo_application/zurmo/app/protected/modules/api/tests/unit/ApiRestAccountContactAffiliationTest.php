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
    * Test Account related API functions.
    */
    class ApiRestAccountContactAffiliationTest extends ApiRestTest
    {
        public function testGetAccountContactAffiliation()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $account = AccountTestHelper::createAccountByNameForOwner('firstAccount', Yii::app()->user->userModel);
            $account2 = AccountTestHelper::createAccountByNameForOwner('secondAccount', Yii::app()->user->userModel);

            $contact = ContactTestHelper::createContactByNameForOwner('First', Yii::app()->user->userModel);
            $contact2 = ContactTestHelper::createContactByNameForOwner('Second', Yii::app()->user->userModel);

            $accountContactAffiliation = new AccountContactAffiliation();
            $accountContactAffiliation->account   = $account;
            $accountContactAffiliation->contact   = $contact;
            $this->assertTrue($accountContactAffiliation->save());

            $accountContactAffiliation2 = new AccountContactAffiliation();
            $accountContactAffiliation2->account   = $account2;
            $accountContactAffiliation2->contact   = $contact2;
            $this->assertTrue($accountContactAffiliation2->save());

            $compareData    = $this->getModelToApiDataUtilData($accountContactAffiliation);
            $response = $this->createApiCallWithRelativeUrl('read/' . $accountContactAffiliation->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
        * @depends testGetAccountContactAffiliation
        */
        public function testDeleteAccountContactAffiliation()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $affiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(2, count($affiliations));

            $response = $this->createApiCallWithRelativeUrl('delete/' . $affiliations[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $affiliations[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('The ID specified was invalid.', $response['message']);

            $affiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(1, count($affiliations));
        }

        /**
         * @depends testGetAccountContactAffiliation
         */
        public function testCreateAccountContactAffiliation()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            AllPermissionsOptimizationUtil::rebuild();
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $account = AccountTestHelper::createAccountByNameForOwner('CreateAccount', Yii::app()->user->userModel);
            $contact = ContactTestHelper::createContactByNameForOwner('CreateContact', Yii::app()->user->userModel);

            $data['account']['id'] = $account->id;
            $data['contact']['id'] = $contact->id;
            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($account->id, $response['data']['account']['id']);
            $this->assertEquals($contact->id, $response['data']['contact']['id']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $response['data']['id'], 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($account->id, $response['data']['account']['id']);
            $this->assertEquals($contact->id, $response['data']['contact']['id']);
        }

        /**
         * @depends testCreateAccountContactAffiliation
         */
        public function testUpdateAccountContactAffiliation()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $account = AccountTestHelper::createAccountByNameForOwner('UpdateAccount', Yii::app()->user->userModel);
            $contact = ContactTestHelper::createContactByNameForOwner('FirstUpdateContact', Yii::app()->user->userModel);
            $contact2 = ContactTestHelper::createContactByNameForOwner('SecondUpdateContact', Yii::app()->user->userModel);

            $accountContactAffiliation = new AccountContactAffiliation();
            $accountContactAffiliation->account   = $account;
            $accountContactAffiliation->contact = $contact;
            $this->assertTrue($accountContactAffiliation->save());

            $data['contact']['id'] = $contact2->id;

            $response = $this->createApiCallWithRelativeUrl('update/' . $accountContactAffiliation->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($account->id, $response['data']['account']['id']);
            $this->assertEquals($contact2->id, $response['data']['contact']['id']);

            $accountContactAffiliationId = $accountContactAffiliation->id;
            $accountContactAffiliation->forgetAll();
            $accountContactAffiliation = AccountContactAffiliation::getById($accountContactAffiliationId);
            $this->assertEquals($accountContactAffiliation->account->id, $response['data']['account']['id']);
            $this->assertEquals($accountContactAffiliation->contact->id, $response['data']['contact']['id']);
        }

        public function testGetAffiliatedAccountsAndContacts()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $account = AccountTestHelper::createAccountByNameForOwner('firstAffAccount', Yii::app()->user->userModel);
            $account2 = AccountTestHelper::createAccountByNameForOwner('secondAffAccount', Yii::app()->user->userModel);
            $account3 = AccountTestHelper::createAccountByNameForOwner('thirdAffAccount', Yii::app()->user->userModel);
            $contact = ContactTestHelper::createContactByNameForOwner('firstAffContact', Yii::app()->user->userModel);
            $contact2 = ContactTestHelper::createContactByNameForOwner('secondAffContact', Yii::app()->user->userModel);
            $contact3 = ContactTestHelper::createContactByNameForOwner('thirdAffContact', Yii::app()->user->userModel);

            $accountContactAffiliation = new AccountContactAffiliation();
            $accountContactAffiliation->account   = $account;
            $accountContactAffiliation->contact = $contact;
            $this->assertTrue($accountContactAffiliation->save());
            sleep(1);
            $accountContactAffiliation2 = new AccountContactAffiliation();
            $accountContactAffiliation2->account   = $account;
            $accountContactAffiliation2->contact = $contact2;
            $this->assertTrue($accountContactAffiliation2->save());
            sleep(1);
            $accountContactAffiliation = new AccountContactAffiliation();
            $accountContactAffiliation->account   = $account2;
            $accountContactAffiliation->contact = $contact2;
            $this->assertTrue($accountContactAffiliation->save());
            sleep(1);
            $accountContactAffiliation = new AccountContactAffiliation();
            $accountContactAffiliation->account   = $account3;
            $accountContactAffiliation->contact = $contact3;
            $this->assertTrue($accountContactAffiliation->save());

            // Get all contacts affiliated with account
            $data = array(
                'search' => array(
                    'modelClassName' => 'AccountContactAffiliation',
                    'searchAttributeData' => array(
                        'clauses' => array(
                            1 => array(
                                'attributeName'        => 'account',
                                'relatedAttributeName' => 'id',
                                'operatorType'         => 'equals',
                                'value'                => $account->id,
                            ),
                        ),
                        'structure' => '1',
                    ),
                ),
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 5,
                ),
                'sort' => 'id asc',
            );

            $response = $this->createApiCallWithRelativeUrl('search/filter/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, $response['data']['totalCount']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals($account->id, $response['data']['items'][0]['account']['id']);
            $this->assertEquals($contact->id, $response['data']['items'][0]['contact']['id']);
            $this->assertEquals($account->id, $response['data']['items'][1]['account']['id']);
            $this->assertEquals($contact2->id, $response['data']['items'][1]['contact']['id']);

            // Get all accounts affiliated with account
            $data = array(
                'search' => array(
                    'modelClassName' => 'AccountContactAffiliation',
                    'searchAttributeData' => array(
                        'clauses' => array(
                            1 => array(
                                'attributeName'        => 'contact',
                                'relatedAttributeName' => 'id',
                                'operatorType'         => 'equals',
                                'value'                => $contact2->id,
                            ),
                        ),
                        'structure' => '1',
                    ),
                ),
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 5,
                ),
                'sort' => 'id asc',
            );

            $response = $this->createApiCallWithRelativeUrl('search/filter/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, $response['data']['totalCount']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals($account->id, $response['data']['items'][0]['account']['id']);
            $this->assertEquals($contact2->id, $response['data']['items'][0]['contact']['id']);
            $this->assertEquals($account2->id, $response['data']['items'][1]['account']['id']);
            $this->assertEquals($contact2->id, $response['data']['items'][1]['contact']['id']);
        }

        public function testListAccountContactAffiliationAttributes()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $allAttributes      = ApiRestTestHelper::getModelAttributes(new AccountContactAffiliation());
            $response = $this->createApiCallWithRelativeUrl('listAttributes/' , 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($allAttributes, $response['data']['items']);
        }

        protected function getApiControllerClassName()
        {
            Yii::import('application.modules.accountContactAffiliations.controllers.AccountContactAffiliationApiController', true);
            return 'AccountContactAffiliationsAccountContactAffiliationApiController';
        }

        protected function getModuleBaseApiUrl()
        {
            return 'accountContactAffiliations/AccountContactAffiliation/api/';
        }
    }
?>