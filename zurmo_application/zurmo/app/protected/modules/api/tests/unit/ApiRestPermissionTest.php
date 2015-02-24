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
    * Test Permission related API functions.
    */
    class ApiRestPermissionTest extends ApiRestTest
    {
        public function testListDefault()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            ZurmoConfigurationUtil::setByUserAndModuleName($super, 'ZurmoModule', 'defaultPermissionSetting', null);
            ZurmoConfigurationUtil::setByUserAndModuleName($super, 'ZurmoModule', 'defaultPermissionGroupSetting', null);
            $this->assertNull(UserConfigurationFormAdapter::resolveAndGetValue($super, 'defaultPermissionGroupSetting',
                                                                                                                false));
            $this->assertEquals(UserConfigurationFormAdapter::resolveAndGetDefaultPermissionSetting($super),
                                                            UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_EVERYONE);

            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $response   = $this->listDefaultPermissionsForCurrentUser($headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertArrayHasKey('data', $response);
            $this->assertCount(2, $response['data']);
            $this->assertArrayHasKey('owner', $response['data']);
            $this->assertCount(1, $response['data']['owner']);
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
         * @depends testListDefault
         */
        public function testListDefaultAfterChange()
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

            // change default permissions to a specific group, doesn't matter if it does not exist.
            $form = UserConfigurationFormAdapter::makeFormFromUserConfigurationByUser($super);
            $form->defaultPermissionSetting      = UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER_AND_USERS_IN_GROUP;
            $form->defaultPermissionGroupSetting = 6;
            UserConfigurationFormAdapter::setConfigurationFromForm($form, $super);
            unset($form);
            // validate that settings were saved.
            $this->assertEquals(UserConfigurationFormAdapter::resolveAndGetDefaultPermissionSetting($super),
                                        UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER_AND_USERS_IN_GROUP);
            $this->assertEquals(UserConfigurationFormAdapter::resolveAndGetValue($super,
                                                                            'defaultPermissionGroupSetting', false), 6);

            $response   = $this->listDefaultPermissionsForCurrentUser($headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertArrayHasKey('data', $response);
            $this->assertCount(2, $response['data']);
            $this->assertArrayHasKey('owner', $response['data']);
            $this->assertCount(1, $response['data']['owner']);
            $this->assertArrayHasKey('id', $response['data']['owner']);
            $this->assertEquals($super->id, $response['data']['owner']['id']);

            $this->assertArrayHasKey('explicitReadWriteModelPermissions', $response['data']);
            $this->assertCount(2, $response['data']['explicitReadWriteModelPermissions']);
            $this->assertArrayHasKey('type', $response['data']['explicitReadWriteModelPermissions']);
            $this->assertEquals(2, $response['data']['explicitReadWriteModelPermissions']['type']);
            $this->assertArrayHasKey('nonEveryoneGroup', $response['data']['explicitReadWriteModelPermissions']);
            $this->assertEquals(6, $response['data']['explicitReadWriteModelPermissions']['nonEveryoneGroup']);

            // change default permissions to owner only.
            $form = UserConfigurationFormAdapter::makeFormFromUserConfigurationByUser($super);
            $form->defaultPermissionSetting         = UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER;
            UserConfigurationFormAdapter::setConfigurationFromForm($form, $super);
            unset($form);
            // validate that settings were saved.
            $this->assertEquals(UserConfigurationFormAdapter::resolveAndGetDefaultPermissionSetting($super),
                                                            UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_OWNER);
            $this->assertNull(UserConfigurationFormAdapter::resolveAndGetValue($super, 'defaultPermissionGroupSetting',
                                                                                                                false));

            $response   = $this->listDefaultPermissionsForCurrentUser($headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertArrayHasKey('data', $response);
            $this->assertCount(2, $response['data']);
            $this->assertArrayHasKey('owner', $response['data']);
            $this->assertCount(1, $response['data']['owner']);
            $this->assertArrayHasKey('id', $response['data']['owner']);
            $this->assertEquals($super->id, $response['data']['owner']['id']);

            $this->assertArrayHasKey('explicitReadWriteModelPermissions', $response['data']);
            $this->assertCount(2, $response['data']['explicitReadWriteModelPermissions']);
            $this->assertArrayHasKey('type', $response['data']['explicitReadWriteModelPermissions']);
            $this->assertEquals('', $response['data']['explicitReadWriteModelPermissions']['type']);
            $this->assertArrayHasKey('nonEveryoneGroup', $response['data']['explicitReadWriteModelPermissions']);
            $this->assertEquals('', $response['data']['explicitReadWriteModelPermissions']['nonEveryoneGroup']);
        }

        protected function listDefaultPermissionsForCurrentUser($headers)
        {
            $response = $this->createApiCallWithRelativeUrl('listDefault/', 'GET', $headers);
            return $response;
        }

        protected function getModuleBaseApiUrl()
        {
            return 'zurmo/permission/api/';
        }

        protected function getApiControllerClassName()
        {
        }
    }
?>