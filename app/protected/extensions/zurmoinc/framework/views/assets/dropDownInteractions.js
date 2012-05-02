$(window).ready(function(){
    /*Dropdowns - Dropkick*/
    $('select:not(.ignore-style)').each(function(){
        if($(this).attr('multiple') != 'multiple')
        {
            $(this).dropkick();
        }
    });
});