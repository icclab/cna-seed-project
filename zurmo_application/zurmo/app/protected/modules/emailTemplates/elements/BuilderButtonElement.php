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

    class BuilderButtonElement extends BaseBuilderTableWrappedElement
    {
        /**
         * @var array
         */
        protected $inlineStylesToKeepOnATag = array('color', 'font-size', 'font-family', 'font-weight');

        public static function isUIAccessible()
        {
            return true;
        }

        protected static function resolveLabel()
        {
            return Zurmo::t('EmailTemplatesModule', 'Button');
        }

        protected function resolveDefaultProperties()
        {
            $properties              = array(
                'backend'       => array(
                    'sizeClass'         => 'button',
                    'text'              => Zurmo::t('Core', 'Click Here'),
                    'width'             => '100%',
                ),
                'frontend'      => array(
                    'href'              => Yii::app()->createAbsoluteUrl('/'),
                    'target'            => '_blank',
                    'inlineStyles'  => array(
                        'color'              => '#ffffff',
                    ),
                )
            );
            return $properties;
        }

        protected function renderControlContentNonEditable()
        {
            $target                 = null;
            $href                   = null;
            extract($this->properties['frontend']);
            $label                  = $this->properties['backend']['text'];
            $frontendOptions        = $this->resolveFrontendPropertiesNonEditable();
            $htmlOptions            = $this->resolveDefaultHtmlOptionsForLink();
            $options                = CMap::mergeArray($htmlOptions, $frontendOptions);
            $content                = ZurmoHtml::link($label, $href, $options);
            return $content;
        }

        protected function resolveFrontendPropertiesNonEditable()
        {
            $properties = array();
            $frontendProperties = ArrayUtil::getArrayValue($this->properties, 'frontend');
            if ($frontendProperties)
            {
                $properties = $frontendProperties;
            }
            $this->resolveInlineStylePropertiesForFrontendNonEditable($properties);
            return $properties;
        }

        protected function resolveInlineStylePropertiesForFrontendNonEditable(array & $mergedProperties)
        {
            $mergedProperties['style'] = '';
            $inlineStyles   = $this->resolveInlineStylesForNonEditable($mergedProperties);
            if ($inlineStyles)
            {
                $usableInlineStyles = array();
                foreach ($this->inlineStylesToKeepOnATag as $style)
                {
                    if (isset($inlineStyles[$style]))
                    {
                        $usableInlineStyles[$style] = $inlineStyles[$style];
                        unset($mergedProperties['inlineStyles'][$style]);
                    }
                }
                unset($mergedProperties['inlineStyles']);
                if ($usableInlineStyles)
                {
                    $mergedProperties['style']  = $this->stringifyProperties($usableInlineStyles, null, null, ':', ';');
                }
            }
        }

        protected function resolveFrontendPropertiesForWrappingTdNonEditable()
        {
            $properties = array();
            $frontendProperties = ArrayUtil::getArrayValue($this->properties, 'frontend');
            if ($frontendProperties)
            {
                $properties = $frontendProperties;
            }
            foreach ($this->inlineStylesToKeepOnATag as $style)
            {
                if (isset($properties['inlineStyles']) && isset($properties['inlineStyles'][$style]) && $style != 'color')
                {
                    unset($properties['inlineStyles'][$style]);
                }
            }
            if (isset($properties['target']))
            {
                unset($properties['target']);
            }
            if (isset($properties['href']))
            {
                unset($properties['href']);
            }
            $this->resolveInlineStylePropertiesNonEditable($properties);
            if (isset($properties['inlineStyles']))
            {
                unset($properties['inlineStyles']);
            }
            return $properties;
        }

        /**
         * @param array $mergedProperties
         * @return array|null
         */
        protected function resolveInlineStylesForNonEditable(array & $mergedProperties)
        {
            return ArrayUtil::getArrayValue($mergedProperties, 'inlineStyles');
        }

        protected function renderSettingsTab(ZurmoActiveForm $form)
        {
            $propertiesForm      = BuilderButtonElementPropertiesEditableElementsUtil::render($this->model, $form);
            $propertiesForm     .= BuilderElementBackgroundPropertiesEditableElementsUtil::render($this->model, $form);
            $propertiesForm     .= BuilderElementTextPropertiesEditableElementsUtil::render($this->model, $form, array('line-height', 'text-align'));
            $propertiesForm     .= BuilderElementBorderPropertiesEditableElementsUtil::render($this->model, $form);
            return $propertiesForm;
        }

        protected function renderContentTab(ZurmoActiveForm $form)
        {
            return null;
        }

        protected function resolveDefaultHtmlOptionsForLink()
        {
            return array();
        }

        /**
         * Supports adding width as style instead of just as width element on table
         * @param $content
         * @param array $customDataAttributes
         * @return string
         */
        protected function resolveWrapperTableNonEditableByContentAndHtmlOptions($content, array $customDataAttributes)
        {
            $backendHtmlOptions = $this->resolveBackendPropertiesForWrapperTableNonEditable();
            $defaultHtmlOptions = $this->resolveNonEditableWrapperOptions($customDataAttributes);
            $options            = CMap::mergeArray($backendHtmlOptions, $defaultHtmlOptions);
            $htmlOptions        = CMap::mergeArray($options, $customDataAttributes);
            $content            = ZurmoHtml::tag('table', $htmlOptions, $content);
            return $content;
        }

        /**
         * Resolve frontend properties for non-editable
         * @return array
         */
        protected function resolveBackendPropertiesForWrapperTableNonEditable()
        {
            $properties = array();
            $backendProperties = ArrayUtil::getArrayValue($this->properties, 'backend');
            if ($backendProperties)
            {
                $properties = array();
                $width      = ArrayUtil::getNestedValue($this->properties, "backend['width']");
                if ($width)
                {
                    $properties['inlineStyles']['width']   = $width;
                }
            }
            $this->resolveInlineStylePropertiesNonEditable($properties);
            return $properties;
        }

        protected function resolveNonEditableWrapperHtmlOptions()
        {
            $htmlOptions            = parent::resolveNonEditableWrapperHtmlOptions();
            $htmlOptions['class']  .= ' ' . $this->properties['backend']['sizeClass'];
            $htmlOptions['align']   = ArrayUtil::getArrayValue($this->properties['backend'], 'align');
            $width                  = ArrayUtil::getNestedValue($this->properties, "backend['width']");
            if ($width)
            {
                $htmlOptions['width']   = $width;
            }
            return $htmlOptions;
        }

        /**
         * Resolve wrapper's column options
         * @return array
         */
        protected function resolveNonEditableContentWrappingTableOptions()
        {
            $frontendOptions    = $this->resolveFrontendPropertiesNonEditable();
            if (isset($frontendOptions['href']))
            {
                unset($frontendOptions['href']);
            }
            if (isset($frontendOptions['target']))
            {
                unset($frontendOptions['target']);
            }
            $htmlOptions        = $this->resolveNonEditableContentWrappingTdHtmlOptions();
            $options            = CMap::mergeArray($htmlOptions, $frontendOptions);
            return $options;
        }

        protected function resolveWrapperTdNonEditableByContent($content)
        {
            $options            = $this->resolveNonEditableContentWrappingTdOptions();
            $options            = CMap::mergeArray($options, array('class' => 'button-td'));
            $content            = ZurmoHtml::tag('td', $options, $content);
            return $content;
        }
    }
?>