<?PHP

	class MOLIEhideRosterPost{
	
		function __construct(){
			add_filter("pre_get_posts", array($this, "hide_roster"));
		}
	
		function hide_roster($query){
			if(is_admin()){
				$screen = get_current_screen();
				if(is_callable("get_current_screen")){
					if($screen->base=="edit"){
						global $wpdb;
						$rosters = array();
						$data = $wpdb->get_results("select * from " . $wpdb->prefix . "postmeta where meta_key = 'CanvasRoster'");
						foreach($data as $roster){
							array_push($rosters, $roster->post_id);
						}
						$query->query_vars['post__not_in'] = array_merge($query->query_vars['post__not_in'], $rosters);
					}
				}
			}
		}	
	
	}
	
	$MOLIEhideRosterPost = new MOLIEhideRosterPost();
	