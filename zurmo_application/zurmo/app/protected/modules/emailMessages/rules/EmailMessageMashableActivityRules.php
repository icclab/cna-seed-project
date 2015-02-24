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
     * Generic rules for the email message model.
     */
    class EmailMessageMashableActivityRules extends MashableActivityRules
    {
        public function resolveSearchAttributesDataByRelatedItemId($relationItemId)
        {
            assert('is_int($relationItemId)');
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'sender',
                    'relatedModelData' => array(
                        'attributeName'        => 'personsOrAccounts',
                        'relatedAttributeName' => 'id',
                        'operatorType'         => 'equals',
                        'value'                => $relationItemId,
                    )
                ),
                2 => array(
                    'attributeName'        => 'recipients',
                    'relatedModelData' => array(
                        'attributeName'        => 'personsOrAccounts',
                        'relatedAttributeName' => 'id',
                        'operatorType'         => 'equals',
                        'value'                => $relationItemId,
                        'resolveAsSubquery'    => true,
                    )
                )
            );
            $searchAttributeData['structure'] = '(1 or 2)';
            return $this->resolveSearchAttributeDataForLatestActivities($searchAttributeData);
        }

        /**
         * @param array $relationItemIds
         * @return array
         */
        public function resolveSearchAttributesDataByRelatedItemIds($relationItemIds)
        {
            assert('is_array($relationItemIds)');
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'sender',
                    'relatedModelData' => array(
                        'attributeName'        => 'personsOrAccounts',
                        'relatedAttributeName' => 'id',
                        'operatorType'         => 'oneOf',
                        'value'                => $relationItemIds,
                    )
                ),
                2 => array(
                    'attributeName'        => 'recipients',
                    'relatedModelData' => array(
                        'attributeName'        => 'personsOrAccounts',
                        'relatedAttributeName' => 'id',
                        'operatorType'         => 'oneOf',
                        'value'                => $relationItemIds,
                        'resolveAsSubquery'    => true,
                    )
                )
            );
            $searchAttributeData['structure'] = '(1 or 2)';
            return $this->resolveSearchAttributeDataForLatestActivities($searchAttributeData);
        }

        public function resolveSearchAttributeDataForLatestActivities($searchAttributeData)
        {
            assert('is_array($searchAttributeData)');
            return $searchAttributeData;
        }

        public function resolveSearchAttributeDataForAllLatestActivities($searchAttributeData)
        {
            assert('is_array($searchAttributeData)');
            $box                 = EmailBox::resolveAndGetByName(EmailBox::NOTIFICATIONS_NAME);
            $searchAttributeData = parent::resolveSearchAttributeDataForAllLatestActivities($searchAttributeData);
            $clausesCount = count($searchAttributeData['clauses']);
            $searchAttributeData['clauses'][$clausesCount + 1] = array(
                'attributeName'        => 'folder',
                    'relatedModelData'  => array(
                        'attributeName' => 'emailBox',
                        'operatorType'  => 'doesNotEqual',
                        'value'         => $box->id),
            );
            if ($searchAttributeData['structure'] != null)
            {
                $searchAttributeData['structure'] .= ' and ';
            }
            $searchAttributeData['structure'] .=  ($clausesCount + 1);
            return $searchAttributeData;
        }

        public function getLatestActivitiesOrderByAttributeName()
        {
            return 'modifiedDateTime';
        }

        public function getLatestActivityExtraDisplayStringByModel($model)
        {
            return FileModelDisplayUtil::renderFileDataDetailsWithDownloadLinksContent($model, 'files');
        }

        /**
         * (non-PHPdoc)
         * @see MashableActivityRules::getSummaryContentTemplate()
         */
        public function getSummaryContentTemplate($ownedByFilter, $viewModuleClassName)
        {
            assert('is_string($ownedByFilter)');
            assert('is_string($viewModuleClassName)');
            return "<span class='less-pronounced-text'>" .
                   "{relatedModelsByImportanceContent} " .
                   "</span><br/><span>{modelStringContent}</span><span>{extraContent}</span>";
        }

        public function renderRelatedModelsByImportanceContent(RedBeanModel $model)
        {
            $content = null;
            if ($model->sender != null  && $model->sender->id > 0)
            {
                $content .= Zurmo::t('EmailMessagesModule', '<span class="email-from"><strong>From:</strong> {senderContent}</span>',
                                    array('{senderContent}' => static::getSenderContent($model->sender)));
            }
            if ($model->recipients->count() > 0)
            {
                if ($content != null)
                {
                    $content .= ' ';
                }
                $content .= Zurmo::t('EmailMessagesModule', '<span class="email-to"><strong>To:</strong> {recipientContent}</span>',
                                    array('{recipientContent}' => static::getRecipientsContent($model->recipients)));
            }
            return $content;
        }

        public static function getSenderContent(EmailMessageSender $emailMessageSender, $additionalParams = array())
        {
            $modelsStringContent  = array();
            if ($emailMessageSender->personsOrAccounts->count() == 0)
            {
                $modelsStringContent[] = $emailMessageSender->fromAddress . ' ' . $emailMessageSender->fromName;
            }
            else
            {
                foreach ($emailMessageSender->personsOrAccounts as $personOrAccount)
                {
                    try
                    {
                        $castedDownModel = self::castDownItem($personOrAccount);
                        if (strval($castedDownModel) != null)
                        {
                            $params          = array('label' => strval($castedDownModel), 'wrapLabel' => false);
                            $moduleClassName = $castedDownModel->getModuleClassName();
                            $moduleId        = $moduleClassName::getDirectoryName();
                            $element         = new DetailsLinkActionElement('default', $moduleId,
                                                                            $castedDownModel->id,
                                                                            array_merge($params, $additionalParams));
                            $modelsStringContent[] = $element->render();
                        }
                    }
                    catch (AccessDeniedSecurityException $e)
                    {
                        $modelsStringContent[] = $emailMessageSender->fromAddress;
                    }
                    catch (NotSupportedException $e)
                    {
                        //If the personOrAccount no longer exists or something else isn't right with the model
                        $modelsStringContent[] = $emailMessageSender->fromAddress . ' ' . $emailMessageSender->fromName;
                    }
                }
            }
            $senderString = self::resolveStringValueModelsDataToStringContent($modelsStringContent);
            if (count($modelsStringContent) > 1)
            {
                return $emailMessageSender->fromAddress . '(' . $senderString . ')';
            }
            return $senderString;
        }

        public static function getRecipientsContent(RedBeanOneToManyRelatedModels $recipients, $type = null, $additionalParams = array())
        {
            assert('$type == null || $type == EmailMessageRecipient::TYPE_TO ||
                    EmailMessageRecipient::TYPE_CC || EmailMessageRecipient::TYPE_BCC');
            $existingModels  = array();
            if ($recipients->count() == 0)
            {
                return;
            }
            foreach ($recipients as $recipient)
            {
                if ($type == null || $recipient->type == $type)
                {
                    $existingPersonsOrAccounts = array();
                    if ($recipient->personsOrAccounts->count() == 0)
                    {
                        $existingPersonsOrAccounts[] = $recipient->toAddress . ' ' . $recipient->toName;
                    }
                    else
                    {
                        foreach ($recipient->personsOrAccounts as $personOrAccount)
                        {
                            try
                            {
                                $castedDownModel = self::castDownItem($personOrAccount);
                                if (strval($castedDownModel) != null)
                                {
                                    $params          = array('label' => strval($castedDownModel), 'wrapLabel' => false);
                                    if (get_class($castedDownModel) == 'Contact')
                                    {
                                        $moduleClassName = ContactsStateMetadataAdapter::getModuleClassNameByModel($castedDownModel);
                                    }
                                    else
                                    {
                                        $moduleClassName = $castedDownModel->getModuleClassName();
                                    }
                                    $moduleId        = $moduleClassName::getDirectoryName();
                                    $element         = new DetailsLinkActionElement('default', $moduleId,
                                                                                    $castedDownModel->id,
                                                                 array_merge($params, $additionalParams));
                                    $existingPersonsOrAccounts[] = $element->render();
                                }
                            }
                            catch (AccessDeniedSecurityException $e)
                            {
                                $existingPersonsOrAccounts[] = $recipient->toAddress . ' ' . $recipient->toName;
                            }
                            catch (NotSupportedException $e)
                            {
                                //If the personOrAccount no longer exists or something else isn't right with the model
                                $existingPersonsOrAccounts[] = $recipient->toAddress . ' ' . $recipient->toName;
                            }
                        }
                    }
                    $recipientString = self::resolveStringValueModelsDataToStringContent($existingPersonsOrAccounts);
                    if (count($existingPersonsOrAccounts) > 1)
                    {
                        $existingModels[] = $recipient->toAddress . '(' . $recipientString . ')';
                    }
                    else
                    {
                        $existingModels[] = $recipientString;
                    }
                }
            }
            return self::resolveStringValueModelsDataToStringContent($existingModels);
        }

        public static function castDownItem(Item $item)
        {
            foreach (array('Contact', 'User', 'Account') as $modelClassName)
            {
                try
                {
                    $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($modelClassName);
                    return $item->castDown(array($modelDerivationPathToItem));
                }
                catch (NotFoundException $e)
                {
                }
            }
            throw new NotSupportedException();
        }

        /**
         * Override to split emailMessages into send and received.  This helps to improve performance on
         * showing lists of email messages as activities.
         * @param $searchAttributesDataStructure
         */
        public static function resolveSearchAttributesDataStructure(& $searchAttributesDataStructure)
        {
            assert('is_string($searchAttributesDataStructure) || $searchAttributesDataStructure === null');
            $searchAttributesDataStructure = '1';
        }

        /**
         * Override to split out emailMessages into send and received. This helps to improve performance on
         * showing lists of email messages as activities.
         * @param $modelClassName
         * @param $relationItemIds
         * @param $ownedByFilter
         * @param $shouldResolveSearchAttributeDataForLatestActivities
         * @param $modelClassNamesAndSearchAttributeData
         */
        public function resolveAdditionalSearchAttributesDataByModelClassNameAndRelatedItemIds(
                            $modelClassName, $relationItemIds, $ownedByFilter,
                            $shouldResolveSearchAttributeDataForLatestActivities,
                            & $modelClassNamesAndSearchAttributeData)
        {
            assert('is_string($modelClassName)');
            assert('is_array($relationItemIds)');
            assert('$ownedByFilter == LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL ||
                    $ownedByFilter == LatestActivitiesConfigurationForm::OWNED_BY_FILTER_USER ||
                    is_int($ownedByFilter)');
            assert('is_bool($shouldResolveSearchAttributeDataForLatestActivities)');
            assert('is_array($modelClassNamesAndSearchAttributeData)');
            if (count($relationItemIds) > 1)
            {
                $searchAttributesData =     // Not Coding Standard
                    $this->resolveSearchAttributesDataByRelatedItemIds($relationItemIds);
                $searchAttributesData['structure'] = '2';
            }
            elseif (count($relationItemIds) == 1)
            {
                $searchAttributesData =    // Not Coding Standard
                    $this->resolveSearchAttributesDataByRelatedItemId($relationItemIds[0]);
                $searchAttributesData['structure'] = '2';
            }
            else
            {
                $searchAttributesData              = array();
                $searchAttributesData['clauses']   = array();
                $searchAttributesData['structure'] = null;
                $searchAttributesData =    // Not Coding Standard
                    $this->resolveSearchAttributeDataForAllLatestActivities($searchAttributesData);
            }
            if ($shouldResolveSearchAttributeDataForLatestActivities)
            {
                $searchAttributesData =    // Not Coding Standard
                    $this->resolveSearchAttributeDataForLatestActivities($searchAttributesData);
            }
            $this->resolveSearchAttributesDataByOwnedByFilter($searchAttributesData, $ownedByFilter);
            $modelClassNamesAndSearchAttributeData[] = array($modelClassName => $searchAttributesData);
        }
    }
?>