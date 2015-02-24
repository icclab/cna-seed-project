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

    class ZurmoRecordSharingPerformanceBenchmarkTest extends ZurmoWalkthroughBaseTest
    {
        protected static $baseUsername  = null;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            // create ContactStates
            ContactsModule::loadStartingData();

            static::$baseUsername       = StringUtil::generateRandomString(6, implode(range('a', 'z')));
        }

        public function setup()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Contact::deleteAll();
            $this->clearAllCaches();
        }

        protected function clearAllCaches()
        {
            ForgetAllCacheUtil::forgetAllCaches();
            PermissionsCache::forgetAll(true);
            RightsCache::forgetAll(true);
            Role::forgetRoleIdToRoleCache();
        }

        public function testRecordSharingPerformanceTimeForOneUserGroup()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(1, 3);
        }

        /**
         * @depends testRecordSharingPerformanceTimeForOneUserGroup
         */
        public function testRecordSharingPerformanceTimeForFiveUsersGroup()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(5);
        }

        /**
         * @depends testRecordSharingPerformanceTimeForFiveUsersGroup
         */
        public function testRecordSharingPerformanceTimeForTenUsersGroup()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(10);
        }

        /**
         * @depends testRecordSharingPerformanceTimeForTenUsersGroup
         */
        public function testRecordSharingPerformanceTimeForFiftyUsersGroup()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(50);
        }

        /**
         * @depends testRecordSharingPerformanceTimeForFiftyUsersGroup
         */
        public function testRecordSharingPerformanceTimeForHundredUsersGroup()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(100);
        }

        /**
         * @depends testRecordSharingPerformanceTimeForHundredUsersGroup
         */
        public function testRecordSharingPerformanceTimeForTwoHundredUsersGroup()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(200);
        }

        /**
         * @depends testRecordSharingPerformanceTimeForTwoHundredUsersGroup
         */
        public function testRecordSharingPerformanceTimeForFiveHundredUsersGroup()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(500);
        }

        /**
         * @depends testRecordSharingPerformanceTimeForFiveHundredUsersGroup
         */
        public function testRecordSharingPerformanceTimeForThousandUsersGroup()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(1000);
        }

        protected function ensureTimeSpentIsLessOrEqualThanExpectedForCount($count, $expectedTime = 1.5)
        {
            $timeSpent      = $this->resolveRecordSharingPerformanceTime($count);
            echo PHP_EOL. $count . ' user(s) group took ' . $timeSpent . ' seconds';
            $this->assertLessThanOrEqual($expectedTime, $timeSpent);
        }

        public function resolveRecordSharingPerformanceTime($count)
        {
            $groupMembers       = array();
            // create group
            $this->resetGetArray();
            $this->setPostArray(array('Group' => array(
                'name'  => "Group $count",
            )));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/group/create');
            $group              = Group::getByName("Group $count");
            $this->assertNotNull($group);
            $this->assertEquals("Group $count", strval($group));
            $group->setRight('ContactsModule', ContactsModule::getAccessRight());
            $group->setRight('ContactsModule', ContactsModule::getCreateRight());
            $group->setRight('ContactsModule', ContactsModule::getDeleteRight());
            $this->assertTrue($group->save());
            $groupId            = $group->id;
            $group->forgetAll();
            $group              = Group::getById($groupId);

            $this->resetGetArray();
            for ($i = 0; $i < $count; $i++)
            {
                $username       = static::$baseUsername . "_${i}_of_${count}";
                // Populate group
                $this->setPostArray(array('UserPasswordForm' =>
                    array('firstName'           => 'Some',
                        'lastName'              => 'Body',
                        'username'              => $username,
                        'newPassword'           => 'myPassword123',
                        'newPassword_repeat'    => 'myPassword123',
                        'officePhone'           => '456765421',
                        'userStatus'            => 'Active')));
                $this->runControllerWithRedirectExceptionAndGetContent('/users/default/create');
                $user           = User::getByUsername($username);
                $this->assertNotNull($user);
                $groupMembers['usernames'][] = $user->username;
                $groupMembers['ids'][] = $user->id;
            }
            $this->assertCount($count, $groupMembers['ids']);

            // set user's group
            $this->setGetArray(array('id' => $groupId));
            $this->setPostArray(array(
                'GroupUserMembershipForm' => array('userMembershipData' => $groupMembers['ids']
                )));
            $this->runControllerWithRedirectExceptionAndGetUrl('/zurmo/group/editUserMembership');
            $group->forgetAll();
            $group          = Group::getById($groupId);
            $this->assertCount($count, $group->users);
            foreach ($groupMembers['ids'] as $userId)
            {
                $user       = User::getById($userId);
                $this->assertEquals($group->id, $user->groups[0]->id);
                $this->assertTrue(RightsUtil::doesUserHaveAllowByRightName('ContactsModule', ContactsModule::getAccessRight(), $user));
                $this->assertTrue(RightsUtil::doesUserHaveAllowByRightName('ContactsModule', ContactsModule::getCreateRight(), $user));
                $this->assertTrue(RightsUtil::doesUserHaveAllowByRightName('ContactsModule', ContactsModule::getDeleteRight(), $user));
            }

            $this->clearAllCaches();
            // go ahead and create contact with group given readwrite, use group's first member to confirm he has create access
            $this->logoutCurrentUserLoginNewUserAndGetByUsername($groupMembers['usernames'][0]);
            $this->resetGetArray();
            $startingState  = ContactsUtil::getStartingState();
            $this->setPostArray(array('Contact' => array(
                'firstName'        => 'John',
                'lastName'         => 'Doe',
                'officePhone'      => '456765421',
                'state'            => array('id' => $startingState->id),
                'explicitReadWriteModelPermissions' => array(
                    'type' => ExplicitReadWriteModelPermissionsUtil::MIXED_TYPE_NONEVERYONE_GROUP,
                    'nonEveryoneGroup' => $groupId
                ))));
            $startTime                      = microtime(true);
            $url                            = $this->runControllerWithRedirectExceptionAndGetUrl('/contacts/default/create');
            $timeTakenForSave               = microtime(true) - $startTime;
            $johnDoeContactId               = intval(substr($url, strpos($url, 'id=') + 3));
            $johnDoeContact                 = Contact::getById($johnDoeContactId);
            $this->assertNotNull($johnDoeContact);
            $this->resetPostArray();
            $this->setGetArray(array('id' => $johnDoeContactId));
            $content                        = $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');
            $this->assertContains('Who can read and write ' . strval($group), $content);

            $this->clearAllCaches();
            $this->resetPostArray();
            // ensure group members have access
            foreach ($groupMembers['usernames'] as $member)
            {
                $user = $this->logoutCurrentUserLoginNewUserAndGetByUsername($member);
                $this->assertNotNull($user);
                $this->setGetArray(array('id' => $johnDoeContactId));
                $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/details');
                $this->runControllerWithNoExceptionsAndGetContent('/contacts/default/edit');
            }
            return $timeTakenForSave;
        }
    }
?>