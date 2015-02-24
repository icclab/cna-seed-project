<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    class StarredUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function teardown()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            Account::deleteAll();
            parent::teardown();
        }

        public function testGetStarredTableName()
        {
            $starredTableName = AccountStarred::getTableName();
            $this->assertEquals('accountstarred', $starredTableName);
        }

        public function testCreateStarredTables()
        {
            $sql = "SHOW TABLES LIKE '%starred'";
            $allStarredTableRows = ZurmoRedBean::getAll($sql);
            $this->assertCount(4, $allStarredTableRows);
        }

        /**
         * @depends testCreateStarredTables
         */
        public function testMarkModelAsStarred()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $account              = new Account();
            $account->owner       = $super;
            $account->name        = 'Test Account';
            $account->officePhone = '1234567890';
            $this->assertTrue($account->save());
            StarredUtil::markModelAsStarred($account);
            $starredRecords       = AccountStarred::getCountByUserIdAndModelId($super->id, $account->id);
            $this->assertEquals(1, $starredRecords);
        }

        /**
         * @depends testCreateStarredTables
         */
        public function testUnmarkModelAsStarred()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $account              = new Account();
            $account->owner       = $super;
            $account->name        = 'Test Account';
            $account->officePhone = '1234567890';
            $this->assertTrue($account->save());
            StarredUtil::markModelAsStarred($account);
            StarredUtil::unmarkModelAsStarred($account);
            $starredRecords       = AccountStarred::getCountByUserIdAndModelId($super->id, $account->id);
            $this->assertEquals(0, $starredRecords);
        }

        /**
         * @depends testCreateStarredTables
         */
        public function testUnmarkModelAsStarredForAllUsers()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $steven = UserTestHelper::createBasicUser('Steven');

            $account              = new Account();
            $account->owner       = $super;
            $account->name        = 'Test Account';
            $account->officePhone = '1234567890';
            $account->addPermissions($steven, Permission::READ);
            $this->assertTrue($account->save());
            AllPermissionsOptimizationUtil::securableItemGivenReadPermissionsForUser($account, $steven);
            StarredUtil::markModelAsStarred($account);

            Yii::app()->user->userModel = $steven;
            StarredUtil::markModelAsStarred($account);
            $starredRecords = AccountStarred::getCountByUserIdAndModelId(null, $account->id);
            $this->assertEquals(2, $starredRecords);

            StarredUtil::unmarkModelAsStarredForAllUsers($account);
            $starredRecords = AccountStarred::getCountByUserIdAndModelId(null, $account->id);
            $this->assertEquals(0, $starredRecords);
        }

        /**
         * @depends testCreateStarredTables
         */
        public function testIsModelStarred()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $account              = new Account();
            $account->owner       = $super;
            $account->name        = 'Test Account';
            $account->officePhone = '1234567890';
            $this->assertTrue($account->save());
            $this->assertFalse(StarredUtil::isModelStarred($account));

            StarredUtil::markModelAsStarred($account);
            $this->assertTrue(StarredUtil::isModelStarred($account));
        }

        /**
         * @depends testCreateStarredTables
         */
        public function testToggleModelStarStatus()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $account              = new Account();
            $account->owner       = $super;
            $account->name        = 'Test Account';
            $account->officePhone = '1234567890';
            $this->assertTrue($account->save());

            $this->assertFalse(StarredUtil::isModelStarred($account));
            $this->assertEquals('icon-star starred', StarredUtil::toggleModelStarStatus('Account', $account->id));
            $this->assertTrue(StarredUtil::isModelStarred($account));
            $this->assertEquals('icon-star unstarred', StarredUtil::toggleModelStarStatus('Account', $account->id));
            $this->assertFalse(StarredUtil::isModelStarred($account));
        }

        public function testGetToggleStarStatusLink()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $account              = new Account();
            $account->owner       = $super;
            $account->name        = 'Test Account';
            $account->officePhone = '1234567890';
            $this->assertTrue($account->save());

            $dataProvider = new RedBeanModelDataProvider('Account');
            $data = $dataProvider->getData();
            $link = StarredUtil::getToggleStarStatusLink($data[0], null);
            $this->assertContains('unstarred', $link);
            $this->assertContains('star-Account-' . $account->id, $link);
        }
    }
?>
