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

    class ZurmoBaseTest extends BaseTest
    {
        public static $activateDefaultLanguages = false;

        protected static $activitiesObserver;

        protected static $conversationsObserver;

        protected static $emailMessagesObserver;

        protected static $contactLatestActivityDateTimeObserver;

        protected static $accountLatestActivityDateTimeObserver;

        protected static $accountContactAffiliationObserver;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            ZurmoDatabaseCompatibilityUtil::createActualPermissionsCacheTable();
            ZurmoDatabaseCompatibilityUtil::createNamedSecurableActualPermissionsCacheTable();
            ZurmoDatabaseCompatibilityUtil::createActualRightsCacheTable();
            ZurmoDatabaseCompatibilityUtil::dropStoredFunctionsAndProcedures();
            PermissionsCache::forgetAll();
            AllPermissionsOptimizationCache::forgetAll();
            RightsCache::forgetAll();
            PoliciesCache::forgetAll();
            Currency::resetCaches();  //php only cache
            Permission::resetCaches(); //php only cache
            self::$activitiesObserver = new ActivitiesObserver();
            self::$activitiesObserver->init(); //runs init();
            self::$conversationsObserver = new ConversationsObserver();
            self::$conversationsObserver->init(); //runs init();
            self::$emailMessagesObserver = new EmailMessagesObserver();
            self::$emailMessagesObserver->init(); //runs init();
            self::$contactLatestActivityDateTimeObserver = new ContactLatestActivityDateTimeObserver();
            self::$contactLatestActivityDateTimeObserver->init(); //runs init();
            self::$accountLatestActivityDateTimeObserver = new AccountLatestActivityDateTimeObserver();
            self::$accountLatestActivityDateTimeObserver->init(); //runs init();
            self::$accountContactAffiliationObserver = new AccountContactAffiliationObserver();
            self::$accountContactAffiliationObserver->init(); //runs init();
            Yii::app()->gameHelper;
            Yii::app()->gamificationObserver; //runs init();
            Yii::app()->gameHelper->resetDeferredPointTypesAndValuesByUserIdToAdd();
            Yii::app()->emailHelper->sendEmailThroughTransport = false;
            Yii::app()->jobQueue->deleteAll();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->gameHelper->resetDeferredPointTypesAndValuesByUserIdToAdd();
        }

        public static function tearDownAfterClass()
        {
            self::$activitiesObserver->destroy();
            self::$conversationsObserver->destroy();
            self::$emailMessagesObserver->destroy();
            self::$contactLatestActivityDateTimeObserver->destroy();
            self::$accountLatestActivityDateTimeObserver->destroy();
            self::$accountContactAffiliationObserver->destroy();
            parent::tearDownAfterClass();
        }

        protected static function startOutputBuffer()
        {
            ob_start();
        }

        protected static function endAndGetOutputBuffer()
        {
            $content = ob_get_contents();
            ob_end_clean();
            self::cleanUpOutputBuffer();
            return $content;
        }

        protected function endPrintOutputBufferAndFail()
        {
            echo $this->endAndGetOutputBuffer();
            $this->fail();
        }

        private static function cleanUpOutputBuffer()
        {
            while (count(ob_get_status(true)) > 1)
            {
                ob_end_clean();
            }
        }
    }
?>