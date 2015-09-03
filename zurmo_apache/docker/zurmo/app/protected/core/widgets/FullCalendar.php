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
     * Widget for displaying full calendar.
     */
    class FullCalendar extends ZurmoWidget
    {
        public $inputId;

        public $startDate;

        public $endDate;

        public $defaultView;

        /**
         * Initialize the Calendar Widget
         */
        public function init()
        {
            parent::init();
        }

        public function run()
        {
            $defaultView     = $this->defaultView;
            $inputId         = $this->inputId;
            $eventsUrl       = Yii::app()->createUrl('calendars/default/getEvents');
            //Set the goto date for calendar
            $startDate     = $this->startDate;
            $startDateAttr = explode('-', $startDate);
            $year          = $startDateAttr[0];
            $month         = intval($startDateAttr[1]) - 1;
            $day           = intval($startDateAttr[2]);

            $currentYear   = date('Y');
            $currentMonth  = intval(date('m')) - 1;
            $currentDay    = date('d');

            $maxCount      = CalendarItemsDataProvider::MAXIMUM_CALENDAR_ITEMS_COUNT;

            //Register full calendar script and css
            self::registerFullCalendarScriptAndCss();

            //Register qtip for event render
            $qtip = new ZurmoTip();
            $qtip->addQTip(".fc-event");

            $cs            = Yii::app()->getClientScript();
            $loadingText   = Zurmo::t('Core', 'Loading..');
            // Begin Not Coding Standard

            $script        = "$(document).on('ready', function() {
                                    $('#{$inputId}').fullCalendar({
                                                                    editable: false,
                                                                    header: {
                                                                                left: 'prev,next today',
                                                                                center: 'title',
                                                                                right: 'month,basicWeek,basicDay'
                                                                            },
                                                                     defaultView: '{$defaultView}',
                                                                     firstDay    :1,
                                                                     ignoreTimeZone:false,
                                                                     lazyFetching : false,
                                                                     loading: function(bool)
                                                                              {
                                                                                if (bool)
                                                                                {
                                                                                    $(this).makeLargeLoadingSpinner(true, '#{$inputId}');
                                                                                }
                                                                                else
                                                                                {
                                                                                    $(this).makeLargeLoadingSpinner(false, '#{$inputId}');
                                                                                }
                                                                              },
                                                                     eventSources: [
                                                                                      getCalendarEvents('{$eventsUrl}', '{$inputId}')
                                                                                   ],
                                                                     eventRender: function(event, element, view) {
                                                                                        element.qtip({
                                                                                            content: {
                                                                                                        text: '{$loadingText}',
                                                                                                        ajax: {
                                                                                                                    url: event.description,
                                                                                                                    type: 'get'
                                                                                                                },
                                                                                                        title: {
                                                                                                                  text: event.title,
                                                                                                                  button: 'Close'
                                                                                                               }
                                                                                                     },
                                                                                            style: {classes:'calendar-event-tooltip'},
                                                                                            show:{
                                                                                                    event: 'click'
                                                                                            },
                                                                                            hide: {
                                                                                                    event: 'false'
                                                                                                  },
                                                                                            position: {
                                                                                                        my: 'bottom center',
                                                                                                        at: 'top center',
                                                                                                        target: 'mouse',
                                                                                                        viewport: $('#calendar'),
                                                                                                        adjust: {
                                                                                                            mouse: false,
                                                                                                            scroll: false
                                                                                                        }
                                                                                                      }
                                                                                        });
                                                                                    },
                                                                     timeFormat: {
                                                                                    'month'    : '',
                                                                                    'basicDay': 'h:mm-{h:mm}tt',
                                                                                    'basicWeek': 'h:mm-{h:mm}tt'
                                                                                 }
                                                                    });
                                         $('#{$inputId}').fullCalendar('gotoDate', {$year}, {$month}, {$day});
                                         $('.fc-button-today').click(function() {
                                                                                    $('#{$inputId}').fullCalendar('changeView', 'basicDay');
                                                                                    $('#{$inputId}').fullCalendar('gotoDate', {$currentYear}, {$currentMonth}, {$currentDay});
                                                                                });
                                 });";

            // End Not Coding Standard
            $cs->registerScript('loadCalendarScript', $script, ClientScript::POS_END);
        }

        /**
         * Registers script and css file
         */
        protected static function registerFullCalendarScriptAndCss()
        {
            $cs            = Yii::app()->getClientScript();
            $baseScriptUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.core.widgets.assets'));
            $cs->registerScriptFile($baseScriptUrl . '/fullCalendar/fullcalendar.min.js', ClientScript::POS_HEAD);
            $cs->registerCssFile($baseScriptUrl . '/fullCalendar/fullcalendar.css');
        }
    }
?>