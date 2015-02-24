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
     * Element used by user configuration to select background texture
     */
    class BackgroundTextureElement extends Element
    {
        /**
         * Renders the setting as a radio list.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            $gameLevel = GameLevel::resolveByTypeAndPerson(GameLevel::TYPE_GENERAL, Yii::app()->user->userModel);
            $content = null;
            $content .= $this->form->radioButtonList(
                $this->model,
                $this->attribute,
                $this->makeData($gameLevel),
                $this->getEditableHtmlOptions(),
                array(),
                $this->resolveDataHtmlOptions($gameLevel)
            );
            $this->registerScript();
            return $content;
        }

        protected function renderControlNonEditable()
        {
            throw new NotImplementedException();
        }

        /**
         * Clear out html options for 'empty' since it is not applicable for a rado dropdown.
         * @see DropDownElement::getEditableHtmlOptions()
         */
        protected function getEditableHtmlOptions()
        {
            $htmlOptions             = array();
            $htmlOptions['separator'] = '';
            $htmlOptions['template']  = '<div class="radio-input texture-swatch {value}">{input}{label}</div>';
            return $htmlOptions;
        }

        protected function makeData(GameLevel $gameLevel)
        {
            $data = array('' => '<span class="background-texture-1"></span>' . Zurmo::t('Core', 'None'));
            return array_merge($data, $this->resolveBackgroundTextureNamesAndLabelsForLocking($gameLevel));
        }

        public function registerScript()
        {
            $removeScript = null;
            foreach (Yii::app()->themeManager->getBackgroundTextureNamesAndLabels() as $value => $notUsed)
            {
                $removeScript .= '$(document.documentElement).removeClass("' . $value . '");' . "\n";
            }
            // Begin Not Coding Standard
            $script = "$('input[name=\"" . $this->getEditableInputName() . "\"]').live('change', function(){
                          $removeScript
                          $(document.documentElement).addClass(this.value);
                          });
                      ";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('changeBackgroundTexture', $script);
        }

        protected function resolveBackgroundTextureNamesAndLabelsForLocking(GameLevel $gameLevel)
        {
            $namesAndUnlockedAtLevels = Yii::app()->themeManager->getBackgroundTextureNamesAndUnlockedAtLevel();
            $data = array();
            foreach (Yii::app()->themeManager->getBackgroundTextureNamesAndLabels() as $name => $label)
            {
                $label = '<span class="background-texture-1"></span>' . $label;
                $unlockedAtLevel = $namesAndUnlockedAtLevels[$name];
                if ($unlockedAtLevel > (int)$gameLevel->value)
                {
                    $title   = Zurmo::t('GamificationModule', 'Unlocked at level {level}', array('{level}' => $unlockedAtLevel));
                    $content = '<span id="background-texture-tooltip-' . $name. '" title="' . $title . '"><i class="icon-lock"></i></span>' . $label; // Not Coding Standard
                    $qtip    = new ZurmoTip();
                    $qtip->addQTip("#background-texture-tooltip-" . $name);
                }
                else
                {
                    $content = $label;
                }
                $data[$name] = $content;
            }
            return $data;
        }

        protected function resolveDataHtmlOptions(GameLevel $gameLevel)
        {
            $dataHtmlOptions = array();
            foreach (Yii::app()->themeManager->getBackgroundTextureNamesAndUnlockedAtLevel() as $name => $unlockedAtLevel)
            {
                $dataHtmlOptions[$name] = array();
                if ($unlockedAtLevel > (int)$gameLevel->value)
                {
                    $dataHtmlOptions[$name]['class']    = 'locked';
                    $dataHtmlOptions[$name]['disabled'] = 'disabled';
                }
            }
            return $dataHtmlOptions;
        }
    }
?>
