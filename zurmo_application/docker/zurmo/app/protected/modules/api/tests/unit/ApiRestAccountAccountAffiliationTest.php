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
    class ApiRestAccountAccountAffiliationTest extends ApiRestTest
    {
        public function testGetAccountAccountAffiliation()
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
            $account3 = AccountTestHelper::createAccountByNameForOwner('thirdAccount', Yii::app()->user->userModel);

            $accountAccountAffiliation = new AccountAccountAffiliation();
            $accountAccountAffiliation->primaryAccount   = $account;
            $accountAccountAffiliation->secondaryAccount = $account2;
            $this->assertTrue($accountAccountAffiliation->save());

            $accountAccountAffiliation2 = new AccountAccountAffiliation();
            $accountAccountAffiliation2->primaryAccount   = $account;
            $accountAccountAffiliation2->secondaryAccount = $account3;
            $this->assertTrue($accountAccountAffiliation2->save());

            $compareData    = $this->getModelToApiDataUtilData($accountAccountAffiliation);
            $response = $this->createApiCallWithRelativeUrl('read/' . $accountAccountAffiliation->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
        * @depends testGetAccountAccountAffiliation
        */
        public function testDeleteAccountAccountAffiliation()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $affiliations = AccountAccountAffiliation::getAll();
            $this->assertEquals(2, count($affiliations));

            $response = $this->createApiCallWithRelativeUrl('delete/' . $affiliations[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $affiliations[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('The ID specified was invalid.', $response['message']);

            $affiliations = AccountAccountAffiliation::getAll();
            $this->assertEquals(1, count($affiliations));
        }

        /**
         * @depends testGetAccountAccountAffiliation
         */
        public function testCreateAccountAccountAffiliation()
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
            $account = AccountTestHelper::createAccountByNameForOwner('primaryAccount', Yii::app()->user->userModel);
            $account2 = AccountTestHelper::createAccountByNameForOwner('secondaryAccount', Yii::app()->user->userModel);

            $data['primaryAccount']['id']   = $account->id;
            $data['secondaryAccount']['id'] = $account2->id;
            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($account->id, $response['data']['primaryAccount']['id']);
            $this->assertEquals($account2->id, $response['data']['secondaryAccount']['id']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $response['data']['id'], 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($account->id, $response['data']['primaryAccount']['id']);
            $this->assertEquals($account2->id, $response['data']['secondaryAccount']['id']);
        }

        /**
         * @depends testCreateAccountAccountAffiliation
         */
        public function testUpdateAccountAccountAffiliation()
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

            $account = AccountTestHelper::createAccountByNameForOwner('primaryUpdateAccount', Yii::app()->user->userModel);
            $account2 = AccountTestHelper::createAccountByNameForOwner('secondaryUpdateAccount', Yii::app()->user->userModel);
            $account3 = AccountTestHelper::createAccountByNameForOwner('thirdUpdateAccount', Yii::app()->user->userModel);

            $accountAccountAffiliation = new AccountAccountAffiliation();
            $accountAccountAffiliation->primaryAccount   = $account;
            $accountAccountAffiliation->secondaryAccount = $account2;
            $this->assertTrue($accountAccountAffiliation->save());

            $data['secondaryAccount']['id'] = $account3->id;

            $response = $this->createApiCallWithRelativeUrl('update/' . $accountAccountAffiliation->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($account->id, $response['data']['primaryAccount']['id']);
            $this->assertEquals($account3->id, $response['data']['secondaryAccount']['id']);

            $accountAccountAffiliationId = $accountAccountAffiliation->id;
            $accountAccountAffiliation->forgetAll();
            $accountAccountAffiliation = AccountAccountAffiliation::getById($accountAccountAffiliationId);
            $this->assertEquals($accountAccountAffiliation->primaryAccount->id, $response['data']['primaryAccount']['id']);
            $this->assertEquals($accountAccountAffiliation->secondaryAccount->id, $response['data']['secondaryAccount']['id']);
        }

        public function testGetAffiliatedAccounts()
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
            $account4 = AccountTestHelper::createAccountByNameForOwner('forthAffAccount', Yii::app()->user->userModel);

            $accountAccountAffiliation = new AccountAccountAffiliation();
            $accountAccountAffiliation->primaryAccount   = $account;
            $accountAccountAffiliation->secondaryAccount = $account2;
            $this->assertTrue($accountAccountAffiliation->save());
            sleep(1);
            $accountAccountAffiliation2 = new AccountAccountAffiliation();
            $accountAccountAffiliation2->primaryAccount   = $account3;
            $accountAccountAffiliation2->secondaryAccount = $account;
            $this->assertTrue($accountAccountAffiliation2->save());
            sleep(1);
            $accountAccountAffiliation3 = new AccountAccountAffiliation();
            $accountAccountAffiliation3->primaryAccount   = $account3;
            $accountAccountAffiliation3->secondaryAccount = $account4;
            $this->assertTrue($accountAccountAffiliation3->save());

            $data = array(
                'search' => array(
                    'modelClassName' => 'AccountAccountAffiliation',
                    'searchAttributeData' => array(
                        'clauses' => array(
                            1 => array(
                                'attributeName'        => 'primaryAccount',
                                'relatedAttributeName' => 'id',
                                'operatorType'         => 'equals',
                                'value'                => $account->id,
                            ),
                            2 => array(
                                'attributeName'        => 'secondaryAccount',
                                'relatedAttributeName' => 'id',
                                'operatorType'         => 'equals',
                                'value'                => $account->id,
                            ),
                        ),
                        'structure' => '(1 OR 2)',
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
            $this->assertEquals($account->id, $response['data']['items'][0]['primaryAccount']['id']);
            $this->assertEquals($account2->id, $response['data']['items'][0]['secondaryAccount']['id']);
            $this->assertEquals($account3->id, $response['data']['items'][1]['primaryAccount']['id']);
            $this->assertEquals($account->id, $response['data']['items'][1]['secondaryAccount']['id']);
        }

        public function testListAccountAccountAffiliationAttributes()
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
            $allAttributes      = ApiRestTestHelper::getModelAttributes(new AccountAccountAffiliation());
            $response = $this->createApiCallWithRelativeUrl('listAttributes/' , 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($allAttributes, $response['data']['items']);
        }

        protected function getApiControllerClassName()
        {
            Yii::import('application.modules.accountAccountAffiliations.controllers.AccountAccountAffiliationApiController', true);
            return 'AccountAccountAffiliationsAccountAccountAffiliationApiController';
        }

        protected function getModuleBaseApiUrl()
        {
            return 'accountAccountAffiliations/AccountAccountAffiliation/api/';
        }
    }
?>