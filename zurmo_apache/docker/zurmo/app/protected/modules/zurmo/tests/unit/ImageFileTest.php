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

    class ImageFileTest extends ZurmoBaseTest
    {
        protected $imageFile1Id;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
            $file = ZurmoTestHelper::createImageFileModel();
            $this->imageFile1Id = $file->id;
            $file->forget();
        }

        public function testCreatingImageTypeValidation()
        {
            $fileContent = new FileContent();
            $fileContent->content = 'testContent';
            $imageFile              = new ImageFileModel();
            $imageFile->name        = 'testImage';
            $imageFile->size        = 123;
            $imageFile->type        = 'plain/txt';
            $imageFile->fileContent = $fileContent;
            $this->assertFalse($imageFile->save());
            $this->assertCount(1, $imageFile->getErrors());
            $this->assertEquals('File type is not valid.', $imageFile->getError('type'));
            $imageFile->type = 'image/png';
            $this->assertTrue($imageFile->save());
            $imageFile->type = 'image/jpg';
            $this->assertTrue($imageFile->save());
            $imageFile->type = 'image/gif';
            $this->assertTrue($imageFile->save());
            $imageFile->type = 'image/jpeg';
            $this->assertTrue($imageFile->save());
        }

        public function testGetByFileName()
        {
            $imageFile = ImageFileModel::getById($this->imageFile1Id);
            $fileName  = $imageFile->getImageCacheFileName();
            $imageFileReturned = ImageFileModel::getByFileName($fileName);
            $this->assertTrue($imageFileReturned->isSame($imageFile));
            try
            {
                $exceptionTriggered = false;
                ImageFileModel::getByFileName('nonExistantFileName');
            }
            catch (NotFoundException $exception)
            {
                $exceptionTriggered = true;
            }
            $this->assertTrue($exceptionTriggered);
        }

        public function testGetImageCachePath()
        {
            $imageFile = ImageFileModel::getById($this->imageFile1Id);
            $expectedPath = Yii::getPathOfAlias('application.runtime.uploads') . DIRECTORY_SEPARATOR .
                                    $imageFile->getImageCacheFileName();
            $this->assertEquals($expectedPath, $imageFile->getImageCachePath());
            $expectedPath = Yii::getPathOfAlias('application.runtime.uploads') . DIRECTORY_SEPARATOR .
                                    ImageFileModel::THUMB_FILE_NAME_PREFIX . $imageFile->getImageCacheFileName();
            $this->assertEquals($expectedPath, $imageFile->getImageCachePath(true));
        }

        public function tesGetImageCachePathByFileName()
        {
            $imageFile = ImageFileModel::getById($this->imageFile1Id);
            $expectedPath = Yii::getPathOfAlias('application.runtime.uploads') . DIRECTORY_SEPARATOR .
                $imageFile->getImageCacheFileName();
            $this->assertEquals($expectedPath,
                                ImageFileModel::getImageCachePathByFileName($imageFile->getImageCacheFileName(), false));
            $expectedPath = Yii::getPathOfAlias('application.runtime.uploads') . DIRECTORY_SEPARATOR .
                ImageFileModel::THUMB_FILE_NAME_PREFIX . $imageFile->getImageCacheFileName();
            $this->assertEquals($expectedPath,
                                ImageFileModel::getImageCachePathByFileName($imageFile->getImageCacheFileName(), true));
        }

        public function testGetImageCacheFileName()
        {
            $imageFile = ImageFileModel::getById($this->imageFile1Id);
            $expectedImageCacheFileName = $imageFile->id . ImageFileModel::FILE_NAME_SEPARATOR . $imageFile->name;
            $this->assertEquals($expectedImageCacheFileName, $imageFile->getImageCacheFileName());
        }

        public function testCreateImageCache()
        {
            $pathToFiles  = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files');
            $fileContents = file_get_contents($pathToFiles . DIRECTORY_SEPARATOR . 'testImage.png');
            $imageFile    = ImageFileModel::getById($this->imageFile1Id);
            if (file_exists($imageFile->getImageCachePath()))
            {
                unlink($imageFile->getImageCachePath());
            }
            if (file_exists($imageFile->getImageCachePath(true)))
            {
                unlink($imageFile->getImageCachePath(true));
            }
            $this->assertEquals($fileContents, $imageFile->fileContent->content);
            $this->assertEquals('testImage.png', $imageFile->name);
            $this->assertEquals('image/png', $imageFile->type);
            $this->assertEquals(3332, $imageFile->size);
            $this->assertFalse(file_exists($imageFile->getImageCachePath()));
            $this->assertFalse(file_exists($imageFile->getImageCachePath(true)));
            //Now cache the image without the creation of the thumb
            $imageFile->createImageCache();
            $this->assertTrue(file_exists($imageFile->getImageCachePath()));
            $this->assertFalse(file_exists($imageFile->getImageCachePath(true)));
            //Now cache the image and create the thumb
            $imageFile->createImageCache(true);
            $this->assertTrue(file_exists($imageFile->getImageCachePath()));
            $this->assertTrue(file_exists($imageFile->getImageCachePath(true)));
        }

        public function testCreateImageIsSharedDefaultValue()
        {
            $imageFile = new ImageFileModel();
            $this->assertFalse((bool) $imageFile->isShared);
        }

        public function testToggle()
        {
            $fileContent = new FileContent();
            $fileContent->content = 'testContent';
            $imageFile              = new ImageFileModel();
            $imageFile->name        = 'testImage';
            $imageFile->size        = 123;
            $imageFile->type        = 'image/gif';
            $imageFile->fileContent = $fileContent;
            $imageFile->save();
            $this->assertFalse((bool) $imageFile->isShared);

            $imageFile->toggle('isShared');
            $this->assertTrue((bool) $imageFile->isShared);
        }

        public function testIsToggleable()
        {
            $fileContent = new FileContent();
            $fileContent->content = 'testContent';
            $imageFile              = new ImageFileModel();
            $imageFile->name        = 'testImage';
            $imageFile->size        = 123;
            $imageFile->type        = 'image/gif';
            $imageFile->fileContent = $fileContent;
            $imageFile->save();
            $this->assertTrue($imageFile->isToggleable('isShared'));
            $this->assertFalse($imageFile->isToggleable('name'));
            $this->assertFalse($imageFile->isToggleable('size'));
            $this->assertFalse($imageFile->isToggleable('content'));

            Yii::app()->user->userModel = UserTestHelper::createBasicUser('test');
            $this->assertFalse($imageFile->isToggleable('isShared'));
        }

        public function testCanDelete()
        {
            $fileContent = new FileContent();
            $fileContent->content = 'testContent';
            $imageFile              = new ImageFileModel();
            $imageFile->name        = 'testImage';
            $imageFile->size        = 123;
            $imageFile->type        = 'image/gif';
            $imageFile->fileContent = $fileContent;
            $imageFile->save();
            $this->assertTrue($imageFile->canDelete());

            Yii::app()->user->userModel = UserTestHelper::createBasicUser('test2');
            $this->assertFalse($imageFile->canDelete());
        }
    }
?>