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

    class EmailHelperTest extends ZurmoBaseTest
    {
        public static $emailHelperSendEmailThroughTransport;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            self::$emailHelperSendEmailThroughTransport = Yii::app()->emailHelper->sendEmailThroughTransport;
            UserTestHelper::createBasicUser('billy');
            UserTestHelper::createBasicUser('jane');
            $someoneSuper = UserTestHelper::createBasicUser('someoneSuper');

            $group = Group::getByName('Super Administrators');
            $group->users->add($someoneSuper);
            $saved = $group->save();
            assert($saved); // Not Coding Standard

            $box = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);

            if (EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $steve = UserTestHelper::createBasicUser('steve');
                EmailMessageTestHelper::createEmailAccount($steve);

                Yii::app()->imap->imapHost        = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapHost'];
                Yii::app()->imap->imapUsername    = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapUsername'];
                Yii::app()->imap->imapPassword    = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapPassword'];
                Yii::app()->imap->imapPort        = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapPort'];
                Yii::app()->imap->imapSSL         = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapSSL'];
                Yii::app()->imap->imapFolder      = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapFolder'];
                Yii::app()->imap->setInboundSettings();
                Yii::app()->imap->init();

                Yii::app()->emailHelper->outboundHost     = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundHost'];
                Yii::app()->emailHelper->outboundPort     = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPort'];
                Yii::app()->emailHelper->outboundUsername = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundUsername'];
                Yii::app()->emailHelper->outboundPassword = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPassword'];
                Yii::app()->emailHelper->outboundSecurity = Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundSecurity'];
                Yii::app()->emailHelper->sendEmailThroughTransport = true;
                Yii::app()->emailHelper->setOutboundSettings();
                Yii::app()->emailHelper->init();
            }
            // Delete item from jobQueue, that is created when new user is created
            Yii::app()->jobQueue->deleteAll();
        }

        public function testSend()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $emailMessage = EmailMessageTestHelper::createDraftSystemEmail('a test email', $super);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $this->assertEquals(0, count(Yii::app()->jobQueue->getAll()));
            Yii::app()->emailHelper->send($emailMessage);
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs));
            $this->assertEquals('ProcessOutboundEmail', $queuedJobs[0][0]['jobType']);
        }

        /**
         * @depends testSend
         */
        public function testSendQueued()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //add a message in the outbox_error folder.
            $emailMessage         = EmailMessageTestHelper::createDraftSystemEmail('a test email 2', $super);
            $box                  = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_OUTBOX_ERROR);
            $emailMessage->save();

            $this->assertEquals(2, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            Yii::app()->emailHelper->sendQueued();
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(2, Yii::app()->emailHelper->getSentCount());

            //add a message in the outbox folder.
            $emailMessage         = EmailMessageTestHelper::createDraftSystemEmail('a test email 3', $super);
            $box                  = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_OUTBOX);
            $emailMessage->save();
            //add a message in the outbox_error folder.
            $emailMessage         = EmailMessageTestHelper::createDraftSystemEmail('a test email 4', $super);
            $box                  = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_OUTBOX_ERROR);
            $emailMessage->save();
            //add a message in the outbox_error folder.
            $emailMessage         = EmailMessageTestHelper::createDraftSystemEmail('a test email 5', $super);
            $box                  = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_OUTBOX_ERROR);
            $emailMessage->save();

            $this->assertEquals(3, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(2, Yii::app()->emailHelper->getSentCount());
            Yii::app()->emailHelper->sendQueued(1);
            $this->assertEquals(2, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(3, Yii::app()->emailHelper->getSentCount());
            Yii::app()->emailHelper->sendQueued(2);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(5, Yii::app()->emailHelper->getSentCount());
        }

        /**
         * @depends testSendQueued
         */
        public function testSendImmediately()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $emailMessage = EmailMessageTestHelper::createDraftSystemEmail('a test email 2', $super);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(5, Yii::app()->emailHelper->getSentCount());
            Yii::app()->emailHelper->sendImmediately($emailMessage);
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(6, Yii::app()->emailHelper->getSentCount());
        }

        /**
         * @depends testSendImmediately
         */
        public function testLoadOutboundSettings()
        {
            $emailHelper = new EmailHelper;
            $emailHelper->outboundHost = null;

            ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', 'outboundHost', 'xxx');

            $emailHelper = new EmailHelper;
            $emailHelper->outboundHost = 'xxx';
        }

        /**
         * @depends testLoadOutboundSettings
         */
        public function testLoadOutboundSettingsFromUserEmailAccount()
        {
            $billy                      = User::getByUsername('billy');
            Yii::app()->user->userModel = $billy;
            $emailHelper = new EmailHelper;

            //Load outbound setting when no EmailAccount was created
            try
            {
                $emailHelper->loadOutboundSettingsFromUserEmailAccount($billy);
                $this->fail();
            }
            catch (NotFoundException $e)
            {
                $this->addToAssertionCount(1);
            }

            //Load outbound setting when EmailAccount useCustomOutboundSettings = false
            EmailMessageTestHelper::createEmailAccount($billy);
            $emailHelper->loadOutboundSettingsFromUserEmailAccount($billy);
            $this->assertEquals('smtp', $emailHelper->outboundType);
            $this->assertEquals(Yii::app()->params['emailTestAccounts']['smtpSettings']['outboundPort'], $emailHelper->outboundPort);
            //outboundHost was set on @testLoadOutboundSettingsFromUserEmailAccount
            $this->assertEquals('xxx', $emailHelper->outboundHost);
            $this->assertEquals($emailHelper->defaultTestToAddress, $emailHelper->fromAddress);
            $this->assertEquals(strval($billy), $emailHelper->fromName);

            //Load outbound setting when EmailAccount useCustomOutboundSettings = true
            $emailAccount = EmailAccount::getByUserAndName($billy);
            $emailAccount->useCustomOutboundSettings = true;
            $emailAccount->outboundType = 'xyz';
            $emailAccount->outboundPort = 55;
            $emailAccount->outboundHost = 'zurmo.com';
            $emailAccount->outboundUsername = 'billy';
            $emailAccount->outboundPassword = 'billypass';
            $emailAccount->outboundSecurity = 'ssl';
            $emailAccount->save();
            $emailHelper->loadOutboundSettingsFromUserEmailAccount($billy);
            $this->assertEquals('xyz', $emailHelper->outboundType);
            $this->assertEquals(55, $emailHelper->outboundPort);
            $this->assertEquals('zurmo.com', $emailHelper->outboundHost);
            $this->assertEquals('billy', $emailHelper->outboundUsername);
            $this->assertEquals('billypass', $emailHelper->outboundPassword);
            $this->assertEquals('ssl', $emailHelper->outboundSecurity);
            $this->assertEquals($billy->getFullName(), $emailHelper->fromName);
            $this->assertEquals('user@zurmo.com', $emailHelper->fromAddress);
        }

        /**
         * @depends testSend
         */
        public function testSendRealEmail()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            if (EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                $user = User::getByUsername('steve');
                $user->primaryEmail->emailAddress = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapUsername'];
                $this->assertTrue($user->save());

                Yii::app()->imap->connect();
                Yii::app()->imap->deleteMessages(true);
                $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
                $this->assertEquals(0, $imapStats->Nmsgs);

                $emailMessage = EmailMessageTestHelper::createOutboxEmail($super, 'Test email',
                    'Raw content', ',b>html content</b>end.', // Not Coding Standard
                    'Zurmo', Yii::app()->emailHelper->outboundUsername,
                    'Ivica', Yii::app()->params['emailTestAccounts']['userImapSettings']['imapUsername']);

                $filesIds = array();
                $fileTxt = ZurmoTestHelper::createFileModel('testNote.txt');
                $filesIds[] = $fileTxt->id;
                $filePng = ZurmoTestHelper::createFileModel('testImage.png');
                $filesIds[] = $filePng->id;
                $fileZip = ZurmoTestHelper::createFileModel('testZip.zip');
                $filesIds[] = $fileZip->id;
                $filePdf = ZurmoTestHelper::createFileModel('testPDF.pdf');
                $filesIds[] = $filePdf->id;
                EmailMessageUtil::attachFilesToMessage($filesIds, $emailMessage);
                $this->assertEquals('4', count($emailMessage->files));

                Yii::app()->imap->connect();
                $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
                $this->assertEquals(0, $imapStats->Nmsgs);

                $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
                $this->assertEquals(6, Yii::app()->emailHelper->getSentCount());
                Yii::app()->emailHelper->sendQueued();
                $job = new ProcessOutboundEmailJob();
                $this->assertTrue($job->run());
                $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
                $this->assertEquals(7, Yii::app()->emailHelper->getSentCount());

                sleep(30);
                Yii::app()->imap->connect();
                $imapStats = Yii::app()->imap->getMessageBoxStatsDetailed();
                $this->assertEquals(1, $imapStats->Nmsgs);
            }
        }

        public static function tearDownAfterClass()
        {
            $imap = new ZurmoImap();
            $imap->imapHost        = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapHost'];
            $imap->imapUsername    = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapUsername'];
            $imap->imapPassword    = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapPassword'];
            $imap->imapPort        = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapPort'];
            $imap->imapSSL         = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapSSL'];
            $imap->imapFolder      = Yii::app()->params['emailTestAccounts']['userImapSettings']['imapFolder'];
            $imap->init();
            $imap->connect();
            $imap->deleteMessages(true);

            Yii::app()->emailHelper->sendEmailThroughTransport = self::$emailHelperSendEmailThroughTransport;
            parent::tearDownAfterClass();
        }

        /**
         * @depends testSendRealEmail
         */
        public function testTooManySendAttemptsResultingInFailure()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            if (EmailMessageTestHelper::isSetEmailAccountsTestConfiguration())
            {
                //add a message in the outbox_error folder.
                $emailMessage = EmailMessageTestHelper::createDraftSystemEmail('a test email 2', $super);
                $box                  = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
                $emailMessage->folder = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_OUTBOX_ERROR);
                $emailMessage->sendAttempts = 5;
                $emailMessage->save();

                $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
                $this->assertEquals(7, Yii::app()->emailHelper->getSentCount());
                Yii::app()->emailHelper->sendQueued();
                $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
                $this->assertEquals(7, Yii::app()->emailHelper->getSentCount());
                $this->assertTrue($emailMessage->folder->isSame(EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_OUTBOX_FAILURE)));
            }
            else
            {
                $this->markTestSkipped();
            }
        }

        public function testSendMessagePopulatesEmailAccountSettings()
        {
            $jane                      = User::getByUsername('jane');
            Yii::app()->user->userModel = $jane;
            $emailHelper = new EmailHelper;
            EmailMessageTestHelper::createEmailAccount($jane);
            $emailAccount = EmailAccount::getByUserAndName($jane);
            $emailAccount->useCustomOutboundSettings = true;
            $emailAccount->outboundType     = 'abc';
            $emailAccount->outboundPort     = 11;
            $emailAccount->outboundHost     = 'dumb.domain';
            $emailAccount->outboundUsername = 'jane';
            $emailAccount->outboundPassword = 'janepass';
            $emailAccount->outboundSecurity = 'ssl';
            $emailAccount->save();

            $emailMessage = EmailMessageTestHelper::createOutboxEmail(
                                $jane,
                                'Test email',
                                'Raw content',
                                'Html content',
                                'Zurmo',
                                Yii::app()->emailHelper->outboundUsername,
                                'John Doe',
                                Yii::app()->params['emailTestAccounts']['userImapSettings']['imapUsername']);
            $emailMessage->account = $emailAccount;
            $emailMessage->save();

            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            Yii::app()->emailHelper->sendQueued();

            $job = new ProcessOutboundEmailJob();
            $this->assertTrue($job->run());
            //Since user email account has invalid settings message is not sent
            $this->assertContains('Connection could not be established with host dumb.domain', strval($emailMessage->error));
            $this->assertEquals(1, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
        }

        public function testResolveAndGetDefaultFromAddress()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $content = Yii::app()->emailHelper->resolveAndGetDefaultFromAddress();
            $this->assertEquals('notification@zurmoalerts.com', $content);
        }

        public function testSetDefaultFromAddress()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $content = Yii::app()->emailHelper->resolveAndGetDefaultFromAddress();
            $this->assertEquals('notification@zurmoalerts.com', $content);
            Yii::app()->emailHelper->setDefaultFromAddress($content);
            $metadata = ZurmoModule::getMetadata();
            $this->assertEquals('notification@zurmoalerts.com', $metadata['global']['defaultFromAddress']);
        }

        public function testResolveAndGetDefaultTestToAddress()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $content = Yii::app()->emailHelper->resolveAndGetDefaultTestToAddress();
            $this->assertEquals('testJobEmail@zurmoalerts.com', $content);
        }

        public function testSetDefaultTestToAddress()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $content = Yii::app()->emailHelper->resolveAndGetDefaultTestToAddress();
            $this->assertEquals('testJobEmail@zurmoalerts.com', $content);
            Yii::app()->emailHelper->setDefaultTestToAddress($content);
            $metadata = ZurmoModule::getMetadata();
            $this->assertEquals('testJobEmail@zurmoalerts.com', $metadata['global']['defaultTestToAddress']);
        }
    }
?>