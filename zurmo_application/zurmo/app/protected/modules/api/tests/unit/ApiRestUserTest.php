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
    * Test User related API functions.
    */
    class ApiRestUserTest extends ApiRestTest
    {
        protected static $createUsersAndGroups = false;

        public function testGetUser()
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

            $user = UserTestHelper::createBasicUser('PeterSmith');
            $compareData  =  $this->getModelToApiDataUtilData($user);

            $response = $this->createApiCallWithRelativeUrl('read/' . $user->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
         * @depends testGetUser
         */
        public function testGetAuthenticatedUser()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $super->primaryEmail->emailAddress   = 'super@zurmo.org';
            $this->assertTrue($super->save());
            Yii::app()->user->userModel = $super;
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $compareData  =  $this->getModelToApiDataUtilData($super);
            $response = $this->createApiCallWithRelativeUrl('getAuthenticatedUser', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
         * @depends testGetUser
         */
        public function testDeleteUser()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $user = User::getByUsername('petersmith');

            $response = $this->createApiCallWithRelativeUrl('delete/' . $user->id, 'DELETE', $headers);

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $user->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('The ID specified was invalid.', $response['message']);
        }

        public function testCreateUser()
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

            $titles = array('Mr.', 'Mrs.', 'Ms.', 'Dr.', 'Swami');
            $customFieldData = CustomFieldData::getByName('Titles');
            $customFieldData->serializedData = serialize($titles);
            $this->assertTrue($customFieldData->save());

            $primaryEmail['emailAddress']   = "a@example.com";
            $primaryEmail['optOut']         = 1;

            $primaryAddress['street1']      = '129 Noodle Boulevard';
            $primaryAddress['street2']      = 'Apartment 6000A';
            $primaryAddress['city']         = 'Noodleville';
            $primaryAddress['postalCode']   = '23453';
            $primaryAddress['country']      = 'The Good Old US of A';

            $currencies                 = Currency::getAll();
            $currency    = $currencies[0];
            $this->assertEquals('USD', $currency->code);

            $manager = new User();
            $manager->username     = 'smith45';
            $manager->title->value = 'Mr.';
            $manager->firstName    = 'Super';
            $manager->lastName     = 'User';
            $manager->setPassword('smith45');
            $saved = $manager->save();
            $this->assertTrue($saved);

            $data['firstName']            = "Michael";
            $data['lastName']             = "Smith";
            $data['jobTitle']             = "President";
            $data['department']           = "Sales";
            $data['officePhone']          = "653-235-7824";
            $data['mobilePhone']          = "653-235-7821";
            $data['officeFax']            = "653-235-7834";
            $data['username']             = "diggy011";
            $data['password']             = "diggy011";
            $data['language']             = "en";
            $data['timeZone']             = "America/Chicago";

            $data['title']['value']       = $titles[3];
            $data['manager']['id']        = $manager->id;

            $data['primaryEmail']         = $primaryEmail;
            $data['primaryAddress']       = $primaryAddress;
            $data['serializedAvatarData'] = '';

            $data['currency']       = array(
                'id' => $currency->id
            );

            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $data['createdByUser']    = array(
                'id' => $super->id,
                'username' => 'super'
            );
            $data['modifiedByUser'] = array(
                'id' => $super->id,
                'username' => 'super'
            );

            // We need to unset some empty values from response.
            unset($response['data']['lastLoginDateTime']);
            unset($response['data']['locale']);
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['primaryEmail']['id'] );
            unset($response['data']['primaryEmail']['isInvalid'] );
            unset($response['data']['primaryAddress']['id']);
            unset($response['data']['primaryAddress']['state']);
            unset($response['data']['primaryAddress']['longitude']);
            unset($response['data']['primaryAddress']['latitude']);
            unset($response['data']['primaryAddress']['invalid']);
            unset($response['data']['title']['id']);
            unset($response['data']['id']);
            unset($response['data']['password']);
            unset($response['data']['manager']['username']);
            unset($response['data']['hideFromLeaderboard']);
            unset($response['data']['hideFromSelecting']);
            unset($response['data']['isRootUser']);
            unset($response['data']['isSystemUser']);
            unset($data['password']);

            ksort($data);
            ksort($response['data']);
            //Add isActive
            $data['isActive'] = '1';
            $this->assertEquals($data, $response['data']);
            // Check if new user can log in
            $newUser = User::getByUsername('diggy011');
            $newUser->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $saved = $newUser->save();
            $authenticationData = $this->login('diggy011', 'diggy011');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
        }

        /**
         * @depends testCreateUser
         */
        public function testUpdateUser()
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

            $user = User::getByUsername('diggy011');
            $compareData  =  $this->getModelToApiDataUtilData($user);
            $user->forget();

            $data['firstName']                = "John";
            $data['password']                 = "aswe019";
            $response = $this->createApiCallWithRelativeUrl('update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            // We need to unset some empty values from response and dates.
            unset($response['data']['modifiedDateTime']);
            unset($compareData['modifiedDateTime']);
            unset($response['data']['lastLoginDateTime']);
            unset($compareData['lastLoginDateTime']);
            $compareData['firstName'] = "John";
            ksort($compareData);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);

            // Check if password is updated
            RedBeanModel::forgetAll();
            $updatedUser = User::getByUsername('diggy011');
            $this->assertNotEquals($user->hash, $updatedUser->hash);

            $response = $this->createApiCallWithRelativeUrl('read/' . $user->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['lastLoginDateTime']);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
         * @depends testUpdateUser
         */
        public function testListUsers()
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

            $response = $this->createApiCallWithRelativeUrl('list/', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
        }

        public function testListUserAttributes()
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
            $allAttributes      = ApiRestTestHelper::getModelAttributes(new User());

            $response = $this->createApiCallWithRelativeUrl('listAttributes/' , 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($allAttributes, $response['data']['items']);
        }

        /**
         * @depends testListUsers
         */
        public function testUnprivilegedUserViewUpdateDeleteUsers()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $notAllowedUser = UserTestHelper::createBasicUser('Steven');
            $notAllowedUser->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $saved = $notAllowedUser->save();

            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $this->assertTrue($everyoneGroup->save());

            $user = User::getByUsername('diggy011');
            $data['firstName']                = "Sam";

            // Test with unprivileged user to view, edit and delete account.
            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $response = $this->createApiCallWithRelativeUrl('read/' . $user->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            $response = $this->createApiCallWithRelativeUrl('update/' . $user->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            $response = $this->createApiCallWithRelativeUrl('delete/' . $user->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            //now check if user have rights, but no permissions.
            $notAllowedUser->setRight('UsersModule', UsersModule::getAccessRight());
            $notAllowedUser->setRight('UsersModule', UsersModule::getCreateRight());
            $saved = $notAllowedUser->save();
            $this->assertTrue($saved);

            $response = $this->createApiCallWithRelativeUrl('read/' . $user->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = $this->createApiCallWithRelativeUrl('update/' . $user->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals('Sam', $response['data']['firstName']);

            $response = $this->createApiCallWithRelativeUrl('delete/' . $user->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $user->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
        }

        /**
        * @depends testUnprivilegedUserViewUpdateDeleteUsers
        */
        public function testBasicSearchUsers()
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
            $manager = User::getByUsername('smith45');

            UserTestHelper::createBasicUser('First');
            UserTestHelper::createBasicUser('Second');
            UserTestHelper::createBasicUser('Third');
            UserTestHelper::createBasicUser('Forth');
            UserTestHelper::createBasicUserWithManager('Fifth', $manager);

            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'username' => '',
                ),
                'sort' => 'username',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(8, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('fifth', $response['data']['items'][0]['username']);
            $this->assertEquals('first', $response['data']['items'][1]['username']);
            $this->assertEquals('forth', $response['data']['items'][2]['username']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(8, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('second', $response['data']['items'][0]['username']);
            $this->assertEquals('smith45', $response['data']['items'][1]['username']);
            $this->assertEquals('steven', $response['data']['items'][2]['username']);

            // Search by name
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['username'] = 'first';
            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('first', $response['data']['items'][0]['username']);

            // No results
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['username'] = 'first2';
            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(0, $response['data']['totalCount']);
            $this->assertFalse(isset($response['data']['items']));

            // Search by name desc.
            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                     'username' => '',
                ),
                'sort' => 'username.desc',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(8, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('third', $response['data']['items'][0]['username']);
            $this->assertEquals('super', $response['data']['items'][1]['username']);
            $this->assertEquals('steven', $response['data']['items'][2]['username']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(8, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('smith45', $response['data']['items'][0]['username']);
            $this->assertEquals('second', $response['data']['items'][1]['username']);
            $this->assertEquals('forth', $response['data']['items'][2]['username']);

            // Search by custom fields, order by name desc
            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'manager'   => array( 'id' => $manager->id),
                ),
                'sort' => 'username',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('fifth', $response['data']['items'][0]['username']);
        }

        /**
        * @depends testBasicSearchUsers
        */
        public function testDynamicSearchUsers()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel        = $super;

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $data = array(
                'dynamicSearch' => array(
                    'dynamicClauses' => array(
                        array(
                            'attributeIndexOrDerivedType' => 'name',
                            'structurePosition' => 1,
                            'username' => 'Fi',
                        ),
                        array(
                            'attributeIndexOrDerivedType' => 'name',
                            'structurePosition' => 2,
                            'username' => 'Se',
                        ),
                    ),
                    'dynamicStructure' => '1 OR 2',
                ),
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 2,
                ),
                'sort' => 'username.asc',
           );

            $response = $this->createApiCallWithRelativeUrl('list/filter/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('fifth', $response['data']['items'][0]['username']);
            $this->assertEquals('first', $response['data']['items'][1]['username']);

            // Get second page
            $data['pagination']['page'] = 2;
            $response = $this->createApiCallWithRelativeUrl('list/filter/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('second', $response['data']['items'][0]['username']);
        }

        public function testNewSearchUsers()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel        = $super;

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $data = array(
                'search' => array(
                    'modelClassName' => 'User',
                    'searchAttributeData' => array(
                        'clauses' => array(
                            1 => array(
                                'attributeName'        => 'username',
                                'operatorType'         => 'startsWith',
                                'value'                => 'Fi'
                            ),
                            2 => array(
                                'attributeName'        => 'username',
                                'operatorType'         => 'startsWith',
                                'value'                => 'Se'
                            ),
                        ),
                        'structure' => '1 OR 2',
                    ),
                ),
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 2,
                ),
                'sort' => 'username asc',
            );

            $response = $this->createApiCallWithRelativeUrl('search/filter/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('fifth', $response['data']['items'][0]['username']);
            $this->assertEquals('first', $response['data']['items'][1]['username']);

            // Get second page
            $data['pagination']['page'] = 2;
            $response = $this->createApiCallWithRelativeUrl('search/filter/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('second', $response['data']['items'][0]['username']);
        }

        public function testEditUserWithIncompleteData()
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

            $user = UserTestHelper::createBasicUser('PeterSmith2');

            // Provide data without required fields.
            $data['username']         = "";

            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(2, count($response['errors']));

            $id = $user->id;
            $data = array();
            $data['username']                = '';
            $response = $this->createApiCallWithRelativeUrl('update/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));
        }

        public function testEditUserWIthIncorrectDataType()
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

            $user = UserTestHelper::createBasicUser('JosephSmith');

            // Provide data with wrong type.
            $data['language']         = "AAAAAAAAAAAA";

            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(3, count($response['errors']));

            $id = $user->id;
            $data = array();
            $data['language']         = "AAAAAAAAAAAA";
            $response = $this->createApiCallWithRelativeUrl('update/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));
        }

        protected function getApiControllerClassName()
        {
            Yii::import('application.modules.users.controllers.UserApiController', true);
            return 'UsersUserApiController';
        }

        protected function getModuleBaseApiUrl()
        {
            return 'users/user/api/';
        }
    }
?>