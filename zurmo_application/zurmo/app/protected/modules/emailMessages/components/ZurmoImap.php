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
     * Helper class to manage access to IMAP server
     */
    class ZurmoImap extends CApplicationComponent
    {
        /*
         * IMAP host
         */
        public $imapHost;

        /*
         * IMAP username
         */
        public $imapUsername;

        /*
         * IMAP password
         */
        public $imapPassword;

        /**
         * IMAP port
         */
        public $imapPort = 143;

        /**
         * Does IMAP server require secure connection
         */
        public $imapSSL = false;

        /**
         * IMAP folder
         */
        public $imapFolder = 'INBOX';

        /**
         * IMAP stream. It is setup after connection to IMAP server established.
         */
        protected $imapStream;

        /**
        * Contains array of settings to load during initialization from the configuration table.
        * @see loadInboundSettings
        * @var array
        */
        protected $settingsToLoad = array(
            'imapHost',
            'imapUsername',
            'imapPassword',
            'imapPort',
            'imapSSL',
            'imapFolder'
        );

        /**
        * Called once per page load, will load up outbound settings from the database if available.
        * (non-PHPdoc)
        * @see CApplicationComponent::init()
        */
        public function init()
        {
            parent::init();
            $this->loadInboundSettings();
        }

        /**
         * Load inbound settings from the database.
         */
        public function loadInboundSettings()
        {
            foreach ($this->settingsToLoad as $keyName)
            {
                if ($keyName == $this->resolvePasswordKeyName())
                {
                    $encryptedKeyValue = ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', $keyName);
                    if ($encryptedKeyValue !== '' && $encryptedKeyValue !== null)
                    {
                        $keyValue = ZurmoPasswordSecurityUtil::decrypt($encryptedKeyValue);
                    }
                    else
                    {
                        $keyValue = null;
                    }
                }
                else
                {
                    $keyValue = ZurmoConfigurationUtil::getByModuleName('EmailMessagesModule', $keyName);
                }
                if (null !== $keyValue)
                {
                    $attributeName  = $this->resolveAttributeNameFromSettingsKey($keyName);
                    $this->$attributeName = $keyValue;
                }
            }
        }

        /**
        * Set inbound settings into the database.
        */
        public function setInboundSettings()
        {
            foreach ($this->settingsToLoad as $keyName)
            {
                $attributeName  = $this->resolveAttributeNameFromSettingsKey($keyName);
                if ($keyName == $this->resolvePasswordKeyName())
                {
                    $password = ZurmoPasswordSecurityUtil::encrypt($this->$attributeName);
                    ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', $keyName, $password);
                }
                else
                {
                    ZurmoConfigurationUtil::setByModuleName('EmailMessagesModule', $keyName, $this->$attributeName );
                }
            }
        }

        /**
         * Connect to imap server
         * @throws CException
         * @return bool
         */
        public function connect()
        {
            $errorReporting = error_reporting();
            error_reporting(0);
            // Clear previous imap errors from stack
            imap_errors();

            if ($this->imapSSL)
            {
                $ssl = "/ssl";
            }
            else
            {
                $ssl = "";
            }
            // To-Do: What to do with novalidate-cert???
            $hostname = "{" . $this->imapHost . ":" . $this->imapPort . "/imap" . $ssl . "/novalidate-cert}" . $this->imapFolder;

            if (is_resource($this->imapStream))
            {
                imap_close($this->imapStream);
                $this->imapStream = null;
            }

            $resource = imap_open($hostname, $this->imapUsername, $this->imapPassword, null, 1);
            $errors = imap_errors();

            // Fix for Exchange server
            if ($errors || !is_resource($resource))
            {
                $resource = imap_open($hostname, $this->imapUsername, $this->imapPassword, null, 1,
                    array('DISABLE_AUTHENTICATOR' => 'GSSAPI'));
                $errors = imap_errors();
            }

            error_reporting($errorReporting);
            if (!$errors && is_resource($resource))
            {
                $this->imapStream = $resource;
                return true;
            }
            else
            {
                $this->imapStream = null;
                return false;
            }
        }

        /**
         * Get detailed info about imap mail box
         */
        public function getMessageBoxStatsDetailed()
        {
            return imap_mailboxmsginfo($this->imapStream);
        }

        /**
        * Get info about imap mail box
        */
        public function resolveMessageBoxStats()
        {
            if ($this->imapStream == null)
            {
                return false;
            }
            return imap_check($this->imapStream);
        }

        /**
        * Get email with attachments
        * @param int messageNumbers - message number
        * @param Object $mailHeaderInfo
        * @return array the email info
        */
        protected function getMessage($messageNumber, $mailHeaderInfo)
        {
            $imapMessage = new ImapMessage();
            $structure = imap_fetchstructure($this->imapStream, $messageNumber);
            if (isset($mailHeaderInfo->to))
            {
                foreach ($mailHeaderInfo->to as $key => $to)
                {
                    if (isset($to->personal))
                    {
                        $imapMessage->to[$key]['name'] = imap_utf8($to->personal);
                    }
                    elseif (isset($to->mailbox))
                    {
                        $imapMessage->to[$key]['name'] = $to->mailbox;
                    }
                    if (isset($to->mailbox) && isset($to->host))
                    {
                        $imapMessage->to[$key]['email'] = $to->mailbox . '@' . $to->host;
                    }
                }
            }
            if (isset($mailHeaderInfo->cc))
            {
                foreach ($mailHeaderInfo->cc as $key => $cc)
                {
                    if (isset($cc->personal))
                    {
                        $imapMessage->cc[$key]['name'] = imap_utf8($cc->personal);
                    }
                    elseif (isset($cc->mailbox) && !isset($cc->host))
                    {
                        $imapMessage->cc[$key]['name'] = $cc->mailbox;
                    }
                    if (isset($cc->mailbox) && isset($cc->host))
                    {
                        $imapMessage->cc[$key]['email'] = $cc->mailbox . '@' . $cc->host;
                    }
                }
            }

            if (isset($mailHeaderInfo->from[0]->personal))
            {
                $imapMessage->fromName = imap_utf8($mailHeaderInfo->from[0]->personal);
            }
            elseif (isset($mailHeaderInfo->from[0]->mailbox))
            {
                $imapMessage->fromName = $mailHeaderInfo->from[0]->mailbox;
            }
            if (isset($mailHeaderInfo->from[0]->mailbox) && isset($mailHeaderInfo->from[0]->host))
            {
                $imapMessage->fromEmail = $mailHeaderInfo->from[0]->mailbox . '@' . $mailHeaderInfo->from[0]->host;
            }

            if (isset($mailHeaderInfo->sender))
            {
                if (isset($mailHeaderInfo->sender[0]->personal))
                {
                    $imapMessage->senderName = imap_utf8($mailHeaderInfo->sender[0]->personal);
                }
                $imapMessage->senderEmail = $mailHeaderInfo->sender[0]->mailbox . '@' . $mailHeaderInfo->from[0]->host;
            }
            else
            {
                if (isset($imapMessage->fromName))
                {
                    $imapMessage->senderName = imap_utf8($imapMessage->fromName);
                }
                $imapMessage->senderEmail = imap_utf8($imapMessage->fromName);
            }

            if (!isset($mailHeaderInfo->subject))
            {
                $imapMessage->subject = Zurmo::t('EmailMessages', 'No Subject');
            }
            else
            {
                $imapMessage->subject = imap_utf8($mailHeaderInfo->subject);
            }

            $imapMessage->textBody      = $this->getPart($messageNumber, 'TEXT/PLAIN', $structure);
            $imapMessage->htmlBody      = $this->getPart($messageNumber, 'TEXT/HTML', $structure);
            $imapMessage->attachments   = $this->getAttachments($structure, $messageNumber);
            $imapMessage->createdDate   = $mailHeaderInfo->date;
            $imapMessage->uid           = $this->getMessageUId($mailHeaderInfo->Msgno);
            $imapMessage->msgNumber     = $mailHeaderInfo->Msgno;
            if (isset($mailHeaderInfo->message_id))
            {
                $imapMessage->msgId         = $mailHeaderInfo->message_id;
            }
            $imapMessage->headers       = $this->resolveAndParseMessageHeaders($messageNumber);
            return $imapMessage;
        }

        /**
         * Get all messages, that satisfy some criteria, for example: 'ALL', 'UNSEEN', 'SUBJECT "Hello"'
         * @param string $searchCriteria
         * @param int $messagesSinceTimestamp
         * @param int $limit
         * @return array the messages that was found
         */
        public function getMessages($searchCriteria = 'ALL', $messagesSinceTimestamp = 0, $limit = null)
        {
            $messages = array();
            if ($this->imapStream == null)
            {
                return $messages;
            }
            $this->resolveMessageBoxStats();
            $messageNumbers = imap_search($this->imapStream, $searchCriteria);
            if (is_array($messageNumbers) && count($messageNumbers) > 0)
            {
                $numberOfProcessedMessages = 0;
                foreach ($messageNumbers as $messageNumber)
                {
                    $mailHeaderInfo = imap_headerinfo($this->imapStream, $messageNumber);
                    if (strtotime($mailHeaderInfo->date) > $messagesSinceTimestamp)
                    {
                        $messages[] = $this->getMessage($messageNumber, $mailHeaderInfo);
                    }
                    $numberOfProcessedMessages++;
                    if (isset($limit) && is_int($limit) && $limit > 0 && $numberOfProcessedMessages == $limit)
                    {
                        break;
                    }
                }
            }
            return $messages;
        }

        /**
         * Expunge all messages on IMAP server
         */
        public function expungeMessages()
        {
            if ($this->imapStream == null)
            {
                return false;
            }
            imap_expunge($this->imapStream);
            return true;
        }

        /**
         * Delete all messages on IMAP server
         */
        public function deleteMessages($expunge = false)
        {
            $messages = $this->getMessages();
            if (!empty($messages))
            {
                foreach ($messages as $message)
                {
                    $this->deleteMessage($message->uid);
                }
            }
            if ($expunge)
            {
                $this->expungeMessages();
            }
            return true;
        }

        /**
         * Delete message on IMAP server
         * @param int $msgUid
         * @return mixed bool false if there is no imapStream available, otherwise result of imap_delete
         */
        public function deleteMessage($msgUid)
        {
            if ($this->imapStream == null)
            {
                return false;
            }
            imap_delete($this->imapStream, $msgUid, FT_UID);
        }

        /**
         * Get all message attachments
         * @param object $structure
         * @param int $messageId
         */
        protected function getAttachments($structure, $messageId)
        {
            $attachments = array();
            if (isset($structure->parts) && count($structure->parts))
            {
                for ($i = 0; $i < count($structure->parts); $i++)
                {
                    $attachment = array(
                          'is_attachment' => false,
                          'filename' => '',
                          'name' => '',
                          'attachment' => '');

                    if ($structure->parts[$i]->ifdparameters)
                    {
                        foreach ($structure->parts[$i]->dparameters as $object)
                        {
                            if (strtolower($object->attribute) == 'filename')
                            {
                                $attachment['is_attachment'] = true;
                                $attachment['filename'] = imap_utf8($object->value);
                            }
                        }
                    }

                    if ($structure->parts[$i]->ifparameters)
                    {
                        foreach ($structure->parts[$i]->parameters as $object)
                        {
                            if (strtolower($object->attribute) == 'name')
                            {
                                $attachment['is_attachment'] = true;
                                $attachment['name'] = imap_utf8($object->value);
                            }
                        }
                    }

                    if ($attachment['is_attachment'])
                    {
                        $attachment['attachment'] = imap_fetchbody($this->imapStream, $messageId, $i + 1);
                        if ($structure->parts[$i]->encoding == 3)
                        {
                            // 3 = BASE64
                            $attachment['attachment'] = base64_decode($attachment['attachment']);
                        }
                        elseif ($structure->parts[$i]->encoding == 4)
                        {
                            // 4 = QUOTED-PRINTABLE
                            $attachment['attachment'] = quoted_printable_decode($attachment['attachment']);
                        }
                        $attachments[] = $attachment;
                    }
                }
            }
            return $attachments;
        }

        /**
         * Get a sequenced message id
         *
         * @param string $msgNo in the format <.*@.*> from the email
         *
         * @return mixed on imap its the unique id (int) and for others its a base64_encoded string
         */
        protected function getMessageUId($msgNo)
        {
            if ($this->imapStream == null)
            {
                return false;
            }
            return imap_uid($this->imapStream, $msgNo);
        }

        /**
         * get the count of mails for the given conditions and params
         *
         * @todo conditions / order other find params
         *
         * @param array $query conditions for the query
         * @return int the number of emails found
         */
        protected function mailCount($query)
        {
            if ($this->imapStream == null)
            {
                return false;
            }
            return imap_num_msg($this->imapStream);
        }

        /**
         * @param $structure
         * @return string
         */
        protected function getMimeType($structure)
        {
            $primaryMimeType = array('TEXT', 'MULTIPART', 'MESSAGE', 'APPLICATION', 'AUDIO', 'IMAGE', 'VIDEO', 'OTHER');
            if ($structure->subtype)
            {
                return $primaryMimeType[(int) $structure->type] . '/' . $structure->subtype;
            }

            return 'TEXT/PLAIN';
        }

        /**
         * @param $msgNumber
         * @param $mimeType
         * @param null $structure
         * @param bool $partNumber
         * @return bool|string
         */
        protected function getPart($msgNumber, $mimeType, $structure = null, $partNumber = false)
        {
            $prefix = null;
            if (!$structure)
            {
                return false;
            }

            if ($mimeType == $this->getMimeType($structure))
            {
                if ($partNumber == 0 && $structure->type == 0)
                {
                    $body = imap_fetchbody($this->imapStream, $msgNumber, 1);
                    return $this->resolveContentFromStructure($body, $structure);
                }
                else
                {
                    $partNumber = ($partNumber > 0) ? $partNumber : 1;
                    $body = imap_fetchbody($this->imapStream, $msgNumber, $partNumber);
                    return $body;
                }
            }

            /* multipart */
            if ($structure->type == 1)
            {
                foreach ($structure->parts as $index => $subStructure)
                {
                    if ($partNumber)
                    {
                        $prefix = $partNumber . '.';
                    }

                    $data = $this->getPart($msgNumber, $mimeType, $subStructure, $prefix . ($index + 1));
                    if ($data)
                    {
                        return $this->resolveContentFromStructure($data, $subStructure);
                    }
                }
            }
        }

        protected function resolveContentFromStructure($content, $structure)
        {
            $dataToReturn = $content;
            if ($structure->encoding == 3)
            {
                // 3 = BASE64
                $dataToReturn = base64_decode($content);
            }
            elseif ($structure->encoding == 4)
            {
                // 4 = QUOTED-PRINTABLE
                $dataToReturn = quoted_printable_decode($content);
            }
            if ($structure->ifparameters && strtoupper($structure->parameters[0]->attribute) == 'CHARSET' &&
                strtoupper($structure->parameters[0]->value) != 'UTF-8')
            {
                return iconv($structure->parameters[0]->value, 'UTF-8', $dataToReturn);
            }
            else
            {
                return $dataToReturn;
            }
        }

        protected function resolvePasswordKeyName()
        {
            return 'imapPassword';
        }

        protected function resolveAttributeNameFromSettingsKey($key)
        {
            return $key;
        }

        protected function resolveAndParseMessageHeaders($messageNumber)
        {
            $headers = imap_fetchheader($this->imapStream, $messageNumber);
            preg_match_all('/([^: ]+): (.+?(?:\r\n\s(?:.+?))*)\r\n/m', $headers, $matches); // Not Coding Standard
            preg_replace('/\r\n\s+/m', '', $matches[2]); // Not Coding Standard
            $headersArray   = array_combine($matches[1], $matches[2]);
            return $headersArray;
        }
    }
?>