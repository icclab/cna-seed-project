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

    class SelectBaseTemplateForEmailTemplateWizardView extends ComponentForEmailTemplateWizardView
    {
        const CHOSEN_DIV_ID                                     = 'chosen-layout';

        const TEMPLATES_DIV_ID                                  = 'templates';

        const BASE_TEMPLATE_RADIO_BUTTON_ATTRIBUTE_NAME         = 'baseTemplateId';

        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return null;
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'selectBaseTemplatePreviousLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'selectBaseTemplateNextLink';
        }

        public static function resolveValidationScenario()
        {
            return EmailTemplateWizardForm::SELECT_BASE_TEMPLATE_VALIDATION_SCENARIO;
        }

        /**
         * @return string
         */
        protected function renderFormContent()
        {
            $leftSideContent           =  null;
            $hiddenElements            = null;
            $this->renderSerializedDataHiddenFields($hiddenElements);
            $leftSideContent  = $this->renderSelectedLayout();
            $leftSideContent .= $this->renderSelectBaseTemplateForm();
            $this->renderHiddenElements($hiddenElements, $leftSideContent);
            $content         = $leftSideContent;
            return $content;
        }

        protected function renderSelectedLayout()
        {
            $textForLink = ZurmoHtml::tag('span', array('class' => 'z-label'),
                                          Zurmo::t('EmailTemplatesModule', 'Select a different layout'));
            $content  = $this->resolveThumbnail();
            $content .= ZurmoHtml::tag('h3', array(), $this->model->name);
            $content .= ZurmoHtml::link($textForLink, '#', array('id' => 'chooser-overlay', 'class' => 'secondary-button'));
            $this->wrapContentInDiv($content, $this->getHtmlOptionsForSelectedLayoutDiv());
            return $content;
        }

        protected function getHtmlOptionsForSelectedLayoutDiv()
        {
            return array(
                'id'    => static::CHOSEN_DIV_ID,
                'class' => 'clearfix',
                'style' => 'display: block;',
            );
        }

        protected function resolveThumbnail()
        {
            $unserializedData   = CJSON::decode($this->model->serializedData);
            $icon               = ArrayUtil::getArrayValue($unserializedData, 'icon');
            if (!empty($icon))
            {
                return ZurmoHtml::icon($icon);
            }
            else
            {
                return ZurmoHtml::icon('icon-user-template');
            }
        }

        protected function renderSelectBaseTemplateForm()
        {
            $element = new SelectBaseTemplateElement($this->model, 'baseTemplateId', $this->form);
            $element->editableTemplate = '{content}{error}';
            $content = $element->render();
            $this->wrapContentInDiv($content, $this->getHtmlOptionsForSelectBaseTemplatesDiv());
            return $content;
        }

        protected function getHtmlOptionsForSelectBaseTemplatesDiv()
        {
            return array(
                'id'    => static::TEMPLATES_DIV_ID,
                'style' => 'display: none;',
            );
        }

        protected function renderSerializedDataHiddenFields(& $hiddenElements)
        {
            $baseTemplateId = $this->getBaseTemplateId();
            $this->renderHiddenField($hiddenElements, 'serializedData[baseTemplateId]', $baseTemplateId);
            $this->renderHiddenField($hiddenElements, 'originalBaseTemplateId', $baseTemplateId);
            $this->renderHiddenField($hiddenElements, BuilderCanvasWizardView::CACHED_SERIALIZED_DATA_ATTRIBUTE_NAME . '[dom]', null);
        }

        protected function getBaseTemplateId()
        {
            $unserializedData   = CJSON::decode($this->model->serializedData);
            $baseTemplateId     = (isset($unserializedData['baseTemplateId']))? $unserializedData['baseTemplateId'] : null;
            return $baseTemplateId;
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            $this->registerUpdateBaseTemplateIdHiddenInputOnSelectionChangeScript();
            $this->registerPreSelectBaseTemplateScript();
            $this->registerUpdateBaseTemplatesByDivIdScript();
            $this->registerResetBaseTemplateIdScript();
            $this->registerResetOriginalBaseTemplateIdScript();
            $this->registerResetSerializedDomDataScript();
            $this->registerChooserButtonClickScript();
            $this->registerChooserCloseButtonClickScript();
        }

        protected function registerResetBaseTemplateIdScript()
        {
            Yii::app()->clientScript->registerScript('resetBaseTemplateIdScript', "
                function resetBaseTemplateId()
                {
                    $('" . static::resolveBaseTemplateIdHiddenInputJQuerySelector() . "').val('');
                }
            ", CClientScript::POS_HEAD);
        }

        protected function registerResetOriginalBaseTemplateIdScript()
        {
            Yii::app()->clientScript->registerScript('resetOriginalBaseBaseTemplateIdScript', "
                function resetOriginalBaseBaseTemplateId()
                {
                    $('" . static::resolveOriginalBaseTemplateIdHiddenInputJQuerySelector() . "').val('');
                }
            ", CClientScript::POS_HEAD);
        }

        protected function registerResetSerializedDomDataScript()
        {
            Yii::app()->clientScript->registerScript('resetSerializedDomDataScript', "
                function resetSerializedDomData()
                {
                    $('" . $this->resolveSerializedDomDataHiddenInputJQuerySelector() . "').val('');
                }
            ", CClientScript::POS_HEAD);
        }

        protected function registerPreSelectBaseTemplateScript()
        {
            Yii::app()->clientScript->registerScript('preSelectBaseTemplateScript', "
                function updateSelectedLayout(item)
                {
                    $('#chosen-layout').find('i').removeClass().addClass(item.data('icon'));
                    $('#chosen-layout').find('h3').html(item.data('name'));
                }
                function preSelectBaseTemplate()
                {
                    baseTemplateId  = $('" . static::resolveBaseTemplateIdHiddenInputJQuerySelector() . "').val();
                    if (baseTemplateId == '')
                    {
                        baseTemplateId = $('" . $this->resolveBaseTemplateListItemsJQuerySelector() . " li:nth-child(2)').data('value');
                        $('" . static::resolveBaseTemplateIdHiddenInputJQuerySelector() . "').val(baseTemplateId);
                        updateSelectedLayout($('" . $this->resolveBaseTemplateListItemsJQuerySelector() . " li:nth-child(2)'));
                    }
                }
                preSelectBaseTemplate();
            ", CClientScript::POS_READY);
        }

        protected function registerUpdateBaseTemplateIdHiddenInputOnSelectionChangeScript()
        {
            Yii::app()->clientScript->registerScript('updateBaseTemplateIdHiddenInputOnSelectionChangeScript', "
                function updateBaseTemplateIdHiddenInputValue(value)
                {
                    $('" . static::resolveBaseTemplateIdHiddenInputJQuerySelector() . "').val(value);
                }

                $('" . $this->resolveBaseTemplateListItemsJQuerySelector() . "').unbind('change').bind('change', function()
                {
                    originalBaseTemplateId  = $('" . $this->resolveOriginalBaseTemplateIdHiddenInputJQuerySelector() . "').val();

                    currentSelectedValue    = $(this).val();
                    // show warning only on edit when a user has already been to canvas once.
                    if (originalBaseTemplateId != '' && currentSelectedValue != originalBaseTemplateId)
                    {
                        if (!confirm('" . Zurmo::t('EmailTemplatesModule', 'Changing base template would trash any existing design made on canvas.') ."'))
                        {
                            return false;
                        }
                    }
                    updateBaseTemplateIdHiddenInputValue(currentSelectedValue);
                    return true;
                });
                ", CClientScript::POS_END);
        }

        protected function registerUpdateBaseTemplatesByDivIdScript()
        {
            // Begin Not Coding Standard
            Yii::app()->clientScript->registerScript('updateBaseTemplatesByDivIdScript', "
                function updateBaseTemplatesByDivId(divId, data)
                {
                    if (data == '')
                    {
                        $('div#' + divId).hide();
                    }
                    else
                    {
                        $('div#' + divId + ' ul').html(data);
                        $('div#' + divId).show();
                    }
                }", CClientScript::POS_HEAD);
            // End Not Coding Standard
        }

        protected function registerChooserButtonClickScript()
        {
            Yii::app()->clientScript->registerScript('chooserButtonClickScript', "
                $('#chooser-overlay').off('click');
                $('#chooser-overlay').on('click', function (event) {
                    $('#" . static::CHOSEN_DIV_ID . "').hide();
                    $('#" . static::TEMPLATES_DIV_ID . "').show();
                    $('#BuilderEmailTemplateWizardView .float-bar').hide();
                    event.preventDefault();
                });
            ");
        }

        protected function registerChooserCloseButtonClickScript()
        {
            Yii::app()->clientScript->registerScript('chooserCloseButtonClickScript', "
                $('." . SelectBaseTemplateElement::CLOSE_LINK_CLASS_NAME . "').click(function(event){
                    $('#" . static::CHOSEN_DIV_ID . "').show();
                    $('#BuilderEmailTemplateWizardView .float-bar').show();
                    $('#" . static::TEMPLATES_DIV_ID . "').hide();
                    event.preventDefault();
                });
            ");
        }

        protected function resolveBaseTemplateIdInputIdWithoutSerializedData()
        {
            $id   = ZurmoHtml::activeId($this->model, static::BASE_TEMPLATE_RADIO_BUTTON_ATTRIBUTE_NAME);
            return $id;
        }

        protected function resolveBaseTemplateListItemsJQuerySelector()
        {
            return SelectBaseTemplateElement::getItemsListJQuerySelector();
        }

        protected function resolveSerializedDomDataHiddenInputJQuerySelector()
        {
            $id = ZurmoHtml::activeId($this->model, 'serializedData[dom]');
            return '#' . $id;
        }

        protected static function resolveBaseTemplateIdHiddenInputJQuerySelector()
        {
            $id = ZurmoHtml::activeId(new BuilderEmailTemplateWizardForm(), 'serializedData[baseTemplateId]');
            return '#' . $id;
        }

        public static function resolveOriginalBaseTemplateIdHiddenInputJQuerySelector()
        {
            $id = ZurmoHtml::activeId(new BuilderEmailTemplateWizardForm(), 'originalBaseTemplateId');
            return '#' . $id;
        }

        protected static function resolveSuccessAjaxCallbackForPageTransition($formName, $nextPageClassName,
                                                                              $validationInputId, $progressPerStep,
                                                                              $stepCount, $model)
        {
            $canvasIFrameSelector       = "#" . BuilderCanvasWizardView::CANVAS_IFRAME_ID;
            $canvasActionUrl            =  static::resolveCanvasActionUrl();
            $refreshCanvasLinkSelector  = "#" . BuilderCanvasWizardView::REFRESH_CANVAS_FROM_SAVED_TEMPLATE_LINK_ID;
            $originalBaseTemplateIdSelector = static::resolveOriginalBaseTemplateIdHiddenInputJQuerySelector();
            $baseTemplateIdSelector         = static::resolveBaseTemplateIdHiddenInputJQuerySelector();
            // Begin Not Coding Standard
            $script         = "
                                initEmailTemplateEditor();
                                selectedBaseTemplateId  = $('" . $baseTemplateIdSelector . "').val();
                                originalBaseTemplateId  = $('" . $originalBaseTemplateIdSelector . "').val();
                                var canvasSourceUrl     = $('" . $canvasIFrameSelector . "').attr('src');
                                if (canvasSourceUrl == 'about:blank' || selectedBaseTemplateId != originalBaseTemplateId)
                                {
                                    // update canvas url
                                    if (canvasSourceUrl == 'about:blank')
                                    {
                                        canvasSourceUrl     = '" . $canvasActionUrl . "';
                                        canvasSourceUrl     = canvasSourceUrl.replace(/id=(\d*)/, 'id=' + data.id);
                                        $('" . $canvasIFrameSelector . "').attr('src', canvasSourceUrl);
                                    }
                                    $('" . $refreshCanvasLinkSelector . "').trigger('click');
                                }
                                $('" . $originalBaseTemplateIdSelector . "').val(selectedBaseTemplateId);

                                ";
            // End Not Coding Standard
            $parentScript   = parent::resolveSuccessAjaxCallbackForPageTransition($formName, $nextPageClassName,
                                                                                    $validationInputId, $progressPerStep,
                                                                                    $stepCount, $model);
            $script         = $script . PHP_EOL . $parentScript;
            return $script;
        }
    }
?>