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
     * UserIdentity represents the data needed to identity a user.
     */
    class SwitchUserIdentity extends UserIdentity
    {
        const ERROR_NO_RIGHT_SWITCH_USER    = 4;

        const PRIMARY_USER                  = 'primaryUser';

        const LAST_USER                     = 'lastUser';

        const PACKED_SESSION_KEY            = 'primaryUserPackedSession';

        const PACKED_COOKIES_KEY            = 'primaryUserPackedCookies';

        /**
         * Authenticates a user.
         * @return boolean whether authentication succeeds.
         */
        public function authenticate()
        {
            if (!static::hasPrimaryUser() && !Yii::app()->user->userModel->isSuperAdministrator())
            {
                // we have an adventurous user here. He isn't admin and he hasn't got the primaryUser state
                // set yet he wants to try.
                return $this->noRightToSwitchUser();
            }
            else
            {
                $primaryUser    = static::getPrimaryUser();
                if ($this->username != $primaryUser)
                {
                    $switchToUser   = User::getByUsername($this->username);
                    if ($switchToUser->isRootUser)
                    {
                        // an adventurous user who is trying to switch to another Root User
                        return $this->noRightToSwitchUser();
                    }
                }
                if (Yii::app()->user->userModel->isSuperAdministrator() && !isset($primaryUser))
                {
                    // we are switching from admin to someone else, store the admin
                    // we do this only for the first admin to someone else switch.
                    // if we have a a scenario like:
                    // super switches to jim
                    // jim switches to super2
                    // super2 switched to jane
                    // then we would have static::PRIMARY_USER = super as that was the actual admin.
                    $this->setPrimaryUser(Yii::app()->user->userModel->username);
                    $this->packSessionAndCookies();
                }
                elseif ($this->username == $primaryUser)
                {
                    // we don't want to remember primary user anymore as we are there.
                    $this->unsetPrimaryUser();
                    $this->unpackSessionAndCookies();
                }
                else
                {
                    // logout current destroy current user's session and cookies completely.
                    $this->clearSessionAndCookiesForNormalUserSwitch();
                }
                $this->setState('username', $this->username);
                Yii::app()->user->setState('identityUser', $this->username);
                Yii::app()->user->switched  = true;
                $this->errorCode = self::ERROR_NONE;
                return true;
            }
        }

        protected function noRightToSwitchUser()
        {
            $this->errorCode    = static::ERROR_NO_RIGHT_SWITCH_USER;
            return false;
        }

        protected static function hasPrimaryUser()
        {
            return static::doesSessionValueExist(static::PRIMARY_USER);
        }

        public static function getPrimaryUser()
        {
           return static::getEncryptedSessionValue(static::PRIMARY_USER);
        }

        protected static function setPrimaryUser($username)
        {
            static::setEncryptedSessionValue(static::PRIMARY_USER, $username);
        }

        protected static function doesSessionValueExist($name)
        {
            return Yii::app()->session->contains($name);
        }

        protected static function getEncryptedSessionValue($name)
        {
            if (static::doesSessionValueExist($name))
            {
                $encryptedValue = Yii::app()->session->get($name);
                $decryptedValue = ZurmoPasswordSecurityUtil::decrypt($encryptedValue);
                return $decryptedValue;
            }
        }

        protected static function setEncryptedSessionValue($name, $value)
        {
            // we encrypt the session value so its not so easy to read
            $value      = ZurmoPasswordSecurityUtil::encrypt($value);
            Yii::app()->session->add($name, $value);
        }

        protected static function unsetPrimaryUser()
        {
            Yii::app()->session->remove(static::PRIMARY_USER);
        }

        protected function clearSessionAndCookiesForNormalUserSwitch()
        {
            $this->clearCookiesForNormalUserSwitch();
            $this->clearSessionForNormalUserSwitch();
        }

        protected function clearCookiesForNormalUserSwitch()
        {
            Yii::app()->user->logout(false);
            Yii::app()->request->cookies->clear();
        }

        protected function clearSessionForNormalUserSwitch()
        {
            $sessionKeys    = Yii::app()->session->keys;
            foreach ($sessionKeys as $sessionKey)
            {
                if ($this->isSessionKeyNotForForSwitchingUser($sessionKey))
                {
                    Yii::app()->session->remove($sessionKey);
                }
            }
        }

        protected function packSessionAndCookies()
        {
            $this->packCookies();
            $this->packSession();
        }

        protected function unpackSessionAndCookies()
        {
            // unpack session at the end so we can unpack cookies first.
            $this->unpackCookies();
            $this->unpackSession();
        }

        protected function packSession()
        {
            $this->packIntoSession(static::PACKED_SESSION_KEY, $_SESSION);
        }

        protected function unpackSession()
        {
            $this->unpackFromSession(static::PACKED_SESSION_KEY, true);
        }

        protected function packCookies()
        {
            $this->packIntoSession(static::PACKED_COOKIES_KEY, $_COOKIE);
        }

        protected function unpackCookies()
        {
            $this->unpackFromSession(static::PACKED_COOKIES_KEY, false);
        }

        protected function packIntoSession($key, array $value)
        {
            Yii::app()->session->add($key, $this->resolveValueForPacking($value));
        }

        protected function resolveValueForPacking(array $value)
        {
            return ZurmoPasswordSecurityUtil::encrypt(serialize($value));
        }

        protected function resolveValueForUnpacking($packedValue)
        {
            return unserialize(ZurmoPasswordSecurityUtil::decrypt($packedValue));
        }

        protected function unpackFromSession($key, $unpackToSession = true)
        {
            $packedValue        = Yii::app()->session->get($key);
            if (!empty($packedValue))
            {
                $unpackedValue  = $this->resolveValueForUnpacking($packedValue);
                // we can't use variable variable for super globals, neither can we pass them by reference
                if ($unpackToSession)
                {
                    $this->unpackSessionByValue($unpackedValue);
                }
                else
                {
                    $this->unpackCookiesByValue($unpackedValue);
                }
            }
        }

        protected function unpackSessionByValue(array $unpackedValue)
        {
            Yii::app()->session->clear();
            foreach ($unpackedValue as $key => $value)
            {
                // exclude the values we used for packing user data, we can restore them but they don't
                // serve any purpose.
                if ($this->isSessionKeyNotForForSwitchingUser($key))
                {
                    // we use session instead of state because not all sessions keys were part of state array
                    // also if we use state to store the value we would also have to remove the StateKeyPrefix
                    // from the keys else we would end up with 2 state key prefixes in same keys.
                    Yii::app()->session->add($key, $value);
                }
            }
        }

        protected function unpackCookiesByValue(array $unpackedValue)
        {
            $this->clearCookiesForNormalUserSwitch();
            foreach ($unpackedValue as $name => $value)
            {
                Yii::app()->request->cookies->add($name, new CHttpCookie($name, $value));
            }
        }

        protected function isSessionKeyNotForForSwitchingUser($key)
        {
            return ($key != static::PACKED_COOKIES_KEY && $key != static::PACKED_SESSION_KEY && $key !== static::PRIMARY_USER);
        }
    }
?>