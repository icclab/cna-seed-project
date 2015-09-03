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
    * Test Task related API functions.
    */
    class ApiRestTaskTest extends ApiRestTest
    {
        public function testGetTask()
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
            $task = TaskTestHelper::createTaskByNameForOwner('First Task', $super);
            $compareData        = $this->getModelToApiDataUtilData($task);

            $response = $this->createApiCallWithRelativeUrl('read/' . $task->id, 'GET', $headers);
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
         * @depends testGetTask
         */
        public function testDeleteTask()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $tasks = Task::getByName('First Task');
            $this->assertEquals(1, count($tasks));

            $response = $this->createApiCallWithRelativeUrl('delete/' . $tasks[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $tasks[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('The ID specified was invalid.', $response['message']);
        }

        public function testCreateTask()
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

            $dueStamp             = DateTimeUtil::convertTimestampToDbFormatDateTime(time()  + 10000);
            $completedStamp       = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 11000);

            $data['name']              = "Check bills with no permissions";
            $data['dueDateTime']       = $dueStamp;
            $data['completedDateTime'] = $completedStamp;
            $data['completed']         = 0;
            $data['description']       = "Task description";
            $data['status']            = Task::STATUS_IN_PROGRESS;

            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertArrayHasKey('id', $response['data']);
            $taskId     = $response['data']['id'];

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
            $data['project'] = null;
            $data['requestedByUser']  = array(
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
            unset($response['data']['latestDateTime']);
            unset($response['data']['id']);

            //$data['latestDateTime'] = $dueStamp;

            ksort($data);
            ksort($response['data']);
            $this->assertEquals($data, $response['data']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $taskId, 'GET', $headers);
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
         * @depends testCreateTask
         */
        public function testCreateTaskWithSpecificOwner()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $billy  = User::getByUsername('billy');
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $dueStamp                   = DateTimeUtil::convertTimestampToDbFormatDateTime(time()  + 10000);
            $completedStamp             = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 11000);

            $data['name']               = "Check bills with just owner";
            $data['dueDateTime']        = $dueStamp;
            $data['completedDateTime']  = $completedStamp;
            $data['completed']          = 0;
            $data['description']        = "Task description";
            $data['owner']['id']        = $billy->id;

            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertArrayHasKey('id', $response['data']);
            $taskId     = $response['data']['id'];

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
            $data['project'] = null;
            $data['requestedByUser']  = array(
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
            $data['status'] = Task::STATUS_NEW;

            // unset explicit permissions, we won't use these in comparison.
            unset($response['data']['explicitReadWriteModelPermissions']);
            // We need to unset some empty values from response.
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['latestDateTime']);
            unset($response['data']['id']);

            //$data['latestDateTime'] = $dueStamp;

            ksort($data);
            ksort($response['data']);
            $this->assertEquals($data, $response['data']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $taskId, 'GET', $headers);
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
         * @depends testCreateTask
         */
        public function testCreateTaskWithSpecificExplicitPermissions()
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

            $dueStamp             = DateTimeUtil::convertTimestampToDbFormatDateTime(time()  + 10000);
            $completedStamp       = DateTimeUtil::convertTimestampToDbFormatDateTime(time() + 11000);

            $data['name']               = "Check bills with owner only permissions";
            $data['dueDateTime']        = $dueStamp;
            $data['completedDateTime']  = $completedStamp;
            $data['completed']          = 0;
            $data['description']        = "Task description";
            // TODO: @Shoaibi/@Ivica: null does not work, empty works. null doesn't send it.
            $data['explicitReadWriteModelPermissions'] = array('nonEveryoneGroup' => '', 'type' => '');

            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertArrayHasKey('id', $response['data']);
            $taskId     = $response['data']['id'];

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
            $data['project'] = null;
            $data['requestedByUser']  = array(
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
            $data['status'] = Task::STATUS_NEW;

            // We need to unset some empty values from response.
            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['latestDateTime']);
            unset($response['data']['id']);
            ksort($data);
            ksort($response['data']);
            $this->assertEquals($data, $response['data']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $taskId, 'GET', $headers);
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
         * @depends testCreateTaskWithSpecificOwner
         */
        public function testUpdateTask()
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

            $tasks = Task::getByName('Check bills with just owner');
            $this->assertEquals(1, count($tasks));
            $compareData        = $this->getModelToApiDataUtilData($tasks[0]);

            $group  = static::$randomNonEveryoneNonAdministratorsGroup;
            $explicitReadWriteModelPermissions = array('type' => 2, 'nonEveryoneGroup' => $group->id);
            $updateData['completed']         = 1;
            $compareData['completed']       = "1";
            $updateData['explicitReadWriteModelPermissions']    = $explicitReadWriteModelPermissions;
            $compareData['explicitReadWriteModelPermissions']   = $explicitReadWriteModelPermissions;

            $response = $this->createApiCallWithRelativeUrl('update/' . $compareData['id'], 'PUT', $headers,
                                                                                        array('data' => $updateData));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            // We need to unset some empty values from response and dates.
            unset($response['data']['modifiedDateTime']);
            unset($compareData['modifiedDateTime']);
            unset($response['data']['latestDateTime']);
            unset($compareData['latestDateTime']);
            ksort($compareData);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $compareData['id'], 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['latestDateTime']);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
         * @depends testUpdateTask
         */
        public function testListTasks()
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

            $tasks = Task::getByName('Check bills with owner only permissions');
            $this->assertEquals(1, count($tasks));
            $compareData        = $this->getModelToApiDataUtilData($tasks[0]);

            $response = $this->createApiCallWithRelativeUrl('list/' , 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals($compareData, $response['data']['items'][2]);
        }

        public function testListTaskAttributes()
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
            $allAttributes      = ApiRestTestHelper::getModelAttributes(new Task());

            $response = $this->createApiCallWithRelativeUrl('listAttributes/' , 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($allAttributes, $response['data']['items']);
        }

        /**
        * @depends testListTasks
        */
        public function testUnprivilegedUserViewUpdateDeleteTasks()
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

            $tasks = Task::getByName('Check bills with owner only permissions');
            $this->assertEquals(1, count($tasks));
            $data['completed']         = 1;

            // Test with unprivileged user to view, edit and delete account.
            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $response = $this->createApiCallWithRelativeUrl('read/' . $tasks[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $tasks[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $tasks[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);

            //now check if user have rights, but no permissions.
            $notAllowedUser->setRight('TasksModule', TasksModule::getAccessRight());
            $notAllowedUser->setRight('TasksModule', TasksModule::getCreateRight());
            $notAllowedUser->setRight('TasksModule', TasksModule::getDeleteRight());
            $saved = $notAllowedUser->save();
            $this->assertTrue($saved);

            $response = $this->createApiCallWithRelativeUrl('read/' . $tasks[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            $response = $this->createApiCallWithRelativeUrl('update/' . $tasks[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            $response = $this->createApiCallWithRelativeUrl('delete/' . $tasks[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have permissions for this action.', $response['message']);

            // Allow everyone group to read/write task
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
            $response = $this->createApiCallWithRelativeUrl('update/' . $tasks[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $response = $this->createApiCallWithRelativeUrl('read/' . $tasks[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            unset($data);
            $data['completed']         = 1;
            $response = $this->createApiCallWithRelativeUrl('update/' . $tasks[0]->id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, $response['data']['completed']);

            // Test with privileged user
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            //Test Delete
            $response = $this->createApiCallWithRelativeUrl('delete/' . $tasks[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $tasks[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
        }

        /**
        * @depends testUnprivilegedUserViewUpdateDeleteTasks
        */
        public function testBasicSearchTasks()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            Task::deleteAll();
            $anotherUser = User::getByUsername('steven');

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $firstAccount = AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('First Account', 'Customer', 'Automotive', $super);
            $secondAccount = AccountTestHelper::createAccountByNameTypeAndIndustryForOwner('Second Account', 'Customer', 'Automotive', $super);

            TaskTestHelper::createTaskWithOwnerAndRelatedAccount('First Task', $super, $firstAccount);
            TaskTestHelper::createTaskWithOwnerAndRelatedAccount('Second Task', $super, $firstAccount);
            TaskTestHelper::createTaskWithOwnerAndRelatedAccount('Third Task', $super, $secondAccount);
            TaskTestHelper::createTaskWithOwnerAndRelatedAccount('Forth Task', $anotherUser, $secondAccount);
            TaskTestHelper::createTaskWithOwnerAndRelatedAccount('Fifth Task', $super, $firstAccount);

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
            $this->assertEquals('Fifth Task', $response['data']['items'][0]['name']);
            $this->assertEquals('First Task', $response['data']['items'][1]['name']);
            $this->assertEquals('Forth Task', $response['data']['items'][2]['name']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('Second Task', $response['data']['items'][0]['name']);
            $this->assertEquals('Third Task', $response['data']['items'][1]['name']);

            // Search by name
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['name'] = 'First Task';
            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('First Task', $response['data']['items'][0]['name']);

            // No results
            $searchParams['pagination']['page'] = 1;
            $searchParams['search']['name'] = 'First Task 2';
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
            $this->assertEquals('Third Task', $response['data']['items'][0]['name']);
            $this->assertEquals('Second Task', $response['data']['items'][1]['name']);
            $this->assertEquals('Forth Task', $response['data']['items'][2]['name']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(2, count($response['data']['items']));
            $this->assertEquals(5, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('First Task', $response['data']['items'][0]['name']);
            $this->assertEquals('Fifth Task', $response['data']['items'][1]['name']);

            // Search by owner, order by name desc
            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'owner'   => array( 'id' => $super->id),
                ),
                'sort' => 'name.desc',
            );

            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(4, $response['data']['totalCount']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Third Task', $response['data']['items'][0]['name']);
            $this->assertEquals('Second Task', $response['data']['items'][1]['name']);
            $this->assertEquals('First Task', $response['data']['items'][2]['name']);

            // Second page
            $searchParams['pagination']['page'] = 2;
            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(4, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('Fifth Task', $response['data']['items'][0]['name']);

            // Search by account, order by name desc
            $searchParams = array(
                'pagination' => array(
                    'page'     => 1,
                    'pageSize' => 3,
                ),
                'search' => array(
                    'activityItems'   => array('id' => $firstAccount->getClassId('Item')),
                ),
                'sort' => 'name.desc',
            );

            $searchParamsQuery = http_build_query($searchParams);
            $response = $this->createApiCallWithRelativeUrl('list/filter/' . $searchParamsQuery, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals('Second Task', $response['data']['items'][0]['name']);
            $this->assertEquals('First Task', $response['data']['items'][1]['name']);
            $this->assertEquals('Fifth Task', $response['data']['items'][2]['name']);
        }

        /**
        * @depends testBasicSearchTasks
        */
        public function testDynamicSearchTasks()
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
            $this->assertEquals('Fifth Task', $response['data']['items'][0]['name']);
            $this->assertEquals('First Task', $response['data']['items'][1]['name']);

            // Get second page
            $data['pagination']['page'] = 2;
            $response = $this->createApiCallWithRelativeUrl('list/filter/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('Second Task', $response['data']['items'][0]['name']);
        }

        public function testNewSearchTasks()
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
                    'modelClassName' => 'Task',
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
            $this->assertEquals('Fifth Task', $response['data']['items'][0]['name']);
            $this->assertEquals('First Task', $response['data']['items'][1]['name']);

            // Get second page
            $data['pagination']['page'] = 2;
            $response = $this->createApiCallWithRelativeUrl('search/filter/', 'POST', $headers, array('data' => $data));

            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(1, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(2, $response['data']['currentPage']);
            $this->assertEquals('Second Task', $response['data']['items'][0]['name']);
        }

        public function testEditTaskWithIncompleteData()
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

            $task = TaskTestHelper::createTaskByNameForOwner('New Task', $super);

            // Provide data without required fields.
            $data['name']         = "";

            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));

            $id = $task->id;
            $data = array();
            $data['name']                = '';
            $response = $this->createApiCallWithRelativeUrl('update/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));
        }

        public function testEditTaskWIthIncorrectDataType()
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

            $task = TaskTestHelper::createTaskByNameForOwner('Newest Task', $super);

            // Provide data with wrong type.
            $data['dueDateTime']         = "A";

            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(2, count($response['errors']));

            $id = $task->id;
            $data = array();
            $data['dueDateTime']         = "A";
            $response = $this->createApiCallWithRelativeUrl('update/' . $id, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals(1, count($response['errors']));
        }

        protected function getApiControllerClassName()
        {
            Yii::import('application.modules.tasks.controllers.TaskApiController', true);
            return 'TasksTaskApiController';
        }

        protected function getModuleBaseApiUrl()
        {
            return 'tasks/task/api/';
        }
    }
?>