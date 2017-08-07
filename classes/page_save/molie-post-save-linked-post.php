<?PHP

	class MOLIEpostSaveLinkedPost{
	
		function __construct(){
			add_action('save_post', array($this, 'link_post'), 999);
		}
		
		function localRewrite($url, $canvas_url, $course_id){
			global $wpdb;
			$database_path = explode("uploads/", urldecode($url));
			$file = array_pop($database_path);
			$image_id = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key='_wp_attached_file' and meta_value like '%" . $file . "%'");
			if(count($image_id)==0){
				$file = preg_replace("/\-[0-9]*x[0-9]*\./",".",$file);
				$image_id = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key='_wp_attached_file' and meta_value like '%" . $file . "%'");
			}
			$verifier = get_post_meta($image_id[0]->post_id,"CanvasFileVerifier",true);
			if($verifier!=""){
				$newURL = get_post_meta($image_id[0]->post_id,"CanvasFileURL",true);
				return $newURL;
			}else{
				return $url;
			}
		}
		
		function change_src(){
			global $post;
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML($this->content);
			$query = "//img"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query); 
			foreach ($result as $node) {
				$img_url = $node->getAttribute("src");
				$course_id = get_post_meta($post->ID, "CanvasCourse", true);
				global $wpdb;
				$course_post = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key='CourseID' and meta_value = '" . $course_id . "'");
				$course_post = get_post($course_post[0]->post_id);
				$canvas_url = get_post_meta($course_post->ID,"courseURL",true);
				$site_url = network_site_url();
				if(strpos($img_url,$site_url)!==FALSE){
					$node->setAttribute("src", $this->localRewrite($img_url, $canvas_url, $course_id));
				}
				
			}
			$this->content = $this->domDocument->saveHTML();
		}
		
		function change_links(){
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML($this->content);
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
			
			$this->content = $this->domDocument->saveHTML();
			
		}
		
		function stylesheets($post){
			$styles = array();
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML(file_get_contents($post->guid));
			$query = "//link"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query); 
			foreach ($result as $node) {
				if($node->getAttribute("rel")=="stylesheet"){
					if(strpos($node->getAttribute("href"), network_site_url())!==FALSE){
						array_push($styles, $node->getAttribute("href"));
					}
				}
			}
			return $styles;
		}

		function link_post($post_id){
		
			if(count($_POST)!=0 && isset($_POST['_wp_http_referer'])){
		
				$linked = get_post_meta($post_id, "CanvasLinked", true);
				
				if($linked==1){
					$this->post = $post_id;
					$post = get_post($this->post);
					$this->content = $post->post_content;
					$this->change_src();
					$this->change_links();
					$strip = explode("body>",$this->content);
					$this->content = substr($strip[1],0,strlen($strip[1])-2);
					
					$styles = $this->stylesheets($post);
					require_once(dirname(__FILE__) . "/../lib/emogrifier.php");
					
					foreach($styles as $style){
						if(strpos($style,network_site_url())!==FALSE){
							$dir = wp_upload_dir();
							$style = str_replace(network_site_url() . "/wp-content/", str_replace("uploads","",$dir['basedir']), $style);
							$style = explode("?",$style);
							$style = $style[0];
						}
						$css = preg_replace("/\/[\*]*[^\*]*\*\//","",file_get_contents($styles[1]));
						$emogrifier = new emogrifier($this->content, $css);
						$this->content = $emogrifier->emogrify();
					}
										
					$canvas_id = get_post_meta($post_id, "postCanvasID", true);
					$course_id = get_post_meta($post_id, "CanvasCourse", true);
					
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
				
					$post = get_post($post_id);
					$publish = 1;
					if($post->post_status!="publish"){
						$publish = 0;
					}
					
					$data = $pages->updatePage( $post->post_title,
												array(
														"wiki_page[body]" => stripslashes(str_replace("&nbsp;"," ",$this->content)), 
														"wiki_page[title]" => $post->post_title,
														"wiki_page[published]" => $publish
													)
												);
	
				}
				
			}
			
		}
	
	}
	
	$MOLIEpostSaveLinkedPost = new MOLIEpostSaveLinkedPost();
	