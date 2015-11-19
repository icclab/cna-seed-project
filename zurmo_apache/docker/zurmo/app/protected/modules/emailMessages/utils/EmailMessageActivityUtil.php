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
     * Helper class for working with emailMessageActivity
     */
    class EmailMessageActivityUtil
    {
        /**
         * @param $hash
         * @param bool $validateQueryStringArray
         * @param bool $validateForTracking
         * @return array
         * @throws NotSupportedException
         */
        public static function resolveQueryStringArrayForHash($hash, $validateQueryStringArray = true,
                                                                                            $validateForTracking = true)
        {
            $hash = base64_decode($hash);
            if (StringUtil::isValidHash($hash))
            {
                $queryStringArray   = array();
                $decryptedString    = ZurmoPasswordSecurityUtil::decrypt($hash);
                if ($decryptedString)
                {
                    parse_str($decryptedString, $queryStringArray);
                    if ($validateQueryStringArray)
                    {
                        if ($validateForTracking)
                        {
                            static::validateAndResolveFullyQualifiedQueryStringArrayForTracking($queryStringArray);
                        }
                        else
                        {
                            static::validateQueryStringArrayForMarketingListsExternalController($queryStringArray);
                        }
                    }
                    return $queryStringArray;
                }
            }
            throw new NotSupportedException();
        }

        public static function resolveQueryStringFromUrlAndCreateOrUpdateActivity()
        {
            // TODO: @Shoaibi: Critical: Tests
            $hash = Yii::app()->request->getQuery('id');
            if (!$hash)
            {
                throw new NotSupportedException();
            }
            $queryStringArray = static::resolveQueryStringArrayForHash($hash);
            return static::processActivityFromQueryStringArray($queryStringArray);
        }

        protected static function processActivityFromQueryStringArray($queryStringArray)
        {
            $activityUpdated = static::createOrUpdateActivity($queryStringArray);
            if (!$activityUpdated)
            {
                throw new FailedToSaveModelException();
            }
            $trackingType = static::resolveTrackingTypeByQueryStringArray($queryStringArray);
            if ($trackingType === EmailMessageActivity::TYPE_CLICK)
            {
                // this shouldn't be here, its here to suppose no-scheme urls from previous versions' database
                $url    = StringUtil::addSchemeIfMissing($queryStringArray['url']);
                return array('redirect' => true, 'url' => $url);
            }
            else
            {
                return array('redirect' => false, 'imagePath' => PlaceholderImageUtil::resolveOneByOnePixelImagePath());
            }
        }

        protected static function resolveTrackingTypeByQueryStringArray($queryStringArray)
        {
            if (!empty($queryStringArray['type']))
            {
                return $queryStringArray['type'];
            }
            elseif (!empty($queryStringArray['url']))
            {
                return EmailMessageActivity::TYPE_CLICK;
            }
            else
            {
                return EmailMessageActivity::TYPE_OPEN;
            }
        }

        // this should be protected but we use it in EmailBounceJob so it has to be public.
        /**
         * @param array $queryStringArray
         * @return bool | array
         * @throws FailedToSaveModelException
         */
        public static function createOrUpdateActivity($queryStringArray)
        {
            $activity = static::resolveExistingActivity($queryStringArray);
            if ($activity)
            {
                $activity->quantity++;
                if (!$activity->save())
                {
                    throw new FailedToSaveModelException();
                }
                else
                {
                    return true;
                }
            }
            else
            {
                return static::createNewActivity($queryStringArray);
            }
        }

        protected static function resolveExistingActivity($queryStringArray)
        {
            $type = static::resolveTrackingTypeByQueryStringArray($queryStringArray);
            list($modelId, $modelType, $personId, $url) = array_values($queryStringArray);
            $modelClassName = static::resolveModelClassNameByModelType($modelType);
            $activities = $modelClassName::getByTypeAndModelIdAndPersonIdAndUrl($type, $modelId, $personId, $url);
            $activitiesCount = count($activities);
            if ($activitiesCount > 1)
            {
                throw new NotSupportedException(); // we found multiple models matching our criteria, should never happen.
            }
            elseif ($activitiesCount === 1)
            {
                return $activities[0];
            }
            else
            {
                return false;
            }
        }

        public static function resolveModelClassNameByModelType($modelType)
        {
            return $modelType . 'Activity';
        }

        protected static function createNewActivity($queryStringArray)
        {
            $type = static::resolveTrackingTypeByQueryStringArray($queryStringArray);
            list($modelId, $modelType, $personId, $url) = array_values($queryStringArray);
            $modelClassName = static::resolveModelClassNameByModelType($modelType);
            $sourceIP       = Yii::app()->request->userHostAddress;
            return $modelClassName::createNewActivity($type, $modelId, $personId, $url, $sourceIP);
        }

        protected static function validateAndResolveFullyQualifiedQueryStringArrayForTracking(& $queryStringArray)
        {
            $rules = array(
                'modelId'       => array(
                    'required'      => true,
                ),
                'modelType'     => array(
                    'required'      => true,
                ),
                'personId'      => array(
                    'required'      => true,
                ),
                'url'           => array(
                    'defaultValue'  => null,
                ),
                'type'           => array(
                    'defaultValue'  => null,
                ),
            );
            static::validateQueryStringArrayAgainstRulesArray($queryStringArray, $rules);
        }

        protected static function validateQueryStringArrayForMarketingListsExternalController(& $queryStringArray)
        {
            // TODO: @Shoaibi: Critical: Tests:
            $rules = array(
                'modelId'           => array(
                    'required'          => true,
                ),
                'modelType'         => array(
                    'required'          => true,
                ),
                'personId'          => array(
                    'required'          => true,
                ),
                'marketingListId'   => array(
                    'required'          => true,
                ),
                'createNewActivity' => array(
                    'defaultValue'      => false,
                ),
            );
            static::validateQueryStringArrayAgainstRulesArray($queryStringArray, $rules);
        }

        protected static function validateQueryStringArrayAgainstRulesArray(& $queryStringArray, $rules)
        {
            foreach ($rules as $index => $rule)
            {
                if (!isset($queryStringArray[$index]))
                {
                    if (array_key_exists('defaultValue', $rule))
                    {
                        $queryStringArray[$index] = $rule['defaultValue'];
                    }
                    elseif (array_key_exists('required', $rule) && $rule['required'])
                    {
                        throw new NotSupportedException();
                    }
                }
            }
        }
    }
?>