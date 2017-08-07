<?PHP

	class MOLIEquiz{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action("admin_enqueue_scripts", array($this, "admin_scripts_and_styles"));
		}
	
		function admin_scripts_and_styles(){
			if(isset($_GET['page'])){
				if($_GET['page']=="molie_quiz"){
					wp_register_style( 'molie_admin_quiz_css', plugins_url() . '/molie-instructure-canvas-linking-tool/css/molie-admin-quiz.css', false, '1.0.0' );
					wp_enqueue_style( 'molie_admin_quiz_css' );
					wp_enqueue_script( 'molie-admin-select', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-select.js', array( 'jquery' ) );
					wp_enqueue_script( 'molie-admin-quiz', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-quiz.js', array( 'jquery' ) );
					wp_localize_script( 'molie-admin-quiz', 'molie_admin_quiz', 
																					array( 
																							'ajaxURL' => admin_url("admin-ajax.php"),
																							'nonce' => wp_create_nonce("molie_admin_quiz")
																						) 
					);
				}
			}
		}
		
		function menu_create(){
			add_submenu_page( "molie_mgmt", __("Choose Course quizzes"), __("Choose Course quizzes"), 'edit_linkedcanvascourse', "molie_quiz", array($this,"quiz"));
		}
		
		function quiz(){
			if(isset($_POST['molie-quiz-nonce'])){
						
				if(wp_verify_nonce($_POST['molie-quiz-nonce'], "molie-quiz-nonce"))
				{
				
					$course_id = get_post_meta($_POST['course_id'], "courseID", true);
					$course_token = get_post_meta($_POST['course_id'], "courseToken", true);
					$course_url = get_post_meta($_POST['course_id'], "courseURL", true);
					
					require_once(__DIR__ . "/../../API/Psr4AutoloaderClass.php");
					$loader = new Psr4AutoloaderClass;
					$loader->register();
					$loader->addNamespace('InstructureCanvasAPI', __DIR__ . "/../../API/InstructureCanvasAPI/src");
					
					$API = new InstructureCanvasAPI\InstructureCanvasAPI( 
																		array(
																			"site" => $course_url,
																			"token" => $course_token,
																			"webService" => "CURL"
																		)
																	);
					
					$quizzes = new InstructureCanvasAPI\Courses\Quizzes\Quizzes();
					$quizzes->setAPI($API);
					$quizzes->setCourseID($course_id);
					$data = $quizzes->getquizzes();
					
					if($data){
						if(count($data->content)!=0){	
							echo "<div id='molie_choose'>";
							echo "<h2>" . __("quizzes in this course") . "</h2>";
							echo "<div id='importProgress'><p><strong>" . __("Import Progress") . " <span id='importTotal'></span></strong></p><div id='importProgressBar'></div></div>";
							echo '<form id="molie_choose_form" action="javascript:function connect(){return false;};">';
							echo "<input type='submit' id='molie_quiz_submit' value='" . __("Link quizzes") . "' />";	
							echo "<input type='submit' id='molie_quiz_skip' value='" . __("Skip step") . "' />";	
							echo "<p><span><a href='javascript:molie_select_all()'>" . __("Select All") . "</a></span> <span><a href='javascript:molie_unselect_all()'>" . __("Unselect All") . "</a></span></p>"; 
							echo "<ul>";
							foreach($data->content as $quiz){
								echo "<li>";
								if(get_post_meta($_POST['course_id'], "canvasQuiz_" . $quiz->id, true)!=""){
									$link_checked = "";
									$link_info = __("quiz already linked");
								}else{
									$link_checked = "checked";
									$link_info = "";
								}
								echo "<input type='checkbox' " . $link_checked . " id='" . $quiz->id . "' course_post='" . $_POST['course_id'] . "'>" . $quiz->title . "<span id='update" . $quiz->id . "'>" . $link_info . "</span></li>";
							}
							echo "</ul>";
							echo "<input type='submit' id='molie_quiz_submit' value='" . __("Link quizzes") . "' />";
							echo "</form>";
							echo "</div>";
							echo "<div id='molie_quiz_assignments' style='display:none'>";
							echo '<form method="post" action="' . admin_url("admin.php?page=molie_assignments") . '">';
							echo "<input name='course_id' type='hidden' value='" . $_POST['course_id'] . "' />";
							echo wp_nonce_field("molie-assignment-nonce", "molie-assignment-nonce");
							echo "<input type='submit' value='" . __("Now, Assignments") . "' />";
							echo "</form>";
							echo "</div>";
						}
					}
					$loader->unregister();
				}else{
				}
			}else{
			
				$args = array(
					"post_type" => "linkedcanvascourse",
					"post_status" => "publish"
				);
		
				$courses = get_posts($args);
		
				if(count($courses)!=0){
				
					echo "<h2>" . __("Choose a course") . "</h2>";
				
					$nonce = wp_create_nonce("molie-quiz-nonce");
					foreach($courses as $course){
					?>
						<form method="post" action='<?PHP echo admin_url("admin.php?page=molie_quiz"); ?>'>
							<p><?PHP echo $course->post_title; ?></p>
							<input type="hidden" name="molie-quiz-nonce" value="<?PHP echo $nonce; ?>"/>
							<input type="hidden" name="url" value="<?PHP echo get_post_meta($course->ID, "courseURL", true); ?>" />
							<input type="hidden" name="token" value="<?PHP echo get_post_meta($course->ID, "courseToken", true); ?>" />
							<input name="course_id" type="hidden" value="<?PHP echo $course->ID; ?>" />
							<input type="submit" value="<?PHP echo __("Get Quizzes"); ?>" />
						</form>
					<?PHP
					}
					
				}
			}
		}
	
	}
	
	$MOLIEquiz = new MOLIEquiz();
	