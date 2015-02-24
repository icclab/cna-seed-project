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
     * Adapter class to generate column definition when provided with rules, modelClassName
     */
    abstract class RedBeanModelMemberRulesToColumnAdapter
    {
        /**
         * Should we assume all int types signed?
         */
        const ASSUME_SIGNED = true;

        /**
         * this the default model class thats passed to validator in case we can't find a suitable model class.
         * this class must allow having beans.
         */
        const DEFAULT_MODEL_CLASS = 'Item';

        /**
         * Should be force using the default model class defined above for validators or try to find suitable one?
         */
        const FORCE_DEFAULT_MODEL_CLASS = true;

        /**
         * Key to store unique indexes for unique validators against models
         */
        const CACHE_KEY = 'RedBeanModelMemberRulesToColumnAdapter_uniqueIndexes';

        /**
         * returns unique indexes for a modelClass if there are any unique validators for its members
         * @param string $modelClassName
         * @return array|null
         */
        public static function resolveUniqueIndexesFromValidator($modelClassName)
        {
            $uniqueIndexes  = GeneralCache::getEntry(static::CACHE_KEY, array());
            if (isset($uniqueIndexes[$modelClassName]))
            {
                return $uniqueIndexes[$modelClassName];
            }
            return null;
        }

        /**
         * Provided modelClassName and rules for a member we resolve a column in table.
         * @param string $modelClassName
         * @param array $rules
         * @param $messageLogger
         * @return array|bool
         */
        public static function resolve($modelClassName, array $rules, & $messageLogger)
        {
            if (empty($rules))
            {
                return false;
            }
            $member                         = $rules[0][0];
            assert('strpos($member, " ") === false');
            $name                           = RedBeanModelMemberToColumnUtil::resolve($member);
            $type                           = null;
            $length                         = null;
            $notNull                        = null;
            $default                        = null;
            static::resolveColumnTypeAndLengthFromRules($modelClassName, $member, $rules, $type, $length,
                                                            $notNull, $default, $messageLogger);
            if (!isset($type))
            {
                return false;
            }
            return RedBeanModelMemberToColumnUtil::resolveColumnMetadataByHintType($name, $type, $length, null, $notNull, $default, null);
        }

        protected static function resolveColumnTypeAndLengthFromRules($modelClassName, $member, array $rules,
                                                                        & $type, & $length, & $notNull,
                                                                        & $default, & $messageLogger)
        {
            $suitableModelClassName = static::findSuitableModelClassName($modelClassName);
            if (!$suitableModelClassName)
            {
                $messageLogger->addErrorMessage(Zurmo::t('Core', 'Unable to find a suitable non-abstract class for' .
                                                ' validators against {{model}}', array('{{model}}' => $modelClassName)));
                return;
            }
            $model                              = $suitableModelClassName::model();
            $yiiValidators                      = CValidator::$builtInValidators;
            $yiiValidatorsToRedBeanValidators   = RedBeanModel::getYiiValidatorsToRedBeanValidators();
            foreach ($rules as $validatorMetadata)
            {
                assert('isset($validatorMetadata[0])');
                assert('isset($validatorMetadata[1])');
                $validatorName       = $validatorMetadata[1];
                $validatorParameters = array_slice($validatorMetadata, 2);
                if (isset($yiiValidators[$validatorName]))
                {
                    $validatorName = $yiiValidators[$validatorName];
                }
                if (isset($yiiValidatorsToRedBeanValidators[$validatorName]))
                {
                    $validatorName = $yiiValidatorsToRedBeanValidators[$validatorName];
                }
                if (!@class_exists($validatorName))
                {
                    continue;
                }
                $validator = CValidator::createValidator($validatorName, $model, $member, $validatorParameters);

                switch ($validatorName)
                {
                    case 'RedBeanModelTypeValidator':
                    case 'TypeValidator':
                    case 'CTypeValidator':
                        if (in_array($validator->type, array('blob', 'boolean', 'date', 'datetime', 'longblob',
                                                        'string', 'float', 'integer', 'time', 'text', 'longtext')))
                        {
                            if (!isset($type) || $validator->type == 'float') // another validator such as CNumberValidator(integer) might have set type to more precise one.
                            {
                                $type = $validator->type;
                            }
                        }
                        break;
                    case 'CBooleanValidator':
                        $type = 'boolean';
                        break;
                    case 'CStringValidator':
                        if ((!isset($type) || $type == 'string'))
                        {
                            static::resolveStringTypeAndLengthByMaxLength($type, $length, $validator->max);
                        }
                        break;
                    case 'CUrlValidator':
                        $type = 'string';
                        if (!isset($length))
                        {
                            $length = 255;
                        }
                        break;
                    case 'CEmailValidator':
                        $type = 'string';
                        if (!isset($length))
                        {
                            $length = 255;
                        }
                        break;
                    case 'RedBeanModelNumberValidator':
                    case 'CNumberValidator':
                        if ((!isset($type) || $type == 'integer') && !isset($validator->precision))
                        {
                            static::resolveIntegerTypeByMinAndMaxValue($type, $validator->min, $validator->max);
                        }
                        break;
                    case 'RedBeanModelDefaultValueValidator':
                    case 'CDefaultValueValidator':
                        // Left here for future use if we want to set defaults on db level too.
                        //$default              = 'DEFAULT ' . $validator->value;
                        break;
                    case 'RedBeanModelRequiredValidator':
                    case 'CRequiredValidator':
                        //$notNull = 'NOT NULL'; // Not Coding Standard
                        // Left here for future use if we want to set required on db level too.
                        break;
                    case 'RedBeanModelUniqueValidator':
                    case 'CUniqueValidator':
                        static::registerUniqueIndexByMemberName($member, $modelClassName);
                        break;
                }
            }
            // we have a string and we don't know anything else about it, better to set it as text.
            if ($type == 'string' && !isset($length))
            {
                $type = 'text';
            }
        }

        protected static function findSuitableModelClassName($modelClassName)
        {
            if (!static::FORCE_DEFAULT_MODEL_CLASS)
            {
                $suitableModelClassName = static::findFirstNonAbstractModelInHierarchy($modelClassName);
                if ($suitableModelClassName)
                {
                    return $suitableModelClassName;
                }
            }
            if (static::DEFAULT_MODEL_CLASS)
            {
                return static::DEFAULT_MODEL_CLASS;
            }
            return false;
        }

        protected static function findFirstNonAbstractModelInHierarchy($modelClassName)
        {
            if (!$modelClassName || $modelClassName == 'RedBeanModel')
            {
                return null;
            }
            $model              = new ReflectionClass($modelClassName);
            if ($model->isAbstract())
            {
                return static::findFirstNonAbstractModelInHierarchy(get_parent_class($modelClassName));
            }
            else
            {
                return $modelClassName;
            }
        }

        protected static function registerUniqueIndexByMemberName($member, $modelClassName)
        {
            $indexName  = RedBeanModelMemberIndexMetadataAdapter::resolveRandomIndexName($member, true);
            $uniqueIndexes  = GeneralCache::getEntry(static::CACHE_KEY, array());
            $uniqueIndexes[$modelClassName][$indexName] = array('members' => array($member), 'unique' => true);
            GeneralCache::cacheEntry(static::CACHE_KEY, $uniqueIndexes);
        }

        public static function resolveStringTypeAndLengthByMaxLength(& $type, & $length, $maxLength = null)
        {
            $type = 'text';
            if (isset($maxLength) && $maxLength > 0)
            {
                if ($maxLength > 65535)
                {
                    $type = 'longtext';
                }
                elseif ($maxLength <= 255)
                {
                    $type     = 'string';
                    $length   = $maxLength;
                }
            }
            // Begin Not Coding Standard
            // TODO: @Shoaibi: Critical: redo this:
            /*
            TINYTEXT - 256 bytes
            TEXT - 65,535 bytes
            MEDIUMTEXT - 16,777,215 bytes
            LONGTEXT - 4,294,967,295 bytes
             */
            // End Not Coding Standard
        }

        public static function resolveIntegerTypeByMinAndMaxValue(& $type, $min, $max)
        {
            $intMaxValuesAllows = DatabaseCompatibilityUtil::resolveIntegerMaxAllowedValuesByType(static::ASSUME_SIGNED);
            $type = 'integer';
            if (isset($max))
            {
                foreach ($intMaxValuesAllows as $relatedType => $valueLimit)
                {
                    $maxAllowedValue = $valueLimit;
                    $minAllowedValue = 0;
                    if (static::ASSUME_SIGNED)
                    {
                        $minAllowedValue = -1 * $valueLimit;
                    }
                    if ((!isset($min) || $min >= $minAllowedValue) &&
                        $max < $maxAllowedValue)
                    {
                        $type = $relatedType;
                        break;
                    }
                }
            }
        }
    }
?>