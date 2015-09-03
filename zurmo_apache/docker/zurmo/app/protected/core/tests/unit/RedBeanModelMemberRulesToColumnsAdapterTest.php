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

    class RedBeanModelMemberRulesToColumnsAdapterTest extends BaseTest
    {
        protected static $messageLogger;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            static::$messageLogger  = new MessageLogger();
        }

        public function testResolveWithEmptyMembers()
        {
            // using AuditEvent everywhere as it can't have bean, neither can its parent.
            $modelClassName = 'AuditEvent';
            $members        = array();
            $rules          = array(array('nonMember', 'required'));
            $columns        = RedBeanModelMemberRulesToColumnsAdapter::resolve($modelClassName,
                                                                                $members,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertEmpty($columns);
        }

        /**
         * @depends testResolveWithEmptyMembers
         * @expectedException CException
         * @expectedMessage Not all members for AuditEvent could be translated to columns.Members: (memberOne),Columns ()
         */
        public function testResolveWithEmptyRules()
        {
            $modelClassName = 'AuditEvent';
            $members        = array('memberOne');
            $rules          = array();
            $columns        = RedBeanModelMemberRulesToColumnsAdapter::resolve($modelClassName,
                                                                                $members,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertEmpty($columns);
        }

        /**
         * @depends testResolveWithEmptyRules
         */
        public function testResolveWithEmptyRulesAndMembers()
        {
            $modelClassName = 'AuditEvent';
            $members        = array();
            $rules          = array();
            $columns        = RedBeanModelMemberRulesToColumnsAdapter::resolve($modelClassName,
                                                                                $members,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertEmpty($columns);
        }

        /**
         * @depends testResolveWithEmptyRulesAndMembers
         */
        public function testResolveWithRulesForNonMember()
        {
            $modelClassName = 'AuditEvent';
            $members        = array('memberOne');
            $rules          = array(
                array('memberOne', 'email'),
                array('nonMember', 'required'),
                array('nonMember', 'website'),
            );
            $columns        = RedBeanModelMemberRulesToColumnsAdapter::resolve($modelClassName,
                                                                                $members,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($columns);
            $this->assertCount(1, $columns);
            $this->assertCount(6, $columns[0]);
            $this->assertArrayHasKey('name', $columns[0]);
            $this->assertArrayHasKey('type', $columns[0]);
            $this->assertArrayHasKey('unsigned', $columns[0]);
            $this->assertArrayHasKey('notNull', $columns[0]);
            $this->assertArrayHasKey('collation', $columns[0]);
            $this->assertArrayHasKey('default', $columns[0]);
            $this->assertEquals('memberone', $columns[0]['name']);
            $this->assertEquals('VARCHAR(255)', $columns[0]['type']);
            $this->assertNull($columns[0]['unsigned']);
            $this->assertEquals('NULL', $columns[0]['notNull']); // Not Coding Standard
            $this->assertEquals('COLLATE utf8_unicode_ci', $columns[0]['collation']);
            $this->assertEquals('DEFAULT NULL', $columns[0]['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithRulesForNonMember
         * @expectedException CException
         * @expectedMessage Failed to resolve AuditEvent.memberOne to column
         */
        public function testResolveWithOnlyRequiredValidatorForMember()
        {
            $modelClassName = 'AuditEvent';
            $members        = array('memberOne');
            $rules          = array(array('memberOne', 'required'));
            $columns        = RedBeanModelMemberRulesToColumnsAdapter::resolve($modelClassName,
                                                                                $members,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertEmpty($columns);
        }

        /**
         * @depends testResolveWithOnlyRequiredValidatorForMember
         */
        public function testResolveWithMixedRules()
        {
            $modelClassName     = 'AuditEvent';
            $expectedColumns    = array(
                array(
                    'name'      => 'type',
                    'type'      => 'INT(11)',
                    'unsigned'  => null,
                    'notNull'   => 'NULL', // Not Coding Standard
                    'collation' => null,
                    'default'   => 'DEFAULT NULL', // Not Coding Standard
                ),
            array(
                    'name'      => 'modelclassname',
                    'type'      => 'VARCHAR(64)',
                    'unsigned'  => null,
                    'notNull'   => 'NULL', // Not Coding Standard
                    'collation' => 'COLLATE utf8_unicode_ci',
                    'default'   => 'DEFAULT NULL', // Not Coding Standard
                ),
            array(
                    'name'      => 'name',
                    'type'      => 'VARCHAR(64)',
                    'unsigned'  => null,
                    'notNull'   => 'NULL', // Not Coding Standard
                    'collation' => 'COLLATE utf8_unicode_ci',
                    'default'   => 'DEFAULT NULL', // Not Coding Standard
                ),
            array(
                    'name'      => 'subject',
                    'type'      => 'VARCHAR(64)',
                    'unsigned'  => null,
                    'notNull'   => 'NULL', // Not Coding Standard
                    'collation' => 'COLLATE utf8_unicode_ci',
                    'default'   => 'DEFAULT NULL', // Not Coding Standard
                ),
            array(
                    'name'      => 'language',
                    'type'      => 'VARCHAR(2)',
                    'unsigned'  => null,
                    'notNull'   => 'NULL', // Not Coding Standard
                    'collation' => 'COLLATE utf8_unicode_ci',
                    'default'   => 'DEFAULT NULL', // Not Coding Standard
                ),
            array(
                    'name'      => 'htmlcontent',
                    'type'      => 'TEXT',
                    'unsigned'  => null,
                    'notNull'   => 'NULL', // Not Coding Standard
                    'collation' => 'COLLATE utf8_unicode_ci',
                    'default'   => 'DEFAULT NULL', // Not Coding Standard
                ),
            array(
                    'name'      => 'textcontent',
                    'type'      => 'TEXT',
                    'unsigned'  => null,
                    'notNull'   => 'NULL', // Not Coding Standard
                    'collation' => 'COLLATE utf8_unicode_ci',
                    'default'   => 'DEFAULT NULL', // Not Coding Standard
                )

            );
            $members            = array(
                'type',
                'modelClassName',
                'name',
                'subject',
                'language',
                'htmlContent',
                'textContent',
            );
            $rules              = array(
                array('type',                       'required'),
                array('type',                       'type',    'type' => 'integer'),
                array('type',                       'numerical'),
                array('modelClassName',             'required'),
                array('modelClassName',             'type',   'type' => 'string'),
                array('modelClassName',             'length', 'max' => 64),
                array('modelClassName',             'ModelExistsAndIsReadableValidator'),
                array('name',                       'required'),
                array('name',                       'type',    'type' => 'string'),
                array('name',                       'length',  'min'  => 3, 'max' => 64),
                array('subject',                    'required'),
                array('subject',                    'type',    'type' => 'string'),
                array('subject',                    'length',  'min'  => 3, 'max' => 64),
                array('language',                   'type',    'type' => 'string'),
                array('language',                   'length',  'min' => 2, 'max' => 2),
                array('language',                   'SetToUserDefaultLanguageValidator'),
                array('htmlContent',                'type',    'type' => 'string'),
                array('textContent',                'type',    'type' => 'string'),
                array('htmlContent',                'AtLeastOneContentAreaRequiredValidator'),
                array('textContent',                'AtLeastOneContentAreaRequiredValidator'),
                array('htmlContent',                'EmailTemplateMergeTagsValidator'),
                array('textContent',                'EmailTemplateMergeTagsValidator'),
            );
            $columns            = RedBeanModelMemberRulesToColumnsAdapter::resolve($modelClassName,
                                                                                $members,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($columns);
            $this->assertCount(7, $columns);
            foreach ($columns as $index => $column)
            {
                $this->assertCount(6, $column);
                $this->assertArrayHasKey('name', $column);
                $this->assertArrayHasKey('type', $column);
                $this->assertArrayHasKey('unsigned', $column);
                $this->assertArrayHasKey('notNull', $column);
                $this->assertArrayHasKey('collation', $column);
                $this->assertArrayHasKey('default', $column);
                $this->assertEquals($expectedColumns[$index], $column);
            }
        }
    }
?>