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
     * Layout for the business card view for an account.
     */
    class AccountCardViewLayout extends CardViewLayout
    {
        public function __construct($model, $haveGoToDetailsLink = false)
        {
            assert('$model instanceof Account');
            assert('is_bool($haveGoToDetailsLink)');
            $this->model               = $model;
            $this->haveGoToDetailsLink = $haveGoToDetailsLink;
        }

        protected function renderFrontOfCardContent()
        {
            $content  = $this->resolveNameContent();
            $content .= $this->resolvePhoneContent();
            $content .= $this->resolveAddressContent();
            return $content;
        }

        protected function resolveNameContent()
        {
            $starLink = null;
            $spanContent = null;
            if (StarredUtil::modelHasStarredInterface($this->model))
            {
                $starLink = StarredUtil::getToggleStarStatusLink($this->model, null);
            }
            return ZurmoHtml::tag('h2', array(), $spanContent . strval($this->model) . $starLink . $this->renderGoToDetailsLink());
        }

        protected function renderGoToDetailsLink()
        {
            if ($this->haveGoToDetailsLink)
            {
                $link = Yii::app()->createUrl('accounts/default/details/', array('id' => $this->model->id));
                return ZurmoHtml::link(Zurmo::t('ZurmoModule', 'Go To Details'), $link, array('class' => 'simple-link', 'target' => '_blank'));
            }
        }

        protected function resolvePhoneContent()
        {
            $content = null;
            if ($this->model->officePhone != null)
            {
                $content .= Yii::app()->phoneHelper->resolvePersonCardViewOfficePhoneNumberContent($this->model->officePhone,
                                                                                                    $this->model);
            }
            if ($content != null)
            {
                return ZurmoHtml::tag('div', array('class' => 'contact-details'), $content);
            }
        }

        protected function resolveAddressContent()
        {
            $element                       = new AddressElement($this->model, 'billingAddress', null);
            $element->breakLines           = false;
            $element->nonEditableTemplate  = '{content}';
            return ZurmoHtml::tag('div', array('class' => 'address'), $element->render());
        }

        protected function renderBackOfCardContent()
        {
            return null;
        }
    }
?>
