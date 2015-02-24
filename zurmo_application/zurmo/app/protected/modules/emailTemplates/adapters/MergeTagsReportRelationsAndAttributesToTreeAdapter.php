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
     * Helper class for adapting relation and attribute data into tree data for showing a list of selectable merge tags
     */
    class MergeTagsReportRelationsAndAttributesToTreeAdapter extends ReportRelationsAndAttributesToTreeAdapter
    {
        protected $uniqueId;

        /**
         * @param Report $report
         * @param string $treeType
         * @param null $uniqueId
         */
        public function __construct(Report $report, $treeType, $uniqueId = null)
        {
            assert('is_string($uniqueId) || $uniqueId === null');
            parent::__construct($report, $treeType);
            $this->uniqueId   = $uniqueId;
        }

        /**
         * Override to support adding special tags
         * @param string $nodeId
         * @return array
         */
        public function getData($nodeId)
        {
            $data = parent::getData($nodeId);
            if ($nodeId == 'source')
            {
                $data[1] = $data[0];
                $data[0] = array('expanded' => false,
                                 'text'     => 'Special Tags');
                $data[0]['children'] = $this->resolveSpecialTagNodes();
            }
            return $data;
        }

        protected function resolveTreeTypeForMakingOrExplodingNodeId()
        {
            return $this->uniqueId;
        }

        protected function resolveSpecialTagNodes()
        {
            $specialTagNodesData = array();
            foreach ($this->getSpecialTagsData() as $data)
            {
                $node = array(  'id'           => self::makeNodeId($data['id']),
                                'text'         => $data['label'],
                                'dataValue'    => $data['dataValue'],
                                'wrapperClass' => 'item-to-place',
                                'expanded'     => false,
                                'hasChildren'  => false);
                $specialTagNodesData[] = $node;
            }
            return $specialTagNodesData;
        }

        /**
         * Override to call a different report adapter class for merge tags. This will allow special tags to be
         * added as well as blocking hasMany variations.
         * @param string $moduleClassName
         * @param string $modelClassName
         * @return ModelRelationsAndAttributesToReportAdapter based object
         */
        protected function makeModelRelationsAndAttributesToReportAdapter($moduleClassName, $modelClassName)
        {
            assert('is_string($moduleClassName)');
            assert('is_string($modelClassName)');
            $rules   = ReportRules::makeByModuleClassName($moduleClassName);
            $model   = new $modelClassName(false);
            return new MergeTagsModelRelationsAndAttributesToRowsAndColumnsReportAdapter($model,
                        $rules,
                        $this->report->getType(),
                        $moduleClassName);
        }

        /**
         * Override as needed
         * @param $attributeNode
         * @param $attribute
         * @param $nodeIdPrefix
         */
        protected function resolveChildNodeDataValueForAttributeNode(& $attributeNode, $attribute, $nodeIdPrefix)
        {
            $attributeNode['dataValue'] = MergeTagsUtil::resolveAttributeStringToMergeTagString($nodeIdPrefix . $attribute);
        }

        /**
         * [[MODEL^URL]] : prints absolute url to the current model attached to template.
         * [[BASE^URL]] : prints absolute url to the current install without trailing slash.
         * [[APPLICATION^NAME]] : prints application name as set in global settings > application name.
         * [[CURRENT^YEAR]] : prints current year.
         * [[LAST^YEAR]] : prints last year.
         * [[OWNERS^AVATAR^SMALL]] : prints the owner's small avatar image (32x32).
         * [[OWNERS^AVATAR^MEDIUM ]] : prints the owner's medium avatar image (32x32).
         * [[OWNERS^AVATAR^LARGE]] : prints the owner's large avatar image (32x32).
         * [[OWNERS^EMAIL^SIGNATURE]] : prints the owner's email signature.
         * [[UNSUBSCRIBE^URL]] : prints unsubscribe url.
         * [[MANAGE^SUBSCRIPTIONS^URL]] : prints manage subscriptions url.
         * @return array
         */
        protected function getSpecialTagsData()
        {
            return  array(
                array('id'        => 'modelUrl',
                      'label'     => Zurmo::t('EmailTemplatesModule', 'Model URL'),
                      'dataValue' => MergeTagsUtil::TAG_PREFIX . 'MODEL' . MergeTagsUtil::CAPITAL_DELIMITER . 'URL' .
                                     MergeTagsUtil::TAG_SUFFIX),
                array('id'        => 'baseUrl',
                      'label'     => Zurmo::t('EmailTemplatesModule', 'Base URL'),
                      'dataValue' => MergeTagsUtil::TAG_PREFIX . 'BASE' . MergeTagsUtil::CAPITAL_DELIMITER . 'URL' .
                                   MergeTagsUtil::TAG_SUFFIX),
                array('id'        => 'applicationName',
                      'label'     => Zurmo::t('ZurmoModule', 'Application Name'),
                      'dataValue' => MergeTagsUtil::TAG_PREFIX . 'APPLICATION' . MergeTagsUtil::CAPITAL_DELIMITER . 'NAME' .
                                   MergeTagsUtil::TAG_SUFFIX),
                array('id'        => 'currentYear',
                      'label'     => Zurmo::t('EmailTemplatesModule', 'Current Year'),
                      'dataValue' => MergeTagsUtil::TAG_PREFIX . 'CURRENT' . MergeTagsUtil::CAPITAL_DELIMITER . 'YEAR' .
                                   MergeTagsUtil::TAG_SUFFIX),
                array('id'        => 'lastYear',
                      'label'     => Zurmo::t('EmailTemplatesModule', 'Last Year'),
                      'dataValue' => MergeTagsUtil::TAG_PREFIX . 'LAST' . MergeTagsUtil::CAPITAL_DELIMITER . 'YEAR' .
                                   MergeTagsUtil::TAG_SUFFIX),
                array('id'        => 'ownersAvatarSmall',
                      'label'     => Zurmo::t('EmailTemplatesModule', 'Owner\'s Avatar Small'),
                      'dataValue' => MergeTagsUtil::TAG_PREFIX . 'OWNERS' . MergeTagsUtil::CAPITAL_DELIMITER . 'AVATAR' .
                                   MergeTagsUtil::CAPITAL_DELIMITER . 'SMALL' . MergeTagsUtil::TAG_SUFFIX),
                array('id'        => 'ownersAvatarMedium',
                      'label'     => Zurmo::t('EmailTemplatesModule', 'Owner\'s Avatar Medium'),
                      'dataValue' => MergeTagsUtil::TAG_PREFIX . 'OWNERS' . MergeTagsUtil::CAPITAL_DELIMITER . 'AVATAR' .
                                     MergeTagsUtil::CAPITAL_DELIMITER . 'MEDIUM' . MergeTagsUtil::TAG_SUFFIX),
                array('id'        => 'ownersAvatarLarge',
                      'label'     => Zurmo::t('EmailTemplatesModule', 'Owner\'s Avatar Large'),
                      'dataValue' => MergeTagsUtil::TAG_PREFIX . 'OWNERS' . MergeTagsUtil::CAPITAL_DELIMITER . 'AVATAR' .
                                     MergeTagsUtil::CAPITAL_DELIMITER . 'LARGE' . MergeTagsUtil::TAG_SUFFIX),
                array('id'        => 'ownersEmailSignature',
                    'label'     => Zurmo::t('EmailTemplatesModule', 'Owner\'s Email Signature'),
                    'dataValue' => MergeTagsUtil::TAG_PREFIX . 'OWNERS' . MergeTagsUtil::CAPITAL_DELIMITER . 'EMAIL' .
                        MergeTagsUtil::CAPITAL_DELIMITER . 'SIGNATURE' . MergeTagsUtil::TAG_SUFFIX),
                array('id'        => 'unsubscribeUrl',
                      'label'     => Zurmo::t('EmailTemplatesModule', 'Unsubscribe URL'),
                      'dataValue' => GlobalMarketingFooterUtil::resolveUnsubscribeUrlMergeTag()),
                array('id'        => 'manageSubscriptionsUrl',
                      'label'     => Zurmo::t('EmailTemplatesModule', 'Manage Subscriptions URL'),
                      'dataValue' => GlobalMarketingFooterUtil::resolveManageSubscriptionsMergeTag()),
            );
        }
    }
?>