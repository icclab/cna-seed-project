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
     * A model to store information about jobs that are stuck. If a job is stuck 5 times in a row when it runs
     * then it will store the job type and quantity 5.  This does not mean if 'an existing job is detected', this means
     * if it is reset from being stuck and gets stuck 5 times in a row before running successfully again.
     */
    class StuckJob extends RedBeanModel
    {
        /**
         * @param string $type
         * @return mixed
         * @throws NotFoundException
         */
        public static function getByType($type)
        {
            assert('is_string($type) && $type != ""');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'type',
                    'operatorType'         => 'equals',
                    'value'                => $type,
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('StuckJob');
            $where  = RedBeanModelDataProvider::makeWhere('StuckJob', $searchAttributeData, $joinTablesAdapter);
            $models = self::getSubset($joinTablesAdapter, null, null, $where, null);
            if (count($models) > 1)
            {
                return $models[0];
            }
            if (count($models) == 0)
            {
                $stuckJob = new StuckJob();
                $stuckJob->type = $type;
                return $stuckJob;
            }
            return $models[0];
        }

        public function __toString()
        {
            if ($this->type == null)
            {
                return null;
            }
            return JobsUtil::resolveStringContentByType($this->type);
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'type',
                    'quantity',

                ),
                'rules' => array(
                    array('type', 'required'),
                    array('type', 'type', 'type' => 'string'),
                    array('type', 'length',  'min'  => 1, 'max' => 64),
                    array('quantity', 'type',    'type' => 'integer'),
                    array('quantity', 'default', 'value' => 0),
                    array('quantity', 'numerical', 'min' => 0),
                    array('quantity', 'required'),
                ),
                'defaultSortAttribute' => 'type',
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
                    'type'      => Zurmo::t('Core', 'Type',  array(), null, $language),
                    'quantity'  => Zurmo::t('Core', 'Quantity',  array(), null, $language),
                )
            );
        }

        /**
         * Returns the display name for the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getLabel($language = null)
        {
            return Zurmo::t('JobsManagerModule', 'Stuck Job', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @param null | string $language
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('JobsManagerModule', 'Stuck Jobs', array(), null, $language);
        }
    }
?>