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

    class BuilderElementEditableModelForm extends CModel
    {
        public $className;
        public $content;
        public $properties;

        public function __construct(array $content, array $properties)
        {
            $this->content      = $content;
            $this->properties   = $properties;
        }

        public function attributeNames()
        {
            array('content', 'properties');
        }

        public function __get($name)
        {
            if (strpos($name, '['))
            {
                $basePropertyName   = substr($name, 0, strpos($name, '['));
                $index              = substr($name, strpos($name, '[') + 1);
                if (property_exists($this, $basePropertyName))
                {
                    return ArrayUtil::getNestedValue($this->{$basePropertyName}, $index);
                }
            }
            return parent::__get($name);
        }

        public function rules()
        {
            return array_merge(parent::rules(), array(
                array('className',  'safe'),
                array('content',    'safe'),
                array('content',    'validateContent'),
                array('properties', 'safe'),
                array('properties', 'validateProperties')
            ));
        }

        public function validateProperties($attribute, $params)
        {
            if (!isset($this->className))
            {
                throw new NotSupportedException();
            }
            $hasErrors = false;
            $elementClassName = $this->className;
            $element = new $elementClassName();
            $prefix = 'properties';
            $this->validatePropertiesByElement($this->$attribute, $element, $prefix, $hasErrors, $attribute, null);
            return !$hasErrors;
        }

        protected function validatePropertiesByElement($properties, $element, $prefix, & $hasErrors, $attribute, $key)
        {
            if (is_array($properties))
            {
                foreach ($properties as $key => $value)
                {
                    $this->validatePropertiesByElement($value, $element, $prefix . '_' . $key, $hasErrors, $attribute, $key);
                }
            }
            else
            {
                $error = $element->validate($key, $properties);
                if (!($error === true))
                {
                    $this->addError($prefix, $error);
                    $hasErrors = true;
                }
            }
        }

        public function validateContent($attribute, $params)
        {
            if (!isset($this->className))
            {
                throw new NotSupportedException();
            }
        }
    }
?>