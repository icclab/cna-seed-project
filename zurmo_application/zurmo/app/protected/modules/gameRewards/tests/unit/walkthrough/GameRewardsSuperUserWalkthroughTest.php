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
     * GameRewwards Module Super User Walkthrough.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class GameRewardsSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        private static $gameReward1;

        private static $gameReward2;

        private static $gameReward3;

        private static $gameReward4;

        private static $gameReward5;

        private static $gameReward6;

        private static $gameReward7;

        private static $gameReward8;

        private static $gameReward9;

        private static $gameReward10;

        private static $gameReward11;

        private static $gameReward12;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            self::$gameReward1  = GameRewardTestHelper::createGameRewardByNameForOwner('superGameReward', $super);
            self::$gameReward2  = GameRewardTestHelper::createGameRewardByNameForOwner('superGameReward2', $super);
            self::$gameReward3  = GameRewardTestHelper::createGameRewardByNameForOwner('superGameReward3', $super);
            self::$gameReward4  = GameRewardTestHelper::createGameRewardByNameForOwner('superGameReward4', $super);
            self::$gameReward5  = GameRewardTestHelper::createGameRewardByNameForOwner('superGameReward5', $super);
            self::$gameReward6  = GameRewardTestHelper::createGameRewardByNameForOwner('superGameReward6', $super);
            self::$gameReward7  = GameRewardTestHelper::createGameRewardByNameForOwner('superGameReward7', $super);
            self::$gameReward8  = GameRewardTestHelper::createGameRewardByNameForOwner('superGameReward8', $super);
            self::$gameReward9  = GameRewardTestHelper::createGameRewardByNameForOwner('superGameReward9', $super);
            self::$gameReward10 = GameRewardTestHelper::createGameRewardByNameForOwner('superGameReward10', $super);
            self::$gameReward11 = GameRewardTestHelper::createGameRewardByNameForOwner('superGameReward11', $super);
            self::$gameReward12 = GameRewardTestHelper::createGameRewardByNameForOwner('superGameReward12', $super);
            //Setup default dashboard.
            Dashboard::getByLayoutIdAndUser(Dashboard::DEFAULT_USER_LAYOUT_ID, $super);
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default');
            $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default/create');

            $content = $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default/list');
            $this->assertContains('anyMixedAttributes', $content);
            //Test the search or paging of the listview.
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $this->setGetArray(array('ajax' => 'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default/list');
            $this->assertNotContains('anyMixedAttributes', $content);
            $this->resetGetArray();

            //Default Controller actions requiring some sort of parameter via POST or GET
            //Load Model Edit Views
            $this->setGetArray(array('id' => self::$gameReward1->id));
            $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default/edit');
            //Save game rewards
            $this->assertEquals(null, self::$gameReward1->description);
            $this->setPostArray(array('GameReward' => array('description' => '555')));
            //Make sure the redirect is to the details view and not the list view.
            $this->runControllerWithRedirectExceptionAndGetContent('gameRewards/default/edit',
            Yii::app()->createUrl('gameRewards/default/details', array('id' => self::$gameReward1->id)));
            $gameReward1 = GameReward::getById(self::$gameReward1->id);
            $this->assertEquals('555', $gameReward1->description);
            //Test having a failed validation on the game rewards during save.
            $this->setGetArray (array('id'      => self::$gameReward1->id));
            $this->setPostArray(array('GameReward' => array('name' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default/edit');
            $this->assertContains('Name cannot be blank', $content);

            //Load Model Detail Views
            $this->setGetArray(array('id' => self::$gameReward1->id, 'lockPortlets' => '1'));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default/details');

            //Load Model MassEdit Views.
            //MassEdit view for single selected ids
            $this->setGetArray(array('selectedIds' => '4,5,6,7,8', 'selectAll' => ''));  // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default/massEdit');
            $this->assertContains('<strong>5</strong>&#160;records selected for updating', $content);

            //MassEdit view for all result selected ids
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default/massEdit');
            $this->assertContains('<strong>12</strong>&#160;records selected for updating', $content);

            //save Model MassEdit for selected Ids
            //Test that the 2 game rewards do not have the office phone number we are populating them with.
            $gameReward1 = GameReward::getById(self::$gameReward1->id);
            $gameReward2 = GameReward::getById(self::$gameReward2->id);
            $this->assertNotEquals('456765421', $gameReward1->description);
            $this->assertNotEquals('456765421', $gameReward2->description);
            $this->setGetArray(array(
                'selectedIds' => self::$gameReward1->id . ',' . self::$gameReward2->id, // Not Coding Standard
                'selectAll' => '',
                'GameReward_page' => 1));
            $this->setPostArray(array(
                'GameReward'  => array('description' => '2222'),
                'MassEdit' => array('description' => 1)
            ));
            $this->runControllerWithRedirectExceptionAndGetContent('gameRewards/default/massEdit');
            //Test that the 2 game rewards have the new office phone number and the other game rewards do not.
            $gameReward1  = GameReward::getById(self::$gameReward1->id);
            $gameReward2  = GameReward::getById(self::$gameReward2->id);
            $gameReward3  = GameReward::getById(self::$gameReward3->id);
            $gameReward4  = GameReward::getById(self::$gameReward4->id);
            $gameReward5  = GameReward::getById(self::$gameReward5->id);
            $gameReward6  = GameReward::getById(self::$gameReward6->id);
            $gameReward7  = GameReward::getById(self::$gameReward7->id);
            $gameReward8  = GameReward::getById(self::$gameReward8->id);
            $gameReward9  = GameReward::getById(self::$gameReward9->id);
            $gameReward10 = GameReward::getById(self::$gameReward10->id);
            $gameReward11 = GameReward::getById(self::$gameReward11->id);
            $gameReward12 = GameReward::getById(self::$gameReward12->id);
            $this->assertEquals  ('2222',      $gameReward1->description);
            $this->assertEquals  ('2222',      $gameReward2->description);
            $this->assertEmpty   ($gameReward3->description);
            $this->assertEmpty   ($gameReward4->description);
            $this->assertEmpty   ($gameReward5->description);
            $this->assertEmpty   ($gameReward6->description);
            $this->assertEmpty   ($gameReward7->description);
            $this->assertEmpty   ($gameReward8->description);
            $this->assertEmpty   ($gameReward9->description);
            $this->assertEmpty   ($gameReward10->description);
            $this->assertEmpty   ($gameReward11->description);
            $this->assertEmpty   ($gameReward12->description);

            //save Model MassEdit for entire search result
            $this->setGetArray(array(
                'selectAll' => '1',
                'GameReward_page' => 1));
            $this->setPostArray(array(
                'GameReward'  => array('description' => '3333'),
                'MassEdit'    => array('description' => 1)
            ));
            $pageSize = Yii::app()->pagination->getForCurrentUserByType('massEditProgressPageSize');
            $this->assertEquals(5, $pageSize);
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', 20);
            $this->runControllerWithRedirectExceptionAndGetContent('gameRewards/default/massEdit');
            Yii::app()->pagination->setForCurrentUserByType('massEditProgressPageSize', $pageSize);
            //Test that all game rewards have the new phone number.
            $gameReward1  = GameReward::getById(self::$gameReward1->id);
            $gameReward2  = GameReward::getById(self::$gameReward2->id);
            $gameReward3  = GameReward::getById(self::$gameReward3->id);
            $gameReward4  = GameReward::getById(self::$gameReward4->id);
            $gameReward5  = GameReward::getById(self::$gameReward5->id);
            $gameReward6  = GameReward::getById(self::$gameReward6->id);
            $gameReward7  = GameReward::getById(self::$gameReward7->id);
            $gameReward8  = GameReward::getById(self::$gameReward8->id);
            $gameReward9  = GameReward::getById(self::$gameReward9->id);
            $gameReward10 = GameReward::getById(self::$gameReward10->id);
            $gameReward11 = GameReward::getById(self::$gameReward11->id);
            $gameReward12 = GameReward::getById(self::$gameReward12->id);
            $this->assertEquals   ('3333', $gameReward1->description);
            $this->assertEquals   ('3333', $gameReward2->description);
            $this->assertEquals   ('3333', $gameReward3->description);
            $this->assertEquals   ('3333', $gameReward4->description);
            $this->assertEquals   ('3333', $gameReward5->description);
            $this->assertEquals   ('3333', $gameReward6->description);
            $this->assertEquals   ('3333', $gameReward7->description);
            $this->assertEquals   ('3333', $gameReward8->description);
            $this->assertEquals   ('3333', $gameReward9->description);
            $this->assertEquals   ('3333', $gameReward10->description);
            $this->assertEquals   ('3333', $gameReward11->description);
            $this->assertEquals   ('3333', $gameReward12->description);

            //Autocomplete for GameReward
            $this->setGetArray(array('term' => 'super'));
            $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default/autoComplete');

            //actionModalList
            $this->setGetArray(array(
                'modalTransferInformation' => array('sourceIdFieldId' => 'x', 'sourceNameFieldId' => 'y', 'modalId' => 'z')
            ));
            $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default/modalList');

            //actionAuditEventsModalList
            $this->setGetArray(array('id' => $gameReward1->id));
            $this->resetPostArray();
            $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default/auditEventsModalList');
        }

        /**
         * @depends testSuperUserAllDefaultControllerActions
         */
        public function testSuperUserDefaultPortletControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Save a layout change. Collapse all portlets in the GameReward Details View.
            //At this point portlets for this view should be created because we have already loaded the 'details' page in a request above.
            $portlets = Portlet::getByLayoutIdAndUserSortedByColumnIdAndPosition(
                                    'GameRewardDetailsAndRelationsView', $super->id, array());
            $this->assertEquals (2, count($portlets[1]));
            $this->assertFalse  (array_key_exists(2, $portlets) );
            $portletPostData = array();
            $portletCount = 0;
            foreach ($portlets as $column => $columnPortlets)
            {
                foreach ($columnPortlets as $position => $portlet)
                {
                    $this->assertEquals('0', $portlet->collapsed);
                    $portletPostData['GameRewardDetailsAndRelationsView_' . $portlet->id] = array(
                        'collapsed' => 'true',
                        'column'    => 0,
                        'id'        => 'GameRewardDetailsAndRelationsView_' . $portlet->id,
                        'position'  => $portletCount,
                    );
                    $portletCount++;
                }
            }
            //There should have been a total of 2 portlets.
            $this->assertEquals(2, $portletCount);
        }

        /**
         * @depends testSuperUserDefaultPortletControllerActions
         */
        public function testSuperUserDeleteAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Delete an game reward.
            $this->setGetArray(array('id' => self::$gameReward4->id));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('gameRewards/default/delete');
            $gameRewards = GameReward::getAll();
            $this->assertEquals(11, count($gameRewards));
            try
            {
                GameReward::getById(self::$gameReward4->id);
                $this->fail();
            }
            catch (NotFoundException $e)
            {
                //success
            }
        }

        /**
         * @depends testSuperUserDeleteAction
         */
        public function testSuperUserCreateAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            //Create a new game reward.
            $this->resetGetArray();
            $this->setPostArray(array('GameReward' => array(
                                        'name'        => 'myNewGameReward',
                                        'description' => '456765421',
                                        'cost'        => 5,
                                        'quantity'    => 10)));
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('gameRewards/default/create');
            $gameRewards = GameReward::getByName('myNewGameReward');
            $gameRewardTransaction1 = new GameRewardTransaction();
            $gameRewardTransaction1->person   = $super;
            $gameRewardTransaction1->quantity = 5;
            $gameRewardTransaction2 = new GameRewardTransaction();
            $gameRewardTransaction2->person   = $super;
            $gameRewardTransaction2->quantity = 5;
            $gameRewards[0]->transactions->add($gameRewardTransaction1);
            $gameRewards[0]->transactions->add($gameRewardTransaction2);
            $this->assertTrue($gameRewards[0]->save());
            $this->assertEquals(1, count($gameRewards));
            $this->assertTrue  ($gameRewards[0]->id > 0);
            $compareRedirectUrl = Yii::app()->createUrl('gameRewards/default/details', array('id' => $gameRewards[0]->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $this->assertEquals('456765421', $gameRewards[0]->description);
            $this->assertTrue  ($gameRewards[0]->owner == $super);
            $gameRewards = GameReward::getAll();
            $this->assertEquals(12, count($gameRewards));
        }

        /**
         * @depends testSuperUserCreateAction
         */
        public function testSuperUserCopyAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $gameRewards = GameReward::getAll();
            $this->assertEquals(12, count($gameRewards));
            $gameRewards = GameReward::getByName('myNewGameReward');

            $postArray = array(
               'GameReward' => array(
                    'quantity'  => 23,
                    'cost'      => 22,
                )
            );

            $this->updateModelValuesFromPostArray($gameRewards[0], $postArray);
            $this->assertModelHasValuesFromPostArray($gameRewards[0], $postArray);

            $this->assertTrue($gameRewards[0]->save());

            $this->assertTrue(
                $this->checkCopyActionResponseAttributeValuesFromPostArray($gameRewards[0], $postArray)
            );

            $postArray['GameReward']['name']        = 'myClonedGameReward';
            $postArray['GameReward']['description'] = 'Cloned description';
            $this->setGetArray(array('id' => $gameRewards[0]->id));
            $this->setPostArray($postArray);
            $this->runControllerWithRedirectExceptionAndGetUrl('gameRewards/default/copy');

            $gameRewards = GameReward::getByName('myClonedGameReward');
            $this->assertEquals(1, count($gameRewards));
            $this->assertTrue  ($gameRewards[0]->owner->isSame($super));
            $this->assertModelHasValuesFromPostArray($gameRewards[0], $postArray);
            $gameRewards = GameReward::getAll();
            $this->assertEquals(13, count($gameRewards));
        }

        /**
         * @depends testSuperUserCreateAction
         */
        public function testStickySearchActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            StickySearchUtil::clearDataByKey('GameRewardsSearchView');
            $value = StickySearchUtil::getDataByKey('GameRewardsSearchView');
            $this->assertNull($value);

            //Sort order desc
            $this->setGetArray(array('GameRewardsSearchForm' => array('anyMixedAttributes'                 => 'xyz',
                                                                   SearchForm::SELECTED_LIST_ATTRIBUTES => array('quantity', 'name')),
                                     'GameReward_sort'       => 'name.desc'));

            $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default/');
            $data = StickySearchUtil::getDataByKey('GameRewardsSearchView');
            $compareData = array('dynamicClauses'                     => array(),
                                 'dynamicStructure'                   => null,
                                 'anyMixedAttributes'                 => 'xyz',
                                 'anyMixedAttributesScope'            => null,
                                 SearchForm::SELECTED_LIST_ATTRIBUTES => array('quantity', 'name'),
                                 'sortAttribute'                      => 'name',
                                 'sortDescending'                     => true
            );
            $this->assertEquals($compareData, $data);

            //Sort order asc
            StickySearchUtil::clearDataByKey('GameRewardsSearchView');
            $this->setGetArray(array('GameRewardsSearchForm' => array('anyMixedAttributes'                 => 'xyz',
                                                                   SearchForm::SELECTED_LIST_ATTRIBUTES => array('quantity', 'name')),
                                     'GameReward_sort'       => 'quantity'));

            $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default/');
            $data = StickySearchUtil::getDataByKey('GameRewardsSearchView');
            $compareData = array('dynamicClauses'                     => array(),
                                 'dynamicStructure'                   => null,
                                 'anyMixedAttributes'                 => 'xyz',
                                 'anyMixedAttributesScope'            => null,
                                 SearchForm::SELECTED_LIST_ATTRIBUTES => array('quantity', 'name'),
                                 'sortAttribute'                      => 'quantity',
                                 'sortDescending'                     => false
            );
            $this->assertEquals($compareData, $data);

            $this->setGetArray(array('clearingSearch' => true));
            $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default');
            $data = StickySearchUtil::getDataByKey('GameRewardsSearchView');
            $compareData = array('dynamicClauses'                     => array(),
                                 'dynamicStructure'                   => null,
                                 'anyMixedAttributesScope'            => null,
                                 SearchForm::SELECTED_LIST_ATTRIBUTES => array('name', 'cost', 'quantity')
            );
            $this->assertEquals($compareData, $data);
        }

        /**
         * @deletes selected gameRewards.
         */
        public function testMassDeleteActionsForSelectedIds()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //MassDelete for selected Record Count
            $gameRewards = GameReward::getAll();
            $this->assertEquals(13, count($gameRewards));

            //Load Model MassDelete Views.
            //MassDelete view for single selected ids
            $this->setGetArray(array('selectedIds' => '5,6,7,8', 'selectAll' => '', ));  // Not Coding Standard
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default/massDelete');
            $this->assertContains('<strong>4</strong>&#160;Game Rewards selected for removal', $content);

            //MassDelete view for all result selected ids
            $this->setGetArray(array('selectAll' => '1'));
            $this->resetPostArray();
            $content = $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default/massDelete');
            $this->assertContains('<strong>13</strong>&#160;Game Rewards selected for removal', $content);
            //MassDelete for selected ids
            $this->setGetArray(array(
                'selectedIds' => implode(',', array(self::$gameReward2->id,self::$gameReward3->id,self::$gameReward12->id)), // Not Coding Standard
                'selectAll' => '',
                'GameReward_page' => 1));
            $this->setPostArray(array('selectedRecordCount' => 3));
            $this->runControllerWithRedirectExceptionAndGetContent('gameRewards/default/massDelete');

            //MassDelete for selected Record Count
            $gameRewards = GameReward::getAll();
            $this->assertEquals(10, count($gameRewards));
        }

        public function testRedeemList()
        {
            $super      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $content    = $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default/redeemList');
            $this->assertContains('<h4 class="reward-name">myClonedGameReward</h4>', $content);
            //Test the search or paging of the listview.
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $this->setGetArray(array('ajax' => 'list-view'));
            $content    = $this->runControllerWithNoExceptionsAndGetContent('gameRewards/default/redeemList');
            $this->assertNotContains('anyMixedAttributes', $content);
            $this->resetGetArray();
        }

        public function testRedeemReward()
        {
            $super                      = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            Yii::app()->user->userModel = $super;
            $gameRewards                = GameReward::getByName('myNewGameReward');

            //not enough coins
            $this->setGetArray(array('id' => $gameRewards[0]->id));
            $content = $this->runControllerWithExitExceptionAndGetContent('gameRewards/default/redeemReward');
            $this->assertContains('You do not have enough coins to redeem this reward', $content);

            //enough coins
            $gameCoin           = new GameCoin();
            $gameCoin->person   = $super;
            $gameCoin->value    = 100;
            $this->assertTrue($gameCoin->save());
            $notifications      = Notification::getAll();

            //check for no notification
            $this->assertEquals(0, count($notifications));
            $this->setGetArray(array('id' => $gameRewards[0]->id));
            $content = $this->runControllerWithExitExceptionAndGetContent('gameRewards/default/redeemReward');
            $this->assertContains('myNewGameReward has been redeemed.', $content);

            //check for notification
            $notifications              = Notification::getAll();
            $this->assertEquals(1, count($notifications));

            //email content
            $this->assertContains('myNewGameReward was redeemed by Clark Kent.', $notifications[0]->notificationMessage->htmlContent);

            //check url
            $this->assertContains('/gameRewards/default/details?id=13', $notifications[0]->notificationMessage->htmlContent); // Not Coding Standard
        }
    }
?>
