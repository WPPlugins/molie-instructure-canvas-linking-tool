<?PHP
	
	class MOLIErosterAjax{
	
		function __construct(){;
			add_action("wp_ajax_molie_roster_import", array($this, "roster_import"));
			add_action("wp_ajax_no_priv_molie_roster_import", array($this, "roster_import"));
		}
		
		private function create_categories($post){
		
			$categories = array();
		
			$course_category = get_post_meta($post->ID, "course_category_id", true);
			if($course_category==""){
				$course_category = wp_create_category( $post->post_title );
				add_post_meta($post->ID, "course_category_id", $course_category, true);
			}			
			array_push($categories, $course_category);
			
			$student_category = get_post_meta($post->ID, "course_students", true);
			if($quiz_category==""){
				$student_category = wp_create_category( "Students", $course_category );
				add_post_meta($post->ID, "course_students", $student_category, true);
			}
			array_push($categories, $student_category);
			
			return $categories;
			
		}
		
		function get_student($post, $student_id){
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
			
			$user = new InstructureCanvasAPI\Courses\Users\Users();
			$user->setAPI($API);
			$data = $user->getProfile($student_id);
			$loader->unregister();
			return $data;
		}
	
		function roster_import(){
				
			if(wp_verify_nonce($_POST['nonce'], "molie_admin_roster"))
			{
			
				$post = get_post($_POST['course_post']);
				
				$categories = $this->create_categories($post);
				
				$student = $this->get_student($post, $_POST['item']);
				
				if(get_post_meta($post->ID, "canvasStudent_" . $_POST['item'], true)==""){
				
					$student_post = wp_insert_post(
												array(
													"post_type" => 'linkedcanvasuser',
													"post_status" => 'publish',
													"post_title" => $student->content->short_name,
													"post_content" => "<img src='" . $student->content->avatar_url . "'><p>" . $student->content->bio . "</p>",
													"post_author" => get_current_user_id()
												)
											);
											
					wp_set_post_categories($student_post, $categories);
											
					update_post_meta($student_post, "CanvasUserID", $student->content->id, true);
					update_post_meta($student_post, "CanvasCourse", get_post_meta($post->ID, "courseID", true), true);
					update_post_meta($post->ID, "canvasStudent_" . $student->content->id, $student_post, true);
					echo __("Student Added");
				}
				else
				{
					echo __("Student already added");
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
	
	$MOLIErosterAjax = new MOLIErosterAjax();
	