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
     * Class that builds demo game rewards and transactions.
     */
    class GameRewardsDemoDataMaker extends DemoDataMaker
    {
        public static function getDependencies()
        {
            return array('users');
        }

        public function makeAll(& $demoDataHelper)
        {
            assert('$demoDataHelper instanceof DemoDataHelper');
            assert('$demoDataHelper->isSetRange("User")');

            $gameRewards = array();
            $gameRewardRandomData = ZurmoRandomDataUtil::getRandomDataByModuleAndModelClassNames('GameRewardsModule', 'GameReward');
            for ($i = 0; $i < 10; $i++)
            {
                $gameReward           = new GameReward();
                $gameReward->name     = $gameRewardRandomData['names'][$i];
                $gameReward->owner    = $demoDataHelper->getRandomByModelName('User');
                $gameReward->cost     = mt_rand(1, 10);
                $gameReward->quantity = mt_rand(1, 20);

                for ($j = 0; $j < 5; $j++)
                {
                    $gameRewardTransaction           = new GameRewardTransaction();
                    $gameRewardTransaction->person   = $demoDataHelper->getRandomByModelName('User');
                    $gameRewardTransaction->quantity = mt_rand(1, 3);
                    $gameReward->transactions->add($gameRewardTransaction);
                }
                $gameReward->addPermissions(Group::getByName(Group::EVERYONE_GROUP_NAME), Permission::READ_WRITE_CHANGE_PERMISSIONS_CHANGE_OWNER);
                $saved = $gameReward->save();
                if (!$saved)
                {
                    throw new FailedToSaveModelException();
                }
                $gameReward = GameReward::getById($gameReward->id);
                AllPermissionsOptimizationUtil::
                    securableItemGivenPermissionsForGroup($gameReward, Group::getByName(Group::EVERYONE_GROUP_NAME));
                $gameReward->save();
                $gameRewards[] = $gameReward->id;
            }
            $demoDataHelper->setRangeByModelName('GameReward', $gameRewards[0], $gameRewards[count($gameRewards)-1]);
        }
    }
?>