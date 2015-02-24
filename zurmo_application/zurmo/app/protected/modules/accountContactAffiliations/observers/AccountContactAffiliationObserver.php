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
     * Used to observe when a contact is added/removed from a related account. This is needed to ensure the relationship
     * is managed in parallel using the AccountContactAffiliation model.
     */
    class AccountContactAffiliationObserver extends CComponent
    {
        public function init()
        {
            Contact::model()->attachEventHandler('onAfterSave', array($this, 'processFromContactSide'));
            Contact::model()->attachEventHandler('onRedBeanOneToManyRelatedModelsChange',
                                                 array($this, 'processFromContactSide'));
        }

        /**
         * Removes attached eventHandlers. Used by tests to ensure there are not duplicate event handlers
         */
        public function destroy()
        {
            Contact::model()->detachEventHandler('onAfterSave', array($this, 'processFromContactSide'));
            Contact::model()->detachEventHandler('onRedBeanOneToManyRelatedModelsChange',
                                                 array($this, 'processFromContactSide'));
        }

        /**
         * @param CEvent $event
         */
        public function processFromContactSide(CEvent $event)
        {
            $model = $event->sender;
            if (isset($model->originalAttributeValues['account']))
            {
                if ($model->originalAttributeValues['account'][1] > 0)
                {
                    //lookup to see if there is a 'primary' affiliation for old acc/con pairing and unmark as primary
                    $accountContactAffiliations = AccountContactAffiliation::
                                                    getPrimaryByAccountIdAndContactId(
                                                    (int)$model->originalAttributeValues['account'][1], (int)$model->id);
                    //shouldn't be more than one, but if there is unset all of them
                    foreach ($accountContactAffiliations as $accountContactAffiliation)
                    {
                        $accountContactAffiliation->primary = false;
                        $accountContactAffiliation->save();
                    }
                }
                //lookup and see if there is an affiliation for the new acc/con pairing
                if ($model->account->id > 0)
                {
                    $accountContactAffiliations = AccountContactAffiliation::getByAccountAndContact($model->account, $model);
                    //Shouldn't be more than one, but if there is, just mark the first primary.
                    if (count($accountContactAffiliations) > 0)
                    {
                        //If so - mark primary.
                        $accountContactAffiliations[0]->primary = true;
                        $accountContactAffiliations[0]->save();
                    }
                    else
                    {
                        //If not, create and mark primary.
                        $accountContactAffiliation = new AccountContactAffiliation();
                        $accountContactAffiliation->primary = true;
                        $accountContactAffiliation->contact = $model;
                        $accountContactAffiliation->account = $model->account;
                        if ($accountContactAffiliation->isAttributeRequired('role') &&
                           $accountContactAffiliation->role->value == null)
                        {
                            $accountContactAffiliation->role->value = $this->resolveRoleValue($accountContactAffiliation);
                        }
                        $accountContactAffiliation->save();
                    }
                }
            }
        }

        /**
         * If role is required, and there is no default value selected, grab the first value available for the role.
         * @param AccountContactAffiliation $accountContactAffiliation
         * @return mixed
         */
        protected function resolveRoleValue(AccountContactAffiliation $accountContactAffiliation)
        {
            $dataAndLabels = CustomFieldDataUtil::getDataIndexedByDataAndTranslatedLabelsByLanguage(
                             $accountContactAffiliation->role->data, Yii::app()->language);
            return key($dataAndLabels);
        }
    }
?>