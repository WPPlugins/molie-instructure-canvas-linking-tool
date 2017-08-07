jQuery(document).ready(
	function(){
		console.log("hello");
		jQuery(".canvasQuestion")
			.each(
				function(index,value){
					console.log(value);
					jQuery(value)
						.on("click", function(){	
								feedback = jQuery(this).attr("feedback");
								if(feedback!=""){
									jQuery("#feedback_" + jQuery(this).attr("counter")).html(feedback);
									jQuery("#feedback_" + jQuery(this).attr("counter")).css("color","#F00");
								}else{
									jQuery("#feedback_" + jQuery(this).attr("counter")).html("Correct");
									jQuery("#feedback_" + jQuery(this).attr("counter")).css("color","#0F0");
								}
							}
						)
				}
			)
	}
);
console.log("here i am");