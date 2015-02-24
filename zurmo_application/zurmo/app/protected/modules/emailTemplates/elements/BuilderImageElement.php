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

    class BuilderImageElement extends BaseBuilderElement
    {
        public static function isUIAccessible()
        {
            return true;
        }

        protected static function resolveLabel()
        {
            return Zurmo::t('Core', 'Image');
        }

        protected function resolveDefaultContent()
        {
            return array('image' => '');
        }

        protected function renderSettingsTab(ZurmoActiveForm $form)
        {
            $propertiesForm     = BuilderElementImagePropertiesEditableElementsUtil::render($this->model, $form);
            return $propertiesForm;
        }

        protected function resolveContentElementClassName()
        {
            return 'ImageElement';
        }

        protected function resolveContentElementAttributeName()
        {
            // no, we can't use array here. Element classes use $this->model{$this->attribute} a lot.
            // it would give an error saying we are trying to convert an array to string.
            return 'content[image]';
        }

        protected function resolveNonEditableWrapperHtmlOptions()
        {
            $htmlOptions            = parent::resolveNonEditableWrapperHtmlOptions();
            $frontendProperties     = ArrayUtil::getArrayValue($this->properties, 'frontend');
            $htmlOptions['align']   = ArrayUtil::getArrayValue($frontendProperties, 'float');
            return $htmlOptions;
        }

        protected function resolveContentElementParams()
        {
            $params                     = parent::resolveContentElementParams();
            $params['applyLinkId']      = $this->resolveApplyLinkId();
            $params['labelHtmlOptions'] = array('label' => 'Image');
            $frontendOptions            = $this->resolveContentElementParamsFromFrontendOptions();
            return array_merge($params, $frontendOptions);
        }

        protected function resolveContentElementParamsFromFrontendOptions()
        {
            $properties = array();
            $frontendProperties = ArrayUtil::getArrayValue($this->properties, 'frontend');
            if ($frontendProperties)
            {
                $properties['alt']                   = ArrayUtil::getArrayValue($frontendProperties, 'alt');
                $properties['htmlOptions']['width']  = ArrayUtil::getArrayValue($frontendProperties, 'width');
                $properties['htmlOptions']['height'] = ArrayUtil::getArrayValue($frontendProperties, 'height');
            }
            return $properties;
        }

        protected function renderControlContentNonEditable()
        {
            $content = parent::renderControlContentNonEditable();
            $this->wrapContentWithLink($content);
            return $content;
        }

        protected function wrapContentWithLink(& $content)
        {
            $href    = null;
            $options = array();
            $frontendProperties = ArrayUtil::getArrayValue($this->properties, 'frontend');
            if ($frontendProperties)
            {
                $href = ArrayUtil::getArrayValue($frontendProperties, 'href');
                if ($href)
                {
                    $options['href'] = $href;
                }
                $target = ArrayUtil::getArrayValue($frontendProperties, 'target');
                if ($target)
                {
                    $options['target'] = $target;
                }
                $title = ArrayUtil::getArrayValue($frontendProperties, 'title');
                if ($title)
                {
                    $options['title'] = $title;
                }
            }
            if ($href != null)
            {
                $content = ZurmoHtml::link($content, $href, $options);
            }
        }

        protected function resolveFrontendPropertiesNonEditable()
        {
            $properties = array();
            $this->resolveInlineStylePropertiesNonEditable($properties);
            return $properties;
        }

        protected function resolveFormHtmlOptions()
        {
            $formHtmlOptions = parent::resolveFormHtmlOptions();
            $formHtmlOptions['class'] .= ' image-element-edit-form';
            return $formHtmlOptions;
        }
    }
?>