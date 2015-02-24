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

    class Group extends Permitable
    {
        const EVERYONE_GROUP_NAME             = 'Everyone';
        const SUPER_ADMINISTRATORS_GROUP_NAME = 'Super Administrators';

        // Everyone and SuperAdministrators are not subtypes
        // because it introduces too much complication with the
        // RedBeanModel mapping, and the subtypes would have no
        // data. This is simply a way to identify the special
        // groups without string comparisons. It is far from
        // ideal, but having spent some time on a subclassed
        // version it is perhaps better. For further thought.
        protected $isEveryone            = false;
        protected $isSuperAdministrators = false;

        /**
         * @param string $name
         * @return An|Group
         * @throws NotFoundException
         */
        public static function getByName($name)
        {
            assert('is_string($name)');
            assert('$name != ""');
            $bean = ZurmoRedBean::findOne('_group', "name = :name ", array(':name' => $name));
            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            if ($bean === false)
            {
                if ($name != self::EVERYONE_GROUP_NAME &&
                    $name != self::SUPER_ADMINISTRATORS_GROUP_NAME)
                {
                    throw new NotFoundException();
                }
                $group = new Group();
                $group->unrestrictedSet('name', $name);
            }
            else
            {
                $group = self::makeModel($bean);
            }
            $group->setSpecialGroup();
            return $group;
        }

        public static function getById($id, $modelClassName = null)
        {
            $group = parent::getById($id, $modelClassName);
            $group->setSpecialGroup();
            return $group;
        }

        public static function isUserASuperAdministrator(User $user)
        {
            if ($user->id < 0)
            {
                throw new NotSupportedException();
            }
            $superGroup   = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            if ($user->groups->contains($superGroup))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        /**
         * @param RedBean_OODBBean $bean
         * @param bool $setDefaults
         */
        protected function constructDerived($bean, $setDefaults)
        {
            assert('$bean === null || $bean instanceof RedBean_OODBBean');
            assert('is_bool($setDefaults)');
            parent::constructDerived($bean, $setDefaults);
            $this->setSpecialGroup();
        }

        protected function setSpecialGroup()
        {
            $this->isEveryone            = $this->name == self::EVERYONE_GROUP_NAME;
            $this->isSuperAdministrators = $this->name == self::SUPER_ADMINISTRATORS_GROUP_NAME;
        }

        public function canGivePermissions()
        {
            return !$this->isSuperAdministrators;
        }

        public function canModifyMemberships()
        {
            if (!static::isUserASuperAdministrator(Yii::app()->user->userModel) && $this->isSuperAdministrators)
            {
                return false;
            }
            return !$this->isEveryone;
        }

        public function canModifyName()
        {
            return !($this->isEveryone ||
                     $this->isSuperAdministrators);
        }

        public function canModifyRights()
        {
            return !$this->isSuperAdministrators;
        }

        public function canModifyPolicies()
        {
            return true;
        }

        public function isDeletable()
        {
            return !($this->isEveryone ||
                     $this->isSuperAdministrators);
        }

        public function contains(Permitable $permitable)
        {
            if ($this->isEveryone ||
                parent::contains($permitable))
            {
                return true;
            }
            else
            {
                if ($permitable instanceof User)
                {
                    foreach ($this->users as $user)
                    {
                        if ($user->isSame($permitable))
                        {
                            return true;
                        }
                    }
                }
                foreach ($this->groups as $group)
                {
                    if ($group->contains($permitable))
                    {
                        return true;
                    }
                }
            }
            return false;
        }

        public function __toString()
        {
            assert('$this->name === null || is_string($this->name)');
            if ($this->name === null)
            {
                return Zurmo::t('Core', '(Unnamed)');
            }
            if ($this->name == self::EVERYONE_GROUP_NAME)
            {
                return GroupsModule::resolveEveryoneDisplayLabel();
            }
            elseif ($this->name == self::SUPER_ADMINISTRATORS_GROUP_NAME)
            {
                return Zurmo::t('ZurmoModule', 'Super Administrators');
            }
            return $this->name;
        }

        public static function mangleTableName()
        {
            return true;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language), array(
                'group'  => Zurmo::t('ZurmoModule', 'Parent Group', array(), null, $language),
                'groups' => Zurmo::t('ZurmoModule', 'Groups', array(), null, $language),
                'name'   => Zurmo::t('Core', 'Name', array(), null, $language),
                'users'  => Zurmo::t('UsersModule', 'Users', array(), null, $language)
            ));
        }

        public function __get($attributeName)
        {
            if ($this->isEveryone)
            {
                if ($attributeName == 'group')
                {
                    return null;
                }
                if ($attributeName == 'groups')
                {
                    return array();
                }
            }
            if ($this->isSuperAdministrators)
            {
                if ($attributeName == 'rights')
                {
                    throw new NotSupportedException();
                }
            }
            return parent::__get($attributeName);
        }

        public function __set($attributeName, $value)
        {
            if (in_array($value, array(self::EVERYONE_GROUP_NAME,
                                       self::SUPER_ADMINISTRATORS_GROUP_NAME)) ||
                $this->isEveryone &&
                    in_array($attributeName, array('name', 'group', 'users', 'groups')) ||
                $this->isSuperAdministrators &&
                    in_array($attributeName, array('name', 'rights')))
            {
                throw new NotSupportedException();
            }
            parent::__set($attributeName, $value);
        }

        public function getEffectiveRight($moduleName, $rightName)
        {
            return $this->getActualRight($moduleName, $rightName) == Right::ALLOW ? Right::ALLOW : Right::DENY;
        }

        /**
         * @param string $moduleName
         * @param string $rightName
         * @return int
         */
        public function getActualRight($moduleName, $rightName)
        {
            assert('is_string($moduleName)');
            assert('is_string($rightName)');
            assert('$moduleName != ""');
            assert('$rightName != ""');
            if ($this->isSuperAdministrators)
            {
                return Right::ALLOW;
            }
            if (!SECURITY_OPTIMIZED)
            {
                return parent::getActualRight($moduleName, $rightName);
            }
            else
            {
                // Optimizations work on the database,
                // anything not saved will not work.
                assert('$this->id > 0');
                return intval(ZurmoDatabaseCompatibilityUtil::
                                callFunction("get_group_actual_right({$this->id}, '$moduleName', '$rightName')"));
            }
        }

        public function getPropagatedActualAllowRight($moduleName, $rightName)
        {
            return Right::NONE;
        }

        /**
         * @param string $moduleName
         * @param string $rightName
         * @return int
         */
        public function getInheritedActualRight($moduleName, $rightName)
        {
            assert('is_string($moduleName)');
            assert('is_string($rightName)');
            assert('$moduleName != ""');
            assert('$rightName  != ""');
            if ($this->isEveryone)
            {
                return Right::NONE;
            }
            if (!SECURITY_OPTIMIZED)
            {
                return parent::getInheritedActualRight($moduleName, $rightName);
            }
            else
            {
                // Optimizations work on the database,
                // anything not saved will not work.
                assert('$this->id > 0');
                return intval(ZurmoDatabaseCompatibilityUtil::
                                callFunction("get_group_inherited_actual_right({$this->id}, '$moduleName', '$rightName')"));
            }
        }

        /**
         * @param string $moduleName
         * @param string $rightName
         * @return int|void
         * @throws NotSupportedException
         */
        protected function getInheritedActualRightIgnoringEveryone($moduleName, $rightName)
        {
            assert('is_string($moduleName)');
            assert('is_string($rightName)');
            assert('$moduleName != ""');
            assert('$rightName  != ""');
            if (!SECURITY_OPTIMIZED)
            {
                // The slow way will remain here as documentation
                // for what the optimized way is doing.
                $combinedRight = Right::NONE;
                if ($this->group != null && $this->group->id > 0)
                {
                    $combinedRight = $this->group->getExplicitActualRight                 ($moduleName, $rightName) |
                                     $this->group->getInheritedActualRightIgnoringEveryone($moduleName, $rightName);
                }
                if (($combinedRight & Right::DENY) == Right::DENY)
                {
                    return Right::DENY;
                }
                assert('in_array($combinedRight, array(Right::NONE, Right::ALLOW))');
                return $combinedRight;
            }
            else
            {
                // It should never get here because the optimized version
                // of getInheritedActualRight will call
                // get_group_inherited_actual_right_ignoring_everyone.
                throw new NotSupportedException();
            }
        }

        /**
         * @param string $moduleName
         * @param string $policyName
         * @return mixed|null|string
         */
        public function getInheritedActualPolicy($moduleName, $policyName)
        {
            assert('is_string($moduleName)');
            assert('is_string($policyName)');
            assert('$moduleName != ""');
            assert('$policyName != ""');
            if ($this->isEveryone)
            {
                return null;
            }
            return parent::getInheritedActualPolicy($moduleName, $policyName);
        }

        /**
         * @param string $moduleName
         * @param string $policyName
         * @return null
         */
        public function getInheritedActualPolicyIgnoringEveryone($moduleName, $policyName)
        {
            assert('is_string($moduleName)');
            assert('is_string($policyName)');
            assert('$moduleName != ""');
            assert('$policyName != ""');
            if ($this->group != null && $this->group->id > 0 && !$this->isSame($this->group)) // Prevent cycles in database autobuild.
            {
                $value = $this->group->getExplicitActualPolicy($moduleName, $policyName);
                if ($value !== null)
                {
                    return $value;
                }
                $value = $this->group->getInheritedActualPolicyIgnoringEveryone($moduleName, $policyName);
                if ($value !== null)
                {
                    return $value;
                }
            }
            return null;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                ),
                'relations' => array(
                    'group'  => array(static::HAS_MANY_BELONGS_TO, 'Group'),
                    'groups' => array(static::HAS_MANY,            'Group'),
                    'users'  => array(static::MANY_MANY,           'User'),
                ),
                'rules' => array(
                    array('name', 'required'),
                    array('name', 'unique'),
                    array('name', 'type',   'type' => 'string'),
                    array('name', 'length', 'min'  => 2, 'max' => 64),
                ),
                'defaultSortAttribute' => 'name'
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * Used to validate if the group name is a reserved word.
         * @return boolean, true if valid. false if not.
         */
        public function isNameNotAReservedName($name)
        {
            $name = strtolower($name);

            $group1 = Group::getByName(Group::EVERYONE_GROUP_NAME);
            $group2 = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            if (($name == strtolower(Group::EVERYONE_GROUP_NAME) && $this->id != $group1->id) ||
                ($name == strtolower(Group::SUPER_ADMINISTRATORS_GROUP_NAME) && $this->id != $group2->id)
            )
            {
                $this->addError('name', Zurmo::t('ZurmoModule', 'This name is reserved. Please pick a different name.'));
                return false;
            }
            return true;
        }

        public static function getModuleClassName()
        {
            return 'GroupsModule';
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
            if (((isset($this->originalAttributeValues['group'])) || $this->isNewModel) &&
                $this->group != null && $this->group->id > 0)
            {
                AllPermissionsOptimizationUtil::groupAddedToGroup($this);
                ReadPermissionsSubscriptionUtil::groupParentHasChanged();
            }
            if (isset($this->originalAttributeValues['group']) && $this->originalAttributeValues['group'][1] > 0)
            {
                $this->forgetPermissionsRightsAndPoliciesCache();
            }
            parent::afterSave();
        }

        protected function beforeSave()
        {
            if (parent::beforeSave())
            {
                if (isset($this->originalAttributeValues['group']) && $this->originalAttributeValues['group'][1] > 0)
                {
                    //copy to new object, so we can populate the old parent group as the related group.
                    //otherwise it gets passed by reference. We need the old $this->group information to properly
                    //utilize the groupBeingRemovedFromGroup method.
                    $group = unserialize(serialize($this));
                    $group->group = Group::getById($this->originalAttributeValues['group'][1]);
                    AllPermissionsOptimizationUtil::groupBeingRemovedFromGroup($group);
                    assert('$this->originalAttributeValues["group"][1] != $this->group->id');
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
            AllPermissionsOptimizationUtil::groupBeingDeleted($this);
            return true;
        }

        protected function afterDelete()
        {
            $this->forgetPermissionsRightsAndPoliciesCache();
            ReadPermissionsSubscriptionUtil::groupHasBeenDeleted();
            AllPermissionsOptimizationCache::forgetAll();
        }

        /**
         * Return number of users in group except system users
         * @return int
         */
        public function getUserCountExceptSystemUsers()
        {
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'isSystemUser',
                    'operatorType'         => 'equals',
                    'value'                => 0,
                ),
                2 => array(
                    'attributeName'        => 'isSystemUser',
                    'operatorType'         => 'isNull',
                    'value'                => null,
                )
            );
            if ($this->name == Group::EVERYONE_GROUP_NAME)
            {
                $searchAttributeData['structure'] = '1 or 2';
            }
            else
            {
                $searchAttributeData['clauses'][3] = array(
                    'attributeName'        => 'groups',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $this->id,
                );
                $searchAttributeData['structure'] = '(1 or 2) and 3';
            }
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('User');
            $where = RedBeanModelDataProvider::makeWhere('User', $searchAttributeData, $joinTablesAdapter);

            return User::getCount($joinTablesAdapter, $where);
        }

        public function getUsersExceptSystemUsers()
        {
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'isSystemUser',
                    'operatorType'         => 'equals',
                    'value'                => 0,
                ),
                2 => array(
                    'attributeName'        => 'isSystemUser',
                    'operatorType'         => 'isNull',
                    'value'                => null,
                )
            );
            if ($this->name == Group::EVERYONE_GROUP_NAME)
            {
                $searchAttributeData['structure'] = '1 or 2';
            }
            else
            {
                $searchAttributeData['clauses'][3] = array(
                    'attributeName'        => 'groups',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $this->id,
                );
                $searchAttributeData['structure'] = '(1 or 2) and 3';
            }
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('User');
            $where = RedBeanModelDataProvider::makeWhere('User', $searchAttributeData, $joinTablesAdapter);
            return User::getSubset($joinTablesAdapter, null, null, $where);
        }
    }
?>