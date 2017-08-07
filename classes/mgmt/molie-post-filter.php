<?PHP

	class MOLIEpostFilter{
	
		function __construct(){
			add_filter('pre_get_posts', array($this, 'filter_admin_pages'));
			add_action('admin_head',  array($this, 'suppress_new'));
			add_action('restrict_manage_posts', array($this, 'extra_filter') );
		}
		
		function extra_filter(){
		
			if(strpos($_SERVER['REQUEST_URI'],"canvas_linked=true")!=FALSE && strpos($_SERVER['REQUEST_URI'],"edit.php")!=FALSEs){
			?>
				<select name="course">
					<option><?PHP echo __("All Courses"); ?></option>
					<?PHP
					
						global $wpdb;
						
						$courses = $wpdb->get_results("select * from " . $wpdb->prefix . "posts where post_type='linkedcanvascourse' and post_status='publish'");
						
						foreach($courses as $course){
							$value = get_post_meta($course->ID, "courseID", true);
							?><option <?PHP if($_GET['course']==$value){ echo " selected "; } ?> value="<?PHP echo $value; ?>"><?PHP echo $course->post_title; ?></option><?PHP
						}
						
					?>
				</select>
				<input type="hidden" name="canvas_linked" value="true" />
			<?PHP
			}
		}
		
		function suppress_new(){
			if(strpos($_SERVER['REQUEST_URI'],"canvas_linked=true")!=FALSE && strpos($_SERVER['REQUEST_URI'],"edit.php")!=FALSE){
				if (current_user_can( "edit_posts" ) ){
					global $post_type_object;
					$post_type_object->cap->create_posts = "edit_" . time();
				}
			}
		}
	
		function filter_admin_pages($query){
			if(strpos($_SERVER['REQUEST_URI'],"canvas_linked=true")!=FALSE && strpos($_SERVER['REQUEST_URI'],"edit.php")!=FALSE){
				$query->set('meta_key', "CanvasCourse");
				if(isset($_GET['course'])){
					$query->set('meta_value', $_GET['course']);
				}
			}
			return $query;
		}
	
	}
	
	$MOLIEpostFilter = new MOLIEpostFilter();
	