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

    class AccountContactAffiliation extends Item
    {
        /**
         * @param $name
         * @throws NotSupportedException
         */
        public static function getByName($name)
        {
            throw new NotSupportedException();
        }

        public static function getModuleClassName()
        {
            return 'AccountContactAffiliationsModule';
        }

        /**
         * Returns the display name for the model class.
         * @param null $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('AccountContactAffiliationsModule',
                            'Account to Contact Affiliation', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('AccountContactAffiliationsModule',
                            'Account to Contact Affiliations', array(), null, $language);
        }

        public static function getPrimaryByAccountIdAndContactId($accountId, $contactId)
        {
            assert('is_int($accountId)');
            assert('is_int($contactId)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'account',
                    'operatorType'         => 'equals',
                    'value'                => $accountId
                ),
                2 => array(
                    'attributeName'        => 'contact',
                    'operatorType'         => 'equals',
                    'value'                => $contactId
                ),
            );
            $searchAttributeData['structure'] = '(1 and 2)';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('AccountContactAffiliation');
            $where             = RedBeanModelDataProvider::makeWhere('AccountContactAffiliation', $searchAttributeData, $joinTablesAdapter);
            $tableName         = AccountContactAffiliation::getTableName();
            $q                 = DatabaseCompatibilityUtil::getQuote();
            $where .= " and {$q}{$tableName}{$q}.{$q}primary{$q} = '1'";
            return self::getSubset($joinTablesAdapter, null, null, $where);
        }

        public static function getByAccountAndContact(Account $account, Contact $contact)
        {
            assert('$account->id > 0');
            assert('$contact->id > 0');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'account',
                    'operatorType'         => 'equals',
                    'value'                => $account->id
                ),
                2 => array(
                    'attributeName'        => 'contact',
                    'operatorType'         => 'equals',
                    'value'                => $contact->id
                ),
            );
            $searchAttributeData['structure'] = '(1 and 2)';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('AccountContactAffiliation');
            $where             = RedBeanModelDataProvider::makeWhere('AccountContactAffiliation', $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, null, $where);
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'primary',
                ),
                'rules' => array(
                    array('primary', 'boolean'),
                    array('primary', 'readOnly'),
                    array('primary', 'default', 'value' => false),
                    array('account', 'required'),
                    array('contact', 'required'),

                ),
                'relations' => array(
                    'role'     => array(static::HAS_ONE, 'OwnedCustomField', static::OWNED,
                                        static::LINK_TYPE_SPECIFIC, 'role'),
                    'account'  => array(static::HAS_ONE, 'Account', static::NOT_OWNED, static::LINK_TYPE_SPECIFIC,
                                        'accountAffiliation'),
                    'contact'  => array(static::HAS_ONE, 'Contact', static::NOT_OWNED, static::LINK_TYPE_SPECIFIC,
                                        'contactAffiliation'),
                ),
                'elements' => array(
                ),
                'elements' => array(
                    'account'  => 'Account',
                    'contact'  => 'Contact',
                    'primary'  => 'CheckBox',
                ),
                'customFields' => array(
                    'role' => 'AccountContactAffiliationRoles',
                ),
                'noAudit' => array(
                    'primary',
                    'account',
                    'contact'
                ),
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

        protected static function translatedAttributeLabels($language)
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'account' => Zurmo::t('AccountsModule', 'AccountsModuleSingularLabel',  $params, null, $language),
                    'contact' => Zurmo::t('ContactsModule', 'ContactsModuleSingularLabel',  $params, null, $language),
                    'role'    => Zurmo::t('ZurmoModule', 'Role', null,  null, $language),
                    'primary' => Zurmo::t('AccountContactAffiliationsModule', 'Primary Relationship', null,  null, $language),
                )
            );
        }

        public function __toString()
        {
            try
            {
                return strval($this->account) . ' - ' . strval($this->contact);
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        /**
         * Handle the search scenario for primary
         */
        public function isAllowedToSetReadOnlyAttribute($attributeName)
        {
            if ( in_array($attributeName, array('primary')))
            {
                return true;
            }
            else
            {
                return parent::isAllowedToSetReadOnlyAttribute($attributeName);
            }
        }
    }
?>