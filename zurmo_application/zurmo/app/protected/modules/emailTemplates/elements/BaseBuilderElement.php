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

    abstract class BaseBuilderElement
    {
        /**
         * class name for move action link
         */
        const OVERLAY_ACTION_MOVE   = 'action-move';

        /**
         * class name for edit action link
         */
        const OVERLAY_ACTION_EDIT    = 'action-edit';

        /**
         * class name for delete action link
         */
        const OVERLAY_ACTION_DELETE  = 'action-delete';

        /**
         * class used for overlay actions container
         */
        const OVERLAY_ACTIONS_CONTAINER_CLASS   = 'builder-element-toolbar';

        /**
         * class used for builder elements that can be dropped in cells
         */
        const BUILDER_ELEMENT_CELL_DROPPABLE_CLASS   = 'builder-element-cell-droppable';

        /**
         * class used for builder placeholder of sortable cells
         */
        const BUILDER_ELEMENT_SORTABLE_ELEMENTS_CLASS   = 'sortable-elements';

        /**
         * class used for builder placeholder for sortable rows
         */
        const BUILDER_ELEMENT_SORTABLE_ROWS_CLASS   = 'sortable-rows';

        /**
         * @var string Id of current element, unique.
         */
        protected $id;

        /**
         * @var array properties, frontend(inlineStyles, css, etc), backend(properties required by builder)
         */
        protected $properties;

        /**
         * @var array actual content.
         */
        protected $content;

        /**
         * @var array extra parameters
         */
        protected $params;

        /**
         * @var object contains model for forms
         */
        protected $model;

        /**
         * @var bool if this element is being rendered for canvas or not.
         * Non-editable rendering behavior varies depending on this.
         * @see resolveCustomDataAttributesNonEditable()
         * @see resolveNonEditableActions()
         */
        protected $renderForCanvas = false;

        /**
         * @return bool If this element should be shown on the drag-n-drop sidebar.
         */
        public static function isUIAccessible()
        {
            return false;
        }

        /**
         * Generate the widget html definition to be put on the left sidebar of drag-n-drop elements.
         * @param string $widgetWrapper the html wrapper tag to use for widget html. Defaults to li.
         * @return string
         */
        public static final function resolveDroppableWidget($widgetWrapper = 'li')
        {
            $label = static::resolveLabel();
            $label = ZurmoHtml::tag('span', array(), $label);
            $icon  = ZurmoHtml::tag('i', static::resolveThumbnailHtmlOptions(), '');
            $widget  = ZurmoHtml::tag('div', array('class' => 'clearfix'), $icon . $label);
            return ZurmoHtml::tag($widgetWrapper, static::resolveWidgetHtmlOptions(), $widget);
        }

        /**
         * Return true for container type elements
         * @return bool
         */
        public static function isContainerType()
        {
            return false;
        }

        /**
         * Return the name of model to use with the form in editable representation
         * @return string
         */
        public static final function getModelClassName()
        {
            return 'BuilderElementEditableModelForm';
        }

        /**
         * Return translated label for current Element.
         * @throws NotImplementedException
         */
        protected static function resolveLabel()
        {
            throw new NotImplementedException('Children element should specify their own label');
        }

        /**
         * Returns the element thumbnail name.
         * @return string
         */
        protected static function resolveThumbnailName()
        {
            $name = strtolower(get_called_class());
            $name = str_replace('element', '', $name);
            $name = str_replace('builder', '', $name);
            return $name;
        }

        /**
         * Returns html options to be applied to element thumbnail
         * @return array
         */
        protected static function resolveThumbnailHtmlOptions()
        {
            return array('class' => 'icon-' . static::resolveThumbnailName());
        }

        /**
         * Returns html options to be applied to element's widget html.
         * @return array
         */
        protected static function resolveWidgetHtmlOptions()
        {
            return  array('data-class' => get_called_class(), 'class' => static::resolveWidgetClassesForHtmlOptions());
        }

        protected static function resolveWidgetClassesForHtmlOptions()
        {
            $classes = 'builder-element builder-element-droppable';
            if (!static::isContainerType())
            {
                $classes .= ' ' . static::BUILDER_ELEMENT_CELL_DROPPABLE_CLASS;
            }
            return $classes;
        }

        /**
         * @param bool $renderForCanvas whether element is being rendered for canvas or not.
         * @param null $id the html dom id.
         * @param null $properties properties for this element, inlineStyles, and such.
         * @param null $content content for this element.
         * @param null $params
         */
        public function __construct($renderForCanvas = false, $id = null, $properties = null, $content = null, $params = null)
        {
            $this->renderForCanvas  = $renderForCanvas;
            $this->initId($id);
            $this->initProperties($properties);
            $this->initContent($content);
            $this->initParams($params);
            $this->initModel();
            $this->cleanUpProperties();
        }

        /**
         * Render current element as nonEditable with all the bells and whistles
         * @return string
         */
        public final function renderNonEditable()
        {
            $this->registerNonEditableSnippets();
            $elementContent = $this->renderControlContentNonEditable();
            $wrappedContent = $this->renderControlWrapperNonEditable($elementContent);
            return $wrappedContent;
        }

        /**
         * Rending current element's editable representation
         * @return string
         */
        public final function renderEditable()
        {
            if ($this->doesNotSupportEditable())
            {
                throw new NotSupportedException('This element does not support editable representation');
            }
            $formTitle                  = $this->resolveFormatterFormTitle();
            $formContent                = $this->renderFormContent();
            $content                    = $formTitle . $formContent;
            $content                    = ZurmoHtml::tag('div', array('class' => 'element-edit-form-overlay clearfix'), $content);
            return $content;
        }

        /**
         * If this element should ever be rendered editable
         * @return bool
         */
        protected function doesNotSupportEditable()
        {
            return false;
        }

        /**
         * Register snippets(javascript, css, etc) required for non-editable view of this element.
         */
        protected function registerNonEditableSnippets()
        {
            $this->registerNonEditableScripts();
            $this->registerNonEditableCss();
        }

        /**
         * Register javascript snippets required for non-editable view of this element.
         */
        protected function registerNonEditableScripts()
        {
        }

        /**
         * Register css snippets required for non-editable view of this element.
         */
        protected function registerNonEditableCss()
        {
        }

        /**
         * Returns the non-editable output for current element.
         * @return string
         */
        protected function renderControlContentNonEditable()
        {
            $content    = $this->renderContentElement(null);
            return $content;
        }

        /**
         * Render current element nonEditable with its wrapper including custom data attributes, properties and overlay actions.
         * @param string $elementContent
         * @return string
         */
        protected final function renderControlWrapperNonEditable($elementContent = '{{dummyContent}}')
        {
            $customDataAttributes   = $this->resolveCustomDataAttributesNonEditable();
            $actionsOverlay         = $this->resolveNonEditableActions();
            $content                = $this->resolveWrapperNonEditable($elementContent, $customDataAttributes, $actionsOverlay);
            return $content;
        }

        /**
         * Render the actual wrapper for nonEditable representation bundling provided information.
         * @param $elementContent
         * @param array $customDataAttributes
         * @param $actionsOverlay
         * @return string
         */
        protected function resolveWrapperNonEditable($elementContent, array $customDataAttributes,
                                                        $actionsOverlay)
        {
            $contentSuffix  = null;
            if (!empty($actionsOverlay))
            {
                    $contentSuffix  .= $actionsOverlay;
            }
            $content    = $this->resolveWrapperNonEditableByContentAndProperties($elementContent, $customDataAttributes);
            if ($contentSuffix !== null)
            {
                $content    .= $contentSuffix;
                $content    = $this->wrapNonEditableElementContent($content);
            }
            return $content;
        }

        /**
         * Wrap non-editable content of element into a wrapper
         * @param $content
         * @return string
         */
        protected function wrapNonEditableElementContent($content)
        {
            $content    = ZurmoHtml::tag('div', array('class' => 'element-wrapper'), $content);
            return $content;
        }

        /**
         * Resolve and return html options of Element wrapper.
         * @return array
         */
        protected function resolveNonEditableElementWrapperHtmlOptions()
        {
            return array('class' => 'element-wrapper');
        }

        /**
         * Resolve and return wrapper using provided content and html options for non-editable representation
         * @param $content
         * @param array $customDataAttributes
         * @return string
         */
        protected function resolveWrapperNonEditableByContentAndProperties($content, array $customDataAttributes)
        {
            $options            = $this->resolveNonEditableWrapperOptions($customDataAttributes);
            $content            = ZurmoHtml::tag('div', $options, $content);
            return $content;
        }

        /**
         * Resolve frontend properties for non-editable
         * @return array
         */
        protected function resolveFrontendPropertiesNonEditable()
        {
            $properties = array();
            $frontendProperties = ArrayUtil::getArrayValue($this->properties, 'frontend');
            if ($frontendProperties)
            {
                // we are not on canvas, may be preview or just generating final newsletter.
                // do not render backend properties.
                $properties = $frontendProperties;
            }
            $this->resolveInlineStylePropertiesNonEditable($properties);
            return $properties;
        }

        /**
         * Resolve inline style properties to be applied to nonEditable representation's wrapper as inline style
         * @param array $mergedProperties
         */
        protected final function resolveInlineStylePropertiesNonEditable(array & $mergedProperties)
        {
            $mergedProperties['style'] = '';
            $inlineStyles   = $this->resolveInlineStylesForNonEditable($mergedProperties);
            if ($inlineStyles)
            {
                unset($mergedProperties['inlineStyles']);
                $mergedProperties['style']  = $this->stringifyProperties($inlineStyles, null, null, ':', ';');
            }
            $this->resolveInlineStylesFromBackendPropertiesNonEditable($mergedProperties);
        }

        /**
         * @param array $mergedProperties
         * @return array|null
         */
        protected function resolveInlineStylesForNonEditable(array & $mergedProperties)
        {
            return ArrayUtil::getArrayValue($mergedProperties, 'inlineStyles');
        }

        /**
         * Resolve any inlineStyles we had to put in backend properties
         * @param array $mergedProperties
         */
        protected function resolveInlineStylesFromBackendPropertiesNonEditable(array & $mergedProperties)
        {
            $this->resolveInlineStylesForBorderDirectionNegationFromBackendPropertiesNonEditable($mergedProperties);
        }

        protected function resolveInlineStylesForBorderDirectionNegationFromBackendPropertiesNonEditable(array & $mergedProperties)
        {
            $borderNegationStyles       = ArrayUtil::getNestedValue($this->properties, 'backend[border-negation]');
            if (!empty($borderNegationStyles))
            {
                $borderNegationKeys     = array_keys($borderNegationStyles, 'none');
                foreach ($borderNegationKeys as $borderNegationKey)
                {
                    $mergedProperties['style'] .= "${borderNegationKey}:none;";
                }
            }
        }

        /**
         * Stringify properties by combing keys and values using a set of prefixes and suffices.
         * @param array $properties
         * @param null $keyPrefix
         * @param null $keySuffix
         * @param null $valuePrefix
         * @param null $valueSuffix
         * @return null|string
         */
        protected final function stringifyProperties(array $properties, $keyPrefix = null, $keySuffix = null,
                                                        $valuePrefix = null, $valueSuffix = null)
        {
            $this->sanitizeProperties($properties);
            $content    = $this->stringifyArray($properties, $keyPrefix, $keySuffix, $valuePrefix, $valueSuffix);
            return $content;
        }

        /**
         * Stringify an array by combining keys and value using a set of prefixes and suffices.
         * @param array $array
         * @param null $keyPrefix
         * @param null $keySuffix
         * @param null $valuePrefix
         * @param null $valueSuffix
         * @return null|string
         */
        protected final function stringifyArray(array $array, $keyPrefix = null, $keySuffix = null,
                                                    $valuePrefix = null, $valueSuffix = null)
        {
            $content    = null;
            foreach ($array as $key => $value)
            {
                $content .= $keyPrefix . $key . $keySuffix . $valuePrefix . $value . $valueSuffix;
            }
            return $content;
        }

        /**
         * Resolve the custom data attributes for nonEditable representation wrapper.
         * @return null|string
         */
        protected final function resolveCustomDataAttributesNonEditable()
        {
            if (!$this->renderForCanvas)
            {
                return array();
            }
            $cda['data-class']      = get_class($this);
            $cda['data-properties'] = CJSON::encode($this->properties);
            $cda['data-content']    = CJSON::encode(array());
            if (!$this->isContainerType())
            {
                // we don't want to bloat container type's data-content as it would be recompiled anyway.
                $cda['data-content']    = CJSON::encode($this->content);
            }
            return $cda;
        }

        /**
         * Resolve the nonEditable representation's overlay actions for wrapper.
         * @return null|string
         */
        protected final function resolveNonEditableActions()
        {
            if (!$this->renderForCanvas)
            {
                return null;
            }
            $overlayLinksContent    = $this->resolveAvailableNonEditableActionLinkContent();
            $overlayContent         = ZurmoHtml::tag('div', $this->resolveNonEditableActionsHtmlOptions(), $overlayLinksContent);
            return $overlayContent;
        }

        /**
         * Resolve html options for the nonEditable representation's overlay actions container.
         * @return array
         */
        protected function resolveNonEditableActionsHtmlOptions()
        {
            return array('class' => static::OVERLAY_ACTIONS_CONTAINER_CLASS,
                            'id' => 'element-actions-' . $this->id);
        }

        /**
         * Resolve the nonEditable representation's overlay action items combined together.
         * @return null|string
         */
        protected final function resolveAvailableNonEditableActionLinkContent()
        {
            $availableActions   = $this->resolveAvailableNonEditableActionsArray();
            $overlayLinkContent = null;
            foreach ($availableActions as $action)
            {
                $linkContent        = $this->resolveAvailableNonEditableActionLinkSpan($action);
                $overlayLinkContent .= $linkContent;
            }
            return $overlayLinkContent;
        }

        protected function resolveAvailableNonEditableActionLinkSpan($action)
        {
            $iconContent = ZurmoHtml::tag('i', array('class' => 'icon-' . $action), '');
            return         ZurmoHtml::tag('span', array('class' => $action), $iconContent);
        }

        /**
         * Return the available overlay actions for nonEditable representation
         * @return array
         */
        protected function resolveAvailableNonEditableActionsArray()
        {
            return array(static::OVERLAY_ACTION_MOVE, static::OVERLAY_ACTION_EDIT, static::OVERLAY_ACTION_DELETE);
        }

        /**
         * Resolve default html options for nonEditable representation's wrapper
         * @return array
         */
        protected function resolveNonEditableWrapperHtmlOptions()
        {
            return array('id' => $this->id, 'class' => 'builder-element-non-editable element-data');
        }

        /**
         * Resolve options for non editable wrapper
         * @param array $customDataAttributes
         * @return array|mixed
         */
        protected function resolveNonEditableWrapperOptions(array $customDataAttributes)
        {
            $htmlOptions        = $this->resolveNonEditableWrapperHtmlOptions();
            $frontendOptions    = $this->resolveFrontendPropertiesNonEditable();
            $options            = CMap::mergeArray($htmlOptions, $frontendOptions, $customDataAttributes);
            return $options;
        }

        /**
         * Render Editable representation's Form content.
         * @return string
         */
        protected final function renderFormContent()
        {
            $this->registerActiveFormScripts();
            $clipWidget             = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget($this->resolveActiveFormClassName(),
                                                                     $this->resolveActiveFormOptions());
            $formInputContent       = $this->renderFormInputsContent($form);
            $formEnd                = $this->renderFormActionLinks();
            $formEnd                .= $clipWidget->renderEndWidget();

            $content                = $formStart;
            $content               .= $form->errorSummary($this->model);
            $content               .= $formInputContent;
            $content               .= $formEnd;
            $content                = ZurmoHtml::tag('div', array('class' => 'wide form'), $content);
            $content                = ZurmoHtml::tag('div', array('class' => 'wrapper'), $content);
            $content               .= $this->renderModalContainer($form);
            return $content;
        }

        protected function renderModalContainer($form)
        {
            return ZurmoHtml::tag('div', array(
                'id' => ModelElement::MODAL_CONTAINER_PREFIX . '-' . $form->id
            ), '');
        }

        /**
         * Returns string containing all form input fields properly wrapped in containers.
         * @param ZurmoActiveForm $form
         * @return string
         */
        protected function renderFormInputsContent(ZurmoActiveForm $form)
        {
            $contentTabContent  = $this->renderContentTab($form);

            $settingsTabContent  = $this->renderSettingsTab($form);
            if (isset($settingsTabContent))
            {
                $settingsTabContent  = $this->wrapEditableContentFormContentInTable($settingsTabContent);
            }

            $content             = $this->renderBeforeFormLayout($form);
            if (isset($contentTabContent, $settingsTabContent))
            {
                $content            .= $this->renderWrappedContentAndSettingsTab($contentTabContent, $settingsTabContent);
            }
            else
            {
                $content            = $contentTabContent . $settingsTabContent;
            }
            $content            .= $this->renderHiddenFields($form);
            $content            .= $this->renderAfterFormLayout($form);
            return $content;
        }

        /**
         * Rendering and return content for Content tab.
         * @param ZurmoActiveForm $form
         * @return string
         */
        protected function renderContentTab(ZurmoActiveForm $form)
        {
            $content    = $this->renderContentElement($form);
            return $content;
        }

        /**
         * Wrap content inside a table. Useful for wrapping form content on Content and Settings tab.
         * @param $content
         * @return string
         */
        protected function wrapEditableContentFormContentInTable($content)
        {
            return ZurmoHtml::tag('table', array('class' => 'form-fields'), $content);
        }

        /**
         * Resolve form title.
         */
        protected function resolveFormTitle()
        {
            return static::resolveLabel();
        }

        /**
         * Resolve form title with some formatting.
         * @return string
         */
        protected function resolveFormatterFormTitle()
        {
            $formTitle                  = ZurmoHtml::tag('h3', array(), $this->resolveFormTitle());
            //$formTitle                  = ZurmoHtml::tag('center', array(), $formTitle);
            return $formTitle;
        }

        /**
         * Resolve Class name for Active Form
         * @return string
         */
        protected function resolveActiveFormClassName()
        {
            return 'ZurmoActiveForm';
        }

        /**
         * Resolve Active form options array
         * @return array
         */
        protected final function resolveActiveFormOptions()
        {
            $options = array('id'                       => $this->resolveFormId(),
                             'action'                   => $this->resolveFormActionUrl(),
                             'enableAjaxValidation'     => $this->resolveEnableAjaxValidation(),
                             'clientOptions'            => $this->resolveFormClientOptions(),
                             'htmlOptions'              => $this->resolveFormHtmlOptions());
            $customActiveFormOptions    = $this->resolveActiveFormCustomOptions();
            $options    = CMap::mergeArray($options, $customActiveFormOptions);
            return $options;
        }

        /**
         * Resolve form id
         * @return string
         */
        protected function resolveFormId()
        {
            $formId = $this->id . '-edit-form';
            return $formId;
        }

        /**
         * Resolve form action url. This url is also used by the ajax post.
         * @return mixed
         */
        protected function resolveFormActionUrl()
        {
            return ComponentForEmailTemplateWizardView::resolveElementNonEditableActionUrl();
        }

        /**
         * Render and return any special hidden fields.
         * @param ZurmoActiveForm $form
         * @return string
         */
        protected function renderHiddenFields(ZurmoActiveForm $form)
        {
            $idHiddenInput          = $this->renderHiddenField('id', $this->id);
            $classNameHiddenInput   = $this->renderHiddenField('className', get_class($this));
            $hiddenFields           = $idHiddenInput . $classNameHiddenInput;
            return $hiddenFields;
        }

        /**
         * Render and return a hiddenField.
         * @param $attributeName
         * @param $value
         * @return string
         */
        protected final function renderHiddenField($attributeName, $value)
        {
            return ZurmoHtml::hiddenField(ZurmoHtml::activeName($this->model, $attributeName),
                                                $value,
                                                array('id' => ZurmoHtml::activeId($this->model, $attributeName)));
         }

        /**
         * Wrap content and settings tab into a tab container and return output.
         * @param $contentTab
         * @param $settingsTab
         * @return string
         */
        protected final function renderWrappedContentAndSettingsTab($contentTab, $settingsTab)
        {
            $contentTabClass        = 'active-tab';
            $settingsTabClass       = null;

            $contentTabHyperLink = ZurmoHtml::link($this->renderContentTabLabel(), '#element-content',
                                                   array('class' => $contentTabClass));
            $contentTabDiv       = ZurmoHtml::tag('div', array('id' => 'element-content',
                                                               'class' => $contentTabClass . ' tab element-edit-form-content-tab'),
                                                               $contentTab);
            $settingsTabHyperLink = ZurmoHtml::link($this->renderSettingsTabLabel(), '#element-settings',
                                                    array('class' => $settingsTabClass));
            $settingsTabDiv       = ZurmoHtml::tag('div', array('id' => 'element-settings',
                                                                'class' => $settingsTabClass . ' tab element-edit-form-settings-tab'),
                                                                $settingsTab);
            $this->registerTabbedContentScripts();
            $tabContent             = ZurmoHtml::tag('div', array('class' => 'tabs-nav'),
                                                            $contentTabHyperLink . $settingsTabHyperLink);
            $content                = ZurmoHtml::tag('div', array('class' => 'edit-form-tab-content tabs-container'),
                                                            $tabContent . $contentTabDiv . $settingsTabDiv);
            return $content;
        }

        /**
         * Render Content Tab Label
         * @return string
         */
        protected function renderContentTabLabel()
        {
            return Zurmo::t('Core', 'Content');
        }

        /**
         * Render Settings Tab Label
         * @return string
         */
        protected function renderSettingsTabLabel()
        {
            return Zurmo::t('Core', 'Settings');
        }

        /**
         * Register Javascript to handle tab switches
         */
        protected function registerTabbedContentScripts()
        {
            $scriptName = 'element-edit-form-tab-switch-handler';
            // Begin Not Coding Standard
            Yii::app()->clientScript->registerScript($scriptName, "
                    $('.edit-form-tab-content .tabs-nav a:not(.simple-link)').click( function(event){
                        event.preventDefault();
                        if ( !$(this).hasClass('active-tab') )
                        {
                            //the menu items
                            $('.active-tab', $(this).parent()).removeClass('active-tab');
                            $(this).addClass('active-tab');
                            //the sections
                            var _old = $('.tab.active-tab'); //maybe add context here for tab-container
                            _old.fadeToggle();
                            _old.removeClass('active-tab');
                            var _new = $( $(this).attr('href') );
                            _new.fadeToggle(150, 'linear');
                            _new.addClass('active-tab');
                        }
                    });
                ");
            // End Not Coding Standard
        }

        /**
         * Render form action buttons.
         * @return string
         */
        protected function renderFormActionLinks()
        {
            $content    = $this->renderApplyLink();
            $content   .= $this->renderBackLink();
            $content    = ZurmoHtml::tag('div', array('class' => 'form-toolbar'), $content);
            $content    = ZurmoHtml::tag('div', array('class' => 'view-toolbar-container clearfix'), $content);
            return $content;
        }

        /**
         * Render Back Action Link
         * @return string
         */
        protected function renderBackLink()
        {
            $this->registerBackScript();
            $label  = ZurmoHtml::tag('span', array('class' => 'z-label'), $this->renderBackLinkLabel());
            $link   = ZurmoHtml::link($label, '#', $this->resolveBackLinkHtmlOptions());
            return $link;
        }

        /**
         * Resolve Back Link html options
         * @return array
         */
        protected function resolveBackLinkHtmlOptions()
        {
            return array('id' => $this->resolveBackLinkId(), 'class' => 'cancel-button');
        }

        /**
         * Resolve link id for back Link
         * @return string
         */
        protected function resolveBackLinkId()
        {
            return 'elementEditFormBackLink';
        }

        /**
         * Render Label for Back Link
         * @return string
         */
        protected function renderBackLinkLabel()
        {
            return Zurmo::t('Core', 'Back');
        }

        /**
         * Render Apply Action Link
         * @return string
         */
        protected function renderApplyLink()
        {
            $this->registerApplyClickScript();
            $label                      = $this->renderApplyLinkLabel();
            $htmlOptions                = $this->resolveApplyLinkHtmlOptions();
            $wrappedLabel               = ZurmoHtml::wrapLink($label);
            $link                       = ZurmoHtml::link($wrappedLabel, '#', $htmlOptions);
            return $link;
        }

        /**
         * Resolve html options for Apply link
         * @return array
         */
        protected function resolveApplyLinkHtmlOptions()
        {
            return array('id' => $this->resolveApplyLinkId(), 'class' => 'z-button');
        }

        /**
         * Resolve link id for apply link
         * @return string
         */
        protected function resolveApplyLinkId()
        {
            return 'elementEditFormApplyLink';
        }

        /**
         * Render label for for Apply Link
         * @return string
         */
        protected function renderApplyLinkLabel()
        {
            return Zurmo::t('Core', 'Apply');
        }

        /**
         * Register any additional Javascript snippets
         */
        protected function registerActiveFormScripts()
        {
            $this->registerHideFormScript();
        }

        /**
         * Register javascript snippet to handle clicking apply link
         */
        protected function registerApplyClickScript()
        {
            Yii::app()->clientScript->registerScript('applyClick', "
                $('#" . $this->resolveApplyLinkId() . "').unbind('click').bind('click', function()
                {
                    jQuery.yii.submitForm(this, '', {}); return false;
                });
            ");
        }

        /**
         * Return the script to init the sortable elements in case BuilderElement is a container
         * @return string
         */
        protected function getAjaxScriptForInitSortableElements()
        {
            $ajaxScript = '';
            if ($this->isContainerType())
            {
                $ajaxScript = "emailTemplateEditor.initSortableElements(emailTemplateEditor.settings.sortableElementsSelector,
                                    emailTemplateEditor.settings.sortableElementsSelector,
                                    $('#" . BuilderCanvasWizardView::CANVAS_IFRAME_ID ."').contents());";
            }
            return $ajaxScript;
        }

        /**
         * Register javascript snippet to handle clicking back link
         */
        protected function registerBackScript()
        {
            Yii::app()->clientScript->registerScript('backLinkClick', "
                $('#" . $this->resolveBackLinkId() . "').unbind('click.backLinkClick').bind('click.backLinkClick', function()
                {
                    hideElementEditFormOverlay();
                    $('#" . BuilderCanvasWizardView::ELEMENTS_CONTAINER_ID . "').show();
                });
            ");
        }

        /**
         * Registers a function to hide the form overlay and empty it.
         */
        protected function registerHideFormScript()
        {
            Yii::app()->clientScript->registerScript('hideElementEditFormOverlay', "
                function hideElementEditFormOverlay()
                {
                    $('#" . BuilderCanvasWizardView::ELEMENT_EDIT_CONTAINER_ID . "').hide();
                    $('#" . BuilderCanvasWizardView::ELEMENT_EDIT_FORM_OVERLAY_CONTAINER_ID . "').empty();
                    $('.editing-element').removeClass('editing-element');
                }
            ");
        }

        /**
         * If form should allow ajax validation or not.
         * @return bool
         */
        protected function resolveEnableAjaxValidation()
        {
            return true;
        }

        /**
         * Resolve any special client options
         * @return array
         */
        protected function resolveFormClientOptions()
        {
            return array('beforeValidate'    => 'js:$(this).beforeValidateAction',
                         'afterValidate'     => 'js:$(this).afterValidateAjaxAction',
                         'afterValidateAjax' => $this->renderConfigSaveAjax(),
                         'summaryID'         => WizardActiveForm::makeErrorsSummaryId($this->resolveFormId()),
                         'validateOnSubmit'  => true,
                         'validateOnChange'  => false);
        }

        protected function renderConfigSaveAjax()
        {
            $ajaxOptions = $this->resolveAjaxPostForApplyClickAjaxOptions(); //todo; remove
            return ZurmoHtml::ajax($ajaxOptions);
        }

        /**
         * Resolve Ajax options for when clicking apply on editable form.
         * @return array
         */
        protected function resolveAjaxPostForApplyClickAjaxOptions()
        {
            $hiddenInputId              = ZurmoHtml::activeId($this->model, 'id');
            $message                    = Zurmo::t('EmailTemplatesModule', 'There was an error applying changes');
            $ajaxArray                  = ComponentForEmailTemplateWizardView::resolveErrorAjaxCallback($message);
            //$ajaxArray['cache']         = 'false'; //todo: should by default be used.
            $ajaxArray['url']           = $this->resolveFormActionUrl();
            $ajaxArray['type']          = 'POST';
            // Begin Not Coding Standard
            $ajaxArray['data'] = 'js:$("#' .  $this->resolveApplyLinkId() . '").closest("form").serialize()';
            $ajaxArray['beforeSend']    = "js:function()
                                        {
                                            emailTemplateEditor.freezeLayoutEditor();
                                        }";
            $ajaxArray['success']       = "js:function (html)
                                        {
                                            var replaceElementId        = $('#" . $hiddenInputId . "').val();
                                            var replaceElementInIframe  = $('#" . BuilderCanvasWizardView::CANVAS_IFRAME_ID . "')
                                                                            .contents().find('#' + replaceElementId).parent();
                                            replaceElementInIframe.replaceWith(html);
                                            " . $this->getAjaxScriptForInitSortableElements() . "
                                            emailTemplateEditor.unfreezeLayoutEditor();
                                            emailTemplateEditor.canvasChanged();
                                            emailTemplateEditor.addPlaceHolderForEmptyCells();
                                        }";
            // End Not Coding Standard
            return $ajaxArray;
        }

        /**
         * Resolve html options for form.
         * @return array
         */
        protected function resolveFormHtmlOptions()
        {
            return array('class' => 'element-edit-form', 'onsubmit' => "return false;");
        }

        /**
         * Resolve custom options for form
         * @return array
         */
        protected function resolveActiveFormCustomOptions()
        {
            return array();
        }

        /**
         * Render and return content that should be part of form but added before any input are rendered.
         * @param ZurmoActiveForm $form
         */
        protected function renderBeforeFormLayout(ZurmoActiveForm $form)
        {
        }

        /**
         * Render and return content that should be part of form but added before action links are rendered.
         * @param ZurmoActiveForm $form
         */
        protected function renderAfterFormLayout(ZurmoActiveForm $form)
        {
        }

        /**
         * Generate a unique id
         * @return string
         */
        protected function generateId()
        {
            return (strtolower(get_class($this)) . '_' . uniqid(time() . '_'));
        }

        /**
         * Resolve default properties
         * @return array
         */
        protected function resolveDefaultProperties()
        {
            return array();
        }

        /**
         * Resolve default parameters
         * @return array
         */
        protected function resolveDefaultParams()
        {
            return array();
        }

        /**
         * Initialize Id. Generate a new one if parameter is not set,
         * @param null $id
         */
        protected function initId($id = null)
        {
            if (!isset($id))
            {
                $id     = $this->generateId();
            }
            $this->id   = $id;
        }

        /**
         * Initialize properties. Set to default one if parameter is not set,
         * @param null $properties
         */
        protected function initProperties($properties = null)
        {
            if (!isset($properties))
            {
                $properties   = $this->resolveDefaultProperties();
            }
            $this->properties   = $properties;
        }

        /**
         * Cleanup any empty indexes from properties
         */
        protected function cleanUpProperties()
        {
            if (!ArrayUtil::getArrayValue($this->params, 'doNotCleanUpProperties'))
            {
                $this->properties   = ArrayUtil::recursivelyRemoveEmptyValues($this->properties);
            }
        }

        /**
         * Initialize content. Set to default one if parameter is not set,
         * @param null $content
         */
        protected function initContent($content = null)
        {
            if (!isset($content))
            {
                $content        = $this->resolveDefaultContent();
            }
            $this->content      = $content;
        }

        /**
         * init element params
         * @param null $params
         */
        protected function initParams($params = null)
        {
            $defaultParams  = $this->resolveDefaultParams();
            if (!isset($params))
            {
                $params     = $defaultParams;
            }
            elseif (ArrayUtil::getArrayValue($params, 'mergeDefault'))
            {
                $params     = CMap::mergeArray($defaultParams, $params);
            }
            $this->params   = $params;
        }

        /**
         * init element model
         */
        protected function initModel()
        {
            $this->model    = $this->getModel();
        }

        /**
         * Return a model to be used on forms
         * @return BuilderElementEditableModelForm
         */
        protected function getModel()
        {
            $modelClassName = static::getModelClassName();
            return new $modelClassName($this->content, $this->properties);
        }

        /**
         * Render the content element using provided form
         * @param ZurmoActiveForm $form
         * @return string
         */
        protected final function renderContentElement(ZurmoActiveForm $form = null)
        {
            $elementClassName   = $this->resolveContentElementClassName();
            $attributeName      = $this->resolveContentElementAttributeName();
            $params             = $this->resolveContentElementParams();
            $element            = new $elementClassName($this->model, $attributeName, $form, $params);
            if (isset($form))
            {
                $this->resolveContentElementEditableTemplate($element);
            }
            else
            {
                $this->resolveContentElementNonEditableTemplate($element);
            }
            $content            = $element->render();
            return $content;
        }

        /**
         * Resolve editable template for content element.
         * @param Element $element
         */
        protected function resolveContentElementEditableTemplate(Element $element)
        {
            $element->editableTemplate = str_replace('{error}', '', $element->editableTemplate);
        }

        /**
         * Resolve non editable template for content element.
         * @param Element $element
         */
        protected function resolveContentElementNonEditableTemplate(Element $element)
        {
            // we need to put wrapper div inside td else it breaks the table layout output.
            $element->nonEditableTemplate   = '{content}';
        }

        /**
         * Resolve params to send to Content element's construct
         */
        protected function resolveContentElementParams()
        {
            return $this->resolveDefaultElementParamsForEditableForm();
        }

        /**
         * Resolve and return default params for elements used on content and settings tab.
         * @param string $label
         * @return array
         */
        protected function resolveDefaultElementParamsForEditableForm($label = '')
        {
            $params = BuilderElementPropertiesEditableElementsUtil::resolveDefaultParams($label);
            return $params;
        }

        /**
         * Returns the default content for current element.
         * @return array
         */
        protected function resolveDefaultContent()
        {
            return array();
        }

        /**
         * Render and Return content for Settings Tab. Returning null hides settings tab from appearing.
         * @param ZurmoActiveForm $form
         * @throws NotImplementedException
         */
        protected function renderSettingsTab(ZurmoActiveForm $form)
        {
            throw new NotImplementedException('Children elements should override it, or remove all calls made to it.');
        }

        /**
         * Resolve the class name of the element to use to render content for editable and non editable representation
         * @throws NotImplementedException
         */
        protected function resolveContentElementClassName()
        {
            throw new NotImplementedException('Children elements should override it, or remove all calls made to it.');
        }

        /**
         * Resolve the attribute name to use to render editable and non-editable representation of content element
         * @throws NotImplementedException
         */
        protected function resolveContentElementAttributeName()
        {
            throw new NotImplementedException('Children elements should override it, or remove all calls made to it.');
        }

        /**
         * Getter for $id
         * @return string
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * Getter for $content
         * @param bool $serialized
         * @return array|string
         */
        public function getContent($serialized = false)
        {
            if ($serialized)
            {
                return CJSON::encode($this->content);
            }
            return $this->content;
        }

        /**
         * Getter for $properties
         * @param bool $serialized
         * @return array|string
         */
        public function getProperties($serialized = false)
        {
            if ($serialized)
            {
                return CJSON::encode($this->properties);
            }
            return $this->properties;
        }

        /**
         * Getter for $renderForCanvas
         * @return bool
         */
        public function getRenderForCanvas()
        {
            return $this->renderForCanvas;
        }

        /**
         * Getter for $params
         * @return array
         */
        public function getParams()
        {
            return $this->params;
        }

        public function validate($attribute, $value)
        {
            $rules = $this->getRules();
            if (isset($rules[$attribute]))
            {
                try
                {
                    return call_user_func(array($this, $rules[$attribute]), $value);
                }
                catch (Exception $exception)
                {
                    throw new NotImplementedException();
                }
            }
            return true;
        }

        protected function getRules()
        {
            return array('font-size'        => 'validateInteger',
                         'border-radius'    => 'validateInteger',
                         'border-width'     => 'validateInteger',
                         'line-height'      => 'validateInteger',
                         'border-top-width' => 'validateInteger',
                         'divider-padding'  => 'validateInteger',
                         'height'           => 'validateInteger',
                         'href'             => 'validateUrl');
        }

//todo: properly use Cvalidator for this
        protected function validateInteger($value)
        {
            if ($value == null)
            {
                return true;
            }
            if (!preg_match('/^[0-9]*$/', $value))
            {
                return Zurmo::t('EmailTemplatesModule', 'Use only integers');
            }
            else
            {
                return true;
            }
        }

        protected function validateUrl($value)
        {
            if ($value == null)
            {
                return true;
            }
            $validator = new CUrlValidator();
            if (!$validator->validateValue($value))
            {
                return Zurmo::t('EmailTemplatesModule', 'Use a valid URL.');
            }
            return true;
        }

        public static function getPropertiesSuffixMappedArray()
        {
            //TODO: @sergio: We need to move this to some rules class
            $mappedArray = array(
                'line-height'       => '%',
                'font-size'         => 'px',
                'border-radius'     => 'px',
                'border-width'      => 'px',
                'border-top-width'  => 'px',
                'divider-padding'   => 'px',
                'height'            => 'px',
                'width'             => 'px',
            );
            return $mappedArray;
        }

        protected function sanitizeProperties(array & $properties)
        {
            $propertiesMappedArray = static::getPropertiesSuffixMappedArray();
            foreach ($properties as $key => $value)
            {
                if (isset($propertiesMappedArray[$key]))
                {
                    $properties[$key] .= $propertiesMappedArray[$key];
                }
            }
        }
    }
?>