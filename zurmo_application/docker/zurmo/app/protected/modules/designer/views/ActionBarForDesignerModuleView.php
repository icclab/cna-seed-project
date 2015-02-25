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
     * Renders an action bar specifically for the various module views in designer
     */
    class ActionBarForDesignerModuleView extends ConfigurableMetadataView
    {
        protected $controllerId;

        protected $moduleId;

        protected $module;

        protected $activeActionElementType;

        public function __construct($controllerId, $moduleId, Module $module, $activeActionElementType)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($activeActionElementType)');
            $this->controllerId              = $controllerId;
            $this->moduleId                  = $moduleId;
            $this->module                    = $module;
            $this->activeActionElementType   = $activeActionElementType;
        }

        protected function renderContent()
        {
            $content  = '<div class="view-toolbar-container clearfix"><nav class="pillbox clearfix">';
            $content .= $this->renderActionElementBar(false);
            $content .= '</nav></div>';
            return $content;
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array(
                                'type'            => 'DesignerGeneralMenu',
                                'iconClass'       => 'icon-general',
                                'moduleClassName' => 'eval:get_class($this->module)',
                            ),
                            array(
                                'type'            => 'DesignerFieldsMenu',
                                'iconClass'       => 'icon-fields',
                                'moduleClassName' => 'eval:get_class($this->module)',
                            ),
                            array(
                                'type'            => 'DesignerLayoutsMenu',
                                'iconClass'       => 'icon-layout',
                                'moduleClassName' => 'eval:get_class($this->module)',
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        protected function resolveActionElementInformationDuringRender(& $elementInformation)
        {
            parent::resolveActionElementInformationDuringRender($elementInformation);
            if ($elementInformation['type'] == $this->activeActionElementType)
            {
                if (isset($elementInformation['htmlOptions']['class']))
                {
                    $elementInformation['htmlOptions']['class'] .= ' active';
                }
                else
                {
                    $elementInformation['htmlOptions']['class'] = 'active';
                }
            }
        }

        /**
         * @param $element
         * @param $elementInformation
         * @return bool
         */
        protected function shouldRenderToolBarElement($element, $elementInformation)
        {
            assert('$element instanceof ActionElement');
            assert('is_array($elementInformation)');
            $moduleMenuItems = $this->module->getDesignerMenuItems();
            if (!parent::shouldRenderToolBarElement($element, $elementInformation))
            {
                return false;
            }
            if ($elementInformation['type'] == 'DesignerGeneralMenu' &&
               !ArrayUtil::getArrayValue($moduleMenuItems, 'showGeneralLink'))
            {
                return false;
            }
            if ($elementInformation['type'] == 'DesignerFieldsMenu' &&
               !ArrayUtil::getArrayValue($moduleMenuItems, 'showFieldsLink'))
            {
                return false;
            }
            if ($elementInformation['type'] == 'DesignerLayoutsMenu' &&
               !ArrayUtil::getArrayValue($moduleMenuItems, 'showLayoutsLink'))
            {
                return false;
            }
            return true;
        }
    }
?>