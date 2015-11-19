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
     * Supportive functions for push dashboard functionality
     */
    class PushDashboardUtil
    {
        const GROUP_PREFIX   = 'Group_';

        const USER_PREFIX    = 'User_';

        const HOME_DASHBOARD = 'HomeDashboard';

        const DETAILS_AND_RELATIONS_VIEW = 'DetailsAndRelationsView';

        /**
         * Validates if current user has rights to push dashboard to users
         * @return bool
         */
        public static function canCurrentUserPushDashboardOrLayout()
        {
            if (RightsUtil::doesUserHaveAllowByRightName('ZurmoModule', ZurmoModule::RIGHT_PUSH_DASHBOARD_OR_LAYOUT,
                Yii::app()->user->userModel))
            {
                return true;
            }
            return false;
        }

        /**
         * Push dashboard to users. Synchronizes user's default dashboard portlets to given dashboard portlets
         * @param Dashboard $dashboard
         * @param $groupsAndUsers
         */
        public static function pushDashboardToUsers(Dashboard $dashboard, $groupsAndUsers)
        {
            $processedUsers = array();
            foreach ($groupsAndUsers['groups'] as $groupId)
            {
                $group = Group::getById(intval($groupId));
                $usersInGroup = $group->getUsersExceptSystemUsers();
                foreach ($usersInGroup as $user)
                {
                    if (!in_array($user->id, $processedUsers))
                    {
                        $processedUsers[] = $user->id;
                        $userDashboard = self::resolveDefaultDashboardByUser($dashboard, $user);
                        self::pushUserHomeDashboardPortlets($user, $userDashboard, $dashboard);
                    }
                }
            }
            foreach ($groupsAndUsers['users'] as $userId)
            {
                $user = User::getById(intval($userId));
                if (!in_array($user->id, $processedUsers))
                {
                    $processedUsers[] = $user->id;
                    $userDashboard = self::resolveDefaultDashboardByUser($dashboard, $user);
                    self::pushUserHomeDashboardPortlets($user, $userDashboard, $dashboard);
                }
            }
        }

        /**
         * Syncs user's dashboard portlets to given dashboard portlets
         * @param User $user
         * @param Dashboard $userDashboard
         * @param Dashboard $pushedDashboard
         */
        public static function pushUserHomeDashboardPortlets(User $user, Dashboard $userDashboard, Dashboard $pushedDashboard)
        {
            $userDashboardPortletsLayoutId   = self::HOME_DASHBOARD . $userDashboard->layoutId;
            $userDashboardPortlets           = Portlet::getByLayoutIdAndUserSortedById($userDashboardPortletsLayoutId,
                                               $user->id);
            $pushedDashboardPortletsLayoutId = self::HOME_DASHBOARD . $pushedDashboard->layoutId;
            $pushedDashboardPortlets         = Portlet::getByLayoutIdAndUserSortedById($pushedDashboardPortletsLayoutId,
                                               Yii::app()->user->userModel->id);
            foreach ($userDashboardPortlets as $portlet)
            {
                $portlet->delete();
                unset($portlet);
            }
            foreach ($pushedDashboardPortlets as $pushedDashboardPortlet)
            {
                $portlet                      = new Portlet();
                $portlet->column              = $pushedDashboardPortlet->column;
                $portlet->position            = $pushedDashboardPortlet->position;
                $portlet->layoutId            = $userDashboardPortletsLayoutId;
                $portlet->collapsed           = $pushedDashboardPortlet->collapsed;
                $portlet->viewType            = $pushedDashboardPortlet->viewType;
                $portlet->serializedViewData  = $pushedDashboardPortlet->serializedViewData;
                $portlet->user                = $user;
                $portlet->save();
            }
        }

        /**
         * Returns default dashboard for user.
         * Creates and return default dashboard, if no dashboard exists for user
         * @param Dashboard $dashboard
         * @param User $user
         * @return Dashboard
         * @throws FailedToSaveModelException
         */
        public static function resolveDefaultDashboardByUser(Dashboard $dashboard, User $user)
        {
            $userDefaultDashboards = Dashboard::getDefaultDashboardsByUser($user);
            if (count($userDefaultDashboards) == 0)
            {
                $userDashboard = Dashboard::setDefaultDashboardForUser($user);
            }
            else
            {
                $userDashboard = $userDefaultDashboards[0];
            }
            $userDashboard->setTreatCurrentUserAsOwnerForPermissions(true);
            $userDashboard->name       = $dashboard->name;
            $userDashboard->layoutType = $dashboard->layoutType;
            $saved = $userDashboard->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            return $userDashboard;
        }

        /**
         * Resolves type-ahead post data to groups and users array
         * @param $postData
         * @return array
         */
        public static function resolveGroupsAndUsersFromPost($postData)
        {
            $groupIds = array();
            $userIds  = array();
            $groupAndUserIds = array_filter(explode(',', $postData['GroupsAndUsers']['ids'])); // Not Coding Standard
            foreach ($groupAndUserIds as $identifier)
            {
                if (strpos($identifier, self::GROUP_PREFIX) !== false)
                {
                    $groupIds[] = intval(substr($identifier,
                    strpos($identifier, self::GROUP_PREFIX) + strlen(self::GROUP_PREFIX), strlen($identifier)));
                }
                elseif (strpos($identifier, self::USER_PREFIX) !== false)
                {
                    $userIds[] = intval(substr($identifier,
                    strpos($identifier, self::USER_PREFIX) + strlen(self::USER_PREFIX), strlen($identifier)));
                }
            }
            $groupsAndUsers = array();
            $groupsAndUsers['groups'] = array_filter($groupIds);
            $groupsAndUsers['users']  = array_filter($userIds);
            return $groupsAndUsers;
        }

        /**
         * For a given model, contact or account or opportunity, pushes DetailsAndRelationsView layout for provided user
         * @param $model
         * @param $groupsAndUsers
         */
        public static function pushLayoutToUsers($model, $groupsAndUsers)
        {
            $processedUsers = array();
            foreach ($groupsAndUsers['groups'] as $groupId)
            {
                $group = Group::getById(intval($groupId));
                $usersInGroup = $group->getUsersExceptSystemUsers();
                foreach ($usersInGroup as $user)
                {
                    if (!in_array($user->id, $processedUsers))
                    {
                        $processedUsers[] = $user->id;
                        self::pushDetailsAndRelationsViewPortlets($user, $model);
                    }
                }
            }
            foreach ($groupsAndUsers['users'] as $userId)
            {
                $user = User::getById(intval($userId));
                if (!in_array($user->id, $processedUsers))
                {
                    $processedUsers[] = $user->id;
                    self::pushDetailsAndRelationsViewPortlets($user, $model);
                }
            }
        }

        public static function pushDetailsAndRelationsViewPortlets(User $user, $model)
        {
            $layoutIdPrefix       = get_class($model);
            $layoutId             = $layoutIdPrefix . self::DETAILS_AND_RELATIONS_VIEW;
            $userLayoutPortlets   = Portlet::getByLayoutIdAndUserSortedById($layoutId, $user->id);
            $pushedLayoutPortlets = Portlet::getByLayoutIdAndUserSortedById($layoutId, Yii::app()->user->userModel->id);
            foreach ($userLayoutPortlets as $portlet)
            {
                $portlet->delete();
                unset($portlet);
            }
            foreach ($pushedLayoutPortlets as $pushedLayoutPortlet)
            {
                $portlet                      = new Portlet();
                $portlet->column              = $pushedLayoutPortlet->column;
                $portlet->position            = $pushedLayoutPortlet->position;
                $portlet->layoutId            = $layoutId;
                $portlet->collapsed           = $pushedLayoutPortlet->collapsed;
                $portlet->viewType            = $pushedLayoutPortlet->viewType;
                $portlet->serializedViewData  = $pushedLayoutPortlet->serializedViewData;
                $portlet->user                = $user;
                $portlet->save();
            }
        }
    }
?>