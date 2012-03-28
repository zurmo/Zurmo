$(window).ready(function(){
    /*Dropdowns - Dropkick*/
    $('select:not(.ignore-style)').each(function(){
        $(this).dropkick();
    });
});