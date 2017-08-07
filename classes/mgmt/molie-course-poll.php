<?PHP
	class MOLIEcoursePoll{
	
		function __construct(){
			if(is_admin()){
				add_action("admin_notices", array($this, "pre"));
			 	add_action("admin_enqueue_scripts", array($this, "admin_scripts_and_styles"));
				add_action( 'wp_dashboard_setup', array($this, 'add_dashboard_widget') );
			}
		}
	
		function add_dashboard_widget() {
			wp_add_dashboard_widget(
                 'molie_dashboard',     
                 __('MOLIE Courses'),     
                 array($this, 'dashboard_widget')
			);
		}
		
		function dashboard_widget() {
			?><p><?PHP echo __("Checking your courses"); ?></p><?PHP
			
			$args = array(
				"post_type" => "linkedcanvascourse",
				"post_status" => "publish"
			);
	
			$courses = get_posts($args);
	
			if(count($courses)!=0){
			
				foreach($courses as $course){
					echo "<div><h3>" . $course->post_title . " <span id='moliecheck_" . $course->ID . "'></span></h3></div>";
					?><script>molie_admin_check_diff_dashboard(<?PHP echo $course->ID; ?>,'<?PHP echo admin_url("admin.php?page=molie_course_check"); ?>', '#moliecheck_<?PHP echo $course->ID; ?>');</script><?PHP
				}
				
			}
				
		}
		
		function admin_scripts_and_styles(){
			wp_enqueue_script( 'molie-admin-check-diff', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-check-diff.js', array( 'jquery' ) );
			wp_localize_script( 'molie-admin-check-diff', 'molie_admin_check_diff', 
																			array( 
																					'ajaxURL' => network_site_url() . "/wp-admin/admin-ajax.php",
																					'nonce' => wp_create_nonce("molie_admin_check")
																				) 
			);
		}
		
		function pre(){
		
			$time = get_option("molie_course_poll");
			
			$check = false;
			
			if($time==""){
				$check = true;
			}else{
				if(($time + 3600) < time()){
					$check = true;
				}
			}
			
			if($check){
			
				update_option("molie_course_poll", time());
		
				$args = array(
						"post_type" => "linkedcanvascourse",
						"post_status" => "publish"
					);
		
				$courses = get_posts($args);
		
				if(count($courses)!=0){
				
					foreach($courses as $course){
						?><script>molie_admin_check_diff_course(<?PHP echo $course->ID; ?>,'<?PHP echo $course->post_title; ?>','<?PHP echo admin_url("admin.php?page=molie_course_check"); ?>');</script><?PHP
					}
					
				}
				echo "<div id='moliepollcheck'></div>";	
				
			}
	
		}
	
	}
	
	$MOLIEcoursePoll = new MOLIEcoursePoll();
	