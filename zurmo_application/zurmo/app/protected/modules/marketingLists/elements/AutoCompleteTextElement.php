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

    // TODO: @Shoaibi/@Jason: Low: This should be refactored and used everywhere instead of manually creating clip.
    abstract class AutoCompleteTextElement extends TextElement
    {
        protected $shouldRenderSelectLink = false;

        abstract protected function getWidgetValue();

        abstract protected function getSource();

        abstract protected function getOptions();

        /**
         * (non-PHPdoc)
         * @see TextElement::renderControlEditable()
         */
        protected function renderControlEditable()
        {
            $cClipWidget             = new CClipWidget();
            $clipId                  = $this->getWidgetClipName();
            $cClipWidget->beginClip($clipId);
            $cClipWidget->widget('zii.widgets.jui.CJuiAutoComplete', array(
                'name'        => $this->getWidgetName(),
                'id'          => $this->getWidgetId(),
                'value'       => $this->getWidgetValue(),
                'source'      => $this->getSource(),
                'options'     => $this->getOptions(),
                'htmlOptions' => $this->getHtmlOptions(),

            ));
            $cClipWidget->endClip();
            $content  = $cClipWidget->getController()->clips[$clipId];
            $content .= $this->renderSelectLink();
            return $content;
        }

        protected function getWidgetId()
        {
            return $this->getEditableInputId();
        }

        protected function getWidgetClipName()
        {
            return get_class($this);
        }

        protected function getWidgetName()
        {
            return $this->getEditableInputName();
        }

        protected function renderSelectLink()
        {
            if (!$this->shouldRenderSelectLink)
            {
                return null;
            }
            $cs = Yii::app()->getClientScript();
            $cs->registerCoreScript('bbq');
            $cs->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.elements.assets')
                ) . '/Modal.js',
                CClientScript::POS_END
            );
            $this->registerSelectLinkScripts();
            $content  = ZurmoHtml::openTag('div', array('class' => 'has-model-select'));
            $content .= ZurmoHtml::hiddenField($this->getIdForHiddenSelectLinkField());
            $content .= ZurmoHtml::ajaxLink('<span class="model-select-icon"></span>',
                Yii::app()->createUrl($this->getSourceUrlForSelectLink(), $this->getSelectLinkUrlParams()),
                $this->resolveAjaxOptionsForSelectingModel(),
                array('id' => $this->getWidgetId() . '-select-link')
            );
            $content .= ZurmoHtml::closeTag('div');
            return $content;
        }

        protected function getSourceUrlForSelectLink()
        {
            throw new NotImplementedException();
        }

        protected function getSelectLinkUrlParams()
        {
            return array(
                'modalTransferInformation' => $this->getModalTransferInformation(),
            );
        }

        protected function getModalTransferInformation()
        {
            return array(
                'sourceIdFieldId'   => $this->getIdForHiddenSelectLinkField(),
                'sourceNameFieldId' => $this->getWidgetId(),
                'modalId'           => $this->getModalContainerId(),
            );
        }

        protected function getIdForHiddenSelectLinkField()
        {
            return $this->getWidgetId() . '-transfer';
        }

        protected function resolveAjaxOptionsForSelectingModel()
        {
            $title = $this->getModalTitleForSelectingModel();
            return   ModalView::getAjaxOptionsForModalLink($title, $this->getModalContainerId());
        }

        protected function getModalContainerId()
        {
            return 'modalContainer';
        }

        protected function getModalTitleForSelectingModel()
        {
            throw new NotImplementedException();
        }

        protected function registerSelectLinkScripts()
        {
            $scriptName = $this->getWidgetId() . '-transfer-script';
            if (Yii::app()->clientScript->isScriptRegistered($scriptName))
            {
                return;
            }
            else
            {
                $selectLinkId = $this->getWidgetId() . '-select-link';
                // Begin Not Coding Standard
                Yii::app()->clientScript->registerScript($scriptName, "
                    $('#{$selectLinkId}').off();
                    $('#{$this->getIdForHiddenSelectLinkField()}').change(function(event){
                        {$this->getAfterChangeSelectIdScript()}
                    });
                ");
                // End Not Coding Standard
            }
        }

        protected function getAfterChangeSelectIdScript()
        {
            throw new NotImplementedException();
        }
    }
?>
