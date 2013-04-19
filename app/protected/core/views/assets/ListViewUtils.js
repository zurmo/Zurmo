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

function processAjaxSuccessError(id, data)
{
    if(data == 'failure')
    {
        alert('An error has occurred. You have attempted to access a page you do not have access to.');
        return false;
    }
}

function processListViewSummaryClone(listViewId, summaryCssClass)
{
    replacementContent = $('#' + listViewId).find('.' + summaryCssClass).html();
    if (typeof(replacementContent) == 'undefined')
    {
        replacementContent = null;
    }
    $('#' + listViewId).parent().parent('.GridView')
    .find('form').first().find('.list-view-items-summary-clone')
    .html(replacementContent);
}

function updateListViewSelectedIds(gridViewId, selectedId, selectedValue)
{
    var array = new Array ();
    var processed = false;
    jQuery.each($('#' + gridViewId + "-selectedIds").val().split(','), function(i, value)
        {
            if(selectedId == value)
            {
                if(selectedValue)
                {
                    array.push(value);
                }
                processed = true;
            }
            else
            {
                if(value != '')
                {
                    array.push(value);
                }
            }
         }
     );
    if(!processed && selectedValue)
    {
        array.push(selectedId);
    }
    $('#' + gridViewId + "-selectedIds").val(array.toString());
}

function addListViewSelectedIdsToUrl(id, options)
{
    options.url = $.param.querystring(options.url, 'selectedIds=' + $('#' + id + "-selectedIds").val());
}

function resetSelectedListAttributes(selectedListAttributesId, hiddenListAttributesId, defaultSelectedAttributes)
{
    $('#' + selectedListAttributesId + ' option').remove().appendTo('#' + hiddenListAttributesId);
    defaults = eval(defaultSelectedAttributes);
    for (i = 0; i < defaults.length; ++i)
    {
        $('#' + hiddenListAttributesId).find('option[value="' + defaults[i] + '"]').remove().appendTo('#' + selectedListAttributesId);
    };
    $('#' + hiddenListAttributesId).find("option").each(function(){
        if(jQuery.inArray($(this).val(), defaults) != -1)
        {
            $(this).remove().appendTo('#' + selectedListAttributesId);
        }
    });
    return false;
}