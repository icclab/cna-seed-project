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
     * Helper class for managing customizations to Zurmo. If you want to do customizations, extend this class and in
     * perInstance.php define:
     * $instanceConfig['custom']['class'] = 'path.to.your.custom.management.component.MyCustomMeasurement';
     * Then in your new component, you can override any of the methods that act as hooks.
     */
    class CustomManagement extends CApplicationComponent
    {
        /**
         * Called right before the auto build is initialized in the installation process.
         * Make sure you do not clear the cache in here if you are running any type of autobuilder such as building
         * the globalmetadata table in order to populate it.
         * @see InstallUtil::runInstallation
         * @param MessageLogger $messageLogger
         */
        public function runBeforeInstallationAutoBuildDatabase(MessageLogger $messageLogger)
        {
        }

        /**
         * Called right after the default data is loaded in the installation process.
         * @see InstallUtil::runInstallation
         * @param MessageLogger $messageLogger
         */
        public function runAfterInstallationDefaultDataLoad(MessageLogger $messageLogger)
        {
        }

        /**
         * Called as a begin request behavior.  This is only called during non-installation behavior. This can be used
         * as a convenience for developers to check and load any missing metadata customizations as they develop.
         */
        public function resolveIsCustomDataLoaded()
        {
        }

        /**
         * Called from ImportCommand.  Override and add calls to any import routines you would like to run.
         * @see ImportCommand
         * @param MessageLogger $messageLogger
         * @param string $importName - Optional array of specific import process to run, otherwise if empty,
         *                             run all available import processes.
         */
        public function runImportsForImportCommand(ImportMessageLogger $messageLogger, $importName = null)
        {
            $messageLogger->addErrorMessage(Zurmo::t('Core', 'No import processes found.'));
            $messageLogger->addErrorMessage(Zurmo::t('Core', 'CustomManagement class needs to be extended.'));
        }

        public function resolveElementInformationDuringFormLayoutRender(DetailsView $view, &$elementInformation)
        {
        }

        public function resolveActionElementInformationDuringRender(MetadataView $view, & $elementInformation)
        {
        }

        /**
         * Called in CalendarUtil to set the title.
         * @param CalendarItem $calendarItem
         * @param RedBeanModel $model
         */
        public function setCalendarItemTitle(CalendarItem $calendarItem, RedBeanModel $model)
        {
            $calendarItem->setTitle(StringUtil::getChoppedStringContent($model->name, CalendarItem::MAXIMUM_TITLE_LENGTH));
        }

        /**
         * Resolve row menu column class for open task portlet.
         * @param string $relationAttributeName
         * @return string
         */
        public function resolveRowMenuColumnClassForOpenTaskPortlet($relationAttributeName)
        {
            return 'RowMenuColumn';
        }

        /**
         * Register task modal detail script.
         * @param string $gridViewId
         */
        public function registerTaskModalDetailsScript($gridViewId)
        {
            assert('is_string($gridViewId)');
            TasksUtil::registerTaskModalDetailsScript($gridViewId);
        }

        /**
         * Resolve data provider by search model.
         * @param TasksByOpportunitySearchForm $searchModel
         * @return string
         */
        public function resolveDataProviderClassNameForControllerBySearchModel($searchModel)
        {
            if ($searchModel->filterByStarred)
            {
                return 'StarredModelDataProvider';
            }
            return 'RedBeanModelDataProvider';
        }

        /**
         * Register script for special task detail link. This is from a redirect of something like
         * tasks/default/details and it should open up the task immediately.
         * @param int $taskId
         * @param string $sourceId
         */
        public function registerOpenToTaskModalDetailsScript($taskId, $sourceId)
        {
            TasksUtil::registerOpenToTaskModalDetailsScript($taskId, $sourceId);
        }

        /**
         * Render kanban search view.
         * @param TasksSearchForm $searchFormModel
         * @param array $params
         * @return string
         */
        public function renderKanbanSearchView($searchFormModel, $params)
        {
            return null;
        }

        /**
         * Resolve kanban columns.
         * @param array $columns
         * @return array
         */
        public function resolveKanbanCardColumns($columns)
        {
            assert('is_array($columns)');
            return $columns;
        }

        /**
         * Renders extra attributes with name in kanban card.
         * @param array $cardColumns
         * @param Task $task
         * @param int $row
         */
        public function renderExtraAttributesWithNameInKanbanCard($cardColumns, Task $task, $row)
        {
            return null;
        }

        /**
         * Resolve task modal button column class for tasks my list view.
         * @return string
         */
        public function resolveTaskModalButtonColumnClassNameForTasksMyListView()
        {
            return 'TaskModalButtonColumn';
        }

        /**
         * @param $viewClassName string
         * @param $params array
         * @param $defaultOptionsContent string
         * @param $parentContent string
         * @return string
         */
        public function renderPortletHeadContentForOpenTaskPortletOnDetailsAndRelationsView($viewClassName,
                                                                                            $params,
                                                                                            $defaultOptionsContent,
                                                                                            $parentContent)
        {
            return $parentContent;
        }

        /**
         * Resolve rules class name
         */
        public function resolveComponentRulesClassNameByModule($moduleClassName, $rulesName)
        {
            return $moduleClassName::getPluralCamelCasedName() . $rulesName;
        }

        /**
         * While populating Saved Layouts, featured templates would have respective icon and background color.
         * @param EmailTemplate $emailTemplate
         * @return null
         */
        public function resolveThumbnailForFeaturedEmailTemplate(EmailTemplate $emailTemplate)
        {
            return null;
        }

        public function resolveAdditionalScriptContentForEmailTemplate($stepCount, & $script)
        {
        }

        public function resolveQueueModelEditAndDetailsViewOnLoadScript($model)
        {
        }

        public function resolveQueueModelEditViewOnRuleTypeChangeScript($model)
        {
        }
    }
?>
