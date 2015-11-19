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

    class MashableUtilTest extends ZurmoBaseTest
    {
        protected $user;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = User::getByUsername('super');
            $billy = UserTestHelper::createBasicUser('billy');
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
            $this->user = Yii::app()->user->userModel;
        }

        public function testCreateMashableInboxRulesByModel()
        {
            $mashableInboxRules = MashableUtil::createMashableInboxRulesByModel('conversation');
            $this->assertEquals('ConversationMashableInboxRules', get_class($mashableInboxRules));
        }

        public function testGetModelDataForCurrentUserByInterfaceName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $mashableModelData = MashableUtil::getModelDataForCurrentUserByInterfaceName('MashableInboxInterface');
            $this->assertEquals(3, count($mashableModelData));
            Yii::app()->user->userModel = User::getByUsername('billy');
            $mashableModelData = MashableUtil::getModelDataForCurrentUserByInterfaceName('MashableInboxInterface');
            $this->assertEquals(1, count($mashableModelData));
        }

        public function testGetUnreadCountForCurrentUserByModelClassName()
        {
            Mission::deleteAll();
            $count = MashableUtil::getUnreadCountForCurrentUserByModelClassName('Mission');
            $this->assertEquals($count, 0);
            $this->makeANewUnreadMissionOwnedByCurrentUser();
            $count = MashableUtil::getUnreadCountForCurrentUserByModelClassName('Mission');
            $this->assertEquals($count, 1);
        }

        public function testGetUnreadCountMashableInboxForCurrentUser()
        {
            Mission::deleteAll();
            Conversation::deleteAll();
            $count = MashableUtil::GetUnreadCountMashableInboxForCurrentUser();
            $this->assertEquals($count, 0);
            $this->makeANewUnreadMissionOwnedByCurrentUser();
            $count = MashableUtil::GetUnreadCountMashableInboxForCurrentUser();
            $this->assertEquals($count, 1);
            $this->makeANewUnreadConversationOwnedByCurrentUser();
            $count = MashableUtil::GetUnreadCountMashableInboxForCurrentUser();
            $this->assertEquals($count, 2);
        }

        public function testGetSearchAttributeMetadataForMashableInboxByModelClassName()
        {
            $conversationRules    = new ConversationMashableInboxRules();
            $expected = MashableUtil::mergeMetadata(
                MashableUtil::mergeMetadata($conversationRules->getMetadataForMashableInbox(), $conversationRules->getSearchAttributeData('test')),
                $conversationRules->getMetadataFilteredByFilteredBy(MashableInboxForm::FILTERED_BY_ALL)
            );
            $searchAttributesData = MashableUtil::getSearchAttributeMetadataForMashableInboxByModelClassName(
                array('Conversation'),
                MashableInboxForm::FILTERED_BY_ALL,
                'test'
            );
            $this->assertEquals($expected, $searchAttributesData[0]['Conversation']);

            $missionRules         = new MissionMashableInboxRules();
            $expected2 = MashableUtil::mergeMetadata(
                MashableUtil::mergeMetadata($missionRules->getMetadataForMashableInbox(), $missionRules->getSearchAttributeData('test')),
                $missionRules->getMetadataFilteredByFilteredBy(MashableInboxForm::FILTERED_BY_ALL)
            );
            $searchAttributesData = MashableUtil::getSearchAttributeMetadataForMashableInboxByModelClassName(
                array('Conversation', 'Mission'),
                MashableInboxForm::FILTERED_BY_ALL,
                'test'
            );
            $this->assertEquals($expected,  $searchAttributesData[0]['Conversation']);
            $this->assertEquals($expected2, $searchAttributesData[1]['Mission']);
        }

        public function testGetSortAttributesByMashableInboxModelClassNames()
        {
            $conversationRules    = new ConversationMashableInboxRules();
            $missionRules         = new MissionMashableInboxRules();
            $sortAttributes       = MashableUtil::getSortAttributesByMashableInboxModelClassNames(
                                                array('Conversation', 'Mission'));
            $this->assertEquals($conversationRules->getMachableInboxOrderByAttributeName(),
                                $sortAttributes['Conversation']);
            $this->assertEquals($missionRules->getMachableInboxOrderByAttributeName(),
                                $sortAttributes['Mission']);
        }

        public function testRenderSummaryContent()
        {
            $conversation = $this->makeANewUnreadConversationOwnedByCurrentUser();
            $conversationRules    = new ConversationMashableInboxRules();
            $content = MashableUtil::renderSummaryContent($conversation);
            $contentWithRemovedId = preg_replace("/(DetailsLinkActionElement.*yt)(\d*)/", "$1", $content);
            $this->assertContains('<div class="model-tag conversation">', $contentWithRemovedId);
            $expectedContent = str_replace('{modelCreationTimeContent}', $conversationRules->getModelCreationTimeContent($conversation),
                                    str_replace('{modelStringContent}', $conversationRules->getModelStringContent($conversation),
                                        $conversationRules->getSummaryContentTemplate()));
            $expectedContentWithRemovedId = preg_replace("/(DetailsLinkActionElement.*yt)(\d*)/", "$1", $expectedContent);
            $this->assertContains($expectedContentWithRemovedId, $contentWithRemovedId);
        }

        public function testResolveContentTemplate()
        {
            $data = array(
                'testVar1' => 'subVar1',
                'testVar2' => 'subVar2',
            );
            $template = '{testVar1} will be resolved and {testVar2} too';
            $content = MashableUtil::resolveContentTemplate($template, $data);
            $this->assertEquals('subVar1 will be resolved and subVar2 too', $content);
            $data = array(
                'testVar1' => 'subVar1',
            );
            $content = MashableUtil::resolveContentTemplate($template, $data);
            $this->assertEquals($content, 'subVar1 will be resolved and {testVar2} too');
        }

        public function testMergeMetada()
        {
            $firstMetadata  = null;
            $secondMetadata = null;
            $mergedMetadata = MashableUtil::mergeMetadata($firstMetadata, $secondMetadata);
            $this->assertEquals($mergedMetadata['clauses'],   array());
            $this->assertEquals($mergedMetadata['structure'], null);

            $firstMetadata  = array(
                    'clauses'       => array(1 => 'testClause1'),
                    'structure'     => '1',
            );
            $secondMetadata = null;
            $mergedMetadata = MashableUtil::mergeMetadata($firstMetadata, $secondMetadata);
            $this->assertEquals(array(1 => 'testClause1'), $mergedMetadata['clauses']);
            $this->assertEquals('1', $mergedMetadata['structure']);

            $firstMetadata  = null;
            $secondMetadata = array(
                    'clauses'       => array(1 => 'testClause1'),
                    'structure'     => '1',
            );
            $mergedMetadata = MashableUtil::mergeMetadata($firstMetadata, $secondMetadata);
            $this->assertEquals($mergedMetadata['clauses'],   array(1 => 'testClause1'));
            $this->assertEquals($mergedMetadata['structure'], '1');

            $firstMetadata  = array(
                    'clauses'       => array(1 => 'testClause1'),
                    'structure'     => '1',
            );
            $secondMetadata  = array(
                    'clauses'       => array(1 => 'testClause1ForSecondMetadata'),
                    'structure'     => '1',
            );
            $mergedMetadata = MashableUtil::mergeMetadata($firstMetadata, $secondMetadata);
            $this->assertEquals(array(1 => 'testClause1',
                                      2 => 'testClause1ForSecondMetadata'),
                                $mergedMetadata['clauses']);
            $this->assertEquals('(1) and (2)', $mergedMetadata['structure']);

            $firstMetadata  = array(
                    'clauses'       => array(1 => 'testClause1',
                                             2 => 'testClause2',
                                             3 => 'testClause3',
                                        ),
                    'structure'     => '1 and (2 or 3)',
            );
            $secondMetadata  = array(
                    'clauses'       => array(1 => 'testClause1ForSecondMetadata',
                                             2 => 'testClause2ForSecondMetadata',
                                        ),
                    'structure'     => '1 and 2',
            );
            $mergedMetadata = MashableUtil::mergeMetadata($firstMetadata, $secondMetadata, false);
            $this->assertEquals($mergedMetadata['clauses'],   array(1 => 'testClause1',
                                                                    2 => 'testClause2',
                                                                    3 => 'testClause3',
                                                                    4 => 'testClause1ForSecondMetadata',
                                                                    5 => 'testClause2ForSecondMetadata'));
            $this->assertEquals($mergedMetadata['structure'], '(1 and (2 or 3)) or (4 and 5)');

            $firstMetadata  = array(
                    'clauses'       => array(1 => 'testClause1'),
                    'structure'     => '1',
            );
            $secondMetadata  = array(
                    'clauses'       => array(1 => 'testClause1ForSecondMetadata'),
                    'structure'     => '1',
            );
            $mergedMetadata = MashableUtil::mergeMetadata($firstMetadata, $secondMetadata);
            $this->assertEquals(array(1 => 'testClause1',
                                      2 => 'testClause1ForSecondMetadata'),
                                $mergedMetadata['clauses']);
            $this->assertEquals('(1) and (2)', $mergedMetadata['structure']);

            $firstMetadata  = array(
                    'clauses'       => array(1 => 'testClause1'),
                    'structure'     => '1',
            );
            $secondMetadata  = array(
                    'clauses'       => array(1 => 'testClause1ForSecondMetadata',
                                             2 => 'testClause2ForSecondMetadata',
                                             3 => 'testClause3ForSecondMetadata',
                                             4 => 'testClause4ForSecondMetadata'),
                    'structure'     => '((1 and 2) or (3 and 4))',
            );
            $mergedMetadata = MashableUtil::mergeMetadata($firstMetadata, $secondMetadata);
            $this->assertEquals(array(1 => 'testClause1',
                                      2 => 'testClause1ForSecondMetadata',
                                      3 => 'testClause2ForSecondMetadata',
                                      4 => 'testClause3ForSecondMetadata',
                                      5 => 'testClause4ForSecondMetadata'),
                                $mergedMetadata['clauses']);
            $this->assertEquals('(1) and (((2 and 3) or (4 and 5)))', $mergedMetadata['structure']);
        }

        public function testSaveSelectedOptionsAsStickyData()
        {
            $testData = array(
                'optionForModel'    => 'aaaaa',
                'filteredBy'        => 'bbbbb',
                'searchTerm'        => 'ccccc');
            $mashableInboxForm = new MashableInboxForm();
            $mashableInboxForm->setAttributes($testData);
            $key = MashableUtil::resolveKeyByModuleAndModel('MashableInboxModule', 'testClassName');
            MashableUtil::saveSelectedOptionsAsStickyData($mashableInboxForm, 'testClassName');
            $this->assertEquals($testData, StickyUtil::getDataByKey($key));

            $testData2 = array(
                'optionForModel'    => 'aaaaa',
                'filteredBy'        => 'bbbbb',
                'searchTerm'        => 'ccccc',
                'selectedIds'       => 'ddddd',
                'massAction'        => 'eeeee');
            $mashableInboxForm = new MashableInboxForm();
            $mashableInboxForm->setAttributes($testData);
            StickyUtil::clearDataByKey($key);
            MashableUtil::saveSelectedOptionsAsStickyData($mashableInboxForm, 'testClassName');
            $this->assertEquals($testData, StickyUtil::getDataByKey($key));
        }

        public function testRestoreSelectedOptionsAsStickyData()
        {
            $key = MashableUtil::
                        resolveKeyByModuleAndModel('MashableInboxModule', 'testClassName');
            StickyUtil::clearDataByKey($key);
            $mashableInboxForm = MashableUtil::
                                    restoreSelectedOptionsAsStickyData('testClassName');
            $mashableInboxFormForCompare = new MashableInboxForm();
            $this->assertEquals($mashableInboxFormForCompare->attributes,
                                $mashableInboxForm->attributes);
            $testData = array(
                'optionForModel'    => 'aaaaa',
                'filteredBy'        => 'bbbbb',
                'searchTerm'        => 'ccccc');
            $key = MashableUtil::
                        resolveKeyByModuleAndModel('MashableInboxModule', 'testClassName');
            StickyUtil::clearDataByKey($key);
            StickyUtil::setDataByKeyAndData($key, $testData);
            $mashableInboxForm = MashableUtil::
                                    restoreSelectedOptionsAsStickyData('testClassName');
            $this->assertEquals($testData, array_intersect($testData, StickyUtil::getDataByKey($key)));
        }

        public function testResolveKeyByModuleAndModel()
        {
            $key = MashableUtil::resolveKeyByModuleAndModel('testModule', 'testClassName');
            $this->assertEquals('testModule_testClassName', $key);
        }

        protected function makeANewUnreadConversationOwnedByCurrentUser()
        {
            $conversation              = new Conversation();
            $conversation->owner       = $this->user;
            $conversation->subject     = 'My test subject';
            $conversation->description = 'My test description';
            $this->assertTrue($conversation->save());
            $conversation->ownerHasReadLatest = false;
            $this->assertTrue($conversation->save());
            return $conversation;
        }

        protected function makeANewUnreadMissionOwnedByCurrentUser()
        {
            $mission              = new Mission();
            $mission->owner       = $this->user;
            $mission->dueDateTime = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $mission->description = 'My test description';
            $mission->reward      = 'My test reward';
            $mission->status      = Mission::STATUS_AVAILABLE;
            $this->assertTrue($mission->save());
            $rules = MashableUtil::createMashableInboxRulesByModel('Mission');
            $rules->markUserAsHavingUnreadLatestModel($mission, $this->user);
            return $mission;
        }
    }
?>