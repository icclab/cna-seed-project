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

    class CampaignItemsRelatedListView extends RelatedListView
    {
        protected static $persistantCampaignItemsPortletConfigs = array(
            'filteredByStage'
        );

        protected $showStageFilter = true;

        function __construct($viewData, $params, $uniqueLayoutId)
        {
            parent::__construct($viewData, $params, $uniqueLayoutId);
            $this->uniquePageId             = get_called_class();
            $campaignItemsConfigurationForm = $this->getConfigurationForm();
            $this->resolveCampaignItemsConfigFormFromRequest($campaignItemsConfigurationForm);
            $this->configurationForm        = $campaignItemsConfigurationForm;
            $this->relationModuleId         = $this->params['relationModuleId'];
        }

        protected function renderContent()
        {
            $content  = $this->renderConfigurationForm();
            $content .= parent::renderContent();
            return ZurmoHtml::tag('div', array('class' => $this->getWrapperDivClass()), $content);
        }

        protected function getGridViewWidgetPath()
        {
            return 'application.modules.campaigns.widgets.CampaignItemsExtendedGridView';
        }

        protected function getRelationAttributeName()
        {
            return 'campaign';
        }

        protected function getUniquePageId()
        {
            return 'CampaignItemsForPortletView';
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'perUser' => array(
                    'title' => "eval:Zurmo::t('EmailMessagesModule', 'Email Recipients')",
                ),
                'global' => array(
                    'gridViewType' => RelatedListView::GRID_VIEW_TYPE_NORMAL,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'null', 'type' => 'CampaignItemSummary'),
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

        public function getModelClassName()
        {
            return 'CampaignItem';
        }

        public static function getModuleClassName()
        {
            return 'CampaignsModule';
        }

        protected function getEmptyText()
        {
            $content = Zurmo::t('Core', 'Email recipients will appear here once the campaign begins sending out');
            return $content;
        }

        protected function getCGridViewLastColumn()
        {
            return array();
        }

        protected function getWrapperDivClass()
        {
            return CampaignDetailsAndRelationsView::CAMPAIGN_ITEMS_PORTLET_CLASS;
        }

        protected function getCGridViewParams()
        {
            return array_merge(parent::getCGridViewParams(),
                array('hideHeader'     => true,
                      'expandableRows' => true));
        }

        protected function getCGridViewColumns()
        {
            $columns = parent::getCGridViewColumns();
            $firstColumn = array(
                'class'                 => 'CampaignItemsDrillDownColumn',
                'id'                    => $this->gridId . $this->gridIdSuffix . '-rowDrillDown',
                'htmlOptions'           => array('class' => 'hasDrillDownLink')
            );
            array_unshift($columns, $firstColumn);
            return $columns;
        }

        /**
         *
         * Override to filter by email message stage
         */
        protected function makeSearchAttributeData()
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => $this->getRelationAttributeName(),
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => (int)$this->params['relationModel']->id,
                )
            );
            $searchAttributeData['structure'] = '1';

            if ($this->configurationForm->filteredByStage != CampaignItemsConfigurationForm::FILTERED_BY_ALL_STAGES)
            {
                switch($this->configurationForm->filteredByStage)
                {
                    case CampaignItemsConfigurationForm::OPENED_STAGE:
                        $type = CampaignItemActivity::TYPE_OPEN;
                        break;
                    case CampaignItemsConfigurationForm::CLICKED_STAGE:
                        $type = CampaignItemActivity::TYPE_CLICK;
                        break;
                    case CampaignItemsConfigurationForm::BOUNCED_STAGE:
                        $type = CampaignItemActivity::TYPE_BOUNCE;
                        break;
                }
                $searchAttributeData['clauses'][2] = array(
                    'attributeName'             => 'campaignItemActivities',
                    'relatedAttributeName'      => 'type',
                    'operatorType'              => 'equals',
                    'value'                     => $type,
                );
                $searchAttributeData['structure'] = '1 AND 2';
            }

            return $searchAttributeData;
        }

        /**
         * @return string
         */
        protected function renderConfigurationForm()
        {
            $formName   = 'campaign-items-configuration-form';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                'ZurmoActiveForm',
                array(
                    'id' => $formName,
                )
            );
            $content  = $formStart;
            $content .= $this->renderConfigurationFormLayout($form);
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $this->registerConfigurationFormLayoutScripts($form);
            return $content;
        }

        /**
         * @param CampaignItemsConfigurationForm $form
         * @return string
         */
        protected function renderConfigurationFormLayout($form)
        {
            assert('$form instanceof ZurmoActiveForm');
            $content      = null;
            $innerContent = null;
            if ($this->showStageFilter)
            {
                $element                   = new CampaignItemStageFilterRadioElement($this->configurationForm,
                                                                        'filteredByStage',
                                                                        $form,
                                                                        array('relationModel' => $this->params['relationModel']));
                $element->editableTemplate =  '<div id="CampaignItemsConfigurationForm_filteredByStage_area">{content}</div>';
                $stageFilterContent        = $element->render();
                $innerContent             .= $stageFilterContent;
            }
            if ($innerContent != null)
            {
                $content .= '<div class="filter-portlet-model-bar">';
                $content .= $innerContent;
                $content .= '</div>' . "\n";
            }
            return $content;
        }

        /**
         * @param CampaignItemsConfigurationForm $form
         */
        protected function registerConfigurationFormLayoutScripts($form)
        {
            assert('$form instanceof ZurmoActiveForm');
            $urlScript = $this->getPortletDetailsUrl(); // Not Coding Standard
            $ajaxSubmitScript = ZurmoHtml::ajax(array(
                    'type'       => 'GET',
                    'data'       => 'js:$("#' . $form->getId() . '").serialize()',
                    'url'        =>  $urlScript,
                    'update'     => '#' . $this->uniqueLayoutId,
                    'beforeSend' => 'js:function(){$(this).makeSmallLoadingSpinner(true, "#' . $this->getGridViewId() . '"); $("#' . $form->getId() . '").parent().children(".cgrid-view").addClass("loading");}',
                    'complete'   => 'js:function()
                    {
                                        $("#' . $form->getId() . '").parent().children(".cgrid-view").removeClass("loading");
                                        $("#filter-portlet-model-bar-' . $this->uniquePageId . '").show();
                    }'
            ));
            Yii::app()->clientScript->registerScript($this->uniquePageId, "
            $('#CampaignItemsConfigurationForm_filteredByStage_area').buttonset();
            $('#CampaignItemsConfigurationForm_filteredByStage_area').change(function()
                {
                    " . $ajaxSubmitScript . "
                }
            );
            ");
        }

        /**
         * @return CampaignItemsConfigurationForm
         */
        protected function getConfigurationForm()
        {
            return new CampaignItemsConfigurationForm();
        }

        /**
         * @param CampaignItemsConfigurationForm $campaignItemsConfigurationForm
         */
        protected function resolveCampaignItemsConfigFormFromRequest(&$campaignItemsConfigurationForm)
        {
            $excludeFromRestore = array();
            if (isset($_GET[get_class($campaignItemsConfigurationForm)]))
            {
                $campaignItemsConfigurationForm->setAttributes($_GET[get_class($campaignItemsConfigurationForm)]);
                $excludeFromRestore = $this->saveUserSettingsFromConfigForm($campaignItemsConfigurationForm);
            }
            $this->restoreUserSettingsToConfigFrom($campaignItemsConfigurationForm, $excludeFromRestore);
        }

        /**
         * @param CampaignItemsConfigurationForm $campaignItemsConfigurationForm
         * @return array
         */
        protected function saveUserSettingsFromConfigForm(&$campaignItemsConfigurationForm)
        {
            $savedConfigs = array();
            $campaignId = $this->params['relationModel']->id;
            foreach (static::$persistantCampaignItemsPortletConfigs as $persistantCampaignItemConfigItem)
            {
                if ($campaignItemsConfigurationForm->$persistantCampaignItemConfigItem !==
                    CampaignItemsPortletPersistentConfigUtil::getForCurrentUserByPortletIdAndKey($this->params['portletId'],
                                                                        "{$campaignId}_" . $persistantCampaignItemConfigItem))
                {
                    CampaignItemsPortletPersistentConfigUtil::setForCurrentUserByPortletIdAndKey($this->params['portletId'],
                                                            "{$campaignId}_" . $persistantCampaignItemConfigItem,
                                                            $campaignItemsConfigurationForm->$persistantCampaignItemConfigItem
                                                        );
                    $savedConfigs[] = $persistantCampaignItemConfigItem;
                }
            }
            return $savedConfigs;
        }

        /**
         * @param CampaignItemsConfigurationForm $campaignItemsConfigurationForm
         * @param string $excludeFromRestore
         * @return CampaignItemsConfigurationForm
         */
        protected function restoreUserSettingsToConfigFrom(&$campaignItemsConfigurationForm, $excludeFromRestore)
        {
            $campaignId = $this->params['relationModel']->id;
            foreach (static::$persistantCampaignItemsPortletConfigs as $persistantCampaignItemConfigItem)
            {
                if (in_array($persistantCampaignItemConfigItem, $excludeFromRestore))
                {
                    continue;
                }
                $persistantCampaignItemConfigItemValue = CampaignItemsPortletPersistentConfigUtil::getForCurrentUserByPortletIdAndKey(
                                                                                $this->params['portletId'],
                                                                                "{$campaignId}_" . $persistantCampaignItemConfigItem);
                if (isset($persistantCampaignItemConfigItemValue))
                {
                    $campaignItemsConfigurationForm->$persistantCampaignItemConfigItem = $persistantCampaignItemConfigItemValue;
                }
            }
            return $campaignItemsConfigurationForm;
        }

        /**
         * After a portlet action is completed, the portlet must be refreshed. This is the url to correctly
         * refresh the portlet content.
         */
        protected function getPortletDetailsUrl()
        {
            $redirectUrl = $this->params['redirectUrl'];
            $params = array_merge($_GET, array('portletId'       => $this->params['portletId'],
                                               'uniqueLayoutId'  => $this->uniqueLayoutId,
                                               'redirectUrl'    => $redirectUrl,
                                               'portletParams'   => array('relationModuleId' => $this->relationModuleId,
                                                                         'relationModelId' => $this->params['relationModel']->id)
                                               )
                                  );
            return Yii::app()->createUrl('/' . $this->relationModuleId . '/defaultPortlet/modalRefresh', $params);
        }
    }
?>