<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class BuilderEmailTemplateWizardView extends EmailTemplateWizardView
    {
        /**
         * @return string
         */
        public function getTitle()
        {
            return parent::getTitle() . ' - ' . Zurmo::t('EmailTemplatesModule', 'Template Builder');
        }

        protected static function resolveContainingViewClassNames()
        {
            return array('GeneralDataForEmailTemplateWizardView',
                         'SelectBaseTemplateForEmailTemplateWizardView',
                         'BuilderCanvasWizardView',
                         'ContentForEmailTemplateWizardView');
        }

        protected function getBeforeValidateActionScript()
        {
            $validationScenarioInputId          = static::getValidationScenarioInputId();
            $serializedDataValidationScenario   = EmailTemplateWizardForm::SERIALIZED_DATA_VALIDATION_SCENARIO;
            return "js:function(form)
                        {
                            if ($('#{$validationScenarioInputId}').val() == '{$serializedDataValidationScenario}')
                            {
                                $(this).beforeValidateAction
                                emailTemplateEditor.freezeLayoutEditor();
                                emailTemplateEditor.compileSerializedData();
                            }
                            return true;
                        }";
        }

        /**
         * collapse left side bar by default to give more room for canvas
         */
        protected function registerScripts()
        {
            parent::registerScripts();
            // Begin Not Coding Standard
            Yii::app()->clientScript->registerScript('builderEmailTemplateCollapseLeftSideView', "
                if(!$('body').hasClass('nav-collapsed'))
                {
                    $('body').addClass('nav-collapsed');
                }
            ");
            // End Not Coding Standard
        }

        protected function renderContainingViews(WizardActiveForm $form)
        {
            $content            = parent::renderContainingViews($form);
            $content            .= $this->resolvePreviewContainerContent();
            return $content;
        }

        protected function resolvePreviewContainerContent()
        {
            $this->registerPreviewIFrameContainerCloserLinkClick();
            $content  = ZurmoHtml::link(ZurmoHtml::tag('span', array('class' => 'z-label'), Zurmo::t('Core', 'Close')),
                '#', array('id' => BuilderCanvasWizardView::PREVIEW_IFRAME_CONTAINER_CLOSE_LINK_ID, 'class' => 'default-btn'));
            $content .= ZurmoHtml::tag('iframe', $this->resolvePreviewIFrameHtmlOptions(), '');
            $this->wrapContentInDiv($content, $this->resolvePreviewIFrameContainerHtmlOptions());
            return $content;
        }

        protected function registerPreviewIFrameContainerCloserLinkClick()
        {
            Yii::app()->clientScript->registerScript('previewIFrameContainerCloserLinkClick', '
                $("#' . BuilderCanvasWizardView::PREVIEW_IFRAME_CONTAINER_CLOSE_LINK_ID . '").unbind("click.reviewIFrameContainerCloserLinkClick")
                                                    .bind("click.reviewIFrameContainerCloserLinkClick", function(event)
                 {
                    $("#' . BuilderCanvasWizardView::PREVIEW_IFRAME_CONTAINER_ID . '").hide();
                    $("body").removeClass("previewing-builder");
                    event.preventDefault();
                 });');
        }

        protected function resolvePreviewIFrameHtmlOptions()
        {
            return array('id' => BuilderCanvasWizardView::PREVIEW_IFRAME_ID,
                // we set it to about:blank instead of preview url to save request and to also have some
                // sort of basic html structure there which we can replace.
                'src'         => 'about:blank',
                'width'       => '100%',
                'height'      => '100%',
                'seamless'    => 'seamless',
                'frameborder' => 0);
        }

        protected function resolvePreviewIFrameContainerHtmlOptions()
        {
            return array('id'    => BuilderCanvasWizardView::PREVIEW_IFRAME_CONTAINER_ID,
                'title' => Zurmo::t('ZurmoModule', 'Preview'),
                'style' => 'display:none');
        }
    }
?>