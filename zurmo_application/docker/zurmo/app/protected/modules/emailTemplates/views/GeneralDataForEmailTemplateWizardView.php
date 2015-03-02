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

    class GeneralDataForEmailTemplateWizardView extends ComponentForEmailTemplateWizardView
    {
        const HIDDEN_ID = 'hiddenId';

        const MODEL_CLASS_NAME_ID = 'modelClassNameForMergeTagsViewId';

        protected $defaultTextAndDropDownElementEditableTemplate    = '<th>{label}<span class="required">*</span></th><td colspan="{colspan}"><div>{content}</div></td>';

        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return Zurmo::t('Core', 'General');
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'generalDataCancelLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'generalDataNextLink';
        }

        public static function resolveValidationScenario()
        {
            return EmailTemplateWizardForm::GENERAL_DATA_VALIDATION_SCENARIO;
        }

        protected function renderPreviousPageLinkLabel()
        {
            return Zurmo::t('Core', 'Cancel');
        }

        /**
         * @return string
         */
        protected function renderFormContent()
        {
            $leftSideContentPrefix                      = $this->form->errorSummary($this->model);
            $leftSideContent                            = null;
            $hiddenElements                             = null;

            $this->renderType($hiddenElements);
            $this->renderBuiltType($hiddenElements);
            $this->renderIsDraft($hiddenElements);
            $this->renderLanguage($hiddenElements);
            $this->renderId($hiddenElements);
            $this->renderModuleClassNameIdForMergeTagsView($hiddenElements);
            $this->renderModelClassName($leftSideContent, $hiddenElements);
            $this->renderName($leftSideContent);
            $this->renderSubject($leftSideContent);
            $this->renderFiles($leftSideContent);
            $this->renderHiddenElements($hiddenElements, $leftSideContent);

            $rightSideContent                           = $this->renderRightSideFormLayout();

            $content                                    = $this->renderLeftAndRightSideBarContentWithWrappers(
                                                                                                $leftSideContent,
                                                                                                $rightSideContent,
                                                                                                $leftSideContentPrefix);
            return $content;
        }

        protected function renderName(& $content)
        {
            $this->renderTextElement($content, 'name', $this->defaultTextAndDropDownElementEditableTemplate);
        }

        protected function renderSubject(& $content)
        {
            $this->renderTextElement($content, 'subject', $this->defaultTextAndDropDownElementEditableTemplate);
        }

        protected function renderType(& $hiddenElements)
        {
            $this->renderHiddenField($hiddenElements, 'type', $this->model->type);
        }

        protected function renderBuiltType(& $hiddenElements)
        {
            $this->renderHiddenField($hiddenElements, 'builtType', $this->model->builtType);
        }

        protected function renderIsDraft(& $hiddenElements)
        {
            $this->renderHiddenField($hiddenElements, 'isDraft', (int)$this->model->isDraft);
        }

        protected function renderId(& $hiddenElements)
        {
            $this->renderHiddenField($hiddenElements, static::HIDDEN_ID, (int)$this->model->id);
        }

        protected function renderModuleClassNameIdForMergeTagsView(& $hiddenElements)
        {
            $hiddenElements .= ZurmoHtml::hiddenField(static::MODEL_CLASS_NAME_ID,
                                                $this->model->modelClassName,
                                                array('id' => static::MODEL_CLASS_NAME_ID));
        }

        protected function renderLanguage(& $hiddenElements)
        {
            $this->renderHiddenField($hiddenElements, 'language', $this->model->language);
        }

        protected function renderModelClassName(& $content, & $hiddenElements)
        {
            if ($this->model->isWorkflowTemplate())
            {
                $element                    = new EmailTemplateModelClassNameElement($this->model,
                                                                                        'modelClassName',
                                                                                        $this->form);
                $element->editableTemplate  = $this->defaultTextAndDropDownElementEditableTemplate;
                $modelClassNameContent      = $element->render();
                $this->wrapContentInTableRow($modelClassNameContent);
                $content                    .= $modelClassNameContent;
            }
            else
            {
                $this->renderHiddenField($hiddenElements, 'modelClassName', 'Contact');
            }
        }

        protected function renderFiles(& $content)
        {
            $element            = new FilesElement($this->model, null, $this->form);
            $this->wrapContentInDiv($filesContent);
            $filesContent       = $element->render();
            $this->wrapContentInTableRow($filesContent);
            $content            .= $filesContent;
        }

        /**
         * @return string
         */
        protected function renderRightSideFormLayout()
        {
            $elementEditableTemplate        = '{label}{content}{error}';
            $ownerElement                   = new OwnerNameIdElement($this->model, 'null', $this->form);
            $ownerElement->editableTemplate = $elementEditableTemplate;
            $ownerElementContent            = $ownerElement->render();
            $ownerElementContent            = ZurmoHtml::tag('div', array('id' => 'owner-box'), $ownerElementContent);

            $permissionsElement             = new EmailTemplateExplicitReadWriteModelPermissionsElement($this->model,
                                                                    'explicitReadWriteModelPermissions', $this->form);
            $permissionsElement->editableTemplate = $elementEditableTemplate;
            $permissionsElementContent      = $permissionsElement->render();
            $content                        = ZurmoHtml::tag('h3', array(), Zurmo::t('ZurmoModule', 'Rights and Permissions'));
            $content                        .= $ownerElementContent . $permissionsElementContent;
            return $content;
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            $this->registerSetIsDraftToZeroScript();
            $this->registerTrashSomeDataOnModuleChangeScript();
            if ($this->model->isWorkflowTemplate())
            {
                $this->registerOnChangeModelClassNameChangeScript();
            }
        }

        protected function registerOnChangeModelClassNameChangeScript()
        {
            $jquerySelector = $this->resolveModuleClassNameJQuerySelector();
            $moduleClassNameSelector    = static::resolveModelClassNameHiddenInputJQuerySelector();
            Yii::app()->clientScript->registerScript('setIsDraftToZero', "
                $('{$jquerySelector}').on('change', function() {
                    $('{$moduleClassNameSelector}').val($(this).val());
                });
                ");
        }

        protected function registerSetIsDraftToZeroScript()
        {
            Yii::app()->clientScript->registerScript('setIsDraftToZero', "
                function setIsDraftToZero()
                {
                    $('" . $this->resolveIsDraftHiddenInputJQuerySelector() ."').val(0);
                }
                ", CClientScript::POS_END);
        }

        protected function resolveIsDraftHiddenInputJQuerySelector()
        {
            $id = ZurmoHtml::activeId($this->model, 'isDraft');
            return '#' . $id;
        }

        protected function resolveModuleClassNameJQuerySelector()
        {
            $name               = ZurmoHtml::activeName($this->model, 'modelClassName');
            $selector           = "select[name^=\"${name}\"]";
            return $selector;
        }

        public static function resolveTemplateIdHiddenInputJQuerySelector($model)
        {
            $id = ZurmoHtml::activeId($model, static::HIDDEN_ID);
            return '#' . $id;
        }

        public static function resolveModelClassNameHiddenInputJQuerySelector()
        {
            return '#' . static::MODEL_CLASS_NAME_ID;
        }

        protected function registerTrashSomeDataOnModuleChangeScript()
        {
            if (!$this->model->isWorkflowTemplate())
            {
                return;
            }
            // Begin Not Coding Standard
            Yii::app()->clientScript->registerScript('trashSomeDataOnModuleChangeScript', "
                $('" . $this->resolveModuleClassNameJQuerySelector() . "').unbind('change.trashSomeDataOnModuleChange')
                                                                .bind('change.trashSomeDataOnModuleChange', function()
                {
                    $('#" . ZurmoHtml::activeId($this->model, 'textContent') . "').val('');
                    if (" . intval($this->model->isPastedHtmlTemplate()) . ")
                    {
                        var htmlContentElement  = $('#" . ZurmoHtml::activeId($this->model, 'htmlContent') . "');
                        $(htmlContentElement).redactor('set', '');
                    }

                    else if (" . intval($this->model->isBuilderTemplate()) . ")
                    {
                        $('" . $this->resolveIsDraftHiddenInputJQuerySelector() ."').val(1);
                        resetBaseTemplateId();
                        resetOriginalBaseBaseTemplateId();
                        resetSerializedDomData();
                        $('#" . SelectBaseTemplateForEmailTemplateWizardView::TEMPLATES_DIV_ID . "').find('.pills').children(':first').click();
                    }
                });
                ");
            // End Not Coding Standard
        }

        protected static function resolveSuccessAjaxCallbackForPageTransition($formName, $nextPageClassName,
                                                                                $validationInputId, $progressPerStep,
                                                                                    $stepCount, $model)
        {
            $actionId                   = Yii::app()->getController()->getAction()->getId();
            $templateIdSelector         = static::resolveTemplateIdHiddenInputJQuerySelector($model);
            $script                     = "if ('create' == '" . $actionId . "')
                                            {
                                                //update id
                                                $('" . $templateIdSelector . "').val(data.id);
                                            }
                                            ";
            $parentScript               = parent::resolveSuccessAjaxCallbackForPageTransition($formName,
                                                                                                $nextPageClassName,
                                                                                                $validationInputId,
                                                                                                $progressPerStep,
                                                                                                $stepCount, $model);
            $script                     = $script . PHP_EOL . $parentScript;
            return $script;
        }
    }
?>