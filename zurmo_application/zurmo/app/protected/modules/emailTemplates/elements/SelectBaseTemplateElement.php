<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class SelectBaseTemplateElement extends Element
    {
        const FILTER_BY_PREDEFINED_TEMPLATES = 1;

        const FILTER_BY_PREVIOUSLY_CREATED_TEMPLATES = 2;

        const CLOSE_LINK_CLASS_NAME = 'closeme';

        const MODEL_CLASS_NAME_ATTRIBUTE = 'modelClassName';

        const ITEMS_LIST_CLASS_NAME = 'template-list';

        const ITEMS_TAG_NAME = 'ul';

        protected function renderControlEditable()
        {
            $dataProvider = $this->getDataProviderByGet();
            $cClipWidget  = new CClipWidget();
            $cClipWidget->beginClip("ListView");
            $cClipWidget->widget('application.core.widgets.ZurmoListView', array(
                'id'                => $this->getListViewId(),
                'dataProvider'      => $dataProvider,
                'itemView'          => 'BaseEmailTemplateItemForListView',
                'itemsTagName'      => static::ITEMS_TAG_NAME,
                'itemsCssClass'     => static::ITEMS_LIST_CLASS_NAME . ' clearfix',
                'pager'             => $this->getCGridViewPagerParams(),
                'htmlOptions'       => array('class' => 'templates-chooser-list clearfix'),
                'beforeAjaxUpdate'  => $this->getCGridViewBeforeAjaxUpdate(),
                'afterAjaxUpdate'   => $this->getCGridViewAfterAjaxUpdate(),
                'template'          => static::getGridTemplate(),
            ));
            $cClipWidget->endClip();
            $content  = $this->renderActionBar();
            $content .= $cClipWidget->getController()->clips['ListView'];
            $content .= $this->renderHiddenInput();
            $this->registerScripts($cClipWidget->id);
            return $content;
        }

        protected static function getGridTemplate()
        {
            $preloader = '<div class="list-preloader"><span class="z-spinner"></span></div>';
            return "{summary}{sorter}{items}" . $preloader . "{pager}";
        }

        protected function getCGridViewBeforeAjaxUpdate()
        {
            return "js:function(id, options) {
                        cacheListItems = $('" . static::getItemsListJQuerySelector() . "').html()
            }";
        }

        public static function getItemsListJQuerySelector()
        {
            return static::ITEMS_TAG_NAME . '.' . static::ITEMS_LIST_CLASS_NAME;
        }

        protected function getCGridViewAfterAjaxUpdate()
        {
            // Begin Not Coding Standard
            return "js:function(id, data) {
                        var html = $('" . static::getItemsListJQuerySelector() . "').html();
                        $('" . static::getItemsListJQuerySelector() . "').html(cacheListItems + html);
            }";
            // End Not Coding Standard
        }

        protected function getDataProviderByGet()
        {
            $modelClassName = $this->resolveModelClassName();
            $filterBy = ArrayUtil::getArrayValue(GetUtil::getData(), 'filterBy');
            $sortAttribute  = null;
            $sortDescending = false;
            if ($filterBy == static::FILTER_BY_PREVIOUSLY_CREATED_TEMPLATES)
            {
                $searchAttributeData = EmailTemplate::getPreviouslyCreatedBuilderTemplateSearchAttributeData($modelClassName, false);
                $sortAttribute       = 'isFeatured';
                $sortDescending      = true;
            }
            else
            {
                $searchAttributeData = EmailTemplate::getPredefinedBuilderTemplatesSearchAttributeData();
                $sortAttribute       = 'id';
            }
            $dataProvider   = RedBeanModelDataProviderUtil::makeDataProvider($searchAttributeData, 'EmailTemplate', 'RedBeanModelDataProvider', $sortAttribute, $sortDescending, 10);
            return $dataProvider;
        }

        protected function renderActionBar()
        {
            $modelClassName = $this->resolveModelClassName();
            $content =  '
                            <div class="pills">
                                    <a href="#" class="filter-link active" data-filter="' . static::FILTER_BY_PREDEFINED_TEMPLATES . '">' . 
                                        Zurmo::t('DesignerModule', 'Layouts') . '</a>
                                    <a href="#" id="saved-templates-link" class="filter-link" data-filter="' . static::FILTER_BY_PREVIOUSLY_CREATED_TEMPLATES . '">' . 
                                        Zurmo::t('EmailTemplatesModule', 'Saved Templates') . '</a>
                            </div>
                        ';
            $content .= $this->renderCloseSelectTemplatesButton();
            $content  = ZurmoHtml::tag('div', array('class' => 'mini-pillbox'), $content);
            return $content;
        }

        protected function renderCloseSelectTemplatesButton()
        {
            $linkText  = ZurmoHtml::icon('icon-x');
            $linkText .= Zurmo::t('Core', 'cancel');
            return ZurmoHtml::link($linkText, '#', array('class' => 'simple-link ' . static::CLOSE_LINK_CLASS_NAME));
        }

        protected function getCGridViewPagerParams()
        {
            $pagerParams = array(
                'class'            => 'BottomLinkPager',
                'nextPageLabel'    => '<span>' . Zurmo::t('Core', 'next') . '</span>',
                'header'           => '<div class="list-preloader"><span class="z-spinner"></span></div>',
                'htmlOptions'      => array('class' => 'endless-list-pager')
            );
            return $pagerParams;
        }

        protected function renderHiddenInput()
        {
            $attribute = $this->attribute;
            return ZurmoHtml::hiddenField($this->getEditableInputName(),
                $this->model->$attribute,
                array('id' => $this->getEditableInputId()));
        }

        protected function renderControlNonEditable()
        {
            throw new NotSupportedException();
        }

        protected function registerScripts($listViewId)
        {
            $script  = $this->renderOnClickUseLinkScript();
            $script .= $this->renderOnClickPreviewLinkScript();
            $script .= $this->renderOnClickFilterLinksScript($listViewId);
            Yii::app()->getClientScript()->registerScript(__CLASS__, $script);
        }

        protected function renderOnClickUseLinkScript()
        {
            $nextPageLinkId = SelectBaseTemplateForEmailTemplateWizardView::getNextPageLinkId();
            $script = "
                $('body').off('click', '.use-template');
                $('body').on('click', '.use-template', function (event) {
                    var currentSelectedValue = $(this).closest('li').data('value');
                    originalBaseTemplateId  = $('" . SelectBaseTemplateForEmailTemplateWizardView::resolveOriginalBaseTemplateIdHiddenInputJQuerySelector() . "').val();
                    // show warning only on edit when a user has already been to canvas once.
                    if (originalBaseTemplateId != '' && currentSelectedValue != originalBaseTemplateId)
                    {
                        if (!confirm('" . Zurmo::t('EmailTemplatesModule', 'Changing base template would trash any existing design made on canvas.') ."'))
                        {
                            return false;
                        }
                    }
                    $('#{$this->getEditableInputId()}').val(currentSelectedValue);
                    updateBaseTemplateIdHiddenInputValue(currentSelectedValue);
                    updateSelectedLayout($(this).closest('li'));
                    $('#BuilderEmailTemplateWizardView .float-bar').show();
                    $('#" . SelectBaseTemplateForEmailTemplateWizardView::CHOSEN_DIV_ID . "').show();
                    $('#" . SelectBaseTemplateForEmailTemplateWizardView::TEMPLATES_DIV_ID . "').hide();
                    event.preventDefault();
                    return true;
                });
            ";
            return $script;
        }

        protected function renderOnClickPreviewLinkScript()
        {
            $url                          = Yii::app()->createUrl('emailTemplates/default/renderPreview', array('id' => null));
            $ajaxOptions['cache']         = 'false';
            $ajaxOptions['url']           = "js:(function(){
                                                return '{$url}' + templateId;
                                             })()";
            $ajaxOptions['success']       = "js:function (html){
                                                $('#" . BuilderCanvasWizardView::PREVIEW_IFRAME_ID . "').contents().find('html').html(html);
                                                $('#" . BuilderCanvasWizardView::PREVIEW_IFRAME_CONTAINER_ID . "').show();
                                                $('body').addClass('previewing-builder');
                                             }";
            $ajax                         = ZurmoHtml::ajax($ajaxOptions);

            $script = "
                $('body').off('click', '.preview-template');
                $('body').on('click', '.preview-template', function (event) {
                    var templateId = $(this).closest('li').data('value');
                    {$ajax}
                    event.preventDefault();
                    return true;
                });
            ";

            return $script;
        }

        protected function renderOnClickFilterLinksScript()
        {
            // Begin Not Coding Standard
            $script = "
                $('body').off('click', '.filter-link');
                $('body').on('click', '.filter-link', function (event) {
                    $('.filter-link.active').removeClass('active');
                    $(this).addClass('active');
                    $('#" . $this->getListViewId() . "').addClass('attachLoadingTarget');
                    $('#" . $this->getListViewId() . "').addClass('loading');
                    $('#" . $this->getListViewId() . " > .pager').hide();
                    $(this).makeSmallLoadingSpinner(true, '#" . $this->getListViewId() . "');
                    $('ul.template-list').html('');
                    $.fn.yiiListView.update('{$this->getListViewId()}', {
                         url: location.href.replace(/&?.*filterBy=([^&]$|[^&]*)/i, ''),
                         data: {filterBy: $(this).data('filter'), modelClassName: $('#{$this->getModelClassNameId()}').val()}
                    });
                    event.preventDefault();
                    return true;
                });
            ";
            // End Not Coding Standard
            return $script;
        }

        protected function getModelClassNameId()
        {
            return $this->getEditableInputId(static::MODEL_CLASS_NAME_ATTRIBUTE, 'value');
        }

        protected function getListViewId()
        {
            return $this->getEditableInputId() . '_list_view';
        }

        protected function resolveModelClassName()
        {
            $modelClassName = ArrayUtil::getArrayValue(GetUtil::getData(), 'modelClassName');
            if (isset($modelClassName))
            {
                return $modelClassName;
            }

            if (isset($this->model->modelClassName))
            {
                return $this->model->modelClassName;
            }
            return 'Contact';
        }
    }
?>