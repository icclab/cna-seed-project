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
     * Special class to isolate the autoBuildDatabase method and test that the rows are the correct
     * count before and after running this method.  AutoBuildDatabase is used both on installation
     * but also during an upgrade or manually  to update the database schema based on any detected
     * changes.
     */
    class AutoBuildDatabaseTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::app()->gameHelper->muteScoringModelsOnSave();
        }

        public static function tearDownAfterClass()
        {
            Yii::app()->gameHelper->unmuteScoringModelsOnSave();
            parent::tearDownAfterClass();
        }

        public function testAutoBuildDatabase()
        {
            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $messageLogger              = new MessageLogger();
            $beforeRowCount             = DatabaseCompatibilityUtil::getTableRowsCountTotal();
            InstallUtil::autoBuildDatabase($messageLogger, true);
            E::deleteAll(); // because if we are running All, C, D and E get autobuild and 2 validators in E cause E to have a record which gets deleted on tearDown.
            $afterRowCount              = DatabaseCompatibilityUtil::getTableRowsCountTotal();
            $this->assertEquals($beforeRowCount, $afterRowCount);
        }

        /**
         * @depends testAutoBuildDatabase
         */
        public function testColumnType()
        {
            $rootModels = array();
            foreach (Module::getModuleObjects() as $module)
            {
                $moduleAndDependenciesRootModelNames    = $module->getRootModelNamesIncludingDependencies();
                $rootModels                             = array_merge(  $rootModels,
                                                                    array_diff($moduleAndDependenciesRootModelNames,
                                                                    $rootModels));
            }

            foreach ($rootModels as $model)
            {
                $meta = $model::getDefaultMetadata();
                if (isset($meta[$model]['rules']))
                {
                    $tableName      = $model::getTableName();
                    $columns = ZurmoRedBean::$writer->getColumns($tableName);
                    foreach ($meta[$model]['rules'] as $rule)
                    {
                        if (is_array($rule) && count($rule) >= 3)
                        {
                            $attributeName       = $rule[0];
                            $validatorName       = $rule[1];
                            $validatorParameters = array_slice($rule, 2);
                            switch ($validatorName)
                            {
                                case 'type':
                                    if (isset($validatorParameters['type']))
                                    {
                                        $type           = $validatorParameters['type'];
                                        $field          = strtolower($attributeName);
                                        $columnType = false;
                                        if (isset($columns[$field]))
                                        {
                                            $columnType         = $columns[$field];
                                        }
                                        $compareType    = null;
                                        $compareTypes = $this->getDatabaseTypesByType($type);
                                        // Remove brackets from database type
                                        $bracketPosition = stripos($columnType, '(');
                                        if ($bracketPosition !== false)
                                        {
                                            $columnType = substr($columnType, 0, $bracketPosition);
                                        }

                                        $databaseColumnType = strtoupper(trim($columnType));
                                        $compareTypeString  = implode(',', $compareTypes); // Not Coding Standard
                                        if (!in_array($databaseColumnType, $compareTypes))
                                        {
                                            $this->fail("Actual database type {$databaseColumnType} not in expected types: {$compareTypeString}.");
                                        }
                                    }
                                    break;
                            }
                        }
                    }
                }
            }
        }

        /**
         * @depends testAutoBuildDatabase
         */
        public function testAutoBuildUpgrade()
        {
            // adding Text Field
            $metadata = Account::getMetadata();
            $metadata['Account']['members'][] = 'newField';
            $rules = array('newField', 'type', 'type' => 'string');
            $metadata['Account']['rules'][] = $rules;

            $metadata['Account']['members'][] = 'string128';
            $rules = array('string128', 'type', 'type' => 'string');
            $metadata['Account']['rules'][] = $rules;
            $rules = array('string128', 'length', 'min' => 1, 'max' => 128);
            $metadata['Account']['rules'][] = $rules;

            $metadata['Account']['members'][] = 'string555';
            $rules = array('string555', 'type', 'type' => 'string');
            $metadata['Account']['rules'][] = $rules;
            $rules = array('string555', 'length', 'min' => 1, 'max' => 555);
            $metadata['Account']['rules'][] = $rules;

            $metadata['Account']['members'][] = 'string100000';
            $rules = array('string100000', 'type', 'type' => 'string');
            $metadata['Account']['rules'][] = $rules;
            $rules = array('string100000', 'length', 'min' => 1, 'max' => 100000);
            $metadata['Account']['rules'][] = $rules;

            $metadata['Account']['members'][] = 'textField';
            $rules = array('textField', 'type', 'type' => 'text');
            $metadata['Account']['rules'][] = $rules;

            $metadata['Account']['members'][] = 'longTextField';
            $rules = array('longTextField', 'type', 'type' => 'longtext');
            $metadata['Account']['rules'][] = $rules;

            $metadata['Account']['members'][] = 'dateField';
            $rules = array('dateField', 'type', 'type' => 'date');
            $metadata['Account']['rules'][] = $rules;

            $metadata['Account']['members'][] = 'booleanField';
            $rules = array('booleanField', 'boolean');
            $metadata['Account']['rules'][] = $rules;

            $metadata['Account']['members'][] = 'integerField';
            $rules = array('integerField', 'type', 'type' => 'integer');
            $metadata['Account']['rules'][] = $rules;

            $metadata['Account']['members'][] = 'dateTimeField';
            $rules = array('dateTimeField', 'type', 'type' => 'datetime');
            $metadata['Account']['rules'][] = $rules;

            $metadata['Account']['members'][] = 'urlField';
            $rules = array('urlField', 'url');
            $metadata['Account']['rules'][] = $rules;

            $metadata['Account']['members'][] = 'floatField';
            $rules = array('floatField', 'type', 'type' => 'float');
            $metadata['Account']['rules'][] = $rules;

            $metadata['Account']['members'][] = 'blobField';
            $rules = array('blobField', 'type', 'type' => 'blob');
            $metadata['Account']['rules'][] = $rules;

            $metadata['Account']['members'][] = 'longBlobField';
            $rules = array('longBlobField', 'type', 'type' => 'longblob');
            $metadata['Account']['rules'][] = $rules;

            Account::setMetadata($metadata);

            $super                      = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $messageLogger              = new MessageLogger();
            $beforeRowCount             = DatabaseCompatibilityUtil::getTableRowsCountTotal();
            InstallUtil::autoBuildDatabase($messageLogger, true);

            $afterRowCount              = DatabaseCompatibilityUtil::getTableRowsCountTotal();
            $this->assertEquals($beforeRowCount, $afterRowCount);

            //Check Account fields
            $tableName = Account::getTableName();
            $columns   = ZurmoRedBean::$writer->getColumns($tableName);
            $unsigned   = '';
            if (!RedBeanModelMemberRulesToColumnAdapter::ASSUME_SIGNED)
            {
                $unsigned   = ' unsigned';
            }

            $this->assertEquals('text',                     $columns['newfield']);
            $this->assertEquals('varchar(128)',             $columns['string128']);
            $this->assertEquals('text',                     $columns['string555']);
            $this->assertEquals('longtext',                 $columns['string100000']);
            $this->assertEquals('text',                     $columns['textfield']);
            $this->assertEquals('date',                     $columns['datefield']);
            $this->assertEquals('tinyint(1) unsigned',      $columns['booleanfield']);
            $this->assertEquals('int(11)' . $unsigned,         $columns['integerfield']);
            $this->assertEquals('datetime',                 $columns['datetimefield']);
            $this->assertEquals('varchar(255)',             $columns['urlfield']);
            $this->assertEquals('double',                   $columns['floatfield']);
            $this->assertEquals('longtext',                 $columns['longtextfield']);
            $this->assertEquals('blob',                     $columns['blobfield']);
            $this->assertEquals('longblob',                 $columns['longblobfield']);

            $account = new Account();
            $account->name  = 'Test Name';
            $account->owner = $super;
            $randomString = str_repeat("Aa", 64);
            $account->string128 = $randomString;
            $this->assertTrue($account->save());

            $metadata = Account::getMetadata();

            foreach ($metadata['Account']['rules'] as $key => $rule)
            {
                if ($rule[0] == 'string128' && $rule[1] == 'length')
                {
                    $metadata['Account']['rules'][$key]['max'] = 64;
                }
            }
            Account::setMetadata($metadata);
            InstallUtil::autoBuildDatabase($messageLogger, true);

            RedBeanModel::forgetAll();
            $modifiedAccount = Account::getById($account->id);

            // autobuild should not decrease length or display width of an existing column
            $this->assertEquals($randomString, $modifiedAccount->string128);
            $this->assertEquals(128, strlen($modifiedAccount->string128));

            //Check Account fields
            $tableName = Account::getTableName();
            $columns   = ZurmoRedBean::$writer->getColumns($tableName);
            $this->assertEquals('varchar(128)',     $columns['string128']);
        }

        /**
         * Based on type validator from models, get database column type
         * @param string $type
         * @return array
         */
        protected function getDatabaseTypesByType($type)
        {
            $typeArray = array(
                'string'    => array('VARCHAR', 'TEXT', 'LONGTEXT'),
                'text'      => array('TEXT'),
                'longtext'  => array('LONGTEXT'),
                'integer'   => array('TINYINT', 'INT', 'INTEGER', 'BIGINT'),
                'float'     => array('FLOAT', 'DOUBLE'),
                'year'      => array('YEAR'),
                'date'      => array('DATE'),
                'datetime'  => array('DATETIME'),
                'blob'      => array('BLOB'),
                'longblob'  => array('LONG_BLOB'),
                'boolean'   => array('TINYINT'),
            );

            $databaseTypes = array();
            if (isset($typeArray[$type]))
            {
                $databaseTypes = $typeArray[$type];
            }
            return $databaseTypes;
        }
    }
?>
