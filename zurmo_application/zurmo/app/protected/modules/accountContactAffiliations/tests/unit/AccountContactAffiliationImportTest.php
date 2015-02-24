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

    class AccountContactAffiliationImportTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::import('application.core.data.*');
            Yii::import('application.modules.accounts.data.*');
            $defaultDataMaker = new AccountsDefaultDataMaker();
            $defaultDataMaker->make();
            Yii::import('application.modules.contacts.data.*');
            $defaultDataMaker = new ContactsDefaultDataMaker();
            $defaultDataMaker->make();
            Yii::import('application.modules.accountContactAffiliations.data.*');
            $defaultDataMaker = new AccountContactAffiliationsDefaultDataMaker();
            $defaultDataMaker->make();
            Currency::getAll(); //forces base currency to be created.
            ContactsModule::loadStartingData();
        }

        public function testSimpleUserImportWhereAllRowsSucceed()
        {
            Yii::app()->user->userModel            = User::getByUsername('super');
            $account                               = AccountTestHelper::
                                                     createAccountByNameForOwner('Account',
                                                                                 Yii::app()->user->userModel);
            $contact                               = ContactTestHelper::
                                                     createContactByNameForOwner('Contact',
                                                                                 Yii::app()->user->userModel);
            $accountContactAffiliations            = AccountContactAffiliation::getAll();
            $this->assertEquals(0, count($accountContactAffiliations));
            $import                                = new Import();
            $serializedData['importRulesType']     = 'AccountContactAffiliations';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
            createTempTableByFileNameAndTableName('importTest.csv', $import->getTempTableName(), true,
                                                  Yii::getPathOfAlias('application.modules.accountContactAffiliations.tests.unit.files'));

            //update the ids of the account column to match the parent account.
            ZurmoRedBean::exec("update " . $import->getTempTableName() . " set column_2 = " .
                               $account->id . " where id != 1 limit 4");
            ZurmoRedBean::exec("update " . $import->getTempTableName() . " set column_1 = " .
                               $contact->id . " where id != 1 limit 4");

            $this->assertEquals(4, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $mappingData = array(
                'column_0'  => ImportMappingUtil::makeDropDownColumnMappingData     ('role'),
                'column_1'  => ImportMappingUtil::makeHasOneColumnMappingData       ('contact'),
                'column_2'  => ImportMappingUtil::makeHasOneColumnMappingData       ('account'),
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('AccountContactAffiliations');
            $page         = 0;
            $config       = array('pagination' => array('pageSize' => 50)); //This way all rows are processed.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);
            $dataProvider->getPagination()->setCurrentPage($page);
            $importResultsUtil = new ImportResultsUtil($import);
            $messageLogger     = new ImportMessageLogger();
            ImportUtil::importByDataProvider($dataProvider,
                                             $importRules,
                                             $mappingData,
                                             $importResultsUtil,
                                             new ExplicitReadWriteModelPermissions(),
                                             $messageLogger);
            $importResultsUtil->processStatusAndMessagesForEachRow();

            //Confirm that 3 models where created.
            $accountContactAffiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(3, count($accountContactAffiliations));

            $this->assertEquals('0',    $accountContactAffiliations[0]->primary);
            $this->assertTrue($accountContactAffiliations[0]->account->isSame($account));
            $this->assertTrue($accountContactAffiliations[0]->contact->isSame($contact));
            $this->assertEquals('',     $accountContactAffiliations[0]->role->value);

            $this->assertEquals('0',    $accountContactAffiliations[1]->primary);
            $this->assertTrue($accountContactAffiliations[1]->account->isSame($account));
            $this->assertTrue($accountContactAffiliations[1]->contact->isSame($contact));
            $this->assertEquals('Support',  $accountContactAffiliations[1]->role->value);

            $this->assertEquals('0',         $accountContactAffiliations[2]->primary);
            $this->assertTrue($accountContactAffiliations[2]->account->isSame($account));
            $this->assertTrue($accountContactAffiliations[2]->contact->isSame($contact));
            $this->assertEquals('Technical', $accountContactAffiliations[2]->role->value);

            //Confirm 3 rows were processed as 'created'.
            $this->assertEquals(3, ImportDatabaseUtil::getCount($import->getTempTableName(), "status = "
                                                                 . ImportRowDataResultsUtil::CREATED));

            //Confirm that 0 rows were processed as 'updated'.
            $this->assertEquals(0, ImportDatabaseUtil::getCount($import->getTempTableName(),  "status = "
                                                                 . ImportRowDataResultsUtil::UPDATED));
        }
    }
?>