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

    class RedBeanModelMemberRulesToColumnAdapterTest extends BaseTest
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

        public function testResolveWithEmptyRules()
        {
            // using AuditEvent everywhere as it can't have bean, neither can its parent.
            $modelClassName = 'AuditEvent';
            $rules          = array();
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertFalse($column);
        }

        /**
         * @depends testResolveWithEmptyRules
         */
        public function testResolveForRequiredAttributeWithNoType()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
              array('attributeName', 'required'),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertFalse($column);
        }

        /**
         * @depends testResolveForRequiredAttributeWithNoType
         */
        public function testResolveWithIgnoredValidators()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'default', 'value' => 10),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertFalse($column);
        }

        /**
         * @depends testResolveWithIgnoredValidators
         */
        public function testResolveForNumericalAttributeWithNoType()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'numerical'),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('attributename', $column['name']);
            $this->assertEquals('INT(11)', $column['type']);
            $this->assertNull($column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertNull($column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveForNumericalAttributeWithNoType
         */
        public function testResolveWithJustTypeValidatorAndValidType()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'type', 'type' => 'string'),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('attributename', $column['name']);
            $this->assertEquals('TEXT', $column['type']);
            $this->assertNull($column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertEquals('COLLATE utf8_unicode_ci', $column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithJustTypeValidatorAndValidType
         */
        public function testResolveWithJustTypeValidatorAndInvalidType()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'type', 'type' => 'dummy'),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertFalse($column);
        }

        /**
         * @depends testResolveWithJustTypeValidatorAndInvalidType
         */
        public function testResolveWithNumericalValidatorAndPrecision()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'numerical',  'min' => 0, 'precision' => 3),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertFalse($column);
        }

        /**
         * @depends testResolveWithNumericalValidatorAndPrecision
         */
        public function testResolveWithTypeAndNumericalValidator()
        {
            $unsigned       = null;
            $assumedSigned  = RedBeanModelMemberRulesToColumnAdapter::ASSUME_SIGNED;
            if (!$assumedSigned)
            {
                $unsigned   = 'UNSIGNED';
            }
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'type', 'type' => 'float'),
                array('attributeName', 'numerical'),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('attributename', $column['name']);
            $this->assertEquals('DOUBLE', $column['type']);
            $this->assertEquals($unsigned, $column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertNull($column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithTypeAndNumericalValidator
         */
        public function testResolveWithNumericalAndTypeValidator()
        {
            $unsigned       = null;
            $assumedSigned  = RedBeanModelMemberRulesToColumnAdapter::ASSUME_SIGNED;
            if (!$assumedSigned)
            {
                $unsigned   = 'UNSIGNED';
            }
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'numerical'),
                array('attributeName', 'type', 'type' => 'float'),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('attributename', $column['name']);
            $this->assertEquals('DOUBLE', $column['type']);
            $this->assertEquals($unsigned, $column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertNull($column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithNumericalAndTypeValidator
         */
        public function testResolveWithTypeAndStringValidator()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'type', 'type' => 'string'),
                array('attributeName', 'length', 'max' => 100),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('attributename', $column['name']);
            $this->assertEquals('VARCHAR(100)', $column['type']);
            $this->assertNull($column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertEquals('COLLATE utf8_unicode_ci', $column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithTypeAndStringValidator
         */
        public function testResolveWithStringAndTypeValidator()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'length', 'max' => 100),
                array('attributeName', 'type', 'type' => 'string'),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('attributename', $column['name']);
            $this->assertEquals('VARCHAR(100)', $column['type']);
            $this->assertNull($column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertEquals('COLLATE utf8_unicode_ci', $column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithStringAndTypeValidator
         */
        public function testResolveWithStringValidatorAndVariableLength()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'type', 'type' => 'string'),
                array('attributeName', 'length', 'max' => 10),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('attributename', $column['name']);
            $this->assertEquals('VARCHAR(10)', $column['type']);
            $this->assertNull($column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertEquals('COLLATE utf8_unicode_ci', $column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard

            $rules          = array(
                array('attributeName', 'type', 'type' => 'string'),
                array('attributeName', 'length', 'max' => 255),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('type', $column);
            $this->assertEquals('VARCHAR(255)', $column['type']);

            $rules          = array(
                array('attributeName', 'type', 'type' => 'string'),
                array('attributeName', 'length', 'max' => 655361),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('type', $column);
            $this->assertEquals('LONGTEXT', $column['type']);
        }

        /**
         * @depends testResolveWithStringValidatorAndVariableLength
         */
        public function testResolveWithEmailValidator()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'email'),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('attributename', $column['name']);
            $this->assertEquals('VARCHAR(255)', $column['type']);
            $this->assertNull($column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertEquals('COLLATE utf8_unicode_ci', $column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithEmailValidator
         */
        public function testResolveWithEmailAndTypeValidator()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'email'),
                array('attributeName', 'type', 'type' => 'string'),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('attributename', $column['name']);
            $this->assertEquals('VARCHAR(255)', $column['type']);
            $this->assertNull($column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertEquals('COLLATE utf8_unicode_ci', $column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithEmailAndTypeValidator
         */
        public function testResolveWithTypeAndEmailValidator()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'type', 'type' => 'string'),
                array('attributeName', 'email'),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('attributename', $column['name']);
            $this->assertEquals('VARCHAR(255)', $column['type']);
            $this->assertNull($column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertEquals('COLLATE utf8_unicode_ci', $column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithTypeAndEmailValidator
         */
        public function testResolveWithEmailAndTypeAndStringValidator()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'email'),
                array('attributeName', 'type', 'type' => 'string'),
                array('attributeName', 'length', 'max' => 64),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('attributename', $column['name']);
            $this->assertEquals('VARCHAR(64)', $column['type']);
            $this->assertNull($column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertEquals('COLLATE utf8_unicode_ci', $column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithEmailAndTypeAndStringValidator
         */
        public function testResolveWithTypeAndStringAndEmailValidator()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'type', 'type' => 'string'),
                array('attributeName', 'length', 'max' => 64),
                array('attributeName', 'email'),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('attributename', $column['name']);
            $this->assertEquals('VARCHAR(64)', $column['type']);
            $this->assertNull($column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertEquals('COLLATE utf8_unicode_ci', $column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithTypeAndStringAndEmailValidator
         */
        public function testResolveWithUrlValidator()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'url'),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('attributename', $column['name']);
            $this->assertEquals('VARCHAR(255)', $column['type']);
            $this->assertNull($column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertEquals('COLLATE utf8_unicode_ci', $column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithUrlValidator
         */
        public function testResolveWithUrlAndTypeValidator()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'url'),
                array('attributeName', 'type', 'type' => 'string'),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('attributename', $column['name']);
            $this->assertEquals('VARCHAR(255)', $column['type']);
            $this->assertNull($column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertEquals('COLLATE utf8_unicode_ci', $column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithUrlAndTypeValidator
         */
        public function testResolveWithTypeAndUrlValidator()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'type', 'type' => 'string'),
                array('attributeName', 'url'),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('attributename', $column['name']);
            $this->assertEquals('VARCHAR(255)', $column['type']);
            $this->assertNull($column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertEquals('COLLATE utf8_unicode_ci', $column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithTypeAndUrlValidator
         */
        public function testResolveWithUrlAndTypeAndStringValidator()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'url'),
                array('attributeName', 'type', 'type' => 'string'),
                array('attributeName', 'length', 'max' => 64),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('attributename', $column['name']);
            $this->assertEquals('VARCHAR(64)', $column['type']);
            $this->assertNull($column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertEquals('COLLATE utf8_unicode_ci', $column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithUrlAndTypeAndStringValidator
         */
        public function testResolveWithTypeAndStringAndUrlValidator()
        {
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'type', 'type' => 'string'),
                array('attributeName', 'length', 'max' => 64),
                array('attributeName', 'url'),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('attributename', $column['name']);
            $this->assertEquals('VARCHAR(64)', $column['type']);
            $this->assertNull($column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertEquals('COLLATE utf8_unicode_ci', $column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithTypeAndStringAndUrlValidator
         */
        public function testResolveWithNumericalValidatorAndVariableMax()
        {
            $unsigned       = null;
            $assumedSigned  = RedBeanModelMemberRulesToColumnAdapter::ASSUME_SIGNED;
            if (!$assumedSigned)
            {
                $unsigned   = 'UNSIGNED';
            }
            $maxAllowed     = DatabaseCompatibilityUtil::resolveIntegerMaxAllowedValuesByType($assumedSigned);
            $modelClassName = 'AuditEvent';
            $types          = array_keys($maxAllowed);
            foreach ($types as $type)
            {
                $dbType         = strtoupper($type);
                if ($type == 'integer')
                {
                    $dbType     = 'INT';
                }
                $dbType         .= '(11)';
                $max            = ($maxAllowed[$type] - ($maxAllowed[$type]/5));
                $rules          = array(
                    array('attributeName' . $type, 'type', 'type' => 'integer'),
                    array('attributeName' . $type, 'numerical', 'max' => $max),
                );
                $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                    $rules,
                                                                                    static::$messageLogger);
                $this->assertNotEmpty($column);
                $this->assertArrayHasKey('name', $column);
                $this->assertArrayHasKey('type', $column);
                $this->assertArrayHasKey('unsigned', $column);
                $this->assertArrayHasKey('notNull', $column);
                $this->assertArrayHasKey('collation', $column);
                $this->assertArrayHasKey('default', $column);
                $this->assertEquals('attributename' . $type, $column['name']);
                $this->assertEquals($dbType, $column['type']);
                $this->assertEquals($unsigned, $column['unsigned']);
                $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
                $this->assertNull($column['collation']);
                $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
            }
        }

        /**
         * @depends testResolveWithNumericalValidatorAndVariableMax
         */
        public function testResolveWithNumericalValidatorAndVariableMinForSigned()
        {
            $assumedSigned  = RedBeanModelMemberRulesToColumnAdapter::ASSUME_SIGNED;
            if (!$assumedSigned)
            {
                return;
            }
            $maxAllowed = DatabaseCompatibilityUtil::resolveIntegerMaxAllowedValuesByType($assumedSigned);
            $modelClassName = 'AuditEvent';
            $types          = array_keys($maxAllowed);
            foreach ($types as $type)
            {
                $dbType         = strtoupper($type);
                if ($type == 'integer')
                {
                    $dbType     = 'INT';
                }
                $dbType         .= '(11)';
                $max            = ($maxAllowed[$type] - ($maxAllowed[$type]/5));
                $minAllowed     = static::calculateMinByMaxAndSigned($maxAllowed[$type], $assumedSigned);
                $minAllowed     = ($minAllowed + $max/5);
                $rules          = array(
                    array('attributeName' . $type, 'type', 'type' => 'integer'),
                    array('attributeName' . $type, 'numerical', 'min' => $minAllowed, 'max' => $max),
                );
                $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                    $rules,
                                                                                    static::$messageLogger);
                $this->assertNotEmpty($column);
                $this->assertArrayHasKey('name', $column);
                $this->assertArrayHasKey('type', $column);
                $this->assertArrayHasKey('unsigned', $column);
                $this->assertArrayHasKey('notNull', $column);
                $this->assertArrayHasKey('collation', $column);
                $this->assertArrayHasKey('default', $column);
                $this->assertEquals('attributename' . $type, $column['name']);
                $this->assertEquals($dbType, $column['type']);
                $this->assertNull($column['unsigned']);
                $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
                $this->assertNull($column['collation']);
                $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
            }
        }

        /**
         * @depends testResolveWithNumericalValidatorAndVariableMinForSigned
         */
        public function testResolveUniqueIndexesFromValidator()
        {
            $modelClassName = 'AuditEvent';
            $uniqueIndexes  = RedBeanModelMemberRulesToColumnAdapter::resolveUniqueIndexesFromValidator($modelClassName);
            $this->assertNull($uniqueIndexes);
        }

        /**
         * @depends testResolveUniqueIndexesFromValidator
         */
        public function testResolveWithUniqueValidator()
        {
            $unsigned       = null;
            $assumedSigned  = RedBeanModelMemberRulesToColumnAdapter::ASSUME_SIGNED;
            if (!$assumedSigned)
            {
                $unsigned   = 'UNSIGNED';
            }
            $modelClassName = 'AuditEvent';
            $rules          = array(
                array('attributeName', 'unique'),
                array('attributeName', 'type', 'type' => 'integer'),
            );
            $column         = RedBeanModelMemberRulesToColumnAdapter::resolve($modelClassName,
                                                                                $rules,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('attributename', $column['name']);
            $this->assertEquals('INT(11)', $column['type']);
            $this->assertEquals($unsigned, $column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertNull($column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard

            $uniqueIndex = RedBeanModelMemberRulesToColumnAdapter::resolveUniqueIndexesFromValidator($modelClassName);
            $this->assertNotEmpty($uniqueIndex);
            $this->assertCount(1, $uniqueIndex);
            $indexName  = key($uniqueIndex);
            $this->assertCount(2, $uniqueIndex[$indexName]);
            $this->assertArrayHasKey('members', $uniqueIndex[$indexName]);
            $this->assertArrayHasKey('unique', $uniqueIndex[$indexName]);
            $this->assertCount(1, $uniqueIndex[$indexName]['members']);
            $this->assertEquals('attributeName', $uniqueIndex[$indexName]['members'][0]);
            $this->assertTrue($uniqueIndex[$indexName]['unique']);
        }

        protected static function calculateMinByMaxAndSigned($max, $signed = false)
        {
            if (!$signed)
            {
                return 0;
            }
            else
            {
                return -1 * $max;
            }
        }
    }
?>