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
     * Class for showing a message and create link when there are no tasks visible to the logged
     * in user for a project when going to the project detail view
     */
    class ZeroTasksForRelatedModelYetView extends ZeroModelsYetView
    {
        protected $relatedModelClassName;

        /**
         * @param string $controllerId
         * @param string $moduleId
         * @param string $modelClassName
         * @param string $relatedModelClassName
         */
        public function __construct($controllerId, $moduleId, $modelClassName, $relatedModelClassName)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($modelClassName)');
            assert('is_string($relatedModelClassName)');
            $this->controllerId   = $controllerId;
            $this->moduleId       = $moduleId;
            $this->modelClassName = $modelClassName;
            $this->relatedModelClassName = $relatedModelClassName;
        }

        /**
         * Gets create link display label
         * @return string
         */
        protected function getCreateLinkDisplayLabel()
        {
            return null;
        }

        /**
         * Gets message content
         * @return string
         */
        protected function getMessageContent()
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            return Zurmo::t('TasksModule',
                            '<h2>"Nothing is so fatiguing as the eternal hanging on of an uncompleted task."</h2>' .
                            '<i>- William James</i>' .
                            '<div class="large-icon"></div><p>' .
                            'Don\'t be fatigued by your uncompleted tasks. <br/>
                                Create a task record and keep track of it until you complete it.</p>', $params);
        }

        /**
         * Renders content for zero model view
         * @return string
         */
        protected function renderContent()
        {
            $params  = $this->getCreateLinkParams();
            $content = ZurmoHtml::tag('div', array('class' => $this->getIconName()), $this->getMessageContent());
            return $content;
        }
    }
?>
