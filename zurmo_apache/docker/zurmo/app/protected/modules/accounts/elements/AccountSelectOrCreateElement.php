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
     * Display the account selection. This is a
     * combination of a type-ahead input text field
     * and a selection button which renders a modal list view
     * to search on account.  Also includes a hidden input for the user
     * id.
     */
    class AccountSelectOrCreateElement extends AccountElement
    {
        protected static $moduleId = 'accounts';

        /**
         * Renders extra html content
         * @return string
         */
        protected function renderExtraHtmlContent()
        {
            return $this->renderCreateAccountModalLink();
        }

        /**
         * Render create account modal link
         * @return array
         */
        private function renderCreateAccountModalLink()
        {
            $id      = $this->getIdForCreateLink();
            $label   = Zurmo::t('AccountsModule', 'or ');
            $label  .= $this->getCreateAccountLabel();
            $content = ZurmoHtml::ajaxLink($label,
                Yii::app()->createUrl('accounts/default/modalCreate', $this->getSelectLinkUrlParams()),
                $this->resolveAjaxOptionsForModalView($id),
                array('id'    => $id,
                      'style' => $this->getSelectLinkStartingStyle(),
                      'class' => 'simple-link'
                )
            );
            return $content;
        }

        /**
         * Get id for create link
         * @return string
         */
        protected function getIdForCreateLink()
        {
            return $this->getEditableInputId($this->attribute, 'CreateLink');
        }

        /**
         * Resolve ajax options for modal view
         * @param string $linkId
         * @return string
         */
        protected function resolveAjaxOptionsForModalView($linkId)
        {
            assert('is_string($linkId)');
            $title  = $this->getCreateAccountLabel();
            return   ModalView::getAjaxOptionsForModalLink($title, $this->getModalContainerId(), 'auto', 600,
                     'center top+25', $class = "'task-dialog'"); // Not Coding Standard
        }

        /**
         * Gets create account label
         * @return string
         */
        private function getCreateAccountLabel()
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            return Zurmo::t('AccountsModule', 'Create a new AccountsModuleSingularLabel', $params);
        }
    }
?>