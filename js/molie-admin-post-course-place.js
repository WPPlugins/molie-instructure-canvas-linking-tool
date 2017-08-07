jQuery("document")
	.ready(
		function(){
			jQuery("#molielightboxclose")
				.on("click", function(){
						jQuery("#molielightbox")
							.fadeOut(300);
					}
				);
		}
	);

function molie_canvas_link(event, page_id){
	
	event.preventDefault();	
	
	var data = {
		'action': 'molie_post_course_place',
		'page': page_id,
		'course_post': jQuery("#link_button_" + page_id).attr("course"),
		'course_id': jQuery("#link_button_" + page_id).attr("course_id"),
		'nonce': molie_admin_post_course_place.nonce,
	};
	
	jQuery("#molielightbox")
			.fadeIn(300);
	
	jQuery("#molielightbox")
		.children()
		.last()
		.children()
		.last()
		.html("<h2>Please wait...</h2>");
	
	jQuery.post(molie_admin_post_course_place.ajaxURL, data, function(response) {
		jQuery("#molielightbox")
			.children()
			.last()
			.children()
			.last()
			.fadeOut(200,function(){
					jQuery("#molielightbox")
						.children()
						.last()
						.children()
						.last()
						.html(response);
					
					jQuery("#molielightbox")
						.children()
						.last()
						.children()
						.last()
						.fadeIn(200);
					
				}
			);
		
	});

}