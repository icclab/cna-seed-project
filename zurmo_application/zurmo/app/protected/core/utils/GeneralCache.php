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
     * This is a general cache helper that utilizes both php caching and memcaching if available. Utilized for
     * caching requirements that are simple in/out of a serialized array or string of information.
     */
    abstract class GeneralCache extends ZurmoCache
    {
        protected static $cachedEntries = array();

        public static $cacheType = 'G:';

        public static function getEntry($identifier, $default = 'NOT_FOUND_EXCEPTION', $cacheDefaultValue = false)
        {
            assert('is_string($identifier)');
            if (static::supportsAndAllowsPhpCaching())
            {
                if (isset(static::$cachedEntries[$identifier]))
                {
                    return static::$cachedEntries[$identifier];
                }
            }
            if (static::supportsAndAllowsMemcache())
            {
                $prefix = static::getCachePrefix($identifier);

                @$serializedData = Yii::app()->cache->get($prefix . $identifier);
                //echo "GET:" . $prefix . $identifier . "\n";
                if ($serializedData !== false)
                {
                    $unserializedData = unserialize($serializedData);
                    if (static::supportsAndAllowsPhpCaching())
                    {
                        static::$cachedEntries[$identifier] = $unserializedData;
                    }
                    return $unserializedData;
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

        public static function cacheEntry($identifier, $entry)
        {
            assert('is_string($identifier)');
            assert('is_string($entry) || is_bool($entry) || is_array($entry) || is_numeric($entry) || is_object($entry) || $entry == null');
            if (static::supportsAndAllowsPhpCaching())
            {
                static::$cachedEntries[$identifier] = $entry;
            }
            if (static::supportsAndAllowsMemcache())
            {
                $prefix = static::getCachePrefix($identifier);
                @Yii::app()->cache->set($prefix . $identifier, serialize($entry));
            }
        }

        public static function forgetEntry($identifier)
        {
            if (static::supportsAndAllowsPhpCaching())
            {
                if (isset(static::$cachedEntries[$identifier]))
                {
                    unset(static::$cachedEntries[$identifier]);
                }
            }
            if (static::supportsAndAllowsMemcache())
            {
                $prefix = static::getCachePrefix($identifier);
                @Yii::app()->cache->delete($prefix . $identifier);
            }
        }

        public static function forgetAll()
        {
            if (static::supportsAndAllowsPhpCaching())
            {
                static::$cachedEntries = array();
            }
            static::clearMemcacheCache();
        }
    }
?>