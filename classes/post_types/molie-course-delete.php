<?PHP

	class MOLIEcoursedelete{
	
		function __construct(){
			add_action("before_delete_post", array($this, "delete_course"));
			if(strpos($_SERVER["REQUEST_URI"],"edit.php?post_status=trash&post_type=linkedcanvascourse")!==FALSE){
				wp_enqueue_script( 'molie-admin-course-delete', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-course-delete.js', array( 'jquery' ) );
			}
		}
		
		function delete_categories($id){
			$args = array(
				'hide_empty' => 0,
				'child_of'                 => $id,
			); 
			$child_categories = get_categories($args );
			if ( !empty ( $child_categories ) ){
				foreach ( $child_categories as $child_category ){
					self::delete_categories($child_category->term_id);
				}
			}
			wp_delete_category($id);
		}
		
		function delete_files($path){
			if (substr($path, strlen($path) - 1, 1) != '/') {
				$path .= '/';
			}
			$files = glob($path . '*', GLOB_MARK);
			foreach ($files as $file) {
				if (is_dir($file)) {
					self::delete_files($file);
				} else {
					unlink($file);
				}
			}
			if(is_dir($path)){
				if(file_exists($path)){
					rmdir($path);
				}
			}
		}
	
		function delete_course($post_id){
			global $post_type, $wpdb;
			if($post_type == "linkedcanvascourse"){
				$file_folder = get_post_meta($post_id, "CanvasCourse", true);
				$upload_dir = wp_upload_dir();
				$this->delete_files($upload_dir['basedir'] . "/" . $file_folder);
				$category = get_post_meta($post_id, "course_category_id", true);
				$this->delete_categories($category);
				$query = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key = 'CanvasCourse' and meta_value = " . get_post_meta($post_id, "courseID", true));
				foreach($query as $resource){
					wp_delete_post($resource->post_id, true);
				}
			}
			
		}
	
	}
	
	$MOLIEcoursedelete = new MOLIEcoursedelete();
	