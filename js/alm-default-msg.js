var message = AlmData.message;
var bgColor = AlmData.bgColor;
var textColor = AlmData.textColor;
var domElement = AlmData.domElement;
jQuery(document).ready(function($){
	$(domElement).prepend('<div id="alm-default-msg" style="background: ' + bgColor + '; color: ' + textColor + '">' + message + '<div id="hide-btn"><span class="x">x</span></div></div>');

	var almBar = $('#alm-default-msg');
	var hideBtn = $('#hide-btn');

	hideBtn.on( "click", function() {
  		console.log( 'Hello you' );
  		almBar.css({
  			opacity: '0',
  			transition: 'all 1s ease'
  		});
	});

});