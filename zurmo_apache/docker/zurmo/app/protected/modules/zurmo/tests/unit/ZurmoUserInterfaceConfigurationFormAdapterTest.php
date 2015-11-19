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

    class ZurmoUserInterfaceConfigurationFormAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            SecurityTestHelper::createSuperAdmin();
            $billy = UserTestHelper::createBasicUser('billy');
            $group = Group::getByName('Super Administrators');
            $group->users->add($billy);
            $saved = $group->save();
            assert($saved); // Not Coding Standard
            UserTestHelper::createBasicUser('sally');
        }

        public function testMakeFormAndSetConfigurationFromForm()
        {
            $billy = User::getByUsername('billy');
            Yii::app()->themeManager->customThemeColorsArray = array('#111111', '#222222', '#333333');
            Yii::app()->themeManager->globalThemeColor = 'custom';
            Yii::app()->themeManager->forceAllUsersTheme = true;
            $logoFileName = 'testLogo.png';
            $logoFilePath = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files') . DIRECTORY_SEPARATOR . $logoFileName;
            ZurmoUserInterfaceConfigurationFormAdapter::resizeLogoImageFile($logoFilePath, $logoFilePath, null, ZurmoUserInterfaceConfigurationForm::DEFAULT_LOGO_HEIGHT);
            $logoFileId   = ZurmoUserInterfaceConfigurationFormAdapter::saveLogoFile($logoFileName, $logoFilePath, 'logoFileModelId');
            ZurmoUserInterfaceConfigurationFormAdapter::publishLogo($logoFileName, $logoFilePath);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoFileModelId', $logoFileId);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'logoThumbFileModelId', $logoFileId);
            //Getting values
            $form = ZurmoUserInterfaceConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $this->assertEquals($logoFileName, $form->logoFileData['name']);
            $this->assertEquals('custom',      $form->themeColor);
            $this->assertEquals('#111111',     $form->customThemeColor1);
            $this->assertEquals('#222222',     $form->customThemeColor2);
            $this->assertEquals('#333333',     $form->customThemeColor3);
            $this->assertEquals('1',           $form->forceAllUsersTheme);
            //Setting values
            $form->themeColor            = 'lime';
            $form->customThemeColor1     = '#999999';
            $form->customThemeColor2     = '#888888';
            $form->customThemeColor3     = '#777777';
            $form->forceAllUsersTheme    = false;
            $logoFileName2               = 'testLogo2.png';
            $logoFilePath2               = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files') . DIRECTORY_SEPARATOR . $logoFileName2;
            copy($logoFilePath2, sys_get_temp_dir() . DIRECTORY_SEPARATOR . $logoFileName2);
            copy($logoFilePath2, sys_get_temp_dir() . DIRECTORY_SEPARATOR . ZurmoUserInterfaceConfigurationForm::LOGO_THUMB_FILE_NAME_PREFIX . $logoFileName2);
            Yii::app()->user->setState('logoFileName', $logoFileName2);
            ZurmoUserInterfaceConfigurationFormAdapter::setConfigurationFromForm($form);
            $form = ZurmoUserInterfaceConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $this->assertEquals('lime',         $form->themeColor);
            $this->assertEquals('#999999',      $form->customThemeColor1);
            $this->assertEquals('#888888',      $form->customThemeColor2);
            $this->assertEquals('#777777',      $form->customThemeColor3);
            $this->assertEquals(false,          $form->forceAllUsersTheme);
            $this->assertEquals($logoFileName2, $form->logoFileData['name']);
        }
    }
?>