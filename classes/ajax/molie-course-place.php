<?PHP
	
	class MOLIEcoursePlace{
	
		function __construct(){;
			add_action("wp_ajax_molie_post_course_place", array($this, "course_place"));
		}
		
		function course_place(){
			require_once(__DIR__ . "/../../API/Psr4AutoloaderClass.php");
			$loader = new Psr4AutoloaderClass;
			$loader->register();
			$loader->addNamespace('InstructureCanvasAPI', __DIR__ . "/../../API/InstructureCanvasAPI/src");
			
			$url = get_post_meta($_POST['course_post'],"courseURL",true);
			$token = get_post_meta($_POST['course_post'],"courseToken",true);
			
			$API = new InstructureCanvasAPI\InstructureCanvasAPI( 
																array(
																	"site" => $url,
																	"token" => $token,
																	"webService" => "CURL"
																)
															);
			
			$modules = new InstructureCanvasAPI\Courses\Modules\Modules();
			$modules->setAPI($API);
			$modules->setCourseID($_POST['course_id']);
			$data = $modules->getModules($_POST['course_id']);
			if($data){
				echo "<button onclick='javascript:molie_canvas_link_page(event);'>" . __("Add page, but not to a module") . "</button> ";
				echo "<button onclick='javascript:molie_canvas_link_page(event);'>" . __("Link page to the spot below") . "</button>";
				echo "<div id='course_choose'>";
				if(count($data->content)!=0){	
					$first = true;
					foreach($data->content as $module){
						echo "<p>" . __("Module") . " : " . $module->name . "</p>";
						echo "<ul>";
						$moduleItems = $modules->getModuleItems($module->id);
						foreach($moduleItems->content as $item){
							$last_positon = $item->position;
							echo "<li><input ";
							if($first){
								echo " checked ";
								$first = false;
							}
							echo " name='place' style='margin-left:" . (10*$item->indent) . "px;' indent='" . $item->indent . "' course_id='" . $_POST['course_post'] . "' page='" . $_POST['page'] . "' place='" . $item->position . "'  course='" . $_POST['course_id'] . "' module_name='" . addslashes($module->name) . "' module='" . $module->id . "' type='radio' />" . __("Add page before") . " " . $item->title . "</li>";
						}
						echo "<li><input name='place' style='margin-left:" . (10*$item->indent) . "px;' indent='" . $item->indent . "' course_id='" . $_POST['course_post'] . "' page='" . $_POST['page'] . "' place='" . ($last_position+1) . "'  course='" . $_POST['course_id'] . "' module_name='" . addslashes($module->name) . "' module='" . $module->id . "' type='radio' />" . __("Add at the end") . "</li>";
						echo "</ul>";
					}
				}
				echo "</div>";
			}

			$loader->unregister();
			die();
		}
		
	
	}
	
	$MOLIEcoursePlace = new MOLIEcoursePlace();
	