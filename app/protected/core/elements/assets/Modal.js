function transferModalValues(dialogId, data)
{
    $.each(data, function(sourceFieldId, value)
    {
      $('#'+ sourceFieldId).val(value);
    });
    $(dialogId).dialog("close");
}