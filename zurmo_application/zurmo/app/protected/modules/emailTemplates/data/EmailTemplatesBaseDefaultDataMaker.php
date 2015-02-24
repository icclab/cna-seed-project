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

    abstract class EmailTemplatesBaseDefaultDataMaker extends DefaultDataMaker
    {
        protected $importedImages = array();

        protected function makeBuilderPredefinedEmailTemplate($name, $unserializedData, $subject = null, $modelClassName = null,
                                                    $language = null, $type = null, $isDraft = 0, $textContent = null,
                                                    $htmlContent = null)
        {
            $emailTemplate                  = new EmailTemplate();
            $emailTemplate->type            = $type;//EmailTemplate::TYPE_WORKFLOW;
            $emailTemplate->builtType       = EmailTemplate::BUILT_TYPE_BUILDER_TEMPLATE;
            $emailTemplate->isDraft         = $isDraft;
            $emailTemplate->modelClassName  = $modelClassName;
            $emailTemplate->name            = $name;
            if (empty($subject))
            {
                $subject                    = $name;
            }
            $emailTemplate->subject         = $subject;
            if (!isset($language))
            {
                $language           = Yii::app()->languageHelper-> getForCurrentUser();
            }
            $emailTemplate->language        = $language;
            $emailTemplate->htmlContent     = $htmlContent;
            $emailTemplate->textContent     = $textContent;
            $emailTemplate->serializedData  = CJSON::encode($unserializedData);
            $emailTemplate->addPermissions(Group::getByName(Group::EVERYONE_GROUP_NAME),
                                            Permission::READ_WRITE_CHANGE_PERMISSIONS_CHANGE_OWNER);
            $saved                          = $emailTemplate->save(false);
            if (!$saved)
            {
                throw new FailedToSaveModelException();
            }
            $emailTemplate = EmailTemplate::getById($emailTemplate->id);
            ReadPermissionsOptimizationUtil::
                securableItemGivenPermissionsForGroup($emailTemplate, Group::getByName(Group::EVERYONE_GROUP_NAME));
            $saved                          = $emailTemplate->save(false);
            assert('$saved');
        }

        protected function makeImages()
        {
            $imagesNamesToImport = array(
                '200x50' => '200x50.gif',
                '200x200' => '200x200.gif',
                '580x180' => '580x180.gif',
                'googleMaps' => 'staticmap.png'
            );
            foreach ($imagesNamesToImport as $type => $name)
            {
                $path = Yii::getPathOfAlias('application.modules.emailTemplates.views.assets.images') . DIRECTORY_SEPARATOR . $name;
                $fileUploadData = ImageFileModelUtil::saveImageFromTemporaryFile($path, $type);
                $this->importedImages[$type] = $fileUploadData['id'];
            }
        }
    }
?>