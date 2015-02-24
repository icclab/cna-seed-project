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
     * Helper class for working with autoresponderItem and campaignItem email Messages
     * At places we intentionally use all lowercase variable names instead of camelCase to do easy
     * compact() on them and have them match column names in db on queries.
     */
    abstract class AutoresponderAndCampaignItemsEmailMessageUtil
    {
        public static $itemClass                        = null;

        public static $returnPath                       = null;

        public static $personId                         = null;

        const CONTENT_ID                                = "@contentId";

        const SENDER_ID                                 = "@senderId";

        const RECIPIENT_ID                              = "@recipientId";

        const EMAIL_MESSAGE_ITEM_ID                     = "@emailMessageItemId";

        const EMAIL_MESSAGE_ID                          = "@emailMessageId";

        const NULL_FLAG                                 = "!~~NULL~~!"; // Not Coding Standard

        public static function resolveAndSaveEmailMessage($textContent, $htmlContent, Item $itemOwnerModel,
                                                    Contact $contact, MarketingList $marketingList, $itemId, $folderId)
        {
            $recipient              = static::resolveRecipient($contact);
            if (empty($recipient))
            {
                throw new MissingRecipientsForEmailMessageException();
            }
            $userId                 = static::resolveCurrentUserId();
            if (get_class($itemOwnerModel) == 'Campaign')
            {
                $ownerId                = $itemOwnerModel->owner->id;
            }
            else
            {
                $ownerId                = $marketingList->owner->id;
            }
            $subject                = $itemOwnerModel->subject;
            $serializedData         = serialize($subject);
            $headers                = static::resolveHeaders($itemId);
            $nowTimestamp           = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
            $emailMessageData       = compact('subject', 'serializedData', 'textContent', 'htmlContent', 'userId', 'ownerId',
                                                'headers', 'attachments', 'folderId', 'nowTimestamp');
            $attachments            = array('relatedModelType' => strtolower(get_class($itemOwnerModel)),
                                            'relatedModelId' => $itemOwnerModel->id);
            $sender                 = static::resolveSender($marketingList, $itemOwnerModel);
            $emailMessageData       = CMap::mergeArray($emailMessageData, $sender, $recipient, $attachments);
            $emailMessageId         = static::saveEmailMessageWithRelated($emailMessageData);
            if (empty($emailMessageId))
            {
                throw new FailedToSaveModelException();
            }
            $emailMessage           = EmailMessage::getById($emailMessageId);
            return $emailMessage;
        }

        protected static function resolveSender(MarketingList $marketingList, $itemOwnerModel)
        {
            if (get_class($itemOwnerModel) == 'Campaign')
            {
                $fromAddress    = $itemOwnerModel->fromAddress;
                $fromName       = $itemOwnerModel->fromName;
            }
            else
            {
                if (!empty($marketingList->fromName) && !empty($marketingList->fromAddress))
                {
                    $fromAddress = $marketingList->fromAddress;
                    $fromName = $marketingList->fromName;
                }
                else
                {
                    $userToSendMessagesFrom = BaseControlUserConfigUtil::getUserToRunAs();
                    $fromAddress = Yii::app()->emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
                    $fromName = strval($userToSendMessagesFrom);
                }
            }
            $sender                 = compact('fromName', 'fromAddress');
            return $sender;
        }

        protected static function resolveRecipient(Contact $contact)
        {
            $recipient  = array();
            if ($contact->primaryEmail->emailAddress != null)
            {
                $toAddress      = $contact->primaryEmail->emailAddress;
                $toName         = strval($contact);
                $recipientType  = EmailMessageRecipient::TYPE_TO;
                $contactItemId  = $contact->getClassId('Item');
                $recipient      = compact('toAddress', 'toName', 'recipientType', 'contactItemId');
            }
            return $recipient;
        }

        protected static function resolveHeaders($zurmoItemId)
        {
            $zurmoItemClass     = static::$itemClass;
            $zurmoPersonId      = static::$personId;
            $headers            = compact('zurmoItemId', 'zurmoItemClass', 'zurmoPersonId');
            if (static::$returnPath)
            {
                $headers['Return-Path'] = static::$returnPath;
            }
            $headers            = serialize($headers);
            return $headers;
        }

        protected static function saveEmailMessageWithRelated(array $emailMessageData)
        {
            $query          = static::resolveEmailMessageCreationFunctionQuery($emailMessageData);
            $emailMessageId = static::getCell($query);
            return $emailMessageId;
        }

        protected static function resolveEmailMessageCreationFunctionQueryWithPlaceholders()
        {
            $query      = "SELECT create_email_message(textContent,
                                                         htmlContent,
                                                         fromName,
                                                         fromAddress,
                                                         userId,
                                                         ownerId,
                                                         subject,
                                                         headers,
                                                         folderId,
                                                         serializedData,
                                                         toAddress,
                                                         toName,
                                                         recipientType,
                                                         contactItemId,
                                                         relatedModelType,
                                                         relatedModelId,
                                                         nowTimestamp)";
            return $query;
        }

        protected static function resolveEmailMessageCreationFunctionQuery(array $emailMessageData)
        {
            $query                  = static::resolveEmailMessageCreationFunctionQueryWithPlaceholders();
            static::escapeValues($emailMessageData);
            static::quoteValues($emailMessageData);
            $query                  = strtr($query, $emailMessageData);
            // need to change it to mysql null.
            // couldn't find a reasonable workaround for this.
            // if we allow escaping and quoting of null in array then at this point its 'null';
            // if we do not allow escaping and quoting then strtr returns false for that key and there is nothing for that
            // key inside the string, something like 'textContentHere', , 'fromName here' which cause mysql syntax errors.
            $query                  = str_replace("'" . static::NULL_FLAG . "'", 'null', $query);
            return $query;
        }

        protected static function getCell($query, $expectingAtLeastOne = true)
        {
            $result     = ZurmoRedBean::getCell($query);
            if (!isset($result) || ($result < 1 && $expectingAtLeastOne))
            {
                throw new NotSupportedException("Query: " . PHP_EOL . $query);
            }
            return intval($result);
        }

        protected static function escapeValues(array & $values)
        {
            // We do use array_map as that would also escape null values
            //$values = array_map(array(ZurmoRedBean::$adapter, 'escape'), $values);
            foreach ($values as $key => & $value)
            {
                if (isset($value))
                {
                    $value  = DatabaseCompatibilityUtil::escape($value);
                }
            }
        }

        protected static function quoteValues(array & $values)
        {
            array_walk($values, create_function('&$value', 'if (isset($value))
            {
                $value = "\'$value\'";
            }
            else
            {
                // a custom flag we use to mark null values.
                $value = "\'' . static::NULL_FLAG . '\'";
            }'));
        }

        protected static function resolveCurrentUserId()
        {
            return Yii::app()->user->userModel->id;
        }
    }
?>