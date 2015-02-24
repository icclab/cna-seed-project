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

    class AccountContactAffiliationObserverTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            ContactsModule::loadStartingData();

            $values = array(
                'AAA',
                'BBB',
                'CCC',
            );
            $typeFieldData = CustomFieldData::getByName('AccountContactAffiliationRoles');
            $typeFieldData->serializedData = serialize($values);
            if (!$typeFieldData->save())
            {
                throw new FailedToSaveModelException();
            }
        }

        public function testChangingContactOnAccountFromContactSideThatObservationTakesPlace()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            $this->assertEquals(0, count(AccountContactAffiliation::getAll()));
            $account = AccountTestHelper::createAccountByNameForOwner('firstAccount', $super);
            $this->assertEquals(0, count(AccountContactAffiliation::getAll()));
            $contact2 = ContactTestHelper::createContactByNameForOwner('secondContact', $super);
            $this->assertEquals(0, count(AccountContactAffiliation::getAll()));
            $contact  = ContactTestHelper::createContactWithAccountByNameForOwner('firstContact', $super, $account);
            $accountContactAffiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(1, count($accountContactAffiliations));
            $this->assertEquals(1, $accountContactAffiliations[0]->primary);
            $this->assertTrue($accountContactAffiliations[0]->account->isSame($account));
            $this->assertTrue($accountContactAffiliations[0]->contact->isSame($contact));

            //Now make a second account and add the first contact to it. This would switch the contact->account to account2
            $account2 = AccountTestHelper::createAccountByNameForOwner('secondAccount', $super);
            $account2->contacts->add($contact);
            $this->assertTrue($account2->contacts->contains($contact));
            //still should be the same affiliation until we save
            $accountContactAffiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(1, count($accountContactAffiliations));
            $this->assertEquals(1, $accountContactAffiliations[0]->primary);
            $this->assertEmpty($accountContactAffiliations[0]->role->value);
            $this->assertTrue($accountContactAffiliations[0]->account->isSame($account));
            $this->assertTrue($accountContactAffiliations[0]->contact->isSame($contact));

            //Now save
            $this->assertTrue ($account2->save());
            $this->assertTrue ($account2->contacts->contains($contact));
            $this->assertFalse($account->contacts->contains($contact));
            //The old affiliation should be there but not primary anymore
            $accountContactAffiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(2, count($accountContactAffiliations));
            $this->assertEquals(0, $accountContactAffiliations[0]->primary);
            $this->assertEmpty($accountContactAffiliations[0]->role->value);
            $this->assertTrue($accountContactAffiliations[0]->account->isSame($account));
            $this->assertTrue($accountContactAffiliations[0]->contact->isSame($contact));
            $this->assertEquals(1, $accountContactAffiliations[1]->primary);
            $this->assertEmpty($accountContactAffiliations[1]->role->value);
            $this->assertTrue($accountContactAffiliations[1]->account->isSame($account2));
            $this->assertTrue($accountContactAffiliations[1]->contact->isSame($contact));

            //Now test removing the contact from the second account
            $account2->contacts->remove($contact);
            $this->assertTrue($account2->save());
            $this->assertTrue($contact->account->id < 0);
            //Both affiliations exist, but there is no longer a primary affiliation
            $accountContactAffiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(2, count($accountContactAffiliations));
            $this->assertEquals(0, $accountContactAffiliations[0]->primary);
            $this->assertTrue($accountContactAffiliations[0]->account->isSame($account));
            $this->assertTrue($accountContactAffiliations[0]->contact->isSame($contact));
            $this->assertEquals(0, $accountContactAffiliations[1]->primary);
            $this->assertTrue($accountContactAffiliations[1]->account->isSame($account2));
            $this->assertTrue($accountContactAffiliations[1]->contact->isSame($contact));

            //Contact is no longer connected to either account at this point.
            $this->assertFalse($account->contacts->contains($contact));
            $this->assertFalse($account2->contacts->contains($contact));

            //Now set the account2 as the primary again
            $account2->contacts->add($contact);
            $this->assertTrue ($account2->save());
            //Now the account2 is the primary again
            $accountContactAffiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(2, count($accountContactAffiliations));
            $this->assertEquals(0, $accountContactAffiliations[0]->primary);
            $this->assertTrue($accountContactAffiliations[0]->account->isSame($account));
            $this->assertTrue($accountContactAffiliations[0]->contact->isSame($contact));
            $this->assertEquals(1, $accountContactAffiliations[1]->primary);
            $this->assertTrue($accountContactAffiliations[1]->account->isSame($account2));
            $this->assertTrue($accountContactAffiliations[1]->contact->isSame($contact));

            //Now set the account as the primary but from the contact side
            $contact->account = $account;
            $this->assertTrue ($contact->save());
            //Now account is primary again
            $accountContactAffiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(2, count($accountContactAffiliations));
            $this->assertEquals(1, $accountContactAffiliations[0]->primary);
            $this->assertTrue($accountContactAffiliations[0]->account->isSame($account));
            $this->assertTrue($accountContactAffiliations[0]->contact->isSame($contact));
            $this->assertEquals(0, $accountContactAffiliations[1]->primary);
            $this->assertTrue($accountContactAffiliations[1]->account->isSame($account2));
            $this->assertTrue($accountContactAffiliations[1]->contact->isSame($contact));

            //Delete account, it should remove one of the affiliations
            //Refresh account to properly grab related affiliations in order to delete 'owned' relations
            $accountId = $account->id;
            $account->forget();
            $account = Account::getById($accountId);
            $this->assertEquals(1, $account->contactAffiliations->count());
            $this->assertTrue($account->delete());
            $accountContactAffiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(1, count($accountContactAffiliations));

            //Now delete from the contact side
            $contactId = $contact->id;
            $contact->forget();
            $contact = Contact::getById($contactId);
            $this->assertEquals(1, $contact->accountAffiliations->count());
            $this->assertTrue($contact->delete());
            $accountContactAffiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(0, count($accountContactAffiliations));
        }

        public function testWhenRoleIsRequired()
        {
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Set role as required but without a default value.
            $attributeForm = AttributesFormFactory::
                             createAttributeFormByAttributeName(new AccountContactAffiliation(), 'role');
            $attributeForm->isRequired = true;
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new AccountContactAffiliation());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }
            //Now create an affiliation, the role should be the first value.
            $this->assertEquals(0, count(AccountContactAffiliation::getAll()));
            $account  = AccountTestHelper::createAccountByNameForOwner('thirdAccount', $super);
            $contact  = ContactTestHelper::createContactWithAccountByNameForOwner('thirdContact', $super, $account);
            $accountContactAffiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(1, count($accountContactAffiliations));
            $this->assertEquals(1, $accountContactAffiliations[0]->primary);
            $this->assertEquals('AAA', $accountContactAffiliations[0]->role->value);
            $this->assertTrue($accountContactAffiliations[0]->account->isSame($account));
            $this->assertTrue($accountContactAffiliations[0]->contact->isSame($contact));
            $this->assertTrue($contact->delete());
            $accountContactAffiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(0, count($accountContactAffiliations));

            //Now add a default value, so the role should be the default value.
            $attributeForm = AttributesFormFactory::
                             createAttributeFormByAttributeName(new AccountContactAffiliation(), 'role');
            $attributeForm->defaultValueOrder = 1;
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new AccountContactAffiliation());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                $this->fail();
            }
            //Now create an account/contact and an affiliation. The role should be BBB
            $this->assertEquals(0, count(AccountContactAffiliation::getAll()));
            $account  = AccountTestHelper::createAccountByNameForOwner('fourthAccount', $super);
            $contact  = ContactTestHelper::createContactWithAccountByNameForOwner('fourthContact', $super, $account);
            $accountContactAffiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(1, count($accountContactAffiliations));
            $this->assertEquals(1, $accountContactAffiliations[0]->primary);
            $this->assertEquals('BBB', $accountContactAffiliations[0]->role->value);
            $this->assertTrue($accountContactAffiliations[0]->account->isSame($account));
            $this->assertTrue($accountContactAffiliations[0]->contact->isSame($contact));
            $this->assertTrue($contact->delete());
            $accountContactAffiliations = AccountContactAffiliation::getAll();
            $this->assertEquals(0, count($accountContactAffiliations));
        }
    }
?>
