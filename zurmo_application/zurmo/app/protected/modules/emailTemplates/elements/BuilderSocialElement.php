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

    class BuilderSocialElement extends BaseBuilderTableWrappedElement
    {
        public static function isUIAccessible()
        {
            return true;
        }

        protected static function resolveLabel()
        {
            return Zurmo::t('EmailTemplatesModule', 'Social');
        }

        protected function resolveDefaultProperties()
        {
            $properties              = array(
                'backend'       => array(
                    'layout'    => 'horizontal',
                    'services'  => array(
                        'Website'   =>  array(
                            'enabled'   => 1,
                            'url'       => Yii::app()->createAbsoluteUrl('/'),
                        ),
                    ),
                    'sizeClass' => 'button',
                ),
            );
            return $properties;
        }

        protected function renderControlContentNonEditable()
        {
            if (!isset($this->properties['backend']['services']))
            {
                return null;
            }
            $content    =   null;
            $sizeClass  = null;
            if (isset($this->properties['backend']['sizeClass']))
            {
                $sizeClass = $this->properties['backend']['sizeClass'];
            }
            foreach ($this->properties['backend']['services'] as $serviceName => $serviceDetails)
            {
                if (ArrayUtil::getArrayValue($serviceDetails, 'enabled') and ArrayUtil::getArrayValue($serviceDetails, 'url'))
                {
                    $properties = array();
                    $properties['frontend']['href']     = $serviceDetails['url'];
                    $properties['frontend']['target']   = '_blank';
                    $properties['backend']['text']      = $serviceName;
                    $properties['backend']['sizeClass'] = 'button social-button ' . $serviceName . ' ' . $sizeClass;
                    $id                                 = $this->id . '_' . $serviceName;
                    $element         = BuilderElementRenderUtil::resolveElement('BuilderSocialButtonElement', $this->renderForCanvas, $id, $properties);

                    $content .= $element->renderNonEditable();
                    $content .= $this->resolveSpacerContentForVerticalLayout();
                    $content .= $this->resolveTdCloseAndOpenContentForHorizontalLayout();
                }
            }
            return $content;
        }

        protected function renderSettingsTab(ZurmoActiveForm $form)
        {
            $propertiesForm     = BuilderSocialElementPropertiesEditableElementsUtil::render($this->model, $form);
            return $propertiesForm;
        }

        protected function renderContentTab(ZurmoActiveForm $form)
        {
            return null;
        }

        protected function resolveNonEditableWrapperHtmlOptions()
        {
            $htmlOptions            = parent::resolveNonEditableWrapperHtmlOptions();
            $htmlOptions['class']  .= ' ' . $this->properties['backend']['layout'];
            return $htmlOptions;
        }

        protected function resolveNonEditableContentWrappingTdHtmlOptions()
        {
            return array();
        }

        protected function resolveSpacerContentForVerticalLayout()
        {
            if ($this->getLayout() == 'vertical')
            {
                $urlToImage = PlaceholderImageUtil::resolveTransparentImageUrl(true);
                return ZurmoHtml::image($urlToImage, '', array('width' => '100%', 'height' => '15'));
            }
        }

        protected function resolveTdCloseAndOpenContentForHorizontalLayout()
        {
            if ($this->getLayout() == 'horizontal')
            {
                $urlToImage = PlaceholderImageUtil::resolveTransparentImageUrl(true);
                $content    = ZurmoHtml::closeTag('td');
                $content   .= ZurmoHtml::openTag('td', array('class' => 'social-horizontal-expander'));
                $content   .= ZurmoHtml::image($urlToImage, '', array('width' => '15', 'height' => '15'));
                $content   .= ZurmoHtml::closeTag('td');
                $content   .= ZurmoHtml::openTag('td');
                return $content;
            }
        }

        protected function getLayout()
        {
            return $this->properties['backend']['layout'];
        }
    }
?>