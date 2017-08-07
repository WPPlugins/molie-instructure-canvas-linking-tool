
function molie_canvas_link_page(event){
	event.preventDefault();	
	jQuery("#course_choose input:checked")
		.each(
			function(index,value){
				
				if(tinymce.activeEditor!=null){
					content = tinymce.activeEditor.getContent();
				}else{
					content = jQuery("#wp-content-editor-container textarea")
						.html();
				}
				
				var data = {
					'action': 'molie_post_link',
					'page_id': jQuery(value).attr("page"),
					'indent': jQuery(value).attr("indent"),
					'course': jQuery(value).attr("course"),
					'course_post': jQuery(value).attr("course_id"),
					'place': jQuery(value).attr("place"),
					'title': jQuery("#titlewrap").children().last().val(),
					'content': content,
					'module': jQuery(value).attr("module"), 
					'nonce': molie_admin_post_link.nonce,
				};
				
				jQuery.post(molie_admin_post_link.ajaxURL, data, function(response) {
				
					jQuery("#linkedcanvascoursemetaedit .inside")
						.each(
							function(index,value){
								jQuery(value)
									.html(response);
							}	
						);
						jQuery("#molielightbox")
							.fadeOut(300);
					}
				);
		
			}
		);
}
