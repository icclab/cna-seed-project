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
     * Helper functionality for working with Strings
     */
    class StringUtil
    {
        const VALID_HASH_PATTERN    = '~^[A-Z0-9\+=/ ]+~i'; // Not Coding Standard

        /**
         * Given a string and a length, return the chopped string if it is larger than the length.
         * @param $string
         * @param $length
         * @param string $ellipsis
         * @return string
         */
        public static function getChoppedStringContent($string, $length, $ellipsis = '...')
        {
            assert('is_string($string) || $string === null');
            assert('is_int($length)');
            if ($string != null && strlen($string) > $length)
            {
                return substr($string, 0, ($length - 3)) . $ellipsis;
            }
            else
            {
                return $string;
            }
        }

        /**
         * Given an integer, resolve the integer with an ordinal suffix and return the content as as string.
         * @param integer $number
         */
        public static function resolveOrdinalIntegerAsStringContent($integer)
        {
            assert('is_int($integer)');
            $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
            if (($integer %100) >= 11 && ($integer%100) <= 13)
            {
               return $integer. 'th';
            }
            else
            {
               return $integer. $ends[$integer % 10];
            }
        }

        public static function renderFluidContent($content)
        {
            assert('$content == null || is_string($content)');
            if ($content != null)
            {
                // Begin Not Coding Standard
                Yii::app()->clientScript->registerScript('TruncateTitleText', "
                    $(function() {
                        $('.truncated-title').ThreeDots({ max_rows:1 });
                    });");
                // End Not Coding Standard
                $innerContent = ZurmoHtml::wrapLabel(strip_tags($content), 'ellipsis-content');
                $content      = ZurmoHtml::wrapLabel($innerContent, 'truncated-title');
                return $content;
            }
        }

        public static function renderFluidTitleContent($title)
        {
            assert('$title == null || is_string($title)');
            $content = static::renderFluidContent($title);
            return ZurmoHtml::tag('h1', array(), $content);
        }

        /**
         * used for customizing label in UI
         */
        public static function resolveCustomizedLabel()
        {
            return strtolower(preg_replace('/[^\da-z]/i', '', Yii::app()->label));
        }

        public static function uncamelize($string )
        {
            $string[0] = strtolower($string[0]);
            $uncamelizeFunction = create_function('$c', 'return "_" . strtolower($c[1]);');
            return preg_replace_callback( '/([A-Z])/', $uncamelizeFunction, $string);
        }

        public static function camelize($string, $capitaliseFirstCharacter = false, $delimiter = '_')
        {
            if ($capitaliseFirstCharacter)
            {
                $string[0] = strtoupper($string[0]);
            }
            $camelizeFunction = create_function('$character', 'return strtoupper($character[1]);');
            return preg_replace_callback('/' . preg_quote($delimiter) . '([a-z])/', $camelizeFunction, $string);
        }

        /**
         * used to add a new line to content.
         * @param $content
         * @param $isHtmlContent
         */
        public static function prependNewLine(& $content, $isHtmlContent)
        {
            if ($isHtmlContent)
            {
                $content = ZurmoHtml::tag('br') . $content;
            }
            else
            {
                $content = PHP_EOL . $content;
            }
        }

        /**
         * Generate a random string
         * @param int $length
         * @param null $characterSet
         * @return string
         */
        public static function generateRandomString($length = 10, $characterSet = null)
        {
            if (empty($characterSet))
            {
                $characterSet = implode(range("A", "Z")) . implode(range("a", "z")) . implode(range("0", "9"));
            }
            $characterSetLength = strlen($characterSet);
            $randomString = '';
            for ($i = 0; $i < $length; $i++)
            {
                $randomCharacter    = $characterSet[rand(0, $characterSetLength - 1)];
                $randomString       .= $randomCharacter;
            }
            return $randomString;
        }

        /**
         * @param $haystack
         * @param $needle
         * @return bool
         */
        public static function startsWith($haystack, $needle)
        {
            return $needle === "" || strpos($haystack, $needle) === 0;
        }

        /**
         * @param $haystack
         * @param $needle
         * @return bool
         */
        public static function endsWith($haystack, $needle)
        {
            return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
        }

        /**
         * Add a default scheme to url
         * @param $url
         * @param string $scheme
         * @return string
         */
        public static function addSchemeIfMissing($url, $scheme = 'http')
        {
            if (!preg_match("~^(?:f|ht)tps?://~i", $url))
            {
                $url = $scheme ."://" . $url;
            }
            return $url;
        }

        public static function resolveHashForQueryStringArray($queryStringArray)
        {
            $queryString            = http_build_query($queryStringArray);
            $encryptedString        = ZurmoPasswordSecurityUtil::encrypt($queryString);
            if (!$encryptedString || !static::isValidHash($encryptedString))
            {
                throw new NotSupportedException();
            }
            $encryptedString        = base64_encode($encryptedString);
            return $encryptedString;
        }

        public static function isValidHash($hash)
        {
            if (empty($hash))
            {
                return false;
            }
            $matches = array();
            $matchesCount = preg_match_all(static::VALID_HASH_PATTERN, $hash, $matches);
            if (!$matchesCount || ($matches[0][0] !== $hash))
            {
                return false;
            }
            return true;
        }

        /**
         * Convert kilobytes, megabytes, and gigabytes into bytes
         * input value is in format $value{size} where size is in {'K','M','G'}
         * @param $value
         * @return int
         */
        public static function convertToBytes($value)
        {
            $strippedValue = substr($value, 0, -1);
            switch(strtoupper(substr($value, -1)))
            {
                case "K":
                    return $strippedValue * 1024;
                case "M":
                    return $strippedValue * pow(1024, 2);
                case "G":
                    return $strippedValue * pow(1024, 3);
                default:
                    return $value;
            }
        }

        /**
         * Similar to calling ucwords() except this handles multiple-byte encoding properly
         * @param $string
         * @return string
         */
        public static function makeWordsUpperCase($string)
        {
            return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
        }
    }
?>