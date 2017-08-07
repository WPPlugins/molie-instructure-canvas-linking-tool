<?PHP

	class MOLIEfileUpload{
	
		function __construct(){
			add_action("wp_ajax_molie_admin_upload_missing_file", array($this, "upload"));
			add_action("wp_ajax_molie_admin_download_missing_file", array($this, "download"));
		}
		
		function download(){
			if(wp_verify_nonce($_POST['nonce'], "molie_admin_upload_missing_file"))
			{	
				$linked = get_post_meta($_POST['post_id'], "CanvasLinked", true);
				if($linked==1){
					if(get_post_meta($post_id,"CanvasCourseIDFileID",true)==""){
						
						global $wpdb;
						$course_id = get_post_meta($_POST['post_id'], "CanvasCourse", true);
						$course_post = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key='CourseID' and meta_value = '" . $course_id . "'");
						$course_post = $course_post[0]->post_id;		
						$url = get_post_meta($course_post, "courseURL", true);
						
						$id = explode("/",$_POST['url']);
						
						if($url=="https://" . $id[2]){
							if($course_id==$id[4]){
								
								$file_id = $id[6];
								
								require_once(__DIR__ . "/../../API/Psr4AutoloaderClass.php");
								$loader = new Psr4AutoloaderClass;
								$loader->register();
								$loader->addNamespace('InstructureCanvasAPI', __DIR__ . "/../../API/InstructureCanvasAPI/src");
										
								$API = new InstructureCanvasAPI\InstructureCanvasAPI( 
																				array(
																					"site" => get_post_meta($course_post, "courseURL", true),
																					"token" => get_post_meta($course_post, "courseToken", true),
																					"webService" => "CURL"
																				)
																			);
									
								$files = new InstructureCanvasAPI\Courses\Files\Files();
								$files->setAPI($API);
								$files->setCourseID(get_post_meta($course_post, "courseID", true));
								$data = $files->getFile($id[6]);
								
								$post = get_post($course_post);
								$file_system = get_post_meta($post->ID, "courseFileSystem", true);
								$folder_id = array_shift($file_system);
								$root_path = $folder_id['actual_path'];
								$file_contents = file_get_contents($data->content->url);
								
								file_put_contents($root_path . "/" . $data->content->display_name, $file_contents);
								
								$filetype = wp_check_filetype( $root_path . "/" . $data->content->display_name, null );

								$attachment = array(
									'guid'           => $root_path . "/" . $data->content->display_name, 
									'post_mime_type' => $filetype['type'],
									'post_title'     => preg_replace( '/\.[^.]+$/', '', $data->content->display_name ),
									'post_content'   => '',
									'post_status'    => 'inherit'
								);
								
								$attach_id = wp_insert_attachment($attachment, $root_path . "/" . $data->content->display_name);
								
								$attachment_data = wp_generate_attachment_metadata( $attach_id, $root_path . "/" . $data->content->display_name);
								
								update_post_meta($attach_id, "_wp_attachment_metadata", $attachment_data, true);
								
								update_post_meta($course_post, "molie_file_" . $data->content->id, $attach_id);
								update_post_meta($attach_id, "CanvasCourse", get_post_meta($course_post, "courseID", true));
								
								$verifier = explode("verifier=",$data->content->url);
								
								update_post_meta($attach_id, "CanvasFileVerifier", $verifier[1]);
								update_post_meta($attach_id, "CanvasCourseIDFileID", get_post_meta($course_post, "courseID", true) . "," . $data->content->display_name);
								update_post_meta($attach_id, "CanvasFileURL", $data->content->url);
								
								echo $data->content->url;
								
								echo true;
							}
						}	
					}
				}
			}
			
			die();
		}
		
		
		function upload(){
			if(wp_verify_nonce($_POST['nonce'], "molie_admin_upload_missing_file"))
			{	
				$linked = get_post_meta($_POST['post_id'], "CanvasLinked", true);
				if($linked==1){
					if(get_post_meta($post_id,"CanvasCourseIDFileID",true)==""){
						
						$course_id = get_post_meta($_POST['post_id'], "CanvasCourse", true);

						global $wpdb;
						$course_post = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key='CourseID' and meta_value = '" . $course_id . "'");
						$course_post = get_post($course_post[0]->post_id);
						
						$file_system = get_post_meta($course_post->ID, "courseFileSystem", true);
												
						foreach($file_system as $folder_id => $file_data){
							if(strpos($file_data['actual_path'],$course_id . "/course files/")===FALSE){
								$folder = $folder_id;	
							}
						}
					
						$file = $_POST['url'];
						$dir = wp_upload_dir();
						$file_path = str_replace($dir['baseurl'], $dir['basedir'], $_POST['url']);
						$attach_path = str_replace($dir['baseurl'], "", $_POST['url']);
						$attach_id = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key='_wp_attached_file' and meta_value = '" . substr($attach_path,1) . "'");
						$attach_id = $attach_id[0]->post_id;
						
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

						$files = new InstructureCanvasAPI\Courses\Files\Files();
						$files->setAPI($API);
						$files->setCourseID($course_id);
						$parts = explode("/", $file_path);
						$name = array_pop($parts);
			
						$data = $files->requestUpload(
												array(
													"name" => $name,
													"parent_folder_id" => $folder,
												)
											);
											
						$file_data = array( "file" => $file_path );
						
						$file_data = $files->upload($data->content->upload_url, $data->content->upload_params, $file_data);
						
						$header_parts = explode("\n", $file_data->header);
						
						foreach($header_parts as $part){
							if(strpos($part,"Location:")!==FALSE){
								$confirm_url = trim(substr($part,(strpos($part,":")+1)));
							}
						}
						
						$confirm_data = $files->confirm($confirm_url);
						$parts = explode("verifier=", $confirm_data->content->url);
						$verifier = array_pop($parts);
						
						update_post_meta($course_post->ID, "molie_file_" . $confirm_data->content->id, $attach_id);
						update_post_meta($attach_id, "CanvasCourse", $course_id);
						update_post_meta($attach_id, "CanvasFileVerifier", $verifier);
						update_post_meta($attach_id, "CanvasCourseIDFileID", $course_id . "," . $confirm_data->content->id);
						update_post_meta($attach_id, "CanvasFileURL", $confirm_data->content->url);
						
						echo true;
							
					}
				}
			}
			
			die();
		}
		
	}
	
	$MOLIEfileUpload = new MOLIEfileUpload();
	