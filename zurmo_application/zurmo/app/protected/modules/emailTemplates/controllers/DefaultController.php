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

    class EmailTemplatesDefaultController extends ZurmoModuleController
    {
        const ZERO_MODELS_FOR_WORKFLOW_CHECK_FILTER_PATH =
            'application.modules.emailTemplates.controllers.filters.EmailTemplatesForWorkflowZeroModelsCheckControllerFilter';

        const ZERO_MODELS_FOR_CONTACT_CHECK_FILTER_PATH =
            'application.modules.emailTemplates.controllers.filters.EmailTemplatesForMarketingZeroModelsCheckControllerFilter';

        public static function getListBreadcrumbLinks()
        {
            $title = Zurmo::t('Core', 'Templates');
            return array($title);
        }

        public static function getDetailsAndEditForWorkflowBreadcrumbLinks()
        {
            return array(Zurmo::t('Core', 'Templates') => array('default/listForWorkflow'));
        }

        public static function getDetailsAndEditForMarketingBreadcrumbLinks()
        {
            return array(Zurmo::t('Core', 'Templates') => array('default/listForMarketing'));
        }

        public function filters()
        {
            return array_merge(parent::filters(),
                array(
                    array(
                        static::ZERO_MODELS_FOR_CONTACT_CHECK_FILTER_PATH . ' + listForMarketing, index',
                        'controller'                    => $this,
                        'activeActionElementType'       => EmailTemplatesForMarketingMenuActionElement::getType(),
                        'breadCrumbLinks'               => static::getListBreadcrumbLinks(),
                        'stateMetadataAdapterClassName' => 'EmailTemplatesForMarketingStateMetadataAdapter'
                    ),
                    array(
                        static::ZERO_MODELS_FOR_WORKFLOW_CHECK_FILTER_PATH . ' + listForWorkflow',
                        'controller'                    => $this,
                        'activeActionElementType'       => EmailTemplatesForWorkflowMenuActionElement::getType(),
                        'breadCrumbLinks'               => static::getListBreadcrumbLinks(),
                        'stateMetadataAdapterClassName' => 'EmailTemplatesForWorkflowStateMetadataAdapter'
                    ),
                )
            );
        }

        public function actionIndex()
        {
            $this->actionListForMarketing();
        }

        public function actionListForMarketing()
        {
            $this->actionListByType(EmailTemplate::TYPE_CONTACT);
        }

        public function actionListForWorkflow()
        {
            $this->actionListByType(EmailTemplate::TYPE_WORKFLOW);
        }

        protected function actionListByType($type)
        {
            assert('is_int($type) || is_string($type)');
            $type               = intval($type);
            $pageSize                       = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                                                        'listPageSize', get_class($this->getModule()));
            $emailTemplate                  = new EmailTemplate(false);
            $emailSearchFormClassName       = static::getSearchFormClassName();
            $searchForm                     = new $emailSearchFormClassName($emailTemplate);
            $stateMetadataAdapter           = static::getStateMetadataAdapterByType($type);
            $dataProvider                   = $this->resolveSearchDataProvider($searchForm, $pageSize,
                                                                                $stateMetadataAdapter,
                                                                                'EmailTemplatesSearchView');
            if (isset($_GET['ajax']) && $_GET['ajax'] == 'list-view')
            {
                $mixedView = $this->makeListView($searchForm, $dataProvider);
                $view = new EmailTemplatesPageView($mixedView);
            }
            else
            {
                $activeActionElementType        = static::getMenuActionElementTypeByType($type);
                $breadCrumbLinks                = static::getListBreadcrumbLinks();
                $breadCrumbsView                = static::getBreadCrumbViewByType($type);
                $viewUtil                       = static::getViewUtilByType($type);
                $actionBar                      = static::getActionBarByType($type);
                $mixedView                      = $this->makeActionBarSearchAndListView($searchForm, $dataProvider,
                                                                                        $actionBar, null,
                                                                                        $activeActionElementType);
                $view                           = new EmailTemplatesPageView($viewUtil::
                                                                                makeViewWithBreadcrumbsForCurrentUser(
                                                                                                    $this,
                                                                                                    $mixedView,
                                                                                                    $breadCrumbLinks,
                                                                                                    $breadCrumbsView));
            }
            echo $view->render();
        }

        public function actionSelectBuiltType($type)
        {
            assert('is_int($type) || is_string($type)');
            $type               = intval($type);
            $viewUtil           = static::getViewUtilByType($type);
            $breadCrumbView     = static::getBreadCrumbViewByType($type);
            $breadCrumbLinks    = static::getBreadCrumbLinksByType($type);
            $breadCrumbLinks[]  = Zurmo::t('EmailTemplatesModule', 'Select Email Template Type');
            $view               = new EmailTemplatesPageView($viewUtil::makeViewWithBreadcrumbsForCurrentUser(
                                                                                    $this,
                                                                                    new EmailTemplateWizardTypesGridView(),
                                                                                    $breadCrumbLinks,
                                                                                    $breadCrumbView));
            echo $view->render();
        }

        public function actionCreate($type, $builtType = null)
        {
            assert('is_int($type) || is_string($type)');
            $type                       = intval($type);
            if ($builtType == null)
            {
                $this->actionSelectBuiltType($type);
                Yii::app()->end(0, false);
            }
            assert('is_int($builtType) || is_string($builtType)');
            $breadCrumbLink             = null;
            $builtType                  = intval($builtType);
            $emailTemplate              = new EmailTemplate();
            $emailTemplate->type        = $type;
            $emailTemplate->builtType   = $builtType;
            if ($emailTemplate->isWorkflowTemplate())
            {
                $emailTemplate->modelClassName = 'Account';
            }
            $breadCrumbLink             = Zurmo::t('Core', 'Create');
            if ($emailTemplate->isPlainTextTemplate()|| $emailTemplate->isPastedHtmlTemplate())
            {
                $emailTemplate->isDraft     = false;
            }
            $this->actionRenderWizardForModel($emailTemplate, $breadCrumbLink);
        }

        public function actionEdit($id) // , $redirectUrl = null
        {
            $emailTemplate      = static::getModelAndCatchNotFoundAndDisplayError('EmailTemplate', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($emailTemplate);
            $breadCrumbLink     = StringUtil::getChoppedStringContent(strval($emailTemplate), 25);
            $this->actionRenderWizardForModel($emailTemplate, $breadCrumbLink);
        }

        protected function actionRenderWizardForModel(EmailTemplate $emailTemplate, $breadCrumbsLink)
        {
            $viewUtil                   = static::getViewUtilByType($emailTemplate->type);
            $breadCrumbView             = static::getBreadCrumbViewByType($emailTemplate->type);
            $breadCrumbLinks            = static::getBreadCrumbLinksByType($emailTemplate->type);
            $breadCrumbLinks[]          = $breadCrumbsLink;
            $progressBarAndStepsView    = EmailTemplateWizardViewFactory::makeStepsAndProgressBarViewFromEmailTemplate($emailTemplate);
            $wizardView                 = EmailTemplateWizardViewFactory::makeViewFromEmailTemplate($emailTemplate);
            $view                       = new EmailTemplatesPageView($viewUtil::makeTwoViewsWithBreadcrumbsForCurrentUser(
                                                                        $this,
                                                                        $progressBarAndStepsView,
                                                                        $wizardView,
                                                                        $breadCrumbLinks,
                                                                        $breadCrumbView));
            echo $view->render();
        }

        public function actionSave($builtType)
        {
            $postData                   = PostUtil::getData();
            $emailTemplate              = null;
            $this->resolveEmailTemplateByPostData($postData, $emailTemplate, $builtType);

            $emailTemplateToWizardFormAdapter   = new EmailTemplateToWizardFormAdapter($emailTemplate);
            $model                              =  $emailTemplateToWizardFormAdapter->makeFormByBuiltType();
            if (isset($postData['ajax']) && $postData['ajax'] === 'edit-form')
            {
                $this->actionValidate($postData, $model);
            }
            $unmuteScoring = false;
            if ($emailTemplate->isBuilderTemplate() && ($emailTemplate->isDraft || !isset($emailTemplate->isDraft)))
            {
                Yii::app()->gameHelper->muteScoringModelsOnSave();
                $unmuteScoring = true;
            }
            $explicitReadWriteModelPermissions = ExplicitReadWriteModelPermissionsUtil::
                                                            resolveByPostDataAndModelThenMake($postData[get_class($model)],
                                                                                                $emailTemplate);
            if ($emailTemplate->save())
            {
                if ($unmuteScoring)
                {
                    Yii::app()->gameHelper->unmuteScoringModelsOnSave();
                }
                if ($explicitReadWriteModelPermissions != null)
                {
                    ExplicitReadWriteModelPermissionsUtil::resolveExplicitReadWriteModelPermissions($emailTemplate,
                                                                                    $explicitReadWriteModelPermissions);
                }
                $modelClassName  = $emailTemplate->modelClassName;
                $moduleClassName = $modelClassName::getModuleClassName();
                echo CJSON::encode(array('id'              => $emailTemplate->id,
                                         'redirectToList'  => false,
                                         'moduleClassName' => $moduleClassName));
                Yii::app()->end(0, false);
            }
            else
            {
                throw new FailedToSaveModelException();
            }
        }

        public function actionDetails($id)
        {
            $emailTemplate = static::getModelAndCatchNotFoundAndDisplayError('EmailTemplate', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($emailTemplate);
            AuditEvent::logAuditEvent('ZurmoModule', ZurmoModule::AUDIT_EVENT_ITEM_VIEWED, array(strval($emailTemplate),
                                                                                'EmailTemplatesModule'), $emailTemplate);
            $detailsView                = new EmailTemplateDetailsView($this->getId(), $this->getModule()->getId(),
                                                                        $emailTemplate, strval($emailTemplate));
            $viewUtil                   = static::getViewUtilByType($emailTemplate->type);
            $breadCrumbView             = static::getBreadCrumbViewByType($emailTemplate->type);
            $breadCrumbLinks            = static::getBreadCrumbLinksByType($emailTemplate->type);
            $breadCrumbLinks[]          = StringUtil::getChoppedStringContent(strval($emailTemplate), 25);
            $view                       = new EmailTemplatesPageView($viewUtil::makeViewWithBreadcrumbsForCurrentUser(
                                                                                                    $this,
                                                                                                    $detailsView,
                                                                                                    $breadCrumbLinks,
                                                                                                    $breadCrumbView));
            echo $view->render();
        }

        public function actionDetailsJson($id, $includeFilesInJson = false, $contactId = null)
        {
            $contactId     = (int) $contactId;
            $emailTemplate = static::getModelAndCatchNotFoundAndDisplayError('EmailTemplate', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($emailTemplate);
            header('Content-type: application/json');
            if ($contactId != null)
            {
                $contact        = Contact::getById($contactId);
                $textContent    = $emailTemplate->textContent;
                $htmlContent    = $emailTemplate->htmlContent;
                GlobalMarketingFooterUtil::removeFooterMergeTags($textContent);
                GlobalMarketingFooterUtil::removeFooterMergeTags($htmlContent);
                // we have already stripped off the merge tags that could introduce problems,
                // no need to send actual data for personId, modelId, modelType and marketingListId.
                AutoresponderAndCampaignItemsUtil::resolveContentsForMergeTags($textContent, $htmlContent, $contact,
                                                                                null, null, null, null);
                $emailTemplate->setTreatCurrentUserAsOwnerForPermissions(true);
                $emailTemplate->textContent = stripslashes($textContent);
                $emailTemplate->htmlContent = stripslashes($htmlContent);
                $emailTemplate->setTreatCurrentUserAsOwnerForPermissions(false);
            }
            $emailTemplate = $this->resolveEmailTemplateAsJson($emailTemplate, $includeFilesInJson);
            echo $emailTemplate;
            Yii::app()->end(0, false);
        }

        protected function resolveEmailTemplateAsJson(EmailTemplate $emailTemplate, $includeFilesInJson)
        {
            $emailTemplateDataUtil          = new ModelToArrayAdapter($emailTemplate);
            $emailTemplateData              = $emailTemplateDataUtil->getData();
            if ($includeFilesInJson)
            {
                $emailTemplateData['filesIds']  = array();
                foreach ($emailTemplate->files as $file)
                {
                    $emailTemplateData['filesIds'][] = $file->id;
                }
            }
            if ($emailTemplate->builtType == EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE)
            {
                unset($emailTemplateData['serializedData']);
            }
            $emailTemplateJson = CJSON::encode($emailTemplateData);
            return $emailTemplateJson;
        }

        protected static function getSearchFormClassName()
        {
            return 'EmailTemplatesSearchForm';
        }

        public function actionDelete($id)
        {
            $emailTemplate      = static::getModelAndCatchNotFoundAndDisplayError('EmailTemplate', intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($emailTemplate);
            $redirectUrl        = null;
            if ($emailTemplate->isWorkflowTemplate())
            {
                $redirectUrl = $this->getId() . '/listForWorkflow';
            }
            elseif ($emailTemplate->isContactTemplate())
            {
                $redirectUrl        = $this->getId() . '/listForMarketing';
            }
            $emailTemplate->delete();

            if (isset($redirectUrl))
            {
                $this->redirect(array($redirectUrl));
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public function actionMergeTagGuide()
        {
            Yii::app()->getClientScript()->setToAjaxMode();
            $view = new ModalView($this, new MergeTagGuideView());
            echo $view->render();
        }

        public function actionGetHtmlContent($id, $className)
        {
            assert('is_string($className)');
            $modelId = (int) $id;
            $model = $className::getById($modelId);
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($model);
            echo $model->htmlContent;
        }

        public function actionGetSerializedToHtmlContent($id)
        {
            $modelId = (int) $id;
            $model = EmailTemplate::getById($modelId);
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($model);
            echo EmailTemplateSerializedDataToHtmlUtil::resolveHtmlBySerializedData($model->serializedData, false);
        }

        /**
         * @param null $uniqueId
         * @param null $nodeId
         * @param string $modelClassName
         */
        public function actionRelationsAndAttributesTreeForMergeTags($uniqueId = null, $nodeId = null, $modelClassName = 'Contact')
        {
            if ($modelClassName == null)
            {
                $modelClassName = 'Contact';
            }
            $moduleClassName = $modelClassName::getModuleClassName();
            $type     = Report::TYPE_ROWS_AND_COLUMNS;
            $treeType = ComponentForReportForm::TYPE_FILTERS;
            $report   = new Report();
            $report->setModuleClassName($moduleClassName);
            $report->setType($type);
            if ($nodeId != null)
            {
                $reportToTreeAdapter = new MergeTagsReportRelationsAndAttributesToTreeAdapter($report, $treeType, $uniqueId);
                echo ZurmoTreeView::saveDataAsJson($reportToTreeAdapter->getData($nodeId));
                Yii::app()->end(0, false);
            }
            $view        = new ReportRelationsAndAttributesForMergeTagsTreeView($type, $treeType, 'edit-form', $uniqueId);
            $content     = $view->render();
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        protected static function getZurmoControllerUtil()
        {
            return new EmailTemplateZurmoControllerUtil();
        }

        protected static function getBreadCrumbViewByType($type)
        {
            $breadCrumbView   = 'MarketingBreadCrumbView';
            if ($type == EmailTemplate::TYPE_WORKFLOW)
            {
                $breadCrumbView = 'WorkflowBreadCrumbView';
            }
            return $breadCrumbView;
        }

        protected static function getViewUtilByType($type)
        {
            $viewUtil = 'MarketingDefaultViewUtil';
            if ($type == EmailTemplate::TYPE_WORKFLOW)
            {
                $viewUtil = 'WorkflowDefaultAdminViewUtil';
            }
            return $viewUtil;
        }

        protected static function getStateMetadataAdapterByType($type)
        {
            $adapterClass   = 'EmailTemplatesForMarketingStateMetadataAdapter';
            if ($type == EmailTemplate::TYPE_WORKFLOW)
            {
                $adapterClass   = 'EmailTemplatesForWorkflowStateMetadataAdapter';
            }
            return $adapterClass;
        }

        protected static function getActionBarByType($type)
        {
            $actionBar  = 'SecuredActionBarForMarketingListsSearchAndListView';
            if ($type == EmailTemplate::TYPE_WORKFLOW)
            {
                $actionBar  = 'SecuredActionBarForWorkflowsSearchAndListView';
            }
            return $actionBar;
        }

        protected static function getMenuActionElementTypeByType($type)
        {
            $menuActionElement  = 'EmailTemplatesForMarketingMenuActionElement';
            if ($type == EmailTemplate::TYPE_WORKFLOW)
            {
                $menuActionElement  = 'EmailTemplatesForWorkflowMenuActionElement';
            }
            $menuActionElementType  = $menuActionElement::getType();
            return $menuActionElementType;
        }

        protected static function getBreadCrumbLinksByType($type)
        {
            $breadCrumbLinks    = static::getDetailsAndEditForMarketingBreadcrumbLinks();
            if ($type == EmailTemplate::TYPE_WORKFLOW)
            {
                $breadCrumbLinks    = static::getDetailsAndEditForWorkflowBreadcrumbLinks();
            }
            return $breadCrumbLinks;
        }

        protected function resolveEmailTemplateByPostData(array $postData, & $emailTemplate, $builtType)
        {
            $formName   = EmailTemplateToWizardFormAdapter::getFormClassNameByBuiltType($builtType);
            $formData   = ArrayUtil::getArrayValue($postData, $formName);
            if (!is_array($formData))
            {
                Yii::app()->end(0, false);
            }
            $id         = intval(ArrayUtil::getArrayValue($formData, GeneralDataForEmailTemplateWizardView::HIDDEN_ID));
            if ($id <= 0)
            {
                $this->resolveCanCurrentUserAccessEmailTemplates();
                $emailTemplate               = new EmailTemplate();
                // this is just here for: testSaveInvalidDataWithoutValidationScenario()
                $emailTemplate->builtType    = $builtType;
            }
            else
            {
                $emailTemplate              = EmailTemplate::getById(intval($id));
            }
            DataToEmailTemplateUtil::resolveEmailTemplateByWizardPostData($emailTemplate, $postData,
                EmailTemplateToWizardFormAdapter::getFormClassNameByBuiltType($builtType));
        }

        protected function resolveCanCurrentUserAccessEmailTemplates()
        {
            if (!RightsUtil::doesUserHaveAllowByRightName('EmailTemplatesModule',
                                                            EmailTemplatesModule::RIGHT_CREATE_EMAIL_TEMPLATES,
                                                            Yii::app()->user->userModel))
            {
                $messageView = new AccessFailureView();
                $view        = new AccessFailurePageView($messageView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
            return true;
        }

        protected function actionValidate($postData, EmailTemplateWizardForm $model)
        {
            if (isset($postData['validationScenario']) && $postData['validationScenario'] != null)
            {
                $model->setScenario($postData['validationScenario']);
            }
            else
            {
                throw new NotSupportedException();
            }
            $errorData = array();
            $validated = $model->validate();
            if ($validated === false)
            {
                foreach ($model->getErrors() as $attribute => $errors)
                {
                    $errorData[ZurmoHtml::activeId($model, $attribute)] = $errors;
                }
            }
            echo CJSON::encode($errorData);
            Yii::app()->end(0, false);
        }

        public function actionRenderCanvas($id = null)
        {
            Yii::app()->clientScript->setToAjaxMode();
            // it would be empty for the first time during create so we just end the request here.
            if (empty($id))
            {
                Yii::app()->end(0, false);
            }
            assert('is_int($id) || is_string($id)');
            $content = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateId($id, true);
            Yii::app()->clientScript->render($content);
            echo $content;
        }

        public function actionRenderPreview($id = null, $useHtmlContent = 1)
        {
            Yii::app()->clientScript->setToAjaxMode();
            if (isset($id))
            {
                $emailTemplate  = EmailTemplate::getById(intval($id));
                ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($emailTemplate);
                $content        = $emailTemplate->htmlContent;
                if (!$useHtmlContent || empty($content))
                {
                    $content    = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateModel($emailTemplate, false);
                }
                Yii::app()->clientScript->render($content);
                echo $content;
                Yii::app()->end(0, false);
            }
            $serializedDataArray    = Yii::app()->request->getPost('serializedData');
            if (!Yii::app()->request->isPostRequest || $serializedDataArray === null)
            {
                Yii::app()->end(0, false);
            }
            $content = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlBySerializedData($serializedDataArray, false);
            Yii::app()->clientScript->render($content);
            echo $content;
        }

        public function actionRenderElementEditable()
        {
            $this->actionRenderElement(true);
        }

        public function actionRenderElementNonEditable()
        {
            $ajax = Yii::app()->request->getPost('ajax');
            if (isset($ajax))
            {
                BuilderElementRenderUtil::validateEditableForm();
            }
            $this->actionRenderElement(false);
        }

        protected function actionRenderElement($editable = false)
        {
            Yii::app()->clientScript->setToAjaxMode();
            $editableForm       = Yii::app()->request->getPost(BaseBuilderElement::getModelClassName());
            $className          = ArrayUtil::getArrayValue($editableForm, 'className');
            $id                 = ArrayUtil::getArrayValue($editableForm, 'id');
            $properties         = ArrayUtil::getArrayValue($editableForm, 'properties');
            $content            = ArrayUtil::getArrayValue($editableForm, 'content');
            $params             = ArrayUtil::getArrayValue($editableForm, 'params');
            $renderForCanvas    = Yii::app()->request->getPost('renderForCanvas', !$editable);
            $wrapElementInRow   = Yii::app()->request->getPost('wrapElementInRow', BuilderElementRenderUtil::DO_NOT_WRAP_IN_ROW);

            // at bare minimum we should have classname. Without it, it does not make sense.
            if (!Yii::app()->request->isPostRequest || !isset($className))
            {
                Yii::app()->end(0, false);
            }
            if ($editable)
            {
                $content = BuilderElementRenderUtil::renderEditable($className, $renderForCanvas, $id, $properties,
                                                                    $content, $params);
            }
            else
            {
                $content = BuilderElementRenderUtil::renderNonEditable($className, $renderForCanvas, $wrapElementInRow,
                                                                        $id, $properties, $content, $params);
            }
            Yii::app()->clientScript->render($content);
            echo $content;
        }

        public function actionConvertEmail($id, $converter = null)
        {
            $emailTemplate  = EmailTemplate::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($emailTemplate);
            $htmlContent    = ZurmoCssInlineConverterUtil::convertAndPrettifyEmailByModel($emailTemplate, $converter);
            echo $htmlContent;
        }

        public function actionSendTestEmail($id, $contactId = null, $emailAddress = null, $useHtmlContent = 1)
        {
            $emailTemplate  = EmailTemplate::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserReadModel($emailTemplate);
            $htmlContent    = $emailTemplate->htmlContent;
            if (!$useHtmlContent)
            {
                $htmlContent    = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateModel($emailTemplate,
                                                                                                                false);
            }
            $contact        = null;
            if (isset($contactId))
            {
                $contact    = Contact::getById(intval($contactId));
            }
            static::resolveEmailMessage($emailTemplate, $contact, $htmlContent, $emailAddress);
        }

        protected static function resolveEmailMessage(EmailTemplate $emailTemplate, Contact $contact = null, $htmlContent, $emailAddress = null)
        {
            // TODO: @Shoaibi: Critical: Refactor this and AutoresponderAndCampaignItemsUtil
            $emailMessage                       = new EmailMessage();
            $emailMessage->subject              = $emailTemplate->subject;
            $emailContent                       = new EmailMessageContent();
            $emailContent->textContent          = $emailTemplate->textContent;
            // we do not need to do : EmailTemplateSerializedDataToHtmlUtil::resolveHtmlByEmailTemplateModel($emailTemplate);
            // check __set of EmailTemplate.
            $emailContent->htmlContent          = $htmlContent;
            $emailMessage->content              = $emailContent;
            $emailMessage->sender               = static::resolveSender();
            static::resolveRecipient($emailMessage, $contact, $emailAddress);
            $box                                = EmailBox::resolveAndGetByName(EmailBox::USER_DEFAULT_NAME);
            $emailMessage->folder               = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
            Yii::app()->emailHelper->sendImmediately($emailMessage);
            $emailMessage->owner                = $emailTemplate->owner;
            $explicitReadWriteModelPermissions  = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($emailTemplate);
            ExplicitReadWriteModelPermissionsUtil::resolveExplicitReadWriteModelPermissions($emailMessage,
                                                                                    $explicitReadWriteModelPermissions);
            if (!$emailMessage->save())
            {
                throw new FailedToSaveModelException("Unable to save EmailMessage");
            }
        }

        protected static function resolveSender()
        {
            $sender                         = new EmailMessageSender();
            $sender->fromAddress            = Yii::app()->emailHelper->resolveFromAddressByUser(Yii::app()->user->userModel);
            $sender->fromName               = strval(Yii::app()->user->userModel);
            return $sender;
        }

        protected static function resolveRecipient(EmailMessage $emailMessage, Contact $contact = null, $emailAddress = null)
        {
            if ($contact === null)
            {
                $contact  = static::resolveDefaultRecipient();
            }
            if ($emailAddress == null)
            {
                $primaryEmailAddress    = $contact->primaryEmail->emailAddress;
            }
            else
            {
                $primaryEmailAddress    = $emailAddress;
            }

            if ($primaryEmailAddress != null)
            {
                $recipient                  = new EmailMessageRecipient();
                $recipient->toAddress       = $primaryEmailAddress;
                $recipient->toName          = strval($contact);
                $recipient->type            = EmailMessageRecipient::TYPE_TO;
                $recipient->personsOrAccounts->add($contact);
                $emailMessage->recipients->add($recipient);
            }
        }

        protected static function resolveDefaultRecipient()
        {
            return Yii::app()->user->userModel;
        }

        public function actionModalList($stateMetadataAdapterClassName = null)
        {
            $modalListLinkProvider = new SelectFromRelatedEditModalListLinkProvider(
                $_GET['modalTransferInformation']['sourceIdFieldId'],
                $_GET['modalTransferInformation']['sourceNameFieldId'],
                $_GET['modalTransferInformation']['modalId']
            );
            echo ModalSearchListControllerUtil::
                setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider, $stateMetadataAdapterClassName);
        }

        public function actionAutoComplete($term, $autoCompleteOptions = null, $type = null)
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType('autoCompleteListPageSize',
                                        get_class($this->getModule()));
            $autoCompleteResults = EmailTemplateAutoCompleteUtil::getByPartialName($term, $pageSize, null,
                                                $type, $autoCompleteOptions);
            if (empty($autoCompleteResults))
            {
                $autoCompleteResults = array(array('id'    => null,
                    'value' => null,
                    'label' => Zurmo::t('Core', 'No results found')));
            }
            echo CJSON::encode($autoCompleteResults);
        }
    }
?>