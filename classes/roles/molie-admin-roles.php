<?PHP

	class MOLIEadminRoles{
	
		function __construct(){
			add_action("init", array($this, "create"));
		}
	
		function create(){
		
			$types = array("course", "quiz", "qa", "dis", "user", "amt");
	
			$AdminCapabilities = array(
				'edit_linkedcanvas?',
				'read_linkedcanvas?',
				'delete_linkedcanvas?',
				'edit_linkedcanvas?s',
				'edit_others_linkedcanvas?s',
				'publish_linkedcanvas?s',
				'read_private_linkedcanvas?s',
				'delete_linkedcanvas?s',
				'delete_private_linkedcanvas?s',
				'delete_published_linkedcanvas?s',
				'delete_others_linkedcanvas?s',
				'edit_private_linkedcanvas?s',
				'edit_published_linkedcanvas?s',
				'edit_linkedcanvas?s'
			);
			
			$AuthorCapabilities = array(
				'edit_linkedcanvas?',
				'read_linkedcanvas?',
				'delete_linkedcanvas?',
				'edit_linkedcanvas?s',
				'publish_linkedcanvas?s',
				'read_private_linkedcanvas?s',
				'delete_linkedcanvas?s',
				'delete_private_linkedcanvas?s',
				'delete_published_linkedcanvas?s',
				'edit_private_linkedcanvas?s',
				'edit_published_linkedcanvas?s',
				'edit_linkedcanvas?s'
			);
			
			$get_users = get_users();
		
			foreach ( $get_users as $user )
			{
				if(in_array("administrator", $user->roles)){
					$user = new WP_User( $user->data->ID );
					foreach ( $AdminCapabilities as $capability ){
						foreach($types as $type){
							$user->add_cap( str_replace("?",$type,$capability) );
						}
					}
				}
				if(in_array("editor", $user->roles)){
					$user = new WP_User( $user->data->ID );
					foreach ( $AuthorCapabilities as $capability ){
						foreach($types as $type){
							$user->add_cap( str_replace("?",$type,$capability) );
						}
					}
				}
			}

		}
	
	}
	
	$MOLIEadminRoles = new MOLIEadminRoles();
	