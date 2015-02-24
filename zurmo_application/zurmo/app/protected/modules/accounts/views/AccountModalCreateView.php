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
     * Modal window for creating account
     */
    class AccountModalCreateView extends AccountEditAndDetailsView
    {
        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata          = parent::getDefaultMetadata();
            $metadata['global']['toolbar']['elements'] = array(
                            array('type'        => 'SaveButton'),
                            array('type'        => 'ModalCancelLink',
                                  'htmlOptions' => 'eval:static::resolveHtmlOptionsForCancel()'
                            )
                        );
            return $metadata;
        }

         /**
          * @return string
          */
         protected function getNewModelTitleLabel()
         {
             return null;
         }

        /**
         * @return array
         */
        protected static function resolveHtmlOptionsForCancel()
        {
            return array(
                'onclick' => '$("#ModalView").parent().dialog("close");'
            );
        }

        /**
         * Resolves ajax validation option for save button
         * @return array
         */
        protected function resolveActiveFormAjaxValidationOptions()
        {
            $url               = Yii::app()->createUrl('accounts/default/modalCreate', GetUtil::getData());
            // Begin Not Coding Standard
            return array('enableAjaxValidation' => true,
                        'clientOptions' => array(
                            'beforeValidate'    => 'js:$(this).beforeValidateAction',
                            'afterValidate'     => 'js:function(form, data, hasError){
                                if(hasError)
                                {
                                    form.find(".attachLoading:first").removeClass("loading");
                                    form.find(".attachLoading:first").removeClass("loading-ajax-submit");
                                }
                                else
                                {
                                ' . $this->saveAccountViaAjax() . '
                                }
                                return false;
                            }',
                            'validateOnSubmit'  => true,
                            'validateOnChange'  => false,
                            'validationUrl'     => $url,
                            'inputContainer'    => 'td'
                        )
            );
            // End Not Coding Standard
        }

        /**
         * Get designer rules type
         * @return string
         */
        public static function getDesignerRulesType()
        {
            return 'AccountModalCreateView';
        }

        /**
         * Save account via ajax
         * @return string ajax response
         */
        protected function saveAccountViaAjax()
        {
            $getData           = GetUtil::getData();
            $sourceIdFieldId   = $getData['modalTransferInformation']['sourceIdFieldId'];
            $sourceNameFieldId = $getData['modalTransferInformation']['sourceNameFieldId'];
            $modalId           = $getData['modalTransferInformation']['modalId'];
            $formId            = static::getFormId();
            $url               = Yii::app()->createUrl('accounts/default/modalCreate', GetUtil::getData());
            // Begin Not Coding Standard
            $options = array(
                                'type'     => 'post',
                                'dataType' => 'json',
                                'url'      => $url,
                                'data'     => 'js:$("#' . $formId . '").serialize()',
                                'success'  => "function(data){
                                                $('#{$sourceIdFieldId}').val(data.id).trigger('change');
                                                $('#{$sourceNameFieldId}').val(data.name).trigger('change');
                                                $('#{$modalId}').dialog('close');
                                              }"
                            );
            // End Not Coding Standard
            return ZurmoHtml::ajax($options);
        }

        /**
         * Gets form id
         * @return string
         */
        protected static function getFormId()
        {
            $getData           = GetUtil::getData();
            $sourceNameFieldId = $getData['modalTransferInformation']['sourceNameFieldId'];
            return $sourceNameFieldId . '-' . parent::getFormId();
        }

        /**
         * Gets form layout unique id
         * @return null
         */
        protected function getFormLayoutUniqueId()
        {
            return 'task-modal-edit-form-layout';
        }
    }
?>