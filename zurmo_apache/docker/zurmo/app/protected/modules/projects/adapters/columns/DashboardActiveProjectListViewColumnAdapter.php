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
     * Column adapter for dashboard active project list view
     */
    class DashboardActiveProjectListViewColumnAdapter extends TextListViewColumnAdapter
    {
        /**
         * @return array
         */
        public function renderGridViewData()
        {
            return array(
                    'name'  => $this->attribute,
                    'value' => 'DashboardActiveProjectListViewColumnAdapter
                                            ::getActiveProjectInformationForDashboard($data)',
                    'type'  => 'raw'
                );
        }

        /**
         * Resolve project link
         * @param string $projectName
         * @param int $id
         * @return string
         */
        protected static function resolveProjectLinkWithRedirectURl($projectName, $id)
        {
            $url = Yii::app()->createUrl('/projects/default/details', array('id' => $id));
            return ZurmoHtml::link($projectName, $url, array('class' => 'edit-project-link'));
        }

        /**
         * Group tasks by kanban type and get stats
         * @param Project $project
         * @return array
         */
        protected static function groupTasksByKanbanTypeAndGetStats(Project $project)
        {
            $tasks = $project->tasks;
            $kanbanItemsArray = array();
            $totalTasksToDoCount = 0;
            $completedTasksCount = 0;
            foreach ($tasks as $task)
            {
                if (ControllerSecurityUtil::doesCurrentUserHavePermissionOnSecurableItem($task, Permission::READ))
                {
                    $totalTasksToDoCount++;
                    if ($task->status == Task::STATUS_COMPLETED)
                    {
                        $completedTasksCount++;
                    }
                    $kanbanItem  = KanbanItem::getByTask($task->id);
                    if ($kanbanItem == null)
                    {
                        //Create KanbanItem here
                        $kanbanItem = TasksUtil::createKanbanItemFromTask($task);
                    }

                    $kanbanItemsArray[$kanbanItem->type][] = $kanbanItem->id;
                }
            }

            $stats = array();
            $kanbanTypeDropDownData = KanbanItem::getTypeDropDownArray();
            foreach ($kanbanTypeDropDownData as $type => $label)
            {
                if (isset($kanbanItemsArray[$type]))
                {
                    $stats[$type] = count($kanbanItemsArray[$type]);
                }
                else
                {
                    $stats[$type] = 0;
                }
            }
            $stats['completionPercent'] = static::resolveCompletionPercentage($completedTasksCount, $totalTasksToDoCount);
            return $stats;
        }

        /**
         * Resolve completion percentags
         * @param int $completedTodosCount
         * @param int $totalToDosCount
         * @return int
         */
        protected static function resolveCompletionPercentage($completedTodosCount, $totalToDosCount)
        {
            if ($totalToDosCount != 0)
            {
                $completionPercent = ($completedTodosCount/$totalToDosCount)*100;
            }
            else
            {
                $completionPercent = 0;
            }
            return round($completionPercent, 2);
        }

        /**
         * Get active project information for dashboard
         * @param array $project
         * @return string
         */
        public static function getActiveProjectInformationForDashboard(Project $project)
        {
            $content = static::resolveProjectLinkWithRedirectURl($project->name, $project->id);
            $stats = static::groupTasksByKanbanTypeAndGetStats($project);
            $kanbanTypes = KanbanItem::getTypeDropDownArray();
            foreach ($stats as $key => $value)
            {
                if ($key != 'completionPercent')
                {
                    $content .= ZurmoHtml::tag('div', array('class' => 'project-stats'),
                                            ZurmoHtml::tag('strong', array(), $value) .
                                            ZurmoHtml::tag('span', array(), $kanbanTypes[$key]));
                }
                else
                {
                    $label = '% ' . Zurmo::t('Core', 'Complete');
                    $color = (int) $value > 0 ? 'percent-yellow' : 'percent-red';
                    $color = (int) $value == 100 ? 'percent-green' : $color;
                    $content .= ZurmoHtml::tag('div', array('class' => 'project-stats percent-complete ' . $color),
                                            ZurmoHtml::tag('strong', array(), $value) .
                                            ZurmoHtml::tag('span', array(), $label));
                }
            }
            return $content;
        }
    }
?>