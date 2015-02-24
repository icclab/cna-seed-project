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

    $cwd = getcwd();
    require_once('../common/TestConfigFileUtils.php');
    TestConfigFileUtils::configureConfigFiles();
    require_once(INSTANCE_ROOT . '/protected/config/debugTest.php');

    chdir(__DIR__);
    require_once('../common/PhpUnitServiceUtil.php');
    require_once('../common/testRoots.php');
    require_once 'File/Iterator.php';
    require_once('File/Iterator/Factory.php');

    define('SELENIUM_SERVER_PATH', $seleniumServerPath);
    define('TEST_BASE_URL', $seleniumTestBaseUrl);
    define('TEST_RESULTS_URL', $seleniumTestResultUrl);
    define('TEST_RESULTS_PATH', $seleniumTestResultsPath);
    define('SELENIUM_SERVER_PORT', $seleniumServerPort);
    define('BROWSERS_TO_RUN', $seleniumBrowsersToRun);
    define('TEST_BASE_CONTROL_URL', $seleniumControlUrl);
    //following is path to the user-extension.js, so as to enable the use of global variables
    define('USER_EXTENSIONS_JS_PATH', './assets/extensions/user-extensions.js');

    class TestSuite
    {
        public static function run()
        {
            global $argv, $argc;

            $usage = PHP_EOL .
                     "  Usage: php [options] TestSuite.php <All|Misc|moduleName|TestClassName> [options]" . PHP_EOL .
                     PHP_EOL .
                     "    All               Run all tests." . PHP_EOL .
                     "    Framework         Run all tests in framework/tests/functional." . PHP_EOL .
                     "    Misc              Run the test suites in app/protected/tests/functional." . PHP_EOL .
                     "    moduleName        Run the test suites in app/protected/modules/moduleName/tests/functional." . PHP_EOL .
                     "    TestClassName     Run the tests in TestClassName.html, wherever that happens to be." . PHP_EOL .
                     "    options" . PHP_EOL .
                     "    -p                port Example: -p4044" . PHP_EOL .
                     "    -h                host Example: -hhttp://www.sitetotest/app/" . PHP_EOL .
                     "    -b                browser <*firefox|*iexplore> if not specified, will run all in browsers " . PHP_EOL .
                     "    -c                test server control url Example: -chttp://www.sitetotest/controlUrl.php" . PHP_EOL .
                     "                      Example: -b*firefox " . PHP_EOL .
                     "    -userExtensions   Example: -userExtensions pathToTheUserExtensionJS " . PHP_EOL .
                     PHP_EOL .
                     "  Examples:" . PHP_EOL .
                     PHP_EOL .
                     "    php TestSuiteSelenium.php accounts (Run the tests in the Accounts module.)" . PHP_EOL .
                     "    php TestSuiteSelenium.php RedBeanModelTest   (Run the test suite RedBeanModelTest.html.)" . PHP_EOL .
                     PHP_EOL                                                                                                   .

            PhpUnitServiceUtil::checkVersion();
            if ($argv[0] != 'TestSuite.php')
            {
                echo $usage;
                exit;
            }
            else
            {
                $whatToTest = $argv[1];
            }
            $whatToTestIsModuleDir = self::isWhatToTestAModule($whatToTest);
            $suiteNames          = array();
            $htmlTestSuiteFiles  = array();
            if ($whatToTest == 'All' || $whatToTest == 'Misc' || !$whatToTestIsModuleDir)
            {
                $compareToTest = $whatToTest;
                if ($whatToTest == 'Misc')
                {
                    $compareToTest = null;
                }
                $htmlTestSuiteFiles = self::buildSuiteFromSeleneseDirectory($htmlTestSuiteFiles, '.', $compareToTest);
            }
            if ($whatToTest != 'Misc' && !$whatToTestIsModuleDir)
            {
                $compareToTest = $whatToTest;
                if ($whatToTest == 'Framework')
                {
                    $compareToTest = null;
                }
                $frameworkTestSuiteDirectory = '../../core/tests/functional';
                $htmlTestSuiteFiles = self::buildSuiteFromSeleneseDirectory(
                    $htmlTestSuiteFiles, $frameworkTestSuiteDirectory, $compareToTest);
            }
            $moduleDirectoryName = '../../modules';
            if (is_dir($moduleDirectoryName))
            {
                $moduleNames = scandir($moduleDirectoryName);
                foreach ($moduleNames as $moduleName)
                {
                    if ($moduleName != '.' &&
                        $moduleName != '..')
                    {
                        $moduleFunctionalTestDirectoryName = "$moduleDirectoryName/$moduleName/tests/functional";
                        if (is_dir($moduleFunctionalTestDirectoryName))
                        {
                            if ($whatToTest          == 'All'        ||
                                // Allow specifying 'Users' for the module name 'users'.
                                $whatToTest          == $moduleName  ||
                                strtolower($whatToTest) == $moduleName  || !$whatToTestIsModuleDir)
                            {
                                if ($whatToTest          == $moduleName || strtolower($whatToTest) == $moduleName)
                                {
                                    $compareToTest = null;
                                }
                                else
                                {
                                    $compareToTest = $whatToTest;
                                }
                                $htmlTestSuiteFiles = self::buildSuiteFromSeleneseDirectory(
                                    $htmlTestSuiteFiles, $moduleFunctionalTestDirectoryName, $compareToTest);
                            }
                        }
                    }
                }
            }
            if (count($htmlTestSuiteFiles) == 0)
            {
                echo $usage;
                echo "  No tests found for '$whatToTest'.\n" . PHP_EOL;
                exit;
            }
            echo 'Suites to run:' . PHP_EOL;
            foreach ($htmlTestSuiteFiles as $pathToSuite)
            {
                if (in_array(basename($pathToSuite), $suiteNames))
                {
                    echo 'Cannot run tests because there are 2 test suites with the same name.' . PHP_EOL;
                    echo 'The duplicate found is here: ' . $pathToSuite . PHP_EOL;
                    exit;
                }
                $suiteNames[] = basename($pathToSuite);
                echo $pathToSuite . PHP_EOL;
            }
            echo 'Running Test Suites using Selenium RC v2:' . PHP_EOL;
            $browsersToRun = self::resolveBrowserFromParameter();

            foreach ($browsersToRun as $browserId => $browserDisplayName)
            {
                self::clearPreviousTestResultsByServerAndBrowser(self::getServerByServerControlUrl(self::resolveHostFromParameterAndConstant()),
                                                                 $browserDisplayName);
                foreach ($htmlTestSuiteFiles as $pathToSuite)
                {
                    if (!self::isInstallationTest($pathToSuite))
                    {
                        echo "Restoring test db" . PHP_EOL;
                        self::remoteAction(self::resolveServerControlUrlFromParameterAndConstant(), array('action' => 'restore'));
                        echo "Restored test db" . PHP_EOL;
                        if (!self::isInstallationTest($pathToSuite))
                        {
                            echo "Set user default time zone." . PHP_EOL;
                            self::remoteAction(self::resolveServerControlUrlFromParameterAndConstant(), array('action' => 'setUserDefaultTimezone'));
                            echo "User default time zone set." . PHP_EOL;
                        }
                        echo "Clear cache on remote server" . PHP_EOL;
                        self::remoteAction(self::resolveHostFromParameterAndConstant(), array('clearCache'         => '1',
                                                                'ignoreBrowserCheck' => '1')); //Eventually remove this since in code for 2.5.9 this is removed
                    }
                    else
                    {
                        echo "Uninstall zurmo" . PHP_EOL;
                        self::remoteAction(self::resolveServerControlUrlFromParameterAndConstant(), array('action' => 'backupRemovePerInstance'));
                    }
                    echo "Cache cleared" . PHP_EOL;

                    echo 'Running test suite: ';
                    echo $pathToSuite . PHP_EOL;

                    $host = self::resolveHostFromParameterAndConstant();

                    $hostFilePart = str_replace('http://', '', $host);
                    $hostFilePart = str_replace('https://', '', $hostFilePart);
                    $hostFilePart = str_replace('/', '', $hostFilePart);
                    $hostFilePart = $hostFilePart . '.';
                    $testResultFileNamePrefix = str_replace('../', '', $pathToSuite);
                    $testResultFileNamePrefix = str_replace('/',   '.', $testResultFileNamePrefix);
                    $testResultFileNamePrefix = str_replace('\\',  '.', $testResultFileNamePrefix);
                    $testResultFileNamePrefix = str_replace('..', '', $testResultFileNamePrefix);
                    $testResultFileNamePrefix = str_replace('.html', '', $testResultFileNamePrefix);
                    $testResultsFileName = $testResultFileNamePrefix . '.' . str_replace(' ', '', $browserDisplayName) . '.TestResults.html';
                    $finalTestResultsPath = TEST_RESULTS_PATH . $hostFilePart . $testResultsFileName;
                    $finalCommand  = 'java -jar "' . SELENIUM_SERVER_PATH .'" ';
                    $finalCommand .= '-port ' . self::resolvePortFromParameterAndConstant();
                    $finalCommand .= ' -htmlSuite ' . $browserId . ' ';
                    $finalCommand .= $host . ' ' . realPath($pathToSuite) . ' ' . $finalTestResultsPath;
                    $finalCommand .= ' -userExtensions ' . self::resolveUserExtensionsJsFromParameterAndConstant();
                    echo $finalCommand . PHP_EOL;
                    exec($finalCommand);
                    echo 'Restoring test db';
                    self::remoteAction(self::resolveServerControlUrlFromParameterAndConstant(), array('action' => 'restore'));
                    if (self::isInstallationTest($pathToSuite))
                    {
                        self::remoteAction(self::resolveServerControlUrlFromParameterAndConstant(), array('action' => 'restorePerInstance'));
                    }
                }
            }
            echo 'Functional Run Complete.' . PHP_EOL;
            self::updateTestResultsSummaryAndDetailsFiles();
        }

        public static function buildSuiteFromSeleneseDirectory($htmlTestSuiteFiles, $directoryName, $whatToTest = null)
        {
            $files = array_merge(
              self::getSeleneseFiles($directoryName, '.html')
            );
            foreach ($files as $file)
            {
                if (!strpos($file, 'TestSuite') === false)
                {
                    if ( $whatToTest == null || $whatToTest == 'All' ||
                        ($whatToTest . '.html' == basename($file) && $whatToTest != null))
                    {
                        $htmlTestSuiteFiles[] = $file;
                    }
                }
            }
            return $htmlTestSuiteFiles;
        }

        /**
         * @param  string $directory
         * @param  string $suffix
         * @return array
         * @since  Method available since Release 3.3.0
         */
        protected static function getSeleneseFiles($directory, $suffix)
        {
            $files    = array();
            $iterator = File_Iterator_Factory::getFileIterator($directory, $suffix);
            foreach ($iterator as $file)
            {
                if (!in_array($file, $files))
                {
                    $files[] = (string)$file;
                }
            }
            return $files;
        }

        /**
         * @return true if what to test is a module directory
         */
        protected static function isWhatToTestAModule($whatToTest)
        {
            $moduleDirectoryName = '../../modules';
            if (is_dir($moduleDirectoryName))
            {
                $moduleNames = scandir($moduleDirectoryName);
                foreach ($moduleNames as $moduleName)
                {
                    if ($moduleName != '.' &&
                        $moduleName != '..')
                    {
                        $moduleFunctionalTestDirectoryName = "$moduleDirectoryName/$moduleName/tests/functional";
                        if (is_dir($moduleFunctionalTestDirectoryName))
                        {
                            if (// Allow specifying 'Users' for the module name 'users'.
                                $whatToTest          == $moduleName  ||
                                ucfirst($whatToTest) == $moduleName)
                            {
                                return true;
                            }
                        }
                    }
                }
            }
            return false;
        }

        protected static function resolvePortFromParameterAndConstant()
        {
            global $argv, $argc;

            for ($i = 0; $i < ($argc); $i++)
            {
                if (substr($argv[$i], 0, 2) == '-p')
                {
                    return substr($argv[$i], 2);
                }
            }
            return SELENIUM_SERVER_PORT;
        }

        protected static function resolveHostFromParameterAndConstant()
        {
            global $argv, $argc;

            for ($i = 0; $i < ($argc); $i++)
            {
                if (substr($argv[$i], 0, 2) == '-h')
                {
                    return substr($argv[$i], 2);
                }
            }
            return TEST_BASE_URL;
        }

        protected static function resolveServerControlUrlFromParameterAndConstant()
        {
            global $argv, $argc;

            for ($i = 0; $i < ($argc); $i++)
            {
                if (substr($argv[$i], 0, 2) == '-c')
                {
                    return substr($argv[$i], 2);
                }
            }
            return TEST_BASE_CONTROL_URL;
        }

        protected static function resolveUserExtensionsJsFromParameterAndConstant()
        {
            global $argv, $argc;

            for ($i = 0; $i < ($argc); $i++)
            {
                if (substr($argv[$i], 0, 16) == '-userExtensions ')
                {
                    return substr($argv[$i], 16);
                }
            }
            return USER_EXTENSIONS_JS_PATH;
        }

        protected static function resolveBrowserFromParameter()
        {
            global $argv, $argc;

            $browserData = self::getBrowsersData();

            for ($i = 0; $i < ($argc); $i++)
            {
                if (substr($argv[$i], 0, 2) == '-b')
                {
                    $browsersToRun = substr($argv[$i], 2);
                    if ($browsersToRun == BROWSERS_TO_RUN)
                    {
                        return self::getBrowsersData();
                    }
                    if (!in_array($browsersToRun,
                        array('*iexplore', '*firefox', '*googlechrome')))
                    {
                        echo 'Invalid Browser specified.' . PHP_EOL;
                        echo 'Specified Browser: ' . $browsersToRun . PHP_EOL;
                        exit;
                    }
                    foreach ($browserData as $id => $name)
                    {
                        if ($id == $browsersToRun)
                        {
                            return array($id => $name);
                        }
                    }
                }
            }
            return self::getBrowsersData();
        }

        protected static function getServerByServerControlUrl($url)
        {
            if (stristr($url, 'dev9.zurmo.com'))
            {
                return 'dev9.zurmo.com';
            }
            elseif (stristr($url, 'dev8.zurmo.com'))
            {
                return 'dev8.zurmo.com';
            }
            return 'Unknown';
        }

        protected static function getBrowsersData()
        {
            return array(
                '*firefox'      => 'FireFox',
                '*iexplore'     => 'Internet Explorer',
                '*googlechrome' => 'Chrome',
            );
        }

        protected static function updateTestResultsSummaryAndDetailsFiles()
        {
            $data = array();
            if (is_dir(TEST_RESULTS_PATH))
            {
                $resultsNames = scandir(TEST_RESULTS_PATH);
                foreach ($resultsNames as $resultFile)
                {
                    if ($resultFile != '.' &&
                        $resultFile != '..' &&
                        $resultFile != 'Summary.html' &&
                        $resultFile != 'Details.html')
                    {
                        $data[] = array(
                            'fileName'     => $resultFile,
                            'modifiedDate' => date ("F d Y H:i:s.", filemtime(TEST_RESULTS_PATH . $resultFile)),
                            'status'       => self::getResultFileStatusByFileName($resultFile),
                            'browser'      => self::getResultFileBrowserByFileName($resultFile),
                            'server'       => self::getResultServerByFileName($resultFile),
                        );
                    }
                }
            }
            self::makeResultsDetailsFile($data);
            self::makeResultsSummaryFile($data);
        }

        protected static function clearPreviousTestResultsByServerAndBrowser($server, $browserDisplayName)
        {
            if (is_dir(TEST_RESULTS_PATH))
            {
                $resultsNames = scandir(TEST_RESULTS_PATH);
                foreach ($resultsNames as $resultFile)
                {
                    if ($resultFile != '.' &&
                    $resultFile != '..' &&
                    stristr($resultFile, strtolower($browserDisplayName)) &&
                    stristr($resultFile, strtolower($server)))
                    {
                        unlink(TEST_RESULTS_PATH . $resultFile);
                    }
                }
            }
        }

        protected static function getResultFileStatusByFileName($resultFile)
        {
            $contents = file_get_contents(TEST_RESULTS_PATH . $resultFile);
            $contents = str_replace('"', '', $contents);
            $contents = strtolower($contents);

            $pieces = explode('id=suitetable', $contents); // Not Coding Standard
            if (!empty($pieces[1]))
            {
                $pieces = explode('</table>', $pieces[1]);
                $pieces = explode('<tr class=title', $pieces[0]); // Not Coding Standard
                $pieces = explode('>', $pieces[1]);
                return trim($pieces[0]);
            }
            return 'Unknown';
        }

        protected static function getResultFileBrowserByFileName($resultFile)
        {
            if (stristr($resultFile, 'firefox'))
            {
                return 'Firefox';
            }
            elseif (stristr($resultFile, 'internetexplorer'))
            {
                return 'IE';
            }
            elseif (stristr($resultFile, 'chrome'))
            {
                return 'Chrome';
            }
            return 'Unknown';
        }

        protected static function getResultServerByFileName($resultFile)
        {
            if (stristr($resultFile, 'dev9.zurmo.com'))
            {
                return 'dev9.zurmo.com';
            }
            elseif (stristr($resultFile, 'dev8.zurmo.com'))
            {
                return 'dev8.zurmo.com';
            }
            return 'Unknown';
        }

        protected static function makeResultsDetailsFile($data)
        {
            $fileName = TEST_RESULTS_PATH . 'Details.html';
            $content = '<html>';
            $content .= '<table border="1" width="100%">'                               . PHP_EOL;
            $content .= '<tr>'                                                          . PHP_EOL;
            $content .= '<td>Status</td>'                                               . PHP_EOL;
            $content .= '<td>Server</td>'                                              . PHP_EOL;
            $content .= '<td>Browser</td>'                                              . PHP_EOL;
            $content .= '<td>Date</td>'                                                 . PHP_EOL;
            $content .= '<td>File</td>'                                                 . PHP_EOL;
            $content .= '</tr>'                                                         . PHP_EOL;
            foreach ($data as $info)
            {
                $link = '<a href="' . TEST_RESULTS_URL . $info['fileName'] . '">' . $info['fileName'] . '</a>';
                $statusColor = 'bgcolor="red"';
                if ($info['status']=='status_passed')
                {
                    $statusColor = 'bgcolor="green"';
                }
                $content .= '<tr>'                                                      . PHP_EOL;
                $content .= '<td ' . $statusColor . '>' . $info['status']   . '</td>'   . PHP_EOL;
                $content .= '<td>' . $info['server']                       . '</td>'   . PHP_EOL;
                $content .= '<td>' . $info['browser']                       . '</td>'   . PHP_EOL;
                $content .= '<td>' . $info['modifiedDate']                  . '</td>'   . PHP_EOL;
                $content .= '<td>' . $link                                  . '</td>'   . PHP_EOL;
                $content .= '</tr>'                                                     . PHP_EOL;
            }
            $content .= '</table>'                                                      . PHP_EOL;
            $content .= '</html>'                                                       . PHP_EOL;

            if (is_writable(TEST_RESULTS_PATH))
            {
                if (!$handle = fopen($fileName, 'w'))
                {
                    echo "Cannot open file ($fileName)";
                    exit;
                }

                // Write $somecontent to our opened file.
                if (fwrite($handle, $content) === false)
                {
                echo "Cannot write to file ($fileName)";
                exit;
                        }
                        fclose($handle);
                }
                else
                {
                    echo "The file $fileName is not writable";
            }
        }

        protected static function makeResultsSummaryFile($data)
        {
            $content = '<html>';
            $content .= '<table border="1" width="100%">'                               . PHP_EOL;
            $content .= '<tr>'                                                          . PHP_EOL;
            $content .= '<td>Status</td>'                                               . PHP_EOL;
            $content .= '<td>Server</td>'                                               . PHP_EOL;
            $content .= '<td>Browser</td>'                                              . PHP_EOL;
            $content .= '<td>Date</td>'                                                 . PHP_EOL;
            $content .= '<td>Test Passed</td>'                                          . PHP_EOL;
            $content .= '<td>Tests Failed</td>'                                         . PHP_EOL;
            $content .= '<td>Details</td>'                                              . PHP_EOL;
            $content .= '</tr>'                                                         . PHP_EOL;

            $link = '<a href="' . TEST_RESULTS_URL . 'Details.html">Details</a>';

            $allBrowsersStats = array();
            foreach ($data as $info)
            {
                if (count($allBrowsersStats) == 0 || !in_array($info['browser'], $allBrowsersStats))
                {
                    $allBrowsersStats[$info['server']][$info['browser']] = array();
                    $allBrowsersStats[$info['server']][$info['browser']]['testsPassed'] = 0;
                    $allBrowsersStats[$info['server']][$info['browser']]['testsFailed'] = 0;
                    $allBrowsersStats[$info['server']][$info['browser']]['modifiedDate'] = 0;
                }
            }

            foreach ($data as $info)
            {
                if ($info['status']=='status_passed')
                {
                    $allBrowsersStats[$info['server']][$info['browser']]['testsPassed']++;
                }
                else
                {
                    $allBrowsersStats[$info['server']][$info['browser']]['testsFailed']++;
                }

                if (strtotime($allBrowsersStats[$info['server']][$info['browser']]['modifiedDate']) < strtotime($info['modifiedDate']))
                {
                    $allBrowsersStats[$info['server']][$info['browser']]['modifiedDate'] = $info['modifiedDate'];
                }
            }

            foreach ($allBrowsersStats as $server => $serverStats)
            {
                foreach ($serverStats as $browser => $browserStats)
                {
                    if ($browserStats['testsFailed'] > 0 || $browserStats['testsPassed'] <= 0)
                    {
                        $status = 'status_failed';
                    }
                    else
                    {
                        $status = 'status_passed';
                    }
                    $statusColor = 'bgcolor="red"';
                    if ($status == 'status_passed')
                    {
                        $statusColor = 'bgcolor="green"';
                    }

                    $content .= '<tr>'                                              . PHP_EOL;
                    $content .= '<td ' . $statusColor . '>' . $status   . '</td>'   . PHP_EOL;
                    $content .= '<td>' . $server                        . '</td>'   . PHP_EOL;
                    $content .= '<td>' . $browser                       . '</td>'   . PHP_EOL;
                    $content .= '<td>' . $browserStats['modifiedDate']  . '</td>'   . PHP_EOL;
                    $content .= '<td>' . $browserStats['testsPassed']   . '</td>'   . PHP_EOL;
                    $content .= '<td>' . $browserStats['testsFailed']   . '</td>'   . PHP_EOL;
                    $content .= '<td>' . $link                          . '</td>'   . PHP_EOL;
                    $content .= '</tr>'                                             . PHP_EOL;
                }
            }
                $content .= '</table>'                                          . PHP_EOL;
                $content .= '</html>'                                           . PHP_EOL;

                $fileName = TEST_RESULTS_PATH . 'Summary.html';
                if (is_writable(TEST_RESULTS_PATH))
                {
                    if (!$handle = fopen($fileName, 'w'))
                    {
                        echo "Cannot open file ($fileName)";
                        exit;
                    }

                    // Write $somecontent to our opened file.
                    if (fwrite($handle, $content) === false)
                    {
                        echo "Cannot write to file ($fileName)";
                        exit;
                    }
                    fclose($handle);
                }
                else
                {
                    echo "The file $fileName is not writable";
                }
        }

        /**
         * Restore database
         * @param string url
         * @param string $action
         */
        protected static function remoteAction($url, $params)
        {
            if (!$url)
            {
                echo "Invalid db control url";
                exit;
            }
            if (isset($params['action']) && in_array($params['action'], array('restore', 'backupRemovePerInstance', 'restorePerInstance', 'setUserDefaultTimezone')))
            {
                $url = $url . "?action=" . urlencode($params['action']);
            }
            elseif (isset($params['clearCache']) && $params['clearCache'] == '1' &&
                    isset($params['ignoreBrowserCheck']) && $params['ignoreBrowserCheck'] == '1')
            {
                $url = $url . "index.php/zurmo/default/login?clearCache=1&ignoreBrowserCheck=1"; // Not Coding Standard
                //Eventually remove this since in code for 2.5.9 this is removed (ignoreBrowserCheck)
            }
            else
            {
                echo "Invalid params";
                exit;
            }

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
            curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error_info = curl_error($ch);
            curl_close($ch);

            if ($httpcode == 200)
            {
                return true;
            }
            else
            {
                echo $error_info;
                exit;
            }
        }

        /**
         * Determine is suite is installation test suite.
         * @param string $path
         * @return boolen
         */
        protected static function isInstallationTest($path)
        {
            $position = strpos($path, 'InstallationTestSuite.html');

            if ($position !== false)
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        /**
         * Determine is suite is actually default timezone test.
         * @param string $path
         * @return boolen
         */
        protected static function isDefaultTimeZoneTest($path)
        {
            $position = strpos($path, DIRECTORY_SEPARATOR . 'TestSuite.html');

            if ($position !== false)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    $testRunner = new TestSuite();
    $testRunner->run();
?>
