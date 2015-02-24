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
     * Helper class for working with email boxes.
     */
    class EmailBoxUtil
    {
        /**
         * Given a box name and user, create an email box with the default folders.
         * @param User $user
         * @param string $name
         */
        public static function createBoxAndDefaultFoldersByUserAndName(User $user, $name)
        {
            assert('$user->id > 0');
            assert('is_string($name)');
            $box = new EmailBox();
            $box->name        = $name;
            $box->user        = $user;
            $folder           = new EmailFolder();
            $folder->name     = EmailFolder::getDefaultDraftName();
            $folder->type     = EmailFolder::TYPE_DRAFT;
            $folder->emailBox = $box;
            $box->folders->add($folder);
            $folder           = new EmailFolder();
            $folder->name     = EmailFolder::getDefaultInboxName();
            $folder->type     = EmailFolder::TYPE_INBOX;
            $folder->emailBox = $box;
            $box->folders->add($folder);
            $folder           = new EmailFolder();
            $folder->name     = EmailFolder::getDefaultSentName();
            $folder->type     = EmailFolder::TYPE_SENT;
            $folder->emailBox = $box;
            $box->folders->add($folder);
            $folder           = new EmailFolder();
            $folder->name     = EmailFolder::getDefaultOutboxName();
            $folder->type     = EmailFolder::TYPE_OUTBOX;
            $folder->emailBox = $box;
            $box->folders->add($folder);
            $folder           = new EmailFolder();
            $folder->name     = EmailFolder::getDefaultOutboxErrorName();
            $folder->type     = EmailFolder::TYPE_OUTBOX_ERROR;
            $folder->emailBox = $box;
            $box->folders->add($folder);
            $folder           = new EmailFolder();
            $folder->name     = EmailFolder::getDefaultOutboxFailureName();
            $folder->type     = EmailFolder::TYPE_OUTBOX_FAILURE;
            $folder->emailBox = $box;
            $box->folders->add($folder);
            $folder           = new EmailFolder();
            $folder->name     = EmailFolder::getDefaultArchivedName();
            $folder->type     = EmailFolder::TYPE_ARCHIVED;
            $folder->emailBox = $box;
            $box->folders->add($folder);
            $folder           = new EmailFolder();
            $folder->name     = EmailFolder::getDefaultArchivedUnmatchedName();
            $folder->type     = EmailFolder::TYPE_ARCHIVED_UNMATCHED;
            $folder->emailBox = $box;
            $box->folders->add($folder);
            $saved            = $box->save();
            if (!$saved)
            {
                throw new NotSupportedException();
            }
            return $box;
        }

        /**
         * @param User $user
         * @param bool $refreshUser
         * @return EmailBox|void
         * @throws NotImplementedException
         * @throws NotSupportedException
         */
        public static function getDefaultEmailBoxByUser(User $user, $refreshUserModel = true)
        {
            assert('$user->id > 0');
            if ($refreshUserModel)
            {
                // doing this ensures that we won't have the multiple emailBoxes exception below but
                // we still gotta let the code decide if we want to do it or not as its tad bit expensive
                static::refreshModel($user);
            }
            if ($user->emailBoxes->count() == 0)
            {
                return self::createBoxAndDefaultFoldersByUserAndName($user, EmailBox::USER_DEFAULT_NAME);
            }
            elseif ($user->emailBoxes->count() > 1)
            {
                //Until multiple boxes are supported against a user, this is not supported
                throw new NotSupportedException();
            }
            else
            {
                return $user->emailBoxes->offsetGet(0);
            }
        }

        protected static function refreshModel(RedBeanModel & $model)
        {
            assert('$model->id > 0');
            // this is just a fail safe. We don't want to lose any unsaved changes.
            // ideally $model should be saved by this point anyway, but regardless:
            if ($model->isModified() && !$model->save())
            {
                throw new FailedToSaveModelException();
            }
            $modelId    = $model->id;
            $modelClass = get_class($model);
            $model->forgetAll();
            $model      = $modelClass::getById($modelId);
        }
    }
?>