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
     * Override class for CButtonColumn in order to allow public access to renderDataCellContent
     * @see CGridView class
     */
    class ButtonColumn extends CButtonColumn
    {
        public function renderDataCellContentFromOutsideClass($row, $data)
        {
            $this->renderDataCellContent($row, $data);
        }

        /**
         * Render the link or ajax link
         * @param string $id the ID of the button
         * @param array $button the button configuration which may contain 'label', 'url', 'imageUrl' and 'options' elements.
         * See {@link buttons} for more details.
         * @param integer $row the row number (zero-based)
         * @param mixed $data the data object associated with the row
         */
        protected function renderButton($id, $button, $row, $data)
        {
            if (isset($button['visible']) && !$this->evaluateExpression($button['visible'],
                    array('row' => $row, 'data' => $data)))
            {
                return;
            }
            $label = isset($button['label']) ? $button['label'] : $id;
            if (isset($button['url']))
            {
                $url = $this->evaluateExpression($button['url'], array('data'=>$data, 'row'=>$row)); // Not Coding Standard
            }
            else
            {
                $url = '#';
            }
            $options = isset($button['options']) ? $button['options'] : array();
            if (!isset($options['title']))
            {
                $options['title'] = $label;
            }
            if (isset($button['ajaxOptions']))
            {
                unset($options['ajaxOptions']);
                echo ZurmoHtml::ajaxLink($label, $url, $button['ajaxOptions'], $options);
            }
            else
            {
                if (isset($button['imageUrl']) && is_string($button['imageUrl']))
                {
                    echo ZurmoHtml::link(CHtml::image($button['imageUrl'], $label), $url, $options);
                }
                else
                {
                    echo ZurmoHtml::link($label, $url, $options);
                }
            }
        }

        protected function registerClientScript()
        {
            $js=array();
            foreach ($this->buttons as $id=>$button)
            {
                if (isset($button['click']))
                {
                    $function=CJavaScript::encode($button['click']);
                    // Begin Not Coding Standard
                    $class=preg_replace('/\s+/','.',$button['options']['class']);
                    $js[]="jQuery(document).off('click','#{$this->grid->id} a.{$class}');";
                    $js[]="jQuery(document).on('click','#{$this->grid->id} a.{$class}',$function);";
                    // End Not Coding Standard
                }
            }

            if ($js !== array())
            {
                Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$this->id, implode("\n", $js)); // Not Coding Standard
            }
        }
    }
?>
