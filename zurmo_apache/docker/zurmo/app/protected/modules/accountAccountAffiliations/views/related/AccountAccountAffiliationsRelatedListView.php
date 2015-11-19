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

    class AccountAccountAffiliationsRelatedListView extends SecuredRelatedListView
    {
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'perUser' => array(
                    'title' => "eval:Zurmo::t('AccountAccountAffiliationsModule',
                                'AccountsModuleSingularLabel Affiliations', LabelUtil::getTranslationParamsForAllModules())",
                ),
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array(  'type'            => 'CreateFromRelatedListLink',
                                    'routeModuleId'   => 'eval:$this->moduleId',
                                    'routeParameters' => 'eval:$this->getCreateLinkRouteParametersForPrimaryAccount()',
                                    'label'           => 'eval:$this->getSelectPrimaryAccountLinkLabel()',
                            ),
                            array(  'type'            => 'CreateFromRelatedListLink',
                                    'routeModuleId'   => 'eval:$this->moduleId',
                                    'routeParameters' => 'eval:$this->getCreateLinkRouteParametersForSecondaryAccount()',
                                    'label'           => 'eval:$this->getSelectSecondaryAccountLinkLabel()',
                            ),
                        ),
                    ),
                    'rowMenu' => array(
                        'elements' => array(
                            array('type' => 'EditLink'),
                            array('type' => 'RelatedDeleteLink'),
                        ),
                    ),
                    'derivedAttributeTypes' => array(
                        'AccountAccountAffiliationOppositeModel',
                    ),
                    'gridViewType' => RelatedListView::GRID_VIEW_TYPE_STACKED,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null',
                                                      'type'          => 'AccountAccountAffiliationOppositeModel'),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        /**
         * @return array
         */
        protected function makeSearchAttributeData()
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'primaryAccount',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => (int)$this->params['relationModel']->id,
                ),
                2 => array(
                'attributeName'            => 'secondaryAccount',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => (int)$this->params['relationModel']->id,
                )
            );
            $searchAttributeData['structure'] = '(1 or 2)';
            return $searchAttributeData;
        }

        protected function getCreateLinkRouteParametersForPrimaryAccount()
        {
            return array(
                'relationAttributeName' => 'secondaryAccount',
                'relationModelId'       => $this->params['relationModel']->id,
                'relationModuleId'      => $this->params['relationModuleId'],
                'redirectUrl'           => $this->params['redirectUrl'],
            );
        }

        protected function getCreateLinkRouteParametersForSecondaryAccount()
        {
            return array(
                'relationAttributeName' => 'primaryAccount',
                'relationModelId'       => $this->params['relationModel']->id,
                'relationModuleId'      => $this->params['relationModuleId'],
                'redirectUrl'           => $this->params['redirectUrl'],
            );
        }

        protected function getSelectPrimaryAccountLinkLabel()
        {
            return AccountAccountAffiliationsModule::resolveAccountRelationLabel('Singular', 'primary');
        }

        protected function getSelectSecondaryAccountLinkLabel()
        {
            return AccountAccountAffiliationsModule::resolveAccountRelationLabel('Singular', 'secondary');
        }

        /**
         * This is required to be defined even though it is not used by this view since we have to use
         * both primaryAccount and secondaryAccount relations together.
         * @return string
         */
        protected function getRelationAttributeName()
        {
            return 'notUsed';
        }

        public static function getAllowedOnPortletViewClassNames()
        {
            return array('AccountDetailsAndRelationsView');
        }

        public static function getModuleClassName()
        {
            return 'AccountAccountAffiliationsModule';
        }

        protected function getEmptyText()
        {
            return Zurmo::t('AccountContactAffiliationsModule',
                'No AccountsModuleSingularLowerCaseLabel affiliations found',
                LabelUtil::getTranslationParamsForAllModules());
        }

        /**
         * What kind of PortletRules this view follows.
         * @return PortletRulesType as string.
         */
        public static function getPortletRulesType()
        {
            return 'AccountAccountAffiliationsRelatedList';
        }

        public function renderPortletHeadContent()
        {
            return $this->renderActionContent();
        }

        protected function renderActionContent()
        {
            $actionElementContent = $this->renderActionElementMenu(Zurmo::t('Core', 'Create'));
            $content              = null;
            if ($actionElementContent != null)
            {
                $content .= '<div class="view-toolbar-container toolbar-mbmenu clearfix"><div class="view-toolbar">';
                $content .= $actionElementContent;
                $content .= '</div></div>';
            }
            return $content;
        }

        public function getOppositeModelListViewString()
        {
            return 'AccountAccountAffiliationsRelatedListView::' .
                   'resolveOpposingAccountLinkContent($data, ' . (int)$this->params["relationModel"]->id . ')';
        }

        public static function resolveOpposingAccountLinkContent(AccountAccountAffiliation $accountAccountAffiliation, $accountId)
        {
            assert('is_int($accountId)');
            if ($accountAccountAffiliation->primaryAccount->id == $accountId)
            {
                $content  = static::resolveAccountWithLinkContent($accountAccountAffiliation->secondaryAccount);
                $content .= ' ' . $accountAccountAffiliation->getAttributeLabel('secondaryAccount');
            }
            else
            {
                $content  = static::resolveAccountWithLinkContent($accountAccountAffiliation->primaryAccount);
                $content .= ' ' . $accountAccountAffiliation->getAttributeLabel('primaryAccount');
            }
            return $content;
        }

        /**
         * @param Account $account
         * @return string
         */
        public static function resolveAccountWithLinkContent(Account $account)
        {
            if (ActionSecurityUtil::canCurrentUserPerformAction('Details', $account))
            {
                $moduleClassName = $account->getModuleClassName();
                $linkRoute       = '/' . $moduleClassName::getDirectoryName() . '/default/details';
                $link            = ActionSecurityUtil::resolveLinkToModelForCurrentUser(strval($account), $account,
                                   $moduleClassName, $linkRoute);
                if ($link != null)
                {
                    $linkContent = $link;
                }
                return ZurmoHtml::tag('div', array(), $linkContent);
            }
        }
    }
?>