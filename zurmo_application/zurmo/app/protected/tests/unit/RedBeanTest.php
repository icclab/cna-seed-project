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
    // This is for testing details of how RedBean works.
    class RedBeanTest extends BaseTest
    {
        public static function getDependentTestModelClassNames()
        {
            return array('Thing', 'Wukka');
        }

        public function testZeros()
        {
            $thing = ZurmoRedBean::dispense('thing');
            $thing->zero = 0;
            ZurmoRedBean::store($thing);
            $id = $thing->id;
            unset($thing);
            $thing = ZurmoRedBean::load('thing', $id);
            $this->assertEquals(0, $thing->zero);

            //Try saving a second thing.
            $thing = ZurmoRedBean::dispense('thing');
            $thing->zero = 2;
            ZurmoRedBean::store($thing);
            $id = $thing->id;
            unset($thing);
            $thing = ZurmoRedBean::load('thing', $id);
            $this->assertEquals(2, $thing->zero);
        }

        public function testNulls()
        {
            $thing = ZurmoRedBean::dispense('thing');
            $thing->zero = null;
            ZurmoRedBean::store($thing);
            $id = $thing->id;
            unset($thing);
            $thing = ZurmoRedBean::load('thing', $id);
            $this->assertEquals(null, $thing->zero);
        }

        /**
         * @expectedException RedBean_Exception_SQL
         */
        public function testGetAllTableFromInexistantTableThrowsException()
        {
            $sql = 'select id from atableneverhere';
            $rows = ZurmoRedBean::getAll($sql);
        }

        public function testStringContainingOnlyNumbers()
        {
            $thing = ZurmoRedBean::dispense('thing');
            $thing->phoneNumberNumber  = 5551234;
            $thing->phoneNumberString1 = '555-1234';
            $thing->phoneNumberString2 = '5551234';
            ZurmoRedBean::store($thing);
            $databaseType = ZurmoRedBean::$toolbox->getDatabaseAdapter()->getDatabase()->getDatabaseType();
            switch ($databaseType)
            {
                case 'mysql':
                    $unsigned = null;
                    if (!RedBeanModelMemberRulesToColumnAdapter::ASSUME_SIGNED)
                    {
                        $unsigned = ' unsigned';
                    }
                    $sql = 'desc thing;';
                    $rows = ZurmoRedBean::getAll($sql);
                    $this->assertEquals('phonenumbernumber',        $rows[2]['Field']);
                    $this->assertEquals('int(11)' . $unsigned,      $rows[2]['Type']);
                    $this->assertEquals('phonenumberstring1',       $rows[3]['Field']);
                    $this->assertEquals('varchar(255)',             $rows[3]['Type']);
                    $this->assertEquals('phonenumberstring2',       $rows[4]['Field']);
                    $this->assertEquals('varchar(255)',             $rows[4]['Type']);
                    break;

                case 'sqlite':
                    $sql  = 'pragma table_info(\'thing\');';
                    $rows = ZurmoRedBean::getAll($sql);
                    $this->assertEquals('phoneNumberNumber',   $rows[2]['name']);
                    $this->assertEquals('INTEGER',             $rows[2]['type']);
                    $this->assertEquals('phoneNumberString1',  $rows[3]['name']);
                    $this->assertEquals('TEXT',                $rows[3]['type']);
                    $this->assertEquals('phoneNumberString2',  $rows[4]['name']);
                    $this->assertEquals('INTEGER',             $rows[4]['type']);
                    break;

                case 'pgsql':
                    $sql = 'select column_name, data_type from information_schema.columns where table_name = \'thing\' and column_name like \'phone%\' order by column_name;';
                    $rows = ZurmoRedBean::getAll($sql);
                    $this->assertEquals('phonenumbernumber',   $rows[0]['column_name']);
                    $this->assertEquals('integer',             $rows[0]['data_type']);
                    $this->assertEquals('phonenumberstring1',  $rows[1]['column_name']);
                    $this->assertEquals('text',                $rows[1]['data_type']);
                    $this->assertEquals('phonenumberstring2',  $rows[2]['column_name']);
                    $this->assertEquals('text',                $rows[2]['data_type']);
                    break;

                default:
                    $this->fail('Test does not support database type: ' . $databaseType);
            }
        }

        public function testRedBeanTypesShowingPDODodginess()
        {
            $wukka = ZurmoRedBean::dispense('wukka');
            $wukka->integer = 69;
            $wukka->string  = 'xxx';
            ZurmoRedBean::store($wukka);
            $this->assertEquals('integer', gettype($wukka->integer));
            $this->assertEquals('string',  gettype($wukka->string));
            $this->assertTrue  ($wukka->integer !== $wukka->string);
            $id = $wukka->id;
            unset($wukka);

            $databaseType = ZurmoRedBean::$toolbox->getDatabaseAdapter()->getDatabase()->getDatabaseType();
            switch ($databaseType)
            {
                case 'mysql':
                    $unsigned = null;
                    if (!RedBeanModelMemberRulesToColumnAdapter::ASSUME_SIGNED)
                    {
                        $unsigned = ' unsigned';
                    }
                    $sql = 'desc wukka;';
                    $rows = ZurmoRedBean::getAll($sql);
                    $this->assertEquals('integer',                  $rows[1]['Field']);
                    $this->assertEquals('smallint(11)' . $unsigned, $rows[1]['Type']);
                    $this->assertEquals('string',                   $rows[2]['Field']);
                    $this->assertEquals('varchar(255)',             $rows[2]['Type']);
                    break;
            }

            $wukka = ZurmoRedBean::load('wukka', $id);
            $this->assertEquals('string', gettype($wukka->integer)); // Dodgy.
            $this->assertEquals('string', gettype($wukka->string));
            $this->assertTrue  ($wukka->integer !== $wukka->string);
        }

        public function testGetBeanWhenThereIsNoneToGet()
        {
            $bean = ZurmoRedBean::dispense('a');
            $bean2 = ZurmoRedBean::relatedOne($bean, 'b');

            $this->assertTrue($bean2 === null);
        }

        public function testUniqueMeta()
        {
            $this->markTestSkipped("Test does not apply any more due to new autobuild scheme, no longer using setMeta");
            $bean = ZurmoRedBean::dispense('wukka');
            $bean->setMeta("buildcommand.unique", array(array("string")));

            $bean->string = 'Pablo';
            ZurmoRedBean::store($bean);

            $bean2 = ZurmoRedBean::dispense('wukka');
            $bean2->string = 'Pablo';

            try
            {
                ZurmoRedBean::store($bean2);
                $this->fail('Expected a RedBean_Exception_SQL: Integrity constraint violation');
            }
            catch (RedBean_Exception_SQL $e)
            {
                $message = "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'Pablo'";
                $this->assertEquals($message, substr($e->getMessage(), 0, strlen($message)));
            }
        }

        public function testExampleStoredProcedure()
        {
            $wukka = ZurmoRedBean::dispense('wukka');
            $wukka->integer = 666;
            $wukka->string  = 'yyy';
            ZurmoRedBean::store($wukka);
            try
            {
                ZurmoRedBean::exec("drop procedure get_wukka_integer");
            }
            catch (Exception $e)
            {
            }
            ZurmoRedBean::exec("
                create procedure get_wukka_integer(in the_string varchar(255), out the_integer int(11))
                begin
                    select wukka.integer
                    into the_integer
                    from wukka
                    where wukka.string = the_string;
                end
            ");
            ZurmoRedBean::exec("call get_wukka_integer('yyy', @the_integer)");
            $this->assertEquals(666, ZurmoRedBean::getCell("select @the_integer"));
        }

        /**
         * @depends testExampleStoredProcedure
         */
        public function testExampleStoredFunction()
        {
            try
            {
                ZurmoRedBean::exec("drop function get_wukka_integer2");
            }
            catch (Exception $e)
            {
            }
            ZurmoRedBean::exec("
                create function get_wukka_integer2(the_string varchar(255))
                returns int(11)
                begin
                    declare the_integer int(11);
                    select wukka.integer
                    into the_integer
                    from wukka
                    where wukka.string = the_string;
                    return the_integer;
                end
            ");
            $this->assertEquals(666, ZurmoRedBean::getCell("select get_wukka_integer2('yyy')"));
        }

        public function testCascadedDeleteDoesNotWorkForLinkedBeans()
        {
            $member = ZurmoRedBean::dispense('marketinglistmember');
            $member->unsubscribed = true;
            ZurmoRedBean::store($member);

            $list = ZurmoRedBean::dispense('marketinglist');
            $list->name = 'dummy';
            ZurmoRedBean::store($list);

            ZurmoRedBeanLinkManager::link($member, $list);
            ZurmoRedBean::store($member);

            $id = $list->id;
            unset($list);

            ZurmoRedBean::trash($member);
            unset($member);

            $list = ZurmoRedBean::load('marketinglist', $id);
            $this->assertNotNull($list); // The list is not deleted.
        }

        public function testDateTimeFields()
        {
            $toolbox = ZurmoRedBeanSetup::kickstart(Yii::app()->db->connectionString,
                                                Yii::app()->db->username,
                                                Yii::app()->db->password);

            $redbean = $toolbox->getRedBean();

            $helper = new RedBean_ModelHelper();
            $redbean->addEventListener('update', $helper);

            for ($i = 1; $i < 10; $i++)
            {
                $person = ZurmoRedBean::dispense("person");
                $person->name = "bill$i";
                $person->date1 = time();
                $person->date2 = date('Y-m-d H:i:s');
                $redbean->store($person);
            }
            // TODO: to be continued...
        }

        public function testDateTimeHinting()
        {
            $bean = ZurmoRedBean::dispense("wukka");                            // Not Coding Standard
            $bean->setMeta("hint",array("prop"=>"datetime"));           // Not Coding Standard
            $bean->prop = "2010-01-01 10:00:00";                    // Not Coding Standard
            ZurmoRedBean::store($bean);                                        // Not Coding Standard

            $rows = ZurmoRedBean::getAll('desc wukka');
            $this->assertEquals('prop',     $rows[3]['Field']);
            $this->assertEquals('datetime', $rows[3]['Type']);
        }
    }
?>
