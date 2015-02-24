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

    class BuilderElementPropertiesEditableElementUtil
    {
        /**
         * Render editable element for a property
         * @param $elementClassName
         * @param CModel $model
         * @param $property
         * @param ZurmoActiveForm $form
         * @param array $params
         * @param bool $wrapInTr
         * @param array $trOptions
         * @return mixed
         */
        public static function render($elementClassName, CModel $model, $property, ZurmoActiveForm $form,
                                        array $params = array(), $wrapInTr = true, array $trOptions = array())
        {
            $attribute  = static::resolveAttributeName($property);
            $element    = new $elementClassName($model, $attribute, $form, $params);
            static::resolveEditableTemplateByProperty($element, $property);
            $content    = $element->render();
            if ($wrapInTr)
            {
                static::wrapContentInTr($content, $trOptions);
            }
            return $content;
        }

        protected static function resolveEditableTemplateByProperty($element, $property)
        {
            $property = str_replace(array('[', ']'), '', $property);
            $icon     = static::getIconByProperty($property);
            if (isset($icon))
            {
                $element->editableTemplate = '<th>{label}</th><td colspan="{colspan}"><div><div class="has-unit-input">{content}' .
                                             $icon . '</div>{error}</div></td>';
            }
            else
            {
                $element->editableTemplate = '<th>{label}</th><td colspan="{colspan}"><div>{content}{error}</div></td>';
            }
        }

        protected static function getIconByProperty($property)
        {
            $propertiesSuffixMappedArray = BaseBuilderElement::getPropertiesSuffixMappedArray();
            if (isset($propertiesSuffixMappedArray[$property]))
            {
                $icon = $propertiesSuffixMappedArray[$property];
                switch ($icon)
                {
                    case '%':
                        $iconClass = 'icon-percentage';
                        break;
                    case 'px':
                        $iconClass = 'icon-pixel';
                        break;
                }
                return ZurmoHtml::icon($iconClass);
            }
        }

        /**
         * Resolve id for a given property
         * @param CModel $model
         * @param $property
         * @param bool $doNotWrapInSquareBrackets
         * @return string
         */
        public static function resolveAttributeId(CModel $model, $property, $doNotWrapInSquareBrackets = false)
        {
            static::resolvePropertyWithSquareBrackets($property, $doNotWrapInSquareBrackets);
            $attribute  = static::resolveAttributeName($property);
            $attribute  = ZurmoHtml::activeId($model, $attribute);
            return $attribute;
        }

        /**
         * Resolve Attribute Name
         * @param $property
         * @return string
         */
        public static function resolveAttributeName($property)
        {
            $property   = "properties${property}";
            return $property;
        }

        /**
         * Resolve qualified attribute name
        /**
         * @param CModel $model
         * @param $property
         * @param bool $doNotWrapInSquareBrackets
         * @return string
         */
        public static function resolveQualifiedAttributeName(CModel $model, $property, $doNotWrapInSquareBrackets = false)
        {
            static::resolvePropertyWithSquareBrackets($property, $doNotWrapInSquareBrackets);
            $property   = static::resolveAttributeName($property);
            $name       = ZurmoHtml::resolveName($model, $property);
            return $name;
        }

        /**
         * @param $content
         * @param $trOptions
         */
        protected static function wrapContentInTr(& $content, $trOptions)
        {
            $content    = ZurmoHtml::tag('tr', $trOptions, $content);
        }

        /**
         * Wrap property in square brackets if required.
         * @param $property
         * @param bool $doNotWrapInSquareBrackets
         */
        protected static function resolvePropertyWithSquareBrackets(& $property, $doNotWrapInSquareBrackets = false)
        {
            if ($doNotWrapInSquareBrackets === false)
            {
                $property   = "[${property}]";
            }
        }
    }
?>