/**
 * Transfer user modal value on selecting user in the form
 */
function transferUserModalValues(dialogId, data, url, attribute, errorInProcess)
{
    var userId;
    $.each(data, function(sourceFieldId, value)
    {
      if(sourceFieldId == 'Task_' + attribute + '_id')
      {
        userId = value;
      }
    });
    url = url + "&attribute=" + attribute + "&userId=" + userId;
    $.ajax(
        {
            type: 'GET',
            url: url,
            beforeSend: function()
                       {
                           $(dialogId).html('');
                           $(this).makeLargeLoadingSpinner(true, dialogId);
                       },
            success: function(data)
                     {
                         $("#permissionContent").html(data);
                         $(dialogId).dialog().dialog("close");
                     },
            error:function()
                  {
                      alert(errorInProcess);
                  }
        }
    );
    $.each(data, function(sourceFieldId, value)
    {
      $('#'+ sourceFieldId).val(value).trigger('change');
    });
    $(dialogId).dialog("close");
}

function updateCheckListItem(element, url, errorMessage)
{
    var passedValue = $(element).val();
    if(passedValue == '')
    {
        alert(errorMessage);
    }
    id = $(element).attr('id');
    id = $(this).attr('id');
    idParts = id.split('_');
    litag = $(element).parent().parent();
    $.ajax({
            type: 'GET',
            url: url,
            dataType: 'html',
            cache: false,
            data: {
                id  :idParts[2],
                name:passedValue
            },
            success: function(data){
              $(litag).find('p').html(data);
              $(litag).find('.editable').show();
              $(litag).find('.task-check-item-actions').show();
              $(litag).find('.editable-task-input').hide();
            }
        });
}

function deleteCheckListItem(element, url)
{
    litag = $(element).parent().parent();
    id    = $(litag).find('input').attr('id');
    console.log(id);
    idParts = id.split('_');
    $.ajax({
            type: 'GET',
            url: url,
            dataType: 'html',
            cache: false,
            data: {
                id  :idParts[1]
            },
            success: function(data){
              $("#TaskCheckListItemsForTaskView").replaceWith(data);
            }
        });
}