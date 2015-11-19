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
      * Model for saved calendar in the system.
      */
    class SavedCalendar extends OwnedSecurableItem
    {
        /**
         * Constants mapping the date range type in FullCalendar widget
         */
        const DATERANGE_TYPE_MONTH = 'month';

        const DATERANGE_TYPE_DAY   = 'basicDay';

        const DATERANGE_TYPE_WEEK  = 'basicWeek';

        public static $colorsArray = array('#315AB0', '#66367b', '#2c3e50', '#269a55', '#c0392b',
                                       '#e67e22', '#3498db', '#501a27', '#0c5b3f', '#c05d91');

        /**
         * @param string $name
         * @return string
         */
        public static function getByName($name)
        {
            return self::getByNameOrEquivalent('name', $name);
        }

        /**
         * @return string
         */
        public function __toString()
        {
            try
            {
                if (trim($this->name) == '')
                {
                    return Zurmo::t('Core', '(Unnamed)');
                }
                return $this->name;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        /**
         * @return string
         */
        public static function getModuleClassName()
        {
            return 'CalendarsModule';
        }

        /**
         * @return bool
         */
        public static function canSaveMetadata()
        {
            return true;
        }

        /**
         * @param string $language
         * @return array
         */
        public static function translatedAttributeLabels($language)
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            return array_merge(parent::translatedAttributeLabels($language), array(
                'description'        => Zurmo::t('ZurmoModule',     'Description',           array(), null, $language),
                'endAttributeName'   => Zurmo::t('CalendarsModule', 'End Attribute Field',   array(), null, $language),
                'location'           => Zurmo::t('ZurmoModule',     'Location',              array(), null, $language),
                'name'               => Zurmo::t('Core',            'Name',                  array(), null, $language),
                'startAttributeName' => Zurmo::t('CalendarsModule', 'Start Attribute Field', array(), null, $language),
                'timeZone'           => Zurmo::t('ZurmoModule',     'Time Zone',             array(), null, $language),
                'moduleClassName'    => Zurmo::t('Core',            'Module',                array(), null, $language),
                ));
        }

        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                    'description',
                    'location',
                    'moduleClassName',
                    'startAttributeName',
                    'endAttributeName',
                    'serializedData',
                    'timeZone',
                    'color'
                ),
                'relations' => array(
                    'sharedSubscribers'   => array(static::HAS_MANY, 'SavedCalendarSubscription'),
                ),
                'rules' => array(
                    array('name',             'required'),
                    array('name',             'type',    'type' => 'string'),
                    array('name',             'length',  'min'  => 1, 'max' => 64),
                    array('description',      'type',    'type' => 'string'),
                    array('location',         'type',    'type' => 'string'),
                    array('moduleClassName',  'type',    'type' => 'string'),
                    array('moduleClassName',  'length',  'max'   => 64),
                    array('startAttributeName',    'required'),
                    array('startAttributeName',    'type', 'type' => 'string'),
                    array('endAttributeName',    'type', 'type' => 'string'),
                    array('serializedData',      'type', 'type' => 'string'),
                    array('timeZone',         'type',    'type'  => 'string'),
                    array('timeZone',         'length',  'max'   => 64),
                    array('timeZone',         'UserDefaultTimeZoneDefaultValueValidator'),
                    array('timeZone',         'ValidateTimeZone'),
                    array('serializedData',   'type', 'type' => 'string'),
                    array('color',            'type',    'type' => 'string'),
                    array('color',            'length',  'min'  => 1, 'max' => 64),
                ),
                'elements' => array(
                    'moduleClassName' => 'CalendarModuleClassNameDropDown'
                ),
                'customFields' => array(),
                'defaultSortAttribute' => 'name',
                'noAudit' => array(
                ),
                'globalSearchAttributeNames' => array(
                    'name',
                ),
            );
            return $metadata;
        }

        /**
         * @return bool
         */
        public static function isTypeDeletable()
        {
            return true;
        }

        /**
         * @return bool
         */
        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        /**
         * @return string
         */
        public static function getGamificationRulesType()
        {
            return null;
        }
    }
?>