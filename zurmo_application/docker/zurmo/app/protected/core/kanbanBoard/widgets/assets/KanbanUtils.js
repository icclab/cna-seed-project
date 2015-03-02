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

function setupKanbanDragDrop(){
    $(window).ready(function(){
        $(".item-to-place").live("mousemove",function(){
            $(this).draggable({
                helper: function(event, ui){
                    var label = $(event.currentTarget).html();
                    var width = $(this).width();
                    var clone = $('<div class="kanban-card clone">' + label + '</div>');
                    clone.width(width);
                    //clone.animate({ width : width}, 250);
                    $('body').append(clone);
                    return clone;
                },
                revert: "invalid",
                snap: ".droppable-dynamic-row-container",
                snapMode: "inner",
                cursor: "pointer",
                start: function(event,ui){
                    $(ui.helper).attr("id", $(this).data("id"));
                },
                stop: function(event, ui){
                    document.body.style.cursor = "auto";
                }
            });
        });

        $( ".droppable-dynamic-rows-container").droppable({
            accept: ".item-to-place",
            hoverClass: "ui-state-active",
            cursor: "pointer",
            drop: function( event, ui ) {
                //todo: hide drop overlay
                $('.dynamic-droppable-area').removeClass('activate-drop-zone');
            },
            activate: function(event,ui){
                dropped = false;
                $('.dynamic-droppable-area').addClass('activate-drop-zone');
            },
            deactivate: function(event,ui){
                $('.dynamic-droppable-area').removeClass('activate-drop-zone');
            }
        });
    });
}

function setUpTaskKanbanSortable(inputurl){
    $('.connectedSortable').sortable({
        forcePlaceholderSize: true,
        forceHelperSize: true,
        items: 'li:not(.ui-state-disabled)',
        connectWith: '.connectedSortable',
        cursor: 'move',
        placeholder: 'activate-drop-zone',//'kanban-card item-to-place',
        helper: function(event, ui){
            var width = ui.width();
            var clone = $('<div class="kanban-card clone">' + ui.clone().html() + '</div>');
            clone.width(width);
            $('body').append(clone);
            return clone;
        },
        over: function( event, ui ) {
            $('.droppable-dynamic-rows-container').removeClass('activate-drop-zone');
            $(this).parent().addClass('activate-drop-zone');
        },
        start: function( event, ui ) {
            $(ui.helper).attr("id", $(ui.item).data("id"));
        },
        update : function (event, ui) {
            serial = $(this).sortable('serialize', {key: 'items[]', attribute: 'id'});
            var ulid = $(this).attr('id');
            var ulidParts = ulid.split('-');
            var type = parseInt(ulidParts[3]);
            var url = inputurl + '?' + serial + '&type=' + type;
            $.ajax({
                url: url,
                type: 'get',
                dataType : 'json',
                beforeSend : function(){
                    $(".ui-overlay-block").fadeIn(50);
                    $(this).makeLargeLoadingSpinner(true, ".ui-overlay-block"); //- add spinner to block anything else
                },
                success: function(data){
                    if(data.hasOwnProperty('button'))
                    {
                        if(data.button != '')
                        {
                            $(ui.item).find('.task-action-toolbar').html(data.button);
                        }
                        else
                        {
                            $(ui.item).addClass('ui-state-disabled');
                            $(ui.item).find('.task-action-toolbar').remove();
                        }
                        $(ui.item).find('.task-status').html(data.status);
                    }

                    if(data.owner !== undefined)
                    {
                        $(ui.item).find('h4 .task-owner').html('(' + data.owner + ')');
                    }
                    if(data.subscriptionContent !== undefined)
                    {
                        $(ui.item).find('.task-subscribers').html(data.subscriptionContent);
                    }
                },
                complete:function(data)
                {
                    $(this).makeLargeLoadingSpinner(false, ".ui-overlay-block");
                    $(".ui-overlay-block").fadeOut(50);
                },
                error: function(request, status, error){
                    alert('We are unable to set the sort order at this time.  Please try again in a few minutes.');
                }
            });
        },
        stop: function(event, ui){
            document.body.style.cursor = 'auto';
            $('.droppable-dynamic-rows-container').removeClass('activate-drop-zone');
        }
        //helper: 'clone'
  }).disableSelection();
}