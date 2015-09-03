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
     * Model for game reward transactions.  This model is used to record date/time information for when a game reward
     * is redeemed
     */
    class GameRewardTransaction extends OwnedModel
    {
        public static function getModuleClassName()
        {
            return 'GameRewardsModule';
        }

        public static function canSaveMetadata()
        {
            return false;
        }

        /**
         * @param string $language
         * @return array
         */
        public static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language), array(
                'redemptionDateTime'    => Zurmo::t('GameRewardsModule', 'Redemption Date', array(), null, $language),
            ));
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'quantity',
                    'redemptionDateTime',
                ),
                'relations' => array(
                    'person' => array(RedBeanModel::HAS_ONE, 'Item', RedBeanModel::NOT_OWNED,
                                      RedBeanModel::LINK_TYPE_SPECIFIC, 'person'),
                    'reward' => array(RedBeanModel::HAS_ONE, 'GameReward', RedBeanModel::NOT_OWNED,
                                          RedBeanModel::LINK_TYPE_SPECIFIC, 'transactions'),
                ),
                'rules' => array(

                    array('redemptionDateTime',  'required'),
                    array('redemptionDateTime',  'readOnly'),
                    array('redemptionDateTime',  'type', 'type' => 'datetime'),
                    array('person',           'required'),
                    array('quantity',         'type',    'type' => 'integer'),
                    array('quantity',         'default', 'value' => 0),
                ),
                'elements' => array(
                    'redemptionDateTime'  => 'DateTime',
                    'person'           => 'Person',
                ),
                'defaultSortAttribute' => 'redemptionDateTime',
                'noAudit' => array(
                    'redemptionDateTime',
                    'person',
                    'quantity'
                ),
            );
            return $metadata;
        }

        public function onCreated()
        {
            $this->unrestrictedSet('redemptionDateTime',  DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
        }

        /**
         * Returns the display name for the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('GameRewardsModule', 'Game Reward Transaction', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('GameRewardsModule', 'Game Reward Transactions', array(), null, $language);
        }
    }
?>
