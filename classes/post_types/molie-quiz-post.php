<?PHP

	class MOLIEquizPost{
	
		function __construct(){
			add_action("init", array($this, "create"));
		}
	
		function create(){
	
			$labels = array(
				'name' => 'Linked Canvas Quiz',
				'singular_name' => 'Linked Canvas Quiz',
				'add_new' => 'Add new Linked Canvas Quiz',
				'add_new_item' => 'Add Linked Canvas Quiz',
				'edit_item' => 'Edit Linked Canvas Quiz',
				'new_item' => 'New Linked Canvas Quiz',
				'all_items' => 'All Linked Canvas Quizs',
				'view_item' => 'View Linked Canvas Quizs',
				'search_items' => 'Search Linked Canvas Quiz',
				'not_found' =>  'No Linked Canvas Quizzes found',
				'not_found_in_trash' => 'No Linked Canvas Quizzes found in trash', 
				'parent_item_colon' => '',
				'menu_name' => 'Linked Canvas Quiz'
			);
				
			$args = array(
				'labels' => $labels,
				'public' => true,
				'show_ui' => true,
				'capability_type' => 'linkedcanvasquiz',
				'hierarchical' => false,
				'rewrite' => false,
				'supports' => array('title'),
				'menu_position' => 99,
				'exclude_from_search' => true,
				'publically_queryable' => true,
				'taxonomies' => array('category',),
			);
		
			register_post_type( 'linkedcanvasquiz' , $args );

		}	
	
	}
	
	$MOLIEquizPost = new MOLIEquizPost();
	