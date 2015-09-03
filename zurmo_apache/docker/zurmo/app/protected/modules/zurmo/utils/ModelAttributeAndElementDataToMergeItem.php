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
     * Acts as a helper model to retrieve model attribute and element related information
     */
    class ModelAttributeAndElementDataToMergeItem
    {
        /**
         * Model associated to the merged item.
         * @var RedBeanModel
         */
        protected $model;

        /**
         * Attribute associated to the merged item.
         * @var string
         */
        protected $attribute;

        /**
         * Element associated to the merged item.
         * @var string
         */
        protected $element;

        /**
         * Primary model associated to the merged item.
         * @var RedBeanModel
         */
        protected $primaryModel;

        /**
         * @var int The position of the model in the list
         * This is use only for the color class
         */
        protected $position;

        /**
         * Constructor for the class.
         * @param type $model
         * @param type $attribute
         * @param type $element
         * @param type $primaryModel
         */
        public function __construct(RedBeanModel $model, $attribute, $element, RedBeanModel $primaryModel, $position)
        {
            assert('is_string($attribute)');
            assert('is_int($position)');
            assert('$element instanceof Element');
            $this->model             = $model;
            $this->attribute         = $attribute;
            $this->element           = $element;
            $this->primaryModel      = $primaryModel;
            $this->position          = $position;
        }

        /**
         * Gets attribute rendered content
         * @return string
         */
        public function getAttributeRenderedContent()
        {
            return $this->decorateContent($this->model->{$this->attribute});
        }

        /**
         * Get attribute values and input ids for on click event
         * @return array
         */
        public function getAttributeInputIdsForOnClick()
        {
            $interfaces = class_implements($this->element);
            $elementClassName = get_class($this->element);
            if (in_array('DerivedElementInterface', $interfaces))
            {
                if ($this->element instanceof DropDownElement)
                {
                    $attributeInputIdMap[] = $this->element->getIdForSelectInput();
                }
                else
                {
                    $attributes       = $elementClassName::getModelAttributeNames();
                    foreach ($attributes as $attribute)
                    {
                        $attributeInputIdMap[] = $this->getDerivedInputId($attribute);
                    }
                }
            }
            elseif (in_array('MultipleAttributesElementInterface', $interfaces))
            {
                $relatedAttributes = $elementClassName::getModelAttributeNames();
                foreach ($relatedAttributes as $relatedAttribute)
                {
                    $attributeInputIdMap[] = $this->getDerivedInputId($this->attribute, $relatedAttribute);
                }
            }
            else
            {
                if ($this->element instanceof ModelElement)
                {
                    $attributeInputIdMap[] = $this->getDerivedInputId($this->attribute, 'name');
                }
                else
                {
                    $attributeInputIdMap[] = $this->getNonDerivedInputId();
                }
            }
            return $attributeInputIdMap;
        }

        /**
         * Get input id for non derived attribute
         * @return string
         */
        protected function getNonDerivedInputId()
        {
            return $this->resolveInputId($this->attribute);
        }

        /**
         * Gets input id for the derived attribute
         * @param string $attribute
         * @param string $relatedAttribute
         * @return string
         */
        protected function getDerivedInputId($attribute, $relatedAttribute)
        {
            assert('is_string($attribute)');
            assert('is_string($relatedAttribute)');
            return $this->resolveInputId($attribute, $relatedAttribute);
        }

        /**
         * Resolves input id for the attribute
         * @param string $attribute
         * @param string $relatedAttribute
         * @return string
         */
        private function resolveInputId($attribute, $relatedAttribute = null)
        {
            assert('is_string($attribute)');
            assert('is_string($relatedAttribute) || ($relatedAttribute === null)');
            if ($this->model->$attribute instanceof CustomField)
            {
                $inputId = Element::resolveInputIdPrefixIntoString(array(get_class($this->model), $attribute, 'value'));
            }
            elseif ($relatedAttribute != null)
            {
                $inputId = Element::resolveInputIdPrefixIntoString(array(get_class($this->model), $attribute, $relatedAttribute));
            }
            else
            {
                $inputId = Element::resolveInputIdPrefixIntoString(array(get_class($this->model), $attribute));
            }
            return $inputId;
        }

        /**
         * Decorates the content
         * @param string $content
         * @return string
         */
        protected function decorateContent($content)
        {
            $decoratedContent = null;

            $inputIds = $this->getAttributeInputIdsForOnClick();
            if ($this->model->id == $this->primaryModel->id)
            {
                $class = ' selected';
            }
            else
            {
                $class = '';
            }
            foreach ($inputIds as $inputId)
            {
                $inputIdArray = explode('_', $inputId);
                $attribute    = $inputIdArray[1];
                $relatedAttribute = null;
                //If related attribute is there
                if (count($inputIdArray) > 2)
                {
                    $relatedAttribute = $inputIdArray[2];
                }
                if ($relatedAttribute == null)
                {
                    $inputValue = $this->model->$attribute;
                    $displayValue = $inputValue;
                    if ($displayValue == null)
                    {
                        $displayValue = Zurmo::t('Core', '(None)');
                    }
                }
                elseif ($this->element instanceof ModelElement)
                {
                    $inputValue       = $this->model->$attribute->$relatedAttribute;
                    $displayValue     = $this->resolveDisplayedValueForRelatedAttribute($attribute, $relatedAttribute);
                    $hiddenInputValue = $this->model->$attribute->id;
                    $hiddenInputId    = $this->getDerivedInputId($attribute, 'id');
                    if ($displayValue == null)
                    {
                        $displayValue = Zurmo::t('Core', '(None)');
                    }
                    if ($hiddenInputValue == '-27')
                    {
                        $hiddenInputValue = '';
                    }
                }
                else
                {
                    $inputValue   = $this->model->$attribute->$relatedAttribute;
                    $displayValue = $this->resolveDisplayedValueForRelatedAttribute($attribute, $relatedAttribute);
                    if (($displayValue == null && ($this->element instanceof DropDownElement && !empty($this->element->params) &&
                        $this->element->params['addBlank'])) || ($content == Zurmo::t('Core', '(None)') && !$this->element instanceof AddressElement))
                    {
                        $displayValue = Zurmo::t('Core', '(None)');
                    }
                }
                if ($displayValue != null)
                {
                    if ($this->element instanceof ModelElement)
                    {
                        $decoratedContent .= ZurmoHtml::link($displayValue, '#', array('data-id'           => $inputId,
                                                                                       'data-value'        => $inputValue,
                                                                                       'data-hiddenid'     => $hiddenInputId,
                                                                                       'data-hiddenvalue'  => $hiddenInputValue,
                                                                                       'class'             => 'possible attributePreElementContentModelElement merge-color-' . $this->position  . $class));
                    }
                    else
                    {
                        $decoratedContent .= ZurmoHtml::link($displayValue, '#', array('data-id'     => $inputId,
                                                                                       'data-value'  => $inputValue,
                                                                                       'class'       => 'possible attributePreElementContent merge-color-' . $this->position . $class));
                    }
                }
            }

            if ($decoratedContent == null)
            {
                if ($this->element instanceof DropDownElement)
                {
                    $decoratedContent = null;
                }
                else
                {
                    $decoratedContent = ZurmoHtml::tag('span', array('class' => 'possible merge-color-' . $this->position), Zurmo::t('Core', '(None)'));
                }
            }
            if ($this->element instanceof AddressElement)
            {
                $decoratedContent .= '</br>';
            }
            return $decoratedContent;
        }

        /**
         * Resolves value for related attribute
         * @param string $attribute
         * @param string $relatedAttribute
         * @return string
         */
        protected function resolveDisplayedValueForRelatedAttribute($attribute, $relatedAttribute)
        {
            return $this->model->$attribute->$relatedAttribute;
        }
    }
?>