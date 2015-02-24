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

    abstract class ReadPermissionsSubscriptionUtil
    {
        const TYPE_ADD    = 1;
        const TYPE_DELETE = 2;

        const STATUS_STARTED    = 1;
        const STATUS_COMPLETED = 2;

        /**
         * Rebuild read permission subscription table
         */
        public static function buildTables()
        {
            $readSubscriptionModelClassNames = PathUtil::getAllReadSubscriptionModelClassNames();
            foreach ($readSubscriptionModelClassNames as $modelClassName)
            {
                $readPermissionsSubscriptionTableName  = static::getSubscriptionTableName($modelClassName);
                static::recreateTable($readPermissionsSubscriptionTableName);
            }
            static::recreateAccountBuildTable();
            ModelCreationApiSyncUtil::buildTable();
        }

        /**
         * Public for testing only. Need to manually create test model tables that would not be picked up normally.
         * @param $modelSubscriptionTableName
         */
        public static function recreateTable($modelSubscriptionTableName)
        {
            $schema = static::getReadSubscriptionTableSchemaByName($modelSubscriptionTableName);
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition(
                $schema, new MessageLogger());
        }

        public static function recreateAccountBuildTable()
        {
            $schema = static::getReadSubscriptionTableSchemaForAccountTempTable();
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition(
                $schema, new MessageLogger());
        }

        protected static function getReadSubscriptionTableSchemaByName($tableName)
        {
            assert('is_string($tableName) && $tableName  != ""');
            return array($tableName =>  array(
                'columns' => array(
                    array(
                        'name' => 'userid',
                        'type' => 'INT(11)',
                        'unsigned' => 'UNSIGNED',
                        'notNull' => 'NOT NULL', // Not Coding Standard
                        'collation' => null,
                        'default' => null,
                    ),
                    array(
                        'name' => 'modelid',
                        'type' => 'INT(11)',
                        'unsigned' => 'UNSIGNED',
                        'notNull' => 'NOT NULL', // Not Coding Standard
                        'collation' => null,
                        'default' => null,
                    ),
                    array(
                        'name' => 'modifieddatetime',
                        'type' => 'DATETIME',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => null,
                        'default' => 'NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'subscriptiontype',
                        'type' => 'TINYINT(4)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => null,
                        'default' => 'NULL', // Not Coding Standard
                    ),
                ),
                'indexes' => array('userid_modelid' =>
                    array(
                        'columns' => array('userid', 'modelid'),
                        'unique' => true,
                    ),
                ),
            )
            );
        }

        /*
         * Schema for temp build table for accounts.
         * After account is created, deleted, or its owner is changed we don't want to rebuild read permission subscription
         * table for all accounts, but just for those that are actually created/deleted/changed owner.
         * So we eep list of these account ids in this temp table, and after account read permission table is updated,
         * we need to remove accounts from temp table.
         */
        protected static function getReadSubscriptionTableSchemaForAccountTempTable()
        {
            $tableName = static::getAccountSubscriptionTempBuildTableName();
            return array($tableName =>  array(
                'columns' => array(
                    array(
                        'name' => 'accountid',
                        'type' => 'INT(11)',
                        'unsigned' => 'UNSIGNED',
                        'notNull' => 'NOT NULL', // Not Coding Standard
                        'collation' => null,
                        'default' => null,
                    ),
                ),
                'indexes' => array(),
                ),
            );
        }

        /**
         * Get array odf all account ids from account temp build table
         * @return array
         */
        protected static function getAccountIdsArrayFromBuildTable()
        {
            $tableName = static::getAccountSubscriptionTempBuildTableName();
            $sql = "select accountid from " . $tableName;
            return ZurmoRedBean::getCol($sql);
        }

        /*
         * Add account id to account temp build table
         */
        protected static function addAccountIdToBuildTable($accountId)
        {
            assert('is_int($accountId)');
            $tableName = static::getAccountSubscriptionTempBuildTableName();
            // Need to check if accountId already exist in table,
            // because save and owner change observer events are triggered during adding new account
            $sql = "select * from $tableName where accountid='$accountId'";
            $results = ZurmoRedBean::getAll($sql);
            if (!is_array($results) || empty($results))
            {
                $sql = "insert into $tableName (accountid) values ($accountId)";
                ZurmoRedBean::exec($sql);
            }
        }

        /*
         * Delete account id to account temp build table
         */
        protected static function deleteAccountIdFromBuildTable($accountId)
        {
            assert('is_int($accountId)');
            $tableName = static::getAccountSubscriptionTempBuildTableName();
            $sql = "delete from $tableName where accountid='$accountId'";
            ZurmoRedBean::exec($sql);
        }

        /*
         * Delete all users items from read permission subscription table
         */
        public static function deleteUserItemsFromAllReadSubscriptionTables($userId)
        {
            assert('is_int($userId)');
            // Check if user exist or not. If user exist we will not delete records
            try
            {
                $user = User::getById($userId);
                throw new NotSupportedException();
            }
            catch (NotFoundException $e)
            {
                // Do nothing
            }
            $readSubscriptionModelClassNames = PathUtil::getAllReadSubscriptionModelClassNames();
            foreach ($readSubscriptionModelClassNames as $modelClassName)
            {
                $readPermissionsSubscriptionTableName  = static::getSubscriptionTableName($modelClassName);
                $sql = "delete from $readPermissionsSubscriptionTableName where userid='$userId'";
                ZurmoRedBean::exec($sql);
            }
        }

        /*
         * Based on account id, we add account id to account temp build table, refrsh account read permission
         * subscription table for account ids in temp build table, and delete all records from account temp build table
         */
        public static function updateAccountReadSubscriptionTableBasedOnBuildTable($accountId)
        {
            assert('is_int($accountId)');
            static::addAccountIdToBuildTable($accountId);
            Yii::app()->jobQueue->add('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', 5);
        }

        protected static function getModelTableName($modelClassName)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            return $modelClassName::getTableName();
        }

        public static function getSubscriptionTableName($modelClassName)
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            return static::getModelTableName($modelClassName) . '_read_subscription';
        }

        /**
         * Get able name for account temp build table
         * @return string
         */
        public static function getAccountSubscriptionTempBuildTableName()
        {
            return 'account_read_subscription_temp_build';
        }

        /**
         * Update read subscription table for all users and models
         * @param MessageLogger $messageLogger
         * @param null | array $modelClassNames
         * @param array $arrayOfModelIdsToUpdate
         * permission subscription table just for account ids that exist in account temp build table
         * @return bool
         */
        public static function updateAllReadSubscriptionTables(MessageLogger $messageLogger, $modelClassNames = null,
                                                               $arrayOfModelIdsToUpdate = array())
        {
            $loggedUser = Yii::app()->user->userModel;
            $users = User::getAll();
            $updateStartTimestamp = time();
            static::setReadPermissionUpdateStatus(static::STATUS_STARTED);
            $messageLogger->addDebugMessage(Zurmo::t('ZurmoModule',
                'Starting read permission building for all users.'));

            foreach ($users as $user)
            {
                if ($user->isSystemUser)
                {
                    $messageLogger->addDebugMessage(Zurmo::t('ZurmoModule',
                        'Skipping system user with userID: {id}', array('{id}' => $user->id)));
                    continue;
                }
                $messageLogger->addDebugMessage(Zurmo::t('ZurmoModule',
                    'Starting read permission building for userID: {id}', array('{id}' => $user->id)));
                $startTime = microtime(true);
                Yii::app()->user->userModel = $user;

                if (!is_array($modelClassNames) || empty($modelClassNames))
                {
                    $modelClassNames = PathUtil::getAllReadSubscriptionModelClassNames();
                }
                if (!empty($modelClassNames) && is_array($modelClassNames))
                {
                    foreach ($modelClassNames as $modelClassName)
                    {
                        if ($modelClassName != 'Account')
                        {
                            static::updateReadSubscriptionTableByModelClassNameAndUser($modelClassName,
                                Yii::app()->user->userModel, $updateStartTimestamp, true,
                                $messageLogger);
                        }
                        else
                        {
                            static::updateReadSubscriptionTableByModelClassNameAndUser($modelClassName,
                                Yii::app()->user->userModel, $updateStartTimestamp, false,
                                $messageLogger, $arrayOfModelIdsToUpdate);
                        }
                    }
                }
                $endTime = microtime(true);
                $executionTimeMs = $endTime - $startTime;
                $messageLogger->addDebugMessage(Zurmo::t('ZurmoModule',
                    'Ending read permission building for userID: {id}', array('{id}' => $user->id)));
                $messageLogger->addDebugMessage(Zurmo::t('ZurmoModule',
                    'Build time for userID: {id} - {miliSeconds}', array('{id}' => $user->id, '{miliSeconds}' => $executionTimeMs)));
            }
            Yii::app()->user->userModel = $loggedUser;
            static::setTimeReadPermissionUpdateTimestamp($updateStartTimestamp);
            static::setReadPermissionUpdateStatus(static::STATUS_COMPLETED);
            return true;
        }

        /**
         * @param MessageLogger $messageLogger
         * @param null $modelClassName
         */
        public static function updateReadSubscriptionTableFromBuildTable(MessageLogger $messageLogger, $modelClassName = null)
        {
            if ($modelClassName == 'Account')
            {
                // ToDO: Add pagination - Ivica: I do not think we need it
                $accountIds = static::getAccountIdsArrayFromBuildTable();
                ReadPermissionsSubscriptionUtil::updateAllReadSubscriptionTables($messageLogger, array($modelClassName), $accountIds);
                if ($modelClassName == 'Account' && !empty($accountIds))
                {
                    foreach ($accountIds as $accountId)
                    {
                        static::deleteAccountIdFromBuildTable((int)$accountId);
                    }
                }
            }
        }

        /**
         * Rebuild All Read Permission Subscription Data
         */
        public static function rebuildAllReadPermissionSubscriptionData()
        {
            Yii::app()->jobQueue->add('ReadPermissionSubscriptionUpdate', 5);
        }

        /**
         * Update all account read permissions items, because group parent is changed
         */
        public static function groupParentHasChanged()
        {
            self::runJobForAccountsWhenRoleOrGroupChanged();
        }

        /**
         * Update all account read permissions items, because group is deleted
         */
        public static function groupHasBeenDeleted()
        {
            self::runJobForAccountsWhenRoleOrGroupChanged();
        }

        /**
         * Update all account read permissions items when permissions for item  is added to group
         */
        public static function securableItemGivenPermissionsForGroup(SecurableItem $securableItem)
        {
            if ($securableItem instanceof Account)
            {
                $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('Account');
                $account      = $securableItem->castDown(array($modelDerivationPathToItem));
                self::updateAccountReadSubscriptionTableBasedOnBuildTable($account->id);
            }
        }

        /**
         * Update all account read permissions items when permissions for item is removed from group
         */
        public static function securableItemLostPermissionsForGroup(SecurableItem $securableItem)
        {
            if ($securableItem instanceof Account)
            {
                $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('Account');
                $account      = $securableItem->castDown(array($modelDerivationPathToItem));
                self::updateAccountReadSubscriptionTableBasedOnBuildTable($account->id);
            }
        }

        /**
         * Update all account read permissions items when permissions for item is added to user
         */
        public static function securableItemGivenPermissionsForUser(SecurableItem $securableItem)
        {
            if ($securableItem instanceof Account)
            {
                $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('Account');
                $account      = $securableItem->castDown(array($modelDerivationPathToItem));
                self::updateAccountReadSubscriptionTableBasedOnBuildTable($account->id);
            }
        }

        /**
         * Update all account read permissions items when permissions for item is removed from user
         */
        public static function securableItemLostPermissionsForUser(SecurableItem $securableItem)
        {
            if ($securableItem instanceof Account)
            {
                $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('Account');
                $account      = $securableItem->castDown(array($modelDerivationPathToItem));
                self::updateAccountReadSubscriptionTableBasedOnBuildTable($account->id);
            }
        }

        /**
         * Update all read permissions, when new user is created
         */
        public static function userCreated()
        {
            // ToDo: update jobs just for one user if needed for performance reasons
            self::runJobForAccountsWhenRoleOrGroupChanged();
        }

        /**
         * Update all account read permissions items when user is added to group
         */
        public static function userAddedToGroup()
        {
            // ToDo: update jobs just for one user if needed for performance reasons
            self::runJobForAccountsWhenRoleOrGroupChanged();
        }

        /**
         * Update all account read permissions items when user is removed from group
         */
        public static function userRemovedFromGroup()
        {
            // ToDo: update jobs just for one user if needed for performance reasons
            self::runJobForAccountsWhenRoleOrGroupChanged();
        }

        /**
         * Update all account read permissions items when user is added to role
         */
        public static function userAddedToRole()
        {
            // ToDo: update jobs just for one user if needed for performance reasons
            self::runJobForAccountsWhenRoleOrGroupChanged();
        }

        /**
         * Update all account read permissions items when user is removed from role
         */
        public static function userBeingRemovedFromRole()
        {
            // ToDo: update jobs just for one user if needed for performance reasons
            self::runJobForAccountsWhenRoleOrGroupChanged();
        }

        /**
         * Update all account read permissions tables, because role parent is set
         */
        public static function roleParentSet()
        {
            self::runJobForAccountsWhenRoleOrGroupChanged();
        }

        /**
         * Update all account read permissions tables, because role parent is removed
         */
        public static function roleParentBeingRemoved()
        {
            // ToDo: This event is not called - check why
            self::runJobForAccountsWhenRoleOrGroupChanged();
        }

        /**
         * Update all account read permissions tables, because role is deleted
         */
        public static function roleHasBeenDeleted()
        {
            self::runJobForAccountsWhenRoleOrGroupChanged();
        }

        /**
         * Update all account read permissions tables when group access to module changed
         */
        public static function modulePermissionsHasBeenChanged($permitable)
        {
            if ($permitable instanceof Group || $permitable instanceof User)
            {
                self::runJobForAccountsWhenPermitableChanged();
            }
        }

        protected static function runJobForAccountsWhenRoleOrGroupChanged()
        {
            Yii::app()->jobQueue->add('ReadPermissionSubscriptionUpdateForAccount', 5);
        }

        protected static function runJobForAccountsWhenAccountPermissionsChanged()
        {
            Yii::app()->jobQueue->add('ReadPermissionSubscriptionUpdateForAccount', 5);
        }

        protected static function runJobForAccountsWhenPermitableChanged()
        {
            Yii::app()->jobQueue->add('ReadPermissionSubscriptionUpdateForAccount', 5);
        }

        /**
         * Update models in read subscription table based on modelId and userId(userId is used implicitly in getSubsetIds)
         * @param string $modelClassName
         * @param User $user
         * @param bool $onlyOwnedModels
         * @param int $updateStartTimestamp
         * @param MessageLogger $messageLogger
         * @param array $arrayOfModelIdsToUpdate
         * permission subscription table just for account ids that exist in account temp build table
         */
        public static function updateReadSubscriptionTableByModelClassNameAndUser($modelClassName, User $user, $updateStartTimestamp,
                                                                                  $onlyOwnedModels = false, MessageLogger $messageLogger,
                                                                                  $arrayOfModelIdsToUpdate = array())
        {
            assert('$modelClassName === null || is_string($modelClassName) && $modelClassName != ""');
            assert('is_int($updateStartTimestamp)');
            $metadata = array();
            $startTime = microtime(true);
            $lastReadPermissionUpdateTimestamp = static::getLastReadPermissionUpdateTimestamp();
            $dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($lastReadPermissionUpdateTimestamp);
            $updateDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($updateStartTimestamp);

            if ($modelClassName == 'Account' && !empty($arrayOfModelIdsToUpdate))
            {
                $metadata['clauses'][1] = array(
                    'attributeName' => 'id',
                    'operatorType'  => 'oneOf',
                    'value'         => $arrayOfModelIdsToUpdate,
                );
                $metadata['structure'] = "1";
            }
            else
            {
                $metadata['clauses'][1] = array(
                    'attributeName'        => 'createdDateTime',
                    'operatorType'         => 'lessThanOrEqualTo',
                    'value'                => $updateDateTime
                );
                $metadata['structure'] = "1";

                if ($onlyOwnedModels)
                {
                    $metadata['clauses'][2] = array(
                        'attributeName'        => 'owner',
                        'operatorType'         => 'equals',
                        'value'                => $user->id
                    );
                    $metadata['structure'] .= " AND 2";
                }
            }

            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
            $where  = RedBeanModelDataProvider::makeWhere($modelClassName, $metadata, $joinTablesAdapter);
            $userModelIds = $modelClassName::getSubsetIds($joinTablesAdapter, null, null, $where);
            $endTime = microtime(true);
            $executionTimeMs = $endTime - $startTime;
            $messageLogger->addDebugMessage(Zurmo::t('ZurmoModule',
                'SQL time {modelClassName}: {miliSeconds}', array('{modelClassName}' => $modelClassName, '{miliSeconds}' => $executionTimeMs)));

            // Get models from subscription table
            $tableName = static::getSubscriptionTableName($modelClassName);
            $sql = "SELECT modelid FROM $tableName WHERE userid = " . $user->id .
                " AND subscriptiontype = " . static::TYPE_ADD;

            if ($modelClassName == 'Account' && !empty($arrayOfModelIdsToUpdate))
            {
                $accountIds = static::getAccountIdsArrayFromBuildTable();
                if (!empty($accountIds))
                {
                    $list = "'". implode(", ", $accountIds) ."'";
                    $sql .= " AND modelid in ($list)";
                }
            }
            $permissionTableRows = ZurmoRedBean::getAll($sql);
            $permissionTableIds = array();
            if (is_array($permissionTableRows) && !empty($permissionTableRows))
            {
                foreach ($permissionTableRows as $permissionTableRow)
                {
                    $permissionTableIds[] = $permissionTableRow['modelid'];
                }
            }
            $modelIdsToAdd = array_diff($userModelIds, $permissionTableIds);
            $modelIdsToDelete = array_diff($permissionTableIds, $userModelIds);
            if (is_array($modelIdsToAdd) && !empty($modelIdsToAdd))
            {
                foreach ($modelIdsToAdd as $modelId)
                {
                    $sql = "DELETE FROM $tableName WHERE
                                                    userid = '" . $user->id . "'
                                                    AND modelid = '{$modelId}'
                                                    AND subscriptiontype='" . self::TYPE_DELETE . "'";
                    ZurmoRedBean::exec($sql);

                    $sql = "SELECT * FROM $tableName WHERE
                            userid = '" . $user->id . "'
                            AND modelid = '{$modelId}'
                            AND subscriptiontype='" . self::TYPE_ADD . "'";
                    $results = ZurmoRedBean::getAll($sql);

                    if (!is_array($results) || empty($results))
                    {
                        $sql = "INSERT INTO $tableName VALUES
                                (null, '" . $user->id . "', '{$modelId}', '{$updateDateTime}', '" . self::TYPE_ADD . "')";
                        ZurmoRedBean::exec($sql);
                    }
                }
            }

            if (is_array($modelIdsToDelete) && !empty($modelIdsToDelete))
            {
                foreach ($modelIdsToDelete as $modelId)
                {
                    $sql = "DELETE FROM $tableName WHERE
                                                    userid = '" . $user->id . "'
                                                    AND modelid = '{$modelId}'
                                                    AND subscriptiontype='" . self::TYPE_ADD . "'";
                    ZurmoRedBean::exec($sql);

                    $sql = "SELECT * FROM $tableName WHERE
                    userid = '" . $user->id . "'
                    AND modelid = '{$modelId}'
                    AND subscriptiontype='" . self::TYPE_DELETE . "'";
                    $results = ZurmoRedBean::getAll($sql);

                    if (!is_array($results) || empty($results))
                    {
                        $sql = "INSERT INTO $tableName VALUES
                                                        (null, '" . $user->id . "', '{$modelId}', '{$updateDateTime}', '" . self::TYPE_DELETE . "')";
                        ZurmoRedBean::exec($sql);
                    }
                }
            }
        }

        /**
         * Add new model to read permission subscription table
         * @param int $modelId
         * @param string $modelClassName
         * @param User $user
         */
        public static function addModelToReadSubscriptionTableByModelIdAndModelClassNameAndUser($modelId,
                                                                                                $modelClassName,
                                                                                                User $user)
        {
            assert('is_int($modelId)');
            assert('is_string($modelClassName)');

            $updateStartTimestamp = time();
            $updateDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($updateStartTimestamp);
            $tableName = static::getSubscriptionTableName($modelClassName);
            $sql = "DELETE FROM $tableName WHERE
                    userid = '" . $user->id . "'
                    AND modelid = '{$modelId}'
                    AND subscriptiontype='" . self::TYPE_DELETE . "'";
            ZurmoRedBean::exec($sql);

            $sql = "SELECT * FROM $tableName WHERE
                    userid = '" . $user->id . "'
                    AND modelid = '{$modelId}'
                    AND subscriptiontype='" . self::TYPE_ADD . "'";
            $results = ZurmoRedBean::getAll($sql);

            if (!is_array($results) || empty($results))
            {
                $sql = "INSERT INTO $tableName VALUES
                    (null, '" . $user->id . "', '{$modelId}', '{$updateDateTime}', '" . self::TYPE_ADD . "')";
                ZurmoRedBean::exec($sql);
            }
        }

        /**
         * Delete model read permission subscription table by model id, class name and user
         * @param int $modelId
         * @param string $modelClassName
         * @param User $user
         */
        public static function deleteModelFromReadSubscriptionTableByModelIdAndModelClassNameAndUser($modelId,
                                                                                                     $modelClassName,
                                                                                                     User $user)
        {
            assert('is_int($modelId)');
            assert('is_string($modelClassName)');

            $updateStartTimestamp = time();
            $updateDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($updateStartTimestamp);
            $tableName = static::getSubscriptionTableName($modelClassName);
            $sql = "DELETE FROM $tableName WHERE
                    userid = '" . $user->id . "'
                    AND modelid = '{$modelId}'
                    AND subscriptiontype='" . self::TYPE_ADD . "'";
            ZurmoRedBean::exec($sql);

            $sql = "SELECT * FROM $tableName WHERE
                    userid = '" . $user->id . "'
                    AND modelid = '{$modelId}'
                    AND subscriptiontype='" . self::TYPE_DELETE . "'";
            $results = ZurmoRedBean::getAll($sql);

            if (!is_array($results) || empty($results))
            {
                $sql = "INSERT INTO $tableName VALUES
                        (null, '" . $user->id . "', '{$modelId}', '{$updateDateTime}', '" . self::TYPE_DELETE . "')";
                ZurmoRedBean::exec($sql);
            }
        }

        /**
         * @param int $modelId
         * @param string $modelClassName
         */
        protected static function deleteOnlyModelToReadSubscriptionTableByModelIdAndModelClassName($modelId,
                                                                                                   $modelClassName)
        {
            assert('is_int($modelId)');
            assert('is_string($modelClassName)');

            $updateStartTimestamp = time();
            $updateDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($updateStartTimestamp);
            $tableName = static::getSubscriptionTableName($modelClassName);
            $sql = "UPDATE $tableName set
                    subscriptiontype='" . self::TYPE_DELETE . "', modifieddatetime='" . $updateDateTime . "'
                    WHERE
                    modelid = '{$modelId}'
                    AND subscriptiontype='" . self::TYPE_ADD . "'";
            ZurmoRedBean::exec($sql);
        }

        /**
         * Call this function when changing model owner
         * This function first mark model as deleted for all users where type was added, and change timestamp,
         * then it add new record for new owner.
         * @param int $modelId
         * @param string $modelClassName
         * @param User $user
         */
        public static function changeOwnerOfModelInReadSubscriptionTableByModelIdAndModelClassNameAndUser($modelId,
                                                                                                          $modelClassName,
                                                                                                          User $user)
        {
            static::deleteOnlyModelToReadSubscriptionTableByModelIdAndModelClassName($modelId, $modelClassName);
            static::addModelToReadSubscriptionTableByModelIdAndModelClassNameAndUser($modelId, $modelClassName, $user);
        }

        /**
         * Get all added or deleted models from read permission subscription table
         * @param $serviceName
         * @param $modelClassName
         * @param $lastUpdateTimestamp
         * @param $type
         * @param $user
         * @param $checkIfModelCreationApiSyncUtilIsNull
         * @return array
         */
        public static function getAddedOrDeletedModelsFromReadSubscriptionTable($serviceName, $modelClassName,
                                                                                $lastUpdateTimestamp, $type, $user,
                                                                                $checkIfModelCreationApiSyncUtilIsNull = true)
        {
            assert('$user instanceof User');
            $tableName = static::getSubscriptionTableName($modelClassName);
            $dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($lastUpdateTimestamp);
            if ($type == ReadPermissionsSubscriptionUtil::TYPE_DELETE)
            {
                $sql = "SELECT {$tableName}.modelid FROM $tableName" .
                    " WHERE {$tableName}.userid = " . $user->id .
                    " AND {$tableName}.subscriptiontype = " . $type .
                    " AND {$tableName}.modifieddatetime >= '" . $dateTime . "'" .
                    " order by {$tableName}.modifieddatetime ASC, {$tableName}.modelid  ASC";
            }
            else
            {
                $sql = "SELECT {$tableName}.modelid FROM $tableName" .
                    " left join " . ModelCreationApiSyncUtil::TABLE_NAME . " isct " .
                    " on isct.modelid = {$tableName}.modelid" .
                    " AND isct.servicename = '" . $serviceName . "'" .
                    " AND isct.modelclassname = '" . $modelClassName . "'" .
                    " WHERE {$tableName}.userid = " . $user->id .
                    " AND {$tableName}.subscriptiontype = " . $type .
                    " AND {$tableName}.modifieddatetime >= '" . $dateTime . "'";
                if ($checkIfModelCreationApiSyncUtilIsNull)
                {
                    $sql .= " AND isct.modelid is null";
                }
                $sql .= " order by {$tableName}.modifieddatetime ASC, {$tableName}.modelid  ASC";
            }
            $modelIdsRows = ZurmoRedBean::getAll($sql);
            $modelIds = array();
            if (is_array($modelIdsRows) && !empty($modelIdsRows))
            {
                foreach ($modelIdsRows as $modelIdRow)
                {
                    $modelIds[] = $modelIdRow['modelid'];
                }
            }
            return $modelIds;
        }

        /**
         * Get all added model names and ids from read permission subscription table
         * @param $serviceName
         * @param $modelClassName
         * @param $lastUpdateTimestamp
         * @param $user
         * @return array
         */
        public static function getAddedModelNamesAndIdsFromReadSubscriptionTable($serviceName,
                                                                                 $modelClassName,
                                                                                 $lastUpdateTimestamp,
                                                                                 $user)
        {
            assert('$user instanceof User');
            $tableName = self::getSubscriptionTableName($modelClassName);
            $modelTableName = $modelClassName::getTableName();
            $dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($lastUpdateTimestamp);
            $sql = "SELECT {$tableName}.modelid, {$modelTableName}.name FROM $tableName" .
                " left join " . ModelCreationApiSyncUtil::TABLE_NAME . " isct " .
                " on isct.modelid = {$tableName}.modelid" .
                " AND isct.servicename = '" . $serviceName . "'" .
                " AND isct.modelclassname = '" . $modelClassName . "'" .
                " left join {$modelTableName} on {$modelTableName}.id = {$tableName}.modelid" .
                " WHERE {$tableName}.userid = " . $user->id .
                " AND {$tableName}.subscriptiontype = " . self::TYPE_ADD .
                " AND {$tableName}.modifieddatetime >= '" . $dateTime . "'" .
                " AND isct.modelid is null" .
                " order by {$tableName}.modifieddatetime ASC, {$tableName}.modelid  ASC";
            $modelIdsRows = ZurmoRedBean::getAll($sql);
            $modelIds = array();
            if (is_array($modelIdsRows) && !empty($modelIdsRows))
            {
                foreach ($modelIdsRows as $modelIdRow)
                {
                    $modelIds[$modelIdRow['modelid']] = $modelIdRow['name'];
                }
            }
            return $modelIds;
        }

        /**
         * Get details about read subscription update details from configuration(global metadata)
         * @return array
         */
        public static function getReadSubscriptionUpdateDetails()
        {
            $readSubscriptionUpdateDetails = ZurmoConfigurationUtil::getByModuleName('ZurmoModule',
                'readSubscriptionUpdateDetails');
            return $readSubscriptionUpdateDetails;
        }

        /**
         * Set read subscription update details from configuration(global metadata)
         * @param array $readSubscriptionUpdateDetails
         */
        public static function setReadSubscriptionUpdateDetails($readSubscriptionUpdateDetails)
        {
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'readSubscriptionUpdateDetails',
                $readSubscriptionUpdateDetails);
        }

        /**
         * Get last read permission update timestamp in  read subscription update details(stored in configuration)
         * @return int
         */
        public static function getLastReadPermissionUpdateTimestamp()
        {
            $readSubscriptionUpdateDetails = static::getReadSubscriptionUpdateDetails();
            if (isset($readSubscriptionUpdateDetails['lastReadPermissionUpdateTimestamp']))
            {
                return $readSubscriptionUpdateDetails['lastReadPermissionUpdateTimestamp'];
            }
            else
            {
                return 0;
            }
        }

        /**
         * Set last read permission update timestamp in  read subscription update details(stored in configuration)
         * @param int $lastReadPermissionUpdateTimestamp
         */
        public static function setTimeReadPermissionUpdateTimestamp($lastReadPermissionUpdateTimestamp)
        {
            $readSubscriptionUpdateDetails = static::getReadSubscriptionUpdateDetails();
            $readSubscriptionUpdateDetails['lastReadPermissionUpdateTimestamp'] = $lastReadPermissionUpdateTimestamp;
            static::setReadSubscriptionUpdateDetails($readSubscriptionUpdateDetails);
        }

        /**
         * Set status of ReadPermissionSubscription update(stored in configuration)
         * @param int $status
         */
        public static function setReadPermissionUpdateStatus($status)
        {
            $readSubscriptionUpdateDetails = static::getReadSubscriptionUpdateDetails();
            $readSubscriptionUpdateDetails['status'] = $status;
            static::setReadSubscriptionUpdateDetails($readSubscriptionUpdateDetails);
        }

        /**
         * Get status of ReadPermissionSubscription update(stored in configuration)
         * @return int
         */
        public static function getReadPermissionUpdateStatus()
        {
            $readSubscriptionUpdateDetails = static::getReadSubscriptionUpdateDetails();
            if (isset($readSubscriptionUpdateDetails['status']))
            {
                return $readSubscriptionUpdateDetails['status'];
            }
            else
            {
                return static::STATUS_STARTED;
            }
        }

        /**
         * Check if ReadPermissionSubscription
         * @return bool
         */
        public static function isReadPermissionSubscriptionUpdateCompleted()
        {
            if (static::getReadPermissionUpdateStatus() == static::STATUS_COMPLETED)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }
?>