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

    class AllPermissionsOptimizationCacheTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();

            SecurityTestHelper::createSuperAdmin();
        }

        public function testCacheAndGetHasReadPermissionOnSecurableItem()
        {
            if (AllPermissionsOptimizationCache::supportsAndAllowsMemcache())
            {
                $account = new Account();
                $account->name = 'Yooples';
                $this->assertTrue($account->save());

                $super = User::getByUsername('super');

                AllPermissionsOptimizationCache::cacheHasReadPermissionOnSecurableItem($account, $super, true);
                $hasReadPermission = AllPermissionsOptimizationCache::getHasReadPermissionOnSecurableItem($account, $super);
                $this->assertTrue($hasReadPermission);

                AllPermissionsOptimizationCache::forgetSecurableItemForRead($account);
                try
                {
                    AllPermissionsOptimizationCache::getHasReadPermissionOnSecurableItem($account, $super);
                    $this->fail('NotFoundException exception is not thrown.');
                }
                catch (NotFoundException $e)
                {
                    $this->assertTrue(true);
                }
            }
        }

        public function testCacheAndGetMungeIdsByUser()
        {
            if (AllPermissionsOptimizationCache::supportsAndAllowsMemcache())
            {
                $super = User::getByUsername('super');
                AllPermissionsOptimizationCache::cacheMungeIdsByUser($super, array(3, 4, 5));
                $mungeIds = AllPermissionsOptimizationCache::getMungeIdsByUser($super);
                $this->assertEquals(array(3, 4, 5), $mungeIds);
                $oldValue = Yii::app()->params['showFlashMessageWhenSecurityCacheShouldBeRebuilt'];
                Yii::app()->params['showFlashMessageWhenSecurityCacheShouldBeRebuilt'] = true;
                $this->assertEquals(0, count(Yii::app()->user->getFlashes()));
                AllPermissionsOptimizationCache::forgetAll();
                $this->assertEquals(1, count(Yii::app()->user->getFlashes()));
                Yii::app()->params['showFlashMessageWhenSecurityCacheShouldBeRebuilt'] = false;
                $this->assertEquals(0, count(Yii::app()->user->getFlashes()));
                AllPermissionsOptimizationCache::forgetAll();
                $this->assertEquals(0, count(Yii::app()->user->getFlashes()));
                Yii::app()->params['showFlashMessageWhenSecurityCacheShouldBeRebuilt'] = $oldValue;
                try
                {
                    AllPermissionsOptimizationCache::getMungeIdsByUser($super);
                    $this->fail('NotFoundException exception is not thrown.');
                }
                catch (NotFoundException $e)
                {
                    $this->assertTrue(true);
                }
            }
        }

        public function testForgetAll()
        {
            if (PermissionsCache::supportsAndAllowsMemcache())
            {
                $super = User::getByUsername('super');
                Yii::app()->user->userModel = $super;

                $account = new Account();
                $account->name = 'Ocean Inc2.';
                $this->assertTrue($account->save());
                $combinedPermissions = 5;

                // Set some GeneralCache, which should stay in cache after cleanup
                GeneralCache::cacheEntry('somethingForTesting', 34);
                $value = GeneralCache::getEntry('somethingForTesting');
                $this->assertEquals(34, $value);

                AllPermissionsOptimizationCache::cacheHasReadPermissionOnSecurableItem($account, $super, true);
                $hasReadPermission = AllPermissionsOptimizationCache::getHasReadPermissionOnSecurableItem($account, $super);
                $this->assertTrue($hasReadPermission);

                AllPermissionsOptimizationCache::forgetAll();
                try
                {
                    AllPermissionsOptimizationCache::getHasReadPermissionOnSecurableItem($account, $super);
                    $this->fail('NotFoundException exception is not thrown.');
                }
                catch (NotFoundException $e)
                {
                    // Data from generalCache should still be in cache
                    $value = GeneralCache::getEntry('somethingForTesting');
                    $this->assertEquals(34, $value);
                }
            }
            // To-Do: Add test for forgetAll with $forgetDbLevelCache = true. It could be added to testForgetAll() function.
            // To-Do: Add test for forgetSecurableItem with $forgetDbLevelCache = true. . It could be added to testForgetSecurableItem() function.
        }
    }
?>
