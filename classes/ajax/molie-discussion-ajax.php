<?PHP
	
	class MOLIEdiscussionAjax{
	
		function __construct(){;
			add_action("wp_ajax_molie_discussion_import", array($this, "discussion_import"));
			add_action("wp_ajax_no_priv_molie_discussion_import", array($this, "discussion_import"));
		}
		
		private function create_categories($post){
			$course_category = get_post_meta($post->ID, "course_category_id", true);
			if($course_category==""){
				$course_category = wp_create_category( $post->post_title );
				add_post_meta($post->ID, "course_category_id", $course_category, true);
			}
			
			$discussion_category = get_post_meta($post->ID, "course_discussion", true);
			if($discussion_category==""){
				$discussion_category = wp_create_category( __("discussions"), $course_category );
				add_post_meta($post->ID, "course_discussion", $discussion_category, true);
			}
			
			return array($course_category, $discussion_category);
		}
		
		function get_discussion($post){
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
			
			$discussion = new InstructureCanvasAPI\Courses\DiscussionTopics\DiscussionTopics();
			$discussion->setAPI($API);
			$discussion->setCourseID(get_post_meta($post->ID, "courseID", true));
			$data = $discussion->getDiscussionTopic($_POST['item']);
			$discussion = $data->content;
			$loader->unregister();
			return $discussion;
		}
	
		function discussion_import(){
				
			if(wp_verify_nonce($_POST['nonce'], "molie_admin_discussion"))
			{
			
				$post = get_post($_POST['course_post']);
				$categories = $this->create_categories($post);
				
				$discussion = $this->get_discussion($post);
				
				if(get_post_meta($post->ID, "canvasDiscussion_" . $discussion->id, true)==""){
				
					$discussion_post = wp_insert_post(
												array(
													"post_type" => 'linkedcanvasdis',
													"post_status" => 'publish',
													"post_title" => $discussion->title,
													"post_content" => $discussion->message,
													"post_author" => get_current_user_id()
												)
											);
											
					
					wp_set_post_categories($discussion_post, $categories);
											
					update_post_meta($discussion_post, "CanvasCourse", get_post_meta($post->ID, "courseID", true), true);
					update_post_meta($discussion_post, "canvasDiscussionURL", $discussion->html_url, true);
					update_post_meta($post->ID, "canvasDiscussion_" . $discussion->id, $discussion_post, true);
					echo __("Discussion linked");
				}
				else
				{
					echo __("Discussion already linked");
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
	
	$MOLIEdiscussionAjax = new MOLIEdiscussionAjax();
	