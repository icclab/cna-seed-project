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

    class EmailNotificationUtil
    {
        /**
         * Based on the current theme, retrieve the email notification template for html content and replace the
         * content tags with the appropriate strings
         * @param string $bodyContent
         * @param User|null $user
         * @return string
         */
        public static function resolveNotificationHtmlTemplate($bodyContent, User $user = null)
        {
            assert('is_string($bodyContent)');
            if ($user === null)
            {
                $user = Yii::app()->user->userModel;
            }
            $url                                = Yii::app()->createAbsoluteUrl('users/default/configurationEdit',
                                                  array('id' => $user->id));
            $htmlTemplate                       = self::getNotificationHtmlTemplate();
            $htmlContent                        = array();
            $htmlContent['{bodyContent}']       = $bodyContent;
            $htmlContent['{preferenceContent}'] = ZurmoHtml::link(Zurmo::t('EmailMessagesModule', 'Manage your email preferences'), $url);
            $htmlContent['{sourceContent}']     = Zurmo::t('EmailMessagesModule', 'Powered By {link}',
                array('{link}' => ZurmoHtml::link(Yii::app()->label, self::resolveWebsiteUrlForNotificationMessage())));
            return strtr($htmlTemplate, $htmlContent);
        }

        protected static function getNotificationHtmlTemplate()
        {
            $theme              = Yii::app()->theme->name;
            $name               = 'NotificationEmailTemplate';
            $templateName       = "themes/$theme/templates/$name.html";
            $customTemplateName = "themes/$theme/templates/Custom$name.html";
            if (!file_exists($customTemplateName))
            {
                $customTemplateName = "themes/default/templates/Custom$name.html";
            }
            else
            {
                if (!file_exists($templateName))
                {
                    $templateName = "themes/default/templates/$name.html";
                }
            }
            if (file_exists($customTemplateName))
            {
                return file_get_contents($customTemplateName);
            }
            else
            {
                if (file_exists($templateName))
                {
                    return file_get_contents($templateName);
                }
            }
        }

        /**
         * Based on the current theme, retrieve the email notification template for text content and replace the
         * content tags with the appropriate strings
         * @param string $bodyContent
         * @param User $user
         * @return string
         */
        public static function resolveNotificationTextTemplate($bodyContent, User $user = null)
        {
            assert('is_string($bodyContent)');
            if ($user === null)
            {
                $user = Yii::app()->user->userModel;
            }
            $url                                = ShortUrlUtil::createShortUrl(
                                                        Yii::app()->createAbsoluteUrl('users/default/configurationEdit',
                                                                                      array('id' => $user->id)
                                                        )
                                                  );
            $textTemplate                       = self::getNotificationTextTemplate();
            $textContent                        = array();
            $textContent['{bodyContent}']       = $bodyContent;
            $textContent['{preferenceContent}'] = Zurmo::t('EmailMessagesModule', 'Manage your email preferences') . ': ' . $url;
            $textContent['{sourceContent}']     = Zurmo::t('EmailMessagesModule', 'Powered By Zurmo', LabelUtil::getTranslationParamsForAllModules());
            $textContent['{sourceContent}']    .= PHP_EOL . self::resolveWebsiteUrlForNotificationMessage();
            return strtr($textTemplate, $textContent);
        }

        protected static function getNotificationTextTemplate()
        {
            $theme              = Yii::app()->theme->name;
            $name               = 'NotificationEmailTemplate';
            $templateName       = "themes/$theme/templates/$name.txt";
            $customTemplateName = "themes/$theme/templates/Custom$name.txt";
            if (!file_exists($customTemplateName))
            {
                $customTemplateName = "themes/default/templates/Custom$name.txt";
            }
            else
            {
                if (!file_exists($templateName))
                {
                    $templateName = "themes/default/templates/$name.txt";
                }
            }
            if (file_exists($customTemplateName))
            {
                return file_get_contents($customTemplateName);
            }
            else
            {
                if (file_exists($templateName))
                {
                    return file_get_contents($templateName);
                }
            }
        }

        /**
         * @param User $senderPerson
         * @param array $recipients
         * @param string $subject
         * @param EmailMessageContent $content
         */
        public static function resolveAndSendEmail($senderPerson, $recipients, $subject, $content)
        {
            assert('$senderPerson instanceof User');
            assert('is_array($recipients)');
            assert('is_string($subject)');
            assert('$content instanceof EmailMessageContent');
            if (count($recipients) == 0)
            {
                return;
            }
            $userToSendMessagesFrom     = $senderPerson;
            $emailMessage               = new EmailMessage();
            $emailMessage->owner        = $senderPerson;
            $emailMessage->subject      = $subject;
            $emailMessage->content      = $content;
            $sender                     = new EmailMessageSender();
            $sender->fromAddress        = Yii::app()->emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
            $sender->fromName           = strval($userToSendMessagesFrom);
            $sender->personsOrAccounts->add($userToSendMessagesFrom);
            $emailMessage->sender       = $sender;
            foreach ($recipients as $recipientPerson)
            {
                $recipient                  = new EmailMessageRecipient();
                $recipient->toAddress       = $recipientPerson->primaryEmail->emailAddress;
                $recipient->toName          = strval($recipientPerson);
                $recipient->type            = EmailMessageRecipient::TYPE_TO;
                $recipient->personsOrAccounts->add($recipientPerson);
                $emailMessage->recipients->add($recipient);
            }
            $box                        = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $emailMessage->folder       = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
            Yii::app()->emailHelper->send($emailMessage);
        }

        /**
         * @return string
         */
        protected static function resolveWebsiteUrlForNotificationMessage()
        {
            $emailNotificationUtilWebsiteUrl = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'emailNotificationUtilWebsiteUrl');
            if ($emailNotificationUtilWebsiteUrl != '')
            {
                return $emailNotificationUtilWebsiteUrl;
            }
            else
            {
                return "http://www.zurmo.com";
            }
        }
    }
?>