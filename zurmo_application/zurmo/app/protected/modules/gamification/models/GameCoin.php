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
     * Model for game coins
     */
    class GameCoin extends Item
    {
        public function __toString()
        {
            if ($this->value == null)
            {
                return Zurmo::t('Core', '(Unnamed)');
            }
            return Zurmo::t('GamificationModule', '{n} coin|{n} coins', array($this->value));
        }

        /**
         * Given an Item (Either User or Person),  try to find an existing model. If the model does
         * not exist, create it and populate the Item.
         * @param Item $person
         * @return The found or created model.
         * @throws NotSupportedException
         */
        public static function resolveByPerson(Item $person)
        {
            assert('$person->id > 0');
            assert('$person instanceof Contact || $person instanceof User');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'person',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $person->getClassId('Item'),
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('GameCoin');
            $where  = RedBeanModelDataProvider::makeWhere('GameCoin', $searchAttributeData, $joinTablesAdapter);
            $models = self::getSubset($joinTablesAdapter, null, 2, $where, null);
            if (count($models) > 1)
            {
                $logContent  = 'Duplicate Game Coin for Person: ' . $person->id;
                GamificationUtil::logAndNotifyOnDuplicateGameModel($logContent);
                return $models[0];
            }
            if (count($models) == 0)
            {
                $gameCoin = new GameCoin();
                $gameCoin->person = $person;
                $gameCoin->value  = 0;
                return $gameCoin;
            }
            return $models[0];
        }

        public static function getModuleClassName()
        {
            return 'GamificationModule';
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
                    'value',
                ),
                'relations' => array(
                    'person' => array(static::HAS_ONE, 'Item', static::NOT_OWNED,
                                      static::LINK_TYPE_SPECIFIC, 'person'),
                ),
                'rules' => array(
                    array('value',         'type',    'type' => 'integer'),
                    array('value',         'default', 'value' => 1),
                    array('value',         'numerical', 'min' => 0),
                    array('value',         'required'),
                    array('person',        'required'),
                ),
                'elements' => array(
                    'person' => 'Person',
                ),
                'defaultSortAttribute' => 'value',
                'noAudit' => array(
                    'value',
                    'person',
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * Eventually refactor to support different randomness seeds on a per module basis, but until this is fleshed
         * out more, we will just hard-code the seeding here.
         */
        public static function showCoin(CController $controller)
        {
            //Reporting and Data cleanup actions should show coins more frequently
            if ($controller->getModule()->getId() == 'reports')
            {
                $value = mt_rand(1, 25);
            }
            else
            {
                $value = mt_rand(1, 50);
            }
            if ($value == 7)
            {
                return true;
            }
            return false;
        }

        /**
         * Add specified value.
         */
        public function addValue($value)
        {
            assert('is_int($value)');
            $this->value = $this->value + $value;
        }

        /**
         * Remove a specified value. Typically used during game reward redemption
         * @param $value
         */
        public function removeValue($value)
        {
            assert('is_int($value)');
            $this->value = $this->value - $value;
        }

        /**
         * Returns the display name for the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('GamificationModule', 'Game Coin', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('GamificationModule', 'Game Coins', array(), null, $language);
        }
    }
?>
