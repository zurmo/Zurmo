/**
 * Clearform
 * Created by Truth <truth@truthanduntruth.com>
 * Report Bugs: <bugs@truthanduntruth.com>
 * Copyright 2010
 */
;(function ($) {
    $.fn.clearform = function (c) {
        var d = {
            form: 'form',
            bind: 'click',
            clear: "input[type!='submit'][type!='button'][type!='hidden'][type!='reset'][type!=checkbox], textarea, select",
            clearCheckbox: "input[type=checkbox][class!='ignoreclearform']",
            css: {},
            complete: function () {}
        };
        var f = {};
        var g = $.extend(f, d, c);
        var h = g.bind.split(" ");
        var i = '';
        $.each(h, function (a, b) {
            i += b + '.clearform '
        });
        $(this).bind(i, function (e) {
            $(g.clear, g.form).not('.ignore-clearform').val('').find('option:first-child').attr('selected', 'selected');
            $(g.clearCheckbox, g.form).not('.multiselect-checkbox').attr('checked', false);
            g.complete()
        }).css(g.css)
    }
})(jQuery);

function attachLoadingOnSubmit(formId)
{
    if($('#' + formId).find(".attachLoading:first").hasClass("loading-ajax-submit"))
    {
        return true;
    }
    if($('#' + formId).find(".attachLoading:first").hasClass("loading"))
    {
        return false;
    }
    $('#' + formId).find(".attachLoading:first").addClass("loading");
    attachLoadingSpinner(formId, true);

    return true;
}

function detachLoadingOnSubmit(formId)
{
    $('#' + formId).find(".attachLoading:first").removeClass("loading");
    $('#' + formId).find(".attachLoading:first").removeClass("loading-ajax-submit");
}

function beforeValidateAction(form)
{
    if(form.find(".attachLoadingTarget").hasClass("loading") || form.find(".attachLoading:first").hasClass("loading"))
    {
        return false;
    }
    if(form.find(".attachLoadingTarget").length)
    {
        form.find(".attachLoadingTarget").addClass("loading");
        form.find(".attachLoadingTarget").addClass("loading-ajax-submit");
    }
    else
    {
        form.find(".attachLoading:first").addClass("loading");
        form.find(".attachLoading:first").addClass("loading-ajax-submit");
    }
    attachLoadingSpinner(form.attr('id'), true);
    return true;
}

function afterValidateAction(form, data, hasError)
{
    if(hasError)
    {
        if(form.find(".attachLoadingTarget").length)
        {
            form.find(".attachLoadingTarget").removeClass("loading");
            form.find(".attachLoadingTarget").removeClass("loading-ajax-submit");
        }
        else
        {
            form.find(".attachLoading:first").removeClass("loading");
            form.find(".attachLoading:first").removeClass("loading-ajax-submit");
        }
        return false;
    }
    else
    {
        return true;
    }
}

function afterValidateAjaxAction(form, data, hasError)
{
    if(!afterValidateAction(form, data, hasError))
    {
        return false;
    }
    if(!hasError) {
        eval($(form).data('settings').afterValidateAjax);
    }
    return false;
}

function searchByQueuedSearch(inputId)
{
    if(basicSearchQueued == 0)
    {
        $('#' + inputId).closest('form').submit();
    }
}
