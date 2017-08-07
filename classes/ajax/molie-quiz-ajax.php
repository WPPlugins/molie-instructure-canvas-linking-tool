<?PHP
	
	class MOLIEquizAjax{
	
		function __construct(){;
			add_action("wp_ajax_molie_quiz_import", array($this, "quiz_import"));
			add_action("wp_ajax_no_priv_molie_quiz_import", array($this, "quiz_import"));
		}
		
		private function create_categories($post){
		
			$categories = array();
		
			$course_category = get_post_meta($post->ID, "course_category_id", true);
			if($course_category==""){
				$course_category = wp_create_category( $post->post_title );
				add_post_meta($post->ID, "course_category_id", $course_category, true);
			}			
			array_push($categories, $course_category);
			
			$quiz_category = get_post_meta($post->ID, "course_module_quizzes", true);
			if($quiz_category==""){
				$quiz_category = wp_create_category( "Quizzes", $course_category );
				add_post_meta($post->ID, "course_module_quizzes", $quiz_category, true);
			}
			array_push($categories, $quiz_category);
			
			return $categories;
			
		}
		
		private function get_quiz_questions($post){
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
				
			$quiz = new InstructureCanvasAPI\Courses\Quizzes\Quizzes();
			$quiz->setAPI($API);
			$quiz->setCourseID(get_post_meta($post->ID, "courseID", true));
			$quiz_data = $quiz->getQuizQuestions($_POST['item']); 
			
			return $quiz_data->content;
		}
		
		function get_quiz($post){
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
			
			$quizzes = new InstructureCanvasAPI\Courses\Quizzes\Quizzes();
			$quizzes->setAPI($API);
			$quizzes->setCourseID(get_post_meta($post->ID, "courseID", true));
			$data = $quizzes->getQuiz($_POST['item']);
			$quizzes = $data->content;
			$loader->unregister();
			return $quizzes;
		}
	
		function quiz_import(){
			if(wp_verify_nonce($_POST['nonce'], "molie_admin_quiz"))
			{
			
				$post = get_post($_POST['course_post']);
				
				$categories = $this->create_categories($post);
				
				$quiz = $this->get_quiz($post);
				
				print_r($quiz);
				
				if(get_post_meta($post->ID, "canvasQuiz_" . $quiz->id, true)==""){
								
					$quiz_post = wp_insert_post(
												array(
													"post_type" => 'linkedcanvasquiz',
													"post_status" => 'publish',
													"post_title" => $quiz->title,
													"post_content" => $quiz->description,
													"post_author" => get_current_user_id()
												)
											);
					
					wp_set_post_categories($quiz_post, $categories);
											
					update_post_meta($quiz_post, "CanvasCourse", get_post_meta($post->ID, "courseID", true), true);
					update_post_meta($quiz_post, "canvasQuizURL", $quiz->html_url, true);
					update_post_meta($post->ID, "canvasQuiz_" . $quiz->id, $quiz_post, true);
					
				}
				else
				{
					$quiz_post = get_post_meta($post->ID, "canvasQuiz_" . $quiz->id, true);
				}
	
				$questions = $this->get_quiz_questions($post);
				
				foreach($questions as $question){
				
					if(get_post_meta($quiz_post, "canvasQuizQuestion_" . $question->id, true)==""){
				
						$question_post = wp_insert_post(
												array(
													"post_type" => 'linkedcanvasqa',
													"post_status" => 'publish',
													"post_title" => $quiz->title . " " . $question->question_name,
													"post_content" => $question->question_text,
													"post_author" => get_current_user_id()
												),
												true
											);
						
						wp_set_post_categories($question_post, $categories);
						
						update_post_meta($question_post, "CanvasCourse", get_post_meta($post->ID, "courseID", true), true);
						update_post_meta($question_post, "canvasQuiz", $question->quiz_id, true);
						update_post_meta($question_post, "canvasQuizWPPost", $quiz_post, true);
						update_post_meta($question_post, "canvasQuestion_id", $question->id, true);
						update_post_meta($question_post, "canvasQuestion_position", $question->position, true);
						update_post_meta($quiz_post, "canvasQuizQuestion_" . $question->id, $question_post ,true);
						
						if($question->question_type=="multiple_choice_question"){
							$counter = 1;
							foreach($question->answers as $answer){
								print_r($answer);
								update_post_meta($question_post, "qa_id_" . $counter, $answer->id, true);
								update_post_meta($question_post, "qa_weight_" . $counter, $answer->weight, true);
								update_post_meta($question_post, "qa_answer_" . $counter, $answer->text, true);
								update_post_meta($question_post, "qa_feedback_" . $counter, $answer->comments, true);
								$counter++;
							}
						}
					
					}
				
				}
				
				echo __("Quiz linked");
			
			}
			else
			{
				print_r($_POST);
				echo "Nonce failed";
			}
			wp_die();
		}	
	
	}
	
	$MOLIEquizAjax = new MOLIEquizAjax();
	