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

    class UserSwitcherElement extends UserElement
    {
        protected function resolveOnSelectionOptionAttributeNameForAutoComplete()
        {
            return 'label';
        }

        protected function getOnSelectOptionForAutoComplete($idInputName)
        {
            // Begin Not Coding Standard
            return 'js:function(event, ui){
                        switchUserByLabel(ui.item["'. $this->resolveOnSelectionOptionAttributeNameForAutoComplete() .'"]);
                    }';
            // End Not Coding Standard
        }

        protected function renderControlEditable()
        {
            $this->registerSwitchUserScript();
            return $this->renderEditableContent();
        }

        protected function registerSwitchUserScript()
        {
            Yii::app()->clientScript->registerScript('switchUserFunctions', '
            function switchUser(username)
            {
                var url                 = "' . $this->resolveUserSwitchUrl() . '";
                qualifiedUrl            = url + "?username=" + username;
                window.location.href    = qualifiedUrl;
            }

            function switchUserByLabel(label)
            {
                var username    = label.slice(label.indexOf("(") + 1, -1);
                switchUser(username);
            }
            ', CClientScript::POS_HEAD);
        }

        protected function resolveUserSwitchUrl()
        {
            return Yii::app()->createUrl('/users/default/switchTo');
        }

        protected function getSelectLinkUrlParams()
        {
            return $this->mergeOptionsWithExcludeRootUserOptions(parent::getSelectLinkUrlParams());
        }

        protected function getAutoCompleteUrlParams()
        {
            return $this->mergeOptionsWithExcludeRootUserOptions(parent::getAutoCompleteUrlParams());
        }

        protected function resolveExcludeRootUserOptions()
        {
            $options        = array('excludeRootUsers' => true);
            $encodedOptions = ArrayUtil::encodeAutoCompleteOptionsArray($options);
            return $encodedOptions;
        }

        protected function mergeOptionsWithExcludeRootUserOptions(array $options)
        {
            $autoCompleteOptions        = array(
                'autoCompleteOptions'       => $this->resolveExcludeRootUserOptions(),
            );
            return CMap::mergeArray($options, $autoCompleteOptions);
        }

        protected function renderSelectLink()
        {
            // disabled the select link due to: https://www.pivotaltracker.com/n/projects/380027/stories/66773464
            // update of: 1st July 12:24pm
            return null;
        }
    }
?>
