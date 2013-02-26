function rebuildDynamicSearchRowNumbersAndStructureInput(formId)
{
    rowCount = 1;
    structure = '';
    $('#' + formId).find('.dynamic-search-row-number-label').each(function()
    {
        $(this).html(rowCount + '.');
        if(structure != '')
        {
            structure += ' AND ';
        }
        structure += rowCount;
        $(this).parent().find('.structure-position').val(rowCount);
        rowCount ++;
    });
    $('#' + formId).find('.dynamic-search-structure-input').val(structure);
    if(rowCount == 1)
    {
        $('#show-dynamic-search-structure-wrapper-' + formId).hide();
    }
    else
    {
        $('#show-dynamic-search-structure-wrapper-' + formId).show();
    }
}

function afterDynamicSearchValidateAjaxAction(form, data, hasError)
{
    if(!afterValidateAction(form, data, hasError))
    {
        $(this).closest('form').find('.search-view-1').show();       
        return false;
    }
    if(!hasError) {
        eval($(form).data('settings').afterValidateAjax);
    }
    return false;
}
function resolveClearLinkPrefixLabelAndVisibility(formId)
{
    criteriaSelected 	   = $('#' + formId).find('.dynamic-search-row-number-label').length;
    if($('#' + formId).find('.anyMixedAttributes-input').val() != '')
    {
        criteriaSelected++;
    }
    if(criteriaSelected > 0)
    {
        $('#' + formId).find('.clear-search-link-criteria-selected-count').html(criteriaSelected + ' ');
        $('#clear-search-link').show();
    }
    else
    {
        $('#clear-search-link').hide();
    }
}