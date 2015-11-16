/**
 * Options saved by user
 */

// the message we are displaying
var message = AlmData.message;

// the container's background color
var bgColor = AlmData.bgColor;

// the text color for the message
var textColor = AlmData.textColor;

// the DOM element to use for the jQuery selector, to determine where the message will be inserted
var domElement = AlmData.domElement;

// whether to prepend or append the message
var prependAppend = AlmData.prependAppend;

jQuery(document).ready(function($){

	// make sure we have a valid target
	var $element = $( domElement );
	if( $element.length !== 1 ) return;

	// the complete HTML for the message we're inserting
	var messageHTML = '<div id="alm-default-msg" ' + 
			' style="background: ' + bgColor + '; ' + 
			' color: ' + textColor + 
		'">' + 
			message + 
		'<div id="hide-btn"><span class="x">x</span></div></div>';

	// prepend or append the message
	if( 'prepend' == prependAppend ) {
		$element.prepend( messageHTML );
	}
	else if( 'append' == prependAppend ) {
		$element.append( messageHTML );
	}

	// onclick for hide button
	$( document ).on( 'click', '#hide-btn', function() {

		// the alert bar element
		var $almBar = $('#alm-default-msg');

  		$almBar.css({
  			opacity: '0',
  			transition: 'all 1s ease'
  		});
  		setTimeout(  function() {
			$almBar.css({display: 'none'});
  		}, 1000);

	}); // end onclick: hide button

	
});  // end: on document ready
