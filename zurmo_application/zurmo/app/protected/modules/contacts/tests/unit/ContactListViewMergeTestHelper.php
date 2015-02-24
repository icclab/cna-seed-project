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

    class ContactListViewMergeTestHelper
    {
        public static function getFirstModel($user)
        {
            $industryValues         = AccountListViewMergeTestHelper::getIndustryValues();
            $account                = new Account();
            $account->name          = 'Some Account';
            $account->owner         = $user;
            assert($account->save()); // Not Coding Standard
            $contactStates          = ContactState::getByName('Qualified');
            $contact                = new Contact();
            $dateTime               = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $contact->setLatestActivityDateTime($dateTime);
            $contact->owner         = $user;
            $contact->title->value  = 'Mr.';
            $contact->firstName     = 'Super';
            $contact->lastName      = 'Man';
            $contact->jobTitle      = 'Superhero';
            $contact->source->value = 'Outbound';
            $contact->account       = $account;
            $contact->companyName   = 'Test Company';
            $contact->description   = 'Some Description';
            $contact->department    = 'Red Tape';
            $contact->officePhone   = '1234567890';
            $contact->mobilePhone   = '0987654321';
            $contact->officeFax     = '1222222222';
            $contact->state         = $contactStates[0];
            $contact->website       = 'http://yahoo.com';
            $contact->industry->value = $industryValues[0];
            $contact->primaryEmail->emailAddress   = 'thejman@zurmoinc.com';
            $contact->primaryEmail->optOut         = 0;
            $contact->primaryEmail->isInvalid      = 0;
            $contact->secondaryEmail->emailAddress = 'digi@magic.net';
            $contact->secondaryEmail->optOut       = 1;
            $contact->secondaryEmail->isInvalid    = 1;
            $contact->primaryAddress->street1      = '129 Noodle Boulevard';
            $contact->primaryAddress->street2      = 'Apartment 6000A';
            $contact->primaryAddress->city         = 'Noodleville';
            $contact->primaryAddress->state        = 'New Delhi';
            $contact->primaryAddress->postalCode   = '23453';
            $contact->primaryAddress->country      = 'The Good Old US of A';
            $contact->secondaryAddress->street1    = '25 de Agosto 2543';
            $contact->secondaryAddress->street2    = 'Local 3';
            $contact->secondaryAddress->city       = 'Ciudad de Los Fideos';
            $contact->secondaryAddress->state      = 'Haryana';
            $contact->secondaryAddress->postalCode = '5123-4';
            $contact->secondaryAddress->country    = 'Latinoland';
            assert($contact->save()); // Not Coding Standard
            return $contact;
        }

        public static function getSecondModel($user)
        {
            $industryValues         = AccountListViewMergeTestHelper::getIndustryValues();
            $account                = new Account();
            $account->name          = 'New Account';
            $account->owner         = $user;
            assert($account->save()); // Not Coding Standard
            $contactCustomerStates   = ContactState::getByName('Customer');
            $contact2                = ContactTestHelper::createContactByNameForOwner('shozin', Yii::app()->user->userModel);
            $contact2->title->value  = 'Mrs.';
            $contact2->state         = $contactCustomerStates[0];
            $contact2->jobTitle       = 'Myhero';
            $contact2->source->value  = 'Trade Show';
            $contact2->companyName    = 'Test Company1';
            $contact2->account        = $account;
            $contact2->description    = 'Hey Description';
            $contact2->industry->value= $industryValues[1];
            $contact2->department     = 'Black Tape';
            $contact2->officePhone    = '1234567899';
            $contact2->mobilePhone    = '0987654123';
            $contact2->officeFax      = '1222222444';
            $contact2->website        = 'http://yahoo1.com';
            $contact2->primaryEmail->emailAddress   = 'test@yahoo.com';
            $contact2->primaryEmail->optOut         = 0;
            $contact2->primaryEmail->isInvalid      = 0;
            $contact2->secondaryEmail->emailAddress = 'test@gmail.com';
            $contact2->secondaryEmail->optOut       = 1;
            $contact2->secondaryEmail->isInvalid    = 1;
            $contact2->primaryAddress->street1      = '302';
            $contact2->primaryAddress->street2      = '9A/1';
            $contact2->primaryAddress->city         = 'New Delhi';
            $contact2->primaryAddress->state        = 'New Delhi';
            $contact2->primaryAddress->postalCode   = '110005';
            $contact2->primaryAddress->country      = 'India';
            $contact2->secondaryAddress->street1    = 'A-8';
            $contact2->secondaryAddress->street2    = 'Sector 56';
            $contact2->secondaryAddress->city       = 'Gurgaon';
            $contact2->secondaryAddress->state      = 'Haryana';
            $contact2->secondaryAddress->postalCode = '5123-4';
            $contact2->secondaryAddress->country    = 'IndiaTest';
            assert($contact2->save()); // Not Coding Standard
            return $contact2;
        }
    }
?>