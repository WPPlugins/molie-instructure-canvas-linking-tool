<?PHP
	
	class MOLIEcourseCheckAjax{
	
		function __construct(){;
			add_action("wp_ajax_molie_admin_course_check_pages", array($this, "check_pages"));
			add_action("wp_ajax_molie_course_check_files", array($this, "check_files"));
			add_action("wp_ajax_molie_course_check_quizzes", array($this, "check_quizzes"));
			add_action("wp_ajax_molie_course_check_assignments", array($this, "check_assignments"));
			add_action("wp_ajax_molie_course_check_discussions", array($this, "check_discussions"));
			add_action("wp_ajax_molie_course_check_users", array($this, "check_users"));
			add_action("wp_ajax_molie_course_check_calendar", array($this, "check_calendar"));
		}
		
		function check_pages(){

			if(wp_verify_nonce($_POST['nonce'], "molie_admin_check"))
			{
	
				require_once(__DIR__ . "/../../API/Psr4AutoloaderClass.php");
				$loader = new Psr4AutoloaderClass;
				$loader->register();
				$loader->addNamespace('InstructureCanvasAPI', __DIR__ . "/../../API/InstructureCanvasAPI/src");
					
				$API = new InstructureCanvasAPI\InstructureCanvasAPI( 
																	array(
																		"site" => get_post_meta($_POST['course'],"courseURL",true),
																		"token" => get_post_meta($_POST['course'],"courseToken",true),
																		"webService" => "CURL"
																	)
																);
	
				$pages = new InstructureCanvasAPI\Courses\Pages\Pages();
				$pages->setAPI($API);
				$pages->setCourseID(get_post_meta($_POST['course'],"courseID",true));
				$data = $pages->getPages();
				$diff = false;
				$pages_diff = array();
				foreach($data->content as $item){
					global $wpdb;
					$data = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key ='postHTMLURL' and meta_value = '" . $item->html_url . "'");
					if(count($data)==0){
						$diff=true;
						array_push($pages_diff, $item);
					}
				}
				if($diff){
					echo json_encode(array($diff,$pages_diff));
				}else{
					echo json_encode(array($diff,$pages_diff));
				}	
			
			}
			else
			{
				echo "Nonce failed";
			}
			wp_die();
		}	
	
		function check_files(){

			if(wp_verify_nonce($_POST['nonce'], "molie_admin_check"))
			{
	
				require_once(__DIR__ . "/../../API/Psr4AutoloaderClass.php");
				$loader = new Psr4AutoloaderClass;
				$loader->register();
				$loader->addNamespace('InstructureCanvasAPI', __DIR__ . "/../../API/InstructureCanvasAPI/src");
					
				$API = new InstructureCanvasAPI\InstructureCanvasAPI( 
																	array(
																		"site" => get_post_meta($_POST['course'],"courseURL",true),
																		"token" => get_post_meta($_POST['course'],"courseToken",true),
																		"webService" => "CURL"
																	)
																);
	
				$files = new InstructureCanvasAPI\Courses\Files\Files();
				$files->setAPI($API);
				$course_id = get_post_meta($_POST['course'],"courseID",true);
				$files->setCourseID($course_id);
				$data = $files->getFiles();
				$diff = false;
				$files_diff = array();
				foreach($data->content as $item){
					global $wpdb;
					$data = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key ='CanvasCourseIDFileID' and meta_value like '%" . $course_id . "," . $item->id . "%'");
					if(count($data)==0){
						$diff=true;
						array_push($files_diff, $item);
					}
				}
				if($diff){
					echo json_encode(array($diff,$files_diff));
				}else{
					echo json_encode(array($diff,$files_diff));
				}	
			
			}
			else
			{
				echo "Nonce failed";
			}
			wp_die();
		}	
	
		function check_quizzes(){

			if(wp_verify_nonce($_POST['nonce'], "molie_admin_check"))
			{
	
				require_once(__DIR__ . "/../../API/Psr4AutoloaderClass.php");
				$loader = new Psr4AutoloaderClass;
				$loader->register();
				$loader->addNamespace('InstructureCanvasAPI', __DIR__ . "/../../API/InstructureCanvasAPI/src");
					
				$API = new InstructureCanvasAPI\InstructureCanvasAPI( 
																	array(
																		"site" => get_post_meta($_POST['course'],"courseURL",true),
																		"token" => get_post_meta($_POST['course'],"courseToken",true),
																		"webService" => "CURL"
																	)
																);
	
				$course_id = get_post_meta($_POST['course'],"courseID",true);
				$quizzes = new InstructureCanvasAPI\Courses\Quizzes\Quizzes();
				$quizzes->setAPI($API);
				$quizzes->setCourseID($course_id);
				$data = $quizzes->getquizzes();
				$diff = false;
				$quiz_diff = array();
				foreach($data->content as $item){
					global $wpdb;
					$data = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key ='canvasQuizURL' and meta_value like '%" . $item->html_url . "%'");
					if(count($data)==0){
						$diff=true;
						array_push($quiz_diff, $item);
					}
				}
				if($diff){
					echo json_encode(array($diff,$quiz_diff));
				}else{
					echo json_encode(array($diff,$quiz_diff));
				}	
			
			}
			else
			{
				echo "Nonce failed";
			}
			wp_die();
		}	
	
		function check_assignments(){

			if(wp_verify_nonce($_POST['nonce'], "molie_admin_check"))
			{
	
				require_once(__DIR__ . "/../../API/Psr4AutoloaderClass.php");
				$loader = new Psr4AutoloaderClass;
				$loader->register();
				$loader->addNamespace('InstructureCanvasAPI', __DIR__ . "/../../API/InstructureCanvasAPI/src");
					
				$API = new InstructureCanvasAPI\InstructureCanvasAPI( 
																	array(
																		"site" => get_post_meta($_POST['course'],"courseURL",true),
																		"token" => get_post_meta($_POST['course'],"courseToken",true),
																		"webService" => "CURL"
																	)
																);
	
				$course_id = get_post_meta($_POST['course'],"courseID",true);
				$assignments = new InstructureCanvasAPI\Courses\Assignments\Assignments();
				$assignments->setAPI($API);
				$assignments->setCourseID($course_id);
				$data = $assignments->getAssignments();
				$diff = false;
				$agmt_diff = array();
				foreach($data->content as $item){
					global $wpdb;
					$data = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key ='canvasQuizURL' and meta_value like '%" . $item->html_url . "%'");
					if(count($data)==0){
						$diff=true;
						array_push($agmt_diff, $item);
					}
				}
				if($diff){
					echo json_encode(array($diff,$agmt_diff));
				}else{
					echo json_encode(array($diff,$agmt_diff));
				}	
			
			}
			else
			{
				echo "Nonce failed";
			}
			wp_die();
		}	
	
		function check_discussions(){

			if(wp_verify_nonce($_POST['nonce'], "molie_admin_check"))
			{
	
				require_once(__DIR__ . "/../../API/Psr4AutoloaderClass.php");
				$loader = new Psr4AutoloaderClass;
				$loader->register();
				$loader->addNamespace('InstructureCanvasAPI', __DIR__ . "/../../API/InstructureCanvasAPI/src");
					
				$API = new InstructureCanvasAPI\InstructureCanvasAPI( 
																	array(
																		"site" => get_post_meta($_POST['course'],"courseURL",true),
																		"token" => get_post_meta($_POST['course'],"courseToken",true),
																		"webService" => "CURL"
																	)
																);
	
				$course_id = get_post_meta($_POST['course'],"courseID",true);
				$discussions = new InstructureCanvasAPI\Courses\DiscussionTopics\DiscussionTopics();
				$discussions->setAPI($API);
				$discussions->setCourseID($course_id);
				$data = $discussions->getDiscussionTopics();
				$diff = false;
				$diss_diff = array();
				foreach($data->content as $item){
					global $wpdb;
					$data = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key ='canvasDiscussionURL' and meta_value like '%" . $item->html_url . "%'");
					if(count($data)==0){
						$diff=true;
						array_push($diss_diff, $item);
					}
				}
				if($diff){
					echo json_encode(array($diff,$diss_diff));
				}else{
					echo json_encode(array($diff,$diss_diff));
				}	
			
			}
			else
			{
				echo "Nonce failed";
			}
			wp_die();
		}	
	
		function check_users(){

			if(wp_verify_nonce($_POST['nonce'], "molie_admin_check"))
			{
	
				require_once(__DIR__ . "/../../API/Psr4AutoloaderClass.php");
				$loader = new Psr4AutoloaderClass;
				$loader->register();
				$loader->addNamespace('InstructureCanvasAPI', __DIR__ . "/../../API/InstructureCanvasAPI/src");
					
				$API = new InstructureCanvasAPI\InstructureCanvasAPI( 
																	array(
																		"site" => get_post_meta($_POST['course'],"courseURL",true),
																		"token" => get_post_meta($_POST['course'],"courseToken",true),
																		"webService" => "CURL"
																	)
																);
	
				$course_id = get_post_meta($_POST['course'],"courseID",true);
				$roster = new InstructureCanvasAPI\Courses\Users\Users();
				$roster->setAPI($API);
				$roster->setCourseID($course_id);
				$data = $roster->getUsers();
				$diff = false;
				$user_diff = array();
				foreach($data->content as $item){
					global $wpdb;
					$data = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_key ='CanvasUserID' and meta_value like '%" . $item->id . "%'");
					if(count($data)==0){
						$diff=true;
						array_push($user_diff, $item);
					}
				}
				if($diff){
					echo json_encode(array($diff,$user_diff));
				}else{
					echo json_encode(array($diff,$user_diff));
				}	
			
			}
			else
			{
				echo "Nonce failed";
			}
			wp_die();
		}

		function check_calendar(){

			if(wp_verify_nonce($_POST['nonce'], "molie_admin_check"))
			{
	
				require_once(__DIR__ . "/../../API/Psr4AutoloaderClass.php");
				$loader = new Psr4AutoloaderClass;
				$loader->register();
				$loader->addNamespace('InstructureCanvasAPI', __DIR__ . "/../../API/InstructureCanvasAPI/src");
					
				$API = new InstructureCanvasAPI\InstructureCanvasAPI( 
																	array(
																		"site" => get_post_meta($_POST['course'],"courseURL",true),
																		"token" => get_post_meta($_POST['course'],"courseToken",true),
																		"webService" => "CURL"
																	)
																);
	
				$course_id = get_post_meta($_POST['course'],"courseID",true);
				$calendar = new InstructureCanvasAPI\Courses\Courses();
				$calendar->setAPI($API);
				$data = $calendar->getCoursesForThisUserWithSyllabus();
				foreach($data->content as $course_data){
					if($course_data->id==$course_id){
						$data = $course_data;
					}
				}
				$calendar = file_get_contents($data->calendar->ics);
				$calendar = explode("\n", $calendar);
				$parse = false;
				$content = array();
				$post_content = "";
				for($x=0;$x<=count($calendar);$x++){
					$line = trim(str_replace("\n","",$calendar[$x]));
					if($line=="BEGIN:VEVENT"){
						$parse = true;
					}
					if($parse){
						if($line=="END:VEVENT"){
							$post_content = $content['date'] . $content['summary'] . $content['location'] . $content['link'];
							$parse = false;
						}
						$parts = explode(":",$line);
						if(count($parts)!=1){
							if($parts[0]=="URL"){
								$data = str_replace("\n","",str_replace("\r","",str_replace($parts[0] . ":","",$line) . trim($calendar[$x+1])));
							}else{
								$data = $parts[1];
							}
							if($parts[0]=="LOCATION"){
								$content['location']= "<p>Location " . $data . "</p>";
							}
							if($parts[0]=="SUMMARY"){
								$content['summary'] = "<p>Summary " . $data . "</p>";
							}
							if($parts[0]=="URL"){
								$content['link'] = "<p>Link <a href='" . $data . "'>" . $data . "</a></p>";
							}
							if($parts[0]=="DTSTART"){
								if(strpos($data,"=")==FALSE){
									$time = mktime(substr($data,10,2),substr($data,12,2),substr($data,14,2),substr($data,4,2),substr($data,6,2),substr($data,0,4));
								}
								$content['date'] = "<p>Date " . date("l, jS F Y G:i:s A", $time) . "</p>";
							}
							if($parts[0]=="DTSTART;VALUE=DATE"){
								$time = mktime(0,0,0,substr($data,4,2),substr($data,6,2),substr($data,0,4));
								$content['date'] = "<p>Date " . date("l, jS F Y G:i:s A", $time) . "</p>";
							}
						}
					}
					
				}	
				
				$calendar = get_post(get_post_meta($_POST['course'], "canvasCalendarPage", true));
				
				if($calendar->post_content==$post_content){
					echo json_encode(array(true));
				}else{
					echo json_encode(array(false));
				}
			
			}
			else
			{
				echo "Nonce failed";
			}
			wp_die();
		}	
	
	}
	
	$MOLIEcourseCheckAjax = new MOLIEcourseCheckAjax();
	