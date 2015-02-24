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
     * Progress view for rebuilding named securable item cache and actual rights cache
     */
    class RebuildSecurityCacheProgressView extends ProgressView
    {
        protected function getMessagePrefix()
        {
            return Zurmo::t('Core', 'Rebuilding');
        }

        protected function getCompleteMessageSuffix()
        {
            return Zurmo::t('Core', 'rebuilt successfully');
        }

        protected function headerLabelPrefixContent()
        {
            return Zurmo::t('ZurmoModule', 'Rebuild Security Cache');
        }

        protected function getMessage()
        {
            return $this->getMessagePrefix() . "&#160;" . $this->start . "&#160;-&#160;" . $this->getEndSize() .
                        "&#160;" . Zurmo::t('Core', 'of') . "&#160;" . $this->totalRecordCount . "&#160;" .
                        Zurmo::t('Core', 'total') . "&#160;" .
                        LabelUtil::getUncapitalizedModelLabelByCountAndModelClassName($this->totalRecordCount, 'User');
        }

        protected function getCompleteMessage()
        {
            $content = $this->totalRecordCount . '&#160;' .
                       LabelUtil::getUncapitalizedModelLabelByCountAndModelClassName($this->totalRecordCount, 'User')
                       . '&#160;' . $this->getCompleteMessageSuffix() . '.';
            return $content;
        }

        protected function renderFormLinks()
        {
           return ZurmoHtml::tag('div',
                                        array('id' => $this->progressBarId . '-links',  'style' => 'display:none;'),
                                        $this->renderReturnLink()
                                );
        }

        protected function renderReturnLink()
        {
            return ZurmoHtml::link(ZurmoHtml::wrapLabel($this->renderReturnMessage()), $this->renderReturnUrl(),
                   array('class' => 'white-button'));
        }

        protected function renderReturnUrl()
        {
            return Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/');
        }

        protected function renderReturnMessage()
        {
            return Zurmo::t('ZurmoModule', 'Return to Development Tools');
        }

        protected function getCreateProgressBarAjax($progressBarId)
        {
            return ZurmoHtml::ajax(array(
                'type' => 'GET',
                'dataType' => 'json',
                'url'  => Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/' . $this->refreshActionId,
                        array_merge($_GET, array( get_class($this->model) . '_page' => ($this->page + 1), 'continue' => true))
                    ),
                'success' => 'function(data)
                    {
                        $(\'#' . $progressBarId . '-msg\').html(data.message);
                        $(\'#' . $progressBarId . '\').progressbar({value: data.value});
                        eval(data.callback);
                    }',
            ));
        }
    }
?>