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
     * Specifically for when showing email templates for marketing
     */
    class MergeTagsView extends View
    {
        /**
         * Used to identify this mergeTagsView as unique
         * @var string
         */
        protected $uniqueId;

        /**
         * If used, populate with the html content id such as EmailTemplate_htmlContent
         * @var null|string
         */
        protected $textContentId;

        /**
         * If used, populate with the html content id such as EmailTemplate_textContent
         * @var null|string
         */
        protected $htmlContentId;

        /**
         * Should this view be hidden by default
         * @var bool
         */
        protected $hideByDefault = true;

        /**
         * The selector for the input where is stored the related moduleClassName
         * @var string
         */
        public $modelClassNameSelector;

        /**
         * @return string
         */
        public function getTreeDivId()
        {
            return 'MergeTagsTreeArea' . $this->uniqueId;
        }

        /**
         * @return string
         */
        public static function getControllerId()
        {
            return 'emailTemplates';
        }

        public function __construct($uniqueId = null, $textContentId = null, $htmlContentId = null, $hideByDefault = true)
        {
            assert('is_string($uniqueId) || $uniqueId === null');
            assert('is_string($textContentId) || $textContentId === null');
            assert('is_string($htmlContentId) || $htmlContentId === null');
            assert('is_bool($hideByDefault)');
            $this->uniqueId                 = $uniqueId;
            $this->textContentId            = $textContentId;
            $this->htmlContentId            = $htmlContentId;
            $this->hideByDefault            = $hideByDefault;
        }

        public function isUniqueToAPage()
        {
            return false;
        }

        /**
         * @return string
         */
        public function renderTreeViewAjaxScriptContent()
        {
            $url    =  Yii::app()->createUrl(static::getControllerId() .
                                             '/default/relationsAndAttributesTreeForMergeTags',
                                             array_merge(GetUtil::getData(), array('uniqueId' => $this->uniqueId)));
            $this->resolveUrl($url);
            // Begin Not Coding Standard
            $script = "
                $('#" . $this->getTreeDivId() . "').addClass('loading');
                $(this).makeLargeLoadingSpinner('" . $this->getTreeDivId() . "');
                $.ajax({
                    url : $url,
                    type : 'GET',
                    success : function(data)
                    {
                        $('#" . $this->getTreeDivId() . "').html(data);
                    },
                    error : function()
                    {
                        //todo: error call
                    }
                });
            ";
            // End Not Coding Standard
            return $script;
        }

        protected function resolveUrl(& $url)
        {
            if ($this->modelClassNameSelector !== null)
            {
                $url = "'" . $url . "&modelClassName='+$('{$this->modelClassNameSelector}').val()";
            }
            else
            {
                $url = "'" . $url . "'";
            }
        }

        protected function renderContent()
        {
            $this->renderTreeViewAjaxScriptContent();
            $spinner  = ZurmoHtml::tag('span', array('class' => 'big-spinner'), '');
            $content  = ZurmoHtml::tag('div', array('id' => static::getTreeDivId(), 'class' => 'hasTree loading'), $spinner);
            $this->registerScriptContent();
            return $content;
        }

        protected function registerScriptContent()
        {
            $cs = Yii::app()->getClientScript();
            $cs->registerCoreScript('treeview');
            $cs->registerCoreScript('bbq');
            $cs->registerCssFile(Yii::app()->getClientScript()->getCoreScriptUrl() . '/treeview/jquery.treeview.css');
            Yii::app()->clientScript->registerScript('mergeTagsScript' . $this->uniqueId,
                                                     $this->renderTreeViewAjaxScriptContent());
            // Begin Not Coding Standard
            $script = '
                $(document).ready(function(){
                    $(".item-to-place").off("mousemove");
                    $(".item-to-place").live("mousemove",function(){
                        $(this).draggable({
                            helper: function(event){
                                var label = $(event.target).html();
                                var width = $(event.target).width() + 50;
                                var clone = $(\'<div class="dynamic-row clone">\' + label + \'</div>\');
                                clone.animate({ width : width}, 250);
                                $("body").append(clone);
                                return clone;
                            },
                            iframeFix: true,
                            revert: "invalid",
                            snapMode: "inner",
                            cursor: "pointer",
                            start: function(event,ui){
                                $(ui.helper).attr("id", $(this).attr("id"));
                            },
                            stop: function(event, ui){
                                document.body.style.cursor = "auto";
                            }
                        });
                    });
                ';
            // End Not Coding Standard
            if ($this->textContentId != null)
            {
                // Begin Not Coding Standard
                $script.='if ($("#' . $this->textContentId . '").data("droppable"))
                                {
                                    $("#' . $this->textContentId . '").droppable("destroy");
                                }';
                $script.= '$("#' . $this->textContentId . '").droppable({
                                iframeFix: true,
                                    hoverClass: "textarea",
                                    accept: ":not(.ui-sortable-helper)",
                                    drop: function(event, ui) {
                                        var $this = $(this);
                                        var tempid = ui.draggable.text();
                                        var dropText = ui.draggable.data("value");
                                        var droparea = document.getElementById("' . $this->textContentId . '");
                                        var range1   = droparea.selectionStart;
                                        var range2   = droparea.selectionEnd;
                                        var val      = droparea.value;
                                        var str1     = val.substring(0, range1);
                                        var str3     = val.substring(range1, val.length);
                                        droparea.value = str1 + dropText + str3;
                                    }
                                });';
                // End Not Coding Standard
            }
            if ($this->htmlContentId != null)
            {
                // Begin Not Coding Standard
                $script.='if ($("#' . $this->htmlContentId . '").parent().data("droppable"))
                                {
                                    $("#' . $this->htmlContentId . '").parent().droppable("destroy");
                                }';
                $script .= '$("#' . $this->htmlContentId . '").parent().droppable({
                                iframeFix: true,
                                drop: function(event, ui) {
                                    var $this    = $(this);
                                    var dropText = ui.draggable.data("value");
                                    var node = document.createTextNode(dropText);
                                    $("#' . $this->htmlContentId . '").redactor("insertNode", node);
                                    $("#' . $this->htmlContentId . '").redactor("sync");
                                }
                            });';
                // End Not Coding Standard
            }
            $script .= '});';
            Yii::app()->clientScript->registerScript('mergeTagsDragDropScript' . $this->uniqueId, $script);
        }

        protected function getViewStyle()
        {
            if ($this->hideByDefault)
            {
                return 'style=display:none;'; // Not Coding Standard
            }
        }
    }
?>