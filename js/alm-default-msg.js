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
		'</div>';

	// prepend or append the message
	if( 'prepend' == prependAppend ) {
		$element.prepend( messageHTML );
	}
	else if( 'append' == prependAppend ) {
		$element.append( messageHTML );
	}
	
});  // end: on document ready