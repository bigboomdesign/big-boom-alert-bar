var message = AlmData.message;
var bgColor = AlmData.bgColor;
var textColor = AlmData.textColor;
jQuery(document).ready(function($){
	$('body').prepend('<div id="alm-default-msg" style="background: ' + bgColor + '; color: ' + textColor + '">' + message + '</div>');
});