function molie_upload_file(post_id,id,url){
	
	var data = {
		'action': 'molie_admin_upload_missing_file',
		'url': url,
		'post_id': post_id,
		'nonce': molie_admin_upload_missing_file.nonce,
	};
	
	jQuery.post(molie_admin_upload_missing_file.ajaxURL, data, function(response) {
	
		data = JSON.parse(response);
		if(data==1){
			if(jQuery("#molie_file_" + id).parent().children().length==1){
				jQuery("#molie_file_" + id).parent().fadeOut(300);
			}else{
				jQuery("#molie_file_" + id).fadeOut(300);
			}
		}
		
	
	});

}

function molie_download_file(post_id,id,url){

	var data = {
		'action': 'molie_admin_download_missing_file',
		'url': url,
		'post_id': post_id,
		'nonce': molie_admin_upload_missing_file.nonce,
	};
	
	jQuery.post(molie_admin_upload_missing_file.ajaxURL, data, function(response) {
	
		console.log(response);
		data = JSON.parse(response);
		if(data==1){
			if(jQuery("#molie_file_" + id).parent().children().length==1){
				jQuery("#molie_file_" + id).parent().fadeOut(300);
			}else{
				jQuery("#molie_file_" + id).fadeOut(300);
			}
		}
		
	
	});

}
