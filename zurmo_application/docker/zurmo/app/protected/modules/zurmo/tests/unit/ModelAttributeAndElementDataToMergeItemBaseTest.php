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

    class ModelAttributeAndElementDataToMergeItemBaseTest extends ZurmoBaseTest
    {
        public $selectedModels          = array();

        public $attributesToBeTested    = array();

        public $nonDerivedAttributes    = array();

        public $dropdownAttributes      = array();

        public $multiAttributeElements  = array();

        public $modelAttributeAndElementDataToMergeItem = 'ModelAttributeAndElementDataToMergeItem';

        public $modelClass;

        public $derivedElementInterfaceDropdownAttributesElements = array();

        public $modelElements = array();

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        protected function getEscapedAttributes()
        {
            return array(
                'Address' => array('latitude', 'longitude', 'invalid')
            );
        }

        protected function getEscapedAttributeByType($type)
        {
            $escapedAttributes = $this->getEscapedAttributes();
            if (isset($escapedAttributes[$type]))
            {
                return $escapedAttributes[$type];
            }
            return array();
        }

        protected function getRenderedContent()
        {
            $primaryModel         = $this->selectedModels[0];
            $attributesToBeTested = $this->attributesToBeTested;
            $content              = null;
            foreach ($attributesToBeTested as $attribute => $elementType)
            {
                $attributeContent = null;
                $position = 1;
                foreach ($this->selectedModels as $selectedModel)
                {
                    $elementClass = $elementType . 'Element';
                    $type         = new $elementClass($selectedModel, $attribute);
                    $modelAttributeAndElementDataToMergeItemClass = $this->modelAttributeAndElementDataToMergeItem;
                    $modelAttributeAndElementDataToMergeItem
                                  = new $modelAttributeAndElementDataToMergeItemClass($selectedModel,
                                                                    $type->getAttribute(), $type, $primaryModel, $position++);
                    $attributeContent .= $modelAttributeAndElementDataToMergeItem->getAttributeRenderedContent();
                    $content      .= ZurmoHtml::tag('div', array(), $attributeContent);
                }
            }
            return $content;
        }

        protected function verifyNonDerivedAttributes($content)
        {
            $model  = $this->selectedModels[0];
            $model1 = $this->selectedModels[1];
            $nonDerivedAttributes = $this->nonDerivedAttributes;
            foreach ($nonDerivedAttributes as $nonDerivedAttribute)
            {
                $matcherElement1 = array(
                    'tag' => 'a',
                    'attributes' => array('data-id' => $this->modelClass . '_' . $nonDerivedAttribute,
                                          'data-value' => $model->$nonDerivedAttribute)
            );
                $matcherElement2 = array(
                    'tag' => 'a',
                    'attributes' => array('data-id' => $this->modelClass . '_' . $nonDerivedAttribute,
                                          'data-value' => $model1->$nonDerivedAttribute)
                );

                $this->assertTag($matcherElement1, $content);
                $this->assertTag($matcherElement2, $content);
            }
        }

        protected function verifyDropdownAttributes($content)
        {
            $model  = $this->selectedModels[0];
            $model1 = $this->selectedModels[1];
            $dropdownAttributes   = $this->dropdownAttributes;
            foreach ($dropdownAttributes as $dropdownAttribute)
            {
                $matcherElement1 = array(
                    'tag'        => 'a',
                    'attributes' => array('data-id' => $this->modelClass . '_' . $dropdownAttribute . '_value',
                                          'data-value' => $model->$dropdownAttribute->value)
                );
                $matcherElement2 = array(
                    'tag'        => 'a',
                    'attributes' => array('data-id' => $this->modelClass . '_' . $dropdownAttribute . '_value',
                                          'data-value' => $model1->$dropdownAttribute->value)
                );
                $this->assertTag($matcherElement1, $content);
                $this->assertTag($matcherElement2, $content);
            }
        }

        protected function verifyMultiAttributeElements($content)
        {
            $model  = $this->selectedModels[0];
            $model1 = $this->selectedModels[1];
            $multiAttributeElements = $this->multiAttributeElements;
            foreach ($multiAttributeElements as $multiAttribute => $type)
            {
                $elementClassName   = $type . 'Element';
                $relatedAttributes  = $elementClassName::getModelAttributeNames();
                foreach ($relatedAttributes as $relatedAttribute)
                {
                    if (!in_array($relatedAttribute, $this->getEscapedAttributeByType($type)))
                    {
                        $matcherElement1 = array(
                            'tag' => 'a',
                            'attributes' => array('data-id' => $this->modelClass . '_' . $multiAttribute . '_' . $relatedAttribute,
                                                  'data-value' => $model->$multiAttribute->$relatedAttribute)
                        );
                        $matcherElement2 = array(
                            'tag' => 'a',
                            'attributes' => array('data-id' => $this->modelClass . '_' . $multiAttribute . '_' . $relatedAttribute,
                                                  'data-value' => $model1->$multiAttribute->$relatedAttribute)
                        );
                        $this->assertTag($matcherElement1, $content);
                        $this->assertTag($matcherElement2, $content);
                    }
                }
            }
        }

        protected function verifyDerivedDropdownAttributeElements($content)
        {
            $model  = $this->selectedModels[0];
            $model1 = $this->selectedModels[1];
            $dropdownAttributeElements   = $this->derivedElementInterfaceDropdownAttributesElements;
            foreach ($dropdownAttributeElements as $dropdownAttributeElement)
            {
                $elementClassName   = $dropdownAttributeElement . 'Element';
                $relatedAttributes  = $elementClassName::getModelAttributeNames();
                foreach ($relatedAttributes as $relatedAttribute)
                {
                    $matcherElement1 = array(
                        'tag'        => 'a',
                        'attributes' => array('data-id' => $this->modelClass . '_' . $relatedAttribute . '_id',
                                              'data-value' => $model->$relatedAttribute->id)
                    );
                    $matcherElement2 = array(
                        'tag'        => 'a',
                        'attributes' => array('data-id' => $this->modelClass . '_' . $relatedAttribute . '_id',
                                              'data-value' => $model1->$relatedAttribute->id)
                    );
                    $this->assertTag($matcherElement1, $content);
                    $this->assertTag($matcherElement2, $content);
                }
            }
        }

        protected function getIndustryValues()
        {
            $values = array(
                'Automotive',
                'Adult Entertainment',
                'Financial Services',
                'Mercenaries & Armaments',
            );
            $industryFieldData = CustomFieldData::getByName('Industries');
            $industryFieldData->serializedData = serialize($values);
            $this->assertTrue($industryFieldData->save());
            return $values;
        }

        protected function verifyModelElements($content)
        {
            $model  = $this->selectedModels[0];
            $model1 = $this->selectedModels[1];
            $modelElements = $this->modelElements;
            foreach ($modelElements as $modelElement)
            {
                $matcherElement1 = array(
                    'tag' => 'a',
                    'attributes' => array('data-id'          => $this->modelClass . '_' . $modelElement . '_name',
                                          'data-value'       => $model->$modelElement->name,
                                          'data-hiddenid'    => $this->modelClass . '_' . $modelElement . '_id',
                                          'data-hiddenvalue' => $model->$modelElement->id)
            );
                $matcherElement2 = array(
                    'tag' => 'a',
                    'attributes' => array('data-id'          => $this->modelClass . '_' . $modelElement . '_name',
                                          'data-value'       => $model1->$modelElement->name,
                                          'data-hiddenid'    => $this->modelClass . '_' . $modelElement . '_id',
                                          'data-hiddenvalue' => $model1->$modelElement->id)
                );

                $this->assertTag($matcherElement1, $content);
                $this->assertTag($matcherElement2, $content);
            }
        }
    }
?>