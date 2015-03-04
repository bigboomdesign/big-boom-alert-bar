var message = AlmData.message;
var bgColor = AlmData.bgColor;
var textColor = AlmData.textColor;
var domElement = AlmData.domElement;
jQuery(document).ready(function($){
	$(domElement).prepend('<div id="alm-default-msg" style="background: ' + bgColor + '; color: ' + textColor + '">' + message + '</div>');
});