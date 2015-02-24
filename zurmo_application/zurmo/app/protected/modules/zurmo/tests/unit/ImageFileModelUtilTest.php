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

    class ImageFileModelUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $user = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $user;
        }

        public function testGetUrlForGetImageFromImageFileName()
        {
            $filename = '1_test.jpg';
            $this->assertContains(
                    'zurmo/imageModel/getImage?fileName=' . $filename,
                    ImageFileModelUtil::getUrlForGetImageFromImageFileName($filename)
            );
            $this->assertContains(
                'zurmo/imageModel/getThumb?fileName=' . $filename,
                ImageFileModelUtil::getUrlForGetImageFromImageFileName($filename, true)
            );
        }

        public function testGetImageSummary()
        {
            $imageFileModel = new ImageFileModel();
            $imageFileModel->name = 'test.gif';
            $imageFileModel->width = 100;
            $imageFileModel->height = 300;
            $imageFileModel->type = 'image/gif';
            $imageFileModel->save();
            $createdDateTime = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($imageFileModel->createdDateTime);
            $expectedContent = '<div class="builder-image-details"><strong>test.gif</strong><br />0 · 100 × 300 · Created by (Unnamed) on ' .
                                $createdDateTime . '</div>';
            $this->assertContains($expectedContent, ImageFileModelUtil::getImageSummary($imageFileModel));

            $expectedContent = $createdDateTime . ' by (Unnamed) 100 × 300';
            $this->assertContains($expectedContent,
                                  ImageFileModelUtil::getImageSummary($imageFileModel,
                                                                      "{createdTime} by {creator} {dimensions}"));
        }

        public function testGetImageSummaryWhenThereIsNoCachedFile()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $pathToFiles = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files');
            $filePath    = $pathToFiles . DIRECTORY_SEPARATOR . 'testImage.png';
            $fileUploadData = ImageFileModelUtil::saveImageFromTemporaryFile($filePath, 'fileNameTest');
            $id = $fileUploadData['id'];
            $imageFileModel = ImageFileModel::getById($id);
            $imageFileModel->width = null;
            $imageFileModel->height = null;
            $this->assertTrue($imageFileModel->save());
            unlink(ImageFileModel::getImageCachePathByFileName($imageFileModel->getImageCacheFileName(), false));
            ImageFileModelUtil::getImageSummary($imageFileModel);
        }

        public function testGetImageFileNameWithDimensions()
        {
            $filename = 'test.png';
            $this->assertEquals('123x321 test.png', ImageFileModelUtil::getImageFileNameWithDimensions($filename, 123, 321));

            $filename = '4512x12 test.png';
            $this->assertEquals('123x321 test.png', ImageFileModelUtil::getImageFileNameWithDimensions($filename, 123, 321));
        }

        public function testGetImageFromHtmlImgTag()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $expectedValue = 'Due to recent improvements in the Zurmo email template builder, you are required ' .
                                'to re-import the image from the external URL. The image currently in use is located ' .
                                'here http://testimagelink.png. You can also select from an existing image in the gallery.';
            $returnedValue = ImageFileModelUtil::getImageFromHtmlImgTag('<img src="http://testimagelink.png">');
            $this->assertContains($expectedValue, $returnedValue);

            $imageFileModel = new ImageFileModel();
            $imageFileModel->name = 'test.gif';
            $imageFileModel->width = 100;
            $imageFileModel->height = 300;
            $imageFileModel->type = 'image/gif';
            $imageFileModel->size = 1234;
            $imageFileModel->fileContent->content = '122';
            $this->assertTrue($imageFileModel->save());

            $url = Yii::app()->createAbsoluteUrl('zurmo/imageModel/getImage',
                                    array('fileName' => $imageFileModel->getImageCacheFileName()));
            $returnedValue = ImageFileModelUtil::getImageFromHtmlImgTag('<img src="'. $url .'">');
            $this->assertSame($imageFileModel, $returnedValue);
        }

        public function testSaveImageFromTemporaryFile()
        {
            $id = ImageFileModel::getCount() + 1;
            Yii::app()->user->userModel = User::getByUsername('super');
            $pathToFiles = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files');
            $filePath    = $pathToFiles . DIRECTORY_SEPARATOR . 'testImage.png';
            $fileUploadData = ImageFileModelUtil::saveImageFromTemporaryFile($filePath, 'fileNameTest');
            $this->assertEquals     ($id,                                           $fileUploadData['id']);
            $this->assertEquals     ('fileNameTest.png',                            $fileUploadData['name']);
            $this->assertContains   ('<img data-url=',                              $fileUploadData['summary']);
            $this->assertEquals     ('3.25KB',                                      $fileUploadData['size']);
            $this->assertContains   ("getThumb?fileName={$id}_fileNameTest.png",    $fileUploadData['thumbnail_url']); // Not Coding Standard
            $this->assertContains   ("getImage?fileName={$id}_fileNameTest.png",    $fileUploadData['filelink']); // Not Coding Standard
            $this->assertContains   ('javascript:parent.transferModalImageValues',  $fileUploadData['insert_link']);
        }
    }
?>
