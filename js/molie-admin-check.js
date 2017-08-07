function molie_admin_check_course(course_id, url){
		
		var data = {
			'action': 'molie_admin_course_check_pages',
			'course': course_id,
			'nonce': molie_admin_check.nonce
		};
		
		jQuery.post(molie_admin_check.ajaxURL, data, function(response) {
				data = JSON.parse(response);
				if(data[0]==false){
					jQuery("#pagesprogress")
						.css("color","#0F0")
						.css("font-weight","bold")
						.html("All Pages linked");
				}else{
					jQuery("#pagesprogress")					
						.css("color","#F00")
						.css("font-weight","bold")
						.html("Not all Pages linked");
					for(x in data[1]){
						
						item = data[1][x];
						jQuery("#pagesissues")
							.append("<li>" + item['title'] + " is not saved locally - <a href='" + item['html_url']  + "'>" + item['html_url'] + "</a></li>");
					}
					jQuery("#molie_pages")
						.append("<p><a target='_blank' href='" + (url + "admin.php?page=molie_choose") + "'>Update pages</a></p>");
				}
			}
		);
		
		var data = {
			'action': 'molie_course_check_files',
			'course': course_id,
			'nonce': molie_admin_check.nonce
		};
		
		jQuery.post(molie_admin_check.ajaxURL, data, function(response) {
				
				data = JSON.parse(response);
				if(data[0]==false){
					jQuery("#filesprogress")					
						.css("color","#0F0")
						.css("font-weight","bold")
						.html("All Files linked");
				}else{
					jQuery("#filesprogress")					
						.css("color","#F00")
						.css("font-weight","bold")
						.html("Not all Files linked");
					for(x in data[1]){
						
						item = data[1][x];
						jQuery("#filesissues")
							.append("<li>" + item['display_name'] + " is not linked locally - <a href='" + item['url']  + "'>" + item['url'] + "</a></li>");
					}
					jQuery("#molie_files")
						.append("<p><a target='_blank' href='" + (url + "admin.php?page=molie_files") + "'>Update Files</a></p>");
				}
			}
		);
		
		var data = {
			'action': 'molie_course_check_quizzes',
			'course': course_id,
			'nonce': molie_admin_check.nonce
		};
		
		jQuery.post(molie_admin_check.ajaxURL, data, function(response) {
				data = JSON.parse(response);
				if(data[0]==false){
					jQuery("#quizprogress")						
						.css("color","#0F0")
						.css("font-weight","bold")
						.html("All Pages linked");
				}else{
					jQuery("#quizprogress")					
						.css("color","#F00")
						.css("font-weight","bold")
						.html("Not all quizzes linked");
					for(x in data[1]){
						item = data[1][x];
						jQuery("#quizissues")
							.append("<li>" + item['title'] + " is not linked locally - <a href='" + item['url']  + "'>" + item['title'] + "</a></li>");
					}
					jQuery("#molie_quiz")
						.append("<p><a target='_blank' href='" + (url + "admin.php?page=molie_quiz") + "'>Update quizzes</a></p>");
				}	
			}
		);
		
		var data = {
			'action': 'molie_course_check_assignments',
			'course': course_id,
			'nonce': molie_admin_check.nonce
		};
		
		jQuery.post(molie_admin_check.ajaxURL, data, function(response) {
				data = JSON.parse(response);
				if(data[0]==false){
					jQuery("#asgmtsprogress")
						.css("color","#0F0")
						.css("font-weight","bold")
						.html("All Pages linked");
				}else{
					jQuery("#asgmtsprogress")					
						.css("color","#F00")
						.css("font-weight","bold")
						.html("Not all Assignments linked");
					for(x in data[1]){
						item = data[1][x];
						jQuery("#assignissues")
							.append("<li>" + item['name'] + " is not saved locally - <a href='" + item['html_url']  + "'>" + item['name'] + "</a></li>");
					}
					jQuery("#molie_assignments")
						.append("<p><a target='_blank' href='" + (url + "admin.php?page=molie_assignments") + "'>Update assignments</a></p>");
				}
			}
		);
		
		var data = {
			'action': 'molie_course_check_discussions',
			'course': course_id,
			'nonce': molie_admin_check.nonce
		};
		
		jQuery.post(molie_admin_check.ajaxURL, data, function(response) {
				data = JSON.parse(response);
				if(data[0]==false){
					jQuery("#disprogress")
						.css("color","#0F0")
						.css("font-weight","bold")
						.html("All Discussions linked");
				}else{
					jQuery("#disprogress")					
						.css("color","#F00")
						.css("font-weight","bold")
						.html("Not all discussions linked");
					for(x in data[1]){						
						item = data[1][x];
						jQuery("#disissues")
							.append("<li>" + item['title'] + " is not saved locally - <a href='" + item['html_url']  + "'>" + item['title'] + "</a></li>");
					}
					jQuery("#molie_discussions")
						.append("<p><a target='_blank' href='" + (url + "admin.php?page=molie_discussions") + "'>Update discussions</a></p>");
				}
			}
		);
		
		var data = {
			'action': 'molie_course_check_users',
			'course': course_id,
			'nonce': molie_admin_check.nonce
		};
		
		jQuery.post(molie_admin_check.ajaxURL, data, function(response) {
				data = JSON.parse(response);
				if(data[0]==false){
					jQuery("#rosterprogress")
						.css("color","#0F0")
						.css("font-weight","bold")
						.html("All Users linked");
				}else{
					jQuery("#rosterprogress")					
						.css("color","#F00")
						.css("font-weight","bold")
						.html("Not all Students linked");
					for(x in data[1]){						
						item = data[1][x];
						jQuery("#rosterissues")
							.append("<li>" + item['name'] + " is not saved locally</li>");
					}
					jQuery("#molie_roster")
						.append("<p><a target='_blank' href='" + (url + "admin.php?page=molie_roster") + "'>Update roster</a></p>");
				}
			}
		);
		
		var data = {
			'action': 'molie_course_check_calendar',
			'course': course_id,
			'nonce': molie_admin_check.nonce
		};
		
		jQuery.post(molie_admin_check.ajaxURL, data, function(response) {
				data = JSON.parse(response);
				if(data[0]==false){
					jQuery("#calendarprogress")
						.css("color","#0F0")
						.css("font-weight","bold")
						.html("Calendar linked");
				}else{
					jQuery("#calendarprogress")					
						.css("color","#F00")
						.css("font-weight","bold")
						.html("Calendar not linked");
					for(x in data[1]){						
						item = data[1][x];
						jQuery("#calendarissues")
							.append("<li>" + item['display_name'] + " is not saved locally - <a href='" + item['url']  + "'>" + item['url'] + "</a></li>");
					}
					jQuery("#molie_calendar")
						.append("<p><a target='_blank' href='" + (url + "admin.php?page=molie_calendar") + "'>Update Calendar</a></p>");
				}
			}
		);
		
}
