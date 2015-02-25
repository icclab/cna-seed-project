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

    class BuilderCanvasWizardView extends ComponentForEmailTemplateWizardView
    {
        const REFRESH_CANVAS_FROM_SAVED_TEMPLATE_LINK_ID    = 'refresh-canvas-from-saved-template';

        const CACHED_SERIALIZED_DATA_ATTRIBUTE_NAME         = 'serializedData';

        const CANVAS_IFRAME_ID                              = 'canvas-iframe';

        const PREVIEW_IFRAME_ID                             = 'preview-iframe';

        const PREVIEW_IFRAME_CONTAINER_ID                   = 'preview-iframe-container';

        const PREVIEW_IFRAME_CONTAINER_CLOSE_LINK_ID        = 'preview-iframe-container-close-link';

        const ELEMENT_EDIT_CONTAINER_ID                     = 'element-edit-container';

        const ELEMENT_EDIT_FORM_OVERLAY_CONTAINER_ID        = 'element-edit-form-overlay-container';

        const UL_ELEMENT_TO_PLACE_ID                        = 'building-blocks';

        const ELEMENT_IFRAME_OVERLAY_ID                     = 'iframe-overlay';

        const ELEMENTS_MENU_BUTTON_ID                       = 'builder-elements-menu-button';

        const CANVAS_CONFIGURATION_MENU_BUTTON_ID           = 'builder-canvas-configuration-menu-button';

        const PREVIEW_MENU_BUTTON_ID                        = 'builder-preview-menu-button';

        const ELEMENTS_CONTAINER_ID                         = 'droppable-element-sidebar';

        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return Zurmo::t('EmailTemplatesModule', 'Canvas');
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'builderCanvasPreviousLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'builderCanvasSaveLink';
        }

        public static function resolveValidationScenario()
        {
            return EmailTemplateWizardForm::SERIALIZED_DATA_VALIDATION_SCENARIO;
        }

        /**
         * @return string
         */
        protected function renderFormContent()
        {
            $freezeOverlayContent      = $this->renderFreezeOverlayContent();
            $toolbarContent            = $this->renderLeftSidebarToolbarContent();
            $leftSidebarContent        = $this->renderLeftSidebarContent();
            $rightSidebarContent       = $this->resolveCanvasContent();
            $this->wrapContentForLeftSideBar($leftSidebarContent);
            $this->wrapContentForRightSideBar($rightSidebarContent);
            $content = $freezeOverlayContent . $toolbarContent .
                       $leftSidebarContent . $rightSidebarContent;
            $content = ZurmoHtml::tag('div', $this->resolveContentHtmlOptions(), $content);
            return $content;
        }

        protected function renderLeftSidebarContent()
        {
            $hiddenElements      = null;
            $leftSidebarContent  = $this->resolveElementsSidebarContent();
            $this->renderHiddenElements($hiddenElements, $leftSidebarContent);
            $leftSidebarContent .= $this->renderRefreshCanvasLinkContent($leftSidebarContent);
            return $leftSidebarContent;
        }

        protected function renderLeftSidebarToolbarContent()
        {
            $content  = '<div class="view-toolbar-container clearfix"><nav class="pillbox clearfix">';
            $element  = new EmailTemplateBuilderElementsMenuActionElement('default', 'emailTemplates', null,
                            array('htmlOptions' => array('id'=> static::ELEMENTS_MENU_BUTTON_ID, 'class' => 'active'),
                                  'iconClass'=> 'icon-elements'));
            $content .= $element->render();
            $element  = new EmailTemplateBuilderCanvasConfigurationMenuActionElement('default', 'emailTemplates', null,
                            array('htmlOptions' => array('id'=> static::CANVAS_CONFIGURATION_MENU_BUTTON_ID), 'iconClass'=> 'icon-configuration'));
            $content .= $element->render();
            $content .= '</nav><nav class="pillbox clearfix">';
            $element  = new EmailTemplateBuilderPreviewMenuActionElement('default', 'emailTemplates', null,
                            array('htmlOptions' => array('id'=> static::PREVIEW_MENU_BUTTON_ID), 'iconClass'=> 'icon-preview'));
            $content .= $element->render();
            $content .= '</nav></div>';
            return $content;
        }

        protected function renderFreezeOverlayContent()
        {
            $span = ZurmoHtml::tag('span', array('class' => 'big-spinner'));
            return ZurmoHtml::tag('div', array('id' => static::ELEMENT_IFRAME_OVERLAY_ID, 'class' => 'ui-overlay-block'), $span);
        }

        protected function resolveCachedSerializedDataHiddenInputJQuerySelector()
        {
            return '#' . get_class($this->model) . '_' . static::CACHED_SERIALIZED_DATA_ATTRIBUTE_NAME . '_dom';
        }

        protected function resolveContentHtmlOptions()
        {
            return array('id' => 'builder', 'class' => 'strong-right clearfix');
        }

        protected function renderRefreshCanvasLinkContent()
        {
            $linkContent    = ZurmoHtml::link('Reload Canvas', '#', $this->resolveRefreshCanvasLinkHtmlOptions());
            //@TODO SHOAIBI, does it need to live in a table?
            //$this->wrapContentInTableCell($linkContent, array('colspan' => 2));
            //$this->wrapContentInTableRow($linkContent);
            return $linkContent;
        }

        protected function resolveRefreshCanvasLinkHtmlOptions()
        {
            return array('id' => static::REFRESH_CANVAS_FROM_SAVED_TEMPLATE_LINK_ID, 'style' => 'display:none');
        }

        protected function generateWidgetTagsForUIAccessibleElements($uiAccessibleElements)
        {
            $content    = null;
            foreach ($uiAccessibleElements as $element)
            {
                $content    .=  $element::resolveDroppableWidget('li');
            }
            $content        = ZurmoHtml::tag('ul', $this->resolveWidgetTagsWrapperHtmlOptions(), $content);
            return $content;
        }

        protected function resolveWidgetTagsWrapperHtmlOptions()
        {
            return array('id' => 'building-blocks', 'class' => 'clearfix builder-elements builder-elements-droppable');
        }

        protected function resolveElementsSidebarContent()
        {
            $uiAccessibleElements   = PathUtil::getAllUIAccessibleBuilderElementClassNames();
            $uiAccessibleContent    = $this->generateWidgetTagsForUIAccessibleElements($uiAccessibleElements);
            $this->wrapContentInDiv($uiAccessibleContent, $this->resolveElementsSidebarHtmlOptions());
            return $uiAccessibleContent;
        }

        protected function resolveElementsSidebarHtmlOptions()
        {
            return array('id' => static::ELEMENTS_CONTAINER_ID);
        }

        protected function resolveCanvasContent()
        {
            $canvasContent  = ZurmoHtml::tag('iframe', $this->resolveCanvasIFrameHtmlOptions(), '');
            return $canvasContent;
        }

        protected function resolveCanvasIFrameHtmlOptions()
        {
            return array('id' => static::CANVAS_IFRAME_ID,
                            'src' => 'about:blank',
                            'width' => '100%',
                            'height'    => '100%',
                            'frameborder' => 0);
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            $this->registerEmailTemplateEditorScripts();
            $this->registerLeftSideToolbarScripts();
            $this->registerRefreshCanvasFromSavedTemplateScript();
        }

        protected function registerEmailTemplateEditorScripts()
        {
            $this->registerEmailTemplateEditorScriptFile();
            $this->registerInitializeEmailTemplateEditorScript();
        }

        protected function registerEmailTemplateEditorScriptFile()
        {
            $baseScriptUrl = Yii::app()->assetManager->publish(
                                            Yii::getPathOfAlias('application.modules.emailTemplates.widgets.assets'));
            Yii::app()->clientScript->registerScriptFile($baseScriptUrl . '/EmailTemplateEditor.js',
                                                        CClientScript::POS_HEAD);
        }

        protected function registerInitializeEmailTemplateEditorScript()
        {
            $elementsContainerId                = '#' . static::ELEMENTS_CONTAINER_ID;
            $elementsToPlaceSelector            = '#' . static::UL_ELEMENT_TO_PLACE_ID;
            $iframeSelector                     = '#' . static::CANVAS_IFRAME_ID;
            $editSelector                       = '#' . static::ELEMENT_EDIT_CONTAINER_ID;
            $editFormSelector                   = '#' . static::ELEMENT_EDIT_FORM_OVERLAY_CONTAINER_ID;
            $editActionSelector                 = 'span.' . BaseBuilderElement::OVERLAY_ACTION_EDIT;
            $moveActionSelector                 = 'span.' . BaseBuilderElement::OVERLAY_ACTION_MOVE;
            $deleteActionSelector               = 'span.' . BaseBuilderElement::OVERLAY_ACTION_DELETE;
            $sortableRowsClass                  = BaseBuilderElement::BUILDER_ELEMENT_SORTABLE_ROWS_CLASS;
            $sortableElementsClass              = BaseBuilderElement::BUILDER_ELEMENT_SORTABLE_ELEMENTS_CLASS;
            $cellDroppableClass                 = BaseBuilderElement::BUILDER_ELEMENT_CELL_DROPPABLE_CLASS;
            $iframeOverlaySelector              = '#' . static::ELEMENT_IFRAME_OVERLAY_ID;
            $cachedSerializedSelector           = static::resolveCachedSerializedDataHiddenInputJQuerySelector();
            $errorOnDeleteMessage               = Zurmo::t('EmailTemplatesModule', 'Cannot delete last row');
            $dropHereMessage                    = Zurmo::t('Core', 'Drop here');
            $csrfToken                          = Yii::app()->request->csrfToken;
            $doNotWrapInRow                     = BuilderElementRenderUtil::DO_NOT_WRAP_IN_ROW;
            $wrapInRow                          = BuilderElementRenderUtil::WRAP_IN_ROW;
            $wrapInHeaderRow                    = BuilderElementRenderUtil::WRAP_IN_HEADER_ROW;
            $modelClassName                     = BaseBuilderElement::getModelClassName();
            $editableActionUrl                  = static::resolveElementEditableActionUrl();
            $nonEditableActionUrl               = static::resolveElementNonEditableActionUrl();
            Yii::app()->getClientScript()->registerScript('initializeEmailTemplateEditor', "
                initEmailTemplateEditor = function () {
                    emailTemplateEditor.init(
                        '{$elementsContainerId}',
                        '{$elementsToPlaceSelector}',
                        '{$iframeSelector}',
                        '{$editSelector}',
                        '{$editFormSelector}',
                        '{$editActionSelector}',
                        '{$moveActionSelector}',
                        '{$deleteActionSelector}',
                        '{$sortableRowsClass}',
                        '{$sortableElementsClass}',
                        '{$cellDroppableClass}',
                        '{$iframeOverlaySelector}',
                        '{$cachedSerializedSelector}',
                        '{$editableActionUrl}',
                        '{$nonEditableActionUrl}',
                        '{$errorOnDeleteMessage}',
                        '{$dropHereMessage}',
                        '{$csrfToken}',
                        {$doNotWrapInRow},
                        {$wrapInRow},
                        {$wrapInHeaderRow},
                        '{$modelClassName}'
                    );
                };
                ", CClientScript::POS_END);
        }

        protected function registerRefreshCanvasFromSavedTemplateScript()
        {
            Yii::app()->clientScript->registerScript('refreshCanvasFromSavedTemplateScript', "
                $('#" . static::REFRESH_CANVAS_FROM_SAVED_TEMPLATE_LINK_ID . "').unbind('click').bind('click', function(event)
                {
                    emailTemplateEditor.reloadCanvas();
                    event.preventDefault();
                });
                ", CClientScript::POS_READY);
        }

        protected function registerLeftSideToolbarScripts()
        {
            $this->registerElementsMenuButtonClickScript();
            $this->registerCanvasConfigurationMenuButtonClickScript();
            $this->registerPreviewMenuButtonClickScript();
        }

        protected function registerElementsMenuButtonClickScript()
        {
            // Begin Not Coding Standard
            Yii::app()->clientScript->registerScript('elementsMenuButtonClickScript', '
                $("#' . static::ELEMENTS_MENU_BUTTON_ID . '").unbind("click.elementsMenuButtonClickScript")
                                                        .bind("click.elementsMenuButtonClickScript", function(event)
                 {
                    if(!$("#' . static::ELEMENTS_MENU_BUTTON_ID . '").hasClass("active"))
                    {
                        $("#' . static::ELEMENTS_MENU_BUTTON_ID . '").addClass("active");
                    }
                    $("#' . static::CANVAS_CONFIGURATION_MENU_BUTTON_ID . '").removeClass("active");
                    $("#' . static::ELEMENT_EDIT_CONTAINER_ID . '").hide();
                    $("#' . static::ELEMENTS_CONTAINER_ID . '").show();
                    event.preventDefault();
                 });');
            // End Not Coding Standard
        }

        protected function registerCanvasConfigurationMenuButtonClickScript()
        {
            // Begin Not Coding Standard
            Yii::app()->clientScript->registerScript('canvasConfigurationMenuButtonClickScript', '
                $("#' . static::CANVAS_CONFIGURATION_MENU_BUTTON_ID . '").unbind("click.canvasConfigurationMenuButtonClick")
                                                    .bind("click.canvasConfigurationMenuButtonClick", function(event)
                 {
                    if(!$("#' . static::CANVAS_CONFIGURATION_MENU_BUTTON_ID . '").hasClass("active"))
                    {
                        $("#' . static::CANVAS_CONFIGURATION_MENU_BUTTON_ID . '").addClass("active");
                    }
                    $("#' . static::ELEMENTS_MENU_BUTTON_ID . '").removeClass("active");
                    $("#' . static::ELEMENTS_CONTAINER_ID . '").hide();
                    $("#' . static::CANVAS_IFRAME_ID . '").contents()
                            .find(".builder-element-non-editable.element-data.body")
                            .siblings(".' . BaseBuilderElement::OVERLAY_ACTIONS_CONTAINER_CLASS . '")
                            .find(".' . BaseBuilderElement::OVERLAY_ACTION_EDIT . '").trigger("click");
                    event.preventDefault();
                    });');
            // End Not Coding Standard
        }

        protected function registerPreviewMenuButtonClickScript()
        {
            $ajaxOption     = $this->resolvePreviewAjaxOptions();
            // Begin Not Coding Standard
            Yii::app()->clientScript->registerScript('previewMenuButtonClickScript', '
                $("#' . static::PREVIEW_MENU_BUTTON_ID . '").unbind("click.previewMenuButtonClick")
                                                            .bind("click.previewMenuButtonClick", function(event){
                    ' . ZurmoHtml::ajax($ajaxOption) . '
                    $("body").addClass("previewing-builder");
                    event.preventDefault();
                });');
            // End Not Coding Standard
        }

        protected function resolvePreviewAjaxOptions()
        {
            $message   = Zurmo::t('EmailTemplatesModule', 'There was an error generating preview');
            $ajaxArray                  = static::resolveErrorAjaxCallback($message);
            $ajaxArray['cache']         = 'false';
            $ajaxArray['url']           = static::resolvePreviewActionUrl();
            $ajaxArray['type']          = 'POST';
            // Begin Not Coding Standard
            $ajaxArray['data']          = 'js:(function(){
                                                jsonSerializedData = {dom: $.parseJSON(emailTemplateEditor.compileSerializedData())};
                                                serializedData     = JSON.stringify(jsonSerializedData);
                                                requestData = {serializedData: serializedData,
                                                                "YII_CSRF_TOKEN": "' . addslashes(Yii::app()->request->csrfToken) .
                                                                '"};
                                                return requestData;
                                            })()';
            $ajaxArray['beforeSend']    = 'js:function(){
                                            $("#' . static::PREVIEW_IFRAME_CONTAINER_ID . '").show();
                                        }';
            $ajaxArray['success']       = 'js:function (html){
                                            $("#' . static::PREVIEW_IFRAME_ID . '").contents().find("html").html(html);
                                        }';
            // End Not Coding Standard
            return $ajaxArray;
        }

        protected static function resolveCompleteAjaxCallbackForSpinnerRemoval($formName)
        {
            $script             = "emailTemplateEditor.unfreezeLayoutEditor();";
            $parentScript       = parent::resolveCompleteAjaxCallbackForSpinnerRemoval($formName);
            $script             = $script . PHP_EOL . $parentScript;
            return $script;
        }

        protected static function resolveSuccessAjaxCallbackForPageTransition($formName, $nextPageClassName,
                                                                              $validationInputId, $progressPerStep,
                                                                              $stepCount, $model)
        {
            $script                     = static::resolveHideCanvasScript();
            $parentScript               = parent::resolveSuccessAjaxCallbackForPageTransition($formName,
                                                                                                $nextPageClassName,
                                                                                                $validationInputId,
                                                                                                $progressPerStep,
                                                                                                $stepCount, $model);
            $script                     = $script . PHP_EOL . $parentScript;
            return $script;
        }

        public static function resolvePreviousPageScript(& $script)
        {
            $script     = static::resolveHideCanvasScript() . PHP_EOL . $script;
        }

        protected static function resolveHideCanvasScript()
        {
            return "$('#" . static::ELEMENT_EDIT_CONTAINER_ID . "').hide();";
        }
    }
?>