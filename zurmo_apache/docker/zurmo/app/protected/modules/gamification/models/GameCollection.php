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
     * Model for game collections.
     */
    class GameCollection extends Item
    {
        public function __toString()
        {
            if (trim($this->type) == '')
            {
                return Zurmo::t('Core', '(Unnamed)');
            }
            return $this->type;
        }

        /**
         * Given a collection type and Item (Either User or Person),  try to find an existing model. If the model does
         * not exist, create it and populate the Item and type.
         * @param string $type
         * @param Item $person
         * @return The found or created model.
         * @throws NotSupportedException
         */
        public static function resolveByTypeAndPerson($type, Item $person)
        {
            assert('is_string($type)');
            assert('$person->id > 0');
            assert('$person instanceof Contact || $person instanceof User');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'type',
                    'operatorType'         => 'equals',
                    'value'                => $type,
                ),
                2 => array(
                    'attributeName'        => 'person',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $person->getClassId('Item'),
                ),
            );
            $searchAttributeData['structure'] = '1 and 2';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('GameCollection');
            $where  = RedBeanModelDataProvider::makeWhere('GameCollection', $searchAttributeData, $joinTablesAdapter);
            $models = self::getSubset($joinTablesAdapter, null, 2, $where, null);
            if (count($models) > 1)
            {
                $logContent  = 'Duplicate Game Collection for Person: ' . $person->id . ' with type ' . $type;
                GamificationUtil::logAndNotifyOnDuplicateGameModel($logContent);
                return $models[0];
            }
            if (count($models) == 0)
            {
                $gameCollectionRules = GameCollectionRulesFactory::createByType($type);
                $gameCollection = new GameCollection();
                $gameCollection->type   = $type;
                $gameCollection->person = $person;
                $gameCollection->serializedData = serialize($gameCollectionRules::makeDefaultData());
                return $gameCollection;
            }
            return $models[0];
        }

        /**
         * Given an  Item (Either User or Person),  try to find an existing model for each type. If the model does
         * not exist, create it and populate the Item and type. @return models found or created indexed by type.
         * @param Item $person
         * @param $collectionTypes
         * @return array
         * @throws FailedToSaveModelException
         */
        public static function resolvePersonAndAvailableTypes(Item $person, $collectionTypes)
        {
            assert('$person->id > 0');
            assert('$person instanceof Contact || $person instanceof User');
            assert('is_array($collectionTypes)');
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
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('GameCollection');
            $where  = RedBeanModelDataProvider::makeWhere('GameCollection', $searchAttributeData, $joinTablesAdapter);
            $models = self::getSubset($joinTablesAdapter, null, null, $where, null);
            $modelsByType = array();
            foreach ($collectionTypes as $type)
            {
                $modelFound = false;
                foreach ($models as $model)
                {
                    if ($model->type == $type)
                    {
                        $modelsByType[$type] = $model;
                        $modelFound          = true;
                        break;
                    }
                }
                if (!$modelFound)
                {
                    $gameCollectionRules      = GameCollectionRulesFactory::createByType($type);
                    $gameCollection           = new GameCollection();
                    $gameCollection->type     = $type;
                    $gameCollection->person   = $person;
                    $gameCollection->serializedData = serialize($gameCollectionRules::makeDefaultData());
                    $saved = $gameCollection->save();
                    if (!$saved)
                    {
                        throw new FailedToSaveModelException();
                    }
                    $modelsByType[$type] = $gameCollection;
                }
            }
            return $modelsByType;
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
                    'type',
                    'serializedData',
                ),
                'relations' => array(
                    'person' => array(static::HAS_ONE, 'Item', static::NOT_OWNED,
                        static::LINK_TYPE_SPECIFIC, 'person'),
                ),
                'rules' => array(
                    array('type',           'required'),
                    array('type',           'type',    'type' => 'string'),
                    array('type',           'length',  'min'  => 3, 'max' => 64),
                    array('serializedData', 'type', 'type' => 'string'),
                    array('person',         'required'),
                ),
                'elements' => array(
                    'person' => 'Person',
                ),
                'defaultSortAttribute' => 'type',
                'noAudit' => array(
                    'type',
                    'serializedData',
                    'person',
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getAvailableTypesCacheIdentifier()
        {
            return 'GameCollectionAvailableTypes';
        }

        public static function getAvailableTypes()
        {
            $cacheIdentifier = self::getAvailableTypesCacheIdentifier();
            try
            {
                return GeneralCache::getEntry($cacheIdentifier);
            }
            catch (NotFoundException $e)
            {
                $availableTypes = array();
                $gameCollectionRulesClassNames = GamificationModule::getAllClassNamesByPathFolder('rules.collections');
                foreach ($gameCollectionRulesClassNames as $gameCollectionRulesClassName)
                {
                    $classToEvaluate     = new ReflectionClass($gameCollectionRulesClassName);
                    if (is_subclass_of($gameCollectionRulesClassName, 'GameCollectionRules') &&
                        !$classToEvaluate->isAbstract())
                    {
                        $availableTypes[] = $gameCollectionRulesClassName::getType();
                    }
                }
            }
            GeneralCache::cacheEntry($cacheIdentifier, $availableTypes);
            return $availableTypes;
        }

        /**
         * Randomly decide if the user should receive a collection item
         * @return bool
         */
        public static function shouldReceiveCollectionItem()
        {
            $value = mt_rand(1, 30);
            if ($value === 2)
            {
                return true;
            }
            return false;
        }

        /**
         * Process receiving a collection item
         * @param User $user
         * @throws FailedToSaveModelException
         * @return string $randomKey representing CollectionItem type
         */
        public static function processRandomReceivingCollectionItemByUser(User $user)
        {
            list($collection, $randomKey, $randomTypeKey) = static::getARandomCollectionItemForUser($user);
            $itemsData             = $collection->getItemsData();
            $itemsData[$randomKey] = $itemsData[$randomKey] + 1;
            $collection->setItemsData($itemsData);
            $saved = $collection->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            return array($collection, $randomKey);
        }

        /**
         * Get a random collection item that can be collected by user
         * @param User $user
         * @return array
         */
        public static function getARandomCollectionItemForUser(User $user)
        {
            $availableTypes = GameCollection::getAvailableTypes();
            $randomTypeKey  = array_rand($availableTypes, 1);
            $collection     = GameCollection::resolveByTypeAndPerson($availableTypes[$randomTypeKey], $user);
            $itemsData      = $collection->getItemsData();
            $randomKey      = array_rand($itemsData, 1);
            return array($collection, $randomKey, $randomTypeKey);
        }

        /**
         * Returns the display name for the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('GamificationModule', 'Game Collection', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('GamificationModule', 'Game Collections', array(), null, $language);
        }

        public function getUnserializedData()
        {
            if ($this->serializedData == null)
            {
                return array();
            }
            return unserialize($this->serializedData);
        }

        /**
         * @return array
         */
        public function getItemsData()
        {
            if ($this->serializedData == null)
            {
                return array();
            }
            $unserializedData = unserialize($this->serializedData);
            if (!isset($unserializedData['Items']))
            {
                return array();
            }
            return $unserializedData['Items'];
        }

        public function setItemsData($itemsData)
        {
            $unserializedData          = $this->getUnserializedData();
            $unserializedData['Items'] = $itemsData;
            $this->serializedData      = serialize($unserializedData);
        }

        public function getRedemptionCount()
        {
            if ($this->serializedData == null)
            {
                return array();
            }
            $unserializedData = unserialize($this->serializedData);
            if (!isset($unserializedData['RedemptionItem']))
            {
                return array();
            }
            return (int)$unserializedData['RedemptionItem'];
        }

        public function setRedemptionCount($redemptionCount)
        {
            $unserializedData                   = $this->getUnserializedData();
            $unserializedData['RedemptionItem'] = $redemptionCount;
            $this->serializedData               = serialize($unserializedData);
        }

        public function redeem()
        {
            $items = $this->getItemsData();
            foreach ($items as $itemType => $quantity)
            {
                if ($quantity <= 0) //Just in case it is negative, as a safety
                {
                    return false;
                }
                $items[$itemType] = $quantity - 1;
            }
            $this->setItemsData($items);
            $redemptionCount = $this->getRedemptionCount();
            $this->setRedemptionCount($redemptionCount + 1);
            return $this->save();
        }

        public function canRedeem()
        {
            $items = $this->getItemsData();
            foreach ($items as $quantity)
            {
                if ($quantity <= 0) //Just in case it is negative, as a safety
                {
                    return false;
                }
            }
            return true;
        }
    }
?>
