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

    class RedBeanModelRelationToColumnAdapterTest extends BaseTest
    {
        protected static $messageLogger;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            static::$messageLogger = new MessageLogger();
        }

        public function testResolveWithEmptyModelClassName()
        {
            $modelClassName     = null;
            $relationName       = 'ThirdCousin';
            $relationMetadata   = array(RedBeanModel::HAS_ONE, 'Person');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertFalse($column);
        }

        /**
         * @depends testResolveWithEmptyModelClassName
         */
        public function testResolveWithInexistentModelClassName()
        {
            $modelClassName     = 'ModelClass';
            $relationName       = 'ThirdCousin';
            $relationMetadata   = array(RedBeanModel::HAS_ONE, 'Person');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertFalse($column);
        }

        /**
         * @depends testResolveWithInexistentModelClassName
         */
        public function testResolveWithEmptyRelationName()
        {
            $modelClassName     = 'User';
            $relationName       = null;
            $relationMetadata   = array(RedBeanModel::HAS_ONE, 'Person');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertFalse($column);
        }

        /**
         * @depends testResolveWithEmptyRelationName
         */
        public function testResolveWithInvalidRelationMetadata()
        {
            $modelClassName     = 'User';
            $relationName       = 'ThirdCousin';
            $relationMetadata   = array('Person');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertFalse($column);
        }

        /**
         * @depends testResolveWithInvalidRelationMetadata
         */
        public function testResolveWithEmptyRelatedModelClassName()
        {
            $modelClassName     = 'User';
            $relationName       = 'ThirdCousin';
            $relationMetadata   = array(RedBeanModel::HAS_ONE, null);
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertFalse($column);
        }

        /**
         * @depends testResolveWithEmptyRelatedModelClassName
         */
        public function testResolveWithInexistentRelatedModelClassName()
        {
            $modelClassName     = 'User';
            $relationName       = 'ThirdCousin';
            $relationMetadata   = array(RedBeanModel::HAS_ONE, 'ModelClass');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertFalse($column);
        }

        /**
         * @depends testResolveWithInexistentRelatedModelClassName
         */
        public function testResolveWithInexistentRelationshipType()
        {
            $modelClassName     = 'User';
            $relationName       = 'ThirdCousin';
            $relationMetadata   = array(100, 'Person');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertFalse($column);
        }

        /**
         * @depends testResolveWithInexistentRelatedModelClassName
         */
        public function testResolveWithInvalidLinkType()
        {
            $modelClassName     = 'Mission';
            $relationName       = 'takenByUser';
            $relationMetadata   = array(RedBeanModel::HAS_ONE,   'User', RedBeanModel::NOT_OWNED, 10, 'takenByUser');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('takenbyuser__user_id', $column['name']);
            $this->assertEquals('INT(11)', $column['type']);
            $this->assertEquals('UNSIGNED', $column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertNull($column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithInvalidLinkType
         */
        public function testResolveWithHasOneAndNoLinkTypeAndSameRelationNameRelatedModelClassName()
        {
            $modelClassName     = 'Autoresponder';
            $relationName       = 'marketingList';
            $relationMetadata   = array(RedBeanModel::HAS_ONE, 'MarketingList', RedBeanModel::NOT_OWNED);
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('marketinglist_id', $column['name']);
            $this->assertEquals('INT(11)', $column['type']);
            $this->assertEquals('UNSIGNED', $column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertNull($column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithHasOneAndNoLinkTypeAndSameRelationNameRelatedModelClassName
         */
        public function testResolveWithHasOneAndNoLinkTypeAndDifferentRelationNameRelatedModelClassName()
        {
            $modelClassName     = 'SocialItem';
            $relationName       = 'toUser';
            $relationMetadata   = array(RedBeanModel::HAS_ONE,  'User', RedBeanModel::NOT_OWNED);
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('touser_touser__user_id', $column['name']);
            $this->assertEquals('INT(11)', $column['type']);
            $this->assertEquals('UNSIGNED', $column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertNull($column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithHasOneAndNoLinkTypeAndDifferentRelationNameRelatedModelClassName
         */
        public function testResolveWithHasOneWithLinkTypeAssumptive()
        {
            $modelClassName     = 'SocialItem';
            $relationName       = 'toUser';
            $relationMetadata   = array(RedBeanModel::HAS_ONE,  'User', RedBeanModel::NOT_OWNED,
                                                                                RedBeanModel::LINK_TYPE_ASSUMPTIVE);
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                        $relationName,
                                                                                        $relationMetadata,
                                                                                        static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('touser_touser__user_id', $column['name']);
            $this->assertEquals('INT(11)', $column['type']);
            $this->assertEquals('UNSIGNED', $column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertNull($column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithHasOneWithLinkTypeAssumptive
         */
        public function testResolveWithHasOneWithLinkTypeSpecific()
        {
            $modelClassName     = 'SocialItem';
            $relationName       = 'toUser';
            $relationMetadata   = array(RedBeanModel::HAS_ONE,  'User', RedBeanModel::NOT_OWNED,
                                                                    RedBeanModel::LINK_TYPE_SPECIFIC, 'toUser');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('touser__user_id', $column['name']);
            $this->assertEquals('INT(11)', $column['type']);
            $this->assertEquals('UNSIGNED', $column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertNull($column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithHasOneWithLinkTypeSpecific
         */
        public function testResolveWithHasManyAndNoLinkTypeAndSameRelationNameRelatedModelClassName()
        {
            $modelClassName     = 'Account';
            $relationName       = 'opportunity';
            $relationMetadata   = array(RedBeanModel::HAS_MANY, 'Opportunity');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNull($column);
        }

        /**
         * @depends testResolveWithHasManyAndNoLinkTypeAndSameRelationNameRelatedModelClassName
         */
        public function testResolveWithHasManyAndNoLinkTypeAndDifferentRelationNameRelatedModelClassName()
        {
            $modelClassName     = 'Account';
            $relationName       = 'accountOpportunities';
            $relationMetadata   = array(RedBeanModel::HAS_MANY, 'Opportunity');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNull($column);
        }

        /**
         * @depends testResolveWithHasManyAndNoLinkTypeAndDifferentRelationNameRelatedModelClassName
         */
        public function testResolveWithHasManyAndLinkTypeAssumptive()
        {
            $modelClassName     = 'Account';
            $relationName       = 'accountOpportunities';
            $relationMetadata   = array(RedBeanModel::HAS_MANY, 'Opportunity',
                                            RedBeanModel::OWNED, RedBeanModel::LINK_TYPE_ASSUMPTIVE);
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNull($column);
        }

        /**
         * @depends testResolveWithHasManyAndLinkTypeAssumptive
         */
        public function testResolveWithHasManyAndLinkTypeSpecific()
        {
            $modelClassName     = 'Account';
            $relationName       = 'accountOpportunities';
            $relationMetadata   = array(RedBeanModel::HAS_MANY, 'Opportunity',
                                            RedBeanModel::OWNED, RedBeanModel::LINK_TYPE_SPECIFIC, 'accountOpt');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNull($column);
        }

        /**
         * @depends testResolveWithHasManyAndLinkTypeSpecific
         */
        public function testResolveWithHasManyAndLinkTypePolymorphic()
        {
            $modelClassName     = 'Campaign';
            $relationName       = 'files';
            $relationMetadata   = array(RedBeanModel::HAS_MANY,  'FileModel', RedBeanModel::OWNED,
                                            RedBeanModel::LINK_TYPE_POLYMORPHIC, 'relatedModel');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNull($column);
            $polymorphicColumns = RedBeanModelRelationToColumnAdapter::resolvePolymorphicColumnsByTableName('filemodel');
            $this->assertNotEmpty($polymorphicColumns);
            $this->assertCount(2, $polymorphicColumns);
            $this->assertArrayHasKey('name', $polymorphicColumns[0]);
            $this->assertArrayHasKey('type', $polymorphicColumns[0]);
            $this->assertArrayHasKey('unsigned', $polymorphicColumns[0]);
            $this->assertArrayHasKey('notNull', $polymorphicColumns[0]);
            $this->assertArrayHasKey('collation', $polymorphicColumns[0]);
            $this->assertArrayHasKey('default', $polymorphicColumns[0]);
            $this->assertEquals('relatedmodel_id', $polymorphicColumns[0]['name']);
            $this->assertEquals('INT(11)', $polymorphicColumns[0]['type']);
            $this->assertEquals('UNSIGNED', $polymorphicColumns[0]['unsigned']);
            $this->assertEquals('NULL', $polymorphicColumns[0]['notNull']); // Not Coding Standard
            $this->assertNull($polymorphicColumns[0]['collation']);
            $this->assertEquals('DEFAULT NULL', $polymorphicColumns[0]['default']); // Not Coding Standard

            $this->assertArrayHasKey('name', $polymorphicColumns[1]);
            $this->assertArrayHasKey('type', $polymorphicColumns[1]);
            $this->assertArrayHasKey('unsigned', $polymorphicColumns[1]);
            $this->assertArrayHasKey('notNull', $polymorphicColumns[1]);
            $this->assertArrayHasKey('collation', $polymorphicColumns[1]);
            $this->assertArrayHasKey('default', $polymorphicColumns[1]);
            $this->assertEquals('relatedmodel_type', $polymorphicColumns[1]['name']);
            $this->assertEquals('VARCHAR(255)', $polymorphicColumns[1]['type']);
            $this->assertNull($polymorphicColumns[1]['unsigned']);
            $this->assertEquals('NULL', $polymorphicColumns[1]['notNull']); // Not Coding Standard
            $this->assertEquals('COLLATE utf8_unicode_ci', $polymorphicColumns[1]['collation']);
            $this->assertEquals('DEFAULT NULL', $polymorphicColumns[1]['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithHasManyAndLinkTypePolymorphic
         */
        public function testResolveWithHasManyBelongsToAndNoLinkTypeAndSameRelationNameRelatedModelClassName()
        {
            $modelClassName     = 'K';
            $relationName       = 'i';
            $relationMetadata   = array(RedBeanModel::HAS_MANY_BELONGS_TO, 'I');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('i_id', $column['name']);
            $this->assertEquals('INT(11)', $column['type']);
            $this->assertEquals('UNSIGNED', $column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertNull($column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithHasManyBelongsToAndNoLinkTypeAndSameRelationNameRelatedModelClassName
         */
        public function testResolveWithHasManyBelongsToAndNoLinkType()
        {
            $modelClassName     = 'K';
            $relationName       = 'i';
            $relationMetadata   = array(RedBeanModel::HAS_MANY_BELONGS_TO, 'I');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('i_id', $column['name']);
            $this->assertEquals('INT(11)', $column['type']);
            $this->assertEquals('UNSIGNED', $column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertNull($column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithHasManyBelongsToAndNoLinkType
         */
        public function testResolveWithHasManyBelongsToAndLinkTypeAssumptive()
        {
            $modelClassName     = 'K';
            $relationName       = 'ii';
            $relationMetadata   = array(RedBeanModel::HAS_MANY_BELONGS_TO, 'II',
                                            RedBeanModel::NOT_OWNED, RedBeanModel::LINK_TYPE_ASSUMPTIVE);
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('ii_id', $column['name']);
            $this->assertEquals('INT(11)', $column['type']);
            $this->assertEquals('UNSIGNED', $column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertNull($column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithHasManyBelongsToAndLinkTypeAssumptive
         */
        public function testResolveWithHasManyBelongsToAndLinkTypeSpecific()
        {
            $modelClassName     = 'K';
            $relationName       = 'iii';
            $relationMetadata   = array(RedBeanModel::HAS_MANY_BELONGS_TO, 'III', RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'ilink');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNotEmpty($column);
            $this->assertArrayHasKey('name', $column);
            $this->assertArrayHasKey('type', $column);
            $this->assertArrayHasKey('unsigned', $column);
            $this->assertArrayHasKey('notNull', $column);
            $this->assertArrayHasKey('collation', $column);
            $this->assertArrayHasKey('default', $column);
            $this->assertEquals('iii_id', $column['name']);
            $this->assertEquals('INT(11)', $column['type']);
            $this->assertEquals('UNSIGNED', $column['unsigned']);
            $this->assertEquals('NULL', $column['notNull']); // Not Coding Standard
            $this->assertNull($column['collation']);
            $this->assertEquals('DEFAULT NULL', $column['default']); // Not Coding Standard
        }

        /**
         * @depends testResolveWithHasManyBelongsToAndLinkTypeSpecific
         */
        public function testResolveWithHasOneBelongsToAndNoLinkTypeAndSameRelationNameRelatedModelClassName()
        {
            $modelClassName     = 'J';
            $relationName       = 'i';
            $relationMetadata   = array(RedBeanModel::HAS_ONE_BELONGS_TO, 'I');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNull($column);
        }

        /**
         * @depends testResolveWithHasOneBelongsToAndNoLinkTypeAndSameRelationNameRelatedModelClassName
         */
        public function testResolveWithHasOneBelongsToAndNoLinkTypeAndDifferentRelationNameRelatedModelClassName()
        {
            $modelClassName     = 'J';
            $relationName       = 'ii';
            $relationMetadata   = array(RedBeanModel::HAS_ONE_BELONGS_TO, 'I');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNull($column);
        }

        /**
         * @depends testResolveWithHasOneBelongsToAndNoLinkTypeAndDifferentRelationNameRelatedModelClassName
         */
        public function testResolveWithHasOneBelongsToAndLinkTypeAssumptive()
        {
            $modelClassName     = 'J';
            $relationName       = 'i';
            $relationMetadata   = array(RedBeanModel::HAS_ONE_BELONGS_TO, 'I',
                                            RedBeanModel::OWNED, RedBeanModel::LINK_TYPE_ASSUMPTIVE);
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNull($column);
        }

        /**
         * @depends testResolveWithHasOneBelongsToAndLinkTypeAssumptive
         */
        public function testResolveWithHasOneBelongsToAndLinkTypeSpecific()
        {
            $modelClassName     = 'J';
            $relationName       = 'i';
            $relationMetadata   = array(RedBeanModel::HAS_ONE_BELONGS_TO, 'I',
                                            RedBeanModel::OWNED, RedBeanModel::LINK_TYPE_SPECIFIC, 'ilink');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNull($column);
        }

        /**
         * @depends testResolveWithHasOneBelongsToAndLinkTypeSpecific
         */
        public function testResolveWithHasOneBelongsToAndLinkTypePolymorphic()
        {
            $modelClassName     = 'J';
            $relationName       = 'i';
            $relationMetadata   = array(RedBeanModel::HAS_ONE_BELONGS_TO, 'I',
                                            RedBeanModel::OWNED, RedBeanModel::LINK_TYPE_POLYMORPHIC, 'ipoly');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNull($column);
        }

        /**
         * @depends testResolveWithHasOneBelongsToAndLinkTypePolymorphic
         */
        public function testResolveWithManyManyAndNoLinkTypeAndSameRelationNameRelatedModelClassName()
        {
            $modelClassName     = 'Contact';
            $relationName       = 'opportunity';
            $relationMetadata   = array(RedBeanModel::MANY_MANY, 'Opportunity');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNull($column);
            $processedTables = RedBeanModelToJoinTableAdapter::resolveProcessedTableNames();
            $this->assertNotEmpty($processedTables);
            $this->assertCount(1, $processedTables);
            $this->assertEquals('contact_opportunity', $processedTables[0]);
        }

        /**
         * @depends testResolveWithManyManyAndNoLinkTypeAndSameRelationNameRelatedModelClassName
         */
        public function testResolveWithManyManyAndNoLinkTypeAndDifferentRelationNameRelatedModelClassName()
        {
            $modelClassName     = 'Conversation';
            $relationName       = 'conversationItems';
            $relationMetadata   = array(RedBeanModel::MANY_MANY, 'Item');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNull($column);
            $processedTables = RedBeanModelToJoinTableAdapter::resolveProcessedTableNames();
            $this->assertNotEmpty($processedTables);
            $this->assertCount(2, $processedTables);
            $this->assertEquals('contact_opportunity', $processedTables[0]);
            $this->assertEquals('conversation_item', $processedTables[1]);
        }

        /**
         * @depends testResolveWithManyManyAndNoLinkTypeAndDifferentRelationNameRelatedModelClassName
         */
        public function testResolveWithManyManyAndLinkTypeAssumptive()
        {
            $modelClassName     = 'Conversation';
            $relationName       = 'conversationItems';
            $relationMetadata   = array(RedBeanModel::MANY_MANY, 'Item',
                                            RedBeanModel::OWNED, RedBeanModel::LINK_TYPE_ASSUMPTIVE);
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNull($column);
            $processedTables = RedBeanModelToJoinTableAdapter::resolveProcessedTableNames();
            $this->assertNotEmpty($processedTables);
            $this->assertCount(2, $processedTables);
            $this->assertEquals('contact_opportunity', $processedTables[0]);
            $this->assertEquals('conversation_item', $processedTables[1]);
        }

        /**
         * @depends testResolveWithManyManyAndLinkTypeAssumptive
         */
        public function testResolveWithManyManyAndLinkTypeSpecific()
        {
            $modelClassName     = 'Conversation';
            $relationName       = 'conversationItems';
            $relationMetadata   = array(RedBeanModel::MANY_MANY, 'Item',
                                            RedBeanModel::OWNED, RedBeanModel::LINK_TYPE_SPECIFIC, 'cItems');
            $column             = RedBeanModelRelationToColumnAdapter::resolve($modelClassName,
                                                                                $relationName,
                                                                                $relationMetadata,
                                                                                static::$messageLogger);
            $this->assertNull($column);
            $processedTables = RedBeanModelToJoinTableAdapter::resolveProcessedTableNames();
            $this->assertNotEmpty($processedTables);
            $this->assertCount(3, $processedTables);
            $this->assertEquals('contact_opportunity', $processedTables[0]);
            $this->assertEquals('conversation_item', $processedTables[1]);
            $this->assertEquals('citems_conversation_item', $processedTables[2]);
        }
    }
?>