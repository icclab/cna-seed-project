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
     * Used to observe when a contact's related model has a change that should update the contact's latestActivityDateTime
     * These settings are enabled/disabled in the designer under the contacts module.
     */
    class ContactLatestActivityDateTimeObserver extends LatestActivityDateTimeObserver
    {
        public function init()
        {
            if (ContactsModule::shouldUpdateLatestActivityDateTimeWhenATaskIsCompleted())
            {
                $eventHandler = array($this, 'updateContactLatestActivityDateTimeByTask');
                Task::model()->attachEventHandler('onAfterSave', $eventHandler);
                $this->attachedEventHandlersIndexedByModelClassName['Task'] = array('onAfterSave', $eventHandler);
            }
            if (ContactsModule::shouldUpdateLatestActivityDateTimeWhenANoteIsCreated())
            {
                $eventHandler = array($this, 'updateContactLatestActivityDateTimeByNote');
                Note::model()->attachEventHandler('onAfterSave', $eventHandler);
                $this->attachedEventHandlersIndexedByModelClassName['Note'] = array('onAfterSave', $eventHandler);
            }
            if (ContactsModule::shouldUpdateLatestActivityDateTimeWhenAnEmailIsSentOrArchived())
            {
                $eventHandler = array($this, 'updateContactLatestActivityDateTimeByEmailMessage');
                EmailMessage::model()->attachEventHandler('onAfterSave', $eventHandler);
                $this->attachedEventHandlersIndexedByModelClassName['EmailMessage'] = array('onAfterSave', $eventHandler);
            }
            if (ContactsModule::shouldUpdateLatestActivityDateTimeWhenAMeetingIsInThePast())
            {
                $eventHandler = array($this, 'resolveModelLatestActivityDateTimeProcessFlagByMeeting');
                Meeting::model()->attachEventHandler('onBeforeSave', $eventHandler);
                $this->attachedEventHandlersIndexedByModelClassName['Meeting'] = array('onBeforeSave', $eventHandler);
            }
        }

        /**
         * Given a event, check that the event's sender is a Task and then check to process updating a related
         * contact's latestActivityDateTime if it should
         * @param CEvent $event
         */
        public function updateContactLatestActivityDateTimeByTask(CEvent $event)
        {
            assert('$event->sender instanceof Task');
            if (array_key_exists('status', $event->sender->originalAttributeValues) &&
                $event->sender->status == Task::STATUS_COMPLETED)
            {
                $this->resolveRelatedModelsAndSetLatestActivityDateTime($event->sender->activityItems,
                    DateTimeUtil::convertTimestampToDbFormatDateTime(time()), 'Contact');
            }
        }

        /**
         * Given a event, check that the event's sender is a Note and then check to process updating a related
         * contact's latestActivityDateTime if it should
         * @param CEvent $event
         */
        public function updateContactLatestActivityDateTimeByNote(CEvent $event)
        {
            assert('$event->sender instanceof Note');
            if ($event->sender->getIsNewModel())
            {
                $this->resolveRelatedModelsAndSetLatestActivityDateTime($event->sender->activityItems,
                    DateTimeUtil::convertTimestampToDbFormatDateTime(time()), 'Contact');
            }
        }

        /**
         * Given a event, check that the event's sender is a EmailMessage and then check to process updating a related
         * contact's latestActivityDateTime if it should
         * Both sent and archived emails will have the sentDateTime just populated.
         * @param CEvent $event
         */
        public function updateContactLatestActivityDateTimeByEmailMessage(CEvent $event)
        {
            assert('$event->sender instanceof EmailMessage');
            //Check for a just sent message
            if (array_key_exists('sentDateTime', $event->sender->originalAttributeValues) &&
                !DateTimeUtil::isDateTimeStringNull($event->sender->sentDateTime))
            {
                foreach ($event->sender->sender->personsOrAccounts as $senderPersonsOrAccount)
                {
                    $this->resolveItemToModelAndPopulateLatestActivityDateTime($senderPersonsOrAccount,
                        $event->sender->sentDateTime, 'Contact');
                }
                foreach ($event->sender->recipients as $emailMessageRecipient)
                {
                    foreach ($emailMessageRecipient->personsOrAccounts as $recipientPersonsOrAccount)
                    {
                        $this->resolveItemToModelAndPopulateLatestActivityDateTime($recipientPersonsOrAccount,
                            $event->sender->sentDateTime, 'Contact');
                    }
                }
            }
        }
    }
?>