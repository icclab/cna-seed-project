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
     * Helper class for working with projects
     */
    class ProjectsUtil
    {
        /**
         * Logs event on adding task check item for the task
         * @param Task $task
         * @param TaskCheckListItem
         */
        public static function logTaskCheckItemEvent(Task $task, TaskCheckListItem $taskCheckListItem)
        {
            assert('$task instanceof Task');
            assert('$taskCheckListItem instanceof TaskCheckListItem');
            $project = $task->project;
            $data    = array('{taskname}' => $task->name, '{taskcheckitemname}' => $taskCheckListItem->name);
            ProjectAuditEvent::logAuditEvent(ProjectAuditEvent::CHECKLIST_ITEM_ADDED, $project, $data);
        }

        /**
         * Logs event on changing task status
         * @param Task $task
         * @param string $currentStatusLabel
         * @param string $newStatusLabel
         */
        public static function logTaskStatusChangeEvent(Task $task, $currentStatusLabel, $newStatusLabel)
        {
            assert('$task instanceof Task');
            assert('is_string($currentStatusLabel)');
            assert('is_string($newStatusLabel)');
            if ($currentStatusLabel != $newStatusLabel)
            {
                $project = $task->project;
                $data    = array('{fromstatus}' => $currentStatusLabel, '{tostatus}' => $newStatusLabel);
                ProjectAuditEvent::logAuditEvent(ProjectAuditEvent::TASK_STATUS_CHANGED, $project, $data);
            }
        }

        /**
         * Logs event on adding task to the project
         * @param Task $task
         */
        public static function logAddTaskEvent(Task $task)
        {
            assert('$task instanceof Task');
            $project = $task->project;
            $data    = array('{taskname}' => $task->name);
            ProjectAuditEvent::logAuditEvent(ProjectAuditEvent::TASK_ADDED, $project, $data);
        }

        /**
         * Logs event on adding comment to the task
         * @param Task $task
         * @param string $comment
         */
        public static function logAddCommentEvent(Task $task, $comment)
        {
            assert('$task instanceof Task');
            assert('is_string($comment)');
            $project = $task->project;
            $data    = array('{comment}' => $comment);
            ProjectAuditEvent::logAuditEvent(ProjectAuditEvent::COMMENT_ADDED, $project, $data);
        }
    }
?>