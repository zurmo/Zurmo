function rebuildSelectInputFromInputs(id, inputCollectionName)
{
    var selected      = $('#' + id).val();
    var selectedLabel = $.trim($('#' + id + ' option:selected').text());
    $('#' + id + ' option').each(function(){
        if($(this).val()!='')
        {
            $(this).remove();
        }
    });
    var order = 1;
    var selectedOrder = 0;
    $('input[name="' + inputCollectionName + '"]').each(function(){
        tempArray = $(this).attr('id').split('_');
        if($(this).val() == selectedLabel)
        {
            selectedOrder = order;
        }
        $('#' + id).append("<option value='" + order + "'>" + $(this).val() + "</option>");
        order ++;
    });
    if(selectedOrder > 0)
    {
        $('#' + id).val(selectedOrder);
    }
    else
    {
        $('#' + id).val(selected);
    }
}