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
     * Element to render an input with an auto-complete that can be used for searching by model name
     * on workflow message queue items and by time queue items.
     *
     */
    class WorkflowMixedModelsAndNameAttributeSearchElement extends AnyMixedAttributesSearchElement
    {
        /**
         * Action is called after selecting an autocomplete item
         * @var bool
         */
        protected $bindBasicSearchHandlerToKeyUp = false;

        protected static function getModelItemIdInputName()
        {
            return WorkflownQueuesSearchForm::ANY_MIXED_ATTRIBUTES_MODEL_ITEM_ID_NAME;
        }

        protected static function getModelClassNameInputName()
        {
            return WorkflownQueuesSearchForm::ANY_MIXED_ATTRIBUTES_MODEL_CLASS_NAME_NAME;
        }

        /**
         * Override to ensure the rendering of the auto-complete and 2 hidden inputs
         */
        protected function renderControlEditable()
        {
            assert('$this->model instanceof SearchForm');
            assert('$this->attribute = "anyMixedAttributes"');
            $content  = $this->renderSearchScopingInputContent();
            $content .= $this->renderTextField();
            $content .= $this->renderHiddenInputsForEditableContent();
            $this->renderEditableScripts();
            return $content;
        }

        protected function renderHiddenInputsForEditableContent()
        {
            $idInputHtmlOptions = array(
                'name'     => $this->getEditableInputName(static::getModelItemIdInputName()),
                'id'       => $this->getEditableInputId(static::getModelItemIdInputName()),
                'class'    => 'workflow-in-queues-hidden-input'
            );
            $content       = $this->form->hiddenField($this->model, static::getModelItemIdInputName(), $idInputHtmlOptions);
            $idInputHtmlOptions = array(
                'name'     => $this->getEditableInputName(static::getModelClassNameInputName()),
                'id'       => $this->getEditableInputId(static::getModelClassNameInputName()),
                'class'    => 'workflow-in-queues-hidden-input'
            );
            $content      .= $this->form->hiddenField($this->model, static::getModelClassNameInputName(), $idInputHtmlOptions);
            return $content;
        }

        /**
         * Render a auto-complete text input field.
         * When the field is typed in, it will trigger ajax
         * call to look up against the Model's name
         * @return The element's content as a string.
         */
        protected function renderTextField()
        {
            $this->registerScriptForAutoCompleteTextField();
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ModelElement");
            $cClipWidget->widget('zii.widgets.jui.CJuiAutoComplete', array(
                'name'    => $this->getNameForTextField(),
                'id'      => $this->getIdForTextField(),
                'value'   => $this->model->{$this->attribute},
                'source'  => $this->makeSourceUrl(),
                'options' => array(
                    'select'   => $this->getOnSelectOptionForAutoComplete(), // Not Coding Standard
                    'appendTo' => 'js:$("#' . $this->getIdForTextField() . '").parent()',
                    'position' => 'js:{my: "left-40px top"}',
                    'search'   => 'js: function(event, ui)
                                  {
                                       var context = $("#' . $this->getIdForTextField() . '").parent();
                                       $(this).makeOrRemoveTogglableSpinner(true, context);
                                  }',
                    'open'     => 'js: function(event, ui)
                                  {
                                       var context = $("#' . $this->getIdForTextField() . '").parent();
                                       $(this).makeOrRemoveTogglableSpinner(false, context);
                                  }',
                    'close'    => 'js: function(event, ui)
                                  {
                                       var context = $("#' . $this->getIdForTextField() . '").parent();
                                       $(this).makeOrRemoveTogglableSpinner(false, context);
                                  }',
                    'response' => 'js: function(event, ui)
                                  {
                                       if (ui.content.length < 1)
                                       {
                                           var context = $("#' . $this->getIdForTextField() . '").parent();
                                           $(this).makeOrRemoveTogglableSpinner(false, context);
                                       }
                                  }'
                ),
                'htmlOptions' => array(
                    'disabled' => $this->getDisabledValue(),
                    'onblur' => 'clearIdFromAutoCompleteField($(this).val(), \'' .
                                    $this->getEditableInputId(static::getModelItemIdInputName()) . '\');
                                 clearIdFromAutoCompleteField($(this).val(), \'' .
                                    $this->getEditableInputId(static::getModelClassNameInputName()) . '\');'
                )
            ));
            $cClipWidget->endClip();

            // Begin Not Coding Standard
            $script = '$("#' . $this->getIdForTextField() . '").data( "autocomplete" )._renderItem = function( ul, item ) {
                            return $( "<li></li>" ).data( "item.autocomplete", item )
                                    .append( "<a><span class=" + item.iconClass + "></span><span>" + item.label + "</span></a>" )
                                    .appendTo( ul );
                        };
                        $("#' . $this->getIdForTextField() . '").data( "autocomplete" )._resizeMenu = function(){
                            return this.menu.element.outerWidth( 219 );
                        };';
            /// End Not Coding Standard
            Yii::app()->clientScript->registerScript('QueueSearchElementPosition', $script);

            $spinner = ZurmoHtml::tag('span', array('class' => 'z-spinner'), '');

            return ZurmoHtml::tag('div', array('class' => 'clearfix', 'id' => 'queue-search-element'),
                   $cClipWidget->getController()->clips['ModelElement'] . $spinner);
        }

        protected function makeSourceUrl()
        {
            return Yii::app()->createUrl('workflows/default/inQueuesAutoComplete',
                                         array('formClassName' => get_class($this->model)));
        }

        /**
         * Gets on select option for the automcomplete text field
         * @return string
         */
        protected function getOnSelectOptionForAutoComplete()
        {
            // Begin Not Coding Standard
            return 'js:function(event, ui){
                                            jQuery("#' . $this->getEditableInputId(static::getModelItemIdInputName()) . '").unbind("change.ajax");
                                            jQuery("#' . $this->getEditableInputId(static::getModelItemIdInputName()) . '").bind("change.ajax", basicSearchHandler);
                                            jQuery("#' . $this->getEditableInputId(static::getModelItemIdInputName()) .
                                                '").val(ui.item["itemId"]).trigger("change");
                                            jQuery("#' . $this->getEditableInputId(static::getModelClassNameInputName()) .
                                                '").val(ui.item["modelClassName"]).trigger("change");
            }';
            // End Not Coding Standard
        }
        //todo: somehow we need to do scope binding. look at how global is done

        protected function getNameForTextField()
        {
            return $this->getEditableInputName();
        }

        protected function getIdForTextField()
        {
            return $this->getEditableInputId();
        }

        /**
         * Registers scripts for autocomplete text field
         */
        protected function registerScriptForAutoCompleteTextField()
        {
            $script = "
                function clearIdFromAutoCompleteField(value, id)
                {
                    if (value == '')
                    {
                        $('#' + id).val('');
                    }
                }
            ";
            Yii::app()->clientScript->registerScript(
                'clearInQueuesItemIdAndModelClassNameFromAutoCompleteField',
                $script,
                CClientScript::POS_END
            );
        }

        protected function renderEditableScripts()
        {
            parent::renderEditableScripts();
            $this->renderScopeChangeScript();
        }

        protected function renderScopeChangeScript()
        {
            // Begin Not Coding Standard
            $script = '$("#' . $this->getIdForTextField() . '").bind("focus", function(event, ui){
                            $("#' . $this->getIdForTextField() . '").autocomplete("option", "source", "' .
                            $this->makeSourceUrl() . '&" + $.param($("#' .
                            $this->getEditableInputId(SearchForm::ANY_MIXED_ATTRIBUTES_SCOPE_NAME) . '").serializeArray()));
                        });
                       ';
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('WorkflowMixedModelsAndNameAttributeSearchScopeChanges', $script);
        }
    }
?>