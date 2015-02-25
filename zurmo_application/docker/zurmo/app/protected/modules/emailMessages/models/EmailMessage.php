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
     * Model for storing an email message.
     */
    class EmailMessage extends OwnedSecurableItem implements MashableActivityInterface
    {
        public static function getMashableActivityRulesType()
        {
            return 'EmailMessage';
        }

        public static function getAllByFolderType($type)
        {
            return static::getByFolderType($type, null);
        }

        public static function getByFolderType($type, $count)
        {
            assert('is_string($type)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'folder',
                    'relatedAttributeName' => 'type',
                    'operatorType'         => 'equals',
                    'value'                => $type,
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('EmailMessage');
            $where = RedBeanModelDataProvider::makeWhere('EmailMessage', $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, $count, $where, null);
        }

        public function __toString()
        {
            if (trim($this->subject) == '')
            {
                return Zurmo::t('Core', '(Unnamed)');
            }
            return $this->subject;
        }

        /**
         * @see OwnedSecurableItem::getModifiedSignalAttribute()
         * @return string
         */
        protected static function getModifiedSignalAttribute()
        {
            return 'subject';
        }

        public static function getModuleClassName()
        {
            return 'EmailMessagesModule';
        }

        public static function canSaveMetadata()
        {
            return false;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'subject',
                    //'type',
                    'sendAttempts',
                    'sentDateTime',
                    'sendOnDateTime',
                    'headers',
                ),
                'relations' => array(
                    'folder'        => array(static::HAS_ONE,  'EmailFolder', static::NOT_OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'folder'),
                    'content'       => array(static::HAS_ONE,  'EmailMessageContent',    static::OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'content'),
                    'files'         => array(static::HAS_MANY, 'FileModel',              static::OWNED,
                                                static::LINK_TYPE_POLYMORPHIC, 'relatedModel'),
                    'sender'        => array(static::HAS_ONE,  'EmailMessageSender',     static::OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'sender'),
                    'recipients'    => array(static::HAS_MANY, 'EmailMessageRecipient',  static::OWNED),
                    'error'         => array(static::HAS_ONE,  'EmailMessageSendError' , static::OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'error'),
                    'account'       => array(static::HAS_ONE,  'EmailAccount', static::NOT_OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'account'),
                ),
                'rules' => array(
                    array('subject',         'required'),
                    array('subject',         'type',    'type' => 'string'),
                    array('subject',         'length',  'min'  => 1, 'max' => 255),
                    array('folder',          'required'),
                    array('sender',          'required'),
                    array('sendAttempts',    'type',    'type' => 'integer'),
                    array('sendAttempts',    'numerical', 'min' => 0),
                    array('sentDateTime',    'type', 'type' => 'datetime'),
                    array('sendOnDateTime',  'type', 'type' => 'datetime'),
                    array('headers',         'type', 'type' => 'string'),
                ),
                'elements' => array(
                    'sentDateTime'  => 'DateTime',
                    'files'         => 'Files',
                )
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        public static function hasRelatedItems()
        {
            return true;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'account'      => Zurmo::t('EmailMessagesModule', 'Email Account',  array(), null, $language),
                    'content'      => Zurmo::t('Core', 'Content',  array(), null, $language),
                    'error'        => Zurmo::t('Core',                'Error',  array(), null, $language),
                    'folder'       => Zurmo::t('ZurmoModule',         'Folder',  array(), null, $language),
                    'files'        => Zurmo::t('ZurmoModule',         'Files',  array(), null, $language),
                    'recipients'   => Zurmo::t('EmailMessagesModule', 'Recipients',  array(), null, $language),
                    'sender'       => Zurmo::t('EmailMessagesModule', 'Sender',  array(), null, $language),
                    'sendAttempts' => Zurmo::t('EmailMessagesModule', 'Send Attempts',  array(), null, $language),
                    'sentDateTime' => Zurmo::t('EmailMessagesModule', 'Sent Date Time',  array(), null, $language),
                    'subject'      => Zurmo::t('Core', 'Subject',  array(), null, $language),
                    //'type'         => Zurmo::t('Core',                'Type',  array(), null, $language),
                )
            );
        }

        /**
         * Returns the display name for the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('EmailMessagesModule', 'Email', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('ZurmoModule', 'Emails', array(), null, $language);
        }

        public function hasSendError()
        {
            return !($this->error == null || $this->error->id < 0);
        }
    }
?>