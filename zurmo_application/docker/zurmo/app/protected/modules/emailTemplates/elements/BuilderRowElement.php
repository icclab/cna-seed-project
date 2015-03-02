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

    class BuilderRowElement extends BuilderContainerElement
    {
        const MAX_COLUMN_WIDTH  = 12;

        protected static function resolveLabel()
        {
            return Zurmo::t('Core', 'Row');
        }

        protected function resolveDefaultProperties()
        {
            return array(
                'backend' => array(
                    'configuration' => 1
                )
            );
        }

        public function __construct($renderForCanvas = false, $id = null, $properties = null, $content = null, $params = null)
        {
            parent::__construct($renderForCanvas, $id, $properties, $content, $params);
            $this->adjustContentColumnDataForConfiguration();
        }

        protected function adjustContentColumnDataForConfiguration()
        {
            $columnCountConfiguration   = $this->resolveColumnCountConfiguration();

            if (isset($columnCountConfiguration))
            {
                $contentColumnCount         = count($this->content);
                $difference                 = $columnCountConfiguration - $contentColumnCount;
                if ($difference < 0)
                {
                    $this->reduceColumns($difference);
                }
                elseif ($difference > 0)
                {
                    $this->induceColumn($difference);
                }
            }
        }

        protected function reduceColumns($count)
        {
            $extraColumns           = array_splice($this->content, $count);
            $lastKey                = ArrayUtil::findLastKey($this->content);
            $lastKeyContent         = ArrayUtil::getNestedValue($this->content, "${lastKey}['content']");
            if (!isset($lastKeyContent))
            {
                $lastKeyContent = array();
            }
            foreach ($extraColumns as $extraColumn)
            {
                $extraColumnContent     = ArrayUtil::getArrayValue($extraColumn, 'content');
                if (isset($extraColumnContent))
                {
                    $lastKeyContent   = CMap::mergeArray($lastKeyContent, $extraColumnContent);
                }
            }
            $this->content[$lastKey]['content'] = $lastKeyContent;
        }

        protected function induceColumn($count)
        {
            for ($i = 0; $i < $count; $i++)
            {
                $blankColumnElement     = BuilderElementRenderUtil::resolveElement('BuilderColumnElement',
                                                                                    $this->renderForCanvas);
                $blankColumnElementData = BuilderElementRenderUtil::resolveSerializedDataByElement($blankColumnElement);
                $this->content          = CMap::mergeArray($this->content, $blankColumnElementData);
            }
        }

        protected function resolveColumnCountConfiguration()
        {
            $configuration  = ArrayUtil::getNestedValue($this->properties, "backend['configuration']");
            if (isset($configuration))
            {
                if (strpos($configuration, ':') === false)
                {
                    return intval($configuration);
                }
                return 2;
            }
        }

        protected function resolveAvailableNonEditableActionsArray()
        {
            return array(static::OVERLAY_ACTION_MOVE, static::OVERLAY_ACTION_EDIT, static::OVERLAY_ACTION_DELETE);
        }

        protected function renderSettingsTab(ZurmoActiveForm $form)
        {
            $propertiesForm      = BuilderRowElementPropertiesEditableElementsUtil::render($this->model, $form);
            $propertiesForm     .= BuilderElementBackgroundPropertiesEditableElementsUtil::render($this->model, $form);
            $propertiesForm     .= BuilderElementBorderPropertiesEditableElementsUtil::render($this->model, $form);
            return $propertiesForm;
        }

        protected function resolveNestedElementsParamsArray()
        {
            return $this->resolveColumnCssClassesByRowConfiguration();
        }

        protected function resolveNonEditableWrapperHtmlOptions()
        {
            $parentOptions          = parent::resolveNonEditableWrapperHtmlOptions();
            $parentOptions['class'] .= ' ' . $this->resolveTableCssClassNames(false);
            return $parentOptions;
        }

        protected function resolveTableCssClassNames($columnWrappingTable = false)
        {
            $cssClasses = null;
            $isHeader   = (bool)ArrayUtil::getNestedValue($this->properties, "backend['header']");
            // $columnWrappingTable = true, $header = true      : container
            // $columnWrappingTable = false, $header = false    : container
            if (($columnWrappingTable && $isHeader) ||
                (!$columnWrappingTable && !$isHeader))
            {
                $cssClasses = 'container';
            }
            // $columnWrappingTable = true, $header = false     : row
            // $columnWrappingTable = false, $header = true     : row header
            elseif (($columnWrappingTable && !$isHeader) ||
                    (!$columnWrappingTable && $isHeader))
            {
                $cssClasses = 'row';
                if ($isHeader)
                {
                    $cssClasses .= ' header';
                }
            }
            return $cssClasses;
        }

        protected function renderControlContentNonEditable()
        {
            // wrap elements in the extra table.
            $elementsContent    = parent::renderControlContentNonEditable();
            $tableHtmlOptions   = $this->resolveColumnWrapperTableHtmlOptions();
            // td comes from columns.
            $content            = ZurmoHtml::tag('tr', array(), $elementsContent);
            $content            = ZurmoHtml::tag('tbody', array(), $content);
            $content            = ZurmoHtml::tag('table', $tableHtmlOptions , $content);
            return $content;
        }

        /**
         * Resolve and return html options for the inner table, one that wraps columns
         * @return array
         */
        protected function resolveColumnWrapperTableHtmlOptions()
        {
            $htmlOptions            = array();
            $htmlOptions['class']   = $this->resolveTableCssClassNames(true);
            return $htmlOptions;
        }

        protected function resolveColumnCssClassesByRowConfiguration()
        {
            $configuration  = ArrayUtil::getNestedValue($this->properties, "backend['configuration']");
            if (!isset($configuration))
            {
                return array();
            }

            $columnCssClasses           = null;
            $columnKeysAndCssClasses    = null;
            $columnKeys                 = array_keys($this->content);
            if (strpos($configuration, ':') == false)
            {
                $columnCount                = intval($configuration);
                $columnWidth                = NumberToWordsUtil::convert(static::MAX_COLUMN_WIDTH / $columnCount);
                $columnCssClasses           = array(BuilderColumnElement::TABLE_CSS_CLASSES_PARAM_KEY => $columnWidth);
                $columnCssClasses           = array_fill(0, count($columnKeys), $columnCssClasses);
            }
            else
            {
                $ratios                     = explode(':', $configuration);
                $total                      = array_sum($ratios);
                $unitRatioWidth             = static::MAX_COLUMN_WIDTH / $total;
                foreach ($ratios as $ratio)
                {
                    $width                  = NumberToWordsUtil::convert($ratio * $unitRatioWidth);
                    $columnCssClasses[]     = array(BuilderColumnElement::TABLE_CSS_CLASSES_PARAM_KEY => $width);
                }
            }
            $columnKeysAndCssClasses    = array_combine($columnKeys, $columnCssClasses);
            return $columnKeysAndCssClasses;
        }
    }
?>