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

    /**
     * Helper class for adapting an emailTemplate to a EmailTemplateWizardForm
     */
    class EmailTemplateToWizardFormAdapter
    {
        /**
         * @var EmailTemplate
         */
        protected $emailTemplate;

        /**
         * @param EmailTemplate $emailTemplate
         */
        public function __construct(EmailTemplate $emailTemplate)
        {
            $this->emailTemplate = $emailTemplate;
        }

        public static function getFormClassNameByBuiltType($builtType)
        {
            assert('is_string($builtType) || is_int($builtType)');
            $builtType = intval($builtType);
            if ($builtType == EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE)
            {
                return 'BuilderEmailTemplateWizardForm';
            }
            return 'ClassicEmailTemplateWizardForm';
        }

        public function makeFormByBuiltType()
        {
            $wizardFormClassName = static::getFormClassNameByBuiltType($this->emailTemplate->builtType);
            return $this->makeWizardFormByClassName($wizardFormClassName);
        }

        public function makeWizardFormByClassName($formModelClassName)
        {
            $formModel       = new $formModelClassName();
            $this->setCommonAttributes($formModel);
            $this->setUncommonAttributes($formModel);
            return $formModel;
        }

        /**
         * @param EmailTemplateWizardForm $formModel
         */
        protected function setCommonAttributes(EmailTemplateWizardForm $formModel)
        {
            $this->resolveEmailTemplateMembers($formModel);
            $this->resolveOwnerIdAndName($formModel);
            $this->resolveIsNew($formModel);
            $this->resolveExplicitReadWritePermissions($formModel);
            $this->resolveIsDraftDefault($formModel);
            $this->resolveFiles($formModel);
        }

        protected function resolveFiles(EmailTemplateWizardForm $formModel)
        {
            foreach ($this->emailTemplate->files as $file)
            {
                $formModel->files[] = $file;
            }
        }

        protected function resolveEmailTemplateMembers(EmailTemplateWizardForm $formModel)
        {
            $metadata               = EmailTemplate::getMetadata();
            $members                = $metadata['EmailTemplate']['members'];
            foreach ($members as $member)
            {
                if (property_exists($formModel, $member) && isset($this->emailTemplate->$member))
                {
                    $emailTemplateMemberValue = $this->emailTemplate->$member;
                    if ($member == 'isDraft')
                    {
                        $emailTemplateMemberValue = (bool)$emailTemplateMemberValue;
                    }
                    $formModel->$member = $emailTemplateMemberValue;
                }
            }
        }

        protected function resolveOwnerIdAndName(EmailTemplateWizardForm $formModel)
        {
            if ($this->emailTemplate->owner->id > 0)
            {
                $formModel->ownerId      = (int)$this->emailTemplate->owner->id;
                $formModel->ownerName    = strval($this->emailTemplate->owner);
            }
        }

        protected function resolveIsNew(EmailTemplateWizardForm $formModel)
        {
            if ($this->emailTemplate->id < 0)
            {
                $formModel->setIsNew();
            }
            else
            {
                $formModel->id = $this->emailTemplate->id;
            }
        }

        protected function resolveExplicitReadWritePermissions(EmailTemplateWizardForm $formModel)
        {
            $explicitReadWritePermissions   = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($this->emailTemplate);
            $formModel->setExplicitReadWriteModelPermissions($explicitReadWritePermissions);
        }

        protected function resolveIsDraftDefault(EmailTemplateWizardForm $formModel)
        {
            if (!isset($formModel->isDraft))
            {
                $formModel->isDraft  = $this->emailTemplate->isBuilderTemplate();
            }
        }

        /**
         * @param EmailTemplateWizardForm $formModel
         */
        protected function setUncommonAttributes(EmailTemplateWizardForm $formModel)
        {
            // handle any custom mappings between EmailTemplateWizardForm and EmailTemplate model here.
            if ($this->emailTemplate->isBuilderTemplate())
            {
                $unserializedData   = CJSON::decode($this->emailTemplate->serializedData);
                $formModel->baseTemplateId = $unserializedData['baseTemplateId'];
            }
        }
    }
?>