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

    class GlobalMarketingFooterUtil
    {
        const CONFIG_KEY_PLAIN                      = 'AutoresponderOrCampaignFooterPlainText';

        const CONFIG_KEY_RICH_TEXT                  = 'AutoresponderOrCampaignFooterRichText';

        const CONFIG_MODULE_NAME                    = 'AutorespondersModule';

        /**
         * @param $isHtmlContent
         * @param bool $returnDefault
         * @return configuration|string
         */
        public static function getContentByType($isHtmlContent, $returnDefault = true)
        {
            assert('is_bool($isHtmlContent)');
            $key        = static::resolveConfigKeyByContentType((bool) $isHtmlContent);
            $content    = ZurmoConfigurationUtil::getByModuleName(static::CONFIG_MODULE_NAME, $key);
            if (empty($content) && $returnDefault)
            {
                $content = static::resolveDefaultValue($isHtmlContent);
            }
            return $content;
        }

        /**
         * @param string $content
         * @param bool $isHtmlContent
         */
        public static function setContentByType($content, $isHtmlContent)
        {
            assert('is_string($content)');
            assert('is_bool($isHtmlContent)');
            $key        = static::resolveConfigKeyByContentType((bool) $isHtmlContent);
            ZurmoConfigurationUtil::setByModuleName(static::CONFIG_MODULE_NAME, $key, $content);
        }

        public static function resolveUnsubscribeUrlMergeTag()
        {
            return MergeTagsUtil::resolveAttributeStringToMergeTagString('unsubscribeUrl');
        }

        public static function resolveManageSubscriptionsMergeTag()
        {
            return MergeTagsUtil::resolveAttributeStringToMergeTagString('manageSubscriptionsUrl');
        }

        public static function resolveGlobalMarketingFooterMergeTag($suffix = null) // Html or PlainText
        {
            return MergeTagsUtil::resolveAttributeStringToMergeTagString('globalMarketingFooter' . $suffix);
        }

        public static function resolveHash($personId, $marketingListId, $modelId, $modelType,
                                           $createNewActivity = true)
        {
            $queryStringArray       = static::resolveHashArray($personId, $marketingListId, $modelId, $modelType,
                                                                $createNewActivity);
            return static::resolveHashByArray($queryStringArray);
        }

        public static function resolveFooterMergeTagsArray($personId, $marketingListId, $modelId, $modelType,
                                                $createNewActivity = true, $preview = false)
        {
            $hashArray              = static::resolveHashArray($personId, $marketingListId, $modelId, $modelType, $createNewActivity);
            $queryStringArray       = CMap::mergeArray($hashArray, compact('preview'));
            return $queryStringArray;
        }

        public static function resolveHashByArray(array $queryStringArray)
        {
            if (!static::isValidQueryStringArray($queryStringArray))
            {
                throw new NotSupportedException();
            }
            unset($queryStringArray['preview']);
            unset($queryStringArray['isHtmlContent']);
            ArrayUtil::setToDefaultValueIfMissing($queryStringArray, 'createNewActivity', false);
            return StringUtil::resolveHashForQueryStringArray($queryStringArray);
        }

        public static function resolveUnsubscribeUrlByArray(array $queryStringArray)
        {
            $hash = $preview = null;
            extract(static::resolvePreviewAndHashFromArray($queryStringArray));
            return static::resolveUnsubscribeUrl($hash, $preview);
        }

        public static function resolveManageSubscriptionsUrlByArray(array $queryStringArray, $preview = false)
        {
            $hash = $preview = null;
            extract(static::resolvePreviewAndHashFromArray($queryStringArray));
            return static::resolveManageSubscriptionsUrl($hash, $preview);
        }

        public static function resolveContentsForGlobalFooter(& $textContent, & $htmlContent)
        {
            if (!empty($textContent))
            {
                static::resolveContentGlobalFooter($textContent, false);
            }
            if (!empty($htmlContent))
            {
                static::resolveContentGlobalFooter($htmlContent, true);
            }
        }

        public static function resolveContentGlobalFooter(& $content, $isHtmlContent)
        {
            static::resolveContentForUnsubscribeAndManageSubscriptionsUrls($content, $isHtmlContent);
            return true;
        }

        public static function resolveHashArray($personId, $marketingListId, $modelId, $modelType,
                                                           $createNewActivity = true)
        {
            $queryStringArray       = compact('personId', 'marketingListId', 'modelId', 'modelType', 'createNewActivity');
            return $queryStringArray;
        }

        public static function removeFooterMergeTags(& $content)
        {
            $mergeTags = array(
                static::resolveUnsubscribeUrlMergeTag(),
                static::resolveManageSubscriptionsMergeTag(),
                static::resolveGlobalMarketingFooterMergeTag('PlainText'),
                static::resolveGlobalMarketingFooterMergeTag('Html'),
            );
            $content    = str_replace($mergeTags, null, $content);
        }

        protected static function resolveConfigKeyByContentType($isHtmlContent)
        {
            assert('is_bool($isHtmlContent)');
            if ($isHtmlContent)
            {
                return static::CONFIG_KEY_RICH_TEXT;
            }
            else
            {
                return static::CONFIG_KEY_PLAIN;
            }
        }

        protected static function resolveDefaultValue($isHtmlContent)
        {
            $unsubscribeUrlPlaceHolder          = static::resolveDefaultUnsubscribeUrlMergeTagContent($isHtmlContent);
            $manageSubscriptionsUrlPlaceHolder  = static::resolveDefaultManageSubscriptionsUrlMergeTagContent($isHtmlContent);
            $recipientMention                   = 'This email was sent to [[PRIMARY^EMAIL]].';
            StringUtil::prependNewLine($unsubscribeUrlPlaceHolder, $isHtmlContent);
            StringUtil::prependNewLine($manageSubscriptionsUrlPlaceHolder, $isHtmlContent);
            StringUtil::prependNewLine($recipientMention, $isHtmlContent);
            $content        = $unsubscribeUrlPlaceHolder;
            $content        .= $manageSubscriptionsUrlPlaceHolder;
            $content        .= $recipientMention;
            return $content;
        }

        protected static function resolveDefaultUnsubscribeUrlMergeTagContent($isHtmlContent)
        {
            $tag                = static::resolveUnsubscribeUrlMergeTag();
            $descriptiveText    = 'Unsubscribe';
            return static::resolveDefaultMergeTagContentWithDescriptiveText($tag, $descriptiveText, $isHtmlContent);
        }

        protected static function resolveDefaultManageSubscriptionsUrlMergeTagContent($isHtmlContent)
        {
            $tag                = static::resolveManageSubscriptionsMergeTag();
            $descriptiveText    = 'Manage Subscriptions';
            return static::resolveDefaultMergeTagContentWithDescriptiveText($tag, $descriptiveText, $isHtmlContent);
        }

        protected static function resolveDefaultMergeTagContentWithDescriptiveText($tag, $descriptiveText, $isHtmlContent)
        {
            $content            = null;
            if ($isHtmlContent)
            {
                $content        = ZurmoHtml::link($descriptiveText, $tag);
            }
            else
            {
                $content        = $descriptiveText . ': ' . $tag;
            }
            return $content;
        }

        protected static function isValidQueryStringArray(array $queryStringArray)
        {
            return ArrayUtil::arrayHasKeys($queryStringArray, static::resolveValidQueryStringArrayKeys());
        }

        protected static function resolveValidQueryStringArrayKeys()
        {
            return array('personId', 'marketingListId', 'modelId', 'modelType'); // createNewActivity and preview indices are optional
        }

        protected static function resolvePreviewAndHashFromArray(array $queryStringArray)
        {
            $preview    = static::resolvePreviewFromArray($queryStringArray);
            $hash       = static::resolveHashByArray($queryStringArray);
            return compact('hash', 'preview');
        }

        protected static function resolvePreviewFromArray(array & $queryStringArray)
        {
            $preview    = ArrayUtil::getArrayValue($queryStringArray, 'preview', false);
            return $preview;
        }

        protected static function resolveUnsubscribeUrl($hash, $preview = false)
        {
            $baseUrl = static::resolveUnsubscribeBaseUrl();
            return static::resolveAbsoluteUrlWithHashAndPreviewForFooter($baseUrl, $hash, $preview);
        }

        protected static function resolveManageSubscriptionsUrl($hash, $preview = false)
        {
            $baseUrl = static::resolveManageSubscriptionsBaseUrl();
            return static::resolveAbsoluteUrlWithHashAndPreviewForFooter($baseUrl, $hash, $preview);
        }

        protected static function resolveAbsoluteUrlWithHashAndPreviewForFooter($baseUrl, $hash, $preview = false)
        {
            $routeParams   = static::resolveFooterUrlParams($hash, $preview);
            return Yii::app()->createAbsoluteUrl($baseUrl, $routeParams);
        }

        protected static function resolveUnsubscribeBaseUrl()
        {
            return ContentTrackingUtil::resolveMarketingExternalControllerUrl(). '/unsubscribe';
        }

        protected static function resolveManageSubscriptionsBaseUrl()
        {
            return ContentTrackingUtil::resolveMarketingExternalControllerUrl() . '/manageSubscriptions';
        }

        protected static function resolveFooterUrlParams($hash, $preview = false)
        {
            $routeParams    = array('hash'  => $hash);
            if ($preview)
            {
                $routeParams['preview'] = intval($preview);
            }
            return $routeParams;
        }

        protected static function resolveContentForUnsubscribeAndManageSubscriptionsUrls(& $content, $isHtmlContent)
        {
            $found = static::isFooterAlreadyPresent($content);
            if (!$found)
            {
                static::appendDefaultFooter($content, $isHtmlContent);
            }
        }

        protected static function isFooterAlreadyPresent($content)
        {
            $footerContent  = array(
                static::resolveUnsubscribeUrlMergeTag(),
                static::resolveManageSubscriptionsMergeTag(),
                static::resolveGlobalMarketingFooterMergeTag(), // intentionally not sending suffix
            );
            $found = false;
            foreach ($footerContent as $footer)
            {
                if (strpos($content, $footer) !== false)
                {
                    // we are good as long as even one of the merge tags is found.
                    $found = true;
                }
            }
            return $found;
        }

        protected static function appendDefaultFooter(& $content, $isHtmlContent)
        {
            $placeholderContent = static::resolveDefaultFooterPlaceholderContentByType($isHtmlContent);
            StringUtil::prependNewLine($placeholderContent, $isHtmlContent);
            $content            .= $placeholderContent;
        }

        protected static function resolveDefaultFooterPlaceholderContentByType($isHtmlContent)
        {
            return static::getContentByType($isHtmlContent, true);
        }
    }
?>