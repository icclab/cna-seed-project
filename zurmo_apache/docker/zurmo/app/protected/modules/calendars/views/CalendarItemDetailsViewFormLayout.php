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
      * Form layout class for calendar item detail view.
      */
    class CalendarItemDetailsViewFormLayout extends DetailsViewFormLayout
    {
        /**
         * Override to render calendar item detail in a different layout.
         * @return A string containing the element's content.
         */
        public function render()
        {
            $content        = '';
            if ($this->alwaysShowErrorSummary || $this->shouldRenderTabbedPanels())
            {
                $content .= $this->errorSummaryContent;
            }
            $tabsContent    = '';
            foreach ($this->metadata['global']['panels'] as $panelNumber => $panel)
            {
                $content .= $this->renderDivTagByPanelNumber($panelNumber);
                $content .= $this->renderPanelHeaderByPanelNumberAndPanel($panelNumber, $panel);
                $content .= $this->resolveStartingDivTagAndColumnQuantityClass($panel);
                $content .= '<ul>';

                foreach ($panel['rows'] as $row)
                {
                    $cellsContent = null;
                    foreach ($row['cells'] as $cell)
                    {
                        if (is_array($cell['elements']))
                        {
                            foreach ($cell['elements'] as $renderedElement)
                            {
                                $cellsContent .= $renderedElement;
                            }
                        }
                    }
                    if (!empty($cellsContent))
                    {
                        $this->resolveRowWrapperTag($content, $cellsContent);
                    }
                }
                $content .= '</ul>';
                $content .= '</div>';
                $content .= '</div>';
            }
            $this->renderScripts();
            return $this->resolveFormLayoutContent($content);
        }

        /**
         * Resolve starting div tag and column quantity class.
         * @param array $panel
         * @return string
         */
        protected function resolveStartingDivTagAndColumnQuantityClass($panel)
        {
            assert('is_array($panel)');
            if (static::getMaximumColumnCountForSpecificPanels($panel) == 2)
            {
                return '<div class="form-fields double-column">';
            }
            return '<div class="form-fields">';
        }

        /**
         * If the cell content contains a <tr at the beginning, then assume we do not
         * need to wrap or end with a tr
         */
        protected function resolveRowWrapperTag(& $content, $cellsContent)
        {
            assert('is_string($content) || $content == null');
            assert('is_string($cellsContent)');
            $content .= $cellsContent;
        }
    }
?>