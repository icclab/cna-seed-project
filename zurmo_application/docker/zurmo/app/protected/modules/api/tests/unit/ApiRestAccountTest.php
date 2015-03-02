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
    class ApiRestAccountTest extends ApiRestTest
    {
        public function testGetAccount()
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

            $account        = AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('First Account', 'Customer',
                                                                                                'Automotive', $super);
            $compareData    = $this->getModelToApiDataUtilData($account);
            $response = $this->createApiCallWithRelativeUrl('read/' . $account->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);
            $this->assertArrayHasKey('owner', $response['data']);
            $this->assertCount(2, $response['data']['owner']);
            $this->assertArrayHasKey('id', $response['data']['owner']);
            $this->assertEquals($super->id, $response['data']['owner']['id']);
            $this->assertArrayHasKey('explicitReadWriteModelPermissions', $response['data']);
            $this->assertArrayHasKey('type', $response['data']['explicitReadWriteModelPermissions']);
            $this->assertArrayHasKey('nonEveryoneGroup', $response['data']['explicitReadWriteModelPermissions']);
        }

        /**
        * @depends testGetAccount
        */
        public function testDeleteAccount()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $accounts = Account::getByName('First Account');
            $this->assertEquals(1, count($accounts));

            $response = $this->createApiCallWithRelativeUrl('delete/' . $accounts[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $accounts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('The ID specified was invalid.', $response['message']);
        }

        public function testCreateAccount()
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

            $industryValues = array(
                'Automotive',
                'Adult Entertainment',
                'Financial Services',
                'Mercenaries & Armaments',
            );
            $industryFieldData = CustomFieldData::getByName('Industries');
            $industryFieldData->serializedData = serialize($industryValues);
            $this->assertTrue($industryFieldData->save());

            $typeValues = array(
                'Prospect',
                'Customer',
                'Vendor',
            );
            $typeFieldData = CustomFieldData::getByName('AccountTypes');
            $typeFieldData->serializedData = serialize($typeValues);
            $this->assertTrue($typeFieldData->save());

            $primaryEmail['emailAddress']   = "a@example.com";
            $primaryEmail['optOut']         = 1;

            $secondaryEmail['emailAddress'] = "b@example.com";
            $secondaryEmail['optOut']       = 0;
            $secondaryEmail['isInvalid']    = 1;

            $billingAddress['street1']      = '129 Noodle Boulevard';
            $billingAddress['street2']      = 'Apartment 6000A';
            $billingAddress['city']         = 'Noodleville';
            $billingAddress['postalCode']   = '23453';
            $billingAddress['country']      = 'The Good Old US of A';

            $shippingAddress['street1']     = '25 de Agosto 2543';
            $shippingAddress['street2']     = 'Local 3';
            $shippingAddress['city']        = 'Ciudad de Los Fideos';
            $shippingAddress['postalCode']  = '5123-4';
            $shippingAddress['country']     = 'Latinoland';

            $account = new Account();
            $data['name']                = "My Company with no permissions";
            $data['officePhone']         = "6438238";
            $data['officeFax']           = "6565465436";
            $data['employees']           = 100;
            $data['website']             = "http://www.google.com";
            $data['annualRevenue']       = "1000000";
            $data['description']         = "Some Description";

            $data['industry']['value']   = $industryValues[2];
            $data['type']['value']       = $typeValues[1];

            $data['primaryEmail']        = $primaryEmail;
            $data['secondaryEmail']      = $secondaryEmail;
            $data['billingAddress']      = $billingAddress;
            $data['shippingAddress']     = $shippingAddress;

            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertArrayHasKey('id', $response['data']);
            $accountId     = $response['data']['id'];

            $this->assertArrayHasKey('owner', $response['data']);
            $this->assertCount(2, $response['data']['owner']);
            $this->assertArrayHasKey('id', $response['data']['owner']);
            $this->assertEquals($super->id, $response['data']['owner']['id']);
            $this->assertArrayHasKey('explicitReadWriteModelPermissions', $response['data']);
            $this->assertCount(2, $response['data']['explicitReadWriteModelPermissions']);
            $this->assertArrayHasKey('type', $response['data']['explicitReadWriteModelPermissions']);
            $this->assertEquals(1, $response['data']['explicitReadWriteModelPermissions']['type']);
            $this->assertArrayHasKey('nonEveryoneGroup', $response['data']['explicitReadWriteModelPermissions']);
            $this->assertEquals('', $response['data']['explicitReadWriteModelPermissions']['nonEveryoneGroup']);

            $data['owner'] = array(
                'id' => $super->id,
                'username' => 'super'
            );
            $data['createdByUser']    = array(
                'id' => $super->id,
                'username' => 'super'
            );
            $data['modifiedByUser'] = array(
                'id' => $super->id,
                'username' => 'super'
            );

            // unset explicit permissions, we won't use these in comparison.
            unset($response['data']['explicitReadWriteModelPermissions']);
            // We need to unset some empty values from response.
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['primaryEmail']['id']);
            unset($response['data']['primaryEmail']['isInvalid']);
            unset($response['data']['secondaryEmail']['id']);
            unset($response['data']['billingAddress']['id']);
            unset($response['data']['billingAddress']['state']);
            unset($response['data']['billingAddress']['longitude']);
            unset($response['data']['billingAddress']['latitude']);
            unset($response['data']['billingAddress']['invalid']);

            unset($response['data']['shippingAddress']['id']);
            unset($response['data']['shippingAddress']['state']);
            unset($response['data']['shippingAddress']['longitude']);
            unset($response['data']['shippingAddress']['latitude']);
            unset($response['data']['shippingAddress']['invalid']);
            unset($response['data']['industry']['id']);
            unset($response['data']['latestActivityDateTime']);
            unset($response['data']['type']['id']);
            unset($response['data']['id']);

            ksort($data);
            ksort($response['data']);
            $this->assertEquals($data, $response['data']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $accountId, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertArrayHasKey('data', $response);
            $this->assertArrayHasKey('owner', $response['data']);
            $this->assertCount(2, $response['data']['owner']);
            $this->assertArrayHasKey('id', $response['data']['owner']);
            $this->assertEquals($super->id, $response['data']['owner']['id']);

            $this->assertArrayHasKey('explicitReadWriteModelPermissions', $response['data']);
            $this->assertCount(2, $response['data']['explicitReadWriteModelPermissions']);
            $this->assertArrayHasKey('type', $response['data']['explicitReadWriteModelPermissions']);
            $this->assertEquals(1, $response['data']['explicitReadWriteModelPermissions']['type']);
            $this->assertArrayHasKey('nonEveryoneGroup', $response['data']['explicitReadWriteModelPermissions']);
            $this->assertEquals('', $response['data']['explicitReadWriteModelPermissions']['nonEveryoneGroup']);
        }

        /**
         * @depends testCreateAccount
         */
        public function testCreateAccountWithSpecificOwner()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $billy  = User::getByUsername('billy');
            AllPermissionsOptimizationUtil::rebuild();
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $industryValues = array(
                'Automotive',
                'Adult Entertainment',
                'Financial Services',
                'Mercenaries & Armaments',
            );
            $industryFieldData = CustomFieldData::getByName('Industries');
            $industryFieldData->serializedData = serialize($industryValues);
            $this->assertTrue($industryFieldData->save());

            $typeValues = array(
                'Prospect',
                'Customer',
                'Vendor',
            );
            $typeFieldData = CustomFieldData::getByName('AccountTypes');
            $typeFieldData->serializedData = serialize($typeValues);
            $this->assertTrue($typeFieldData->save());

            $primaryEmail['emailAddress']   = "a@example.com";
            $primaryEmail['optOut']         = 1;

            $secondaryEmail['emailAddress'] = "b@example.com";
            $secondaryEmail['optOut']       = 0;
            $secondaryEmail['isInvalid']    = 1;

            $billingAddress['street1']      = '129 Noodle Boulevard';
            $billingAddress['street2']      = 'Apartment 6000A';
            $billingAddress['city']         = 'Noodleville';
            $billingAddress['postalCode']   = '23453';
            $billingAddress['country']      = 'The Good Old US of A';

            $shippingAddress['street1']     = '25 de Agosto 2543';
            $shippingAddress['street2']     = 'Local 3';
            $shippingAddress['city']        = 'Ciudad de Los Fideos';
            $shippingAddress['postalCode']  = '5123-4';
            $shippingAddress['country']     = 'Latinoland';

            $account = new Account();
            $data['name']                = "My Company with just owner";
            $data['officePhone']         = "6438238";
            $data['officeFax']           = "6565465436";
            $data['employees']           = 100;
            $data['website']             = "http://www.google.com";
            $data['annualRevenue']       = "1000000";
            $data['description']         = "Some Description";

            $data['industry']['value']   = $industryValues[2];
            $data['type']['value']       = $typeValues[1];

            $data['primaryEmail']        = $primaryEmail;
            $data['secondaryEmail']      = $secondaryEmail;
            $data['billingAddress']      = $billingAddress;
            $data['shippingAddress']     = $shippingAddress;
            $data['owner']['id']        = $billy->id;

            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertArrayHasKey('id', $response['data']);
            $accountId     = $response['data']['id'];

            $this->assertArrayHasKey('owner', $response['data']);
            $this->assertCount(2, $response['data']['owner']);
            $this->assertArrayHasKey('id', $response['data']['owner']);
            $this->assertEquals($billy->id, $response['data']['owner']['id']);
            $this->assertArrayHasKey('explicitReadWriteModelPermissions', $response['data']);
            $this->assertCount(2, $response['data']['explicitReadWriteModelPermissions']);
            $this->assertArrayHasKey('type', $response['data']['explicitReadWriteModelPermissions']);
            $this->assertEquals(1, $response['data']['explicitReadWriteModelPermissions']['type']);
            $this->assertArrayHasKey('nonEveryoneGroup', $response['data']['explicitReadWriteModelPermissions']);
            $this->assertEquals('', $response['data']['explicitReadWriteModelPermissions']['nonEveryoneGroup']);

            $data['owner'] = array(
                'id' => $billy->id,
                'username' => 'billy'
            );
            $data['createdByUser']    = array(
                'id' => $super->id,
                'username' => 'super'
            );
            $data['modifiedByUser'] = array(
                'id' => $super->id,
                'username' => 'super'
            );

            // unset explicit permissions, we won't use these in comparison.
            unset($response['data']['explicitReadWriteModelPermissions']);
            // We need to unset some empty values from response.
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['primaryEmail']['id']);
            unset($response['data']['primaryEmail']['isInvalid']);
            unset($response['data']['secondaryEmail']['id']);
            unset($response['data']['billingAddress']['id']);
            unset($response['data']['billingAddress']['state']);
            unset($response['data']['billingAddress']['longitude']);
            unset($response['data']['billingAddress']['latitude']);
            unset($response['data']['billingAddress']['invalid']);

            unset($response['data']['shippingAddress']['id']);
            unset($response['data']['shippingAddress']['state']);
            unset($response['data']['shippingAddress']['longitude']);
            unset($response['data']['shippingAddress']['latitude']);
            unset($response['data']['shippingAddress']['invalid']);
            unset($response['data']['industry']['id']);
            unset($response['data']['latestActivityDateTime']);
            unset($response['data']['type']['id']);
            unset($response['data']['id']);

            ksort($data);
            ksort($response['data']);
            $this->assertEquals($data, $response['data']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $accountId, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertArrayHasKey('data', $response);
            $this->assertArrayHasKey('owner', $response['data']);
            $this->assertCount(2, $response['data']['owner']);
            $this->assertArrayHasKey('id', $response['data']['owner']);
            $this->assertEquals($billy->id, $response['data']['owner']['id']);

            $this->assertArrayHasKey('explicitReadWriteModelPermissions', $response['data']);
            $this->assertCount(2, $response['data']['explicitReadWriteModelPermissions']);
            $this->assertArrayHasKey('type', $response['data']['explicitReadWriteModelPermissions']);
            $this->assertEquals(1, $response['data']['explicitReadWriteModelPermissions']['type']);
            $this->assertArrayHasKey('nonEveryoneGroup', $response['data']['explicitReadWriteModelPermissions']);
            $this->assertEquals('', $response['data']['explicitReadWriteModelPermissions']['nonEveryoneGroup']);
        }

        /**
         * @depends testCreateAccount
         */
        public function testCreateAccountWithSpecificExplicitPermissions()
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

            $industryValues = array(
                'Automotive',
                'Adult Entertainment',
                'Financial Services',
                'Mercenaries & Armaments',
            );
            $industryFieldData = CustomFieldData::getByName('Industries');
            $industryFieldData->serializedData = serialize($industryValues);
            $this->assertTrue($industryFieldData->save());

            $typeValues = array(
                'Prospect',
                'Customer',
                'Vendor',
            );
            $typeFieldData = CustomFieldData::getByName('AccountTypes');
            $typeFieldData->serializedData = serialize($typeValues);
            $this->assertTrue($typeFieldData->save());

            $primaryEmail['emailAddress']   = "a@example.com";
            $primaryEmail['optOut']         = 1;

            $secondaryEmail['emailAddress'] = "b@example.com";
            $secondaryEmail['optOut']       = 0;
            $secondaryEmail['isInvalid']    = 1;

            $billingAddress['street1']      = '129 Noodle Boulevard';
            $billingAddress['street2']      = 'Apartment 6000A';
            $billingAddress['city']         = 'Noodleville';
            $billingAddress['postalCode']   = '23453';
            $billingAddress['country']      = 'The Good Old US of A';

            $shippingAddress['street1']     = '25 de Agosto 2543';
            $shippingAddress['street2']     = 'Local 3';
            $shippingAddress['city']        = 'Ciudad de Los Fideos';
            $shippingAddress['postalCode']  = '5123-4';
            $shippingAddress['country']     = 'Latinoland';

            $account = new Account();
            $data['name']                = "My Company with owner only permissions";
            $data['officePhone']         = "6438238";
            $data['officeFax']           = "6565465436";
            $data['employees']           = 100;
            $data['website']             = "http://www.google.com";
            $data['annualRevenue']       = "1000000";
            $data['description']         = "Some Description";

            $data['industry']['value']   = $industryValues[2];
            $data['type']['value']       = $typeValues[1];

            $data['primaryEmail']        = $primaryEmail;
            $data['secondaryEmail']      = $secondaryEmail;
            $data['billingAddress']      = $billingAddress;
            $data['shippingAddress']     = $shippingAddress;
            // TODO: @Shoaibi/@Ivica: null does not work, empty works. null doesn't send it.
            $data['explicitReadWriteModelPermissions'] = array('nonEveryoneGroup' => '', 'type' => '');

            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertArrayHasKey('id', $response['data']);
            $accountId     = $response['data']['id'];

            $this->assertArrayHasKey('owner', $response['data']);
            $this->assertCount(2, $response['data']['owner']);
            $this->assertArrayHasKey('id', $response['data']['owner']);
            $this->assertEquals($super->id, $response['data']['owner']['id']);
            $this->assertArrayHasKey('explicitReadWriteModelPermissions', $response['data']);
            $this->assertCount(2, $response['data']['explicitReadWriteModelPermissions']);
            $this->assertArrayHasKey('type', $response['data']['explicitReadWriteModelPermissions']);
            $this->assertEquals('', $response['data']['explicitReadWriteModelPermissions']['type']);
            // following also works. wonder why.
            //$this->assertTrue(null === $response['data']['explicitReadWriteModelPermissions']['type']);
            $this->assertArrayHasKey('nonEveryoneGroup', $response['data']['explicitReadWriteModelPermissions']);
            $this->assertEquals('', $response['data']['explicitReadWriteModelPermissions']['nonEveryoneGroup']);

            $data['owner'] = array(
                'id' => $super->id,
                'username' => 'super'
            );
            $data['createdByUser']    = array(
                'id' => $super->id,
                'username' => 'super'
            );
            $data['modifiedByUser'] = array(
                'id' => $super->id,
                'username' => 'super'
            );

            // We need to unset some empty values from response.
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['primaryEmail']['id']);
            unset($response['data']['primaryEmail']['isInvalid']);
            unset($response['data']['secondaryEmail']['id']);
            unset($response['data']['billingAddress']['id']);
            unset($response['data']['billingAddress']['state']);
            unset($response['data']['billingAddress']['longitude']);
            unset($response['data']['billingAddress']['latitude']);
            unset($response['data']['billingAddress']['invalid']);

            unset($response['data']['shippingAddress']['id']);
            unset($response['data']['shippingAddress']['state']);
            unset($response['data']['shippingAddress']['longitude']);
            unset($response['data']['shippingAddress']['latitude']);
            unset($response['data']['shippingAddress']['invalid']);
            unset($response['data']['industry']['id']);
            unset($response['data']['latestActivityDateTime']);
            unset($response['data']['type']['id']);
            unset($response['data']['id']);

            ksort($data);
            ksort($response['data']);
            $this->assertEquals($data, $response['data']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $accountId, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertArrayHasKey('data', $response);
            $this->assertArrayHasKey('owner', $response['data']);
            $this->assertCount(2, $response['data']['owner']);
            $this->assertArrayHasKey('id', $response['data']['owner']);
            $this->assertEquals($super->id, $response['data']['owner']['id']);

            $this->assertArrayHasKey('explicitReadWriteModelPermissions', $response['data']);
            $this->assertCount(2, $response['data']['explicitReadWriteModelPermissions']);
            $this->assertArrayHasKey('type', $response['data']['explicitReadWriteModelPermissions']);
            $this->assertEquals('', $response['data']['explicitReadWriteModelPermissions']['type']);
            $this->assertArrayHasKey('nonEveryoneGroup', $response['data']['explicitReadWriteModelPermissions']);
            $this->assertEquals('', $response['data']['explicitReadWriteModelPermissions']['nonEveryoneGroup']);
        }

        /**
        * @depends testCreateAccountWithSpecificOwner
        */
        public function testUpdateAccount()
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

            $accounts = Account::getByName('My Company with just owner');
            $this->assertEquals(1, count($accounts));
            $compareData                                        = $this->getModelToApiDataUtilData($accounts[0]);
            $group  = static::$randomNonEveryoneNonAdministratorsGroup;
            $explicitReadWriteModelPermissions = array('type' => 2, 'nonEveryoneGroup' => $group->id);

            $data['name']                                       = "My Company 2";
            $data['explicitReadWriteModelPermissions']          = $explicitReadWriteModelPermissions;
            $compareData['name']                                = "My Company 2";
            $compareData['explicitReadWriteModelPermissions']   = $explicitReadWriteModelPermissions;

            $response = $this->createApiCallWithRelativeUrl('update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            // We need to unset some empty values from response and dates.
            unset($response['data']['modifiedDateTime']);
            unset($compareData['modifiedDateTime']);
            ksort($compareData);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $compareData['id'], 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            unset($response['data']['modifiedDateTime']);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
        * @depends testUpdateAccount
        */
        public function testListAccounts()
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

            $accounts = Account::getByName('My Company 2');
            $this->assertEquals(1, count($accounts));
            $compareData  = $this->getModelToApiDataUtilData($accounts[0]);

            $response = $this->createApiCallWithRelativeUrl('list/' , 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals($compareData, $response['data']['items'][0]);
        }

        public function testListAccountAttributes()
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
            $allAttributes      = ApiRestTestHelper::getModelAttributes(new Account());
            $response = $this->createApiCallWithRelativeUrl('listAttributes/' , 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($allAttributes, $response['data']['items']);
        }

        /**
        * @depends testListAccounts
        */
        public function testUnprivilegedUserViewUpdateDeleteAcounts()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $notAllowedUser = UserTestHelper::createBasicUser('Steven');
            $notAllowedUser->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $this->assertTrue($notAllowedUser->save());

            // Test with unprivileged user to view, edit and delete account.
            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $this->assertTrue($everyoneGroup->save());

            $accounts = Account::getByName('My Company 2');
            $this->assertEquals(1, count($accounts));
            $data['name']                = "My Company 3";

            // Check first if user doesn't have rights.
            $response = $this->createApiCallWithRelativeUrl('read/' . $accounts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            $response = $this->createApiCallWithRelativeUrl('update/' . $accounts[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            $response = $this->createApiCallWithRelativeUrl('delete/' . $accounts[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            //now check if user have rights, but no permissions.
            $notAllowedUser->setRight('AccountsModule', AccountsModule::getAccessRight());
            $notAllowedUser->setRight('AccountsModule', AccountsModule::getCreateRight());
            $notAllowedUser->setRight('AccountsModule', AccountsModule::getDeleteRight());
            $saved = $notAllowedUser->save();
            $this->assertTrue($saved);

            $response = $this->createApiCallWithRelativeUrl('read/' . $accounts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            $response = $this->createApiCallWithRelativeUrl('update/' . $accounts[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            $response = $this->createApiCallWithRelativeUrl('delete/' . $accounts[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            // Test with privileged user
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            unset($data);
            $data['explicitReadWriteModelPermissions'] = array(
                'type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_EVERYONE_GROUP
            );
            $response = $this->createApiCallWithRelativeUrl('update/' . $accounts[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $response = $this->createApiCallWithRelativeUrl('read/' . $accounts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            unset($data);
            $data['name']                = "My Company 3";
            $response = $this->createApiCallWithRelativeUrl('update/' . $accounts[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals('My Company 3', $response['data']['name']);

            // Test with privileged user
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            //Test Delete
            $response = $this->createApiCallWithRelativeUrl('delete/' . $accounts[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $accounts[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
        }

        /**
        * @depends testUnprivilegedUserViewUpdateDeleteAcounts
        */
        public function testBasicSearchAccounts()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            Account::deleteAll();
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('First Account', 'Customer', 'Automotive', $super);
            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Second Account', 'Customer', 'Automotive', $super);
            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Third Account', 'Customer', 'Financial Services', $super);
            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Forth Account', 'Vendor', 'Financial Services', $super);
            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Fifth Account', 'Vendor', 'Financial Services', $super);

            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'name' => '',
                ),
                'sort' => 'name',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Fifth Account', $response['data']['items'][0]['name']);
            $this->assertEquals('First Account', $response['data']['items'][1]['name']);
            $this->assertEquals('Forth Account', $response['data']['items'][2]['name']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('Second Account', $response['data']['items'][0]['name']);
            $this->assertEquals('Third Account', $response['data']['items'][1]['name']);

            // Search by name
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['name'] = 'First Account';
            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('First Account', $response['data']['items'][0]['name']);

            // No results
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['name'] = 'First Account 2';
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
                    'name' => '',
                ),
                'sort' => 'name.desc',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Third Account', $response['data']['items'][0]['name']);
            $this->assertEquals('Second Account', $response['data']['items'][1]['name']);
            $this->assertEquals('Forth Account', $response['data']['items'][2]['name']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('First Account', $response['data']['items'][0]['name']);
            $this->assertEquals('Fifth Account', $response['data']['items'][1]['name']);

            // Search by custom fields, order by name desc
            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'industry' => array( 'value' => 'Financial Services'),
                    'type'     => array( 'value' => 'Vendor'),
                    'owner'   => array( 'id' => $super->id),
                ),
                'sort' => 'name.desc',
            );
            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, $response['data']['totalCount']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Forth Account', $response['data']['items'][0]['name']);
            $this->assertEquals('Fifth Account', $response['data']['items'][1]['name']);
        }

        /**
        * @depends testBasicSearchAccounts
        */
        public function testDynamicSearchAccounts()
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
                            'attributeIndexOrDerivedType' => 'owner',
                            'structurePosition' => 1,
                            'owner' => array(
                                'id' => Yii::app()->user->userModel->id,
                            ),
                        ),
                        array(
                            'attributeIndexOrDerivedType' => 'name',
                            'structurePosition' => 2,
                            'name' => 'Fi',
                        ),
                        array(
                            'attributeIndexOrDerivedType' => 'name',
                            'structurePosition' => 3,
                            'name' => 'Se',
                        ),
                    ),
                    'dynamicStructure' => '1 AND (2 OR 3)',
                ),
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 2,
                ),
                'sort' => 'name.asc',
           );

            $response = $this->createApiCallWithRelativeUrl('list/filter/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Fifth Account', $response['data']['items'][0]['name']);
            $this->assertEquals('First Account', $response['data']['items'][1]['name']);

            // Get second page
            $data['pagination']['page'] = 2;
            $response = $this->createApiCallWithRelativeUrl('list/filter/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('Second Account', $response['data']['items'][0]['name']);
        }

        public function testNewSearchAccounts()
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
                    'modelClassName' => 'Account',
                    'searchAttributeData' => array(
                        'clauses' => array(
                            1 => array(
                                'attributeName'        => 'owner',
                                'relatedAttributeName' => 'id',
                                'operatorType'         => 'equals',
                                'value'                => Yii::app()->user->userModel->id,
                            ),
                            2 => array(
                                'attributeName'        => 'name',
                                'operatorType'         => 'startsWith',
                                'value'                => 'Fi'
                            ),
                            3 => array(
                                'attributeName'        => 'name',
                                'operatorType'         => 'startsWith',
                                'value'                => 'Se'
                            ),
                        ),
                        'structure' => '1 AND (2 OR 3)',
                    ),
                ),
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 2,
                ),
                'sort' => 'name asc',
            );

            $response = $this->createApiCallWithRelativeUrl('search/filter/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Fifth Account', $response['data']['items'][0]['name']);
            $this->assertEquals('First Account', $response['data']['items'][1]['name']);

            // Get second page
            $data['pagination']['page'] = 2;
            $response = $this->createApiCallWithRelativeUrl('search/filter/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('Second Account', $response['data']['items'][0]['name']);

            // Check with invalid search options
            // Invalid modelClassName
            $data['search']['modelClassName'] = 'InvalidClassName';
            $response = $this->createApiCallWithRelativeUrl('search/filter/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('InvalidClassName class does not exist.', $response['message']);

            $data['search']['modelClassName'] = 'AccountsModule';
            $response = $this->createApiCallWithRelativeUrl('search/filter/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('AccountsModule should be subclass of RedBeanModel.', $response['message']);
        }

        public function testCreateWithRelations()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $account2 = AccountTestHelper::createAccountByNameForOwner('Faber', $super);
            $contact = ContactTestHelper::createContactByNameForOwner('Simon', $super);

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $data['name'] = 'Zurmo';
            $data['modelRelations'] = array(
                'accounts' => array(
                    array(
                        'action' => 'add',
                        'modelId' => $account2->id,
                        'modelClassName' => 'Account'
                    ),
                ),
                'contacts' => array(
                    array(
                        'action' => 'add',
                        'modelId' => $contact->id,
                        'modelClassName' => 'Contact'
                    ),
                ),
            );
            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            RedBeanModel::forgetAll();
            $account = Account::getById($response['data']['id']);
            $this->assertEquals(1, count($account->accounts));
            $this->assertEquals($account2->id, $account->accounts[0]->id);
            $this->assertEquals(1, count($account->contacts));
            $this->assertEquals($contact->id, $account->contacts[0]->id);

            $account2 = Account::getById($account2->id);
            $this->assertEquals($account->id, $account2->account->id);

            $contact = Contact::getById($contact->id);
            $this->assertEquals($account->id, $contact->account->id);
        }

        /**
        * @depends testCreateWithRelations
        */
        public function testUpdateWithRelations()
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

            $account  = AccountTestHelper::createAccountByNameForOwner('Factor X', $super);
            $account1 = AccountTestHelper::createAccountByNameForOwner('Miko', $super);
            $account2 = AccountTestHelper::createAccountByNameForOwner('Troter', $super);
            $contact  = ContactTestHelper::createContactByNameForOwner('Simon', $super);

            $compareData  = $this->getModelToApiDataUtilData($account);
            $account->forget();

            $data['modelRelations'] = array(
                'accounts' => array(
                    array(
                        'action' => 'add',
                        'modelId' => $account1->id,
                        'modelClassName' => 'Account'
                    ),
                    array(
                        'action' => 'add',
                        'modelId' => $account2->id,
                        'modelClassName' => 'Account'
                    ),
                ),
                'contacts' => array(
                    array(
                        'action' => 'add',
                        'modelId' => $contact->id,
                        'modelClassName' => 'Contact'
                    ),
                ),
            );
            $data['name'] = 'Zurmo Inc.';

            $response = $this->createApiCallWithRelativeUrl('update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            unset($response['data']['modifiedDateTime']);
            unset($compareData['modifiedDateTime']);
            $compareData['name'] = 'Zurmo Inc.';
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);

            RedBeanModel::forgetAll();
            $account = Account::getById($compareData['id']);
            $this->assertEquals(2, count($account->accounts));
            $this->assertEquals($account1->id, $account->accounts[0]->id);
            $this->assertEquals($account2->id, $account->accounts[1]->id);
            $this->assertEquals(1, count($account->contacts));
            $this->assertEquals($contact->id, $account->contacts[0]->id);

            $account1 = Account::getById($account1->id);
            $this->assertEquals($account->id, $account1->account->id);
            $account2 = Account::getById($account2->id);
            $this->assertEquals($account->id, $account2->account->id);
            $contact = Contact::getById($contact->id);
            $this->assertEquals($account->id, $contact->account->id);

            // Now test remove relations
            $data['modelRelations'] = array(
                'accounts' => array(
                    array(
                        'action' => 'remove',
                        'modelId' => $account1->id,
                        'modelClassName' => 'Account'
                    ),
                    array(
                        'action' => 'remove',
                        'modelId' => $account2->id,
                        'modelClassName' => 'Account'
                    ),
                ),
                'contacts' => array(
                    array(
                        'action' => 'remove',
                        'modelId' => $contact->id,
                        'modelClassName' => 'Contact'
                    ),
                ),
            );

            $response = $this->createApiCallWithRelativeUrl('update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            RedBeanModel::forgetAll();
            $updatedModel = Account::getById($compareData['id']);
            $this->assertEquals(0, count($updatedModel->accounts));
            $this->assertEquals(0, count($updatedModel->contacts));

            $account1 = Account::getById($account1->id);
            $this->assertLessThanOrEqual(0, $account1->account->id);
            $account2 = Account::getById($account2->id);
            $this->assertLessThanOrEqual(0, $account2->account->id);
            $contact = Contact::getById($contact->id);
            $this->assertLessThanOrEqual(0, $contact->account->id);
        }

        public function testEditAccountWithIncompleteData()
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

            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('New Account', 'Customer', 'Automotive', $super);

            // Provide data without required field
            $data['officePhone']         = "6438238";
            $data['officeFax']           = "6565465436";

            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);

            $accounts = Account::getByName('New Account');
            $this->assertEquals(1, count($accounts));
            $id = $accounts[0]->id;
            $data = array();
            $data['name']                = '';
            $response = $this->createApiCallWithRelativeUrl('update/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));
        }

        public function testEditAccountWIthIncorrectDataType()
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

            AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Newest Account', 'Customer', 'Automotive', $super);

            // Provide data with wrong type.
            $data['name']         = "AAA";
            $data['employees']           = "SS";

            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));

            $accounts = Account::getByName('Newest Account');
            $this->assertEquals(1, count($accounts));
            $id = $accounts[0]->id;
            $data = array();
            $data['employees']                = 'DD';
            $response = $this->createApiCallWithRelativeUrl('update/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));
        }

        public function testNotAllowedGuestAction()
        {
            $authenticationData = $this->login('st', 'st');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $response = $this->createApiCallWithRelativeUrl('read/1', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('Sign in required.', $response['message']);
        }

        public function testCreateWithScenario()
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
            $data['name'] = 'ABCD';
            $data['createdDateTime']     = '2014-06-12 15:22:41';
            $data['modifiedDateTime']    = '2014-06-12 15:28:41';
            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('It is not allowed to set read only attribute: createdDateTime.', $response['message']);

            // Now try to use some invalid scenario name
            $data['modelScenario']       = 'dummyScenarioName';

            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('It is not allowed to set read only attribute: createdDateTime.', $response['message']);

            // Now use scenario name that allow us to set all values
            $data['modelScenario']       = 'importModel';
            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            RedBeanModel::forgetAll();
            $account = Account::getById($response['data']['id']);
            $this->assertEquals($account->name, $response['data']['name']);
            $this->assertEquals($account->createdDateTime, $response['data']['createdDateTime']);
        }

        public function testUpdateWithScenario()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $account  = AccountTestHelper::createAccountByNameForOwner('Factor X', $super);
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $data['createdDateTime']     = '2014-06-12 14:26:41';
            $data['createdDateTime']     = '2014-06-12 14:28:41';
            $response = $this->createApiCallWithRelativeUrl('update/' . $account->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('It is not allowed to set read only attribute: createdDateTime.', $response['message']);

            // Now try to use some invalid scenario name
            $data['modelScenario']       = 'dummyScenarioName';

            $response = $this->createApiCallWithRelativeUrl('update/' . $account->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('It is not allowed to set read only attribute: createdDateTime.', $response['message']);

            // For update, it is not allowed to set created and modified date time
            $data['modelScenario']       = 'importModel';
            $response = $this->createApiCallWithRelativeUrl('update/' . $account->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
        }

        protected function getApiControllerClassName()
        {
            Yii::import('application.modules.accounts.controllers.AccountApiController', true);
            return 'AccountsAccountApiController';
        }

        protected function getModuleBaseApiUrl()
        {
            return 'accounts/account/api/';
        }
    }
?>