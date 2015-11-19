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
     * Class to adapt global configuration values into a configuration form.
     * Saves global values from a configuration form.
     */
    class ZurmoConfigurationFormAdapter
    {
        /**
         * @return ZurmoConfigurationForm
         */
        public static function makeFormFromGlobalConfiguration()
        {
            $form                                         = new ZurmoConfigurationForm();
            $form->applicationName                        = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'applicationName');
            $form->timeZone                               = Yii::app()->timeZoneHelper->getGlobalValue();
            $form->listPageSize                           = Yii::app()->pagination->getGlobalValueByType('listPageSize');
            $form->subListPageSize                        = Yii::app()->pagination->getGlobalValueByType('subListPageSize');
            $form->modalListPageSize                      = Yii::app()->pagination->getGlobalValueByType('modalListPageSize');
            $form->dashboardListPageSize                  = Yii::app()->pagination->getGlobalValueByType('dashboardListPageSize');
            $form->defaultFromEmailAddress                = Yii::app()->emailHelper->resolveAndGetDefaultFromAddress();
            $form->defaultTestToEmailAddress              = Yii::app()->emailHelper->resolveAndGetDefaultTestToAddress();
            $form->gamificationModalNotificationsEnabled  = Yii::app()->gameHelper->modalNotificationsEnabled;
            $form->gamificationModalCollectionsEnabled    = Yii::app()->gameHelper->modalCollectionsEnabled;
            $form->gamificationModalCoinsEnabled          = Yii::app()->gameHelper->modalCoinsEnabled;
            $form->realtimeUpdatesEnabled                 = static::getRealtimeUpdatesEnabled();
            $form->reCaptchaPrivateKey                    = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'reCaptchaPrivateKey');
            $form->reCaptchaPublicKey                     = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'reCaptchaPublicKey');
            return $form;
        }

        /**
         * Given a ZurmoConfigurationForm, save the configuration global values.
         */
        public static function setConfigurationFromForm(ZurmoConfigurationForm $form)
        {
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'applicationName', $form->applicationName);
            Yii::app()->timeZoneHelper  ->setGlobalValue(                         (string)$form->timeZone);
            Yii::app()->pagination->setGlobalValueByType('listPageSize',          (int)   $form->listPageSize);
            Yii::app()->pagination->setGlobalValueByType('subListPageSize',       (int)   $form->subListPageSize);
            Yii::app()->pagination->setGlobalValueByType('modalListPageSize',     (int)   $form->modalListPageSize);
            Yii::app()->pagination->setGlobalValueByType('dashboardListPageSize', (int)   $form->dashboardListPageSize);
            Yii::app()->emailHelper->setDefaultFromAddress($form->defaultFromEmailAddress);
            Yii::app()->emailHelper->setDefaultTestToAddress($form->defaultTestToEmailAddress);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule',
                                                    'gamificationModalNotificationsEnabled',
                                                    (boolean) $form->gamificationModalNotificationsEnabled);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule',
                                                    'gamificationModalCollectionsEnabled',
                                                    (boolean) $form->gamificationModalCollectionsEnabled);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule',
                                                    'gamificationModalCoinsEnabled',
                                                    (boolean) $form->gamificationModalCoinsEnabled);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule',
                                                    'realtimeUpdatesEnabled',
                                                    (boolean) $form->realtimeUpdatesEnabled);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'reCaptchaPrivateKey', $form->reCaptchaPrivateKey);
            ZurmoConfigurationUtil::setByModuleName('ZurmoModule', 'reCaptchaPublicKey',  $form->reCaptchaPublicKey);
        }

        public static function getRealtimeUpdatesEnabled()
        {
            if (ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'realtimeUpdatesEnabled') !== null)
            {
                return ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'realtimeUpdatesEnabled');
            }
            else
            {
                return false;
            }
        }
    }
?>