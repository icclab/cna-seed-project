<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

    class GameReward extends OwnedSecurableItem
    {
        /**
         * @param string $name
         * @return string
         */
        public static function getByName($name)
        {
            return self::getByNameOrEquivalent('name', $name);
        }

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
         * @return string
         */
        public static function getModuleClassName()
        {
            return 'GameRewardsModule';
        }

        /**
         * @return bool
         */
        public static function canSaveMetadata()
        {
            return true;
        }

        /**
         * @param string $language
         * @return array
         */
        public static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language), array(
                'cost'                  => Zurmo::t('GameRewardsModule', 'Cost in Coins',            array(), null, $language),
                'description'           => Zurmo::t('ZurmoModule',       'Description',              array(), null, $language),
                'expirationDateTime'    => Zurmo::t('GameRewardsModule', 'Expiration Date Time',     array(), null, $language),
                'name'                  => Zurmo::t('Core',              'Name',                     array(), null, $language),
                'quantity'              => Zurmo::t('ZurmoModule',       'Quantity Available',       array(), null, $language),
                'transactions'          => Zurmo::t('GameRewardsModule', 'Game Reward Transactions', array(), null, $language),
                ));
        }

        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'cost',
                    'description',
                    'expirationDateTime',
                    'name',
                    'quantity',
                ),
                'relations' => array(
                    'transactions' => array(RedBeanModel::HAS_MANY, 'GameRewardTransaction', RedBeanModel::OWNED,
                                            RedBeanModel::LINK_TYPE_SPECIFIC, 'transactions'),
                ),
                'rules' => array(
                    array('cost',           'numerical',  'min' => 1),
                    array('cost',           'type',    'type' => 'integer'),
                    array('cost',           'required'),
                    array('description',        'type',    'type' => 'string'),
                    array('expirationDateTime', 'type', 'type' => 'datetime'),
                    array('name',           'required'),
                    array('name',           'type',    'type' => 'string'),
                    array('name',           'length',  'min'  => 3, 'max' => 64),
                    array('quantity',       'numerical',  'min' => 0),
                    array('quantity',       'type',    'type' => 'integer'),
                    array('quantity',       'required'),
                ),
                'elements' => array(
                    'description'        => 'TextArea',
                    'expirationDateTime' => 'DateTime',
                ),
                'customFields' => array(),
                'defaultSortAttribute' => 'name',
                'noAudit' => array(
                    'transactions'
                ),
            );
            return $metadata;
        }

        /**
         * @return bool
         */
        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * @return bool
         */
        public static function hasReadPermissionsOptimization()
        {
            return true;
        }
    }
?>