function getSelectedCalendars(selector)
{
    var selectedCal = [];
    $(selector).each(function()
    {
        if($(this).is(':checked'))
        {
            selectedCal.push($(this).val());
        }
    });
    var selectedCalString = '';
    if(selectedCal.length > 0)
    {
        selectedCalString = selectedCal.join(',');
    }
    return selectedCalString;
}

/**
 * Adds the calendar row to the shared calendar list view
 */
function addCalendarRowToSharedCalendarListView(calendarId, url, sharedListContainerId, errorInProcess)
{
    url = url + "?id=" + calendarId;
    $.ajax(
        {
            type: 'GET',
            url: url,
            beforeSend: function(xhr)
                       {
                           $('#modalContainer').html('');
                           $(this).makeLargeLoadingSpinner(true, '#modalContainer');
                       },
            success: function(dataOrHtml, textStatus, xmlReq)
                     {
                         $(this).processAjaxSuccessUpdateHtmlOrShowDataOnFailure(dataOrHtml, sharedListContainerId);
                     },
            complete:function(XMLHttpRequest, textStatus)
                     {
                       $('#modalContainer').dialog('close');
                     },
            error:function(xhr, textStatus, errorThrown)
                  {
                      alert(errorInProcess);
                  }
        }
    );
}

function getModuleDateTimeAttributes(moduleName, url, targetId, attributeName)
{
    $.ajax({
            url: url,
            dataType: 'html',
            data:{moduleName : moduleName, attribute : attributeName},
            success: function(data) {
                $('#' + targetId).html(data);
            }
        });
}

function getCalendarEvents(url, inputId)
{
    $('#' + inputId).fullCalendar('removeEvents');
    var events = function(start, end, callback) {
        var view                    = $('#' + inputId).fullCalendar('getView');
        var selectedMyCalendars     = getSelectedCalendars('.mycalendar');
        var selectedSharedCalendars = getSelectedCalendars('.sharedcalendar');
        $.ajax({
            url: url,
            dataType: 'json',
            beforeSend: function(xhr)
                       {
                           $('.mycalendar,.sharedcalendar').attr("disabled", true);
                           $('#calItemCountResult').hide();
                       },
            data:
            {
                selectedMyCalendarIds : selectedMyCalendars,
                selectedSharedCalendarIds : selectedSharedCalendars,
                startDate      : $.fullCalendar.formatDate(view.start, 'yyyy-MM-dd'),
                endDate        : $.fullCalendar.formatDate(view.end, 'yyyy-MM-dd'),
                dateRangeType  : view.name
            },
            success: function(data) {
                var events = [];
                if(data.isMaxCountReached)
                {
                    $('#calItemCountResult').show();
                }
                $(data.items).each(function() {
                    var endDateTime = '';
                    var allDay = false;
                    var className = '';
                    if ($(this).attr('end') !== undefined)
                    {
                        endDateTime = $(this).attr('end');
                    }
                    if ($(this).attr('allDay') !== undefined)
                    {
                        allDay = true;
                    }
                    if ($(this).attr('className') !== undefined)
                    {
                        className = $(this).attr('className');
                    }
                    events.push({
                        title: $(this).attr('title'),
                        start: $(this).attr('start'), // will be parsed
                        end  : endDateTime,
                        color : $(this).attr('color'),
                        description : $(this).attr('description'),
                        className : className,
                        allDay: allDay
                    });
                });
                callback(events);
                $('.mycalendar,.sharedcalendar').removeAttr('disabled');
            }
        });
    }
    return events;
}