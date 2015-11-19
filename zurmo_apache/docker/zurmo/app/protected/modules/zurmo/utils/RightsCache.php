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
     * This is a rights caching helper. Currently it just wraps the general cache until we can split out the caching
     * in memcache by categories. This way in the future we can flush just the rights cache instead of having to flush
     * the entire cache like we are doing now.
     */
    abstract class RightsCache extends GeneralCache
    {
        public static function getEntry($identifier, $default = 'NOT_FOUND_EXCEPTION', $cacheDefaultValue = false)
        {
            try
            {
                return parent::getEntry($identifier, $default, $cacheDefaultValue);
            }
            catch (NotFoundException $e)
            {
                if (static::supportsAndAllowsDatabaseCaching())
                {
                    $row = ZurmoRedBean::getRow("select entry from actual_rights_cache " .
                                                "where identifier = '" . $identifier . "'");
                    if ($row != null && isset($row['entry']))
                    {
                        //Calling parent because we don't need to re-cache the db cache item
                        parent::cacheEntry($identifier, $row['entry']);
                        return $row['entry'];
                    }
                }
                if ($default === 'NOT_FOUND_EXCEPTION')
                {
                    throw new NotFoundException();
                }
                else
                {
                    if ($cacheDefaultValue)
                    {
                        static::cacheEntry($identifier, $default);
                    }
                    return $default;
                }
            }
        }

        public static function cacheEntry($identifier, $entry)
        {
            assert('is_string($entry) || is_numeric($entry)');
            parent::cacheEntry($identifier, $entry);
            if (static::supportsAndAllowsDatabaseCaching())
            {
                ZurmoRedBean::exec("insert into actual_rights_cache
                             (identifier, entry) values ('" . $identifier . "', '" . $entry . "') on duplicate key
                             update entry = " . $entry);
            }
        }

        // The $forgetDbLevel cache is for testing.
        public static function forgetAll($forgetDbLevelCache = true)
        {
            if (static::supportsAndAllowsDatabaseCaching() && $forgetDbLevelCache)
            {
                ZurmoDatabaseCompatibilityUtil::
                    callProcedureWithoutOuts("clear_cache_actual_rights()");
            }
            parent::forgetAll();
        }
    }
?>
