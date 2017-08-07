<?PHP
	class MOLIEpostUnlink{
	
		function __construct(){
			add_action("wp_ajax_molie_post_unlink", array($this, "post_unlink"));
		}
	
		function post_unlink(){
			
			if(wp_verify_nonce($_POST['nonce'], "molie_admin_post_unlink"))
			{				
				if(isset($_POST['remove'])){
					$this->remove($_POST['page_id']);
				}
				delete_post_meta($_POST['page_id'], "CanvasLinked");
				$this->new_form($_POST['page_id']);
			}
			
			die();
			
		}
		
		function new_form($post){
			$args = array(
					"post_type" => "linkedcanvascourse",
					"post_status" => "publish"
				);
		
			$courses = get_posts($args);
		
			if(count($courses)!=0){
				echo "<p>" . __("Choose a course") . "</p>";
				foreach($courses as $course){
				?>
					<button id="link_button_<?PHP echo $post; ?>" course="<?PHP echo $course->ID; ?>" onclick="javascript:molie_canvas_link(event, <?PHP echo $post; ?>); return false;" url="<?PHP echo get_post_meta($course->ID, "courseURL", true); ?>" token="<?PHP echo get_post_meta($course->ID, "courseToken", true); ?>" course_id="<?PHP echo get_post_meta($course->ID, "courseID", true); ?>"><?PHP echo $course->post_title; ?></button>
				<?PHP
				}
			}
		}	
		
		function remove($post_id){
		
			$post = get_post($post_id);		
			$course_id = get_post_meta($post_id, "CanvasCourse", true);
				
			global $wpdb;
			$course_post = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key='CourseID' and meta_value = '" . $course_id . "'");
			$course_post = get_post($course_post[0]->post_id);
		
			require_once(__DIR__ . "/../../API/Psr4AutoloaderClass.php");
			$loader = new Psr4AutoloaderClass;
			$loader->register();
			$loader->addNamespace('InstructureCanvasAPI', __DIR__ . "/../../API/InstructureCanvasAPI/src");
					
			$API = new InstructureCanvasAPI\InstructureCanvasAPI( 
															array(
																"site" => get_post_meta($course_post->ID, "courseURL", true),
																"token" => get_post_meta($course_post->ID, "courseToken", true),
																"webService" => "CURL"
															)
														);
											
			$pages = new InstructureCanvasAPI\Courses\Pages\Pages();
			$pages->setAPI($API);
			
			$pages->setCourseID($course_id);
			$data = $pages->deletePage(get_post_meta($post_id,"postURL", true));
			delete_post_meta($post_id, "CanvasCourse");
			delete_post_meta($post_id, "postCanvasID");
			delete_post_meta($post_id, "postURL");
			delete_post_meta($post_id, "postHTMLURL");
			
		}
	
	}
	
	$MOLIEpostUnlink = new MOLIEpostUnlink();
	