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
     * Base Element used to display text and html areas on email template,
     * autoresponder and campaign edit and detail views
     */
    class EmailTemplateHtmlAndTextContentElement extends Element implements DerivedElementInterface
    {
        const SELECTIVE_TAB_LOAD_KEY    = 'selectiveTabLoad';

        const HTML_CONTENT_INPUT_NAME   = 'htmlContent';

        const TEXT_CONTENT_INPUT_NAME   = 'textContent';

        public $plugins = array('fontfamily', 'fontsize', 'fontcolor', 'mergetags', 'imagegallery');

        public static function getModelAttributeNames()
        {
            return array(
                static::HTML_CONTENT_INPUT_NAME,
                static::TEXT_CONTENT_INPUT_NAME,
            );
        }

        public static function renderModelAttributeLabel($name)
        {
            $labels = static::renderLabels();
            return $labels[$name];
        }

        protected static function renderLabels()
        {
            $labels = array(Zurmo::t('EmailMessagesModule', 'Html Content'),
                            Zurmo::t('EmailMessagesModule', 'Text Content'));
            return array_combine(static::getModelAttributeNames(), $labels);
        }

        protected function renderHtmlContentAreaLabel()
        {
            return static::renderModelAttributeLabel(static::HTML_CONTENT_INPUT_NAME);
        }

        protected function renderTextContentAreaLabel()
        {
            return static::renderModelAttributeLabel(static::TEXT_CONTENT_INPUT_NAME);
        }

        protected function resolveTabbedContent($plainTextContent, $htmlContent, $activeTab = 'text')
        {
            $textClass = 'active-tab';
            $htmlClass = null;
            $htmlTabHyperLink = null;
            $htmlContentDiv = null;
            if ($activeTab == 'html')
            {
                $textClass = null;
                $htmlClass = 'active-tab';
            }
            $textTabHyperLink   = ZurmoHtml::link($this->renderTextContentAreaLabel(),
                                                  '#tab1',
                                                  array('class' => $textClass));
            $plainTextDiv       = ZurmoHtml::tag('div', array('id' => 'tab1',
                                                        'class' => $textClass . ' tab email-template-' . static::TEXT_CONTENT_INPUT_NAME),
                                                    $plainTextContent);
            if (isset($htmlContent))
            {
                $this->registerTabbedContentScripts();
                $this->registerRedactorIframeHeightScripts();
                $htmlTabHyperLink   = ZurmoHtml::link($this->renderHtmlContentAreaLabel(),
                                                          '#tab2',
                                                          array('class' => $htmlClass));
                $htmlContentDiv     = ZurmoHtml::tag('div', array('id' => 'tab2',
                                                        'class' => $htmlClass . ' tab email-template-' . static::HTML_CONTENT_INPUT_NAME),
                                                    $htmlContent);
            }
            $tagsGuideLink      = null;
            if ($this->form)
            {
                $controllerId           = $this->getControllerId();
                $moduleId               = $this->getModuleId();
                $modelId                = $this->model->id;
                $tagsGuideLinkElement   = new MergeTagGuideAjaxLinkActionElement($controllerId, $moduleId, $modelId);
                $tagsGuideLink          = $tagsGuideLinkElement->render();
            }
            $tabContent         = ZurmoHtml::tag('div', array('class' => 'tabs-nav'), $textTabHyperLink . $htmlTabHyperLink . $tagsGuideLink);
            $content            = ZurmoHtml::tag('div', array('class' => 'email-template-content'), $tabContent . $plainTextDiv . $htmlContentDiv);
            if ($this->form)
            {
                $content           .= $this->renderTextAndHtmlContentAreaError();
            }
            return $content;
        }

        protected function registerTabbedContentScripts()
        {
            $scriptName = 'email-templates-tab-switch-handler';
            if (Yii::app()->clientScript->isScriptRegistered($scriptName))
            {
                return;
            }
            else
            {
                Yii::app()->clientScript->registerScript($scriptName, "
                        $('.tabs-nav a:not(.simple-link)').click( function(event)
                        {
                            //the menu items
                            $('.active-tab', $(this).parent()).removeClass('active-tab');
                            $(this).addClass('active-tab');
                            //the sections
                            var _old = $('.tab.active-tab'); //maybe add context here for tab-container
                            _old.fadeToggle();
                            var _new = $( $(this).attr('href') );
                            _new.fadeToggle(150, 'linear', function()
                            {
                                    _old.removeClass('active-tab');
                                    _new.addClass('active-tab');
                                    _new.trigger('tab-changed');
                            });
                            event.preventDefault();
                        });
                    ");
            }
        }

        protected function registerRedactorIframeHeightScripts()
        {
            $scriptName = 'redactor-iframe-height';
            if (Yii::app()->clientScript->isScriptRegistered($scriptName))
            {
                return;
            }
            else
            {
                // Begin Not Coding Standard
                Yii::app()->clientScript->registerScript($scriptName, "
                        $('.redactor-iframe').load(function(){
                            var contentHeight = $('.redactor-iframe').contents().find('body').outerHeight();
                            $('.redactor-iframe').height(contentHeight + 50);
                        });
                    ");
                // End Not Coding Standard
            }
        }

        protected function renderControlNonEditable()
        {
            assert('$this->attribute == null');
            $textContent    = nl2br(Yii::app()->format->text($this->model->textContent));
            $activeTab      = $this->getActiveTab();
            $htmlContent    = $this->renderNonEditableHtmlContentArea();
            $content        = $this->resolveTabbedContent($textContent, $htmlContent, $activeTab);
            return $content;
        }

        protected function renderControlEditable()
        {
            $textContent    = $this->renderTextContentArea();
            $activeTab      = $this->getActiveTab();
            $htmlContent    = $this->renderEditableHtmlContentArea();
            $content        = $this->resolveTabbedContent($textContent, $htmlContent, $activeTab);
            return $content;
        }

        protected function getActiveTab()
        {
            // TODO: @Shoaibi: High: Refactor this
            if ($this->resolveSelectiveLoadOfTabs() && isset($this->form))
            {
                if ($this->isPastedHtmlTemplate() && $this->isHtmlContentNotEmptyOrBothContentsEmpty())
                {
                    return 'html';
                }
                return 'text';
            }
            if ($this->isHtmlContentNotEmptyOrBothContentsEmpty())
            {
                return 'html';
            }
            return 'text';
        }

        protected function isHtmlContentNotEmptyOrBothContentsEmpty()
        {
            return (!empty($this->model->htmlContent) || (empty($this->model->textContent) && empty($this->model->htmlContent)));
        }

        protected function renderEditableHtmlContentArea()
        {
            if (!$this->resolveSelectiveLoadOfTabs() || $this->isPastedHtmlTemplate())
            {
                return $this->renderHtmlContentArea();
            }
            return null;
        }

        protected function renderNonEditableHtmlContentArea()
        {
            if (!$this->resolveSelectiveLoadOfTabs() || !$this->isPlainTextTemplate())
            {
                $url            = Yii::app()->createUrl('emailTemplates/default/getHtmlContent',
                                    array('id' => $this->model->id, 'className' => get_class($this->model)));
                return "<iframe src='{$url}' class='redactor-iframe' width='100%' height='100%' frameborder='0' seamless></iframe>";
            }
            return null;
        }

        // REVIEW : @Shoaibi Create a HTML element out of it.
        protected function renderHtmlContentArea()
        {
            $id                      = $this->getEditableInputId(static::HTML_CONTENT_INPUT_NAME);
            $htmlOptions             = array();
            $htmlContent             = $this->model->htmlContent;
            $htmlOptions['id']       = $id;
            $htmlOptions['name']     = $this->getEditableInputName(static::HTML_CONTENT_INPUT_NAME);
            $cClipWidget             = new CClipWidget();
            $cClipWidget->beginClip("Redactor");
            // Begin Not Coding Standard
            $cClipWidget->widget('application.core.widgets.Redactor', array(
                                'htmlOptions'           => $htmlOptions,
                                'content'               => $htmlContent,
                                'paragraphy'            => "false",
                                'fullpage'              => "true",
                                'deniedTags'            => "false",
                                'plugins'               => CJSON::encode($this->resolvePlugins()),
                                'observeImages'         => 'true',
                                'imageUpload'           => ImageFileModelUtil::getUrlForActionUpload(),
                                'imageGetJson'          => ImageFileModelUtil::getUrlForActionGetUploaded(),
                                'initCallback' => 'function(){
                                    var contentHeight = $(".redactor_box iframe").contents().find("body").outerHeight();
                                    $(".redactor_box iframe").height(contentHeight + 50);
                                }'
            ));
            // End Not Coding Standard
            $cClipWidget->endClip();
            $content                 = ZurmoHtml::label($this->renderHtmlContentAreaLabel(), $id);
            $content                .= $cClipWidget->getController()->clips['Redactor'];
            return $content;
        }

         protected function renderTextContentArea()
         {
            $textContentElement                         = new TextAreaElement($this->model, static::TEXT_CONTENT_INPUT_NAME, $this->form);
            $textContentElement->editableTemplate       = str_replace('{error}', '', $this->editableTemplate);
            return $textContentElement->render();
         }

        protected function renderTextAndHtmlContentAreaError()
        {
            if (strpos($this->editableTemplate, '{error}') !== false)
            {
                $textContentError = $this->form->error($this->model, static::TEXT_CONTENT_INPUT_NAME,
                    array('inputID' => $this->getEditableInputId(static::TEXT_CONTENT_INPUT_NAME)));
                $htmlContentError = $this->form->error($this->model, static::HTML_CONTENT_INPUT_NAME,
                    array('inputID' => $this->getEditableInputId(static::HTML_CONTENT_INPUT_NAME)));
                return $textContentError . $htmlContentError;
            }
            else
            {
                return null;
            }
        }

        protected function renderLabel()
        {
            return null;
        }

        protected function renderError()
        {
            return null;
        }

        protected function getControllerId()
        {
            return Yii::app()->getController()->getId();
        }

        protected function getModuleId()
        {
            return 'emailTemplates';
        }

        protected function isPastedHtmlTemplate()
        {
            return $this->callMethodIfExistsElseReturnTrue('isPastedHtmlTemplate');
        }

        protected function isPlainTextTemplate()
        {
            return $this->callMethodIfExistsElseReturnTrue('isPlainTextTemplate');
        }

        protected function isBuilderTemplate()
        {
            return $this->callMethodIfExistsElseReturnTrue('isBuilderTemplate');
        }

        protected function callMethodIfExistsElseReturnTrue($method)
        {
            if (method_exists($this->model, $method))
            {
                return $this->model->$method();
            }
            return true;
        }

        protected function resolveDeniedTags()
        {
            return array();
        }

        protected function resolvePlugins()
        {
            return $this->plugins;
        }

        protected function resolveSelectiveLoadOfTabs()
        {
            return (bool)ArrayUtil::getArrayValue($this->params, static::SELECTIVE_TAB_LOAD_KEY);
        }
    }
?>