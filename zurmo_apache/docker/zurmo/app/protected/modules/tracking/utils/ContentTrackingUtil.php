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

    class ContentTrackingUtil
    {
        protected static $baseQueryStringArray;

        public static function resolveContentsForTracking(& $textContent, & $htmlContent, $enableTracking, $modelId,
                                                            $modelType, $personId)
        {
            if (!empty($textContent))
            {
                static::resolveContentForTracking($enableTracking, $textContent, $modelId, $modelType, $personId, false);
            }
            if (!empty($htmlContent))
            {
                static::resolveContentForTracking($enableTracking, $htmlContent, $modelId, $modelType, $personId, true);
            }
        }

        public static function resolveContentForTracking($tracking, & $content, $modelId, $modelType, $personId,
                                                         $isHtmlContent)
        {
            assert('is_int($modelId)');
            if (!$tracking)
            {
                return true;
            }
            if (strpos($content, static::resolveBaseTrackingUrl()) !== false) // it already contains few tracking  urls in the content
            {
                return false;
            }
            static::$baseQueryStringArray = static::resolveBaseQueryStringArray($modelId, $modelType, $personId);
            static::resolveContentForEmailOpenTracking($content, $isHtmlContent);
            static::resolveContentForLinkClickTracking($content, $isHtmlContent);
            return true;
        }

        protected static function resolveContentForEmailOpenTracking(& $content, $isHtmlContent = false)
        {
            if (!$isHtmlContent)
            {
                return false;
            }
            $hash               = StringUtil::resolveHashForQueryStringArray(static::$baseQueryStringArray);
            $trackingUrl        = static::resolveAbsoluteTrackingUrlByHash($hash);
            $applicationName    = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'applicationName');
            if (!isset($applicationName))
            {
                $applicationName    = 'Tracker';
            }
            $imageTag           = ZurmoHtml::image($trackingUrl, $applicationName, array('width' => 1, 'height' => 1));
            $imageTag           = ZurmoHtml::tag('br') . $imageTag;
            if ($bodyTagPosition = strpos($content, '</body>'))
            {
                $content = substr_replace($content , $imageTag . '</body>' , $bodyTagPosition, strlen('</body>'));
            }
            else
            {
                $content .= $imageTag;
            }
            return true;
        }

        protected static function resolveContentForLinkClickTracking(& $content, $isHtmlContent = false)
        {
            static::resolvePlainLinksForClickTracking($content, $isHtmlContent);
            static::resolveHrefLinksForClickTracking($content, $isHtmlContent);
        }

        protected static function resolvePlainLinksForClickTracking(& $content, $isHtmlContent)
        {
            $spacePrefixedAndSuffixedLinkRegex = static::getPlainLinkRegex($isHtmlContent);
            if ($isHtmlContent)
            {
                $callBack = 'static::resolveTrackingUrlForMatchedPlainLinkArrayWithHtmlContent';
            }
            else
            {
                $callBack = 'static::resolveTrackingUrlForMatchedPlainLinkArray';
            }
            $content = preg_replace_callback($spacePrefixedAndSuffixedLinkRegex,
                $callBack,
                $content);
            if ($content === null)
            {
                throw new NotSupportedException();
            }
        }

        protected static function resolveHrefLinksForClickTracking(& $content, $isHtmlContent)
        {
            if ($isHtmlContent)
            {
                $hrefPrefixedLinkRegex  = static::getHrefLinkRegex();
                $content = preg_replace_callback($hrefPrefixedLinkRegex,
                    'static::resolveTrackingUrlForMatchedHrefLinkArray',
                    $content);
                if ($content === null)
                {
                    throw new NotSupportedException();
                }
            }
        }

        protected static function resolveTrackingUrlForMatchedPlainLinkArray($matches)
        {
            $matchPosition  = strpos($matches[0], $matches[2]);
            $prefix = substr($matches[1], 0, $matchPosition);
            return $prefix . static::resolveTrackingUrlForLink(trim($matches[2])) . ' ';
        }

        protected static function resolveTrackingUrlForMatchedPlainLinkArrayWithHtmlContent($matches)
        {
            $matchPosition  = strpos($matches[0], $matches[2]);
            $prefix = substr($matches[1], 0, $matchPosition);
            $trackingUrl = $prefix . '<a href="' . static::resolveTrackingUrlForLink(trim($matches[2])) . '">' . trim($matches[2]) . '</a> ';
            return $trackingUrl;
        }

        protected static function resolveTrackingUrlForMatchedHrefLinkArray($matches)
        {
            $quotes         = $matches[1];
            $prefixLength   = strpos($matches[0], 'href=' . $matches[1]);
            $prefix         = substr($matches[0], 0, $prefixLength + 5);
            return $prefix . $quotes . static::resolveTrackingUrlForLink($matches[2]) . $quotes;
        }

        protected static function resolveTrackingUrlForLink($link)
        {
            if (!static::isMarketingExternalUrl($link))
            {
                $queryStringArray = static::$baseQueryStringArray;
                $queryStringArray['url'] = StringUtil::addSchemeIfMissing($link);
                $hash = StringUtil::resolveHashForQueryStringArray($queryStringArray);
                $link = static::resolveAbsoluteTrackingUrlByHash($hash);
            }
            return $link;
        }

        protected static function resolveAbsoluteTrackingUrlByHash($hash)
        {
            return Yii::app()->createAbsoluteUrl(static::resolveBaseTrackingUrl(), array('id' => $hash));
        }

        protected static function resolveBaseTrackingUrl()
        {
            return '/tracking/default/track';
        }

        protected static function resolveBaseQueryStringArray($modelId, $modelType, $personId)
        {
            return compact('modelId', 'modelType', 'personId');
        }

        protected static function getBaseLinkRegex()
        {
            // Begin Not Coding Standard
            $baseLinkRegex = <<<PTN
(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))
PTN;
            // (?i)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'".,<>?«»“”‘’]))
            return $baseLinkRegex;
            // End Not Coding Standard
        }

        protected static function getPlainLinkRegex($isHtmlContent)
        {
            $baseLinkRegex  = static::getBaseLinkRegex();
            // plain links would either be on new line or have a space before them.
            // we don't care about "here is a linkhttp://www.yahoo.com" for now.
            $plainLinkRegex = '(\n|\r|\r\n|\s)' . $baseLinkRegex;
            if ($isHtmlContent)
            {
                $plainLinkRegex = substr($plainLinkRegex, 0, -1) . '(?!(?>[^<]*(?:<(?!/?a\b)[^<]*)*)</a>))';
            }
            $linkRegex = '%' . $plainLinkRegex . '%i';
            return $linkRegex;
        }

        protected static function getHrefLinkRegex()
        {
            $baseLinkRegex  = static::getBaseLinkRegex();
            $hrefPrefixedLinkRegex  = '<a [^>]*href=(\'|")' . $baseLinkRegex . '(\'|")'; // Not Coding Standard
            $linkRegex = '%' . $hrefPrefixedLinkRegex . '%i';
            return $linkRegex;
        }

        public static function resolveMarketingExternalControllerUrl()
        {
            return '/marketingLists/external';
        }

        protected static function isMarketingExternalUrl($url)
        {
            return (strpos($url, static::resolveMarketingExternalControllerUrl()) !== false);
        }
    }
?>