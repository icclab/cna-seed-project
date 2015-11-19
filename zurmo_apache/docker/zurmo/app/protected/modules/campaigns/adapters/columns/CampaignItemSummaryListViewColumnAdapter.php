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

    class CampaignItemSummaryListViewColumnAdapter extends TextListViewColumnAdapter
    {
        public function renderGridViewData()
        {
            $className  = get_class($this);
            $value      = $className . '::resolveContactAndMetricsSummary($data)';
            return array(
                'value' => $value,
                'type'  => 'raw',
            );
        }

        /**
         * @param CampaignItem $campaignItem
         * @return string
         */
        public static function resolveContactAndMetricsSummary(CampaignItem $campaignItem)
        {
            if (ActionSecurityUtil::canCurrentUserPerformAction('Details', $campaignItem->contact))
            {
                $content  = static::resolveContactWithLink($campaignItem->contact);
                $content .= static::renderMetricsContent($campaignItem);
                return $content;
            }
            else
            {
                return static::renderRestrictedContactAccessLink($campaignItem->contact);
            }
        }

        /**
         * @param CampaignItem $campaignItem
         * @return string
         */
        public static function resolveDrillDownMetricsSummaryContent(CampaignItem $campaignItem)
        {
            $isQueued              = $campaignItem->isQueued();
            $isSkipped             = $campaignItem->isSkipped();
            if ($isQueued)
            {
                $content = static::getQueuedContentForDrillDown($campaignItem);
            }
            elseif ($isSkipped)
            {
                $content = static::getSkippedContentForDrillDown($campaignItem);
            }
            elseif ($campaignItem->hasFailedToSend())
            {
                $content = static::getSendFailedContentForDrillDown($campaignItem);
            }
            elseif ($campaignItem->isSent())
            {
                $content = static::getSentContentForDrillDown($campaignItem->emailMessage);
                $tableRows = null;
                if ($campaignItem->hasAtLeastOneOpenActivity())
                {
                    $tableRows .= static::getOpenedContentForDrillDown($campaignItem);
                }
                if ($campaignItem->hasAtLeastOneClickActivity())
                {
                    $tableRows .= static::getClickedContentForDrillDown($campaignItem);
                }
                $content .= static::getWrapperTable($tableRows);
                if ($campaignItem->hasAtLeastOneUnsubscribeActivity())
                {
                    $content .= static::getUnsubscribedContentForDrillDown();
                }
                if ($campaignItem->hasAtLeastOneBounceActivity())
                {
                    $content .= static::getBouncedContentForDrillDown();
                }
            }
            else //still awaiting queueing
            {
                $content = static::getAwaitingQueueingContentForDrillDown();
            }
            return $content;
        }

        /**
         * @param Contact $contact
         * @return string
         */
        public static function resolveContactWithLink(Contact $contact)
        {
            $linkContent = static::renderRestrictedContactAccessLink($contact);
            if (ActionSecurityUtil::canCurrentUserPerformAction('Details', $contact))
            {
                $moduleClassName = static::resolveModuleClassName($contact);
                $linkRoute       = '/' . $moduleClassName::getDirectoryName() . '/default/details';
                $link            = ActionSecurityUtil::resolveLinkToModelForCurrentUser(strval($contact), $contact,
                                       $moduleClassName, $linkRoute);
                if ($link != null)
                {
                    $linkContent = $link;
                }
            }
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-name'), $linkContent);
        }

        /**
         * @param Contact $contact
         * @return string
         */
        protected static function renderRestrictedContactAccessLink(Contact $contact)
        {
            $title       = Zurmo::t('CampaignsModule', 'You cannot see this contact due to limited access');
            $content     = ZurmoHtml::tag('em', array(), Zurmo::t('Core', 'Restricted'));
            $content    .= ZurmoHtml::tag('span', array('id'    => 'restricted-access-contact-tooltip' . $contact->id,
                                                        'class' => 'tooltip',
                                                        'title' => $title), '?');
            $qtip = new ZurmoTip(array('options' => array('position' => array('my' => 'bottom left', 'at' => 'top left',
                                                          'adjust' => array('x' => 6, 'y' => -1)))));
            $qtip->addQTip('#restricted-access-contact-tooltip' . $contact->id);
            return $content;
        }

        /**
         * @param EmailMessage $emailMessage
         * @return string
         */
        protected static function renderRestrictedEmailMessageAccessLink(EmailMessage $emailMessage)
        {
            $title       = Zurmo::t('CampaignsModule', 'You cannot see the performance metrics due to limited access');
            $content     = ZurmoHtml::tag('em', array(), Zurmo::t('Core', 'Restricted'));
            $content    .= ZurmoHtml::tag('span', array('id'    => 'restricted-access-email-message-tooltip' . $emailMessage->id,
                           'class' => 'tooltip',
                           'title' => $title), '?');
            $qtip = new ZurmoTip(array('options' => array('position' => array('my' => 'bottom left', 'at' => 'top left',
                           'adjust' => array('x' => 6, 'y' => -1)))));
            $qtip->addQTip('#restricted-access-email-message-tooltip' . $emailMessage->id);
            return $content;
        }

        /**
         * @param Contact $contact
         * @return string
         */
        protected static function resolveModuleClassName(Contact $contact)
        {
            if (LeadsUtil::isStateALead($contact->state))
            {
                return 'LeadsModule';
            }
            else
            {
                return $contact->getModuleClassName();
            }
        }

        /**
         * @param CampaignItem $campaignItem
         * @return string
         */
        protected static function renderMetricsContent(CampaignItem $campaignItem)
        {
            if (!ActionSecurityUtil::canCurrentUserPerformAction('Details', $campaignItem->emailMessage))
            {
                return static::renderRestrictedEmailMessageAccessLink($campaignItem->emailMessage);
            }
            $isQueued              = $campaignItem->isQueued();
            $isSkipped             = $campaignItem->isSkipped();
            if ($isQueued)
            {
                $content = static::getQueuedContent();
            }
            elseif ($isSkipped)
            {
                $content = static::getSkippedContent();
            }
            elseif ($campaignItem->hasFailedToSend())
            {
                $content = static::getSendFailedContent();
            }
            elseif ($campaignItem->isSent())
            {
                $content = static::getSentContent();
                if ($campaignItem->hasAtLeastOneOpenActivity())
                {
                    $content .= static::getOpenedContent();
                }
                if ($campaignItem->hasAtLeastOneClickActivity())
                {
                    $content .= static::getClickedContent();
                }
                if ($campaignItem->hasAtLeastOneUnsubscribeActivity())
                {
                    $content .= static::getUnsubscribedContent();
                }
                if ($campaignItem->hasAtLeastOneBounceActivity())
                {
                    $content .= static::getBouncedContent();
                }
            }
            else //still awaiting queueing
            {
                $content = static::getAwaitingQueueingContent();
            }
            return ZurmoHtml::wrapAndRenderContinuumButtonContent($content);
        }

        protected static function getQueuedContent()
        {
            $content = '<i>&#9679;</i><span>' . Zurmo::t('MarketingModule', 'Queued') . '</span>';
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-stage-status queued'), $content);
        }

        protected static function getQueuedContentForDrillDown(CampaignItem $campaignItem)
        {
            $monitorJobData = JobsToJobsCollectionViewUtil::getNonMonitorJobsData();
            if ($campaignItem->emailMessage->folder->type == EmailFolder::TYPE_OUTBOX_ERROR)
            {
                $content = Zurmo::t('MarketingModule',
                                    'Attempted to send the message {count} times but an error occurred: {error}.',
                    array('{count}' => $campaignItem->emailMessage->sendAttempts,
                          '{error}' => strval($campaignItem->emailMessage->error)));
            }
            else
            {
                $content = Zurmo::t('MarketingModule',
                                'The last completed run date of the {jobName} job was on {dateTime}. The email message has not yet been sent.',
                                array('{jobName}'  => ProcessOutboundEmailJob::getDisplayName(),
                                      '{dateTime}' => $monitorJobData[ProcessOutboundEmailJob::getType()]['lastCompletedRunEncodedContent']));
            }
            return ZurmoHtml::tag('h4', array(), $content);
        }

        protected static function getSkippedContent()
        {
            $span       = ZurmoHtml::tag('span', array(), Zurmo::t('Core', 'Skipped'));
            $content    = '<i>&#9679;</i>' . $span;
            return ZurmoHtml::tag('div',
                                  array('class'        => 'email-recipient-stage-status stage-false'),
                                  $content
            );
        }

        protected static function getSkippedContentForDrillDown(CampaignItem $campaignItem)
        {
            $campaignItemActivities = CampaignItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                                            CampaignItemActivity::TYPE_SKIP,
                                            $campaignItem->id,
                                            $campaignItem->contact->getClassId('Person'),
                                            null,
                                            'latestDateTime'
                                      );
            $content = Zurmo::t('MarketingModule', 'The message was not created.');
            if ($campaignItem->contact->primaryEmail->emailAddress == null)
            {
                if ($campaignItem->contact->secondaryEmail->emailAddress == null)
                {
                    $content = Zurmo::t('MarketingModule', 'Contact has no primary email address populated.');
                }
                else
                {
                    $content = Zurmo::t('MarketingModule', 'The primary email address is not populated. ' .
                                        'The secondary email address is populated, but the primary email address is the one used to send the email message.');
                }
            }
            elseif (MarketingListMember::getByMarketingListIdContactIdAndUnsubscribed(
                    $campaignItem->campaign->marketingList->id,
                    $campaignItem->contact->id,
                    true) != false)
            {
                $content = Zurmo::t('MarketingModule', 'The contact is not subscribed to the MarketingListsModuleSingularLabel',
                    LabelUtil::getTranslationParamsForAllModules());
            }
            elseif ($campaignItem->contact->primaryEmail->optOut)
            {
                if ($campaignItem->contact->secondaryEmail->optOut)
                {
                    $content = Zurmo::t('MarketingModule', 'The primary email address is opted out.');
                }
                else
                {
                    $content = Zurmo::t('MarketingModule', 'The primary email address is opted out. ' .
                                        'The secondary email address is not opted out but the primary email address is the one used to send the email message.');
                }
            }

            return ZurmoHtml::tag('h4', array(), $content);
        }

        protected static function getSentContent()
        {
            $content = '<i>&#9679;</i><span>' . Zurmo::t('Core', 'Sent') . '</span>';
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-stage-status stage-true'), $content);
        }

        protected static function getSentContentForDrillDown(EmailMessage $emailMessage)
        {
            $content = Zurmo::t('MarketingModule',
                                'Email message was sent on {sentDateTime}',
                                array('{sentDateTime}' => DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($emailMessage->sentDateTime)));
            return ZurmoHtml::tag('h4', array(), $content);
        }

        protected static function getSendFailedContent()
        {
            $content = '<i>&#9679;</i><span>' . Zurmo::t('MarketingModule', 'Send Failed') . '</span>';
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-stage-status stage-false'), $content);
        }

        protected static function getSendFailedContentForDrillDown(CampaignItem $campaignItem)
        {
            $emailMessage = $campaignItem->emailMessage;
            if ($emailMessage->hasSendError())
            {
                $errorContent = Zurmo::t('MarketingModule',
                                         'This message was undeliverable after {count} attempts due to the following reason: {error}.',
                                         array('{count}' => $emailMessage->sendAttempts,
                                               '{error}' => strval($emailMessage->error)));
            }
            return ZurmoHtml::tag('h4', array('class' => 'error'), $errorContent);
        }

        protected static function getOpenedContent()
        {
            $content = '<i>&#9679;</i><span>' . Zurmo::t('MarketingModule', 'Opened') . '</span>';
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-stage-status stage-true'), $content);
        }

        protected static function getOpenedContentForDrillDown(CampaignItem $campaignItem)
        {
            $typesArray = CampaignItemActivity::getTypesArray();
            $campaignItemActivities = CampaignItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                CampaignItemActivity::TYPE_OPEN,
                $campaignItem->id,
                $campaignItem->contact->getClassId('Person'),
                null,
                'latestDateTime'
            );
            $content = null;
            foreach ($campaignItemActivities as $campaignItemActivity)
            {
                $content .= '<tr>';
                $content .= '<td>' . $typesArray[CampaignItemActivity::TYPE_OPEN] . '</td>';
                $content .= '<td>' . DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($campaignItemActivity->latestDateTime) . '</td>';
                $content .= '<td>' . $campaignItemActivity->quantity . '</td>';
                $content .= '<td>' . $campaignItemActivity->latestSourceIP . '</td>';
                $content .= '<td></td>';
                $content .= '</tr>';
            }
            return $content;
        }

        protected static function getClickedContent()
        {
            $content = '<i>&#9679;</i><span>' . Zurmo::t('MarketingModule', 'Clicked') . '</span>';
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-stage-status stage-true'), $content);
        }

        protected static function getClickedContentForDrillDown(CampaignItem $campaignItem)
        {
            $typesArray = CampaignItemActivity::getTypesArray();
            $campaignItemActivities = CampaignItemActivity::getByTypeAndModelIdAndPersonIdAndUrl(
                CampaignItemActivity::TYPE_CLICK,
                $campaignItem->id,
                $campaignItem->contact->getClassId('Person'),
                null,
                'latestDateTime'
            );
            $content = null;
            foreach ($campaignItemActivities as $campaignItemActivity)
            {
                $content .= '<tr>';
                $content .= '<td>' . $typesArray[CampaignItemActivity::TYPE_CLICK] . '</td>';
                $content .= '<td>' . DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($campaignItemActivity->latestDateTime) . '</td>';
                $content .= '<td>' . $campaignItemActivity->quantity . '</td>';
                $content .= '<td>' . $campaignItemActivity->latestSourceIP . '</td>';
                $content .= '<td>' . $campaignItemActivity->emailMessageUrl . '</td>';
                $content .= '</tr>';
            }
            return $content;
        }

        protected static function getUnsubscribedContent()
        {
            $content = '<i>&#9679;</i><span>' . Zurmo::t('Core', 'Unsubscribed') . '</span>';
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-stage-status stage-false'), $content);
        }

        protected static function getUnsubscribedContentForDrillDown()
        {
            return null;
        }

        protected static function getBouncedContent()
        {
            $content = '<i>&#9679;</i><span>' . Zurmo::t('MarketingModule', 'Bounced') . '</span>';
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-stage-status stage-false'), $content);
        }

        protected static function getBouncedContentForDrillDown()
        {
            return null;
        }

        protected static function getAwaitingQueueingContent()
        {
            $content = '<i>&#9679;</i><span>' . Zurmo::t('MarketingModule', 'Awaiting queueing') . '</span>';
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-stage-status queued'), $content);
        }

        protected static function getAwaitingQueueingContentForDrillDown()
        {
            $monitorJobData = JobsToJobsCollectionViewUtil::getNonMonitorJobsData();
            $content = Zurmo::t('MarketingModule',
                                'The last completed run date of the {jobName} job was on {dateTime}. The email message has not yet been created.',
                                array('{jobName}'  => CampaignQueueMessagesInOutboxJob::getDisplayName(),
                                      '{dateTime}' => $monitorJobData[CampaignQueueMessagesInOutboxJob::getType()]['lastCompletedRunEncodedContent']));
            return ZurmoHtml::tag('h4', array(), $content);
        }

        protected static function getWrapperTable($tableRows)
        {
            if (empty($tableRows))
            {
                return null;
            }
            $tableContent  = '<table>';
            $tableContent .= '<thead><tr>';
            $tableContent .= '<th>' . Zurmo::t('MarketingModule', 'Event') . '</th>';
            $tableContent .= '<th>' . Zurmo::t('ZurmoModule', 'Latest Date Time') . '</th>';
            $tableContent .= '<th>' . Zurmo::t('Core', 'Quantity') . '</th>';
            $tableContent .= '<th>' . Zurmo::t('MarketingModule', 'Latest source IP') . '</th>';
            $tableContent .= '<th>' . Zurmo::t('Core', 'URL') . '</th>';
            $tableContent .= '</tr></thead>';
            $tableContent .= $tableRows;
            $tableContent .= '</table>';
            return $tableContent;
        }
    }
?>