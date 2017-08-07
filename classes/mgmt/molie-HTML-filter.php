<?PHP

	class MOLIEHTMLFilter{
	
		function __construct(){
			add_filter('esc_html',  array($this, 'change_post'));
		}
	
		function change_post($word) {
			if(strpos($_SERVER['REQUEST_URI'],"canvas_linked=true")!=FALSE && strpos($_SERVER['REQUEST_URI'],"edit.php")!=FALSE){
				if(isset($_GET['course'])){
					global $wpdb;
					$data = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key = 'courseID' and meta_value = '" . $_GET['course'] . "'");
					$course_post = get_post($data[0]->post_id);
					switch($word){
						case "Posts" : $word = $word . " from " . $course_post->post_title; break;
						case "Linked Canvas User" : $word = __("Users on") . " " . $course_post->post_title; break;
						case "Linked Canvas Quiz" : $word = __("Quizzes on") . " " . $course_post->post_title; break;
						case "Linked Canvas Assignment" : $word = __("Assignments on") . " " . $course_post->post_title; break;
						case "Linked Canvas Discussion" : $word = __("Discussions on") . " " . $course_post->post_title; break;
					}
				}else if($word == "Posts"){
					$word = __("Posts linked to Canvas Courses");
				}
			}
			return $word;
		}
	
	}
	
	$MOLIEHTMLFilter = new MOLIEHTMLFilter();
	