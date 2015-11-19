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
     * Class to adapt user interface configuration values into a configuration form.
     * Saves global values from a configuration form.
     */
    class ZurmoUserInterfaceConfigurationFormAdapter
    {
        /**
         * @return ZurmoUserInterfaceConfigurationForm
         */
        public static function makeFormFromGlobalConfiguration()
        {
            $form = new ZurmoUserInterfaceConfigurationForm();
            self::getThemeAttributes($form);
            self::getLogoAttributes($form);
            return $form;
        }

        /**
         * Given a ZurmoUserInterfaceConfigurationForm, save the configuration global values.
         */
        public static function setConfigurationFromForm(ZurmoUserInterfaceConfigurationForm $form)
        {
            self::setThemeAttributes($form);
            self::setLogoAttributes($form);
        }

        public static function getLogoAttributes(& $form)
        {
           if (null !== ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'logoThumbFileModelId'))
           {
               $logoThumbFileId  = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'logoThumbFileModelId');
               $logoThumbFileSrc = Yii::app()->createUrl('zurmo/default/logo');
               $logoThumbFile    = FileModel::getById($logoThumbFileId);
               $logoFileData     = array('name'              => $logoThumbFile->name,
                                         'type'              => $logoThumbFile->type,
                                         'size'              => (int) $logoThumbFile->size,
                                         'thumbnail_url'     => $logoThumbFileSrc);
           }
           else
           {
               $logoThumbFilePath = Yii::app()->theme->basePath . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Zurmo_logo.png';
               $logoThumbFileSrc  = Yii::app()->themeManager->baseUrl . '/default/images/Zurmo_logo.png';
               $logoFileData      = array('name'              => pathinfo($logoThumbFilePath, PATHINFO_FILENAME),
                                          'type'              => ZurmoFileHelper::getMimeType($logoThumbFilePath),
                                          'size'              => filesize($logoThumbFilePath),
                                          'thumbnail_url'     => $logoThumbFileSrc);
           }
           $form->logoFileData  = $logoFileData;
        }

        public static function getThemeAttributes(& $form)
        {
            $customThemeColorsArray   = Yii::app()->themeManager->customThemeColorsArray;
            $form->themeColor         = Yii::app()->themeManager->globalThemeColor;
            $form->customThemeColor1  = $customThemeColorsArray[0];
            $form->customThemeColor2  = $customThemeColorsArray[1];
            $form->customThemeColor3  = $customThemeColorsArray[2];
            $form->forceAllUsersTheme = Yii::app()->themeManager->forceAllUsersTheme;
        }

        public static function setLogoAttributes($form)
        {
           if (Yii::app()->user->getState('deleteCustomLogo') === true)
           {
               if (ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'logoFileModelId') !== null)
               {
                   self::deleteCurrentCustomLogo();
                   ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoFileModelId', null);
                   ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoThumbFileModelId', null);
                   ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoWidth', null);
                   ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoHeight', null);
                   Yii::app()->user->setState('deleteCustomLogo', null);
               }
           }
           if (null !== Yii::app()->user->getState('logoFileName'))
           {
               $logoFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . Yii::app()->user->getState('logoFileName');
               self::resizeLogoImageFile($logoFilePath, $logoFilePath, null, ZurmoUserInterfaceConfigurationForm::DEFAULT_LOGO_HEIGHT);
               $logoFileName = Yii::app()->user->getState('logoFileName');
               $logoFileId   = self::saveLogoFile($logoFileName, $logoFilePath, 'logoFileModelId');
               self::publishLogo($logoFileName, $logoFilePath);
               self::deleteCurrentCustomLogo();
               ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoFileModelId', $logoFileId);
               $thumbFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . ZurmoUserInterfaceConfigurationForm::LOGO_THUMB_FILE_NAME_PREFIX . $logoFileName;
               $thumbFileId = self::saveLogoFile($logoFileName, $thumbFilePath, 'logoThumbFileModelId');
               ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoThumbFileModelId', $thumbFileId);
               Yii::app()->user->setState('logoFileName', null);
           }
        }

        public static function setThemeAttributes($form)
        {
            $shouldCompile             = false;
            $customThemeColorsArray    = array();
            $customThemeColorsArray[0] = $form->customThemeColor1;
            $customThemeColorsArray[1] = $form->customThemeColor2;
            $customThemeColorsArray[2] = $form->customThemeColor3;
            if ($customThemeColorsArray != Yii::app()->themeManager->customThemeColorsArray)
            {
                $shouldCompile = true;
            }
            Yii::app()->themeManager->customThemeColorsArray = $customThemeColorsArray;
            Yii::app()->themeManager->globalThemeColor       = $form->themeColor;
            Yii::app()->themeManager->forceAllUsersTheme     = $form->forceAllUsersTheme;
            if ($shouldCompile)
            {
                Yii::app()->lessCompiler->compileColorDependentLessFile(ThemeManager::CUSTOM_NAME);
            }
        }

        public static function resolveLogoWidth()
        {
           if (!($logoWidth = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'logoWidth')))
           {
               $logoWidth = ZurmoUserInterfaceConfigurationForm::DEFAULT_LOGO_WIDTH;
           }
           return $logoWidth;
        }

        public static function resolveLogoHeight()
        {
           if (!($logoHeight = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'logoHeight')))
           {
               $logoHeight = ZurmoUserInterfaceConfigurationForm::DEFAULT_LOGO_HEIGHT;
           }
           return $logoHeight;
        }

        public static function saveLogoFile($fileName, $filePath, $fileModelIdentifier)
        {
           if (ZurmoConfigurationUtil::getByModuleName('ZurmoModule', $fileModelIdentifier) !== null)
           {
               $fileModelId                   = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', $fileModelIdentifier);
               $file                          = FileModel::getById($fileModelId);
               $contents                      = file_get_contents($filePath);
               $file->fileContent->content    = $contents;
               $file->name                    = $fileName;
               $file->type                    = ZurmoFileHelper::getMimeType($filePath);
               $file->size                    = filesize($filePath);
               $file->save();
               return $file->id;
           }
           else
           {
               $contents             = file_get_contents($filePath);
               $fileContent          = new FileContent();
               $fileContent->content = $contents;
               $file                 = new FileModel();
               $file->fileContent    = $fileContent;
               $file->name           = $fileName;
               $file->type           = ZurmoFileHelper::getMimeType($filePath);
               $file->size           = filesize($filePath);
               $file->save();
               return $file->id;
           }
        }

        public static function publishLogo($logoFileName, $logoFilePath)
        {
            if (!is_dir(Yii::getPathOfAlias('application.runtime.uploads')))
            {
                mkdir(Yii::getPathOfAlias('application.runtime.uploads'), 0755, true); // set recursive flag and permissions 0755
            }
            copy($logoFilePath, Yii::getPathOfAlias('application.runtime.uploads') . DIRECTORY_SEPARATOR . $logoFileName);
            Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.runtime.uploads') . DIRECTORY_SEPARATOR . $logoFileName);
        }

        public static function deleteCurrentCustomLogo()
        {
            if ($logoFileModelId = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'logoFileModelId'))
            {
                //Get path of currently uploaded logo, required to delete/unlink legacy logo from runtime/uploads
                $logoFileModel       = FileModel::getById($logoFileModelId);
                $currentLogoFileName = $logoFileModel->name;
                $currentLogoFilePath = Yii::getPathOfAlias('application.runtime.uploads') . DIRECTORY_SEPARATOR . $currentLogoFileName;
                if (file_exists($currentLogoFilePath))
                {
                    unlink($currentLogoFilePath);
                }
            }
        }

        public static function resizeLogoImageFile($sourcePath, $destinationPath, $newWidth, $newHeight)
        {
            WideImage::load($sourcePath)->resize($newWidth, $newHeight)->saveToFile($destinationPath);
            list($logoWidth, $logoHeight) = getimagesize($destinationPath);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoWidth', $logoWidth);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoHeight', $logoHeight);
        }
    }
?>