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

    class AccountDedupeRulesTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $user = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $user;
            $accountData = array(
                'Account1' => '123-456-789',
                'Account2' => '123-456-789',
                'Account3' => '123-456-789',
                'Account4' => '123-456-789',
                'Account5' => '123-456-789',
                'Account6' => '987-654-321',
            );
            foreach ($accountData as $name => $phone)
            {
                $account = new Account();
                $account->name         = $name;
                $account->officePhone  = $phone;
                assert($account->save()); // Not Coding Standard
            }
        }

        public function setUp()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testGetDedupeViewClassName()
        {
            $account     = new Account();
            $dedupeRules = new AccountDedupeRules($account);
            $this->assertEquals('CreateModelsToMergeListAndChartView', $dedupeRules->getDedupeViewClassName());
        }

        public function testRegisterScriptForEditAndDetailsView()
        {
            $account     = new Account();
            $dedupeRules = new AccountDedupeRules($account);
            //Dedupe elements
            $element     = new TextElement($account, 'name');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $this->assertTrue(Yii::app()->clientScript->isScriptRegistered('DedupeRulesAccount_name#dedupe-for-edit-and-details-view'));
            $element     = new PhoneElement($account, 'officePhone');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $this->assertTrue(Yii::app()->clientScript->isScriptRegistered('DedupeRulesAccount_officePhone#dedupe-for-edit-and-details-view'));
            //Non dedupe elements
            $element     = new DropDownElement($account, 'industry');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new PhoneElement($account, 'officeFax');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new IntegerElement($account, 'employees');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new DecimalElement($account, 'annualRevenue');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new DropDownElement($account, 'type');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new UrlElement($account, 'website');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new AddressElement($account, 'billingAddress');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new AddressElement($account, 'shipingAddress');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new TextAreaElement($account, 'description');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));

            //Only the dedupe elements needs the script registered
            $this->assertCount(2, array_pop(Yii::app()->clientScript->scripts));
        }

        public function testSearchForDuplicateModels()
        {
            $account      = new Account();
            $dedupeRules  = new AccountDedupeRules($account);
            $searchResult = $dedupeRules->searchForDuplicateModels('name', 'Account1');
            $this->assertEquals('There is 1 possible match. <span class="underline">Click here</span> to view.', $searchResult['message']);
            $searchResult = $dedupeRules->searchForDuplicateModels('name', 'account');
            $this->assertEquals('There are at least 5 possible matches. <span class="underline">Click here</span> to view.', $searchResult['message']);
            $this->assertCount (6, $searchResult['matchedModels']);
            $searchResult = $dedupeRules->searchForDuplicateModels('officePhone', '123-456-789');
            $this->assertEquals('There are 5 possible matches. <span class="underline">Click here</span> to view.', $searchResult['message']);
            $this->assertCount (5, $searchResult['matchedModels']);
            $searchResult = $dedupeRules->searchForDuplicateModels('officePhone', '987-654-321');
            $this->assertEquals('There is 1 possible match. <span class="underline">Click here</span> to view.', $searchResult['message']);
            $this->assertCount (1, $searchResult['matchedModels']);
            try
            {
                $dedupeRules->searchForDuplicateModels('annualRevenue', '123456');
                $this->fail();
            }
            catch (NotImplementedException $exception)
            {
                $this->assertEquals('There is no search callback defined for attribute: annualRevenue', $exception->getMessage());
            }
        }
    }
?>