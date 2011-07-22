    function sticky_relocate(e)
    {
        if ($('#' + e.data.canvasId).contents().find('.sticky-anchor').length != 0)
        {
            var window_top = $(window).scrollTop();
            var div_top    = $('#' + e.data.canvasId).contents().find('.sticky-anchor').offset().top;
            if (window_top > (div_top))
            {
                $('#' + e.data.canvasId).contents().find('.sticky').addClass('stick');
                $('#' + e.data.canvasId).contents().find('.sticky').css('top', 0 + 'px');
            }
            else
            {
                $('#' + e.data.canvasId).contents().find('.sticky').removeClass('stick');
                $('#' + e.data.canvasId).contents().find('.sticky').css('top', 0 + 'px');
            }
        }
    }