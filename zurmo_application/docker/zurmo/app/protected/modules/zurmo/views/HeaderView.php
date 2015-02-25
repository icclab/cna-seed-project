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

    class HeaderView extends View
    {
        /**
         * @var string
         */
        protected $applicationName;

        /**
         * @var array
         */
        protected $moduleNamesAndLabels;

        /**
         * @var string
         */
        protected $sourceUrl;

        /**
         * @var array
         */
        protected $shortcutsCreateMenuItems;

        /**
         * @param array $settingsMenuItems
         * @param array $userMenuItems
         * @param array $shortcutsCreateMenuItems
         * @param array $moduleNamesAndLabels
         * @param string $sourceUrl
         * @param string $applicationName
         */
        public function __construct($settingsMenuItems, $userMenuItems,
                                    $shortcutsCreateMenuItems,
                                    $moduleNamesAndLabels, $sourceUrl, $applicationName)
        {
            assert('is_array($settingsMenuItems)');
            assert('is_array($userMenuItems)');
            assert('is_array($shortcutsCreateMenuItems)');
            assert('is_array($moduleNamesAndLabels)');
            assert('is_string($sourceUrl)');
            assert('is_string($applicationName) || $applicationName == null');
            $this->applicationName          = $applicationName;
            $this->moduleNamesAndLabels     = $moduleNamesAndLabels;
            $this->sourceUrl                = $sourceUrl;
            $this->shortcutsCreateMenuItems = $shortcutsCreateMenuItems;
            $this->settingsMenuItems        = $settingsMenuItems;
            $this->userMenuItems            = $userMenuItems;
        }

        protected function renderContent()
        {
            $this->renderLoginRequiredAjaxResponse();

            $logoAndSearchContent = $this->renderLogoAndSearchContent();
            $userActionsContent   = $this->renderUserActionsContent();
            $content  = ZurmoHtml::tag('div', array('class' => 'logo-and-search'), $logoAndSearchContent);
            $content .= ZurmoHtml::tag('div', array('class' => 'user-actions clearfix'), $userActionsContent);
            return ZurmoHtml::tag('div', array('class' => 'container clearfix'), $content);
        }

        protected function renderLogoAndSearchContent()
        {
            $content  = $this->resolveAndRenderLogoContent();
            $content .= $this->resolveAndRenderGlobalSearchContent();
            return $content;
        }

        protected function resolveAndRenderGlobalSearchContent()
        {
            $globalSearchView = new GlobalSearchView($this->moduleNamesAndLabels, $this->sourceUrl);
            return $globalSearchView->render();
        }

        protected function resolveAndRenderLogoContent()
        {
            $homeUrl   = Yii::app()->createUrl('home/default');
            $content   = null;
            if ($logoFileModelId = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'logoFileModelId'))
            {
                $logoFileModel = FileModel::getById($logoFileModelId);
                $logoFileSrc   = Yii::app()->getAssetManager()->getPublishedUrl(Yii::getPathOfAlias('application.runtime.uploads') .
                    DIRECTORY_SEPARATOR . $logoFileModel->name);
            }
            else
            {
                $logoFileSrc   = Yii::app()->themeManager->baseUrl . '/default/images/Zurmo_logo.png';
            }
            $logoHeight = ZurmoUserInterfaceConfigurationFormAdapter::resolveLogoHeight();
            $logoWidth  = ZurmoUserInterfaceConfigurationFormAdapter::resolveLogoWidth();
            if (Yii::app()->userInterface->isMobile())
            {
                //make sure width and height are NEVER defined
                $content   .= '<img src="' . $logoFileSrc . '" alt="Zurmo Logo" />';
            }
            else
            {
                $content   .= '<img src="' . $logoFileSrc . '" alt="Zurmo Logo" height="'
                              . $logoHeight .'" width="' . $logoWidth .'" />';
            }
            if ($this->applicationName != null)
            {
                $content  .= ZurmoHtml::tag('span', array(), $this->applicationName);
            }
            return ZurmoHtml::link($content, $homeUrl, array('class' => 'clearfix', 'id' => 'corp-logo'));
        }

        protected function renderUserActionsContent()
        {
            $headerLinksView = new HeaderLinksView($this->settingsMenuItems, $this->userMenuItems);
            $content  = $headerLinksView->render();
            $content .= $this->resolveAndRenderShortcutsContent();
            return $content;
        }

        protected function resolveAndRenderShortcutsContent()
        {
            $shortcutsCreateMenuView = new ShortcutsCreateMenuView(
                Yii::app()->controller->getId(),
                Yii::app()->controller->getModule()->getId(),
                $this->shortcutsCreateMenuItems
            );
            return $shortcutsCreateMenuView->render();
        }

        protected function renderLoginRequiredAjaxResponse()
        {
            if (Yii::app()->user->loginRequiredAjaxResponse)
            {
                Yii::app()->clientScript->registerCoreScript('cookie');
                Yii::app()->clientScript->registerScript('ajaxLoginRequired', '
                    jQuery("body").ajaxComplete(
                        function(event, request, options)
                        {
                            if (request.responseText == "' . Yii::app()->user->loginRequiredAjaxResponse . '")
                            {
                                $.cookie("' . Yii::app()->user->loginRequiredAjaxResponse . 'Cookie", 1,
                                        {
                                            expires : 1,
                                            path:  "/"
                                        });
                                window.location.reload(true);
                            }
                        }
                    );
                ');
            }
        }

        protected function getContainerWrapperTag()
        {
            return 'header';
        }
    }
?>
