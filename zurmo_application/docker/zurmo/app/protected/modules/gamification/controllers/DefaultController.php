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

    class GamificationDefaultController extends ZurmoModuleController
    {
        public function actionIndex()
        {
            $this->actionLeaderboard();
        }

        public function actionLeaderboard($type = null)
        {
            if ($type == null)
            {
                $type = GamePointUtil::LEADERBOARD_TYPE_WEEKLY;
            }
            if ($type == GamePointUtil::LEADERBOARD_TYPE_WEEKLY)
            {
                $activeActionElementType = 'LeaderboardWeeklyMenu';
            }
            elseif ($type == GamePointUtil::LEADERBOARD_TYPE_MONTHLY)
            {
                $activeActionElementType = 'LeaderboardMonthlyMenu';
            }
            elseif ($type == GamePointUtil::LEADERBOARD_TYPE_OVERALL)
            {
                $activeActionElementType = 'LeaderboardOverallMenu';
            }
            else
            {
                throw new NotSupportedException();
            }
            $metadata = array(); //can put the typing information here easily. from the type.
            $pageSize                         = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                                'listPageSize', get_class($this->getModule()));
            $gameLevel                        = new GameLevel(false);
            $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider( $metadata,
                                                                            get_class($gameLevel),
                                                                            'LeaderboardDataProvider',
                                                                            'notUsed',
                                                                            true,
                                                                            $pageSize);
            $dataProvider->setType($type);
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $listView            = new LeaderboardListView($this->getId(), $this->getModule()->getId(),
                                                               get_class($gameLevel), $dataProvider, array());
                $view = new AccountsPageView($listView);
            }
            else
            {
                $mixedView = new LeaderboardActionBarAndListView(
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $gameLevel,
                                    'GamificationModule',
                                    $dataProvider,
                                    $activeActionElementType);
                $view = new AccountsPageView(ZurmoDefaultViewUtil::
                            makeStandardViewForCurrentUser($this, $mixedView));
            }
            echo $view->render();
        }

        public function actionCollectRandomCoin()
        {
            $gameCoin = GameCoin::resolveByPerson(Yii::app()->user->userModel);
            $gameCoin->addValue(1);
            $saved = $gameCoin->save();
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
        }

        public function actionRedeemCollection($id)
        {
            $gameCollection = GameCollection::getById((int)$id);
            if ($gameCollection->person->getClassId('Item') != Yii::app()->user->userModel->getClassId('Item'))
            {
                throw new NotSupportedException();
            }
            if ($gameCollection->redeem())
            {
                $gameCollectionRules = GameCollectionRulesFactory::createByType($gameCollection->type);
                $gameCoin = GameCoin::resolveByPerson(Yii::app()->user->userModel);
                $gameCoin->addValue($gameCollectionRules::getCoinRedemptionValue());
                $saved = $gameCoin->save();
                if (!$saved)
                {
                    throw new FailedToSaveModelException();
                }
            }
            echo UserGameDashboardView::renderCollectionContent(Yii::app()->user->userModel, $gameCollection);
        }

        public function actionRefreshGameDashboardCoinContainer($id)
        {
            $user     = User::getById((int)$id);
            $gameCoin = GameCoin::resolveByPerson($user);
            echo UserGameDashboardView::renderCoinsContent($gameCoin->value, $user);
        }

        public function actionClaimCollectionItem($key, $typeKey)
        {
            if (Yii::app()->request->isAjaxRequest)
            {
                $availableTypes = GameCollection::getAvailableTypes();
                $collection     = GameCollection::resolveByTypeAndPerson($availableTypes[$typeKey], Yii::app()->user->userModel);
                $itemsData      = $collection->getItemsData();
                $itemsData[$key] = $itemsData[$key] + 1;
                $collection->setItemsData($itemsData);
                if (!$collection->save())
                {
                    throw new FailedToSaveModelException();
                }
            }
        }
    }
?>
