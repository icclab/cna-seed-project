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

    class OwnedSecurableTestItemTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            //do the rebuild to ensure the tables get created properly.
            AllPermissionsOptimizationUtil::rebuild();
            //Manually build the test model munge tables.
            ReadPermissionsOptimizationUtil::recreateTable(ReadPermissionsOptimizationUtil::getMungeTableName('OwnedSecurableTestItem'));
        }

        public static function getDependentTestModelClassNames()
        {
            return array('OwnedSecurableTestItem', 'OwnedSecurableTestItem3');
        }

        public function testSavingTwiceWithAModelThatHasACurrencyValueAsARelation()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $testItem = new OwnedSecurableTestItem();
            $testItem->member = 'test';
            $saved = $testItem->save();
            $this->assertTrue($saved);

            //Because OwnedSecurableTestItem as a relatedCurrency, there are some strange issues with saving again.
            //It creates currency validation issues for any of the related users like owner, modifiedUser etc.
            //Need to investigate further to fix.

            //$testItem->forget();
            //$testItem = OwnedSecurableTestItem::getById($testItem->id);

           //Save again immediately after.
            $validated = $testItem->validate();
           // echo "<pre>";
           // print_r($testItem->getErrors());
           // echo "</pre>";
            $this->assertTrue($validated);
            $saved = $testItem->save();
            $this->assertTrue($saved);

            //Reset count of test items to 0.
            $testItem->delete();
        }

        public function testOwnerChangeChangesModifiedDateTime()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $user = UserTestHelper::createBasicUser('basic');
            $testItem = new OwnedSecurableTestItem();
            $testItem->member = 'test';
            $this->assertTrue($testItem->save());
            $defaultDateTimeModified = $testItem->modifiedDateTime;

            sleep(1);
            $testItem->owner = $user;
            $this->assertTrue($testItem->save());
            $testItemId = $testItem->id;
            $testItem->forget();
            $testItem = OwnedSecurableTestItem::getById($testItemId);
            $this->assertNotEquals($defaultDateTimeModified, $testItem->modifiedDateTime);
        }

        /**
         * Event should not fire when the owner being set is the same
         */
        public function testOwnerChangeDoesNotTriggerEventOnSet()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $testItem = new OwnedSecurableTestItem3();
            $testItem->member = 'test';
            $this->assertTrue($testItem->save());

            $testItem->member  = 'test1';
            $testItem->member2 = 'test2';
            $this->assertTrue($testItem->save());
            $testItemId = $testItem->id;
            $testItem->forget();
            $testItem = OwnedSecurableTestItem3::getById($testItemId);
            $this->assertEquals('test1', $testItem->member);
            $this->assertEquals('test2', $testItem->member2);

            $testItem->owner = Yii::app()->user->userModel;
            $this->assertTrue($testItem->save());
            $testItemId = $testItem->id;
            $testItem->forget();
            $testItem = OwnedSecurableTestItem3::getById($testItemId);
            $this->assertEquals('test1', $testItem->member);
            $this->assertEquals('test2', $testItem->member2);
        }

        public function testOwnerChangeTriggersEventOnAfterAndBeforeOwnerChange()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $user = UserTestHelper::createBasicUser('basic1');
            $testItem = new OwnedSecurableTestItem3();
            $testItem->member = 'test';
            $this->assertTrue($testItem->save());

            $testItem->member  = 'test1';
            $testItem->member2 = 'test2';
            $this->assertTrue($testItem->save());
            $testItemId = $testItem->id;
            $testItem->forget();
            $testItem = OwnedSecurableTestItem3::getById($testItemId);
            $this->assertEquals('test1', $testItem->member);
            $this->assertEquals('test2', $testItem->member2);

            $testItem->owner = $user;
            $this->assertTrue($testItem->save());
            $testItemId = $testItem->id;
            $testItem->forget();
            $testItem = OwnedSecurableTestItem3::getById($testItemId);
            $this->assertEquals('onAfterOwnerChange', $testItem->member);
            $this->assertEquals('onBeforeOwnerChange', $testItem->member2);
        }
    }
?>
