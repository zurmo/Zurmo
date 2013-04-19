/*********************************************************************************
 * Zurmo is a customer relationship management program developed by
 * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
 *
 * Zurmo is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * Zurmo is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
 * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
 *
 * The interactive user interfaces in original and modified versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the Zurmo
 * logo and Zurmo copyright notice. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display the words
 * "Copyright Zurmo Inc. 2013. All rights reserved".
 ********************************************************************************/

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
    makeOrRemoveLoadingSpinner(true, $('#' + formId).find(".attachLoading:first"));

    return true;
}

function detachLoadingOnSubmit(formId)
{
    $('#' + formId).find(".attachLoading:first").removeClass("loading");
    $('#' + formId).find(".attachLoading:first").removeClass("loading-ajax-submit");
}

function beforeValidateAction(form)
{
    var context;
    if(form.find(".attachLoadingTarget").hasClass("loading") || form.find(".attachLoading:first").hasClass("loading"))
    {
        return false;
    }
    if(form.find(".attachLoadingTarget").length)
    {
        context = form.find(".attachLoadingTarget");
        context.addClass("loading");
        context.addClass("loading-ajax-submit");
    }
    else
    {
        context = form.find(".attachLoading:first");
        context.addClass("loading");
        context.addClass("loading-ajax-submit");
    }
    makeOrRemoveLoadingSpinner(true, context);
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
            form.find(".attachLoadingTarget").removeClass("attachLoadingTarget");
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
