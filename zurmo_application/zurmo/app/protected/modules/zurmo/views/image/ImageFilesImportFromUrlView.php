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
     * Class ImageFilesImportFromView
     */
    class ImageFilesImportFromUrlView extends View
    {
        private $controller;

        private $formModel;

        /**
         * @param CController $controller
         * @param CFormModel $formModel
         */
        public function __construct(CController $controller, CFormModel $formModel)
        {
            $this->controller         = $controller;
            $this->formModel          = $formModel;
        }

        /**
         * Renders the view content.
         */
        protected function renderContent()
        {
            $this->setCssClasses(array('form'));
            $content = $this->renderForm();
            return $content;
        }

        protected function renderForm()
        {
            list($form, $formStart) = $this->controller->renderBeginWidget(
                'ZurmoActiveForm',
                array(
                    'id'                   => 'image-import-form',
                    'action'               => Yii::app()->controller->createUrl('imageModel/uploadFromUrl'),
                    'enableAjaxValidation' => true,
                    'clientOptions' => array(
                        'validateOnSubmit' => true,
                        'validateOnChange' => false,
                        'beforeValidate'    => 'js:$(this).beforeValidateAction',
                        'afterValidate'     => 'js:$(this).afterValidateAjaxAction',
                        'afterValidateAjax' => $this->renderPreviewImportedImageScript(),
                    ),
                )
            );
            $content  = $formStart;
            $content .= $form->labelEx ($this->formModel, 'url');
            $content .= ZurmoHtml::openTag('div', array('class' => 'import-url-field'));
            $content .= $form->urlField($this->formModel, 'url');
            $content .= $form->error   ($this->formModel, 'url');
            $content .= ZurmoHtml::closeTag('div');
            $linkOptions = array('onclick'  => "$(this).addClass('attachLoadingTarget').closest('form').submit();" .
                                               "$(this).makeOrRemoveLoadingSpinner(true, $(this), 'dark');",                                 'class'    => 'secondary-button');
            $content .= ZurmoHtml::tag('div', array('id'    => 'import-image-hidden-div',
                                                    'class' => Redactor::LINK_FOR_INSERT_CLASS,
                                                    'style' => 'display:none;'), '');
            $spinner = ZurmoHtml::tag('span', array('class' => 'z-spinner'));
            $label = ZurmoHtml::tag('span', array('class' => 'z-label'), Zurmo::t('ZurmoModule', 'Import'));
            $content .= ZurmoHtml::link($spinner . $label, "#", $linkOptions);
            $content .= $this->controller->renderEndWidget();
            return $content;
        }

        protected function renderPreviewImportedImageScript()
        {
            return "transferModalImageValues(data.id, data.summary); " .
                   "$('#import-image-hidden-div').data('url', data.filelink).click(); ";
        }

        protected function getViewStyle()
        {
            return " style='display:none;'";
        }
    }
?>