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
     * Kanban view for tasks related to account/contact/lead/opportunity
     */
    abstract class TasksForRelatedKanbanView extends SecuredRelatedListView
    {
        /**
         * Override to have the default
         * @var bool
         */
        protected $renderViewToolBarDuringRenderContent = true;

        protected static $defaultPageSize = 1000;

        protected $searchFormModel;

        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'        => 'RelatedKanbanViewDetailsMenu',
                                  'iconClass'   => 'icon-details',
                                  'id'          => 'RelatedKanbanViewActionMenu',
                                  'itemOptions' => array('class' => 'hasDetailsFlyout'),
                                  'model'       => 'eval:$this->params["relationModel"]',
                            ),
                            array('type'                => 'CreateTaskFromRelatedKanbanModalMenu',
                                  'routeModuleId'       => 'eval:$this->moduleId',
                                  'routeParameters'     => 'eval:$this->getCreateLinkRouteParameters()',
                                  'ajaxOptions'         => 'eval:TasksUtil::resolveAjaxOptionsForModalView("Create", $this->getGridViewId())',
                                  'sourceKanbanBoardId' => 'eval:$this->getGridViewId()',
                                  'modalContainerId'    => 'eval:TasksUtil::getModalContainerId()',
                            ),

                        ),
                    ),
                ),
            );
            return $metadata;
        }

        /**
         * Constructor for the view
         * @param string $controllerId
         * @param string $moduleId
         * @param string $modelClassName
         * @param object $dataProvider
         * @param array $params
         * @param string $gridIdSuffix
         * @param array $gridViewPagerParams
         * @param object $kanbanBoard
         */
        public function __construct(
            $controllerId,
            $moduleId,
            $modelClassName,
            $dataProvider,
            $params,
            $gridIdSuffix = null,
            $gridViewPagerParams = array(),
            $kanbanBoard            = null,
            $searchModel            = null
        )
        {
            assert('is_string($modelClassName)');
            assert('is_array($this->gridViewPagerParams)');
            assert('$kanbanBoard === null || $kanbanBoard instanceof $kanbanBoard');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->modelClassName         = $modelClassName;
            $this->dataProvider           = $dataProvider;
            $this->gridIdSuffix           = $gridIdSuffix;
            $this->gridViewPagerParams    = $gridViewPagerParams;
            $this->gridId                 = $this->getGridId();
            $this->setKanbanBoard($kanbanBoard);
            $this->params                 = $params;
            $this->modelId                = $params["relationModel"]->id;
            $this->searchFormModel        = $searchModel;
            if($this->searchFormModel !== null)
            {
                $this->searchFormModel->setKanbanBoard($kanbanBoard);
            }
        }

        /**
         * Renders content for a list view. Utilizes a CActiveDataprovider
         * and a CGridView widget.
         * and form layout.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ListView");
            $cClipWidget->widget($this->getGridViewWidgetPath(), $this->getCGridViewParams());
            $cClipWidget->endClip();
            $content     = $this->renderKanbanViewTitleWithActionBars();
            if($this->searchFormModel !== null)
            {
                $content    .= $this->renderSearchView();
            }
            $this->registerKanbanGridScript();
            TasksUtil::resolveShouldOpenToTask($this->getGridId());
            $content    .= $cClipWidget->getController()->clips['ListView'] . "\n";
            $content .= $this->renderScripts();
            $zeroModelView = new ZeroTasksForRelatedModelYetView($this->controllerId,
                                                                 $this->moduleId, 'Task',
                                                                 get_class($this->params['relationModel']));
            $content .= $zeroModelView->render();
            $content .= $this->renderUIOverLayBlock();
            return $content;
        }

        /**
         * Resolve extra parameters for kanban board
         * @return array
         */
        protected function resolveExtraParamsForKanbanBoard()
        {
            return array('cardColumns' => $this->getCardColumns());
        }

        /**
         * @return array
         */
        protected function getCardColumns()
        {
            $columns = array(
                'name'   => array('value'   => $this->getLinkString('$data->name', 'name'), 'class' => 'task-name'),
                'status' => array('value'   => 'TasksUtil::resolveActionButtonForTaskByStatus(intval($data->status), "' .
                                               $this->controllerId . '", "' . $this->moduleId . '", $data->id)',
                                               'class' => 'task-status'),
                'subscribe' => array('value' => array('TasksUtil', 'getKanbanSubscriptionLink'),
                                                'class' => 'task-subscription'),
                'completionBar' => array('value' => 'TasksUtil::renderCompletionProgressBarContent($data)',
                                                    'class' => 'task-completion')
            );
            return Yii::app()->custom->resolveKanbanCardColumns($columns);
        }

        /**
         * @return array
         */
        protected function getCGridViewColumns()
        {
            $columns = array();
            return $columns;
        }

        /**
         * Gets module class name for the view
         * @return string
         */
        public static function getModuleClassName()
        {
            return 'TasksModule';
        }

        /**
         * Resolves pagination params
         * @return array
         */
        protected function resolvePaginationParams()
        {
            return GetUtil::getData();
        }

        /**
         * Makes search attribute data
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
                )
            );
            $searchAttributeData['structure'] = '1';
            return $searchAttributeData;
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
            $content     = TasksUtil::getModalDetailsLink($data,
                                                          $this->controllerId,
                                                          $this->moduleId,
                                                          $this->getActionModuleClassName());
            return $content;
        }

        /**
         * Gets relation attribute name
         * @return null
         */
        protected function getRelationAttributeName()
        {
            return null;
        }

        /**
         * @return null
         */
        public function renderPortletHeadContent()
        {
            return null;
        }

        /**
         * Gets title for kanban board
         * @return string
         */
        public function getTitle()
        {
            return $this->getKanbanBoardTitle();
        }

        /**
         * Render a toolbar above the form layout. This includes buttons and/or
         * links to go to different views or process actions such as save or delete
         * @param boolean $renderedInForm
         * @return A string containing the element's content.
         *
         */
        protected function renderActionElementBar($renderedInForm)
        {
            $kanbanActive = false;
            if ($this->params['relationModuleId'] == 'projects')
            {
                $kanbanActive = true;
            }
            else
            {
                $getData = GetUtil::getData();
                if (isset($getData['kanbanBoard']) && $getData['kanbanBoard'] == 1)
                {
                   $kanbanActive = true;
                }
            }

            if ($kanbanActive)
            {
               $content = parent::renderActionElementBar($renderedInForm);
            }
            return $content;
        }

        /**
         * @return string
         */
        protected function getGridViewWidgetPath()
        {
            return $this->getKanbanBoard()->getGridViewWidgetPath();
        }

        /**
         * @return string
         */
        protected function getCGridViewParams()
        {
            $params = parent::getCGridViewParams();
            $params = array_merge($params, $this->getKanbanBoard()->getGridViewParams());
            return array_merge($params, $this->resolveExtraParamsForKanbanBoard());
        }

        /**
         * Get grid id
         * @return string
         */
        protected function getGridId()
        {
            return $this->getRelationAttributeName() . '-tasks-kanban-view';
        }

        /**
         * Renders kanban view with action bars
         * @return string
         */
        protected function renderKanbanViewTitleWithActionBars()
        {
            $content                 = $this->renderTitleContent();
            $actionElementBarContent = $this->renderActionElementBar(false);
            $actionBarContent        = ZurmoHtml::tag('nav', array('class' => 'pillbox clearfix'),
                                                      $actionElementBarContent);
            $secondActionBarContent  = $this->renderSecondActionElementBar(false);
            $secondActionBarContent .= $this->resolveShouldRenderActionBarLinksForKanbanBoard();
            if ($secondActionBarContent != null)
            {
                $actionBarContent .= ZurmoHtml::tag('nav', array('class' => 'pillbox clearfix'), $secondActionBarContent);
            }
            $content .= ZurmoHtml::tag('div', array('class' => 'view-toolbar-container clearfix'), $actionBarContent);
            return $content;
        }

        protected function resolveShouldRenderActionBarLinksForKanbanBoard()
        {
            if ($this->shouldRenderActionBarLinksForKanbanBoard())
            {
                return ZurmoDefaultViewUtil::renderActionBarLinksForKanbanBoard($this->controllerId,
                    $this->params['relationModuleId'],
                    (int)$this->params['relationModel']->id,
                    true);
            }
        }

        protected function shouldRenderActionBarLinksForKanbanBoard()
        {
            return true;
        }

        /**
         * Modify the grid template for kanban view
         * @return string
         */
        protected static function getGridTemplate()
        {
            $preloader = '<div class="list-preloader"><span class="z-spinner"></span></div>';
            $items     = '<div class="items-wrapper">{items}</div>';
            return "{summary}" . $items . "{pager}" . $preloader;
        }

        public static function getDesignerRulesType()
        {
            return null;
        }

        /**
         * Register kanban grid script
         */
        protected function registerKanbanGridScript()
        {
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.kanbanBoard.widgets.assets')) . '/KanbanUtils.js');
            TasksUtil::registerTaskModalDetailsScript($this->getGridId());
            if ($this->dataProvider->getTotalItemCount() == 0)
            {
                $script  = "$('#" . $this->getGridId() . "').hide();";
                $script .= "$('#ZeroTasksForRelatedModelYetView').show();";
            }
            else
            {
                $script  = "$('#" . $this->getGridId() . "').show();";
                $script .= "$('#ZeroTasksForRelatedModelYetView').hide();";
            }
            Yii::app()->clientScript->registerScript('taskKanbanDetailScript', $script);
        }

        /**
         * Calling TaskKanbanBoardExtendedGridView::registerKanbanColumnSortableScript in order to reinitialize
         * the sorting for the card columns after the board is refreshed
         * @return string
         */
        protected function getCGridViewAfterAjaxUpdate()
        {
            // Begin Not Coding Standard
            return 'js:function(id, data) {
                        processAjaxSuccessError(id, data);
                        if($("#" + id).find(".kanban-card").length > 0)
                        {
                            $("#' . $this->getGridId() . '").show();
                            $("#ZeroTasksForRelatedModelYetView").hide();
                        }
                        else
                        {
                            $("#' . $this->getGridId() . '").hide();
                            $("#ZeroTasksForRelatedModelYetView").show();
                        }
                        $(this).makeLargeLoadingSpinner(false, ".ui-overlay-block");
                        $(".ui-overlay-block").fadeOut(50);
                        ' . TaskKanbanBoardExtendedGridView::registerKanbanColumnSortableScript() . '
                    }';
            // End Not Coding Standard
        }

        /**
         * Resolve configuration for data provider
         * @return array
         */
        protected function resolveConfigForDataProvider()
        {
            return array(
                            'pagination' => array(
                                'pageSize' => static::$defaultPageSize,
                        )
                    );
        }

        protected function renderUIOverLayBlock()
        {
            $spinner = ZurmoHtml::tag('span', array('class' => 'z-spinner'), '');
            return ZurmoHtml::tag('div', array('class' => 'ui-overlay-block'), $spinner);
        }

        /**
         * Override to take care of blocking kanban by overlay
         * @return string
         */
        protected function getCGridViewBeforeAjaxUpdate()
        {
            // Begin Not Coding Standard
            return 'js:function(id, options){
                            $(".ui-overlay-block").fadeIn(50);
                            $(this).makeLargeLoadingSpinner(true, ".ui-overlay-block");
                    }';
            // End Not Coding Standard
        }

        /**
         * Show the table on empty as we need the javascripts initialized when first task is created
         * @return boolean
         */
        protected function getShowTableOnEmpty()
        {
            return true;
        }

        /**
         * Renders search view.
         * @return string
         */
        protected function renderSearchView()
        {
            return Yii::app()->custom->renderKanbanSearchView($this->searchFormModel, $this->params);
        }
    }
?>