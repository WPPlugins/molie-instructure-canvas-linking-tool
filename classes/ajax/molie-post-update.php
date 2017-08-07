<?PHP
	class MOLIEpostUpdate{
	
		function __construct(){
			add_action("wp_ajax_molie_post_update", array($this, "post_update"));
		}
	
		function post_update(){
			
			if(wp_verify_nonce($_POST['nonce'], "molie_admin_post_update"))
			{
				if($_POST['direction']=="upload"){
					$this->upload();
				}else{
					$this->download();
				}
			}
			
			die();
			
		}
		
		function upload(){
		
			$post = get_post($_POST['page_id']);		
			$course_id = get_post_meta($_POST['page_id'], "CanvasCourse", true);
				
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
			$pages->setCourseID(get_post_meta($course_post->ID, "courseID", true));
			$data = $pages->setPage($_POST['canvas_page_id'], array("wiki_page[body]" => $post->post_content));
		}
		
		function download(){
		
			$course_id = get_post_meta($_POST['page_id'], "CanvasCourse", true);
				
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
			$pages->setCourseID(get_post_meta($course_post->ID, "courseID", true));
			$data = $pages->getPage(get_post_meta($_POST['page_id'], "postURL", true));
			$new_content = $data->content->body;
			
			//print_r($new_content);
			//print_r($_POST);
			$update = $wpdb->update( $wpdb->prefix . "posts", array("post_content" => $new_content), array( "ID" => $_POST['page_id'] ) );
			if($update === false){
				print_r($wpdb);
				echo json_encode(array("error"));
			}else{
				echo json_encode(array($new_content));
			}
			
			die();				
		}
	
	}
	
	$MOLIEpostUpdate = new MOLIEpostUpdate();
	