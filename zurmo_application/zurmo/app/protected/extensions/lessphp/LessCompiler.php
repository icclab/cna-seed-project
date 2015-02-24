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

    require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'lessc.inc.php');

    class LessCompiler extends CApplicationComponent
    {
        public $formatterName = 'compressed';

        public $themeColorDependentLessFilesToCompile;

        public $lessFilesToCompile;

        protected $compiledCssPath;

        protected $compiledImagesPath;

        protected $compiledCustomCssPath;

        protected $lessFilesPath;

        protected $lessCompiler;

        protected $themeColors;

        /**
         * Initialize component
         */
        public function init()
        {
            parent::init();
            $this->setCompiledCssPath();
            $this->setCompiledCustomCssPath();
            $this->setLessFilesPath();
            $this->setThemeColors();
        }

        /**
         * Set path where compiled css files will be saved
         */
        protected function setCompiledCssPath()
        {
            $themePath                = Yii::app()->themeManager->getBasePath() . DIRECTORY_SEPARATOR .
                                        Yii::app()->theme->name;
            $this->compiledCssPath    = $themePath . DIRECTORY_SEPARATOR . 'css';
            $this->compiledImagesPath = $themePath . DIRECTORY_SEPARATOR . 'images';
        }

        /**
         * Get path where css files will be saved
         * @return null || string
         */
        protected function getCompiledCssPath()
        {
            if (isset($this->compiledCssPath) && !empty($this->compiledCssPath))
            {
                return $this->compiledCssPath;
            }
            else
            {
                return null;
            }
        }

        /**
         * Get url where images files are stored
         * @return string
         */
        protected function getCustomCompiledImagesUrl()
        {
            $themeName              = Yii::app()->theme->name;
            $themeBaseUrl           = Yii::app()->themeManager->baseUrl . '/' . $themeName;
            return "'" . $themeBaseUrl . "/images/'";
        }

        /**
         * Set path where compiled custom css file will be saved
         */
        protected function setCompiledCustomCssPath()
        {
            if (!is_dir(Yii::getPathOfAlias('application.runtime.themes')))
            {
                mkdir(Yii::getPathOfAlias('application.runtime.themes'), 0755, true); // set recursive flag and permissions 0755
            }
            $this->compiledCustomCssPath = Yii::getPathOfAlias('application.runtime.themes');
        }

        /**
         * Get path where custom css files will be saved
         * @return null || string
         */
        public function getCompiledCustomCssPath()
        {
            if (isset($this->compiledCustomCssPath) && !empty($this->compiledCustomCssPath))
            {
                return $this->compiledCustomCssPath;
            }
            else
            {
                return null;
            }
        }

        /**
         * Set path for less files
         */
        protected function setLessFilesPath()
        {
            $themePath = Yii::app()->themeManager->getBasePath() . DIRECTORY_SEPARATOR . Yii::app()->theme->name;
            $this->lessFilesPath = $themePath . DIRECTORY_SEPARATOR . 'less';
        }

        /**
         * Get path of less files
         * @return null || string
         */
        protected function getLessFilesPath()
        {
            if (isset($this->lessFilesPath) && !empty($this->lessFilesPath))
            {
                return $this->lessFilesPath;
            }
            else
            {
                return null;
            }
        }

        /**
         * Set the themes color array to compile
         */
        protected function setThemeColors()
        {
            $this->themeColors =  Yii::app()->themeManager->getThemeColorNamesAndColors();
        }

        protected function getThemeColors()
        {
            if (isset($this->themeColors) && !empty($this->themeColors))
            {
                return $this->themeColors;
            }
            else
            {
                return null;
            }
        }

        /**
         * Initialize less compiler
         * @param $formatterName
         * @param $z_textColor
         * @param $z_themeColor
         * @param $z_themeColor2
         * @param $z_themeColorBtn
         * @param $z_themeColorHeader
         * @param $z_path
         * @return lessc
         */
        protected function initializeLessCompiler($formatterName,
                                                  $z_textColor,
                                                  $z_themeColor,
                                                  $z_themeColor2,
                                                  $z_themeColorBtn,
                                                  $z_themeColorHeader,
                                                  $z_path)
        {
            $lessCompiler = new lessc;
            $lessCompiler->setPreserveComments(false);
            $lessCompiler->setFormatter($formatterName);
            $lessCompiler->setImportDir($this->getLessFilesPath());
            $lessCompiler->setVariables(array(
                "z_textColor"         => $z_textColor, //text color all around
                "z_themeColor"        => $z_themeColor, //main color for links/titles/top-bar (blue in the original theme)
                "z_themeColor2"       => $z_themeColor2, //secondary color used for hovers and emphasizing (green in the original theme)
                "z_themeColorBtn"     => $z_themeColorBtn, //<-- this is suggested so buttons would always be green and not maybe red/purple etc.
                "z_themeColorHeader"  => $z_themeColorHeader,  //used to create the top dark bar gradient (top)
                "z_path"              => $z_path
                //"z_themeColorHeader2" => "#333535", //used to create the top dark bar gradient (bottom)
            ));
            return $lessCompiler;
        }

        /**
         * Compile all less files
         */
        public function compile()
        {

            foreach ($this->getThemeColors() as $colorName => $notUsed)
            {
                $this->compileColorDependentLessFile($colorName);
            }
            $this->compileBaseFiles();
        }

        /**
         * Compile only the base files that dont depend on the theme color
         */
        public function compileBaseFiles()
        {
            if (is_array($this->lessFilesToCompile) && !empty($this->lessFilesToCompile))
            {
                foreach ($this->lessFilesToCompile as $lessFile)
                {
                    $this->resolveCompiledFile($lessFile, $this->getCompiledCssPath());
                }
                if (file_exists($this->getLessFilesPath() . DIRECTORY_SEPARATOR . 'custom.less'))
                {
                    $this->resolveCompiledFile($this->getLessFilesPath() . DIRECTORY_SEPARATOR . 'custom.less', $this->getCompiledCssPath());
                }
            }
        }

        /**
         * Compile less files that depends on the theme color
         * @param string $colorName The name of the theme color
         * @throws NotFoundException
         */
        public function compileColorDependentLessFile($colorName)
        {
            assert('is_string($colorName)');
            $themeColors = $this->getThemeColors();
            if (isset($themeColors[$colorName]))
            {
                $colors = $themeColors[$colorName];
                if (is_array($this->themeColorDependentLessFilesToCompile) && !empty($this->themeColorDependentLessFilesToCompile))
                {
                    foreach ($this->themeColorDependentLessFilesToCompile as $fileToCompile)
                    {
                        $this->resolveCompileFileWithThemeColorsAndColorName($fileToCompile, $colors, $colorName);
                    }
                    if ($colorName == ThemeManager::CUSTOM_NAME)
                    {
                        Yii::app()->getAssetManager()->publish($this->getCompiledCustomCssPath(),false, -1, true);
                    }
                }
            }
            else
            {
                throw new NotFoundException();
            }
        }

        protected function resolveCompileFileWithThemeColorsAndColorName($fileToCompile, $themeColors, $colorName)
        {
            $compiledCssPath = $this->getCompiledCssPath();
            $imagesUrl = "'../images/'";
            if ($colorName == ThemeManager::CUSTOM_NAME)
            {
                $compiledCssPath = $this->getCompiledCustomCssPath();
                $imagesUrl = $this->getCustomCompiledImagesUrl();
            }
            if (is_string($colorName) && count($themeColors) == 5)
            {
                $this->resolveCompiledFile($fileToCompile, $compiledCssPath, true, $themeColors, $colorName, $imagesUrl);
            }
        }

        protected function resolveCompiledFile($fileToCompile, $compiledCssPath, $colorDependent = false, $themeColors = array(), $colorName = null, $imagesUrl = "'../images/'")
        {
            if (empty($themeColors))
            {
                $themeColors = array('#545454', '#282A76', '#7CB830', '#97c43d', '#464646');
            }
            // We need to construct new less compiler for each file, otherwise compiler doesn't work as expected
            $lessCompiler = $this->initializeLessCompiler($this->formatterName,
                            $themeColors[0], $themeColors[1], $themeColors[2], $themeColors[3], $themeColors[4], $imagesUrl);
            $lessFilePath = $this->getLessFilesPath() . DIRECTORY_SEPARATOR . $fileToCompile;
            $cssFileName = str_replace('less', 'css', $fileToCompile);
            if ($colorDependent)
            {
                $cssFileName  = str_replace('.less', '', $fileToCompile) . '-' . $colorName . '.css';
            }
            $cssFilePath  = $compiledCssPath . DIRECTORY_SEPARATOR . $cssFileName;
            $lessCompiler->compileFile($lessFilePath, $cssFilePath);
        }
    }
?>
