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
     * User interface element for managing related model relations for conversation participants. This class supports a HAS_MANY
     * specifically for the 'user' or 'contact' relation. This is utilized by the conversation model.
     *
     */
    class MultiplePeopleForConversationElement extends MultipleRelatedItemModelsAutoCompleteElement
    {
        protected $modelDerivationPathToItemFromContact = null;

        protected $modelDerivationPathToItemFromUser = null;

        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        protected function getFormName()
        {
            return 'ConversationParticipantsForm';
        }

        protected function assertModelType()
        {
            assert('$this->model instanceof Conversation');
        }

        public static function getDisplayName()
        {
            return Zurmo::t('ConversationsModule', 'Participants');
        }

        protected function getWidgetSourceUrl()
        {
            return  Yii::app()->createUrl('users/default/autoCompleteForMultiSelectAutoComplete');
        }

        protected function getWidgetHintText()
        {
            return Zurmo::t('ConversationsModule', 'Type a User\'s name');
        }

        protected function getRelationName()
        {
            return 'conversationParticipants';
        }

        protected function getDefaultExistingIdsAndLabel()
        {
            return array('id'                               => $this->model->owner->getClassId('Item'),
                        $this->getWidgetPropertyToSearch()  => strval($this->model->owner),
                        'readonly'                          => true);
        }

        protected function resolveIdAndNameByModel(RedBeanModel $model)
        {
            $existingPeople = null;
            if (!isset($this->modelDerivationPathToItemFromContact))
            {
                $this->modelDerivationPathToItemFromContact = RuntimeUtil::getModelDerivationPathToItem('Contact');
            }
            if (!isset($this->modelDerivationPathToItemFromUser))
            {
                $this->modelDerivationPathToItemFromUser = RuntimeUtil::getModelDerivationPathToItem('User');
            }
            try
            {
                $contact = $model->person->castDown(array($this->modelDerivationPathToItemFromContact));
                if (get_class($contact) == 'Contact')
                {
                    $existingPeople = array('id'                                => $contact->getClassId('Item'),
                                            $this->getWidgetPropertyToSearch()  => strval($contact));
                }
                else
                {
                    throw new NotFoundException();
                }
            }
            catch (NotFoundException $e)
            {
                try
                {
                    $user = $model->person->castDown(array($this->modelDerivationPathToItemFromUser));
                    //Owner is always added first.
                    if (get_class($user) == 'User' && $user->id != $this->model->owner->id)
                    {
                        $readOnly = false;
                        $existingPeople = array('id'                                => $user->getClassId('Item'),
                                                $this->getWidgetPropertyToSearch()  => strval($user),
                                                'readonly'                          => $readOnly);
                    }
                }
                catch (NotFoundException $e)
                {
                    //This means the item is not a recognized or expected supported model.
                    throw new NotSupportedException();
                }
            }
            return $existingPeople;
        }
    }
?>