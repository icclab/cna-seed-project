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
     * Default controller for ByTimeWorkflowInQueue actions
      */
    class WorkflowsDefaultTimeQueueController extends ZurmoBaseController
    {
        const ZERO_MODELS_CHECK_FILTER_PATH = 'application.modules.workflows.controllers.filters.WorkflowZeroModelsCheckControllerFilter';

        public static function getListBreadcrumbLinks()
        {
            $title = Zurmo::t('WorkflowsModule', 'Time Queue');
            return array($title);
        }

        public function filters()
        {
            return array_merge(parent::filters(),
                array(
                    array(
                        static::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                        'controller' => $this,
                        'activeActionElementType' => 'ByTimeWorkflowInQueuesMenu',
                        'breadCrumbLinks'         => static::getListBreadcrumbLinks(),
                    ),
                )
            );
        }

        public function actionIndex()
        {
            $this->actionList();
        }

        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $activeActionElementType        = 'ByTimeWorkflowInQueuesMenu';
            $model                          = new ByTimeWorkflowInQueue(false);
            $searchForm                     = new ByTimeWorkflowInQueuesSearchForm($model);
            $dataProvider                   = $this->resolveSearchDataProvider($searchForm, $pageSize, null,
                                              'ByTimeWorkflowInQueuesSearchView');
            $breadCrumbLinks                = static::getListBreadcrumbLinks();
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView(
                    $searchForm,
                    $dataProvider,
                    'ByTimeWorkflowInQueuesListView'
                );
                $view = new WorkflowsPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeActionBarSearchAndListView($searchForm, $dataProvider,
                             'SecuredActionBarForWorkflowsSearchAndListView', 'ByTimeWorkflowInQueues', $activeActionElementType);
                $view = new WorkflowsPageView(ZurmoDefaultAdminViewUtil::
                                              makeViewWithBreadcrumbsForCurrentUser(
                                              $this, $mixedView, $breadCrumbLinks, 'WorkflowBreadCrumbView'));
            }
            echo $view->render();
        }

                /**
         * Action for displaying a mass delete form and also action when that form is first submitted.
         * When the form is submitted, in the event that the quantity of models to delete is greater
         * than the pageSize, then once the pageSize quantity has been reached, the user will be
         * redirected to the makeMassDeleteProgressView.
         * In the mass delete progress view, a javascript refresh will take place that will call a refresh
         * action, usually makeMassDeleteProgressView.
         * If there is no need for a progress view, then a flash message will be added and the user will
         * be redirected to the list view for the model.  A flash message will appear providing information
         * on the delete records.
         * @see Controller->makeMassDeleteProgressView
         * @see Controller->processMassDelete
         * @see
         */
        public function actionMassDelete()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'massDeleteProgressPageSize');
            $queueItem = new ByTimeWorkflowInQueue(false);

            $activeAttributes = $this->resolveActiveAttributesFromMassDeletePost();
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new ByTimeWorkflowInQueuesSearchForm($queueItem),
                $pageSize,
                Yii::app()->user->userModel->id,
                null,
                'ByTimeWorkflowInQueuesSearchView');
            $selectedRecordCount = static::getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $queueItem = $this->processMassDelete(
                $pageSize,
                $activeAttributes,
                $selectedRecordCount,
                'WorkflowsPageView',
                $queueItem,
                Zurmo::t('WorkflowsModule', 'Time Queue'),
                $dataProvider,
                array($this->getId() . '/list')
            );
            $massDeleteView = $this->makeMassDeleteView(
                $queueItem,
                $activeAttributes,
                $selectedRecordCount,
                Zurmo::t('WorkflowsModule', 'Time Queue'),
                'MassDeleteView',
                false
            );
            $view = new WorkflowsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $massDeleteView));
            echo $view->render();
        }

        /**
         * Action called in the event that the mass delete quantity is larger than the pageSize.
         * This action is called after the pageSize quantity has been deleted and continues to be
         * called until the mass delete action is complete.  For example, if there are 20 records to delete
         * and the pageSize is 5, then this action will be called 3 times.  The first 5 are updated when
         * the actionMassDelete is called upon the initial form submission.
         */
        public function actionMassDeleteProgress()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'massDeleteProgressPageSize');
            $queueItem = new ByTimeWorkflowInQueue(false);
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new ByTimeWorkflowInQueuesSearchForm($queueItem),
                $pageSize,
                Yii::app()->user->userModel->id,
                null,
                'ByTimeWorkflowInQueuesSearchView'
            );
            $this->processMassDeleteProgress(
                'ByTimeWorkflowInQueue',
                $pageSize,
                Zurmo::t('WorkflowsModule', 'Time Queue'),
                $dataProvider
            );
        }
    }
?>
