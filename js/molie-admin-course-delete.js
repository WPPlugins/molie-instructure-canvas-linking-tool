jQuery(document).ready(
	function(){
	
		jQuery(".submitdelete")
			.each(
					function(index,value){
						jQuery(value).on("click", 
							function(ev){
								ev.preventDefault();
								if(confirm("Deleting a course will delete all course resources from the site. Are you sure?")){
									window.location.href = jQuery(value).attr("href");
								}
								return false;
							}
					);
				}
			)
			
	}
);