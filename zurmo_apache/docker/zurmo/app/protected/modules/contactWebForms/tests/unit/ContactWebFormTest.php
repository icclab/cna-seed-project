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

    class ContactWebFormTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateAndGetContactWebFormById()
        {
            ContactWebFormTestHelper::deleteAllContactWebForms();
            $placedAttributes                   = array('firstName', 'lastName', 'companyName', 'jobTitle');
            $this->assertTrue(ContactsModule::loadStartingData());
            $contactStates                      = ContactState::getByName('New');
            $contactWebForm                     = new ContactWebForm();
            $contactWebForm->name               = 'Test Form';
            $contactWebForm->redirectUrl        = 'http://zurmo.com';
            $contactWebForm->submitButtonLabel  = 'Save';
            $contactWebForm->defaultState       = $contactStates[0];
            $contactWebForm->serializedData     = serialize($placedAttributes);
            $contactWebForm->defaultOwner       = Yii::app()->user->userModel;
            $this->assertTrue($contactWebForm->save());
            $id                                 = $contactWebForm->id;
            unset($contactWebForm);
            $contactWebForm = ContactWebForm::getById($id);
            $this->assertEquals('Test Form'         , $contactWebForm->name);
            $this->assertEquals('http://zurmo.com'  , $contactWebForm->redirectUrl);
            $this->assertEquals('Save'              , $contactWebForm->submitButtonLabel);
            $this->assertEquals('New'               , $contactWebForm->defaultState->name);
            $this->assertEquals($placedAttributes   , unserialize($contactWebForm->serializedData));
            $this->assertNull($contactWebForm->defaultPermissionSetting);
            $this->assertNull($contactWebForm->defaultPermissionGroupSetting);
            $contactWebForm->name                     = 'New Test Form';
            $contactWebForm->redirectUrl              = 'http://zurmo.org';
            $contactWebForm->submitButtonLabel        = 'Save and Redirect';
            $contactWebForm->defaultPermissionSetting = UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_EVERYONE;
            $this->assertTrue($contactWebForm->save());
            $id                                     = $contactWebForm->id;
            unset($contactWebForm);
            $contactWebForm = ContactWebForm::getById($id);
            $this->assertEquals('New Test Form'     , $contactWebForm->name);
            $this->assertEquals('http://zurmo.org'  , $contactWebForm->redirectUrl);
            $this->assertEquals('Save and Redirect' , $contactWebForm->submitButtonLabel);
            $this->assertEquals($contactWebForm->defaultPermissionSetting,
                                UserConfigurationForm::DEFAULT_PERMISSIONS_SETTING_EVERYONE);
            $this->assertNull($contactWebForm->defaultPermissionGroupSetting);
        }

        /**
         * @depends testCreateAndGetContactWebFormById
         */
        public function testCreateWebFormEntries()
        {
            $contactWebForms            = ContactWebForm::getAll();
            $contactWebForm             = $contactWebForms[0];
            $contactFormAttributes      = unserialize($contactWebForm->serializedData);
            $contact                    = new Contact();
            $contact->owner             = $contactWebForm->defaultOwner;
            $contact->state             = $contactWebForm->defaultState;
            $contact->firstName         = 'Super';
            $contact->lastName          = 'Man';
            $contact->jobTitle          = 'Superhero';
            $contact->companyName       = 'Test Inc.';
            $contactWebFormEntryStatus  = ContactWebFormEntry::STATUS_SUCCESS;
            $contactWebFormEntryMessage = ContactWebFormEntry::STATUS_SUCCESS_MESSAGE;
            $this->assertTrue($contact->save());

            foreach ($contactFormAttributes as $attributeName)
            {
                $contactFormAttributes[$attributeName] = $contact->$attributeName;
            }
            $contactFormAttributes['owner']      = $contactWebForm->defaultOwner->id;
            $contactFormAttributes['state']      = $contactWebForm->defaultState->id;

            $contactWebFormEntry = new ContactWebFormEntry();
            $contactWebFormEntry->serializedData = serialize($contactFormAttributes);
            $contactWebFormEntry->status         = $contactWebFormEntryStatus;
            $contactWebFormEntry->message        = $contactWebFormEntryMessage;
            $contactWebFormEntry->contactWebForm = $contactWebForm;
            $contactWebFormEntry->contact        = $contact;
            $this->assertTrue($contactWebFormEntry->save());
            unset($contactWebFormEntry);
            $this->assertEquals(1, count($contactWebForm->entries));
            $contactWebFormEntry = new ContactWebFormEntry();
            $contactWebFormEntry->serializedData = serialize($contactFormAttributes);
            $contactWebFormEntry->status         = $contactWebFormEntryStatus;
            $contactWebFormEntry->message        = $contactWebFormEntryMessage;
            $contactWebFormEntry->contactWebForm = $contactWebForm;
            $contactWebFormEntry->contact        = $contact;
            $this->assertTrue($contactWebFormEntry->save());
            $contactWebForm->entries->add($contactWebFormEntry);
            $contactWebFormEntryId               = $contactWebFormEntry->id;
            unset($contactWebFormEntry);
            $this->assertEquals(2, count($contactWebForm->entries));
        }

        /**
         * @depends testCreateWebFormEntries
         */
        public function testDeleteContactWebForm()
        {
            $contactWebForms = ContactWebForm::getAll();
            $this->assertEquals(1, count($contactWebForms));
            $contactWebForms[0]->delete();
            $contactWebForms = ContactWebForm::getAll();
            $this->assertEquals(0, count($contactWebForms));
        }
    }
?>