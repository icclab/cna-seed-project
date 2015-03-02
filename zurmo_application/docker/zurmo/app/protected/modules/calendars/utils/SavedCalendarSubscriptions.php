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

    class SavedCalendarSubscriptions
    {
        protected $mySavedCalendarsAndSelected = array();

        protected $subscribedToSavedCalendarsAndSelected = array();

        /**
         * Makes shared and saved subscriptions.
         * @param User $user
         * @param string $selectedCalendarIds
         * @param string $subscribedCalendarIds
         * @return SavedCalendarSubscriptions
         */
        public static function makeByUser(User $user, $selectedCalendarIds = null, $subscribedCalendarIds = null)
        {
            assert('is_string($subscribedCalendarIds) || ($subscribedCalendarIds === null)');
            assert('is_string($selectedCalendarIds) || ($selectedCalendarIds === null)');
            $savedCalendarSubscriptions = new SavedCalendarSubscriptions();
            self::addMySavedCalendars($savedCalendarSubscriptions, $user, $selectedCalendarIds);
            self::addMySubscribedCalendars($savedCalendarSubscriptions, $user, $subscribedCalendarIds);
            return $savedCalendarSubscriptions;
        }

        /**
         * Add My saved calendars.
         *
         * @param SavedCalendarSubscriptions $savedCalendarSubscriptions
         * @param User $user
         * @param string $selectedCalendarIds
         * @return \SavedCalendarSubscriptions
         */
        private static function addMySavedCalendars(SavedCalendarSubscriptions $savedCalendarSubscriptions,
                                                    User $user, $selectedCalendarIds)
        {
            $mySavedCalendars           = CalendarUtil::getUserSavedCalendars($user);
            if (count($mySavedCalendars) == 0)
            {
                $mySavedCalendars    = CalendarUtil::loadDefaultCalendars($user);
                $selectedCalendarIds = $mySavedCalendars[0]->id . ',' . $mySavedCalendars[1]->id; // Not Coding Standard
            }
            ZurmoConfigurationUtil::setByUserAndModuleName($user,
                                                           'CalendarsModule',
                                                           'myCalendarSelections', $selectedCalendarIds);
            $selectedCalendarIdArray = array();
            if ($selectedCalendarIds != null)
            {
                $selectedCalendarIdArray = explode(',', $selectedCalendarIds); // Not Coding Standard
            }
            foreach ($mySavedCalendars as $key => $mySavedCalendar)
            {
                CalendarUtil::setMyCalendarColor($mySavedCalendar, $user);
                if (in_array($mySavedCalendar->id, $selectedCalendarIdArray))
                {
                    $savedCalendarSubscriptions->addMySavedCalendar($mySavedCalendar, true);
                }
                else
                {
                    $savedCalendarSubscriptions->addMySavedCalendar($mySavedCalendar, false);
                }
            }
        }

        /**
         * Add my subscribed calendars.
         * @param SavedCalendarSubscriptions $savedCalendarSubscriptions
         * @param User $user
         * @param string $subscribedCalendarIds
         * @return \SavedCalendarSubscriptions
         */
        private static function addMySubscribedCalendars(SavedCalendarSubscriptions $savedCalendarSubscriptions,
                                                  User $user,
                                                  $subscribedCalendarIds)
        {
            $mySubscribedCalendars           = CalendarUtil::getUserSubscribedCalendars($user);
            if (count($mySubscribedCalendars) > 0)
            {
                ZurmoConfigurationUtil::setByUserAndModuleName($user,
                                                               'CalendarsModule',
                                                               'mySubscribedCalendarSelections', $subscribedCalendarIds);
                $subscribedCalendarIdArray = array();
                if ($subscribedCalendarIds != null)
                {
                    $subscribedCalendarIdArray = explode(',', $subscribedCalendarIds); // Not Coding Standard
                }
                foreach ($mySubscribedCalendars as $key => $mySubscribedCalendar)
                {
                    CalendarUtil::setSharedCalendarColor($mySubscribedCalendar);
                    if (in_array($mySubscribedCalendar->id, $subscribedCalendarIdArray))
                    {
                        $savedCalendarSubscriptions->addSubscribedToCalendar($mySubscribedCalendar, true);
                    }
                    else
                    {
                        $savedCalendarSubscriptions->addSubscribedToCalendar($mySubscribedCalendar, false);
                    }
                }
            }
        }

        /**
         * @param SavedCalendar $savedCalendar
         * @param bool $selected
         */
        public function addMySavedCalendar(SavedCalendar $savedCalendar, $selected)
        {
            assert('is_bool($selected)');
            if (!isset($this->mySavedCalendarsAndSelected[$savedCalendar->id]))
            {
                $this->mySavedCalendarsAndSelected[$savedCalendar->id] = array($savedCalendar, $selected);
            }
        }

        /**
         * @param SavedCalendar $subscribedCalendar
         * @param bool $selected
         */
        public function addSubscribedToCalendar(SavedCalendarSubscription $subscribedCalendar, $selected)
        {
            assert('is_bool($selected)');
            if (!isset($this->subscribedToSavedCalendarsAndSelected[$subscribedCalendar->id]))
            {
                $this->subscribedToSavedCalendarsAndSelected[$subscribedCalendar->id] = array($subscribedCalendar, $selected);
            }
        }

        /**
         * @return array
         */
        public function getMySavedCalendarsAndSelected()
        {
            return $this->mySavedCalendarsAndSelected;
        }

        /**
         * @return array
         */
        public function getSubscribedToSavedCalendarsAndSelected()
        {
            return $this->subscribedToSavedCalendarsAndSelected;
        }
    }
?>