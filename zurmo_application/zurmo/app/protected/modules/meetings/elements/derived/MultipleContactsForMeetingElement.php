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

    /**
     * User interface element for managing related model relations for activities. This class supports a HAS_MANY
     * specifically for the 'contact' relation. This is utilized by the meeting model.
     *
     */
    class MultipleContactsForMeetingElement extends MultiSelectRelatedModelsAutoCompleteElement
    {
        protected $modelDerivationPathToItemFromContact = null;

        protected function getFormName()
        {
            return 'ActivityItemForm';
        }

        protected function getUnqualifiedNameForIdField()
        {
            return '[Contact][ids]';
        }

        protected function getUnqualifiedIdForIdField()
        {
            return '_Contact_ids';
        }

        protected function assertModelType()
        {
            assert('$this->model instanceof Activity');
        }

        protected function getFormattedAttributeLabel()
        {
            return Yii::app()->format->text(Zurmo::t('MeetingsModule', 'Attendees'));
        }

        public static function getDisplayName()
        {
            return Zurmo::t('MeetingsModule', 'Related ContactsModulePluralLabel and LeadsModulePluralLabel',
                LabelUtil::getTranslationParamsForAllModules());
        }

        protected function getWidgetSourceUrl()
        {
            return  Yii::app()->createUrl('contacts/variableContactState/autoCompleteAllContactsOrUsersForMultiSelectAutoComplete');
        }

        protected function getWidgetHintText()
        {
            return Zurmo::t('MeetingsModule', 'Type a person: name or email address',
                                            LabelUtil::getTranslationParamsForAllModules());
        }

        protected function getRelationName()
        {
            return 'activityItems';
        }

        protected function resolveIdAndNameByModel(RedBeanModel $model)
        {
            $existingContact = null;
            if (!isset($this->modelDerivationPathToItemFromContact))
            {
                $this->modelDerivationPathToItemFromContact = RuntimeUtil::getModelDerivationPathToItem('Contact');
            }
            try
            {
                $contact = $model->castDown(array($this->modelDerivationPathToItemFromContact));
                if (get_class($contact) == 'Contact')
                {
                    $existingContact = array('id' => Meeting::CONTACT_ATTENDEE_PREFIX . $contact->id,
                                            'name' => self::renderHtmlContentLabelFromContactAndKeyword($contact, null));
                }
            }
            catch (NotFoundException $e)
            {
                //do nothing
            }
            return $existingContact;
        }

        /**
         * Given a contact model and a keyword, render the strval of the contact and the matched email address
         * that the keyword matches. If the keyword does not match any email addresses on the contact, render the
         * primary email if it exists. Otherwise just render the strval contact.
         * @param object $contact - model
         * @param string $keyword
         */
        public static function renderHtmlContentLabelFromContactAndKeyword($contact, $keyword)
        {
            assert('$contact instanceof Contact && $contact->id > 0');
            assert('$keyword == null || is_string($keyword)');
            try
            {
                if (substr($contact->secondaryEmail->emailAddress, 0, strlen($keyword)) === $keyword)
                {
                    $emailAddressToUse = $contact->secondaryEmail->emailAddress;
                }
                else
                {
                    $emailAddressToUse = $contact->primaryEmail->emailAddress;
                }
                if ($emailAddressToUse != null)
                {
                    return strval($contact) . '&#160&#160<b>' . strval($emailAddressToUse) . '</b>';
                }
                else
                {
                    return strval($contact);
                }
            }
            catch (AccessDeniedSecurityException $exception)
            {
                return Zurmo::t('Core', 'Restricted');
            }
        }

        public static function renderHtmlContentLabelFromUserAndKeyword($user, $keyword)
        {
            assert('$user instanceof User && $user->id > 0');
            assert('$keyword == null || is_string($keyword)');
            try
            {
                if (substr($user->primaryEmail->emailAddress, 0, strlen($keyword)) === $keyword)
                {
                    $emailAddressToUse = $user->primaryEmail->emailAddress;
                    return strval($user) . '&#160&#160<b>' . strval($emailAddressToUse) . '</b>';
                }
                else
                {
                    return strval($user);
                }
            }
            catch (AccessDeniedSecurityException $exception)
            {
                return Zurmo::t('Core', 'Restricted');
            }
        }

        protected function getExistingIdsAndLabels()
        {
            $existingRecords = parent::getExistingIdsAndLabels();
            $userAttendees   = $this->model->userAttendees;
            foreach ($userAttendees as $user)
            {
                $existingRecords[] = array('id'   => Meeting::USER_ATTENDEE_PREFIX . $user->id,
                                           'name' => self::renderHtmlContentLabelFromUserAndKeyword($user, null));
            }
            return $existingRecords;
        }
    }
?>