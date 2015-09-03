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
     * Helper class for working with all permission optimizations. (Munge)
     */
    class AllPermissionsOptimizationUtil
    {
        /**
         * Performance booster to replace constant calls to getEffectivePermissions. While getEffectivePermissions does
         * make use of cache, in scenarios with complex nested roles and groups, the initial call to get the effective
         * permissions for a given user can take a long time. This is true with securityOptimization = true and when false.
         * This utility makes use of the existing munge 'read' tables already in place that can give accurate permission
         * information at the atomic 'model' level.
         * @param $requiredPermissions
         * @param OwnedSecurableItem $ownedSecurableItem
         * @param User $user
         * @return bool
         */
        public static function checkPermissionsHasAnyOf($requiredPermissions, OwnedSecurableItem $ownedSecurableItem, User $user)
        {
            assert('is_int($requiredPermissions)');
            if ($requiredPermissions == Permission::READ)
            {
                return static::resolveAndCheckPermissionsForRead($requiredPermissions, $ownedSecurableItem, $user);
            }
            elseif (in_array($requiredPermissions, array(Permission::READ, Permission::WRITE, Permission::DELETE,
                                                        Permission::CHANGE_PERMISSIONS, Permission::CHANGE_OWNER)))
            {
                if ((bool)Yii::app()->params['processReadMungeAsWriteMunge'])
                {
                    //Forcing use of Permission::READ since it is expected you are always using read/write together
                    //as explicit permissions if processReadMungeAsWriteMunge is true
                    return static::resolveAndCheckPermissionsForRead(Permission::READ, $ownedSecurableItem, $user);
                }
                else
                {
                    //todo: in future refactor split read munge into read munge and write munge to separate out
                    //the read and write logic. But for now this is done together since the use of read without write
                    //is not exposed in the user interface. @see ExplicitReadWriteModelPermissions addReadOnlyPermitable
                    //removeReadOnlyPermitable
                    return false;
                }
            }
            else
            {
                //ideally we should throw an exception, but just in case there is some custom scenario with a permission
                //not supported, we should just return false.
                return false;
            }
        }

        /**
         * @param $requiredPermissions
         * @param OwnedSecurableItem $ownedSecurableItem
         * @param User $user
         * @return bool
         * @throws AccessDeniedSecurityException
         */
        protected static function resolveAndCheckPermissionsForRead($requiredPermissions,
                                                                    OwnedSecurableItem $ownedSecurableItem, User $user)
        {
            try
            {
                if (AllPermissionsOptimizationCache::getHasReadPermissionOnSecurableItem($ownedSecurableItem, $user))
                {
                    return true;
                }
                else
                {
                    throw new AccessDeniedSecurityException($user, $requiredPermissions, Permission::NONE);
                }
            }
            catch (NotFoundException $e)
            {
                try
                {
                    $hasReadPermission = self::checkPermissionsHasRead($requiredPermissions, $ownedSecurableItem, $user);
                    AllPermissionsOptimizationCache::cacheHasReadPermissionOnSecurableItem(
                        $ownedSecurableItem, $user, $hasReadPermission);
                    return $hasReadPermission;
                }
                catch (AccessDeniedSecurityException $e)
                {
                    $hasReadPermission = false;
                    AllPermissionsOptimizationCache::cacheHasReadPermissionOnSecurableItem(
                        $ownedSecurableItem, $user, $hasReadPermission);
                    throw new AccessDeniedSecurityException($user, $requiredPermissions, Permission::NONE);
                }
            }
        }

        /**
         * @param $requiredPermissions
         * @param OwnedSecurableItem $ownedSecurableItem
         * @param User $user
         * @return bool
         * @throws NotSupportedException
         * @throws AccessDeniedSecurityException
         */
        protected static function checkPermissionsHasRead($requiredPermissions, OwnedSecurableItem  $ownedSecurableItem, User $user)
        {
            $modelClassName  = get_class($ownedSecurableItem);
            $moduleClassName = $modelClassName::getModuleClassName();
            $permission = PermissionsUtil::getActualPermissionDataForReadByModuleNameForUser($moduleClassName, $user);
            if ($permission == Permission::NONE)
            {
                $mungeIds = static::getMungeIdsByUser($user);
                if (count($mungeIds) > 0 && $permission == Permission::NONE)
                {
                    $quote          = DatabaseCompatibilityUtil::getQuote();
                    $mungeTableName = ReadPermissionsOptimizationUtil::getMungeTableName($modelClassName);
                    $sql            = "select id from " . $mungeTableName . " where {$quote}securableitem_id{$quote} = " .
                                      $ownedSecurableItem->getClassId('SecurableItem') . " and {$quote}munge_id{$quote} in ('" .
                                      join("', '", $mungeIds) . "') limit 1";
                    $id = ZurmoRedBean::getCol($sql);
                    if (!empty($id))
                    {
                        return true;
                    }
                    else
                    {
                        throw new AccessDeniedSecurityException($user, $requiredPermissions, Permission::NONE);
                    }
                }
                else
                {
                    throw new NotSupportedException();
                }
            }
            elseif ($permission == Permission::DENY)
            {
                throw new AccessDeniedSecurityException($user, $requiredPermissions, Permission::DENY);
            }
            else
            {
                return true;
            }
        }

        /**
         * @param bool $overwriteExistingTables
         * @param bool $forcePhp
         * @param null $messageStreamer
         */
        public static function rebuild($overwriteExistingTables = true, $forcePhp = false, $messageStreamer = null)
        {
            ReadPermissionsOptimizationUtil::rebuild($overwriteExistingTables, $forcePhp, $messageStreamer);
        }

        /**
         * @param OwnedSecurableItem $ownedSecurableItem
         */
        public static function ownedSecurableItemCreated(OwnedSecurableItem $ownedSecurableItem)
        {
            ReadPermissionsOptimizationUtil::ownedSecurableItemCreated($ownedSecurableItem);
        }

        /**
         * @param OwnedSecurableItem $ownedSecurableItem
         * @param User $oldUser
         */
        public static function ownedSecurableItemOwnerChanged(OwnedSecurableItem $ownedSecurableItem, User $oldUser = null)
        {
            ReadPermissionsOptimizationUtil::ownedSecurableItemOwnerChanged($ownedSecurableItem, $oldUser);
            AllPermissionsOptimizationCache::forgetSecurableItemForRead($ownedSecurableItem);
        }

        /**
         * @param SecurableItem $securableItem
         */
        public static function securableItemBeingDeleted(SecurableItem $securableItem)
        {
            ReadPermissionsOptimizationUtil::securableItemBeingDeleted($securableItem);
            AllPermissionsOptimizationCache::forgetSecurableItemForRead($securableItem);
        }

        /**
         * @param SecurableItem $securableItem
         * @param User $user
         */
        public static function securableItemGivenPermissionsForUser(SecurableItem $securableItem, User $user)
        {
            self::securableItemGivenReadPermissionsForUser($securableItem, $user);
        }

        /**
         * @param SecurableItem $securableItem
         * @param Group $group
         */
        public static function securableItemGivenPermissionsForGroup(SecurableItem $securableItem, Group $group)
        {
            self::securableItemGivenReadPermissionsForGroup($securableItem, $group);
        }

        /**
         * @param SecurableItem $securableItem
         * @param User $user
         */
        public static function securableItemLostPermissionsForUser(SecurableItem $securableItem, User $user)
        {
            self::securableItemLostReadPermissionsForUser($securableItem, $user);
        }

        /**
         * @param SecurableItem $securableItem
         * @param Group $group
         */
        public static function securableItemLostPermissionsForGroup(SecurableItem $securableItem, Group $group)
        {
            self::securableItemLostReadPermissionsForGroup($securableItem, $group);
        }

        /**
         * @param SecurableItem $securableItem
         * @param User $user
         */
        public static function securableItemGivenReadPermissionsForUser(SecurableItem $securableItem, User $user)
        {
            ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForUser($securableItem, $user);
            AllPermissionsOptimizationCache::forgetSecurableItemForRead($securableItem);
        }

        /**
         * @param SecurableItem $securableItem
         * @param Group $group
         */
        public static function securableItemGivenReadPermissionsForGroup(SecurableItem $securableItem, Group $group)
        {
            ReadPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($securableItem, $group);
            AllPermissionsOptimizationCache::forgetSecurableItemForRead($securableItem);
        }

        /**
         * @param SecurableItem $securableItem
         * @param User $user
         */
        public static function securableItemLostReadPermissionsForUser(SecurableItem $securableItem, User $user)
        {
            ReadPermissionsOptimizationUtil::securableItemLostPermissionsForUser($securableItem, $user);
            AllPermissionsOptimizationCache::forgetSecurableItemForRead($securableItem);
        }

        /**
         * @param SecurableItem $securableItem
         * @param Group $group
         */
        public static function securableItemLostReadPermissionsForGroup(SecurableItem $securableItem, Group $group)
        {
            ReadPermissionsOptimizationUtil::securableItemLostPermissionsForGroup($securableItem, $group);
            AllPermissionsOptimizationCache::forgetSecurableItemForRead($securableItem);
        }

        /**
         * @param $user
         */
        public static function userBeingDeleted($user)
        {
            ReadPermissionsOptimizationUtil::userBeingDeleted($user);
            AllPermissionsOptimizationCache::forgetAll();
        }

        /**
         * @param Group $group
         * @param User $user
         */
        public static function userAddedToGroup(Group $group, User $user)
        {
            ReadPermissionsOptimizationUtil::userAddedToGroup($group, $user);
            AllPermissionsOptimizationCache::forgetAll();
        }

        /**
         * @param Group $group
         * @param User $user
         */
        public static function userRemovedFromGroup(Group $group, User $user)
        {
            ReadPermissionsOptimizationUtil::userRemovedFromGroup($group, $user);
            AllPermissionsOptimizationCache::forgetAll();
        }

        /**
         * @param Group $group
         */
        public static function groupAddedToGroup(Group $group)
        {
            ReadPermissionsOptimizationUtil::groupAddedToGroup($group);
            AllPermissionsOptimizationCache::forgetAll();
        }

        /**
         * @param Group $group
         */
        public static function groupBeingRemovedFromGroup(Group $group)
        {
            ReadPermissionsOptimizationUtil::groupBeingRemovedFromGroup($group);
            AllPermissionsOptimizationCache::forgetAll();
        }

        /**
         * @param $group
         */
        public static function groupBeingDeleted($group)
        {
            ReadPermissionsOptimizationUtil::groupBeingDeleted($group);
            AllPermissionsOptimizationCache::forgetAll();
        }

        /**
         * @param Role $role
         */
        public static function roleParentSet(Role $role)
        {
            ReadPermissionsOptimizationUtil::roleParentSet($role);
            AllPermissionsOptimizationCache::forgetAll();
        }

        /**
         * @param Role $role
         */
        public static function roleParentBeingRemoved(Role $role)
        {
            ReadPermissionsOptimizationUtil::roleParentBeingRemoved($role);
            AllPermissionsOptimizationCache::forgetAll();
        }

        /**
         * @param Role $role
         */
        public static function roleBeingDeleted(Role $role)
        {
            ReadPermissionsOptimizationUtil::roleBeingDeleted($role);
            AllPermissionsOptimizationCache::forgetAll();
        }

        /**
         * @param User $user
         */
        public static function userAddedToRole(User $user)
        {
            ReadPermissionsOptimizationUtil::userAddedToRole($user);
            AllPermissionsOptimizationCache::forgetAll();
        }

        /**
         * @param User $user
         * @param Role $role
         */
        public static function userBeingRemovedFromRole(User $user, Role $role)
        {
            ReadPermissionsOptimizationUtil::userBeingRemovedFromRole($user, $role);
            AllPermissionsOptimizationCache::forgetAll();
        }

        /**
         * @param Group $group
         * @param $groupMungeIds
         */
        public static function getAllUpstreamGroupsRecursively(Group $group, & $groupMungeIds)
        {
            ReadPermissionsOptimizationUtil::getAllUpstreamGroupsRecursively($group, $groupMungeIds);
        }

        /**
         * @param User $user
         * @return array
         */
        public static function getMungeIdsByUser(User $user)
        {
            try
            {
                return AllPermissionsOptimizationCache::getMungeIdsByUser($user);
            }
            catch (NotFoundException $e)
            {
                list($roleId, $groupIds) = self::getUserRoleIdAndGroupIds($user);
                $mungeIds = array("U$user->id");
                if ($roleId != null)
                {
                    $mungeIds[] = "R$roleId";
                }
                foreach ($groupIds as $groupId)
                {
                    $mungeIds[] = "G$groupId";
                }
                //Add everyone group
                $everyoneGroupId = Group::getByName(Group::EVERYONE_GROUP_NAME)->id;
                if (!in_array("G" . $everyoneGroupId, $mungeIds) && $everyoneGroupId > 0)
                {
                    $mungeIds[] = "G" . $everyoneGroupId;
                }
                AllPermissionsOptimizationCache::cacheMungeIdsByUser($user, $mungeIds);
            }
            return $mungeIds;
        }

        /**
         * @param User $user
         * @return array
         */
        protected static function getUserRoleIdAndGroupIds(User $user)
        {
            if ($user->role->id > 0)
            {
                $roleId = $user->role->id;
            }
            else
            {
                $roleId = null;
            }
            $groupIds = array();
            foreach ($user->groups as $group)
            {
                $groupIds[] = $group->id;
            }
            return array($roleId, $groupIds);
        }
    }
?>