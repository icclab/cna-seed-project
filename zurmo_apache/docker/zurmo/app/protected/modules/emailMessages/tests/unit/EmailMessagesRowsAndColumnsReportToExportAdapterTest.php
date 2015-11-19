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
     * Test RowsAndColumnsReportToExportAdapter for EmailMessage
     */
    class EmailMessagesRowsAndColumnsReportToExportAdapterTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
            DisplayAttributeForReportForm::resetCount();
        }

        public function testExportRelationAttributes()
        {
            $report = new Report();
            $report->setType(Report::TYPE_ROWS_AND_COLUMNS);
            $report->setModuleClassName('EmailMessagesModule');
            $report->setFiltersStructure('');

            $emailMessage              = new EmailMessage();
            $emailMessage->owner       = Yii::app()->user->userModel;
            $emailMessage->subject     = 'A test email';
            $emailContent              = new EmailMessageContent();
            $emailContent->textContent = 'A test text message from Zurmo.';
            $emailContent->htmlContent = 'A test text message from Zurmo.';
            $emailMessage->content     = $emailContent;
            $sender                    = new EmailMessageSender();
            $sender->fromAddress       = 'super@zurmo.com';
            $sender->fromName          = 'super';
            $sender->personsOrAccounts->add(Yii::app()->user->userModel);
            $emailMessage->sender      = $sender;
            $recipient                 = new EmailMessageRecipient();
            $recipient->toAddress      = 'billy@joe.com';
            $recipient->toName         = 'Test Recipient';
            $recipient->type           = EmailMessageRecipient::TYPE_TO;
            $emailMessage->recipients->add($recipient);
            $box                       = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder      = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_SENT);
            $this->assertTrue($emailMessage->save());

            $displayAttribute1    = new DisplayAttributeForReportForm('EmailMessagesModule', 'EmailMessage',
                                            Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute1->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute1->attributeIndexOrDerivedType = 'sender___User__personsOrAccounts__Inferred___firstName';
            $report->addDisplayAttribute($displayAttribute1);

            $displayAttribute2    = new DisplayAttributeForReportForm('EmailMessagesModule', 'EmailMessage',
                                            Report::TYPE_ROWS_AND_COLUMNS);
            $displayAttribute2->setModelAliasUsingTableAliasName('relatedModel');
            $displayAttribute2->attributeIndexOrDerivedType = 'sender___Contact__personsOrAccounts__Inferred___firstName';
            $report->addDisplayAttribute($displayAttribute2);

            $dataProvider       = new RowsAndColumnsReportDataProvider($report);
            $adapter            = ReportToExportAdapterFactory::createReportToExportAdapter($report, $dataProvider);
            $compareHeaderData  = array('Sender >> Users >> First Name', 'Sender >> Contacts >> First Name');
            $compareRowData     = array(array('Clark', ''));
            $this->assertEquals($compareHeaderData, $adapter->getHeaderData());
            $this->assertEquals($compareRowData, $adapter->getData());
        }
    }
?>