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

    class AccountListViewMergeTestHelper
    {
        public static function getIndustryValues()
        {
            $values = array(
                'Automotive',
                'Adult Entertainment',
                'Financial Services',
                'Mercenaries & Armaments',
            );
            $industryFieldData = CustomFieldData::getByName('Industries');
            $industryFieldData->serializedData = serialize($values);
            assert($industryFieldData->save()); // Not Coding Standard
            return $values;
        }

        public static function getTypeValues()
        {
            $values = array(
                'Prospect',
                'Customer',
                'Vendor',
            );
            $typeFieldData = CustomFieldData::getByName('AccountTypes');
            $typeFieldData->serializedData = serialize($values);
            assert($typeFieldData->save()); // Not Coding Standard
            return $values;
        }

        public static function getFirstModel($user)
        {
            $industryValues                         = self::getIndustryValues();
            $accountTypeValues                      = self::getTypeValues();
            $account                                = new Account();
            $account->owner                         = $user;
            $account->name                          = 'Test Account1';
            $account->officePhone                   = '1234567890';
            $account->industry->value               = $industryValues[1];
            $account->officeFax                     = '12345876';
            $account->employees                     = 50;
            $account->annualRevenue                 = 1000000;
            $account->type->value                   = $accountTypeValues[1];
            $account->website                       = 'http://yahoo.com';
            $account->billingAddress->street1       = '129 Noodle Boulevard';
            $account->billingAddress->street2       = 'Apartment 6000A';
            $account->billingAddress->city          = 'Noodleville';
            $account->billingAddress->postalCode    = '23453';
            $account->billingAddress->state         = 'Alaska';
            $account->billingAddress->country       = 'The Good Old US of A';
            $account->shippingAddress->street1      = '25 de Agosto 2543';
            $account->shippingAddress->street2      = 'Local 3';
            $account->shippingAddress->city         = 'Ciudad de Los Fideos';
            $account->shippingAddress->postalCode   = '5123-4';
            $account->shippingAddress->state        = 'Alaska';
            $account->shippingAddress->country      = 'Latinoland';

            $account->description                   = 'My First Account Description';
            assert($account->save()); // Not Coding Standard
            return $account;
        }

        public static function getSecondModel($user)
        {
            $industryValues                         = self::getIndustryValues();
            $accountTypeValues                      = self::getTypeValues();
            $account2                               = new Account();
            $account2->owner                        = $user;
            $account2->name                         = 'Test Account2';
            $account2->officePhone                  = '3454567890';
            $account2->industry->value              = $industryValues[1];
            $account2->officeFax                    = '234345876';
            $account2->employees                    =  80;
            $account2->annualRevenue                = 1000099;
            $account2->type->value                  = $accountTypeValues[2];
            $account2->website                      = 'http://google.com';
            $account2->billingAddress->street1      = '302';
            $account2->billingAddress->street2      = '9A/1';
            $account2->billingAddress->city         = 'delhi';
            $account2->billingAddress->state        = 'delhi';
            $account2->billingAddress->postalCode   = '23453';
            $account2->billingAddress->country      = 'The Good Old US of A';
            $account2->shippingAddress->street1     = 'pusa road';
            $account2->shippingAddress->street2     = 'near hotel crown';
            $account2->shippingAddress->city        = 'delhi';
            $account2->shippingAddress->state       = 'delhi';
            $account2->shippingAddress->postalCode  = '110005';
            $account2->shippingAddress->country     = 'India Test';
            $account2->description = 'My Second Account Description';
            assert($account2->save()); // Not Coding Standard
            return $account2;
        }
    }
?>