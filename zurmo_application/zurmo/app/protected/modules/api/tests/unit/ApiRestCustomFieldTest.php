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
    * Test CustomField related API functions.
    */
    class ApiRestCustomFieldTest extends ApiRestTest
    {
        public function testListCustomFieldData()
        {
            Yii::app()->user->userModel        = User::getByUsername('super');
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

            $customFieldDataItems = CustomFieldData::getAll();
            $compareData = array();
            foreach ($customFieldDataItems as $customFieldDataItem)
            {
                $dataAndLabels    = CustomFieldDataUtil::
                    getDataIndexedByDataAndTranslatedLabelsByLanguage($customFieldDataItem, 'en');
                $compareData[$customFieldDataItem->name] = $dataAndLabels;
            }

            //Test List
            $response = $this->createApiCallWithRelativeUrl('list/', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);
        }

        public function testGetCustomFieldData()
        {
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            //Fill some data
            $values = array(
                'Prospect',
                'Customer',
                'Vendor',
            );
            $typeFieldData = CustomFieldData::getByName('AccountTypes');
            $typeFieldData->serializedData = serialize($values);
            $this->assertTrue($typeFieldData->save());

            CustomFieldData::forgetAll();
            $customFieldData = CustomFieldData::getByName('AccountTypes');
            $compareData    = CustomFieldDataUtil::
                getDataIndexedByDataAndTranslatedLabelsByLanguage($customFieldData, 'en');

            $response = $this->createApiCallWithRelativeUrl('read/AccountTypes', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);
        }

        public function testAddValuesToCustomFieldData()
        {
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $typeFieldData = CustomFieldData::getByName('AccountTypes');
            $compareData    = CustomFieldDataUtil::
                getDataIndexedByDataAndTranslatedLabelsByLanguage($typeFieldData, 'en');

            $data = array(
                'values' => array('Unknown', 'None', array('Not Alowed', '22'), 'None'),
            );
            $response = $this->createApiCallWithRelativeUrl('addValues/' . $typeFieldData->name, 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $compareData['Unknown'] = 'Unknown';
            $compareData['None']    = 'None';
            $this->assertEquals($compareData, $response['data']);

            // Test get custom field
            CustomFieldData::forgetAll();
            $response = $this->createApiCallWithRelativeUrl('read/AccountTypes', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);

            // Test with user that not belong to super administrator group
            $notAllowedUser = UserTestHelper::createBasicUser('Steven');
            $notAllowedUser->setRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API);
            $this->assertTrue($notAllowedUser->save());

            // Test with unprivileged user that do not belong to super administrators group
            $authenticationData = $this->login('steven', 'steven');
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );

            $data = array(
                'values' => array('Unknown2', 'None2'),
            );
            $response = $this->createApiCallWithRelativeUrl('addValues/AccountTypes', 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
            $this->assertEquals('You do not have rights to perform this action.', $response['message']);
        }

        protected function getApiControllerClassName()
        {
            Yii::import('application.modules.zurmo.controllers.CustomFieldApiController', true);
            return 'ZurmoCustomFieldApiController';
        }

        protected function getModuleBaseApiUrl()
        {
            return 'zurmo/customField/api/';
        }
    }
?>