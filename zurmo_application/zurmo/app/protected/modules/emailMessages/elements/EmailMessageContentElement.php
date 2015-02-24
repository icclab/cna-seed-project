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
     * Display email message content.
     */
    class EmailMessageContentElement extends Element
    {
        protected function renderControlNonEditable()
        {
            assert('$this->model->{$this->attribute} instanceof EmailMessageContent');
            $emailMessageContent = $this->model->{$this->attribute};
            if ($emailMessageContent->htmlContent != null)
            {
                // we don't use Yii::app()->format->html because we know its good in terms of
                // purification. Plus using that messes up html.
                return $emailMessageContent->htmlContent;
            }
            elseif ($emailMessageContent->textContent != null)
            {
                return Yii::app()->format->text($emailMessageContent->textContent);
            }
        }

        protected function renderControlEditable()
        {
            $textContent = $this->renderTextContent();
            $htmlContent = $this->renderHtmlContent();
            $content = $this->resolveTabbedContent($textContent, $htmlContent);
            return $content;
        }

        protected function renderHtmlContent()
        {
            $emailMessageContent     = $this->model->{$this->attribute};
            $inputNameIdPrefix       = $this->attribute;
            $attribute               = 'htmlContent';
            $id                      = $this->getEditableInputId  ($inputNameIdPrefix, $attribute);
            $htmlOptions             = array();
            $htmlOptions['id']       = $id;
            $htmlOptions['name']     = $this->getEditableInputName($inputNameIdPrefix, $attribute);
            $cClipWidget   = new CClipWidget();
            $cClipWidget->beginClip("Redactor");
            // Begin Not Coding Standard
            $cClipWidget->widget('application.core.widgets.Redactor', array(
                'htmlOptions'   => $htmlOptions,
                'content'       => $emailMessageContent->$attribute,
                'paragraphy'    => "false",
                'fullpage'      => "true",
                'observeImages' => 'true',
                'deniedTags'    => CJSON::encode($this->resolveDeniedTags()),
                'initCallback'  => 'function(){
                                             var contentHeight = $(".redactor_box iframe").contents().find("body").outerHeight();
                                             $(".redactor_box iframe").height(contentHeight + 50);
                                        }',
                'plugins'       => CJSON::encode(array('fontfamily', 'fontsize', 'fontcolor', 'imagegallery')),
            ));
            // End Not Coding Standard
            $cClipWidget->endClip();
            $content  = $cClipWidget->getController()->clips['Redactor'];
            return $content;
        }

        protected function renderTextContent()
        {
            $inputNameIdPrefix       = $this->attribute;
            $attribute               = 'textContent';
            $htmlOptions             = array();
            $htmlOptions['id']       = $this->getEditableInputId  ($inputNameIdPrefix, $attribute);;
            $htmlOptions['name']     = $this->getEditableInputName($inputNameIdPrefix, $attribute);
            $htmlOptions['encode']   = false;
            return $this->form->textArea($this->model->{$this->attribute}, 'textContent', $htmlOptions);
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
                    'class' => $textClass . ' tab email-template-textContent'),
                $plainTextContent);
            if (isset($htmlContent))
            {
                $this->registerTabbedContentScripts();
                $this->registerRedactorIframeHeightScripts();
                $htmlTabHyperLink   = ZurmoHtml::link($this->renderHtmlContentAreaLabel(),
                    '#tab2',
                    array('class' => $htmlClass));
                $htmlContentDiv     = ZurmoHtml::tag('div', array('id' => 'tab2',
                        'class' => $htmlClass . ' tab email-template-htmlContent'),
                    $htmlContent);
            }
            $tagsGuideLink      = null;
            $tabContent         = ZurmoHtml::tag('div', array('class' => 'tabs-nav'), $textTabHyperLink . $htmlTabHyperLink);
            $content            = ZurmoHtml::tag('div', array('class' => 'email-template-content'), $tabContent . $plainTextDiv . $htmlContentDiv);
            if ($this->form)
            {
//                $content           .= $this->renderTextAndHtmlContentAreaError();
            }
            return $content;
        }

        protected function renderLabel()
        {
            return Zurmo::t('EmailMessagesModule', 'Body');
        }

        protected function renderAttributeLabel($attribute)
        {
            $model = $this->attribute;
            $label = $this->model->$model->getAttributeLabel($attribute);
            if ($this->form === null)
            {
                return $label;
            }
            else
            {
                return $this->form->labelEx($this->model,
                                            $this->attribute,
                                            array('for' => $this->getEditableInputId($this->attribute, $attribute),
                                                  'label' => $label));
            }
        }

        protected function renderHtmlContentAreaLabel()
        {
            return $this->renderAttributeLabel('htmlContent');
        }

        protected function renderTextContentAreaLabel()
        {
            return $this->renderAttributeLabel('textContent');
        }

        protected function resolveDeniedTags()
        {
            return array();
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

        protected function renderTextAndHtmlContentAreaError()
        {
            if (strpos($this->editableTemplate, '{error}') !== false)
            {
                $emailMessageContent    = $this->model->{$this->attribute};
                $textContentError       = $this->form->error($emailMessageContent, 'textContent');
                $htmlContentError       = $this->form->error($emailMessageContent, 'htmlContent');
                return $textContentError . $htmlContentError;
            }
            else
            {
                return null;
            }
        }
    }
?>