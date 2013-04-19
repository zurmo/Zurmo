function rebuildSelectInputFromInputs(id, inputCollectionName)
{
    var selected      = $('#' + id).val();
    var selectedLabel = $.trim($('#' + id + ' option:selected').text());
    $('#' + id + ' option').each(function(){
        if ($(this).val()!='')
        {
            $(this).remove();
        }
    });
    var order = 0;
    var selectedOrder = 0;
    $('input[name="' + inputCollectionName + '"]').each(function(){
        tempArray = $(this).attr('id').split('_');
        if ($(this).val() == selectedLabel)
        {
            selectedOrder = order;
        }
        $('#' + id).append("<option value='" + order + "'>" + $(this).val() + "</option>");
        order ++;
    });
    if (selectedOrder > 0)
    {
        $('#' + id).val(selectedOrder);
    }
    else
    {
        $('#' + id).val(selected);
    }
}

/**
 * Rebuild the select input from an array of data and labels.  Respect the existing selected value if it is still
 * available
 * @param id
 * @param inputCollectionName
 */
function rebuildSelectInputFromDataAndLabels(id, dataAndLabels)
{
    var selected      = $('#' + id).val();
    $('#' + id).find('option').remove();
    $.each(dataAndLabels, function(value, label){
        $('#' + id).append("<option value='" + value + "'>" + label + "</option>");
    });
    $('#' + id).val(selected);
}