$(window).ready(function(){
	
	//main menu flyouts or mbmenu releacment
	//$( '#MenuView .parent, #HeaderLinksView .parent, #ShortcutsMenu .parent' ).hover(
	$( '.nav > .parent' ).hover(
		function(){
			if ( $(this).find('ul').length > 0 ){
				$(this).find('ul').stop(true, true).delay(0).fadeIn(100);
			}
		}, 
		function(){
			if ( $(this).find('ul').length > 0 ){
				$(this).find('ul').stop(true, true).fadeOut(250);
			}
		}
	);
	
	
	//Main nav hover
	$('#MenuView a span').hover(
		function(){
			$(this).stop(true, true).animate({ backgroundColor : '#7cb830', color: '#fff' }, 350);
		},
		function(){
			if ( $(this).parent().parent().hasClass('active') == false ){
				$(this).stop(true, true).animate({ backgroundColor : 'transparent', color: '#555555' }, 250, null, 
	    			function() { this.style.backgroundColor='transparent'; });	
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
	 
	//$('#edit-form select, .search-view-1 select, #inline-edit-form select').dropkick();

	$('select:not(.ignore-style)').dropkick();

	$('html').click(function(e) {
		$.each($('td > select'), function(index, value) {
			$(value).dropkick('close');
		});
	});
	
	
	/*Checkboxes
	 from: http://webdesign.maratz.com/lab/fancy-checkboxes-and-radio-buttons/jquery.html
	 * */


	function setupCheckboxes( $context ) {
		
		if ( $('input:checkbox', $context ).length ) {	
			$('input:checkbox', $context ).each(function(){ 
				$(this).parent().removeClass('c_on');
			});
			$('label input:checked', $context ).each(function(){ 
				$(this).parent('label').addClass('c_on');
		    });
		}
		
		$('label', $context[0] ).
		    live( 'click', { $inputContext:$(this).content  },
				function( event ){
			        if ( $('input:checkbox', event.data.$inputContext ).length ) {	
						$('input:checkbox', event.data.$inputContext ).each(function(){ 
							$(this).parent().removeClass('c_on');
						});
						$('label input:checked', event.data.$inputContext ).each(function(){ 
							$(this).parent('label').addClass('c_on');
					    });
					}
			});
	}
	
	function testme(){
		log('hi');
	}
	
	//Still buggy...
	/*
	var $tableItems = $('.items')[0];
	
	$('label.hasCheckBox', $tableItems ).each(function(){
		
		$(this).bind('click', { $inputContext:$(this).content  },
			function( event ){
				
		        if ( $('input:checkbox', $tableItems ).length ) {	
		        		
					$('input:checkbox', $tableItems ).each(function(){ 
						$(this).parent().removeClass('c_on');
					});
					
					$('label input:checked', $tableItems ).each(function(){ 
						$(this).parent('label').addClass('c_on');
				    });
				    
				}
		});
	});
	*/
    
    //we're doing that because the multiselect widget isn't generated yet..
    window.setTimeout(
    	function setCheckboxes(){
    		setupCheckboxes( $('#search-form') );
    		setupCheckboxes( $('#app-search') );
    		//setupCheckboxes( $('.checkbox-column') );
    	},
    1000 );
	
	
	
	$('input:checkbox').live('change', function(event){
		//log( $(this).attr('id') );
		//log( $(this).is(':checked'));
		//log('----');
	    //checkall();
	});
	
	function checkall(){
		$('input:checkbox', '.items').each(function(){
			log( $(this).attr('id') );
			log( $(this).is(':checked'));
			log('----');
		});
	}
	
	
	
	
	
	/*Label overlays input, address fields*/
	$(".overlay-label-field input").live('focus', function(){
		$(this).prev().fadeOut(100);
	});
	
	$(".overlay-label-field > input").live('blur', function(){
		if($(this).val() == "") {
			$(this).prev().fadeIn(250);
		}
	});
	$(".overlay-label-field > input").each( function(){
		if($(this).val() == "") {
			$('label', $(this)).fadeIn(250);
		}
	});
	
	
	/*Form errors
	$('.form .errorMessage').each(function(){
		$(this).css({ top: -$(this).height() -20 });
	});
	*/
	
	
	 window.setTimeout(
    	function do1(){
    		//$('.ui-datepicker-trigger').trigger('click');
    	}, 10 );
	
	
	


});


// usage: log('inside coolFunc', this, arguments);
// paulirish.com/2009/log-a-lightweight-wrapper-for-consolelog/
window.log = function f(){ log.history = log.history || []; log.history.push(arguments); if(this.console) { var args = arguments, newarr; args.callee = args.callee.caller; newarr = [].slice.call(args); if (typeof console.log === 'object') log.apply.call(console.log, console, newarr); else console.log.apply(console, newarr);}};

// make it safe to use console.log always
(function(a){function b(){}for(var c="assert,count,debug,dir,dirxml,error,exception,group,groupCollapsed,groupEnd,info,log,markTimeline,profile,profileEnd,time,timeEnd,trace,warn".split(","),d;!!(d=c.pop());){a[d]=a[d]||b;}})
(function(){try{console.log();return window.console;}catch(a){return (window.console={});}}());