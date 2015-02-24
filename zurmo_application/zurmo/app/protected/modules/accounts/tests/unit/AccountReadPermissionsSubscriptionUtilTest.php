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

    class AccountReadPermissionsSubscriptionUtilTest extends ZurmoBaseTest
    {
        protected static $johnny;

        protected static $billy;

        protected static $david;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            self::$johnny = UserTestHelper::createBasicUser('Johnny');
            self::$billy = UserTestHelper::createBasicUser('Billy');
            self::$david = UserTestHelper::createBasicUser('David');
            Yii::app()->readPermissionSubscriptionObserver->enabled = true;

            $role1 = new Role();
            $role1->name = 'Role1';
            assert($role1->save()); // Not Coding Standard

            $role2 = new Role();
            $role2->name = 'Role2';
            assert($role2->save()); // Not Coding Standard

            $role3 = new Role();
            $role3->name = 'Role3';
            assert($role3->save()); // Not Coding Standard

            $role4 = new Role();
            $role4->name = 'Role4';
            assert($role4->save()); // Not Coding Standard

            $role5 = new Role();
            $role5->name = 'Role5';
            assert($role5->save()); // Not Coding Standard

            $role3->roles->add($role2);
            $role2->roles->add($role1);
            $role5->roles->add($role4);
            assert($role3->save()); // Not Coding Standard
            assert($role2->save()); // Not Coding Standard
            assert($role5->save()); // Not Coding Standard
        }

        public function setUp()
        {
            parent::setUp();
        }

        public static function tearDownAfterClass()
        {
            Yii::app()->readPermissionSubscriptionObserver->enabled = false;
            parent::tearDownAfterClass();
        }

        /**
         * Create new account, new basic user and new group.
         * Add user to group, allow group to access new account
         * After job is completed record for new account and new user should be in account_read_subscription.
         * Test group deletion
         */
        public function testGroupChangeOrDeleteScenario1()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $johnny = self::$johnny;
            $job = new ReadPermissionSubscriptionUpdateForAccountJob();
            $jobBasedOnBuildTable = new ReadPermissionSubscriptionUpdateForAccountFromBuildTableJob();
            Yii::app()->jobQueue->deleteAll();

            $this->deleteAllModelsAndRecordsFromReadPermissionTable('Account');
            $account = AccountTestHelper::createAccountByNameForOwner('First Account', $super);
            Yii::app()->jobQueue->deleteAll();
            sleep(1);

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(0, count($queuedJobs));
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(1, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);

            $group = new Group();
            $group->name = 'Group1';
            $this->assertTrue($group->save());

            //$group->users->add($johnny);
            //$this->assertTrue($group->save());
            // We need to add user to group using GroupUserMembershipForm, so ReadPermissionsSubscriptionUtil::userAddedToGroup(); will be triggered
            $form = new GroupUserMembershipForm();
            $fakePostData = array(
                'userMembershipData'    => array(0 => $johnny->id),
                'userNonMembershipData' => array()
            );
            $form = GroupUserMembershipFormUtil::setFormFromCastedPost($form, $fakePostData);
            $saved = GroupUserMembershipFormUtil::setMembershipFromForm($form, $group);
            $this->assertTrue($saved);

            // Because we save group, new queued job will be created, but read permission table should stay same
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(1, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);

            // Now add permissions to group
            $account->addPermissions($group, Permission::READ);
            $this->assertTrue($account->save());
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($jobBasedOnBuildTable->run());

            // Because user is added to group, and group have read access to account, this account should be in
            // read permission table for user
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);

            // Test delete group
            $group = Group::getByName('Group1');
            $group->delete();
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            // Because user is added to group, and group have read access to account, this account should be in
            // read permission table for user
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[1]['subscriptiontype']);
        }

        /**
         * Remove user from group, and in this case user and account should still exist in table but with TYPE_DELETE
         * Also in this scenario test when user is added again to the group, after it is removed from group
         * @depends testGroupChangeOrDeleteScenario1
         */
        public function testGroupChangeOrDeleteScenario2()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $job = new ReadPermissionSubscriptionUpdateForAccountJob();
            $jobBasedOnBuildTable = new ReadPermissionSubscriptionUpdateForAccountFromBuildTableJob();
            Yii::app()->jobQueue->deleteAll();
            $this->deleteAllModelsAndRecordsFromReadPermissionTable('Account');
            $johnny = self::$johnny;

            $account = AccountTestHelper::createAccountByNameForOwner('Second Account', $super);
            Yii::app()->jobQueue->deleteAll();
            sleep(1);

            $group = new Group();
            $group->name = 'Group2';
            $this->assertTrue($group->save());
            $group->users->add($johnny);
            $this->assertTrue($group->save());

            $account->addPermissions($group, Permission::READ);
            $this->assertTrue($account->save());
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();
            AllPermissionsOptimizationCache::forgetAll();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($jobBasedOnBuildTable->run());

            // Check if everything is added correctly
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);

            // Remove user from group
            //$group->users->remove($johnny);
            //$this->assertTrue($group->save());
            $form = new GroupUserMembershipForm();
            $fakePostData = array(
                'userMembershipData'    => array(),
                'userNonMembershipData' => array()
            );
            $form = GroupUserMembershipFormUtil::setFormFromCastedPost($form, $fakePostData);
            $saved = GroupUserMembershipFormUtil::setMembershipFromForm($form, $group);
            $this->assertTrue($saved);
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            // Because user is added to group, and group have read access to account, this account should be in
            // read permission table for user
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[1]['subscriptiontype']);

            // Now add user to group again and test
            //$group->users->add($johnny);
            //$this->assertTrue($group->save());
            // We need to add user to group using GroupUserMembershipForm, so ReadPermissionsSubscriptionUtil::userAddedToGroup(); will be triggered
            $form = new GroupUserMembershipForm();
            $fakePostData = array(
                'userMembershipData'    => array(0 => $johnny->id),
                'userNonMembershipData' => array()
            );
            $form = GroupUserMembershipFormUtil::setFormFromCastedPost($form, $fakePostData);
            $saved = GroupUserMembershipFormUtil::setMembershipFromForm($form, $group);
            $this->assertTrue($saved);
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            // Because user is added to group, and group have read access to account, this account should be in
            // read permission table for user
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
        }

        /**
         * Remove permissions from group to access account, and in this case user should be removed from group
         * @depends testGroupChangeOrDeleteScenario2
         */
        public function testGroupChangeOrDeleteScenario3()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $jobBasedOnBuildTable = new ReadPermissionSubscriptionUpdateForAccountFromBuildTableJob();

            $johnny = self::$johnny;
            $group = Group::getByName('Group2');
            $accounts = Account::getByName('Second Account');
            $account = $accounts[0];

            $account->removePermissions($group, Permission::READ);
            $this->assertTrue($account->save());
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($jobBasedOnBuildTable->run());

            // Because user is added to group, and group have read access to account, this account should be in
            // read permission table for user
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[1]['subscriptiontype']);
        }

        /**
         * Test nested groups
         */
        public function testGroupChangeOrDeleteScenario4()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $job = new ReadPermissionSubscriptionUpdateForAccountJob();
            $jobBasedOnBuildTable = new ReadPermissionSubscriptionUpdateForAccountFromBuildTableJob();

            $johnny = self::$johnny;
            $this->deleteAllModelsAndRecordsFromReadPermissionTable('Account');

            $account = AccountTestHelper::createAccountByNameForOwner('Third Account', $super);
            Yii::app()->jobQueue->deleteAll();
            sleep(1);

            $parentGroup = new Group();
            $parentGroup->name = 'Parent';
            $this->assertTrue($parentGroup->save());

            $group = new Group();
            $group->name = 'Child';
            $group->group = $parentGroup;
            $saved = $group->save();
            $this->assertTrue($saved);
            $group->users->add($johnny);
            $this->assertTrue($group->save());

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(1, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);

            // Add permissions for parentGroup to READ account
            $account->addPermissions($parentGroup, Permission::READ);
            $this->assertTrue($account->save());
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($jobBasedOnBuildTable->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);

            // Remove permissions from parentGroup to READ account
            $account->removePermissions($parentGroup, Permission::READ);
            $this->assertTrue($account->save());
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($jobBasedOnBuildTable->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[1]['subscriptiontype']);

            // Test parent group adding/removing
            $account->addPermissions($parentGroup, Permission::READ);
            $this->assertTrue($account->save());
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($jobBasedOnBuildTable->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);

            // Delete parent group
            $parentGroup->delete();
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[1]['subscriptiontype']);

            // Now test adding parent group
            $group->forget();
            $group = Group::getByName('Child');
            $accountId = $account->id;
            $account->forget();
            $account = Account::getById($accountId);
            $parentGroup2 = new Group();
            $parentGroup2->name = 'Parent';
            $this->assertTrue($parentGroup2->save());

            $group->group = $parentGroup2;
            $saved = $group->save();
            $this->assertTrue($saved);

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $account->addPermissions($parentGroup2, Permission::READ);
            $this->assertTrue($account->save());
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($jobBasedOnBuildTable->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
        }

        // Test when module permissions for group changes
        // It just test if job is triggered, we do not test generated read permission subscription table data
        public function testGroupChangeOrDeleteScenario5()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $job = new ReadPermissionSubscriptionUpdateForAccountJob();
            $jobBasedOnBuildTable = new ReadPermissionSubscriptionUpdateForAccountFromBuildTableJob();

            $johnny = self::$johnny;
            $this->deleteAllModelsAndRecordsFromReadPermissionTable('Account');

            $account = AccountTestHelper::createAccountByNameForOwner('Fifth Account', $super);
            Yii::app()->jobQueue->deleteAll();
            sleep(1);

            $group = new Group();
            $group->name = 'Group5';
            $this->assertTrue($group->save());
            $group->users->add($johnny);
            $this->assertTrue($group->save());
            Yii::app()->jobQueue->deleteAll();

            $fakePost = array(
                'AccountsModule__' . Permission::CHANGE_PERMISSIONS    => strval(Permission::ALLOW),
            );
            $validatedPost = ModulePermissionsFormUtil::typeCastPostData($fakePost);
            $saved = ModulePermissionsFormUtil::setPermissionsFromCastedPost($validatedPost, $group);
            $this->assertTrue($saved);

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());
        }

        /**
         * Create new account, new basic user and new role, and add it to user - job should be triggered
         * Test role deletion - job should be triggered
         */
        public function testRoleChangeOrDeleteScenario1()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $johnny = self::$johnny;
            $job = new ReadPermissionSubscriptionUpdateForAccountJob();
            $jobBasedOnBuildTable = new ReadPermissionSubscriptionUpdateForAccountFromBuildTableJob();
            Yii::app()->jobQueue->deleteAll();

            $this->deleteAllModelsAndRecordsFromReadPermissionTable('Account');
            $account = AccountTestHelper::createAccountByNameForOwner('First Account For Roles', $super);
            Yii::app()->jobQueue->deleteAll();
            sleep(1);

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(0, count($queuedJobs));
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(1, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);

            // Set user role
            $role1 = Role::getByName('Role1');
            $johnny->role = $role1;
            $this->assertTrue($johnny->save());

            // Because we save role, new queued job will be created, but read permission table should stay same
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(1, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);

            Yii::app()->jobQueue->deleteAll();
            $johnny->role = null;
            $this->assertTrue($johnny->save());

            // Because we save role, new queued job will be created, but read permission table should stay same
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(1, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
        }

        /**
         * Test role nestings
         * 1. role3->role2->role1(role3 is parent of role2 which is parent of role1), $role5->role4
         * 2. create account for user1
         * 3. user2 have role3, user1 have role1, user3 role5
         * 4. user1 get access to account1, user2 should have accesss to it, and it should be in read permission table
         * 5. remove role1 from role2, and test, user2 should lost access to the account
         * 6. change group parent of role1 to be role4, user3 should get access to the account
         * 7. revert role nesting
         */
        public function testRoleChangeOrDeleteScenario2()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $job = new ReadPermissionSubscriptionUpdateForAccountJob();
            $jobBasedOnBuildTable = new ReadPermissionSubscriptionUpdateForAccountFromBuildTableJob();
            Yii::app()->jobQueue->deleteAll();

            $this->deleteAllModelsAndRecordsFromReadPermissionTable('Account');
            Yii::app()->jobQueue->deleteAll();
            sleep(1);

            $user1 = self::$johnny;
            $user2 = self::$billy;
            $user3 = self::$david;

            $account = AccountTestHelper::createAccountByNameForOwner('Second Account For Roles', $user1);
            Yii::app()->jobQueue->deleteAll();

            // Set user role
            $role1 = Role::getByName('Role1');
            $role2 = Role::getByName('Role2');
            $role3 = Role::getByName('Role3');
            $role4 = Role::getByName('Role4');
            $role5 = Role::getByName('Role5');

            $user1->role = $role1;
            $this->assertTrue($user1->save());
            Yii::app()->jobQueue->deleteAll();
            $user2->role = $role3;
            $this->assertTrue($user2->save());
            $user3->role = $role5;
            $this->assertTrue($user3->save());
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(3, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($user1->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
            $this->assertEquals($user2->id, $rows[2]['userid']);
            $this->assertEquals($account->id, $rows[2]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[2]['subscriptiontype']);

            // Remove role1 from role2 - this is way how it works in uI
            //$role2->roles->remove($role1);
            //$this->assertTrue($role2->save());
            Yii::app()->jobQueue->deleteAll();
            $role1->role = null;
            $this->assertTrue($role1->save());
            $role1->forget();
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(3, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($user1->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
            $this->assertEquals($user2->id, $rows[2]['userid']);
            $this->assertEquals($account->id, $rows[2]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[2]['subscriptiontype']);

            // Now add role4 as parent of role1
            Yii::app()->jobQueue->deleteAll();
            $role1 = Role::getByName('Role1');
            $role1->role = $role4;
            $this->assertTrue($role1->save());
            $role4->forgetAll();
            $role2->forgetAll();
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();
            $role4 = Role::getByName('Role4');
            $role2 = Role::getByName('Role2');

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(4, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($user1->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
            $this->assertEquals($user2->id, $rows[2]['userid']);
            $this->assertEquals($account->id, $rows[2]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[2]['subscriptiontype']);
            $this->assertEquals($user3->id, $rows[3]['userid']);
            $this->assertEquals($account->id, $rows[3]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[3]['subscriptiontype']);

            // Revert role nesting
            $role4->roles->remove($role1);
            $this->assertTrue($role4->save());
            $role2->roles->add($role1);
            $this->assertTrue($role2->save());
        }

        /**
         * Test parent role deletions
         * 1. r3->r2->r1
         * 2. user2 have role3, user1 have role1
         * 3. create account for user1, user2 should get access to it
         * 4. delete role2, user2 should lost access to account
         * 5. make r3->r1 relationship
         * 6. u2 should get access to the account
         * 7. delete r3, user2 will lost access to the account
         * @depends testRoleChangeOrDeleteScenario2
         */
        public function testRoleChangeOrDeleteScenario3()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $job = new ReadPermissionSubscriptionUpdateForAccountJob();
            $jobBasedOnBuildTable = new ReadPermissionSubscriptionUpdateForAccountFromBuildTableJob();
            Yii::app()->jobQueue->deleteAll();

            $this->deleteAllModelsAndRecordsFromReadPermissionTable('Account');
            Yii::app()->jobQueue->deleteAll();
            sleep(1);

            $user1 = self::$johnny;
            $user2 = self::$billy;

            $account = AccountTestHelper::createAccountByNameForOwner('Third Account For Roles', $user1);
            Yii::app()->jobQueue->deleteAll();

            // Set user role
            $role1 = Role::getByName('Role1');
            $role2 = Role::getByName('Role2');
            $role3 = Role::getByName('Role3');

            // Just to trigger role changes
            $user2->role = $role1;
            $this->assertTrue($user2->save());
            $user2->forget();
            $user2 = User::getByUsername('billy');

            Yii::app()->jobQueue->deleteAll();
            $user2->role = $role3;
            $this->assertTrue($user2->save());
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(3, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($user1->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
            $this->assertEquals($user2->id, $rows[2]['userid']);
            $this->assertEquals($account->id, $rows[2]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[2]['subscriptiontype']);

            // Delete role2
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($role2->delete());
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(3, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($user1->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
            $this->assertEquals($user2->id, $rows[2]['userid']);
            $this->assertEquals($account->id, $rows[2]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[2]['subscriptiontype']);

            // Set role3 to be parent of role1, $user2 should get access to the account
            Yii::app()->jobQueue->deleteAll();
            $role1->role = $role3;
            $this->assertTrue($role1->save());
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(3, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($user1->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
            $this->assertEquals($user2->id, $rows[2]['userid']);
            $this->assertEquals($account->id, $rows[2]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[2]['subscriptiontype']);

            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($role3->delete());
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(3, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($user1->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
            $this->assertEquals($user2->id, $rows[2]['userid']);
            $this->assertEquals($account->id, $rows[2]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[2]['subscriptiontype']);

            // Revert roles for next test
            $role2 = new Role();
            $role2->name = 'Role2';
            $this->assertTrue($role2->save());

            $role3 = new Role();
            $role3->name = 'Role3';
            $this->assertTrue($role3->save());

            $role3->roles->add($role2);
            $role2->roles->add($role1);
            $this->assertTrue($role3->save());
            $this->assertTrue($role2->save());

            $user2->forget();
            $user2 = User::getByUsername('billy');
            $user2->role = $role3;
            $this->assertTrue($user2->save());
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();
        }

        /**
         * Test when user change role, from one to another to null
         * @depends testRoleChangeOrDeleteScenario2
         */
        public function testRoleChangeOrDeleteScenario4()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $job = new ReadPermissionSubscriptionUpdateForAccountJob();
            $jobBasedOnBuildTable = new ReadPermissionSubscriptionUpdateForAccountFromBuildTableJob();
            Yii::app()->jobQueue->deleteAll();

            $this->deleteAllModelsAndRecordsFromReadPermissionTable('Account');
            Yii::app()->jobQueue->deleteAll();
            sleep(1);

            $user1 = self::$johnny;
            $user2 = self::$billy;
            $user3 = self::$david;

            $account = AccountTestHelper::createAccountByNameForOwner('Forth Account For Roles', $user1);
            Yii::app()->jobQueue->deleteAll();

            // Set user role
            $role1 = Role::getByName('Role1');
            $role2 = Role::getByName('Role2');
            $role3 = Role::getByName('Role3');
            $role4 = Role::getByName('Role4');

            // Just to trigger role changes
            Yii::app()->jobQueue->deleteAll();
            $user1->role = null;
            $this->assertTrue($user1->save());
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($user1->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);

            // Now set $role1 for $user1
            Yii::app()->jobQueue->deleteAll();
            $user1->role = $role1;
            $this->assertTrue($user1->save());
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(3, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($user1->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
            $this->assertEquals($user2->id, $rows[2]['userid']);
            $this->assertEquals($account->id, $rows[2]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[2]['subscriptiontype']);

            // Now set $role4 for $user1
            Yii::app()->jobQueue->deleteAll();
            $user1->role = $role4;
            $this->assertTrue($user1->save());
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(4, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($user1->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
            $this->assertEquals($user2->id, $rows[2]['userid']);
            $this->assertEquals($account->id, $rows[2]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[2]['subscriptiontype']);
            $this->assertEquals($user3->id, $rows[3]['userid']);
            $this->assertEquals($account->id, $rows[3]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[3]['subscriptiontype']);

            // Now set $role1 for $user1
            Yii::app()->jobQueue->deleteAll();
            $user1->role = null;
            $this->assertTrue($user1->save());
            RedBeanModel::forgetAll();
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(4, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($user1->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
            $this->assertEquals($user2->id, $rows[2]['userid']);
            $this->assertEquals($account->id, $rows[2]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[2]['subscriptiontype']);
            $this->assertEquals($user3->id, $rows[3]['userid']);
            $this->assertEquals($account->id, $rows[3]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[3]['subscriptiontype']);
        }

        /**
         * Test cases when:
         * 1. user is created
         * 2. permissions for user to access account are added
         * 3. permissions for user to access account are removed
         * 4. permissions for user to access account are added after they are being removed
         */
        public function testUserCreationsAndPermissions()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->deleteAllModelsAndRecordsFromReadPermissionTable('Account');
            Yii::app()->jobQueue->deleteAll();
            $job = new ReadPermissionSubscriptionUpdateForAccountJob();
            $jobBasedOnBuildTable = new ReadPermissionSubscriptionUpdateForAccountFromBuildTableJob();
            $account = AccountTestHelper::createAccountByNameForOwner('First Account For Users', $super);
            Yii::app()->jobQueue->deleteAll();
            sleep(1);

            $user = new User();
            $user->username     = 'smith';
            $user->lastName     = 'Smitson';
            $user->setPassword(strtolower('password'));
            $this->assertTrue($user->save());

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccount', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(1, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);

            // Test add read permissions to user for account
            $account->addPermissions($user, Permission::READ);
            $this->assertTrue($account->save());
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($jobBasedOnBuildTable->run());

            // Check if everything is added correctly
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($user->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);

            // Test remove read permissions from user for account
            $account->removePermissions($user, Permission::READ);
            $this->assertTrue($account->save());
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($jobBasedOnBuildTable->run());

            // Because user is added to group, and group have read access to account, this account should be in
            // read permission table for user
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($user->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[1]['subscriptiontype']);

            // Test add read permissions AGAIN to user for account
            $account->addPermissions($user, Permission::READ);
            $this->assertTrue($account->save());
            ReadPermissionsOptimizationUtil::rebuild();

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($jobBasedOnBuildTable->run());

            // Check if everything is added correctly
            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($user->id, $rows[1]['userid']);
            $this->assertEquals($account->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
        }

        public function testSecurableItemGivenOrLostPermissionsForGroup()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->deleteAllModelsAndRecordsFromReadPermissionTable('Account');
            Yii::app()->jobQueue->deleteAll();
            $job = new ReadPermissionSubscriptionUpdateForAccountJob();
            $jobBasedOnBuildTable = new ReadPermissionSubscriptionUpdateForAccountFromBuildTableJob();
            $account = AccountTestHelper::createAccountByNameForOwner('Test Account 1', $super);
            Yii::app()->jobQueue->deleteAll();
            sleep(1);

            ReadPermissionsSubscriptionUtil::securableItemGivenPermissionsForGroup($account);
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();

            ReadPermissionsSubscriptionUtil::securableItemLostPermissionsForGroup($account);
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
        }

        public function testSecurableItemGivenOrLostPermissionsForUser()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->deleteAllModelsAndRecordsFromReadPermissionTable('Account');
            Yii::app()->jobQueue->deleteAll();
            $job = new ReadPermissionSubscriptionUpdateForAccountJob();
            $jobBasedOnBuildTable = new ReadPermissionSubscriptionUpdateForAccountFromBuildTableJob();
            $account = AccountTestHelper::createAccountByNameForOwner('Test Account 2', $super);
            Yii::app()->jobQueue->deleteAll();
            sleep(1);

            $user = self::$johnny;

            ReadPermissionsSubscriptionUtil::securableItemGivenPermissionsForUser($account);
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();

            ReadPermissionsSubscriptionUtil::securableItemLostPermissionsForUser($account);
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
        }

        protected function deleteAllModelsAndRecordsFromReadPermissionTable($modelClassName)
        {
            $models = $modelClassName::getAll();
            foreach ($models as $model)
            {
                $model->delete();
            }
            $tableName = ReadPermissionsSubscriptionUtil::getSubscriptionTableName($modelClassName);
            $sql = "DELETE FROM $tableName";
            ZurmoRedBean::exec($sql);
            $tableName = ReadPermissionsSubscriptionUtil::getAccountSubscriptionTempBuildTableName($modelClassName);
            $sql = "DELETE FROM $tableName";
            ZurmoRedBean::exec($sql);
        }
    }
?>
