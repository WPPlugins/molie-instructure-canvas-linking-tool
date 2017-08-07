<?PHP

	class MOLIEassignments{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action("admin_enqueue_scripts", array($this, "admin_scripts_and_styles"));
		}
	
		function admin_scripts_and_styles(){
			if(isset($_GET['page'])){
				if($_GET['page']=="molie_assignments"){
					wp_register_style( 'molie_admin_assignment_css', plugins_url() . '/molie-instructure-canvas-linking-tool/css/molie-admin-assignment.css', false, '1.0.0' );
					wp_enqueue_style( 'molie_admin_assignment_css' );
					wp_enqueue_script( 'molie-admin-select', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-select.js', array( 'jquery' ) );
					wp_enqueue_script( 'molie-admin-assignment', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-assignment.js', array( 'jquery' ) );
					wp_localize_script( 'molie-admin-assignment', 'molie_admin_assignment', 
																					array( 
																							'ajaxURL' => admin_url("admin-ajax.php"),
																							'nonce' => wp_create_nonce("molie_admin_assignment")
																						) 
					);
				}
			}
		}
		
		function menu_create(){
			add_submenu_page( "molie_mgmt", __("Choose Course Assignments"), __("Choose Course Assignments"), 'edit_linkedcanvascourse', "molie_assignments", array($this,"assignment"));
		}
		
		function assignment(){
			if(isset($_POST['molie-assignment-nonce'])){
						
				if(wp_verify_nonce($_POST['molie-assignment-nonce'], "molie-assignment-nonce"))
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
					
					$assignments = new InstructureCanvasAPI\Courses\Assignments\Assignments();
					$assignments->setAPI($API);
					$assignments->setCourseID($course_id);
					$data = $assignments->getAssignments();
					if($data){
						if(count($data->content)!=0){	
							echo "<div id='molie_choose'>";
							echo "<h2>" . __("Assignments in this course") . "</h2>";
							echo "<div id='importProgress'><p><strong>" . __("Import Progress") . " <span id='importTotal'></span></strong></p><div id='importProgressBar'></div></div>";
							echo '<form id="molie_choose_form" action="javascript:function connect(){return false;};">';
							echo "<input type='submit' id='molie_assignment_submit' value='" . __("Link Assignments") . "' />";	
							echo "<input type='submit' id='molie_assignment_skip' value='" . __("Skip step") . "' />";
							echo "<p><span><a href='javascript:molie_select_all()'>" . __("Select All") . "</a></span> <span><a href='javascript:molie_unselect_all()'>" . __("Unselect All") . "</a></span></p>"; 
							echo "<ul>";
							foreach($data->content as $quiz){
								echo "<li>";
								if(get_post_meta($_POST['course_id'], "canvasQuiz_" . $quiz->id, true)!=""){
									$link_checked = "";
									$link_info = __("Quiz already linked");
								}else{
									$link_checked = "checked";
									$link_info = "";
								}
								echo "<input type='checkbox' " . $link_checked . " id='" . $quiz->id . "' course_post='" . $_POST['course_id'] . "'>" . $quiz->name . "<span id='update" . $quiz->id . "'>" . $link_info . "</span></li>";
							}
							echo "</ul>";
							echo "<input type='submit' id='molie_assignment_submit' value='" . __("Link Assignments") . "' />";
							echo "</form>";
							echo "</div>";
							echo "<div id='molie_discussions' style='display:none'>";
							echo '<form method="post" action="' . admin_url("admin.php?page=molie_discussions") . '">';
							echo "<input name='course_id' type='hidden' value='" . $_POST['course_id'] . "' />";
							echo wp_nonce_field("molie-discussion-nonce", "molie-discussion-nonce");
							echo "<input type='submit' value='" . __("Now, Discussions") . "' />";
							echo "</form>";
							echo "</div>";
						}
					}
					$loader->unregister();
				}else{
					echo "HELLO";
					print_r($_POST);
				}
			}else{
			
				$args = array(
					"post_type" => "linkedcanvascourse",
					"post_status" => "publish"
				);
		
				$courses = get_posts($args);
		
				if(count($courses)!=0){
				
					echo "<h2>" . __("Choose a course") . "</h2>";
				
					$nonce = wp_create_nonce("molie-assignment-nonce");
					foreach($courses as $course){
					?>
						<form method="post" action='<?PHP echo admin_url("admin.php?page=molie_assignments");  ?>'>
							<p><?PHP echo $course->post_title; ?></p>
							<input type="hidden" name="molie-assignment-nonce" value="<?PHP echo $nonce; ?>"/>
							<input type="hidden" name="url" value="<?PHP echo get_post_meta($course->ID, "courseURL", true); ?>" />
							<input type="hidden" name="token" value="<?PHP echo get_post_meta($course->ID, "courseToken", true); ?>" />
							<input name="course_id" type="hidden" value="<?PHP echo $course->ID; ?>" />
							<input type="submit" value="<?PHP echo __("Get Assignments"); ?>" />
						</form>
					<?PHP
					}
					
				}
			}
		}
	
	}
	
	$MOLIEassignments = new MOLIEassignments();
	