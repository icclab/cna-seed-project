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
     * For exporting large amount of data, we store export information in database,
     * and ExportJob will use this data to regenerate dataprovider, get data, and
     * finally generate export file in desired format.
     */
    class ExportItem extends OwnedSecurableItem
    {
        public static function getUncompletedItems()
        {
            return self::getSubset(null, null, null, "isCompleted = 0");
        }

        public function __toString()
        {
            return Zurmo::t('Core', '(Unnamed)');
        }

        /**
         * @see OwnedSecurableItem::getModifiedSignalAttribute()
         * @return string
         */
        protected static function getModifiedSignalAttribute()
        {
            return 'exportFileName';
        }

        public static function getModuleClassName()
        {
            return 'ExportModule';
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'isCompleted',
                    'exportFileType',
                    'exportFileName',
                    'modelClassName',
                    'processOffset',
                    'serializedData',
                    'isJobRunning',
                    'cancelExport'
                ),
                'relations' => array(
                    'exportFileModel' => array(static::HAS_ONE,  'ExportFileModel', static::OWNED),
                ),
                'rules' => array(
                    array('isCompleted',      'boolean'),
                    array('exportFileType',   'required'),
                    array('exportFileType',   'type', 'type' => 'string'),
                    array('exportFileName',   'required'),
                    array('exportFileName',   'type', 'type' => 'string'),
                    array('modelClassName',   'required'),
                    array('modelClassName',   'type', 'type' => 'string'),
                    array('processOffset',    'type',    'type' => 'integer'),
                    array('processOffset',    'default',    'value' => 0),
                    array('serializedData',   'required'),
                    array('serializedData',   'type', 'type' => 'longtext'),
                    //4,294,967,295 is the max storage capacity for longtext // Not Coding Standard
                    // keeping this rule here for safe-keeping
                    array('serializedData',   'length', 'max' => 4294967290),
                    array('isJobRunning',     'boolean'),
                    array('cancelExport',     'boolean'),
                ),
                'defaultSortAttribute' => 'id',
                'noAudit' => array(
                    'modelClassName',
                    'processOffset',
                    'serializedData',
                    'exportFileModel',
                ),
                'elements' => array(
                    'exportFileName'  => 'ExportFileName'
                ),
                'globalSearchAttributeNames' => array(
                    'exportFileName',
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'isCompleted'    => Zurmo::t('ExportModule', 'Is Completed',     array(), null, $language),
                    'exportFileName' => Zurmo::t('ExportModule', 'Export File Name', array(), null, $language),
                    'exportFileType' => Zurmo::t('ExportModule', 'Export File Type', array(), null, $language),
                    'isJobRunning'   => Zurmo::t('ExportModule', 'Is Job Running',   array(), null, $language),
                    'cancelExport'   => Zurmo::t('ExportModule', 'Cancel Export',   array(), null, $language),
                )
            );
        }

        protected function afterSave()
        {
            if ($this->isNewModel && !$this->isCompleted)
            {
                Yii::app()->jobQueue->add('Export', 5);
            }
            parent::afterSave();
        }

        /**
         * Get the export items which got cancelled.
         * @return array
         */
        public static function getCancelledItems()
        {
            return self::getSubset(null, null, null, "cancelExport = 1");
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }
    }
?>