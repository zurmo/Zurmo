/*
 * Script modified from NETTUTS.com [by James Padolsey]
 * @requires jQuery($), jQuery UI & sortable/draggable UI modules
 * http://net.tutsplus.com/tutorials/javascript-ajax/inettuts/
 */

var juiPortlets = {

    jQuery : $,

    settings : {
        uniqueLayoutId: null,
        csrfTokenName: null,
        csrfToken: null,
        moduleId: null,
        saveUrl: null,
        columnsClass : null,
        widgetSelector: '.juiportlet-widget',
        handleSelector: '.juiportlet-widget-head',
        contentSelector: '.juiportlet-widget-content',
        widgetDefault : {
            movable: true,
            removable: true,
            collapsible: true,
            editable: true
        }
    },

    init : function (uniqueLayoutId, moduleId, saveUrl, csrfTokenName, csrfToken, columnsClass, collapsible, movable) {
        this.uniqueLayoutId = uniqueLayoutId;
        this.moduleId       = moduleId;
        this.saveUrl        = saveUrl;
        this.csrfTokenName  = csrfTokenName;
        this.csrfToken      = csrfToken;
        this.columnsClass   = columnsClass;
        this.settings.widgetDefault.collapsible = collapsible;
        this.settings.widgetDefault.movable = movable;
        this.addWidgetControls();
        if(movable)
        {
            this.makeSortable();
        }
    },

    refresh : function () {
        this.addWidgetControls();
        this.makeSortable();
    },

    getWidgetSettings : function (id) {
        var juiPortlets = this
        return juiPortlets.settings.widgetDefault;
    },

    addWidgetControls : function () {
        var juiPortlets = this,
            $ = this.jQuery,
            settings = this.settings;
        $(settings.widgetSelector, $(juiPortlets.columnsClass)).each(function () {
            var thisWidgetSettings = juiPortlets.getWidgetSettings(this.id);
            if (thisWidgetSettings.removable) {
                if($('#' + this.id).find(settings.handleSelector).find(':contains("CLOSE")').text()=='CLOSE')
                {
                    $('#' + this.id).find(settings.handleSelector).find(':contains("CLOSE")').mousedown(function (e) {
                        e.stopPropagation();
                    }).click(function () {
                        if(confirm('This widget will be removed, ok?')) {
                            $(this).parents(settings.widgetSelector).animate({
                                opacity: 0
                            },function () {
                                $(this).wrap('<div/>').parent().slideUp(function () {
                                    $(this).remove();
                                    juiPortlets.savePreferences();
                                });
                            });
                        }
                        return false;
                    })
                }
            }

            if (thisWidgetSettings.editable) {

            }

            if (thisWidgetSettings.collapsible) {
                if($('#' + this.id).find(settings.handleSelector).find(':contains("COLLAPSE")').text()!='COLLAPSE')
                {
                    if($('#' + this.id).find(settings.contentSelector).css('display') == 'none')
                    {
                        var collapseStyle = '-38px 0px';
                    }
                    else
                    {
                        var collapseStyle = '';
                    }
                    $('<a href="#" class="collapse" style="background-position:' + collapseStyle + ';">COLLAPSE</a>').mousedown(function (e) {
                        e.stopPropagation();
                    }).toggle(function () {
                        var bg = $(this).css('background-position');

                        if(bg == 'undefined' || bg == null)
                        {
                            bg = $(this).css('background-position-x') + " " + $(this).css('background-position-y');
                        }
                        if(bg.substring(0, 5) == '-38px')
                        {
                            $(this).css({backgroundPosition: ''}).css({backgroundPositionX: -52, backgroundPositionY: 0})
                                .parents(settings.widgetSelector)
                                    .find(settings.contentSelector).show();
                        }
                        else
                        {
                            $(this).css({backgroundPosition: '-38px 0px'}).css({backgroundPositionX: -38, backgroundPositionY: 0})
                                .parents(settings.widgetSelector)
                                    .find(settings.contentSelector).hide();
                        }
                        juiPortlets.savePreferences();
                    },function () {
                        var bg = $(this).css('background-position');
                        if(bg == 'undefined' || bg == null)
                        {
                            bg = $(this).css('background-position-x') + " " + $(this).css('background-position-y');
                        }
                        if(bg.substring(0, 5) == '-38px')
                        {
                            $(this).css({backgroundPosition: ''}).css({backgroundPositionX: -52, backgroundPositionY: 0})
                                .parents(settings.widgetSelector)
                                    .find(settings.contentSelector).show();
                        }
                        else
                        {
                            $(this).css({backgroundPosition: '-38px 0px'}).css({backgroundPositionX: -38, backgroundPositionY: 0})
                                .parents(settings.widgetSelector)
                                    .find(settings.contentSelector).hide();
                        }
                        juiPortlets.savePreferences();
                    }).prependTo($(settings.handleSelector,this));
                }
            }
        });
    },
    getCurrentColumnByPortletId : function (portletId) {
        var juiPortlets = this,
            $ = this.jQuery,
            settings = this.settings;
       var returnValue = null;
       $(juiPortlets.columnsClass).each(function(i){
            $(settings.widgetSelector,this).each(function(j){
                if(portletId==$(this).attr('id'))
                {
                    returnValue = i;
                }
            });
        });
        return returnValue;
    },
    getCurrentPositionByPortletId : function (portletId) {
        var juiPortlets = this,
            $ = this.jQuery,
            settings = this.settings;
       var returnValue = null;
       $(juiPortlets.columnsClass).each(function(i){
            $(settings.widgetSelector,this).each(function(j){
                if(portletId==$(this).attr('id'))
                {
                    returnValue = j;
                }
            });
        });
        return returnValue;
    },
    savePreferences : function () {
        var juiPortlets = this,
            $ = this.jQuery,
            settings = this.settings;
        var myObject = {
          portletLayoutConfiguration : {
          'uniqueLayoutId' : juiPortlets.uniqueLayoutId,
          'portlets' : {}
          },
        };
        myObject[juiPortlets.csrfTokenName] = juiPortlets.csrfToken;
        $(juiPortlets.columnsClass).each(function(i){
            $(settings.widgetSelector,this).each(function(j){
                var portletSetting = {
                    id : $(this).attr('id'),
                    collapsed : $(settings.contentSelector,this).css('display') === 'none' ? true : false,
                    column : i,
                    position : j
                }
                myObject['portletLayoutConfiguration']['portlets'][portletSetting.id] = portletSetting;
            });
        });
        $.ajax({
            url : juiPortlets.saveUrl,
            type : 'post',
            data : $.param(myObject),
            dataType : 'json',
            success : function(data)
            {
                if (data != null && typeof data == 'object') {
                    //todo: any success function call needed
                }
                else
                {
                    //todo: failure to save
                }
            },
            error : function()
            {
                //todo: error call
            }
        });


    },
    makeSortable : function () {
        var juiPortlets = this,
            $ = this.jQuery,
            settings = this.settings,
            $sortableItems = (function () {
                var notSortable = null;
                $(settings.widgetSelector,$(juiPortlets.columnsClass)).each(function (i) {
                    if (!juiPortlets.getWidgetSettings(this.id).movable) {
                        if(!this.id) {
                            this.id = 'widget-no-id-' + i;
                        }
                        notSortable += '#' + this.id + ',';
                    }
                });
                return $('> li:not(' + notSortable + ')', juiPortlets.columnsClass);
            })();

        $sortableItems.find(settings.handleSelector).css({
            cursor: 'move'
        }).mousedown(function (e) {
            $sortableItems.css({width:''});
            $(this).parent().css({
                width: $(this).parent().width() + 'px'
            });
        }).mouseup(function () {
            if(!$(this).parent().hasClass('dragging')) {
                $(this).parent().css({width:''});
            } else {
                $(juiPortlets.columnsClass).sortable('disable');
            }
        });

        $(juiPortlets.columnsClass).sortable({
            items: $sortableItems,
            connectWith: $(juiPortlets.columnsClass),
            handle: settings.handleSelector,
            placeholder: 'juiportlet-widget-placeholder',
            forcePlaceholderSize: true,
            revert: 300,
            delay: 100,
            opacity: 0.8,
            containment: 'document',
            start: function (e,ui) {
                $(ui.helper).addClass('dragging');
            },
            stop: function (e,ui) {
                $(ui.item).css({width:''}).removeClass('dragging');
                $(juiPortlets.columnsClass).sortable('enable');
                juiPortlets.savePreferences();
            }
        });
    }

};