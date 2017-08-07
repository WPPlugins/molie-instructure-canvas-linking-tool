function molie_update_post(page_id,canvas_page_id,direction){
		
	var data = {
		'action': 'molie_post_update',
		'page_id': page_id,
		'canvas_page_id': canvas_page_id,
		'nonce': molie_admin_post_update.nonce,
		'direction' : direction,
	};
	
	jQuery.post(molie_admin_post_update.ajaxURL, data, function(response) {
	
		console.log(response);
		data = JSON.parse(response);
		console.log(data[0]);
		if(data[0]=="error"){
			alert("An error has occured");
		}else{
			alert("Page updated");
			if(direction == "download"){
					jQuery('#content_ifr').contents().find('#tinymce').html(data[0]);
			}
		}
		jQuery("#canvassyncmessage")
			.fadeOut(100);
	
	});

}
