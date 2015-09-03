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
     * Configuration Module Walkthrough.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class ConfigurationSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setup()
        {
            $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $this->runControllerWithNoExceptionsAndGetContent('configuration/default');
            $this->runControllerWithNoExceptionsAndGetContent('configuration/default/index');
        }

        public function testRunDiagnostic()
        {
            ZurmoRedBean::exec("SHOW TABLES");
            $countBefore  = ZurmoRedBean::getCell("SELECT FOUND_ROWS();");
            $content = $this->runControllerWithNoExceptionsAndGetContent('configuration/default/runDiagnostic');
            $this->assertContains("Failed Required Services", $content);
            $this->assertContains("<span class=\"fail\">FAIL</span>", $content);
            $this->assertContains("Zurmo runs only on Apache 2.2.1 and higher or Microsoft-IIS 5.0.0 or higher web servers.", $content);
            $this->assertContains("\$_SERVER does not have HTTP_HOST, SERVER_NAME, SERVER_PORT, HTTP_ACCEPT, HTTP_USER_AGENT", $content);
            $criticalFailureCount   = substr_count($content, "<span class=\"fail\">FAIL</span>");
            $this->assertThat(true, $this->logicalOr($this->equalTo(2, $criticalFailureCount), $this->equalTo(3, $criticalFailureCount)));
            if ($criticalFailureCount === 3)
            {
                $this->assertTrue(
                    // this can happen if minScript dir does not exist
                    strpos($content, "The application.log runtime file is writable.<br />\n" .
                                            "The /minScript/cache runtime directory is not writable.<br />\n" .
                                            "The debug.php file is present.") !== false ||
                    // this happens on CI servers
                    strpos($content, "Host Info/Script Url is incorrectly configured.") !== false
                );
            }
            $this->assertFileExists(realpath(INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'perInstance.php'));
            $this->assertFileExists(realpath(INSTANCE_ROOT . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'debug.php'));
            ZurmoRedBean::exec("SHOW TABLES");
            $countAfter  = ZurmoRedBean::getCell("SELECT FOUND_ROWS();");
            $this->assertEquals($countBefore, $countAfter);
        }
    }
?>