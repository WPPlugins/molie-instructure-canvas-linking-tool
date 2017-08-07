jQuery(document).ready(
	function(){
	
		jQuery("#molie_show_previous a")
			.on("click", 
					function(){
						jQuery("#molie_previous")
							.fadeIn(200);
					}
			);
			
		jQuery(".canvasUse")
			.on("click", 
					function(ev){
						jQuery("#canvas_token").val(jQuery(ev.currentTarget).parent().children().first().html());
						jQuery("#canvas_url").val(jQuery(ev.currentTarget).parent().parent().prev().html());
					}
			);	
	
		jQuery("form#molie_link_form #molie_link_submit")
			.on("click", 
					function(){
					
						jQuery("#molie_show_previous a")
							.fadeOut(200);
					
						jQuery("#molie_previous")
							.fadeOut(100);
					
						jQuery("#molie_response")
								.html("Getting your courses....");
					
						jQuery("body")
							.css("cursor", "progress");
						
						var data = {
							'action': 'molie_course_list',
							'url': jQuery("#canvas_url").val(),
							'admin_url': jQuery("#admin_url").val(),
							'token': jQuery("#canvas_token").val(),
							'nonce': molie_admin_link.nonce
						};

						// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
						jQuery.post(molie_admin_link.ajaxURL, data, function(response) {
							jQuery("body")
								.css("cursor", "default");
							jQuery("#molie_response")
								.fadeOut(200, function(){
									jQuery("#molie_response")
										.html(response);
									jQuery("#molie_response")
										.fadeIn(200);
								}
							);
							
						});
					}
			);
	}
);