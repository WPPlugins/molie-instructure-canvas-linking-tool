<?PHP

	class MOLIEmediaUpload{
	
		function __construct(){
			//add_filter( 'wp_handle_upload', array($this, 'media_added'));
			add_action( 'add_attachment', array($this, 'media_added'));
			add_filter( 'attachment_fields_to_edit', array($this, 'attachment_field_course_set'), 10, 2 );
			add_filter( 'attachment_fields_to_save', array($this, 'attachment_field_course_save'), 10, 2 );
			add_filter( 'media_send_to_editor' , array($this, "add_canvas_fields"));
			add_filter( 'wp_update_attachment_metadata' , array($this, "metadata"), 10, 2 );
		}
		
		function metadata($data, $post){
			if(isset($_REQUEST['action'])){
				if($_REQUEST['action']=="upload-attachment"){
					if(get_post_meta($post,"CanvasFileURL",true)!=""){
						$data = get_post_meta($post,"_wp_attachment_metadata",true);
					}
				}
			}
			return $data;
		}
		
		function media_added($post_id){
			$post = get_post($post_id);
			$linked = get_post_meta($post->post_parent, "CanvasLinked", true);
			if($linked==1){
				if(get_post_meta($post_id,"CanvasCourseIDFileID",true)==""){
					
					$course_id = get_post_meta($post->post_parent, "CanvasCourse", true);

					global $wpdb;
					$course_post = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key='CourseID' and meta_value = '" . $course_id . "'");
					$course_post = get_post($course_post[0]->post_id);
					
					$file_system = get_post_meta($course_post->ID, "courseFileSystem", true);
					
					foreach($file_system as $folder_id => $file_data){
						if(strpos($file_data['actual_path'],$course_id . "/course files/")===FALSE){
							$folder = $folder_id;	
							$actual_path = $file_data['actual_path'];
						}
					}
					
					$file = get_attached_file($post_id);
					$parts = explode("/", $file);
					$new_name = $actual_path . "/" . array_pop($parts);
					rename($file, $new_name);
					$dir = wp_upload_dir();
					$change_path = str_replace("\\","/",$dir['basedir']) . "/";
					$new_path = str_replace($change_path, "", $new_name);
					
					$data = wp_generate_attachment_metadata( $post_id, $new_name );
					
					update_post_meta($post_id, "_wp_attached_file", $new_path);
					update_post_meta($post_id, "_wp_attachment_metadata", $data, true);	
					
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
					$parts = explode("/", $post->guid);
					$name = array_pop($parts);
					$data = $files->requestUpload(
											array(
												"name" => $name,
												"content_type" => $post->post_mime_type,
												"parent_folder_id" => $folder,
											)
										);
										
					$file_data = array( "file" => $change_path . $new_path );
					
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
					
					update_post_meta($course_post->ID, "molie_file_" . $confirm_data->content->id, $post_id);
					update_post_meta($post_id, "CanvasCourse", $course_id);
					update_post_meta($post_id, "CanvasFileVerifier", $verifier);
					update_post_meta($post_id, "CanvasCourseIDFileID", $course_id . "," . $_POST['item']);
					update_post_meta($post_id, "CanvasFileURL", $confirm_data->content->url);
						
				}
			}
		}
		
		function attachment_field_course_set( $form_fields, $post ) {
			
			global $wpdb;
			
			$form_fields['linked-course'] = array(
				'label' => 'Course',
				'input' => 'html',
				'helps' => __('Add this Media to a Canvas Course'),
			);
			
			$course_of_post = get_post_meta(wp_get_post_parent_id($post->ID), "CanvasCourse", true);
			if($course_of_post==""){
				$course_of_post = get_post_meta($post->ID, "CanvasCourse", true);
			}
			
			$courses = $wpdb->get_results("select * from " . $wpdb->prefix . "posts where post_type='linkedcanvascourse' and post_status='publish'");
			
			$form_fields['linked-course']['html'] = '<select name="linked-course" id="linked-course">';
			$form_fields['linked-course']['html'] .= '<option>' . __("No course") . $course_of_post . '</option>';
			foreach($courses as $course){
				$id = get_post_meta($course->ID, "courseID", true);
				$selected = "";
				if($id == $course_of_post){
					$selected = " selected ";
				}
				$form_fields['linked-course']['html'] .= '<option value="' . $id . '" ' . $selected . '>' . $course->post_title . '</option>';
			}
			$form_fields['linked-course']['html'] .= '</select>';

			return $form_fields;
		}
		
		function attachment_field_course_save( $post, $attachment ) {
			if( isset( $attachment['linked-course'] ) )
				update_post_meta( $post['ID'], 'CanvasCourse', $attachment['linked-course'] );
			if( isset( $_POST['linked-course'] ) )
				update_post_meta( $post['ID'], 'CanvasCourse', $_POST['linked-course'] );
			
			return $post;
		}
			
		function add_canvas_fields($in){
			return substr($in,0,strlen($in)-2) . " new_attr='hey hey hey' /> ";
		}
		
	}
	
	$MOLIEmediaUpload = new MOLIEmediaUpload();
	