<?PHP

	class MOLIEpostSaveImageHandler{
	
		function __construct(){
			add_action('save_post', array($this, 'detect_media'));
			add_action('admin_notices', array( $this, 'admin_notices' ) );
			add_action("admin_enqueue_scripts", array($this, "admin_scripts_and_styles"));
		}
		
		function admin_scripts_and_styles(){
			wp_enqueue_script( 'molie-admin-upload-missing-file', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-upload-missing-file.js', array( 'jquery' ) );
			wp_localize_script( 'molie-admin-upload-missing-file', 'molie_admin_upload_missing_file', 
																			array( 
																					'ajaxURL' => network_site_url() . "/wp-admin/admin-ajax.php",
																					'nonce' => wp_create_nonce("molie_admin_upload_missing_file")
																				) 
			);
		}

		public function add_notice_query_var($location) {
			remove_filter( 'redirect_post_location', array( $this, 'add_notice_query_var' ), 99 );
			$data = implode(",",$this->missing);
			return add_query_arg( array( 'canvas_upload' => $data ), $location );
		}

		public function admin_notices() {
			global $post;
			if ( ! isset( $_GET['canvas_upload'] ) ) {
				return;
			}
			?>
				<div class="error">
			<?PHP
				$items = explode(",", urldecode($_GET['canvas_upload']));
				for($x=0;$x<count($items);$x+=2){
					if($items[$x]=="local"){
					?>
						<p id='molie_file_<?PHP echo $x; ?>'><?PHP echo __("The local file"); ?> <a href="<?php echo $items[$x+1]; ?>"><?php echo $items[$x+1]; ?></a> <?PHP echo __("is not on Canvas"); ?> : <a href="javascript:molie_upload_file(<?PHP echo $post->ID; ?>,<?PHP echo $x; ?>,'<?PHP echo $items[$x+1]; ?>');"><?PHP echo __("Upload file"); ?></a></p>
					<?php
					}else{
					?>
						<p id='molie_file_<?PHP echo $x; ?>'><?PHP echo __("The Canvas file"); ?> <a href="<?php echo $items[$x+1]; ?>"><?php echo $items[$x+1]; ?></a> <?PHP echo __("is not saved locally"); ?> : <a href="javascript:molie_download_file(<?PHP echo $post->ID; ?>,<?PHP echo $x; ?>,'<?PHP echo $items[$x+1]; ?>');"><?PHP echo __("Download file"); ?></a></p>
					<?php
					}
				}
			?>
				</div>
			<?PHP
		}
		
		function get_local_image($img_attrs){
			global $wpdb;
			$image_post = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_value = '" . $img_attrs[6] . "," . $img_attrs[8] . "'");
			return wp_get_attachment_url( $image_post[0]->post_id ); 
		}
		
		function get_image($path, $post_id){
			global $wpdb;
			$this->path = $path;
			echo "select max(post_id) as id from " . $wpdb->prefix . "postmeta where meta_value like '%" . $path . "%'<br />";
			$this->image_id = $wpdb->get_results("select max(post_id) as id from " . $wpdb->prefix . "postmeta where meta_value like '%" . $path . "%'");
			print_r($this->image_id);
			echo "<br />";
			if($this->image_id[0]->id!=""){
				$canvas_id = $wpdb->get_results("select meta_value from " . $wpdb->prefix . "postmeta where meta_key = 'CanvasCourse' and post_id = " . $image_id[0]->id);
				if($canvas_id[0]->meta_value!=""){
					$course = get_post_meta($post_id, "CanvasCourse", true);
					if($course == $canvas_id[0]->meta_value){
						return true;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		
		function canvasCheck($url){
			global $wpdb;
			$verifier = explode("verifier=",$url);
			echo $url . "<br />";
			$this->image = $wpdb->get_results("select * from " . $wpdb->prefix . "postmeta where meta_value like '%" . array_pop($verifier) . "%'");
			print_r($this->image);
			echo "<br />";
			if(count($this->image)==0){
				array_push( $this->missing , "canvas");
				array_push( $this->missing , $url);
			}
		}
		
		function localCheck($url){
			global $wpdb;
			$database_path = explode("uploads/", urldecode($url));
			$file = array_pop($database_path);
			$image_id = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key='_wp_attached_file' and meta_value like '%" . $file . "%'");
			if(get_post_meta($image_id[0]->post_id,"CanvasFileVerifier",true)==""){
				array_push( $this->missing , "local");
				array_push( $this->missing , $url );
			}
		}
		
		function detect_media($post_id){

			if(count($_POST)!=0 && !isset($_POST['_wp_http_referer'])){
				$post = get_post($post_id);
				if($post->post_type=="post"){
					$linked = get_post_meta($post_id, "CanvasLinked", true);
					if($linked==1){
						if(trim($post->post_content)!=""){
							$this->post = $post_id;
							$this->missing = array();
							$this->domDocument = new DOMDocument();
							@$this->domDocument->loadHTML($post->post_content);
							$query = "//img"; 
							$xpath = new DOMXPath($this->domDocument); 
							$result = $xpath->query($query); 
							foreach ($result as $node) {
								$img_url = $node->getAttribute("src");
								if($img_url==""){
									$img_url = $node->getAttribute("src");
								}
								
								$course_id = get_post_meta($post_id, "CanvasCourse", true);
								global $wpdb;
								$course_post = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key='CourseID' and meta_value = '" . $course_id . "'");
								$course_post = get_post($course_post[0]->post_id);
								$canvas_url = get_post_meta($course_post->ID,"courseURL",true);
								$site_url = network_site_url();
								
								if(strpos($img_url, $canvas_url . "/courses/" . $course_id . "/files")!==FALSE){
									$this->canvasCheck($img_url);
								}
								if(strpos($img_url,$site_url)!==FALSE){
									$this->localCheck($img_url);
								}
								
							}
						}
						if(count($this->missing)!=0){
							add_filter( 'redirect_post_location', array( $this, 'add_notice_query_var' ), 99 );
						}
					}
				}
				
			}
			
		}
	
	}
	
	$MOLIEpostSaveImageHandler = new MOLIEpostSaveImageHandler();
	