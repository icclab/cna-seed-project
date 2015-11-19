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
     * The configurable View for a model detail view with relation views.
     */
    abstract class ConfigurableDetailsAndRelationsView extends DetailsAndRelationsView
    {
        /**
         * @param string $controllerId
         * @param string $moduleId
         * @param array $params
         */
        public function __construct($controllerId, $moduleId, $params)
        {
            assert('isset($params["controllerId"])');
            assert('isset($params["relationModuleId"])');
            assert('isset($params["relationModel"])');
            $this->controllerId        = $controllerId;
            $this->moduleId            = $moduleId;
            $this->uniqueLayoutId      = get_class($this);
            $this->params              = $params;
            $this->modelId             = $params["relationModel"]->id;
        }

        /**
         * @return string
         */
        protected function renderContent()
        {
            $metadata = self::getMetadata();
            $portletsAreRemovable   = true;
            $portletsAreMovable     = true;
            $this->resolvePortletConfigurableParams($portletsAreMovable, $portletsAreRemovable);
            $content          = $this->renderActionElementBar(true);
            $viewClassName    = static::getModelRelationsSecuredPortletFrameViewClassName();
            $configurableView = new $viewClassName( $this->controllerId,
                                                    $this->moduleId,
                                                    $this->uniqueLayoutId,
                                                    $this->params,
                                                    $metadata,
                                                    false,
                                                    $portletsAreMovable,
                                                    false,
                                                    static::getDefaultLayoutType(), // This could be driven by a db value based on layout type id
                                                    $portletsAreRemovable);
            $content          .=  $configurableView->render();
            $content          .= $this->renderScripts();
            return $content;
        }

        /**
         * @param bool $renderedInForm
         * @return A|string
         */
        protected function renderActionElementBar($renderedInForm)
        {
            $getData = GetUtil::getData();
            if (isset($getData['kanbanBoard']) && $getData['kanbanBoard'] == 1)
            {
                $isKanbanActive = true;
            }
            else
            {
                $isKanbanActive = false;
            }
            $toolbarContent = null;
            if (Yii::app()->userInterface->isMobile() === false)
            {
                $kanbanToggleLink    = ZurmoDefaultViewUtil::renderActionBarLinksForKanbanBoard(
                                       $this->controllerId, $this->moduleId, $this->modelId, $isKanbanActive);
                if ($isKanbanActive)
                {
                    $content = parent::renderActionElementBar($renderedInForm) . $kanbanToggleLink;
                }
                else
                {
                    $content = $kanbanToggleLink. $this->resolveAndRenderLockingLink($renderedInForm);
                }
                $toolbarContent = ZurmoHtml::tag('nav', array('class' => 'pillbox clearfix'), $content);
            }
            $toolbarContent = ZurmoHtml::tag('div', array('class' => 'view-toolbar-container widgets-lock clearfix '), $toolbarContent);
            return $toolbarContent;
        }

        protected function resolveAndRenderLockingLink($renderedInForm)
        {
            $isViewLocked     = ZurmoDefaultViewUtil::getLockKeyForDetailsAndRelationsView('lockPortletsForDetailsAndRelationsView');
            if ($isViewLocked === false)
            {
                $title   = Zurmo::t('Core', 'Lock and prevent layout changes to this screen');
                $url     = $this->resolveLockPortletUrl($this->params["relationModel"]->id, true);
                $icon    = ZurmoHtml::tag('i', array('class' => 'icon-unlock'), '<!--' . Zurmo::t('Core', 'Lock') . '-->');
                $link    = ZurmoHtml::link($icon, $url, array('title' => $title));
                $content = ZurmoHtml::tag('nav', array('class' => 'default-button'), $link);
                $content = parent::renderActionElementBar($renderedInForm) . $this->renderPushLayoutButton() . $content;
            }
            else
            {
                $title   = Zurmo::t('Core', 'Unlock to edit this screen\'s layout');
                $url     = $this->resolveLockPortletUrl($this->params["relationModel"]->id, false);
                $icon    = ZurmoHtml::tag('i', array('class' => 'icon-lock'), '<!--' . Zurmo::t('Core', 'Unlock') . '-->');
                $content = ZurmoHtml::link($icon, $url, array('title' => $title));
                $content = ZurmoHtml::tag('nav', array('class' => 'default-button'), $content);
            }
            return $content;
        }

        protected function renderPushLayoutButton()
        {
            if (PushDashboardUtil::canCurrentUserPushDashboardOrLayout())
            {
                $pushLayoutLinkActionElement  = new PushLayoutLinkActionElement(
                                                    $this->controllerId, $this->moduleId, $this->modelId,
                                                    array('htmlOptions' => array('id' => 'PushLayoutLink'),
                                                          'iconClass'   => 'icon-change-dashboard'));
                return $pushLayoutLinkActionElement->render();
            }
        }

        /**
         * @return array
         */
        protected static function resolveAjaxOptionsForAddPortlet()
        {
            $title = Zurmo::t('HomeModule', 'Add Portlet');
            return ModalView::getAjaxOptionsForModalLink($title);
        }

        /**
         * Resolves url for lock/unlock functionality
         * @param string $id
         * @param string $lockPortlets
         * @return string
         */
        private function resolveLockPortletUrl($id, $lockPortlets)
        {
            assert('is_bool($lockPortlets)');
            assert('is_int($id)');
            $url = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/details',
                                         array('id' => $id, 'lockPortlets' => $lockPortlets));
            return $url;
        }

        /**
         * Resolves portlet configurable parameters
         * @param boolean $portletsAreMovable
         * @param boolean $portletsAreRemovable
         */
        protected function resolvePortletConfigurableParams(& $portletsAreMovable, & $portletsAreRemovable)
        {
            assert('is_bool($portletsAreMovable)');
            assert('is_bool($portletsAreRemovable)');
            $getData = GetUtil::getData();
            if (isset($getData['lockPortlets']))
            {
                $lockPortlets = (bool)$getData['lockPortlets'];
                if ($lockPortlets)
                {
                    ZurmoDefaultViewUtil::setLockKeyForDetailsAndRelationsView('lockPortletsForDetailsAndRelationsView', true);
                }
                else
                {
                    ZurmoDefaultViewUtil::setLockKeyForDetailsAndRelationsView('lockPortletsForDetailsAndRelationsView', false);
                }
            }
            $isViewLocked = ZurmoDefaultViewUtil::getLockKeyForDetailsAndRelationsView('lockPortletsForDetailsAndRelationsView');
            //Default case for the first time
            if ($isViewLocked === null)
            {
                ZurmoDefaultViewUtil::setLockKeyForDetailsAndRelationsView('lockPortletsForDetailsAndRelationsView', true);
                $isViewLocked = true;
            }
            if ($isViewLocked == true)
            {
                $portletsAreRemovable   = false;
                $portletsAreMovable     = false;
            }
        }

        /**
         * Get layout type for configurable details and relations view
         * @return type
         */
        public static function getLayoutTypesData()
        {
            return array(
                '100'   => Zurmo::t('HomeModule', '{number} Column', array('{number}' => 1)),
                '50,50' => Zurmo::t('HomeModule', '{number} Columns', array('{number}' => 2)), // Not Coding Standard
                '75,25' => Zurmo::t('HomeModule', '{number} Columns Left Strong', array('{number}' => 2)), // Not Coding Standard
            );
        }

        /**
         * Get the layout type
         * @return string
         */
        public static function getDefaultLayoutType()
        {
            return '75,25'; // Not Coding Standard
        }
    }
?>