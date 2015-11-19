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
     * Base class for displaying meetings list view for a related model
     */
    abstract class UpcomingMeetingsRelatedListView extends SecuredRelatedListView
    {
        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata = array_merge($metadata, array(
                'perUser' => array(
                    'title' => "eval:Zurmo::t('MeetingsModule', 'Upcoming MeetingsModulePluralLabel List',
                               LabelUtil::getTranslationParamsForAllModules())",
                ),
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array(  'type'            => 'CreateFromRelatedListLink',
                                    'routeModuleId'   => 'eval:$this->moduleId',
                                    'routeParameters' => 'eval:$this->getCreateLinkRouteParameters()'),
                        ),
                    ),
                    'nonPlaceableAttributeNames' => array(
                        'latestDateTime',
                    ),
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'name', 'type' => 'Text', 'isLink' => true),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'startDateTime', 'type' => 'DateTime'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            ));
            return $metadata;
        }

        /**
         * @return array
         */
        protected function getCreateLinkRouteParameters()
        {
            return array(
                'relationAttributeName' => $this->getRelationAttributeName(),
                'relationModelId'       => $this->params['relationModel']->id,
                'relationModuleId'      => $this->params['relationModuleId'],
                'redirectUrl'           => $this->params['redirectUrl'],
            );
        }

        /**
         * @param null $stringTime
         * @return array
         */
        protected function makeSearchAttributeData($stringTime = null)
        {
            assert('is_string($stringTime) || $stringTime == null');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'startDateTime',
                    'operatorType'         => 'greaterThan',
                    'value'                => DateTimeUtil::
                                              convertDateIntoTimeZoneAdjustedDateTimeBeginningOfDay(
                                              DateTimeUtil::getFirstDayOfAMonthDate($stringTime))
                ),
                2 => array(
                    'attributeName'        => 'startDateTime',
                    'operatorType'         => 'lessThan',
                    'value'                => DateTimeUtil::
                                              convertDateIntoTimeZoneAdjustedDateTimeEndOfDay(
                                              DateTimeUtil::getLastDayOfAMonthDate($stringTime))
                ),
                3 => array(
                    'attributeName'        => 'logged',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => true
                ),
                4 => array(
                    'attributeName'        => 'logged',
                    'operatorType'         => 'isNull',
                    'value'                => null
                ),
                5 => array(
                    'attributeName'        => 'activityItems',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => (int)$this->params['relationModel']->getClassId('Item')
                )
            );
            $searchAttributeData['structure'] = '(1 and 2 and (3 or 4) and 5)';
            return $searchAttributeData;
        }

        protected function getSortAttributeForDataProvider()
        {
            return 'startDateTime';
        }

        protected function resolveSortDescendingForDataProvider()
        {
            return true;
        }

        /**
         * @return string
         */
        public static function getModuleClassName()
        {
            return 'MeetingsModule';
        }
    }
?>