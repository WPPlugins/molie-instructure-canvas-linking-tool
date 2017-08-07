<?PHP

	class MOLIEuserPost{
	
		function __construct(){
			add_action("init", array($this, "create"));
		}
	
		function create(){
	
			$labels = array(
				'name' => 'Linked Canvas User',
				'singular_name' => 'Linked Canvas User',
				'add_new' => 'Add new Linked Canvas User',
				'add_new_item' => 'Add Linked Canvas User',
				'edit_item' => 'Edit Linked Canvas User',
				'new_item' => 'New Linked Canvas User',
				'all_items' => 'All Linked Canvas Users',
				'view_item' => 'View Linked Canvas Users',
				'search_items' => 'Search Linked Canvas User',
				'not_found' =>  'No Linked Canvas Users found',
				'not_found_in_trash' => 'No Linked Canvas Users found in trash', 
				'parent_item_colon' => '',
				'menu_name' => 'Linked Canvas Users'
			);
				
			$args = array(
				'labels' => $labels,
				'public' => true,
				'show_ui' => true,
				'capability_type' => 'linkedcanvasuser',
				'hierarchical' => false,
				'rewrite' => false,
				'supports' => array('title','editor'),
				'menu_position' => 99,
				'exclude_from_search' => true,
				'publically_queryable' => true,
				'taxonomies' => array('category',),
			);
		
			register_post_type( 'linkedcanvasuser' , $args );
			
		}	
	
	}
	
	$MOLIEuserPost = new MOLIEuserPost();
	