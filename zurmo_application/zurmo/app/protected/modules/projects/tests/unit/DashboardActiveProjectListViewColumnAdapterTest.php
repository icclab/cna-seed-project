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
    class DashboardActiveProjectListViewColumnAdapterTest extends ZurmoBaseTest
    {
        private $project;
        private $super;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            ProjectTestHelper::createProjectByNameForOwner('testProject', $super);
        }

        public function setUp()
        {
            parent::setUp();
            $this->super = User::getByUsername('super');
            Yii::app()->user->userModel = $this->super;
            $projects                   = Project::getAll();
            $this->project              = $projects[0];
        }

        public function testGetActiveProjectInformationForDashboard()
        {
            //Project with no tasks should be 0% completed
            $content = DashboardActiveProjectListViewColumnAdapter::getActiveProjectInformationForDashboard($this->project);
            $this->assertTag(
                array(
                    'tag' => 'strong',
                    'ancestor' => array(
                        'tag' => 'div',
                        'attributes' => array('class' => 'project-stats percent-complete percent-red')
                    ),
                    'content' => '0'
                ),
                $content
            );

            //Test with tasks in the project
            $task1 = new Task();
            $task1->name = "task1";
            $task1->status = Task::STATUS_AWAITING_ACCEPTANCE;
            $this->project->tasks->add($task1);
            $task2 = new Task();
            $task2->name = "task2";
            $task2->status = Task::STATUS_COMPLETED;
            $this->project->tasks->add($task2);
            $task3 = new Task();
            $task3->name = "task3";
            $task3->status = Task::STATUS_IN_PROGRESS;
            $this->project->tasks->add($task3);
            $task4 = new Task();
            $task4->name = "task4";
            $task4->status = Task::STATUS_REJECTED;
            $this->project->tasks->add($task4);
            $task5 = new Task();
            $task5->name = "task5";
            $task5->status = Task::STATUS_NEW;
            $this->project->tasks->add($task5);
            $this->project->validate();
            $this->assertTrue($this->project->save());
            $content = DashboardActiveProjectListViewColumnAdapter::getActiveProjectInformationForDashboard($this->project);
            $this->assertTag(
                array(
                    'tag' => 'strong',
                    'ancestor' => array(
                        'tag' => 'div',
                        'attributes' => array('class' => 'project-stats percent-complete percent-yellow')
                    ),
                    'content' => '20'
                ),
                $content
            );
        }
    }
?>