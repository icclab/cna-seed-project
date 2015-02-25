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

    class RedBeanModelMemberIndexesMetadataAdapterTest extends BaseTest
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

        public function testResolveWithEmptyMetadata()
        {
            $indexesMetadata    = array();
            $modelClassName     = 'Email';
            $resolvedIndexes    = RedBeanModelMemberIndexesMetadataAdapter::resolve($modelClassName,
                                                                                    $indexesMetadata,
                                                                                    static::$messageLogger);
            $this->assertEmpty($resolvedIndexes);
        }

        /**
         * @depends testResolveWithEmptyMetadata
         * @expectedException CException
         * @expectedMessage Failed to resolve Email.indexName index
         */
        public function testResolveWithMissingMembersInMetadata()
        {
            $indexesMetadata    = array(
                                    'indexName' => array(),
                                    );
            $modelClassName     = 'Email';
            $resolvedIndexes    = RedBeanModelMemberIndexesMetadataAdapter::resolve($modelClassName,
                                                                                    $indexesMetadata,
                                                                                    static::$messageLogger);
            $this->assertEmpty($resolvedIndexes);
        }

        /**
         * @depends testResolveWithMissingMembersInMetadata
         */
        public function testResolve()
        {
            $indexesMetadata    = array(
                                    'indexNameOne' => array(
                                                            'members' => array('memberOne')
                                                        ),
                                    'indexNameTwo' => array(
                                                            'members' => array('memberOne', 'memberTwo'),
                                                            'unique' => true
                                                        ),
                                    );
            $modelClassName     = 'Email';
            $resolvedIndexes    = RedBeanModelMemberIndexesMetadataAdapter::resolve($modelClassName,
                                                                                    $indexesMetadata,
                                                                                    static::$messageLogger);
            $this->assertNotEmpty($resolvedIndexes);
            $this->assertCount(2, $resolvedIndexes);
            $firstIndexKey  = key($resolvedIndexes);
            $secondIndexKey = key($resolvedIndexes);
            $this->assertArrayHasKey('indexNameOne', $resolvedIndexes);
            $this->assertCount(2, $resolvedIndexes['indexNameOne']);
            $this->assertArrayHasKey('columns', $resolvedIndexes['indexNameOne']);
            $this->assertArrayHasKey('unique', $resolvedIndexes['indexNameOne']);
            $this->assertCount(1, $resolvedIndexes['indexNameOne']['columns']);
            $this->assertEquals('memberone', $resolvedIndexes['indexNameOne']['columns'][0]);
            $this->assertFalse($resolvedIndexes['indexNameOne']['unique']);

            $this->assertArrayHasKey('indexNameTwo', $resolvedIndexes);
            $this->assertCount(2, $resolvedIndexes['indexNameTwo']);
            $this->assertArrayHasKey('columns', $resolvedIndexes['indexNameTwo']);
            $this->assertArrayHasKey('unique', $resolvedIndexes['indexNameTwo']);
            $this->assertCount(2, $resolvedIndexes['indexNameTwo']['columns']);
            $this->assertEquals('memberone', $resolvedIndexes['indexNameTwo']['columns'][0]);
            $this->assertEquals('membertwo', $resolvedIndexes['indexNameTwo']['columns'][1]);
            $this->assertTrue($resolvedIndexes['indexNameTwo']['unique']);
        }
    }
?>