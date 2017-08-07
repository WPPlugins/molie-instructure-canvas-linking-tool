<?PHP

	class MOLIEitemdelete{
	
		function __construct(){
			add_action("before_delete_post", array($this, "delete"));
		}
		
		function get_delete($post_id){
			global $wpdb;
			$course_id = get_post_meta($post_id, "CanvasCourse", true);
			$query = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key = 'courseID' and meta_value = " . $course_id);
			return $query[0]->post_id;
		}
		
		function delete($post_id){
			$post = get_post($post_id);
			switch($post->post_type){
				case "post" : $this->delete_post($post_id); break;
				case "linkedcanvasquiz" : $this->delete_quiz($post_id); break;
				case "linkedcanvasuser" : $this->delete_user($post_id); break;
				case "linkedcanvasdis" : $this->delete_dis($post_id); break;
				case "linkedcanvasamt" : $this->delete_amt($post_id); break;
			}
		}
		
		function delete_post($post_id){
			global $wpdb;
			$main_course = $this->get_delete($post_id);
			$url = get_post_meta($post_id, "postURL", true);
			if($url!=""){
				$data = $wpdb->get_results("select meta_key from " . $wpdb->prefix . "postmeta where post_id = " . $main_course . " and meta_value = '" . $url . "' and meta_key like '%course_" . $main_course . "%'");
				delete_post_meta($main_course, $data[0]->meta_key);
			}
		}
		
		function delete_amt($post_id){
			global $wpdb;
			$main_course = $this->get_delete($post_id);
			$data = $wpdb->get_results("select meta_key from " . $wpdb->prefix . "postmeta where post_id = " . $main_course . " and meta_value = " . $post_id . " and meta_key like '%canvasQuiz_%'");
			delete_post_meta($main_course, $data[0]->meta_key, $post_id);
		}
		
		function delete_dis($post_id){
			global $wpdb;
			$main_course = $this->get_delete($post_id);
			$data = $wpdb->get_results("select meta_key from " . $wpdb->prefix . "postmeta where post_id = " . $main_course . " and meta_value = " . $post_id . " and meta_key like '%canvasDiscussion_%'");
			delete_post_meta($main_course, $data[0]->meta_key, $post_id);
		}
				
		function delete_quiz($post_id){
			global $wpdb;
			$main_course = $this->get_delete($post_id);
			$data = $wpdb->get_results("select meta_key from " . $wpdb->prefix . "postmeta where post_id = " . $main_course . " and meta_value = " . $post_id . " and meta_key like '%canvasQuiz_%'");
			delete_post_meta($main_course, $data[0]->meta_key, $post_id);
			$data = $wpdb->get_results("select meta_value from " . $wpdb->prefix . "postmeta where post_id = " . $post_id . " and meta_key like '%canvasQuizQuestion_%'");
			foreach($data as $answer){
				wp_delete_post($answer->meta_value);
			}
		}	
	
		function delete_user($post_id){
			global $wpdb;
			$main_course = $this->get_delete($post_id);
			$data = $wpdb->get_results("select meta_key from " . $wpdb->prefix . "postmeta where post_id = " . $main_course . " and meta_value = " . $post_id . " and meta_key like '%canvasStudent_%'");
			delete_post_meta($main_course, $data[0]->meta_key, $post_id);
		}
	
	}
	
	$MOLIEitemdelete = new MOLIEitemdelete();
	