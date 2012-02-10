$(window).ready(function(){
	
	//main menu flyouts
	$( '#MenuView .parent' ).hover(
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
	
	$( '#HeaderLinksView .parent' ).hover(
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
