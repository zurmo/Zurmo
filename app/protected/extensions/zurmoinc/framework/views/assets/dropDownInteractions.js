$(document).ready(function(){
    /*Dropdowns - Dropkick*/
    $('select:not(.ignore-style)').each(function(){
        if($(this).attr('multiple') != 'multiple')
        {
            $(this).dropkick();
        }
    });
});

function resetDropKickDropDowns(inputObj)
{
    //Reseting DropKick Information
    inputObj.closest('form').find('select:not(.ignore-style)').each(function(){
        $(this).removeData('dropkick');
    });
    inputObj.closest('form').find('div.dk_container').each(function(){
        $(this).remove();
    });
    inputObj.closest('form').find('select:not(.ignore-style)').each(function(){
        $(this).dropkick();
        $(this).dropkick('rebindToggle');
    });
}