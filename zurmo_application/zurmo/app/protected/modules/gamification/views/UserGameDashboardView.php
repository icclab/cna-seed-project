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
     * Class for displaying a user's game dashboard
     */
    class UserGameDashboardView extends View
    {
        const COLLECTION_CONTAINER_ID_PREFIX  = 'game-collection-container';

        const COMPLETE_COLLECTION_LINK_PREFIX = 'complete-collection-link';

        protected $controller;

        protected $user;

        protected $generalLevelData;

        protected $badgeData;

        protected $rankingData;

        protected $statisticsData;

        public static function renderCollectionContent(User $user, GameCollection $collection)
        {
            $gameCollectionRules  = GameCollectionRulesFactory::createByType($collection->type);

            $collectionImageUrl   = Yii::app()->themeManager->baseUrl . '/default/images/collections/' .
                                    $gameCollectionRules::getType() . '/' .
                                    $gameCollectionRules::makeLargeCollectionImageName();
            $collectionBadgeImage = static::resolveLazyLoadImage($collectionImageUrl);
            $content  = ZurmoHtml::tag('div', array('class' => 'collection-badge'), $collectionBadgeImage);
            $content .= ZurmoHtml::tag('h3', array(), $gameCollectionRules->getCollectionLabel() . ' ' .
                                       Zurmo::t('GamificationModule', 'Collection'));
            $content .= static::renderCollectionItemsContent($user, $collection, $gameCollectionRules);
            $content  = ZurmoHtml::tag('div', array(), $content);
            if ($collection->canRedeem() && $user->id == Yii::app()->user->userModel->id)
            {
                $extraClass = ' redeemable';
            }
            else
            {
                $extraClass = null;
            }
            return ZurmoHtml::tag('div', array('id'    => static::getCollectionContainerId($collection->id),
                                               'class' => 'gd-collection-panel clearfix'. $extraClass), $content);
        }

        public static function renderCoinsContent($coinValue, User $user)
        {
            $url  = Yii::app()->createUrl('gameRewards/default/redeemList/');
            $content  = ZurmoHtml::tag('span', array('id' => 'gd-z-coin'), '');
            $content .= ZurmoHtml::tag('h3', array(), Zurmo::t('GamificationModule', '{n} coin|{n} coins',
                array($coinValue)));
            if($user->id == Yii::app()->user->userModel->id)
            {
                $content .= ZurmoHtml::link(Zurmo::t('ZurmoModule', 'Redeem'), $url);
            }
            return      ZurmoHtml::tag('div', array('id' => self::getGameCoinContainerId()), $content);
        }

        /**
         * @param CController $controller
         * @param User $user
         * @param array $generalLevelData
         * @param array $badgeData
         * @param array $rankingData
         * @param array $statisticsData
         * @param array $collectionData
         */
        public function __construct(CController $controller, User $user, array $generalLevelData, array $badgeData,
                                    array $rankingData, array $statisticsData, array $collectionData)
        {
            $this->controller       = $controller;
            $this->user             = $user;
            $this->generalLevelData = $generalLevelData;
            $this->badgeData        = $badgeData;
            $this->rankingData      = $rankingData;
            $this->statisticsData   = $statisticsData;
            $this->collectionData   = $collectionData;
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        protected function renderContent()
        {
            $this->registerScripts();
            return $this->renderDashboardContent();
        }

        protected function registerScripts()
        {
            $this->registerCoreScriptAndPublishToAssets();
            $this->registerCloseButtonScript();
            $this->registerLazyLoadImagesScript();
        }

        protected function registerCoreScriptAndPublishToAssets()
        {
            Yii::app()->clientScript->registerCoreScript('gamification-dashboard');
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.modules.gamification.views.assets')
                ) . '/gamification-dashboard.js',
                CClientScript::POS_END
            );
        }

        protected function registerCloseButtonScript()
        {
            // Begin Not Coding Standard
            $script = "$('.close-dashboard-button a').on('click', function(){
                           if($('#UserGameDashboardView').length){
                               closeGamificationDashboard();
                               return false;
                           }
                       });
                       $('#gd-overlay, #gd-container, #gd-centralizer').on('click', function(event){
                           if(this === event.target && $('#UserGameDashboardView').length){
                               closeGamificationDashboard();
                               return false;
                           }
                       });";
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('closeGameficationDashboardScript', $script);
        }

        protected function registerLazyLoadImagesScript()
        {
            $dummyImageUrl = static::getDummyImageUrl();
            $script = <<<SPT
// we only monitor nav-right because the ones on left would already be loaded.
$('a.nav-button#nav-right[href="#"]').unbind('click.lazyLoadImages').bind('click.lazyLoadImages', function(event)
    {
        updateGamificationImagesSrcForLazyLoading();
    });

    function updateGamificationImagesSrcForLazyLoading()
    {
        $('.visible-panel img[src*="{$dummyImageUrl}"]').each(function()
        {
            var dataSrc = $(this).data('src');
            if (typeof dataSrc !== 'undefined')
            {
                this.src = dataSrc;
            }
        });
    }
    updateGamificationImagesSrcForLazyLoading(); // called on page load to resolve src for the first 4 images.
SPT;
            Yii::app()->clientScript->registerScript('lazyLoadGameficationDashboardImagesScript', $script);
        }

        protected function renderDashboardContent()
        {
            $content  = $this->renderProfileContent();
            $content .= $this->renderBadgesContent();
            $content .= static::renderCoinsContent($this->getGameCoinForUser()->value, $this->user);
            $content .= $this->renderLeaderboardContent();
            $content .= $this->renderStatisticsContent();
            $content .= $this->renderCollectionsContent();
            $content  = ZurmoHtml::tag('div', array('id' => 'game-dashboard', 'class' => 'clearfix'), $content);
            $content  = $this->renderDashboardCloseButton() . $content;
            $content  = ZurmoHtml::tag('div', array('id' => 'gd-centralizer'), $content);
            $blackOut  = ZurmoHtml::tag('div', array('id' => 'gd-overlay'), '');
            $container = ZurmoHtml::tag('div', array('id' => 'gd-container'), $content);
            return $blackOut . $container;
        }

        protected function renderProfileContent()
        {
            $content  = $this->user->getAvatarImage(240);
            $content .= ZurmoHtml::tag('h3', array(), strval($this->user));
            $content .= $this->renderMiniStatisticsContent();
            return      ZurmoHtml::tag('div', array('id' => 'gd-profile-card'), $content);
        }

        protected function renderMiniStatisticsContent()
        {
            $percentageToNextLabel = Zurmo::t('GamificationModule',
                                              '{percentage}% to Level {level}',
                                              array('{percentage}' => (int)$this->generalLevelData['nextLevelPercentageComplete'],
                                                    '{level}'      => (int)$this->generalLevelData['level'] + 1));

            $levelContent  = ZurmoHtml::tag('strong', array(), $this->generalLevelData['level']);
            $levelContent .= ZurmoHtml::tag('span', array(), Zurmo::t('GamificationModule', 'Level'));
            $levelContent .= ZurmoHtml::tag('span', array(), $percentageToNextLabel);

            $content  = ZurmoHtml::tag('div', array('id'    => 'gd-mini-stats-chart-div'), $this->renderMiniStatisticsChart());
            $content .= ZurmoHtml::tag('div', array('class' => 'gd-level'), $levelContent);
            $badgeLabelContent = Zurmo::t('GamificationModule', '<strong>{n}</strong> Badge|<strong>{n}</strong> Badges',
                                          array(count($this->badgeData)));
            $content .= ZurmoHtml::tag('div', array('class' => 'gd-num-badges'), $badgeLabelContent);
            $collectionLabelContent = Zurmo::t('GamificationModule', '<strong>{n}</strong> Collection|<strong>{n}</strong> Collections',
                                               array($this->getCompletedCollectionCount()));
            $content .= ZurmoHtml::tag('div', array('class' => 'gd-num-collections'), $collectionLabelContent);
            return      ZurmoHtml::tag('div',  array('id'    => 'gd-mini-stats-card'), $content);
        }

        protected function renderMiniStatisticsChart()
        {
            $chartData = array(array('column' => 'level-undone',
                                     'value'  => 100 - (int)$this->generalLevelData['nextLevelPercentageComplete']),
                               array('column' => 'level-done',
                                     'value'  => (int)$this->generalLevelData['nextLevelPercentageComplete']));
            Yii::import('ext.amcharts.AmChartMaker');
            $amChart = new AmChartMaker();
            $amChart->data = $chartData;
            $amChart->id   =  'miniChart';
            $amChart->type = ChartRules::TYPE_DONUT_PROGRESSION;
            $amChart->addSerialGraph('value', 'column');
            $amChart->addSerialGraph('value', 'column');
            $javascript = $amChart->javascriptChart();
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '-mini-chart', $javascript);
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("Chart");
            $cClipWidget->widget('application.core.widgets.AmChart', array(
                'id'        => 'miniChart',
                'height'    => '150px',
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['Chart'];
        }

        protected function renderBadgesContent()
        {
            $content  = ZurmoHtml::tag('h2', array(), Zurmo::t('GamificationModule', 'Badges Achieved'));
            if (empty($this->badgeData))
            {
                $content .= $this->renderEmptyBadgeContent();
            }
            else
            {
                $content .= $this->renderPopulatedBadgeContent();
            }
            return ZurmoHtml::tag('div', array('id' => 'gd-badges-list'), $content);
        }

        protected function renderEmptyBadgeContent()
        {
            $content  = ZurmoHtml::tag('span', array('class' => 'icon-empty'), '');
            $content .= Zurmo::t('GamificationModule', 'No Achievements Found');
            return ZurmoHtml::tag('span', array('class' => 'empty type-achievements'), $content);
        }

        protected function renderPopulatedBadgeContent()
        {
            $content = '<ul>' . "\n";
            foreach ($this->badgeData as $badge)
            {
                $gameBadgeRulesClassName = $badge->type . 'GameBadgeRules';
                $value                   = $gameBadgeRulesClassName::getItemCountByGrade((int)$badge->grade);
                $badgeDisplayLabel       = $gameBadgeRulesClassName::getPassiveDisplayLabel($value);
                $badgeContent      = null;
                $badgeIconContent  = ZurmoHtml::tag('div',   array('class' => 'gloss'), '');
                $badgeIconContent .= ZurmoHtml::tag('strong',   array('class' => 'badge-icon',
                    'title' => $badgeDisplayLabel), '');
                $badgeIconContent .= ZurmoHtml::tag('span',   array('class' => 'badge-grade'), (int)$badge->grade);
                $badgeContent .= ZurmoHtml::tag('div',   array('class' => 'badge ' . $badge->type), $badgeIconContent);
                $badgeContent .= ZurmoHtml::tag('h3',   array(), $badgeDisplayLabel);
                $badgeContent .= ZurmoHtml::tag('span', array(),
                    DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay(
                        $badge->createdDateTime, 'long', null));
                $content      .= ZurmoHtml::tag('li',   array(), $badgeContent);
            }
            $content .= '</ul>' . "\n";
            return $content;
        }

        protected function renderLeaderboardContent()
        {
            $content  = ZurmoHtml::tag('h2', array(), Zurmo::t('GamificationModule', 'Leaderboard Rankings'));
            foreach ($this->rankingData as $ranking)
            {
                $rankingContent  = ZurmoHtml::tag('strong', array(), $ranking['rank']);
                $rankingContent .= ZurmoHtml::tag('span', array(), $ranking['typeLabel']);
                $content .= ZurmoHtml::tag('div', array('class' => 'leaderboard-rank'), $rankingContent);
            }
            return      ZurmoHtml::tag('div', array('id' => 'gd-leaderboard', 'class' => 'clearfix'), $content);
        }

        protected function renderStatisticsContent()
        {
            $content = ZurmoHtml::tag('h2', array(), Zurmo::t('GamificationModule', 'Overall Statistics'));
            $rows = '';
            foreach ($this->statisticsData as $statistics)
            {
                $statisticsContent  = ZurmoHtml::tag('h3', array(), $statistics['levelTypeLabel']);
                $statisticsContent .= ZurmoHtml::tag('span', array('class' => 'stat-level'), $statistics['level']);
                $pointsContent      = Zurmo::t('GamificationModule', '{n}<em>Point</em>|{n}<em>Points</em>', array($statistics['points']));
                $statisticsContent .= ZurmoHtml::tag('span', array('class' => 'stat-points'), $pointsContent);
                $statisticsContent .= $this->renderPercentHolderContent((int)$statistics['nextLevelPercentageComplete']);
                $rows .= ZurmoHtml::tag('div', array('class' => 'stat-row'), $statisticsContent);
            }
            $content .= ZurmoHtml::tag('div', array('id' => 'gd-stats-wrapper'), $rows);
            return      ZurmoHtml::tag('div', array('id' => 'gd-statistics'), $content);
        }

        protected function renderCollectionsContent()
        {
            $content  = ZurmoHtml::link('&cedil;', '#', array('id' => 'nav-left', 'class' => 'nav-button'));
            $content .= $this->renderCollectionsCarouselWrapperAndContent();
            $content .= ZurmoHtml::link('&circ;', '#', array('id' => 'nav-right', 'class' => 'nav-button'));
            return      ZurmoHtml::tag('div', array('id' => 'gd-collections'), $content);
        }

        protected function renderCollectionsCarouselWrapperAndContent()
        {
            $collectionsListContent = null;
            foreach ($this->collectionData as $collection)
            {
                $collectionsListContent .= $this->renderCollectionContent($this->user, $collection);
            }
            $width = count($this->collectionData) * 285; //closed panel width.
            $content = ZurmoHtml::tag('div', array('id' => 'gd-carousel', 'style' => "width:" . $width . "px"), $collectionsListContent);
            return     ZurmoHtml::tag('div', array('id' => 'gd-carousel-wrapper'), $content);
        }

        protected static function renderCollectionItemsContent(User $user, GameCollection $collection,
                                                               GameCollectionRules $gameCollectionRules)
        {
            $itemTypesAndLabels = $gameCollectionRules->getItemTypesAndLabels();
            $content    = null;
            $canCollect = true;
            foreach ($collection->getItemsData() as $itemType => $quantityCollected)
            {
                $itemLabel               = $itemTypesAndLabels[$itemType];
                $collectionItemImagePath = $gameCollectionRules::makeMediumCOllectionItemImagePath($itemType);
                $itemContent = static::resolveLazyLoadImage($collectionItemImagePath, $itemLabel,
                                          array('class' => 'qtip-shadow', 'data-tooltip' => $itemLabel));
                $qtip = new ZurmoTip(array('options' => array('position' => array('my' => 'bottom center', 'at' => 'top center'),
                                                          'content'  => array('attr' => 'data-tooltip'))));
                $qtip->addQTip(".gd-collection-item img");
                $itemContent .= ZurmoHtml::tag('span', array('class' => 'num-collected'), 'x' . $quantityCollected);
                $classContent = 'gd-collection-item';
                if ($quantityCollected == 0)
                {
                    $classContent .= ' missing';
                    $canCollect = false;
                }
                $content .= ZurmoHtml::tag('div', array('class' => $classContent), $itemContent);
            }
            $itemRedeemContent = static::renderCompleteButton($collection->id, $user->id, $canCollect);
            $content           .= ZurmoHtml::tag('div', array('class' => 'gd-collection-item-redeemed'), $itemRedeemContent);
            return ZurmoHtml::tag('div', array('class' => 'gd-collection-items clearfix'), $content);
        }

        protected static function renderCompleteButton($collectionId, $userId, $canCollect = true)
        {
            assert('is_int($collectionId)');
            assert('is_int($userId)');
            assert('is_bool($canCollect)');
            $url           = Yii::app()->createUrl('gamification/default/redeemCollection/', array('id' => $collectionId));
            $htmlOptions   = array();
            $disabledClass = null;
            $disabled      = false;
            if (!$canCollect || $userId != Yii::app()->user->userModel->id)
            {
                $disabledClass = ' disabled';
                $disabled      = true;
            }
            $id                      = static::getCompleteCollectionLinkId($collectionId);
            $htmlOptions['id']       = $id;
            $htmlOptions['name']     = $id;
            $htmlOptions['class']    = 'attachLoading z-button coin-button' . $disabledClass;
            if ($disabled)
            {
                $htmlOptions['onclick']   = 'js:return false;';
            }
            else
            {
                $htmlOptions['onclick']   = 'js:$(this).addClass("loading").addClass("loading-ajax-submit");
                                                        $(this).makeOrRemoveLoadingSpinner(true, "#" + $(this).attr("id"), "#763d05");';
            }
            $aContent                = ZurmoHtml::wrapLink(Zurmo::t('Core', 'Complete'));
            $containerId             = static::getCollectionContainerId($collectionId);
            return ZurmoHtml::ajaxLink($aContent, $url, array(
                'type'    => 'GET',
                'success' => 'js:function(data)
                    {
                        $("#' . $containerId . '").replaceWith(data);
                        $("#' . $containerId . '").addClass("visible-panel");
                        ' . self::renderGameCoinRefreshAjax($userId) . '
                        updateGamificationImagesSrcForLazyLoading();
                    }'
            ), $htmlOptions);
        }

        protected static function getCollectionContainerId($collectionId)
        {
            return self::COLLECTION_CONTAINER_ID_PREFIX . '-' . $collectionId;
        }

        protected static function getCompleteCollectionLinkId($collectionId)
        {
            return self::COMPLETE_COLLECTION_LINK_PREFIX . '-' . $collectionId;
        }

        protected function getCompletedCollectionCount()
        {
            $count = 0;
            foreach ($this->collectionData as $collection)
            {
                if ($collection->getRedemptionCount() > 0)
                {
                    $count++;
                }
            }
            return $count;
        }

        protected function getGameCoinForUser()
        {
            return GameCoin::resolveByPerson($this->user);
        }

        protected function renderPercentHolderContent($percentageComplete)
        {
            assert('is_int($percentageComplete)');
            $percentCompleteContent = ZurmoHtml::tag('span',
                array('class' => 'percentComplete z_' . $percentageComplete),
                      ZurmoHtml::tag('span', array('class' => 'percent'), $percentageComplete . '%'));
            return ZurmoHtml::tag('span', array('class' => 'percentHolder'), $percentCompleteContent);
        }

        protected static function renderGameCoinRefreshAjax($userId)
        {
            assert('is_int($userId)');
            return ZurmoHtml::ajax(array(
                'type' => 'GET',
                'url'  =>  static::getGameCoinRefreshUrl($userId),
                'success' => 'function(data){$("#' . self::getGameCoinContainerId() . '").replaceWith(data)}',
            ));
        }

        protected static function getGameCoinRefreshUrl($userId)
        {
            assert('is_int($userId)');
            return Yii::app()->createUrl('gamification/default/refreshGameDashboardCoinContainer', array('id' => $userId));
        }

        protected static function getGameCoinContainerId()
        {
            return 'gd-z-coins';
        }

        protected static function renderDashboardCloseButton()
        {
            return '<div class="close-dashboard-button"><a href="#"><span class="ui-icon ui-icon-closethick">close</span></a></div>';
        }

        protected static function getDummyImageUrl()
        {
            return PlaceholderImageUtil::resolveOneByOnePixelImageUrl(false);
        }

        protected static function resolveLazyLoadImage($source, $alt = '', $htmlOptions = array())
        {
            $dummyImageUrl = static::getDummyImageUrl();
            if ($source != $dummyImageUrl)
            {
                $htmlOptions['data-src']    = $source;
                $source                     = $dummyImageUrl;
            }
            return ZurmoHtml::image($source, $alt, $htmlOptions);
        }
    }
?>
