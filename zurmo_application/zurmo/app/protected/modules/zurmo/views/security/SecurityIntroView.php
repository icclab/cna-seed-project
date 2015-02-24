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
     * View when a user first comes to roles or groups. Provides an overview of how security works
     */
    class SecurityIntroView extends IntroView
    {
        protected function renderIntroContent()
        {
            $this->registerScripts();
            $content  = $this->renderBasicIntroContent();
            $content .= $this->renderAdvancedLinkContent();
            $content .= $this->renderAdvancedIntroContent();
            return $content;
        }

        protected function renderBasicIntroContent()
        {
            $params   = LabelUtil::getTranslationParamsForAllModules();
            $content  = '<h1>' . Zurmo::t('ZurmoModule', 'How does security work in Zurmo?', $params). '</h1>';
            $content .= '<div id="security-basic-intro" class="module-intro-steps clearfix">';
            $content .= '<div class="third security-rights"><span class="icon"></span>';
            $content .= '<p><strong>' . Zurmo::t('ZurmoModule', 'Rights') . '</strong>';
            $content .= Zurmo::t('ZurmoModule', 'Rights control who can access modules, create records, and delete records in a module.');
            $content .= '</p>';
            $content .= '</div>';
            $content .= '<div class="third security-permissions"><span class="icon"></span>';
            $content .= '<p><strong>' . Zurmo::t('ZurmoModule', 'Permissions') . '</strong>';
            $content .= Zurmo::t('ZurmoModule', 'Permissions control who can read, write, and delete specific records.');
            $content .= '</p>';
            $content .= '</div>';
            $content .= '<div class="third security-roles"><span class="icon"></span>';
            $content .= '<p><strong>' . Zurmo::t('ZurmoModule', 'Roles') . '</strong>';
            $content .= Zurmo::t('ZurmoModule', 'Roles expand visibility allowing managers to read/write their employees\' records.');
            $content .= '</p>';
            $content .= '</div>';
            $content .= '</div>';
            return $content;
        }

        protected function renderAdvancedLinkContent()
        {
            $content = Zurmo::t('ZurmoModule', 'Read more on advanced security features</u></b>');
            return ZurmoHtml::tag('a', array('id' => 'security-advanced-toggle', 'class' => 'simple-link', 'href' => '#'), $content);
        }

        protected function renderAdvancedIntroContent()
        {
            $content  = '<div id="security-advanced-intro" class="module-intro-steps clearfix" style="display:none;">';
            $content .= '<div class="third security-groups"><span class="icon"></span>';
            $content .= '<p><strong>' . Zurmo::t('ZurmoModule', 'Groups') . '</strong>';
            $content .= Zurmo::t('ZurmoModule', 'Groups are used to restrict rights and permissions for specific users.');
            $content .= '</p>';
            $content .= '</div>';
            $content .= '<div class="third security-nested-groups"><span class="icon"></span>';
            $content .= '<p><strong>' . Zurmo::t('ZurmoModule', 'Nested Groups') . '</strong>';
            $content .= Zurmo::t('ZurmoModule', 'Nested groups or \'children\' groups allow additional flexibility in controlling what rights and permissions are restricted for users.');
            $content .= '</p>';
            $content .= '</div>';
            $content .= '<div class="third security-adhoc-sharing"><span class="icon"></span>';
            $content .= '<p><strong>' . Zurmo::t('ZurmoModule', 'Ad-hoc Sharing') . '</strong>';
            $content .= Zurmo::t('ZurmoModule', 'Groups and nested groups can also be used to share records ad-hoc.');
            $content .= '</p>';
            $content .= '</div>';
            $content .= '</div>';
            return $content;
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            $content = "$('#security-advanced-toggle').click(function()
                         {
                             $('#security-advanced-intro').toggle();
                             return false;
                         });";
            Yii::app()->clientScript->registerScript('SecurityIntroAdvancedToggle', $content);
        }
    }
?>
