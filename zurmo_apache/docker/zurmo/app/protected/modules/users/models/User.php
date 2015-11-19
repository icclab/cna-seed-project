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

    class User extends Permitable
    {
        const AVATAR_TYPE_DEFAULT       = 1;
        const AVATAR_TYPE_PRIMARY_EMAIL = 2;
        const AVATAR_TYPE_CUSTOM_EMAIL  = 3;

        private $avatarImageUrl;

        /**
         * @param string $username
         * @throws NotFoundException
         */
        public static function getByUsername($username)
        {
            assert('is_string($username)');
            assert('$username != ""');
            $bean = ZurmoRedBean::findOne('_user', "username = :username ", array(':username' => $username));
            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            if ($bean === false)
            {
                throw new NotFoundException();
            }
            RedBeansCache::cacheBean($bean, static::getTableName() . $bean->id);
            return self::makeModel($bean);
        }

        /**
         * Added fallback for system users to never be able to login
         * @param $username
         * @param $password
         * @return An
         * @throws NoRightWebLoginException
         * @throws BadPasswordException
         * @throws ApiNoRightWebApiLoginException
         */
        public static function authenticate($username, $password)
        {
            assert('is_string($username)');
            assert('$username != ""');
            assert('is_string($password)');
            $user = static::getByUsername($username);
            if (!static::compareWithCurrentPasswordHash($password, $user))
            {
                throw new BadPasswordException();
            }
            self::resolveAuthenticatedUserCanLogin($user);
            $user->login();
            return $user;
        }

        /**
         * Compare provided password with the hash stored in database.
         * @param $password
         * @param User $user
         * @return bool
         */
        protected static function compareWithCurrentPasswordHash($password, User $user)
        {
            $phpassHashObject       = static::resolvePhpassHashObject();
            $hashedPassword         = static::hashPassword($password);
            $databaseHash           = $user->hash;
            return $phpassHashObject->checkPassword($hashedPassword, $databaseHash);
        }

        /**
         * Check if authenticated user can login
         * @param User $user
         * @return bool
         * @throws NoRightWebLoginException
         * @throws ApiNoRightWebApiLoginException
         */
        public static function resolveAuthenticatedUserCanLogin(User $user)
        {
            if (Right::ALLOW != $user->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB) &&
                !ApiRequest::isApiRequest())
            {
                throw new NoRightWebLoginException();
            }
            if (Right::ALLOW != $user->getEffectiveRight('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API) &&
                ApiRequest::isApiRequest())
            {
                throw new ApiNoRightWebApiLoginException();
            }
            if ($user->isSystemUser && !ApiRequest::isApiRequest())
            {
                throw new NoRightWebLoginException();
            }
            if ($user->isSystemUser && ApiRequest::isApiRequest())
            {
                throw new ApiNoRightWebApiLoginException();
            }
            return true;
        }

        /**
         * @param RedBean_OODBBean $bean
         * @param bool $setDefaults
         */
        protected function constructDerived($bean, $setDefaults)
        {
            assert('$bean === null || $bean instanceof RedBean_OODBBean');
            assert('is_bool($setDefaults)');
            // Does a subset of what RedBeanModel::__construct does
            // in order to mix in the Person - this is metadata wise,
            // User doesn't get any functionality from Person.
            $modelClassName = 'Person';
            $tableName = $modelClassName::getTableName();
            if ($bean === null)
            {
                $personBean = ZurmoRedBean::dispense($tableName);
            }
            else
            {
                $userBean = $this->getClassBean('User');
                $personBean = ZurmoRedBeanLinkManager::getBean($userBean, $tableName);
                assert('$personBean !== null');
            }
            //This is a hack to recover from a bug we cannot figure out how to solve.
            //Rarely the person attributes are not part of the user, memcache needs to be restarted to solve this
            //problem as you can't use the system once this occurs. this check below will clear the specific cache
            //that causes this. Still need to figure out what is setting the cache wrong to begin with
            if (!static::isAnAttribute('lastName'))
            {
                static::forgetBeanModel('User');
            }
            $this->setClassBean                  ($modelClassName, $personBean);
            $this->mapAndCacheMetadataAndSetHints($modelClassName, $personBean);
            parent::constructDerived($bean, $setDefaults);
        }

        protected function unrestrictedDelete()
        {
            // Does a subset of what RedBeanModel::unrestrictedDelete
            // does to the classes in the class hierarchy but to Person
            // which is mixed in.
            $modelClassName = 'Person';
            $this->deleteOwnedRelatedModels  ($modelClassName);
            $this->deleteForeignRelatedModels($modelClassName);
            return parent::unrestrictedDelete();
        }

        public static function getMixedInModelClassNames()
        {
            return array('Person');
        }

        protected function linkBeans()
        {
            // Link the beans up the inheritance hierarchy, skipping
            // the person bean, then link that to the user. So the
            // user is linked to both the person and the permitable,
            // to complete the mixing in of the Person's data.
            $baseBean = null;
            foreach ($this->modelClassNameToBean as $modelClassName => $bean)
            {
                if ($modelClassName == 'Person')
                {
                    continue;
                }
                if ($baseBean !== null)
                {
                    ZurmoRedBeanLinkManager::link($bean, $baseBean);
                }
                $baseBean = $bean;
            }
            $userBean   = $this->modelClassNameToBean['User'];
            $personBean = $this->modelClassNameToBean['Person'];
            ZurmoRedBeanLinkManager::link($userBean, $personBean);
        }

        // Because no functionality is mixed in, because this is
        // purely and RedBeanModel trick, and php knows nothing about
        // it, a couple fof Person methods must be duplicated in User.
        public function __toString()
        {
            $fullName = $this->getFullName();
            if ($fullName == '')
            {
                return Zurmo::t('Core', '(Unnamed)');
            }
            return $fullName;
        }

        public static function getModuleClassName()
        {
            return 'UsersModule';
        }

        public function getFullName()
        {
            $fullName = array();
            if ($this->firstName != '')
            {
                $fullName[] = $this->firstName;
            }
            if ($this->lastName != '')
            {
                $fullName[] = $this->lastName;
            }
            return join(' ' , $fullName);
        }

        public function save($runValidation = true, array $attributeNames = null)
        {
            $passwordChanged = array_key_exists('hash', $this->originalAttributeValues);
            unset($this->originalAttributeValues['hash']);
            assert('!isset($this->originalAttributeValues["hash"])');
            $saved = parent::save($runValidation, $attributeNames);

            if ($saved && $passwordChanged)
            {
                AuditEvent::
                logAuditEvent('UsersModule', UsersModule::AUDIT_EVENT_USER_PASSWORD_CHANGED, $this->username, $this);
            }
            if ($saved)
            {
                $this->setIsActive();
            }
            return $saved;
        }

        /**
         * If a user is being added to a role, raise two events signaling a potential change in
         * Rights/Policies for this user.
         * @see Permitable::afterSave()
         */
        protected function afterSave()
        {
            if (((isset($this->originalAttributeValues['role'])) || $this->isNewModel) &&
                $this->role != null && $this->role->id > 0)
            {
                AllPermissionsOptimizationUtil::userAddedToRole($this);
                ReadPermissionsSubscriptionUtil::userAddedToRole();
                $this->onChangeRights();
                $this->onChangePolicies();
            }
            if ($this->isNewModel)
            {
                ReadPermissionsSubscriptionUtil::userCreated();
            }
            if (isset($this->originalAttributeValues['role']) && $this->originalAttributeValues['role'][1] > 0)
            {
                ReadPermissionsSubscriptionUtil::userBeingRemovedFromRole();
            }
            if (isset($this->originalAttributeValues['language']) && Yii::app()->user->userModel != null &&
                Yii::app()->user->userModel == $this)
            {
                Yii::app()->languageHelper->setActive($this->language);
            }
            parent::afterSave();
        }

        /**
         * If a user is removed from a role, raise two events signaling a potential change in
         * Rights/Policies for this user.
         * @see Item::beforeSave()
         */
        protected function beforeSave()
        {
            if (parent::beforeSave())
            {
                if (isset($this->originalAttributeValues['role']) && $this->originalAttributeValues['role'][1] > 0)
                {
                    AllPermissionsOptimizationUtil::userBeingRemovedFromRole($this, Role::getById($this->originalAttributeValues['role'][1]));
                    $this->onChangeRights();
                    $this->onChangePolicies();
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
            AllPermissionsOptimizationUtil::userBeingDeleted($this);
            return true;
        }

        protected function afterDelete()
        {
            parent::afterDelete();
            ReadPermissionsSubscriptionUtil::deleteUserItemsFromAllReadSubscriptionTables($this->id);
        }

        protected function logAuditEventsListForCreatedAndModifed($newModel)
        {
            if ($newModel)
            {
                // When the first user is created there can be no
                // current user. Log the first user as creating themselves.
                if (Yii::app()->user->userModel == null || !Yii::app()->user->userModel->id > 0)
                {
                    Yii::app()->user->userModel = $this;
                }
                AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_CREATED, strval($this), $this);
            }
            else
            {
                AuditUtil::logAuditEventsListForChangedAttributeValues($this);
            }
        }

        public static function getMetadata()
        {
            $className = get_called_class();
            try
            {
                // not using default value to save cpu cycles on requests that follow the first exception.
                return GeneralCache::getEntry($className . 'Metadata');
            }
            catch (NotFoundException $e)
            {
                $defaultMetadata = self::getDefaultMetadata();
                $metadata        = parent::getMetadata();
                $modelClassName = 'Person';
                try
                {
                    $globalMetadata = GlobalMetadata::getByClassName($modelClassName);
                    $metadata[$modelClassName] = unserialize($globalMetadata->serializedMetadata);
                }
                catch (NotFoundException $e)
                {
                    if (isset($defaultMetadata[$modelClassName]))
                    {
                        $metadata[$modelClassName] = $defaultMetadata[$modelClassName];
                    }
                }
                if (YII_DEBUG)
                {
                    self::assertMetadataIsValid($metadata);
                }
            }
            GeneralCache::cacheEntry($className . 'Metadata', $metadata);
            return $metadata;
        }

        public static function setMetadata(array $metadata)
        {
            if (YII_DEBUG)
            {
                self::assertMetadataIsValid($metadata);
            }
            // Save the mixed in Person metadata.
            if (isset($metadata['Person']))
            {
                $modelClassName = 'Person';
                try
                {
                    $globalMetadata = GlobalMetadata::getByClassName($modelClassName);
                }
                catch (NotFoundException $e)
                {
                    $globalMetadata = new GlobalMetadata();
                    $globalMetadata->className = $modelClassName;
                }
                $globalMetadata->serializedMetadata = serialize($metadata[$modelClassName]);
                $saved = $globalMetadata->save();
                assert('$saved');
            }
            if (isset($metadata['User']))
            {
                parent::setMetadata($metadata);
            }
            GeneralCache::forgetEntry(get_called_class() . 'Metadata');
        }

        public function setPassword($password)
        {
            assert('is_string($password)');
            $this->hash = self::encryptPassword($password);
        }

        public static function encryptPassword($password)
        {
            $hashedPassword     = static::hashPassword($password);
            $phpassHashObject   = static::resolvePhpassHashObject();
            $passwordHash       = $phpassHashObject->hashPassword($hashedPassword);
            return $passwordHash;
        }

        public static function hashPassword($password)
        {
            // we keep this for legacy purposes
            return md5($password);
        }

        public static function resolvePhpassHashObject()
        {
            // workaround to get namespaces working.
            // we don't need any special autoloading care thanks to author embedding that logic in Loader.php
            Yii::setPathOfAlias('Phpass', Yii::getPathOfAlias('application.extensions.phpass.src.Phpass'));
            $phpassHash = new \Phpass\Hash;
            return $phpassHash;
        }

        public function serializeAndSetAvatarData(Array $avatar)
        {
            $this->serializedAvatarData = serialize($avatar);
        }

        public function getAvatarImage($size = 250, $addScheme = false)
        {
            $avatarUrl = $this->getAvatarImageUrl($size, $addScheme);
            return ZurmoHtml::image($avatarUrl, $this->getFullName(), array('class'  => 'gravatar',
                                                                              'width'  => $size,
                                                                              'height' => $size));
        }

        private function getAvatarImageUrl($size, $addScheme = false)
        {
            assert('is_int($size)');
            {
                if (isset($this->serializedAvatarData))
                {
                    $avatar = unserialize($this->serializedAvatarData);
                }
                // Begin Not Coding Standard
                $baseGravatarUrl = '//www.gravatar.com/avatar/%s?s=' . $size . '&r=g';
                $gravatarUrlFormat        = $baseGravatarUrl . '&d=identicon';
                $gravatarDefaultUrlFormat = $baseGravatarUrl . '&d=mm';
                // End Not Coding Standard
                if (isset($avatar['avatarType']) && $avatar['avatarType'] == static::AVATAR_TYPE_DEFAULT)
                {
                    $avatarUrl = sprintf($gravatarDefaultUrlFormat, '');
                }
                elseif (isset($avatar['avatarType']) && $avatar['avatarType'] == static::AVATAR_TYPE_PRIMARY_EMAIL)
                {
                    $email      = $this->primaryEmail->emailAddress;
                    $emailHash  = md5(strtolower(trim($email)));
                    $avatarUrl  = sprintf($gravatarUrlFormat, $emailHash);
                }
                elseif (isset($avatar['avatarType']) && $avatar['avatarType'] == static::AVATAR_TYPE_CUSTOM_EMAIL)
                {
                    $email      = $avatar['customAvatarEmailAddress'];
                    $emailHash  = md5(strtolower(trim($email)));
                    $avatarUrl  = sprintf($gravatarUrlFormat, $emailHash);
                }
                else
                {
                    $avatarUrl = sprintf($gravatarDefaultUrlFormat, '');
                }
                if (isset($this->avatarImageUrl))
                {
                    $this->avatarImageUrl = $avatarUrl;
                }
                else
                {
                    if (CurlUtil::urlExists('http:' . $avatarUrl))
                    {
                        $this->avatarImageUrl = $avatarUrl;
                    }
                    else
                    {
                        $this->avatarImageUrl = Yii::app()->theme->baseUrl . '/images/offline_user.png';
                    }
                }
                if ($addScheme)
                {
                    return 'http:' . $this->avatarImageUrl;
                }
                return $this->avatarImageUrl;
            }
        }

        public static function mangleTableName()
        {
            return true;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(Person::getTranslatedAttributeLabels($language),
                    array_merge(parent::translatedAttributeLabels($language),
                array(
                    'currency'            => Zurmo::t('ZurmoModule', 'Currency',                array(), null, $language),
                    'emailAccounts'       => Zurmo::t('EmailMessagesModule', 'Email Accounts',          array(), null, $language),
                    'emailBoxes'          => Zurmo::t('EmailMessagesModule', 'Email Boxes',             array(), null, $language),
                    'emailSignatures'     => Zurmo::t('EmailMessagesModule', 'Email Signatures',        array(), null, $language),
                    'fullName'            => Zurmo::t('Core', 'Name',                    array(), null, $language),
                    'groups'              => Zurmo::t('ZurmoModule', 'Groups',                  array(), null, $language),
                    'hash'                => Zurmo::t('UsersModule', 'Hash',                    array(), null, $language),
                    'isActive'            => Zurmo::t('UsersModule', 'Is Active',               array(), null, $language),
                    'isRootUser'          => Zurmo::t('UsersModule', 'Is Root User',            array(), null, $language),
                    'hideFromSelecting'   => Zurmo::t('UsersModule', 'Hide from selecting',     array(), null, $language),
                    'hideFromLeaderboard' => Zurmo::t('UsersModule', 'Hide from leaderboard',   array(), null, $language),
                    'isSystemUser'        => Zurmo::t('UsersModule', 'Is System User',          array(), null, $language),
                    'language'            => Zurmo::t('Core', 'Language',                array(), null, $language),
                    'locale'              => Zurmo::t('UsersModule', 'Locale',                  array(), null, $language),
                    'manager'             => Zurmo::t('UsersModule', 'Manager',                 array(), null, $language),
                    'primaryEmail'        => Zurmo::t('EmailMessagesModule', 'Email',                   array(), null, $language),
                    'primaryAddress'      => Zurmo::t('ZurmoModule', 'Address',                 array(), null, $language),
                    'role'                => Zurmo::t('ZurmoModule', 'Role',                    array(), null, $language),
                    'timeZone'            => Zurmo::t('ZurmoModule', 'Time Zone',               array(), null, $language),
                    'title'               => Zurmo::t('ZurmoModule', 'Salutation',              array(), null, $language),
                    'username'            => Zurmo::t('ZurmoModule', 'Username',                array(), null, $language),
                    'lastLoginDateTime'   => Zurmo::t('UsersModule', 'Last Login',              array(), null, $language),
                )
            ));
        }

        public function getActualRight($moduleName, $rightName)
        {
            assert('is_string($moduleName)');
            assert('is_string($rightName)');
            assert('$moduleName != ""');
            assert('$rightName  != ""');
                $identifier = $this->id . $moduleName . $rightName . 'ActualRight';
                if (!SECURITY_OPTIMIZED)
                {
                    // The slow way will remain here as documentation
                    // for what the optimized way is doing.
                    try
                    {
                        // not using default value to save cpu cycles on requests that follow the first exception.
                        return RightsCache::getEntry($identifier);
                    }
                    catch (NotFoundException $e)
                    {
                        if (Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME)->contains($this))
                        {
                            $actualRight = Right::ALLOW;
                        }
                        else
                        {
                            $actualRight = parent::getActualRight($moduleName, $rightName);
                        }
                        RightsCache::cacheEntry($identifier, $actualRight);
                    }
                }
                else
                {
                    try
                    {
                        // not using default value to save cpu cycles on requests that follow the first exception.
                        return RightsCache::getEntry($identifier);
                    }
                    catch (NotFoundException $e)
                    {
                        // Optimizations work on the database,
                        // anything not saved will not work.
                        assert('$this->id > 0');
                        $actualRight     = intval(ZurmoDatabaseCompatibilityUtil::
                                           callFunction("get_user_actual_right({$this->id}, '$moduleName', '$rightName')"));
                        RightsCache::cacheEntry($identifier, $actualRight);
                    }
                }
            return $actualRight;
        }

        public function getPropagatedActualAllowRight($moduleName, $rightName)
        {
            if (!SECURITY_OPTIMIZED)
            {
                return $this->recursiveGetPropagatedActualAllowRight($this->role, $moduleName, $rightName);
            }
            else
            {
                // Optimizations work on the database,
                // anything not saved will not work.
                assert('$this->id > 0');
                return intval(ZurmoDatabaseCompatibilityUtil::
                                callFunction("get_user_propagated_actual_allow_right({$this->id}, '$moduleName', '$rightName')"));
            }
        }

        protected function recursiveGetPropagatedActualAllowRight(Role $role, $moduleName, $rightName)
        {
            if (!SECURITY_OPTIMIZED)
            {
                // The slow way will remain here as documentation
                // for what the optimized way is doing.
                foreach ($role->roles as $subRole)
                {
                    foreach ($subRole->users as $userInSubRole)
                    {
                        if ($userInSubRole->getActualRight($moduleName, $rightName) == Right::ALLOW)
                        {
                            return Right::ALLOW;
                        }
                    }
                    if ($this->recursiveGetPropagatedActualAllowRight($subRole, $moduleName, $rightName) == Right::ALLOW)
                    {
                        return Right::ALLOW;
                    }
                }
                return Right::NONE;
            }
            else
            {
                // It should never get here because the optimized version
                // of getPropagatedActualAllowRight will call
                // get_user_propagated_actual_allow_right.
                throw new NotSupportedException();
            }
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
                                callFunction("get_user_inherited_actual_right({$this->id}, '$moduleName', '$rightName')"));
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
                foreach ($this->groups as $group)
                {
                    $combinedRight |= $group->getExplicitActualRight                 ($moduleName, $rightName) |
                                      $group->getInheritedActualRightIgnoringEveryone($moduleName, $rightName);
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
                // get_user_inherited_actual_right_ignoring_everyone.
                throw new NotSupportedException();
            }
        }

        /**
         * @param string $moduleName
         * @param string $policyName
         * @return null
         */
        protected function getInheritedActualPolicyIgnoringEveryone($moduleName, $policyName)
        {
            assert('is_string($moduleName)');
            assert('is_string($policyName)');
            assert('$moduleName != ""');
            assert('$policyName != ""');
            $values = array();
            foreach ($this->groups as $group)
            {
                $value = $group->getExplicitActualPolicy($moduleName, $policyName);
                if ($value !== null)
                {
                    $values[] = $value;
                }
                else
                {
                    $value = $group->getInheritedActualPolicyIgnoringEveryone($moduleName, $policyName);
                    if ($value !== null)
                    {
                        $values[] = $value;
                    }
                }
            }
            if (count($values) > 0)
            {
                return $moduleName::getStrongerPolicy($policyName, $values);
            }
            return null;
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            // User is going to have a Person bean.
            // As far as Php is concerned User is not a
            // Person - because it isn't inheriting it,
            // but the RedBeanModel essentially uses the
            // Php inheritance to accumulate the data
            // it needs in the getDefaultMetadata() methods
            // to connect everything up in the database
            // in the same order as the inheritance.
            // By getting the person metadata from Person
            // and mixing it into the metadata for User
            // and the construction of User overriding
            // to create and connect the Person bean,
            // the User effectively is a Person from
            // a data point of view.
            $personMetadata = Person::getDefaultMetadata();
            $metadata       = parent::getDefaultMetadata();
            $metadata['Person'] = $personMetadata['Person'];
            $metadata[__CLASS__] = array(
                'members' => array(
                    'hash',
                    'language',
                    'locale',
                    'timeZone',
                    'username',
                    'serializedAvatarData',
                    'isActive',
                    'lastLoginDateTime',
                    'isRootUser',
                    'hideFromSelecting',
                    'isSystemUser',
                    'hideFromLeaderboard'
                ),
                'relations' => array(
                    'currency'          => array(static::HAS_ONE,             'Currency'),
                    'groups'            => array(static::MANY_MANY,           'Group'),
                    'manager'           => array(static::HAS_ONE,             'User',
                                                    static::NOT_OWNED,            static::LINK_TYPE_SPECIFIC,  'manager'),
                    'role'              => array(static::HAS_MANY_BELONGS_TO, 'Role'),
                    'emailBoxes'        => array(static::HAS_MANY,            'EmailBox'),
                    'emailAccounts'     => array(static::HAS_MANY,            'EmailAccount'),
                    'emailSignatures'   => array(static::HAS_MANY,            'EmailSignature',
                                                    static::OWNED),
                ),
                'foreignRelations' => array(
                    'Dashboard',
                    'Portlet',
                ),
                'rules' => array(
                    array('hash',     'type',    'type' => 'string'),
                    array('hash',     'length',  'min'   => 60, 'max' => 60),
                    array('language', 'type',    'type'  => 'string'),
                    array('language', 'length',  'max'   => 10),
                    array('locale',   'type',    'type'  => 'string'),
                    array('locale',   'length',  'max'   => 10),
                    array('timeZone', 'type',    'type'  => 'string'),
                    array('timeZone', 'length',  'max'   => 64),
                    array('timeZone', 'UserDefaultTimeZoneDefaultValueValidator'),
                    array('timeZone', 'ValidateTimeZone'),
                    array('username', 'required'),
                    array('username', 'unique'),
                    array('username', 'UsernameLengthValidator', 'on' => 'createUser, editUser'),
                    array('username', 'type',  'type' => 'string'),
                    array('username', 'match',   'pattern' => '/^[^A-Z]+$/', // Not Coding Standard
                                               'message' => 'Username must be lowercase.'),
                    array('username', 'length',  'max'   => 64),
                    array('username', 'filter', 'filter' => 'trim'),
                    array('serializedAvatarData', 'type', 'type' => 'string'),
                    array('isActive',            'readOnly'),
                    array('isActive',            'boolean'),
                    array('isRootUser',          'readOnly'),
                    array('isRootUser',          'boolean'),
                    array('hideFromSelecting',   'boolean'),
                    array('isSystemUser',        'readOnly'),
                    array('isSystemUser',        'boolean'),
                    array('hideFromLeaderboard', 'boolean'),
                    array('lastLoginDateTime',    'type', 'type' => 'datetime'),
                ),
                'elements' => array(
                    'currency' => 'CurrencyDropDown',
                    'language' => 'LanguageStaticDropDown',
                    'locale'   => 'LocaleStaticDropDown',
                    'role'     => 'Role',
                    'timeZone' => 'TimeZoneStaticDropDown',
                ),
                'defaultSortAttribute' => 'lastName',
                'noExport' => array(
                    'hash'
                ),
                'noApiExport' => array(
                    'hash'
                ),
                'noAudit' => array(
                    'serializedAvatarData',
                ),
                'indexes' => array(
                    'permitable_id' => array(
                        'members' => array('permitable_id'),
                        'unique' => false),
                ),
            );
            return $metadata;
        }

        /**
         * Check if user's email is unique.
         * @return boolean
         */
        public function beforeValidate()
        {
            if (!parent::beforeValidate())
            {
                return false;
            }

            if (isset($this->primaryEmail) &&
                isset($this->primaryEmail->emailAddress) &&
                !$this->isUserEmailUnique($this->primaryEmail->emailAddress))
            {
                return false;
            }
            return true;
        }

        /**
         * Check if user email is unique in system. Two users can't share same email address.
         * @param string $email
         * @return bool
         */
        public function isUserEmailUnique($email)
        {
            if (!$email)
            {
                return true;
            }

            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'primaryEmail',
                    'relatedAttributeName' => 'emailAddress',
                    'operatorType'         => 'equals',
                    'value'                => $email,
                )
            );

            if ($this->id > 0)
            {
                $searchAttributeData['clauses'][2] = array(
                    'attributeName'        => 'id',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => $this->id,
                );
                $searchAttributeData['structure'] = '(1 AND 2)';
            }
            else
            {
                $searchAttributeData['structure'] = '1';
            }

            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('User');
            $where = RedBeanModelDataProvider::makeWhere('User', $searchAttributeData, $joinTablesAdapter);
            $models = static::getSubset($joinTablesAdapter, null, null, $where, null);

            if (count($models) > 0 && is_array($models))
            {
                // Todo: fix form element name below
                $this->primaryEmail->addError('emailAddress', Zurmo::t('UsersModule', 'Email address already exists in system.'));
                return false;
            }
            return true;
        }

        public static function getActiveUserCount($includeRootUser = false)
        {
            $searchAttributeData    = self::makeActiveUsersQuerySearchAttributeData($includeRootUser);
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('User');
            $where = RedBeanModelDataProvider::makeWhere('User', $searchAttributeData, $joinTablesAdapter);
            return static::getCount($joinTablesAdapter, $where, null);
        }

        public static function getByCriteria($active = true, $groupId = null)
        {
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'isActive',
                    'operatorType'         => 'equals',
                    'value'                => (bool)$active,
                ),
            );
            $searchAttributeData['structure'] = '1';

            if (isset($groupId))
            {
                $searchAttributeData['clauses'][2] = array(
                    'attributeName'        => 'groups',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $groupId,
                );
                $searchAttributeData['structure'] .= ' and 2';
            }
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('User');
            $where = RedBeanModelDataProvider::makeWhere('User', $searchAttributeData, $joinTablesAdapter);
            return static::getSubset($joinTablesAdapter, null, null, $where);
        }

        public static function getRootUserCount()
        {
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'isRootUser',
                    'operatorType'         => 'equals',
                    'value'                => true,
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('User');
            $where = RedBeanModelDataProvider::makeWhere('User', $searchAttributeData, $joinTablesAdapter);
            return static::getCount($joinTablesAdapter, $where, null);
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * Currently user only supports a single email signature even though the architecture is setup to handle
         * more than one.
         * @return EmailSignature object
         */
        public function getEmailSignature()
        {
            if ($this->emailSignatures->count() == 0)
            {
                $emailSignature       = new EmailSignature();
                $emailSignature->user = $this;
                $this->emailSignatures->add($emailSignature);
                $this->save();
            }
            else
            {
                $emailSignature = $this->emailSignatures[0];
            }
            return $emailSignature;
        }

        public function isDeletable()
        {
            $superAdminGroup = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            if ($superAdminGroup->users->count() == 1 && $superAdminGroup->contains($this))
            {
                return false;
            }
            return parent::isDeletable();
        }

        /**
         * Sets the user as the root user only if there is not an existing root user.  There is only one root user allowed
         * @throws NotSupportedException
         */
        public function setIsRootUser()
        {
            if (static::getRootUserCount() > 0)
            {
                throw new ExistingRootUserException();
            }
            $this->unrestrictedSet('isRootUser', true);
        }

        public function setIsSystemUser()
        {
            $this->unrestrictedSet('isSystemUser', true);
        }

        /**
        * to change isActive attribute  properly during save
        */
        protected function setIsActive()
        {
            if ( Right::DENY == $this->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB) ||
                Right::DENY == $this->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_MOBILE) ||
                Right::DENY == $this->getExplicitActualRight ('UsersModule', UsersModule::RIGHT_LOGIN_VIA_WEB_API))
            {
                $isActive = false;
            }
            else
            {
                $isActive = true;
            }
            if ($this->isActive != $isActive)
            {
                $data = array(strval($this), array('isActive'),
                                BooleanUtil::boolToString((boolean) $this->isActive),
                                BooleanUtil::boolToString((boolean) $isActive));
                AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_MODIFIED,
                    $data, $this);
                $this->unrestrictedSet('isActive', $isActive);
                $this->save();
            }
        }

        /**
         * Overriding so when sorting by lastName it sorts bye firstName lastName
         */
        public static function getSortAttributesByAttribute($attribute)
        {
            if ($attribute == 'firstName')
            {
                return array('firstName', 'lastName');
            }
            return parent::getSortAttributesByAttribute($attribute);
        }

        protected function login()
        {
            if (!ApiRequest::isApiRequest())
            {
                $this->unrestrictedSet('lastLoginDateTime',  DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
                $this->save();
            }
        }

        /**
         * Handle the search scenario for isActive, isRootUser and isSystemUser attributes.
         */
        public function isAllowedToSetReadOnlyAttribute($attributeName)
        {
            if ($this->getScenario() == 'importModel' || $this->getScenario() == 'searchModel')
            {
                if ( in_array($attributeName, array('isActive',
                                                    'isRootUser',
                                                    'isSystemUser')))
                {
                    return true;
                }
                else
                {
                    return parent::isAllowedToSetReadOnlyAttribute($attributeName);
                }
            }
        }

        public function setIsNotRootUser()
        {
            $this->unrestrictedSet('isRootUser', false);
        }

        public function setIsNotSystemUser()
        {
            $this->unrestrictedSet('isSystemUser', false);
        }

        /**
         * @return bool
         * @throws NotSupportedException
         */
        public function isSuperAdministrator()
        {
            if ($this->id < 0)
            {
                throw new NotSupportedException();
            }
            return Group::isUserASuperAdministrator($this);
        }

        /**
         * Make active users query search attributes data.
         * @param bool $includeRootUser
         * @return array
         */
        public static function makeActiveUsersQuerySearchAttributeData($includeRootUser = false)
        {
            assert('is_bool($includeRootUser)');
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'isActive',
                    'operatorType'         => 'equals',
                    'value'                => true,
                ),
                2 => array(
                    'attributeName'        => 'isSystemUser',
                    'operatorType'         => 'equals',
                    'value'                => 0,
                ),
                3 => array(
                    'attributeName'        => 'isSystemUser',
                    'operatorType'         => 'isNull',
                    'value'                => null,
                ),
            );
            $searchAttributeData['structure'] = '1 and (2 or 3)';
            if ($includeRootUser == false)
            {
                $searchAttributeData['clauses'][4] = array(
                    'attributeName'        => 'isRootUser',
                    'operatorType'         => 'equals',
                    'value'                => 0,
                );
                $searchAttributeData['clauses'][5] = array(
                    'attributeName'        => 'isRootUser',
                    'operatorType'         => 'isNull',
                    'value'                => null,
                );
                $searchAttributeData['structure'] = '1 and (2 or 3) and (4 or 5)';
            }
            return $searchAttributeData;
        }

        /**
         * Get active users.
         * @return array
         */
        public static function getActiveUsers($includeRootUser = false)
        {
            $searchAttributeData    = self::makeActiveUsersQuerySearchAttributeData($includeRootUser);
            $joinTablesAdapter      = new RedBeanModelJoinTablesQueryAdapter('User');
            $where                  = RedBeanModelDataProvider::makeWhere('User', $searchAttributeData, $joinTablesAdapter);
            return User::getSubset($joinTablesAdapter, null, null, $where);
        }
    }