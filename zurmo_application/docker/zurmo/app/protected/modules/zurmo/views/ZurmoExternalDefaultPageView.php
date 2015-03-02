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
     * Base class for module page views.  Constructs the ZurmoExternalDefaultView.
     */
    abstract class ZurmoExternalDefaultPageView extends ZurmoPageView
    {
        public function __construct(View $view)
        {
            parent::__construct(new ZurmoExternalDefaultView($view));
        }

        public function render()
        {
            static::registerAllPagesScriptFiles();
            static::registerExternalPageScriptFiles();
            $content =  $this->renderXHtmlHead()      .
                        $this->renderXHtmlBodyStart() .
                        View::render()                .
                        $this->renderXHtmlBodyEnd();
            Yii::app()->getClientScript()->render($content);
            return $content;
        }

        protected function renderXHtmlHead()
        {
            $themeName              = Yii::app()->theme->name;
            $themeBaseUrl           = Yii::app()->getRequest()->getHostInfo() .
                                      Yii::app()->themeManager->baseUrl . '/' . $themeName;
            $cs = Yii::app()->getClientScript();
            $specialCssContent = null;
            $publishedAssetsPath = Yii::app()->getRequest()->getHostInfo() . Yii::app()->assetManager->publish(
                    Yii::getPathOfAlias("application.core.views.assets.fonts"));
            $specialCssContent .= "<style>" .
                                    "@font-face" .
                                    "{" .
                                    "font-family: 'zurmo_gamification_symbly_rRg';" .
                                    "src: url('{$publishedAssetsPath}/zurmogamificationsymblyregular-regular-webfont.eot');" .
                                    "src: url('{$publishedAssetsPath}/zurmogamificationsymblyregular-regular-webfont.eot?#iefix') format('embedded-opentype'), " .
                                    "url('{$publishedAssetsPath}/zurmogamificationsymblyregular-regular-webfont.woff') format('woff'), " .
                                    "url('{$publishedAssetsPath}/zurmogamificationsymblyregular-regular-webfont.ttf') format('truetype'), " .
                                    "url('{$publishedAssetsPath}/zurmogamificationsymblyregular-regular-webfont.svg#zurmo_gamification_symbly_rRg') format('svg');" .
                                    "font-weight: normal;" .
                                    "font-style: normal;" .
                                    "unicode-range: U+00-FFFF;" . // Not Coding Standard
                                    "}" .
                                    "</style>";
            if (!MINIFY_SCRIPTS && Yii::app()->isApplicationInstalled())
            {
                $specialCssContent .= '<link rel="stylesheet/less" type="text/css" id="default-theme" href="' .
                    $themeBaseUrl . '/less/default-theme.less"/>';
                if (Yii::app()->userInterface->isMobile())
                {
                    $specialCssContent .= '<link rel="stylesheet/less" type="text/css" id="mobile" href="' .
                        $themeBaseUrl . '/less/mobile.less"/>';
                }
                $specialCssContent .= '<!--[if lt IE 9]><link rel="stylesheet/less" type="text/css" href="' .
                    $themeBaseUrl . '/less/ie.less"/><![endif]-->';
            }
            else
            {
                Yii::app()->themeManager->registerThemeColorCss();
                if (file_exists("themes/$themeName/css/commercial.css"))
                {
                    $cs->registerCssFile($themeBaseUrl . '/css/commercial.css' .
                        ZurmoAssetManager::getCssAndJavascriptHashQueryString("themes/$themeName/css/commercial.css"));
                }
                if (file_exists("themes/$themeName/css/custom.css"))
                {
                    $cs->registerCssFile($themeBaseUrl . '/css/custom.css' .
                        ZurmoAssetManager::getCssAndJavascriptHashQueryString("themes/$themeName/css/custom.css"));
                }
                if (Yii::app()->userInterface->isMobile())
                {
                    //todo: remove this?
                    $cs->registerCssFile($themeBaseUrl . '/css/mobile.css');
                }
                if (Yii::app()->getRequest()->isContextiveExternalRequest())
                {
                    $cs->registerCssFile($themeBaseUrl . '/css/gmail.css' .
                        ZurmoAssetManager::getCssAndJavascriptHashQueryString("themes/$themeName/css/gmail.css"));
                }
                if (Yii::app()->getClientScript()->isIsolationMode())
                {
                    $cs->registerCssFile($themeBaseUrl . '/css/webforms-external.css' .
                        ZurmoAssetManager::getCssAndJavascriptHashQueryString("themes/$themeName/css/webforms-external.css"));
                }
            }
            if (MINIFY_SCRIPTS)
            {
                Yii::app()->minScript->generateScriptMap('css');
                Yii::app()->minScript->generateScriptMap('css-color');
            }
            if (Yii::app()->browser->getName() == 'msie' && Yii::app()->browser->getVersion() < 9)
            {
                $cs->registerCssFile($themeBaseUrl . '/css/ie.css' .
                    ZurmoAssetManager::getCssAndJavascriptHashQueryString("themes/$themeName/css/ie.css"), 'screen, projection');
            }

            foreach ($this->getStyles() as $style)
            {
                if ($style != 'ie')
                {
                    if (file_exists("themes/$themeName/css/$style.css"))
                    {
                        // Begin Not Coding Standard
                        $cs->registerCssFile($themeBaseUrl . '/css/' . $style. '.css' .
                            ZurmoAssetManager::getCssAndJavascriptHashQueryString("themes/$themeName/css/$style.css")); // Not Coding Standard
                        // End Not Coding Standard
                    }
                }
            }
            return '<head>' . $specialCssContent . '</head>';
        }

        public static function registerExternalPageScriptFiles()
        {
            if (!MINIFY_SCRIPTS && Yii::app()->isApplicationInstalled())
            {
                Yii::app()->clientScript->registerScriptFile(
                    Yii::app()->getAssetManager()->publish(
                        Yii::getPathOfAlias('application.core.views.assets')) . '/less-1.2.0.min.js');
            }
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.views.assets')) . '/interactions.js');
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.views.assets')) . '/jquery.truncateText.js');
        }
    }
?>