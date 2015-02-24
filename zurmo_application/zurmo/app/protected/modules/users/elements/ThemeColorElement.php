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
     * Element used by user configuration to select a theme color
     */
    class ThemeColorElement extends Element
    {
        protected $shouldDisableLocked = true;

        protected $showLocked = true;

        /**
         * Renders the setting as a radio list.
         * @return A string containing the element's content.
         */
        protected function renderControlEditable()
        {
            if (!$this->shouldRenderControlEditable())
            {
                return null;
            }
            $gameLevel = GameLevel::resolveByTypeAndPerson(GameLevel::TYPE_GENERAL, Yii::app()->user->userModel);
            $content = null;
            $content .= $this->form->radioButtonList(
                $this->model,
                $this->getAttributeForRadioButtonList(),
                $this->resolveThemeColorNamesAndLabelsForLocking($gameLevel),
                $this->getEditableHtmlOptions(),
                array(),
                $this->resolveDataHtmlOptions($gameLevel)
            );
            $this->registerScript();
            return $content;
        }

        protected function getAttributeForRadioButtonList()
        {
            return $this->attribute;
        }

        protected function shouldRenderControlEditable()
        {
            if (Yii::app()->themeManager->forceAllUsersTheme)
            {
                return false;
            }
            return true;
        }

        protected function renderControlNonEditable()
        {
            throw new NotImplementedException();
        }

        /**
         * Clear out html options for 'empty' since it is not applicable for a radio dropdown.
         * @see DropDownElement::getEditableHtmlOptions()
         */
        protected function getEditableHtmlOptions()
        {
            $htmlOptions              = array();
            $htmlOptions['separator'] = '';
            $htmlOptions['template']  = '<div class="radio-input color-swatch {value}">{input}{label}</div>';
            return $htmlOptions;
        }

        public function registerScript()
        {
            $removeScript = null;
            foreach (Yii::app()->themeManager->getThemeColorNamesAndLabels() as $value => $notUsed)
            {
                $removeScript .= '$(document.body).removeClass("' . $value . '");' . "\n";
            }
            $themeName         = Yii::app()->theme->name;
            $themeBaseUrl      = Yii::app()->themeManager->baseUrl . '/default/css';

            $primaryFileName   = 'zurmo-custom.css';
            $secondaryFileName = 'imports-custom.css';
            if (Yii::app()->themeManager->activeThemeColor != ThemeManager::CUSTOM_NAME)
            {
                Yii::app()->themeManager->registerThemeColorCss();
            }
            $primaryCustomCssUrl   = Yii::app()->assetManager->getPublishedUrl(Yii::app()->lessCompiler->compiledCustomCssPath) . DIRECTORY_SEPARATOR . $primaryFileName;
            $secondaryCustomCssUrl = Yii::app()->assetManager->getPublishedUrl(Yii::app()->lessCompiler->compiledCustomCssPath) . DIRECTORY_SEPARATOR . $secondaryFileName;

            // Begin Not Coding Standard
            $script = "$('input[name=\"" . $this->getEditableInputName($this->getAttributeForRadioButtonList()) . "\"]').live('change', function(){
                          $removeScript
                          $(document.body).addClass(this.value);
                          var themeBaseUrl          = '$themeBaseUrl';
                          var primaryCustomCssUrl   = '$primaryCustomCssUrl';
                          var secondaryCustomCssUrl = '$secondaryCustomCssUrl';
                          //use zurmo-blue since it is likely that all colors would change at same time. best we can do here for now
                          var baseHashQueryString       = '" . ZurmoAssetManager::getCssAndJavascriptHashQueryString("themes/$themeName/" . '/css/zurmo-blue.css') . "';
                          if(this.value === 'custom')
                          {
                            $('head').append('<link rel=\"stylesheet\" href=\"'+primaryCustomCssUrl+'\" type=\"text/css\" />');
                            $('head').append('<link rel=\"stylesheet\" href=\"'+secondaryCustomCssUrl+'\" type=\"text/css\" />');
                          }
                          else
                          {
                            $('head').append('<link rel=\"stylesheet\" href=\"'+themeBaseUrl+'/zurmo-'+this.value+'.css' + baseHashQueryString + '\" type=\"text/css\" />');
                          }
                          });
                      ";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('changeThemeColor', $script);
        }

        protected function resolveThemeColorNamesAndLabelsForLocking(GameLevel $gameLevel)
        {
            $namesAndUnlockedAtLevels = Yii::app()->themeManager->getThemeColorNamesAndUnlockedAtLevel();
            $data = array();
            foreach (Yii::app()->themeManager->getThemeColorNamesAndLabels() as $name => $label)
            {
                $colorArray = Yii::app()->themeManager->themeColorNamesAndColors[$name];
                $spans  = '<span class="theme-color-1" style="background-color:' . $colorArray[1] . '"></span>';
                $spans .= '<span class="theme-color-2" style="background-color:' . $colorArray[2] . '"></span>';
                $spans .= '<span class="theme-color-3" style="background-color:' . $colorArray[4] . '"></span>';
                $label  = $spans . $label;
                $unlockedAtLevel = $namesAndUnlockedAtLevels[$name];
                if ($unlockedAtLevel > (int)$gameLevel->value && $this->shouldDisableLocked)
                {
                    $title   = Zurmo::t('GamificationModule', 'Unlocked at level {level}', array('{level}' => $unlockedAtLevel));
                    $content = '<span id="theme-color-tooltip-' . $name. '" title="' . $title . '"><i class="icon-lock"></i></span>' . $label; // Not Coding Standard
                    $qtip    = new ZurmoTip();
                    $qtip->addQTip("#theme-color-tooltip-" . $name);
                }
                else
                {
                    $content = $label;
                }
                if (($unlockedAtLevel <= 1) || $this->showLocked)
                {
                    $data[$name] = $content;
                }
            }
            return $data;
        }

        protected function resolveDataHtmlOptions(GameLevel $gameLevel)
        {
            $dataHtmlOptions = array();
            foreach (Yii::app()->themeManager->getThemeColorNamesAndUnlockedAtLevel() as $name => $unlockedAtLevel)
            {
                $dataHtmlOptions[$name] = array();
                if ($unlockedAtLevel > (int)$gameLevel->value && $this->shouldDisableLocked)
                {
                    $dataHtmlOptions[$name]['class']    = 'locked';
                    $dataHtmlOptions[$name]['disabled'] = 'disabled';
                }
            }
            return $dataHtmlOptions;
        }
    }
?>
