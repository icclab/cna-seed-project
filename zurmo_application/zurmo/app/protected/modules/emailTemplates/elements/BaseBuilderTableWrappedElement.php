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

    abstract class BaseBuilderTableWrappedElement extends BaseBuilderElement
    {
        protected $shouldWrapCenterTagAroundTdForNonEditable = false;

        public static function isUiAccessible()
        {
            return false;
        }

        protected function resolveWrapperNonEditableByContentAndProperties($content, array $customDataAttributes)
        {
            // these are container elements, we wrap them in tables instead of divs
            if ($this->shouldWrapCenterTagAroundTdForNonEditable)
            {
                $content    = $this->resolveWrapperCenterNonEditableByContent($content);
            }
            $content        = $this->resolveWrapperTdNonEditableByContent($content);
            $content        = $this->resolveWrapperTrNonEditableByContent($content);
            $content        = $this->resolveWrapperTBodyNonEditableByContent($content);
            $content        = $this->resolveWrapperTableNonEditableByContentAndHtmlOptions($content, $customDataAttributes);
            return $content;
        }

        /**
         * Create a 'center' wrapper
         * @param $content
         * @return string
         */
        protected function resolveWrapperCenterNonEditableByContent($content)
        {
            $content            = ZurmoHtml::tag('center', array(), $content);
            return $content;
        }

        /**
         * Resolve and return td(s) by using provided content for non-editable representation
         * @param $content
         * @param array $properties
         * @return string
         */
        protected function resolveWrapperTdNonEditableByContent($content)
        {
            $options            = $this->resolveNonEditableContentWrappingTdOptions();
            $content            = ZurmoHtml::tag('td', $options, $content);
            return $content;
        }

        /**
         * Resolve and return tr(s) by using provided content for non-editable representation
         * @param $content
         * @return string
         */
        protected function resolveWrapperTrNonEditableByContent($content)
        {
            $content        = ZurmoHtml::tag('tr', array(), $content);
            return $content;
        }

        /**
         * Resolve and return tbody by using provided content for non-editable representation
         * @param $content
         * @return string
         */
        protected function resolveWrapperTBodyNonEditableByContent($content)
        {
            $content        = ZurmoHtml::tag('tbody', array(), $content);
            return $content;
        }

        /**
         * Resolve and return table by using provided content and htmloptions for non-editable representation
         * @param $content
         * @param array $customDataAttributes
         * @return string
         */
        protected function resolveWrapperTableNonEditableByContentAndHtmlOptions($content, array $customDataAttributes)
        {
            $defaultHtmlOptions = $this->resolveNonEditableWrapperOptions($customDataAttributes);
            $htmlOptions        = CMap::mergeArray($defaultHtmlOptions, $customDataAttributes);
            $content            = ZurmoHtml::tag('table', $htmlOptions, $content);
            return $content;
        }

        protected function resolveNonEditableWrapperOptions(array $customDataAttributes)
        {
            // frontend options are rendered directly on the td in this case.
            $htmlOptions        = $this->resolveNonEditableWrapperHtmlOptions();
            $options            = CMap::mergeArray($htmlOptions, $customDataAttributes);
            return $options;
        }

        /**
         * Resolve wrapper's column html options
         * @return array
         */
        protected function resolveNonEditableContentWrappingTdHtmlOptions()
        {
            return array();
        }

        /**
         * Resolve wrapper's column options
         * @return array
         */
        protected function resolveNonEditableContentWrappingTdOptions()
        {
            $frontendOptions    = $this->resolveFrontendPropertiesForWrappingTdNonEditable();
            $htmlOptions        = $this->resolveNonEditableContentWrappingTdHtmlOptions();
            $options            = CMap::mergeArray($htmlOptions, $frontendOptions);
            return $options;
        }

        protected function resolveFrontendPropertiesForWrappingTdNonEditable()
        {
            return $this->resolveFrontendPropertiesNonEditable();
        }
    }
?>