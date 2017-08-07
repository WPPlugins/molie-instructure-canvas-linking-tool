<?PHP

	class MOLIEchoose{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action("admin_enqueue_scripts", array($this, "admin_scripts_and_styles"));
		}
	
		function admin_scripts_and_styles(){
			if(isset($_GET['page'])){
				if($_GET['page']=="molie_choose"){
					wp_register_style( 'molie_admin_choose_css', plugins_url() . '/molie-instructure-canvas-linking-tool/css/molie-admin-choose.css', false, '1.0.0' );
					wp_enqueue_style( 'molie_admin_choose_css' );
					wp_enqueue_script( 'molie-admin-select', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-select.js', array( 'jquery' ) );
					wp_enqueue_script( 'molie-admin-choose', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-choose.js', array( 'jquery' ) );
					wp_localize_script( 'molie-admin-choose', 'molie_admin_choose', 
																					array( 
																							'ajaxURL' => admin_url("admin-ajax.php"),
																							'nonce' => wp_create_nonce("molie_admin_choose")
																						) 
					);
				}
			}
		}
		
		function menu_create(){
			add_submenu_page( "molie_mgmt", __("Choose course Pages"), __("Choose Course Pages"), 'edit_linkedcanvascourse', "molie_choose", array($this,"choose"));
		}
		
		function choose(){

			if(isset($_POST['molie-link-nonce']))
			{
			
				if(wp_verify_nonce($_POST['molie-link-nonce'], "molie-link"))
				{

					$course_details = explode("|", $_POST['molie_course']);
					$posts = get_posts(array("post_type" => 'linkedcanvascourse'));
					$post_insert = true;
					foreach($posts as $post){
						if($post->post_title==$course_details[1]){
							$post_insert = false;
							$course = $post->ID;
						}
					}
					
					if($post_insert){
						$course = wp_insert_post(
													array(
														"post_type" => 'linkedcanvascourse',
														"post_status" => 'publish',
														"post_title" => $course_details[1],
														"post_author" => get_current_user_id()
													)
												);
						update_post_meta($course, "courseID", $course_details[0], true);
						update_post_meta($course, "courseToken", $_POST['token'], true);
						update_post_meta($course, "courseURL", $_POST['url'], true);
					}
					
					$urls = get_user_meta(get_current_user_id(), "courseLinkURLsTokens", true);
					
					if(!is_array($urls)){
						$urls = array();
					}
											
					if(!isset($urls[$_POST['url']])){
						$urls[$_POST['url']] = array($_POST['token']);
					}else{
						$insert = true;
						foreach($urls[$_POST['url']] as $token){
							if($token == $_POST['token']){
								$insert = false; 
							}
						}
						if($insert){
							$urls[$_POST['url']][] = $_POST['token'];
						}
					}
					
					update_user_meta(get_current_user_id(), "courseLinkURLsTokens", $urls);
					
					require_once(__DIR__ . "/../../API/Psr4AutoloaderClass.php");
					$loader = new Psr4AutoloaderClass;
					$loader->register();
					$loader->addNamespace('InstructureCanvasAPI', __DIR__ . "/../../API/InstructureCanvasAPI/src");
						
					$API = new InstructureCanvasAPI\InstructureCanvasAPI( 
																		array(
																			"site" => $_POST['url'],
																			"token" => $_POST['token'],
																			"webService" => "CURL"
																		)
																	);
					
					$modules = new InstructureCanvasAPI\Courses\Modules\Modules();
					$modules->setAPI($API);
					$modules->setCourseID($course_details[0]);
					$data = $modules->getModules($course_details[0]);
					if($data){
						if(count($data->content)!=0){
							$module_pages = array();
							echo "<div id='molie_choose'>";
							echo "<h2>" . __("Modules and Pages in this course") . "</h2>";
							echo "<p>" . __("Select the pages you'd like to import") . "</p>";
							echo "<div id='importProgress'><p><strong>" . __("Import Progress") . " <span id='importTotal'></span></strong></p><div id='importProgressBar'></div></div>";
							echo '<form id="molie_choose_form" action="javascript:function connect(){return false;};">';
							echo "<input type='submit' id='molie_choose_submit' value='" . __("Import pages") . "' />";	
							echo "<input type='submit' id='molie_choose_skip' value='" . __("Skip step") . "' />";	
							echo "<p><span><a href='javascript:molie_select_all()'>" . __("Select All") . "</a></span> <span><a href='javascript:molie_unselect_all()'>" . __("Unselect All") . "</a></span></p>"; 
							echo "<p>" . __("Course syllabus") . "</p>";
							echo "<ul>";
							$link_info = "";
							$link_checked = "";
							if($this->linked($course, "course_" . $course . "_course-syllabus")){
								$link_info = "<span class='pageLinked'>" . __("Page already linked - importing will override") . "</span>";
								$link_checked = " override='true' ";
							}else{
								$link_checked = "checked";
							}
							echo "<li><input " . $link_checked . " course='" . $course . "' type='checkbox' id='course-syllabus'/>" . __("Course Syllabus") . " " . $link_info . "</li>";
							echo "</ul>";
							echo "<p>" . __("Course home page") . "</p>";
							echo "<ul>";
							$link_info = "";
							$link_checked = "";
							if($this->linked($course, "course_" . $course . "_course-home-page")){
								$link_info = "<span class='pageLinked'>" . __("Page already linked - importing will override") . "</span>";
								$link_checked = " override='true' ";
							}else{
								$link_checked = "checked";
							}
							echo "<li><input " . $link_checked . " course='" . $course . "' type='checkbox' id='course-home-page'/>" . __("Course Home Page") . " " . $link_info . "</li>";
							echo "</ul>";
							foreach($data->content as $module){
								echo "<p>" . __("Module") . " : " . $module->name . "</p>";
								echo "<ul>";
								$moduleItems = $modules->getModuleItems($module->id);
								foreach($moduleItems->content as $item){
									$link_info = "";
									$link_checked = "";
									if($this->linked($course, "course_" . $course . "_" . $item->page_url)){
										$link_info = "<span class='pageLinked'>" . __("Page already linked - importing will override") . "</span>";
										$link_checked = " override='true' ";
									}else{
										$link_checked = "checked";
									}
									array_push($module_pages, $item->page_url);
									echo "<li><input " . $link_checked . " course='" . $course . "' module_name='" . addslashes($module->name) . "' module='" . $module->id . "' type='checkbox' id='" . $item->id . "'/>" . $item->title . " " . $link_info . "</li>";
								}
								echo "</ul>";
							}
							
							$pages = new InstructureCanvasAPI\Courses\Pages\Pages();
							$pages->setAPI($API);
							$pages->setCourseID($course_details[0]);
							$data = $pages->getPages();
							
							echo "<p>" . __("Pages outside modules") . "</p>";
							echo "<ul>";
							foreach($data->content as $item){
								if($item->front_page!=1){
									if(!in_array($item->url, $module_pages)){
										$link_info = "";
										$link_checked = "";
										if($this->linked($course, "course_" . $course . "_" . $item->url)){
											$link_info = "<span class='pageLinked'>" . __("Page already linked - importing will override") . "</span>";
											$link_checked = " override='true' ";
										}else{
											$link_checked = "checked";
										}
										echo "<li><input " . $link_checked . " course='" . $course . "' type='checkbox' id='" . $item->url . "'/>" . $item->title . " " . $link_info . "</li>";
									}
								}
							}
							echo "</ul>";
							
							echo "<input type='submit' id='molie_choose_submit' value='" . __("Import pages") . "' />";
							echo "</form>";
							echo "</div>";
							echo "<div id='molie_files' style='display:none'>";
							echo "<p>" . __("Page Linking complete") . "</p>";
							
							?><form method="post" action='<?PHP echo admin_url("admin.php?page=molie_files"); ?>'><?PHP
							$nonce = wp_create_nonce("molie-files");
							echo '<input type="hidden" name="molie-files-nonce" value="' . $nonce . '"/>';
							echo "<input type='hidden' name='course_ID' value='" . $course . "' />";
							echo "<p><input type='submit' value='" . __("Now, lets connect files") . "' /></p>";
							echo "</div>";
						}
					}
					$loader->unregister();
				}
				else
				{
					print_r($_POST);
					echo "nonce failed";
				}
			}
			else
			{
				$args = array(
					"post_type" => "linkedcanvascourse",
					"post_status" => "publish"
				);
		
				$courses = get_posts($args);
		
				if(count($courses)!=0){
				
					echo "<h2>" . __("Choose a course") . "</h2>";
					$args = array(
						"post_type" => "linkedcanvascourse",
						"post_status" => "publish"
					);
		
					$courses = get_posts($args);
			
					if(count($courses)!=0){
				
						$nonce = wp_create_nonce("molie-link");
						foreach($courses as $course){
							?><form method="post" action='<?PHP echo admin_url("admin.php?page=molie_choose"); ?>'>
								<p><?PHP echo $course->post_title; ?></p>
								<input type="hidden" name="molie-link-nonce" value="<?PHP echo $nonce; ?>"/>
								<input type="hidden" name="molie_course" value="<?PHP echo get_post_meta($course->ID, "courseID", true) . "|" . $course->post_title; ?>" />
								<input type="hidden" name="url" value="<?PHP echo get_post_meta($course->ID, "courseURL", true); ?>" />
								<input type="hidden" name="token" value="<?PHP echo get_post_meta($course->ID, "courseToken", true); ?>" />
								<input name="course" type="hidden" value="<?PHP echo get_post_meta($course->ID, "courseID", true); ?>" />
								<input type="submit" value="<?PHP echo __("Choose course pages"); ?>" />
							</form>
						<?PHP
						
						}
						
					}
					
				}
			}
		}	
		
		private function linked($course, $item_id){
			global $wpdb;	
			$results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'postmeta WHERE post_id = ' . $course . ' AND meta_key = "' . $item_id . '"', OBJECT );	
			if(count($results)!=0){
				return true;
			}
			else
			{
				return false;
			}
		}
	
	}
	
	$MOLIEchoose = new MOLIEchoose();
	