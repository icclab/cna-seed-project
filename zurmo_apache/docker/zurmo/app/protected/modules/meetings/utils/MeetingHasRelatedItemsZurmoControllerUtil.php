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
     * Extended class to support models that have related items such as activities or conversations.
     */
    class MeetingHasRelatedItemsZurmoControllerUtil extends ModelHasRelatedItemsZurmoControllerUtil
    {
        /**
         * Passing in a $model, process any relatedItems that have to be removed, added, or changed.
         */
        protected function resolveModelsRelatedItemsFromPost(& $model)
        {
            if (isset($_POST[$this->relatedItemsFormName]) &&
                isset($_POST[$this->relatedItemsFormName]['Contact']) &&
                !empty($_POST[$this->relatedItemsFormName]['Contact']['ids']))
            {
                $contactActivityItems = array();
                $userAttendees        = array();
                $contactItemPrefix    = Meeting::CONTACT_ATTENDEE_PREFIX;
                $userItemPrefix       = Meeting::USER_ATTENDEE_PREFIX;
                $attendees = explode(',', $_POST[$this->relatedItemsFormName]['Contact']['ids']); // Not Coding Standard
                foreach ($attendees as $item)
                {
                    if (strpos($item, $contactItemPrefix) !== false)
                    {
                        $contactActivityItems[] = substr($item,
                        strpos($item, $contactItemPrefix) + strlen($contactItemPrefix), strlen($item));
                    }
                    elseif (strpos($item, $userItemPrefix) !== false)
                    {
                        $userAttendees[] = intval(substr($item,
                        strpos($item, $userItemPrefix) + strlen($userItemPrefix), strlen($item)));
                    }
                }
                $this->resolveUserAttendees($model, $userAttendees);
                $_POST[$this->relatedItemsFormName]['Contact']['ids'] = implode(',', $contactActivityItems); // Not Coding Standard
            }
            parent::resolveModelsRelatedItemsFromPost($model);
        }

        /**
         * @param $model
         * @param array $reformedUserAttendees array of User Ids
         * Remove user attendees that are not provided. Add additional. Do not re-add already added ones.
         */
        protected function resolveUserAttendees(& $model, $reformedUserAttendees = array())
        {
            $usersAlreadyAdded  = array();
            if ($model->userAttendees->count() > 0)
            {
                foreach ($model->userAttendees as $user)
                {
                    if (!in_array($user->id, $reformedUserAttendees))
                    {
                        $model->userAttendees->remove($user);
                    }
                    else
                    {
                        $usersAlreadyAdded[] = $user->id;
                    }
                }
            }
            foreach ($reformedUserAttendees as $userId)
            {
                if ($userId != null && $userId > 0 && !in_array($userId, $usersAlreadyAdded))
                {
                    $user = User::getById($userId);
                    $model->userAttendees->add($user);
                }
            }
        }
    }
?>