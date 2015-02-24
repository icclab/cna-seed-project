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

    class Campaign extends OwnedSecurableItem
    {
        const STATUS_PAUSED                     = 1;

        const STATUS_ACTIVE                     = 2;

        const STATUS_PROCESSING                 = 3;

        const STATUS_COMPLETED                  = 4;

        public static function getByName($name)
        {
            return self::getByNameOrEquivalent('name', $name);
        }

        public static function getModuleClassName()
        {
            return 'CampaignsModule';
        }

        public static function getStatusDropDownArray()
        {
            return array(
                static::STATUS_PAUSED       => Zurmo::t('CampaignsModule', 'Paused'),
                static::STATUS_ACTIVE       => Zurmo::t('Core', 'Active'),
                static::STATUS_PROCESSING   => Zurmo::t('Core', 'Processing'),
                static::STATUS_COMPLETED    => Zurmo::t('Core', 'Completed'),
            );
        }

        public function __toString()
        {
            try
            {
                if (trim($this->name) == '')
                {
                    return Yii::t('Core', '(Unnamed)');
                }
                return $this->name;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        /**
         * Returns the display name for the model class.
         * @param null $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('CampaignsModule', 'Campaign', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('CampaignsModule', 'Campaigns', array(), null, $language);
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getByStatus($status, $pageSize = null)
        {
            assert('is_int($status)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'status',
                    'operatorType'              => 'equals',
                    'value'                     => intval($status),
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, $pageSize, $where, null);
        }

        public static function getByStatusAndSendingTime($status, $sendingTimestamp = null, $pageSize = null, $offset = null, $inPast = true)
        {
            assert('is_int($status)');
            assert('$offset  === null || is_int($offset)');
            assert('is_bool($inPast)');
            if (empty($sendingTimestamp))
            {
                $sendingTimestamp = time();
            }
            $sendOnDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime($sendingTimestamp);
            if ($inPast)
            {
                $sendOnDateTimeOperator = 'lessThan';
            }
            else
            {
                $sendOnDateTimeOperator = 'greaterThan';
            }
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'status',
                    'operatorType'              => 'equals',
                    'value'                     => intval($status),
                ),
                2 => array(
                    'attributeName'             => 'sendOnDateTime',
                    'operatorType'              => $sendOnDateTimeOperator,
                    'value'                     => $sendOnDateTime,
                ),
            );
            $searchAttributeData['structure'] = '(1 and 2)';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, $offset, $pageSize, $where, null);
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                    'subject',
                    'status',
                    'sendOnDateTime',
                    'supportsRichText',
                    'enableTracking',
                    'htmlContent',
                    'textContent',
                    'fromName',
                    'fromAddress',
                ),
                'rules' => array(
                    array('name',                   'required'),
                    array('name',                   'type',    'type' => 'string'),
                    array('name',                   'length',  'min'  => 1, 'max' => 64),
                    array('status',                 'required'),
                    array('status',                 'type',    'type' => 'integer'),
                    array('status',                 'default', 'value' => static::STATUS_ACTIVE),
                    array('supportsRichText',       'required'),
                    array('supportsRichText',       'boolean'),
                    array('sendOnDateTime',         'required'),
                    array('sendOnDateTime',         'type', 'type' => 'datetime'),
                    array('sendOnDateTime',         'dateTimeDefault', 'value' => DateTimeCalculatorUtil::NOW),
                    array('fromName',                'required'),
                    array('fromName',               'type',    'type' => 'string'),
                    array('fromName',               'length',  'min'  => 1, 'max' => 64),
                    array('fromAddress',            'required'),
                    array('fromAddress',            'type', 'type' => 'string'),
                    array('fromAddress',            'length',  'min'  => 6, 'max' => 64),
                    array('fromAddress',            'email'),
                    array('subject',                'required'),
                    array('subject',                'type',    'type' => 'string'),
                    array('subject',                'length',  'min'  => 1, 'max' => 255),
                    array('htmlContent',            'type',    'type' => 'string'),
                    array('textContent',            'type',    'type' => 'string'),
                    array('htmlContent',            'StripDummyHtmlContentFromOtherwiseEmptyFieldValidator'),
                    array('textContent',            'AtLeastOneContentAreaRequiredValidator', 'except' => 'searchModel'),
                    array('htmlContent',            'CampaignMergeTagsValidator', 'except' => 'searchModel'),
                    array('textContent',            'CampaignMergeTagsValidator', 'except' => 'searchModel'),
                    array('enableTracking',         'boolean'),
                    array('enableTracking',         'default', 'value' => false),
                    array('marketingList',          'required'),
                ),
                'relations' => array(
                    'campaignItems'     => array(static::HAS_MANY, 'CampaignItem'),
                    'marketingList'     => array(static::HAS_ONE, 'MarketingList', static::NOT_OWNED),
                    'files'             => array(static::HAS_MANY,  'FileModel', static::OWNED,
                                                static::LINK_TYPE_POLYMORPHIC, 'relatedModel'),
                ),
                'elements' => array(
                    'marketingList'    => 'MarketingList',
                    'htmlContent'      => 'TextArea',
                    'textContent'      => 'TextArea',
                    'supportsRichText' => 'CheckBox',
                    'enableTracking'   => 'CheckBox',
                    'sendOnDateTime'   => 'DateTime',
                    'status'           => 'CampaignStatus',
                ),
                'defaultSortAttribute' => 'name',
            );
            return $metadata;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'name'                  => Zurmo::t('Core', 'Name', null,  null, $language),
                    'status'                => Zurmo::t('ZurmoModule', 'Status', null,  null, $language),
                    'sendOnDateTime'       => Zurmo::t('CampaignsModule', 'Send On', null,  null, $language),
                    'supportsRichText'      => Zurmo::t('CampaignsModule', 'Supports HTML', null,  null, $language),
                    'fromName'              => Zurmo::t('EmailMessagesModule', 'From Name', null,  null, $language),
                    'fromAddress'           => Zurmo::t('EmailMessagesModule', 'From Address', null,  null, $language),
                    'subject'               => Zurmo::t('Core', 'Subject', null,  null, $language),
                    'htmlContent'           => Zurmo::t('EmailMessagesModule', 'Html Content', null,  null, $language),
                    'textContent'           => Zurmo::t('EmailMessagesModule', 'Text Content', null,  null, $language),
                )
            );
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        public static function getGamificationRulesType()
        {
            return 'CampaignGamification';
        }

        public function getErrors($attributeNameOrNames = null)
        {
            // TODO: @Shoaibi/@Jason: Low: We should have overridden getErrors' original code but this was easier.
            // this was done because marketingList is required but we didn't used to get an error with the right
            // form with parent's getErrors. We had something like "Name cannot be blank." for MarketingList too
            // with this we have: "Marketing List cannot be blank."
            return $this->attributeNameToErrors;
        }

        public function beforeValidate()
        {
            if ($this->getScenario() != 'searchModel')
            {
                $this->validateHtmlOnly();
            }
            return parent::beforeValidate();
        }

        protected function validateHtmlOnly()
        {
            if ($this->supportsRichText && empty($this->htmlContent))
            {
                $this->addError('htmlContent', Zurmo::t('CampaignsModule', 'You choose to support HTML but didn\'t set any HTML content.'));
                return false;
            }
            if (!$this->supportsRichText && empty($this->textContent))
            {
                $this->addError('textContent', Zurmo::t('CampaignsModule', 'You choose not to support HTML but didn\'t set any text content.'));
                return false;
            }
            return true;
        }

        protected function afterSave()
        {
            Yii::app()->jobQueue->resolveToAddJobTypeByModelByDateTimeAttribute($this, 'sendOnDateTime',
                                                                                'CampaignGenerateDueCampaignItems');
            parent::afterSave();
        }

        protected function afterDelete()
        {
            parent::afterDelete();
            $campaignitems = CampaignItem::getByProcessedAndCampaignId(0, $this->id);
            foreach ($campaignitems as $campaignitem)
            {
                ZurmoRedBean::exec("DELETE FROM campaignitemactivity WHERE campaignitem_id = " . $campaignitem->id);
            }
            ZurmoRedBean::exec("DELETE FROM campaignitem WHERE processed = 0 and campaign_id = " . $this->id);
        }
    }
?>