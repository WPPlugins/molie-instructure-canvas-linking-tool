<?PHP

	class MOLIEcalendar{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action("admin_enqueue_scripts", array($this, "admin_scripts_and_styles"));
		}
	
		function admin_scripts_and_styles(){
			if(isset($_GET['page'])){
				if($_GET['page']=="molie_roster"){
					wp_register_style( 'molie_admin_calendar_css', plugins_url() . '/molie-instructure-canvas-linking-tool/css/molie-admin-roster.css', false, '1.0.0' );
					wp_enqueue_script( 'molie-admin-select', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-select.js', array( 'jquery' ) );
					wp_enqueue_style( 'molie_admin_calendar_css' );
				}
			}
		}
		
		function menu_create(){
			add_submenu_page( "molie_mgmt", __("Choose Course Calendar"), __("Choose Course Calendar"), 'edit_linkedcanvascourse', "molie_calendar", array($this,"calendar"));
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
		
		function calendar(){
		
			if(isset($_POST['molie-calendar-nonce'])){
						
				if(wp_verify_nonce($_POST['molie-calendar-nonce'], "molie-calendar-nonce"))
				{
				
					$course_id = get_post_meta($_POST['course_id'], "courseID", true);
					$course_token = get_post_meta($_POST['course_id'], "courseToken", true);
					$course_url = get_post_meta($_POST['course_id'], "courseURL", true);
					
					$categories = $this->create_categories(get_post($_POST['course_id']));
						
					if(get_post_meta($_POST['course_id'], "canvasCalendarPage", true)==""){
						$calendar_post = wp_insert_post(
												array(
													"post_type" => 'post',
													"post_status" => 'publish',
													"post_title" => __("Course Calendar"),
													"post_author" => get_current_user_id()
												)
											);
											
						wp_set_post_categories($roster_post, $categories);
											
						update_post_meta($calendar_post, "CanvasCalendar", "true", true);
						update_post_meta($calendar_post, "CanvasCourse", $course_id, true);
						update_post_meta($_POST['course_id'], "canvasCalendarPage", $calendar_post, true);
					}else{
						$calendar_post = get_post_meta($_POST['course_id'], "canvasCalendarPage", true);
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
					
					$calendar = new InstructureCanvasAPI\Courses\Courses();
					$calendar->setAPI($API);
					$data = $calendar->getCoursesForThisUserWithSyllabus();
					foreach($data->content as $course_data){
						if($course_data->id==$course_id){
							$data = $course_data;
						}
					}
					$calendar = file_get_contents($data->calendar->ics);
					$calendar = explode("\n", $calendar);
					$parse = false;
					$content = array();
					$post_content = "";
					for($x=0;$x<=count($calendar);$x++){
						$line = trim(str_replace("\n","",$calendar[$x]));
						if($line=="BEGIN:VEVENT"){
							$parse = true;
						}
						if($parse){
							if($line=="END:VEVENT"){
								$post_content = $content['date'] . $content['summary'] . $content['location'] . $content['link'];
								$parse = false;
							}
							$parts = explode(":",$line);
							if(count($parts)!=1){
								if($parts[0]=="URL"){
									$data = str_replace("\n","",str_replace("\r","",str_replace($parts[0] . ":","",$line) . trim($calendar[$x+1])));
								}else{
									$data = $parts[1];
								}
								if($parts[0]=="LOCATION"){
									$content['location']= "<p>Location " . $data . "</p>";
								}
								if($parts[0]=="SUMMARY"){
									$content['summary'] = "<p>Summary " . $data . "</p>";
								}
								if($parts[0]=="URL"){
									$content['link'] = "<p>Link <a href='" . $data . "'>" . $data . "</a></p>";
								}
								if($parts[0]=="DTSTART"){
									if(strpos($data,"=")==FALSE){
										$time = mktime(substr($data,10,2),substr($data,12,2),substr($data,14,2),substr($data,4,2),substr($data,6,2),substr($data,0,4));
									}
									$content['date'] = "<p>Date " . date("l, jS F Y G:i:s A", $time) . "</p>";
								}
								if($parts[0]=="DTSTART;VALUE=DATE"){
									$time = mktime(0,0,0,substr($data,4,2),substr($data,6,2),substr($data,0,4));
									$content['date'] = "<p>Date " . date("l, jS F Y G:i:s A", $time) . "</p>";
								}
							}
						}
					}
					wp_update_post(
						array(
							"ID" => $calendar_post,
							"post_content" => $post_content,
						)	
					);
					$loader->unregister();
					echo "<h1>" . __("Calendar linking") . "</h1>";
					echo "<p>" . __("Calendar linked") . "</p>";
					echo "<h2>" . __("Linking complete") . "</h2>";
					
					global $wpdb;
					$data = $wpdb->get_results('select ' . $wpdb->prefix . 'posts.ID, post_title, meta_key, meta_value from ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta where post_type = "post" and ID = post_id and meta_key = "CanvasCourse" and meta_value = ' . $course_id);
					
					$admin_url = admin_url("edit.php?course=" . $course_id . "&canvas_linked=true");
					
					?><p><?PHP echo count($data) . " " . __("course pages linked"); ?> <a href="<?PHP echo $admin_url; ?>"><?PHP echo __("See pages in this course"); ?></a></p><?PHP
					
					$data = $wpdb->get_results('select ' . $wpdb->prefix . 'posts.ID, post_title, meta_key, meta_value from ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta where post_type = "attachment" and ID = post_id and meta_key = "CanvasCourse" and meta_value = ' . $course_id);
					
					$admin_url = admin_url('admin.php?page=molie_media_mgmt&course_id=' . $course_id);
					
					?><p><?PHP echo count($data) . " " . __("course files linked"); ?> <a href="<?PHP echo $admin_url; ?>"><?PHP echo __("See media in this course"); ?></a></p><?PHP
					
					$data = $wpdb->get_results('select ' . $wpdb->prefix . 'posts.ID, post_title, meta_key, meta_value from ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta where post_type = "linkedcanvasuser" and ID = post_id and meta_key = "CanvasCourse" and meta_value = ' . $course_id);
					$admin_url = admin_url("edit.php?post_type=linkedcanvasuser&course=" . $course_id . "&canvas_linked=true");
					
					?><p><?PHP echo count($data) . " " . __("users linked"); ?> <a href="<?PHP echo $admin_url; ?>"><?PHP echo __("See users in this course"); ?></a></p><?PHP
					
					$data = $wpdb->get_results('select ' . $wpdb->prefix . 'posts.ID, post_title, meta_key, meta_value from ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta where post_type = "linkedcanvasquiz" and ID = post_id and meta_key = "CanvasCourse" and meta_value = ' . $course_id);
					$admin_url = admin_url("edit.php?post_type=linkedcanvasquiz&course=" . $course_id . "&canvas_linked=true");
					
					?><p><?PHP echo count($data) . " " . __("quizzes linked"); ?> <a href="<?PHP echo $admin_url; ?>"><?PHP echo __("See quizzes in this course"); ?></a></p><?PHP
			
					$data = $wpdb->get_results('select ' . $wpdb->prefix . 'posts.ID, post_title, meta_key, meta_value from ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta where post_type = "linkedcanvasamt" and ID = post_id and meta_key = "CanvasCourse" and meta_value = ' . $course_id);
					$admin_url = admin_url("edit.php?post_type=linkedcanvasamt&course=" . $course_id . "&canvas_linked=true");
					
					?><p><?PHP echo count($data) . " " . __("assignments linked"); ?> <a href="<?PHP echo $admin_url; ?>"><?PHP echo __("See assignments in this course"); ?></a></p><?PHP
			
					$data = $wpdb->get_results('select ' . $wpdb->prefix . 'posts.ID, post_title, meta_key, meta_value from ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta where post_type = "linkedcanvasdis" and ID = post_id and meta_key = "CanvasCourse" and meta_value = ' . $course_id);
					$admin_url = admin_url("edit.php?post_type=linkedcanvasdis&course=" . $course_id . "&canvas_linked=true");
			
					?><p><?PHP echo count($data) . " " . __("discussions linked"); ?> <a href="<?PHP echo $admin_url; ?>"><?PHP echo __("See discussions in this course"); ?></a></p><?PHP
					
					$admin_url = admin_url("admin.php?page=molie_help");
			
					echo "<p>" . __("So now see the guidance on the tool") . " <a href='" . $admin_url . "'>" . __("MOLIE Help") . "</a></p>";
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
				
					$nonce = wp_create_nonce("molie-calendar-nonce");
					foreach($courses as $course){
					?>
						<form method="post" action='<?PHP echo admin_url("admin.php?page=molie_calendar"); ?>'>
							<p><?PHP echo $course->post_title; ?></p>
							<input type="hidden" name="molie-calendar-nonce" value="<?PHP echo $nonce; ?>"/>
							<input type="hidden" name="url" value="<?PHP echo get_post_meta($course->ID, "courseURL", true); ?>" />
							<input type="hidden" name="token" value="<?PHP echo get_post_meta($course->ID, "courseToken", true); ?>" />
							<input name="course_id" type="hidden" value="<?PHP echo $course->ID; ?>" />
							<input type="submit" value="<?PHP echo __("Get Calendar"); ?>" />
						</form>
					<?PHP
					}
					
				}
			}
		}
	
	}
	
	$MOLIEcalendar = new MOLIEcalendar();
	