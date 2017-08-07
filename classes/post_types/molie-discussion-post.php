<?PHP

	class MOLIEdiscussionPost{
	
		function __construct(){
			add_action("init", array($this, "create"));
		}
	
		function create(){
	
			$labels = array(
				'name' => 'Linked Canvas Discussion',
				'singular_name' => 'Linked Canvas Discussion',
				'add_new' => 'Add new Linked Canvas Discussion',
				'add_new_item' => 'Add Linked Canvas Discussion',
				'edit_item' => 'Edit Linked Canvas Discussion',
				'new_item' => 'New Linked Canvas Discussion',
				'all_items' => 'All Linked Canvas Discussions',
				'view_item' => 'View Linked Canvas Discussions',
				'search_items' => 'Search Linked Canvas Discussions',
				'not_found' =>  'No Linked Canvas Discussions found',
				'not_found_in_trash' => 'No Linked Canvas Discussions found in trash', 
				'parent_item_colon' => '',
				'menu_name' => 'Linked Canvas Discussions'
			);
				
			$args = array(
				'labels' => $labels,
				'public' => true,
				'show_ui' => true,
				'capability_type' => 'linkedcanvasdis',
				'hierarchical' => false,
				'rewrite' => false,
				'supports' => array('title'),
				'menu_position' => 99,
				'exclude_from_search' => true,
				'publically_queryable' => true,
				'taxonomies' => array('category',),
			);
		
			register_post_type( 'linkedcanvasdis' , $args );

		}	
	
	}
	
	$MOLIEdiscussionPost = new MOLIEdiscussionPost();
	