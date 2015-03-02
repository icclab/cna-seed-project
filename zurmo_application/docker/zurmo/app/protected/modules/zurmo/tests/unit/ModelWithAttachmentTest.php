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
     * Series of tests to test attachments and file upload.
     *
     */
    class ModelWithAttachmentTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public static function tearDownAfterClass()
        {
            parent::tearDownAfterClass();
            $_FILES = null;
        }

        public static function getDependentTestModelClassNames()
        {
            return array('ModelWithAttachmentTestItem');
        }

        public function setUp()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testFileSetAndGet()
        {
            $fileCount = FileModel::getCount();
            $this->assertEquals(0, $fileCount);

            $pathToFiles = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files');
            $filePath    = $pathToFiles . DIRECTORY_SEPARATOR . 'testNote.txt';
            $contents    = file_get_contents($pathToFiles . DIRECTORY_SEPARATOR . 'testNote.txt');
            $this->assertEquals(6495, strlen($contents));
            $fileContent          = new FileContent();
            $fileContent->content = $contents;
            $file          = new FileModel();
            $file->fileContent = $fileContent;
            $file->name    = 'testNote.txt';
            $file->type    = ZurmoFileHelper::getMimeType($pathToFiles . DIRECTORY_SEPARATOR . 'testNote.txt');
            $file->size    = filesize($filePath);
            $saved         = $file->save();
            $this->assertTrue($saved);
            $fileId = $file->id;
            $file->forget();

            //Now retrieve the file and make sure the content matches.
            $file = FileModel::getById($fileId);
            $this->assertEquals($contents, $file->fileContent->content);
            $this->assertEquals('testNote.txt', $file->name);
            $this->assertEquals('text/plain', $file->type);
            $this->assertEquals(6495, $file->size);

            //Remove the fileModel. The related fileContent should also be removed because it is OWNED by the fileModel.
            $this->assertEquals(1, FileModel::getCount());
            $this->assertEquals(1, FileContent::getCount());
            $file->delete();
            $this->assertEquals(0, FileModel::getCount());
            $this->assertEquals(0, FileContent::getCount());
        }

        public function testMakeFileViaUpload()
        {
            $pathToFiles = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files');
            $contents    = file_get_contents($pathToFiles . DIRECTORY_SEPARATOR . 'testNote.txt');
            $filePath    = $pathToFiles . DIRECTORY_SEPARATOR . 'testNote.txt';
            self::resetAndPopulateFilesArrayByFilePathAndName('test', $filePath, 'testNote.txt');
            $uploadedFile = CUploadedFile::getInstanceByName('test');
            $fileModel     = FileModelUtil::makeByUploadedFile($uploadedFile);
            $this->assertTrue($fileModel instanceof FileModel);
            $fileModelId = $fileModel->id;
            $fileModel->forget();

            //Now retrieve the file and make sure the content matches.
            $file = FileModel::getById($fileModelId);
            $this->assertEquals($contents, $file->fileContent->content);
            $this->assertEquals('testNote.txt', $file->name);
            $this->assertEquals('text/plain', $file->type);
            $this->assertEquals(6495, $file->size);
            $fileModel->delete();
            $this->assertEquals(0, FileModel::getCount());
            $this->assertEquals(0, FileContent::getCount());
        }

        /**
         * @expectedException FailedFileUploadException
         */
        public function testMakeFileViaUploadWhereFileIsAnEmptyFile()
        {
            $pathToFiles = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files');
            $contents    = file_get_contents($pathToFiles . DIRECTORY_SEPARATOR . 'testEmptyNote.txt');
            $filePath    = $pathToFiles . DIRECTORY_SEPARATOR . 'testEmptyNote.txt';
            self::resetAndPopulateFilesArrayByFilePathAndName('test', $filePath, 'testEmptyNote.txt');
            $uploadedFile = CUploadedFile::getInstanceByName('test');
            $fileModel     = FileModelUtil::makeByUploadedFile($uploadedFile);
        }

        /**
        * @expectedException FailedFileUploadException
        */
        public function testMakeFileViaUploadWhereFileNotExist()
        {
            $pathToFiles = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files');
            $filePath    = $pathToFiles . DIRECTORY_SEPARATOR . 'testMissingFile.txt';
            $uploadedFile = CUploadedFile::getInstanceByName('test');
            $fileModel     = FileModelUtil::makeByUploadedFile($uploadedFile);
        }

        public function testModelWithAttachmentTestItem()
        {
            $pathToFiles    = Yii::getPathOfAlias('application.modules.zurmo.tests.unit.files');
            $filePath       = $pathToFiles . DIRECTORY_SEPARATOR . 'testNote.txt';

            // create 2 extra file model which should persist when $model is delete, their content should also persist.
            $pdfFile        = FileModelUtil::makeByFilePathAndName($filePath, 'testPDF.pdf');
            $pdfFileId      = $pdfFile->id;
            $pdfFileName    = $pdfFile->name;
            $pdfFileContent = $pdfFile->fileContent->content;
            $pdfFileType    = $pdfFile->type;
            $pdfFileSize    = $pdfFile->size;
            $pdfFile->forgetAll();
            unset($pdfFile);
            $zipFile        = FileModelUtil::makeByFilePathAndName($filePath, 'testZip.zip');
            $zipFileId      = $zipFile->id;
            $zipFileName    = $zipFile->name;
            $zipFileContent = $zipFile->fileContent->content;
            $zipFileType    = $zipFile->type;
            $zipFileSize    = $zipFile->size;
            $zipFile->forgetAll();
            unset($zipFile);

            $model = new ModelWithAttachmentTestItem();
            $fileModel     = FileModelUtil::makeByFilePathAndName($filePath, 'testNote.txt');
            $firstFileModelId = $fileModel->id;
            $this->assertTrue($fileModel instanceof FileModel);
            $this->assertEquals(6495, strlen($fileModel->fileContent->content));
            $theContent    = $fileModel->fileContent->content;
            $model->files->add($fileModel);
            $model->member = 'test';
            $saved         = $model->save();
            $this->assertTrue($saved);
            $modelId = $model->id;
            $model->forget();

            $model = ModelWithAttachmentTestItem::getById($modelId);
            $this->assertEquals($theContent,    $model->files[0]->fileContent->content);
            $this->assertEquals('testNote.txt', $model->files[0]->name);
            $this->assertEquals('text/plain',   $model->files[0]->type);
            $this->assertEquals(6495,           $model->files[0]->size);

            //Now try using CUploadedFile.
            self::resetAndPopulateFilesArrayByFilePathAndName('aTest', $filePath, 'testNote.txt');
            $uploadedFile = CUploadedFile::getInstanceByName('aTest');
            $this->assertTrue($uploadedFile instanceof CUploadedFile);
            $fileModel     = FileModelUtil::makeByUploadedFile($uploadedFile);
            $this->assertTrue($fileModel instanceof FileModel);
            $this->assertTrue($fileModel->id != $firstFileModelId);
            $this->assertTrue($fileModel->id > 0);
            $model = ModelWithAttachmentTestItem::getById($modelId);
            $model->files->add($fileModel);
            $saved         = $model->save();
            $this->assertTrue($saved);
            $modelId = $model->id;
            $model->forget();
            RedBeanModel::forgetAll();

            $model = ModelWithAttachmentTestItem::getById($modelId);
            $this->assertEquals(2, $model->files->count());

            //Delete the model and confirm the related models are removed
            $this->assertEquals(1, ModelWithAttachmentTestitem::getCount());
            $this->assertEquals(4, FileModel::getCount());
            $this->assertEquals(4, FileContent::getCount());
            $this->assertTrue($model->delete());
            $this->assertEquals(0, ModelWithAttachmentTestitem::getCount());
            $this->assertEquals(2, FileModel::getCount());
            $this->assertEquals(2, FileContent::getCount());

            $pdfFile    = FileModel::getById($pdfFileId);
            $this->assertEquals($pdfFileContent,    $pdfFile->fileContent->content);
            $this->assertEquals($pdfFileName,       $pdfFile->name);
            $this->assertEquals($pdfFileType,       $pdfFile->type);
            $this->assertEquals($pdfFileSize,       $pdfFile->size);
            $zipFile    = FileModel::getById($zipFileId);
            $this->assertEquals($zipFileContent,    $zipFile->fileContent->content);
            $this->assertEquals($zipFileName,       $zipFile->name);
            $this->assertEquals($zipFileType,       $zipFile->type);
            $this->assertEquals($zipFileSize,       $zipFile->size);
        }

        public function testSavingFileContentWhenNotModified()
        {
            $fileContent = new FileContent();
            $fileContent-> content = 'testContent';
            $file          = new FileModel();
            $file->fileContent = $fileContent;
            $file->name    = 'aTestName';
            $file->type    = 'aTestType';
            $file->size    = 500;
            $saved         = $file->save();
            $this->assertTrue($saved);
            $fileId = $file->id;
            $file->forget();

            //Now retrieve the file and make sure the content matches.
            $fileModel = FileModel::getById($fileId);
            $this->assertTrue($fileModel->save());
            $file->forget();
            $fileModel = FileModel::getById($fileId);
            $this->assertEquals('testContent', $file->fileContent->content);
        }
    }
?>