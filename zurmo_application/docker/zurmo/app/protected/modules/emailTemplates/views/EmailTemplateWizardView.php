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

    abstract class EmailTemplateWizardView extends WizardView
    {
        protected $containingViews;

        /**
         * @return string|void
         */
        public static function getModuleId()
        {
            return 'emailTemplates';
        }

        protected static function resolveContainingViewClassNames()
        {
            throw new NotImplementedException();
        }

        /**
         * @return string
         */
        public function getTitle()
        {
            return Zurmo::t('EmailTemplatesModule', 'Email Template Wizard');
        }

        protected function resolveContainingViews(WizardActiveForm $form)
        {
            if (!isset($this->containingViews))
            {
                $this->initContainingViews($form);
            }
            return $this->containingViews;
        }

        protected function initContainingViews(WizardActiveForm $form)
        {
            $viewClassNames         = static::resolveContainingViewClassNames();
            $views                  = array();
            foreach ($viewClassNames as $id => $view)
            {
                $views[]                = new $view($this->model, $form, (bool)$id);
            }
            $this->containingViews  = $views;
        }

        protected function renderAfterFormContent()
        {
            $content = parent::renderAfterFormContent();
            return $content . $this->renderElementEditContainerAndContent();
        }

        protected function renderElementEditContainerAndContent()
        {
            $editFormContent        = $this->resolveEditFormContent();
            $content                = ZurmoHtml::tag('div', array('id'    => BuilderCanvasWizardView::ELEMENT_EDIT_CONTAINER_ID,
                                                                  'style' => 'display:none'),
                                                     $editFormContent);
            return $content;
        }

        protected function resolveEditFormContent()
        {
            $content = ZurmoHtml::tag('div', array('id' => BuilderCanvasWizardView::ELEMENT_EDIT_FORM_OVERLAY_CONTAINER_ID), '');
            return $content;
        }

        /**
         * @return string
         */
        protected static function getStartingValidationScenario()
        {
            return EmailTemplateWizardForm::GENERAL_DATA_VALIDATION_SCENARIO;
        }

        protected function renderContainingViews(WizardActiveForm $form)
        {
            $views              = $this->resolveContainingViews($form);
            $rows               = count($views);
            $gridView = new GridView($rows, 1);
            foreach ($views as $row => $view)
            {
                $gridView->setView($view, $row, 0);
            }
            $content            = $gridView->render();
            return $content;
        }

        protected function renderConfigSaveAjax($formName)
        {
            assert('is_string($formName)');
            $script             = "linkId = $('#" . $formName . "').find('.attachLoadingTarget').attr('id');";
            $script            .= $this->renderTreeViewAjaxScriptContentForMergeTagsView();
            $viewClassNames     = static::resolveContainingViewClassNames();
            $progressPerStep    = $this->resolveProgressPerStep();
            $validationInputId  = static::getValidationScenarioInputId();
            foreach ($viewClassNames as $id => $viewClassName)
            {
                $script         .= $this->resolveNextPageScript($formName, $viewClassName,
                                                                $validationInputId, $progressPerStep, $id);
                $this->registerPreviousPageScript($viewClassName, $validationInputId, $progressPerStep, $id);
            }
            return $script;
        }

        protected function resolveSaveRedirectToListUrl()
        {
            $action = $this->resolveListActionByEmailTemplateType();
            return Yii::app()->createUrl(static::getModuleId() . '/' . static::getControllerId() . '/' . $action);
        }

        protected function getFormActionUrl()
        {
            return Yii::app()->createUrl(static::getModuleId() . '/' . static::getControllerId() . '/save',
                                                    array('builtType' => $this->model->builtType));
        }

        protected function resolveListActionByEmailTemplateType()
        {
            $action = 'ListForMarketing';
            if (Yii::app()->request->getQuery('type') == EmailTemplate::TYPE_WORKFLOW)
            {
                $action = 'ListForWorkflow';
            }
            return $action;
        }

        protected function renderTreeViewAjaxScriptContentForMergeTagsView()
        {
            if ($this->model->isWorkflowTemplate())
            {
                $view = new MergeTagsView('EmailTemplate',
                    get_class($this->model) . '_textContent',
                    get_class($this->model) . '_htmlContent', false); //todo: get these last 2 values dynamically
                $view->modelClassNameSelector = GeneralDataForEmailTemplateWizardView::
                    resolveModelClassNameHiddenInputJQuerySelector();
                return $view->renderTreeViewAjaxScriptContent();
            }
        }

        protected function resolveNextPageScript($formName, $viewClassName, $validationInputId, $progressPerStep, $stepCount)
        {
            $scriptPrefix       = 'if';
            if ($stepCount)
            {
                $scriptPrefix	= 'else if'; // Not Coding Standard
            }
            $ajaxOptions        = $this->resolveAdditionalAjaxOptions($formName, $viewClassName, $validationInputId,
                                                                        $progressPerStep, $stepCount);
            $nextPageLinkId     = $viewClassName::getNextPageLinkId();
            $redirectAfterSave  = $viewClassName::redirectAfterSave();
            $script             = $this->getSaveAjaxString($formName, $redirectAfterSave, $ajaxOptions);
            // Begin Not Coding Standard
            $script             = $scriptPrefix . " (linkId == '" . $nextPageLinkId . "')
                                    {
                                        " . $script . "
                                    }";
            // End Not Coding Standard
            return $script;
        }

        protected function resolveAdditionalAjaxOptions($formName, $viewClassName, $validationInputId,
                                                        $progressPerStep, $stepCount)
        {
            $nextPageClassName  = static::resolveNextPageClassName($stepCount);
            $ajaxOptions        = $viewClassName::resolveAdditionalAjaxOptions($formName, $validationInputId,
                                                                                $progressPerStep, $stepCount, $nextPageClassName, $this->model);
            return $ajaxOptions;
        }

        protected function registerPreviousPageScript($viewClassName, $validationInput, $progressPerStep, $stepCount)
        {
            $previousPageLinkId         = $viewClassName::getPreviousPageLinkId();
            $scriptName                 = "clickflow." . $previousPageLinkId;
            $eventName                  = "click." . $previousPageLinkId;
            $previousPageClassName      = static::resolvePreviousPageClassName($stepCount);
            if (!isset($previousPageClassName))
            {
                $script     = $this->resolvePreviousPageScriptForFirstStep($previousPageLinkId);
            }
            else
            {
                $script     = $this->resolvePreviousPageScriptForStep($viewClassName, $previousPageClassName, $validationInput, $progressPerStep, $stepCount);
            }

            $script         = "
            $('#" . $previousPageLinkId . "').unbind('" . $eventName . "').bind('" . $eventName . "', function()
            {
                " . $script . "
            });";
            Yii::app()->clientScript->registerScript($scriptName, $script);
        }

        protected function resolvePreviousPageScriptForFirstStep()
        {
            $script     = "
                            url = '" . $this->resolveSaveRedirectToListUrl() . "';
                            window.location.href = url;
                            return false;";
            return $script;
        }

        protected function resolvePreviousPageScriptForStep($viewClassName, $previousPageClassName, $validationInputId,
                                                            $progressPerStep, $stepCount)
        {
            $validationScenario     = $previousPageClassName::resolveValidationScenario();
            $progress               = $stepCount * $progressPerStep;
            $script                 = "
                            $('#" . $validationInputId . "').val('" . $validationScenario . "');
                            $('#" . $previousPageClassName . "').show();
                            $('#" . $viewClassName . "').hide();
                            $('.StepsAndProgressBarForWizardView').find('.progress-bar').width('" . $progress ."%');
                            $('.StepsAndProgressBarForWizardView').find('.current-step').removeClass('current-step')
                                                                    .prev().addClass('current-step');
                            return false;
                    ";
            $viewClassName::resolvePreviousPageScript($script);
            return $script;
        }

        protected function resolveProgressPerStep()
        {
            return (100 / (count(static::resolveContainingViewClassNames())));
        }

        protected function registerClickFlowScript()
        {
            // this is here just because its abstract in parent.
        }

        protected function resolveNextPageClassName($currentId)
        {
            return ArrayUtil::getArrayValue(static::resolveContainingViewClassNames(), $currentId + 1 );
        }

        protected function resolvePreviousPageClassName($currentId)
        {
            return ArrayUtil::getArrayValue(static::resolveContainingViewClassNames(), $currentId - 1 );
        }

        protected function wrapContentInDiv(& $content, $htmlOptions = array())
        {
            $content = ZurmoHtml::tag('div', $htmlOptions, $content);
        }
    }
?>