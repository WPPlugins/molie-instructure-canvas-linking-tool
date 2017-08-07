<?PHP

	class MOLIEhideCalendarPost{
	
		function __construct(){
			add_filter("pre_get_posts", array($this, "hide_calendar"));
		}
	
		function hide_calendar($query){
			if(is_admin()){
				if(is_callable("get_current_screen")){
					$screen = get_current_screen();
					if($screen->base=="edit"){
						global $wpdb;
						$calendars = array();
						$data = $wpdb->get_results("select * from " . $wpdb->prefix . "postmeta where meta_key = 'CanvasCalendar'");
						foreach($data as $calendar){
							array_push($calendars, $calendar->post_id);
						}					
						$query->query_vars['post__not_in'] = array_merge($query->query_vars['post__not_in'], $calendars);
					}
				}
			}
		}	
	
	}
	
	$MOLIEhideCalendarPost = new MOLIEhideCalendarPost();
	