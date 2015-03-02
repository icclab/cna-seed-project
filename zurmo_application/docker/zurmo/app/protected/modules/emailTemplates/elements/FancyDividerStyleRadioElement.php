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

    class FancyDividerStyleRadioElement extends Element
    {
        protected function resolveAvailableStyles()
        {
            $styles    = array(
                'fancy-divider-1.png'   => 'Fancy 1',
                'fancy-divider-2.png'   => 'Fancy 2',
                'fancy-divider-3.png'   => 'Fancy 3',
                'fancy-divider-4.png'   => 'Fancy 4',
                'fancy-divider-5.png'   => 'Fancy 5',
            );
            return $styles;
        }

        protected function resolveData()
        {
            $styles = $this->resolveAvailableStyles();
            $data   = array();
            foreach ($styles as $value => $label)
            {
                $fancyDividerSpanClass  = substr($value, 0, -4);
                $dataLabel              = ZurmoHtml::span($fancyDividerSpanClass);
                $dataLabel              .= ZurmoHtml::tag('span', array('class' => 'fancy-divider-label'), $label);
                $data[$value]           = $dataLabel;
            }
            return $data;
        }

        /**
         * Renders the setting as a radio list.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            $content = $this->form->radioButtonList(
                $this->model,
                $this->attribute,
                $this->resolveData(),
                $this->getEditableHtmlOptions()
            );
            return $content;
        }

        protected function renderControlNonEditable()
        {
            throw new NotImplementedException();
        }

        protected function resolveIdForLabel()
        {
            return ZurmoHtml::ID_PREFIX . $this->getEditableInputId();
        }

        protected function resolveTemplate()
        {
            $template   = '<div class="radio-input divider-swatch">{input}{label}</div>';
            return $template;
        }

        public function getEditableHtmlOptions()
        {
            $htmlOptions = array(
                'name'      => $this->getEditableInputName(),
                'id'        => $this->getEditableInputId(),
                'separator' => '',
                'template'  => $this->resolveTemplate(),
            );
            return $htmlOptions;
        }
    }
?>