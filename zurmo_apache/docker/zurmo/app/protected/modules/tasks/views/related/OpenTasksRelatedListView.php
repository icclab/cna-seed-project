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

    abstract class OpenTasksRelatedListView extends SecuredRelatedListView
    {
        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'perUser' => array(
                    'title' => "eval:Zurmo::t('TasksModule', 'Open TasksModulePluralLabel', LabelUtil::getTranslationParamsForAllModules())",
                ),
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array(  'type'             => 'CreateFromRelatedModalLink',
                                    'portletId'        => 'eval:$this->params["portletId"]',
                                    'routeModuleId'    => 'eval:$this->moduleId',
                                    'routeParameters'  => 'eval:$this->getCreateLinkRouteParameters()',
                                    'ajaxOptions'      => 'eval:TasksUtil::resolveAjaxOptionsForModalView("Create")',
                                    'uniqueLayoutId'   => 'eval:$this->uniqueLayoutId',
                                    'modalContainerId' => 'eval:TasksUtil::getModalContainerId()'
                                 ),
                        ),
                    ),
                    'rowMenu' => array(
                        'elements' => array(
                            array(  'type'             => 'EditModalLink',
                                    'htmlOptions'      => 'eval:$this->getActionModalLinksHtmlOptions("Edit")'
                                 ),
                            array(  'type'             => 'CopyModalLink',
                                    'htmlOptions'      => 'eval:$this->getActionModalLinksHtmlOptions("Copy")'
                                 ),
                            array('type' => 'TaskRelatedDeleteLink'),
                        ),
                    ),
                    'derivedAttributeTypes' => array(
                        'CloseTaskCheckBox',
                    ),
                    'nonPlaceableAttributeNames' => array(
                        'latestDateTime',
                    ),
                    'gridViewType' => RelatedListView::GRID_VIEW_TYPE_STACKED,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'CloseTaskCheckBox'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'name', 'type' => 'Text', 'isLink' => true),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        /**
         * @return array
         */
        protected function makeSearchAttributeData()
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'activityItems',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => (int)$this->params['relationModel']->getClassId('Item'),
                ),
                2 => array(
                    'attributeName'        => 'status',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => Task::STATUS_COMPLETED
                )
            );
            $searchAttributeData['structure'] = '(1 and 2)';
            return $searchAttributeData;
        }

        /**
         * @return string
         */
        public static function getModuleClassName()
        {
            return 'TasksModule';
        }

        /**
         * Override to handle security/access resolution on links.
         */
        public function getLinkString($attributeString, $attribute)
        {
            return array($this, 'resolveLinkString');
        }

        /**
         * Resolves the link string for task detail modal view
         * @param array $data
         * @param int $row
         * @return string
         */
        public function resolveLinkString($data, $row)
        {
            $content = TasksUtil::getModalDetailsLink($data, $this->controllerId,
                                                      $this->moduleId,
                                                      $this->getActionModuleClassName(), false);
            return $content;
        }

        /**
         * Override to pass the sourceId
         * @return type
         */
        protected function getCreateLinkRouteParameters()
        {
            $routeParams = array_merge( array('sourceId' => $this->getGridViewId()),
                                        parent::getCreateLinkRouteParameters());
            if (($redirectUrl = ArrayUtil::getArrayValue($routeParams, 'redirectUrl')) != null)
            {
                $routeParams['redirectUrl'] = TasksUtil::resolveOpenTasksActionsRedirectUrlForDetailsAndRelationsView($redirectUrl);
            }
            return $routeParams;
        }

        /**
         * Register the additional script for task detail modal
         */
        protected function renderScripts()
        {
            parent::renderScripts();
            Yii::app()->custom->registerTaskModalDetailsScript($this->getGridViewId());
            TasksUtil::registerTaskModalEditScript($this->getGridViewId(), $this->getCreateLinkRouteParameters());
            TasksUtil::registerTaskModalCopyScript($this->getGridViewId(), $this->getCreateLinkRouteParameters());
            TasksUtil::registerTaskModalDeleteScript($this->getGridViewId());
        }

        /**
         * Get action modal link html options based on type
         * @param string $type
         * @return array
         */
        protected function getActionModalLinksHtmlOptions($type)
        {
            if ($type == "Edit")
            {
                return array('class' => 'edit-related-open-task');
            }
            elseif ($type == "Copy")
            {
                return array('class' => 'copy-related-open-task');
            }
        }

        /**
         * Resolve row menu column class.
         * @return string
         */
        protected function resolveRowMenuColumnClass()
        {
            return Yii::app()->custom->resolveRowMenuColumnClassForOpenTaskPortlet($this->getRelationAttributeName());
        }

        /**
         * Gets sort attribute for data provider.
         * @return string
         */
        protected function getSortAttributeForDataProvider()
        {
            return 'dueDateTime';
        }

        /**
         * @return string
         */
        public function renderPortletHeadContent()
        {
            $parentContent          = parent::renderPortletHeadContent();
            $defaultOptionsContent  = $this->renderWrapperAndActionElementMenu(Zurmo::t('Core', 'Options'));
            $wrappedContent         = Yii::app()->custom->renderPortletHeadContentForOpenTaskPortletOnDetailsAndRelationsView(get_class($this),
                                                                                                                      $this->params,
                                                                                                                      $defaultOptionsContent,
                                                                                                                      $parentContent);
            return $wrappedContent;
        }
    }
?>