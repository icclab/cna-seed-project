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
      * Data provider for calendar items.
      */
    class CalendarItemsDataProvider extends CDataProvider
    {
        const MAXIMUM_CALENDAR_ITEMS_COUNT = 200;

        const MAXIMUM_CALENDAR_ITEMS_DISPLAYED_FOR_ANY_DATE = 5;

        /**
         * @var array
         */
        protected $savedCalendarSubscriptions;

        /**
         * @var string
         */
        protected $moduleClassName;

        /**
         * @var SavedCalendar
         */
        protected $savedCalendar;

        /**
         * @var string
         */
        protected $startDate;

        /**
         * @var string
         */
        protected $endDate;

        /**
         * @var string
         */
        protected $dateRangeType;

        /**
         * @var array
         */
        private $_calendarItemsData;

        /**
         * @var boolean
         */
        private $_isMaxCountReached = false;

        /**
         * @var int
         */
        private $_itemCount;

        /**
         * @param SavedCalendarSubscriptions $savedCalendarSubscriptions
         * @param array $config
         */
        public function __construct(SavedCalendarSubscriptions $savedCalendarSubscriptions, array $config = array())
        {
            $this->savedCalendarSubscriptions = $savedCalendarSubscriptions;
            foreach ($config as $key => $value)
            {
                $this->$key = $value;
            }
            $this->startDate  = DateTimeUtil::convertTimestampToDbFormatDate(strtotime($this->startDate));
            $this->endDate    = DateTimeUtil::convertTimestampToDbFormatDate(strtotime($this->endDate));
            $this->_itemCount = 0;
        }

        /**
         * Calculates total item count.
         *
         * @return int
         */
        public function calculateTotalItemCount()
        {
            return 0;
        }

        /**
         * Override so when refresh is true it resets _calendarItemsData
         */
        public function getData($refresh = false)
        {
            if ($refresh)
            {
                $this->_calendarItemsData = null;
            }
            if ($this->_calendarItemsData === null)
            {
                $this->_calendarItemsData = $this->fetchData();
            }
            return $this->_calendarItemsData;
        }

        /**
         * Fetches data.
         *
         * @return array
         */
        protected function fetchData()
        {
            return $this->resolveCalendarItems();
        }

        /**
         * Fetches keys for data items.
         * @return array
         */
        protected function fetchKeys()
        {
            $keys = array();
            foreach ($this->getData() as $data)
            {
                $keys[] = $data->getId();
            }
            return $keys;
        }

        /**
         * Resolve calendar items.
         * @return array
         */
        protected function resolveCalendarItems()
        {
            $calendarItems = array();
            foreach ($this->savedCalendarSubscriptions->getMySavedCalendarsAndSelected() as $savedCalendarData)
            {
                if ($savedCalendarData[1])
                {
                    $models = $this->resolveRedBeanModelsByCalendar($savedCalendarData[0]);
                    $this->resolveRedBeanModelsToCalendarItems($calendarItems, $models, $savedCalendarData[0]);
                }
            }
            foreach ($this->savedCalendarSubscriptions->getSubscribedToSavedCalendarsAndSelected() as $savedCalendarData)
            {
                if ($savedCalendarData[1])
                {
                    $models        = $this->resolveRedBeanModelsByCalendar($savedCalendarData[0]->savedcalendar);
                    $savedCalendar = $savedCalendarData[0]->savedcalendar;
                    $savedCalendar->color = $savedCalendarData[0]->color;
                    $this->resolveRedBeanModelsToCalendarItems($calendarItems, $models, $savedCalendar);
                }
            }
            return $calendarItems;
        }

        /**
         * Resolve redbean models by calendar.
         * @param SavedCalendar $calendar
         * @return array
         */
        protected function resolveRedBeanModelsByCalendar(SavedCalendar $calendar)
        {
            $models             = array();
            $report             = $this->makeReportBySavedCalendar($calendar);
            $reportDataProvider = new CalendarRowsAndColumnsReportDataProvider($report);
            $reportResultsRows  = $reportDataProvider->getData(true);
            foreach ($reportResultsRows as $reportResultsRowData)
            {
                if ($this->_itemCount >= self::MAXIMUM_CALENDAR_ITEMS_COUNT)
                {
                    $this->setIsMaxCountReached(true);
                    break;
                }
                $models[] = $reportResultsRowData->getModel('attribute0');
                $this->_itemCount++;
            }
            return $models;
        }

        /**
         * Makes report by saved calendar.
         * @param SavedCalendar $savedCalendar
         * @return Report
         */
        protected function makeReportBySavedCalendar(SavedCalendar $savedCalendar)
        {
            $moduleClassName      = $savedCalendar->moduleClassName;
            $report               = SavedCalendarToReportAdapter::makeReportBySavedCalendar($savedCalendar);
            $existingFilters      = $report->getFilters();
            $existingFiltersCount = count($existingFilters);
            $newFiltersToAdd      = array();
            $newStructureToAdd    = null;

            $this->processFiltersAndStructureForMeetingsThatStartAndEndAfterRange($newFiltersToAdd, $newStructureToAdd, $existingFiltersCount, $savedCalendar, $report);
            $this->processFiltersAndStructureForMeetingsThatStartBeforeRange($newFiltersToAdd, $newStructureToAdd, $existingFiltersCount, $savedCalendar, $report);
            $this->processFiltersAndStructureForMeetingsThatEndAfterRange($newFiltersToAdd, $newStructureToAdd, $existingFiltersCount, $savedCalendar, $report);
            foreach ($newFiltersToAdd as $filter)
            {
                $report->addFilter($filter);
            }
            if ($report->getFiltersStructure() != null)
            {
                $report->setFiltersStructure($report->getFiltersStructure() . " AND ({$newStructureToAdd})");
            }
            else
            {
                $report->setFiltersStructure($newStructureToAdd);
            }

            $displayAttribute = new DisplayAttributeForReportForm($moduleClassName, $moduleClassName::getPrimaryModelName(),
                                    $report->getType());
            $displayAttribute->attributeIndexOrDerivedType = 'id';
            $report->addDisplayAttribute($displayAttribute);
            return $report;
        }

        protected function processFiltersAndStructureForMeetingsThatStartAndEndAfterRange(& $filters, & $structure, & $filtersCount, SavedCalendar $savedCalendar, Report $report)
        {
            if (count($filters) != 0)
            {
                $structure .= ' OR ';
            }
            $moduleClassName = $savedCalendar->moduleClassName;
            $startFilter = new FilterForReportForm($moduleClassName, $moduleClassName::getPrimaryModelName(), $report->getType());
            $startFilter->attributeIndexOrDerivedType = $savedCalendar->startAttributeName;
            $startFilter->value                       = $this->startDate;
            $startFilter->valueType                   = MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER;
            $filters[] = $startFilter;
            $endFilter = new FilterForReportForm($moduleClassName, $moduleClassName::getPrimaryModelName(), $report->getType());
            if ($savedCalendar->endAttributeName != null)
            {
                $endFilter->attributeIndexOrDerivedType = $savedCalendar->endAttributeName;
            }
            else
            {
                $endFilter->attributeIndexOrDerivedType = $savedCalendar->startAttributeName;
            }
            $endFilter->value                       = $this->endDate;
            $endFilter->valueType                   = MixedDateTypesSearchFormAttributeMappingRules::TYPE_BEFORE;
            $filters[] = $endFilter;
            $structure .= '(' . ($filtersCount + 1) . ' AND ' . ($filtersCount + 2) . ')';
            $filtersCount += 2;
        }

        protected function processFiltersAndStructureForMeetingsThatStartBeforeRange(& $filters, & $structure, & $filtersCount, SavedCalendar $savedCalendar, Report $report)
        {
            if (count($filters) != 0)
            {
                $structure .= ' OR ';
            }
            $moduleClassName = $savedCalendar->moduleClassName;
            $startFilter = new FilterForReportForm($moduleClassName, $moduleClassName::getPrimaryModelName(), $report->getType());
            $startFilter->attributeIndexOrDerivedType = $savedCalendar->startAttributeName;
            $startFilter->value                       = $this->startDate;
            $startFilter->valueType                   = MixedDateTypesSearchFormAttributeMappingRules::TYPE_BEFORE;
            $filters[] = $startFilter;
            $endFilter = new FilterForReportForm($moduleClassName, $moduleClassName::getPrimaryModelName(), $report->getType());
            if ($savedCalendar->endAttributeName != null)
            {
                $endFilter->attributeIndexOrDerivedType = $savedCalendar->endAttributeName;
            }
            else
            {
                $endFilter->attributeIndexOrDerivedType = $savedCalendar->startAttributeName;
            }
            $endFilter->value                       = $this->startDate;
            $endFilter->valueType                   = MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER;
            $filters[] = $endFilter;
            $structure .= '(' . ($filtersCount + 1) . ' AND ' . ($filtersCount + 2) . ')';
            $filtersCount += 2;
        }

        protected function processFiltersAndStructureForMeetingsThatEndAfterRange(& $filters, & $structure, & $filtersCount, SavedCalendar $savedCalendar, Report $report)
        {
            if (count($filters) != 0)
            {
                $structure .= ' OR ';
            }
            $moduleClassName = $savedCalendar->moduleClassName;
            $startFilter = new FilterForReportForm($moduleClassName, $moduleClassName::getPrimaryModelName(), $report->getType());
            $startFilter->attributeIndexOrDerivedType = $savedCalendar->startAttributeName;
            $startFilter->value                       = $this->endDate;
            $startFilter->valueType                   = MixedDateTypesSearchFormAttributeMappingRules::TYPE_BEFORE;
            $filters[] = $startFilter;
            $endFilter = new FilterForReportForm($moduleClassName, $moduleClassName::getPrimaryModelName(), $report->getType());
            if ($savedCalendar->endAttributeName != null)
            {
                $endFilter->attributeIndexOrDerivedType = $savedCalendar->endAttributeName;
            }
            else
            {
                $endFilter->attributeIndexOrDerivedType = $savedCalendar->startAttributeName;
            }
            $endFilter->value                       = $this->endDate;
            $endFilter->valueType                   = MixedDateTypesSearchFormAttributeMappingRules::TYPE_AFTER;
            $filters[] = $endFilter;
            $structure .= '(' . ($filtersCount + 1) . ' AND ' . ($filtersCount + 2) . ')';
            $filtersCount += 2;
        }

        /**
         * Get the list of calendar items
         * @param array $calendarItems
         * @param array $models
         * @param SavedCalendar $savedCalendar
         */
        protected function resolveRedBeanModelsToCalendarItems(& $calendarItems, array $models, SavedCalendar $savedCalendar)
        {
            foreach ($models as $model)
            {
                $calendarItems[] = CalendarUtil::makeCalendarItemByModel($model, $savedCalendar);
            }
        }

        /**
         * @return string
         */
        public function getModuleClassName()
        {
            return $this->moduleClassName;
        }

        /**
         * @return string
         */
        public function getStartDate()
        {
            return $this->startDate;
        }

        /**
         * @return string
         */
        public function getEndDate()
        {
            return $this->endDate;
        }

        /**
         * @param string $moduleClassName
         */
        public function setModuleClassName($moduleClassName)
        {
            $this->moduleClassName = $moduleClassName;
        }

        /**
         * @return array
         */
        public function getSavedCalendarSubscriptions()
        {
            return $this->savedCalendarSubscriptions;
        }

        /**
         * @param array $savedCalendarSubscriptions
         */
        public function setSavedCalendarSubscriptions($savedCalendarSubscriptions)
        {
            $this->savedCalendarSubscriptions = $savedCalendarSubscriptions;
        }

        /**
         * @return string
         */
        public function getDateRangeType()
        {
            return $this->dateRangeType;
        }

        /**
         * @param string $dateRangeType
         */
        public function setDateRangeType($dateRangeType)
        {
            $this->dateRangeType = $dateRangeType;
        }

        /**
         * @return bool
         */
        public function getIsMaxCountReached()
        {
            return $this->_isMaxCountReached;
        }

        /**
         * Sets is max count reached
         * @param bool $isMaxCountReached
         */
        public function setIsMaxCountReached($isMaxCountReached)
        {
            assert('is_bool($isMaxCountReached)');
            $this->_isMaxCountReached = $isMaxCountReached;
        }
    }
?>