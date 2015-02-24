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
     * Base class defining rules for game collections
     */
    abstract class GameCollectionRules
    {
        /**
         * @return string
         * @throws NotImplementedException - Implement in children classes
         */
        public static function getType()
        {
            throw NotImplementedException();
        }

        /**
         * @return string
         * @throws NotImplementedException - Implement in children classes
         */
        public static function getCollectionLabel()
        {
            throw NotImplementedException();
        }

        /**
         * @return array
         * @throws NotImplementedException - Implement in children classes
         */
        public static function getItemTypesAndLabels()
        {
            throw NotImplementedException();
        }

        /**
         * Frequencies are measured on a scale of 1 to 10 with 10 the most frequent.
         * @return array
         * @throws NotImplementedException - Implement in children classes
         */
        public static function getItemTypesAndFrequencies()
        {
            throw NotImplementedException();
        }

        /**
         * Upon completing a collection, the collection items can be redeemed for a quantity of coins as well
         * as the collection 'item' itself.
         * @return int
         */
        public static function getCoinRedemptionValue()
        {
            return 10;
        }

        /**
         * Upon redeeming a collection, if a collection has a redemption item, then return true, otherwise false
         * @return bool
         */
        public static function hasCollectionRedemptionItem()
        {
            return false;
        }

        /**
         * @see hasCollectionRedemptionItem
         * @throws NotImplementedException
         * @return bool
         */
        public static function getCollectionLogoType()
        {
            throw new NotImplementedException();
        }

        /**
         * @see hasCollectionRedemptionItem
         * @throws NotImplementedException
         * @return bool
         */
        public static function getCollectionLogoLabel()
        {
            throw new NotImplementedException();
        }

        /**
         * @return array of default data used when instantiating a GameCollection model for a user
         * Includes the array of items and default quantities, as well as the Collection 'Item' that is redeemed
         * if it has one
         */
        public static function makeDefaultData()
        {
            $data = array();
            if (static::hasCollectionRedemptionItem())
            {
                $data['RedemptionItem'] = 0;
            }
            foreach (static::getItemTypesAndLabels() as $type => $notUsed)
            {
                $data['Items'][$type] = 0;
            }
            return $data;
        }

        public static function makeLargeCollectionImageName()
        {
            return static::getCollectionLogoType() . '.png';
        }

        public static function makeMediumCollectionItemImageName($itemType)
        {
            assert('is_string($itemType)');
            return $itemType. '.png';
        }

        public static function makeMediumCOllectionItemImagePath($itemType)
        {
            assert('is_string($itemType)');
            return Yii::app()->themeManager->baseUrl . '/default/images/collections/' .
                   static::getType() . '/' . static::makeMediumCollectionItemImageName($itemType);
        }
    }
?>