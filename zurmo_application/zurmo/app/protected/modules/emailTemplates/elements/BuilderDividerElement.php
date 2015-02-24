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

    class BuilderDividerElement extends BaseBuilderTableWrappedElement
    {
        public static function isUIAccessible()
        {
            return true;
        }

        protected static function resolveLabel()
        {
            return Zurmo::t('EmailTemplatesModule', 'Divider');
        }

        protected function resolveDefaultProperties()
        {
            $properties              = array(
                'backend'   => array(
                    'divider-padding'           => '10',
                ),
                'frontend'   => array(
                    'inlineStyles'  => array(
                        'border-top-width'              => '1',
                        'border-top-style'              => 'solid',
                        'border-top-color'              => '#333333',
                        ),
                    )
            );
            return $properties;
        }

        protected function renderControlContentNonEditable()
        {
            $src            = $this->resolveDividerImageUrl();
            $alt            = static::resolveLabel();
            $imageOptions   = array();
            $imageHeight    = ArrayUtil::getNestedValue($this->properties, "frontend['inlineStyles']['border-top-width']");
            if (isset($imageHeight))
            {
                // get rid of px as for border-top-width we have to save it with px(it appears in style property).
                $imageHeight    = substr($imageHeight, 0, -2);
                $imageOptions   = array('height' => $imageHeight);
            }
            $content        = ZurmoHtml::image($src, $alt, $imageOptions);
            return $content;
        }

        protected function renderSettingsTab(ZurmoActiveForm $form)
        {
            $propertiesForm = BuilderDividerElementPropertiesEditableElementsUtil::render($this->model, $form);
            return $propertiesForm;
        }

        protected function renderContentTab(ZurmoActiveForm $form)
        {
            return null;
        }

        /**
         * Resolve the divider image for the middle td
         * @return string
         */
        protected function resolveDividerImageUrl()
        {
            // its simple divider, we would use same dummy image for divider
            return $this->resolveDummyDividerImageUrl();
        }

        /**
         * resolve the url for td before and after divider image
         * @return string
         */
        protected function resolveDummyDividerImageUrl()
        {
            return PlaceholderImageUtil::resolveTransparentImageUrl(true);
        }

        protected function resolveWrapperTdNonEditableByContent($content)
        {
            $options            = $this->resolveNonEditableContentWrappingTdOptions();
            $content            = ZurmoHtml::tag('tr', array(), ZurmoHtml::tag('td', $options, $content));
            $content            = $this->resolveContentForPaddingTds($content);
            return $content;
        }

        /**
         * Add Padding Tds to content
         * @param $content
         * @return string
         */
        protected function resolveContentForPaddingTds($content)
        {
            $paddingTdContent   = $this->resolvePaddingTdContent();
            $content            = $paddingTdContent . $content . $paddingTdContent;
            return $content;
        }

        /**
         * Resolve padding td content
         * @return string
         */
        protected function resolvePaddingTdContent()
        {
            $src            = $this->resolveDummyDividerImageUrl();
            $alt            = static::resolveLabel();
            if (isset($this->properties['backend']) && isset($this->properties['backend']['divider-padding']))
            {
                $height = $this->properties['backend']['divider-padding'];
            }
            else
            {
                $height = 0;
            }
            $imageOptions   = array('height' => $height);
            $content        = ZurmoHtml::image($src, $alt, $imageOptions);
            $content        = ZurmoHtml::tag('tr', array(), ZurmoHtml::tag('td', array(), $content));
            return $content;
        }

        protected function resolveNonEditableWrapperHtmlOptions()
        {
            $options            = parent::resolveNonEditableWrapperHtmlOptions();
            $options['class']   .= ' ' . $this->resolveDividerCssClassNames();
            return $options;
        }

        /**
         * Resolve additional css class names to put on wrapper table.
         * @return string
         */
        protected function resolveDividerCssClassNames()
        {
            return 'simple-divider';
        }

        /**
         * Overrinding since in the dividerElement we wrap each td in a tr
         * @param $content
         * @return string
         */
        protected function resolveWrapperTrNonEditableByContent($content)
        {
            return $content;
        }
    }
?>