<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
     * details.
     *
     * You should have received a copy of the GNU General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * A job that check if user added some new required attributes to Contacts or Meetings so GoogleApps sync will work fine.
     */
    abstract class ModelIntegrationIntegrityCheckJob extends BaseJob
    {
        protected $modelsToCheck = array();

        abstract public function getNotificationType();

        abstract public function getNotificationRulesClassName();

        abstract public function getNotificationMessage();

        public static function getRecommendedRunFrequencyContent()
        {
            return Zurmo::t('Core', 'Once per day.');
        }

        /**
         * For all models from list, get added required attributes for each model
         * @param $models
         * @return array
         */
        protected function resolveAddedRequiredAttributesViaDesignerForAllModels($models)
        {
            $addedRequiredAttributesViaDesigner = array();
            foreach ($models as $modelClassName)
            {
                $requiredAttributes = $this->resolveRequiredAttributesFromMetadata($modelClassName::getMetadata(), true);
                $defaultRequiredAttributes = $this->resolveRequiredAttributesFromMetadata($modelClassName::getDefaultMetadata());
                $addedRequiredAttributesViaDesignerForModel = $this->resolveAddedRequiredAttributesViaDesigner(
                    $defaultRequiredAttributes, $requiredAttributes);

                if (!empty($addedRequiredAttributesViaDesignerForModel))
                {
                    $addedRequiredAttributesViaDesigner[$modelClassName] = $addedRequiredAttributesViaDesignerForModel;
                }
            }
            return $addedRequiredAttributesViaDesigner;
        }

        /**
         * Send notification messages based on list of added required attributes
         * @param $addedRequiredAttributesViaDesigner
         */
        protected function sendNotificationMessages($addedRequiredAttributesViaDesigner)
        {
            $message = '';
            foreach ($addedRequiredAttributesViaDesigner as $modelClassName => $attributes)
            {
                foreach ($attributes as $attributeName)
                {
                    $message .= $modelClassName::getModelLabelByTypeAndLanguage('Singular', Yii::app()->language) .
                        ':' . $modelClassName::getAnAttributeLabel($attributeName) . '<br />';
                }
            }

            $notificationMessage                    = new NotificationMessage();
            $notificationMessage->htmlContent       = static::getNotificationMessage() . '<br />' . $message;
            $notificationRulesClassName = static::getNotificationRulesClassName();
            $rules                      = new $notificationRulesClassName();
            $superAdminGroup = Group::getByName(Group::SUPER_ADMINISTRATORS_GROUP_NAME);
            foreach ($superAdminGroup->users as $user)
            {
                Notification::deleteByTypeAndUser(static::getNotificationType(), $user);
                $rules->addUser($user);
                NotificationsUtil::submit($notificationMessage, $rules);
            }
        }

        /**
         * Based on default model metadata and customized model metadata, get added required attributes
         * @param $defaultRequiredAttributes
         * @param $requiredAttributes
         * @return array
         */
        protected function resolveAddedRequiredAttributesViaDesigner($defaultRequiredAttributes, $requiredAttributes)
        {
            $addedRequiredAttributesViaDesigner = array();
            if (is_array($requiredAttributes) && !empty($requiredAttributes))
            {
                foreach ($requiredAttributes as $modelClassName => $rules)
                {
                    if (is_array($rules) && !empty($rules))
                    {
                        foreach ($rules as $attributeName => $value)
                        {
                            if (!isset($defaultRequiredAttributes[$modelClassName][$attributeName]) ||
                                $defaultRequiredAttributes[$modelClassName][$attributeName] != $value)
                            {
                                $addedRequiredAttributesViaDesigner[] = $attributeName;
                            }
                        }
                    }
                }
            }
            return $addedRequiredAttributesViaDesigner;
        }

        /**
         * Based on model metadata, get all required attributes
         * @param $metadata
         * @param bool $onlyWithoutSetDefaultValue
         * @return array
         */
        protected function resolveRequiredAttributesFromMetadata($metadata, $onlyWithoutSetDefaultValue = false)
        {
            $requiredAttributes = array();
            foreach ($metadata as $modelClassName => $modelClassMetadata)
            {
                if (isset($modelClassMetadata['rules']) &&
                    is_array($modelClassMetadata['rules']) &&
                    !empty($modelClassMetadata['rules']))
                {
                    foreach ($modelClassMetadata['rules'] as $rule)
                    {
                        if ($rule[1] == 'required')
                        {
                            if ($onlyWithoutSetDefaultValue)
                            {
                                $haveDefaultValue = false;
                                foreach ($modelClassMetadata['rules'] as $innerRule)
                                {
                                    // Check if there is default value for same attribute
                                    if ($innerRule[1] == 'default' && $innerRule[0] == $rule[0])
                                    {
                                        $haveDefaultValue = true;
                                    }
                                }
                                if (!$haveDefaultValue)
                                {
                                    $requiredAttributes[$modelClassName][$rule[0]] = $rule[1];
                                }
                            }
                            else
                            {
                                $requiredAttributes[$modelClassName][$rule[0]] = $rule[1];
                            }
                        }
                    }
                }
            }
            return $requiredAttributes;
        }
    }
?>