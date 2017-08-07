<?PHP

	class MOLIEquizanswerPost{
	
		function __construct(){
			add_action("init", array($this, "create"));
		}
	
		function create(){
	
			$labels = array(
				'name' => 'Linked Canvas Quiz Answer',
				'singular_name' => 'Linked Canvas Quiz Answer',
				'add_new' => 'Add new Linked Canvas Quiz Answer',
				'add_new_item' => 'Add Linked Canvas Quiz Answer',
				'edit_item' => 'Edit Linked Canvas Quiz Answer',
				'new_item' => 'New Linked Canvas Quiz Answer',
				'all_items' => 'All Linked Canvas Quiz Answers',
				'view_item' => 'View Linked Canvas Quiz Answers',
				'search_items' => 'Search Linked Canvas Quiz Answer',
				'not_found' =>  'No Linked Canvas Quiz Answers found',
				'not_found_in_trash' => 'No Linked Canvas Quiz Answers found in trash', 
				'parent_item_colon' => '',
				'menu_name' => 'Linked Canvas Quiz Answers'
			);
				
			$args = array(
				'labels' => $labels,
				'public' => false,
				'show_ui' => true,
				'capability_type' => 'linkedcanvasqa',
				'hierarchical' => false,
				'rewrite' => false,
				'supports' => array('title'),
				'menu_position' => 98,
				'exclude_from_search' => true,
				'publically_queryable' => true,
				'taxonomies' => array('category',),
			);
		
			register_post_type( 'linkedcanvasqa' , $args );

		}	
	
	}
	
	$MOLIEquizanswerPost = new MOLIEquizanswerPost();
	