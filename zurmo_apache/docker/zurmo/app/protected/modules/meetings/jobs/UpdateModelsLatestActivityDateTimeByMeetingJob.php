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
     * A job to update LatestActivityDateTime on contacts and accounts when required because a meeting occurred in the past
     */
    class UpdateModelsLatestActivityDateTimeByMeetingJob extends BaseJob
    {
        /**
         * @var int
         */
        protected static $pageSize = 200;

        /**
         * @returns Translated label that describes this job type.
         */
        public static function getDisplayName()
        {
           $params = LabelUtil::getTranslationParamsForAllModules();
           return Zurmo::t('JobsManagerModule',
                    'Process MeetingsModulePluralLowerCaseLabel for related records latest activity dates',
                    $params);
        }

        /**
         * @return The type of the NotificationRules
         */
        public static function getType()
        {
            return 'UpdateModelsLatestActivityDateTimeByMeeting';
        }

        public static function getRecommendedRunFrequencyContent()
        {
            return Zurmo::t('Core', 'Once per hour.');
        }

        /**
         * Processes any meetings where the startDateTime is in the past and it has not been processed yet for
         * latestActivityDateTime.
         *
         * @see BaseJob::run()
         */
        public function run()
        {
            $processed = 0;
            foreach (static::getModelsToProcess(self::$pageSize) as $meeting)
            {
                ContactLatestActivityDateTimeObserver::
                    resolveRelatedModelsAndSetLatestActivityDateTime(
                        $meeting->activityItems, $meeting->startDateTime, 'Contact');
                AccountLatestActivityDateTimeObserver::
                    resolveRelatedModelsAndSetLatestActivityDateTime(
                        $meeting->activityItems, $meeting->startDateTime, 'Account');
                $meeting->processedForLatestActivity = true;
                $saved = $meeting->save();
                if (!$saved)
                {
                    throw new FailedToSaveModelException();
                }
                else
                {
                    $processed++;
                }
            }

            $this->getMessageLogger()->addInfoMessage($this->resolveProcessedMessage($processed));
            return true;
        }

        /**
         * Would rather have used the Yii translation to properly handle singular/plural in one string
         * but because i need to pass in label params, it would not work.
         * @param $processed
         * @return string
         */
        protected function resolveProcessedMessage($processed)
        {
            $params          = LabelUtil::getTranslationParamsForAllModules();
            $params['{count}'] = $processed;
            if ($processed > 0 && $processed < 2)
            {
                return Zurmo::t('MeetingsModule', 'Processed {count} MeetingsModuleSingularLabel',
                                $params);
            }
            else
            {
                return Zurmo::t('MeetingsModule', 'Processed {count} MeetingsModulePluralLabel',
                                $params);
            }
        }

        /**
         * @param $pageSize
         * @return array of ByTimeWorkflowInQueue models
         */
        public static function getModelsToProcess($pageSize)
        {
            assert('is_int($pageSize)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'processedForLatestActivity',
                    'operatorType'         => 'equals',
                    'value'                => '0',
                ),
                2 => array(
                    'attributeName'        => 'processedForLatestActivity',
                    'operatorType'         => 'isNull',
                    'value'                => null,
                ),
                3 => array(
                    'attributeName'        => 'startDateTime',
                    'operatorType'         => 'lessThan',
                    'value'                => DateTimeUtil::convertTimestampToDbFormatDateTime(
                                              Yii::app()->timeZoneHelper->convertFromLocalTimeStampForCurrentUser(time()))
                ),
            );
            $searchAttributeData['structure'] = '(1 or 2) and 3';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('Meeting');
            $where = RedBeanModelDataProvider::makeWhere('Meeting', $searchAttributeData, $joinTablesAdapter);
            return Meeting::getSubset($joinTablesAdapter, null, $pageSize, $where, null);
        }
    }
?>