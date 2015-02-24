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

    class ImageModalSearchAndListAndUploadView extends GridView
    {
        public function __construct(CController $controller, $moduleId, $actionId, $modalListLinkProvider,
                                    ModelForm $searchForm,  RedBeanModel $model, CDataProvider $dataProvider,
                                    $gridIdSuffix = null)
        {
            parent::__construct(3, 1);
            $searchAndListView = new ImageModalSearchAndListView(
                $controller->id,
                $moduleId,
                $actionId,
                $modalListLinkProvider,
                $searchForm,
                $model,
                $dataProvider,
                $gridIdSuffix
            );
            $this->setView($searchAndListView, 0, 0);

            $imageUploadView = new ImageFilesUploadView($searchAndListView->getListViewGridId());
            $this->setView($imageUploadView, 1, 0);

            $imageFilesImportFromUrlView = new ImageFilesImportFromUrlView($controller, new ImportImageFromUrlForm());
            $this->setView($imageFilesImportFromUrlView, 2, 0);
            $this->registerScripts($modalListLinkProvider);
        }

        protected function renderContent()
        {
            $content = $this->renderTabs();
            return $content . ZurmoHtml::tag('div', array('class' => 'image-tabbed-content'), parent::renderContent());
        }

        protected function renderTabs()
        {
            $content  = ZurmoHtml::link(
                            Zurmo::t('ZurmoModule', 'Library'),
                            '#',
                            array('class' => 'choose-tab active', 'data-view' => 'ImageModalSearchAndListView'));
            $content .= ZurmoHtml::link(
                            Zurmo::t('ZurmoModule', 'Upload'),
                            '#',
                            array('class' => 'upload-tab', 'data-view' => 'ImageFilesUploadView'));
            $content .= ZurmoHtml::link(
                            Zurmo::t('ZurmoModule', 'Import From Url'),
                            '#',
                            array('class' => 'upload-tab', 'data-view' => 'ImageFilesImportFromUrlView'));
            return ZurmoHtml::tag('div', array('class' => 'image-tabs clearfix'), $content);
        }

        protected function registerScripts($modalListLinkProvider)
        {
            $transferModalJavascriptFunction = "function transferModalImageValues(id, summary){ }"; // Not Coding Standard
            if ($modalListLinkProvider->getSourceIdFieldId() !== null)
            {
                // Begin Not Coding Standard
                $transferModalJavascriptFunction = "function transferModalImageValues(id, summary)
                                    {
                                        data = {
                                        {$modalListLinkProvider->getSourceIdFieldId()} : id,
                                        }
                                        transferModalValues('#{$modalListLinkProvider->getModalId()}', data);
                                        replaceImageSummary('{$modalListLinkProvider->getSourceNameFieldId()}', summary);
                                    };";
                // End Not Coding Standard
            }
            // Begin Not Coding Standard
            $javaScript = "
                $('div.image-tabs > a').click(function(){
                    $('div.image-tabbed-content > div').hide();
                    $('div.image-tabs > a').removeClass('active');
                    $(this).addClass('active');
                    $('#' + $(this).data('view')).show();
                });
                {$transferModalJavascriptFunction}
            ";
            // End Not Coding Standard
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->getId(), $javaScript);
        }
    }
?>