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
     * Display the emailTemplate selection. This is a
     * combination of a type-ahead input text field
     * and a selection button which renders a modal list view
     * to search on emailTemplates.  Also includes a hidden input for the user
     * id.
     */
    class EmailTemplateElement extends ModelElement
    {
        const DISABLE_DROPDOWN_WHEN_AJAX_IN_PROGRESS   = true;

        const DISABLE_TEXTBOX_WHEN_AJAX_IN_PROGRESS    = true;

        const NOTIFICATION_BAR_ID                      = 'FlashMessageBar';

        public $editableTemplate = '<th>{label}</th><td colspan="{colspan}"><div class="has-model-select">{content}</div>{error}</td>';

        protected static $moduleId = 'emailTemplates';

        protected $name;

        protected $id;

        public function __construct($model, $attribute, $form = null, array $params = array())
        {
            parent::__construct($model, $attribute, $form, $params);
            if ($this->attribute == 'null')
            {
                $this->attribute = 'contactEmailTemplateNames';
                $this->name      = '';
                $this->id        = '';
            }
            else
            {
                $this->name      = parent::getName();
                $this->id        = parent::getId();
            }
        }

        /**
         * Render a hidden input, a text input with an auto-complete
         * event, and a select button. These three items together
         * form the Account Editable Element
         * @return The element's content as a string.
         */
        protected function renderControlEditable()
        {
            $this->registerScripts();
            return $this->renderEditableContent();
        }

        protected function wrapHasModelSelectInput(& $content)
        {
        }

        protected function getName()
        {
            return $this->name;
        }

        protected function getId()
        {
            return $this->id;
        }

        protected function registerScripts()
        {
            $this->registerUpdateFlashBarScript();
            $this->registerChangeHandlerScript();
        }

        protected function registerChangeHandlerScript()
        {
            $hiddenId = $this->getIdForHiddenField();
            $scriptName = $hiddenId . '_changeHandler';
            if (Yii::app()->clientScript->isScriptRegistered($scriptName))
            {
                return;
            }
            else
            {
                // Begin Not Coding Standard
                Yii::app()->clientScript->registerScript($scriptName, '
                        function updateContentElementsWithData(textContentElement, htmlContentElement, subjectElement, data)
                        {
                            if ($(".tabs-nav > a:first").hasClass("active-tab"))
                            {
                                $(".tabs-nav > a:eq(1)").click();
                            }
                            updateElementWithData(textContentElement, data.textContent);
                            updateElementWithData(subjectElement, data.subject);
                            $(htmlContentElement).redactor("set", data.htmlContent);
                            $(htmlContentElement).redactor("toggle");
                            $(htmlContentElement).redactor("toggle");
                            var contentHeight = $(".redactor_box iframe").contents().find("html").outerHeight();
                            $(".redactor_box iframe").height(contentHeight + 50);
                        }

                        function updateElementWithData(element, data)
                        {
                            if ($(element).attr("type") == "text")
                            {
                                $(element).val(data);
                            }
                            else
                            {
                                $(element).html(data);
                            }
                        }

                        function deleteExistingAttachments()
                        {
                            $("table.files tr.template-download td.name span.upload-actions.delete button.icon-delete:first")
                                .click();
                            $("table.files tr.template-download")
                                .remove();
                        }

                        function updateAddFilesWithDataFromAjax(filesIds, notificationBarId)
                        {
                            if (filesIds != "")
                            {
                                var url             = "' . $this->getCloneExitingFilesUrl() . '";
                                var templateId      = "#' . FileUpload::DOWNLOAD_TEMPLATE_ID .'";
                                var targetClass     = ".files";
                                var filesIdsString  = filesIds.join();
                                $.ajax(
                                    {
                                        url:        url,
                                        dataType:   "json",
                                        data:
                                        {
                                            commaSeparatedExistingModelIds: filesIdsString
                                        },
                                        success:    function(data, status, request)
                                                    {
                                                        $(templateId).tmpl(data).appendTo(targetClass);
                                                    },
                                        error:      function(request, status, error)
                                                    {
                                                        var data = {' . // Not Coding Standard
                                                                    '   "message" : "' . Zurmo::t('Core',
                                                                            'There was an error processing your request') .
                                                                            '",
                                                                        "type"    : "error"
                                                                    };
                                                                    updateFlashBar(data, notificationBarId);
                                                    },
                                    }
                                );
                            }
                        }
                        $("#' . $hiddenId . '").unbind("change.action").bind("change.action", function(event, ui)
                                                {
                                                    selectedOptionValue     = $(this).val();
                                                    if (selectedOptionValue)
                                                    {
                                                        var dropDown            = $(this);
                                                        var notificationBarId   = "' . static::NOTIFICATION_BAR_ID . '";
                                                        var url                 = "' . $this->getEmailTemplateDetailsJsonUrl() . '";
                                                        var disableDropDown     = "' . static::DISABLE_DROPDOWN_WHEN_AJAX_IN_PROGRESS . '";
                                                        var disableTextBox      = "' . static::DISABLE_TEXTBOX_WHEN_AJAX_IN_PROGRESS. '";
                                                        var textContentId       = "' . $this->getTextContentId() . '";
                                                        var htmlContentId       = "' . $this->getHtmlContentId() . '";
                                                        var subjectId           = "' . $this->getSubjectId() . '";
                                                        var subjectElement      = $("#" + subjectId);
                                                        var textContentElement  = $("#" + textContentId);
                                                        var htmlContentElement  = $("#" + htmlContentId);
                                                        var contactId           = ' . $this->getContactId() . ';
                                                        var redActorElement     = $("#" + htmlContentId).parent().find(".redactor_editor");
                                                        $.ajax(
                                                            {
                                                                url:        url,
                                                                dataType:   "json",
                                                                data:
                                                                {
                                                                    id: selectedOptionValue,
                                                                    renderJson: true,
                                                                    includeFilesInJson: true,
                                                                    contactId: contactId
                                                                },
                                                                beforeSend: function(request, settings)
                                                                            {
                                                                                $(this).makeLargeLoadingSpinner(true, ".email-template-content");
                                                                                if (disableDropDown == true)
                                                                                {
                                                                                    $(dropDown).attr("disabled", "disabled");
                                                                                }
                                                                                if (disableTextBox == true)
                                                                                {
                                                                                    $(textContentElement).attr("disabled", "disabled");
                                                                                    $(htmlContentElement).attr("disabled", "disabled");
                                                                                    $(subjectElement).attr("disabled", "disabled");
                                                                                    $(redActorElement).hide();
                                                                                }
                                                                                deleteExistingAttachments();
                                                                            },
                                                                success:    function(data, status, request)
                                                                            {
                                                                                $(this).makeLargeLoadingSpinner(false, ".email-template-content");
                                                                                $(".email-template-content .big-spinner").remove();
                                                                                updateContentElementsWithData(textContentElement,
                                                                                                              htmlContentElement,
                                                                                                              subjectElement,
                                                                                                              data);
                                                                                subjectElement.focus();
                                                                                updateAddFilesWithDataFromAjax(data.filesIds, notificationBarId);
                                                                            },
                                                                error:      function(request, status, error)
                                                                            {
                                                                                var data = {' . // Not Coding Standard
                                                                                    '   "message" : "' . Zurmo::t('Core',
                                                                                        'There was an error processing your request') .
                                                                                    '",
                                                                                    "type"    : "error"
                                                                                };
                                                                                updateFlashBar(data, notificationBarId);
                                                                            },
                                                                complete:   function(request, status)
                                                                {
                                                                    $(dropDown).removeAttr("disabled");
                                                                    $(dropDown).val("");
                                                                    $(textContentElement).removeAttr("disabled");
                                                                    $(htmlContentElement).removeAttr("disabled");
                                                                    $(subjectElement).removeAttr("disabled");
                                                                    $(redActorElement).show();
                                                                    event.preventDefault();
                                                                    return false;
                                                                }
                                                            }
                                                        );
                                                    }
                                                }
                        );
                ');
                // End Not Coding Standard
            }
        }

        protected function registerUpdateFlashBarScript()
        {
            if (Yii::app()->clientScript->isScriptRegistered('handleUpdateFlashBar'))
            {
                return;
            }
            else
            {
                Yii::app()->clientScript->registerScript('handleUpdateFlashBar', '
                    function updateFlashBar(data, flashBarId)
                    {
                        $("#" + flashBarId).jnotifyAddMessage(
                        {
                            text: data.message,
                            permanent: false,
                            showIcon: true,
                            type: data.type
                        });
                    }
                ');
            }
        }

        protected function renderLabel()
        {
            $label = Zurmo::t('EmailTemplatesModule', 'Select a template');
            if ($this->form === null)
            {
                return $label;
            }
            $id = $this->getIdForTextField();
            return ZurmoHtml::tag('label',
                                  array('for' => $id),
                                  $label);
        }

        protected function renderError()
        {
            return null;
        }

        protected function getEditableHtmlOptions()
        {
            $prompt             = array('prompt' => Zurmo::t('EmailTemplatesModule', 'Select a template'));
            $parentHtmlOptions  = parent::getEditableHtmlOptions();
            $htmlOptions        = CMap::mergeArray($parentHtmlOptions, $prompt);
            return $htmlOptions;
        }

        protected function getEmailTemplateDetailsJsonUrl()
        {
            return Yii::app()->createUrl('/emailTemplates/default/detailsJson');
        }

        protected function getTextContentId()
        {
            return $this->getEditableInputId(EmailTemplateHtmlAndTextContentElement::TEXT_CONTENT_INPUT_NAME);
        }

        protected function getSubjectId()
        {
            return $this->getEditableInputId('subject');
        }

        protected function getHtmlContentId()
        {
            return $this->getEditableInputId(EmailTemplateHtmlAndTextContentElement::HTML_CONTENT_INPUT_NAME);
        }

        protected function getCloneExitingFilesUrl()
        {
            return Yii::app()->createUrl('/zurmo/fileModel/cloneExistingFiles');
        }

        protected function getContactId()
        {
            return 'null';
        }

        protected function getAutoCompleteUrlParams()
        {
            return array('type' => EmailTemplate::TYPE_CONTACT);
        }

        protected function getSelectLinkUrlParams()
        {
            return array_merge(parent::getSelectLinkUrlParams(),
                array('stateMetadataAdapterClassName' => 'EmailTemplatesForMarketingStateMetadataAdapter'));
        }

        protected function getNameForHiddenField()
        {
            return '';
        }

        protected function getNameForTextField()
        {
            return '';
        }
    }
?>
