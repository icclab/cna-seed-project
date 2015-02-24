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
     * Used to observe when a model's related model has a change that should update the model's latestActivityDateTime
     * These settings are enabled/disabled in the designer under the contacts or accounts module.
     */
    abstract class LatestActivityDateTimeObserver extends BaseObserver
    {
        /**
         * Given a event, check that the event's sender is a meeting.  this is a beforeSave event
         * that should reset the latestActivityDateTimeProcessFlag if the startDateTime has changed.
         * This flag is then used by the UpdateModelsLatestActivityDateTimeByMeetingJob and
         * UpdateAccountLatestActivityDateTimeByMeetingJob
         * @param Cevent $event
         */
        public function resolveModelLatestActivityDateTimeProcessFlagByMeeting(Cevent $event)
        {
            assert('$event->sender instanceof Meeting');
            if (array_key_exists('startDateTime', $event->sender->originalAttributeValues))
            {
                $event->sender->processedForLatestActivity = false;
            }
        }

        /**
         * @param $activityItems
         * @param $dateTime
         */
        public static function resolveRelatedModelsAndSetLatestActivityDateTime($activityItems, $dateTime, $modelClassName)
        {
            assert('$activityItems instanceof RedBeanManyToManyRelatedModels');
            assert('is_string($dateTime)');
            assert('is_string($modelClassName)');
            foreach ($activityItems as $item)
            {
                static::resolveItemToModelAndPopulateLatestActivityDateTime($item, $dateTime, $modelClassName);
            }
        }

        /**
         * @param Item $item
         * @param $dateTime
         * @param $modelClassName
         * @throws FailedToSaveModelException
         */
        public static function resolveItemToModelAndPopulateLatestActivityDateTime(Item $item, $dateTime, $modelClassName)
        {
            assert('is_string($dateTime)');
            assert('is_string($modelClassName)');
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($modelClassName);
            try
            {
                $castedDownModel = $item->castDown(array($modelDerivationPathToItem));
                if (DateTimeUtil::isDateTimeStringNull($castedDownModel->latestActivityDateTime) ||
                    $dateTime > $castedDownModel->latestActivityDateTime)
                {
                    $castedDownModel->setLatestActivityDateTime($dateTime);
                    $saved = $castedDownModel->save();
                    if (!$saved)
                    {
                        throw new FailedToSaveModelException();
                    }
                }
            }
            catch (NotFoundException $e)
            {
                //do nothing
            }
            catch (AccessDeniedSecurityException $e)
            {
                //do nothing, since the current user cannot update the related model. Fail silently.
            }
        }
    }
?>