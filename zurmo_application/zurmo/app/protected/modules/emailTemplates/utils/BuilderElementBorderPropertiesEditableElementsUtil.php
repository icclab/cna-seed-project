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

    class BuilderElementBorderPropertiesEditableElementsUtil extends BuilderElementPropertiesEditableElementsUtil
    {
        public static function render(CModel $model, ZurmoActiveForm $form, array $excludeItems = array(), $wrapInTr = true, array $trOptions = array())
        {
            $content                               = parent::render($model, $form, $excludeItems, $wrapInTr, $trOptions);
            $positionParams                        = static::resolveDefaultParams(Zurmo::t('Core', 'Position'));
            $positionParams['checkboxInputPrefix'] = 'properties[backend][border-negation]';
            $positionElement                        = new BorderPositionElement($model, null, $form, $positionParams);
            $content                               .= $positionElement->render();
            return $content;
        }

        protected static function resolveConfiguration()
        {
            $configurationItems         = array();
            $configurationItems[]       = static::resolveConfigurationItem(
                                            'BuilderElementInlineStylePropertiesEditableElementUtil',
                                            'CustomColorElement',
                                            'border-color',
                                            static::resolveBorderColorParams());
            $configurationItems[]       = static::resolveConfigurationItem(
                                            'BuilderElementInlineStylePropertiesEditableElementUtil',
                                            'TextElement',
                                            'border-width',
                                            static::resolveDefaultParams(
                                                Zurmo::t('EmailTemplatesModule', 'Border Width')));
            $configurationItems[]       = static::resolveConfigurationItem(
                                            'BuilderElementInlineStylePropertiesEditableElementUtil',
                                            'TextElement',
                                            'border-radius',
                                            static::resolveDefaultParams(
                                                Zurmo::t('EmailTemplatesModule', 'Border Radius')));
            $configurationItems[]       = static::resolveConfigurationItem(
                                            'BuilderElementInlineStylePropertiesEditableElementUtil',
                                            'BorderStyleStaticDropDownFormElement',
                                            'border-style',
                                            static::resolveDefaultParams(
                                                Zurmo::t('EmailTemplatesModule', 'Border Style')));
            return $configurationItems;
        }

        protected static function registerScripts(ZurmoActiveForm $form, CModel $model)
        {
            $borderStyleDropDownName  = BuilderElementInlineStylePropertiesEditableElementUtil::
                                                            resolveQualifiedAttributeName($model, 'border-style');
            Yii::app()->clientScript->registerScript('selectBorderTypeAndPositionsOnBorderColorChangeEvent', '
                function selectBorderTypeAndPositionsOnBorderColorChangeEvent(firstChange)
                {
                    if (firstChange)
                    {
                        if (areAllDirectionalCheckboxesUnchecked())
                        {
                            // only change selection if user has not made one already.
                            checkAllDirectionalCheckboxesAndRaiseEvents();
                        }
                        if ($("[name=\"' . $borderStyleDropDownName . '\"]").val() == "")
                        {
                            // first one is "None"
                            $("[name=\"' . $borderStyleDropDownName . '\"]  option:nth-child(2)").attr("selected", "selected");
                        }
                    }
                }
            ');
        }

        protected static function resolveBorderColorParams()
        {
            $params     = static::resolveDefaultParams(Zurmo::t('EmailTemplatesModule', 'Border Color'));
            static::resolveBorderColorParamsForColorChangeHandler($params);
            return $params;
        }

        protected static function resolveBorderColorParamsForColorChangeHandler(array & $params)
        {
            $params[CustomColorElement::COLOR_CHANGE_HANDLER_KEY] = 'selectBorderTypeAndPositionsOnBorderColorChangeEvent';
        }
    }
?>