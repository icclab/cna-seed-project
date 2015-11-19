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

    class Role extends Item
    {
        protected static $roleIdToRoleCache = array();

        /**
         * @param string $name
         * @throws NotFoundException
         */
        public static function getByName($name)
        {
            assert('is_string($name)');
            assert('$name != ""');
            $bean = ZurmoRedBean::findOne('role', "name = :name ", array(':name' => $name));
            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            if ($bean === false)
            {
                throw new NotFoundException();
            }
            return self::makeModel($bean);
        }

        public function __toString()
        {
            assert('$this->name === null || is_string($this->name)');
            if ($this->name === null)
            {
                return Zurmo::t('Core', '(Unnamed)');
            }
            return $this->name;
        }

        public static function getIdsByUsersMemberOfGroup($groupId)
        {
            $groupId    = intval($groupId);
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'users',
                    'relatedModelData'          => array(
                            'attributeName'             => 'groups',
                            'relatedAttributeName'      => 'id',
                            'operatorType'              => 'equals',
                            'value'                     => $groupId,
                    ),
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return static::getSubsetIds($joinTablesAdapter, null, null, $where);
        }

        public static function getFromCacheOrDatabase($roleId)
        {
            $roleId     = intval($roleId);
            if (!isset(static::$roleIdToRoleCache[$roleId]))
            {
                static::$roleIdToRoleCache[$roleId] = static::getById($roleId);
            }
            return static::$roleIdToRoleCache[$roleId];
        }

        public static function forgetRoleIdToRoleCache()
        {
            static::$roleIdToRoleCache  = array();
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language), array(
                'name'    => Zurmo::t('Core', 'Name', array(), null, $language),
                'role'    => Zurmo::t('ZurmoModule', 'Parent Role', array(), null, $language),
                'roles'   => Zurmo::t('ZurmoModule', 'Roles', array(), null, $language),
                'users'   => Zurmo::t('UsersModule', 'Users', array(), null, $language)
            ));
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                ),
                'relations' => array(
                    'role'  => array(static::HAS_MANY_BELONGS_TO, 'Role'),
                    'roles' => array(static::HAS_MANY,            'Role'),
                    'users' => array(static::HAS_MANY,            'User'),
                ),
                'rules' => array(
                    array('name', 'required'),
                    array('name', 'unique'),
                    array('name', 'type',   'type' => 'string'),
                    array('name', 'length', 'min'  => 1, 'max' => 64),
                ),
                'defaultSortAttribute' => 'name'
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getModuleClassName()
        {
            return 'RolesModule';
        }

        protected function forgetPermissionsRightsAndPoliciesCache()
        {
            PermissionsCache::forgetAll();
            Permission::resetCaches();
            RightsCache::forgetAll();
            PoliciesCache::forgetAll();
        }

        protected function afterSave()
        {
            if (((isset($this->originalAttributeValues['role'])) || $this->isNewModel) &&
                $this->role != null && $this->role->id > 0)
            {
                AllPermissionsOptimizationUtil::roleParentSet($this);
                ReadPermissionsSubscriptionUtil::roleParentSet();
            }
            if (isset($this->originalAttributeValues['role']) && $this->originalAttributeValues['role'][1] > 0)
            {
                $this->forgetPermissionsRightsAndPoliciesCache();
            }
            static::$roleIdToRoleCache[intval($this->id)] = $this;
            parent::afterSave();
        }

        protected function beforeSave()
        {
            if (parent::beforeSave())
            {
                if (isset($this->originalAttributeValues['role']) && $this->originalAttributeValues['role'][1] > 0)
                {
                    //copy to new object, so we can populate the old parent role as the related role.
                    //otherwise it gets passed by reference. We need the old $this->role information to properly
                    //utilize the roleParentBeingRemoved method.
                    $role = unserialize(serialize($this));
                    $role->role = Role::getById($this->originalAttributeValues['role'][1]);
                    AllPermissionsOptimizationUtil::roleParentBeingRemoved($role);
                    ReadPermissionsSubscriptionUtil::roleParentBeingRemoved();
                    assert('$this->originalAttributeValues["role"][1] != $this->role->id');
                }
                return true;
            }
            else
            {
                return false;
            }
        }

        protected function beforeDelete()
        {
            if (!parent::beforeDelete())
            {
                return false;
            }
            AllPermissionsOptimizationUtil::roleBeingDeleted($this);
            return true;
        }

        protected function afterDelete()
        {
            $this->forgetPermissionsRightsAndPoliciesCache();
            ReadPermissionsSubscriptionUtil::roleHasBeenDeleted();
            AllPermissionsOptimizationCache::forgetAll();
            static::forgetRoleIdToRoleCache();
        }

        protected function beforeValidate()
        {
            if (!$this->checkIfParentIsNotInChildRoles($this->roles))
            {
                $errorMessage = Zurmo::t('ZurmoModule', 'You cannot select a child role for the parent role');
                $this->addError('role', $errorMessage);
                return false;
            }
            return parent::beforeValidate();
        }

        /**
         * Recursively
         * @param $roles
         * @return bool
         */
        protected function checkIfParentIsNotInChildRoles($roles)
        {
            $isNotInChildRoles = true;
            foreach ($roles as $role)
            {
                if ($this->role->isSame($role))
                {
                    $isNotInChildRoles = false;
                }
                else
                {
                    $isNotInChildRoles &= $this->checkIfParentIsNotInChildRoles($role->roles);
                }
            }
            return $isNotInChildRoles;
        }
    }
?>