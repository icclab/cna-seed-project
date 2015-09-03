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

    class BuilderElementPropertiesEditableElementsUtil
    {
        public static function render(CModel $model, ZurmoActiveForm $form, array $excludeItems = array(), $wrapInTr = true, array $trOptions = array())
        {
            static::registerScripts($form, $model);
            $content                = null;
            $configurationItems     = static::resolveConfiguration();
            foreach ($configurationItems as $configurationItem)
            {
                $util           = null;
                $elementClass   = null;
                $property       = null;
                $params         = array();
                extract($configurationItem);
                if (!in_array($property, $excludeItems))
                {
                    $property       = "[${property}]";
                    $content        .= $util::render($elementClass, $model, $property, $form, $params, $wrapInTr, $trOptions);
                }
            }
            return $content;
        }

        /**
         * @return array
         */
        protected static function resolveConfiguration()
        {
            return array();
        }

        /**
         * @param $util
         * @param $elementClass
         * @param $property
         * @param $params
         * @return array
         */
        protected static function resolveConfigurationItem($util, $elementClass, $property, $params)
        {
            return compact('util', 'elementClass', 'property', 'params');
        }

        /**
         * @param string $label
         * @return array
         */
        public static function resolveDefaultParams($label = '')
        {
            $params = array();
            // we set label to an empty string as a default value.
            // we already hide label in non-editable representation of content element.
            // it is only shown in editable representation, which can also be overriden to hide it.
            // setting it to empty string here isn't to hide it.
            // it is rather to avoid Element trying to do ask ModelForm's model for a label.
            // BuilderElementEditableModelForm does not set a model so we would see an error there.
            $params['labelHtmlOptions'] = array('label' => $label);
            return $params;
        }

        /**
         * @param ZurmoActiveForm $form
         * @param CModel $model
         */
        protected static function registerScripts(ZurmoActiveForm $form, CModel $model)
        {
        }
  }
?>