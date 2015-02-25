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

    class RoleTest extends ZurmoBaseTest
    {
        protected $roleWithNoUsers;

        protected $roleWithOneUsers;

        protected $roleWithTwoUsers;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            SecurityTestHelper::createUsers();
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testAddingUserToRole()
        {
            $role = $this->createRole('myRole');
            $benny = User::getByUsername('benny');
            //Add the role to benny
            $benny->role = $role;
            $saved = $benny->save();
            $this->assertTrue($saved);
            $roleId = $role->id;
            unset($role);
            $role = Role::getById($roleId);
            $this->assertEquals(1, $role->users->count());
            $this->assertTrue($role->users[0]->isSame($benny));

            //Now try adding billy to the role but from the other side, from the role side.
            $billy = User::getByUsername('billy');
            $role->users->add($billy);
            $saved = $role->save();
            $this->assertTrue($saved);
            $billy->forget(); //need to forget billy otherwise it won't pick up the change. i tried unset(), test fails
            $billy = User::getByUsername('billy');
            $this->assertTrue($billy->role->id > 0);
            $this->assertTrue($billy->role->isSame($role));
        }

        public function testAddingChildRoleAsAParentRole()
        {
            $childRole              = $this->createRole('childRole');
            $parentRole             = $this->createRole('parentRole');
            $parentRole->roles->add($childRole);
            $grandParentRole        = $this->createRole('grandParentRole');
            $grandParentRole->roles->add($parentRole);
            $saved                  = $grandParentRole->save();
            $this->assertTrue($saved);
            $parentRole->role       = $childRole;
            $this->assertFalse($parentRole->validate());
            $this->assertEquals('You cannot select a child role for the parent role', $parentRole->getError('role'));
            $grandParentRole->role  = $childRole;
            $this->assertFalse($grandParentRole->validate());
            $this->assertEquals('You cannot select a child role for the parent role', $grandParentRole->getError('role'));
        }

        public function testCreateRoleWithNoUsersAndNoParents()
        {
            $this->roleWithNoUsers = $this->createRole('noUsers');
        }

        /**
         * @depends testCreateRoleWithNoUsersAndNoParents
         */
        public function testAddingUserToRoleWithNoParentsAndNoUsers()
        {
            // create a role with no parents
            $role       = $this->createRole('OneUser');
            // create a user.
            $user       = UserTestHelper::createBasicUser(UserTestHelper::generateRandomUsername());
            //Add the role to user
            $this->addUserToRole($user, $role);

            // ensure we have got the user part of the role.
            $roleId     = $role->id;
            $role->forgetAll();
            unset($role);
            $role       = Role::getById($roleId);
            $this->assertEquals(1, $role->users->count());
            $this->assertTrue($role->users[0]->isSame($user));
            $this->roleWithOneUsers = $role;
        }

        /**
         * @depends testAddingUserToRoleWithNoParentsAndNoUsers
         */
        public function testAddingUserToRoleWithNoParentsAndOneUser()
        {
            // create a role with no parents
            $role       = $this->createRole('twoUsers');
            // create 2 users
            $users      = UserTestHelper::generateBasicUsers(2);
            foreach ($users as $user)
            {
                $this->addUserToRole($user, $role);
            }

            // ensure we have got the user part of the role.
            $roleId     = $role->id;
            $role->forgetAll();
            unset($role);
            $role       = Role::getById($roleId);
            $this->assertEquals(count($users), $role->users->count());
            foreach ($users as $i => $user)
            {
                $this->assertTrue($role->users[$i]->isSame($user));
            }
            $this->roleWithTwoUsers = $role;
        }

        /**
         * @depends testAddingUserToRoleWithNoParentsAndOneUser
         */
        public function testMovingRoleWithNoUsersToParent()
        {
            $this->moveRoleToParent('noUsers');
        }

        /**
         * @depends testMovingRoleWithNoUsersToParent
         */
        public function testMovingRoleWithOneUserToParent()
        {
            $this->moveRoleToParent('oneUser');
        }

        /**
         * @depends testMovingRoleWithOneUserToParent
         */
        public function testMovingRoleWithTwoUsersToParent()
        {
            $this->moveRoleToParent('twoUsers');
        }

        /**
         * @depends testMovingRoleWithNoUsersToParent
         */
        public function testRemovingRoleWithNoUsersFromParent()
        {
            $this->removeRoleFromParent('noUsers');
        }

        /**
         * @depends testMovingRoleWithOneUserToParent
         */
        public function testRemovingRoleWithOneUserFromParent()
        {
            $this->removeRoleFromParent('oneUser');
        }

        /**
         * @depends testMovingRoleWithTwoUsersToParent
         */
        public function testRemovingRoleWithTwoUsersFromParent()
        {
            $this->removeRoleFromParent('twoUsers');
        }

        protected function createRole($name)
        {
            $role       = new Role();
            $role->name = $name;
            $saved      = $role->save();
            $this->assertTrue($saved);
            return $role;
        }

        protected function addUserToRole(User $user, Role $role)
        {
            $user->role = $role;
            $saved      = $user->save();
            $this->assertTrue($saved);
        }

        protected function moveRoleToParent($roleName, $parentName = null)
        {
            $this->addOrRemoveRoleFromParent($roleName, $parentName, true);
        }

        protected function removeRoleFromParent($roleName, $parentName = null)
        {
            $this->addOrRemoveRoleFromParent($roleName, $parentName, false);
        }

        protected function addOrRemoveRoleFromParent($roleName, $parentName = null, $add = true)
        {
            if (!isset($parentName))
            {
                $parentName = $roleName . 'Parent';
            }
            $role                   = Role::getByName($roleName);
            try
            {
                $parentRole             = Role::getByName($parentName);
            }
            catch (NotFoundException $e)
            {
                $parentRole             = $this->createRole($parentName);
            }
            if ($add)
            {
                $parentRole->roles->add($role);
            }
            else
            {
                if ($parentRole->roles->contains($role))
                {
                    $parentRole->roles->remove($role);
                }
                else
                {
                    throw new NotFoundException('Child role not found in parent');
                }
            }
            $saved                  = $parentRole->save();
            $this->assertTrue($saved);
        }
    }
?>
