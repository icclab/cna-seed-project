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

    class GameRewardRedeemSummaryListViewColumnAdapter extends TextListViewColumnAdapter
    {
        const REDEEM_REWARD_LINK_PREFIX = 'redeem-reward-link';

        public function renderGridViewData()
        {
            $className  = get_class($this);
            $value      = $className . '::resolveSummary($data, ' . $this->view->getAvailableCoinsForCurrentUser() . ')';
            return array(
                'name'  => null,
                'value' => $value,
                'type'  => 'raw',
            );
        }

        /**
         * @param GameReward $gameReward
         * @param int $availableCoins
         * @return string
         */
        public static function resolveSummary(GameReward $gameReward, $availableCoins)
        {
            assert('is_int($availableCoins)');
            $content  = ZurmoHtml::tag('h4', array('class' => 'reward-name'), strval($gameReward));
            if ($gameReward->description != null)
            {
                 $content .= ZurmoHtml::tag('p', array('class' => 'reward-description'), $gameReward->description);
            }
            $content .= ZurmoHtml::tag('span', array('class' => 'reward-cost'), $gameReward->cost . ' x ');
            $content .= ZurmoHtml::tag('span', array(),
                        ' - ' . $gameReward->quantity . ' ' . Zurmo::t('Core', 'Available') .
                        ' ' . static::renderExpirationDateTimeContent($gameReward));
            $content .= static::renderRedeemLink($gameReward, $availableCoins);
            return $content;
        }

        protected static function renderExpirationDateTimeContent(GameReward $gameReward)
        {
            if (!DateTimeUtil::isDateTimeStringNull($gameReward->expirationDateTime))
            {
                $content = Zurmo::t('ZurmoModule', 'Until') . ' ';
                return $content . DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($gameReward->expirationDateTime);
            }
        }

        protected static function renderRedeemLink(GameReward $gameReward, $availableCoins)
        {
            assert('is_int($availableCoins)');
            $url      = Yii::app()->createUrl('gameRewards/default/redeemReward', array('id' => $gameReward->id));
            $label    = Zurmo::t('ZurmoModule', 'Redeem');
            $aContent = ZurmoHtml::wrapLink($label);
            // Begin Not Coding Standard
            return      ZurmoHtml::ajaxLink($aContent, $url,
                array('type'       => 'GET',
                      'dataType'     => 'json',
                      'success'    => 'function(data){
                        $("#FlashMessageBar").jnotifyAddMessage({
                            text: data.message,
                            permanent: false,
                        });
                        $("#GameRewardsRedeemListView").each(function(){
                            $(this).find(".pager").find(".refresh").find("a").click();
                        });
                      }'
                ),
                self::resolveHtmlOptionsForRedeemLink($gameReward, $availableCoins));
            // End Not Coding Standard
        }

        protected static function resolveHtmlOptionsForRedeemLink(GameReward $gameReward, $availableCoins)
        {
            assert('is_int($availableCoins)');
            $htmlOptions   = array();
            $disabledClass = null;
            $disabled      = false;
            if ($gameReward->cost > $availableCoins || $gameReward->quantity <= 0)
            {
                $disabledClass = ' disabled';
                $disabled      = true;
            }
            $id                       = static::getRedeemRewardLinkId($gameReward->id);
            $htmlOptions['id']        = $id;
            $htmlOptions['name']      = $id;
            $htmlOptions['class']     = 'attachLoading z-button reward-redeem-link' . $disabledClass;
            $htmlOptions['namespace'] = 'redeem';
            if ($disabled)
            {
                $htmlOptions['onclick']   = 'js:return false;';
            }
            else
            {
                $htmlOptions['onclick']   = 'js:$(this).addClass("loading").addClass("loading-ajax-submit");
                                                        $(this).makeOrRemoveLoadingSpinner(true, "#" + $(this).attr("id"));';
            }
            return $htmlOptions;
        }

        protected static function getRedeemRewardLinkId($gameRewardId)
        {
            return self::REDEEM_REWARD_LINK_PREFIX . '-' . $gameRewardId;
        }
    }
?>