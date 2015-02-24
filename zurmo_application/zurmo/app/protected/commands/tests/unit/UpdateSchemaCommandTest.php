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

    class UpdateSchemaCommandTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            $this->assertTrue(ContactsModule::loadStartingData());
        }

        public function tearDown()
        {
            parent::tearDown();
            ContactState::deleteAll();
        }

        public function testRunWithoutAnyValueForOverwriteExistingReadTables()
        {
            $output = array();
            $this->runUpdateSchema($output);
            $this->assertTrue(array_search('Info  - Schema generation completed', $output) !== false);
            $this->assertTrue(array_search('Skipping existing read Tables.', $output) !== false);
            $this->assertTrue(array_search('Skipping account_read', $output) !== false);
            $this->assertTrue(array_search('Skipping campaign_read', $output) !== false);
            $this->assertTrue(array_search('Skipping contact_read', $output) !== false);
            $this->assertTrue(array_search('Skipping conversation_read', $output) !== false);
            $this->assertTrue(array_search('Skipping emailmessage_read', $output) !== false);
            $this->assertTrue(array_search('Skipping emailtemplate_read', $output) !== false);
            $this->assertTrue(array_search('Skipping gamereward_read', $output) !== false);
            $this->assertTrue(array_search('Skipping marketinglist_read', $output) !== false);
            $this->assertTrue(array_search('Skipping meeting_read', $output) !== false);
            $this->assertTrue(array_search('Skipping mission_read', $output) !== false);
            $this->assertTrue(array_search('Skipping note_read', $output) !== false);
            $this->assertTrue(array_search('Skipping opportunity_read', $output) !== false);
            $this->assertTrue(array_search('Skipping savedreport_read', $output) !== false);
            $this->assertTrue(array_search('Skipping product_read', $output) !== false);
            $this->assertTrue(array_search('Skipping socialitem_read', $output) !== false);
            $this->assertTrue(array_search('Skipping task_read', $output) !== false);
            $this->assertTrue(array_search('Skipping contactwebform_read', $output) !== false);
            $this->assertTrue(array_search('Skipping project_read', $output) !== false);
            $this->assertTrue(array_search('Schema update complete.', $output) !== false);
        }

        /**
         * @depends testRunWithoutAnyValueForOverwriteExistingReadTables
         */
        public function testRunWithOverwriteExistingReadTablesSetToZero()
        {
            $output = array();
            $this->runUpdateSchema($output, 0);
            $this->assertTrue(array_search('Info  - Schema generation completed', $output) !== false);
            $this->assertTrue(array_search('Skipping existing read Tables.', $output) !== false);
            $this->assertTrue(array_search('Skipping account_read', $output) !== false);
            $this->assertTrue(array_search('Skipping campaign_read', $output) !== false);
            $this->assertTrue(array_search('Skipping contact_read', $output) !== false);
            $this->assertTrue(array_search('Skipping conversation_read', $output) !== false);
            $this->assertTrue(array_search('Skipping emailmessage_read', $output) !== false);
            $this->assertTrue(array_search('Skipping emailtemplate_read', $output) !== false);
            $this->assertTrue(array_search('Skipping gamereward_read', $output) !== false);
            $this->assertTrue(array_search('Skipping marketinglist_read', $output) !== false);
            $this->assertTrue(array_search('Skipping meeting_read', $output) !== false);
            $this->assertTrue(array_search('Skipping mission_read', $output) !== false);
            $this->assertTrue(array_search('Skipping note_read', $output) !== false);
            $this->assertTrue(array_search('Skipping opportunity_read', $output) !== false);
            $this->assertTrue(array_search('Skipping savedreport_read', $output) !== false);
            $this->assertTrue(array_search('Skipping product_read', $output) !== false);
            $this->assertTrue(array_search('Skipping socialitem_read', $output) !== false);
            $this->assertTrue(array_search('Skipping task_read', $output) !== false);
            $this->assertTrue(array_search('Skipping contactwebform_read', $output) !== false);
            $this->assertTrue(array_search('Skipping project_read', $output) !== false);
            $this->assertTrue(array_search('Schema update complete.', $output) !== false);
        }

        /**
         * @depends testRunWithOverwriteExistingReadTablesSetToZero
         */
        public function testRunWithOverwriteExistingReadTablesSetToOne()
        {
            $output = array();
            $this->runUpdateSchema($output, 1);
            $this->assertTrue(array_search('Info  - Schema generation completed', $output) !== false);
            $this->assertTrue(array_search('Overwriting any existing read Tables.', $output) !== false);
            $this->assertTrue(array_search('Building account_read', $output) !== false);
            $this->assertTrue(array_search('Building campaign_read', $output) !== false);
            $this->assertTrue(array_search('Building contact_read', $output) !== false);
            $this->assertTrue(array_search('Building conversation_read', $output) !== false);
            $this->assertTrue(array_search('Building emailmessage_read', $output) !== false);
            $this->assertTrue(array_search('Building emailtemplate_read', $output) !== false);
            $this->assertTrue(array_search('Building gamereward_read', $output) !== false);
            $this->assertTrue(array_search('Building marketinglist_read', $output) !== false);
            $this->assertTrue(array_search('Building meeting_read', $output) !== false);
            $this->assertTrue(array_search('Building mission_read', $output) !== false);
            $this->assertTrue(array_search('Building note_read', $output) !== false);
            $this->assertTrue(array_search('Building opportunity_read', $output) !== false);
            $this->assertTrue(array_search('Building savedreport_read', $output) !== false);
            $this->assertTrue(array_search('Building product_read', $output) !== false);
            $this->assertTrue(array_search('Building socialitem_read', $output) !== false);
            $this->assertTrue(array_search('Building task_read', $output) !== false);
            $this->assertTrue(array_search('Building contactwebform_read', $output) !== false);
            $this->assertTrue(array_search('Building project_read', $output) !== false);
            $this->assertTrue(array_search('Schema update complete.', $output) !== false);
        }

        protected function runUpdateSchema(& $output, $overwriteExistingReadTables = null)
        {
            $messageLogger              = new MessageLogger();
            InstallUtil::autoBuildDatabase($messageLogger, true);

            chdir(COMMON_ROOT . DIRECTORY_SEPARATOR . 'protected' . DIRECTORY_SEPARATOR . 'commands');

            $command = "php zurmocTest.php updateSchema super " . $overwriteExistingReadTables;
            //echo PHP_EOL . "Executing : $command" . PHP_EOL;

            if (!IS_WINNT)
            {
                $command .= ' 2>&1';
            }
            exec($command, $output);
        }
    }
?>