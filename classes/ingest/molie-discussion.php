<?PHP

	class MOLIEdiscussion{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action("admin_enqueue_scripts", array($this, "admin_scripts_and_styles"));
		}
	
		function admin_scripts_and_styles(){
			if(isset($_GET['page'])){
				if($_GET['page']=="molie_discussions"){
					wp_register_style( 'molie_admin_discussion_css', plugins_url() . '/molie-instructure-canvas-linking-tool/css/molie-admin-discussion.css', false, '1.0.0' );
					wp_enqueue_style( 'molie_admin_discussion_css' );
					wp_enqueue_script( 'molie-admin-select', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-select.js', array( 'jquery' ) );
					wp_enqueue_script( 'molie-admin-discussion', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-discussion.js', array( 'jquery' ) );
					wp_localize_script( 'molie-admin-discussion', 'molie_admin_discussion', 
																					array( 
																							'ajaxURL' => admin_url("admin-ajax.php"),
																							'nonce' => wp_create_nonce("molie_admin_discussion")
																						) 
					);
				}
			}
		}
		
		function menu_create(){
			add_submenu_page( "molie_mgmt", __("Choose Course Discussions"), __("Choose Course Discussions"), 'edit_linkedcanvascourse', "molie_discussions", array($this,"discussion"));
		}
		
		function discussion(){
		
			if(isset($_POST['molie-discussion-nonce'])){
						
				if(wp_verify_nonce($_POST['molie-discussion-nonce'], "molie-discussion-nonce"))
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
					
					$discussions = new InstructureCanvasAPI\Courses\DiscussionTopics\DiscussionTopics();
					$discussions->setAPI($API);
					$discussions->setCourseID($course_id);
					$data = $discussions->getDiscussionTopics();
					if($data){
						if(count($data->content)!=0){	
							echo "<div id='molie_choose'>";
							echo "<h2>" . __("Discussions in this course") . "</h2>";
							echo "<div id='importProgress'><p><strong>" . __("Import Progress") . " <span id='importTotal'></span></strong></p><div id='importProgressBar'></div></div>";
							echo '<form id="molie_choose_form" action="javascript:function connect(){return false;};">';
							echo "<input type='submit' id='molie_discussion_submit' value='" . __("Link discussions") . "' />";	
							echo "<input type='submit' id='molie_discussion_skip' value='" . __("Skip step") . "' />";
							echo "<p><span><a href='javascript:molie_select_all()'>" . __("Select All") . "</a></span> <span><a href='javascript:molie_unselect_all()'>" . __("Unselect All") . "</a></span></p>"; 
							echo "<ul>";
							foreach($data->content as $discussion){
								echo "<li>";
								if(get_post_meta($_POST['course_id'], "canvasDiscussion_" . $discussion->id, true)!=""){
									$link_checked = "";
									$link_info = __("discussion already linked");
								}else{
									$link_checked = "checked";
									$link_info = "";
								}
								echo "<input type='checkbox' " . $link_checked . " id='" . $discussion->id . "' course_post='" . $_POST['course_id'] . "'>" . $discussion->title . "<span id='update" . $discussion->id . "'>" . $link_info . "</span></li>";
							}
							echo "</ul>";
							echo "<input type='submit' id='molie_discussion_submit' value='" . __("Link discussions") . "' />";
							echo "</form>";
							echo "</div>";
							echo "<div id='molie_roster' style='display:none'>";
							
							echo '<form method="post" action="' . admin_url("admin.php?page=molie_roster") . '">';
							echo "<input name='course_id' type='hidden' value='" . $_POST['course_id'] . "' />";
							echo wp_nonce_field("molie-roster-nonce", "molie-roster-nonce");
							echo "<input type='submit' value='" . __("Link roster") . "' />";
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
				
					$nonce = wp_create_nonce("molie-discussion-nonce");
					foreach($courses as $course){
					?>
						<form method="post" action='<?PHP echo admin_url("admin.php?page=molie_discussions"); ?>'>
							<p><?PHP echo $course->post_title; ?></p>
							<input type="hidden" name="molie-discussion-nonce" value="<?PHP echo $nonce; ?>"/>
							<input type="hidden" name="url" value="<?PHP echo get_post_meta($course->ID, "courseURL", true); ?>" />
							<input type="hidden" name="token" value="<?PHP echo get_post_meta($course->ID, "courseToken", true); ?>" />
							<input name="course_id" type="hidden" value="<?PHP echo $course->ID; ?>" />
							<input type="submit" value="<?PHP echo __("Get Discussions"); ?>" />
						</form>
					<?PHP
					}
					
				}
			}
		}
	
	}
	
	$MOLIEdiscussion = new MOLIEdiscussion();
	