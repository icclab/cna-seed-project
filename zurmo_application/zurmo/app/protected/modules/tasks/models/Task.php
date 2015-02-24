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

    class Task extends MashableActivity
    {
        /*
         * Constants for task status
         */
        const STATUS_NEW                   = 1;

        const STATUS_IN_PROGRESS           = 2;

        const STATUS_AWAITING_ACCEPTANCE   = 3;

        const STATUS_REJECTED              = 4;

        const STATUS_COMPLETED             = 5;

        /**
         * @return string
         */
        public function __toString()
        {
            try
            {
                if (trim($this->name) == '')
                {
                    return Zurmo::t('Core', '(Unnamed)');
                }
                return $this->name;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        /**
         * Gets module class name
         * @return string
         */
        public static function getModuleClassName()
        {
            return 'TasksModule';
        }

        /**
         * @return bool
         */
        public static function canSaveMetadata()
        {
            return true;
        }

        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'completedDateTime',
                    'completed',
                    'description',
                    'dueDateTime',
                    'name',
                    'status'
                ),
                'relations' => array(
                    'requestedByUser'           => array(static::HAS_ONE, 'User', static::NOT_OWNED,
                                                        static::LINK_TYPE_SPECIFIC, 'requestedByUser'),
                    'comments'                  => array(static::HAS_MANY, 'Comment', static::OWNED,
                                                        static::LINK_TYPE_POLYMORPHIC, 'relatedModel'),
                    'checkListItems'            => array(static::HAS_MANY, 'TaskCheckListItem', static::OWNED),
                    'notificationSubscribers'   => array(static::HAS_MANY, 'NotificationSubscriber', static::OWNED),
                    'files'                     => array(static::HAS_MANY, 'FileModel', static::OWNED,
                                                        static::LINK_TYPE_POLYMORPHIC, 'relatedModel'),
                    'project'                   => array(static::HAS_ONE, 'Project'),
                ),
                'rules' => array(
                    array('completedDateTime', 'type', 'type' => 'datetime'),
                    array('completed',         'boolean'),
                    array('dueDateTime',       'type', 'type' => 'datetime'),
                    array('description',       'type',    'type' => 'string'),
                    array('name',              'required'),
                    array('name',              'type',    'type' => 'string'),
                    array('name',              'length',  'min'  => 1, 'max' => 128),
                    array('status',            'type', 'type' => 'integer'),
                    array('status',            'default', 'value' => Task::STATUS_NEW),
                    array('status',            'required'),
                ),
                'elements' => array(
                    'completedDateTime' => 'DateTime',
                    'description'       => 'TextArea',
                    'dueDateTime'       => 'DateTime',
                    'requestedByUser'   => 'User',
                    'comment'           => 'Comment',
                    'checkListItem'     => 'TaskCheckListItem',
                    'files'             => 'Files',
                    'project'           => 'Project',
                    'status'            => 'TaskStatusDropDown'
                ),
                'defaultSortAttribute' => 'name',
                'noAudit' => array(
                    'description'
                ),
            );
            return $metadata;
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
            // Even though setting the requestedByUser is not technically
            // a default in the sense of a Yii default rule,
            // if true the requestedByUser is not set because blank models
            // are used for searching mass updating.
            if ($bean ===  null && $setDefaults)
            {
                $currentUser = Yii::app()->user->userModel;
                if (!$currentUser instanceof User)
                {
                    throw new NoCurrentUserSecurityException();
                }
                AuditUtil::saveOriginalAttributeValue($this, 'requestedByUser', $currentUser);
                $this->unrestrictedSet('requestedByUser', $currentUser);
            }
        }

        /**
         * @param $language
         * @return array
         */
        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'completedDateTime' => Zurmo::t('TasksModule', 'Completed On', array(), null, $language),
                    'completed'         => Zurmo::t('Core', 'Completed',  array(), null, $language),
                    'description'       => Zurmo::t('ZurmoModule', 'Description',  array(), null, $language),
                    'dueDateTime'       => Zurmo::t('TasksModule', 'Due On',       array(), null, $language),
                    'name'              => Zurmo::t('Core', 'Name',  array(), null, $language),
                    'status'            => Zurmo::t('ZurmoModule', 'Status',  array(), null, $language),
                    'requestedByUser'   => Zurmo::t('TasksModule', 'Requested By User',  array(), null, $language),
                    'files'             => Zurmo::t('ZurmoModule', 'Files',  array(), null, $language),
                )
            );
        }

        /**
         * @return bool
         */
        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * @return string
         */
        public static function getMashableActivityRulesType()
        {
            return 'Task';
        }

        /**
         * @return bool
         */
        protected function beforeSave()
        {
            if (parent::beforeSave())
            {
                $this->resolveAndSetDefaultSubscribers();
                return true;
            }
            else
            {
                return false;
            }
        }

        public function __set($attributeName, $value)
        {
            parent::__set($attributeName, $value);
            if ($attributeName == 'status')
            {
                $this->resolveStatusAndSetCompletedFields();
            }
        }

        /**
         * @return bool
         */
        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        /**
         * @return string
         */
        public static function getGamificationRulesType()
        {
            return 'TaskGamification';
        }

        /**
         * @return array of status values and labels
         */
        public static function getStatusDropDownArray()
        {
            return array(
                self::STATUS_NEW                 => Zurmo::t('Core', 'New'),
                self::STATUS_IN_PROGRESS         => Zurmo::t('Core', 'In Progress'),
                self::STATUS_AWAITING_ACCEPTANCE => Zurmo::t('Core', 'Awaiting Acceptance'),
                self::STATUS_REJECTED            => Zurmo::t('Core', 'Rejected'),
                self::STATUS_COMPLETED           => Zurmo::t('Core', 'Completed'),
            );
        }

        /**
         * Gets the display name for the status
         * @param int $status
         */
        public static function getStatusDisplayName($status)
        {
            $statusArray = self::getStatusDropDownArray();
            if (array_key_exists($status, $statusArray))
            {
                return $statusArray[$status];
            }
            return Zurmo::t('Core', '(None)');
        }

        /**
         * @return bool
         */
        public static function hasReadPermissionsSubscriptionOptimization()
        {
            return true;
        }

        public function doNotificationSubscribersContainPerson(Item $item)
        {
            foreach ($this->notificationSubscribers as $notificationSubscriber)
            {
                if ($notificationSubscriber->person->getClassId('Item') == $item->getClassId('Item'))
                {
                    return true;
                }
            }
            return false;
        }

        protected function afterSave()
        {
            if ($this->getScenario() != 'importModel')
            {
                $this->processNotificationsToBeSent();
            }
            if ($this->getScenario() != 'kanbanViewButtonClick' && $this->getScenario() != 'kanbanViewDrag')
            {
                TasksUtil::checkKanbanTypeByStatusAndUpdateIfRequired($this);
            }
            parent::afterSave();
        }

        /**
         *  Process notifications on modal details screen
         */
        private function processNotificationsToBeSent()
        {
            if (array_key_exists('status', $this->originalAttributeValues))
            {
                if ($this->status == Task::STATUS_AWAITING_ACCEPTANCE &&
                   $this->requestedByUser->id != Yii::app()->user->userModel->id)
                {
                    TasksNotificationUtil::submitTaskNotificationMessage($this,
                        TasksNotificationUtil::TASK_STATUS_BECOMES_AWAITING_ACCEPTANCE,
                        Yii::app()->user->userModel);
                }
                elseif ($this->status == Task::STATUS_REJECTED &&
                       $this->owner->id != Yii::app()->user->userModel->id)
                {
                    TasksNotificationUtil::submitTaskNotificationMessage($this,
                        TasksNotificationUtil::TASK_STATUS_BECOMES_REJECTED,
                        Yii::app()->user->userModel);
                }
                elseif ($this->status == Task::STATUS_COMPLETED)
                {
                    TasksNotificationUtil::submitTaskNotificationMessage($this,
                        TasksNotificationUtil::TASK_STATUS_BECOMES_COMPLETED,
                        Yii::app()->user->userModel);
                }
            }
            if ($this->isNewModel)
            {
                if ($this->owner->id != $this->requestedByUser->id && $this->owner->id != Yii::app()->user->userModel->id)
                {
                    TasksNotificationUtil::submitTaskNotificationMessage($this,
                        TasksNotificationUtil::TASK_NEW);
                }
            }
            elseif (array_key_exists('owner', $this->originalAttributeValues) &&
               $this->owner->id != Yii::app()->user->userModel->id)
            {
                TasksNotificationUtil::submitTaskNotificationMessage($this,
                                                         TasksNotificationUtil::TASK_OWNER_CHANGE);
            }
        }

        /**
         * Resolve and set default subscribers
         */
        protected function resolveAndSetDefaultSubscribers()
        {
            //Add requested by user as default subscriber
            if ($this->requestedByUser->id > 0)
            {
                TasksUtil::addSubscriber($this->requestedByUser, $this, false);
            }
            TasksUtil::addSubscriber($this->owner, $this, false);
        }

        /**
         * Resolve status and set completed fields
         */
        protected function resolveStatusAndSetCompletedFields()
        {
            if ($this->completed != true && $this->status != Task::STATUS_COMPLETED)
            {
                $this->completed = false;
            }
            else
            {
                $this->completed = true;
            }

            if ($this->completed == true)
            {
                if ($this->completedDateTime == null)
                {
                    $this->completedDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                }
                $this->unrestrictedSet('latestDateTime', $this->completedDateTime);
            }
        }
    }
?>