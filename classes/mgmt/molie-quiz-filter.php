<?PHP

	class MOLIEquizFilter{
	
		function __construct(){
			add_action('admin_head',  array($this, 'suppress_new'));
		}
		
		function suppress_new(){
			if(strpos($_SERVER['REQUEST_URI'],"post.php")!=FALSE){
				global $post;				
				if(strpos($post->post_type,"linkedcanvas")!==FALSE){
					if (current_user_can( "edit_posts" ) ){
						global $post_type_object;
						$post_type_object->cap->create_posts = "edit_" . time();
					}
				}
			}
		}
	
	}
	
	$MOLIEquizFilter = new MOLIEquizFilter();
	