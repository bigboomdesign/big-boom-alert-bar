var ds = ds || {};
/**
 * Demo 2
 */
( function( $ ) {
	var media;
	// MHULL edit: array to hold image ID's (data we'll be saving)
	var aIds = [];
	var iImg = 0;

	ds.media = media = {
		// MHull edit: changed ID
		buttonId: '#media-button-slides',
		detailsContainerId: '#attachment-details',
		settingsContainerId: '#attachment-settings',

		init: function() {
			$( media.buttonId ).on( 'click', this.openMediaDialog );
		},

		openMediaDialog: function( e ) {
			e.preventDefault();
			// Mhull edit: gallery actions
			// reset image count
			iImg = 0;
			var clone = wp.media.gallery.shortcode;
			wp.media.gallery.shortcode = function(attachments) {
				//images = attachments.pluck('id');
				//console.log(images);
				//jQuery('#custom_media_id').val(images);
				$(attachments.models).each(function(){
					var attachment = this.attributes;
					media.handleMediaAttachment(attachment.props, attachment);
				});
				wp.media.gallery.shortcode = clone;
				var shortcode= new Object();
				shortcode.string = function() {return ''};
				return shortcode;
			}
			// end: gallery actions
			
			// regular multi-select actions that were here already
			wp.media.editor.send.attachment = media.handleMediaAttachment;
			wp.media.editor.remove = media.closeMediaDialog;

			// An unique ID
			wp.media.editor.open( 'ds-editor' );
		},

		handleMediaAttachment: function( props, attachment ) {
			/**
			 * attachment
			 */
			iImg++;
			// if this is the first image, clear out the slide area and the ID array
			if(iImg == 1){
				aIds = [];
				$("#gallery-thumbs-slides img").css("display", 'none');
			}
			aIds.push(attachment.id);
			//aUrls.push(attachment.url);
			$("#wpland_slides").attr("value", aIds.join());
			$("#gallery-thumbs-slides").append("<img class='gallery-thumb' src='" + attachment.url + "' />");
		},
		closeMediaDialog: function( id ) {
			wp.media.editor.remove( id );
		}
	};

	$( document ).ready( function() {
		media.init();
		// MHULL edit: action for "clear" link
		$(document).on('click', "a#clear-gallery-images", function(){
			console.log('hey');
			aIds = [];
			$("#gallery-thumbs-slides img").css('display', 'none');
			$("#wpland_slides").attr("value", "");			
		});		
	});
} )( jQuery );
