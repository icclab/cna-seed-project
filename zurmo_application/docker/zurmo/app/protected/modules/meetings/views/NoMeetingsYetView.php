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
     * View for showing in the user interface when the user does not have any meeting yet for specific date.
     */
    class NoMeetingsYetView extends View
    {
        public $cssClasses = array('splash-view clearfix');
        protected $redirectUrl;
        protected $controllerId;
        protected $moduleId;
        protected $relationModel;
        protected $relationModuleId;
        protected $stringTime;

        public function __construct($redirectUrl , $controllerId, $moduleId, $relationModel, $relationModuleId, $stringTime)
        {
            $this->redirectUrl = $redirectUrl;
            $this->controllerId = $controllerId;
            $this->moduleId = $moduleId;
            $this->relationModel = $relationModel;
            $this->relationModuleId = $relationModuleId;
            $this->stringTime = $stringTime;
        }

        protected function renderContent()
        {
            $url = $this->getCreateMeetingUrl();
            $content = ZurmoHtml::openTag('div', array('class' => $this->getIconName()));
            $content .= $this->getMessageContent();
            if (RightsUtil::doesUserHaveAllowByRightName('MeetingsModule', MeetingsModule::getCreateRight(),
                Yii::app()->user->userModel))
            {
                $content .= ZurmoHtml::link(ZurmoHtml::wrapLabel($this->getCreateLinkDisplayLabel()),
                                        $url, array('class' => 'z-button green-button'));
            }
            $content .= ZurmoHtml::closeTag('div');
            return $content;
        }

        protected function getIconName()
        {
            return 'NoMeetings';
        }

        protected function getCreateLinkDisplayLabel()
        {
            return Zurmo::t('MeetingsModule', 'Create Meeting');
        }

        protected function getMessageContent()
        {
            return '<h2>' . Zurmo::t('MeetingsModule', 'No meeting scheduled') . '.</h2><div class="large-icon"></div>';
        }

        protected function getCreateMeetingUrl()
        {
            if (!$this->relationModel && !$this->relationModuleId)
            {
                return Yii::app()->createUrl('/meetings/default/createMeeting',
                                             array('redirectUrl' => $this->redirectUrl, 'startDate' => $this->stringTime));
            }
            else
            {
                $params = array(
                    'relationAttributeName' => get_class($this->relationModel),
                    'relationModelId'       => $this->relationModel->id,
                    'relationModuleId'      => $this->relationModuleId,
                    'startDate'             => $this->stringTime,
                    'redirectUrl'           => $this->redirectUrl,
                );
                return Yii::app()->createUrl($this->moduleId . '/' .
                                        $this->controllerId . '/createFromRelationAndStartDate/', $params);
            }
        }
    }
?>
