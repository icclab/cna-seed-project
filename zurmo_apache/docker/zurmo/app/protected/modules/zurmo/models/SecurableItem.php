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

    class SecurableItem extends Item
    {
        /**
         * Utilized by workflow engine to understand expected permissions, since this is not saved in the model
         * until after the save action is completed. This information which could be changed via post or something else
         * usually done via a controller, might be needed for workflow actions.
         * @var ExplicitReadWriteModelPermissions | null
         */
        private $_explicitReadWriteModelPermissionsForWorkflow;

        /**
         * Permitables we should add to model in afterSave()
         * @var array
         */
        private $_permitablesToAttachAfterSave  = array();

        /**
         * @var bool
         */
        private $permissionsChanged  = false;

        /**
         * Permitables we should remove from model in afterSave()
         * @var array
         */
        private $_permitablesToDetachAfterSave  = array();

        public function getExplicitReadWriteModelPermissionsForWorkflow()
        {
            return $this->_explicitReadWriteModelPermissionsForWorkflow;
        }

        public function setExplicitReadWriteModelPermissionsForWorkflow(ExplicitReadWriteModelPermissions $permissions)
        {
            $this->_explicitReadWriteModelPermissionsForWorkflow = $permissions;
        }

        public function clearExplicitReadWriteModelPermissionsForWorkflow()
        {
            $this->_explicitReadWriteModelPermissionsForWorkflow = null;
        }

        public function getEffectivePermissions($permitable = null)
        {
            list($allowPermissions, $denyPermissions) = $this->getActualPermissions($permitable);
            $permissions = $allowPermissions & ~$denyPermissions;
            assert("($permissions & ~Permission::ALL) == 0");
            return $permissions;
        }

        /**
         * @param null|Permitable $permitable
         * @return array
         * @throws NoCurrentUserSecurityException
         */
        public function getActualPermissions($permitable = null)
        {
            assert('$permitable === null || $permitable instanceof Permitable');
            if ($permitable === null)
            {
                $permitable = Yii::app()->user->userModel;
                if (!$permitable instanceof User)
                {
                    throw new NoCurrentUserSecurityException();
                }
            }
            if (!SECURITY_OPTIMIZED || $this->processGetActualPermissionsAsNonOptimized())
            {
                // The slow way will remain here as documentation
                // for what the optimized way is doing.
                $allowPermissions = Permission::NONE;
                $denyPermissions  = Permission::NONE;
                if (Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME)->contains($permitable))
                {
                    $allowPermissions = Permission::ALL;
                }
                else
                {
                    foreach ($this->unrestrictedGet('permissions') as $permission)
                    {
                        $effectivePermissions = $permission->getEffectivePermissions($permitable);
                        if ($permission->type == Permission::ALLOW)
                        {
                            $allowPermissions |= $effectivePermissions;
                        }
                        else
                        {
                            $denyPermissions  |= $effectivePermissions;
                        }
                    }
                    $allowPermissions |= $this->getPropagatedActualAllowPermissions($permitable);
                    if (!($this instanceof NamedSecurableItem))
                    {
                        foreach (array(get_class($this), static::getModuleClassName()) as $securableItemName)
                        {
                            try
                            {
                                $securableType = NamedSecurableItem::getByName($securableItemName);
                                $typeAllowPermissions = Permission::NONE;
                                $typeDenyPermissions  = Permission::NONE;
                                foreach ($securableType->unrestrictedGet('permissions') as $permission)
                                {
                                    $effectivePermissions = $permission->getEffectivePermissions($permitable);
                                    if ($permission->type == Permission::ALLOW)
                                    {
                                        $typeAllowPermissions |= $effectivePermissions;
                                    }
                                    else
                                    {
                                        $typeDenyPermissions  |= $effectivePermissions;
                                    }
                                    // We shouldn't see something that isn't owned having CHANGE_OWNER.
                                    // assert('$typeAllowPermissions & Permission::CHANGE_OWNER == Permission::NONE');
                                }
                                $allowPermissions |= $typeAllowPermissions;
                                $denyPermissions  |= $typeDenyPermissions;
                            }
                            catch (NotFoundException $e)
                            {
                            }
                        }
                    }
                }
            }
            else
            {
                try
                {
                    $combinedPermissions = PermissionsCache::getCombinedPermissions($this, $permitable);
                }
                catch (NotFoundException $e)
                {
                    $securableItemId = $this      ->getClassId('SecurableItem');
                    $permitableId    = $permitable->getClassId('Permitable');
                    // Optimizations work on the database,
                    // anything not saved will not work.
                    assert('$permitableId > 0');
                    $className       = get_class($this);
                    $moduleName      = static::getModuleClassName();
                    $cachingOn  = PermissionsCache::supportsAndAllowsDatabaseCaching() ? 1 : 0;
                    $combinedPermissions = intval(ZurmoDatabaseCompatibilityUtil::
                                                    callFunction("get_securableitem_actual_permissions_for_permitable($securableItemId, $permitableId, '$className', '$moduleName', $cachingOn)"));
                    PermissionsCache::cacheCombinedPermissions($this, $permitable, $combinedPermissions);
                }
                $allowPermissions = ($combinedPermissions >> 8) & Permission::ALL;
                $denyPermissions  = $combinedPermissions        & Permission::ALL;
            }
            assert("($allowPermissions & ~Permission::ALL) == 0");
            assert("($denyPermissions  & ~Permission::ALL) == 0");
            return array($allowPermissions, $denyPermissions);
        }

        /**
         * Override if you need to force the permissions to process non-optimized. @see NamedSecurableItem
         * @return bool
         */
        public function processGetActualPermissionsAsNonOptimized()
        {
            return false;
        }

        public function getPropagatedActualAllowPermissions(Permitable $permitable)
        {
            if ($permitable instanceof User)
            {
                $allowPermissions = Permission::NONE;
                $descendentRoles = $this->getAllDescendentRoles($permitable->role);
                foreach ($descendentRoles as $role)
                {
                    $allowPermissions |= $this->recursiveGetPropagatedAllowPermissions($role);
                }
                return $allowPermissions;
            }
            else
            {
                return Permission::NONE;
            }
        }

        protected function recursiveGetPropagatedAllowPermissions($role)
        {
            if (!SECURITY_OPTIMIZED || $this->processGetActualPermissionsAsNonOptimized())
            {
                // The slow way will remain here as documentation
                // for what the optimized way is doing.
                $propagatedPermissions = Permission::NONE;
                foreach ($role->users as $userInRole)
                {
                    $propagatedPermissions |= $this->getEffectivePermissions($userInRole) ;
                }
                return $propagatedPermissions;
            }
            else
            {
                // It should never get here because the optimized version
                // of getActualPermissions will call
                // get_securableitem_propagated_allow_permissions_for_permitable.
                throw new NotSupportedException();
            }
        }

        protected function getAllDescendentRoles($role)
        {
            $descendentRoles = array();
            if (count($role->roles) > 0)
            {
                foreach ($role->roles as $childRole)
                {
                    $descendentRoles[] = $childRole;
                    $descendentRoles = array_merge($descendentRoles,
                                                   $this->getAllDescendentRoles($childRole));
                }
            }
            return $descendentRoles;
        }

        public function getExplicitActualPermissions($permitable = null)
        {
            assert('$permitable === null || $permitable instanceof Permitable');
            if ($permitable === null)
            {
                $permitable = Yii::app()->user->userModel;
                if (!$permitable instanceof User)
                {
                    throw new NoCurrentUserSecurityException();
                }
            }
            $allowPermissions = Permission::NONE;
            $denyPermissions  = Permission::NONE;
            foreach ($this->unrestrictedGet('permissions') as $permission)
            {
                $explicitPermissions = $permission->getExplicitPermissions($permitable);
                if ($permission->type == Permission::ALLOW)
                {
                    $allowPermissions |= $explicitPermissions;
                }
                else
                {
                    $denyPermissions  |= $explicitPermissions;
                }
            }
            assert("($allowPermissions & ~Permission::ALL) == 0");
            assert("($denyPermissions  & ~Permission::ALL) == 0");
            return array($allowPermissions, $denyPermissions);
        }

        public function getInheritedActualPermissions($permitable = null)
        {
            assert('$permitable === null || $permitable instanceof Permitable');
            if ($permitable === null)
            {
                $permitable = Yii::app()->user->userModel;
                if (!$permitable instanceof User)
                {
                    throw new NoCurrentUserSecurityException();
                }
            }
            $allowPermissions = Permission::NONE;
            $denyPermissions  = Permission::NONE;
            foreach ($this->unrestrictedGet('permissions') as $permission)
            {
                $inheritedPermissions = $permission->getInheritedPermissions($permitable);
                if ($permission->type == Permission::ALLOW)
                {
                    $allowPermissions |= $inheritedPermissions;
                }
                else
                {
                    $denyPermissions  |= $inheritedPermissions;
                }
            }
            if (!($this instanceof NamedSecurableItem))
            {
                foreach (array(get_class($this), static::getModuleClassName()) as $securableItemName)
                {
                    try
                    {
                        $securableType = NamedSecurableItem::getByName($securableItemName);
                        $typeAllowPermissions = Permission::NONE;
                        $typeDenyPermissions  = Permission::NONE;
                        foreach ($securableType->permissions as $permission)
                        {
                            $inheritedPermissions = $permission->getInheritedPermissions($permitable);
                            if ($permission->type == Permission::ALLOW)
                            {
                                $typeAllowPermissions |= $inheritedPermissions;
                            }
                            else
                            {
                                $typeDenyPermissions  |= $inheritedPermissions;
                            }
                        }
                        $allowPermissions |= $typeAllowPermissions;
                        $denyPermissions  |= $typeDenyPermissions;
                    }
                    catch (NotFoundException $e)
                    {
                    }
                }
            }
            assert("($allowPermissions & ~Permission::ALL) == 0");
            assert("($denyPermissions  & ~Permission::ALL) == 0");
            return array($allowPermissions, $denyPermissions);
        }

        /**
         * @param Permitable $permitable
         * @param int $permissions
         * @param array $type
         * @return bool true/false if permissions was added. if false, the the permission already
         * existed
         */
        public function addPermissions(Permitable $permitable, $permissions, $type = Permission::ALLOW)
        {
            assert('is_int($permissions)');
            assert("($permissions & ~Permission::ALL) == 0");
            assert('$permissions != Permission::NONE');
            assert('in_array($type, array(Permission::ALLOW, Permission::DENY))');
            $this->checkPermissionsHasAnyOf(Permission::CHANGE_PERMISSIONS);
            if ($this instanceof NamedSecurableItem)
            {
                PermissionsCache::forgetAll();
                AllPermissionsOptimizationCache::forgetAll();
            }
            else
            {
                PermissionsCache::forgetSecurableItem($this);
                AllPermissionsOptimizationCache::forgetSecurableItemForRead($this);
            }
            $found = false;
            foreach ($this->permissions as $permission)
            {
                if ($permission->permitable->isSame($permitable) &&
                    $permission->type == $type)
                {
                    $permission->permissions |= $permissions;
                    $found = true;
                    break;
                }
            }
            if (!$found)
            {
                $permission = new Permission();
                $permission->permitable  = $permitable;
                $permission->type        = $type;
                $permission->permissions = $permissions;
                $this->permissions->add($permission);
                $this->permissionsChanged = true;
                return true;
            }
            else
            {
               return false;
            }
        }

        /**
         * @param Permitable $permitable
         * @param int $permissions
         * @param array $type
         */
        public function removePermissions(Permitable $permitable, $permissions = Permission::ALL, $type = Permission::ALLOW_DENY)
        {
            assert('is_int($permissions)');
            assert("($permissions & ~Permission::ALL) == 0");
            assert('$permissions != Permission::NONE');
            assert('in_array($type, array(Permission::ALLOW, Permission::DENY, Permission::ALLOW_DENY))');
            $this->checkPermissionsHasAnyOf(Permission::CHANGE_PERMISSIONS);
            if ($this instanceof NamedSecurableItem)
            {
                PermissionsCache::forgetAll();
                AllPermissionsOptimizationCache::forgetAll();
            }
            else
            {
                PermissionsCache::forgetSecurableItem($this);
                AllPermissionsOptimizationCache::forgetSecurableItemForRead($this);
            }
            foreach ($this->permissions as $permission)
            {
                if ($permission->permitable->isSame($permitable) &&
                    ($permission->type == $type ||
                     $type == Permission::ALLOW_DENY))
                {
                    $permission->permissions &= ~$permissions;
                    if ($permission->permissions == Permission::NONE)
                    {
                        $this->permissions->remove($permission);
                    }
                }
            }
            if ($this instanceof NamedSecurableItem)
            {
                PermissionsCache::forgetAll();
                AllPermissionsOptimizationCache::forgetAll();
            }
            else
            {
                PermissionsCache::forgetSecurableItem($this);
                AllPermissionsOptimizationCache::forgetSecurableItemForRead($this);
            }
            $this->permissionsChanged = true;
        }

        public function removeAllPermissions()
        {
            $this->checkPermissionsHasAnyOf(Permission::CHANGE_PERMISSIONS);
            PermissionsCache::forgetAll();
            AllPermissionsOptimizationCache::forgetAll();
            $this->permissions->removeAll();
        }

        public function __get($attributeName)
        {
            if (!$this->isSaving  &&
                !$this->isSetting &&
                !$this->isValidating &&
                // Anyone can get the id and owner, createdByUser, and modifiedByUser anytime.
                !in_array($attributeName, array('id', 'owner', 'createdByUser', 'modifiedByUser')))
            {
                $this->checkPermissionsHasAnyOf(Permission::READ);
            }
            return parent::__get($attributeName);
        }

        public function __set($attributeName, $value)
        {
            if ($attributeName == 'owner')
            {
                $this->checkPermissionsHasAnyOf(Permission::CHANGE_OWNER);
            }
            elseif ($attributeName == 'permissions')
            {
                $this->checkPermissionsHasAnyOf(Permission::CHANGE_PERMISSIONS);
            }
            else
            {
                $this->checkPermissionsHasAnyOf(Permission::WRITE);
            }
            parent::__set($attributeName, $value);
        }

        public function delete()
        {
            $this->checkPermissionsHasAnyOf(Permission::DELETE);
            return parent::delete();
        }

        /**
         * @param int $requiredPermissions
         * @throws AccessDeniedSecurityException
         */
        public function checkPermissionsHasAnyOf($requiredPermissions, User $user = null)
        {
            assert('is_int($requiredPermissions)');
            if ($user == null)
            {
                $user = Yii::app()->user->userModel;
            }
            $effectivePermissions = $this->getEffectivePermissions($user);
            if (($effectivePermissions & $requiredPermissions) == 0)
            {
                throw new AccessDeniedSecurityException($user, $requiredPermissions, $effectivePermissions);
            }
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'relations' => array(
                    'permissions' => array(static::HAS_MANY, 'Permission', static::OWNED),
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return false;
        }

        /**
         * Override on any models you want to utilize ReadPermissionsOptimization
         */
        public static function hasReadPermissionsOptimization()
        {
            return false;
        }

        /**
         * Handle Permitable Attachment/Detachment after model has been saved.
         */
        protected function afterSave()
        {
            parent::afterSave();
            $this->resolvePermitablesToUpdate();
            $this->permissionsChanged = false;
        }

        /**
         * Resolve Permitables to be updated, save model again if required.
         */
        protected function resolvePermitablesToUpdate()
        {
            $explicitReadWriteModelPermissions  = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($this);
            $this->resolveRelativePermitablesToBeUpdated($explicitReadWriteModelPermissions);
            if ($this->isPermitableUpdateRequired($explicitReadWriteModelPermissions))
            {
                $this->updatePermitables($explicitReadWriteModelPermissions);
            }
        }

        /**
         * Updates permitables for current model.
         * @param ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions
         * @return bool
         * @throws NotSupportedException
         */
        protected function updatePermitables(ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions)
        {
            $this->isSaving = false;
            $this->resolvePermitablesToAttach($explicitReadWriteModelPermissions);
            $this->resolvePermitablesToDetach($explicitReadWriteModelPermissions);
            $permissionsUpdated = ExplicitReadWriteModelPermissionsUtil::resolveExplicitReadWriteModelPermissions(
                                                                                $this,
                                                                                $explicitReadWriteModelPermissions);
            if (!$permissionsUpdated || !$this->save(false))
            {
                throw new NotSupportedException('Unable to update permissions of model');
            }
        }

        /**
         * Return true/false depending on if we need to update Permitables or not.
         * @param ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions
         * @return bool
         */
        protected function isPermitableUpdateRequired(ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions)
        {
            return (!(empty($this->_permitablesToAttachAfterSave) && empty($this->_permitablesToDetachAfterSave)));
        }

        /**
         * Resolves relative _permitablesToDetachAfterSave and _permitablesToAttachAfterSave.
         * _permitablesToDetachAfterSave is rid of items that exist in _permitablesToAttachAfterSave too.
         * _permitablesToAttachAfterSave is rid of items that are already attached to model due to previous save.
         * @param ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions
         */
        protected function resolveRelativePermitablesToBeUpdated(ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions)
        {
            // If same permitable exists in the attachment and detachment list, attachment takes precedence
            // this has to be done before we calculate relative attachment list below else we would end
            // up removing a permitable that was in attachment list and already existing too.
            $this->_permitablesToDetachAfterSave = array_diff($this->_permitablesToDetachAfterSave,
                                                                $this->_permitablesToAttachAfterSave);

            // calculate new permitables to add relative to existing ones, do not re-add existing ones.
            $existingPermitables                 = $explicitReadWriteModelPermissions->getReadWritePermitables();
            $this->_permitablesToAttachAfterSave = array_diff($this->_permitablesToAttachAfterSave, $existingPermitables);
        }

        /**
         * Add desired RW permitables to model, reset the _permitablesToAttachAfterSave array once done.
         * @param ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions
         */
        protected function resolvePermitablesToAttach(ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions)
        {
            foreach ($this->_permitablesToAttachAfterSave as $permitable)
            {
                $explicitReadWriteModelPermissions->addReadWritePermitable($permitable);
            }
            // this is what prevents infinite loops of saves
            $this->_permitablesToAttachAfterSave = array();
        }

        /**
         * Remove desired RW permitables from model, reset the _permitablesToDetachAfterSave array once done.
         * @param ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions
         */
        protected function resolvePermitablesToDetach(ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions)
        {
            foreach ($this->_permitablesToDetachAfterSave as $permitable)
            {
                $explicitReadWriteModelPermissions->addReadWritePermitableToRemove($permitable);
            }
            // this is what prevents infinite loops of saves
            $this->_permitablesToDetachAfterSave = array();
        }

        /**
         * Add a permitable to list of permitables to be attached on model's afterSave
         * checkDetachBeforeAddition ensures that we do not have same permitable in attachment
         * as well as detachment. If found in detachment list, it is removed from there.
         * @param Permitable $permitable
         * @param bool $checkDetachBeforeAddition
         */
        public function addPermitableToAttachAfterSave(Permitable $permitable, $checkDetachBeforeAddition = false)
        {
            if (!$checkDetachBeforeAddition || !$this->removePermitableFromPermitablesToDetachAfterSave($permitable))
            {
                $this->_permitablesToAttachAfterSave[] = $permitable;
            }
        }

        /**
         * Remove a permitable from the list of permitables to be attached on model's afterSave
         * @param Permitable $permitable
         * @return bool
         */
        public function removePermitableFromPermitablesToAttachAfterSave(Permitable $permitable)
        {
            $key = array_search($permitable, $this->_permitablesToAttachAfterSave);
            if ($key !== false)
            {
                unset($this->_permitablesToAttachAfterSave[$key]);
                return true;
            }
            return false;
        }

        /**
         * Add a permitable to list of permitables to be detached on model's afterSave
         * checkAttachBeforeAddition ensures that we do not have same permitable in detachment
         * as well as attachment. If found in attachment list, it is removed from there.
         * @param Permitable $permitable
         * @param bool $checkAttachBeforeAddition
         */
        public function addPermitableToDetachAfterSave(Permitable $permitable, $checkAttachBeforeAddition = false)
        {
            if (!$checkAttachBeforeAddition || !$this->removePermitableFromPermitablesToAttachAfterSave($permitable))
            {
                $this->_permitablesToDetachAfterSave[] = $permitable;
            }
        }

        /**
         * Remove a permitable from the list of permitables to be detached on model's afterSave
         * @param Permitable $permitable
         * @return bool
         */
        public function removePermitableFromPermitablesToDetachAfterSave(Permitable $permitable)
        {
            $key = array_search($permitable, $this->_permitablesToDetachAfterSave);
            if ($key !== false)
            {
                unset($this->_permitablesToDetachAfterSave[$key]);
                return true;
            }
            return false;
        }

        public function arePermissionsChanged()
        {
            return $this->permissionsChanged;
        }
    }
?>