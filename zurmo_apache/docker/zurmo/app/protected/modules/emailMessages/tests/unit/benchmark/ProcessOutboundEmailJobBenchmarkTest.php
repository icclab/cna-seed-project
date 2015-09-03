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
    class ProcessOutboundEmailJobBenchmarkTest extends BaseTest
    {
        protected $user;

        protected $singleItemExpectedTime   = 0.5;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            $this->user                 = User::getByUsername('super');
            Yii::app()->user->userModel = $this->user;
            EmailAccount::deleteAll();
            EmailMessage::deleteAll();
            EmailMessageContent::deleteAll();
            EmailMessageSender::deleteAll();
            EmailMessageRecipient::deleteAll();
            EmailMessageSendError::deleteAll();
            FileModel::deleteAll();
        }

        public function testSingleEmailMessage()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(1);
        }

        /**
         * @depends testSingleEmailMessage
         */
        public function testFiveEmailMessages()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(5);
        }

        /**
         * @depends testFiveEmailMessages
         */
        public function testTenEmailMessages()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(10);
        }

        /**
         * @depends testTenEmailMessages
         */
        public function testFiftyEmailMessages()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(50);
        }

        /**
         * @depends testFiftyEmailMessages
         */
        public function testHundredEmailMessages()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(100);
        }

        /**
         * @depends testHundredEmailMessages
         */
        public function testTwoFiftyEmailMessages()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(250);
        }

        /**
         * @depends testTwoFiftyEmailMessages
         */
        public function testFiveHundredEmailMessages()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(500);
        }

        /**
         * @depends testFiveHundredEmailMessages
         */
        public function testThousandEmailMessages()
        {
            $this->ensureTimeSpentIsLessOrEqualThanExpectedForCount(1000);
        }

        protected function ensureTimeSpentIsLessOrEqualThanExpectedForCount($count)
        {
            $expectedTime   = $this->singleItemExpectedTime * $count;
            $timeSpent      = $this->generateAndProcessEmailMessages($count);
            echo PHP_EOL. $count . ' emailMessage(s) took ' . $timeSpent . ' seconds';
            $this->assertLessThanOrEqual($expectedTime, $timeSpent);
        }

        public function generateAndProcessEmailMessages($count)
        {
            $box                        = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $outboxFolder               = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_OUTBOX);
            $sentFolder                 = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_SENT);

            $fileNames                  = array('testImage.png', 'testZip.zip', 'testPDF.pdf');
            $emailMessageIds            = array();
            $files                      = array();
            for ($i = 0; $i < $count; $i++)
            {
                $emailMessage               = EmailMessageTestHelper::createDraftSystemEmail('My Email Message ' . $i, $this->user);
                foreach ($fileNames as $index => $fileName)
                {
                    $file                       = ZurmoTestHelper::createFileModel($fileName);
                    $files[$index]['name']      = $fileName;
                    $files[$index]['type']      = $file->type;
                    $files[$index]['size']      = $file->size;
                    $files[$index]['contents']  = $file->fileContent->content;
                    $emailMessage->files->add($file);
                }
                $emailMessage->folder       = $outboxFolder;
                $saved                      = $emailMessage->save(false);
                $this->assertTrue($saved);
                $emailMessageIds[]          = $emailMessage->id;
                $emailMessage->forget();
                unset($emailMessage);
            }

            OutboundEmailBatchSizeConfigUtil::setBatchSize($count + 1);
            Yii::app()->jobQueue->deleteAll();
            ForgetAllCacheUtil::forgetAllCaches();
            $job                        = new ProcessOutboundEmailJob();
            $startedAt                  = microtime(true);
            $this->assertTrue($job->run());
            $timeTaken                  = microtime(true) - $startedAt;

            ForgetAllCacheUtil::forgetAllCaches();
            $emailMessages              = EmailMessage::getAll();
            $this->assertEquals($count, count($emailMessages));

            foreach ($emailMessageIds as $i => $emailMessageId)
            {
                $emailMessage   = EmailMessage::getById($emailMessageId);
                $this->assertEquals('My Email Message ' . $i, $emailMessage->subject);
                $this->assertEquals(1, $emailMessage->sendAttempts);
                $this->assertEquals($sentFolder->id, $emailMessage->folder->id);
                $this->assertNotEmpty($emailMessage->files);
                $this->assertCount(count($files), $emailMessage->files);
                foreach ($emailMessage->files as $index => $file)
                {
                    $this->assertEquals($file->name, $emailMessage->files[$index]->name);
                    $this->assertEquals($file->type, $emailMessage->files[$index]->type);
                    $this->assertEquals($file->size, $emailMessage->files[$index]->size);
                    $this->assertEquals($file->fileContent->content, $emailMessage->files[$index]->fileContent->content);
                    $this->assertEquals($file->fileContent->id, $emailMessage->files[$index]->fileContent->id);
                }
            }
            return $timeTaken;
        }
    }
?>