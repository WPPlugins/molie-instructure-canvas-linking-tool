<?PHP

	class MOLIEmediaMgmt{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action("admin_enqueue_scripts", array($this, "admin_scripts_and_styles"));
		}
	
		function admin_scripts_and_styles(){
			if(isset($_GET['page'])){
				if($_GET['page']=="molie_mediamgmt"){
					wp_register_style( 'molie_admin_media_mgmt_css', plugins_url() . '/molie-instructure-canvas-linking-tool/css/molie-admin-media-mgmt.css', false, '1.0.0' );
					wp_enqueue_style( 'molie_admin_media_mgmt_css' );
				}
			}
		}
		
		function menu_create(){
			add_submenu_page( "molie_mgmt", __("Course Media"), __("Course Media"), 'edit_linkedcanvascourse', "molie_mediamgmt", array($this,"media"));
		}
		
		function media(){
		
			global $wpdb;
			$data = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key = 'courseID' and meta_value = '" . $_GET['course_id'] . "'");
		
			$course_post = get_post($data[0]->post_id);
		
			?><h2><?PHP echo __("Media used in"); ?> <?PHP echo $course_post->post_title; ?></h2><?PHP
			?><p><?PHP echo __("Click on a file to see more"); ?></p><?PHP
			
			$args = array(
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'posts_per_page' => -1,
				'meta_key' => 'CanvasCourse',
				'meta_value' => get_post_meta($_GET['course_id'],"courseID",true),
				'meta_compare' => '='
			);

			$query = new WP_Query( $args );
			
			while($query->have_posts()){
				$query->the_post();
				
				$upload_url = admin_url("upload.php?item=" . get_the_id());
			
				?><div class="media"><a href="<?PHP echo $upload_url; ?>"><p><img src="<?PHP echo wp_get_attachment_thumb_url(get_the_ID()); ?>"/></p><p><?PHP echo the_title(); ?></p></a></div><?PHP
			}
		}
	
	}
	
	$MOLIEmediaMgmt = new MOLIEmediaMgmt();
	