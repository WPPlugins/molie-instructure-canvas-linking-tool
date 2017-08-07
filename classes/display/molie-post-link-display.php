<?PHP

	class MOLIEpostLinkDisplay{
	
		function __construct(){
			add_filter('the_content', array($this, 'change_pictures'));
		}
		
		function get_category($course, $module){
			global $wpdb;
			$course_post = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key='CourseID' and meta_value = '" . $course . "'");
			$post = get_post($course_post[0]->post_id);
			$category = get_post_meta($post->ID, "course_module_" . $module, true);
			return get_category_link($category);
		}
		
		function get_url($url){
			global $wpdb;
			$image_post = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_value = '" . $url . "'");
			$post = get_post($image_post[0]->post_id);
			return $post->guid;
		}
		
		function get_urls($content){
			global $post;
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML($content);
			$query = "//a"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query); 
			foreach ($result as $node) {
				$url = $node->getAttribute("href");
				if(strpos($url, "/modules/")!==FALSE){
					$course = get_post_meta($post->ID, "CanvasCourse", true);
					$parts = explode("/", $url);
					$new_url = $this->get_category($course, array_pop($parts));
				}else{
					$new_url = $this->get_url($url);
				}
				$content = str_replace($url, $new_url, $content);
			}
			return $content;
		}
	
		function change_pictures($the_content){
			global $post;
			if(get_post_meta($post->ID, "CanvasLinked", true)==true){
				$the_content = $this->get_urls($the_content);
			}
			return $the_content;
		}
	
	}
	
	$MOLIEpostLinkDisplay = new MOLIEpostLinkDisplay();
	