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
     * Base class for working with the wizard based user interfaces such as reporting and workflow
     */
    abstract class WizardView extends View
    {
        /**
         * @var WizardForm
         */
        protected $model;

        /**
         * whether the model is being copied to 'Save As' or not.
         * @var bool $isBeingCopied
         */
        protected $isBeingCopied;

        /**
         * @return mixed
         */
        abstract protected function registerClickFlowScript();

        /**
         * @param WizardActiveForm $form
         * @return mixed
         */
        abstract protected function renderContainingViews(WizardActiveForm $form);

        /**
         * @param string $formName
         * @return mixed
         */
        abstract protected function renderConfigSaveAjax($formName);

        /**
         * @return string
         */
        public static function getFormId()
        {
            return 'edit-form';
        }

        /**
         * Override in children with correct moduleId
         * @throws NotImplementedException
         */
        public static function getModuleId()
        {
            throw new NotImplementedException();
        }

        /**
         * Override in children with correct controllerId
         * @throws NotImplementedException
         */
        public static function getControllerId()
        {
            return 'default';
        }

        /**
         * @param string $formName
         * @param string $componentViewClassName
         * @param string $reportType
         * @return string
         */
        public static function renderTreeViewAjaxScriptContent($formName, $componentViewClassName, $reportType)
        {
            assert('is_string($formName)');
            assert('is_string($componentViewClassName)');
            assert('is_string($reportType)');
            $url    =  Yii::app()->createUrl(static::getModuleId() . '/' . static::getControllerId() . '/relationsAndAttributesTree',
                array_merge($_GET, array('type' => $reportType,
                    'treeType' => $componentViewClassName::getTreeType())));
            // Begin Not Coding Standard
            $script = "
                $('#" . $componentViewClassName::getTreeDivId() . "').addClass('loading');
                $(this).makeLargeLoadingSpinner('" . $componentViewClassName::getTreeDivId() . "');
                $.ajax({
                    url : '" . $url . "',
                    type : 'POST',
                    data : $('#" . $formName . "').serialize(),
                    success : function(data)
                    {
                        $('#" . $componentViewClassName::getTreeDivId() . "').html(data);
                    },
                    error : function()
                    {
                        //todo: error call
                    }
                });
            ";
            // End Not Coding Standard
            return $script;
        }

        /**
         * @param WizardForm $model
         * @param bool $isBeingCopied
         */
        public function __construct(WizardForm $model, $isBeingCopied = false)
        {
            assert('is_bool($isBeingCopied)');
            $this->model              = $model;
            $this->isBeingCopied = $isBeingCopied;
        }

        /**
         * @return bool
         */
        public function isUniqueToAPage()
        {
            return true;
        }

        /**
         * @return string
         */
        protected static function renderValidationScenarioInputContent()
        {
            $idInputHtmlOptions  = array('id' => static::getValidationScenarioInputId());
            $hiddenInputName     = 'validationScenario';
            return ZurmoHtml::hiddenField($hiddenInputName, static::getStartingValidationScenario(), $idInputHtmlOptions);
        }

        /**
         * Override in children classes. Should @return string
         * @throws NotImplementedException
         */
        protected static function getStartingValidationScenario()
        {
            throw new NotImplementedException();
        }

        /**
         * @return string
         */
        protected static function getValidationScenarioInputId()
        {
            return 'componentType';
        }

        /**
         * @return string
         */
        protected function renderContent()
        {
            $content  = $this->renderForm();
            $this->registerScripts();
            $this->registerCss();
            return $content;
        }

        /**
         * @return string
         */
        protected function renderForm()
        {
            $content  = '<div class="wrapper">';
            $content .= $this->renderTitleContent();
            $content .= '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                            'WizardActiveForm',
                                                            array('id'                      => static::getFormId(),
                                                                  'action'                  => $this->getFormActionUrl(),
                                                                  'enableAjaxValidation'    => true,
                                                                  'clientOptions'           => $this->getClientOptions(),
                                                                  'modelClassNameForError'  => get_class($this->model))
                                                            );
            $content .= $formStart;
            $content .= static::renderValidationScenarioInputContent();
            $content .= $this->renderContainingViews($form);
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= $this->renderAfterFormContent();
            $content .= $this->renderUIOverLayBlock();
            $content .= '</div>';
            $content .= '</div>';
            $content .= $this->renderModalContainer();
            return $content;
        }

        protected function renderUIOverLayBlock()
        {
            $spinner = ZurmoHtml::tag('span', array('class' => 'z-spinner'), '');
            return ZurmoHtml::tag('div', array('class' => 'ui-overlay-block'), $spinner);
        }

        protected function renderAfterFormContent()
        {
        }

        /**
         * @return array
         */
        protected function getClientOptions()
        {
            return array(
                        'validateOnSubmit'  => true,
                        'validateOnChange'  => false,
                        'beforeValidate'    => $this->getBeforeValidateActionScript(),
                        'afterValidate'     => 'js:$(this).afterValidateAjaxAction',
                        'afterValidateAjax' => $this->renderConfigSaveAjax(static::getFormId()),
                    );
        }

        /**
         * The script to be triggered before validation action
         * @see WizardView::getClientOptions
         * @return string
         */
        protected function getBeforeValidateActionScript()
        {
            return 'js:$(this).beforeValidateAction';
        }

        protected function registerScripts()
        {
            //Registered to make sure things work when debug mode is on. Otherwise this is missing.
            Yii::app()->getClientScript()->registerCoreScript('bbq');
            $this->registerOperatorOnLoadAndOnChangeScript();
        }

        protected function registerCss()
        {
            Yii::app()->getClientScript()->registerCssFile(Yii::app()->getClientScript()->getCoreScriptUrl() .
                                                           '/treeview/jquery.treeview.css');
        }

        /**
         * @return mixed
         */
        protected function getFormActionUrl()
        {
            return Yii::app()->createUrl(static::getModuleId() . '/' . static::getControllerId() . '/save',
                array('type' => $this->model->type, 'id' => $this->model->id, 'isBeingCopied' => $this->isBeingCopied));
        }

        /**
         * @param $formName
         * @param bool $redirectAfterSave
         * @param array $additionalAjaxOptions
         * @return string
         */
        protected function getSaveAjaxString($formName, $redirectAfterSave = true, array $additionalAjaxOptions = array())
        {
            assert('is_string($formName)');
            $ajaxArray              = $this->resolveSaveAjaxArray($formName, $redirectAfterSave, $additionalAjaxOptions);
            return ZurmoHtml::ajax($ajaxArray);
        }

        /**
         * @param $formName
         * @param bool $redirectAfterSave
         * @param array $additionalAjaxOptions
         * @return array
         */
        protected function resolveSaveAjaxArray($formName, $redirectAfterSave = true, array $additionalAjaxOptions = array())
        {
            $ajaxArray                  = array('type'      => 'POST',
                                                'cache'     => 'false',
                                                'data'      => 'js:$("#' . $formName . '").serialize()',
                                                'url'       =>  'js:$("#' . $formName . '").attr("action")',
                                                'dataType'  => 'json',
                                            );
            if ($redirectAfterSave)
            {
                $ajaxArray['success']  = 'js:function(data)
                                            {
                                                if (data.redirectToList)
                                                {
                                                    url = "' . $this->resolveSaveRedirectToListUrl() . '";
                                                }
                                                else
                                                {
                                                    url = "' . $this->resolveSaveRedirectToDetailsUrl() . '" + "?id=" + data.id
                                                }
                                                window.location.href = url;
                                            }';
            }
            $ajaxArray                  = CMap::mergeArray($ajaxArray, $additionalAjaxOptions);
            return $ajaxArray;
        }

        /**
         * @return string
         */
        protected function resolveSaveRedirectToDetailsUrl()
        {
            return Yii::app()->createUrl(static::getModuleId() . '/' . static::getControllerId() . '/details');
        }

        /**
         * @return string
         */
        protected function resolveSaveRedirectToListUrl()
        {
            return Yii::app()->createUrl(static::getModuleId() . '/' . static::getControllerId() . '/list');
        }

        protected function registerOperatorOnLoadAndOnChangeScript()
        {
            OperatorStaticDropDownElement::registerOnLoadAndOnChangeScript();
        }

        protected function renderModalContainer()
        {
            return ZurmoHtml::tag('div', array(
                        'id' => ModelElement::MODAL_CONTAINER_PREFIX . '-' . $this->getFormId()
                   ), '');
        }
    }
?>