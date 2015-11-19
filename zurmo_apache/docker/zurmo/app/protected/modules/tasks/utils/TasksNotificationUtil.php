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
     * Helper class for working with tasks notification
     */
    class TasksNotificationUtil extends NotificationsUtil
    {
        const TASK_NEW                                = 'TaskNew';

        const TASK_STATUS_BECOMES_AWAITING_ACCEPTANCE = 'TaskStatusBecomesAwaitingAcceptance';

        const TASK_STATUS_BECOMES_COMPLETED           = 'TaskStatusBecomesCompleted';

        const TASK_STATUS_BECOMES_REJECTED            = 'TaskStatusBecomesAccepted';

        const TASK_OWNER_CHANGE                       = 'TaskOwnerChange';

        const TASK_NEW_COMMENT                        = 'TaskNewComment';

        /**
         * Submit task notification message
         * @param Task $task
         * @param string $action
         * @param null|User $relatedUser, the user associated with the task notification. In case of
         * @param null|Comment $comment
         * owner change it would be previous owner, in case of comment, it would be the user
         * making the comment
         */
        public static function submitTaskNotificationMessage(Task $task, $action, User $relatedUser = null,
                                                             Comment $comment = null)
        {
            assert('is_string($action)');
            $message = static::getNotificationMessageByAction($task, $action, $relatedUser, $comment);
            $rule = new TaskNotificationRules();
            $peopleToSendNotification = static::resolvePeopleToSendNotification($task, $action, $relatedUser);
            foreach ($peopleToSendNotification as $person)
            {
                $rule->addUser($person);
            }
            $rule->setModel($task);
            $rule->setCritical(true);
            $rule->setAllowDuplicates(true);
            static::processTaskNotification($message, $rule, $action);
        }

        /**
         * Process task notification
         * @param NotificationMessage $message
         * @param TaskNotificationRules $rule
         * @param string $action
         */
        protected static function processTaskNotification(NotificationMessage $message, TaskNotificationRules $rule, $action)
        {
            assert('is_string($action)');
            $users = $rule->getUsers();
            //This scenario would be there when there is only one subscriber. In that case users would
            //be zero
            if (count($users) == 0)
            {
                return;
            }
            $notifications = static::resolveAndGetNotifications($users, $rule->getType(), $message, $rule->allowDuplicates());
            if (static::resolveShouldSendEmailIfCritical() && $rule->isCritical())
            {
                foreach ($notifications as $notification)
                {
                    static::sendTaskEmail($notification, $rule, $action);
                }
            }
        }

        /**
         * Gets notification message by action
         * @param Task $task
         * @param $action
         * @param User $relatedUser
         * @param Comment $comment
         * @return NotificationMessage
         */
        protected static function getNotificationMessageByAction(Task $task, $action, User $relatedUser = null,
                                                                 Comment $comment = null)
        {
            assert('is_string($action)');
            $message                     = new NotificationMessage();
            $messageContent              = static::getEmailMessageContent($task, $action, $relatedUser);
            $messageContentSecondPart    = static::getEmailMessageContentSecondPart($action, $comment);
            $url                         = Yii::app()->createAbsoluteUrl('tasks/default/details/',
                                           array('id' => $task->id));
            $message->textContent        = $messageContent;
            if ($messageContentSecondPart != null)
            {
                $message->textContent .= "\n" . $messageContentSecondPart;
            }
            $message->textContent       .= "\n" . ZurmoHtml::link(Zurmo::t('Core', 'Click Here'), $url);
            $message->htmlContent        = $messageContent;
            if ($messageContentSecondPart != null)
            {
                $message->htmlContent .= "<br/>" . $messageContentSecondPart;
            }
            $message->htmlContent       .= "<br/>" . ZurmoHtml::link(Zurmo::t('Core', 'Click Here'), $url);
            return $message;
        }

        /**
         * Gets notification subscribers
         * @param Task $task
         * @param $action
         * @param User $relatedUser
         * @return array
         */
        public static function resolvePeopleToSendNotification(Task $task, $action, User $relatedUser = null)
        {
            assert('is_string($action)');
            $peopleToSendNotification = array();
            if ($action == self::TASK_NEW || $action == self::TASK_OWNER_CHANGE ||
               $action == self::TASK_STATUS_BECOMES_REJECTED)
            {
                $peopleToSendNotification[] = $task->owner;
            }
            elseif ($action == self::TASK_STATUS_BECOMES_AWAITING_ACCEPTANCE)
            {
                $peopleToSendNotification[] = $task->requestedByUser;
            }
            elseif ($action == self::TASK_STATUS_BECOMES_COMPLETED ||
                   $action == self::TASK_NEW_COMMENT)
            {
                $peopleToSendNotification = TasksUtil::getTaskSubscribers($task);
                if ($action == self::TASK_NEW_COMMENT && $relatedUser != null)
                {
                    foreach ($peopleToSendNotification as $key => $person)
                    {
                        if ($person->getClassId('Item') == $relatedUser->getClassId('Item'))
                        {
                            unset($peopleToSendNotification[$key]);
                        }
                    }
                }
                if (($action == self::TASK_STATUS_BECOMES_COMPLETED) &&
                                    (Yii::app()->user->userModel->id == $task->owner->id))
                {
                    foreach ($peopleToSendNotification as $key => $person)
                    {
                        if ($person->getClassId('Item') == $task->owner->getClassId('Item'))
                        {
                            unset($peopleToSendNotification[$key]);
                        }
                    }
                }
            }
            return $peopleToSendNotification;
        }

        /**
         * Gets email subject for the notification
         * @param Task $task
         * @param $action
         * @return string
         */
        public static function getTaskEmailSubject(Task $task, $action)
        {
            assert('$task instanceof Task');
            $relatedModelStringValue = TasksUtil::resolveFirstRelatedModelStringValue($task);
            if ($relatedModelStringValue != null)
            {
                $relatedModelStringValue = '(' . $relatedModelStringValue . ')';
            }
            $params = array('{task}'         => strval($task),
                            '{relatedModel}' => $relatedModelStringValue);
            if ($action == self::TASK_NEW)
            {
                return Zurmo::t('TasksModule', 'ASSIGNMENT {relatedModel}: {task}', $params);
            }
            elseif ($action == self::TASK_STATUS_BECOMES_AWAITING_ACCEPTANCE)
            {
                return Zurmo::t('TasksModule', 'DELIVERED {relatedModel}: {task}', $params);
            }
            elseif ($action == self::TASK_STATUS_BECOMES_COMPLETED)
            {
                return Zurmo::t('TasksModule', 'ACCEPTED {relatedModel}: {task}', $params);
            }
            elseif ($action == self::TASK_STATUS_BECOMES_REJECTED)
            {
                return Zurmo::t('TasksModule', 'REJECTED {relatedModel}: {task}', $params);
            }
            elseif ($action == self::TASK_OWNER_CHANGE)
            {
                return Zurmo::t('TasksModule', 'ASSIGNMENT {relatedModel}: {task}', $params);
            }
            elseif ($action == self::TASK_NEW_COMMENT)
            {
                return Zurmo::t('TasksModule', 'NEW COMMENT {relatedModel}: {task}', $params);
            }
        }

        /**
         * Gets email message for the notification
         * @param Task $task
         * @param $action
         * @param User $relatedUser
         * @return string
         */
        public static function getEmailMessageContent(Task $task, $action, User $relatedUser = null)
        {
            assert('is_string($action)');
            if ($action == self::TASK_NEW)
            {
                return Zurmo::t('TasksModule', "The task, '{task}', is now owned by you.",
                                               array('{task}'   => strval($task)));
            }
            elseif ($action == self::TASK_STATUS_BECOMES_AWAITING_ACCEPTANCE)
            {
                return Zurmo::t('TasksModule', "The task you requested, '{task}', has been finished. You can now choose to accept or reject the task.",
                                               array('{task}' => strval($task),
                                                     '{user}' => strval($relatedUser)));
            }
            elseif ($action == self::TASK_STATUS_BECOMES_COMPLETED)
            {
                return Zurmo::t('TasksModule', "The task, '{task}', was accepted by {user}.",
                                               array('{task}'         => strval($task),
                                                     '{user}' => strval($relatedUser)));
            }
            elseif ($action == self::TASK_STATUS_BECOMES_REJECTED)
            {
                return Zurmo::t('TasksModule', "The task, '{task}', has been rejected by {user}.",
                    array('{task}'         => strval($task),
                        '{user}' => strval($relatedUser)));
            }
            elseif ($action == self::TASK_OWNER_CHANGE)
            {
                return Zurmo::t('TasksModule', "The task, '{task}', is now owned by you.",
                                               array('{task}'   => strval($task)));
            }
            elseif ($action == self::TASK_NEW_COMMENT)
            {
                return Zurmo::t('TasksModule', "{user} has commented on the task '{task}':",
                                               array('{task}'         => strval($task),
                                                     '{user}' => strval($relatedUser)));
            }
        }

        /**
         * @param $action
         * @param Comment $comment
         * @return string
         */
        public static function getEmailMessageContentSecondPart($action, Comment $comment = null)
        {
            assert('is_string($action)');
            if ($action == self::TASK_NEW_COMMENT)
            {
                return strval($comment);
            }
        }

        /**
         * Send task email
         * @param Notification $notification
         * @param TaskNotificationRules $rule
         * @param string $action
         */
        protected static function sendTaskEmail(Notification $notification, TaskNotificationRules $rule, $action)
        {
            assert('is_string($action)');
            if ($notification->owner->primaryEmail->emailAddress !== null &&
                !UserConfigurationFormAdapter::resolveAndGetValue($notification->owner, 'turnOffEmailNotifications'))
            {
                $emailMessage               = static::makeEmailMessage($notification, $rule, $action);
                $emailMessage->content      = static::makeEmailContent($notification);
                $emailMessage->sender       = static::makeSender();
                $emailMessage->recipients->add(static::makeRecipient($notification));
                $box                        = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
                $emailMessage->folder       = EmailFolder::getByBoxAndType($box, EmailFolder::TYPE_DRAFT);
                try
                {
                    Yii::app()->emailHelper->send($emailMessage);
                }
                catch (CException $e)
                {
                    //Not sure what to do yet when catching an exception here. Currently ignoring gracefully.
                }
            }
        }

        /**
         * @param Notification $notification
         * @param TaskNotificationRules $rule
         * @param string $action
         * @return EmailMessage
         */
        protected static function makeEmailMessage(Notification $notification, TaskNotificationRules $rule, $action)
        {
            assert('is_string($action)');
            $emailMessage               = new EmailMessage();
            $emailMessage->owner        = Yii::app()->user->userModel;
            $task                       = $rule->getModel();
            $emailMessage->subject      = static::getTaskEmailSubject($task, $action);
            return $emailMessage;
        }

        /**
         * @param Notification $notification
         * @return EmailMessageContent
         */
        protected static function makeEmailContent(Notification $notification)
        {
            $emailContent               = new EmailMessageContent();
            $emailContent->textContent  = EmailNotificationUtil::
                                            resolveNotificationTextTemplate(
                                            $notification->notificationMessage->textContent, $notification->owner);
            $emailContent->htmlContent  = EmailNotificationUtil::
                                            resolveNotificationHtmlTemplate(
                                            $notification->notificationMessage->htmlContent, $notification->owner);
            return $emailContent;
        }

        /**
         * @return EmailMessageSender
         */
        protected static function makeSender()
        {
            $userToSendMessagesFrom     = BaseControlUserConfigUtil::getUserToRunAs();
            $sender                     = new EmailMessageSender();
            $sender->fromAddress        = Yii::app()->emailHelper->resolveFromAddressByUser($userToSendMessagesFrom);
            $sender->fromName           = strval($userToSendMessagesFrom);
            return $sender;
        }

        /**
         * @param Notification $notification
         * @return EmailMessageRecipient
         */
        protected static function makeRecipient(Notification $notification)
        {
            $recipient                  = new EmailMessageRecipient();
            $recipient->toAddress       = $notification->owner->primaryEmail->emailAddress;
            $recipient->toName          = strval($notification->owner);
            $recipient->type            = EmailMessageRecipient::TYPE_TO;
            $recipient->personsOrAccounts->add($notification->owner);
            return $recipient;
        }

        /**
         * Resolve to save notification
         * @return bool
         */
        protected static function resolveToSaveNotification()
        {
            return false;
        }
    }
?>