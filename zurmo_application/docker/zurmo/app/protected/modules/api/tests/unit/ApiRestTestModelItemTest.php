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
    * Test ApiTestModelItem related API functions.
    */
    class ApiRestTestModelItemTest extends ApiRestTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $multiSelectValues = array(
                'Multi 1',
                'Multi 2',
                'Multi 3',
            );
            $customFieldData = CustomFieldData::getByName('ApiTestMultiDropDown');
            $customFieldData->serializedData = serialize($multiSelectValues);
            $save = $customFieldData->save();
            assert('$save'); // Not Coding Standard

            $tagCloudValues = array(
                'Cloud 1',
                'Cloud 2',
                'Cloud 3',
            );
            $customFieldData = CustomFieldData::getByName('ApiTestTagCloud');
            $customFieldData->serializedData = serialize($tagCloudValues);
            $save = $customFieldData->save();
            assert('$save'); // Not Coding Standard
        }

        public static function getDependentTestModelClassNames()
        {
            return array('ApiTestModelItem', 'ApiTestModelItem2', 'ApiTestModelItem3', 'ApiTestModelItem4');
        }

        public function testCreate()
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

            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);

            $testItem2 = new ApiTestModelItem2();
            $testItem2->name     = 'John1';
            $this->assertTrue($testItem2->save());

            $testItem4 = new ApiTestModelItem4();
            $testItem4->name     = 'John1';
            $this->assertTrue($testItem4->save());

            //HAS_MANY and MANY_MANY relationships should be ignored.
            $testItem3_1 = new ApiTestModelItem3();
            $testItem3_1->name     = 'Kevin1';
            $this->assertTrue($testItem3_1->save());

            $testItem3_2 = new ApiTestModelItem3();
            $testItem3_2->name     = 'Jim1';
            $this->assertTrue($testItem3_2->save());

            $testItemRelated = new ApiTestModelItem();
            $testItemRelated->lastName     = 'AAAA1';
            $testItemRelated->string        = 'some string1';
            $this->assertTrue($testItemRelated->save());

            $testItem = new ApiTestModelItem();

            $testItem->firstName     = 'Bob5 with no permissions';
            $testItem->lastName      = 'Bob51';
            $testItem->boolean       = true;
            $testItem->date          = '2002-04-03';
            $testItem->dateTime      = '2002-04-03 02:00:43';
            $testItem->float         = 54.22;
            $testItem->integer       = 10;
            $testItem->phone         = '21313213';
            $testItem->string        = 'aString1';
            $testItem->textArea      = 'Some Text Area1';
            $testItem->url           = 'http://www.asite.com';
            $testItem->currencyValue = $currencyValue;
            $testItem->modelItem2    = $testItem2;
            $testItem->modelItems3->add($testItem3_1);
            $testItem->modelItems3->add($testItem3_2);
            $testItem->modelItems4->add($testItem4);
            $testItem->modelItems->add($testItemRelated);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 1';
            $testItem->multiDropDown->values->add($customFieldValue);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 3';
            $testItem->multiDropDown->values->add($customFieldValue);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 2';
            $testItem->tagCloud->values->add($customFieldValue);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 3';
            $testItem->tagCloud->values->add($customFieldValue);

            $this->assertTrue($testItem->save());
            $data  = $this->getModelToApiDataUtilData($testItem);
            unset($data['createdDateTime']);
            unset($data['modifiedDateTime']);
            unset($data['id']);
            unset($data['currencyValue']['id']);
            $data['owner'] = array(
                 'id' => $super->id,
            );

            $compareData = $data;
            unset($data['createdByUser']);
            unset($data['modifiedByUser']);

            $this->assertTrue($testItemRelated->delete());
            $testItem->delete();
            $testItem->forget();
            unset($testItem);

            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
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

            // unset explicit permissions, we won't use these in comparison.
            // compareData's version of permissions came from manual creation, would be none.
            // response's one came from api, would be default
            unset($response['data']['explicitReadWriteModelPermissions']);
            unset($compareData['explicitReadWriteModelPermissions']);

            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['id']);
            unset($response['data']['owner']['username']);
            unset($compareData['id']);
            unset($response['data']['currencyValue']['id']);
            unset($compareData['currencyValue']['id']);
            unset($compareData['createdDateTime']);
            unset($compareData['modifiedDateTime']);
            ksort($compareData);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);

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
         * @depends testCreate
         */
        public function testCreateWithSpecificOwner()
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

            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);

            $testItem2 = new ApiTestModelItem2();
            $testItem2->name     = 'John2';
            $this->assertTrue($testItem2->save());

            $testItem4 = new ApiTestModelItem4();
            $testItem4->name     = 'John2';
            $this->assertTrue($testItem4->save());

            //HAS_MANY and MANY_MANY relationships should be ignored.
            $testItem3_1 = new ApiTestModelItem3();
            $testItem3_1->name     = 'Kevin2';
            $this->assertTrue($testItem3_1->save());

            $testItem3_2 = new ApiTestModelItem3();
            $testItem3_2->name     = 'Jim2';
            $this->assertTrue($testItem3_2->save());

            $testItemRelated = new ApiTestModelItem();
            $testItemRelated->lastName     = 'AAAA2';
            $testItemRelated->string        = 'some string2';
            $this->assertTrue($testItemRelated->save());

            $testItem = new ApiTestModelItem();

            $testItem->firstName     = 'Bob5 with just owner';
            $testItem->lastName      = 'Bob52';
            $testItem->boolean       = true;
            $testItem->date          = '2002-04-03';
            $testItem->dateTime      = '2002-04-03 02:00:43';
            $testItem->float         = 54.22;
            $testItem->integer       = 10;
            $testItem->phone         = '21313213';
            $testItem->string        = 'aString2';
            $testItem->textArea      = 'Some Text Area2';
            $testItem->url           = 'http://www.asite.com';
            $testItem->currencyValue = $currencyValue;
            $testItem->modelItem2    = $testItem2;
            $testItem->modelItems3->add($testItem3_1);
            $testItem->modelItems3->add($testItem3_2);
            $testItem->modelItems4->add($testItem4);
            $testItem->modelItems->add($testItemRelated);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 1';
            $testItem->multiDropDown->values->add($customFieldValue);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 3';
            $testItem->multiDropDown->values->add($customFieldValue);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 2';
            $testItem->tagCloud->values->add($customFieldValue);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 3';
            $testItem->tagCloud->values->add($customFieldValue);

            $this->assertTrue($testItem->save());
            $data  = $this->getModelToApiDataUtilData($testItem);
            unset($data['createdDateTime']);
            unset($data['modifiedDateTime']);
            unset($data['id']);
            unset($data['currencyValue']['id']);
            $data['owner'] = array(
                'id' => $super->id,
            );

            $compareData = $data;
            unset($data['createdByUser']);
            unset($data['modifiedByUser']);

            $this->assertTrue($testItemRelated->delete());
            $testItem->delete();
            $testItem->forget();
            unset($testItem);
            $data['owner'] = array('id' => $billy->id);

            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
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

            // unset explicit permissions, we won't use these in comparison.
            // compareData's version of permissions came from manual creation, would be none.
            // response's one came from api, would be default
            unset($response['data']['explicitReadWriteModelPermissions']);
            unset($compareData['explicitReadWriteModelPermissions']);

            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['id']);
            unset($compareData['id']);
            unset($response['data']['owner']);
            unset($compareData['owner']);
            unset($response['data']['currencyValue']['id']);
            unset($compareData['currencyValue']['id']);
            unset($compareData['createdDateTime']);
            unset($compareData['modifiedDateTime']);
            ksort($compareData);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);

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
         * @depends testCreate
         */
        public function testCreateWithSpecificExplicitPermissions()
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

            $currencies                 = Currency::getAll();
            $currencyValue              = new CurrencyValue();
            $currencyValue->value       = 100;
            $currencyValue->currency    = $currencies[0];
            $this->assertEquals('USD', $currencyValue->currency->code);

            $testItem2 = new ApiTestModelItem2();
            $testItem2->name     = 'John3';
            $this->assertTrue($testItem2->save());

            $testItem4 = new ApiTestModelItem4();
            $testItem4->name     = 'John3';
            $this->assertTrue($testItem4->save());

            //HAS_MANY and MANY_MANY relationships should be ignored.
            $testItem3_1 = new ApiTestModelItem3();
            $testItem3_1->name     = 'Kevin3';
            $this->assertTrue($testItem3_1->save());

            $testItem3_2 = new ApiTestModelItem3();
            $testItem3_2->name     = 'Jim3';
            $this->assertTrue($testItem3_2->save());

            $testItemRelated = new ApiTestModelItem();
            $testItemRelated->lastName     = 'AAAA3';
            $testItemRelated->string        = 'some string3';
            $this->assertTrue($testItemRelated->save());

            $testItem = new ApiTestModelItem();

            $testItem->firstName     = 'Bob5 with owner only';
            $testItem->lastName      = 'Bob53';
            $testItem->boolean       = true;
            $testItem->date          = '2002-04-03';
            $testItem->dateTime      = '2002-04-03 02:00:43';
            $testItem->float         = 54.22;
            $testItem->integer       = 10;
            $testItem->phone         = '21313213';
            $testItem->string        = 'aString3';
            $testItem->textArea      = 'Some Text Area3';
            $testItem->url           = 'http://www.asite.com';
            $testItem->currencyValue = $currencyValue;
            $testItem->modelItem2    = $testItem2;
            $testItem->modelItems3->add($testItem3_1);
            $testItem->modelItems3->add($testItem3_2);
            $testItem->modelItems4->add($testItem4);
            $testItem->modelItems->add($testItemRelated);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 1';
            $testItem->multiDropDown->values->add($customFieldValue);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Multi 3';
            $testItem->multiDropDown->values->add($customFieldValue);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 2';
            $testItem->tagCloud->values->add($customFieldValue);

            $customFieldValue = new CustomFieldValue();
            $customFieldValue->value = 'Cloud 3';
            $testItem->tagCloud->values->add($customFieldValue);

            $this->assertTrue($testItem->save());
            $data  = $this->getModelToApiDataUtilData($testItem);
            unset($data['createdDateTime']);
            unset($data['modifiedDateTime']);
            unset($data['id']);
            unset($data['currencyValue']['id']);
            $data['owner'] = array(
                'id' => $super->id,
            );

            $compareData = $data;
            unset($data['createdByUser']);
            unset($data['modifiedByUser']);

            $this->assertTrue($testItemRelated->delete());
            $testItem->delete();
            $testItem->forget();
            unset($testItem);
            // TODO: @Shoaibi/@Ivica: null does not work, empty works. null doesn't send it.
            $data['explicitReadWriteModelPermissions'] = array('nonEveryoneGroup' => '', 'type' => '');

            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
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

            // unset explicit permissions, we won't use these in comparison.
            // compareData's version of permissions came from manual creation, would be none.
            // response's one came from api, would be default
            unset($response['data']['explicitReadWriteModelPermissions']);
            unset($compareData['explicitReadWriteModelPermissions']);

            unset($response['data']['createdDateTime']);
            unset($response['data']['modifiedDateTime']);
            unset($response['data']['id']);
            unset($response['data']['owner']['username']);
            unset($compareData['id']);
            unset($response['data']['currencyValue']['id']);
            unset($compareData['currencyValue']['id']);
            unset($compareData['createdDateTime']);
            unset($compareData['modifiedDateTime']);
            ksort($compareData);
            ksort($response['data']);
            $this->assertEquals($compareData, $response['data']);

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
         * @depends testCreate
         */
        public function testGet()
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
            $testModels = ApiTestModelItem::getByName('Bob5 with just owner');
            $this->assertEquals(1, count($testModels));
            $compareData  = $this->getModelToApiDataUtilData($testModels[0]);

            $response = $this->createApiCallWithRelativeUrl('read/' . $compareData['id'], 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
         * @depends testGet
         */
        public function testUpdate()
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

            $testModels = ApiTestModelItem::getByName('Bob5 with just owner');
            $this->assertEquals(1, count($testModels));
            $compareData  = $this->getModelToApiDataUtilData($testModels[0]);
            $testModels[0]->forget();

            $data = array('firstName' => 'Bob6 with just owner');
            $compareData['firstName'] = 'Bob6 with just owner';
            $group  = static::$randomNonEveryoneNonAdministratorsGroup;
            $explicitReadWriteModelPermissions = array('type' => 2, 'nonEveryoneGroup' => $group->id);
            $data['explicitReadWriteModelPermissions']    = $explicitReadWriteModelPermissions;
            $compareData['explicitReadWriteModelPermissions']   = $explicitReadWriteModelPermissions;
            $response = $this->createApiCallWithRelativeUrl('update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            unset($response['data']['modifiedDateTime']);
            unset($compareData['modifiedDateTime']);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $compareData['id'], 'GET', $headers);
            $response = json_decode($response, true);
            unset($response['data']['modifiedDateTime']);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);
        }

        /**
         * @depends testUpdate
         */
        public function testList()
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

            $testModels = ApiTestModelItem::getByName('Bob6 with just owner');
            $this->assertEquals(1, count($testModels));
            $compareData  = $this->getModelToApiDataUtilData($testModels[0]);

            $response = $this->createApiCallWithRelativeUrl('list/', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals(3, count($response['data']['items']));
            $this->assertEquals(3, $response['data']['totalCount']);
            $this->assertEquals(1, $response['data']['currentPage']);
            $this->assertEquals($compareData, $response['data']['items'][1]);
        }

        public function testCreateWithRelations()
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

            $testItem4 = new ApiTestModelItem4();
            $testItem4->name     = 'John6';
            $this->assertTrue($testItem4->save());

            $testItem3_1 = new ApiTestModelItem3();
            $testItem3_1->name     = 'Kevin6';
            $this->assertTrue($testItem3_1->save());

            $testItem3_2 = new ApiTestModelItem3();
            $testItem3_2->name     = 'Jim6';
            $this->assertTrue($testItem3_2->save());

            $testItemRelated = new ApiTestModelItem();
            $testItemRelated->lastName     = 'Cohens';
            $testItemRelated->string        = 'aString';
            $this->assertTrue($testItemRelated->save());

            $data['firstName'] = "Larry";
            $data['lastName']  = "Larouse";
            $data['string']    = "aString";
            $data['modelRelations'] = array(
                'modelItems3' => array(
                    array(
                        'action' => 'add',
                        'modelId' => $testItem3_1->id,
                        'modelClassName' => 'ApiTestModelItem3'
                    ),
                    array(
                        'action' => 'add',
                        'modelId' => $testItem3_2->id,
                        'modelClassName' => 'ApiTestModelItem3'
                    ),
                ),
                'modelItems4' => array(
                    array(
                        'action' => 'add',
                        'modelId' => $testItem4->id,
                        'modelClassName' => 'ApiTestModelItem4'
                    ),
                ),
                'modelItems' => array(
                    array(
                        'action' => 'add',
                        'modelId' => $testItemRelated->id,
                        'modelClassName' => 'ApiTestModelItem'
                    ),
                ),
            );

            $billy  = User::getByUsername('billy');
            $data['owner'] = array('id' => $billy->id);

            $response = $this->createApiCallWithRelativeUrl('create/', 'POST', $headers, array('data' => $data));
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

            RedBeanModel::forgetAll();
            $updatedModel = ApiTestModelItem::getById($response['data']['id']);
            $this->assertEquals(2, count($updatedModel->modelItems3));
            // We don't know order how data are pulled from database, so we compare if all expected data are in array.
            $this->assertTrue(in_array($updatedModel->modelItems3[0]->id, array($testItem3_1->id, $testItem3_2->id)));
            $this->assertTrue(in_array($updatedModel->modelItems3[1]->id, array($testItem3_1->id, $testItem3_2->id)));
            $this->assertTrue($updatedModel->modelItems3[0]->id != $updatedModel->modelItems3[1]->id);

            $this->assertEquals(1, count($updatedModel->modelItems4));
            $this->assertEquals($testItem4->id, $updatedModel->modelItems4[0]->id);

            $this->assertEquals(1, count($updatedModel->modelItems));
            $this->assertEquals($testItemRelated->id, $updatedModel->modelItems[0]->id);

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
        * @depends testUpdate
        */
        public function testUpdateWithRelations()
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

            $testModel = new ApiTestModelItem();
            $testModel->firstName = "Ruth";
            $testModel->lastName  = "Tester";
            $testModel->string    = "aString";
            $this->assertTrue($testModel->save());

            $compareData  = $this->getModelToApiDataUtilData($testModel);
            $testModel->forget();

            $testItem4 = new ApiTestModelItem4();
            $testItem4->name     = 'John7';
            $this->assertTrue($testItem4->save());

            $testItem3_1 = new ApiTestModelItem3();
            $testItem3_1->name     = 'Kevin7';
            $this->assertTrue($testItem3_1->save());

            $testItem3_2 = new ApiTestModelItem3();
            $testItem3_2->name     = 'Jim7';
            $this->assertTrue($testItem3_2->save());

            $testItemRelated = new ApiTestModelItem();
            $testItemRelated->lastName     = 'Cohens7';
            $testItemRelated->string        = 'aString';
            $this->assertTrue($testItemRelated->save());

            $data['modelRelations'] = array(
                'modelItems3' => array(
                    array(
                        'action' => 'add',
                        'modelId' => $testItem3_1->id,
                        'modelClassName' => 'ApiTestModelItem3'
                    ),
                    array(
                        'action' => 'add',
                        'modelId' => $testItem3_2->id,
                        'modelClassName' => 'ApiTestModelItem3'
                    ),
                ),
                'modelItems4' => array(
                    array(
                        'action' => 'add',
                        'modelId' => $testItem4->id,
                        'modelClassName' => 'ApiTestModelItem4'
                    ),
                ),
                'modelItems' => array(
                    array(
                        'action' => 'add',
                        'modelId' => $testItemRelated->id,
                        'modelClassName' => 'ApiTestModelItem'
                    ),
                ),
            );

            $data['firstName'] = 'Michael6';
            $group  = static::$randomNonEveryoneNonAdministratorsGroup;
            $explicitReadWriteModelPermissions = array('type' => 2, 'nonEveryoneGroup' => $group->id);
            $data['explicitReadWriteModelPermissions']    = $explicitReadWriteModelPermissions;
            $compareData['explicitReadWriteModelPermissions']   = $explicitReadWriteModelPermissions;

            $response = $this->createApiCallWithRelativeUrl('update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            unset($response['data']['modifiedDateTime']);
            unset($compareData['modifiedDateTime']);
            $compareData['firstName'] = 'Michael6';

            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            $this->assertEquals($compareData, $response['data']);

            RedBeanModel::forgetAll();
            $updatedModel = ApiTestModelItem::getById($compareData['id']);
            $this->assertEquals(2, count($updatedModel->modelItems3));
            $this->assertEquals($testItem3_1->id, $updatedModel->modelItems3[0]->id);
            $this->assertEquals($testItem3_2->id, $updatedModel->modelItems3[1]->id);

            $this->assertEquals(1, count($updatedModel->modelItems4));
            $this->assertEquals($testItem4->id, $updatedModel->modelItems4[0]->id);

            $this->assertEquals(1, count($updatedModel->modelItems));
            $this->assertEquals($testItemRelated->id, $updatedModel->modelItems[0]->id);

            // Now test remove relations
            $data['modelRelations'] = array(
                'modelItems3' => array(
                    array(
                        'action' => 'remove',
                        'modelId' => $testItem3_1->id,
                        'modelClassName' => 'ApiTestModelItem3'
                    ),
                    array(
                        'action' => 'remove',
                        'modelId' => $testItem3_2->id,
                        'modelClassName' => 'ApiTestModelItem3'
                    ),
                ),
                'modelItems4' => array(
                    array(
                        'action' => 'remove',
                        'modelId' => $testItem4->id,
                        'modelClassName' => 'ApiTestModelItem4'
                    ),
                ),
                'modelItems' => array(
                    array(
                        'action' => 'remove',
                        'modelId' => $testItemRelated->id,
                        'modelClassName' => 'ApiTestModelItem'
                    ),
                ),
            );
            $response = $this->createApiCallWithRelativeUrl('update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
            RedBeanModel::forgetAll();
            $updatedModel = ApiTestModelItem::getById($compareData['id']);
            $this->assertEquals(0, count($updatedModel->modelItems3));
            $this->assertEquals(0, count($updatedModel->modelItems4));
            $this->assertEquals(0, count($updatedModel->modelItems));

            // Test with invalid action
            $data['modelRelations'] = array(
                'modelItems' => array(
                    array(
                        'action' => 'invalidAction',
                        'modelId' => $testItemRelated->id,
                        'modelClassName' => 'ApiTestModelItem'
                    ),
                ),
            );
            $response = $this->createApiCallWithRelativeUrl('update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);

            // Test with invalid relation
            $data['modelRelations'] = array(
                'aaad' => array(
                    array(
                        'action' => 'remove',
                        'modelId' => $testItemRelated->id,
                        'modelClassName' => 'ApiTestModelItem'
                    ),
                ),
            );
            $response = $this->createApiCallWithRelativeUrl('update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);

            // Test with invalid related model id
            $data['modelRelations'] = array(
                'modelItems3' => array(
                    array(
                        'action' => 'remove',
                        'modelId' => 345,
                        'modelClassName' => 'ApiTestModelItem3'
                    ),
                ),
            );
            $response = $this->createApiCallWithRelativeUrl('update/' . $compareData['id'], 'PUT', $headers, array('data' => $data));
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
        }

        /**
         * @depends testList
         */
        public function testDelete()
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

            $testModels = ApiTestModelItem::getByName('Michael6');
            $this->assertEquals(1, count($testModels));

            $response = $this->createApiCallWithRelativeUrl('delete/' . $testModels[0]->id, 'DELETE', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);

            $response = $this->createApiCallWithRelativeUrl('read/' . $testModels[0]->id, 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_FAILURE, $response['status']);
        }

        public function testLogout()
        {
            $authenticationData = $this->login();
            $headers = array(
                'Accept: application/json',
                'ZURMO_SESSION_ID: ' . $authenticationData['sessionId'],
                'ZURMO_TOKEN: ' . $authenticationData['token'],
                'ZURMO_API_REQUEST_TYPE: REST',
            );
            $response = ApiRestTestHelper::createApiCall($this->serverUrl . '/test.php/zurmo/api/logout', 'GET', $headers);
            $response = json_decode($response, true);
            $this->assertEquals(ApiResponse::STATUS_SUCCESS, $response['status']);
        }

        protected function getApiControllerClassName()
        {
            Yii::import('application.modules.api.controllers.TestModelItemApiController', true);
            return 'ApiTestModelItemApiController';
        }

        protected function getModuleBaseApiUrl()
        {
            return 'api/testModelItem/api/';
        }
    }
?>