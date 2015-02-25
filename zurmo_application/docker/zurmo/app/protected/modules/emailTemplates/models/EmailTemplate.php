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

    class EmailTemplate extends OwnedSecurableItem
    {
        const TYPE_WORKFLOW = 1;

        const TYPE_CONTACT  = 2;

        const BUILT_TYPE_PLAIN_TEXT_ONLY    = 1;

        const BUILT_TYPE_PASTED_HTML        = 2;

        const BUILT_TYPE_BUILDER_TEMPLATE   = 3;

        /**
         * Php caching for a single request
         * @var array
         */
        private static $cachedDataAndLabelsByType = array();

        public static function getByName($name)
        {
            return self::getByNameOrEquivalent('name', $name);
        }

        public static function getModuleClassName()
        {
            return 'EmailTemplatesModule';
        }

        protected static function getLabel($language = null)
        {
            return Zurmo::t('EmailTemplatesModule', 'Email Template', array(), null, $language);
        }

        /**
         * Returns the display name for plural of the model class.
         * @return dynamic label name based on module.
         * @param null | string $language
         */
        protected static function getPluralLabel($language = null)
        {
            return Zurmo::t('EmailTemplatesModule', 'Email Templates', array(), null, $language);
        }

        public static function getTypeDropDownArray()
        {
            return array(
                static::TYPE_WORKFLOW     => Zurmo::t('WorkflowsModule', 'Workflow'),
                static::TYPE_CONTACT      => Zurmo::t('ContactsModule',  'Contact'),
            );
        }

        public static function getBuiltTypeDropDownArray()
        {
            return array(
                static::BUILT_TYPE_BUILDER_TEMPLATE => Zurmo::t('EmailTemplatesModule', 'Template Builder'),
                static::BUILT_TYPE_PLAIN_TEXT_ONLY  => Zurmo::t('EmailTemplatesModule', 'Plain Text'),
                static::BUILT_TYPE_PASTED_HTML      => Zurmo::t('EmailTemplatesModule', 'HTML'),
            );
        }

        public static function renderNonEditableTypeStringContent($type)
        {
            assert('is_int($type) || $type == null');
            $dropDownArray = self::getTypeDropDownArray();
            if (!empty($dropDownArray[$type]))
            {
                return Yii::app()->format->text($dropDownArray[$type]);
            }
        }

        public static function getNonEditableBuiltTypeStringContent($builtType)
        {
            assert('is_int($builtType) || $builtType == null');
            $dropDownArray = self::getBuiltTypeDropDownArray();
            if (!empty($dropDownArray[$builtType]))
            {
                return Yii::app()->format->text($dropDownArray[$builtType]);
            }
        }

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

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'type',
                    'builtType',
                    'isDraft',
                    'modelClassName',
                    'name',
                    'subject',
                    'language',
                    'htmlContent',
                    'textContent',
                    'serializedData',
                    'isFeatured',
                ),
                'rules' => array(
                    array('type',                       'required'),
                    array('type',                       'type',    'type' => 'integer'),
                    array('type',                       'numerical'),
                    array('isDraft',                    'type',     'type' => 'boolean'),
                    array('isDraft',                    'SetToTrueForBuilderTemplateElseFalseValidator'),
                    array('builtType',                  'required'),
                    array('builtType',                  'type',     'type' => 'integer'),
                    array('builtType',                  'numerical'),
                    array('modelClassName',             'required'),
                    array('modelClassName',             'type',   'type' => 'string'),
                    array('modelClassName',             'length', 'max' => 64),
                    array('modelClassName',             'ModelExistsAndIsReadableValidator'),
                    array('name',                       'required'),
                    array('name',                       'type',    'type' => 'string'),
                    array('name',                       'length',  'min'  => 1, 'max' => 64),
                    array('subject',                    'required'),
                    array('subject',                    'type',    'type' => 'string'),
                    array('subject',                    'length',  'min'  => 1, 'max' => 255),
                    array('language',                   'type',    'type' => 'string'),
                    array('language',                   'length',  'min' => 2, 'max' => 2),
                    array('language',                   'SetToUserDefaultLanguageValidator'),
                    array('htmlContent',                'type',    'type' => 'string'),
                    array('textContent',                'type',    'type' => 'string'),
                    array('htmlContent',                'StripDummyHtmlContentFromOtherwiseEmptyFieldValidator'),
                    array('textContent',                'EmailTemplateAtLeastOneContentAreaRequiredValidator'),
                    array('htmlContent',                'EmailTemplateMergeTagsValidator'),
                    array('textContent',                'EmailTemplateMergeTagsValidator'),
                    array('serializedData',             'type', 'type' => 'string'),
                    array('serializedData',             'EmailTemplateSerializedDataValidator'),
                    array('isFeatured',                 'type',     'type'  => 'boolean'),
                ),
                'elements' => array(
                    'htmlContent'                   => 'TextArea',
                    'textContent'                   => 'TextArea',
                ),
                'relations' => array(
                    'files'                         => array(static::HAS_MANY,  'FileModel', static::OWNED,
                                                            static::LINK_TYPE_POLYMORPHIC, 'relatedModel'),
                ),
            );
            return $metadata;
        }

        /**
         * @param $type
         * @param bool $includeDrafts
         * @return Array
         */
        public static function getByType($type, $includeDrafts = false)
        {
            assert('is_int($type)');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'type',
                    'operatorType'         => 'equals',
                    'value'                => $type,
                ),
                2 => array(
                    'attributeName'         => 'modelClassName',
                    'operatorType'          => 'isNotNull',
                    'value'                 => null,
                ),
            );
            $searchAttributeData['structure'] = '1 and 2';
            if (!$includeDrafts)
            {
                $searchAttributeData['clauses'][3] = array(
                'attributeName'         => 'isDraft',
                'operatorType'          => 'equals',
                'value'                 => intval($includeDrafts),
                );
                $searchAttributeData['structure'] .= ' and 3';
            }
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, null, $where, 'name');
        }

        /**
         * Returns the SearchAttributeData array to search for all predefinedBuilderTemplates
         * @return array
         */
        public static function getPredefinedBuilderTemplatesSearchAttributeData()
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'         => 'builtType',
                    'operatorType'          => 'equals',
                    'value'                 => static::BUILT_TYPE_BUILDER_TEMPLATE,
                ),
                2 => array(
                    'attributeName'         => 'modelClassName',
                    'operatorType'          => 'isNull',
                    'value'                 => null,
                ),
            );
            $searchAttributeData['structure'] = '1 and 2';
            return $searchAttributeData;
        }

        /**
         * Returns PredefinedTemplates
         * @return Array of EmailTemplate models
         */
        public static function getPredefinedBuilderTemplates()
        {
            $searchAttributeData              = static::getPredefinedBuilderTemplatesSearchAttributeData();
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, null, $where, 'name');
        }

        protected static function bypassReadPermissionsOptimizationToSqlQueryBasedOnWhere($where)
        {
            $q                 = DatabaseCompatibilityUtil::getQuote();
            $builtTemplateType = static::BUILT_TYPE_BUILDER_TEMPLATE;
            $isNull            = SQLOperatorUtil::resolveOperatorAndValueForNullOrEmpty('isNull');
            $expectedWhere     = "({$q}emailtemplate{$q}.{$q}builttype{$q} = {$builtTemplateType}) and " .
                                 "({$q}emailtemplate{$q}.{$q}modelclassname{$q} {$isNull})";
            if ($where == $expectedWhere)
            {
                return true;
            }
            return parent::bypassReadPermissionsOptimizationToSqlQueryBasedOnWhere($where);
        }

        public function checkPermissionsHasAnyOf($requiredPermissions, User $user = null)
        {
            if ($user == null)
            {
                $user = Yii::app()->user->userModel;
            }
            $effectivePermissions = $this->getEffectivePermissions($user);
            if (($effectivePermissions & $requiredPermissions) == 0)
            {
                $this->setTreatCurrentUserAsOwnerForPermissions(true);
                if (!$this->isPredefinedBuilderTemplate())
                {
                    throw new AccessDeniedSecurityException($user, $requiredPermissions, $effectivePermissions);
                }
                else
                {
                    //Do nothing
                }
            }
        }

        /**
         * Returns the SearchAttributeData array to search for all previouslyCreatedBuilderTemplates
         * @param null $modelClassName
         * @param bool $includeDrafts
         * @return array
         */
        public static function getPreviouslyCreatedBuilderTemplateSearchAttributeData($modelClassName = null, $includeDrafts = false)
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'         => 'builtType',
                    'operatorType'          => 'equals',
                    'value'                 => static::BUILT_TYPE_BUILDER_TEMPLATE,
                ));
            $searchAttributeData['structure'] = '1';
            if (!$includeDrafts)
            {
                $searchAttributeData['clauses'][2] = array(
                    'attributeName'         => 'isDraft',
                    'operatorType'          => 'equals',
                    'value'                 => intval($includeDrafts),
                );
                $searchAttributeData['structure'] .= ' and 2';
            }
            if (isset($modelClassName))
            {
                $searchAttributeData['clauses'][3] = array(
                    'attributeName'        => 'modelClassName',
                    'operatorType'         => 'equals',
                    'value'                => $modelClassName,
                );
            }
            else
            {
                // if moduleClassName isn't give then at least exclude the pre-defined ones.
                $searchAttributeData['clauses'][3] = array(
                    'attributeName'         => 'modelClassName',
                    'operatorType'          => 'isNotNull',
                    'value'                 => null,
                );
            }
            $searchAttributeData['structure'] .= ' and 3';
            return $searchAttributeData;
        }

        /**
         * @param null $modelClassName
         * @param bool $includeDrafts
         * @param null $limit number of previously created templates
         * @return Array of EmailTemplate models
         */
        public static function getPreviouslyCreatedBuilderTemplates($modelClassName = null, $includeDrafts = false, $limit = null)
        {
            $searchAttributeData    = static::getPreviouslyCreatedBuilderTemplateSearchAttributeData($modelClassName, $includeDrafts);
            $joinTablesAdapter      = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where                  = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            return self::getSubset($joinTablesAdapter, null, $limit, $where, 'name');
        }

        /**
         * @param int $type
         * @return array
         */
        public static function getDataAndLabelsByType($type)
        {
            assert('is_int($type)');
            if (isset(self::$cachedDataAndLabelsByType[$type]))
            {
                return self::$cachedDataAndLabelsByType[$type];
            }
            $dataAndLabels = array();
            $emailTemplates = static::getByType($type);
            foreach ($emailTemplates as $emailTemplate)
            {
                $dataAndLabels[$emailTemplate->id] = strval($emailTemplate);
            }
            self::$cachedDataAndLabelsByType[$type] = $dataAndLabels;
            return self::$cachedDataAndLabelsByType[$type];
        }

        public static function getGamificationRulesType()
        {
            return 'EmailTemplateGamification';
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'modelClassName'  => Zurmo::t('Core',                'Module',       null, null, $language),
                    'language'        => Zurmo::t('Core',                'Language',     null, null, $language),
                    'htmlContent'     => Zurmo::t('EmailMessagesModule', 'Html Content', null, null, $language),
                    'name'            => Zurmo::t('Core',                'Name',         null, null, $language),
                    'subject'         => Zurmo::t('Core',                'Subject',      null, null, $language),
                    'type'            => Zurmo::t('Core',                'Type',         null, null, $language),
                    'textContent'     => Zurmo::t('EmailMessagesModule', 'Text Content', null, null, $language),
                )
            );
        }

        public function isContactTemplate()
        {
            return ($this->type == static::TYPE_CONTACT);
        }

        public function isWorkflowTemplate()
        {
            return ($this->type == static::TYPE_WORKFLOW);
        }

        public function isPlainTextTemplate()
        {
            return ($this->builtType == static::BUILT_TYPE_PLAIN_TEXT_ONLY);
        }

        public function isPastedHtmlTemplate()
        {
            return ($this->builtType == static::BUILT_TYPE_PASTED_HTML);
        }

        public function isBuilderTemplate()
        {
            return ($this->builtType == static::BUILT_TYPE_BUILDER_TEMPLATE);
        }

        public function isPredefinedBuilderTemplate()
        {
            return ($this->isBuilderTemplate() && empty($this->modelClassName));
        }

        public function __set($attributeName, $value)
        {
            parent::__set($attributeName, $value);
            // we exclude predefined because:
            // a- we don't want htmlContent compiled for those. It wont be used anywhere anyway.
            // b- Using console installer we get errors due to getAssetManager(used in BuilderCanvasElement)
            //    not being available in CConsoleApplication
            if ($attributeName == 'serializedData' &&
                $this->isBuilderTemplate() &&
                !$this->isPredefinedBuilderTemplate() &&
                ArrayUtil::getArrayValue(CJSON::decode($this->serializedData), 'dom'))
            {
                $this->htmlContent  = EmailTemplateSerializedDataToHtmlUtil::resolveHtmlBySerializedData($this->serializedData, false);
            }
        }

        public function validate(array $attributeNames = null, $ignoreRequiredValidator = false, $validateAll = false)
        {
            if ($validateAll == false)
            {
                $metadata               = static::getMetadata();
                $excludedMembers        = array('textContent');
                $members                = $metadata['EmailTemplate']['members'];
                // ignore content fields, we validate them inside the wizard form anyway.
                $attributeNames         = array_diff($members, $excludedMembers);
            }
            return parent::validate($attributeNames, $ignoreRequiredValidator);
        }
    }
?>