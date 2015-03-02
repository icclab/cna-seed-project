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
     * A class for creating notification models.
     */
    class Notification extends Item implements MashableInboxInterface
    {
        public static function getMashableInboxRulesType()
        {
            return 'Notification';
        }

        public function __toString()
        {
            if ($this->type == null)
            {
                return null;
            }
            $notificationRulesClassName = $this->type . 'NotificationRules';
            if (@class_exists($notificationRulesClassName))
            {
                return $notificationRulesClassName::getDisplayName();
            }
            else
            {
                return Zurmo::t('Core', '(Unnamed)');
            }
        }

        /**
         * Given a type and a user, find out how many existing notifications exist for that user
         * and that type.
         * @param string $type
         * @param User $user
         * @return int
         */
        public static function getCountByTypeAndUser($type, User $user)
        {
            $models = self::getByTypeAndUser($type, $user);
            return count($models);
        }

        /**
         * @param $type
         * @param User $user
         * @return Array of models
         */
        public static function getByTypeAndUser($type, User $user)
        {
            assert('is_string($type) && $type != ""');
            assert('$user->id > 0');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'type',
                    'operatorType'         => 'equals',
                    'value'                => $type,
                ),
                2 => array(
                    'attributeName'        => 'owner',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $user->id,
                ),
            );
            $searchAttributeData['structure'] = '1 and 2';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Notification');
            $where = RedBeanModelDataProvider::makeWhere('Notification', $searchAttributeData, $joinTablesAdapter);
            $models = self::getSubset($joinTablesAdapter, null, null, $where, null);
            return $models;
        }

        /**
         * Get all notifications based on notificationMessage id
         * @param $notificationMessageId
         * @return Array of models
         */
        public static function getByNotificationMessageId($notificationMessageId)
        {
            assert('is_int($notificationMessageId)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'notificationMessage',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $notificationMessageId,
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Notification');
            $where = RedBeanModelDataProvider::makeWhere('Notification', $searchAttributeData, $joinTablesAdapter);
            $models = self::getSubset($joinTablesAdapter, null, null, $where, null);
            return $models;
        }

        /**
         * Delete all notifications and related NotificationMessages by type and user
         * @param $type
         * @param User $user
         */
        public static function deleteByTypeAndUser($type, User $user)
        {
            $notifications = static::getByTypeAndUser($type, $user);
            if (!empty($notifications))
            {
                foreach ($notifications as $notification)
                {
                    static::deleteNotificationAndRelatedNotificationMessage($notification);
                }
            }
        }

        /**
         * Delete notification and related notificationMessage, if there are no relations from other notifications to
         * this notificationMessage
         * @param Notification $notification
         */
        public static function deleteNotificationAndRelatedNotificationMessage(Notification $notification)
        {
            try
            {
                if (isset($notification->notificationMessage) && $notification->notificationMessage instanceOf NotificationMessage)
                {
                    $notificationMessageNotifications = Notification::getByNotificationMessageId($notification->notificationMessage->id);
                    if (count($notificationMessageNotifications) == 1)
                    {
                        $notification->notificationMessage->delete();
                    }
                }
            }
            catch (NotFoundException $e)
            {
            }
            $notification->delete();
        }

        public static function getCountByUser(User $user)
        {
            assert('$user->id > 0');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'owner',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $user->id,
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Notification');
            $where  = RedBeanModelDataProvider::makeWhere('Notification', $searchAttributeData, $joinTablesAdapter);
            return self::getCount($joinTablesAdapter, $where, null, true);
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'type',
                    'ownerHasReadLatest',
                ),
                'relations' => array(
                    'notificationMessage' => array(static::HAS_ONE,  'NotificationMessage', static::NOT_OWNED),
                    'owner' =>               array(static::HAS_ONE, 'User', static::NOT_OWNED,
                                                   static::LINK_TYPE_SPECIFIC, 'owner'),
                ),
                'rules' => array(
                    array('owner',                  'required'),
                    array('type',                   'required'),
                    array('type',                   'type',    'type' => 'string'),
                    array('type',                   'length',  'min'  => 1, 'max' => 64),
                    array('ownerHasReadLatest',     'boolean'),
                ),
                'elements' => array(
                    'owner' => 'User',
                ),
                'defaultSortAttribute' => null,
                'noAudit' => array(
                    'owner',
                    'type',
                    'ownerHasReadLatest',
                )
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getModuleClassName()
        {
            return 'NotificationsModule';
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'ownerHasReadLatest'  => Zurmo::t('NotificationsModule', 'Owner Has Read Latest',  array(), null, $language),
                    'notificationMessage' => Zurmo::t('NotificationsModule', 'Notification Message',  array(), null, $language),
                    'owner'               => Zurmo::t('ZurmoModule', 'Owner',  array(), null, $language),
                    'type'                => Zurmo::t('Core', 'Type',  array(), null, $language),
                )
            );
        }
    }
?>
