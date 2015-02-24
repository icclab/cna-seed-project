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

    class ProjectsSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            ProjectTestHelper::createProjectByNameForOwner("My Project 1", $super);
            ProjectTestHelper::createProjectByNameForOwner("My Project 2", $super);
            ProjectTestHelper::createProjectByNameForOwner("My Project 3", $super);
            ProjectTestHelper::createProjectByNameForOwner("My Project 4", $super);
            ProjectTestHelper::createProjectByNameForOwner("My Project 5", $super);
            ProjectTestHelper::createProjectByNameForOwner("My Project 6", $super);
            ProjectTestHelper::createProjectByNameForOwner("My Project 7", $super);
            ProjectTestHelper::createProjectByNameForOwner("My Project 8", $super);
            ProjectTestHelper::createProjectByNameForOwner("My Project 9", $super);
            ProjectTestHelper::createProjectByNameForOwner("My Project 10", $super);
            ProjectTestHelper::createProjectByNameForOwner("My Project 11", $super);
            ProjectTestHelper::createProjectByNameForOwner("My Project 12", $super);
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('projects/default');
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/create');

            $content = $this->runControllerWithNoExceptionsAndGetContent('projects/default/list');
            $this->assertContains('anyMixedAttributes', $content);
            //Test the search or paging of the listview.
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $this->setGetArray(array('ajax' => 'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('projects/default/list');
            $this->assertNotContains('anyMixedAttributes', $content);
            $this->resetGetArray();

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $projects            = Project::getAll();
            $this->assertEquals(12, count($projects));
            $superProjectId     = self::getModelIdByModelNameAndName('Project', 'My Project 1');
            $superProjectId2    = self::getModelIdByModelNameAndName('Project', 'My Project 2');
            $superProjectId3    = self::getModelIdByModelNameAndName('Project', 'My Project 3');
            $superProjectId4    = self::getModelIdByModelNameAndName('Project', 'My Project 4');
            $superProjectId5    = self::getModelIdByModelNameAndName('Project', 'My Project 5');
            $superProjectId6    = self::getModelIdByModelNameAndName('Project', 'My Project 6');
            $superProjectId7    = self::getModelIdByModelNameAndName('Project', 'My Project 7');
            $superProjectId8    = self::getModelIdByModelNameAndName('Project', 'My Project 8');
            $superProjectId9    = self::getModelIdByModelNameAndName('Project', 'My Project 9');
            $superProjectId10   = self::getModelIdByModelNameAndName('Project', 'My Project 10');
            $superProjectId11   = self::getModelIdByModelNameAndName('Project', 'My Project 11');
            $superProjectId12   = self::getModelIdByModelNameAndName('Project', 'My Project 12');

            $this->setGetArray(array('id' => $superProjectId));
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/edit');
            //Save project.
            $superProject       = Project::getById($superProjectId);
            $this->setPostArray(array('Project' => array('name' => 'My New Project 1')));

            //Test having a failed validation on the project during save.
            $this->setGetArray (array('id'      => $superProjectId));
            $this->setPostArray(array('Project' => array('name' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('projects/default/edit');
            $this->assertContains('Name cannot be blank', $content);

            //Load Model Detail Views
            $this->setGetArray(array('id' => $superProjectId));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/details');
        }

        public function testSuperUserCreateAction()
        {
            $super                                      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel                 = $super;
            $this->resetGetArray();

            $project                                    = array();
            $project['name']                            = 'Red Widget';
            $this->setPostArray(array('Project' => $project, 'Project_owner_name' => 'Super User'));
            $redirectUrl                                = $this->runControllerWithRedirectExceptionAndGetUrl('projects/default/create');

            $projects                                   = Project::getByName('Red Widget');
            $this->assertEquals(1, count($projects));
            $this->assertTrue  ($projects[0]->id > 0);
            $compareRedirectUrl                         = Yii::app()->createUrl('projects/default/details', array('id' => $projects[0]->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
        }

        public function testSuperUserDeleteAction()
        {
            $super                      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $super;

            $project                    = ProjectTestHelper::createProjectByNameForOwner("My New Project", $super);

            //Delete a project
            $this->setGetArray(array('id' => $project->id));
            $this->resetPostArray();
            $projects       = Project::getAll();
            $this->assertEquals(14, count($projects));
            $this->runControllerWithRedirectExceptionAndGetContent('projects/default/delete');
            $projects       = Project::getAll();
            $this->assertEquals(13, count($projects));
            try
            {
                Project::getById($project->id);
                $this->fail();
            }
            catch (NotFoundException $e)
            {
                //success
            }
        }

        /**
         * @deletes selected projects.
         */
        public function testMassDeleteActionsForSelectedIds()
        {
            $super                  = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $projects               = Project::getAll();
            $this->assertEquals(13, count($projects));
            $superProjectId        = self::getModelIdByModelNameAndName('Project', 'My Project 1');
            $superProjectId2       = self::getModelIdByModelNameAndName('Project', 'My Project 2');
            $superProjectId3       = self::getModelIdByModelNameAndName('Project', 'My Project 3');
            $superProjectId4       = self::getModelIdByModelNameAndName('Project', 'My Project 4');
            $superProjectId5       = self::getModelIdByModelNameAndName('Project', 'My Project 5');
            $superProjectId6       = self::getModelIdByModelNameAndName('Project', 'My Project 6');
            $superProjectId7       = self::getModelIdByModelNameAndName('Project', 'My Project 7');
            $superProjectId8       = self::getModelIdByModelNameAndName('Project', 'My Project 8');
            $superProjectId9       = self::getModelIdByModelNameAndName('Project', 'My Project 9');
            $superProjectId10      = self::getModelIdByModelNameAndName('Project', 'My Project 10');
            $superProjectId11      = self::getModelIdByModelNameAndName('Project', 'My Project 11');
            $superProjectId12      = self::getModelIdByModelNameAndName('Project', 'My Project 12');
            //Load Model MassDelete Views.
            //MassDelete view for single selected ids
            $this->setGetArray(array('selectedIds' => '5,6,7,8,9', 'selectAll' => '', ));  // Not Coding Standard
            $this->resetPostArray();
            $content                = $this->runControllerWithNoExceptionsAndGetContent('projects/default/massDelete');

            $this->assertContains('<strong>5</strong>&#160;Projects selected for removal', $content);

             //MassDelete view for all result selected ids
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('projects/default/massDelete');
            $this->assertContains('<strong>13</strong>&#160;Projects selected for removal', $content);

            //MassDelete for selected Record Count
            $projects               = Project::getAll();
            $this->assertEquals(13, count($projects));

            //MassDelete for selected ids for paged scenario
            $superProject1 = Project::getById($superProjectId);
            $superProject2 = Project::getById($superProjectId2);
            $superProject3 = Project::getById($superProjectId3);
            $superProject4 = Project::getById($superProjectId4);
            $superProject5 = Project::getById($superProjectId5);
            $superProject6 = Project::getById($superProjectId6);
            $superProject7 = Project::getById($superProjectId7);

            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            //MassDelete for selected ids for page 1
            $this->setGetArray(array(
                'selectedIds'  => $superProjectId . ',' . $superProjectId2 . ',' .  // Not Coding Standard
                                  $superProjectId3 . ',' . $superProjectId4 . ',' . // Not Coding Standard
                                  $superProjectId5 . ',' . $superProjectId6 . ',' . // Not Coding Standard
                                  $superProjectId7,
                'selectAll'    => '',
                'massDelete'   => '',
                'Project_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 7));
            $this->runControllerWithExitExceptionAndGetContent('projects/default/massDelete');

            //MassDelete for selected Record Count
            $projects = Project::getAll();
            $this->assertEquals(8, count($projects));

            //MassDelete for selected ids for page 2
            $this->setGetArray(array(
                'selectedIds'  => $superProjectId . ',' . $superProjectId2 . ',' .  // Not Coding Standard
                                  $superProjectId3 . ',' . $superProjectId4 . ',' . // Not Coding Standard
                                  $superProjectId5 . ',' . $superProjectId6 . ',' . // Not Coding Standard
                                  $superProjectId7,
                'selectAll'    => '',
                'massDelete'   => '',
                'Project_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 7));
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/massDeleteProgress');

            //MassDelete for selected Record Count
            $projects = Project::getAll();
            $this->assertEquals(6, count($projects));
        }

        /**
         *Test Bug with mass delete and multiple pages when using select all
         */
        public function testMassDeletePagesProperlyAndRemovesAllSelected()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //MassDelete for selected Record Count
            $projects = Project::getAll();
            $this->assertEquals(6, count($projects));

            //save Model MassDelete for entire search result
            $this->setGetArray(array(
                'selectAll' => '1',           // Not Coding Standard
                'Project_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 7));
            //Run Mass Delete using progress save for page1.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithExitExceptionAndGetContent('projects/default/massDelete');

            //check for previous mass delete progress
            $projects = Project::getAll();
            $this->assertEquals(1, count($projects));

            $this->setGetArray(array(
                'selectAll' => '1',           // Not Coding Standard
                'Project_page' => 2));
            $this->setPostArray(array('selectedRecordCount' => 7));
            //Run Mass Delete using progress save for page2.
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massDeleteProgressPageSize');
            $this->assertEquals(5, $pageSize);
            $this->runControllerWithNoExceptionsAndGetContent('projects/default/massDeleteProgress');

            //calculating projects count
            $projects = Project::getAll();
            $this->assertEquals(0, count($projects));
        }

        public function testCloningWithAnotherProject()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $super;

            $project = ProjectTestHelper::createProjectByNameForOwner("My Project 1", $super);
            $id = $project->id;
            $this->setGetArray(array('id' => $id));
            $content = $this->runControllerWithNoExceptionsAndGetContent('projects/default/copy');
            $this->assertContains('My Project 1', $content);
            $projects = Project::getAll();
            $this->assertEquals(1, count($projects));
        }
    }
?>