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
	
});
