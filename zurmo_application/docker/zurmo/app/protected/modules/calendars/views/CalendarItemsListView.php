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
     * Calendar Items list view display.
     */
    class CalendarItemsListView extends ListView
    {
        protected $calendarItems;

        protected $params;

        /**
         * Class constructor.
         * @param string $controllerId
         * @param string $moduleId
         * @param string $calendarItems
         * @param string $params
         */
        public function __construct($controllerId, $moduleId, $calendarItems, $params)
        {
            $this->controllerId      = $controllerId;
            $this->moduleId          = $moduleId;
            $this->calendarItems     = $calendarItems;
            $this->params            = $params;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'color', 'type' => 'CalendarColor'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'title', 'type' => 'CalendarItemTitle'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'start', 'type' => 'CalendarDate'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'end', 'type' => 'CalendarDate'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),

            );
            return $metadata;
        }

        /**
         * Gets pager css class.
         * @return string
         */
        protected static function getPagerCssClass()
        {
            return 'pager horizontal';
        }

        /**
         * Gets summary text.
         * @return string
         */
        protected static function getSummaryText()
        {
            return Zurmo::t('Core', '{start}-{end} of {count} result(s).');
        }

        /**
         * Gets grid view pager params.
         * @return array
         */
        protected function getCGridViewPagerParams()
        {
            return array(
                    'firstPageLabel'   => '<span>first</span>',
                    'prevPageLabel'    => '<span>previous</span>',
                    'nextPageLabel'    => '<span>next</span>',
                    'lastPageLabel'    => '<span>last</span>',
                    'paginationParams' => array_merge(GetUtil::getData(), $this->params),
                    'route'            => '/calendars/default/getDayEvents',
                    'class'            => 'SimpleListLinkPager',
                );
        }

        /**
         * Gets designer rule type.
         * @return null
         */
        public static function getDesignerRulesType()
        {
            return 'CalendarItemsListView';
        }

        /**
         * Gets data provider.
         * @return CalendarListItemsDataProvider
         */
        protected function getDataProvider()
        {
            return new CalendarListItemsDataProvider($this->calendarItems, $this->resolveConfigForDataProvider());
        }

        /**
         * Resolve configuration for data provider
         * @return array
         */
        protected function resolveConfigForDataProvider()
        {
            return array(
                            'pagination' => array(
                                                    'pageSize' => CalendarItemsDataProvider::MAXIMUM_CALENDAR_ITEMS_DISPLAYED_FOR_ANY_DATE,
                                                 )
                    );
        }

        /**
         * Gets grid view id.
         * @return string
         */
        public function getGridViewId()
        {
            $startDateArray = explode('-', $this->params['startDate']);
            return 'calendarDayEvents-' . $startDateArray[2];
        }

        /**
         * Renders the content by adding the scripts necessary for the view.
         * @return string
         */
        protected function renderContent()
        {
            $content = parent::renderContent();
            Yii::app()->getClientScript()->render($content);
            return $content;
        }

        /**
         * Render script for interaction.js
         */
        protected function renderScripts()
        {
            parent::renderScripts();
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.views.assets')) . '/interactions.js');
        }

        /**
         * Get the meta data and merge with standard CGridView column elements
         * to create a column array that fits the CGridView columns API
         */
         protected function getCGridViewColumns()
         {
            $columns    = array();
            $metadata   = static::getDefaultMetadata();
            foreach ($metadata['global']['panels'] as $panel)
            {
                foreach ($panel['rows'] as $row)
                {
                    foreach ($row['cells'] as $cell)
                    {
                        foreach ($cell['elements'] as $columnInformation)
                        {
                            $column = $this->processColumnInfoToFetchColumnData($columnInformation);
                            array_push($columns, $column);
                        }
                    }
                }
            }
            $lastColumn = $this->getCGridViewLastColumn();
            if (!empty($lastColumn))
            {
                array_push($columns, $lastColumn);
            }
            return $columns;
        }

        /**
         * Gets grid view last column.
         * @return array
         */
        protected function getCGridViewLastColumn()
        {
            return array(
                'class'           => 'ButtonColumn',
                'template'        => '{update}',
                'buttons' => array(
                    'update' => array(
                    'url'             => '$data["detailsUrl"]',
                    'imageUrl'        => false,
                    //todo @amit has to correct the class.
                    'options'         => array('class' => 'pencil', 'title' => 'Details'),
                    'label'           => '!'
                    ),
                ),
            );
        }
    }
?>