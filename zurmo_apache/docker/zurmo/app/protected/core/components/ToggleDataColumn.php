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
     * Each cell will be a button to toggle the values of the cell
     */
    class ToggleDataColumn extends DataColumn
    {
        /**
         * @var string The icon for toggle button "checked" state.
         */
        public $checkedIcon = 'icon-checked';

        /**
         * @var string The icon for toggle button "unchecked" state.
         */
        public $uncheckedIcon = 'icon-unchecked';

        /**
         * @var string Name of the action to call for toggle values
         */
        public $toggleAction = 'toggle';

        /**
         * @var string Name of the method to call to check if the data can be toggleable
         */
        public $toggleCheckerMethod = 'isToggleable';

        /**
         * @var string a javascript function that will be invoked after the toggle ajax call.
         * The function signature is function(data)
         */
        public $afterToggle;

        public function init()
        {
            if ($this->name === null) {
                throw new NotSupportedException('name attribute cannot be empty.');
            }
            $this->registerClientScript();
        }

        protected function renderDataCellContent($row, $data)
        {
            $checked   = ZurmoHtml::value($data, $this->name);
            $iconClass = $checked ? $this->checkedIcon : $this->uncheckedIcon;
            $icon      = ZurmoHtml::tag('span', array('class' => 'toggle-column'), ZurmoHtml::tag('i', array('class' => $iconClass), ''));
            if (isset($this->visible) && !$this->evaluateExpression($this->visible, array('row' => $row, 'data' => $data)))
            {
                echo $icon;
            }
            else
            {
                echo ZurmoHtml::link($icon, $this->getUrl($data), $this->getHtmlOptions());
            }
        }

        protected function getHtmlOptions()
        {
            return array('class' => $this->name . '-toggle');
        }

        protected function getUrl($data)
        {
            $options = array('id'             => $data->id,
                             'attribute'      => $this->name,
            );
            return Yii::app()->controller->createUrl($this->toggleAction, $options);
        }

        /**
         * Registers the client scripts for the button column.
         */
        protected function registerClientScript()
        {
            $htmlOptions = $this->getHtmlOptions();
            $js=array();
            $function = CJavaScript::encode($this->getClickScript());
            $class = preg_replace('/\s+/', '.', $htmlOptions['class']);
            $js[] = "jQuery(document).off('click','#{$this->grid->id} a.{$class}');"; // Not Coding Standard
            $js[] = "jQuery(document).on('click', '#{$this->grid->id} a.{$class}', $function);"; // Not Coding Standard
            Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$this->id, implode("\n",$js));
        }

        protected function getClickScript()
        {
            if (Yii::app()->request->enableCsrfValidation) {
                $csrfTokenName = Yii::app()->request->csrfTokenName;
                $csrfToken = Yii::app()->request->csrfToken;
                $csrf = "\n\t\tdata:{ '$csrfTokenName':'$csrfToken' },"; // Not Coding Standard
            } else {
                $csrf = '';
            }

            if ($this->afterToggle === null) {
                $this->afterToggle = 'function(){}';
            }
            // Begin Not Coding Standard
            return "js:
                function() {
                    var th=this;
                    var afterToggle={$this->afterToggle};
                    $.fn.yiiGridView.update('{$this->grid->id}', {
                        type:'POST',
                        url:$(this).attr('href'),{$csrf}
                        success:function(data) {
                            $.fn.yiiGridView.update('{$this->grid->id}');
                            afterToggle(true, data);
                        },
                        error:function(XHR){
                            afterToggle(false,XHR);
                        }
                    });
                    return false;
                }";
            // End Not Coding Standard
        }
    }
?>