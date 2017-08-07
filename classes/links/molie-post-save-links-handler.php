<?PHP

	class MOLIEpostSaveLinksHandler{
	
		function __construct(){
			//add_action('save_post', array($this, 'detect_links'));
		}
		
		function detect_links($post_id){
			$post = get_post($post_id);
			if($post->post_type=="post"){
				$linked = get_post_meta($_POST['post_id'], "CanvasLinked", true);
				if($linked==1){
					if(trim($post->post_content)!=""){
						$this->post = $post_id;
						$this->domDocument = new DOMDocument();
						@$this->domDocument->loadHTML($post->post_content);
						$query = "//a"; 
						$xpath = new DOMXPath($this->domDocument); 
						$result = $xpath->query($query); 
						foreach ($result as $node) {
							$href = $node->getAttribute("href");
							if(strpos($href,network_site_url())!==FALSE){
								$linked_post_id = url_to_postid($href);
								echo $linked_post_id . "<br />";
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
						
						
						$new_content = $this->domDocument->saveHTML();
						$strip = explode("body>",$new_content);
						$final_content = substr($strip[1],0,strlen($strip[1])-2);
						
						$post = array(		        
										'ID' => $post_id,	
										'post_content' => $final_content,	      
									);		      		    
						
						remove_action( 'save_post', array( $this, 'detect_links' ) );
						wp_update_post( $post );		    
						add_action( 'save_post', array( $this, 'detect_links' ) );
						
					}
				}
			}
		}
	
	}
	
	$MOLIEpostSaveLinksHandler = new MOLIEpostSaveLinksHandler();
	