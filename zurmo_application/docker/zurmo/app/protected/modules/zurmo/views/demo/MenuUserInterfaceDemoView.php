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

    class MenuUserInterfaceDemoView extends View
    {
        /**
         * @see ZurmoDefaultViewUtil::makeStandardViewForCurrentUser()
         */
        const STANDARD_VIEW             = 'standardView';

        /**
         * @see ZurmoDefaultViewUtil::makeViewWithBreadcrumbsForCurrentUser()
         */
        const STANDARD_BREADCRUMBS_VIEW = 'standardBreadCrumbsView';

        /**
         * @see ZurmoDefaultViewUtil::makeErrorViewForCurrentUser()
         */
        const GRACEFUL_ERROR_VIEW       = 'gracefulErrorView';

        /**
         * @see ErrorPageView
         */
        const UNEXPECTED_ERROR_VIEW     = 'unexpectedErrorView';

        /**
         * @see ZurmoDefaultViewUtil::makeAuthorizationViewForCurrentUser()
         */
        const AUTHORIZATION_VIEW        = 'authorizationView';

        /**
         * @see ZurmoExternalDefaultPageView
         * @see ContactWebFormsExternalPageView
         */
        const CONTACT_FORM_EXTERNAL_VIEW = 'contactFormExternalView';

        /**
         * @see MarketingListsExternalActionsPageView
         */
        const MARKETING_LISTS_EXTERNAL_PREVIEW_VIEW = 'marketingListsExternalPreviewView';

        const MARKETING_LISTS_MANAGE_SUBSCRIPTIONS_VIEW  = 'marketingListsManageSubscriptionsView';

        /**
         * @see MobileHeaderView
         */
        const MOBILE_HEADER_VIEW         = 'mobileHeaderView';

        protected function renderContent()
        {
            $route = Yii::app()->controller->getModule()->getId() . '/' . Yii::app()->controller->getId() . '/' .
                     Yii::app()->controller->getAction()->getId();
            $standardViewUrl            = Yii::app()->createUrl($route, array('type' => static::STANDARD_VIEW));
            $standardBreadcrumbsViewUrl = Yii::app()->createUrl($route, array('type' => static::STANDARD_BREADCRUMBS_VIEW));
            $gracefulErrorViewUrl       = Yii::app()->createUrl($route, array('type' => static::GRACEFUL_ERROR_VIEW));
            $unexpectedErrorViewUrl     = Yii::app()->createUrl($route, array('type' => static::UNEXPECTED_ERROR_VIEW));
            $authorizationViewUrl       = Yii::app()->createUrl($route, array('type' => static::AUTHORIZATION_VIEW));
            $contactFormExternalViewUrl = Yii::app()->createUrl($route, array('type' => static::CONTACT_FORM_EXTERNAL_VIEW));
            $marketingListsExternalPreviewViewUrl = Yii::app()->createUrl($route, array('type' => static::MARKETING_LISTS_EXTERNAL_PREVIEW_VIEW));
            $marketingListsSubscriptionsViewUrl = Yii::app()->createUrl($route, array('type' => static::MARKETING_LISTS_MANAGE_SUBSCRIPTIONS_VIEW));
            $mobileHeaderViewUrl        = Yii::app()->createUrl($route, array('type' => static::MOBILE_HEADER_VIEW));

            //ZurmoDefaultViewUtil::makeTwoViewsWithBreadcrumbsForCurrentUser(
            //ZurmoDefaultViewUtil::makeTwoStandardViewsForCurrentUser(
            //ZurmoDefaultAdminViewUtil::makeViewWithBreadcrumbsForCurrentUser(

            $content  = null;
            $content .= ZurmoHtml::tag('li', array(), ZurmoHtml::link('Standard View',              $standardViewUrl));
            $content .= ZurmoHtml::tag('li', array(), ZurmoHtml::link('Standard Breadcrumbs View',  $standardBreadcrumbsViewUrl));
            $content .= ZurmoHtml::tag('li', array(), ZurmoHtml::link('Graceful Error View',        $gracefulErrorViewUrl));
            $content .= ZurmoHtml::tag('li', array(), ZurmoHtml::link('Unexpected Error View',      $unexpectedErrorViewUrl));
            $content .= ZurmoHtml::tag('li', array(), ZurmoHtml::link('Authorization View',         $authorizationViewUrl));
            $content .= ZurmoHtml::tag('li', array(), ZurmoHtml::link('Contact Web Form External View',         $contactFormExternalViewUrl));
            $content .= ZurmoHtml::tag('li', array(), ZurmoHtml::link('Marketing Lists External Preview View',  $marketingListsExternalPreviewViewUrl));
            $content .= ZurmoHtml::tag('li', array(), ZurmoHtml::link('Marketing Lists Manage Subscriptions View',  $marketingListsSubscriptionsViewUrl));
            $content .= ZurmoHtml::tag('li', array(), ZurmoHtml::link('Mobile Header View',                     $mobileHeaderViewUrl));

            return ZurmoHtml::tag('ul', array(), $content);
        }
    }
?>
