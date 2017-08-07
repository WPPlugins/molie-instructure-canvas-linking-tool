<?PHP

	class MOLIEassignmentPost{
	
		function __construct(){
			add_action("init", array($this, "create"));
		}
	
		function create(){
	
			$labels = array(
				'name' => 'Linked Canvas Assignment',
				'singular_name' => 'Linked Canvas Assignment',
				'add_new' => 'Add new Linked Canvas Assignment',
				'add_new_item' => 'Add Linked Canvas Assignment',
				'edit_item' => 'Edit Linked Canvas Assignment',
				'new_item' => 'New Linked Canvas Assignment',
				'all_items' => 'All Linked Canvas Assignments',
				'view_item' => 'View Linked Canvas Assignments',
				'search_items' => 'Search Linked Canvas Assignment',
				'not_found' =>  'No Linked Canvas Assignments found',
				'not_found_in_trash' => 'No Linked Canvas Assignments found in trash', 
				'parent_item_colon' => '',
				'menu_name' => 'Linked Canvas Assignments'
			);
				
			$args = array(
				'labels' => $labels,
				'public' => true,
				'show_ui' => true,
				'capability_type' => 'linkedcanvasamt',
				'hierarchical' => false,
				'rewrite' => false,
				'supports' => array('title','editor'),
				'menu_position' => 99,
				'exclude_from_search' => true,
				'publically_queryable' => true,
				'taxonomies' => array('category'),
			);
		
			register_post_type( 'linkedcanvasamt' , $args );
			
		}	
	
	}
	
	$MOLIEassignmentPost = new MOLIEassignmentPost();
	