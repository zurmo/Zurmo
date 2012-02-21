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
	var appChromeHeight = 0;
	var bufferHeight = 0;
	var recentlyViewedHeight = 0;
	
	if ( $('#LoginPageView').length > 0 ) {
		appChromeHeight = 38 + $('#FooterView').outerHeight(true);
		if ( wrapperDivHeight < viewportHeight  ){
			bufferHeight = viewportHeight - appChromeHeight;
			$('#LoginView').height(  bufferHeight   );
		}
	} else {
		recentlyViewedHeight = $('#RecentlyViewedView').outerHeight(true);
		appChromeHeight = recentlyViewedHeight + $('#MenuView').outerHeight(true) + $('#HeaderView').outerHeight(true) + $('#FooterView').outerHeight(true);
		if ( wrapperDivHeight < viewportHeight  ){
			bufferHeight = viewportHeight - appChromeHeight;
			$('#RecentlyViewedView').height( $('#RecentlyViewedView').height() + bufferHeight   );
		}
	}
	
	
	
	
	
	/*Dropdowns - Dropkick*/
	 
	$('td > select').dropkick();
	//$('select').dropkick();
	$('html').click(function(e) {
		$.each($('td > select'), function(index, value) {
			$(value).dropkick('close');
		});
	});


});
