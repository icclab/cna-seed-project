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
     * Class AllPermissionsOptimizationCache
     * This is so that accessing the checkPermissionsHasAnyOf( of a securable item several times, specifically
     * setting several of its attributes in succession, will not recalculate
     * the user's permissions every time. Changes to permissions during a
     * request may be ignored, then will be picked up during the next request.
     * Permissions optimizations are cached at two levels, in php - they will be remembered
     * during the request and in memcache - they will be remembered across requests,
     * in the database - they will be remembered across requests even if
     * memcache doesn't have them.
     */
    class AllPermissionsOptimizationCache extends ZurmoCache
    {
        const READ = 'R';

        const CHANGE = 'C';

        /**
         * @var string
         */
        public static $mungeIdsCachePrefix = 'MI:';

        /**
         * Just Permission::READ
         * @var array
         */
        private static $securableItemToPermitableToReadPermissions   = array();

        /**
         * @var array
         */
        private static $mungeIdsByUser = array();

        /**
         * Includes PERMISSION::WRITE, PERMISSION::CHANGE_OWNER, and PERMISSION::CHANGE_PERMISSIONS
         * Implicitly would assume Permission::READ as well.
         * @var array
         */
        private static $securableItemToPermitableToChangePermissions = array();

        /**
         * @var string
         */
        public static $cacheType = 'APO:';

//todo: figure out how to best refactor to support write more easily... without needing total duplication.

        /**
         * @param SecurableItem $securableItem
         * @param Permitable $permitable
         * @return mixed | boolean
         * @throws NotFoundException
         */
        public static function getHasReadPermissionOnSecurableItem(SecurableItem $securableItem, Permitable $permitable)
        {
            if ($securableItem->getClassId('SecurableItem') == 0 ||
                $permitable   ->getClassId('Permitable')    == 0)
            {
                throw new NotFoundException();
            }

            $securableItemModelIdentifer = $securableItem->getClassId('SecurableItem');
            $permitableModelIdentifier   = $permitable   ->getClassId('Permitable');

            if (static::supportsAndAllowsPhpCaching())
            {
                if (isset(static::$securableItemToPermitableToReadPermissions
                                        [$securableItemModelIdentifer]
                                        [$permitableModelIdentifier]))
                {
                    return static::$securableItemToPermitableToReadPermissions
                                        [$securableItemModelIdentifer]
                                        [$permitableModelIdentifier];
                }
            }

            if (static::supportsAndAllowsMemcache())
            {
                $prefix = static::getCachePrefix($securableItemModelIdentifer) . self::READ;
                $serializedData = Yii::app()->cache->get($prefix . $securableItemModelIdentifer);
                if ($serializedData !== false)
                {
                    $permitablesHasReadPermission = unserialize($serializedData);
                    assert('is_array($permitablesHasReadPermission)');
                    if (isset($permitablesHasReadPermission[$permitableModelIdentifier]))
                    {
                        $hasReadPermission = $permitablesHasReadPermission[$permitableModelIdentifier];
                        if (static::supportsAndAllowsPhpCaching())
                        {
                            static::$securableItemToPermitableToReadPermissions
                                            [$securableItemModelIdentifer]
                                            [$permitableModelIdentifier] = $hasReadPermission;
                        }
                        return $hasReadPermission;
                    }
                }
            }
            throw new NotFoundException();
        }

        /**
         * @param SecurableItem $securableItem
         * @param Permitable $permitable
         * @param boolean $hasReadPermission
         */
        public static function cacheHasReadPermissionOnSecurableItem(SecurableItem $securableItem, Permitable $permitable,
                                                                     $hasReadPermission)
        {
            assert('is_bool($hasReadPermission)');

            if ($securableItem->getClassId('SecurableItem') == 0 ||
                $permitable   ->getClassId('Permitable')    == 0)
            {
                return;
            }

            $securableItemModelIdentifer = $securableItem->getClassId('SecurableItem');
            $permitableModelIdentifier   = $permitable   ->getClassId('Permitable');

            if (static::supportsAndAllowsPhpCaching())
            {
                static::$securableItemToPermitableToReadPermissions
                                        [$securableItemModelIdentifer]
                                        [$permitableModelIdentifier] = $hasReadPermission;
            }

            if (static::supportsAndAllowsMemcache())
            {
                $prefix = static::getCachePrefix($securableItemModelIdentifer). self::READ;
                $permitablesHasReadPermission = Yii::app()->cache->get($prefix . $securableItemModelIdentifer);
                if ($permitablesHasReadPermission === false)
                {
                    $permitablesHasReadPermission = array($permitableModelIdentifier => $hasReadPermission);
                    Yii::app()->cache->set($prefix . $securableItemModelIdentifer,
                                           serialize($permitablesHasReadPermission));
                }
                else
                {
                    $permitablesHasReadPermission = unserialize($permitablesHasReadPermission);
                    assert('is_array($permitablesHasReadPermission)');
                    $permitablesHasReadPermission[$permitableModelIdentifier] = $hasReadPermission;
                    Yii::app()->cache->set($prefix . $securableItemModelIdentifer,
                                           serialize($permitablesHasReadPermission));
                }
            }
        }

        /**
         * @param User $user
         * @return mixed | array $mungeIds
         * @throws NotFoundException
         */
        public static function getMungeIdsByUser(User $user)
        {
            if ($user->getClassId('Permitable')    == 0)
            {
                throw new NotFoundException();
            }
            $userModelIdentifier = $user->getModelIdentifier();
            if (static::supportsAndAllowsPhpCaching())
            {
                if (isset(static::$mungeIdsByUser[$user->id]))
                {
                    return static::$mungeIdsByUser[$user->id];
                }
            }
            if (static::supportsAndAllowsMemcache())
            {
                $prefix = static::getCachePrefix($userModelIdentifier) . static::$mungeIdsCachePrefix;
                $serializedData = Yii::app()->cache->get($prefix . $userModelIdentifier);
                if ($serializedData !== false)
                {
                    return unserialize($serializedData);
                }
            }
            throw new NotFoundException();
        }

        /**
         * @param User $user
         * @param array $mungeIds
         */
        public static function cacheMungeIdsByUser(User $user, array $mungeIds)
        {
            if ($user->getClassId('Permitable') == 0)
            {
                return;
            }
            $userModelIdentifier   = $user->getModelIdentifier();
            if (static::supportsAndAllowsPhpCaching())
            {
                static::$mungeIdsByUser[$user->id] = $mungeIds;
            }
            if (static::supportsAndAllowsMemcache())
            {
                $prefix = static::getCachePrefix($userModelIdentifier) . static::$mungeIdsCachePrefix;

                Yii::app()->cache->set($prefix . $userModelIdentifier, serialize($mungeIds));
            }
        }

        /**
         * @param SecurableItem $securableItem
         */
        public static function forgetSecurableItemForRead(SecurableItem $securableItem)
        {
            if ($securableItem->getClassId('SecurableItem') == 0)
            {
                return;
            }
            $securableItemModelIdentifer = $securableItem->getClassId('SecurableItem');
            if (static::supportsAndAllowsPhpCaching())
            {
                static::$securableItemToPermitableToReadPermissions[$securableItemModelIdentifer] = array();
            }

            if (static::supportsAndAllowsMemcache())
            {
                $prefix = static::getCachePrefix($securableItemModelIdentifer) . static::READ;
                Yii::app()->cache->delete($prefix . $securableItemModelIdentifer);
            }
        }

        /**
         * @param SecurableItem $securableItem
         */
        public static function forgetSecurableItemForChange(SecurableItem $securableItem)
        {
            if ($securableItem->getClassId('SecurableItem') == 0)
            {
                return;
            }
            $securableItemModelIdentifer = $securableItem->getClassId('SecurableItem');
            if (static::supportsAndAllowsPhpCaching())
            {
                static::$securableItemToPermitableToChangePermissions[$securableItemModelIdentifer] = array();
            }

            if (static::supportsAndAllowsMemcache())
            {
                $prefix = static::getCachePrefix($securableItemModelIdentifer) . static::CHANGE;
                Yii::app()->cache->delete($prefix . $securableItemModelIdentifer);
            }
        }

        public static function forgetAll()
        {
            if (static::supportsAndAllowsPhpCaching())
            {
                static::$securableItemToPermitableToReadPermissions   = array();
                static::$securableItemToPermitableToChangePermissions = array();
                static::$mungeIdsByUser                               = array();
            }
            if (static::supportsAndAllowsMemcache())
            {
                static::incrementCacheIncrementValue(static::$cacheType);
                static::resolveToSetFlashMessageOnForgetAll();
            }
        }

        /**
         * In larger deployments the showFlashMessageWhenSecurityCacheShouldBeRebuilt should be set to true
         * since when clearing cache, it would require a rebuild to ensure future requests are fast. Matters the most
         * when nested roles/groups are used.
         */
        protected static function resolveToSetFlashMessageOnForgetAll()
        {
            if ((bool)Yii::app()->params['showFlashMessageWhenSecurityCacheShouldBeRebuilt'])
            {
                Yii::app()->user->setFlash('permissionsOptimization',
                    Zurmo::t('ZurmoModule', 'The security cache should be rebuilt to improve performance. ' .
                                            'Please ask your administrator to perform this action.'));
            }
        }
    }
?>
