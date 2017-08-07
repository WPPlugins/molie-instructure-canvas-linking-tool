function molie_file_get(items, orig_length){
	if(items.length!=0){
		item = items.shift();
		
		var data = {
			'action': 'molie_file_import',
			'course_post': jQuery(item).attr("course_post"),
			'item': jQuery(item).attr("id"),
			'verifier': jQuery(item).attr("verifier"),
			'folder': jQuery(item).attr("folder"),
			'filename': jQuery(item).attr("filename"),
			'url': jQuery(item).attr("url"),
			'nonce': molie_admin_file.nonce
		};
		
		jQuery("#update" + jQuery(item).attr("id"))
			.html("Downloading...")
			.css("color","#00F");
		
		jQuery.post(molie_admin_file.ajaxURL, data, function(response) {
			
			width = jQuery("#importProgress")
						.width();
						
			width = width - 10;
						
			progress = (orig_length - items.length) * (width / orig_length);

			jQuery("#importTotal")
				.html((orig_length - items.length) + " / " + orig_length);

			jQuery("#importProgressBar")
				.animate({width:progress+"px"}, 400);
				
			percentage = (100-((items.length/orig_length) * 100)).toString();
			percentage = percentage.split(".");

			jQuery("#importProgressBar")
				.html(percentage[0] + "%");
				
			jQuery("#update" + jQuery(item).attr("id"))
				.html("File Downloaded")
				.css("color","#0F0");
			
			molie_file_get(items, orig_length);
			
		});
	}else{	
		children = Array();
		jQuery("div#molie_choose form")
			.children()
			.each(
				function(index,value){
					children.push(value);
				}
			);
		molie_file_fade_out(children);
	}
}

function molie_file_fade_out(items){
	if(items.length!=0){
		item = items.shift();
		jQuery(item)
			.fadeOut(10, function(){
							molie_file_fade_out(items);
						}
					);
	}else{
		jQuery("div#molie_quiz_assignments")
			.fadeIn(500);
	}
}

jQuery(document).ready(
	function(){
	
		jQuery("form#molie_choose_form #molie_choose_skip")
			.on("click", 
					function(){
					
						children = Array();
						jQuery("div#molie_choose form")
							.children()
							.each(
								function(index,value){
									children.push(value);
								}
							);
						molie_file_fade_out(children);	
					
					}
			);
	
		jQuery("form#molie_choose_form #molie_file_submit")
			.on("click", 
					function(){
					
						items = Array();
						
						jQuery("#importProgress")
							.slideDown(500);
							
						jQuery("#importProgressBar")
							.animate({width:"40px"}, 400);
							
						jQuery("#importProgressBar")
							.html("0%");	
					
						jQuery("form#molie_choose_form input:checked")
							.each(							
								function(index,value){	
									items.push(value);									
								}
							);
							
						molie_file_get(items, items.length);
					
					}
			);
	}
);