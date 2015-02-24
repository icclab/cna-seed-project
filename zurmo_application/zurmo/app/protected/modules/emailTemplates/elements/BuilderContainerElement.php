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

    abstract class BuilderContainerElement extends BaseBuilderTableWrappedElement
    {
        /**
         * Key for storing whether this element is last in current container or not.
         */
        const LAST_PARAM_KEY    = 'last';

        public static function isContainerType()
        {
            return true;
        }

        protected function renderContentTab(ZurmoActiveForm $form)
        {
            // we don't need content tab for container elements, there is nothing to show.
        }

        protected function renderHiddenFields(ZurmoActiveForm $form)
        {
            $content    = $this->renderHiddenField('content', CJSON::encode($this->content));
            $content    .= parent::renderHiddenFields($form);
            return $content;
        }

        protected function renderControlContentNonEditable()
        {
            $content        = $this->resolveNestedElementsNonEditable();
            return $content;
        }

        /**
         * Resolve and return nested elements non-editable
         * @return string
         */
        protected function resolveNestedElementsNonEditable()
        {
            $content            = null;
            $lastKey            = $this->findLastKeyInContentArray();
            $elementsParams     = $this->resolveNestedElementsParamsArray();
            foreach ($this->content as $id => $elementData)
            {
                $elementParams  = $this->resolveNestedElementParamsById($id, $lastKey, $elementsParams);
                $content        .= $this->resolveElementByIdAndDataNonEditable($id, $elementData, $elementParams);
            }
            return $content;
        }

        /**
         * Find the last key if content array
         * @return mixed
         */
        protected function findLastKeyInContentArray()
        {
            return ArrayUtil::findLastKey($this->content);
        }

        /**
         * Resolve amd return an element non-editable when provided with id, its data and additional params.
         * @param $id
         * @param array $elementData
         * @param array $elementParams
         * @return string
         */
        protected function resolveElementByIdAndDataNonEditable($id, array $elementData, array $elementParams)
        {
            $class          = null;
            $properties     = null;
            $content        = null;
            extract($elementData);
            $elementContent = BuilderElementRenderUtil::renderNonEditable($class, $this->renderForCanvas, false, $id,
                                                                            $properties, $content, $elementParams);
            return $elementContent;
        }

        /**
         * Resolve and return any special params we would want to send to a nested element.
         * @param $id
         * @param $lastKey
         * @return array
         */
        protected function resolveNestedElementParamsById($id, $lastKey, array $paramMapping)
        {
            $elementParams  = array();
            $mappedParams   = ArrayUtil::getArrayValue($paramMapping, $id);
            if (isset($mappedParams))
            {
                $elementParams  = $mappedParams;
            }
            if ($lastKey == $id)
            {
                $elementParams[static::LAST_PARAM_KEY] = true;
            }
            return $elementParams;
        }

        /**
         * Resolve and return an array containing element params in key-value pairs with keys are
         * indices inside the content array
         * @return array
         */
        protected function resolveNestedElementsParamsArray()
        {
            return array();
        }
    }
?>