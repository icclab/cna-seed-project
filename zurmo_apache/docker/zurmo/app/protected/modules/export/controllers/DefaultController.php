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

    class ExportDefaultController extends ZurmoModuleController
    {
        public function filters()
        {
            $filters   = array();
            $filters[] = array(
                ZurmoBaseController::RIGHTS_FILTER_PATH,
                'moduleClassName' => 'ExportModule',
                'rightName' => ExportModule::getAccessRight(),
            );
            return $filters;
        }

        public function actionDownload($id)
        {
            try
            {
                $exportItem = ExportItem::getById((int)$id);
                if ($exportItem instanceOf ExportItem)
                {
                    $fileModel = $exportItem->exportFileModel;
                    Yii::app()->request->sendFile($fileModel->name, $fileModel->fileContent->content, $fileModel->type, false);
                }
            }
            catch (Exception $e)
            {
                Yii::app()->user->setFlash('notification',
                    Zurmo::t('ExportModule', 'Export file you requested is not available anymore.')
                );
                $this->redirect(Yii::app()->createUrl('home/default/index'));
            }
        }

        /**
         * Lists export items.
         */
        public function actionList()
        {
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                              'listPageSize', get_class($this->getModule()));
            $exportItem                     = new ExportItem(false);
            $searchForm                     = new ExportSearchForm($exportItem);
            $listAttributesSelector         = new ListAttributesSelector('ExportListView', get_class($this->getModule()));
            $searchForm->setListAttributesSelector($listAttributesSelector);
            $dataProvider = $this->resolveSearchDataProvider(
                $searchForm,
                $pageSize,
                null,
                'ExportSearchView'
            );
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView(
                    $searchForm,
                    $dataProvider
                );
                $view = new ExportPageView($mixedView);
            }
            else
            {
                $mixedView = $this->makeSearchAndListView($searchForm, 'Export', $dataProvider);
                $breadCrumbLinks = array(
                                            Zurmo::t('ExportModule', 'Export')
                                        );
                $view = new ExportPageView(ZurmoDefaultAdminViewUtil::
                                            makeViewWithBreadcrumbsForCurrentUser($this, $mixedView, $breadCrumbLinks, 'SettingsBreadCrumbView'));
            }
            echo $view->render();
        }

        /**
         * Cancels export.
         * @param int $id
         */
        public function actionCancel($id)
        {
            $exportItem                 = ExportItem::getById(intval($id));
            $exportItem->cancelExport   = true;
            $exportItem->save();
            $this->redirect(Yii::app()->createUrl('export/default/list'));
        }
    }
?>
