<?PHP

	class MOLIEcourseStatus{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action("admin_enqueue_scripts", array($this, "admin_scripts_and_styles"));
		}
	
		function admin_scripts_and_styles(){
			if(isset($_GET['page'])){
				if($_GET['page']=="molie_course_check"){
					wp_register_style( 'molie_admin_choose_css', plugins_url() . '/molie-instructure-canvas-linking-tool/css/molie-admin-choose.css', false, '1.0.0' );
					wp_enqueue_style( 'molie_admin_choose_css' );
					wp_enqueue_script( 'molie-admin-check', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-check.js', array( 'jquery' ) );
					wp_localize_script( 'molie-admin-check', 'molie_admin_check', 
																					array( 
																							'ajaxURL' => network_site_url() . "/wp-admin/admin-ajax.php",
																							'nonce' => wp_create_nonce("molie_admin_check")
																						) 
					);
				}
			}
		}
	
		function menu_create(){
			add_submenu_page( "molie_mgmt", __("Check course"), __("Check course"), 'edit_linkedcanvascourse', "molie_course_check", array($this,"course_check"));
		}
		
		function course_check(){
			
			if(isset($_GET['course_id'])){
			
				$course_post = get_post($_GET['course_id']);
				$course_id = get_post_meta($_GET['course_id'],"courseID",true);
				echo "<h1>" . __("Checking") . " " . $course_post->post_title . "</h1>";
				
				global $wpdb;
				
				$pages_data = $wpdb->get_results('select ' . $wpdb->prefix . 'posts.ID, post_title, meta_key, meta_value from ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta where post_type = "post" and ID = post_id and meta_key = "CanvasCourse" and meta_value = ' . $course_id);
				
				$files_data = $wpdb->get_results('select ' . $wpdb->prefix . 'posts.ID, post_title, meta_key, meta_value from ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta where post_type = "attachment" and ID = post_id and meta_key = "CanvasCourse" and meta_value = ' . $course_id);
				
				$users_data = $wpdb->get_results('select ' . $wpdb->prefix . 'posts.ID, post_title, meta_key, meta_value from ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta where post_type = "linkedcanvasuser" and ID = post_id and meta_key = "CanvasCourse" and meta_value = ' . $course_id);
				
				$quiz_data = $wpdb->get_results('select ' . $wpdb->prefix . 'posts.ID, post_title, meta_key, meta_value from ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta where post_type = "linkedcanvasquiz" and ID = post_id and meta_key = "CanvasCourse" and meta_value = ' . $course_id);
				
				$discussion_data = $wpdb->get_results('select ' . $wpdb->prefix . 'posts.ID, post_title, meta_key, meta_value from ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta where post_type = "linkedcanvasdis" and ID = post_id and meta_key = "CanvasCourse" and meta_value = ' . $course_id);
				
				$assignment_data = $wpdb->get_results('select ' . $wpdb->prefix . 'posts.ID, post_title, meta_key, meta_value from ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta where post_type = "linkedcanvasamt" and ID = post_id and meta_key = "CanvasCourse" and meta_value = ' . $course_id);
		
				echo "<div id='molie_pages'><p>" . count($pages_data) . " " . __("pages being checked") . " <span id='pagesprogress'></span></p><div><ul id='pagesissues'></ul></div></div>";
				echo "<div id='molie_files'><p>" . count($files_data) . " " . __("files being checked") . " <span id='filesprogress'></span></p><div><ul id='filesissues'></ul></div></div>";
				echo "<div id='molie_assignments'><p>" . count($assignment_data) . " " . __("assignments being checked") . " <span id='asgmtsprogress'></span></p><div><ul id='assignissues'></ul></div></div>";
				echo "<div id='molie_quiz'><p>" . count($quiz_data) . " " . __("quizzes being checked") . " <span id='quizprogress'></span></p><div><ul id='quizissues'></ul></div></div>";
				echo "<div id='molie_discussions'><p>" . count($discussion_data) . " " . __("discussions being checked") . " <span id='disprogress'></span></p><div><ul id='disissues'></ul></div></div>";
				echo "<div id='molie_roster'><p>" . count($users_data) . " " . __("users being checked") . " <span id='rosterprogress'></span></p><div><ul id='rosterissues'></ul></div></div>";
				echo "<div id='molie_calendar'><p>" . __("Calendar being checked") . " <span id='calendarprogress'></span></p><div><ul id='calendarissues'></ul></div></div>";
				
				$form_url = admin_url("wp-admin/admin.php");
				
				echo "<script type='text/javascript' language='javascript'>molie_admin_check_course(" . $_GET['course_id'] . ",'" . $form_url . "');</script>";
				
			}else{
				$args = array(
					"post_type" => "linkedcanvascourse",
					"post_status" => "publish"
				);
		
				$courses = get_posts($args);
		
				if(count($courses)!=0){
				
					echo "<h2>" . __("Choose a course") . "</h2>";
				
					$nonce = wp_create_nonce("molie-course-nonce");
					foreach($courses as $course){
						$form_url = admin_url("wp-admin/admin.php");
					?>
						<form method="GET" action='<?PHP echo $form_url; ?>'>
							<p><?PHP echo $course->post_title; ?></p>
							<input type="hidden" name="page" value="molie_course_check"/>
							<input type="hidden" name="molie-calendar-nonce" value="<?PHP echo $nonce; ?>"/>
							<input name="course_id" type="hidden" value="<?PHP echo $course->ID; ?>" />
							<input type="submit" value="<?PHP echo __("Check course"); ?>" />
						</form>
					<?PHP
					}
					
				}else{
					?><p><?PHP echo __("No courses created yet"); ?></p><?PHP
				}
				
			}
			
			$mgmt_url = admin_url("edit.php?post_type=linkedcanvascourse");
			
			?><p><a href="<?PHP echo $mgmt_url; ?>"><?PHP echo __("Course Management Page in WordPress"); ?></a></p><?PHP
		}
	
	}
	
	$MOLIEcourseStatus = new MOLIEcourseStatus();
	