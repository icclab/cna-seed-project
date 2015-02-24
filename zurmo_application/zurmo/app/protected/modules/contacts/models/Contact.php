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

    class Contact extends Person implements StarredInterface
    {
        public static function getByName($name)
        {
            return ZurmoModelSearch::getModelsByFullName('Contact', $name);
        }

        protected static function translatedAttributeLabels($language)
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'account'          => Zurmo::t('AccountsModule', 'AccountsModuleSingularLabel',    $params, null, $language),
                    'companyName'      => Zurmo::t('ContactsModule', 'Company Name',  array(), null, $language),
                    'description'      => Zurmo::t('ZurmoModule', 'Description',  array(), null, $language),
                    'industry'         => Zurmo::t('ZurmoModule', 'Industry',  array(), null, $language),
                    'latestActivityDateTime' => Zurmo::t('ZurmoModule', 'Latest Activity Date Time', array(), null, $language),
                    'meetings'         => Zurmo::t('MeetingsModule', 'MeetingsModulePluralLabel', $params, null, $language),
                    'notes'            => Zurmo::t('NotesModule', 'NotesModulePluralLabel', $params, null, $language),
                    'opportunities'    => Zurmo::t('OpportunitiesModule', 'OpportunitiesModulePluralLabel', $params, null, $language),
                    'secondaryAddress' => Zurmo::t('ContactsModule', 'Secondary Address',  array(), null, $language),
                    'secondaryEmail'   => Zurmo::t('ZurmoModule', 'Secondary Email',  array(), null, $language),
                    'source'           => Zurmo::t('ContactsModule', 'Source', $params, null, $language),
                    'state'            => Zurmo::t('ZurmoModule', 'Status', $params, null, $language),
                    'tasks'            => Zurmo::t('TasksModule', 'TasksModulePluralLabel', $params, null, $language),
                    'website'          => Zurmo::t('ZurmoModule', 'Website',  array(), null, $language),
                )
            );
        }

        public static function getModuleClassName()
        {
            return 'ContactsModule';
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'companyName',
                    'description',
                    'latestActivityDateTime',
                    'website',
                    'googleWebTrackingId',
                ),
                'relations' => array(
                    'account'             => array(static::HAS_ONE,   'Account'),
                    'accountAffiliations' => array(static::HAS_MANY, 'AccountContactAffiliation',
                                                   static::OWNED, static::LINK_TYPE_SPECIFIC,
                                                   'contactAffiliation'),
                    'industry'         => array(static::HAS_ONE,   'OwnedCustomField', static::OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'industry'),
                    'products'         => array(static::HAS_MANY, 'Product'),
                    'opportunities'    => array(static::MANY_MANY, 'Opportunity'),
                    'secondaryAddress' => array(static::HAS_ONE,   'Address',          static::OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'secondaryAddress'),
                    'secondaryEmail'   => array(static::HAS_ONE,   'Email',            static::OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'secondaryEmail'),
                    'source'           => array(static::HAS_ONE,   'OwnedCustomField', static::OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'source'),
                    'state'            => array(static::HAS_ONE,   'ContactState', static::NOT_OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'state'),
                    'projects'         => array(static::MANY_MANY, 'Project'),
                ),
                'derivedRelationsViaCastedUpModel' => array(
                    'meetings' => array(static::MANY_MANY, 'Meeting', 'activityItems'),
                    'notes'    => array(static::MANY_MANY, 'Note',    'activityItems'),
                    'tasks'    => array(static::MANY_MANY, 'Task',    'activityItems'),
                ),
                'rules' => array(
                    array('companyName',            'type',    'type' => 'string'),
                    array('companyName',            'length',  'min'  => 1, 'max' => 64),
                    array('description',            'type',    'type' => 'string'),
                    array('latestActivityDateTime', 'readOnly'),
                    array('latestActivityDateTime', 'type', 'type' => 'datetime'),
                    array('state',                  'required'),
                    array('website',                'url',     'defaultScheme' => 'http'),
                    array('googleWebTrackingId',    'type',    'type' => 'string'),
                ),
                'elements' => array(
                    'account'                 => 'Account',
                    'description'             => 'TextArea',
                    'latestActivityDateTime'  => 'DateTime',
                    'secondaryEmail'          => 'EmailAddressInformation',
                    'secondaryAddress'        => 'Address',
                    'state'                   => 'ContactState',
                ),
                'customFields' => array(
                    'industry' => 'Industries',
                    'source'   => 'LeadSources',
                ),
                'defaultSortAttribute' => 'lastName',
                'rollupRelations' => array(
                    'opportunities',
                ),
                'noAudit' => array(
                    'description',
                    'latestActivityDateTime',
                    'website'
                ),
                'indexes' => array(
                    'person_id' => array(
                        'members' => array('person_id'),
                        'unique' => false),
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getRollUpRulesType()
        {
            return 'Contact';
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        public static function getGamificationRulesType()
        {
            return 'ContactGamification';
        }

        /**
         * Override since Person has its own override.
         * @see RedBeanModel::getLabel
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            if (null != $moduleClassName = static::getModuleClassName())
            {
                return $moduleClassName::getModuleLabelByTypeAndLanguage('Singular', $language);
            }
            return get_called_class();
        }

        /**
         * Override since Person has its own override.
         * @see RedBeanModel::getPluralLabel
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            if (null != $moduleClassName = static::getModuleClassName())
            {
                return $moduleClassName::getModuleLabelByTypeAndLanguage('Plural', $language);
            }
            return static::getLabel($language) . 's';
        }

        public static function hasReadPermissionsSubscriptionOptimization()
        {
            return true;
        }

        public static function supportsQueueing()
        {
            return true;
        }

        public function setLatestActivityDateTime($dateTime)
        {
            assert('is_string($dateTime)');
            $this->unrestrictedSet('latestActivityDateTime', $dateTime);
        }

        protected function afterDelete()
        {
            parent::afterDelete();
            ContactsUtil::resolveMarketingListMembersByContact($this);
        }

        /**
         * Override to handle the set read-only latestActivityDateTime attribute on the import scenario.
         * (non-PHPdoc)
         * @see RedBeanModel::isAllowedToSetReadOnlyAttribute()
         */
        public function isAllowedToSetReadOnlyAttribute($attributeName)
        {
            if ($this->getScenario() == 'importModel' || $this->getScenario() == 'searchModel')
            {
                if ( $attributeName == 'latestActivityDateTime')
                {
                    return true;
                }
                else
                {
                    return parent::isAllowedToSetReadOnlyAttribute($attributeName);
                }
            }
        }
    }
?>
