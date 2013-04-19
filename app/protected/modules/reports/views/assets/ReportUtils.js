$(window).ready(function(){
    $(".item-to-place").live("mousemove",function(){
        $(this).draggable({
            helper: function(event){
                var label = $(event.target).html();
                var width = $('.wrapper').width() * 0.5 - 55;
                var clone = $('<div class="dynamic-row clone">' + label + '</div>');
                //clone.width(width);
                clone.animate({ width : width}, 250);
                $('body').append(clone);
                return clone;
            },
            revert: "invalid",
            snap: ".droppable-dynamic-row-container",
            snapMode: "inner",
            cursor: "pointer",
            start: function(event,ui){
                $(ui.helper).attr("id", $(this).attr("id"));
            },
            stop: function(event, ui){
                document.body.style.cursor = "auto";
            }
        });
    });
    
    var dropped = false;
    $( ".droppable-dynamic-rows-container").droppable({
        accept: ".item-to-place",
        hoverClass: "ui-state-active",
        cursor: "pointer",
        drop: function( event, ui ) {
            //todo: hide drop overlay
            $('.dynamic-droppable-area').removeClass('activate-drop-zone');
            dropped = true;
        },
        activate: function(event,ui){
            dropped = false;
            $('.dynamic-droppable-area').addClass('activate-drop-zone');
            var currentNode = $(event.currentTarget).parentsUntil( '.ComponentWithTreeForReportWizardView').parent();
            var size = currentNode.find('.dynamic-rows ul').find(' > li').size();
            if(size === 0){
                currentNode.find('.zero-components-view > div').fadeOut(150);
            }
        },
        deactivate: function(event,ui){
            $('.dynamic-droppable-area').removeClass('activate-drop-zone');
            var currentNode = $($(ui.draggable[0])).parentsUntil( '.ComponentWithTreeForReportWizardView').parent();
            var size = currentNode.find('.dynamic-rows ul').find(' > li').size();
            if(size === 0 && dropped === false){
                currentNode.find('.zero-components-view > div').fadeIn(400);
            } else {
                currentNode.find('.zero-components-view > div').fadeOut(150);
            }
        }
    });
});

function rebuildReportFiltersAttributeRowNumbersAndStructureInput(divId){
    rowCount = 1;
    structure = '';
    $('#' + divId).find('.dynamic-row-number-label').each(function(){
        $(this).html(rowCount + '.');
        if(structure != ''){
            structure += ' AND ';
        }
        structure += rowCount;
        $(this).parent().find('.structure-position').val(rowCount);
        rowCount ++;
    });
    $('#' + divId).find('.filters-structure-input').val(structure);
    if(rowCount == 1){
        //hmm. not sure exactly how this will be named.
        $('#show-filters-structure-wrapper').hide();
    } else {
        $('#show-filters-structure-wrapper').show();
    }
}