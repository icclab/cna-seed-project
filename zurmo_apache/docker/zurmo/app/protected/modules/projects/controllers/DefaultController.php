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

    class ProjectsDefaultController extends ZurmoModuleController
    {
        const PROJECTS_MOBILE_CHECK_FILTER_PATH = 'application.modules.projects.controllers.filters.ProjectsMobileCheckControllerFilter';

        /**
         * Gets dashboard breadcrumb links
         * @return string
         */
        public static function getDashboardBreadcrumbLinks()
        {
            $title = Zurmo::t('ZurmoModule', 'Dashboard');
            return array($title);
        }

        /**
         * Gets listview breadcrumb links
         * @return string
         */
        public static function getListBreadcrumbLinks()
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            $title = Zurmo::t('ProjectsModule', 'ProjectsModulePluralLabel', $params);
            return array($title);
        }

        /**
         * @return array
         */
        public function filters()
        {
            $modelClassName             = $this->getModule()->getPrimaryModelName();
            $viewClassName              = $modelClassName . 'EditAndDetailsView';
            $zeroModelsYetViewClassName = 'ProjectsZeroModelsYetView';
            $pageViewClassName          = 'ProjectsPageView';
            return array_merge(parent::filters(),
                array(
                    array(
                        self::PROJECTS_MOBILE_CHECK_FILTER_PATH,
                   ),
                    array(
                        ZurmoBaseController::REQUIRED_ATTRIBUTES_FILTER_PATH . ' + create, createFromRelation, edit',
                        'moduleClassName' => get_class($this->getModule()),
                        'viewClassName'   => $viewClassName,
                   ),
                    array(
                        static::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                        'controller'                 => $this,
                        'zeroModelsYetViewClassName' => $zeroModelsYetViewClassName,
                        'modelClassName'             => $modelClassName,
                        'pageViewClassName'          => $pageViewClassName
                   ),
               )
            );
        }

        /**
         * List view for projects
         */
        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $project                        = new Project(false);
            $searchForm                     = new ProjectsSearchForm($project);
            $listAttributesSelector         = new ListAttributesSelector('ProjectsListView', get_class($this->getModule()));
            $searchForm->setListAttributesSelector($listAttributesSelector);
            $dataProvider                   = $this->resolveSearchDataProvider(
                                                    $searchForm,
                                                    $pageSize,
                                                    null,
                                                    'ProjectsSearchView'
                                                );
            $breadCrumbLinks                = static::getListBreadcrumbLinks();
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView  = $this->makeListView(
                            $searchForm,
                            $dataProvider
                        );
                $view       = new ProjectsPageView($mixedView);
            }
            else
            {
                $mixedView        = $this->makeActionBarSearchAndListView(
                                                    $searchForm,
                                                    $dataProvider,
                                                    'SecuredActionBarForProjectsSearchAndListView',
                                                    null,
                                                    'ProjectsListMenu');
                $view             = new ProjectsPageView(ProjectDefaultViewUtil::
                                                    makeViewWithBreadcrumbsForCurrentUser(
                                                        $this, $mixedView, $breadCrumbLinks, 'ProjectBreadCrumbView'));
            }
            echo $view->render();
        }

        /**
         * Details view for project
         * @param int $id
         */
        public function actionDetails($id)
        {
            $project            = static::getModelAndCatchNotFoundAndDisplayError('Project', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($project);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED,
                                      array(strval($project), 'ProjectsModule'), $project);
            $view = TasksUtil::resolveTaskKanbanViewForRelation($project, $this->getModule()->getId(), $this,
                                                                'TasksForProjectKanbanView', 'ProjectsPageView');
            echo $view->render();
        }

        /**
         * Create Project
         */
        public function actionCreate()
        {
            $params                 = LabelUtil::getTranslationParamsForAllModules();
            $title                  = Zurmo::t('ProjectsModule', 'Create ProjectsModuleSingularLabel', $params);
            $breadCrumbLinks        = array($title);
            $editAndDetailsView     = $this->makeEditAndDetailsView(
                                            $this->attemptToSaveModelFromPost(new Project()), 'Edit');
            $view                   = new ProjectsPageView(ProjectDefaultViewUtil::
                                                makeViewWithBreadcrumbsForCurrentUser(
                                                    $this, $editAndDetailsView, $breadCrumbLinks, 'ProjectBreadCrumbView'));
            echo $view->render();
        }

        /**
         * Edit Project
         */
        public function actionEdit($id, $redirectUrl = null)
        {
            $project         = Project::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($project);
            $breadCrumbLinks = array(StringUtil::getChoppedStringContent(strval($project), 25));
            $view            = new ProjectsPageView(ProjectDefaultViewUtil::
                                                        makeViewWithBreadcrumbsForCurrentUser($this,
                                                            $this->makeEditAndDetailsView(
                                                                $this->attemptToSaveModelFromPost(
                                                                    $project, $redirectUrl), 'Edit'), $breadCrumbLinks, 'ProjectBreadCrumbView'                                                   ));
            echo $view->render();
        }

        /**
         * Delete project
         * @param int $id
         */
        public function actionDelete($id)
        {
            $project = Project::GetById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($project);
            $project->delete();
            $this->redirect(array($this->getId() . '/index'));
        }

        /**
         * Gets search form class name
         * @return string
         */
        protected static function getSearchFormClassName()
        {
            return 'ProjectsSearchForm';
        }

        /**
         * Exports project data
         */
        public function actionExport()
        {
            $this->export('ProjectsSearchView');
        }

        /**
         * Copies the project
         * @param int $id
         */
        public function actionCopy($id, $redirectUrl = null)
        {
            $copyToProject      = new Project();
            $postVariableName   = get_class($copyToProject);
            $project            = Project::getById((int)$id);
            if (!isset($_POST[$postVariableName]))
            {
                ProjectZurmoCopyModelUtil::copy($project, $copyToProject);
                $this->processEdit($copyToProject);
            }
            else
            {
                $breadCrumbLinks = array(StringUtil::getChoppedStringContent(strval($project), 25));
                ProjectZurmoCopyModelUtil::processAfterCopy($project, $copyToProject);
                $view            = new ProjectsPageView(ProjectDefaultViewUtil::
                                                        makeViewWithBreadcrumbsForCurrentUser($this,
                                                            $this->makeEditAndDetailsView(
                                                                $this->attemptToSaveModelFromPost(
                                                                    $copyToProject, $redirectUrl), 'Edit'), $breadCrumbLinks, 'ProjectBreadCrumbView'));
                echo $view->render();
            }
        }

        /**
         * Process the editing of project
         * @param Project $project
         * @param string $redirectUrl
         */
        protected function processEdit(Project $project, $redirectUrl = null)
        {
            $view = new ProjectsPageView(ProjectDefaultViewUtil::
                            makeStandardViewForCurrentUser($this,
                            $this->makeEditAndDetailsView(
                                $this->attemptToSaveModelFromPost($project, $redirectUrl), 'Edit')));
            echo $view->render();
        }

        /**
         * Action for displaying a mass edit form and also action when that form is first submitted.
         * When the form is submitted, in the event that the quantity of models to update is greater
         * than the pageSize, then once the pageSize quantity has been reached, the user will be
         * redirected to the makeMassEditProgressView.
         * In the mass edit progress view, a javascript refresh will take place that will call a refresh
         * action, usually massEditProgressSave.
         * If there is no need for a progress view, then a flash message will be added and the user will
         * be redirected to the list view for the model.  A flash message will appear providing information
         * on the updated records.
         * @see Controler->makeMassEditProgressView
         * @see Controller->processMassEdit
         * @see
         */
        public function actionMassEdit()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'massEditProgressPageSize');
            $project = new Project(false);
            $activeAttributes = $this->resolveActiveAttributesFromMassEditPost();
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new ProjectsSearchForm($project),
                $pageSize,
                Yii::app()->user->userModel->id,
                null,
                'ProjectsSearchView');
            $selectedRecordCount = static::getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $project = $this->processMassEdit(
                $pageSize,
                $activeAttributes,
                $selectedRecordCount,
                'ProjectsPageView',
                $project,
                ProjectsModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
            $massEditView = $this->makeMassEditView(
                $project,
                $activeAttributes,
                $selectedRecordCount,
                ProjectsModule::getModuleLabelByTypeAndLanguage('Plural')
            );
            $view = new ProjectsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $massEditView));
            echo $view->render();
        }

        /**
         * Action called in the event that the mass edit quantity is larger than the pageSize.
         * This action is called after the pageSize quantity has been updated and continues to be
         * called until the mass edit action is complete.  For example, if there are 20 records to update
         * and the pageSize is 5, then this action will be called 3 times.  The first 5 are updated when
         * the actionMassEdit is called upon the initial form submission.
         */
        public function actionMassEditProgressSave()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'massEditProgressPageSize');
            $project = new Project(false);
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new ProjectsSearchForm($project),
                $pageSize,
                Yii::app()->user->userModel->id,
                null,
                'ProjectsSearchView'
            );
            $this->processMassEditProgressSave(
                'Project',
                $pageSize,
                ProjectsModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
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
            $project = new Project(false);

            $activeAttributes = $this->resolveActiveAttributesFromMassDeletePost();
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new ProjectsSearchForm($project),
                $pageSize,
                Yii::app()->user->userModel->id,
                null,
                'ProjectsSearchView');
            $selectedRecordCount = static::getSelectedRecordCountByResolvingSelectAllFromGet($dataProvider);
            $project = $this->processMassDelete(
                $pageSize,
                $activeAttributes,
                $selectedRecordCount,
                'ProjectsPageView',
                $project,
                ProjectsModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
            $massDeleteView = $this->makeMassDeleteView(
                $project,
                $activeAttributes,
                $selectedRecordCount,
                ProjectsModule::getModuleLabelByTypeAndLanguage('Plural')
            );
            $view = new ProjectsPageView(ProjectDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $massDeleteView));
            echo $view->render();
        }

        /**
         * Action called in the event that the mass delete quantity is larger than the pageSize.
         * This action is called after the pageSize quantity has been delted and continues to be
         * called until the mass delete action is complete.  For example, if there are 20 records to delete
         * and the pageSize is 5, then this action will be called 3 times.  The first 5 are updated when
         * the actionMassDelete is called upon the initial form submission.
         */
        public function actionMassDeleteProgress()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'massDeleteProgressPageSize');
            $project = new Project(false);
            $dataProvider = $this->getDataProviderByResolvingSelectAllFromGet(
                new ProjectsSearchForm($project),
                $pageSize,
                Yii::app()->user->userModel->id,
                null,
                'ProjectsSearchView'
            );
            $this->processMassDeleteProgress(
                'Project',
                $pageSize,
                ProjectsModule::getModuleLabelByTypeAndLanguage('Plural'),
                $dataProvider
            );
        }

        /**
         * Project Modal List Field
         */
        public function actionModalList()
        {
            $modalListLinkProvider = new SelectFromRelatedEditModalListLinkProvider(
                                            $_GET['modalTransferInformation']['sourceIdFieldId'],
                                            $_GET['modalTransferInformation']['sourceNameFieldId'],
                                            $_GET['modalTransferInformation']['modalId']
            );
            echo ModalSearchListControllerUtil::
                 setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider);
        }

        /**
         * Render autocomplete options of accounts for projects
         * @param string $term
         */
        public function actionAutoCompleteAllAccountsForMultiSelectAutoComplete($term)
        {
            $this->processAutoCompleteOptionsForRelations('Account', $term);
        }

        /**
         * Render autocomplete options of opportunities for projects
         * @param string $term
         */
        public function actionAutoCompleteAllOpportunitiesForMultiSelectAutoComplete($term)
        {
            $this->processAutoCompleteOptionsForRelations('Opportunity', $term);
        }

        /**
         * Process auto complete options for relations
         * @param string $relatedModelClassName
         * @param string $term
         */
        protected function processAutoCompleteOptionsForRelations($relatedModelClassName, $term)
        {
            $pageSize     = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'autoCompleteListPageSize', get_class($this->getModule()));
            $adapterName  = null;
            $projectRelations      = self::getProjectRelationsByPartialName($relatedModelClassName, $term, $pageSize, $adapterName);
            $autoCompleteResults    = array();
            foreach ($projectRelations as $projectRelation)
            {
                $autoCompleteResults[] = array(
                    'id'   => $projectRelation->id,
                    'name' => self::renderHtmlContentLabelFromRelationAndKeyword($projectRelation, $term)
                );
            }
            echo CJSON::encode($autoCompleteResults);
        }

        /**
         * @param string $partialName
         * @param int $pageSize
         * @param null|string $stateMetadataAdapterClassName
         */
        public static function getProjectRelationsByPartialName($className, $partialName, $pageSize, $stateMetadataAdapterClassName = null)
        {
            assert('is_string($partialName)');
            assert('is_int($pageSize)');
            assert('$stateMetadataAdapterClassName == null || is_string($stateMetadataAdapterClassName)');
            $joinTablesAdapter  = new RedBeanModelJoinTablesQueryAdapter($className);
            $metadata           = array('clauses' => array(), 'structure' => '');
            if ($stateMetadataAdapterClassName != null)
            {
                $stateMetadataAdapter   = new $stateMetadataAdapterClassName($metadata);
                $metadata               = $stateMetadataAdapter->getAdaptedDataProviderMetadata();
                $metadata['structure']  = '(' . $metadata['structure'] . ')';
            }
            $where  = RedBeanModelDataProvider::makeWhere($className, $metadata, $joinTablesAdapter);
            if ($where != null)
            {
                $where .= 'and';
            }
            $where .= self::getWherePartForPartialNameSearchByPartialName(lcfirst($className), $partialName);
            return $className::getSubset($joinTablesAdapter, null, $pageSize, $where, lcfirst($className) . ".name");
        }

        /**
         * @param string $partialName
         * @return string
         */
        protected static function getWherePartForPartialNameSearchByPartialName($tableName, $partialName)
        {
            assert('is_string($partialName)');
            return "   ($tableName.name  like '$partialName%') ";
        }

        /**
         * @param RelatedModel Account, Contact or Opportunity
         * @param string $keyword
         * @return string
         */
        public static function renderHtmlContentLabelFromRelationAndKeyword($relatedModel, $keyword)
        {
            assert('($relatedModel instanceof Account || $relatedModel instanceof Opportunity) && $relatedModel->id > 0');
            assert('$keyword == null || is_string($keyword)');

            if ($relatedModel->name != null)
            {
                return strval($relatedModel) . '&#160&#160<b>'. '</b>';
            }
            else
            {
                return strval($relatedModel);
            }
        }

        /**
         * @return ProjectZurmoControllerUtil
         */
        protected static function getZurmoControllerUtil()
        {
            return new ProjectZurmoControllerUtil('projectItems', 'ProjectItemForm');
        }

        /**
         * Create a project from a relation for example, on accounts details and relations view
         * @param string $relationAttributeName
         * @param string $relationModelId
         * @param string $relationModuleId
         * @param string $redirectUrl
         */
        public function actionCreateFromRelation($relationAttributeName, $relationModelId, $relationModuleId, $redirectUrl)
        {
            $project = $this->resolveNewModelByRelationInformation( new Project(),
                                                                                $relationAttributeName,
                                                                                (int)$relationModelId,
                                                                                $relationModuleId);
            $this->actionCreateByModel($project, $redirectUrl);
        }

        /**
         * Creates by modal
         * @param Project $project
         * @param string $redirectUrl
         */
        protected function actionCreateByModel(Project $project, $redirectUrl = null)
        {
            $titleBarAndEditView    = $this->makeEditAndDetailsView(
                                                $this->attemptToSaveModelFromPost($project, $redirectUrl), 'Edit');
            $view                   = new ProjectsPageView(ZurmoDefaultViewUtil::
                                                                makeStandardViewForCurrentUser($this, $titleBarAndEditView));
            echo $view->render();
        }

        /**
         * Display projects dashboard
         */
        public function actionDashboardDetails()
        {
            $params = array(
                'controllerId' => $this->getId(),
                'moduleId'     => $this->getModule()->getId(),
            );
            $gridViewId              = 'notUsed';
            $pageVar                 = 'notUsed';
            $introView               = new ProjectsDashboardIntroView(get_class($this->getModule()));
            $actionBarView           = new SecuredActionBarForProjectsDashboardView(
                                            'default',
                                            'projects',
                                            new Project(), //Just to fill in a model
                                            $gridViewId,
                                            $pageVar,
                                            false,
                                            'ProjectsDashboardMenu',
                                            $introView);
            $projectsDashboardView  = new ProjectsDashboardView(
                                            $this->getId(),
                                            $this->getModule()->getId(),
                                            'ProjectsDashboard',
                                            $params);
            $projectsDashboardView->setCssClasses( array( 'clearfix' ) );

            $gridView                = new GridView(2, 1);
            $gridView->setView($actionBarView, 0, 0);
            $gridView->setView($projectsDashboardView, 1, 0);
            $breadCrumbLinks         = static::getDashboardBreadcrumbLinks();
            $view                    = new ProjectsPageView(ProjectDefaultViewUtil::
                                                                       makeViewWithBreadcrumbsForCurrentUser(
                                                                            $this,
                                                                            $gridView,
                                                                            $breadCrumbLinks,
                                                                            'ProjectBreadCrumbView'));
            echo $view->render();
        }

        /**
         * Display list view of feeds for projects on dashboard
         */
        public function actionShowProjectsLatestActivityFeed()
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType('dashboardListPageSize');
            $listView = ProjectZurmoControllerUtil::getProjectsLatestActivityFeedView($this, $pageSize);
            echo $listView->render();
        }
    }
?>