<?PHP
	
	class MOLIEfileAjax{
	
		function __construct(){;
			add_action("wp_ajax_molie_file_import", array($this, "file_import"));
			add_action("wp_ajax_no_priv_molie_file_import", array($this, "file_import"));
		}
		
		private function get_file($post){
			require_once(__DIR__ . "/../../API/Psr4AutoloaderClass.php");
			$loader = new Psr4AutoloaderClass;
			$loader->register();
			$loader->addNamespace('InstructureCanvasAPI', __DIR__ . "/../../API/InstructureCanvasAPI/src");
					
			$API = new InstructureCanvasAPI\InstructureCanvasAPI( 
															array(
																"site" => get_post_meta($post->ID, "courseURL", true),
																"token" => get_post_meta($post->ID, "courseToken", true),
																"webService" => "CURL"
															)
														);
				
			$file = new InstructureCanvasAPI\Courses\Files\Files();
			$file->setAPI($API);
			$file->setCourseID(get_post_meta($post->ID, "courseID", true));
			$file_data = $file->getFile($_POST['item']);
			return $file_data->content;
		}
		
	
		function file_import(){
			if(wp_verify_nonce($_POST['nonce'], "molie_admin_file"))
			{
				$post = get_post($_POST['course_post']);
				$file_system = get_post_meta($post->ID, "courseFileSystem", true);
				$file_contents = file_get_contents($_POST['url']);
				file_put_contents($file_system[$_POST['folder']]['actual_path'] . "/" . $_POST['filename'], $file_contents);
				
				$filetype = wp_check_filetype( basename( $file_system[$_POST['folder']]['actual_path'] . "/" . $_POST['filename'] ), null );

				echo $file_system[$_POST['folder']]['actual_path'] . "/" . $_POST['filename'] . "<br />";

				$attachment = array(
					'guid'           => $file_system[$_POST['folder']]['actual_path'] . "/" . $_POST['filename'], 
					'post_mime_type' => $filetype['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $_POST['filename'] ) ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				);
				
				$attach_id = wp_insert_attachment($attachment, $file_system[$_POST['folder']]['actual_path'] . "/" . $_POST['filename']);
				
				$data = wp_generate_attachment_metadata( $attach_id, $file_system[$_POST['folder']]['actual_path'] . "/" . $_POST['filename'] );
				
				update_post_meta($attach_id, "_wp_attachment_metadata", $data, true);
				
				update_post_meta($post->ID, "molie_file_" . $_POST['item'], $attach_id);
				update_post_meta($attach_id, "CanvasCourse", get_post_meta($post->ID, "courseID", true));
				update_post_meta($attach_id, "CanvasFileVerifier", $_POST['verifier']);
				update_post_meta($attach_id, "CanvasCourseIDFileID", get_post_meta($post->ID, "courseID", true) . "," . $_POST['item']);
				
				$file_url = get_post_meta($post->ID, "courseURL", true) . "/courses/" . get_post_meta($post->ID, "courseID", true) . "/files/" . $_POST['item'] . "/download?verifier=" . $_POST['verifier'];
				
				update_post_meta($attach_id, "CanvasFileURL", $file_url);
				
				echo __("File Downloaded");
			}
			else
			{
				echo "Nonce failed";
			}
			wp_die();
		}	
	
	}
	
	$MOLIEfileAjax = new MOLIEfileAjax();
	