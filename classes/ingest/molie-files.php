<?PHP

	class MOLIEfiles{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action("admin_enqueue_scripts", array($this, "admin_scripts_and_styles"));
		}
	
		function admin_scripts_and_styles(){
			if(isset($_GET['page'])){
				if($_GET['page']=="molie_files"){
					wp_register_style( 'molie_admin_file_css', plugins_url() . '/molie-instructure-canvas-linking-tool/css/molie-admin-file.css', false, '1.0.0' );
					wp_enqueue_style( 'molie_admin_file_css' );
					wp_enqueue_script( 'molie-admin-select', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-select.js', array( 'jquery' ) );
					wp_enqueue_script( 'molie-admin-file', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-file.js', array( 'jquery' ) );
					wp_localize_script( 'molie-admin-file', 'molie_admin_file', 
																					array( 
																							'ajaxURL' => admin_url("admin-ajax.php"),
																							'nonce' => wp_create_nonce("molie_admin_file")
																						) 
					);
				}
			}
		}
		
		function menu_create(){
			add_submenu_page( "molie_mgmt", __("Choose Course Files"), __("Choose Course Files"), 'edit_linkedcanvascourse', "molie_files", array($this,"files"));
		}
		
		function files(){
			if(!isset($_POST['molie-files-nonce'])){
				
				$args = array(
					"post_type" => "linkedcanvascourse",
					"post_status" => "publish"
				);
		
				$courses = get_posts($args);
		
				if(count($courses)!=0){
				
					echo "<h2>" . __("Choose a course") . "</h2>";
				
					$nonce = wp_create_nonce("molie-files");
					foreach($courses as $course){
					?>
						<form method="post" action="<?PHP echo admin_url("admin.php?page=molie_files"); ?>">
							<p><?PHP echo $course->post_title; ?></p>
							<input type="hidden" name="molie-files-nonce" value="<?PHP echo $nonce; ?>"/>
							<input type="hidden" name="url" value="<?PHP echo get_post_meta($course->ID, "courseURL", true); ?>" />
							<input type="hidden" name="token" value="<?PHP echo get_post_meta($course->ID, "courseToken", true); ?>" />
							<input name="course_ID" type="hidden" value="<?PHP echo $course->ID; ?>" />
							<input type="submit" value="<?PHP echo __("Get Files"); ?>" />
						</form>
					<?PHP
					}
					
				}
			
			}
			else
			{
				if(wp_verify_nonce($_POST['molie-files-nonce'], "molie-files"))
				{
				
					$course_id = get_post_meta($_POST['course_ID'], "courseID", true);
					$course_token = get_post_meta($_POST['course_ID'], "courseToken", true);
					$course_url = get_post_meta($_POST['course_ID'], "courseURL", true);
			
					require_once(__DIR__ . "/../../API/Psr4AutoloaderClass.php");
					$loader = new Psr4AutoloaderClass;
					$loader->register();
					$loader->addNamespace('InstructureCanvasAPI', __DIR__ . "/../../API/InstructureCanvasAPI/src");
					
					$API = new InstructureCanvasAPI\InstructureCanvasAPI( 
																		array(
																			"site" => $course_url,
																			"token" => $course_token,
																			"webService" => "CURL"
																		)
																	);
					
					$folders = new InstructureCanvasAPI\Courses\Folders\Folders();
					$folders->setAPI($API);
					$folders->setCourseID($course_id);
					$data = $folders->getFolders();
					$folders = $data->content;
					$this->file_system = Array();
					foreach($folders as $folder){
						if($folder->name=="course files"){
							$base_id = $folder->id;
						}
						if(!isset($this->file_system[$folder->id])){
							$this->file_system[$folder->id] = Array("name" => $folder->name, "id" => $folder->id, "url" => $folder->files_url);
							$this->file_system[$folder->id]['children'] = Array();
							if($folder->parent_folder_id!=""){
								if(!isset($this->file_system[$folder->parent_folder_id])){
									$this->file_system[$folder->parent_folder_id] = Array();
									$this->file_system[$folder->parent_folder_id]["children"] = Array();
								}
								array_push($this->file_system[$folder->parent_folder_id]['children'],$folder->id);
							}
						}
						else
						{
							$this->file_system[$folder->id]["name"] = $folder->name;
							$this->file_system[$folder->id]["id"] = $folder->id;
							$this->file_system[$folder->id]["url"] = $folder->files_url;
							if($folder->parent_folder_id!=""){
								if(!isset($this->file_system[$folder->parent_folder_id])){
									$this->file_system[$folder->parent_folder_id] = Array();
									$this->file_system[$folder->parent_folder_id]["children"] = Array();
								}
								array_push($this->file_system[$folder->parent_folder_id]['children'],$folder->id);
							}
						}
					}
					$upload_dir = wp_upload_dir();
					$base_path = $upload_dir['basedir'];
					if(!is_dir($base_path . "/" . $course_id)){
						mkdir($base_path . "/" . $course_id);
					}	
					$this->make_folders($base_id, $base_path . "/" . $course_id);
					foreach($this->file_system as $index => $id){
						unset($this->file_system[$index]['name']);
						unset($this->file_system[$index]['id']);
						unset($this->file_system[$index]['children']);
					}
					delete_post_meta($_POST['course_ID'], "courseFileSystem");
					add_post_meta($_POST['course_ID'], "courseFileSystem", $this->file_system, true);
					
					$files = new InstructureCanvasAPI\Courses\Files\Files();
					$files->setAPI($API);
					$files->setCourseID($course_id);
					$data = $files->getFiles();
					$files = $data->content;
					if($data){
						if(count($data->content)!=0){	
							echo "<div id='molie_choose'>";
							echo "<h2>" . __("Files in this course") . "</h2>";
							echo "<p>" . __("You should import all files") . "</p>";
							echo "<div id='importProgress'><p><strong>" . __("Import Progress") . " <span id='importTotal'></span></strong></p><div id='importProgressBar'></div></div>";
							echo '<form id="molie_choose_form" action="javascript:function connect(){return false;};">';
							echo "<input type='submit' id='molie_file_submit' value='" . __("Import files") . "' />";	
							echo "<input type='submit' id='molie_choose_skip' value='" . __("Skip step") . "' />";	
							echo "<p><span><a href='javascript:molie_select_all()'>" . __("Select All") . "</a></span> <span><a href='javascript:molie_unselect_all()'>" . __("Unselect All") . "</a></span></p>"; 
							echo "<ul>";
							foreach($data->content as $file){
								echo "<li>";
								if(isset($file->thumbnail_url)){
									echo "<img src='" . $file->thumbnail_url . "' />";
								}
								$file_size = ($file->size / 1000);
								$file_size_parts = explode(".", $file_size);
								if(strlen($file_size_parts[0])>=4){
									$file_size = ($file->size / 1000000);
									$file_size = substr($file_size,0,4) . "MB";
								}
								else
								{
									$file_size = substr($file_size,0,4) . "KB";
								}
								if($this->is_file_downloaded($_POST['course_ID'], $file))
								{
									$checked = "";
									$downloaded = __("Already Downloaded");
								}
								else
								{
									$checked = "checked";
									$downloaded = "";
								}
								
								$parts = explode("verifier=", $file->url);
								$verifier = array_pop($parts);
								echo "<input verifier='" . $verifier . "' type='checkbox' " . $checked . " filename='" . $file->display_name . "' folder='" . $file->folder_id . "' url='" . $file->url . "' id='" . $file->id . "' course_post='" . $_POST['course_ID'] . "'>" . $file->display_name . " (" . $file_size . ")<span id='update" . $file->id . "'>" . $downloaded . "</span></li>";
							}
							echo "</ul>";
							echo "<input type='submit' id='molie_file_submit' value='" . __("Import files") . "' />";
							echo "</form>";
							echo "</div>";
							echo "<div id='molie_quiz_assignments'>";
							echo '<form method="post" action="' . admin_url("admin.php?page=molie_quiz") . '">';
							echo "<input name='course_id' type='hidden' value='" . $_POST['course_ID'] . "' />";
							echo wp_nonce_field("molie-quiz-nonce", "molie-quiz-nonce");
							echo "<input type='submit' value='" . __("Now, Quizzes") . "' />";
							echo "</form>";
							echo "</div>";
						}
					}
					$loader->unregister();
				}
			}
		}
		
		function is_file_downloaded($post_id, $file){
			if(file_exists($this->file_system[$file->folder_id]['actual_path'] . "/" . $file->display_name)){ 
				return true;
			}
			else
			{
				return false;
			}
		}
		
		function make_folders($id, $path){
			if(!is_dir($path . "/" . $this->file_system[$id]['name'])){
				mkdir($path . "/" . $this->file_system[$id]['name']);
				$path = $path . "/" . $this->file_system[$id]['name'];
				$this->file_system[$id]['actual_path'] = str_replace("\\", "/", $path);
			}
			else
			{
				$path = $path . "/" . $this->file_system[$id]['name'];
				$this->file_system[$id]['actual_path'] = str_replace("\\", "/", $path);
			}
			
			foreach($this->file_system[$id]['children'] as $id){
				$this->make_folders($id, $path);
			}
		}
	
	}
	
	$MOLIEfiles = new MOLIEfiles();
	