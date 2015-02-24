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

    class C extends B
    {
        private static $theDefaultE = null;

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'c',
                    'defaultedInt',

                ),
                'relations' => array(
                    'e'           => array(static::HAS_ONE,  'E'),
                    'eRequired'   => array(static::HAS_ONE,  'E', static::NOT_OWNED,
                                           static::LINK_TYPE_SPECIFIC, 'eRequired'),
                    'eUnique'     => array(static::HAS_ONE,  'E', static::NOT_OWNED,
                                           static::LINK_TYPE_SPECIFIC, 'eUnique'),
                    'eDefaulted1' => array(static::HAS_ONE,  'E', static::NOT_OWNED,
                                           static::LINK_TYPE_SPECIFIC, 'eDefaulted1'),
                    'eDefaulted2' => array(static::HAS_ONE,  'E', static::NOT_OWNED,
                                           static::LINK_TYPE_SPECIFIC, 'eDefaulted2'),
                    'eMany'       => array(static::HAS_MANY, 'E')
                ),
                'rules' => array(
                    array('eRequired',      'required'),
                    array('eUnique',        'unique'),
                    array('c',              'type', 'type' => 'string'),
                    array('c',              'length', 'max' => 255),
                    array('defaultedInt',   'type', 'type' => 'integer'),
                    array('defaultedInt',   'default', 'value' => 69),
                    array('eDefaulted1',    'default', 'value' => E::getByE('theDefaultE')),
                    array('eDefaulted2',    'default', 'value' => self::getTheDefaultE()),
                ),
            );
            return $metadata;
        }

        protected static function getTheDefaultE()
        {
            if (self::$theDefaultE === null || !self::theDefaultEIsAvailable())
            {
                // The tests needing the default E must create it.
                // In real life it would need to make sense that it
                // already exists.
                self::$theDefaultE = E::getByE('theDefaultE');
            }
            return self::$theDefaultE;
        }

        protected static function theDefaultEIsAvailable()
        {
            try
            {
                $e = E::getById(self::$theDefaultE->id);
                return true;
            }
            catch (NotFoundException $ex)
            {
                return false;
            }
        }
    }
?>