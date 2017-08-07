function molie_admin_check_diff_dashboard(course_id, url, div_id){
		
		var data = {
			'action': 'molie_admin_course_check_pages',
			'course': course_id,
			'nonce': molie_admin_check_diff.nonce
		};
		
		jQuery.post(molie_admin_check_diff.ajaxURL, data, function(response) {
				data = JSON.parse(response);
				if(data[0]==true){
					if(jQuery(div_id).html()==""){
						jQuery(div_id).html("is out of date. <a href='" + url + "'>Please check your course</a>");
					}
				}
			}
		);
		
		var data = {
			'action': 'molie_course_check_files',
			'course': course_id,
			'nonce': molie_admin_check_diff.nonce
		};
		
		jQuery.post(molie_admin_check_diff.ajaxURL, data, function(response) {
				
				data = JSON.parse(response);
				if(data[0]==true){
					if(jQuery(div_id).html()==""){
						jQuery(div_id).html("is out of date. <a href='" + url + "'>Please check your course</a>");
					}
				}
			}
		);
		
		var data = {
			'action': 'molie_course_check_quizzes',
			'course': course_id,
			'nonce': molie_admin_check_diff.nonce
		};
		
		jQuery.post(molie_admin_check_diff.ajaxURL, data, function(response) {
				data = JSON.parse(response);
				if(data[0]==true){
					if(jQuery(div_id).html()==""){
						jQuery(div_id).html("is out of date. <a href='" + url + "'>Please check your course</a>");
					}
				}	
			}
		);
		
		var data = {
			'action': 'molie_course_check_assignments',
			'course': course_id,
			'nonce': molie_admin_check_diff.nonce
		};
		
		jQuery.post(molie_admin_check_diff.ajaxURL, data, function(response) {
				data = JSON.parse(response);
				if(data[0]==true){
					if(jQuery(div_id).html()==""){
						jQuery(div_id).html("is out of date. <a href='" + url + "'>Please check your course</a>");
					}
				}
			}
		);
		
		var data = {
			'action': 'molie_course_check_discussions',
			'course': course_id,
			'nonce': molie_admin_check_diff.nonce
		};
		
		jQuery.post(molie_admin_check_diff.ajaxURL, data, function(response) {
				data = JSON.parse(response);
				if(data[0]==true){
					if(jQuery(div_id).html()==""){
						jQuery(div_id).html("is out of date. <a href='" + url + "'>Please check your course</a>");
					}
				}
			}
		);
		
		var data = {
			'action': 'molie_course_check_users',
			'course': course_id,
			'nonce': molie_admin_check_diff.nonce
		};
		
		jQuery.post(molie_admin_check_diff.ajaxURL, data, function(response) {
				data = JSON.parse(response);
				if(data[0]==true){
					if(jQuery(div_id).html()==""){
						jQuery(div_id).html("is out of date. <a href='" + url + "'>Please check your course</a>");
					}
				}
			}
		);
		
}


function molie_admin_check_diff_course(course_id, course_name, url){
		
		var data = {
			'action': 'molie_admin_course_check_pages',
			'course': course_id,
			'nonce': molie_admin_check_diff.nonce
		};
		
		jQuery.post(molie_admin_check_diff.ajaxURL, data, function(response) {
				data = JSON.parse(response);
				if(data[0]==true){
					if(jQuery("#moliepollcheck").html()==""){
						jQuery("#moliepollcheck").addClass("notice notice-warning");
						jQuery("#moliepollcheck").html("<p>Course " + course_name + " is out of date. <a href='" + url + "'>Please check your course</a></p>");
					}
				}
			}
		);
		
		var data = {
			'action': 'molie_course_check_files',
			'course': course_id,
			'nonce': molie_admin_check_diff.nonce
		};
		
		jQuery.post(molie_admin_check_diff.ajaxURL, data, function(response) {
				
				data = JSON.parse(response);
				if(data[0]==true){
					if(jQuery("#moliepollcheck").html()==""){
						jQuery("#moliepollcheck").addClass("notice notice-warning");
						jQuery("#moliepollcheck").html("<p>Course " + course_name + " is out of date. <a href='" + url + "'>Please check your course</a></p>");
					}
				}
			}
		);
		
		var data = {
			'action': 'molie_course_check_quizzes',
			'course': course_id,
			'nonce': molie_admin_check_diff.nonce
		};
		
		jQuery.post(molie_admin_check_diff.ajaxURL, data, function(response) {
				data = JSON.parse(response);
				if(data[0]==true){
					if(jQuery("#moliepollcheck").html()==""){
						jQuery("#moliepollcheck").addClass("notice notice-warning");
						jQuery("#moliepollcheck").html("<p>Course " + course_name + " is out of date. <a href='" + url + "'>Please check your course</a></p>");
					}
				}	
			}
		);
		
		var data = {
			'action': 'molie_course_check_assignments',
			'course': course_id,
			'nonce': molie_admin_check_diff.nonce
		};
		
		jQuery.post(molie_admin_check_diff.ajaxURL, data, function(response) {
				data = JSON.parse(response);
				if(data[0]==true){
					if(jQuery("#moliepollcheck").html()==""){
						jQuery("#moliepollcheck").addClass("notice notice-warning");
						jQuery("#moliepollcheck").html("<p>Course " + course_name + " is out of date. <a href='" + url + "'>Please check your course</a></p>");
					}
				}
			}
		);
		
		var data = {
			'action': 'molie_course_check_discussions',
			'course': course_id,
			'nonce': molie_admin_check_diff.nonce
		};
		
		jQuery.post(molie_admin_check_diff.ajaxURL, data, function(response) {
				data = JSON.parse(response);
				if(data[0]==true){
					if(jQuery("#moliepollcheck").html()==""){
						jQuery("#moliepollcheck").addClass("notice notice-warning");
						jQuery("#moliepollcheck").html("<p>Course " + course_name + " is out of date. <a href='" + url + "'>Please check your course</a></p>");
					}
				}
			}
		);
		
		var data = {
			'action': 'molie_course_check_users',
			'course': course_id,
			'nonce': molie_admin_check_diff.nonce
		};
		
		jQuery.post(molie_admin_check_diff.ajaxURL, data, function(response) {
				data = JSON.parse(response);
				if(data[0]==true){
					if(jQuery("#moliepollcheck").html()==""){
						jQuery("#moliepollcheck").addClass("notice notice-warning");
						jQuery("#moliepollcheck").html("<p>Course " + course_name + " is out of date. <a href='" + url + "'>Please check your course</a></p>");
					}
				}
			}
		);
		
}
