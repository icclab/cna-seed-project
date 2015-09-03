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
     * Projects Module Walkthrough.
     */
    class ProjectsRegularUserWalkthroughTest extends ZurmoRegularUserWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = Yii::app()->user->userModel;

            //Setup test data owned by the super user.
            ProjectTestHelper::createProjectByNameForOwner("My Project 1", $super);
            ProjectTestHelper::createProjectByNameForOwner("My Project 2", $super);
            ProjectTestHelper::createProjectByNameForOwner("My Project 3", $super);
            ProjectTestHelper::createProjectByNameForOwner("My Project 4", $super);
            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
            AllPermissionsOptimizationUtil::rebuild();
        }

        public function testRegularUserAllControllerActionsNoElevation()
        {
            //Create project owned by user super.
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $project = ProjectTestHelper::createProjectByNameForOwner('My Project 5', $super);
            Yii::app()->user->userModel = User::getByUsername('nobody');

            //Now test all portlet controller actions
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default');
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/index');
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/list');
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/create');
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/edit');
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/dashboardDetails');
            $this->setGetArray(array('id' => $project->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/details');
            $this->setGetArray(array('selectedIds' => '4,5,6,7,8', 'selectAll' => ''));  // Not Coding Standard
            $this->resetPostArray();
            //actionDelete should fail.
            $this->setGetArray(array('id' => $project->id));
            $this->resetPostArray();
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/delete');
        }

        /**
         * @depends testRegularUserAllControllerActionsNoElevation
         */
        public function testRegularUserControllerActionsWithElevationToAccessAndCreate()
        {
            //Now test peon with elevated rights to tabs /other available rights
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');

            //Now test peon with elevated rights to projects
            $nobody->setRight('ProjectsModule', ProjectsModule::RIGHT_ACCESS_PROJECTS);
            $nobody->setRight('ProjectsModule', ProjectsModule::RIGHT_CREATE_PROJECTS);
            $nobody->setRight('ProjectsModule', ProjectsModule::RIGHT_DELETE_PROJECTS);
            $this->assertTrue($nobody->save());

            //Test nobody with elevated rights.
            Yii::app()->user->userModel = $nobody;
            $content = $this->runControllerWithNoExceptionsAndGetContent('projects/default/list');

            $this->assertContains('John Kenneth Galbraith', $content);
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/create');
            //Test nobody can view an existing project he owns.
            $project = ProjectTestHelper::createProjectByNameForOwner('projectOwnedByNobody', $nobody);

            //At this point the listview for projects should show the search/list and not the helper screen.
            $content = $this->runControllerWithNoExceptionsAndGetContent('projects/default/list');
            $this->assertNotContains('John Kenneth Galbraith', $content);

            $this->setGetArray(array('id' => $project->id));
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/edit');

            //Test nobody can delete an existing project he owns and it redirects to index.
            $this->setGetArray(array('id' => $project->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('projects/default/delete',
                                                                   Yii::app()->createUrl('projects/default/index'));
        }

        /**
         * @depends testRegularUserControllerActionsWithElevationToAccessAndCreate
         */
        public function testRegularUserControllerActionsWithElevationToModels()
        {
            //Create project owned by user super.
            $super      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $project    = ProjectTestHelper::createProjectByNameForOwner('projectForElevationToModelTest', $super);

            //Test nobody, access to edit and details should fail.
            $nobody = $this->logoutCurrentUserLoginNewUserAndGetByUsername('nobody');
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/dashboardDetails');
            $this->setGetArray(array('id' => $project->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/edit');
            $this->setGetArray(array('id' => $project->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/details');
            $this->setGetArray(array('id' => $project->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/delete');

            //give nobody access to read
            Yii::app()->user->userModel = $super;
            $project->addPermissions($nobody, Permission::READ);
            $this->assertTrue($project->save());
            AllPermissionsOptimizationUtil::securableItemGivenReadPermissionsForUser($project, $nobody);

            //Now the nobody user can access the details view.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $project->id));
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/details');

            //Test nobody, access to edit should fail.
            $this->setGetArray(array('id' => $project->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/edit');
            $this->setGetArray(array('id' => $project->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/delete');

            $projectId  = $project->id;
            $project->forget();
            $project    = Project::getById($projectId);
            //give nobody access to read and write
            Yii::app()->user->userModel = $super;
            $project->addPermissions($nobody, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            //TODO :Its wierd that giving opportunity errors
            $this->assertTrue($project->save());
            AllPermissionsOptimizationUtil::securableItemLostReadPermissionsForUser($project, $nobody);
            AllPermissionsOptimizationUtil::securableItemGivenPermissionsForUser($project, $nobody);

            //Now the nobody user should be able to access the edit view and still the details view.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $project->id));
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/details');
            $this->setGetArray(array('id' => $project->id));
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/edit');

            $projectId  = $project->id;
            $project->forget();
            $project    = Project::getById($projectId);
            //revoke nobody access to read
            Yii::app()->user->userModel = $super;
            $project->addPermissions($nobody, Permission::READ_WRITE_CHANGE_PERMISSIONS, Permission::DENY);
            $this->assertTrue($project->save());
            AllPermissionsOptimizationUtil::securableItemLostPermissionsForUser($project, $nobody);

            //Test nobody, access to detail should fail.
            Yii::app()->user->userModel = $nobody;
            $this->setGetArray(array('id' => $project->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/details');
            $this->setGetArray(array('id' => $project->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/edit');

            //create some roles
            Yii::app()->user->userModel = $super;
            $parentRole = new Role();
            $parentRole->name = 'AAA';
            $this->assertTrue($parentRole->save());

            $childRole = new Role();
            $childRole->name = 'BBB';
            $this->assertTrue($childRole->save());

            $userInParentRole = User::getByUsername('confused');
            $userInChildRole = User::getByUsername('nobody');

            $childRole->users->add($userInChildRole);
            $this->assertTrue($childRole->save());
            $parentRole->users->add($userInParentRole);
            $parentRole->roles->add($childRole);
            $this->assertTrue($parentRole->save());
            $userInChildRole->forget();
            $userInChildRole = User::getByUsername('nobody');
            $userInParentRole->forget();
            $userInParentRole = User::getByUsername('confused');
            $parentRoleId = $parentRole->id;
            $parentRole->forget();
            $parentRole = Role::getById($parentRoleId);
            $childRoleId = $childRole->id;
            $childRole->forget();
            $childRole = Role::getById($childRoleId);

            //create project owned by super

            $project2 = ProjectTestHelper::createProjectByNameForOwner('testingParentRolePermission', $super);

            //Test userInParentRole, access to details and edit should fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $project2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/details');
            $this->setGetArray(array('id' => $project2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/edit');

            //give userInChildRole access to READ
            Yii::app()->user->userModel = $super;
            $project2->addPermissions($userInChildRole, Permission::READ);
            $this->assertTrue($project2->save());
            AllPermissionsOptimizationUtil::securableItemGivenReadPermissionsForUser($project2, $userInChildRole);

            //Test userInChildRole, access to details should not fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $project2->id));
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/details');

            //Test userInParentRole, access to details should not fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $project2->id));
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/details');

            $projectId  = $project2->id;
            $project2->forget();
            $project2   = Project::getById($projectId);

            //give userInChildRole access to read and write
            Yii::app()->user->userModel = $super;
            $project2->addPermissions($userInChildRole, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($project2->save());
            AllPermissionsOptimizationUtil::securableItemLostReadPermissionsForUser($project2, $userInChildRole);
            AllPermissionsOptimizationUtil::securableItemGivenPermissionsForUser($project2, $userInChildRole);

            //Test userInChildRole, access to edit should not fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $project2->id));
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/edit');

            //Test userInParentRole, access to edit should not fail.
            $this->logoutCurrentUserLoginNewUserAndGetByUsername($userInParentRole->username);
            $this->setGetArray(array('id' => $project2->id));
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/edit');

            $projectId  = $project2->id;
            $project2->forget();
            $project2   = Project::getById($projectId);
            //revoke userInChildRole access to read and write
            Yii::app()->user->userModel = $super;
            $project2->addPermissions($userInChildRole, Permission::READ_WRITE_CHANGE_PERMISSIONS, Permission::DENY);
            $this->assertTrue($project2->save());
            AllPermissionsOptimizationUtil::securableItemLostPermissionsForUser($project2, $userInChildRole);

            //Test userInChildRole, access to detail should fail.
            Yii::app()->user->userModel = $userInChildRole;
            $this->setGetArray(array('id' => $project2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/details');
            $this->setGetArray(array('id' => $project2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/edit');

            //Test userInParentRole, access to detail should fail.
            Yii::app()->user->userModel = $userInParentRole;
            $this->setGetArray(array('id' => $project2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/details');
            $this->setGetArray(array('id' => $project2->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/edit');

            //clear up the role relationships between users so not to effect next assertions
            $parentRole->users->remove($userInParentRole);
            $parentRole->roles->remove($childRole);
            $this->assertTrue($parentRole->save());
            $childRole->users->remove($userInChildRole);
            $this->assertTrue($childRole->save());

            //create some groups and assign users to groups
            Yii::app()->user->userModel = $super;
            $parentGroup = new Group();
            $parentGroup->name = 'AAA';
            $this->assertTrue($parentGroup->save());

            $childGroup = new Group();
            $childGroup->name = 'BBB';
            $this->assertTrue($childGroup->save());

            $userInChildGroup = User::getByUsername('confused');
            $userInParentGroup = User::getByUsername('nobody');

            $childGroup->users->add($userInChildGroup);
            $this->assertTrue($childGroup->save());
            $parentGroup->users->add($userInParentGroup);
            $parentGroup->groups->add($childGroup);
            $this->assertTrue($parentGroup->save());
            $parentGroup->forget();
            $childGroup->forget();
            $parentGroup = Group::getByName('AAA');
            $childGroup = Group::getByName('BBB');

            //Add access for the confused user to Products and creation of Products.
            $userInChildGroup->setRight('ProjectsModule', ProjectsModule::RIGHT_ACCESS_PROJECTS);
            $userInChildGroup->setRight('ProjectsModule', ProjectsModule::RIGHT_CREATE_PROJECTS);
            $this->assertTrue($userInChildGroup->save());

            //create project owned by super
            $project3 = ProjectTestHelper::createProjectByNameForOwner('testingParentGroupPermission', $super);

            //Test userInParentGroup, access to details and edit should fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $project3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/details');
            $this->setGetArray(array('id' => $project3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/edit');

            //Test userInChildGroup, access to details and edit should fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $project3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/details');
            $this->setGetArray(array('id' => $project3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/edit');

            //give parentGroup access to READ
            Yii::app()->user->userModel = $super;
            $project3->addPermissions($parentGroup, Permission::READ);
            $this->assertTrue($project3->save());
            AllPermissionsOptimizationUtil::securableItemGivenReadPermissionsForGroup($project3, $parentGroup);

            //Test userInParentGroup, access to details should not fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $project3->id));
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/details');

            //Test userInChildGroup, access to details should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $project3->id));
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/details');

            $projectId  = $project3->id;
            $project3->forget();
            $project3   = Project::getById($projectId);
            //give parentGroup access to read and write
            Yii::app()->user->userModel = $super;
            $project3->addPermissions($parentGroup, Permission::READ_WRITE_CHANGE_PERMISSIONS);
            $this->assertTrue($project3->save());
            AllPermissionsOptimizationUtil::securableItemLostReadPermissionsForGroup($project3, $parentGroup);
            AllPermissionsOptimizationUtil::securableItemGivenPermissionsForGroup($project3, $parentGroup);

            //Test userInParentGroup, access to edit should not fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $project3->id));
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/edit');

            //Test userInChildGroup, access to edit should not fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->logoutCurrentUserLoginNewUserAndGetByUsername($userInChildGroup->username);
            $this->setGetArray(array('id' => $project3->id));
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/edit');

            $projectId  = $project3->id;
            $project3->forget();
            $project3   = Project::getById($projectId);
            //revoke parentGroup access to read and write
            Yii::app()->user->userModel = $super;
            $project3->addPermissions($parentGroup, Permission::READ_WRITE_CHANGE_PERMISSIONS, Permission::DENY);
            $this->assertTrue($project3->save());
            AllPermissionsOptimizationUtil::securableItemLostPermissionsForGroup($project3, $parentGroup);

            //Test userInChildGroup, access to detail should fail.
            Yii::app()->user->userModel = $userInChildGroup;
            $this->setGetArray(array('id' => $project3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/details');
            $this->setGetArray(array('id' => $project3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/edit');

            //Test userInParentGroup, access to detail should fail.
            Yii::app()->user->userModel = $userInParentGroup;
            $this->setGetArray(array('id' => $project3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/details');
            $this->setGetArray(array('id' => $project3->id));
            $this->runControllerShouldResultInAccessFailureAndGetContent('projects/default/edit');

            //clear up the role relationships between users so not to effect next assertions
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $userInParentGroup->forget();
            $userInChildGroup->forget();
            $childGroup->forget();
            $parentGroup->forget();
            $userInParentGroup          = User::getByUsername('nobody');
            $userInChildGroup           = User::getByUsername('confused');
            $childGroup                 = Group::getByName('BBB');
            $parentGroup                = Group::getByName('AAA');

            //clear up the role relationships between users so not to effect next assertions
            $parentGroup->users->remove($userInParentGroup);
            $parentGroup->groups->remove($childGroup);
            $this->assertTrue($parentGroup->save());
            $childGroup->users->remove($userInChildGroup);
            $this->assertTrue($childGroup->save());
        }

        /**
         * @depends testRegularUserControllerActionsWithElevationToModels
         */
        public function testRegularUserViewingProductWithoutAccessToAccount()
        {
            $super       = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $aUser       = UserTestHelper::createBasicUser('aUser');
            $aUser->setRight('ProjectsModule', ProjectsModule::RIGHT_ACCESS_PROJECTS);
            $this->assertTrue($aUser->save());
            $aUser       = User::getByUsername('aUser');
            $project     = ProjectTestHelper::createProjectByNameForOwner('projectOwnedByaUser', $aUser);
            $id          = $project->id;
            $project->forget();
            unset($project);
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('aUser');
            $content = $this->runControllerWithNoExceptionsAndGetContent('projects/default');
            $this->assertNotContains('Fatal error: Method Project::__toString() must not throw an exception', $content);
        }

         /**
         * @deletes selected projects.
         */
        public function testRegularMassDeleteActionsForSelectedIds()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $confused = User::getByUsername('confused');
            $nobody = User::getByUsername('nobody');
            $this->assertEquals(Right::DENY, $confused->getEffectiveRight('ZurmoModule', ZurmoModule::RIGHT_BULK_DELETE));
            $confused->setRight('ZurmoModule', ZurmoModule::RIGHT_BULK_DELETE);
            //Load MassDelete view
            $projects = Project::getAll();
            $this->assertEquals(9, count($projects));
            $project1 = ProjectTestHelper::createProjectByNameForOwner('projectDelete1', $confused);
            $project2 = ProjectTestHelper::createProjectByNameForOwner('projectDelete2', $confused);
            $project3 = ProjectTestHelper::createProjectByNameForOwner('projectDelete3', $nobody);
            $project4 = ProjectTestHelper::createProjectByNameForOwner('projectDelete4', $confused);
            $project5 = ProjectTestHelper::createProjectByNameForOwner('projectDelete5', $confused);
            $project6 = ProjectTestHelper::createProjectByNameForOwner('projectDelete6', $nobody);
            $selectedIds = $project1->id . ',' . $project2->id . ',' . $project3->id ;    // Not Coding Standard
            $this->setGetArray(array('selectedIds' => $selectedIds, 'selectAll' => ''));  // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('projects/default/massDelete');
            $this->assertContains('<strong>3</strong>&#160;Projects selected for removal', $content);
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            //calculating projects after adding 6 new records
            $projects = Project::getAll();
            $this->assertEquals(15, count($projects));
            //Deleting 6 opportunities for pagination scenario
            //Run Mass Delete using progress save for page1
            $selectedIds = $project1->id . ',' . $project2->id . ',' . // Not Coding Standard
                           $project3->id . ',' . $project4->id . ',' . // Not Coding Standard
                           $project5->id . ',' . $project6->id;        // Not Coding Standard
            $this->setGetArray(array(
                'selectedIds' => $selectedIds, // Not Coding Standard
                'selectAll' => '',
                'Project_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 6));
            $content = $this->runControllerWithExitExceptionAndGetContent('projects/default/massDelete');
            $projects = Project::getAll();
            $this->assertEquals(10, count($projects));

            //Run Mass Delete using progress save for page2
            $selectedIds = $project1->id . ',' . $project2->id . ',' . // Not Coding Standard
                           $project3->id . ',' . $project4->id . ',' . // Not Coding Standard
                           $project5->id . ',' . $project6->id;        // Not Coding Standard
            $this->setGetArray(array(
                'selectedIds' => $selectedIds, // Not Coding Standard
                'selectAll' => '',
                'Project_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 6));
            $content = $this->runControllerWithNoExceptionsAndGetContent('projects/default/massDeleteProgress');
            $projects = Project::getAll();
            $this->assertEquals(9, count($projects));
        }

         /**
         *Test Bug with mass delete and multiple pages when using select all
         */
        public function testRegularMassDeletePagesProperlyAndRemovesAllSelected()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $confused = User::getByUsername('confused');
            $nobody = User::getByUsername('nobody');

            //Load MassDelete view for the 6 projects
            $projects = Project::getAll();
            $this->assertEquals(9, count($projects));

            //mass Delete pagination scenario
            //Run Mass Delete using progress save for page1
            $this->setGetArray(array(
                'selectAll' => '1',
                'Project_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 9));
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $content = $this->runControllerWithExitExceptionAndGetContent('projects/default/massDelete');
            $projects = Project::getAll();
            $this->assertEquals(4, count($projects));

           //Run Mass Delete using progress save for page2
            $this->setGetArray(array(
                'selectAll' => '1',
                'Project_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 9));
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $content = $this->runControllerWithNoExceptionsAndGetContent('projects/default/massDeleteProgress');

            $projects = Project::getAll();
            $this->assertEquals(0, count($projects));
        }
    }
?>