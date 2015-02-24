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

    class ThemeColorConfigurationElement extends ThemeColorElement
    {
        protected $shouldDisableLocked = false;

        protected $showLocked = false;

        protected $attribute2 = 'themeColor';

        /**
         * Override to assure model and attribute for this element
         */
        public function __construct($model, $attribute, $form = null, array $params = array())
        {
            assert('$attribute == "forceAllUsersTheme"');
            assert('get_class($model) == "ZurmoUserInterfaceConfigurationForm"');
            $this->model     = $model;
            $this->attribute = $attribute;
            $this->form      = $form;
            $this->params    = $params;

        }

        protected function getAttributeForRadioButtonList()
        {
            return $this->attribute2;
        }

        protected function renderControlEditable()
        {
            $content  = $this->renderForAllCheckBox();
            $content .= ZurmoHtml::tag('div',
                                       array('id'    => 'theme-color-configuration',
                                             'style' => $this->getStyleForThemeColorDiv(),
                                       ),
                                       parent::renderControlEditable());
            return $content;
        }

        protected function shouldRenderControlEditable()
        {
            return true;
        }

        protected function renderForAllCheckBox()
        {
            $id          = $this->getEditableInputId();
            $checkBox    = $this->form->checkBox($this->model, $this->attribute, $this->getEditableHtmlOptions());
            $error       = $this->form->error   ($this->model, $this->attribute, array('inputID' => $id));
            $content     = $checkBox . $error;
            return $content;
        }

        public function registerScript()
        {
            $checkBoxId = $this->getEditableInputId();
            // Begin Not Coding Standard
            Yii::app()->clientScript->registerScript('forceAllUsersThemeCheckBox', "
                    $('#{$checkBoxId}').change(function(){
                        $('#theme-color-configuration').toggle();
                    });
                ");
            // End Not Coding Standard
            parent::registerScript();
        }

        protected function getStyleForThemeColorDiv()
        {
            $attribute     = $this->attribute;
            $isHidden      = !$this->model->$attribute;
            if ($isHidden)
            {
                $style = 'display: none;';
            }
            else
            {
                $style = null;
            }
            return $style;
        }
    }
?>