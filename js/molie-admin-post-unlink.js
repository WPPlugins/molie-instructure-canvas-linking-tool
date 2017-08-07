function molie_canvas_unlink(page_id){
			
	var data = {
		'action': 'molie_post_unlink',
		'page_id': page_id,
		'nonce': molie_admin_post_unlink.nonce
	};
	
	jQuery.post(molie_admin_post_unlink.ajaxURL, data, function(response) {
		jQuery("#linkedcanvascoursemetaedit .inside")
			.each(
				function(index,value){
					jQuery(value)
						.html(response);
				}	
			);
	});

}

function molie_canvas_unlink_delete(page_id,remove){
			
	var data = {
		'action': 'molie_post_unlink',
		'page_id': page_id,
		'remove': true,
		'nonce': molie_admin_post_unlink.nonce
	};
	
	jQuery.post(molie_admin_post_unlink.ajaxURL, data, function(response) {
		console.log(response);
		jQuery("#linkedcanvascoursemetaedit .inside")
			.each(
				function(index,value){
					jQuery(value)
						.html(response);
				}	
			);

	});

}
