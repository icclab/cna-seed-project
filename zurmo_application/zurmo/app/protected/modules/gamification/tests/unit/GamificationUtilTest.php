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

    class GamificationUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        /**
         * The best we can cover for is making sure the notification is created and it is not marked as critical.
         */
        public function testLogAndNotifyOnDuplicateGameModel()
        {
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $this->assertEquals(0, count(Notification::getAll()));
            GamificationUtil::logAndNotifyOnDuplicateGameModel('some content');
            //It should not send an email because it is non-critical
            $this->assertEquals(0, Yii::app()->emailHelper->getQueuedCount());
            $this->assertEquals(0, Yii::app()->emailHelper->getSentCount());
            $this->assertEquals(1, count(Notification::getAll()));
        }

        public function testFindGameTableRowsThatAreDuplicatedByTypePersonKey()
        {
            $this->assertEmpty(GamificationUtil::findGameTableRowsThatAreDuplicatedByTypePersonKey('gamecollection'));
            $this->assertEmpty(GamificationUtil::findGameTableRowsThatAreDuplicatedByTypePersonKey('gamelevel'));
            $this->assertEmpty(GamificationUtil::findGameTableRowsThatAreDuplicatedByTypePersonKey('gamepoint'));
            $this->assertEmpty(GamificationUtil::findGameTableRowsThatAreDuplicatedByTypePersonKey('gamescore'));
        }

        public function testFindGameTableRowsThatAreDuplicatedByPersonKey()
        {
            $this->assertEmpty(GamificationUtil::findGameTableRowsThatAreDuplicatedByPersonKey('gamecoin'));
        }

        public function testRemoveDuplicatesByModelsNonGameCollection()
        {
            $super    = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $models   = array();
            $gameCoin = new GameCoin();
            $gameCoin->person = $super;
            $gameCoin->value  = 34;
            $gameCoin->save();
            $models[] = $gameCoin;
            $gameCoin2 = new GameCoin();
            $gameCoin2->person = $super;
            $gameCoin2->value  = 56;
            $gameCoin2->save();
            $models[] = $gameCoin2;
            $messageContent = null;
            $this->assertEquals(2, count(GameCoin::getAll()));
            GamificationUtil::removeDuplicatesByModels($models, $messageContent);
            $gameCoins = GameCoin::getAll();
            $this->assertEquals(1, count($gameCoins));
            //Ensure it deleted the smaller value (Bank error in your favor)
            $this->assertEquals(56, $gameCoins[0]->value);
            $this->assertNotNull($messageContent);
        }

        public function testRemoveDuplicatesByModelsGameCollection()
        {
            $super    = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            $models   = array();
            $gameCollection            = new GameCollection();
            $gameCollection->person    = $super;
            $gameCollection->type      = 'Basketball';
            $gameCollection->serializedData = serialize(array('something'));
            $gameCollection->save();
            $models[] = $gameCollection;
            $gameCollection2            = new GameCollection();
            $gameCollection2->person    = $super;
            $gameCollection2->type      = 'Basketball';
            $gameCollection2->serializedData = serialize(array('something2'));
            $gameCollection2->save();
            $models[] = $gameCollection2;
            $messageContent = null;
            $this->assertEquals(2, count(GameCollection::getAll()));
            GamificationUtil::removeDuplicatesByModels($models, $messageContent);
            $gameCollections = GameCollection::getAll();
            $this->assertEquals(1, count($gameCollections));
            $this->assertNotNull($messageContent);
        }
    }
?>