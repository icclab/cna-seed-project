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
     * Class that builds demo projects
     */
    class ProjectsDemoDataMaker extends DemoDataMaker
    {
        /**
         * Limit projects to 5
         * @var int
         */
        protected $loadMagnitude = 5;

        public static function getDependencies()
        {
            return array();
        }

        /**
         * @param DemoDataHelper $demoDataHelper
         */
        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            $projects = array();
            $super = User::getByUsername('super');
            for ($i = 0; $i < $this->resolveQuantityToLoad(); $i++)
            {
                $project            = new Project();
                $project->owner     = $demoDataHelper->getRandomByModelName('User');
                $account            = $demoDataHelper->getRandomByModelName('Account');
                $project->accounts->add($account);
                $this->populateModel($project);
                $project->addPermissions(Group::getByName(Group::EVERYONE_GROUP_NAME), Permission::READ_WRITE_CHANGE_PERMISSIONS_CHANGE_OWNER);
                $saved = $project->save();
                assert('$saved');
                $project = Project::getById($project->id);
                AllPermissionsOptimizationUtil::
                    securableItemGivenPermissionsForGroup($project, Group::getByName(Group::EVERYONE_GROUP_NAME));
                $project->save();
                assert('$saved');
                ProjectAuditEvent::logAuditEvent(ProjectAuditEvent::PROJECT_CREATED, $project, $project->name);
                self::addDemoTasks($project, 3, $demoDataHelper);
                $projects[] = $project->id;
            }
            $demoDataHelper->setRangeByModelName('Project', $projects[0], $projects[count($projects)-1]);
        }

        /**
         * Populate model with required data
         * @param RedBeanModel $model
         */
        public function populateModel(& $model)
        {
            assert('$model instanceof Project');
            parent::populateModel($model);
            $projectRandomData  = ZurmoRandomDataUtil::
                                    getRandomDataByModuleAndModelClassNames('ProjectsModule', 'Project');
            $name               = RandomDataUtil::getRandomValueFromArray($projectRandomData['names']);
            $model->name        = $name;
            $model->description = $name . ' Description';
        }

        /**
         * Add demo tasks for the project
         * @param type $project
         */
        protected static function addDemoTasks($project, $taskInputCount = 1, & $demoDataHelper)
        {
            $randomTasks = self::getRandomTasks();
            for ($i = 0; $i < count($randomTasks); $i++)
            {
                $task                       = new Task();
                $task->name                 = $randomTasks[$i]['name'];
                $task->owner                = $demoDataHelper->getRandomByModelName('User');
                $task->requestedByUser      = $demoDataHelper->getRandomByModelName('User');
                $task->completedDateTime    = '0000-00-00 00:00:00';
                $task->project              = $project;
                $task->status               = Task::STATUS_NEW;
                $task->save();
                //Notification subscriber
                $notificationSubscriber             = new NotificationSubscriber();
                $notificationSubscriber->person     = $demoDataHelper->getRandomByModelName('User');
                $notificationSubscriber->hasReadLatest = false;
                //Task check list items
                $task->notificationSubscribers->add($notificationSubscriber);
                $taskCheckListItems = $randomTasks[$i]['checkListItems'];
                foreach ($taskCheckListItems as $itemKey => $name)
                {
                    $taskCheckListItem = new TaskCheckListItem();
                    $taskCheckListItem->name = $name;
                    if (($itemKey * $i * rand(5, 100)) % 3 == 0)
                    {
                        $taskCheckListItem->completed = true;
                    }
                    $task->checkListItems->add($taskCheckListItem);
                    ProjectsUtil::logTaskCheckItemEvent($task, $taskCheckListItem);
                }
                //Comments
                $commentItems  = $randomTasks[$i]['comments'];
                foreach ($commentItems as $description)
                {
                    $comment = new Comment();
                    $comment->description = $description;
                    $comment->setScenario('importModel');
                    $comment->createdByUser = $demoDataHelper->getRandomByModelName('User');
                    $task->comments->add($comment);
                    ProjectsUtil::logAddCommentEvent($task, strval($comment));
                }
                //Add Super user
                $comment                = new Comment();
                $comment->description   = 'Versatile idea regarding the task';
                $task->comments->add($comment);
                $task->addPermissions(Group::getByName(Group::EVERYONE_GROUP_NAME), Permission::READ_WRITE_CHANGE_PERMISSIONS_CHANGE_OWNER);
                $task->save();
                $currentStatus              = $task->status;
                ProjectsUtil::logAddTaskEvent($task);
                $task = Task::getById($task->id);
                $task->status = RandomDataUtil::getRandomValueFromArray(self::getTaskStatusOptions());
                $task->save();
                AllPermissionsOptimizationUtil::
                    securableItemGivenPermissionsForGroup($task, Group::getByName(Group::EVERYONE_GROUP_NAME));
                $task->save();
                ProjectsUtil::logTaskStatusChangeEvent($task,
                                                       Task::getStatusDisplayName($currentStatus),
                                                       Task::getStatusDisplayName(intval($task->status)));
            }
        }

        /**
         * Gets the list of random task
         * @return array
         */
        protected static function getRandomTasks()
        {
            $tasksList = array(
                'Create Demo Proposal',
                'Come up with a contacts list for the client',
                'Prepare telephone directory for the company',
                'Get an accounting software',
                'Usage of google analytics on company website',
            );
            $multipliedTasksList = array();
            for ($i = 1; $i <= 2; $i++)
            {
               foreach ($tasksList as $task)
               {
                   $multipliedTasksList[] = array('name' => $task . ' v' . $i,
                                                  'checkListItems' => self::getTaskCheckListItems($task),
                                                  'comments' => self::getTaskComments($task));
               }
            }
            return $multipliedTasksList;
        }

        /**
         * Gets the list of task check items
         * @return array
         */
        protected static function getTaskCheckListItems($key)
        {
            $checklistItemsArray =  array(
                'Create Demo Proposal'                         => array('Get the requirements',
                                                                        'Analysis of requirements'),
                'Come up with a contacts list for the client'  => array('Call the contacts',
                                                                        'Enter the data into excel'),
                'Prepare telephone directory for the company'  => array('Gather the list of employees with there contact details',
                                                                        'Enter the data into excel'),
                'Get an accounting software'                   => array('Research the available softwares',
                                                                        'Discuss with the team'),
                'Usage of google analytics on company website' => array('Explore the usage',
                                                                        'Implement into the website'),
            );

            return $checklistItemsArray[$key];
        }

        /**
         * Gets the list of task check items
         * @return array
         */
        protected static function getTaskComments($key)
        {
            $comments = array(
                'Create Demo Proposal'                         => array('Quite useful moving forward',
                                                                       'Would be helful for other people'),
                'Come up with a contacts list for the client'  => array('Very beneficial for the company',
                                                                        'Helpful for the sales team'),
                'Prepare telephone directory for the company'  => array('Very helpful for the employees',
                                                                        'Can easily track people'),
                'Get an accounting software'                   => array('Helpful for finance department',
                                                                        'Reduced work load',
                                                                        'Less number of people required'),
                'Usage of google analytics on company website' => array('Aids in site analysis',
                                                                        'Would be helpful from SEO perspective'),
            );
            return $comments[$key];
        }

        /**
         * Get random task status options
         * @return array
         */
        protected static function getTaskStatusOptions()
        {
            $data = Task::getStatusDropDownArray();
            return array_keys($data);
        }
    }
?>