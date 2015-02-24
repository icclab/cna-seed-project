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

    class CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtilTest extends BaseTest
    {
        // TODO: @Shoaibi: High: Add coverage for more validation exception
        protected static $messageLogger;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            static::$messageLogger = new MessageLogger();
        }

        /**
         * @expectedException CException
         * @expectedMessage Invalid Schema definition received for . More than one table definitions defined in schema
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionWithEmptySchemaDefinition()
        {
            $schema = array();
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
        }

        /**
         * @depends testGenerateOrUpdateTableBySchemaDefinitionWithEmptySchemaDefinition
         * @expectedException CException
         * @expectedMessage Invalid Schema definition received for 0. Table name: 0 is not string
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionWithNoTableName()
        {
            $schema     = array(array(
                'columns' => array(
                    array(
                        'name' => 'hash',
                        'type' => 'VARCHAR(32)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                ),
                'indexes' => array(),
                )
            );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
        }

        /**
         * @depends testGenerateOrUpdateTableBySchemaDefinitionWithNoTableName
         * @expectedException CException
         * @expectedMessage Invalid Schema definition received for tableName. More than one table definitions defined in schema
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionWithTwoValuesInSchema()
        {
            $schema     = array('tablename' =>  array(
                                    'columns' => array(
                                        array(
                                            'name' => 'hash',
                                            'type' => 'VARCHAR(32)',
                                            'unsigned' => null,
                                            'notNull' => 'NULL', // Not Coding Standard
                                            'collation' => 'COLLATE utf8_unicode_ci',
                                            'default' => 'DEFAULT NULL', // Not Coding Standard
                                        ),
                                    ),
                                    'indexes' => array(),
                                ),
                                'tablename2' => array(
                                    'columns' => array(
                                        array(
                                            'name' => 'hash',
                                            'type' => 'VARCHAR(32)',
                                            'unsigned' => null,
                                            'notNull' => 'NULL', // Not Coding Standard
                                            'collation' => 'COLLATE utf8_unicode_ci',
                                            'default' => 'DEFAULT NULL', // Not Coding Standard
                                        ),
                                    ),
                                    'indexes' => array(),
                                )

                            );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
        }

        /**
         * @depends testGenerateOrUpdateTableBySchemaDefinitionWithTwoValuesInSchema
         * @expectedException CException
         * @expectedMessage Invalid Schema definition received for tableName. Table schema should always contain 2 sub-definitions
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionWithNoColumnsKey()
        {
            $schema     = array('tablename' =>  array(
                                        'indexes' => array(),
                                    ),
                                );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
        }

        /**
         * @depends testGenerateOrUpdateTableBySchemaDefinitionWithNoColumnsKey
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionWithNoColumns()
        {
            $schema     = array('tablewithnocolumns' =>  array(
                                        'columns'   => array(),
                                        'indexes'   => array(),
                                    ),
                                    );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
            $processedTables    = CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::resolveProcessedTables();
            $this->assertNotEmpty($processedTables);
            $this->assertCount(1, $processedTables);
            $this->assertEquals('tablewithnocolumns', $processedTables[0]);
        }

        /**
         * @depends testGenerateOrUpdateTableBySchemaDefinitionWithNoColumns
         * @expectedException CException
         * @expectedMessage Invalid Schema definition received for tableName. Table schema should always contain 2 sub-definitions
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionWithNoIndexesKey()
        {
            $schema     = array('tablename' =>  array(
                'columns' => array(
                    array(
                        'name' => 'hash',
                        'type' => 'VARCHAR(32)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                ),
                ),
            );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
        }

        /**
         * @depends testGenerateOrUpdateTableBySchemaDefinitionWithNoIndexesKey
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionWithNoIndexes()
        {
            $schema     = array('tablename1' => array(
                'columns' => array(
                    array(
                        'name' => 'hash',
                        'type' => 'VARCHAR(32)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                ),
                'indexes' => array(),
            ),
            );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
            $processedTables    = CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::resolveProcessedTables();
            $this->assertNotEmpty($processedTables);
            $this->assertCount(2, $processedTables);
            $this->assertEquals('tablewithnocolumns', $processedTables[0]);
            $this->assertEquals('tablename1', $processedTables[1]);
        }

        /**
         * @depends testGenerateOrUpdateTableBySchemaDefinitionWithNoIndexes
         * @expectedException CException
         * @expectedMessage Invalid Schema definition received for tableName2. Column: hash definition should always have 6 clauses
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionWithColumnsMissingKeys()
        {
            $schema     = array('tablename2' => array(
                'columns' => array(
                    array(
                        'name' => 'hash',
                        'type' => 'VARCHAR(32)',
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                ),
                'indexes' => array(),
            ),
            );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
        }

        /**
         * @depends testGenerateOrUpdateTableBySchemaDefinitionWithColumnsMissingKeys
         * @expectedException CException
         * @expectedMessage Invalid Schema definition received for tableName2. Column: hash missing notNull clause
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionWithColumnsHavingExtraKeys()
        {
            $schema     = array('tablename2' => array(
                'columns' => array(
                    array(
                        'name' => 'hash',
                        'type' => 'VARCHAR(32)',
                        'unsigned' => null,
                        'NOTNULLHERE' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                ),
                'indexes' => array(),
            ),
            );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                            static::$messageLogger);
        }

        /**
         * @depends testGenerateOrUpdateTableBySchemaDefinitionWithColumnsHavingExtraKeys
         * @expectedException CException
         * @expectedMessage Invalid Schema definition received for tableName2. Index Name: 0 is not a string
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionWithIndexesHavingIntegerKeys()
        {
            $schema     = array('tablename2' => array(
                'columns' => array(
                    array(
                        'name' => 'hash',
                        'type' => 'VARCHAR(32)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                ),
                'indexes' => array(
                                array(
                                    'columns' => array('hash'),
                                    'unique' => false
                                )
                ),
            ),
            );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
        }

        /**
         * @depends testGenerateOrUpdateTableBySchemaDefinitionWithIndexesHavingIntegerKeys
         * @expectedException CException
         * @expectedMessage Invalid Schema definition received for tableName2. Index: indexName does not have 2 clauses
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionWithIndexesHavingMoreThanTwoItems()
        {
            $schema     = array('tablename2' => array(
                'columns' => array(
                    array(
                        'name' => 'hash',
                        'type' => 'VARCHAR(32)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                ),
                'indexes' => array(
                    'indexName' => array(
                        'columns' => array('hash'),
                        'unique' => false,
                        'third' => 1,
                    )
                ),
            ),
            );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
        }

        /**
         * @depends testGenerateOrUpdateTableBySchemaDefinitionWithIndexesHavingMoreThanTwoItems
         * @expectedException CException
         * @expectedMessage Invalid Schema definition received for tableName2. Index: indexName does not have indexed column names
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionWithIndexesHavingNoColumnsKey()
        {
            $schema     = array('tablename2' => array(
                'columns' => array(
                    array(
                        'name' => 'hash',
                        'type' => 'VARCHAR(32)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                ),
                'indexes' => array(
                    'indexName' => array(
                        'unique' => false,
                        'third' => 1,
                    )
                ),
            ),
            );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
        }

        /**
         * @depends testGenerateOrUpdateTableBySchemaDefinitionWithIndexesHavingNoColumnsKey
         * @expectedException CException
         * @expectedMessage Invalid Schema definition received for tableName2. Index: indexName does not have index uniqueness clause defined
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionWithUIndexesHavingNoUniqueKey()
        {
            $schema     = array('tablename2' => array(
                'columns' => array(
                    array(
                        'name' => 'hash',
                        'type' => 'VARCHAR(32)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                ),
                'indexes' => array(
                    'indexName' => array(
                        'columns' => array(),
                        'third' => 1,
                    )
                ),
            ),
            );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
        }

        /**
         * @depends testGenerateOrUpdateTableBySchemaDefinitionWithUIndexesHavingNoUniqueKey
         * @expectedException CException
         * @expectedMessage Invalid Schema definition received for tableName2. Index: indexName column definition is not an array
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionWithIndexColumnKeyNotBeingArray()
        {
            $schema     = array('tablename2' => array(
                'columns' => array(
                    array(
                        'name' => 'hash',
                        'type' => 'VARCHAR(32)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                ),
                'indexes' => array(
                    'indexName' => array(
                        'columns' => 'hash',
                        'unique' => true,
                    )
                ),
            ),
            );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
        }

        /**
         * @depends testGenerateOrUpdateTableBySchemaDefinitionWithIndexColumnKeyNotBeingArray
         * @expectedException CException
         * @expectedMessage Invalid Schema definition received for tableName2. Index: indexName column: hasha does not exist in current schema definition provided
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionWithIndexColumnNotFound()
        {
            $schema     = array('tablename2' => array(
                'columns' => array(
                    array(
                        'name' => 'hash',
                        'type' => 'VARCHAR(32)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                ),
                'indexes' => array(
                    'indexName' => array(
                        'columns'   => array('hasha'),
                        'unique' => false,
                    )
                ),
            ),
            );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
        }

        /**
         * @depends testGenerateOrUpdateTableBySchemaDefinitionWithIndexColumnNotFound
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionWithValidSchema()
        {
            $schema     = array('tablename3' => array(
                'columns' => array(
                    array(
                        'name' => 'hash',
                        'type' => 'VARCHAR(32)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'language',
                        'type' => 'VARCHAR(10)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'locale',
                        'type' => 'VARCHAR(10)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'timezone',
                        'type' => 'VARCHAR(64)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'username',
                        'type' => 'VARCHAR(64)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'serializedavatardata',
                        'type' => 'TEXT',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'isactive',
                        'type' => 'TINYINT(1) UNSIGNED',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => null,
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'lastlogindatetime',
                        'type' => 'DATETIME',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => null,
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'permitable_id',
                        'type' => 'INT(11)',
                        'unsigned' => 'UNSIGNED',
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => null,
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'person_id',
                        'type' => 'INT(11)',
                        'unsigned' => 'UNSIGNED',
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => null,
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'currency_id',
                        'type' => 'INT(11)',
                        'unsigned' => 'UNSIGNED',
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => null,
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'manager__user_id',
                        'type' => 'INT(11)',
                        'unsigned' => 'UNSIGNED',
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => null,
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'role_id',
                        'type' => 'INT(11)',
                        'unsigned' => 'UNSIGNED',
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => null,
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                ),
                'indexes' => array(
                    'unique_username_Index' => array(
                        'columns' => array('username'),
                        'unique' => true
                    ),
                    'user_role_id_Index' => array(
                        'columns'       => array('username', 'role_id'),
                        'unique'        => false,
                    ),
                )
            )
            );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
            $processedTables    = CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::resolveProcessedTables();
            $this->assertNotEmpty($processedTables);
            $this->assertCount(3, $processedTables);
            $this->assertEquals('tablewithnocolumns', $processedTables[0]);
            $this->assertEquals('tablename1', $processedTables[1]);
            $this->assertEquals('tablename3', $processedTables[2]);
        }

        /**
         * @depends testGenerateOrUpdateTableBySchemaDefinitionWithValidSchema
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionWithValidButChangedSchemaForExistingTableWithIsFreshInstall()
        {
            Yii::app()->params['isFreshInstall'] = true;
            $schema     = array('tablename3' => array(
                'columns' => array(
                    array(
                        'name' => 'hash',
                        'type' => 'VARCHAR(64)',
                        'unsigned' => null,
                        'notNull' => 'NOT NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT "bacdefghi"',
                    ),
                    array(
                        'name' => 'newlanguage',
                        'type' => 'VARCHAR(100)',
                        'unsigned' => null,
                        'notNull' => 'NOT NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT "1234567"',
                    ),
                    array(
                        'name' => 'locale',
                        'type' => 'VARCHAR(100)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'timezone',
                        'type' => 'VARCHAR(32)',
                        'unsigned' => null,
                        'notNull' => 'NOT NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_general_ci',
                        'default' => 'DEFAULT "abc/def"',
                    ),
                    array(
                        'name' => 'username',
                        'type' => 'VARCHAR(10)',
                        'unsigned' => null,
                        'notNull' => 'NOT NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT "superman"',
                    ),
                    array(
                        'name' => 'serializedavatardata',
                        'type' => 'TEXT',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'isactive',
                        'type' => 'TINYINT(1) UNSIGNED',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => null,
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'permitable_id',
                        'type' => 'INT(11)',
                        'unsigned' => 'UNSIGNED',
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => null,
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'role_id',
                        'type' => 'INT(11)',
                        'unsigned' => 'UNSIGNED',
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => null,
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                ),
                'indexes' => array(
                    'unique_username_Index' => array(
                        'columns' => array('username'),
                        'unique' => true
                    ),
                    'unique_isactive_Index' => array(
                        'columns' => array('isactive'),
                        'unique' => true
                    ),
                    'role_id_Index' => array(
                        'columns'   => array('role_id'),
                        'unique'    => false,
                    ),
                    'user_role_id_Index' => array(
                        'columns'       => array('username', 'role_id'),
                        'unique'        => false,
                    ),
                )
            )
            );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
            $processedTables    = CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::resolveProcessedTables();
            $this->assertNotEmpty($processedTables);
            $this->assertCount(3, $processedTables);
            $this->assertEquals('tablewithnocolumns', $processedTables[0]);
            $this->assertEquals('tablename1', $processedTables[1]);
            $this->assertEquals('tablename3', $processedTables[2]);
            Yii::app()->params['isFreshInstall'] = false;
        }

        /**
         * @depends testGenerateOrUpdateTableBySchemaDefinitionWithValidButChangedSchemaForExistingTableWithIsFreshInstall
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionWithValidButChangedSchemaForExistingTableWithNoIsFreshInstall()
        {
            Yii::app()->params['isFreshInstall'] = false;
            $schema     = array('tablename3' => array(
                'columns' => array(
                    array(
                        'name' => 'hash',
                        'type' => 'TEXT',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'language',
                        'type' => 'VARCHAR(100)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'newlocale',
                        'type' => 'VARCHAR(10)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'timezone',
                        'type' => 'VARCHAR(64)',
                        'unsigned' => null,
                        'notNull' => 'NOT NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT "America/Chicago"',
                    ),
                    array(
                        'name' => 'username',
                        'type' => 'VARCHAR(64)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT "superman"',
                    ),
                    array(
                        'name' => 'serializedavatardata',
                        'type' => 'VARCHAR(255)',
                        'unsigned' => null,
                        'notNull' => 'NOT NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT "abcdef"',
                    ),
                    array(
                        'name' => 'role_id',
                        'type' => 'INT(11)',
                        'unsigned' => 'UNSIGNED',
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => null,
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                ),
                'indexes' => array(
                    'unique_username_Index' => array(
                        'columns' => array('username'),
                        'unique' => true
                    ),
                    'unique_language_Index' => array(
                        'columns' => array('language'),
                        'unique' => true
                    ),
                    'role_id_Index' => array(
                        'columns'   => array('role_id'),
                        'unique'    => false,
                    ),
                    'new_username_Index' => array(
                        'columns' => array('username'),
                        'unique' => false
                    ),
                    'user_role_id_Index' => array(
                        'columns'       => array('role_id', 'username'),
                        'unique'        => false,
                    ),
                )
            )
            );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
            $processedTables    = CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::resolveProcessedTables();
            $this->assertNotEmpty($processedTables);
            $this->assertCount(3, $processedTables);
            $this->assertEquals('tablewithnocolumns', $processedTables[0]);
            $this->assertEquals('tablename1', $processedTables[1]);
            $this->assertEquals('tablename3', $processedTables[2]);
            // we do not need try-catch here as if there was an exception it would have been thrown already.
            $existingFields     = ZurmoRedBean::$writer->getColumnsWithDetails('tablename3');
            $this->assertNotEmpty($existingFields);
            $this->assertCount(15, $existingFields);
            $this->assertEquals('text', $existingFields['hash']['Type']);
            $this->assertEquals('varchar(100)', $existingFields['language']['Type']);
            $this->assertArrayHasKey('newlocale', $existingFields);
            $this->assertEquals('NO', $existingFields['timezone']['Null']); // Not Coding Standard
            $this->assertEquals('America/Chicago', $existingFields['timezone']['Default']);
            $this->assertEquals('superman', $existingFields['username']['Default']);
            $this->assertEquals('varchar(255)', $existingFields['serializedavatardata']['Type']);
            $this->assertEquals('abcdef', $existingFields['serializedavatardata']['Default']);
            $existingIndexes    = ZurmoRedBean::$writer->getIndexes('tablename3');
            $this->assertCount(6, $existingIndexes);
            $this->assertArrayHasKey('unique_language_Index', $existingIndexes);
            $this->assertArrayHasKey('role_id_Index', $existingIndexes);
            $this->assertArrayHasKey('user_role_id_Index', $existingIndexes);
        }

        /**
         * @depends testGenerateOrUpdateTableBySchemaDefinitionWithValidButChangedSchemaForExistingTableWithNoIsFreshInstall
         */
        public function testGenerateOrUpdateTableBySchemaDefinitionChangingColumnLength()
        {
            // try decreasing length, shouldn't work
            $schema     = array('tablename3' => array(
                'columns' => array(
                    array(
                        'name' => 'language',
                        'type' => 'VARCHAR(10)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'role_id',
                        'type' => 'INT(5)',
                        'unsigned' => 'UNSIGNED',
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => null,
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                ),
                'indexes' => array()
            )
            );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
            $processedTables    = CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::resolveProcessedTables();
            $this->assertNotEmpty($processedTables);
            $this->assertCount(3, $processedTables);
            $this->assertEquals('tablewithnocolumns', $processedTables[0]);
            $this->assertEquals('tablename1', $processedTables[1]);
            $this->assertEquals('tablename3', $processedTables[2]);
            // we do not need try-catch here as if there was an exception it would have been thrown already.
            $existingFields     = ZurmoRedBean::$writer->getColumnsWithDetails('tablename3');
            $this->assertNotEmpty($existingFields);
            $this->assertCount(15, $existingFields);
            $this->assertArrayHasKey('language', $existingFields);
            $this->assertArrayHasKey('role_id', $existingFields);
            $this->assertEquals('int(11) unsigned', $existingFields['role_id']['Type']);
            $this->assertEquals('varchar(100)', $existingFields['language']['Type']);

            // try increasing lengths, should work
            $schema     = array('tablename3' => array(
                'columns' => array(
                    array(
                        'name' => 'language',
                        'type' => 'VARCHAR(120)',
                        'unsigned' => null,
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                    array(
                        'name' => 'role_id',
                        'type' => 'INT(15)',
                        'unsigned' => 'UNSIGNED',
                        'notNull' => 'NULL', // Not Coding Standard
                        'collation' => null,
                        'default' => 'DEFAULT NULL', // Not Coding Standard
                    ),
                ),
                'indexes' => array()
            )
            );
            CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::generateOrUpdateTableBySchemaDefinition($schema,
                                                                                                static::$messageLogger);
            $processedTables    = CreateOrUpdateExistingTableFromSchemaDefinitionArrayUtil::resolveProcessedTables();
            $this->assertNotEmpty($processedTables);
            $this->assertCount(3, $processedTables);
            $this->assertEquals('tablewithnocolumns', $processedTables[0]);
            $this->assertEquals('tablename1', $processedTables[1]);
            $this->assertEquals('tablename3', $processedTables[2]);
            // we do not need try-catch here as if there was an exception it would have been thrown already.
            $existingFields     = ZurmoRedBean::$writer->getColumnsWithDetails('tablename3');
            $this->assertNotEmpty($existingFields);
            $this->assertCount(15, $existingFields);
            $this->assertArrayHasKey('language', $existingFields);
            $this->assertArrayHasKey('role_id', $existingFields);
            $this->assertEquals('int(15) unsigned', $existingFields['role_id']['Type']);
            $this->assertEquals('varchar(120)', $existingFields['language']['Type']);
        }
    }
?>