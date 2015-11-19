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
     * Column adapter for project in feeds on dashboard
     */
    class ProjectFeedListViewColumnAdapter extends TextListViewColumnAdapter
    {
        /**
         * Renders grid view data
         * @return array
         */
        public function renderGridViewData()
        {
            return array(
                    'name'  => $this->attribute,
                    'value' => 'ProjectFeedListViewColumnAdapter::getFeedInformationForDashboard($data)',
                    'type'  => 'raw'
                );
        }

        /**
         * Get feed information if projects for user
         * @param ProjectAuditEvent $projectAuditEvent
         * @return string
         */
        public static function getFeedInformationForDashboard(ProjectAuditEvent $projectAuditEvent)
        {
            assert('$projectAuditEvent instanceof ProjectAuditEvent');
            $project           = Project::getById(intval($projectAuditEvent->project->id));
            $dateTime          = DateTimeUtil::getTimeSinceDisplayContent($projectAuditEvent->dateTime);
            $data              = array('{timeSpanLabel}' => $dateTime);
            if (ActionSecurityUtil::canCurrentUserPerformAction('Details', $project))
            {
                $projectName            = static::resolveProjectName($project);
                $data['{projectname}']  = $projectName;
                $user                   = User::getById($projectAuditEvent->user->id);
                $data['{username}']     = $user->getFullName();
                $unserializedData       = unserialize($projectAuditEvent->serializedData);
                if (is_array($unserializedData))
                {
                    $data = array_merge($unserializedData, $data);
                }
            }
            else
            {
                return Zurmo::t('ProjectsModule', '<strong>Activity on a restricted project
                                                   </strong> <small>about {timeSpanLabel}</small>', $data);
            }
            return static::getMessageContentByEventAndData($projectAuditEvent->eventName, $data);
        }

        /**
         * Resolve project name with link to details url
         * @param Project $project
         * @return string
         */
        protected static function resolveProjectName($project)
        {
            assert('$project instanceof Project');
            return ZurmoHtml::link($project->name, Yii::app()->createUrl('projects/default/details', array('id' => $project->id)));
        }

        /**
         * Get message content by event and data
         * @param string $event
         * @param array $data
         * @return string
         */
        public static function getMessageContentByEventAndData($event, $data)
        {
            assert('is_string($event)');
            assert('is_array($data)');
            if ($event == ProjectAuditEvent::PROJECT_CREATED)
            {
                return Zurmo::t('ProjectsModule', '<strong><i>{projectname}</i></strong> is added by user
                                                   <strong>{username}</strong>
                                                   <small>about {timeSpanLabel}</small>', $data);
            }
            elseif ($event == ProjectAuditEvent::PROJECT_ARCHIVED)
            {
                return Zurmo::t('ProjectsModule', '<strong><i>{projectname}</i></strong> is archived by user
                                                   <strong>{username}</strong>
                                                   <small>about {timeSpanLabel}</small>', $data);
            }
            elseif ($event == ProjectAuditEvent::TASK_STATUS_CHANGED)
            {
                return Zurmo::t('ProjectsModule', '<strong><i>{projectname}</i> {username} changed status
                                                  from "{fromstatus} to {tostatus}"</strong>
                                                  <small>about {timeSpanLabel}</small>', $data);
            }
            elseif ($event == ProjectAuditEvent::TASK_ADDED)
            {
                return Zurmo::t('ProjectsModule', '<strong><i>{projectname}</i> {username}
                                                    added task "{taskname}"</strong>
                                                    <small>about {timeSpanLabel}</small>', $data);
            }
            elseif ($event == ProjectAuditEvent::COMMENT_ADDED)
            {
                return Zurmo::t('ProjectsModule', '<strong><i>{projectname}</i> {username}
                                                    added comment "{comment}"</strong>
                                                    <small>about {timeSpanLabel}</small>', $data);
            }
            elseif ($event == ProjectAuditEvent::CHECKLIST_ITEM_ADDED)
            {
                return Zurmo::t('ProjectsModule', '<strong><i>{projectname}</i> {username}
                                                  added checklist item "{taskcheckitemname} in Task {taskname}"
                                                  </strong> <small>about {timeSpanLabel}</small>', $data);
            }
        }
    }
?>