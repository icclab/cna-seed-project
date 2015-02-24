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

    class CombinedCalendarView extends ConfigurableMetadataView
    {
        /**
         * Data provider associated with the combined calendar view.
         * @var CalendarItemsDataProvider
         */
        protected $dataProvider;

        /**
         * Saved calendar subscriptions.
         * @var savedCalendarSubscriptions
         */
        protected $savedCalendarSubscriptions;

        /**
         * Controller id associated with the view.
         * @var string
         */
        protected $controllerId;

        /**
         * Module id associated with the view.
         * @var string
         */
        protected $moduleId;

        /**
         * Get default metadata.
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        /**
         * Class constructor.
         * @param CalendarItemsDataProvider $dataProvider
         * @param string $controllerId
         * @param string $moduleId
         */
        public function __construct(CalendarItemsDataProvider $dataProvider, $controllerId, $moduleId)
        {
            $this->dataProvider               = $dataProvider;
            $this->savedCalendarSubscriptions = $this->dataProvider->getSavedCalendarSubscriptions();
            $this->controllerId               = $controllerId;
            $this->moduleId                   = $moduleId;
        }

        /**
         * Renders content.
         * @return string
         */
        protected function renderContent()
        {
            Yii::app()->clientScript->registerScriptFile(Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.modules.calendars.assets')) . '/CalendarsUtil.js',
                                            CClientScript::POS_END);
            //Right
            $rightSideContent = $this->renderOverMaxCountText() . $this->renderFullCalendarContent();
            $right    = ZurmoHtml::tag('div', array('class' => 'right-column'), $rightSideContent);
            //Left
            $content  = $this->renderSmallCalendarContent();
            $content  .= $this->renderMyCalendarsContent();
            $content  .= $this->renderSubscribedToCalendarsContent();
            $left     = ZurmoHtml::tag('div', array('class' => 'left-column'), $content);

            $params   = LabelUtil::getTranslationParamsForAllModules();
            $title    = ZurmoHtml::tag('h1', array(), Zurmo::t('CalendarsModule', 'CalendarsModuleSingularLabel', $params));
            $view     = ZurmoHtml::tag('div', array('class' => 'calendar-view'), $left . $right);
            $wrapper  = ZurmoHtml::tag('div', array('class' => 'wrapper'), $title . $view);
            CalendarUtil::registerSelectCalendarScript($this->dataProvider->getStartDate(), $this->dataProvider->getEndDate());
            CalendarUtil::registerCalendarUnsubscriptionScript($this->dataProvider->getStartDate(), $this->dataProvider->getEndDate());
            CalendarUtil::registerSavedCalendarDeleteScript($this->dataProvider->getStartDate(), $this->dataProvider->getEndDate());
            return $wrapper;
        }

        /**
         * Renders small calendar content.
         * @return string
         */
        protected function renderSmallCalendarContent()
        {
            // Begin Not Coding Standard
            $script = "$( '#smallcalendar' ).datepicker({onSelect: function (date) {
                                                                    var dateArray = date.split('/');
                                                                    var month     = parseInt(dateArray[0]) - 1;
                                                                    $('#calendar').fullCalendar('changeView', 'basicDay');
                                                                    $('#calendar').fullCalendar('gotoDate', dateArray[2], month, dateArray[1]);
                                                                 }
                        });";
            Yii::app()->clientScript->registerScript('smallcalendarscript', $script, ClientScript::POS_END);
            // End Not Coding Standard
            return ZurmoHtml::tag('div', array('id' => 'smallcalendar'), '');
        }

        /**
         * Renders my calendar content.
         * @return string
         */
        protected function renderMyCalendarsContent()
        {
            $myCalendarsListView = new MyCalendarListView($this->controllerId,
                                                          $this->moduleId,
                                                          $this->savedCalendarSubscriptions->getMySavedCalendarsAndSelected(),
                                                          'mycalendar[]', 'mycalendar', 'saved');
            return $myCalendarsListView->render();
        }

        /**
         * Renders calendar content which user has subscribed to.
         * @return string
         */
        protected function renderSubscribedToCalendarsContent()
        {
            $mySharedCalendarsListView = new MySharedCalendarListView($this->controllerId,
                                                          $this->moduleId,
                                                          $this->savedCalendarSubscriptions->getSubscribedToSavedCalendarsAndSelected(),
                                                          'sharedcalendar[]', 'sharedcalendar', 'shared');
            return $mySharedCalendarsListView->render();
        }

        /**
         * Renders full calendar content.
         * @return string
         */
        protected function renderFullCalendarContent()
        {
            $view = new FullCalendarForCombinedView($this->dataProvider);
            return $view->render();
        }

        /**
         * Renders the message when the count of records is more than the limit.
         */
        public function renderOverMaxCountText()
        {
            $label = Zurmo::t('CalendarsModule', 'Only displaying the first {count} calendar items. Try using filters to narrow your results ',
                                array('{count}' => CalendarItemsDataProvider::MAXIMUM_CALENDAR_ITEMS_COUNT));
            $content  = '<div class="general-issue-notice" id="calItemCountResult" style="display:none"><span class="icon-notice"></span><p>';
            $content .= $label;
            $content .= '</p></div>';
            return $content;
        }
    }
?>