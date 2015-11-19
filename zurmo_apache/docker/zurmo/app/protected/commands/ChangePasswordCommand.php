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
     * ChangePassword command is used for changing user passwords in exceptional cases when frontend access is lost.
     */
    class ChangePasswordCommand extends CConsoleCommand
    {
        public function getHelp()
        {
            // Begin Not Coding Standard
            return <<<EOD
    USAGE
      zurmoc changePassword --username=user --password=password

    DESCRIPTION
      Changes password of provided username. Quote password input to allow spaces and other special characters.
      Use this command only as a fallback option such as having to reset super user's password, etc.

    PARAMETERS
     * username: A username that exists on current zurmo installation.
     * password: New password.

EOD;
    // End Not Coding Standard
    }

    /**
     * Execute the action.
     * @param array command line parameters specific for this command
     */
    public function actionIndex($username, $password)
    {
        // we want to change password and while doing so it would be better to assume
        // a root-level system user.
        Yii::app()->user->userModel = BaseControlUserConfigUtil::getUserToRunAs(true);

        if (!isset($username))
        {
            $this->usageError('A username must be specified.');
        }
        if (!isset($password))
        {
            $this->usageError('You must specify the new password.');
        }
        try
        {
            $user = User::getByUsername($username);
        }
        catch (NotFoundException $e)
        {
            $this->usageError('The specified username does not exist.');
        }
        $user->setScenario('changePassword');
        $userPasswordForm = new UserPasswordForm($user);
        $userPasswordForm->setScenario('changePassword');
        $userPasswordForm->newPassword          = $password;
        $userPasswordForm->newPassword_repeat   = $password;
        if (!$userPasswordForm->validate())
        {
            $this->addErrorsAsUsageErrors($userPasswordForm->getErrors());
        }

        if (!$user->validate())
        {
            $this->addErrorsAsUsageErrors($user->getErrors());
        }
        if (!$user->save())
        {
            throw new FailedToSaveModelException();
        }
        echo 'Updated Password' . "\n";
    }

    protected function addErrorsAsUsageErrors(array $errors)
    {
        foreach ($errors as $errorData)
        {
            foreach ($errorData as $errorOrRelatedError)
            {
                if (is_array($errorOrRelatedError))
                {
                    foreach ($errorOrRelatedError as $relatedError)
                    {
                        if (is_array($relatedError))
                        {
                            foreach ($relatedError as $relatedRelatedError)
                            {
                                if ($relatedRelatedError != '')
                                {
                                    $this->usageError($relatedRelatedError);
                                }
                            }
                        }
                        elseif ($relatedError != '')
                        {
                            $this->usageError($relatedError);
                        }
                    }
                }
                elseif ($errorOrRelatedError != '')
                {
                    $this->usageError($errorOrRelatedError);
                }
            }
        }
    }
}
?>