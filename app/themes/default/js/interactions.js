$(window).ready(function(){
	
	//test - resizing left bar.
	//$( "#MenuView" ).resizable({ alsoResize: '#RecentlyViewedView' });

	//main menu flyouts

	$( '#MenuView .parent' ).hover(
		function(){
			if ( $(this).find('ul') ){
				$(this).find('ul').addClass('active-submenu');
				$(this).find('ul').fadeIn(100);
			}
		}, 
		function(){
			if ( $(this).find('ul') ){
				$(this).find('ul').removeClass('active-submenu');
				$(this).find('ul').fadeOut(250);
			}
		}
	);

});
