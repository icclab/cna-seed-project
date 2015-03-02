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

    class AccountContactAffiliationsDefaultController extends ZurmoModuleController
    {
        public function filters()
        {
            $modelClassName   = $this->getModule()->getPrimaryModelName();
            $viewClassName    = $modelClassName . 'EditAndDetailsView';
            return array_merge(parent::filters(),
                array(
                    array(
                        ZurmoBaseController::REQUIRED_ATTRIBUTES_FILTER_PATH . ' + create, createFromRelation, edit',
                        'moduleClassName' => get_class($this->getModule()),
                        'viewClassName'   => $viewClassName,
                   ),
                   array(
                        static::getRightsFilterPath(),
                        'moduleClassName' => 'AccountsModule',
                        'rightName'       => AccountsModule::getAccessRight(),
                   ),
                   array(
                        static::getRightsFilterPath(),
                        'moduleClassName' => 'ContactsModule',
                        'rightName'       => ContactsModule::getAccessRight(),
                   )
               )
            );
        }

        public function actionList()
        {
            throw new NotImplementedException();
        }

        public function actionDetails($id)
        {
            throw new NotImplementedException();
        }

        public function actionCreate()
        {
            $this->actionCreateByModel(new AccountContactAffiliation());
        }

        public function actionCreateFromRelation($relationAttributeName, $relationModelId, $relationModuleId, $redirectUrl)
        {
            $accountContactAffiliation = $this->resolveNewModelByRelationInformation( new AccountContactAffiliation(),
                                                                                     $relationAttributeName,
                                                                                     (int)$relationModelId,
                                                                                     $relationModuleId);
            $this->actionCreateByModel($accountContactAffiliation, $redirectUrl);
        }

        protected function actionCreateByModel(AccountContactAffiliation $accountContactAffiliation, $redirectUrl = null)
        {
            $titleBarAndEditView = $this->makeEditAndDetailsView(
                                            $this->attemptToSaveModelFromPost($accountContactAffiliation, $redirectUrl), 'Edit');
            $view = new AccountContactAffiliationsPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, $titleBarAndEditView));
            echo $view->render();
        }

        public function actionEdit($id, $redirectUrl = null)
        {
            $accountContactAffiliation = AccountContactAffiliation::getById(intval($id));
            $this->processEdit($accountContactAffiliation, $redirectUrl);
        }

        protected function processEdit(AccountContactAffiliation $accountContactAffiliation, $redirectUrl = null)
        {
            $view    = new AccountContactAffiliationsPageView(ZurmoDefaultViewUtil::
                            makeStandardViewForCurrentUser($this,
                            $this->makeEditAndDetailsView(
                                $this->attemptToSaveModelFromPost($accountContactAffiliation, $redirectUrl), 'Edit')));
            echo $view->render();
        }

        public function actionDelete($id)
        {
            $accountContactAffiliation = AccountContactAffiliation::getById(intval($id));
            $accountContactAffiliation->delete();
            //Do not redirect since there is no index view to go to. Also delete is called from related portlets only
        }

        public function actionAutoComplete($term, $autoCompleteOptions = null)
        {
            throw new NotImplementedException();
        }

        /**
         * Override since we do not want to automatically copy the account over
         * @param RedBeanModel $model
         * @param RedBeanModel $relatedModel
         */
        protected function addRelatedModelAccountToModel(RedBeanModel $model, RedBeanModel $relatedModel)
        {
        }
    }
?>
