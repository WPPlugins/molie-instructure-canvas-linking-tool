function molie_ajax_get(items, orig_length){

	console.log(items);

	if(items.length!=0){
		item = items.shift();
		var data = {
			'action': 'molie_page_import',
			'module': jQuery(item).attr("module"),
			'override': jQuery(item).attr("override"),
			'module_name': jQuery(item).attr("module_name"),
			'item': jQuery(item).attr("id"),
			'course': jQuery(item).attr("course"),
			'nonce': molie_admin_choose.nonce
		};
		
		jQuery.post(molie_admin_choose.ajaxURL, data, function(response) {
		
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
				
			html = jQuery(item)
						.parent()
						.html();
						
			jQuery(item)
				.parent()
				.html(html + "<span>" + response + "</span>");
				
			molie_ajax_get(items, orig_length);
			
		});
	}else{
		children = Array();
		jQuery("div#molie_choose")
			.children()
			.each(
				function(index,value){
					children.push(value);
				}
			);
		molie_fade_out(children);	
	}
}

function molie_fade_out(items){
	console.log(items);
	if(items.length!=0){
		item = items.shift();
		jQuery(item)
			.fadeOut(10, function(){
							molie_fade_out(items);
						}
					);
	}else{
		jQuery("div#molie_files")
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
						molie_fade_out(children);	
					
					}
			);
	
		jQuery("form#molie_choose_form #molie_choose_submit")
			.on("click", 
					function(){
					
						jQuery(".pageLinked")
							.each(
								function(index,value){
									jQuery(value)
										.fadeOut(100);
								}
							);
					
						items = Array();
						
						jQuery("#importProgress")
							.slideDown(500);
					
						jQuery("form#molie_choose_form input:checked")
							.each(							
								function(index,value){	
									items.push(value);									
								}
							);
							
						molie_ajax_get(items, items.length);
					
					}
			);
	}
);