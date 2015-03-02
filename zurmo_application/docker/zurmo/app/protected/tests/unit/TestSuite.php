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

    // we don't want to be left in dark if any occurs occur.
    error_reporting(E_ALL);
    ini_set('display_errors', true);

    $cwd = getcwd();

    require_once('../common/PhpUnitServiceUtil.php');
    require_once('../common/testRoots.php');
    require_once('../common/bootstrap.php');

    class TestSuite
    {
        // these constants serve no purpose for PHPUnit
        // we return these when exiting inside this class under special circumstances
        // these are useful for chained invocations of TestSuite, say
        // like: phpunit TestSuite.php FirstTest && phpunit TestSuite.php SecondTest
        // if FirstTest can't be run due to any reason, say inexisting test or tempdir not writable, SecondTest
        // would never be run, which makes great sense considering "&&".
        // this would be super useful in a CI system, we could just look at return code
        //  instead of reading long strings
        const ERROR_INVOCATION_WITHOUT_TESTSUITE        = -1;

        const ERROR_WALKTHROUGH_AND_BENCHMARK_SELECTED  = -2;

        const ERROR_TEST_NOT_FOUND                      = -3;

        const ERROR_TEMP_DIR_NOT_WRITABLE               = -4;

        protected static $dependentTestModelClassNames  = array();

        public static function suite()
        {
            global $argv;

            PhpUnitServiceUtil::checkVersion();
            $usage = PHP_EOL                                                                                                    .
                "  Usage: phpunit [phpunit options] TestSuite.php <All|Framework|Misc|moduleName|TestClassName> [custom options]" . PHP_EOL .
                PHP_EOL                                                                                                    .
                "    All                     Run all tests." . PHP_EOL                                                    .
                "    Framework               Run the tests in app/protected/extensions/framework/tests/unit." . PHP_EOL          .
                "    Misc                    Run the tests in app/protected/tests/unit." . PHP_EOL                               .
                "    moduleName              Run the tests in app/protected/modules/moduleName/tests/unit." . PHP_EOL            .
                "    TestClassName           Run the tests in TestClassName.php, wherever that happens to be." . PHP_EOL         .
                PHP_EOL                                                                                                    .
                "  Custom Options:" . PHP_EOL                                                                                   .
                PHP_EOL                                                                                                    .
                "    --only-walkthroughs     For the specified test, only includes tests under a walkthroughs directory." . PHP_EOL .
                "    --exclude-walkthroughs  For the specified test, exclude tests under a walkthroughs directory." . PHP_EOL       .
                "    --only-benchmarks       For the specified test, only includes tests under a benchmarks directory." . PHP_EOL .
                "    --exclude-benchmarks    For the specified test, exclude tests under a benchmarks directory." . PHP_EOL      .
                "    --reuse-schema          Reload a previously auto build database. (Will auto build if there is no" . PHP_EOL .
                "                            previous one. The auto built schema is dumped to the system temp dir in" . PHP_EOL  .
                "                            autobuild.sql.)" . PHP_EOL                                                          .
                PHP_EOL                                                                                                    .
                "  Examples:" . PHP_EOL                                                                                         .
                PHP_EOL                                                                                                    .
                "    phpunit --verbose TestSuite.php accounts (Run the tests in the Accounts module.)" . PHP_EOL                . // Not Coding Standard
                "    phpunit TestSuite.php RedBeanModelTest   (Run the tests in RedBeanModelTest.php.)" . PHP_EOL               .
                PHP_EOL                                                                                                    .
                "    To run specific tests use the phpunit --filter <regex> option." . PHP_EOL                                  . // Not Coding Standard
                "    phpunit has its own options. Check phpunit --help." . PHP_EOL . PHP_EOL;                                             // Not Coding Standard

            $onlyWalkthroughs     =  self::customOptionSet('--only-walkthroughs',     $argv);
            $excludeWalkthroughs  =  self::customOptionSet('--exclude-walkthroughs',  $argv);
            $onlyBenchmarks       =  self::customOptionSet('--only-benchmarks',       $argv);
            $excludeBenchmarks    =  self::customOptionSet('--exclude-benchmarks',    $argv);
            $reuse                =  self::customOptionSet('--reuse-schema',          $argv);

            if ($argv[count($argv) - 2] != 'TestSuite.php')
            {
                echo $usage;
                exit(static::ERROR_INVOCATION_WITHOUT_TESTSUITE);
            }

            if ($onlyWalkthroughs && $onlyBenchmarks)
            {
                echo $usage;
                echo "It doesn't have sense to select both \"--only-walkthroughs\" and \"--only-benchmarks\" options. " . PHP_EOL . PHP_EOL;
                exit(static::ERROR_WALKTHROUGH_AND_BENCHMARK_SELECTED);
            }

            $whatToTest           = $argv[count($argv) - 1];
            $includeUnitTests     = !$onlyWalkthroughs && !$onlyBenchmarks;
            $includeWalkthroughs  = !$excludeWalkthroughs && !$onlyBenchmarks;
            $includeBenchmarks    = !$excludeBenchmarks && !$onlyWalkthroughs;

            $suite = new PHPUnit_Framework_TestSuite();
            $suite->setName("$whatToTest Tests");
            self::buildAndAddSuiteFromDirectory($suite, 'Framework', COMMON_ROOT . '/protected/core/tests/unit', $whatToTest, true, false, $includeBenchmarks);
            $moduleDirectoryName = COMMON_ROOT . '/protected/modules';
            if (is_dir($moduleDirectoryName))
            {
                $moduleNames = scandir($moduleDirectoryName);
                foreach ($moduleNames as $moduleName)
                {
                    if ($moduleName != '.' &&
                        $moduleName != '..')
                    {
                        $moduleUnitTestDirectoryName = "$moduleDirectoryName/$moduleName/tests/unit";
                        self::buildAndAddSuiteFromDirectory($suite, $moduleName, $moduleUnitTestDirectoryName, $whatToTest, $includeUnitTests, $includeWalkthroughs, $includeBenchmarks);
                    }
                }
            }
            self::buildAndAddSuiteFromDirectory($suite, 'Misc',            COMMON_ROOT . '/protected/tests/unit',                     $whatToTest, $includeUnitTests, $includeWalkthroughs, $includeBenchmarks);
            self::buildAndAddSuiteFromDirectory($suite, 'Commands',        COMMON_ROOT . '/protected/commands/tests/unit',             $whatToTest, $includeUnitTests, $includeWalkthroughs, $includeBenchmarks);
////////////////////////////////////////////////////////////////////////////////
// Temporary - See Readme.txt in the notSupposedToBeHere directory.
            self::buildAndAddSuiteFromDirectory($suite, 'BadDependencies', COMMON_ROOT . '/protected/tests/unit/notSupposedToBeHere', $whatToTest, $includeUnitTests, $includeWalkthroughs, $includeBenchmarks);
////////////////////////////////////////////////////////////////////////////////

            if ($suite->count() == 0)
            {
                echo $usage;
                echo "  No tests found for '$whatToTest'." . PHP_EOL . PHP_EOL;
                exit(static::ERROR_TEST_NOT_FOUND);
            }

            echo "Testing with database: '"  . Yii::app()->db->connectionString . '\', ' .
                                                'username: \'' . Yii::app()->db->username         . "'." . PHP_EOL;

            static::setupDatabaseConnection();

            // get rid of any caches from last execution, this ensure we rebuild any required tables
            // without this some of many_many tables have issues as we use cache to determine
            // if we need to rebuild those.
            ForgetAllCacheUtil::forgetAllCaches();

            $template        = "{message}\n";
            $messageStreamer = new MessageStreamer($template);
            $messageStreamer->setExtraRenderBytes(0);
            $messageLogger = new MessageLogger($messageStreamer);
            $messageLogger->logDateTimeStamp = false;
            if (!$reuse)
            {
                if (!is_writable(sys_get_temp_dir()))
                {
                    echo PHP_EOL .PHP_EOL . "Temp directory must be writable to store reusable schema" . PHP_EOL; // Not Coding Standard
                    echo "Temp directory: " . sys_get_temp_dir() .  PHP_EOL . PHP_EOL; // Not Coding Standard
                    exit(static::ERROR_TEMP_DIR_NOT_WRITABLE);
                }
                echo "Auto building database schema..." . PHP_EOL;
                ZurmoRedBean::$writer->wipeAll();
                InstallUtil::autoBuildDatabase($messageLogger, true);
                $messageLogger->printMessages();
                // recreate all tables, we know there aren't existing because we just did a wipeAll();
                static::rebuildReadPermissionsTables(true, true, $messageStreamer);
                assert('RedBeanDatabase::isSetup()');
                Yii::app()->user->userModel = InstallUtil::createSuperUser('super', 'super');

                echo "Saving auto built schema..." . PHP_EOL;
                $schemaFile = sys_get_temp_dir() . '/autobuilt.sql';
                $success = preg_match("/;dbname=([^;]+)/", Yii::app()->db->connectionString, $matches); // Not Coding Standard
                assert('$success == 1');
                $databaseName = $matches[1];

                $systemOutput = system('mysqldump -u' . Yii::app()->db->username .
                                        ' -p' . Yii::app()->db->password .
                                        ' ' . $databaseName            .
                                        " > $schemaFile");
                if ($systemOutput != null)
                {
                    echo 'Dumping schema using system command. Output: ' . $systemOutput . PHP_EOL . PHP_EOL;
                }
            }
            else
            {
                echo PHP_EOL;
                static::buildDependentTestModels($messageLogger);
                $messageLogger->printMessages();
            }
            echo PHP_EOL;
            static::closeDatabaseConnection();
            return $suite;
        }

        protected static function rebuildReadPermissionsTables($forceOverwrite, $forcePhp, $messageStreamer)
        {
            echo 'Rebuilding read permissions' . PHP_EOL;
            AllPermissionsOptimizationUtil::rebuild($forceOverwrite, $forcePhp, $messageStreamer);
            echo 'Read permissions rebuild complete.' . PHP_EOL;
        }

        public static function customOptionSet($customOption, &$argv)
        {
            $set = in_array($customOption, $argv);
            $argv = array_values(array_diff($argv, array($customOption)));
            return $set;
        }

        public static function buildAndAddSuiteFromDirectory($parentSuite, $name, $directoryName, $whatToTest, $includeUnitTests, $includeWalkthroughs, $includeBenchmarks)
        {
            if ($includeUnitTests)
            {
                self::buildAndAddSuiteFromDirectory2($parentSuite, $name, $directoryName,                  $whatToTest);
            }
            if ($includeWalkthroughs)
            {
                self::buildAndAddSuiteFromDirectory2($parentSuite, $name, $directoryName . '/walkthrough', $whatToTest);
            }
            if ($includeBenchmarks)
            {
                self::buildAndAddSuiteFromDirectory2($parentSuite, $name, $directoryName . '/benchmark', $whatToTest);
            }
        }

        public static function buildAndAddSuiteFromDirectory2($parentSuite, $name, $directoryName, $whatToTest)
        {
            assert('is_string($directoryName) && $directoryName != ""');
            if (is_dir($directoryName))
            {
                $suite = new PHPUnit_Framework_TestSuite();
                $suite->setName(ucfirst($name) . ' Tests');
                $fileNames = scandir($directoryName);
                foreach ($fileNames as $fileName)
                {
                    if (substr($fileName, strlen($fileName) - strlen('Test.php')) == 'Test.php')
                    {
                        require_once("$directoryName/$fileName");
                        $className = substr($fileName, 0, strlen($fileName) - 4);
                        if (substr($className, strlen($className) - 8) != 'BaseTest')
                        {
                            if ($whatToTest == 'All'                                           ||
                                $whatToTest == 'Framework'       && $name == 'Framework'       ||
                                $whatToTest == 'Misc'            && $name == 'Misc'            ||
                                $whatToTest == 'BadDependencies' && $name == 'BadDependencies' ||
                                $whatToTest == $name                                           ||
                                $whatToTest == $className)
                            {
                                if (@class_exists($className, false))
                                {
                                    $suite->addTestSuite(new PHPUnit_Framework_TestSuite($className));
                                    static::resolveDependentTestModelClassNamesForClass($className, $directoryName);
                                }
                            }
                        }
                    }
                }
                if ($suite->count() > 0)
                {
                    $parentSuite->addTestSuite($suite);
                }
            }
        }

        public static function buildDependentTestModels($messageLogger)
        {
            RedBeanModelsToTablesAdapter::generateTablesFromModelClassNames(static::$dependentTestModelClassNames,
                                                                                                $messageLogger);
            static::buildReadPermissionsOptimizationTableForTestModels();
        }

        protected static function resolveDependentTestModelClassNamesForClass($className, $directoryName)
        {
            $dependentTestModelClassNames = $className::getDependentTestModelClassNames();
            if (!empty($dependentTestModelClassNames))
            {
                $dependentTestModelClassNames = CMap::mergeArray(static::$dependentTestModelClassNames,
                                                                    $dependentTestModelClassNames);
                static::$dependentTestModelClassNames = array_unique($dependentTestModelClassNames);
            }
        }

        protected static function buildReadPermissionsOptimizationTableForTestModels()
        {
            foreach (static::$dependentTestModelClassNames as $modelClassName)
            {
                if (is_subclass_of($modelClassName, 'SecurableItem') && $modelClassName::hasReadPermissionsOptimization())
                {
                    ReadPermissionsOptimizationUtil::recreateTable(ReadPermissionsOptimizationUtil::getMungeTableName(
                                                                                                    $modelClassName));
                }
            }
        }

        protected static function setupDatabaseConnection($force = false)
        {
            if (!RedBeanDatabase::isSetup() || $force)
            {
                RedBeanDatabase::setup(Yii::app()->db->connectionString,
                                        Yii::app()->db->username,
                                        Yii::app()->db->password);
            }
        }

        protected static function closeDatabaseConnection()
        {
            if (RedBeanDatabase::isSetup())
            {
                RedBeanDatabase::close();
                assert('!RedBeanDatabase::isSetup()');
            }
        }
    }
?>