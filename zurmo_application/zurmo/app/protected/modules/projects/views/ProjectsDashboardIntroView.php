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
     * View when a user first comes to the projects dashboard. Provides an overview of how projects are working
     */
    class ProjectsDashboardIntroView extends IntroView
    {
        /**
         * Renders introduction content
         * @return string
         */
        protected function renderIntroContent()
        {
            $content  = '<h1>' . Zurmo::t('ProjectsModule', 'How do Projects work in Zurmo?', LabelUtil::getTranslationParamsForAllModules()). '</h1>';
            $content .= '<div id="projects-intro-steps" class="module-intro-steps clearfix">';
            $content .= '<div class="third project-create"><span class="icon"></span>';
            $content .= '<p><strong>' . Zurmo::t('Core', 'Create') . '</strong>';
            $content .= Zurmo::t('ProjectsModule', 'Create projects, add tasks and work using an agile methodology');
            $content .= '</p>';
            $content .= '</div>';
            $content .= '<div class="third project-collaborate"><span class="icon"></span>';
            $content .= '<p><strong>' . Zurmo::t('Core', 'Collaborate') . '</strong>';
            $content .= Zurmo::t('ProjectsModule', 'Collaborate with users on tasks to get things done');
            $content .= '</p>';
            $content .= '</div>';
            $content .= '<div class="third project-track"><span class="icon"></span>';
            $content .= '<p><strong>' . Zurmo::t('Core', 'Track') . '</strong>';
            $content .= Zurmo::t('ProjectsModule', 'Subscribe to notifications and track progress against milestones');
            $content .= '</p>';
            $content .= '</div>';
            $content .= '</div>';
            $this->registerScripts();
            return $content;
        }

        /**
         * Register scripts
         */
    }
?>
