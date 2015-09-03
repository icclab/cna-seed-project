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

    class UserImportTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function testSimpleUserImportWhereAllRowsSucceed()
        {
            Yii::app()->user->userModel = User::getByUsername('super');

            $users                      = User::getAll();
            $this->assertEquals(1, count($users));
            $import                                = new Import();
            $serializedData['importRulesType']     = 'User';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
            createTempTableByFileNameAndTableName('importAnalyzerTest.csv', $import->getTempTableName(), true,
                                                  Yii::getPathOfAlias('application.modules.users.tests.unit.files'));

            $this->assertEquals(11, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $defaultLanguage = Yii::app()->language;
            $localeIds       = ZurmoLocale::getSelectableLocaleIds();
            $defaultLocale   = $localeIds[0];

            $timezoneIdentifiers = DateTimeZone::listIdentifiers();
            $defaultTimeZone     = $timezoneIdentifiers[0];
            $defaultCurrency     = Yii::app()->currencyHelper->getActiveCurrencyForCurrentUser();
            $defaultCurrencyId   = $defaultCurrency->id;

            $mappingData = array(
                'column_0'  => array('attributeIndexOrDerivedType' => 'username',
                                         'type' => 'importColumn',
                                      'mappingRulesData' => array()),

                'column_1'  => array('attributeIndexOrDerivedType' => 'Password',
                                      'type' => 'importColumn',
                                      'mappingRulesData' => array(
                                          'PasswordDefaultValueModelAttributeMappingRuleForm' =>
                                          array('defaultValue' => null))),
                'column_2'  => array('attributeIndexOrDerivedType' => 'UserStatus',
                                      'type' => 'importColumn',
                                      'mappingRulesData' => array(
                                          'UserStatusDefaultValueMappingRuleForm' =>
                                          array('defaultValue' => UserStatusUtil::ACTIVE))),
                'column_3'  => ImportMappingUtil::makeStringColumnMappingData('firstName'),
                'column_4'  => ImportMappingUtil::makeStringColumnMappingData('lastName'),
                'column_5'  => array('attributeIndexOrDerivedType'          => 'role',
                                     'type'                                 => 'importColumn',
                                     'mappingRulesData' => array(
                                        'DefaultModelNameIdMappingRuleForm'    =>
                                        array('defaultModelId' => null),
                                        'RelatedModelValueTypeMappingRuleForm' =>
                                        array('type' => RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID))),
                'column_6'  => array('attributeIndexOrDerivedType' => 'language',
                                     'type' => 'importColumn',
                                     'mappingRulesData' => array(
                                     'DefaultValueModelAttributeMappingRuleForm' =>
                                     array('defaultValue' => $defaultLanguage))),
                'column_7'  => array('attributeIndexOrDerivedType' => 'locale',
                                    'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                    'DefaultValueModelAttributeMappingRuleForm' =>
                                    array('defaultValue' => $defaultLocale))),
                'column_8'  => array('attributeIndexOrDerivedType' => 'timeZone',
                                    'type' => 'importColumn',
                                    'mappingRulesData' => array(
                                    'DefaultValueModelAttributeMappingRuleForm' =>
                                    array('defaultValue' => $defaultTimeZone))),
                'column_9'  => array('attributeIndexOrDerivedType' => 'currency',
                                     'type' => 'importColumn',
                                     'mappingRulesData' => array(
                                     'DefaultValueModelAttributeMappingRuleForm' =>
                                     array('defaultValue' => $defaultCurrencyId))),
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('Users');
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

            //Confirm that 10 models where created.
            $this->assertEquals(11, User::getCount());
            $activeUser   = User::getByUsername('myusername7');
            $userStatus   = UserStatusUtil::makeByUser($activeUser);
            $this->assertTrue($userStatus->isActive());
            $this->assertEquals($defaultLanguage, $activeUser->language);
            $this->assertEquals($defaultLocale,   $activeUser->locale);
            $this->assertEquals($defaultTimeZone, $activeUser->timeZone);
            $this->assertEquals($defaultCurrency, $activeUser->currency);
            $inactiveUser = User::getByUsername('myusername8');
            $userStatus   = UserStatusUtil::makeByUser($inactiveUser);
            $this->assertEquals($defaultLanguage, $inactiveUser->language);
            $this->assertEquals($defaultLocale,   $inactiveUser->locale);
            $this->assertEquals($defaultTimeZone, $inactiveUser->timeZone);
            $this->assertEquals($defaultCurrency, $inactiveUser->currency);
            $this->assertFalse($userStatus->isActive());

            //Confirm 10 rows were processed as 'created'.
            $this->assertEquals(10, ImportDatabaseUtil::getCount($import->getTempTableName(), "status = "
                                                                 . ImportRowDataResultsUtil::CREATED));

            //Confirm that 0 rows were processed as 'updated'.
            $this->assertEquals(0, ImportDatabaseUtil::getCount($import->getTempTableName(),  "status = "
                                                                 . ImportRowDataResultsUtil::UPDATED));

            //Confirm 2 rows were processed as 'errors'.
            $this->assertEquals(0, ImportDatabaseUtil::getCount($import->getTempTableName(),  "status = "
                                                                 . ImportRowDataResultsUtil::ERROR));

            $beansWithErrors = ImportDatabaseUtil::getSubset($import->getTempTableName(),     "status = "
                                                                 . ImportRowDataResultsUtil::ERROR);
            $this->assertEquals(0, count($beansWithErrors));
        }

        /**
         * @depends testSimpleUserImportWhereAllRowsSucceed
         */
        public function testUserImportWithOptionalFields()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $users                      = User::getAll();
            $this->assertEquals(11, count($users));
            $import                                = new Import();
            $serializedData['importRulesType']     = 'User';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
                createTempTableByFileNameAndTableName('importAnalyzerWithOptionalFields.csv',
                                              $import->getTempTableName(), true,
                                              Yii::getPathOfAlias('application.modules.users.tests.unit.files'));

            $this->assertEquals(11, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $defaultLanguage = Yii::app()->language;
            $localeIds       = ZurmoLocale::getSelectableLocaleIds();
            $defaultLocale   = $localeIds[0];

            $timezoneIdentifiers = DateTimeZone::listIdentifiers();
            $defaultTimeZone     = $timezoneIdentifiers[0];
            $defaultCurrency     = Yii::app()->currencyHelper->getActiveCurrencyForCurrentUser();
            $defaultCurrencyId   = $defaultCurrency->id;

            $mappingData = array(
                'column_0'  => array('attributeIndexOrDerivedType' => 'username',
                                     'type' => 'importColumn',
                                     'mappingRulesData' => array()),
                'column_1'  => array('attributeIndexOrDerivedType' => 'Password',
                                     'type' => 'importColumn',
                                     'mappingRulesData' => array(
                                     'PasswordDefaultValueModelAttributeMappingRuleForm' =>
                                     array('defaultValue' => null))),
                'column_3'  => ImportMappingUtil::makeStringColumnMappingData('firstName'),
                'column_4'  => ImportMappingUtil::makeStringColumnMappingData('lastName'),
                'column_5'  => array('attributeIndexOrDerivedType' => 'language',
                                     'type' => 'extraColumn',
                                     'mappingRulesData' => array(
                                     'DefaultValueModelAttributeMappingRuleForm' =>
                                     array('defaultValue' => $defaultLanguage))),
                'column_6'  => array('attributeIndexOrDerivedType' => 'locale',
                                     'type' => 'extraColumn',
                                     'mappingRulesData' => array(
                                     'DefaultValueModelAttributeMappingRuleForm' =>
                                     array('defaultValue' => $defaultLocale))),
                'column_7'  => array('attributeIndexOrDerivedType' => 'timeZone',
                                     'type' => 'extraColumn',
                                     'mappingRulesData' => array(
                                     'DefaultValueModelAttributeMappingRuleForm' =>
                                     array('defaultValue' => $defaultTimeZone))),
                'column_8'  => array('attributeIndexOrDerivedType' => 'currency',
                                     'type' => 'extraColumn',
                                     'mappingRulesData' => array(
                                     'DefaultValueModelAttributeMappingRuleForm' =>
                                     array('defaultValue' => $defaultCurrencyId))),
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('Users');
            $page         = 0;
            $config       = array('pagination' => array('pageSize' => 50)); //This way all rows are processed.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);
            $dataProvider->getPagination()->setCurrentPage($page);
            $importResultsUtil = new ImportResultsUtil($import);
            $messageLogger     = new ImportMessageLogger();
            ImportUtil::importByDataProvider($dataProvider, $importRules, $mappingData, $importResultsUtil,
                                             new ExplicitReadWriteModelPermissions(), $messageLogger);
            $importResultsUtil->processStatusAndMessagesForEachRow();

            //Confirm that 10 new models are created.
            $users = User::getAll();
            $this->assertEquals(21, count($users));
            $user   = User::getByUsername('myusername11');
            $this->assertEquals($defaultLanguage, $user->language);
            $this->assertEquals($defaultLocale,   $user->locale);
            $this->assertEquals($defaultTimeZone, $user->timeZone);
            $this->assertEquals($defaultCurrency, $user->currency);
            //Confirm 10 rows were processed as 'created'.
            $this->assertEquals(10, ImportDatabaseUtil::getCount($import->getTempTableName(), "status = "
                                . ImportRowDataResultsUtil::CREATED));
            //Confirm that 0 rows were processed as 'updated'.
            $this->assertEquals(0, ImportDatabaseUtil::getCount($import->getTempTableName(),  "status = "
                                . ImportRowDataResultsUtil::UPDATED));
            //Confirm 2 rows were processed as 'errors'.
            $this->assertEquals(0, ImportDatabaseUtil::getCount($import->getTempTableName(),  "status = "
                                . ImportRowDataResultsUtil::ERROR));
            $beansWithErrors = ImportDatabaseUtil::getSubset($import->getTempTableName(),     "status = "
                                . ImportRowDataResultsUtil::ERROR);
            $this->assertEquals(0, count($beansWithErrors));
        }
    }
?>