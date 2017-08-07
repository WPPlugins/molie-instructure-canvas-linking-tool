<?PHP
	
	class MOLIEassignmentAjax{
	
		function __construct(){;
			add_action("wp_ajax_molie_assignment_import", array($this, "assignment_import"));
			add_action("wp_ajax_no_priv_molie_assignment_import", array($this, "assignment_import"));
		}
		
		private function create_categories($post){
		
			$categories = array();
		
			$course_category = get_post_meta($post->ID, "course_category_id", true);
			if($course_category==""){
				$course_category = wp_create_category( $post->post_title );
				add_post_meta($post->ID, "course_category_id", $course_category, true);
			}			
			array_push($categories, $course_category);
			
			$assignment_category = get_post_meta($post->ID, "course_module_assignments", true);
			if($module_category==""){
				$assignment_category = wp_create_category( "Assignments", $course_category );
				add_post_meta($post->ID, "course_module_assignments", $assignment_category, true);
			}
			array_push($categories, $assignment_category);
			
			return $categories;
			
		}
		
		function get_assignment($post){
			require_once(__DIR__ . "/../../API/Psr4AutoloaderClass.php");
			$loader = new Psr4AutoloaderClass;
			$loader->register();
			$loader->addNamespace('InstructureCanvasAPI', __DIR__ . "/../../API/InstructureCanvasAPI/src");
			
			$API = new InstructureCanvasAPI\InstructureCanvasAPI( 
															array(
																"site" => get_post_meta($post->ID, "courseURL", true),
																"token" => get_post_meta($post->ID, "courseToken", true),
																"webService" => "CURL"
															)
														);
			
			$assignment = new InstructureCanvasAPI\Courses\Assignments\Assignments();
			$assignment->setAPI($API);
			$assignment->setCourseID(get_post_meta($post->ID, "courseID", true));
			$data = $assignment->getAssignment($_POST['item']);
			$assignment = $data->content;
			$loader->unregister();
			return $assignment;
		}
	
		function assignment_import(){
		
			if(wp_verify_nonce($_POST['nonce'], "molie_admin_assignment"))
			{
			
				$post = get_post($_POST['course_post']);
				
				$categories = $this->create_categories($post);
				
				$quiz = $this->get_assignment($post);
		
				if(get_post_meta($post->ID, "canvasQuiz_" . $quiz->id, true)==""){
				
					$quiz_post = wp_insert_post(
												array(
													"post_type" => 'linkedcanvasamt',
													"post_status" => 'publish',
													"post_title" => $quiz->name,
													"post_content" => $quiz->description,
													"post_author" => get_current_user_id()
												)
											);
											
					wp_set_post_categories($quiz_post, $categories);
											
					update_post_meta($quiz_post, "CanvasCourse", get_post_meta($post->ID, "courseID", true), true);
					update_post_meta($quiz_post, "canvasQuizURL", $quiz->html_url, true);
					update_post_meta($post->ID, "canvasQuiz_" . $quiz->id, $quiz_post, true);
					echo __("Assignment linked");
				}
				else
				{
					echo __("Assignment already linked");
				}	
				
			}
			else
			{
				print_r($_POST);
				echo "Nonce failed";
			}
			wp_die();
		}	
	
	}
	
	$MOLIEassignmentAjax = new MOLIEassignmentAjax();
	