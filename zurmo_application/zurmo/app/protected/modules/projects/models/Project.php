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

    class Project extends OwnedSecurableItem
    {
        /*
         * Constants for project status
         */
        const STATUS_ACTIVE     = 1;

        const STATUS_ARCHIVED   = 2;

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
            return 'ProjectsModule';
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
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                       'status' => Zurmo::t('ZurmoModule', 'Status',  array(), null, $language),
                       'tasks'  => Zurmo::t('TasksModule', 'TasksModulePluralLabel', $params, null, $language),
                       'accounts'  => Zurmo::t('AccountsModule', 'AccountsModulePluralLabel', $params, null, $language),
                       'contacts'  => Zurmo::t('ContactsModule', 'ContactsModulePluralLabel', $params, null, $language),
                       'opportunities'  => Zurmo::t('OpportunitiesModule', 'OpportunitiesModulePluralLabel', $params, null, $language)
                    )
                );
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
                    'status'
                ),
                'relations' => array(
                    'opportunities'    => array(static::MANY_MANY, 'Opportunity'),
                    'contacts'         => array(static::MANY_MANY, 'Contact'),
                    'accounts'         => array(static::MANY_MANY, 'Account'),
                    'tasks'            => array(static::HAS_MANY,  'Task'),
                    'auditEvents'      => array(static::HAS_MANY,  'ProjectAuditEvent'),
                ),
                'rules' => array(
                    array('name',           'required'),
                    array('name',           'type',    'type' => 'string'),
                    array('name',           'length',  'min'  => 3, 'max' => 64),
                    array('description',    'type',    'type' => 'string'),
                    array('status',         'type',    'type' => 'integer'),
                    array('status',         'default', 'value' => Project::STATUS_ACTIVE),
                    array('status',         'required'),
                ),
                'elements' => array(
                    'status' => 'ProjectStatusDropDown',
                ),
                'customFields' => array(
                ),
                'defaultSortAttribute' => 'name',
                'noAudit' => array(
                ),
                'nonConfigurableAttributes' => array()
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
            return 'ProjectGamification';
        }

        /**
         * @return array of status values and labels
         */
        public static function getStatusDropDownArray()
        {
            return array(
                self::STATUS_ACTIVE    => Zurmo::t('Core', 'Active'),
                self::STATUS_ARCHIVED  => Zurmo::t('ZurmoModule', 'Archived'),
            );
        }

        /**
         * Delete task associated to project as well
         * @return bool
         */
        protected function beforeDelete()
        {
            if (parent::beforeDelete())
            {
                foreach ($this->tasks as $task)
                {
                    $task->delete();
                }
                foreach ($this->auditEvents as $auditEvent)
                {
                    $auditEvent->delete();
                }
                return true;
            }
            return false;
        }

        /**
         * Handle audit of projects after save
         */
        protected function afterSave()
        {
            if ($this->getIsNewModel())
            {
                ProjectAuditEvent::logAuditEvent(ProjectAuditEvent::PROJECT_CREATED, $this, $this->name);
            }
            elseif ($this->status == Project::STATUS_ARCHIVED)
            {
                ProjectAuditEvent::logAuditEvent(ProjectAuditEvent::PROJECT_ARCHIVED, $this, $this->name);
            }
            parent::afterSave();
        }
    }
?>