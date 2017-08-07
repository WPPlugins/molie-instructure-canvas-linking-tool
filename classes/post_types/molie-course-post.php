<?PHP

	class MOLIEcoursePost{
	
		function __construct(){
			add_action("init", array($this, "create"));
		}
	
		function create(){
	
			$labels = array(
				'name' => 'Linked Canvas Course',
				'singular_name' => 'Linked Canvas Course',
				'add_new' => 'Add new Linked Canvas Course',
				'add_new_item' => 'Add Linked Canvas Course',
				'edit_item' => 'Edit Linked Canvas Course',
				'new_item' => 'New Linked Canvas Course',
				'all_items' => 'All Linked Canvas Courses',
				'view_item' => 'View Linked Canvas Courses',
				'search_items' => 'Search Linked Canvas Course',
				'not_found' =>  'No Linked Canvas Courses found',
				'not_found_in_trash' => 'No Linked Canvas Courses found in trash', 
				'parent_item_colon' => '',
				'menu_name' => 'Linked Canvas Courses'
			);
				
			$args = array(
				'labels' => $labels,
				'public' => false,
				'show_ui' => true,
				'capability_type' => 'linkedcanvascourse',
				'hierarchical' => false,
				'rewrite' => false,
				'supports' => array('title'),
				'menu_position' => 99,
				'exclude_from_search' => true,
				'publically_queryable' => true,
			);
		
			register_post_type( 'linkedcanvascourse' , $args );

		}	
	
	}
	
	$MOLIEcoursePost = new MOLIEcoursePost();
	