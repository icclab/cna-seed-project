<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    class PushDashboardUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testPushDashboardToUsers()
        {
            $super = Yii::app()->user->userModel;
            //Make and Get Five Users
            $users = UserTestHelper::generateBasicUsers(5);
            $this->assertEquals(5, count($users));
            //Remove any one portlet from super's default dashboard
            $defaultDashboards = Dashboard::getDefaultDashboardsByUser($super);
            if (count($defaultDashboards) == 0)
            {
                $defaultDashboard = Dashboard::setDefaultDashboardForUser($super);
            }
            else
            {
                $defaultDashboard = $defaultDashboards[0];
            }
            $defaultDashboardLayoutId = PushDashboardUtil::HOME_DASHBOARD . $defaultDashboard->layoutId;
            $defaultPortlets = Portlet::getByLayoutIdAndUserSortedById($defaultDashboardLayoutId, $super->id);
            if (empty($defaultPortlets))
            {
                $metadata = HomeDashboardView::getDefaultMetadata();
                $portletCollection = Portlet::makePortletsUsingMetadataSortedByColumnIdAndPosition($defaultDashboardLayoutId,
                                     $metadata, $super, null);
                $this->assertNotEmpty($portletCollection);
                Portlet::savePortlets($portletCollection);
                $defaultPortlets = Portlet::getByLayoutIdAndUserSortedById($defaultDashboardLayoutId, $super->id);
            }
            $this->assertTrue(count($defaultPortlets) > 0);
            foreach ($defaultPortlets as $portlet)
            {
                $portlet->delete();
                break;
            }
            $defaultPortlets = Portlet::getByLayoutIdAndUserSortedById($defaultDashboardLayoutId, $super->id);
            $deafultPortletViews = array();
            foreach ($defaultPortlets as $portlet)
            {
                $deafultPortletViews[] = $portlet->viewType;
            }
            //Push super's default dashboard to five users
            $userIds = array();
            foreach ($users as $user)
            {
                $userIds[] = $user->id;
            }
            $groupsAndUsers = array();
            $groupsAndUsers['groups'] = array();
            $groupsAndUsers['users']  = $userIds;
            PushDashboardUtil::pushDashboardToUsers($defaultDashboard, $groupsAndUsers);
            //Validate and compare portlets of five user's dashboard with super's default dashboard
            foreach ($users as $user)
            {
                $userPortlets = Portlet::getByLayoutIdAndUserSortedById($defaultDashboardLayoutId, $user->id);
                $this->assertEquals(count($defaultPortlets), count($userPortlets));
                foreach ($userPortlets as $portlet)
                {
                    $this->assertTrue(in_array($portlet->viewType, $deafultPortletViews));
                }
            }
        }

        public function testPushLayoutToUsers()
        {
            $super = Yii::app()->user->userModel;
            //Make and Get Five Users
            $users = UserTestHelper::generateBasicUsers(5);
            $this->assertEquals(5, count($users));
            //Remove first portlet from Super's ContactDetailsAndRelationsView
            $defaultLayoutId = 'Contact' . PushDashboardUtil::DETAILS_AND_RELATIONS_VIEW;
            $defaultPortlets = Portlet::getByLayoutIdAndUserSortedById($defaultLayoutId, $super->id);
            if (empty($defaultPortlets))
            {
                $metadata = ContactDetailsAndRelationsView::getDefaultMetadata();
                $portletCollection = Portlet::makePortletsUsingMetadataSortedByColumnIdAndPosition($defaultLayoutId,
                                     $metadata, $super, null);
                $this->assertNotEmpty($portletCollection);
                Portlet::savePortlets($portletCollection);
                $defaultPortlets = Portlet::getByLayoutIdAndUserSortedById($defaultLayoutId, $super->id);
            }
            $this->assertTrue(count($defaultPortlets) > 0);
            foreach ($defaultPortlets as $portlet)
            {
                $portlet->delete();
                break;
            }
            $defaultPortlets = Portlet::getByLayoutIdAndUserSortedById($defaultLayoutId, $super->id);
            $deafultPortletViews = array();
            foreach ($defaultPortlets as $portlet)
            {
                $deafultPortletViews[] = $portlet->viewType;
            }
            //Push super's default ContactDetailsAndRelationsView to five users
            $userIds = array();
            foreach ($users as $user)
            {
                $userIds[] = $user->id;
            }
            $groupsAndUsers = array();
            $groupsAndUsers['groups'] = array();
            $groupsAndUsers['users']  = $userIds;
            PushDashboardUtil::pushLayoutToUsers(new Contact(false), $groupsAndUsers);
            //Validate portlets of five user's ContactDetailsAndRelationsView with super's ContactDetailsAndRelationsView
            foreach ($users as $user)
            {
                $userPortlets = Portlet::getByLayoutIdAndUserSortedById($defaultLayoutId, $user->id);
                $this->assertEquals(count($defaultPortlets), count($userPortlets));
                foreach ($userPortlets as $portlet)
                {
                    $this->assertTrue(in_array($portlet->viewType, $deafultPortletViews));
                }
            }
        }
    }
?>