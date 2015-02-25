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

    class ProjectImportTest extends ImportBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::import('application.core.data.*');
            Yii::import('application.modules.projects.data.*');
        }

        public function testSimpleUserImportWhereAllRowsSucceed()
        {
            Yii::app()->user->userModel            = User::getByUsername('super');
            $import                                = new Import();
            $serializedData['importRulesType']     = 'Projects';
            $serializedData['firstRowIsHeaderRow'] = true;
            $import->serializedData                = serialize($serializedData);
            $this->assertTrue($import->save());

            ImportTestHelper::
            createTempTableByFileNameAndTableName('projectsSample.csv', $import->getTempTableName(), true,
                                                  Yii::getPathOfAlias('application.modules.projects.tests.unit.files'));

            $this->assertEquals(3, ImportDatabaseUtil::getCount($import->getTempTableName())); // includes header rows.

            $ownerColumnMappingData         = array('attributeIndexOrDerivedType' => 'owner',
                                               'type' => 'importColumn', 'mappingRulesData' => array(
                                               'DefaultModelNameIdMappingRuleForm' =>
                                               array('defaultModelId' => null),
                                               'UserValueTypeModelAttributeMappingRuleForm' =>
                                               array('type' =>
                                               UserValueTypeModelAttributeMappingRuleForm::ZURMO_USERNAME)));

            $mappingData = array(
                'column_0'  => $ownerColumnMappingData,
                'column_1'  => ImportMappingUtil::makeStringColumnMappingData      ('name'),
                'column_2'  => ImportMappingUtil::makeTextAreaColumnMappingData    ('description'),
                'column_3'  => ImportMappingUtil::makeDropDownColumnMappingData    ('status'),
            );

            $importRules  = ImportRulesUtil::makeImportRulesByType('Projects');
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
            //Confirm that 2 models where created.
            $projects = Project::getAll();
            $this->assertEquals(2, count($projects));

            $projects = Project::getByName('My first project');
            $this->assertEquals(1,                         count($projects[0]));
            $this->assertEquals('super',                   $projects[0]->owner->username);
            $this->assertEquals('My first project',        $projects[0]->name);
            $this->assertEquals(2,                         $projects[0]->status);
            //todo ask Jason for it
            //$this->assertEquals('My first project Desc',   $projects[0]->description);
            $projects[0]->delete();

            $projects = Project::getByName('My second project');
            $this->assertEquals(1,                         count($projects[0]));
            $this->assertEquals('super',                   $projects[0]->owner->username);
            $this->assertEquals('My second project',       $projects[0]->name);
            $this->assertEquals(1,                         $projects[0]->status);
            //$this->assertEquals('My second project Desc',  $projects[0]->description);

            $projects[0]->delete();

            //Confirm that 2 rows were processed as 'updated'.
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