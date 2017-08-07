<?PHP
	class MOLIEtinyMCE{
	
		function __construct(){
			add_filter( 'tiny_mce_before_init', array( $this, 'mce_options' ) );
		}
		
		function mce_options($init){
			global $post;
			$linked = get_post_meta($post->ID, "CanvasLinked", true);
			
			if($linked==1){
				$init['wpautop'] = false;
				$init['valid_children'] = "+ol[br],+ul[br]";
				$init['fix_list_elements'] = false;
				$init['forced_root_block'] = false;
				$init['tadv_noautop'] = true;
			}
			
			return $init;
		
		}
		
	}
	
	$MOLIEtinyMCE = new MOLIEtinyMCE();
	