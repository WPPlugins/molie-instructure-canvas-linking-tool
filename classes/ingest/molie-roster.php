<?PHP

	class MOLIEroster{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action("admin_enqueue_scripts", array($this, "admin_scripts_and_styles"));
		}
	
		function admin_scripts_and_styles(){
			if(isset($_GET['page'])){
				if($_GET['page']=="molie_roster"){
					wp_register_style( 'molie_admin_roster_css', plugins_url() . '/molie-instructure-canvas-linking-tool/css/molie-admin-roster.css', false, '1.0.0' );
					wp_enqueue_style( 'molie_admin_roster_css' );
					wp_enqueue_script( 'molie-admin-select', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-select.js', array( 'jquery' ) );
					wp_enqueue_script( 'molie-admin-roster', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-roster.js', array( 'jquery' ) );
					wp_localize_script( 'molie-admin-roster', 'molie_admin_roster', 
																					array( 
																							'ajaxURL' => admin_url("admin-ajax.php"),
																							'nonce' => wp_create_nonce("molie_admin_roster")
																						) 
					);
				}
			}
		}
		
		function menu_create(){
			add_submenu_page( "molie_mgmt", __("Choose Course Roster"), __("Choose Course Roster"), 'edit_linkedcanvascourse', "molie_roster", array($this,"roster"));
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
		
		function roster(){
		
			if(isset($_POST['molie-roster-nonce'])){
						
				if(wp_verify_nonce($_POST['molie-roster-nonce'], "molie-roster-nonce"))
				{
				
					$course_id = get_post_meta($_POST['course_id'], "courseID", true);
					$course_token = get_post_meta($_POST['course_id'], "courseToken", true);
					$course_url = get_post_meta($_POST['course_id'], "courseURL", true);
					
					$categories = $this->create_categories(get_post($_POST['course_id']));
						
					if(get_post_meta($_POST['course_id'], "canvasRosterPage", true)==""){
						$roster_post = wp_insert_post(
												array(
													"post_type" => 'post',
													"post_status" => 'publish',
													"post_title" => __("Course Roster"),
													"post_author" => get_current_user_id()
												)
											);
											
						wp_set_post_categories($roster_post, $categories);
											
						update_post_meta($roster_post, "CanvasRoster", "true", true);
						update_post_meta($roster_post, "CanvasCourse", $course_id, true);
						update_post_meta($_POST['course_id'], "canvasRosterPage", $roster_post, true);
					}
					
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
					
					$roster = new InstructureCanvasAPI\Courses\Users\Users();
					$roster->setAPI($API);
					$roster->setCourseID($course_id);
					$data = $roster->getUsers();
					if($data){
						if(count($data->content)!=0){	
							echo "<div id='molie_choose'>";
							echo "<h2>" . __("Course users") . "</h2>";
							echo "<div id='importProgress'><p><strong>" . __("Import Progress") . " <span id='importTotal'></span></strong></p><div id='importProgressBar'></div></div>";
							echo '<form id="molie_choose_form" action="javascript:function connect(){return false;};">';
							echo "<input type='submit' id='molie_roster_submit' value='" . __("Add to roster") . "' />";	
							echo "<input type='submit' id='molie_roster_skip' value='" . __("Skip step") . "' />";	
							echo "<p><span><a href='javascript:molie_select_all()'>" . __("Select All") . "</a></span> <span><a href='javascript:molie_unselect_all()'>" . __("Unselect All") . "</a></span></p>"; 
							echo "<ul>";
							foreach($data->content as $student){
								echo "<li>";
								if(get_post_meta($_POST['course_id'], "canvasStudent_" . $student->id, true)!=""){
									$link_checked = "";
									$link_info = __("Student already linked");
								}else{
									$link_checked = "checked";
									$link_info = "";
								}
								echo "<input type='checkbox' " . $link_checked . " id='" . $student->id . "' course_post='" . $_POST['course_id'] . "'>" . $student->short_name . "<span id='update" . $student->id . "'>" . $link_info . "</span></li>";
							}
							echo "</ul>";
							echo "<input type='submit' id='molie_roster_submit' value='" . __("Add to roster") . "' />";
							echo "</form>";
							echo "</div>";
							echo "<div id='molie_calendar' style='display:none'>";
							echo '<form method="post" action="' . admin_url("admin.php?page=molie_calendar") . '">';
							echo "<input name='course_id' type='hidden' value='" . $_POST['course_id'] . "' />";
							echo wp_nonce_field("molie-calendar-nonce", "molie-calendar-nonce");
							echo "<input type='submit' value='" . __("Link Calendar") . "' />";
							echo "</form>";
							echo "</div>";
						}
					}
					$loader->unregister();
				}else{
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
				
					$nonce = wp_create_nonce("molie-roster-nonce");
					foreach($courses as $course){
					?>
						<form method="post" action='<?PHP echo admin_url("admin.php?page=molie_roster"); ?>'>
							<p><?PHP echo $course->post_title; ?></p>
							<input type="hidden" name="molie-roster-nonce" value="<?PHP echo $nonce; ?>"/>
							<input type="hidden" name="url" value="<?PHP echo get_post_meta($course->ID, "courseURL", true); ?>" />
							<input type="hidden" name="token" value="<?PHP echo get_post_meta($course->ID, "courseToken", true); ?>" />
							<input name="course_id" type="hidden" value="<?PHP echo $course->ID; ?>" />
							<input type="submit" value="<?PHP echo __("Get Roster"); ?>" />
						</form>
					<?PHP
					}
					
				}
			}
		}
	
	}
	
	$MOLIEroster = new MOLIEroster();
	