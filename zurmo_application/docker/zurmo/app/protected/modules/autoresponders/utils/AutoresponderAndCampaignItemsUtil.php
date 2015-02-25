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
     * Helper class for working with autoresponderItem and campaignItem
     * At places we intentionally use all lowercase variable names instead of camelCase to do easy
     * compact() on them and have them match column names in db on queries.
     */
    abstract class AutoresponderAndCampaignItemsUtil
    {
        public static $folder                   = null;

        public static $returnPath               = null;

        public static $ownerModelRelationName   = null;

        public static $emailMessageForeignKey   = null;

        public static $itemTableName            = null;

        public static $itemClass                = null;

        public static $personId                 = null;

        public static function processDueItem(OwnedModel $item)
        {
            assert('is_object($item)');
            $emailMessageId             = null;
            $itemId                     = $item->id;
            assert('static::$itemClass === "AutoresponderItem" || static::$itemClass === "CampaignItem"');
            $contact                    = static::resolveContact($item);
            $itemOwnerModel             = static::resolveItemOwnerModel($item);
            if ($itemOwnerModel->id < 0)
            {
                // the corresponding autoresponder/campaign has been deleted already.
                $item->delete();
                return false;
            }
            static::$personId           = $contact->getClassId('Person');

            if (static::skipMessage($contact, $itemOwnerModel))
            {
               static::createSkipActivity($itemId);
            }
            else
            {
                $marketingList              = $itemOwnerModel->marketingList;
                assert('is_object($marketingList)');
                assert('get_class($marketingList) === "MarketingList"');
                $textContent                = $itemOwnerModel->textContent;
                $htmlContent                = null;
                if (static::supportsRichText($itemOwnerModel))
                {
                    $htmlContent = $itemOwnerModel->htmlContent;
                }
                static::resolveContent($textContent, $htmlContent, $contact, $itemOwnerModel->enableTracking,
                                       (int)$itemId, static::$itemClass, (int)$marketingList->id);
                try
                {
                    $item->emailMessage   = static::resolveEmailMessage($textContent, $htmlContent, $itemOwnerModel,
                                                                        $contact, $marketingList, $itemId);
                }
                catch (MissingRecipientsForEmailMessageException $e)
                {
                   static::createSkipActivity($itemId);
                }
            }
            $marked = static::markItemAsProcessedWithSQL($itemId, $item->emailMessage->id);
            return $marked;
        }

        protected static function resolveContact(OwnedModel $item)
        {
            $contact                    = $item->contact;
            if (empty($contact) || $contact->id < 0)
            {
                throw new NotFoundException();
            }
            return $contact;
        }

        protected static function resolveItemOwnerModel(OwnedModel $item)
        {
            $itemOwnerModel             = $item->{static::$ownerModelRelationName};
            assert('is_object($itemOwnerModel)');
            assert('get_class($itemOwnerModel) === "Autoresponder" || get_class($itemOwnerModel) === "Campaign"');
            return $itemOwnerModel;
        }

        protected static function skipMessage(Contact $contact, Item $itemOwnerModel)
        {
            return ($contact->primaryEmail->optOut ||
                // TODO: @Shoaibi: Critical0: We could use SQL for getByMarketingListIdContactIdandUnsubscribed to save further performance here.
                (get_class($itemOwnerModel) === "Campaign" && MarketingListMember::getByMarketingListIdContactIdAndUnsubscribed(
                        $itemOwnerModel->marketingList->id,
                        $contact->id,
                        true) != false));
        }

        protected static function supportsRichText(Item $itemOwnerModel)
        {
            return ((static::$itemClass == 'CampaignItem' && $itemOwnerModel->supportsRichText) ||
                        (static::$itemClass == 'AutoresponderItem'));
        }

        protected static function createSkipActivity($itemId)
        {
            $activityClass  = static::$itemClass . 'Activity';
            $type           = $activityClass::TYPE_SKIP;
            $activityClass::createNewActivity($type, $itemId, static::$personId);
        }

        protected static function resolveContent(& $textContent, & $htmlContent, Contact $contact,
                                                            $enableTracking, $modelId, $modelType, $marketingListId)
        {
            assert('is_int($modelId)');
            assert('is_int($marketingListId)');
            GlobalMarketingFooterUtil::resolveContentsForGlobalFooter($textContent, $htmlContent);
            static::resolveContentsForMergeTags($textContent, $htmlContent, $contact,
                                                $marketingListId, $modelId, $modelType);
            ContentTrackingUtil::resolveContentsForTracking($textContent, $htmlContent, $enableTracking,
                                                            $modelId, $modelType, static::$personId);
        }

        public static function resolveContentsForMergeTags(& $textContent, & $htmlContent, Contact $contact,
                                                            $marketingListId, $modelId, $modelType)
        {
            static::resolveContentForMergeTags($textContent, $contact, false, $marketingListId, $modelId, $modelType);
            static::resolveContentForMergeTags($htmlContent, $contact, true, $marketingListId, $modelId, $modelType);
        }

        protected static function resolveContentForMergeTags(& $content, Contact $contact, $isHtmlContent,
                                                                $marketingListId, $modelId, $modelType)
        {
            $resolved   = static::resolveMergeTags($content, $contact, $isHtmlContent,
                                                    $marketingListId, $modelId, $modelType);
            if ($resolved === false)
            {
                throw new NotSupportedException(Zurmo::t('EmailTemplatesModule', 'Provided content contains few invalid merge tags.'));
            }
        }

        protected static function resolveLanguageForContent()
        {
            // TODO: @Shoaibi/@Jason: Low: we might add support for language
            return null;
        }

        protected static function resolveEmailTemplateType()
        {
            return EmailTemplate::TYPE_CONTACT;
        }

        protected static function resolveErrorOnFirstMissingMergeTag()
        {
            return true;
        }

        protected static function resolveMergeTagsParams($marketingListId, $modelId, $modelType, $isHtmlContent = false)
        {
            $params     = GlobalMarketingFooterUtil::resolveFooterMergeTagsArray(static::$personId, $marketingListId,
                                                                            $modelId, $modelType, true,
                                                                            false, $isHtmlContent);
            return $params;
        }

        protected static function resolveMergeTagsUtil($content)
        {
            $language       = static::resolveLanguageForContent();
            $templateType   = static::resolveEmailTemplateType();
            $util           = MergeTagsUtilFactory::make($templateType, $language, $content);
            return $util;
        }

        protected static function resolveMergeTags(& $content, Contact $contact, $isHtmlContent,
                                                   $marketingListId, $modelId, $modelType)
        {
            $invalidTags            = array();
            $language               = static::resolveLanguageForContent();
            $errorOnFirstMissing    = static::resolveErrorOnFirstMissingMergeTag();
            $params                 = static::resolveMergeTagsParams($marketingListId, $modelId, $modelType, $isHtmlContent);
            $util                   = static::resolveMergeTagsUtil($content);
            $resolvedContent        = $util->resolveMergeTags($contact, $invalidTags, $language,
                                                                $errorOnFirstMissing, $params);
            if ($resolvedContent !== false)
            {
                $content    = $resolvedContent;
                return true;
            }
            return false;
        }

        protected static function resolveEmailMessage($textContent, $htmlContent, Item $itemOwnerModel,
                                                    Contact $contact, MarketingList $marketingList, $itemId)
        {
            $emailMessage   = static::saveEmailMessage($textContent, $htmlContent, $itemOwnerModel,
                                                        $contact, $marketingList, $itemId);
            static::sendEmailMessage($emailMessage);
            static::resolveExplicitPermissionsForEmailMessage($emailMessage, $marketingList);
            return $emailMessage;
        }

        protected static function saveEmailMessage($textContent, $htmlContent, Item $itemOwnerModel,
                                                    Contact $contact, MarketingList $marketingList, $itemId)
        {
            AutoresponderAndCampaignItemsEmailMessageUtil::$itemClass   = static::$itemClass;
            AutoresponderAndCampaignItemsEmailMessageUtil::$personId    = static::$personId;
            AutoresponderAndCampaignItemsEmailMessageUtil::$returnPath  = static::$returnPath;
            $emailMessage   = AutoresponderAndCampaignItemsEmailMessageUtil::resolveAndSaveEmailMessage($textContent,
                                                                                                    $htmlContent,
                                                                                                    $itemOwnerModel,
                                                                                                    $contact,
                                                                                                    $marketingList,
                                                                                                    $itemId,
                                                                                                    static::$folder->id);
            return $emailMessage;
        }

        protected static function sendEmailMessage(EmailMessage & $emailMessage)
        {
            Yii::app()->emailHelper->send($emailMessage, true, false);
        }

        protected static function resolveExplicitPermissionsForEmailMessage(EmailMessage & $emailMessage, MarketingList $marketingList)
        {
            $explicitReadWriteModelPermissions  = ExplicitReadWriteModelPermissionsUtil::makeBySecurableItem($marketingList);
            ExplicitReadWriteModelPermissionsUtil::resolveExplicitReadWriteModelPermissions($emailMessage,
                                                                                    $explicitReadWriteModelPermissions);
        }

        protected static function markItemAsProcessedWithSQL($itemId, $emailMessageId = null)
        {
            $sql                    = "UPDATE " . DatabaseCompatibilityUtil::quoteString(static::$itemTableName);
            $sql                    .= " SET " . DatabaseCompatibilityUtil::quoteString('processed') . ' = 1';
            if ($emailMessageId)
            {
                $sql .= ", " . DatabaseCompatibilityUtil::quoteString(static::$emailMessageForeignKey);
                $sql .= " = ${emailMessageId}";
            }
            $sql                    .= " WHERE " . DatabaseCompatibilityUtil::quoteString('id') . " = ${itemId};";
            $effectedRows           = ZurmoRedBean::exec($sql);
            return ($effectedRows == 1);
        }
    }
?>