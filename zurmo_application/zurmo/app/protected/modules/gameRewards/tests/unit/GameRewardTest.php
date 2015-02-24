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

    class GameRewardTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateAndGetGameRewardById()
        {
            $user = UserTestHelper::createBasicUser('Steven');
            $gameReward                     = new GameReward();
            $gameReward->owner              = $user;
            $gameReward->cost               = 5;
            $gameReward->description        = 'A cool gift card to reward you for your work';
            $gameReward->expirationDateTime = '2025-05-05 04:00:00';
            $gameReward->name               = '50 dollar giftcard to somewhere';
            $gameReward->quantity           = 3;
            $this->assertTrue($gameReward->save());
            $id = $gameReward->id;
            unset($gameReward);
            $gameReward = GameReward::getById($id);
            $this->assertEquals($user->id,  $gameReward->owner->id);
            $this->assertEquals(5,          $gameReward->cost);
            $this->assertEquals('A cool gift card to reward you for your work', $gameReward->description);
            $this->assertEquals('2025-05-05 04:00:00',             $gameReward->expirationDateTime);
            $this->assertEquals('50 dollar giftcard to somewhere', $gameReward->name);
            $this->assertEquals(3,          $gameReward->quantity);
        }

        /**
         * @depends testCreateAndGetGameRewardById
         */
        public function testGameTransaction()
        {
            $sally = UserTestHelper::createBasicUser('Sally');
            $gameRewards = GameReward::getAll();
            $this->assertEquals(1, count($gameRewards));

            $gameRewardTransaction = new GameRewardTransaction();
            $gameRewardTransaction->quantity = 1;
            $gameRewardTransaction->person = $sally;

            $gameRewards[0]->transactions->add($gameRewardTransaction);
            $this->assertTrue($gameRewards[0]->save());
            $id = $gameRewards[0]->id;
            unset($gameRewards);
            $gameReward = GameReward::getById($id);
            $this->assertEquals(1, count($gameReward->transactions));
            $this->assertEquals(1, $gameReward->transactions[0]->quantity);
            $this->assertEquals($sally->getClassId('Item'), $gameReward->transactions[0]->person->getClassId('Item'));
        }

        /**
         * @depends testCreateAndGetGameRewardById
         */
        public function testGetGameRewardsByName()
        {
            $gameReward = GameReward::getByName('50 dollar giftcard to somewhere');
            $this->assertEquals(1, count($gameReward));
            $this->assertEquals('50 dollar giftcard to somewhere', $gameReward[0]->name);
        }

        /**
         * @depends testCreateAndGetGameRewardById
         */
        public function testGetLabel()
        {
            $gameReward = GameReward::getByName('50 dollar giftcard to somewhere');
            $this->assertEquals(1, count($gameReward));
            $this->assertEquals('Game Reward',  $gameReward[0]::getModelLabelByTypeAndLanguage('Singular'));
            $this->assertEquals('Game Rewards', $gameReward[0]::getModelLabelByTypeAndLanguage('Plural'));
        }

        /**
         * @depends testGetGameRewardsByName
         */
        public function testGetGameRewardsByNameForNonExistentName()
        {
            $gameReward = GameReward::getByName('59 dollar giftcard to somewhere');
            $this->assertEquals(0, count($gameReward));
        }

        /**
         * @depends testCreateAndGetGameRewardById
         */
        public function testSetAndGetOwner()
        {
            $user = UserTestHelper::createBasicUser('Dicky');

            $gameReward = GameReward::getByName('50 dollar giftcard to somewhere');
            $this->assertEquals(1, count($gameReward));
            $gameReward = $gameReward[0];
            $gameReward->owner = $user;
            $saved = $gameReward->save();
            $this->assertTrue($saved);
            unset($user);
            $this->assertTrue($gameReward->owner->id > 0);
            $user = $gameReward->owner;
            $gameReward->owner = null;
            $this->assertNotNull($gameReward->owner);
            $this->assertFalse($gameReward->validate());
            $gameReward->forget();
        }

        /**
         * @depends testSetAndGetOwner
         */
        public function testReplaceOwner()
        {
            $gameRewards = GameReward::getByName('50 dollar giftcard to somewhere');
            $this->assertEquals(1, count($gameRewards));
            $gameReward = $gameRewards[0];
            $user = User::getByUsername('dicky');
            $this->assertEquals($user->id, $gameReward->owner->id);
            unset($user);
            $gameReward->owner = User::getByUsername('sally');
            $this->assertTrue($gameReward->owner !== null);
            $user = $gameReward->owner;
            $this->assertEquals('sally', $user->username);
            unset($user);
        }

        /**
         * @depends testCreateAndGetGameRewardById
         */
        public function testUpdateGameRewardFromForm()
        {
            $gameRewards = GameReward::getByName('50 dollar giftcard to somewhere');
            $gameReward = $gameRewards[0];
            $this->assertEquals($gameReward->name, '50 dollar giftcard to somewhere');
            $postData = array('name' => 'New Name');
            $gameReward->setAttributes($postData);
            $this->assertTrue($gameReward->save());

            $id = $gameReward->id;
            unset($gameReward);
            $gameReward = GameReward::getById($id);
            $this->assertEquals('New Name', $gameReward->name);
        }
    }
?>
