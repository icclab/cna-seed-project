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

    class ContactDedupeRulesTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            $user = SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = $user;
            ContactsModule::loadStartingData();
            $contactData = array(
                'Sam'   => '123-456-789',
                'Sally' => '123-456-789',
                'Sarah' => '123-456-789',
                'Jason' => '123-456-789',
                'James' => '123-456-789',
                'Roger' => '987-654-321',
            );
            $contactStates = ContactState::getAll();
            $lastContactState  = $contactStates[count($contactStates) - 1];
            foreach ($contactData as $firstName => $phone)
            {
                $contact = new Contact();
                $contact->title->value = 'Mr.';
                $contact->firstName    = $firstName;
                $contact->lastName     = 'son';
                $contact->owner        = $user;
                $contact->state        = $lastContactState;
                $contact->mobilePhone  = $phone;
                $contact->officePhone  = $phone . 'X';
                $contact->primaryEmail->emailAddress = strtolower($firstName) . '@zurmoland.com';
                $contact->secondaryEmail->emailAddress = strtolower($firstName) . '@zurmoworld.com';
                $contact->save();
            }
        }

        public function setUp()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testGetDedupeViewClassName()
        {
            $contact     = new Contact();
            $dedupeRules = new ContactDedupeRules($contact);
            $this->assertEquals('CreateModelsToMergeListAndChartView', $dedupeRules->getDedupeViewClassName());
        }

        public function testRegisterScriptForEditAndDetailsView()
        {
            $contact     = new Contact();
            $dedupeRules = new ContactDedupeRules($contact);
            //Dedupe elements
            $element     = new PhoneElement($contact, 'mobilePhone');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new PhoneElement($contact, 'officePhone');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new TitleFullNameElement($contact, 'null');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new EmailAddressInformationElement($contact, 'primaryEmail');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new EmailAddressInformationElement($contact, 'secondaryEmail');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $this->assertTrue(Yii::app()->clientScript->isScriptRegistered('DedupeRulesContact_mobilePhone#dedupe-for-edit-and-details-view'));
            $this->assertTrue(Yii::app()->clientScript->isScriptRegistered('DedupeRulesContact_officePhone#dedupe-for-edit-and-details-view'));
            $this->assertTrue(Yii::app()->clientScript->isScriptRegistered('DedupeRulesContact_lastName#dedupe-for-edit-and-details-view'));
            $this->assertTrue(Yii::app()->clientScript->isScriptRegistered('DedupeRulesContact_primaryEmail_emailAddress#dedupe-for-edit-and-details-view'));
            $this->assertTrue(Yii::app()->clientScript->isScriptRegistered('DedupeRulesContact_secondaryEmail_emailAddress#dedupe-for-edit-and-details-view'));
            //Non dedupe elements
            $element     = new ContactStateDropDownElement($contact, 'null');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new TextElement($contact, 'jobTitle');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new AccountElement($contact, 'account');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new TextElement($contact, 'department');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new DropDownElement($contact, 'source');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new PhoneElement($contact, 'officeFax');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new AddressElement($contact, 'primaryAddress');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new AddressElement($contact, 'secondaryAddress');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));
            $element     = new TextAreaElement($contact, 'description');
            $this->assertNull($dedupeRules->registerScriptForEditAndDetailsView($element));

            //Only the dedupe elements needs the script registered
            $this->assertCount(5, array_pop(Yii::app()->clientScript->scripts));
        }

        public function testSearchForDuplicateModels()
        {
            $contact      = new Contact();
            $dedupeRules  = new ContactDedupeRules($contact);
            $searchResult = $dedupeRules->searchForDuplicateModels('lastName', 'Sam');
            $this->assertEquals('There is 1 possible match. <span class="underline">Click here</span> to view.', $searchResult['message']);
            $this->assertCount (1, $searchResult['matchedModels']);
            $searchResult = $dedupeRules->searchForDuplicateModels('lastName', 'son');
            $this->assertEquals('There are at least 5 possible matches. <span class="underline">Click here</span> to view.', $searchResult['message']);
            $this->assertCount (6, $searchResult['matchedModels']);
            $searchResult = $dedupeRules->searchForDuplicateModels('primaryEmail', 'sam@zurmoland.com');
            $this->assertEquals('There is 1 possible match. <span class="underline">Click here</span> to view.', $searchResult['message']);
            $this->assertCount (1, $searchResult['matchedModels']);
            $searchResult = $dedupeRules->searchForDuplicateModels('primaryEmail', 'sam@zurmoworld.com');
            $this->assertEquals('There is 1 possible match. <span class="underline">Click here</span> to view.', $searchResult['message']);
            $this->assertCount (1, $searchResult['matchedModels']);
            $searchResult = $dedupeRules->searchForDuplicateModels('mobilePhone', '123-456-789');
            $this->assertEquals('There are 5 possible matches. <span class="underline">Click here</span> to view.', $searchResult['message']);
            $this->assertCount (5, $searchResult['matchedModels']);
            $searchResult = $dedupeRules->searchForDuplicateModels('mobilePhone', '987-654-321X');
            $this->assertEquals('There is 1 possible match. <span class="underline">Click here</span> to view.', $searchResult['message']);
            $this->assertCount (1, $searchResult['matchedModels']);
            try
            {
                $dedupeRules->searchForDuplicateModels('firstName', 'Sam');
                $this->fail();
            }
            catch (NotImplementedException $exception)
            {
                $this->assertEquals('There is no search callback defined for attribute: firstName', $exception->getMessage());
            }
        }
    }
?>