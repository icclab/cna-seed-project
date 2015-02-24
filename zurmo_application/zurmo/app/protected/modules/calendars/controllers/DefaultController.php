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

    class CalendarsDefaultController extends ZurmoModuleController
    {
        /**
         * @return array
         */
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
                        ZurmoModuleController::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                        'controller' => $this,
                   ),
               )
            );
        }

        /**
         * Redirect to combined details view for calendar.
         * @param int $id
         */
        public function actionDetails($id = null)
        {
            $urlParams = array($this->getId() . '/combinedDetails');
            $this->redirect($urlParams);
        }

        /**
         * Create the calendar.
         */
        public function actionCreate()
        {
            $savedCalendar                     = new SavedCalendar();
            $savedCalendar->moduleClassName    = 'MeetingsModule';
            $attributes                        = CalendarUtil::getModelAttributesForSelectedModule($savedCalendar->moduleClassName);
            $attributeKeys                     = array_keys($attributes);
            $savedCalendar->startAttributeName = $attributeKeys[0];
            $this->attemptToValidateAjaxFromPost($savedCalendar, 'SavedCalendar');
            if (isset($_POST['SavedCalendar']))
            {
                $this->attemptToSaveModelFromPost($savedCalendar, null, false, false);
                echo CJSON::encode(array('redirecttodetails' => true));
                Yii::app()->end(0, false);
            }
            else
            {
                $editAndDetailsView = $this->makeEditAndDetailsView(
                                                $this->attemptToSaveModelFromPost($savedCalendar, null, false, false), 'Edit');
                $view               = new CalendarsPageView(ZurmoDefaultViewUtil::
                                                            makeStandardViewForCurrentUser($this, $editAndDetailsView));
                echo $view->render();
            }
        }

        /**
         * Edit the calendar.
         * @param int $id
         */
        public function actionEdit($id)
        {
            $savedCalendar = SavedCalendar::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($savedCalendar);
            $this->processEdit($savedCalendar);
        }

        /**
         * Process edit of the calendar.
         * @param SavedCalendar $calendar
         */
        protected function processEdit(SavedCalendar $calendar)
        {
            $this->attemptToValidateAjaxFromPost($calendar, 'SavedCalendar');
            if (isset($_POST['SavedCalendar']))
            {
                $this->attemptToSaveModelFromPost($calendar, null, false, false);
                echo CJSON::encode(array('redirecttodetails' => true));
                Yii::app()->end(0, false);
            }
            else
            {
                $view = new CalendarsPageView(ZurmoDefaultViewUtil::
                                makeStandardViewForCurrentUser($this,
                                $this->makeEditAndDetailsView(
                                    $calendar, 'Edit')));
                echo $view->render();
            }
        }

        /**
         * Combined details for the calendar.
         */
        public function actionCombinedDetails()
        {
            $dataProvider               = CalendarUtil::getCalendarItemsDataProvider(Yii::app()->user->userModel);
            $interactiveCalendarView    = new CombinedCalendarView($dataProvider, $this->getId(), $this->getModule()->getId());
            $view                       = new CalendarsPageView(ZurmoDefaultViewUtil::
                                                  makeStandardViewForCurrentUser($this, $interactiveCalendarView));
            echo $view->render();
        }

        /**
         * Render relations and attributes tree
         * @param string $type
         * @param string $treeType
         * @param int $id
         * @param string $nodeId
         */
        public function actionRelationsAndAttributesTree($type, $treeType, $id = null, $nodeId = null)
        {
            $postData      = PostUtil::getData();
            $report        = CalendarUtil::resolveReportBySavedCalendarPostData($type, $id, $postData);
            if ($nodeId != null)
            {
                $reportToTreeAdapter = new CalendarReportRelationsAndAttributesToTreeAdapter($report, $treeType);
                echo ZurmoTreeView::saveDataAsJson($reportToTreeAdapter->getData($nodeId));
                Yii::app()->end(0, false);
            }
            $view        = new ReportRelationsAndAttributesForSavedCalendarTreeView($type, $treeType, 'edit-form');
            $content     = $view->render();
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        /**
         * Add attribute from tree.
         *
         * @param string $type
         * @param string $treeType
         * @param string $nodeId
         * @param int $rowNumber
         * @param boolean $trackableStructurePosition
         * @param int $id
         */
        public function actionAddAttributeFromTree($type, $treeType, $nodeId, $rowNumber,
                                                   $trackableStructurePosition = false, $id = null)
        {
            $postData   = PostUtil::getData();
            $report     = CalendarUtil::resolveReportBySavedCalendarPostData($type, $id, $postData);
            ReportUtil::processAttributeAdditionFromTree($nodeId, $treeType, $report, $rowNumber, $trackableStructurePosition);
        }

        /**
         * Override to handle report filters
         * @param SavedCalendar | ModelForm $model
         * @param string $postVariableName
         * @throws NotSupportedException();
         */
        protected function attemptToValidateAjaxFromPost($model, $postVariableName)
        {
            if (isset($_POST['ajax']) && $_POST['ajax'] == 'edit-form')
            {
                $postData                      = PostUtil::getData();
                $readyToUsePostData            = ExplicitReadWriteModelPermissionsUtil::
                                                         removeIfExistsFromPostData($_POST[$postVariableName]);
                $sanitizedPostdata             = PostUtil::sanitizePostByDesignerTypeForSavingModel($model, $readyToUsePostData);
                $sanitizedOwnerPostData        = PostUtil::
                                                 sanitizePostDataToJustHavingElementForSavingModel($sanitizedPostdata, 'owner');
                $sanitizedPostDataWithoutOwner = PostUtil::removeElementFromPostDataForSavingModel($sanitizedPostdata, 'owner');
                $model->setAttributes(($sanitizedPostDataWithoutOwner));
                if ($model->validate())
                {
                    $modelToStringValue = strval($model);
                    if ($sanitizedOwnerPostData != null)
                    {
                        $model->setAttributes($sanitizedOwnerPostData);
                    }
                    if ($model instanceof OwnedSecurableItem)
                    {
                        $model->validate(array('owner'));
                    }
                }
                $wizardFormClassName  = ReportToWizardFormAdapter::getFormClassNameByType(Report::TYPE_ROWS_AND_COLUMNS);
                if (!isset($postData[$wizardFormClassName]))
                {
                    throw new NotSupportedException();
                }
                $report = SavedCalendarToReportAdapter::makeReportBySavedCalendar($model);
                DataToReportUtil::resolveFiltersStructure($postData[$wizardFormClassName], $report);
                DataToReportUtil::resolveFilters($postData[$wizardFormClassName], $report);
                //This would do the filter and filter structure validation
                $reportToWizardFormAdapter      = new ReportToWizardFormAdapter($report);
                $reportForm                     = $reportToWizardFormAdapter->makeFormByType();
                $postData['validationScenario'] = $wizardFormClassName::FILTERS_VALIDATION_SCENARIO;
                $filtersErrorData               = ReportUtil::validateReportWizardForm($postData, $reportForm);
                $errorData = ZurmoActiveForm::makeErrorsDataAndResolveForOwnedModelAttributes($model);
                $errorData = array_merge($errorData, $filtersErrorData);
                echo CJSON::encode($errorData);
                Yii::app()->end(0, false);
            }
        }

        /**
         * Get events for the selected calendars.
         * @param string $selectedMyCalendarIds
         * @param string $selectedSharedCalendarIds
         * @param string $startDate
         * @param string $endDate
         * @param string $dateRangeType
         */
        public function actionGetEvents($selectedMyCalendarIds = null,
                                        $selectedSharedCalendarIds = null,
                                        $startDate = null,
                                        $endDate = null,
                                        $dateRangeType = null)
        {
            $dataProvider               = CalendarUtil::processAndGetDataProviderForEventsData($selectedMyCalendarIds,
                                                                                               $selectedSharedCalendarIds,
                                                                                               $startDate,
                                                                                               $endDate,
                                                                                               $dateRangeType);
            $items                      = CalendarUtil::getFullCalendarItems($dataProvider);
            foreach ($items as $index => $item)
            {
                $itemClass = isset($item['className']) ? $item['className']:null;
                //If not more events
                if ($itemClass != 'more-events')
                {
                    $item['description']     = Yii::app()->createUrl('calendars/default/getCalendarItemDetail',
                                                                     array(
                                                                            'modelClass' => $item['modelClass'],
                                                                            'modelId'    => $item['modelId']
                                                                     ));
                }
                else
                {
                    $moreEventStartDate     = date('Y-m-d', strtotime($item['start']));
                    $dateTimeObject         = new DateTime($moreEventStartDate);
                    $dateTimeObject->add(new DateInterval('P1D'));
                    $moreEventEndDate       = $dateTimeObject->format('Y-m-d');
                    $params               = array(
                                                    'selectedMyCalendarIds'     => $selectedMyCalendarIds,
                                                    'selectedSharedCalendarIds' => $selectedSharedCalendarIds,
                                                    'startDate'                 => $moreEventStartDate,
                                                    'endDate'                   => $moreEventEndDate,
                                                    'dateRangeType'             => SavedCalendar::DATERANGE_TYPE_DAY
                                                );
                    $item['description']     = Yii::app()->createUrl('calendars/default/getDayEvents', $params);
                }
                $items[$index]            = $item;
            }
            echo CJSON::encode(array('items' => $items, 'isMaxCountReached' => $dataProvider->getIsMaxCountReached()));
        }

        /**
         * Deletes a calendar.
         * @param string $id
         */
        public function actionDelete($id)
        {
            $savedCalendar = SavedCalendar::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($savedCalendar);
            $savedCalendar->delete();
            $dataProvider                        = CalendarUtil::getCalendarItemsDataProvider(Yii::app()->user->userModel);
            $savedCalendarSubscriptions          = $dataProvider->getSavedCalendarSubscriptions();
            $content                             = CalendarUtil::makeCalendarItemsList($savedCalendarSubscriptions->getMySavedCalendarsAndSelected(),
                                                                                       'mycalendar[]', 'mycalendar', 'saved');
            echo $content;
        }

        /**
         * Renders modal list for the shared calendars for the user.
         */
        public function actionModalList()
        {
            $modalListLinkProvider = new SelectFromSharedCalendarsModalListLinkProvider(
                                            CalendarUtil::getModalContainerId(),
                                            'shared-calendars-list'
                                        );
            echo ModalSearchListControllerUtil::
                 setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider, 'SharedCalendersStateMetadataAdapter');
        }

        /**
         * Add subscription for calendar.
         * @param int $id
         */
        public function actionAddSubsriptionForCalendar($id)
        {
            $savedCalendar                       = SavedCalendar::getById(intval($id));
            $user                                = Yii::app()->user->userModel;
            $savedCalendarSubscription           = new SavedCalendarSubscription();
            $savedCalendarSubscription->user     = $user;
            $savedCalendarSubscription->savedcalendar = $savedCalendar;
            $savedCalendarSubscription->save();
            $dataProvider                        = CalendarUtil::getCalendarItemsDataProvider($user);
            $savedCalendarSubscriptions          = $dataProvider->getSavedCalendarSubscriptions();
            $content                             = CalendarUtil::makeCalendarItemsList($savedCalendarSubscriptions->getSubscribedToSavedCalendarsAndSelected(),
                                                                                       'sharedcalendar[]', 'sharedcalendar', 'shared');
            echo $content;
        }

        /**
         * Remove the subscription for the calendar.
         * @param int $id
         */
        public function actionUnsubscribe($id)
        {
            $savedCalendarSubscription = SavedCalendarSubscription::getById(intval($id));
            $savedCalendarSubscription->delete();
            $dataProvider                        = CalendarUtil::getCalendarItemsDataProvider(Yii::app()->user->userModel);
            $savedCalendarSubscriptions          = $dataProvider->getSavedCalendarSubscriptions();
            $content                             = CalendarUtil::makeCalendarItemsList($savedCalendarSubscriptions->getSubscribedToSavedCalendarsAndSelected(),
                                                                                       'sharedcalendar[]', 'sharedcalendar', 'shared');
            echo $content;
        }

        /**
         * Get date time attributes.
         * @param string $moduleName
         * @param string $attribute
         */
        public function actionGetDateTimeAttributes($moduleName, $attribute)
        {
            assert('is_string($attribute)');
            assert('is_string($moduleName)');
            $data = CalendarUtil::getModelAttributesForSelectedModule($moduleName);
            $htmlOptions = array();
            if ($attribute == 'endAttributeName')
            {
                $htmlOptions['empty'] = Zurmo::t('Core', '(None)');
            }
            echo ZurmoHtml::listOptions('', $data, $htmlOptions);
        }

        /**
         * Get Zurmo controller util.
         * @return CalendarZurmoControllerUtil
         */
        protected static function getZurmoControllerUtil()
        {
            return new CalendarZurmoControllerUtil();
        }

        /**
         * Get events for the selected calendars.
         * @param string $selectedMyCalendarIds
         * @param string $selectedSharedCalendarIds
         * @param string $startDate
         * @param string $endDate
         * @param string $dateRangeType
         */
        public function actionGetDayEvents($selectedMyCalendarIds = null,
                                            $selectedSharedCalendarIds = null,
                                            $startDate = null,
                                            $endDate = null,
                                            $dateRangeType = null)
        {
            $dataProvider               = CalendarUtil::processAndGetDataProviderForEventsData($selectedMyCalendarIds,
                                                                                               $selectedSharedCalendarIds,
                                                                                               $startDate,
                                                                                               $endDate,
                                                                                               $dateRangeType,
                                                                                               false);
            $items                      = CalendarUtil::processDataProviderAndGetCalendarItems($dataProvider);
            $modifiedItems = array();
            foreach ($items as $item)
            {
                $item['start'] = CalendarUtil::getFullCalendarFormattedDateTimeElement($item['start']);
                $startDateTime = strtotime($item['start']);
                if (date('Y-m-d', $startDateTime) == $startDate)
                {
                    $item['end'] = CalendarUtil::getFullCalendarFormattedDateTimeElement($item['end']);
                    $modifiedItems[] = $item;
                }
            }
            $modifiedItems                      = CalendarUtil::populateDetailsUrlForCalendarItems($modifiedItems);
            $calItemListView            = new CalendarItemsListView(
                                                                    $this->getId(),
                                                                    $this->getModule()->getId(),
                                                                    $modifiedItems,
                                                                    GetUtil::getData()
                                                                   );
            Yii::app()->getClientScript()->setToAjaxMode();
            echo $calItemListView->render();
        }

        /**
         * Get event details in the calendar.
         *
         * @param string $modelClass
         * @param string $modelId
         */
        public function actionGetCalendarItemDetail($modelClass, $modelId)
        {
            $model                   = $modelClass::getById(intval($modelId));
            $itemDetailViewClassName = $modelClass . 'ForCalendarItemDetailsView';
            $itemDetailViewInstance  = new $itemDetailViewClassName($this->getId(), $this->getModule()->getId(), $model);
            echo $itemDetailViewInstance->render();
        }

        public function actionOrgGraph()
        {
            $renderOrgChartModalView = new AccountOrgChartModalView();
            $view = new ModalView($this, $renderOrgChartModalView);
            return $view->render();
        }
    }
?>