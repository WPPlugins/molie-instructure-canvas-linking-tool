<?PHP

	class MOLIEcoursePostEditor{
	
		function __construct(){
			add_action("admin_head", array($this, "metabox"));
			add_action("save_post", array($this, "save_post"));
		}
	
		function metabox(){
	
			global $post;
		
			if(isset($_GET['action'])){
		
				if($_GET['action']=="edit" && $post->post_type=="linkedcanvascourse"){
					add_meta_box("linkedcanvascoursemeta",__("Edit course"),array($this,"editor"));
					wp_register_style( 'molie_admin_course_post_editor_css', plugins_url() . '/molie-instructure-canvas-linking-tool/css/molie-admin-course-post-editor.css', false, '1.0.0' );
					wp_enqueue_style( 'molie_admin_course_post_editor_css' );
				}

			}

		}	
		
		function editor(){
		
			global $post;
			$course_id = get_post_meta($post->ID, "courseID", true);
			
			?><p><label for="courseURL"><?PHP echo __("Course URL"); ?></label><input id="courseURL" name="courseURL" maxlength="200" size="100" value="<?PHP echo get_post_meta($post->ID, "courseURL", true); ?>" /></p><?PHP
			?><p><label for="courseToken"><?PHP echo __("Course Token"); ?></label><input id="courseToken" name="courseToken" maxlength="200" size="100" value="<?PHP echo get_post_meta($post->ID, "courseToken", true); ?>" /></p><?PHP
			
			$admin_url = admin_url("edit.php?course=" . $course_id . "&canvas_linked=true");
			
			?><p><a href="<?PHP echo $admin_url; ?>"><?PHP echo __("See pages in this course"); ?></a></p><?PHP
			
			$admin_url = admin_url('admin.php?page=molie_media_mgmt&course_id=' . $_GET['post']);
			
			?><p><a href="<?PHP echo $admin_url; ?>"><?PHP echo __("See media in this course"); ?></a></p><?PHP
			
			$admin_url = admin_url("edit.php?post_type=linkedcanvasuser&course=" . $course_id . "&canvas_linked=true");
			
			?><p><a href="<?PHP echo $admin_url; ?>"><?PHP echo __("See users in this course"); ?></a></p><?PHP
			
			$admin_url = admin_url("edit.php?post_type=linkedcanvasquiz&course=" . $course_id . "&canvas_linked=true");
			
			?><p><a href="<?PHP echo $admin_url; ?>"><?PHP echo __("See quizzes in this course"); ?></a></p><?PHP
			
			$admin_url = admin_url("edit.php?post_type=linkedcanvasdis&course=" . $course_id . "&canvas_linked=true");
			
			?><p><a href="<?PHP echo $admin_url; ?>"><?PHP echo __("See discussions in this course"); ?></a></p><?PHP
		}
		
		function save_post($post_id){
			$post = get_post($post_id);
			if($post->post_type=="linkedcanvascourse"){
				if(isset($_POST['courseURL'])){
					update_post_meta($post_id, "courseURL", $_POST['courseURL']); 
				}
				if(isset($_POST['courseToken'])){
					update_post_meta($post_id, "courseToken", $_POST['courseToken']);
				}
			}
		}
	
	}
	
	$MOLIEcoursePostEditor = new MOLIEcoursePostEditor();
	