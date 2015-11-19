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
     * Class BaseEmailTemplateItemForListView
     * View of the BaseTemplate item for the @see SelectBaseTemplateForEmailTemplateWizardView
     */
    class BaseEmailTemplateItemForListView extends ItemForListView
    {
        const USE_LINK_CLASS_NAME   = 'use-template';

        const PREVIEW_LINK_CLASS_NAME   = 'preview-template';

        protected $useClass = self::USE_LINK_CLASS_NAME;

        protected $previewClass = self::PREVIEW_LINK_CLASS_NAME;

        protected $icon;

        public function __construct($data)
        {
            parent::__construct($data);
            if (isset($data['useClass']))
            {
                $this->useClass = $data['useClass'];
            }
            if (isset($data['previewClass']))
            {
                $this->previewClass = $data['previewClass'];
            }
            $this->setIcon();
        }

        public function renderItem()
        {
            $content  = null;
            $content .= $this->renderScreenOptions();
            $content .= $this->renderName();
            $content .= $this->renderUseLink();
            $content .= $this->renderPreviewLink();
            $htmlOptions = $this->getHtmlOptionsForItem();
            return ZurmoHtml::tag('li', $htmlOptions, $content);
        }

        protected function getHtmlOptionsForItem()
        {
            return array('class'        => 'base-template-selection',
                         'data-value'   => $this->model->id,
                         'data-name'    => $this->model->name,
                         'data-icon'    => $this->icon,
                         'data-subject' => $this->model->subject,
            );
        }

        protected function renderScreenOptions()
        {
            if ($this->model->modelClassName != '' && !$this->model->isFeatured)
            {
                $linkText       = ZurmoHtml::icon('icon-details') . '<span class="button-label"></span>' . ZurmoHtml::icon('icon-trigger');
                $link           = ZurmoHtml::link($linkText, '', array('class' => 'button-action-trigger'));
                $detailsContent = ItemDetailsViewUtil::renderAfterFormLayoutForDetailsContent($this->model);
                $description    = ZurmoHtml::tag('h4', array(), $this->model->name);
                $description   .= ZurmoHtml::tag('p', array(), $this->model->subject);
                $description   .= $detailsContent;
                $description    = ZurmoHtml::tag('div', array('class' => 'screen-options'), $description);
                $description    = ZurmoHtml::tag('li', array(), $description);
                $description    = ZurmoHtml::tag('ul', array('class' => 'button-actions'), $description);
                $content        = $link . $description;
                $content        = ZurmoHtml::tag('div', array('class' => 'default-button template-info'), $content);
                return $content;
            }
        }

        protected function renderName()
        {
            $icon = $this->resolveThumbnail();
            $name = ZurmoHtml::tag('h4', array('class' => 'name'), $this->model->name);
            return '<label>' . $icon . $name . '</label>';
        }

        protected function renderPreviewLink()
        {
            $htmlOptions    = $this->htmlOptionsForPreviewLink();
            $previewLabel   = Zurmo::t('ZurmoModule', 'Preview');
            $previewSpan    = ZurmoHtml::tag('span', array('class' => 'z-label'), $previewLabel);
            return ZurmoHtml::link($previewSpan, '#', $htmlOptions);
        }

        protected function htmlOptionsForPreviewLink()
        {
            return array(
                'class' => 'secondary-button ' . $this->previewClass,
            );
        }

        protected function renderUseLink()
        {
            $htmlOptions = $this->htmlOptionsForUseLink();
            $useLabel    = Zurmo::t('EmailTemplatesModule', 'Use');
            $useSpan     = ZurmoHtml::tag('span', array('class' => 'z-label'), $useLabel);
            return ZurmoHtml::link($useSpan, '#', $htmlOptions);
        }

        protected function htmlOptionsForUseLink()
        {
            return array(
                'class' => 'z-button ' . $this->useClass,
            );
        }

        protected function resolveThumbnail()
        {
            if ($this->model->isFeatured)
            {
                if ($thumbnail = Yii::app()->custom->resolveThumbnailForFeaturedEmailTemplate($this->model))
                {
                    return $thumbnail;
                }
            }
            return ZurmoHtml::icon($this->icon);
        }

        protected function setIcon()
        {
            $unserializedData   = CJSON::decode($this->model->serializedData);
            $icon               = ArrayUtil::getArrayValue($unserializedData, 'icon');
            if (!empty($icon))
            {
                $this->icon = $icon;
            }
            else
            {
                $this->icon = 'icon-user-template';
            }
        }
    }
?>