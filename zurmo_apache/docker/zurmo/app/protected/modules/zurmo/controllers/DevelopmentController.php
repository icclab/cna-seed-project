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
     * Controller Class for Development Tools
     *
     */
    class ZurmoDevelopmentController extends ZurmoModuleController
    {
        public function filters()
        {
            return array(
                array(
                    ZurmoBaseController::RIGHTS_FILTER_PATH,
                    'moduleClassName' => 'ZurmoModule',
                    'rightName' => ZurmoModule::RIGHT_ACCESS_GLOBAL_CONFIGURATION,
               ),
            );
        }

        public function actionIndex()
        {
            if (isset($_GET['clearCache']) && $_GET['clearCache'] == 1)
            {
                Yii::app()->user->setFlash('notification', Zurmo::t('ZurmoModule', 'Cache has been successfully cleaned.'));
            }
            if (isset($_GET['resolveCustomData']) && $_GET['resolveCustomData'] == 1)
            {
                Yii::app()->user->setFlash('notification', Zurmo::t('ZurmoModule', 'Custom data updated successfully.'));
            }
            $breadCrumbLinks = array(
                Zurmo::t('ZurmoModule', 'Developer Tools'),
            );
            $view = new ConfigurationPageView(ZurmoDefaultAdminViewUtil::
                            makeViewWithBreadcrumbsForCurrentUser($this, new DevelopmentListView(), $breadCrumbLinks, 'SettingsBreadCrumbView'));
            echo $view->render();
        }

        public function actionCompileCss()
        {
            Yii::app()->lessCompiler->compile();
            Yii::app()->user->setFlash('notification', Zurmo::t('ZurmoModule', 'Less CSS files compiled successfully.'));
            $this->actionIndex();
        }

        public function actionReadMetadata($className)
        {
            if (!Group::isUserASuperAdministrator(Yii::app()->user->userModel))
            {
                throw new NotSupportedException();
            }
            if (GlobalMetadata::isClassMetadataSavedInDatabase($className))
            {
                echo 'The metadata is saved in the database ' . "<BR>";
            }
            else
            {
                echo 'The metadata is not saved in the database ' . "<BR>";
            }
            echo "<pre>";
            print_r($className::getMetadata());
            echo "</pre>";
        }

        /**
         * This is a not so fancy way of doing what actionRebuildSecurityCache is doing. It is not paged
         * and really only for development use until this performance improvement is fully stable.
         * todo: can remove this method at some point in the future.
         * @throws NotSupportedException
         */
        public function actionRebuildAllNamedSecurableActualPermissions()
        {
            if (!Group::isUserASuperAdministrator(Yii::app()->user->userModel))
            {
                throw new NotSupportedException();
            }
            $namedSecurableItems = array();
            $modules             = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                if ($module instanceof SecurableModule)
                {
                    $namedSecurableItems[] = NamedSecurableItem::getByName(get_class($module));
                }
            }
            foreach (User::getAll() as $user)
            {
                if (!$user->isSuperAdministrator() && !$user->isSystemUser)
                {
                    echo 'Processing named securable cache for user: ' . strval($user) . "<BR>";
                    foreach ($namedSecurableItems as $namedSecurableItem)
                    {
                        $namedSecurableItem->getActualPermissions($user);
                        //echo '-processing for module: ' . $namedSecurableItem->name . "<BR>";
                    }
                    echo 'Current memory usage: ' . Yii::app()->performance->getMemoryUsage() . "<BR>";
                }
                else
                {
                    echo 'Skipping adding named securable cache for user: ' . strval($user) . "<BR>";
                }
                if (!$user->isSystemUser)
                {
                    echo 'Processing actual rights cache for user: ' . strval($user) . "<BR>";
                    RightsUtil::cacheAllRightsByPermitable($user);
                    echo 'Current memory usage: ' . Yii::app()->performance->getMemoryUsage() . "<BR>";
                }
                else
                {
                    echo 'Skipping adding actual rights cache for user: ' . strval($user) . "<BR>";
                }
            }
        }

        public function actionRebuildSecurityCache($User_page = 1, $continue = false)
        {
            if (!Group::isUserASuperAdministrator(Yii::app()->user->userModel))
            {
                $failureMessageContent = Zurmo::t('Core', 'You must be a super administrator to rebuild the security cache.');
                $messageView           = new AccessFailureView($failureMessageContent);
                $view                  = new AccessFailurePageView($messageView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
            if ($User_page == 1)
            {
                //to more quickly show the view to the user. To give a better indication of what is happening.
                $pageSize = 1;
            }
            else
            {
                $pageSize = 25;
            }

            $namedSecurableItems = array();
            $modules             = Module::getModuleObjects();
            foreach ($modules as $module)
            {
                if ($module instanceof SecurableModule)
                {
                    $namedSecurableItems[] = NamedSecurableItem::getByName(get_class($module));
                }
            }

            if ($continue)
            {
                $page = static::getMassActionProgressStartFromGet('User_page', $pageSize);
            }
            else
            {
                $page = 1;
            }
            $title = Zurmo::t('ZurmoModule', 'Rebuilding Cache');
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'isSystemUser',
                    'operatorType'         => 'equals',
                    'value'                => 0,
                ),
                2 => array(
                    'attributeName'        => 'isSystemUser',
                    'operatorType'         => 'isNull',
                    'value'                => null,
                )
            );
            $searchAttributeData['structure'] = '1 or 2';
            $dataProvider = RedBeanModelDataProviderUtil::
                                makeDataProvider($searchAttributeData, 'User', 'RedBeanModelDataProvider', null, false, $pageSize);
            $selectedRecordCount = $dataProvider->getTotalItemCount();
            $users = $dataProvider->getData();
            foreach ($users as $user)
            {
                if (!$user->isSuperAdministrator())
                {
                    foreach ($namedSecurableItems as $namedSecurableItem)
                    {
                        $namedSecurableItem->getActualPermissions($user);
                    }
                }
                RightsUtil::cacheAllRightsByPermitable($user);
            }
            $rebuildView = new RebuildSecurityCacheProgressView(
                            $this->getId(),
                            $this->getModule()->getId(),
                            new User(),
                            $selectedRecordCount,
                            $page,
                            $pageSize,
                            $User_page,
                            'rebuildSecurityCache',
                            $title
            );
            if (!$continue)
            {
                $view = new ZurmoPageView(ZurmoDefaultAdminViewUtil::
                    makeStandardViewForCurrentUser($this, $rebuildView));
                echo $view->render();
                Yii::app()->end(0, false);
            }
            else
            {
                echo $rebuildView->renderRefreshJSONScript();
            }
        }

        /**
         * @see GamificationUtil::logAndNotifyOnDuplicateGameModel($logContent for an explanation of why you would
         * use this method.
         */
        public function actionRepairGamification()
        {
            $duplicateModelsData                   = array();
            //Check GameCoin for duplication person models
            $gameCoinDuplicateData                = GamificationUtil::findGameTableRowsThatAreDuplicatedByPersonKey(GameCoin::getTableName());
            $duplicateModelsData['GameCoin']      = $gameCoinDuplicateData;
            //Check GameCollection, GameLevel, GamePoint, and GameScore for duplicate type/person models

            $gameCollectionDuplicateData           = GamificationUtil::findGameTableRowsThatAreDuplicatedByTypePersonKey(GameCollection::getTableName());
            $duplicateModelsData['GameCollection'] = $gameCollectionDuplicateData;
            $gameLevelDuplicateData                = GamificationUtil::findGameTableRowsThatAreDuplicatedByTypePersonKey(GameLevel::getTableName());
            $duplicateModelsData['GameLevel']      = $gameLevelDuplicateData;
            $gamePointDuplicateData                = GamificationUtil::findGameTableRowsThatAreDuplicatedByTypePersonKey(GamePoint::getTableName());
            $duplicateModelsData['GamePoint']      = $gamePointDuplicateData;
            $gameScoreDuplicateData                = GamificationUtil::findGameTableRowsThatAreDuplicatedByTypePersonKey(GameScore::getTableName());
            $duplicateModelsData['GameScore']      = $gameScoreDuplicateData;
            foreach ($duplicateModelsData as $modelClassName => $duplicatesData)
            {
                if (empty($duplicatesData))
                {
                    echo 'No duplicates found for ' . $modelClassName . "<BR>";
                }
                else
                {
                    echo 'Duplicates discovered for ' . $modelClassName . "<BR>";
                    foreach ($duplicatesData as $typePersonKeyDuplicateData)
                    {
                        $searchAttributeData = array();
                        if ($modelClassName == 'GameCoin')
                        {
                            $searchAttributeData['clauses'] = array(
                                1 => array(
                                    'attributeName'        => 'person',
                                    'relatedAttributeName' => 'id',
                                    'operatorType'         => 'equals',
                                    'value'                => $typePersonKeyDuplicateData['person_item_id'],
                                ),
                            );
                            $searchAttributeData['structure'] = '1';
                        }
                        else
                        {
                            $searchAttributeData['clauses'] = array(
                                1 => array(
                                    'attributeName'        => 'type',
                                    'operatorType'         => 'equals',
                                    'value'                => $typePersonKeyDuplicateData['type'],
                                ),
                                2 => array(
                                    'attributeName'        => 'person',
                                    'relatedAttributeName' => 'id',
                                    'operatorType'         => 'equals',
                                    'value'                => $typePersonKeyDuplicateData['person_item_id'],
                                ),
                            );
                            $searchAttributeData['structure'] = '1 and 2';
                        }
                        $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
                        $where  = RedBeanModelDataProvider::makeWhere($modelClassName, $searchAttributeData, $joinTablesAdapter);
                        $models = $modelClassName::getSubset($joinTablesAdapter, null, null, $where, null);
                        if ($modelClassName == 'GameCoin')
                        {
                            echo $modelClassName . ' --- Quantity of duplicates: ' . count($models) .
                                ' --- for person_item_id: ' . $typePersonKeyDuplicateData['person_item_id'] . "<BR>";
                        }
                        else
                        {
                            echo $modelClassName . ' --- Quantity of duplicates: ' . count($models) .
                                ' --- for person_item_id: ' . $typePersonKeyDuplicateData['person_item_id'] .
                                ' with type: ' . $typePersonKeyDuplicateData['type'] . "<BR>";
                        }
                        $messageContent = null;
                        GamificationUtil::removeDuplicatesByModels($models, $messageContent);
                        echo $messageContent;
                    }
                }
            }
            echo "<BR>" . 'Repair complete.' . "<BR>";
        }
    }
?>