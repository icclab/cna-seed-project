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

    abstract class ComponentForEmailTemplateWizardView extends ComponentForWizardModelView
    {
        /**
         * @return string
         */
        public function getTitle()
        {
            return static::getWizardStepTitle();
        }

        /**
         * Override if the view should show a previous link.
         */
        protected function renderPreviousPageLinkContent()
        {
            $label  = ZurmoHtml::tag('span', array('class' => 'z-label'), $this->renderPreviousPageLinkLabel());
            $link   = ZurmoHtml::link($label, '#', $this->resolvePreviousPageLinkHtmlOptions());
            return $link;
        }

        protected function resolvePreviousPageLinkHtmlOptions()
        {
            return array('id' => static::getPreviousPageLinkId(), 'class' => 'cancel-button');
        }

        protected function renderPreviousPageLinkLabel()
        {
            return Zurmo::t('Core', 'Previous');
        }

        /**
         * Override if the view should show a next link.
         */
        protected function renderNextPageLinkContent()
        {
            $params                = array();
            $params['label']       = $this->renderNextPageLinkLabel();
            $params['htmlOptions'] = $this->resolveNextPageLinkHtmlOptions();
            $element               = new SaveButtonActionElement(null, null, null, $params);
            return $element->render();
        }

        protected function resolveNextPageLinkHtmlOptions()
        {
            if ($this->model->builtType == EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE)
            {
                return array('id' => static::getNextPageLinkId(), 'onclick' => 'js:$(this).addClass("attachLoadingTarget");$(this).addClass("loading");$(this).makeOrRemoveLoadingSpinner(true);');
            }
            else
            {
                return array('id' => static::getNextPageLinkId(), 'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            }
        }

        protected function renderNextPageLinkLabel()
        {
            return Zurmo::t('Core', 'Next');
        }

        protected static function getControllerId()
        {
            return 'default';
        }

        protected static function getModuleId()
        {
            return 'emailTemplates';
        }

        protected function renderTextElement(& $content, $attributeName, $template = null)
        {
            $element            = new TextElement($this->model, $attributeName, $this->form);
            if (isset($template))
            {
                $element->editableTemplate = $template;
            }
            $elementContent    = $element->render();
            $this->wrapContentInTableRow($elementContent);
            $content            .= $elementContent;
        }

        protected function renderHiddenField(& $hiddenElements, $attributeName, $value = null)
        {
            $hiddenElements .= ZurmoHtml::hiddenField(ZurmoHtml::activeName($this->model, $attributeName),
                                                    $value,
                                                    array('id' => ZurmoHtml::activeId($this->model, $attributeName)));
        }

        protected function wrapContentInTableRow(& $content, $htmlOptions = array())
        {
            $content = ZurmoHtml::tag('tr', $htmlOptions, $content);
        }

        protected function wrapContentInTableCell(& $content, $htmlOptions = array())
        {
            $content = ZurmoHtml::tag('td', $htmlOptions, $content);
        }

        protected function wrapContentInDiv(& $content, $htmlOptions = array())
        {
            $content = ZurmoHtml::tag('div', $htmlOptions, $content);
        }

        protected function renderHiddenElements($hiddenElements, & $content)
        {
            $this->wrapContentInTableCell($hiddenElements, array('colspan' => 2));
            $this->wrapContentInTableRow($hiddenElements);
            $content .= $hiddenElements;
        }

        protected function wrapContentForLeftSideBar(& $content, $prefix = null)
        {
            $content    = '<table class="form-fields"><colgroup><col class="col-0"><col class="col-1"></colgroup>' . $content;
            $content    .= '</table>';
            $this->wrapContentInDiv($content, array('class' => 'panel'));
            $this->wrapContentInDiv($content, array('class' => 'left-column'));
            $content    = $prefix . $content;
        }

        protected function wrapContentForRightSideBar(& $content)
        {
            $this->wrapContentInDiv($content, array('class' => 'right-side-edit-view-panel'));
            $this->wrapContentInDiv($content, array('class' => 'right-column'));
        }

        protected function wrapContentForAttributesContainer(& $content)
        {
            $this->wrapContentInDiv($content, array('class' => 'attributesContainer'));
        }

        protected function renderLeftAndRightSideBarContentWithWrappers($leftSideBarContent, $rightSideBarContent = null,
                                                                            $leftSideBarPrefix = null)
        {
            $this->wrapContentForLeftSideBar($leftSideBarContent, $leftSideBarPrefix);
            if ($rightSideBarContent)
            {
                $this->wrapContentForRightSideBar($rightSideBarContent);
            }
            $content    = $leftSideBarContent . $rightSideBarContent;
            $this->wrapContentForAttributesContainer($content);
            return $content;
        }

        public static function resolveAdditionalAjaxOptions($formName, $validationInputId, $progressPerStep,
                                                            $stepCount, $nextPageClassName, $model)
        {
            $errorCallback          = static::resolveErrorAjaxCallback();
            $successCallback        = static::resolveSuccessAjaxCallback($formName, $validationInputId, $progressPerStep,
                                                                            $stepCount, $nextPageClassName, $model);
            $completeCallback       = static::resolveCompleteAjaxCallback($formName);
            $ajaxArray              = CMap::mergeArray($errorCallback, $successCallback, $completeCallback);
            return $ajaxArray;
        }

        protected static function resolveSuccessAjaxCallback($formName, $validationInputId, $progressPerStep,
                                                             $stepCount, $nextPageClassName, $model)
        {
            $ajaxArray          = array();
            if (isset($nextPageClassName))
            {
                $callback           = static::resolveSuccessAjaxCallbackForPageTransition($formName, $nextPageClassName,
                                                                                            $validationInputId,
                                                                                            $progressPerStep,
                                                                                            $stepCount, $model);
                $callback           = "js:function(data)
                                        {
                                            ${callback}
                                        }";
                $ajaxArray['success']   = $callback;
            }
            return $ajaxArray;
        }

        protected static function resolveSuccessAjaxCallbackForPageTransition($formName, $nextPageClassName,
                                                                              $validationInputId, $progressPerStep,
                                                                              $stepCount, $model)
        {
            $ownClassName   = get_called_class();
            $progress       = ($stepCount + 2) * $progressPerStep;
            $script         = "
                        $('#" . $validationInputId . "').val('" .  $nextPageClassName::resolveValidationScenario() . "');
                        $('#" . $ownClassName . "').hide();
                        $('#" . $nextPageClassName . "').show();
                        $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('" . $progress . "%');
                        $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step')
                                                                .next().addClass('current-step');
                        ";
            Yii::app()->custom->resolveAdditionalScriptContentForEmailTemplate($stepCount, $script);
            return $script;
        }

        protected static function resolveCompleteAjaxCallback($formName)
        {
            $callback               = static::resolveCompleteAjaxCallbackForSpinnerRemoval($formName);
            $ajaxArray['complete']  = "js:function()
                                        {
                                            ${callback}
                                        }";
            return $ajaxArray;
        }

        protected static function resolveCompleteAjaxCallbackForSpinnerRemoval($formName)
        {
            $script = "
                        $('#" . $formName . "').find('.attachLoadingTarget').removeClass('loading');
                        $('#" . $formName . "').find('.attachLoadingTarget').removeClass('loading-ajax-submit');
                        $('#" . $formName . "').find('.attachLoadingTarget').removeClass('attachLoadingTarget');
                        ";
            return $script;
        }

        public static function resolveErrorAjaxCallback($message = null)
        {
            if ($message === null)
            {
                $message   = Zurmo::t('EmailTemplatesModule',
                                            'There was an error saving EmailTemplatesModuleSingularLabel',
                                            LabelUtil::getTranslationParamsForAllModules());
            }
            $ajaxArray                  = array();
            // Begin Not Coding Standard
            $ajaxArray['error']       = "js:function(data)
                                        {
                                            $('#FlashMessageBar').jnotifyAddMessage({
                                                text: \"" . $message . "\",
                                                permanent: true,
                                                clickOverlay : true,
                                                showIcon: false,
                                            });
                                        }";
            // End Not Coding Standard
            return $ajaxArray;
        }

        protected static function resolveRelativeUrl($action, $params = array())
        {
            return Yii::app()->createUrl(static::getModuleId() . '/' . static::getControllerId() . '/' . $action, $params);
        }

        protected static function resolveCanvasActionUrl($id = 0)
        {
            return static::resolveRelativeUrl('renderCanvas', array('id' => $id));
        }

        protected static function resolvePreviewActionUrl()
        {
            return static::resolveRelativeUrl('renderPreview');
        }

        protected static function resolveElementEditableActionUrl()
        {
            return static::resolveRelativeUrl('renderElementEditable');
        }

        public static function resolveElementNonEditableActionUrl()
        {
            return static::resolveRelativeUrl('renderElementNonEditable');
        }

        public static function resolveValidationScenario()
        {
        }

        public static function redirectAfterSave()
        {
            return false;
        }

        public static function resolvePreviousPageScript(& $script)
        {
        }
    }
?>