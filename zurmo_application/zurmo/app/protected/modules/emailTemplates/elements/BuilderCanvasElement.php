<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class BuilderCanvasElement extends BuilderContainerElement
    {
        protected $shouldWrapCenterTagAroundTdForNonEditable = true;

        public static function resolveLabel()
        {
            return Zurmo::t('EmailTemplatesModule', 'Canvas');
        }

        protected function resolveWrapperNonEditable($elementContent, array $customDataAttributes,
                                                     $actionsOverlay)
        {
            $content    = parent::resolveWrapperNonEditable($elementContent, $customDataAttributes,
                                                            $actionsOverlay);
            $content    = $this->normalizeHtmlContent($content);
            return $content;
        }

        protected function resolveNonEditableContentWrappingTdHtmlOptions()
        {
            return array('class' => BaseBuilderElement::BUILDER_ELEMENT_SORTABLE_ROWS_CLASS . ' ui-sortable', 'align' => 'center', 'valign' => 'top');
        }

        protected function renderSettingsTab(ZurmoActiveForm $form)
        {
            $propertiesForm     = BuilderElementBackgroundPropertiesEditableElementsUtil::render($this->model, $form);
            $propertiesForm     .= BuilderElementTextPropertiesEditableElementsUtil::render($this->model, $form);
            $propertiesForm     .= BuilderElementBorderPropertiesEditableElementsUtil::render($this->model, $form);
            return $propertiesForm;
        }

        protected function resolveAvailableNonEditableActionsArray()
        {
            return array(static::OVERLAY_ACTION_EDIT);
        }

        /**
         * Override to hide the canvas icons.  There is only an 'edit' link and this is controlled via the toolbar
         * @param $action
         * @return string
         */
        protected function resolveAvailableNonEditableActionLinkSpan($action)
        {
            $iconContent = ZurmoHtml::tag('i', array('class' => 'icon-' . $action), '');
            return         ZurmoHtml::tag('span', array('class' => $action, 'style' => 'display:none;'), $iconContent);
        }

        protected function resolveNonEditableWrapperHtmlOptions()
        {
            $parentOptions          = parent::resolveNonEditableWrapperHtmlOptions();
            $parentOptions['class'] .= ' body';
            return $parentOptions;
        }

        /**
         * Normalizes html content by adding missing nodes.
         * @param $content
         * @return mixed
         */
        protected function normalizeHtmlContent($content)
        {
            $doctype        = '<!DOCTYPE html  PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
            $headContent    = $this->renderHtmlHead();
            $bodyContent    = $this->renderHtmlBody($content);
            $html           = ZurmoHtml::tag('html', array('xmlns' => 'http://www.w3.org/1999/xhtml'), $headContent . $bodyContent);
            $content        = $doctype . $html;
            return $content;
        }

        protected function renderHtmlBody($content)
        {
            $bodyContent    = ZurmoHtml::tag('body', array(), $content);
            return $bodyContent;
        }

        protected function renderHtmlHead()
        {
            $headContent   = $this->renderMetaTag();
            $headContent  .= $this->renderCss();
            if ($this->renderForCanvas)
            {
                if (MINIFY_SCRIPTS)
                {
                    $headContent .= $this->renderBuilderCssTools();
                }
                else
                {
                    $headContent .= $this->renderLess();
                }
            }
            $headContent    = ZurmoHtml::tag('head', array(), $headContent);
            return $headContent;
        }

        protected function renderMetaTag()
        {
            return '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'; // Not Coding Standard
        }

        protected function renderCss()
        {
            $css    = $this->renderIconFontCss();
            $css   .= $this->resolveCanvasGlobalCssContent();
            return $css;
        }

        protected function renderBuilderCssTools()
        {
            $cs = Yii::app()->getClientScript();
            $themeName = Yii::app()->theme->name;
            $baseUrl   = Yii::app()->themeManager->baseUrl . '/default';
            $cs->registerCssFile($baseUrl . '/css/builder-iframe-tools.css' .
                ZurmoAssetManager::getCssAndJavascriptHashQueryString("themes/$themeName/css/builder-iframe-tools.css"));
        }

        protected function renderLess()
        {
            $baseUrl = Yii::app()->themeManager->baseUrl . '/default';
            $publishedAssetsPath = Yii::app()->assetManager->publish(Yii::getPathOfAlias("application.core.views.assets"));
            $less = '<link rel="stylesheet/less" type="text/css" id="default-theme" href="' . $baseUrl . '/less/builder-iframe-tools.less"/>
                     <script type="text/javascript" src="' . $publishedAssetsPath . '/less-1.2.0.min.js"></script>';
            return $less;
        }

        protected function renderIconFontCss()
        {
            //TODO: @sergio: Shouldnt we move this thing to a file too?
            if ($this->renderForCanvas)
            {
                $publishedAssetsPath = Yii::app()->assetManager->publish(Yii::getPathOfAlias("application.core.views.assets.fonts"));
                $iconsFont = "<style>" .
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
                return $iconsFont;
            }
        }

        protected function resolveCanvasGlobalCssPath()
        {
            $path          = Yii::getPathOfAlias('application.modules.emailTemplates.widgets.assets');
            Yii::app()->getAssetManager()->publish($path);
            return $path . '/zurb.css';
        }

        protected function resolveCanvasOverrideCssPath()
        {
            $path          = Yii::getPathOfAlias('application.modules.emailTemplates.widgets.assets');
            Yii::app()->getAssetManager()->publish($path);
            return $path . '/zurmo-zurb.css';
        }

        protected function resolveCanvasGlobalCssContent()
        {
            $path   = $this->resolveCanvasGlobalCssPath();
            $overridePath = $this->resolveCanvasOverrideCssPath();
            $css    = ZurmoHtml::tag('style', array('type' => 'text/css' ), file_get_contents($path) . file_get_contents($overridePath));
            return $css;
        }
    }
?>