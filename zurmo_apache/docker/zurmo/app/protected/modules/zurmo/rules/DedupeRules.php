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

    /**
     * Class DedupeRules
     * Base class of dedupe rules that assist
     * Extend this class to make a set of DedupeRules that is for a specific model.
     */
    abstract class DedupeRules
    {
        protected $model;

        public function __construct(RedBeanModel $model)
        {
            $this->model = $model;
        }

        /**
         * This array should map the relation between the attribute and related attribute that should trigger the dedupe
         * @return array
         */
        protected function getDedupeAttributesAndRelatedAttributesMappedArray()
        {
            return array();
        }

        /**
         * This array contains a list of the Element names that will trigger the dedupe
         * @return array
         */
        protected function getDedupeElements()
        {
            return array();
        }

        /**
         * This array maps the relation between the attribute name and function callback for search for duplicate models
         * @return array
         */
        protected function getDedupeAttributesAndSearchForDuplicateModelsCallbackMappedArray()
        {
            return array();
        }

        /**
         * The ViewClassName used to display the results of the dedupe models list
         * @return string
         */
        public function getDedupeViewClassName()
        {
            return 'CreateModelsToMergeListAndChartView';
        }

        /**
         * Register the script that will make the ajax call to search for a dedupe and update the DedupeViewClassName
         * with the content returned. It also display a clickable flash message with the number of results found
         * @see ZurmoModuleController::actionSearchForDuplicateModels
         * @param Element $element
         * @return null
         */
        public function registerScriptForEditAndDetailsView(Element $element)
        {
            if (!$this->shouldCreateScriptForElement($element))
            {
                return null;
            }
            $id            = $this->getInputIdForDedupe($element);
            $dedupeViewId  = $this->getDedupeViewClassName();
            $link          = ZurmoHtml::link(Zurmo::t('Core', 'click here'), '#', array('onclick' => 'js:$("#' . $dedupeViewId . '").closest("form")[0].submit();'));
            $spanUnderline = ZurmoHtml::tag('span', array('class' => 'underline'), $link);
            $textMessage   = '<br>' . ZurmoHtml::encode(Zurmo::t('ZurmoModule', 'If you still want to save '));
            $textMessage  .= $spanUnderline . '.';
            $spinnerId     = 'dedupe-spinner';
            // Begin Not Coding Standard
            $ajaxScript = ZurmoHtml::ajax(array(
                'type'       => 'GET',
                'data'       => array('attribute' => $this->getAttributeForDedupe($element),
                                      'value'     => "js:$('#{$id}').val()",
                ),
                'url'        => 'searchForDuplicateModels',

                'beforeSend' => "js:function(){
                                     $('#" . $id . "').after('<div id=\"" . $spinnerId . "\"><span class=\"z-spinner\"></span></div>');
                                     $(this).makeOrRemoveLoadingSpinner(true, '#" . $spinnerId . "', 'dark');
                                }",
                'success'    => "js:function(data, textStatus, jqXHR){
                                        var returnObj = jQuery.parseJSON(data);
                                        $('#" . $dedupeViewId . "').closest('form').off('submit.dedupe');
                                        if (returnObj != null)
                                        {
                                            var textMessage = '<a href=\"#\" onclick=\"$(\'#" . $dedupeViewId . "\').show();dedupeShouldSubmitFormAfterMessage = false;$(\'.jnotify-item-close\').click(); return false;\">' + returnObj.message + '</a>';
                                            if (shouldSubmitForm)
                                            {
                                                $('#" . $dedupeViewId . "').closest('form').find('a[name=\'save\']').removeClass('loading');
                                                textMessage += '" . $textMessage . "';
                                            }
                                            $('#" . $dedupeViewId . "').replaceWith(returnObj.content);
                                            $('#FlashMessageBar').jnotifyAddMessage({
                                                text: textMessage,
                                                permanent: true,
                                                clickOverlay : true,
                                                showIcon: false,
                                            });

                                        }
                                        else if (shouldSubmitForm)
                                        {
                                            $('#" . $dedupeViewId . "').closest('form')[0].submit();
                                        }
                                 }",
                'complete'    => "js:function(){ $('#" . $id . "').next('#" . $spinnerId . "').remove(); }"
            ));
            $js = "var shouldSubmitForm = false;
                    $('#{$id}' ).change(function() {
                        if ($('#{$id}').val() != '')
                        {
                            {$ajaxScript}
                            $(this).closest('form').on('submit.dedupe', function(e)
                            {
                                shouldSubmitForm = true;
                                return false;
                            });
                        }

                   });
            ";

            Yii::app()->getClientScript()->registerScript(__CLASS__ . $id . '#dedupe-for-edit-and-details-view', $js);
            // End Not Coding Standard
        }

        /**
         * Returns the input id that should be used to trigger the dedupe
         * @param Element $element
         * @return null|string
         */
        protected function getInputIdForDedupe(Element $element)
        {
            $interfaces = class_implements($element);
            if (in_array('MultipleAttributesElementInterface', $interfaces))
            {
                return Element::resolveInputIdPrefixIntoString(array(get_class($this->model), $element->getAttribute(), $this->getRelatedAttributeForDedupe($element)));
            }
            else
            {
                return Element::resolveInputIdPrefixIntoString(array(get_class($this->model), $this->getAttributeForDedupe($element)));
            }
        }

        /**
         * Returns the attribute name that should be used to trigger the dedupe
         * @param Element $element
         * @return mixed
         * @throws NotSupportedException
         */
        protected function getAttributeForDedupe(Element $element)
        {
            $interfaces = class_implements($element);
            if (in_array('DerivedElementInterface', $interfaces))
            {
                $attributesForDedupeInElement = array_values(array_intersect(array_keys($this->getDedupeAttributesAndRelatedAttributesMappedArray()),
                                $element->getModelAttributeNames()));
                if (count($attributesForDedupeInElement) == 1)
                {
                    return $attributesForDedupeInElement[0];
                }
                else
                {
                    throw new NotSupportedException('Dedupe multiple attributes on the same element is not possible');
                }
            }
            return $element->getAttribute();
        }

        /**
         * Return the related attribute that should be used to trigger the dedupe
         * @param Element $element
         * @return mixed
         */
        protected function getRelatedAttributeForDedupe(Element $element)
        {
            $dedupeMappingArray = $this->getDedupeAttributesAndRelatedAttributesMappedArray();
            return $dedupeMappingArray[$element->getAttribute()];
        }

        /**
         * Returns the name of the element
         * @param Element $element
         * @return string
         */
        protected function getElementNameByElement(Element $element)
        {
            return str_replace('Element', '', get_class($element));
        }

        /**
         * Based on the Element the data from @see DedupeRules::getDedupeAttributesAndRelatedAttributesMappedArray
         * and @see DedupeRules::getDedupeElements and from the model id, decided if the script for dedupe should be
         * registered
         * @param Element $element
         * @return bool
         */
        protected function shouldCreateScriptForElement(Element $element)
        {
            if (!in_array($this->getElementNameByElement($element), $this->getDedupeElements()) ||
                !array_key_exists($this->getAttributeForDedupe($element), $this->getDedupeAttributesAndRelatedAttributesMappedArray()) ||
                $this->model->id > 0)
            {
                return false;
            }
            return true;
        }

        public function searchForDuplicateModels($attribute, $value)
        {
            assert('is_string($attribute) && $attribute != null');
            assert('is_string($value)');
            $callback      = $this->getCallbackToSearchForDuplicateModelsByAttribute($attribute);
            if ($callback == null)
            {
                throw new NotImplementedException('There is no search callback defined for attribute: ' . $attribute);
            }
            $matchedModels = call_user_func($callback, $value, ModelsListDuplicateMergedModelForm::MAX_SELECTED_MODELS_COUNT + 1);
            if (count($matchedModels) > 0)
            {
                if (count($matchedModels) > ModelsListDuplicateMergedModelForm::MAX_SELECTED_MODELS_COUNT)
                {
                    $message =  Zurmo::t('ZurmoModule',
                                         'There are at least {n} possible matches.',
                                         ModelsListDuplicateMergedModelForm::MAX_SELECTED_MODELS_COUNT
                    );
                }
                else
                {
                    $message =  Zurmo::t('ZurmoModule',
                                         'There is {n} possible match.|There are {n} possible matches.',
                                         count($matchedModels)
                    );
                }
                $clickHere = ZurmoHtml::tag('span', array('class' => 'underline'), Zurmo::t('Core', 'Click here'));
                $message .= ' ' . $clickHere . ' ' . Zurmo::t('ZurmoModule', 'to view') . '.';
                return array('message' => $message, 'matchedModels' => $matchedModels);
            }
        }

        protected function getCallbackToSearchForDuplicateModelsByAttribute($attribute)
        {
            $callbackMappedArray = $this->getDedupeAttributesAndSearchForDuplicateModelsCallbackMappedArray();
            if (array_key_exists($attribute, $callbackMappedArray))
            {
                return $callbackMappedArray[$attribute];
            }
        }
    }
?>