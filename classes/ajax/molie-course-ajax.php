<?PHP
	
	class MOLIEcourseAjax{
	
		function __construct(){;
			add_action("wp_ajax_molie_course_list", array($this, "course_list"));
		}
	
		function course_list(){
			if(wp_verify_nonce($_POST['nonce'], "molie_admin_choose"))
			{

				require_once(__DIR__ . "/../../API/Psr4AutoloaderClass.php");
				$loader = new Psr4AutoloaderClass;
				$loader->register();
				$loader->addNamespace('InstructureCanvasAPI', __DIR__ . "/../../API/InstructureCanvasAPI/src");
				
				$url = str_replace("http://","https://",$_POST['url']);
			
				if(strpos($url,"https://")===FALSE){
					$url = "https://" . $url;
				}
				
				$API = new InstructureCanvasAPI\InstructureCanvasAPI( 
																	array(
																		"site" => $url,
																		"token" => $_POST['token'],
																		"webService" => "CURL"
																	)
																);

				$courses = new InstructureCanvasAPI\Courses\Courses();

				$courses->setAPI($API);

				$data = $courses->getCoursesForThisUser();

				if($data){
					if(count($data->content)!=0){
						echo "<form method='post' action='" . $_POST['admin_url'] . "admin.php?page=molie_choose'>";
						wp_nonce_field("molie-link", "molie-link-nonce");
						echo "<p>" . __("Now Choose a Course") . "</p>";
						echo "<p><select name='molie_course'>";
						foreach($data->content as $course){
							echo "<option value='" . $course->id . "|" . $course->name . "'>" . $course->name . "</option>";
						}
						echo "</select></p>";
						echo "<input type='hidden' name='token' value='" . $_POST['token'] . "' />";
						echo "<input type='hidden' name='url' value='" . $url . "' />";
						echo "<input type='submit' value='" . __("Link Course") . "'>";
						echo "</form>";
					}
					else
					{
						echo "<p>" . __("There was an error connecting") . "</p>";
						echo "<p>" . $data[1] . "</p>";
					}
				}
				$loader->unregister();
			}
			else
			{
				print_r($_POST);
				echo "Nonce failed";
			}
			wp_die();
		}	
	
	}
	
	$MOLIEcourseAjax = new MOLIEcourseAjax();
	