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

    // This is so that accessing a securable item several times, specifically
    // setting several of its attributes in succession, will not recalculate
    // the user's permissions every time. Changes to permissions during a
    // request may be ignored, then will be picked up during the next request.
    // Permissions are cached at three levels, in php - they will be remembered
    // during the request, in memcache - they will be remembered across requests,
    // in the database - they will be remembered across requests even if
    // memcache doesn't have them.
    class PermissionsCache extends ZurmoCache
    {
        private static $securableItemToPermitableToCombinedPermissions = array();

        private static $namedSecurableItemActualPermissions = array();

        public static $cacheType = 'P:';

        public static $modulePermissionsDataCachePrefix = 'MD:';

        /**
         * @param SecurableItem $securableItem
         * @param Permitable $permitable
         * @return mixed
         * @throws NotFoundException
         */
        public static function getCombinedPermissions(SecurableItem $securableItem, Permitable $permitable)
        {
            if ($securableItem->getClassId('SecurableItem') == 0 ||
                $permitable   ->getClassId('Permitable')    == 0)
            {
                throw new NotFoundException();
            }

            $securableItemModelIdentifer = $securableItem->getModelIdentifier();
            $permitableModelIdentifier   = $permitable   ->getModelIdentifier();

            if (static::supportsAndAllowsPhpCaching())
            {
                if (isset(static::$securableItemToPermitableToCombinedPermissions
                                        [$securableItemModelIdentifer]
                                        [$permitableModelIdentifier]))
                {
                    return static::$securableItemToPermitableToCombinedPermissions
                                        [$securableItemModelIdentifer]
                                        [$permitableModelIdentifier];
                }
            }

            if (static::supportsAndAllowsMemcache())
            {
                $prefix = static::getCachePrefix($securableItemModelIdentifer);
                $serializedData = Yii::app()->cache->get($prefix . $securableItemModelIdentifer);
                if ($serializedData !== false)
                {
                    $permitablesCombinedPermissions = unserialize($serializedData);
                    assert('is_array($permitablesCombinedPermissions)');
                    if (isset($permitablesCombinedPermissions[$permitableModelIdentifier]))
                    {
                        $combinedPermissions = $permitablesCombinedPermissions[$permitableModelIdentifier];
                        if (static::supportsAndAllowsPhpCaching())
                        {
                            static::$securableItemToPermitableToCombinedPermissions
                                            [$securableItemModelIdentifer]
                                            [$permitableModelIdentifier] = $combinedPermissions;
                        }
                        return $combinedPermissions;
                    }
                }
            }

            // NOTE: the db level will get the permissions from the db level cache
            // when php asks for them to be calculated so it doesn't need to be done
            // explicity here.

            throw new NotFoundException();
        }

        /**
         * @param SecurableItem $securableItem
         * @param Permitable $permitable
         * @param int $combinedPermissions
         */
        public static function cacheCombinedPermissions(SecurableItem $securableItem, Permitable $permitable,
                                                        $combinedPermissions)
        {
            assert('is_int($combinedPermissions) || ' .
                   'is_numeric($combinedPermissions[0]) && is_string($combinedPermissions[0])');

            if ($securableItem->getClassId('SecurableItem') == 0 ||
                $permitable   ->getClassId('Permitable')    == 0)
            {
                return;
            }

            $securableItemModelIdentifer = $securableItem->getModelIdentifier();
            $permitableModelIdentifier   = $permitable   ->getModelIdentifier();

            if (static::supportsAndAllowsPhpCaching())
            {
                static::$securableItemToPermitableToCombinedPermissions
                                        [$securableItemModelIdentifer]
                                        [$permitableModelIdentifier] = $combinedPermissions;
            }

            if (static::supportsAndAllowsMemcache())
            {
                $prefix = static::getCachePrefix($securableItemModelIdentifer);
                $permitablesCombinedPermissions = Yii::app()->cache->get($prefix . $securableItemModelIdentifer);
                if ($permitablesCombinedPermissions === false)
                {
                    $permitablesCombinedPermissions = array($permitableModelIdentifier => $combinedPermissions);
                    Yii::app()->cache->set($prefix . $securableItemModelIdentifer,
                                           serialize($permitablesCombinedPermissions));
                }
                else
                {
                    $permitablesCombinedPermissions = unserialize($permitablesCombinedPermissions);
                    assert('is_array($permitablesCombinedPermissions)');
                    $permitablesCombinedPermissions[$permitableModelIdentifier] = $combinedPermissions;
                    Yii::app()->cache->set($prefix . $securableItemModelIdentifer,
                                           serialize($permitablesCombinedPermissions));
                }
            }

            // NOTE: the db level caches the permissions when it calculates
            // them so php does not need to explicitly cache them here.
        }

        /**
         * Cache the actual permissions for a permitable against a named securable item.  The actual permissions against
         * a named securable item do not change very often making this cache useful to speed up performance.
         * @param string $namedSecurableItemName
         * @param object $permitable
         * @param array $actualPermissions
         * @param $cacheToDatabase
         */
        public static function cacheNamedSecurableItemActualPermissions($namedSecurableItemName, $permitable, $actualPermissions,
                                                                        $cacheToDatabase = true)
        {
            assert('is_string($namedSecurableItemName)');
            assert('$permitable instanceof Permitable');
            assert('is_array($actualPermissions)');
            assert('is_bool($cacheToDatabase)');
            $cacheKeyName = $namedSecurableItemName . get_class($permitable) . $permitable->id . 'ActualPermissions';
            if (static::supportsAndAllowsPhpCaching())
            {
                static::$namedSecurableItemActualPermissions[$cacheKeyName] = $actualPermissions;
            }
            if (static::supportsAndAllowsMemcache())
            {
                $prefix = static::getCachePrefix($cacheKeyName);
                Yii::app()->cache->set($prefix . $cacheKeyName, serialize($actualPermissions));
            }
            if (static::supportsAndAllowsDatabaseCaching() && $cacheToDatabase)
            {
                if ($permitable->getClassId('Permitable') > 0)
                {
                    ZurmoRedBean::exec("insert into named_securable_actual_permissions_cache
                                     (securableitem_name, permitable_id, allow_permissions, deny_permissions)
                                     values ('" . $namedSecurableItemName . "', " . $permitable->getClassId('Permitable') . ", " .
                                                  $actualPermissions[0] . ", " . $actualPermissions[1] . ") on duplicate key
                                     update allow_permissions = " . $actualPermissions[0] . " AND deny_permissions = " .
                                     $actualPermissions[1]);
                }
            }
        }

        /**
         * Given the name of a named securable item, return the cached entry if available.
         * @param $namedSecurableItemName
         * @param $permitable
         * @return mixed
         * @throws NotFoundException
         */
        public static function getNamedSecurableItemActualPermissions($namedSecurableItemName, $permitable)
        {
            assert('is_string($namedSecurableItemName)');
            assert('$permitable instanceof Permitable');
            $cacheKeyName = $namedSecurableItemName . get_class($permitable) . $permitable->id . 'ActualPermissions';
            if (static::supportsAndAllowsPhpCaching())
            {
                if (isset(static::$namedSecurableItemActualPermissions[$cacheKeyName]))
                {
                    return static::$namedSecurableItemActualPermissions[$cacheKeyName];
                }
            }
            if (static::supportsAndAllowsMemcache())
            {
                $prefix = static::getCachePrefix($cacheKeyName);
                $serializedData = Yii::app()->cache->get($prefix . $cacheKeyName);
                if ($serializedData !== false)
                {
                    $actualPermissions = unserialize($serializedData);
                    assert('is_array($actualPermissions)');
                    return $actualPermissions;
                }
            }
            if (static::supportsAndAllowsDatabaseCaching())
            {
                if ($permitable->getClassId('Permitable') > 0)
                {
                    $row = ZurmoRedBean::getRow("select allow_permissions, deny_permissions " .
                                                "from named_securable_actual_permissions_cache " .
                                                "where securableitem_name = '" . $namedSecurableItemName . "' and " .
                                                "permitable_id = '" . $permitable->getClassId('Permitable'). "'");
                    if ($row != null && isset($row['allow_permissions']) && isset($row['deny_permissions']))
                    {
                        static::cacheNamedSecurableItemActualPermissions($namedSecurableItemName, $permitable,
                                    array($row['allow_permissions'], $row['deny_permissions']), false);
                        return array($row['allow_permissions'], $row['deny_permissions']);
                    }
                }
            }
            throw new NotFoundException();
        }

        /**
         * @param Permitable $permitable
         * @return mixed
         * @throws NotFoundException
         */
        public static function getAllModulePermissionsDataByPermitable($permitable)
        {
            assert('$permitable instanceof Permitable');
            if ($permitable->getClassId('Permitable')    == 0)
            {
                throw new NotFoundException();
            }
            $permitableModelIdentifier = $permitable->getModelIdentifier();
            if (static::supportsAndAllowsMemcache())
            {
                $prefix = static::getCachePrefix($permitableModelIdentifier) . static::$modulePermissionsDataCachePrefix;
                $serializedData = Yii::app()->cache->get($prefix . $permitableModelIdentifier);
                if ($serializedData !== false)
                {
                    return unserialize($serializedData);
                }
            }
            throw new NotFoundException();
        }

        /**
         * @param Permitable $permitable
         * @param array $data
         */
        public static function cacheAllModulePermissionsDataByPermitables($permitable, array $data)
        {
            assert('$permitable instanceof Permitable');
            if ($permitable->getClassId('Permitable') == 0)
            {
                return;
            }
            $permitableModelIdentifier   = $permitable->getModelIdentifier();
            if (static::supportsAndAllowsMemcache())
            {
                $prefix = static::getCachePrefix($permitableModelIdentifier) . static::$modulePermissionsDataCachePrefix;

                Yii::app()->cache->set($prefix . $permitableModelIdentifier, serialize($data));
            }
        }

        // The $forgetDbLevel cache is for testing.
        public static function forgetSecurableItem(SecurableItem $securableItem, $forgetDbLevelCache = true)
        {
            if ($securableItem->getClassId('SecurableItem') == 0)
            {
                return;
            }

            $securableItemModelIdentifer = $securableItem->getModelIdentifier();

            if (static::supportsAndAllowsPhpCaching())
            {
                static::$securableItemToPermitableToCombinedPermissions[$securableItemModelIdentifer] = array();
            }

            if (static::supportsAndAllowsMemcache())
            {
                $prefix = static::getCachePrefix($securableItemModelIdentifer);
                Yii::app()->cache->delete($prefix . $securableItemModelIdentifer);
            }

            if (SECURITY_OPTIMIZED && static::supportsAndAllowsDatabaseCaching() && $forgetDbLevelCache)
            {
                $securableItemId = $securableItem->getClassID('SecurableItem');
                ZurmoDatabaseCompatibilityUtil::
                    callProcedureWithoutOuts("clear_cache_securableitem_actual_permissions($securableItemId)");
            }
        }

        // The $forgetDbLevel cache is for testing.
        public static function forgetAll($forgetDbLevelCache = true)
        {
            if (static::supportsAndAllowsPhpCaching())
            {
                static::$securableItemToPermitableToCombinedPermissions = array();
                static::$namedSecurableItemActualPermissions = array();
            }

            if (SECURITY_OPTIMIZED && static::supportsAndAllowsDatabaseCaching() && $forgetDbLevelCache)
            {
                ZurmoDatabaseCompatibilityUtil::callProcedureWithoutOuts("clear_cache_all_actual_permissions()");
            }
            if (static::supportsAndAllowsDatabaseCaching() && $forgetDbLevelCache)
            {
                ZurmoDatabaseCompatibilityUtil::
                    callProcedureWithoutOuts("clear_cache_named_securable_all_actual_permissions()");
            }
            static::clearMemcacheCache();
        }
    }
?>
