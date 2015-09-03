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
     * Base class to test API functions.
     */
    abstract class ApiBaseTest extends ZurmoBaseTest
    {
        protected $serverUrl = '';

        protected static $createUsersAndGroups = true;

        protected static $randomNonEveryoneNonAdministratorsGroup = null;

        abstract protected function getApiControllerClassName();

        abstract protected function getModuleBaseApiUrl();

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            if (static::$createUsersAndGroups)
            {
                SecurityTestHelper::createUsers();
                SecurityTestHelper::createGroups();
            }
            $everyoneGroup = Group::getByName(Group::EVERYONE_GROUP_NAME);
            assert($everyoneGroup->save()); // Not Coding Standard
            static::setRandomNonEveryoneNonAdministratorsGroup();
        }

        protected final function getBaseApiUrl()
        {
            $moduleBaseApiUrl   = $this->getModuleBaseApiUrl();
            $entryScript        = '/test.php/';
            $baseApiUrl         = $this->serverUrl . $entryScript . $moduleBaseApiUrl;
            return $baseApiUrl;
        }

        public function setUp()
        {
            parent::setUp();
            RedBeanModel::forgetAll();
            $this->setupServerUrl();
            if (!$this->isApiTestUrlConfigured())
            {
                $this->markTestSkipped(Zurmo::t('ApiModule', 'API test url is not configured in perInstanceTest.php file.'));
            }
        }

        protected function setupServerUrl()
        {
            $testApiUrl = Yii::app()->params['testApiUrl'];
            if (isset($testApiUrl) && strlen($testApiUrl) > 0)
            {
                $this->serverUrl = $testApiUrl;
            }
        }

        protected function isApiTestUrlConfigured()
        {
            $isApiTestUrlConfigured = false;
            if (isset($this->serverUrl) && strlen($this->serverUrl) > 0)
            {
                $isApiTestUrlConfigured = true;
            }
            return $isApiTestUrlConfigured;
        }

        protected function getModelToApiDataUtilData(RedBeanModel $model)
        {
            $apiControllerClassName = $this->getApiControllerClassName();
            $method                 = $this->getProtectedMethod($apiControllerClassName, __FUNCTION__);
            $data                   = $method->invokeArgs(null, array($model));
            return $data;
        }

        protected function createApiCallWithRelativeUrl($relativeUrl, $method, $headers, $data = array())
        {
            $baseUrl                = $this->getBaseApiUrl();
            $url                    = $baseUrl . $relativeUrl;
            return ApiRestTestHelper::createApiCall($url, $method, $headers, $data);
        }

        protected static function setRandomNonEveryoneNonAdministratorsGroup()
        {
            $groups = Group::getAll();
            foreach ($groups as $group)
            {
                if ($group->isDeletable())
                {
                    static::$randomNonEveryoneNonAdministratorsGroup = $group;
                    break;
                }
            }
        }
    }
?>