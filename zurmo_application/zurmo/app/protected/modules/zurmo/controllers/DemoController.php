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

    Yii::import('application.modules.zurmo.controllers.DefaultController', true);
    class ZurmoDemoController extends ZurmoDefaultController
    {
        /**
         * Special method to load demo data for testing user interface pagination.  This will load enough data to
         * test each type of pagination.  Use this for development only.
         */
        public function actionLoadPaginationDemoData()
        {
            if (!Group::isUserASuperAdministrator(Yii::app()->user->userModel))
            {
                throw new NotSupportedException();
            }
            UserInterfaceDevelopmentUtil::makePaginationData();
        }

        public function actionLoadMassDeleteDemoData()
        {
            if (!Group::isUserASuperAdministrator(Yii::app()->user->userModel))
            {
                throw new NotSupportedException();
            }
            UserInterfaceDevelopmentUtil::makeMassDeleteData();
        }

        public function actionUserInterface($type = null)
        {
            if (!Group::isUserASuperAdministrator(Yii::app()->user->userModel))
            {
                throw new NotSupportedException();
            }
            if ($type == null)
            {
                $demoView = new MenuUserInterfaceDemoView();
                $view     = new ZurmoConfigurationPageView(ZurmoDefaultAdminViewUtil::
                                makeStandardViewForCurrentUser($this, $demoView));
                echo $view->render();
            }
            elseif ($type == MenuUserInterfaceDemoView::STANDARD_VIEW)
            {
                $demoView          = new StandardUserInterfaceDemoView();
                $demoView->message = 'Standard View';
                $view     = new ZurmoConfigurationPageView(ZurmoDefaultViewUtil::
                                makeStandardViewForCurrentUser($this, $demoView));
                echo $view->render();
            }
            elseif ($type == MenuUserInterfaceDemoView::STANDARD_BREADCRUMBS_VIEW)
            {
                $breadCrumbLinks = array(
                    'Breadcrumb 1' => array('/zurmo/demo/userInterface'),
                    'Breadcrumb 2',
                );
                $demoView          = new StandardUserInterfaceDemoView();
                $demoView->message = 'Standard View with BreadCrumbs';
                $view = new ZurmoConfigurationPageView(ZurmoDefaultViewUtil::makeViewWithBreadcrumbsForCurrentUser($this,
                            $demoView, $breadCrumbLinks, 'SettingsBreadCrumbView'));
                echo $view->render();
            }
            elseif ($type == MenuUserInterfaceDemoView::GRACEFUL_ERROR_VIEW)
            {
                $demoView          = new StandardUserInterfaceDemoView();
                $demoView->message = 'Graceful Error View';
                $view     = new ZurmoConfigurationPageView(ZurmoDefaultViewUtil::
                                    makeErrorViewForCurrentUser($this, $demoView));
                echo $view->render();
            }
            elseif ($type == MenuUserInterfaceDemoView::UNEXPECTED_ERROR_VIEW)
            {
                $view        = new ErrorPageView('Unexpected error view');
                echo $view->render();
            }
            elseif ($type == MenuUserInterfaceDemoView::AUTHORIZATION_VIEW)
            {
                $demoView          = new StandardUserInterfaceDemoView();
                $demoView->message = 'Authorization View';
                $view = new ZurmoConfigurationPageView(ZurmoDefaultViewUtil::makeAuthorizationViewForCurrentUser($this, $demoView));
                $view->setCssClasses(array_merge($view->getCssClasses(), array('ZurmoAuthorizationPageView')));
                echo $view->render();
            }
            elseif ($type == MenuUserInterfaceDemoView::CONTACT_FORM_EXTERNAL_VIEW)
            {
                $containedView = new ContactExternalEditAndDetailsView('Edit',
                                        $this->getId(),
                                        $this->getModule()->getId(),
                                        new ContactWebFormsModelForm(new Contact()),
                                        ContactExternalEditAndDetailsView::getMetadata());
                $view          = new ContactWebFormsExternalPageView(ZurmoExternalViewUtil::
                                        makeExternalViewForCurrentUser($containedView));
                echo $view->render();
            }
            elseif ($type == MenuUserInterfaceDemoView::MARKETING_LISTS_EXTERNAL_PREVIEW_VIEW)
            {
                $splashView = new MarketingListsExternalActionsPreviewView();
                $view       = new MarketingListsExternalActionsPageView($this, $splashView);
                echo $view->render();
            }
            elseif ($type == MenuUserInterfaceDemoView::MARKETING_LISTS_MANAGE_SUBSCRIPTIONS_VIEW)
            {
                $marketingListMember = MarketingListMember::getSubset(null, 0, 1);
                $marketingLists      = MarketingList::getByUnsubscribedAndAnyoneCanSubscribe($marketingListMember[0]->contact->id);
                $listView = new MarketingListsManageSubscriptionsListView($this->getId(),
                                    $this->getModule()->getId(),
                                    $marketingLists,
                                    -100,
                                    -100,
                                    -100,
                                    'notUsed');
                $view = new MarketingListsManageSubscriptionsPageView($this, $listView);
                echo $view->render();
            }
            elseif ($type == MenuUserInterfaceDemoView::MOBILE_HEADER_VIEW)
            {
                Yii::app()->userInterface->setSelectedUserInterfaceType(UserInterface::MOBILE);
                $demoView          = new StandardUserInterfaceDemoView();
                $demoView->message = 'Standard View';
                $view              = new ZurmoConfigurationPageView(ZurmoDefaultViewUtil::
                                            makeStandardViewForCurrentUser($this, $demoView));
                echo $view->render();
            }
            else
            {
                throw new NotSupportedException();
            }
        }
    }
?>
