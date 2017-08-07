<?PHP

	class MOLIElinkedPostEditor{
	
		function __construct(){
			add_action("admin_head", array($this, "metabox"));
			add_action("admin_enqueue_scripts", array($this, "admin_scripts_and_styles"));
		}
	
		function admin_scripts_and_styles(){
			wp_register_style( 'molie_admin_post_link_css', plugins_url() . '/molie-instructure-canvas-linking-tool/css/molie-admin-post-link.css', false, '1.0.0' );
			wp_enqueue_style( 'molie_admin_post_link_css' );
			wp_enqueue_script( 'molie-admin-post-course-place', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-post-course-place.js', array( 'jquery' ) );
			wp_localize_script( 'molie-admin-post-course-place', 'molie_admin_post_course_place', 
																			array( 
																					'ajaxURL' => network_site_url() . "/wp-admin/admin-ajax.php",
																					'nonce' => wp_create_nonce("molie_admin_post_course_place")
																				) 
			);
			wp_enqueue_script( 'molie-admin-post-link', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-post-link.js', array( 'jquery' ) );
			wp_localize_script( 'molie-admin-post-link', 'molie_admin_post_link', 
																			array( 
																					'ajaxURL' => network_site_url() . "/wp-admin/admin-ajax.php",
																					'nonce' => wp_create_nonce("molie_admin_post_link")
																				) 
			);
			wp_enqueue_script( 'molie-admin-post-unlink', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-post-unlink.js', array( 'jquery' ) );
			wp_localize_script( 'molie-admin-post-unlink', 'molie_admin_post_unlink', 
																			array( 
																					'ajaxURL' => network_site_url() . "/wp-admin/admin-ajax.php",
																					'nonce' => wp_create_nonce("molie_admin_post_unlink")
																				) 
			);
		}
	
		function metabox(){
	
			global $post;
		
			if(isset($_GET['action'])){
		
				if($_GET['action']=="edit" & $post->post_type=="post"){
					?><div id="molielightbox"><div class="holder"><span id="molielightboxclose"><?PHP echo __("Close"); ?></span><div></div></div></div><?PHP
					add_meta_box("linkedcanvascoursemetaedit",__("Canvas Linking"),array($this,"editor"),null,"side","high");
				}

			}

		}	
		
		function editor(){
		
			global $post;
			$linked = get_post_meta($post->ID, "CanvasLinked", true);
			if($linked==1){
				?><p><?PHP echo __("This course is Canvas linked"); ?></p><?PHP
				?><p><a href="javascript:molie_canvas_unlink(<?PHP echo $post->ID; ?>);"><?PHP echo __("Unlink Course, but leave on Canvas"); ?></a></p><?PHP
				?><p><a href="javascript:molie_canvas_unlink_delete(<?PHP echo $post->ID; ?>);"><?PHP echo __("Unlink and delete from course"); ?></a></p><?PHP
				?><p><a target="_blank" href="<?PHP echo get_post_meta($post->ID, "postHTMLURL", true); ?>"><?PHP echo __("See this page on Canvas"); ?></a></p><?PHP
			}else{
				
				$args = array(
					"post_type" => "linkedcanvascourse",
					"post_status" => "publish"
				);
		
				$courses = get_posts($args);
			
				if(count($courses)!=0){
					echo "<p>" . __("Choose a course") . "</p>";
					foreach($courses as $course){
					?>
						<button id="link_button_<?PHP echo $post->ID; ?>" onclick="javascript:molie_canvas_link(event, <?PHP echo $post->ID; ?>); return false;" course="<?PHP echo $course->ID; ?>" course_id="<?PHP echo get_post_meta($course->ID, "courseID", true); ?>"><?PHP echo $course->post_title; ?></button>
					<?PHP
					}
				}
			}
			
		}
	
	}
	
	$MOLIElinkedPostEditor = new MOLIElinkedPostEditor();
	