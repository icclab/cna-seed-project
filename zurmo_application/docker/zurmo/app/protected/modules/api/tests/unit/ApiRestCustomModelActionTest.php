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
    * Test custom model action API functions.
    */
    class ApiRestCustomModelActionTest extends ApiRestTest
    {
        public static function getDependentTestModelClassNames()
        {
            return array('ApiTestModelItem2');
        }

        public function testCustomGetAction()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel        = $super;
            $apiTestModelItem2Temp = new ApiTestModelItem2();
            $apiTestModelItem2Temp->name = 'first';
            $saved = $apiTestModelItem2Temp->save();
            $this->assertTrue($saved);

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $testModels = ApiTestModelItem2::getByName('first');
            $this->assertEquals(1, count($testModels));
            $compareData  = $this->getModelToApiDataUtilData($testModels[0]);

            $response = $this->createApiCallWithRelativeUrl('customGet/?id=' . $compareData['id'], 'GET', $headers);
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
         * @depends testCustomGetAction
         */
        public function testCustomDeleteAction()
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

            $testModels = ApiTestModelItem2::getByName('first');
            $this->assertEquals(1, count($testModels));

            //Test Delete
            $response = $this->createApiCallWithRelativeUrl('customDelete/?id=' . $testModels[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = $this->createApiCallWithRelativeUrl('customGet/?id=' . $testModels[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
        }

        /**
         * @depends testCustomGetAction
         */
        public function testCustomPostAction()
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

            //Test Create
            $data['name'] = 'new name with no permissions';
            $response = $this->createApiCallWithRelativeUrl('customPost/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertTrue(is_int($response['data']['id']));
            $this->assertGreaterThan(0, $response['data']['id']);
            $modelId     = $response['data']['id'];

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
            $data['modelItem'] = null;
            // unset explicit permissions, we won't use these in comparison.
            unset($response['data']['explicitReadWriteModelPermissions']);
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['id']);
            ksort($data);
            ksort($response['data']);
            $this->assertEquals($data, $response['data']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $modelId, 'GET', $headers);
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
         * @depends testCustomPostAction
         */
        public function testCustomPostActionWithSpecificOwner()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel        = $super;
            $billy  = User::getByUsername('billy');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            //Test Create
            $data['name'] = 'new name with just owner';
            $data['owner'] = array('id' => $billy->id);

            $response = $this->createApiCallWithRelativeUrl('customPost/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertTrue(is_int($response['data']['id']));
            $this->assertGreaterThan(0, $response['data']['id']);
            $modelId     = $response['data']['id'];

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
            $data['modelItem'] = null;
            // unset explicit permissions, we won't use these in comparison.
            unset($response['data']['explicitReadWriteModelPermissions']);
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['id']);
            ksort($data);
            ksort($response['data']);
            $this->assertEquals($data, $response['data']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $modelId, 'GET', $headers);
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
         * @depends testCustomPostAction
         */
        public function testCustomPostActionWithSpecificExplicitPermissions()
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

            //Test Create
            $data['name'] = 'new name with owner only';
            // TODO: @Shoaibi/@Ivica: null does not work, empty works. null doesn't send it.
            $data['explicitReadWriteModelPermissions'] = array('nonEveryoneGroup' => '', 'type' => '');

            $response = $this->createApiCallWithRelativeUrl('customPost/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertTrue(is_int($response['data']['id']));
            $this->assertGreaterThan(0, $response['data']['id']);
            $modelId     = $response['data']['id'];

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
            $data['modelItem'] = null;
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['id']);
            ksort($data);
            ksort($response['data']);
            $this->assertEquals($data, $response['data']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $modelId, 'GET', $headers);
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
        * @depends testCustomPostActionWithSpecificOwner
        */
        public function testCustomUpdateAction()
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

            $testModels = ApiTestModelItem2::getByName('new name with just owner');
            $this->assertEquals(1, count($testModels));
            $compareData  = $this->getModelToApiDataUtilData($testModels[0]);
            $data['name'] = 'new name 2 with just owner';
            $compareData['name'] = 'new name 2 with just owner';
            $group  = static::$randomNonEveryoneNonAdministratorsGroup;
            $explicitReadWriteModelPermissions = array('type' => 2, 'nonEveryoneGroup' => $group->id);
            $data['explicitReadWriteModelPermissions']    = $explicitReadWriteModelPermissions;
            $compareData['explicitReadWriteModelPermissions']   = $explicitReadWriteModelPermissions;
            $testModels[0]->forget();

            $response = $this->createApiCallWithRelativeUrl('customUpdate/?id=' . $compareData['id'], 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            unset($response['data']['modifiedDateTime']);
            unset($compareData['modifiedDateTime']);
            ksort($compareData);
            ksort($response['data']);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);

            $response = $this->createApiCallWithRelativeUrl('customGet/?id=' . $compareData['id'], 'GET', $headers);
            $response = json_decode($response, true);
            unset($response['data']['modifiedDateTime']);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals('new name 2 with just owner', $response['data']['name']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
        * @depends testCustomUpdateAction
        */
        public function testCustomListAction()
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

            $testModels = ApiTestModelItem2::getByName('new name 2 with just owner');
            $this->assertEquals(1, count($testModels));
            $compareData  = $this->getModelToApiDataUtilData($testModels[0]);

            $response = $this->createApiCallWithRelativeUrl('customList/', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals($compareData, $response['data']['items'][1]);
        }

        public function testListCustomModelAttributes()
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
            $allAttributes      = ApiRestTestHelper::getModelAttributes(new ApiTestModelItem2());

            $response = $this->createApiCallWithRelativeUrl('listAttributes/' , 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($allAttributes, $response['data']['items']);
        }

        protected function getApiControllerClassName()
        {
            Yii::import('application.modules.api.controllers.TestModelItem2ApiController', true);
            return 'ApiTestModelItem2ApiController';
        }

        protected function getModuleBaseApiUrl()
        {
            return 'api/testModelItem2/api/';
        }
    }
?>