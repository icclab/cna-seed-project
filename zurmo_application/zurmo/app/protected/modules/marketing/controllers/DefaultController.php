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

    class MarketingDefaultController extends ZurmoBaseController
    {
        public static function getDashboardBreadcrumbLinks()
        {
            $title = Zurmo::t('ZurmoModule', 'Dashboard');
            return array($title);
        }

        public function filters()
        {
            return array_merge(parent::filters(),
                array(
                    array(
                        ZurmoBaseController::RIGHTS_FILTER_PATH,
                        'moduleClassName' => 'MarketingModule',
                        'rightName' => MarketingModule::RIGHT_ACCESS_MARKETING,
                   ),
               )
            );
        }

        public function actionIndex()
        {
            $this->actionDashboardDetails();
        }

        public function actionDashboardDetails()
        {
            $params = array(
                'controllerId' => $this->getId(),
                'moduleId'     => $this->getModule()->getId(),
            );
            $gridViewId              = 'notUsed';
            $pageVar                 = 'notUsed';
            $introView               = new MarketingDashboardIntroView(get_class($this->getModule()));
            $actionBarView           = new SecuredActionBarForMarketingSearchAndListView(
                                            'default',
                                            'marketing',
                                            new EmailTemplate(), //Just to fill in a marketing model
                                            $gridViewId,
                                            $pageVar,
                                            false,
                                            'MarketingDashboardMenu',
                                            $introView);
            $marketingDashboardView  = new MarketingDashboardView(
                                            $this->getId(),
                                            $this->getModule()->getId(),
                                            'MarketingDashboard',
                                            $params);
            $marketingDashboardView->setCssClasses( array( 'clearfix' ) );

            $gridView                = new GridView(2, 1);
            $gridView->setView($actionBarView, 0, 0);
            $gridView->setView($marketingDashboardView, 1, 0);
            $breadCrumbLinks         = static::getDashboardBreadcrumbLinks();
            $view                    = new MarketingPageView(MarketingDefaultViewUtil::
                                       makeViewWithBreadcrumbsForCurrentUser(
                                            $this,
                                            $gridView,
                                            $breadCrumbLinks,
                                            'MarketingBreadCrumbView'));
            echo $view->render();
        }

        public function actionConfigurationEdit()
        {
            $breadCrumbLinks = array(
                Zurmo::t('MarketingModule', 'Marketing Configuration'),
            );
            $form               = MarketingConfigurationFormAdapter::makeFormFromMarketingConfiguration();
            $postData           = PostUtil::getData();
            $postVariableName   = get_class($form);
            if (isset($postData[$postVariableName]))
            {
                $form->setAttributes($postData[$postVariableName]);
                if ($form->validate())
                {
                    MarketingConfigurationFormAdapter::setConfigurationFromForm($form);
                    Yii::app()->user->setFlash('notification',
                        Zurmo::t('ZurmoModule', 'Global configuration saved successfully.')
                    );
                    $this->redirect(Yii::app()->createUrl('configuration/default/index'));
                }
            }
            $editView = new MarketingConfigurationEditAndDetailsView(
                'Edit',
                $this->getId(),
                $this->getModule()->getId(),
                $form);
            $editView->setCssClasses( array('AdministrativeArea') );
            $view = new ZurmoConfigurationPageView(ZurmoDefaultAdminViewUtil::makeViewWithBreadcrumbsForCurrentUser(
                    $this, $editView, $breadCrumbLinks, 'SettingsBreadCrumbView'));
            echo $view->render();
        }

        public function actionPreviewFooter($isHtmlContent, $content)
        {
            Yii::app()->getClientScript()->setToAjaxMode();
            $view   = new GlobalMarketingFooterConfigurationPreviewView((bool)$isHtmlContent, $content);
            $modalView = new ModalView($this, $view);
            echo $modalView->render();
        }
    }
?>