function processAjaxSuccessError(id, data)
{
    if(data == 'failure')
    {
        alert('An error has occurred. You have attempted to access a page you do not have access to.');
        return false;
    }
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
    //alert( $('#' + gridViewId + "-selectedIds").val() );
}

function addListViewSelectedIdsToUrl(id, options)
{
    options.url = $.param.querystring(options.url, 'selectedIds=' + $('#' + id + "-selectedIds").val());
}