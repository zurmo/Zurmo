$("[id^='ContactWebForm_serializedData_']").live('change', function()
{
    if ($(this).is(':checked'))
    {
        var attributeId      = $(this).val();
        var elementId        = $(this).attr('id');
        var attributeLabel   = $('label[for=' + elementId + ']').html();
        $(this).closest('div').remove();
        var attributeElement = '<li><div class="dynamic-row"><div>';
        attributeElement    += '<label for=\'' + elementId + '\'>' + attributeLabel + '</label>';
        attributeElement    += '<input type=\'hidden\' name=\'attributeIndexOrDerivedType[]\' value=\'' + attributeId + '\' />';
        attributeElement    += '</div><a class=\"remove-dynamic-row-link\" id=\'' + elementId + '\' data-value=\'' + attributeId + '\' href="#">—</a></div></li>';
        $('ul#yw1').append(attributeElement);
    }
});
$('.remove-dynamic-row-link').live('click', function(){
    var attributeId      = $(this).attr('data-value');
    var elementId        = $(this).attr('id');
    var attributeLabel   = $('label[for=' + elementId + ']').html();
    $(this).closest('li').remove();
    var attributeElement = '<div class=\'multi-select-checkbox-input\'><label class=\'hasCheckBox\'><label class=\'hasCheckBox\'>';
    attributeElement    += '<input id=\'' + elementId + '\' value=\'' + attributeId + '\' type=\'checkbox\'';
    attributeElement    += ' name=\'ContactWebForm[serializedData][]\'></label></label><label for=\'' + elementId + '\'>' + attributeLabel + '</label></div>';
    $('span#ContactWebForm_serializedData').append(attributeElement);
    return false;
});