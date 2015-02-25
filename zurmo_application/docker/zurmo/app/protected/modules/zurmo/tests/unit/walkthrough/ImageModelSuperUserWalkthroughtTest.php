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

    class ImageModelSuperUserWalkthroughtTest extends ZurmoWalkthroughBaseTest
    {
        protected $imageFileId;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
            $file = ZurmoTestHelper::createImageFileModel();
            $this->imageFile1Id = $file->id;
            $file->forget();
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super     = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $imageFile = ImageFileModel::getById($this->imageFile1Id);
            if (file_exists($imageFile->getImageCachePath()))
            {
                unlink($imageFile->getImageCachePath());
            }
            if (file_exists($imageFile->getImageCachePath(true)))
            {
                unlink($imageFile->getImageCachePath(true));
            }
            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/imageModel/getUploaded');
            $returnedObject = CJSON::decode($content);
            $this->assertContains('zurmo/imageModel/getImage?fileName=' . $imageFile->getImageCacheFileName(),
                                  $returnedObject[0]['image']);
            $this->assertContains('zurmo/imageModel/getThumb?fileName=' . $imageFile->getImageCacheFileName(),
                                  $returnedObject[0]['thumb']);
            $this->assertFalse(file_exists($imageFile->getImageCachePath()));
            $this->assertFalse(file_exists($imageFile->getImageCachePath(true)));
            //Test getting the image
            $this->setGetArray(array('fileName' => $imageFile->getImageCacheFileName()));
            @$this->runControllerWithExitExceptionAndGetContent('zurmo/imageModel/getImage');
            $this->assertTrue(file_exists($imageFile->getImageCachePath()));
            $this->assertFalse(file_exists($imageFile->getImageCachePath(true)));
            //Test getting the image thumb
            $this->setGetArray(array('fileName' => $imageFile->getImageCacheFileName()));
            @$this->runControllerWithExitExceptionAndGetContent('zurmo/imageModel/getThumb');
            $this->assertTrue(file_exists($imageFile->getImageCachePath()));
            $this->assertTrue(file_exists($imageFile->getImageCachePath(true)));
            //Test uploading invalid image
            $pathToFiles = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files');
            $filePath    = $pathToFiles . DIRECTORY_SEPARATOR . 'testNote.txt';
            self::resetAndPopulateFilesArrayByFilePathAndName('file', $filePath, 'testNote.txt');
            $content = $this->runControllerWithNoExceptionsAndGetContent('zurmo/imageModel/upload');
            $returnedObject = CJSON::decode($content);
            $this->assertEquals('Error uploading the image', $returnedObject[0]['error']);
            //Test uploading valid image
            $pathToFiles  = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files');
            $fileContents = file_get_contents($pathToFiles . DIRECTORY_SEPARATOR . 'testImage.png');
            $filePath     = $pathToFiles . DIRECTORY_SEPARATOR . 'testImage.png';
            self::resetAndPopulateFilesArrayByFilePathAndName('file', $filePath, 'testImage.png');
            $content        = $this->runControllerWithNoExceptionsAndGetContent('zurmo/imageModel/upload');
            $returnedObject = CJSON::decode($content);
            $this->assertContains('zurmo/imageModel/getImage?fileName=2_testImage.png', $returnedObject[0]['filelink']); // Not Coding Standard
            $createdImageFile = ImageFileModel::getById(2);
            $this->assertEquals($fileContents, $createdImageFile->fileContent->content);
            //Test deleting image
            $createdImageFile->inactive = false;
            $this->setGetArray(array('id' => 2));
            $this->runControllerWithNoExceptionsAndGetContent('zurmo/imageModel/delete', true);
            RedBeanModel::forgetAll();
            $deletedImageFile = ImageFileModel::getById(2);
            $deletedImageFile->inactive = true;
        }
    }
?>