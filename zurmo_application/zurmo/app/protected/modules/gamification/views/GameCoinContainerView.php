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
     * Class for displaying a modal window with a game notification.
     */
    class GameCoinContainerView extends View
    {
        protected $controller;

        /**
         * @param CController $controller
         */
        public function __construct(CController $controller)
        {
            $this->controller = $controller;
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        protected function renderContent()
        {
            if (Yii::app()->gameHelper->getModalCoinsEnabled() && GameCoin::showCoin($this->controller))
            {
                $this->registerScripts();
                return $this->renderCoinContainerContent();
            }
        }

        protected function registerScripts()
        {
            $cs = Yii::app()->getClientScript();
            $cs->registerCoreScript('jquery-animate-sprite');
            $cs->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.modules.gamification.views.assets')
                ) . '/jquery.animateSprite.js',
                CClientScript::POS_END
            );

            $url    = $this->makeAjaxClickUrl();
            $coin = ZurmoHtml::tag('div', array('class' => 'game-coin-quantity'),
                    ($this->getGameCoinForCurrentUser()->value + 1) . '<i></i>');
            // Begin Not Coding Standard
            $script = "$('.random-game-coin').click(function(e){
                                $(this).unbind('click');
                                " . ZurmoHtml::ajax(array('type' => 'GET', 'url' =>  $url)) . "
                                var audio = document.getElementById('game-coin-chime');
                                audio.play();
                                $('.game-coin').animate({top:15}, 75, function(){ $(this).hide(0) });
                                $('.smoke').show(0).animate({top:0}, 500).animateSprite({
                                    columns: 8,
                                    totalFrames: 40,
                                    duration: 1000,
                                    loop: false,
                                    complete: function(){
                                        $('.random-game-coin').remove();
                                    }
                                });
                                $('$coin').prependTo('#user-toolbar')
                                    .delay(300)
                                    .animate({top:8}, 250)
                                    .delay(3500)
                                    .fadeOut(250, function(){
                                        $(this).remove();
                                    });
                            });";
            Yii::app()->clientScript->registerScript('gameCoinClickScript', $script);
            // End Not Coding Standard
        }

        protected function renderCoinContainerContent()
        {
            $content = $this->renderCoinContent();
            $content .= $this->renderAudioContent();
            return ZurmoHtml::tag('div', array('class' => 'random-game-coin'), $content);
        }

        protected function renderCoinContent()
        {
            $content = ZurmoHtml::tag('div', array('class' => 'game-coin'), '');
            $content .= ZurmoHtml::tag('div', array('class' => 'smoke'), '');
            return ZurmoHtml::tag('div', array(), $content);
        }

        protected function renderAudioContent()
        {
            $publishedAssetsPath = Yii::app()->assetManager->publish(
                Yii::getPathOfAlias("application.modules.gamification.views.assets.audio"));
            $MP3AudioFilePath = $publishedAssetsPath . '/cash-register.mp3';
            $OGGAudioFilePath = $publishedAssetsPath . '/cash-register.ogg';
            $WAVAudioFilePath = $publishedAssetsPath . '/cash-register.wav';
            $content  = ZurmoHtml::tag('source', array('src' => $MP3AudioFilePath, 'type' => 'audio/mpeg'), '');
            $content .= ZurmoHtml::tag('source', array('src' => $OGGAudioFilePath, 'type' => 'audio/ogg'), '');
            $content .= ZurmoHtml::tag('source', array('src' => $WAVAudioFilePath, 'type' => 'audio/wav'), '');
            return ZurmoHtml::tag('audio', array('id' => 'game-coin-chime'), $content);
        }

        protected function getGameCoinForCurrentUser()
        {
            return GameCoin::resolveByPerson(Yii::app()->user->userModel);
        }

        protected function makeAjaxClickUrl()
        {
            return Yii::app()->createUrl('gamification/default/CollectRandomCoin');
        }
    }
?>
