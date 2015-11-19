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

    class UserInterfaceChooserView extends View
    {
        protected function renderContent()
        {
            return $this->renderUserInterfaceTypeSelector();
        }

        /**
         * Render section for selection user interface type.
         * Show only if user is using mobile and tablet devices.
         */
        protected function renderUserInterfaceTypeSelector()
        {
            if (Yii::app()->userInterface->isMobile())
            {
                $mobileActive  = ' active';
                $desktopActive = null;
            }
            else
            {
                $mobileActive  = null;
                $desktopActive = ' active';
            }
            $content   = ZurmoHtml::link(ZurmoHtml::tag('i', array('class' => 'icon-mobile' . $mobileActive), ''),
                            Yii::app()->createUrl('zurmo/default/userInterface', array('userInterface' => UserInterface::MOBILE)),
                            array('title' => Zurmo::t('ZurmoModule', 'Show Mobile')));

            $content  .= ZurmoHtml::link(ZurmoHtml::tag('i', array('class' => 'icon-desktop' . $desktopActive), ''),
                            Yii::app()->createUrl('zurmo/default/userInterface', array('userInterface' => UserInterface::DESKTOP)),
                            array('title' => Zurmo::t('ZurmoModule', 'Show Full')));

            $content   = ZurmoHtml::tag('div', array('class' => 'device'), $content);

            $collapser = ZurmoHtml::link(ZurmoHtml::tag('i', $this->getCollapserLinkOptions(), ''),
                            Yii::app()->createUrl('zurmo/default/toggleCollapse', array('returnUrl' => Yii::app()->request->url)),
                            array('title' => Zurmo::t('ZurmoModule', 'Collapse or Expand')));
            return $collapser . $content;
        }

        protected function getCollapserLinkOptions()
        {
            $options = array();
            if (Yii::app()->userInterface->isMenuCollapsed())
            {
                $options['class'] = 'icon-expand';
            }
            else
            {
                $options['class'] = 'icon-collapse';
            }
            return $options;
        }

        protected function renderContainerWrapperId()
        {
            return false;
        }

        protected function resolveDefaultClasses()
        {
            return array('ui-chooser');
        }
    }
?>
