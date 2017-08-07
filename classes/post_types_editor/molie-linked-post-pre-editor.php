<?PHP
	class MOLIElinkedPostPreEditor{
	
		function __construct(){
			if(!isset($_GET['canvas_upload'])){
				add_action("admin_notices", array($this, "pre"));
				add_action("admin_enqueue_scripts", array($this, "admin_scripts_and_styles"));
			}
		}
	
		function admin_scripts_and_styles(){
			wp_register_style( 'molie_admin_link_options_css', plugins_url() . '/molie-instructure-canvas-linking-tool/css/molie-admin-link-options.css', false, '1.0.0' );
			wp_enqueue_style( 'molie_admin_link_options_css' );
			wp_enqueue_script( 'molie-admin-link-options', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-link-options.js', array( 'jquery' ) );
			wp_enqueue_script( 'molie-admin-post-update', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-post-update.js', array( 'jquery' ) );
			wp_localize_script( 'molie-admin-post-update', 'molie_admin_post_update', 
																			array( 
																					'ajaxURL' => network_site_url() . "/wp-admin/admin-ajax.php",
																					'nonce' => wp_create_nonce("molie_admin_post_update")
																				) 
			);
		}
		
		function compare(){
			$diff = true;
			//echo "<div>" . $this->wp . "</div>";
			//echo "<div>" . $this->canvas . "</div>";
			for($x=0;$x<strlen($this->canvas);$x++){
				if($this->canvas[$x]!=$this->wp[$x]){
					echo $x . " " .  $this->canvas[$x] . " " . $this->wp[$x] . "<Br />";
					$diff = false;
				}
			}
			return $diff;
		}
		
		function change_src(){
			global $post;
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML($this->wp);
			$query = "//img"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query); 
			foreach ($result as $node) {
				$img_url = $node->getAttribute("src");
				if(strpos($img_url,network_site_url())!==FALSE){
					$stem = str_replace(network_site_url() . "/wp-content/uploads/","",$img_url);
					global $wpdb;
					$image_post = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_value = '" . urldecode($stem) . "'");
					$new_url = get_post_meta($image_post[0]->post_id,"CanvasFileURL",true);
					$parts = explode("&verifier=",$new_url);
					$node->setAttribute("src", $parts[0]);
				}
				
			}
			$this->wp = $this->domDocument->saveHTML();
		}
		
		function remove_styles(){
			global $post;
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML($this->wp);
			$query = "//*[@style]"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query); 
			foreach ($result as $node) {
				$node->setAttribute("style","");
			}
			$this->wp = $this->domDocument->saveHTML();
			@$this->domDocument->loadHTML($this->canvas);
			$query = "//*[@style]"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query); 
			foreach ($result as $node) {
				$node->setAttribute("style","");
			}
			$this->canvas = $this->domDocument->saveHTML();
		}
		
		function change_links(){
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML($this->wp);
			$query = "//a"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query); 
			foreach ($result as $node) {
				$href = $node->getAttribute("href");
				if(strpos($href,network_site_url())!==FALSE){
					$linked_post_id = url_to_postid($href);
					if($linked_post_id!=0){
						if(get_post_meta($linked_post_id, "CanvasLinked", true)==1){
							$link_post = get_post($linked_post_id);
							if($node->getAttribute("TITLE")==false){
								$node->setAttribute("TITLE",$link_post->title);
							}
							if($node->getAttribute("DATA-API-ENDPOINT")==false){
								$canvas_url = get_post_meta($linked_post_id,"postHTMLURL",true);
								$node->setAttribute("DATA-API-ENDPOINT", $canvas_url);
							}
							if($node->getAttribute("DATA-API-RETURNTYPE")==false){
								$node->setAttribute("DATA-API-RETURNTYPE", "page");
							}
							if($node->getAttribute("DATA-MCE-HREF")==false){
								$node->setAttribute("DATA-MCE-HREF", $href);
							}
						}
					}else{
						if(strpos($href,"/category/")!==FALSE || strpos($href,"?cat=")!==FALSE){	
							if(strpos($href,"?cat=")!==FALSE){
								$main = explode("?", $href);
								$parameters = explode("=", $main[1]);
								for($x=0;$x<=count($parameters);$x++){
									if($parameters[$x]=="cat"){
										$category_id = $parameters[$x+1]; 
									}
								}
								$category_id = $wpdb->get_results("select meta_key from " . $wpdb->prefix . "postmeta where post_id='" . $post->ID . "' and meta_value = '" . $category_id . "'");
								$module_id = explode("_", $category_id[0]->meta_key);
								$id = array_pop($module_id);
								$course_id = get_post_meta($post->ID, "courseID", true);
								$course_url = get_post_meta($post->ID, "courseURL", true);
								if($id=="id"){
									$cat_url = $course_url . "/courses/" . $course_id . "/modules";
								}else{
									$cat_url = $course_url . "/courses/" . $course_id . "/modules#module_" . $id;
								}
								echo $node->getAttribute("TITLE") . "<br />";
								if($node->getAttribute("TITLE")==false){
									$node->setAttribute("TITLE",$category->name);
								}
								if($node->getAttribute("DATA-API-ENDPOINT")==false){
									$node->setAttribute("DATA-API-ENDPOINT", $cat_url);
								}
								if($node->getAttribute("DATA-API-RETURNTYPE")==false){
									$node->setAttribute("DATA-API-RETURNTYPE", "module");
								}
								if($node->getAttribute("DATA-MCE-HREF")==false){
									$node->setAttribute("DATA-MCE-HREF", $cat_url);
								}
							}else{
								$parts = explode("/", $href);
								$parts = array_filter($parts);
								$category = get_category_by_slug( array_pop($parts) );
								$course = get_post_meta($post_id,"CanvasCourse",true);
								global $wpdb;
								$course_post = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key='CourseID' and meta_value = '" . $course . "'");
								$post = get_post($course_post[0]->post_id);
								$category_id = $wpdb->get_results("select meta_key from " . $wpdb->prefix . "postmeta where post_id='" . $post->ID . "' and meta_value = '" . $category->term_id . "'");
								$module_id = explode("_", $category_id[0]->meta_key);
								$id = array_pop($module_id);
								$course_id = get_post_meta($post->ID, "courseID", true);
								$course_url = get_post_meta($post->ID, "courseURL", true);
								if($id=="id"){
									$cat_url = $course_url . "/courses/" . $course_id . "/modules";
								}else{
									$cat_url = $course_url . "/courses/" . $course_id . "/modules#module_" . $id;
								}
								echo $node->getAttribute("TITLE") . "<br />";
								if($node->getAttribute("TITLE")==false){
									$node->setAttribute("TITLE",$category->name);
								}
								if($node->getAttribute("DATA-API-ENDPOINT")==false){
									$node->setAttribute("DATA-API-ENDPOINT", $cat_url);
								}
								if($node->getAttribute("DATA-API-RETURNTYPE")==false){
									$node->setAttribute("DATA-API-RETURNTYPE", "module");
								}
								if($node->getAttribute("DATA-MCE-HREF")==false){
									$node->setAttribute("DATA-MCE-HREF", $cat_url);
								}
							}
						}
					}
				}
			}
			
			$query = "//a/img"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query); 
			foreach ($result as $node) {
				$src = $node->getAttribute("src");
				$a_links = $xpath->query("//a[img[@src='" . $src . "']]");
				foreach($a_links as $link){
					$link->setAttribute("href", $src);
				}
			}
			
			$this->wp = $this->domDocument->saveHTML();
		}
		
		function prepare(){
			$this->wp = preg_replace('/\srel="[a-zA-Z0-9\s\-]*"/',"",$this->wp);
			$this->wp = preg_replace('/>[\s\t\r]*</',"><",$this->wp);
			$this->canvas = preg_replace('/>[\s\t\r]*</',"><",$this->canvas);
			$this->canvas = preg_replace('/style\=\"([a-zA-Z\-]*)\: /','style="$1:',$this->canvas);
			$this->wp = preg_replace('/style\=\"([a-zA-Z\-]*)\: /','style="$1:',$this->wp);
			$this->canvas = str_replace(';">','">',$this->canvas);
			$this->wp = str_replace(';">','">',$this->wp);
			$this->wp = preg_replace('/\sdata-api-endpoint="[a-zA-Z0-9\s\-\/\:\.]*"/',"",$this->wp);
			$this->wp = preg_replace('/\sdata-api-returntype="[a-zA-Z0-9\s\-]*"/',"",$this->wp);
			$this->wp = str_replace('&nbsp;'," ",$this->wp);
			$this->wp = str_replace('> <',"><",$this->wp);
			$this->wp = str_replace('\r',"",$this->wp);
			$this->remove_styles();
			$this->change_src();
			$this->change_links();
		}
		
		function pre(){
			
			global $post;
			
			if($post->post_type=="post" && isset($_GET['action'])){
				
				$linked = get_post_meta($post->ID, "CanvasLinked", true);
				
				if($linked==1){
					
					$canvas_id = get_post_meta($post->ID, "postCanvasID", true);
					$course_id = get_post_meta($post->ID, "CanvasCourse", true);
					
					global $wpdb;
					$course_post = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key='CourseID' and meta_value = '" . $course_id . "'");
					$course_post = get_post($course_post[0]->post_id);
					
					require_once(__DIR__ . "/../../API/Psr4AutoloaderClass.php");
					$loader = new Psr4AutoloaderClass;
					$loader->register();
					$loader->addNamespace('InstructureCanvasAPI', __DIR__ . "/../../API/InstructureCanvasAPI/src");
							
					$API = new InstructureCanvasAPI\InstructureCanvasAPI( 
																	array(
																		"site" => get_post_meta($course_post->ID, "courseURL", true),
																		"token" => get_post_meta($course_post->ID, "courseToken", true),
																		"webService" => "CURL"
																	)
																);
						
					$pages = new InstructureCanvasAPI\Courses\Pages\Pages();
					$pages->setAPI($API);
					$pages->setCourseID(get_post_meta($course_post->ID, "courseID", true));
					$data = $pages->getPage(get_post_meta($post->ID, "postURL", true));
					
					$this->canvas = $data->content->body;
					$this->wp = $post->post_content;
					$this->prepare();
					
					$this->wp = explode("body>",$this->wp);
					$this->wp = substr($this->wp[1],0,strlen($this->wp[1])-2);
					
					$this->canvas = explode("body>",$this->canvas);
					$this->canvas = substr($this->canvas[1],0,strlen($this->canvas[1])-2);
					
					if($this->compare()){
						?><div id='canvassyncmessage' class="notice notice-success" style="border:1px solid #000">
							<p><?PHP echo __("This post is up to date on Canvas and WP"); ?> : <span class="molieoptions">(<?PHP echo __("Click for Options"); ?>)</span></p>
							<div id="molielinkoptions">
								<p><a href="<?PHP echo get_post_meta($post->ID, "postHTMLURL", true); ?>"><?PHP echo __("See this page on Canvas"); ?></p>
								<p><a href="javascript:molie_update_post(<?PHP echo $post->ID; ?>,'<?PHP echo get_post_meta($post->ID, "postURL", true); ?>','upload');">Update Canvas with this page</a></p>
								<p><a href="javascript:molie_update_post(<?PHP echo $post->ID; ?>,'<?PHP echo get_post_meta($post->ID, "postURL", true); ?>','download');">Update this page with Canvas content</a></p>
							</div>
						</div>
						<?PHP
					}else{
						?><div id='canvassyncmessage' class="notice notice-info" style="border:1px solid #000">
							<p><?PHP echo __("This post does not appear to be the same as the one it is linked to on Canvas"); ?> : <span class="molieoptions">(<?PHP echo __("Click for Options"); ?>)</span></p>
							<div id="molielinkoptions">
								<p><a href="<?PHP echo get_post_meta($post->ID, "postHTMLURL", true); ?>"><?PHP echo __("See this page on Canvas"); ?></p>
								<p><a href="javascript:molie_update_post(<?PHP echo $post->ID; ?>,'<?PHP echo get_post_meta($post->ID, "postURL", true); ?>','upload');">Update Canvas with this page</a></p>
								<p><a href="javascript:molie_update_post(<?PHP echo $post->ID; ?>,'<?PHP echo get_post_meta($post->ID, "postURL", true); ?>','download');">Update this page with Canvas content</a></p>
							</div>
						</div>
						</div>
						<?PHP
					}
					
				}
			
			}
				
		}
	
	}
	
	$MOLIElinkedPostPreEditor = new MOLIElinkedPostPreEditor();
	