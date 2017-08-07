function molie_select_all(){
	
	jQuery("input")
		.each(							
			function(index,value){	
				jQuery(value).prop('checked', true);								
			}
		);
	
}

function molie_unselect_all(){
	
	jQuery("input")
		.each(							
			function(index,value){	
				jQuery(value).prop('checked', false);									
			}
		);
	
}
