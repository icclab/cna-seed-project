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

    class Redactor extends ZurmoWidget
    {
        const LINK_FOR_INSERT_CLASS = 'image-gallery-modal-insert';

        public $scriptFile          = array('redactor.min.js');

        public $cssFile             = array('redactor.css');

        public $assetFolderName     = 'redactor';

        public $htmlOptions;

        public $content;

        // this is css property of redactor, not the widget
        public $css;

        public $buttons         = "['html', '|', 'formatting', 'bold', 'italic', 'deleted', '|',
                                   'unorderedlist', 'orderedlist', 'outdent', 'indent', '|', 'table', 'link', '|',
                                   'alignleft', 'aligncenter', 'alignright', '|', 'horizontalrule']";

        public $pastePlainText  = "true";

        public $visual          = "true";

        public $paragraphy      = "true";

        public $cleanup         = "true";

        public $iframe          = "true";

        public $minHeight       = 100;

        public $convertDivs     = "false";

        public $observeImages   = "false";

        public $wym             = "false";

        public $removeEmptyTags = "false";

        public $tidyHtml        = "true";

        public $xhtml           = "true";

        public $fullpage;

        public $toolbarExternal;

        public $plugins;

        public $deniedTags;

        public $allowedTags;

        public $imageUpload;

        public $imageGetJson;

        public $initCallback;

        public $changeCallback;

        public $focusCallback;

        public $syncAfterCallback;

        public $syncBeforeCallback;

        public $textareaKeydownCallback;

        public $imageUploadErrorCallback;

        public $urlForImageGallery;

        public function run()
        {
            $id                 = $this->htmlOptions['id'];
            $name               = $this->htmlOptions['name'];
            $linkForInsertClass = static::LINK_FOR_INSERT_CLASS;
            $urlForImageGallery = Yii::app()->createUrl('zurmo/imageModel/modalList/');
            unset($this->htmlOptions['name']);
            $javaScript = "
                    $(document).ready(
                        function()
                        {
                            $('#{$id}').redactor(
                            {
                                {$this->renderRedactorParamForInit('initCallback')}
                                {$this->renderRedactorParamForInit('changeCallback')}
                                {$this->renderRedactorParamForInit('focusCallback')}
                                {$this->renderRedactorParamForInit('syncAfterCallback')}
                                {$this->renderRedactorParamForInit('syncBeforeCallback')}
                                {$this->renderRedactorParamForInit('textareaKeydownCallback')}
                                {$this->renderRedactorParamForInit('imageUploadErrorCallback')}
                                {$this->renderRedactorParamForInit('plugins')}
                                {$this->renderRedactorParamForInit('toolbarExternal')}
                                {$this->renderRedactorParamForInit('fullpage')}
                                {$this->renderRedactorParamForInit('allowedTags')}
                                {$this->renderRedactorParamForInit('deniedTags')}
                                {$this->renderRedactorParamForInit('iframe')}
                                {$this->renderRedactorParamForInit('css')}
                                {$this->renderRedactorParamForInit('urlForImageGallery')}
                                buttons:            {$this->buttons},
                                cleanup:            {$this->cleanup},
                                convertDivs:        {$this->convertDivs},
                                imageGetJson:       '{$this->imageGetJson}',
                                imageUpload:        '{$this->imageUpload}',
                                minHeight:          {$this->minHeight},
                                observeImages:      {$this->observeImages},
                                paragraphy:         {$this->paragraphy},
                                pastePlainText:     {$this->pastePlainText},
                                removeEmptyTags:    {$this->removeEmptyTags},
                                visual:             {$this->visual},
                                tidyHtml:           {$this->tidyHtml},
                                wym:                {$this->wym},
                                xhtml:              {$this->xhtml},
                                linkForInsertClass: '{$linkForInsertClass}',
                                urlForImageGallery: '{$urlForImageGallery}',
                            });
                        }
                    );";
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->getId(), $javaScript);
            $content    = ZurmoHtml::textArea($name, $this->content, $this->htmlOptions);
            echo $content;
        }

        protected function renderRedactorParamForInit($paramName)
        {
            $paramValue = $this->$paramName;
            if (isset($paramValue))
            {
                $config = "{$paramName}: {$paramValue},"; // Not Coding Standard
                return $config;
            }
        }

        public function init()
        {
            $this->resolveSelectivePluginScriptLoad();
            parent::init();
            // TODO: @Shoaibi: Critical: Find a better way to deal with this.
            //$this->resolveSelectiveCssLoad();
        }

        protected function resolveSelectiveCssLoad()
        {
            $this->resolveSelectiveCssLoadForIframeSetting();
        }

        protected function resolveSelectiveCssLoadForIframeSetting()
        {
            if ($this->iframe == 'true')
            {
                $this->css  = "'" . $this->scriptUrl . "/css/redactor-iframe.css'";
            }
        }

        protected function resolveSelectivePluginScriptLoad()
        {
            $plugins        = CJSON::decode($this->plugins);
            if (!empty($plugins))
            {
                $this->registerPluginScriptFiles($plugins);
            }
        }

        protected function registerPluginScriptFiles(array $plugins)
        {
            $this->resolvePluginScriptNames($plugins);
            $this->scriptFile   = CMap::mergeArray($plugins, $this->scriptFile);
        }

        protected function resolvePluginScriptNames(array & $pluginNames)
        {
            array_walk($pluginNames, function(&$pluginName)
                                        {
                                            $pluginName .= '.js';
                                        });
        }
    }
?>