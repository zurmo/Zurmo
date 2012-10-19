function processAjaxSuccessError(id, data)
{
    if(data == 'failure')
    {
        alert('An error has occurred. You have attempted to access a page you do not have access to.');
        return false;
    }
}

function processListViewSummaryClone(listViewId, summaryCssClass)
{
    replacementContent = $('#' + listViewId).find('.' + summaryCssClass).html();
    $('#' + listViewId).parent().parent('.GridView')
    .find('form').first().find('.list-view-items-summary-clone')
    .html(replacementContent);
}

function updateListViewSelectedIds(gridViewId, selectedId, selectedValue)
{
    var array = new Array ();
    var processed = false;
    jQuery.each($('#' + gridViewId + "-selectedIds").val().split(','), function(i, value)
        {
            if(selectedId == value)
            {
                if(selectedValue)
                {
                    array.push(value);
                }
                processed = true;
            }
            else
            {
                if(value != '')
                {
                    array.push(value);
                }
            }
         }
     );
    if(!processed && selectedValue)
    {
        array.push(selectedId);
    }
    $('#' + gridViewId + "-selectedIds").val(array.toString());
}

function addListViewSelectedIdsToUrl(id, options)
{
    options.url = $.param.querystring(options.url, 'selectedIds=' + $('#' + id + "-selectedIds").val());
}

function resetSelectedListAttributes(selectedListAttributesId, hiddenListAttributesId, defaultSelectedAttributes)
{
    $('#' + selectedListAttributesId + ' option').remove().appendTo('#' + hiddenListAttributesId);
    defaults = eval(defaultSelectedAttributes);
    for (i = 0; i < defaults.length; ++i)
    {
        $('#' + hiddenListAttributesId).find('option[value="' + defaults[i] + '"]').remove().appendTo('#' + selectedListAttributesId);
    };
    $('#' + hiddenListAttributesId).find("option").each(function(){
        if(jQuery.inArray($(this).val(), defaults) != -1)
        {
            $(this).remove().appendTo('#' + selectedListAttributesId);
        }
    });
    return false;
}