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

    class BuilderColumnElement extends BuilderContainerElement
    {
        /**
         * Param key used to store additional table css classes.
         */
        const TABLE_CSS_CLASSES_PARAM_KEY   = 'tableCssClasses';

        protected function doesNotSupportEditable()
        {
            return true;
        }

        protected function resolveAvailableNonEditableActionsArray()
        {
            return array();
        }

        protected function resolveWrapperNonEditable($elementContent, array $customDataAttributes,
                                                     $actionsOverlay)
        {
            $content    = parent::resolveWrapperNonEditable($elementContent, $customDataAttributes,
                                                            $actionsOverlay);
            $content    = ZurmoHtml::tag('td', $this->resolveColumnWrapperTdHtmlOptions(), $content);
            return $content;
        }

        protected function resolveWrapperTdNonEditableByContent($content, array $properties = array())
        {
            $this->resolveContentWhenColumnIsEmpty($content);
            $content        = parent::resolveWrapperTdNonEditableByContent($content, $properties);
            $content       .= ZurmoHtml::tag('td', $this->resolveNonEditableExpanderTdHtmlOptions(), '');
            return $content;
        }

        /**
         * When the color is empty we need to add an extra div for the drop area to be visible
         * @param $content
         */
        protected function resolveContentWhenColumnIsEmpty(& $content)
        {
            if ($this->renderForCanvas && empty($content))
            {
                $content = '<div class="element-wrapper empty-element-wrapper"></div>';
            }
        }

        protected function resolveNonEditableContentWrappingTdHtmlOptions()
        {
            return array('class' => BaseBuilderElement::BUILDER_ELEMENT_SORTABLE_ELEMENTS_CLASS);
        }

        protected function resolveNonEditableWrapperHtmlOptions()
        {
            $parentOptions          = parent::resolveNonEditableWrapperHtmlOptions();
            $columnLength           = ArrayUtil::getArrayValue($this->params, static::TABLE_CSS_CLASSES_PARAM_KEY);
            if (!isset($columnLength))
            {
                $columnLength           = BuilderRowElement::MAX_COLUMN_WIDTH;
                $columnLength           = NumberToWordsUtil::convert($columnLength);
            }
            $parentOptions['class'] .= " ${columnLength} columns";
            return $parentOptions;
        }

        /**
         * Resolve and return html options of expander td
         * @return array
         */
        protected function resolveNonEditableExpanderTdHtmlOptions()
        {
            return array('class' => 'expander');
        }

        /**
         * Resolve and return html options for the td wrapping whole non-editable output
         * @return array
         */
        protected function resolveColumnWrapperTdHtmlOptions()
        {
            $htmlOptions    = array('class' => 'wrapper');
            if (!empty($this->params[static::LAST_PARAM_KEY]))
            {
                $htmlOptions['class']    .= ' last';
            }
            return $htmlOptions;
        }
    }
?>