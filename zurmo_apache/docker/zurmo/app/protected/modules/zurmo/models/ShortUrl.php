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

    class ShortUrl extends RedBeanModel
    {
        const ALLOWED_CHARS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        const HASH_LENGTH = 10;

        const LIFE_TIME_IN_DAYS = 100;

        public function onCreated()
        {
            $this->unrestrictedSet('createdDateTime',  DateTimeUtil::convertTimestampToDbFormatDateTime(time()));
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'hash',
                    'url',
                    'createdDateTime',
                ),
                'rules' => array(
                    array('hash',  'required'),
                    array('hash',  'type',      'type' => 'string'),
                    array('hash',  'length',    'min'  => static::HASH_LENGTH, 'max' => static::HASH_LENGTH),
                    array('url',   'required'),
                    array('url',   'type',      'type' => 'string'),
                    array('createdDateTime',  'required'),
                    array('createdDateTime',  'readOnly'),
                    array('createdDateTime',  'type', 'type' => 'datetime'),
                ),
                'elements' => array(
                    'createdDateTime'  => 'DateTime',
                ),
            );
            return $metadata;
        }

        /**
         * This resolves the hash by url, if url is not already saved it creates a new
         * ShortUrl model and saves the hash/url pair
         * @param string $url
         * @return string
         */
        public static function resolveHashByUrl($url)
        {
            assert('is_string($url)');
            try
            {
                $hash = static::getHashByUrl($url);
            }
            catch (NotFoundException $exception)
            {
                $hashIsNew = false;
                while (!$hashIsNew)
                {
                    try
                    {
                        $hash = static::getRandomHash();
                        static::getUrlByHash($hash);
                    }
                    catch (NotFoundException $NotFoundUrlException)
                    {
                        $hashIsNew = true;
                    }
                }
                $className      = __CLASS__;
                $shortUrl       = new $className();
                $shortUrl->url  = trim($url);
                $shortUrl->hash = $hash;
                $shortUrl->save();
            }
            return $hash;
        }

        /**
         * Given an url get the stored hash or trows exception if it does not exists
         * @param string $url
         * @return string
         * @throws NotFoundException
         */
        public static function getHashByUrl($url)
        {
            assert('is_string($url)');
            assert('$url != ""');
            $url       = trim($url);
            $className = __CLASS__;
            $bean      = ZurmoRedBean::findOne($className::getTableName(), "url = :url", array(':url' => $url));
            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            if ($bean === false)
            {
                throw new NotFoundException();
            }
            else
            {
                $shortUrl = self::makeModel($bean);
            }
            return $shortUrl->hash;
        }

        /**
         * Given an hash return the stored url or throws exception if hash does not exists
         * @param string $hash
         * @return string
         * @throws NotFoundException
         */
        public static function getUrlByHash($hash)
        {
            assert('is_string($hash)');
            assert('$hash != ""');
            $className = __CLASS__;
            $bean = ZurmoRedBean::findOne($className::getTableName(), "hash = :hash ", array(':hash' => $hash));
            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            if ($bean === false)
            {
                throw new NotFoundException();
            }
            else
            {
                $shortUrl = self::makeModel($bean);
            }
            return $shortUrl->url;
        }

        /**
         * Crete a random hash based
         * @return string
         */
        protected static function getRandomHash()
        {
            $hash           = null;
            $length         = strlen(static::ALLOWED_CHARS);
            $allowedChars   = static::ALLOWED_CHARS;
            for ($i = 0; $i < static::HASH_LENGTH; $i++)
            {
                $hash .= $allowedChars[mt_rand(0, $length - 1)];
            }
            return $hash;
        }

        /**
         * Deletes all the old ShortUrls
         */
        public static function deleteOld()
        {
            $timestamp = time() - static::LIFE_TIME_IN_DAYS * 24 * 60 * 60;
            $dateTime  = DateTimeUtil::convertTimestampToDbFormatDateTime($timestamp);
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'createdDateTime',
                    'operatorType'              => 'lessThan',
                    'value'                     => $dateTime,
                ),
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter(get_called_class());
            $where = RedBeanModelDataProvider::makeWhere(get_called_class(), $searchAttributeData, $joinTablesAdapter);
            $models = self::getSubset($joinTablesAdapter, null, null, $where, null);
            foreach ($models as $model)
            {
                $model->delete();
            }
        }
    }
?>