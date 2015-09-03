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
     * A securable item 'owned' by a user in the system.
     */
    class OwnedSecurableItem extends SecurableItem
    {
        /**
         * @var bool
         */
        private $treatCurrentUserAsOwnerForPermissions = false;

        /**
         * @var bool
         */
        private $onAfterOwnerChangeEventRaised = false;

        /**
         * Set when the current user needs to operate like the owner. This can be when a user is creating a new model
         * that he/she does not own.  The current user still needs to be able to set permissions for example
         * @param boolean $value
         */
        public function setTreatCurrentUserAsOwnerForPermissions($value)
        {
            assert('is_bool($value)');
            $this->treatCurrentUserAsOwnerForPermissions = $value;
        }

        /**
         * @param RedBean_OODBBean $bean
         * @param bool $setDefaults
         * @throws NoCurrentUserSecurityException
         */
        protected function constructDerived($bean, $setDefaults)
        {
            assert('$bean === null || $bean instanceof RedBean_OODBBean');
            assert('is_bool($setDefaults)');
            parent::constructDerived($bean, $setDefaults);
            // Even though setting the owner is not technically
            // a default in the sense of a Yii default rule,
            // if true the owner is not set because blank models
            // are used for searching mass updating.
            if ($bean ===  null && $setDefaults)
            {
                $currentUser = Yii::app()->user->userModel;
                if (!$currentUser instanceof User)
                {
                    throw new NoCurrentUserSecurityException();
                }
                AuditUtil::saveOriginalAttributeValue($this, 'owner', $currentUser);
                $this->unrestrictedSet('owner', $currentUser);
            }
        }

        /**
         * See Stories #82063952 and #82699138
         * Detects if the model is changed outside of being altered by a default value. An example is a new opportunity
         * that has a default stage value.  This shows as isModified=true but this is not sufficient to signal a save
         * is needed.  The 'name' attribute for example is a good signal that the model has in fact been modified.
         *
         * @see RedBeanModel::isReallyModified
         * @param $relationType integer
         * @param $isOwned boolean
         * @return bool
         */
        public function isReallyModified($relationType, $isOwned)
        {
            assert('is_int($relationType)');
            assert('is_bool($isOwned)');
            if($relationType == self::HAS_ONE || $relationType == self::HAS_MANY_BELONGS_TO)
            {
                $modifiedSignalAttribute = static::getModifiedSignalAttribute();
                if($modifiedSignalAttribute != null &&
                   !$isOwned && $this->id < 0 && $this->isModified() && $this->$modifiedSignalAttribute == null)
                {
                    return false;
                }
            }
            return $this->isModified();
        }

        /**
         * Used to signal @see isReallyModified
         * @return string - attribute that must have a value on the model when saving.
         */
        protected static function getModifiedSignalAttribute()
        {
            return 'name';
        }

        public function getEffectivePermissions($permitable = null)
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
            if (Permission::ALL == $this->resolveEffectivePermissionsForOwnerAndCreatedByUser($permitable))
            {
                return Permission::ALL;
            }
            else
            {
                return parent::getEffectivePermissions($permitable);
            }
        }

        protected function resolveEffectivePermissionsForOwnerAndCreatedByUser($permitable)
        {
            $owner         = $this->unrestrictedGet('owner');
            $createdByUser = $this->unrestrictedGet('createdByUser');
            # If an owned securable item doesn't yet have an owner
            # then whoever is creating it has full access to it. If they
            # save it with the owner being someone else they are giving
            # it away and potentially lose access to it.
            if ($owner->id < 0 ||
                $owner->isSame($permitable))
            {
                return Permission::ALL;
            }
            //If the record has not been created yet, then the created user should have full access
            //Or if the record has not been created yet and doesn't have a createdByUser than any user can
            //have full access
            elseif ((($this->id < 0) &&
                    (($createdByUser->id > 0 &&
                            $createdByUser->isSame($permitable)) || $createdByUser->id < 0)) ||
                $this->treatCurrentUserAsOwnerForPermissions)
            {
                return Permission::ALL;
            }
            return null;
        }

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
            $owner = $this->unrestrictedGet('owner');
            # If an owned securable item doesn't yet have an owner
            # then whoever is creating it has full access to it. If they
            # save it with the owner being someone else they are giving
            # it away and potentially lose access to it.
            if ($owner->id < 0 ||
                $owner->isSame($permitable))
            {
                return array(Permission::ALL, Permission::NONE);
            }
            else
            {
                return parent::getActualPermissions($permitable);
            }
        }

        /**
         * Used to set model's owner without all the checks and audit logs.
         * Used in event handling code for queues.
         * @param User $user
         */
        public function setOwnerUnrestricted(User $user)
        {
            parent::unrestrictedSet('owner', $user);
        }

        public function __set($attributeName, $value)
        {
            if ($attributeName == 'owner')
            {
                $this->isSetting = true;
                if ($value === null || !$this->owner->isSame($value))
                {
                    $this->isSetting = false;
                    $this->onBeforeOwnerChange(new CEvent($this, array('newOwner' => $value)));
                    $this->ownerChange($value);
                    $this->onAfterOwnerChange(new CEvent($this));
                }
                else
                {
                    $this->isSetting = false;
                }
            }
            else
            {
                parent::__set($attributeName, $value);
            }
        }

        public function onBeforeOwnerChange(CEvent $event)
        {
            $this->raiseEvent('onBeforeOwnerChange', $event);
        }

        protected function ownerChange($newOwnerValue)
        {
            $this->checkPermissionsHasAnyOf(Permission::CHANGE_OWNER);
            $this->isSetting = true;
            try
            {
                if (!$this->isSaving)
                {
                    AuditUtil::saveOriginalAttributeValue($this, 'owner', $newOwnerValue);
                }
                parent::__set('owner', $newOwnerValue);
                $this->isSetting = false;
            }
            catch (Exception $e)
            {
                $this->isSetting = false;
                throw $e;
            }
        }

        public function onAfterOwnerChange(CEvent $event)
        {
            $this->raiseEvent('onAfterOwnerChange', $event);
            $this->onAfterOwnerChangeEventRaised = true;
        }

        public function onAfterOwnerChangeAfterSave(CEvent $event)
        {
            $this->raiseEvent('onAfterOwnerChangeAfterSave', $event);
        }

        protected function afterSave()
        {
            if ($this->hasReadPermissionsOptimization())
            {
                if ($this->isNewModel)
                {
                    AllPermissionsOptimizationUtil::ownedSecurableItemCreated($this);
                }
                elseif (isset($this->originalAttributeValues['owner']) &&
                              $this->originalAttributeValues['owner'][1] > 0)
                {
                    AllPermissionsOptimizationUtil::ownedSecurableItemOwnerChanged($this,
                                                            User::getById($this->originalAttributeValues['owner'][1]));
                }
            }
            if ($this->onAfterOwnerChangeEventRaised)
            {
                $this->onAfterOwnerChangeAfterSave(new CEvent($this));
                $this->onAfterOwnerChangeEventRaised = false;
            }
            parent::afterSave();
        }

        protected function beforeDelete()
        {
            if (!parent::beforeDelete())
            {
                return false;
            }
            if ($this->hasReadPermissionsOptimization())
            {
                AllPermissionsOptimizationUtil::securableItemBeingDeleted($this);
            }
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'relations' => array(
                    'owner' => array(static::HAS_ONE, 'User', static::NOT_OWNED,
                                     static::LINK_TYPE_SPECIFIC, 'owner'),
                ),
                'rules' => array(
                    array('owner', 'required'),
                ),
                'elements' => array(
                    'owner' => 'User',
                ),
                'indexes' => array(
                    'owner__user_id' => array(
                        'members' => array('owner__user_id'),
                        'unique' => false),
                ),
            );
            return $metadata;
        }

        /**
         * Override to add ReadPermissionOptimization query parts.
         * @param string $tableName
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @param null|int  $offset
         * @param null|int  $count
         * @param null|string $where
         * @param null|string $orderBy
         * @param bool $selectCount
         * @param bool $selectDistinct
         * @param array $quotedExtraSelectColumnNameAndAliases
         * @return string
         * @throws NoCurrentUserSecurityException
         */
        public static function makeSubsetOrCountSqlQuery($tableName,
                                                         RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter = null,
                                                         $offset = null, $count = null,
                                                         $where = null, $orderBy = null,
                                                         $selectCount = false, $selectDistinct = false,
                                                         array $quotedExtraSelectColumnNameAndAliases = array())
        {
            assert('is_string($tableName) && $tableName != ""');
            assert('$offset  === null || is_integer($offset)  && $offset  >= 0');
            assert('$count   === null || is_integer($count)   && $count   >= 1');
            assert('$where   === null || is_string ($where)   && $where   != ""');
            assert('$orderBy === null || is_string ($orderBy) && $orderBy != ""');
            assert('is_bool($selectCount)');
            assert('is_bool($selectDistinct)');
            $user = Yii::app()->user->userModel;
            if (!$user instanceof User)
            {
                throw new NoCurrentUserSecurityException();
            }
            if ($joinTablesAdapter == null)
            {
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            }
            static::resolveReadPermissionsOptimizationToSqlQuery($user, $joinTablesAdapter, $where, $selectDistinct);
            return parent::makeSubsetOrCountSqlQuery($tableName, $joinTablesAdapter, $offset, $count,
                                                         $where, $orderBy, $selectCount, $selectDistinct,
                                                         $quotedExtraSelectColumnNameAndAliases);
        }

        /**
         * @param User $user
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @param $where
         * @param $selectDistinct
         * @throws NotSupportedException
         */
        public static function resolveReadPermissionsOptimizationToSqlQuery(User $user,
                                    RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter,
                                    & $where,
                                    & $selectDistinct)
        {
            assert('$where == null || is_string($where)');
            assert('is_bool($selectDistinct)');
            $modelClassName  = get_called_class();
            $moduleClassName = $modelClassName::getModuleClassName();
            //Currently only adds munge if the module is securable and this model supports it.
            if (static::hasReadPermissionsOptimization() && $moduleClassName != null &&
                is_subclass_of($moduleClassName, 'SecurableModule'))
            {
                $permission = PermissionsUtil::getActualPermissionDataForReadByModuleNameForUser($moduleClassName);

                if (($permission == Permission::NONE || $permission == Permission::DENY) &&
                        !static::bypassReadPermissionsOptimizationToSqlQueryBasedOnWhere($where))
                {
                    $quote                               = DatabaseCompatibilityUtil::getQuote();
                    $modelAttributeToDataProviderAdapter = new OwnedSecurableItemIdToDataProviderAdapter(
                                                               $modelClassName, null);
                    $builder           = new ModelJoinBuilder($modelAttributeToDataProviderAdapter, $joinTablesAdapter);
                    $ownedTableAliasName = $builder->resolveJoins();
                    $ownerColumnName = static::getForeignKeyName('OwnedSecurableItem', 'owner');
                    $mungeIds = AllPermissionsOptimizationUtil::getMungeIdsByUser($user);
                    if ($where != null)
                    {
                        $where = '(' . $where . ') and ';
                    }
                    if (count($mungeIds) > 0 && $permission == Permission::NONE)
                    {
                        $extraOnQueryPart    = " and {$quote}munge_id{$quote} in ('" . join("', '", $mungeIds) . "')";
                        $mungeTableName      = ReadPermissionsOptimizationUtil::getMungeTableName($modelClassName);
                        $mungeTableAliasName = $joinTablesAdapter->addLeftTableAndGetAliasName(
                                                                    $mungeTableName,
                                                                    'securableitem_id',
                                                                    $ownedTableAliasName,
                                                                    'securableitem_id',
                                                                    $extraOnQueryPart);

                        $where .= "($quote$ownedTableAliasName$quote.$quote$ownerColumnName$quote = $user->id OR "; // Not Coding Standard
                        $where .= "$quote$mungeTableName$quote.{$quote}munge_id{$quote} IS NOT NULL)"; // Not Coding Standard
                        $selectDistinct = true; //must use distinct since adding munge table query.
                    }
                    elseif ($permission == Permission::DENY)
                    {
                        $where .= "$quote$ownedTableAliasName$quote.$quote$ownerColumnName$quote = $user->id"; // Not Coding Standard
                    }
                    else
                    {
                        throw new NotSupportedException();
                    }
                }
            }
        }

        protected static function bypassReadPermissionsOptimizationToSqlQueryBasedOnWhere($where)
        {
            return false;
        }

        public static function isTypeDeletable()
        {
            return false;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'owner' => Zurmo::t('ZurmoModule', 'Owner', array(), null, $language),
                )
            );
        }

        /**
         * Should model have read permission subscription table or not.
         * This feature is used to track of created/deleted models, so we can easily sync Zurmo with Google Apps or Outlook
         * @return bool
         */
        public static function hasReadPermissionsSubscriptionOptimization()
        {
            return false;
        }

        /**
         * Describes if the current model supports being routed through queues.
         * This is for the Queues feature in commercial edition.
         */
        public static function supportsQueueing()
        {
            return false;
        }

        public function checkPermissionsHasAnyOf($requiredPermissions, User $user = null)
        {
            assert('is_int($requiredPermissions)');
            assert('in_array($requiredPermissions,
                             array(Permission::READ, Permission::WRITE, Permission::DELETE,
                                   Permission::CHANGE_PERMISSIONS, Permission::CHANGE_OWNER))');
            if ($user == null)
            {
                $user = Yii::app()->user->userModel;
            }
            if (Permission::ALL == $this->resolveEffectivePermissionsForOwnerAndCreatedByUser($user))
            {
                return;
            }
            elseif ($this->isDeleting)
            {
                //Avoid potential problems with accessing information already removed from munge.
                //Potentially there could be some gap with doing this, but it improves performance on complex
                //role/group setups.
                return;
            }
            else
            {
                if (SECURITY_OPTIMIZED)
                {
                    $modelClassName  = get_called_class();
                    $moduleClassName = $modelClassName::getModuleClassName();
                    if (static::hasReadPermissionsOptimization() &&
                       $moduleClassName != null &&
                       is_subclass_of($moduleClassName, 'SecurableModule') &&
                       AllPermissionsOptimizationUtil::checkPermissionsHasAnyOf($requiredPermissions, $this, $user))
                    {
                        return;
                    }
                }
                parent::checkPermissionsHasAnyOf($requiredPermissions, $user);
            }
        }
    }
?>