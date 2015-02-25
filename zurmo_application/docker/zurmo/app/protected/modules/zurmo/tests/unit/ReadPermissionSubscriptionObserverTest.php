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

    class ReadPermissionSubscriptionObserverTest extends ZurmoBaseTest
    {
        protected static $billy;
        protected static $johnny;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            self::$billy = UserTestHelper::createBasicUser('Billy');
            $group = Group::getByName('Super Administrators');
            $group->users->add(self::$billy);
            $group->save();

            self::$johnny = UserTestHelper::createBasicUser('Johnny');
            ContactsModule::loadStartingData();

            Yii::app()->readPermissionSubscriptionObserver->enabled = true;
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public static function tearDownAfterClass()
        {
            Yii::app()->readPermissionSubscriptionObserver->enabled = false;
            parent::tearDownAfterClass();
        }

        public function testOnCreateOwnerChangeAndDeleteModel()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            // Clean contact table
            $sql = "SELECT * FROM contact_read_subscription";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertTrue(empty($rows));

            $contact1 = ContactTestHelper::createContactByNameForOwner('Jason', $super);
            $sql = "SELECT * FROM contact_read_subscription";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(1, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($contact1->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            sleep(1);

            // Test deletion
            $contact1->delete();
            $sql = "SELECT * FROM contact_read_subscription";
            $rows2 = ZurmoRedBean::getAll($sql);
            $this->assertEquals(1, count($rows2));
            $this->assertEquals($super->id, $rows2[0]['userid']);
            $this->assertEquals($contact1->id, $rows2[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows2[0]['subscriptiontype']);
            $this->assertNotEquals($rows[0]['modifieddatetime'], $rows2[0]['modifieddatetime']);

            // Test owner change
            $sql = "DELETE FROM contact_read_subscription";
            ZurmoRedBean::exec($sql);
            $sql = "SELECT * FROM contact_read_subscription";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertTrue(empty($rows));

            $contact2 = ContactTestHelper::createContactByNameForOwner('Ray', $super);
            $sql = "SELECT * FROM contact_read_subscription";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(1, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($contact2->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);

            $contact2->owner = self::$billy;
            $this->assertTrue($contact2->save());
            $sql = "SELECT * FROM contact_read_subscription order by id";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($contact2->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[0]['subscriptiontype']);
            $this->assertEquals(self::$billy->id, $rows[1]['userid']);
            $this->assertEquals($contact2->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
        }

        public function testOnCreateOwnerChangeAndDeleteAccountModel()
        {
            $super = User::getByUsername('super');
            $billy = self::$billy;
            Yii::app()->user->userModel = $super;

            $job = new ReadPermissionSubscriptionUpdateForAccountFromBuildTableJob();
            Yii::app()->jobQueue->deleteAll();

            // Clean contact table
            $sql = "SELECT * FROM account_read_subscription";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertTrue(empty($rows));

            $account1 = AccountTestHelper::createAccountByNameForOwner('First Account', $super);
            sleep(1);

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));

            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account1->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($billy->id, $rows[1]['userid']);
            $this->assertEquals($account1->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
            sleep(1);

            // Test deletion
            $account1->delete();
            sleep(1);
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows2 = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows2));
            $this->assertEquals($super->id, $rows2[0]['userid']);
            $this->assertEquals($account1->id, $rows2[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows2[0]['subscriptiontype']);
            $this->assertNotEquals($rows[0]['modifieddatetime'], $rows2[0]['modifieddatetime']);
            $this->assertEquals($billy->id, $rows2[1]['userid']);
            $this->assertEquals($account1->id, $rows2[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows2[1]['subscriptiontype']);
            $this->assertNotEquals($rows[1]['modifieddatetime'], $rows2[1]['modifieddatetime']);

            // Test owner change, but when both users have permissions to access the account
            $sql = "DELETE FROM account_read_subscription";
            ZurmoRedBean::exec($sql);
            $sql = "SELECT * FROM account_read_subscription";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertTrue(empty($rows));

            $account2 = AccountTestHelper::createAccountByNameForOwner('Second Account', $super);
            sleep(1);
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));

            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account2->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($billy->id, $rows[1]['userid']);
            $this->assertEquals($account2->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
            sleep(1);

            $account2->owner = self::$billy;
            $this->assertTrue($account2->save());
            sleep(1);

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(2, count($rows));
            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account2->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals(self::$billy->id, $rows[1]['userid']);
            $this->assertEquals($account2->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);

            // Clean account table
            $accounts = Account::getAll();
            foreach ($accounts as $account)
            {
                $account->delete();
            }
            $sql = "DELETE FROM account_read_subscription";
            ZurmoRedBean::exec($sql);
            $johnny = self::$johnny;
            $account3 = AccountTestHelper::createAccountByNameForOwner('Third Account', $johnny);
            sleep(1);
            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(3, count($rows));

            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account3->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($billy->id, $rows[1]['userid']);
            $this->assertEquals($account3->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[2]['userid']);
            $this->assertEquals($account3->id, $rows[2]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[2]['subscriptiontype']);

            $account3Id = $account3->id;
            $account3->forgetAll();
            $account3 = Account::getById($account3Id);
            $this->assertTrue($account3->save());
            $account3->forgetAll();

            PermissionsCache::forgetAll();
            $account3 = Account::getById($account3Id);
            $account3->owner = $super;
            $this->assertTrue($account3->save());
            sleep(1);

            $queuedJobs = Yii::app()->jobQueue->getAll();
            $this->assertEquals(1, count($queuedJobs[5]));
            $this->assertEquals('ReadPermissionSubscriptionUpdateForAccountFromBuildTable', $queuedJobs[5][0]['jobType']);
            Yii::app()->jobQueue->deleteAll();
            $this->assertTrue($job->run());

            $sql = "SELECT * FROM account_read_subscription order by userid";
            $rows = ZurmoRedBean::getAll($sql);
            $this->assertEquals(3, count($rows));

            $this->assertEquals($super->id, $rows[0]['userid']);
            $this->assertEquals($account3->id, $rows[0]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[0]['subscriptiontype']);
            $this->assertEquals($billy->id, $rows[1]['userid']);
            $this->assertEquals($account3->id, $rows[1]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_ADD, $rows[1]['subscriptiontype']);
            $this->assertEquals($johnny->id, $rows[2]['userid']);
            $this->assertEquals($account3->id, $rows[2]['modelid']);
            $this->assertEquals(ReadPermissionsSubscriptionUtil::TYPE_DELETE, $rows[2]['subscriptiontype']);
        }
    }
?>