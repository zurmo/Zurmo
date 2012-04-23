    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
     * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
     ********************************************************************************/

var designer = {
    jQuery : $,
    enableElementToPlace : function(id)
    {
        $('#' + id + '_elementToPlace').draggable('enable');
    },
    getCellElementDivStart : function(id)
    {
        if(this.settings.mergeRowAndAttributePlacement)
        {
            return '<div id="' + id + '_Placed" class="cell-element">';
        }
        return '<div id="' + id + '_Placed" class="movable-cell-element cell-element">';
    },
    getCellModifySettingsSpan : function()
    {
        if(this.settings.canModifyCellSettings)
        {
            return '<span class="cell-modify-settings-link cell-element-icon  ui-icon ui-icon-wrench">&#160;</span>';
        }
        return '';
    },
    getCellRemoveSpan : function()
    {
        if(!this.settings.mergeRowAndAttributePlacement)
        {
            return '<span class="cell-element-icon ui-icon ui-icon-trash"></span>';
        }
        return '';
    },
    getCellHandleSpan : function()
    {
        return '<span class="cell-handle-icon ui-icon ui-icon-arrow-4"></span>';
    },
    getCellSettingsDiv : function(id)
    {
        var content = this.settings.cellSettingsContent;
        return content.replace(/{cellId}/gi, id);
    },
    getLayoutData : function()
    {
        var data = {
        };



        var panelCount = 0;
        $('.sortable-panel').each(function(){
            data['layout[panels][' + panelCount + ']'] = '';
            //alert('aa' + $(this).children('.panel-settings').find('.settings-form-field'));
            var rowCount = 0;
            $(this).find('.sortable-row-list-helper').children('li').each(function(){
                data['layout[panels][' + panelCount + '][rows][' + rowCount + ']'] = '';
                var cellCount = 0;
                $(this).children('.droppable-cell-container-helper').each(function(){
                    if($(this).children('.cell-element').length == 0)
                    {
                        var elementId = 'Null';
                    }
                    else
                    {
                        var cellId = $(this).children('.cell-element').attr('id');
                        var elementId = cellId.substring(0, cellId.indexOf("_Placed"));
                        $(this).children('.cell-element').children('.cell-settings').find('.settings-form-field').each(function(){
                            var cellSettingId = $(this).attr('id').substring(0, $(this).attr('id').indexOf("_"));
                            //$(this).attr('name', 'layoutSettings[panels][' + panelCount + ']' +
                            //'[rows][' + rowCount + '][cells][' + cellCount + '][' + elementId + '][' +  cellSettingId + ']');
                            if(!($(this).is(':checkbox') && $(this).attr('checked') == false))
                            {
                                var cellSettingValue = $(this).val();
                                data['layout[panels][' + panelCount + '][rows][' + rowCount + '][cells][' + cellCount + '][' + cellSettingId + ']'] = $(this).val();
                            }
                        });

                    }
                    data['layout[panels][' + panelCount + '][rows][' + rowCount + '][cells][' + cellCount + '][element]'] = elementId;
                    cellCount ++;
                });
                rowCount ++;
            });
            $(this).children('.panel-settings').find('.settings-form-field').each(function(){
                var panelSettingId = $(this).attr('id').substring(0, $(this).attr('id').indexOf("_"));
                data['layout[panels][' + panelCount + '][' + panelSettingId + ']'] = $(this).attr('value');
                //$(this).attr('name', 'layoutSettings[panels][' + panelCount + '][' + panelSettingId + ']');
            });
            panelCount ++;
        });

        //sortable-panel-list

        //alert(jQuery.param(data));
        return data;
    },
    getPanelModifySpan : function()
    {
        if(this.settings.canModifyPanelSettings)
        {
            return '<span class="panel-modify-settings-link panel-element-icon ui-icon ui-icon-wrench">&#160;</span>';
        }
    },
    getPanelNextId : function()
    {
        var maxPanelId = 0;
        $('#layout-container').contents().find('.sortable-panel').each(function(){
            var id = parseInt($(this).attr('id').substring($(this).attr('id').indexOf("_") + 1));
            if(id > maxPanelId)
            {
                maxPanelId = id;
            }
        });
        return maxPanelId + 1;
    },
    getPanelRemoveSpan : function()
    {
        if(this.settings.canRemovePanels)
        {
            return '<span class="panel-element-icon ui-icon ui-icon-trash">&#160;</span>';
        }
    },
    getPanelHandleSpan : function()
    {
        if(this.settings.canMovePanels)
        {
            return '<span class="panel-handle-icon ui-icon ui-icon-arrow-4">&#160;</span>';
        }
    },
    getPanelSettingsDiv : function(id)
    {
        var content = this.settings.panelSettingsContent;
        return content.replace(/{panelId}/gi, id);
    },
    getRowCells : function()
    {
        if(this.settings.maxCellsPerRow == 1)
        {
            return '<div class="layout-single-column droppable-cell-container droppable-cell-container-helper ui-state-hover"></div>';
        }
        else
        {
            return '<div class="layout-double-column droppable-cell-container droppable-cell-container-helper ui-state-hover"></div>\
            <div class="layout-double-column droppable-cell-container droppable-cell-container-helper ui-state-hover"></div>';
        }
    },
    getRowModifyCellSpan : function()
    {
        if(this.settings.maxCellsPerRow == 1 || !this.settings.canMergeAndSplitCells)
        {
            return '';
        }
        return '<span class="row-element-icon ui-icon ui-icon-circle-minus">&#160;</span>';
    },

    getRowRemoveSpan : function()
    {
        if(this.settings.canRemoveRows)
        {
            return '<span class="row-element-icon ui-icon ui-icon-trash"></span>';
        }
        return '';
    },
    getRowHandleSpan : function()
    {
        if(this.settings.canMoveRows)
        {
            return '<span class="row-handle-icon ui-icon ui-icon-arrow-4"></span>';
        }
        return '';
    },
    getRowUlSortableClass : function()
    {
        if(this.settings.canMoveRows)
        {
            return 'sortable-row-list';
        }
        return '';
    },
    updateLayoutElementWidth : function()
    {
        var total_elem_width = 0;
        $('.layout-elements-column-container').each(function() {
            total_elem_width += $(this).outerWidth( true );
        });
        $('.layout-elements').css('width', total_elem_width + 150);
    },
    init : function (
        canAddPanels,
        canModifyPanelSettings,
        canRemovePanels,
        canMovePanels,
        canAddRows,
        canMoveRows,
        canRemoveRows,
        canModifyCellSettings,
        canMergeAndSplitCells,
        mergeRowAndAttributePlacement,
        maxCellsPerRow,
        panelSettingsContent,
        cellSettingsContent
    ) {
        this.settings.canAddPanels                  = canAddPanels;
        this.settings.canModifyPanelSettings        = canModifyPanelSettings;
        this.settings.canRemovePanels               = canRemovePanels;
        this.settings.canMovePanels                 = canMovePanels;
        this.settings.canAddRows                    = canAddRows;
        this.settings.canMoveRows                   = canMoveRows;
        this.settings.canRemoveRows                 = canRemoveRows;
        this.settings.canModifyCellSettings         = canModifyCellSettings;
        this.settings.canMergeAndSplitCells         = canMergeAndSplitCells;
        this.settings.mergeRowAndAttributePlacement = mergeRowAndAttributePlacement;
        this.settings.maxCellsPerRow                = maxCellsPerRow;
        this.settings.panelSettingsContent          = panelSettingsContent;
        this.settings.cellSettingsContent           = cellSettingsContent;
        this.setupLayout();
        this.updateLayoutElementWidth();
    },
    initDroppableCells : function(selector)
    {
        var designer = this;
        $( selector ).droppable("destroy");
        $( selector ).droppable({
                accept: ".element-to-place, .cell-element",
                //activeClass: "ui-state-hover",
                hoverClass: "ui-state-active",
                cursor: 'pointer',
                drop: function( event, ui ) {

                    if($( this ).children("div").length > 0 )
                    {
                        var id = $( this ).children("div").attr('id');
                        if(ui.helper.attr('id') != id)
                        {
                            designer.enableElementToPlace(id.substring(0, id.indexOf("_Placed")));
                        }
                    }
                    if (ui.helper.hasClass('element-to-place')) {
                        ui.draggable.draggable('disable');
                        attributeName = ui.helper.attr('id').substring(0, ui.helper.attr('id').indexOf("_elementToPlace"));
                        $( this ).html(designer.getCellElementDivStart(attributeName)
                        + designer.getCellHandleSpan()
                        + ui.helper.html() + designer.getCellRemoveSpan()
                        + designer.getCellModifySettingsSpan()
                        + designer.getCellSettingsDiv(attributeName + '_Placed') + '</div>');
                    }
                    else
                    {
                        $('#' + ui.helper.attr('id')).remove();
                        $( this ).html(designer.getCellElementDivStart(ui.helper.attr('id').substring(0, ui.helper.attr('id').indexOf("_Placed")))
                        + ui.helper.html() + '</div>');
                    }
                    document.body.style.cursor = 'auto';
                }
            });
    },
    initSortableRows : function(selector)
    {
        var designer = this;
        $( selector ).sortable("destroy");
        $( selector ).sortable({
            revert: true,
            axis: "y",
            connectWith: ".sortable-row-connector",
            dropOnEmpty: true,
            cursor: 'pointer',
            handle: "> .ui-icon-arrow-4",
            placeholder: "ui-state-highlight",
            stop: function(event, ui) {
                if (ui.item.hasClass('rowToPlace')) {
                    $(ui.item).replaceWith(
                    '<li class="ui-state-default">' +
                        designer.getRowHandleSpan() +
                        designer.getRowCells() +
                        designer.getRowRemoveSpan() +
                        designer.getRowModifyCellSpan() +
                    '</li>'
                    );
                    designer.initDroppableCells(".droppable-cell-container");
                }
                document.body.style.cursor = 'auto';
            }
        });
    },
    prepareSaveLayout : function(formId)
    {
        layoutData = this.getLayoutData();
        //alert('x' + jQuery.param(layoutData));
        return $('#' + formId).serialize() + '&save=Save&ajax=' + formId + '&' + jQuery.param(layoutData);
    },
    settings : {
        canAddPanels               : true,
        canModifyPanelSettings     : true,
        canRemovePanels            : true,
        canMovePanels              : true,
        canAddRows                 : true,
        canMoveRows                : true,
        canRemoveRows              : true,
        canModifyCellSettings      : true,
        canMergeAndSplitCells      : true,
        maxCellsPerRow             : 2,
        panelSettingsContent       : ''
    },
    setupLayout : function()
    {
        var designer = this;
        $( ".sortable-panel-list" ).sortable({
            revert: true,
            axis: "y",
            handle: "> .ui-icon-arrow-4",
            placeholder: "ui-state-highlight",
            cursor: 'pointer',
            stop: function(event, ui) {
                var nextPanelId = designer.getPanelNextId();
                if (ui.item.hasClass('panelToPlace')) {
                    $(ui.item).replaceWith(
                    '<li id="panel_' + nextPanelId + '" class="ui-state-default sortable-panel">\
                        <span class="panel-title-display">&#160;</span>\
                        ' + designer.getPanelHandleSpan()+ designer.getPanelRemoveSpan() + designer.getPanelModifySpan() +
                        '<div class="sortable-row-list-container">\
                            <ul class="' + designer.getRowUlSortableClass() + ' sortable-row-list-helper sortable-row-connector">\
                                <li class="ui-state-default">' +
                                    designer.getRowHandleSpan() +
                                    designer.getRowCells() +
                                    designer.getRowRemoveSpan() +
                                    designer.getRowModifyCellSpan() +
                                '</li>\
                            </ul>\
                        </div>'
                        + designer.getPanelSettingsDiv(nextPanelId) +
                    '</li>'
                    );
                    designer.initSortableRows("#layout-container ul.sortable-row-list");
                    designer.initDroppableCells(".droppable-cell-container");
                }
                document.body.style.cursor = 'auto';
            }
        });
        designer.initSortableRows(".sortable-row-list");

        $( ".rowToPlace" ).draggable({
            connectToSortable: ".sortable-row-list",
            helper: "clone",
            revert: "invalid",
            cursor: 'pointer',
            stop: function(event, ui){
                document.body.style.cursor = 'auto';
            }
        });
        $( ".panelToPlace" ).draggable({
            connectToSortable: ".sortable-panel-list",
            helper: "clone",
            revert: "invalid",
            cursor: 'pointer',
            stop: function(event, ui){
                document.body.style.cursor = 'auto';
            }
        });
        designer.initDroppableCells(".droppable-cell-container");

        $('.rowToPlace, .panelToPlace, .element-to-place').live('mouseover',function(){
                document.body.style.cursor = 'pointer';
        });
        $('.rowToPlace, .panelToPlace, .element-to-place, .cell-element').live('mouseout',function(){
                document.body.style.cursor = 'auto';
        });
        $('.movable-cell-element').live('mouseover',function(){
            //document.body.style.cursor = 'pointer';
            $(this).draggable({
                revert: "invalid",
                snap: ".droppable-cell-container",
                snapMode: "inner",
                cursor: 'pointer',
                handle: "> .ui-icon-arrow-4",
                start: function(event,ui)
                {
                    $(ui.helper).addClass('ui-state-default');
                    $(ui.helper).css('height', '20px');
                    $(ui.helper).css('width', '260px');
                },
                stop: function(event, ui){
                    document.body.style.cursor = 'auto';
                }
            });
        });


        $( ".element-to-place" ).draggable({
            helper: "clone",
            revert: "invalid",
            snap: ".droppable-cell-container",
            snapMode: "inner",
            cursor: 'pointer',
            start: function(event,ui)
            {
                $(ui.helper).attr('id', $(this).attr('id'));
                $(ui.helper).css('height', '20px');
                $(ui.helper).css('width', '260px');
            },
            stop: function(event, ui){
                document.body.style.cursor = 'auto';
            }
        });
        $( ".element-to-place.ui-state-disabled" ).draggable({disabled: true});

        $('.ui-icon-circle-minus').live('click',function(){
            //find id of element in 2nd div
            if($(this).parent().children("div:eq(1)").children("div").length > 0 )
            {

                var id = $(this).parent().children("div:eq(1)").children("div").attr('id');
                designer.enableElementToPlace(id.substring(0, id.indexOf("_Placed")));
            }
            $(this).parent().children("div:eq(1)").remove();
            $(this).parent().children("div:eq(0)").removeClass('layout-double-column');

            $(this).parent().children("div:eq(0)").addClass('layout-single-column');
            $(this).removeClass('ui-icon-circle-minus');
            $(this).addClass('ui-icon-circle-plus');
        });
        $('.ui-icon-circle-plus').live('click',function(){
            $(this).parent().children("div:eq(0)").after('<div class="layout-double-column droppable-cell-container droppable-cell-container-helper ui-state-hover"></div>');
            $(this).parent().children("div:eq(0)").removeClass('layout-single-column');
            $(this).parent().children("div:eq(0)").addClass('layout-double-column');
            $(this).removeClass('ui-icon-circle-plus');
            $(this).addClass('ui-icon-circle-minus');
            designer.initDroppableCells(".droppable-cell-container");
        });

        $('.ui-icon-trash').live('click',function(){
            if($(this).parent().hasClass('cell-element'))
            {
                var id = $(this).parent().attr('id');
                designer.enableElementToPlace(id.substring(0, id.indexOf("_Placed")));
                $(this).parent().remove();
            }
            //am i a row? is my parent parent have a class sortable-row-list
            else if($(this).parent().parent().hasClass('sortable-row-list'))
            {
                $(this).parent().children('.droppable-cell-container').children("div").each(function()
                {
                    designer.enableElementToPlace($(this).attr('id').substring(0, $(this).attr('id').indexOf("_Placed")));
                });
                $(this).parent().remove();
            }
            //am i a panel? does my parent have class sortable-panel
            else if($(this).parent().hasClass('sortable-panel'))
            {
                $(this).parent().find('.droppable-cell-container').children("div").each(function()
                {
                    designer.enableElementToPlace($(this).attr('id').substring(0, $(this).attr('id').indexOf("_Placed")));
                });
                $(this).parent().remove();
            }
            //todo: do we need to refresh columns or sortables or anything?
        });
        $('.panel-modify-settings-link').live('click',function(){
            var panelId = $(this).parent().attr('id');
            $(this).parent().children('.panel-settings').dialog(
            {
                modal: true,
                draggable: false,
                resizable: false,
                width: 400,
                height: 300,
                close: function(event, ui) {
                    $('#' + panelId).children('.panel-title-display').html($(this).find('.panel-title').val());
                    $(this).dialog('destroy');
                    $(this).appendTo('#' + panelId);
                  }

            });
            $('a.ui-dialog-titlebar-close').remove();
        });

        $('.cell-modify-settings-link').live('click',function(){
            var cellId = $(this).parent().attr('id');
            $(this).parent().children('.cell-settings').dialog(
            {
                modal: true,
                draggable: false,
                resizable: false,
                width: 400,
                height: 300,
                close: function(event, ui) {
                    $(this).dialog('destroy');
                    $(this).appendTo('#' + cellId);
                  }

            });
            $('a.ui-dialog-titlebar-close').remove();
        });
        $( "ul, li" ).disableSelection();

    },
    updateFlashBarAfterSaveLayout : function(data, flashBarId)
    {
        $('#' + flashBarId).jnotifyAddMessage(
        {
            text: data.message,
            permanent: false,
            showIcon: true,
            type: data.type
        });
    }
}
