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

function addListViewSelectedIdsAndSelectAllToUrl(id, options)
{
    options.url = $.param.querystring(options.url, 'selectedIds=' + $('#' + id + "-selectedIds").val());
    options.url = $.param.querystring(options.url, 'selectAll=' + $('#' + id + "-selectAll").val());
}

function selectAllResults(gridViewId, rowSelectorName)
{
    $('#' + gridViewId + "-selectedIds").val(null);
    $('#' + gridViewId + "-selectAll").val(1);
    jQuery("input[name='" + rowSelectorName + "_all']").attr('checked',true);
    jQuery("input[name='" + rowSelectorName + "_all']").attr('disabled',true);
    jQuery("input[name='" + rowSelectorName + "[]']").each(function() {
        this.checked  = true;
        this.disabled = true;
    });
}

function selectNoneResults(gridViewId, rowSelectorName)
{
    $('#' + gridViewId + "-selectedIds").val(null);
    $('#' + gridViewId + "-selectAll").val(null);
    jQuery("input[name='" + rowSelectorName + "_all']").attr('checked',false);
    jQuery("input[name='" + rowSelectorName + "_all']").attr('disabled',false);
    jQuery("input[name='" + rowSelectorName + "[]']").each(function() {
        this.checked  = false;
        this.disabled = false;
    });
}