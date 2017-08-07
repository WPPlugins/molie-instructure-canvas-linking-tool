<?PHP

	class MOLIElink{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action("admin_enqueue_scripts", array($this, "admin_scripts_and_styles"));
		}
	
		function admin_scripts_and_styles(){
			if(isset($_GET['page'])){
				if($_GET['page']=="molie_link"){
					wp_register_style( 'molie_admin_link', plugins_url() . '/molie-instructure-canvas-linking-tool/css/molie-admin-link.css', false, '1.0.0' );
					wp_enqueue_style( 'molie_admin_link' );
					wp_enqueue_script( 'molie-admin-link', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-link.js', array( 'jquery' ) );
					wp_localize_script( 'molie-admin-link', 'molie_admin_link', 
																					array( 
																							'ajaxURL' => admin_url("admin-ajax.php"),
																							'nonce' => wp_create_nonce("molie_admin_choose")
																						) 
					);
				}
			}
		}
		
		function menu_create(){
			add_submenu_page( "molie_mgmt", __("Link your course"), __("Link your course"), 'edit_linkedcanvascourse', "molie_link", array($this,"link"));
		}
		
		function link(){
		
			$args = array(
				"post_type" => "linkedcanvascourse",
				"post_status" => "publish"
			);
		
			$courses = get_posts($args);
		
			if(count($courses)!=0){
			
				?><h1><?PHP echo __("Courses already linked to WordPress"); ?></h1>
				<?PHP
				
					$nonce = wp_create_nonce("molie-link");
				
					foreach($courses as $course){
						?>
						<form method="post" action='<?PHP echo admin_url("admin.php?page=molie_choose"); ?>'>
							<p><?PHP echo $course->post_title; ?></p>
							<?PHP
								
							?>
							<input type="hidden" name="molie-link-nonce" value="<?PHP echo $nonce; ?>"/>
							<input type="hidden" name="molie_course" value="<?PHP echo get_post_meta($course->ID, "courseID", true) . "|" . $course->post_title; ?>" />
							<input type="hidden" name="url" value="<?PHP echo get_post_meta($course->ID, "courseURL", true); ?>" />
							<input type="hidden" name="token" value="<?PHP echo get_post_meta($course->ID, "courseToken", true); ?>" />
							<input type="submit" value="<?PHP echo __("View content"); ?>" />
						</form>
						<?PHP
					}
			}
			?>
				<h1><?PHP echo __("Link a Course to WordPress"); ?></h1>
				<div id="molie_process">
					<form id="molie_link_form" action="javascript:function connect(){return false;};">
						<label><?PHP echo __("Enter the Canvas URL"); ?></label>
						<input id="canvas_url" type="text" />
						<label><?PHP echo __("Enter the Canvas Token"); ?></label>
						<input id="canvas_token" type="text" />
						<input id="admin_url" type="hidden" value="<?PHP echo admin_url(); ?>" />
						<input type="submit" id="molie_link_submit" value="<?PHP echo __("Connect"); ?>" />
					</form>
					<p class="token"><a target="_blank" href="<?PHP echo admin_url("admin.php?page=molie_token"); ?>"><?PHP echo __("How to get your token"); ?></a></p>
					<?PHP
					
						$urls = get_user_meta(get_current_user_id(), "courseLinkURLsTokens", true);
						if(is_array($urls)){
					
					?><p id="molie_show_previous"><a><?PHP echo __("See previously used information"); ?></a></p>
					<div id="molie_previous">
						<h3><?PHP echo __("Previously used information"); ?></h3>
						<?PHP

							foreach($urls as $url => $tokens){
								?><div class="canvasData">
									<div class="canvasURL"><?PHP echo $url; ?></div>
									<div class="canvasTokens"><?PHP 
											
										foreach($tokens as $token){
											?><div><span><?PHP echo $token; ?></span> - <a class="canvasUse"><?PHP echo __("Use this data"); ?></a></div><?PHP
										}
										
										?></div>
									</div><?PHP
							}
						?>
					</div><?PHP
						}
					?>
					<div id="molie_response">
					</div>
				</div>
			<?PHP
		}	
	
	}
	
	$MOLIElink = new MOLIElink();
	