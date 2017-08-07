<?PHP
	class MOLIEpostLink{
	
		function __construct(){
			add_action("wp_ajax_molie_post_link", array($this, "link"));
		}
		
		function new_form($post_id, $url){
			?><p><?PHP echo __("This course is Canvas linked"); ?></p><?PHP
			?><p><a href="javascript:molie_canvas_unlink(<?PHP echo $post_id; ?>);"><?PHP echo __("Unlink Course, but leave on Canvas"); ?></a></p><?PHP
			?><p><a href="javascript:molie_canvas_unlink_delete(<?PHP echo $post_id; ?>);"><?PHP echo __("Unlink and delete from course"); ?></a></p><?PHP
			?><p><a href="<?PHP echo $url; ?>);"><?PHP echo __("See this page on Canvas"); ?></a></p><?PHP
		}
	
		function link(){
		
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
			
			$post = get_post($_POST['page_id']);
			$publish = "true";
			if($post->post_status!="publish"){
				$publish = "false";
			}
			
			$pages = new InstructureCanvasAPI\Courses\Pages\Pages();
			$pages->setAPI($API);
			
			$pages->setCourseID($_POST['course']);
			$data = $pages->createPage(	$_POST['title'],
										array(
												"wiki_page[body]" => stripslashes($_POST['content']), 
												"wiki_page[title]" => $_POST['title'],
												"wiki_page[published]" => $publish,
												"wiki_page[editing_roles]" => "teachers",
											)
										);			
										
			update_post_meta($post->ID, "CanvasLinked", true);
			update_post_meta($post->ID, "CanvasCourse", $_POST['course']);
			update_post_meta($post->ID, "postCanvasID", $data->content->page_id);
			update_post_meta($post->ID, "postURL", $data->content->url);
			update_post_meta($post->ID, "postHTMLURL", $data->content->html_url);
			
			$modules = new InstructureCanvasAPI\Courses\Modules\Modules();
			$modules->setAPI($API);
			$modules->setCourseID($_POST['course']);
			$data = $modules->addModuleItem( $_POST['module'], 
											array(
												"module_item[title]" => $_POST['title'],
												"module_item[type]"	=> "Page",
												"module_item[page_url]"	=> $data->content->url,
												"module_item[position]" => $_POST['place'],
												"module_item[content_id]" => $data->content->page_id,
												"module_item[indent]" => $_POST['indent']
											)
										);
			
			$this->new_form($_POST['page_id'], $data->content->html_url);
			
			die();
		
		}
		
	}
	
	$MOLIEpostLink = new MOLIEpostLink();
	