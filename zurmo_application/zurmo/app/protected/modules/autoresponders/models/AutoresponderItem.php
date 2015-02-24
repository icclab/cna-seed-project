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

    class AutoresponderItem extends OwnedModel
    {
        public static function getModuleClassName()
        {
            return 'AutorespondersModule';
        }

        /**
         * Returns the display name for the model class.
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('AutorespondersModule', 'Autoresponder Item', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('AutorespondersModule', 'Autoresponder Items', array(), null, $language);
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'processDateTime',
                    'processed',
                ),
                'relations' => array(
                    'contact'                     => array(static::HAS_ONE, 'Contact', static::NOT_OWNED),
                    'emailMessage'                => array(static::HAS_ONE, 'EmailMessage'),
                    'autoresponderItemActivities' => array(static::HAS_MANY, 'AutoresponderItemActivity'),
                    'autoresponder'               => array(static::HAS_ONE, 'Autoresponder', static::NOT_OWNED),
                ),
                'rules' => array(
                    array('processDateTime',        'required'),
                    array('processDateTime',        'type', 'type' => 'datetime'),
                    array('processed',              'boolean'),
                    array('processed',              'default', 'value' => false),
                ),
                'elements' => array(
                ),
                'defaultSortAttribute' => 'processDateTime',
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function getByProcessed($processed, $pageSize = null)
        {
            assert('is_int($processed)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'processed',
                    'operatorType'              => 'equals',
                    'value'                     => intval($processed),
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, $pageSize, $where, 'processDateTime');
        }

        public static function getByProcessedAndProcessDateTime($processed, $timestamp = null, $pageSize = null,
                                                                $offset = 0, $inPast = true)
        {
            assert('is_int($processed)');
            assert('is_int($offset)');
            assert('is_bool($inPast)');
            if (empty($timestamp))
            {
                $timestamp = time();
            }
            $dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($timestamp);
            if ($inPast)
            {
                $processDateTimeOperator = 'lessThan';
            }
            else
            {
                $processDateTimeOperator = 'greaterThan';
            }
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'processed',
                    'operatorType'              => 'equals',
                    'value'                     => intval($processed),
                ),
                2 => array(
                    'attributeName'             => 'processDateTime',
                    'operatorType'              => $processDateTimeOperator,
                    'value'                     => $dateTime,
                ),
            );
            $searchAttributeData['structure'] = '(1 and 2)';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, $offset, $pageSize, $where, 'processDateTime');
        }

        /**
         * @param int $processed
         * @param int $autoresponderId
         * @param null|int  $pageSize
          */
        public static function getByProcessedAndAutoresponderId($processed, $autoresponderId, $pageSize = null)
        {
            assert('is_int($processed)');
            assert('is_int($autoresponderId) || is_string($autoresponderId)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'processed',
                    'operatorType'              => 'equals',
                    'value'                     => intval($processed),
                ),
                2 => array(
                    'attributeName'             => 'autoresponder',
                    'relatedAttributeName'      => 'id',
                    'operatorType'              => 'equals',
                    'value'                     => $autoresponderId,
                ),
            );
            $searchAttributeData['structure'] = '(1 and 2)';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, $pageSize, $where, 'processDateTime');
        }

        /**
         * @param int $processed
         * @param int $autoresponderId
         * @param null $timestamp
         * @param null|int $pageSize
         */
        public static function getByProcessedAndAutoresponderIdWithProcessDateTime($processed, $autoresponderId, $timestamp = null, $pageSize = null)
        {
            if (empty($timestamp))
            {
                $timestamp = time();
            }
            $dateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($timestamp);
            assert('is_int($processed)');
            assert('is_int($autoresponderId) || is_string($autoresponderId)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'processed',
                    'operatorType'              => 'equals',
                    'value'                     => intval($processed),
                ),
                2 => array(
                    'attributeName'             => 'processDateTime',
                    'operatorType'              => 'lessThan',
                    'value'                     => $dateTime,
                ),
                3 => array(
                    'attributeName'             => 'autoresponder',
                    'relatedAttributeName'      => 'id',
                    'operatorType'              => 'equals',
                    'value'                     => $autoresponderId,
                ),
            );
            $searchAttributeData['structure'] = '(1 and 2 and 3)';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, $pageSize, $where, 'processDateTime');
        }

        public static function registerAutoresponderItemsByAutoresponderOperation($operation, $marketingListId, $contact)
        {
            $autoresponders = Autoresponder::getByOperationTypeAndMarketingListId($operation, $marketingListId);
            $now = time();
            foreach ($autoresponders as $autoresponder)
            {
                $processTimestamp = $autoresponder->resolveNewTimeStampForDuration($now);
                $processDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($processTimestamp);
                $processed = false;
                static::addNewItem($processed, $processDateTime, $contact, $autoresponder);
            }
        }

        public static function addNewItem($processed, $processDateTime, $contact, $autoresponder)
        {
            $autoresponderItem                              = new self;
            $autoresponderItem->processed                   = $processed;
            $autoresponderItem->processDateTime             = $processDateTime;
            $autoresponderItem->contact                     = $contact;
            $autoresponderItem->autoresponder               = $autoresponder;
            $saved                                          = $autoresponderItem->unrestrictedSave();
            assert('$saved');
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            return $saved;
        }

        /**
         * Special handling to set 'isNewModel'. This is needed to properly set the jobQueue
         * //todo: move backwards into OwnedModel if that is ok generally.
         * @see RedBeanModel::beforeSave()
         */
        protected function beforeSave()
        {
            $this->isNewModel = $this->id < 0;
            return parent::beforeSave();
        }

        protected function afterSave()
        {
            Yii::app()->jobQueue->resolveToAddJobTypeByModelByDateTimeAttribute($this, 'processDateTime',
                                    'AutoresponderQueueMessagesInOutbox');
            parent::afterSave();
            $this->originalAttributeValues = array();
            $this->isNewModel = false; //reset.
        }
    }
?>