<?PHP

	class MOLIEyourCourses{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
		}
	
		function menu_create(){
			add_submenu_page( "molie_mgmt", __("Your courses"), __("Your courses"), 'edit_linkedcanvascourse', "molie_your_course", array($this,"your_course"));
		}
		
		function your_course(){
			
			if(isset($_POST['course_id'])){
			
				$course_post = get_post($_POST['course_id']);
				$course_id = get_post_meta($_POST['course_id'],"courseID",true);
				
				?><h1><?PHP echo $course_post->post_title; ?></h1><?PHP
				?><p><a target="_blank" href="<?PHP echo admin_url("post.php?action=edit&post=" . $_POST['course_id'] ); ?>"><?PHP echo __("Edit course settings"); ?></a></p><?PHP
				
				global $wpdb;
				$data = $wpdb->get_results('select ' . $wpdb->prefix . 'posts.ID, post_title, meta_key, meta_value from ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta where post_type = "post" and ID = post_id and meta_key = "CanvasCourse" and meta_value = ' . $course_id);
				$admin_url = admin_url("edit.php?course=" . $course_id . "&canvas_linked=true");
				
				?><p><?PHP echo count($data) . " " . __("course pages linked"); ?> <a href="<?PHP echo $admin_url; ?>"><?PHP echo __("See pages in this course"); ?></a></p><?PHP
				
				$data = $wpdb->get_results('select ' . $wpdb->prefix . 'posts.ID, post_title, meta_key, meta_value from ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta where post_type = "attachment" and ID = post_id and meta_key = "CanvasCourse" and meta_value = ' . $course_id);
				
				$active_blog = get_active_blog_for_user( get_current_user_id() );
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
					?>
						<form method="post" action=''>
							<p><?PHP echo $course->post_title; ?></p>
							<input type="hidden" name="molie-calendar-nonce" value="<?PHP echo $nonce; ?>"/>
							<input name="course_id" type="hidden" value="<?PHP echo $course->ID; ?>" />
							<input type="submit" value="<?PHP echo __("Show course"); ?>" />
						</form>
					<?PHP
					}
					
				}else{
					?><p><?PHP echo __("No courses created yet"); ?></p><?PHP
				}
				
			}

			$admin_url = admin_url("wp-admin/edit.php?post_type=linkedcanvascourse");
			
			?><p><a target="_blank" href="<?PHP echo $admin_url; ?>"><?PHP echo __("Course Management Page in WordPress"); ?></a></p><?PHP
		}
	
	}
	
	$MOLIEyourCourses = new MOLIEyourCourses();
	