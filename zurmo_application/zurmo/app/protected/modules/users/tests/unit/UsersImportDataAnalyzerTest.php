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

    class UsersImportDataAnalyzerTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $super = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $super;
        }

        public function testImportDataAnalysisResults()
        {
            $super                             = User::getByUsername('super');
            Yii::app()->user->userModel        = $super;
            $role = new Role();
            $role->name = 'role';
            $this->assertTrue($role->save());
            $import                            = new Import();
            $serializedData['importRulesType'] = 'Users';
            $import->serializedData            = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
            createTempTableByFileNameAndTableName('importAnalyzerTest.csv', $import->getTempTableName(), true,
                                                  Yii::getPathOfAlias('application.modules.users.tests.unit.files'));

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
                'column_5'  => array('attributeIndexOrDerivedType'          => 'role',
                                     'type'                                 => 'importColumn',
                                     'mappingRulesData'                     => array(
                                          'DefaultModelNameIdMappingRuleForm'    =>
                                          array('defaultModelId' => $role->id),
                                          'RelatedModelValueTypeMappingRuleForm' =>
                                          array('type' => RelatedModelValueTypeMappingRuleForm::ZURMO_MODEL_ID)))
                );
            $serializedData                = unserialize($import->serializedData);
            $serializedData['mappingData'] = $mappingData;
            $import->serializedData        = serialize($serializedData);
            $this->assertTrue($import->save());

            $importRules  = ImportRulesUtil::makeImportRulesByType('Users');
            $config       = array('pagination' => array('pageSize' => 15));
            //This test csv has a header row.
            $dataProvider = new ImportDataProvider($import->getTempTableName(), true, $config);

            //Run data analyzer
            $importDataAnalyzer = new ImportDataAnalyzer($importRules, $dataProvider, $mappingData,
                                                         array('column_0', 'column_1', 'column_2', 'column_5'));
            $importDataAnalyzer->analyzePage();
            $data = $dataProvider->getData();
            $this->assertEquals(10, count($data));

            $compareData = array();
            $compareData['column_5']   = array();
            $compareData['column_5'][] = 'Was not found and this row will be skipped during import.';
            $this->assertEquals($compareData, unserialize($data[0]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_SKIP, $data[0]->analysisStatus);

            $this->assertNull($data[1]->serializedAnalysisMessages);
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[1]->analysisStatus);

            $this->assertNull($data[2]->serializedAnalysisMessages);
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[2]->analysisStatus);

            $this->assertNull($data[3]->serializedAnalysisMessages);
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[3]->analysisStatus);

            $compareData = array();
            $compareData['column_0']   = array();
            $compareData['column_0'][] = 'Is too long. Maximum length is 64. This value will truncated upon import.';
            $this->assertEquals($compareData, unserialize($data[4]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_WARN, $data[4]->analysisStatus);

            $this->assertNull($data[5]->serializedAnalysisMessages);
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[5]->analysisStatus);

            $this->assertNull($data[6]->serializedAnalysisMessages);
            $this->assertEquals(ImportDataAnalyzer::STATUS_CLEAN, $data[6]->analysisStatus);

            $compareData = array();
            $compareData['column_2']   = array();
            $compareData['column_2'][] = 'Status value is invalid. This status will be set to active upon import.';
            $this->assertEquals($compareData, unserialize($data[7]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_WARN, $data[7]->analysisStatus);

            $compareData = array();
            $compareData['column_1']   = array();
            $compareData['column_1'][] = 'Is too long. Maximum length is 60. This value will truncated upon import.';
            $this->assertEquals($compareData, unserialize($data[8]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_WARN, $data[8]->analysisStatus);

            $compareData = array();
            $compareData['column_2']   = array();
            $compareData['column_2'][] = 'Status value is invalid. This status will be set to active upon import.';
            $this->assertEquals($compareData, unserialize($data[9]->serializedAnalysisMessages));
            $this->assertEquals(ImportDataAnalyzer::STATUS_WARN, $data[9]->analysisStatus);
        }
    }
?>
