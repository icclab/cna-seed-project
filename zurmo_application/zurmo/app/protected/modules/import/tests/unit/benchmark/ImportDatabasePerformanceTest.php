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

    class ImportDatabasePerformanceTest extends ImportBaseTest
    {
        protected static $temporaryFilePath = null;

        protected static $testCounter = 0;

        protected static $csvFileSizesToTest = array(1, 5, 10, 50, 100);

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            static::generateCsvAndReturnFileName(static::$csvFileSizesToTest[static::$testCounter]);
            static::$testCounter++;
        }

        public function testBulkInsertWith1M()
        {
            $this->bulkInsertTestWithSpecificSize(1);
        }

        /**
         * @depends testBulkInsertWith1M
         */
        public function testBulkInsertWith5M()
        {
            $this->bulkInsertTestWithSpecificSize(5);
        }

        /**
         * @depends testBulkInsertWith5M
         */
        public function testBulkInsertWith10M()
        {
            $this->bulkInsertTestWithSpecificSize(10);
        }

        /**
         * @depends testBulkInsertWith10M
         */
        public function testBulkInsertWith50M()
        {
            $this->bulkInsertTestWithSpecificSize(50);
        }

        /**
         * Disabled because on sandbox1 it gave memory exhaustion errors
         * @depends testBulkInsertWith50M
         */
        public function disabled_testBulkInsertWith100M()
        {
            $this->bulkInsertTestWithSpecificSize(100);
        }

        protected function bulkInsertTestWithSpecificSize($sizeInMB)
        {
            $startTime              = microtime(true);
            $expectedRecordCount    = ($sizeInMB * 1018);
            $testTableName          = "testimporttable";
            $fileName               = basename(static::$temporaryFilePath);
            $dirName                = dirname(static::$temporaryFilePath);
            $this->assertTrue(ImportTestHelper::createTempTableByFileNameAndTableName($fileName,
                                                                                        $testTableName,
                                                                                        false,
                                                                                        $dirName));
            $count = ImportDatabaseUtil::getCount($testTableName);
            $this->assertEquals($expectedRecordCount, $count);
            $endTime = microtime(true);
            $difference = number_format(($endTime - $startTime), 3);
            echo "${sizeInMB}MB (${expectedRecordCount} rows) CSV test: ${difference} seconds." . PHP_EOL . PHP_EOL;
        }

        protected static function generateCsvAndReturnFileName($sizeInMB)
        {
            echo "Setting up dummy csv file of ${sizeInMB}MB" . PHP_EOL;
            $startTime              = microtime(true);
            if (!isset(static::$temporaryFilePath))
            {
                static::$temporaryFilePath = tempnam(sys_get_temp_dir(), "benchmark_csv_");
            }
            if (file_exists(static::$temporaryFilePath))
            {
                $existingSize = floor(filesize(static::$temporaryFilePath)/1048576);
                $sizeInMB -= $existingSize;
            }
            $itemCount          = $sizeInMB * 1018;
            $totalLength        = 1024; // how long each each row should be in total.
            $data               = array();
            for ($i = 0; $i < $itemCount; $i++)
            {
                $remainingLength = $totalLength;
                do
                {
                    $lengthOfCurrentSegment = rand(1, $remainingLength);
                    $data[$i][] = str_repeat('_', $lengthOfCurrentSegment);
                    $remainingLength -= $lengthOfCurrentSegment;
                } while ($remainingLength > 0);
            }

            $csv = ExportItemToCsvFileUtil::export($data);
            // this doesn't matter even if its not binary, no special characters in this string
            file_put_contents(static::$temporaryFilePath, $csv, FILE_APPEND);
            $endTime = microtime(true);
            $difference = number_format(($endTime - $startTime), 3);
            echo "CSV generated in: ${difference} seconds." . PHP_EOL;
        }
    }
?>
