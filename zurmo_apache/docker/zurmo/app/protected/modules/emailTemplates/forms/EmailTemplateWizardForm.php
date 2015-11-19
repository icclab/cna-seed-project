<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    abstract class EmailTemplateWizardForm extends WizardForm
    {
        const GENERAL_DATA_VALIDATION_SCENARIO              = 'ValidateForGeneralData';

        const SELECT_BASE_TEMPLATE_VALIDATION_SCENARIO      = 'ValidateForSelectBaseTemplate';

        const SERIALIZED_DATA_VALIDATION_SCENARIO           = 'ValidateForSerializedData';

        const PLAIN_AND_RICH_CONTENT_VALIDATION_SCENARIO    = 'ValidateForPlainAndRichContent';

        /**
         * @var integer
         */
        public $type;

        /**
         * @var integer
         */
        public $builtType;

        /**
         * @var boolean
         */
        public $isDraft;

        /**
         * @var string
         */
        public $modelClassName;

        /**
         * @var string
         */
        public $name;

        /**
         * @var string
         */
        public $language;

        /**
         * @var string
         */
        public $subject;

        /**
         * @var string
         */
        public $htmlContent;

        /**
         * @var string
         */
        public $textContent;

        /**
         * @var array
         */
        public $files = array();

        /**
         * @var integer
         */
        public $ownerId;

        /**
         * @var string
         */
        public $ownerName;

        /**
         * Object containing information on how to setup permissions for the new models that are created during the
         * import process.
         * @var object ExplicitReadWriteModelPermissions
         * @see ExplicitReadWriteModelPermissions
         */
        protected $explicitReadWriteModelPermissions;

        /**
         * @var integer
         */
        public $baseTemplateId;

        /**
         * @var string
         */
        public $serializedData;

        /**
         * @return array
         */
        public function rules()
        {
            return array(
                array('ownerId',            'type',     'type' => 'integer', ),
                array('ownerId',            'required', 'on' => static::GENERAL_DATA_VALIDATION_SCENARIO),
                array('ownerName',          'required', 'on' => static::GENERAL_DATA_VALIDATION_SCENARIO),
                array('type',               'required', 'on' => static::GENERAL_DATA_VALIDATION_SCENARIO),
                array('type',               'type',     'type' => 'integer'),
                array('type',               'numerical'),
                array('isDraft',            'type',     'type' => 'boolean'),
                array('isDraft',            'SetToTrueForBuilderTemplateElseFalseValidator',
                                                                    'on' => static::GENERAL_DATA_VALIDATION_SCENARIO),
                array('builtType',          'required', 'on' => static::GENERAL_DATA_VALIDATION_SCENARIO),
                array('builtType',          'type',     'type' => 'integer'),
                array('builtType',          'numerical'),
                array('modelClassName',     'required', 'on' => static::GENERAL_DATA_VALIDATION_SCENARIO),
                array('modelClassName',     'type',   'type' => 'string'),
                array('modelClassName',     'length', 'max' => 64),
                array('modelClassName',     'ModelExistsAndIsReadableValidator',
                                                                    'on' => static::GENERAL_DATA_VALIDATION_SCENARIO),
                array('name',               'required', 'on' => static::GENERAL_DATA_VALIDATION_SCENARIO),
                array('name',               'type',    'type' => 'string'),
                array('name',               'length',  'min'  => 1, 'max' => 64),
                array('subject',            'required', 'on' => static::GENERAL_DATA_VALIDATION_SCENARIO),
                array('subject',            'type',    'type' => 'string'),
                array('subject',            'length',  'min'  => 1, 'max' => 255),
                array('language',           'type',    'type' => 'string'),
                array('language',           'length',  'min' => 2, 'max' => 2),
                array('language',           'SetToUserDefaultLanguageValidator',
                                                                        'on' => static::GENERAL_DATA_VALIDATION_SCENARIO),
                array('htmlContent',        'type',    'type' => 'string'),
                array('textContent',        'type',    'type' => 'string'),
                array('htmlContent',        'StripDummyHtmlContentFromOtherwiseEmptyFieldValidator',
                                                                        'on' => static::PLAIN_AND_RICH_CONTENT_VALIDATION_SCENARIO),
                array('htmlContent',        'EmailTemplateAtLeastOneContentAreaRequiredValidator',
                                                                        'on' => static::PLAIN_AND_RICH_CONTENT_VALIDATION_SCENARIO),
                array('textContent',        'EmailTemplateAtLeastOneContentAreaRequiredValidator',
                                                                        'on' => static::PLAIN_AND_RICH_CONTENT_VALIDATION_SCENARIO),
                array('htmlContent',        'EmailTemplateMergeTagsValidator',
                                                                        'on' => static::PLAIN_AND_RICH_CONTENT_VALIDATION_SCENARIO),
                array('textContent',        'EmailTemplateMergeTagsValidator',
                                                                        'on' => static::PLAIN_AND_RICH_CONTENT_VALIDATION_SCENARIO),
                array('baseTemplateId',     'type',     'type' => 'integer'),
                array('baseTemplateId',     'required', 'on' => static::SELECT_BASE_TEMPLATE_VALIDATION_SCENARIO),
                array('serializedData',     'required', 'on' => static::SERIALIZED_DATA_VALIDATION_SCENARIO),
                array('serializedData',     'type', 'type' => 'string'),
                array('serializedData',     'EmailTemplateSerializedDataValidator',
                                                                'on' => static::SERIALIZED_DATA_VALIDATION_SCENARIO),
            );
        }

        /**
         * @return object
         */
        public function getExplicitReadWriteModelPermissions()
        {
            return $this->explicitReadWriteModelPermissions;
        }

        /**
         * @param ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions
         */
        public function setExplicitReadWriteModelPermissions(ExplicitReadWriteModelPermissions $explicitReadWriteModelPermissions)
        {
            $this->explicitReadWriteModelPermissions = $explicitReadWriteModelPermissions;
        }

        public function isContactTemplate()
        {
            return ($this->type == EmailTemplate::TYPE_CONTACT);
        }

        public function isWorkflowTemplate()
        {
            return ($this->type == EmailTemplate::TYPE_WORKFLOW);
        }

        public function isPlainTextTemplate()
        {
            return ($this->builtType == EmailTemplate::BUILT_TYPE_PLAIN_TEXT_ONLY);
        }

        public function isPastedHtmlTemplate()
        {
            return ($this->builtType == EmailTemplate::BUILT_TYPE_PASTED_HTML);
        }

        public function isBuilderTemplate()
        {
            return ($this->builtType == EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE);
        }
    }
?>