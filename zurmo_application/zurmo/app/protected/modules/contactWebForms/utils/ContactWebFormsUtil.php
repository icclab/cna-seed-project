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
     * Helper class with functions to assist in working with ContactWebForms module
     */
    class ContactWebFormsUtil
    {
        public static $restrictHiddenControlAttributes = array('Address',
                                                               'EmailAddressInformation',
                                                               'MultiSelectDropDown',
                                                               'CurrencyValue',
                                                               'DropDownDependency',
                                                               'CalculatedNumber',
                                                               'TagCloud');

        /**
         * Get list of all index and derived attributes that can be placed on any web form
         * @return array of all attributes
         */
        public static function getAllAttributes()
        {
            $contact             = new Contact();
            $adapter             = new ContactWebFormModelAttributesAdapter($contact);
            $attributes          = $adapter->getAttributes();
            $placeAbleAttributes = array();
            foreach ($attributes as $attributeName => $attributeData)
            {
                if (!$attributeData['isReadOnly'] && $attributeName != 'googleWebTrackingId')
                {
                    $placeAbleAttributes[$attributeName] = $attributeData;
                }
            }
            $placeAbleAttributes  = ArrayUtil::subValueSort($placeAbleAttributes, 'attributeLabel', 'asort');
            return $placeAbleAttributes;
        }

        /**
         * @param ContactWebForm $contactWebForm
         * @return array of placed attributes on given web form
         */
        public static function getPlacedAttributes(ContactWebForm $contactWebForm)
        {
            assert('$contactWebForm instanceof ContactWebForm');
            if (empty($contactWebForm->serializedData))
            {
                $contactWebForm->serializedData = serialize(array());
            }
            $allAttributes            = static::getAllAttributes();
            $contactWebFormAttributes = unserialize($contactWebForm->serializedData);
            $contactWebFormAttributes = static::resolveWebFormAttributes($contactWebFormAttributes);
            $contactWebFormAttributes = static::resolveWebFormWithAllRequiredAttributes($contactWebFormAttributes,
                                                                                        $allAttributes);
            $placedAttributes         = array();
            foreach ($contactWebFormAttributes as $attributeName)
            {
                $placedAttributes[$attributeName] = $allAttributes[$attributeName];
            }
            return $placedAttributes;
        }

        /**
         * @param ContactWebForm $contactWebForm
         * @return array of attributes that are not placed on given web form
         */
        public static function getNonPlacedAttributes(ContactWebForm $contactWebForm)
        {
            assert('$contactWebForm instanceof ContactWebForm');
            $allAttributes = static::getAllAttributes();
            $placedAttributes = static::getPlacedAttributes($contactWebForm);
            $nonPlacedAttributes = array();
            foreach ($allAttributes as $attributeName => $attributeData)
            {
                if (!array_key_exists($attributeName, $placedAttributes))
                {
                    $nonPlacedAttributes[$attributeName] = $attributeData['attributeLabel'];
                }
            }
            return $nonPlacedAttributes;
        }

        /**
         * @param ContactWebForm $contactWebForm
         * @param $model
         * @return array of formulated placed attributes, used by SortableContactWebFormAttributesElement
         */
        public static function resolvePlacedAttributesForWebFormAttributesElement(ContactWebForm $contactWebForm, $model)
        {
            $resolvedPlacedAttributes = array();
            $placedAttributes = static::getPlacedAttributes($contactWebForm);
            if (empty($contactWebForm->serializedData))
            {
                $contactWebForm->serializedData = serialize(array());
            }
            $contactWebFormAttributes = unserialize($contactWebForm->serializedData);
            foreach ($placedAttributes as $attributeName => $attributeData)
            {
                $webFormAttributeForm = new ContactWebFormAttributeForm();
                if (isset($contactWebFormAttributes[$attributeName]))
                {
                    $webFormAttributeForm->setAttributes($contactWebFormAttributes[$attributeName]);
                }
                else
                {
                    $webFormAttributeForm->label = $attributeData['attributeLabel'];
                }
                $resolvedPlacedAttributes[$attributeName] = static::resolvePlacedAttributeByName($webFormAttributeForm,
                                                            $model, $attributeName, $attributeData);
            }
            return $resolvedPlacedAttributes;
        }

        /**
         * @param $webFormAttributeForm
         * @param $model
         * @param $attributeName
         * @param $attributeData
         * @return array
         */
        public static function resolvePlacedAttributeByName($webFormAttributeForm, $model, $attributeName,
                                                            $attributeData)
        {
            $webFormAttributeForm->attribute = $attributeName;
            $params = array('inputPrefix' => array(get_class($webFormAttributeForm), $attributeName));
            if ($attributeData['isRequired'])
            {
                $webFormAttributeForm->required = true;
                $isRequiredChecked              = 'checked';
                $isRequiredDisabled             = 'disabled';
                $removePlacedAttributeLink      = null;
            }
            else
            {
                if (isset($webFormAttributeForm->required) && $webFormAttributeForm->required == true)
                {
                    $isRequiredChecked  = 'checked';
                    $isRequiredDisabled = null;
                }
                else
                {
                    $isRequiredChecked  = 'checked';
                    $isRequiredDisabled = null;
                }
                $removePlacedAttributeLink = '<a class="remove-dynamic-row-link" id="ContactWebForm_serializedData_' .
                                              $attributeName . '" data-value="' . $attributeName . '"' .
                                              ' data-label="' . $attributeData['attributeLabel'] . '" href="#">—</a>';
            }
            if (isset($webFormAttributeForm->hidden) && $webFormAttributeForm->hidden == true)
            {
                $isHiddenChecked = 'checked';
                $hideHiddenAttributeElementStyle  = 'display:block;';
            }
            else
            {
                $isHiddenChecked = '';
                $hideHiddenAttributeElementStyle  = 'display:none;';
            }
            $attributeLabelElement = new TextElement($webFormAttributeForm, 'label', $model, $params);
            $isRequiredElement     = new CheckBoxElement($webFormAttributeForm, 'required', $model,
                                     array_merge($params, array('checked'  => $isRequiredChecked,
                                                                'disabled' => $isRequiredDisabled)));
            if (!in_array($attributeData['elementType'], static::$restrictHiddenControlAttributes))
            {
                $isHiddenElement       = new DerivedCheckBoxElement($webFormAttributeForm, 'hidden', $model,
                                         array_merge($params, array('checked'     => $isHiddenChecked),
                                                              array('htmlOptions' => array('class' => 'hiddenAttribute',
                                                                                     'data-value' => $attributeName))));
                $isHiddenElement->editableTemplate       = '{content}{label}{error}';
                $renderHiddenAttributeElement = static::renderHiddenAttributeElement($webFormAttributeForm,
                                                'hiddenValue', $model, $attributeData['elementType'], $params);
                $isHiddenElementContent       = $isHiddenElement->render();
            }
            else
            {
                $isHiddenElementContent       = null;
                $renderHiddenAttributeElement = null;
            }
            $attributeLabelElement->editableTemplate = '{content}{error}';
            $isRequiredElement->editableTemplate     = '{content}{label}{error}';

            return array('{attributeName}'                   => $attributeName,
                         '{attributeLabelElement}'           => $attributeLabelElement->render(),
                         '{isRequiredElement}'               => $isRequiredElement->render(),
                         '{isHiddenElement}'                 => $isHiddenElementContent,
                         '{renderHiddenAttributeElement}'    => $renderHiddenAttributeElement,
                         '{removePlacedAttributeLink}'       => $removePlacedAttributeLink,
                         '{hideHiddenAttributeElementStyle}' => $hideHiddenAttributeElementStyle);
        }

        /**
         * @param integer $id
         * @return string
         */
        public static function getEmbedScript($id)
        {
            $embedScript = '<div id="zurmoExternalWebForm">' .
                           '<script type="text/javascript" ' .
                           'src="' . Yii::app()->createAbsoluteUrl('contacts/external/sourceFiles/',
                                                                    array('id' => $id)) . '">' .
                           '</script></div>';
            return $embedScript;
        }

        /**
         * @param $contactWebFormAttributes
         * @return array of placed attributes' name
         */
        public static function resolveWebFormAttributes($contactWebFormAttributes)
        {
            if (ArrayUtil::isAssoc($contactWebFormAttributes))
            {
                return array_keys($contactWebFormAttributes);
            }
            else
            {
                return $contactWebFormAttributes;
            }
        }

        /**
         * @param ContactWebForm $contactWebForm
         * @return array of placed attributes having custom labels
         */
        public static function getCustomDisplayLabels(ContactWebForm $contactWebForm)
        {
            $contactWebFormAttributes = unserialize($contactWebForm->serializedData);
            $customLabelAttributes    = array();
            if (ArrayUtil::isAssoc($contactWebFormAttributes))
            {
                foreach ($contactWebFormAttributes as $attributeId => $attributeData)
                {
                    if (isset($attributeData['label']))
                    {
                        $customLabelAttributes[$attributeId] = Zurmo::t('ContactWebFormsModule', $attributeData['label']);
                    }
                }
            }
            return $customLabelAttributes;
        }

        /**
         * @param ContactWebForm $contactWebForm
         * @return array of rules for custom required attributes
         */
        public static function getCustomRequiredFields(ContactWebForm $contactWebForm)
        {
            $contactWebFormAttributes = unserialize($contactWebForm->serializedData);
            $customRequiredFields     = array();
            if (ArrayUtil::isAssoc($contactWebFormAttributes))
            {
                foreach ($contactWebFormAttributes as $attributeId => $attributeData)
                {
                    if (isset($attributeData['required']) && $attributeData['required'] == true)
                    {
                        $customRequiredFields[] = $attributeId;
                    }
                }
            }
            return $customRequiredFields;
        }

        /**
         * @param $contactWebFormAttributes
         * @param array $allAttributes
         * @return array of required attributes - custom field required attributes and custom required attributes
         */
        public static function resolveWebFormWithAllRequiredAttributes($contactWebFormAttributes,
                                                                       $allAttributes = array())
        {
            if (count($allAttributes) == 0)
            {
                $allAttributes = static::getAllAttributes();
            }
            foreach ($allAttributes as $attributeName => $attributeData)
            {
                if ($attributeData['isRequired'] && !in_array($attributeName, $contactWebFormAttributes))
                {
                    $contactWebFormAttributes[] = $attributeName;
                }
            }
            return $contactWebFormAttributes;
        }

        /**
         * @param $contactWebForm
         * @param array $resolvedWebFormAttributes
         * @return array of web form attributes, excluding custom hidden attributes
         */
        public static function excludeHiddenAttributes(ContactWebForm $contactWebForm,
                                                       $resolvedWebFormAttributes = array())
        {
            $webFormAttributes = unserialize($contactWebForm->serializedData);
            foreach ($webFormAttributes as $attributeName => $attribute)
            {
                if (isset($attribute['hidden']) && $attribute['hidden'] == true &&
                    isset($attribute['hiddenValue']) && !empty($attribute['hiddenValue']))
                {
                    if (in_array($attributeName, $resolvedWebFormAttributes))
                    {
                        //Remove hidden attribute from resolved attributes
                        foreach ($resolvedWebFormAttributes as $resolvedAttributeIndex => $resolvedAttribute)
                        {
                            if ($resolvedAttribute == $attributeName)
                            {
                                unset($resolvedWebFormAttributes[$resolvedAttributeIndex]);
                                break;
                            }
                        }
                        $resolvedWebFormAttributes = array_values($resolvedWebFormAttributes);
                    }
                }
            }
            return $resolvedWebFormAttributes;
        }

        /**
         * @param $postVariableName
         * @param $contactWebForm
         */
        public static function resolveHiddenAttributesForContactModel($postVariableName, ContactWebForm $contactWebForm)
        {
            $ContactWebFormAttributes = unserialize($contactWebForm->serializedData);
            foreach ($ContactWebFormAttributes as $attributeName => $attribute)
            {
                if (isset($attribute['hidden']) && $attribute['hidden'] == true &&
                       isset($attribute['hiddenValue']) && !empty($attribute['hiddenValue']))
                {
                    $dropDownAttributeTypes = array('CheckBox', 'RadioDropDown', 'DropDown');
                    $allAttributes = ContactWebFormsUtil::getAllAttributes();
                    $attributeData = $allAttributes[$attributeName];
                    if (in_array($attributeData['elementType'], $dropDownAttributeTypes))
                    {
                        $_POST[$postVariableName][$attributeName]['value'] = $attribute['hiddenValue'];
                    }
                    else
                    {
                        $_POST[$postVariableName][$attributeName] = $attribute['hiddenValue'];
                    }
                }
            }
        }

        /**
         * @param array $webFormEntryAttributes
         * @param $contactWebForm
         * @return array of resolved web form attributes, for web form entry model
         */
        public static function resolveHiddenAttributesForContactWebFormEntryModel($webFormEntryAttributes = array(),
                                                                                  ContactWebForm $contactWebForm)
        {
            $ContactWebFormAttributes = unserialize($contactWebForm->serializedData);
            foreach ($ContactWebFormAttributes as $attributeName => $attribute)
            {
                if (isset($attribute['hidden']) && $attribute['hidden'] == true &&
                       isset($attribute['hiddenValue']) && !empty($attribute['hiddenValue']))
                {
                    $webFormEntryAttributes[$attributeName] = $attribute['hiddenValue'];
                }
            }
            return $webFormEntryAttributes;
        }

        /**
         * @param $model
         * @param $attributeName
         * @param $form
         * @param $elementType
         * @param $params
         * @return mixed
         */
        public static function renderHiddenAttributeElement($model, $attributeName, $form, $elementType, $params)
        {
            if ($elementType === 'CheckBox')
            {
                $className = 'BooleanStaticDropDownElement';
            }
            elseif ($elementType === 'RadioDropDown' || $elementType === 'DropDown')
            {
                $className = 'ContactWebFormAttributeFormStaticDropDownFormElement';
            }
            else
            {
                $className = $elementType . 'Element';
            }
            $element = new $className($model, $attributeName, $form, $params);
            $element->editableTemplate = '{content}{error}';
            $content = $element->render();
            return $content;
        }

        /**
         * @param $attributeData
         * @return string
         */
        public static function getPlacedAttributeContent($attributeData)
        {
            $label = ZurmoHtml::tag('label', array(), Zurmo::t('ContactWebFormsModule', 'Label'));
            $value = ZurmoHtml::tag('label', array(), Zurmo::t('Core', 'Value'));
            $content  = ZurmoHtml::openTag('li');
            $content .= ZurmoHtml::openTag('div', array('class' => 'dynamic-row webform-chosen-field clearfix'));
            $content .= ZurmoHtml::tag('span', array('class' => 'is-required-checkbox'),
                                        $attributeData['{isRequiredElement}']);
            $content .= ZurmoHtml::tag('span', array('class' => 'field-label'), $label .
                                        $attributeData['{attributeLabelElement}']);
            $content .= ZurmoHtml::tag('span', array('class' => 'is-hidden-checkbox'),
                                        $attributeData['{isHiddenElement}']);
            $content .= ZurmoHtml::openTag('span', array('id'    => 'hiddenAttributeElement_' .
                                                                     $attributeData['{attributeName}'],
                                                         'class' => 'hidden-field-label',
                                                         'style' => $attributeData['{hideHiddenAttributeElementStyle}']));
            $content .= $value . $attributeData['{renderHiddenAttributeElement}'];
            $content .= ZurmoHtml::closeTag('span');
            $content .= $attributeData['{removePlacedAttributeLink}'];
            $content .= ZurmoHtml::closeTag('li');
            return $content;
        }

        /**
         * @param $attributeName
         * @param $value
         * @return string
         */
        public static function sanitizeHiddenAttributeValue($attributeName, $value)
        {
            $designerType = ModelAttributeToDesignerTypeUtil::getDesignerType(new Contact(false), $attributeName);
            $sanitizedAttributeValue = $value;
            if ($designerType == 'Date' && !empty($value))
            {
                $sanitizedAttributeValue = DateTimeUtil::resolveValueForDateDBFormatted($value);
            }
            if ($designerType == 'DateTime' && !empty($value))
            {
                $sanitizedAttributeValue = DateTimeUtil::
                                               convertDateTimeLocaleFormattedDisplayToDbFormattedDateTimeWithSecondsAsZero($value);
            }
            return DataUtil::purifyHtml($sanitizedAttributeValue);
        }

        public static function getResolvedModuleIdForContactWebFormEntry(Contact $contact)
        {
            if (LeadsUtil::isStateALead($contact->state))
            {
                return 'leads';
            }
            return 'contacts';
        }
    }
?>