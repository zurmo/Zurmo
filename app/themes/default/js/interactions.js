$(window).ready(function(){
	
	//main menu flyouts or mbmenu releacment
	//$( '#MenuView .parent, #HeaderLinksView .parent, #ShortcutsMenu .parent' ).hover(
	$( '.nav > .parent' ).hover(
		function(){
			if ( $(this).find('ul') ){
				$(this).find('ul').stop(true, true).fadeIn(100);
			}
		}, 
		function(){
			if ( $(this).find('ul') ){
				$(this).find('ul').stop(true, true).fadeOut(250);
			}
		}
	);
	
	/*Resizes the app to fill the browser's window case smaller'*/
	var viewportHeight = $(window).height();
	var wrapperDivHeight = $('body > div').outerHeight(true)
	var recentlyViewedHeight = $('#RecentlyViewedView').outerHeight(true);
	var appChromeHeight = recentlyViewedHeight + $('#MenuView').outerHeight(true) + $('#HeaderView').outerHeight(true) + $('#FooterView').outerHeight(true);
	var bufferHeight = 0;
	if ( wrapperDivHeight < viewportHeight  ){
		bufferHeight = viewportHeight - appChromeHeight;
		$('#RecentlyViewedView').height( $('#RecentlyViewedView').height() + bufferHeight   );
	}
	
});
