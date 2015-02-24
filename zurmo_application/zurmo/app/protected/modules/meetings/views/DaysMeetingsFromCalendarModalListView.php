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
     * Special modal view utilized by calendar to show meetings on a particular day.
     */
    class DaysMeetingsFromCalendarModalListView extends ListView
    {
        protected $redirectUrl;

        protected $ownerOnly = false;

        protected $relationModuleId;

        /**
         * @param string $controllerId
         * @param string $moduleId
         * @param string $stringTime
         * @param string $redirectUrl
         * @param bool $ownerOnly
         * @param null|RedBeanModel $relationModel
         */
        public function __construct($controllerId, $moduleId, $stringTime, $redirectUrl,
                                    $ownerOnly = false, $relationModel = null, $relationModuleId = null)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($stringTime)');
            assert('is_string($redirectUrl) || $redirectUrl == null');
            assert('is_bool($ownerOnly)');
            assert('$relationModel == null || $relationModel instanceof RedBeanModel');
            assert('$relationModuleId == null || is_string($relationModuleId)');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->stringTime             = $stringTime;
            $this->redirectUrl            = $redirectUrl;
            $this->modelClassName         = 'Meeting';
            $this->gridId                 = 'days-meetings-list-view';
            $this->rowsAreSelectable      = false;
            $this->ownerOnly              = $ownerOnly;
            $this->relationModel          = $relationModel;
            $this->relationModuleId       = $relationModuleId;
        }

        protected static function getPagerCssClass()
        {
            return 'pager horizontal';
        }

        protected function getCGridViewPagerParams()
        {
            return array(
                    'firstPageLabel'   => '<span>first</span>',
                    'prevPageLabel'    => '<span>previous</span>',
                    'nextPageLabel'    => '<span>next</span>',
                    'lastPageLabel'    => '<span>last</span>',
                    'class'            => 'SimpleListLinkPager',
                    'paginationParams' => GetUtil::getData(),
                    'route'            => 'default/daysMeetingsFromCalendarModalList',
                );
        }

        /**
         * Override to not run global eval, since it causes doubling up of ajax requests on the pager.
         * (non-PHPdoc)
         * @see ListView::getCGridViewAfterAjaxUpdate()
         */
        protected function getCGridViewAfterAjaxUpdate()
        {
            // Begin Not Coding Standard
            return 'js:function(id, data) {
                        processAjaxSuccessError(id, data);
                    }';
            // End Not Coding Standard
        }

        /**
         * Override to remove action buttons.
         */
        protected function getCGridViewLastColumn()
        {
            return array();
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'nonPlaceableAttributeNames' => array(
                        'latestDateTime',
                    ),
                    'derivedAttributeTypes' => array(
                        'MeetingDaySummary',
                    ),
                    'gridViewType' => RelatedListView::GRID_VIEW_TYPE_STACKED,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'MeetingDaySummary'),
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

        protected function getGridViewWidgetPath()
        {
            $resolvedMetadata = $this->getResolvedMetadata();
            if (isset($resolvedMetadata['global']['gridViewType']) &&
                     $resolvedMetadata['global']['gridViewType'] == RelatedListView::GRID_VIEW_TYPE_STACKED)
             {
                 return 'application.core.widgets.StackedExtendedGridView';
             }

            return parent::getGridViewWidgetPath();
        }

        /**
         * Override to handle security/access resolution on links.
         */
        public function getLinkString($attributeString, $attribute)
        {
            $string  = 'ActionSecurityUtil::resolveLinkToEditModelForCurrentUser("' . $attributeString . '", ';
            $string .= '$data, "' . $this->getActionModuleClassName() . '", ';
            $string .= '"' . $this->getGridViewActionRoute('edit') . '", "' . $this->redirectUrl . '")';
            return $string;
        }

        protected function makeSearchAttributeData()
        {
            assert('!($this->ownerOnly && $this->relationModel != null)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'startDateTime',
                    'operatorType'         => 'greaterThanOrEqualTo',
                    'value'                => DateTimeUtil::
                                                convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay($this->stringTime)
                ),
                2 => array(
                    'attributeName'        => 'startDateTime',
                    'operatorType'         => 'lessThanOrEqualTo',
                    'value'                => DateTimeUtil::
                                                convertDateIntoTimeZoneAdjustedDateTimeEndOfDay($this->stringTime)
                )
                );
            $searchAttributeData['structure'] = '(1 and 2)';
            if ($this->ownerOnly)
            {
                $searchAttributeData['clauses'][3] =
                array(
                    'attributeName'        => 'owner',
                    'operatorType'         => 'equals',
                    'value'                => Yii::app()->user->userModel->id,
                );
                $searchAttributeData['structure'] = '(1 and 2 and 3)';
            }
            //The assertion above ensures that either ownerOnly or relationModel is populated but not both.
            if ($this->relationModel != null)
            {
                $searchAttributeData['clauses'][3] =
                array(
                    'attributeName'        => 'activityItems',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => (int)$this->relationModel->getClassId('Item')
                );
                $searchAttributeData['structure'] = '(1 and 2 and 3)';
            }
            return $searchAttributeData;
        }

        protected function makeDataProviderBySearchAttributeData($searchAttributeData)
        {
            assert('is_array($searchAttributeData)');
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType('subListPageSize');
            return new RedBeanModelDataProvider( $this->modelClassName, null, false,
                                                                $searchAttributeData, array(
                                                                    'pagination' => array(
                                                                        'pageSize' => $pageSize,
                                                                    )
                                                                ));
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        public function getDataProvider()
        {
            if ($this->dataProvider == null)
            {
                $this->dataProvider = $this->makeDataProviderBySearchAttributeData($this->makeSearchAttributeData());
            }
            return $this->dataProvider;
        }

        protected function getCreateMeetingUrl()
        {
            if (!$this->relationModel && !$this->relationModuleId)
            {
                return Yii::app()->createUrl('/meetings/default/createMeeting',
                                             array('redirectUrl' => $this->redirectUrl, 'startDate' => $this->stringTime));
            }
            else
            {
                $params = array(
                    'relationAttributeName' => get_class($this->relationModel),
                    'relationModelId'       => $this->relationModel->id,
                    'relationModuleId'      => $this->relationModuleId,
                    'startDate'             => $this->stringTime,
                    'redirectUrl'           => $this->redirectUrl,
                );
                return Yii::app()->createUrl($this->moduleId . '/' .
                                        $this->controllerId . '/createFromRelationAndStartDate/', $params);
            }
        }

        /**
         * Override to add link for meeting creation
         */
        protected function renderContent()
        {
            $content = '';
            if (RightsUtil::doesUserHaveAllowByRightName('MeetingsModule', MeetingsModule::getCreateRight(),
                Yii::app()->user->userModel))
            {
                $spanContent = ZurmoHtml::tag('span', array('class' => 'z-label'), Zurmo::t('MeetingsModule', 'Create Meeting'));
                $linkContent = ZurmoHtml::link($spanContent, $this->getCreateMeetingUrl(), array('class' => 'secondary-button'));
                $divContent = ZurmoHtml::tag('div', array('class' => 'portlet-toolbar'), $linkContent);
                $content = ZurmoHtml::tag('div', array('class' => 'view-toolbar-container clearfix'), $divContent);
            }
            $content .= parent::renderContent();
            return $content;
        }
    }
?>
