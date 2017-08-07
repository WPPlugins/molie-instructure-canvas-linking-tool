<?PHP

	class MOLIEquizPostEditor{
	
		function __construct(){
			add_action("admin_head", array($this, "metabox"));
			add_action("save_post", array($this, "save_post"));
		}
	
		function metabox(){
	
			global $post;
		
			if(isset($_GET['action'])){
	
				if($_GET['action']=="edit" && $post->post_type=="linkedcanvasquiz"){
					add_meta_box("linkedcanvasquizmeta",__("Edit course"),array($this,"editor"));
				}

			}

		}	
		
		function editor(){
		
			global $post;
			$course_id = get_post_meta($post->ID, "courseID", true);
			wp_editor($post->post_content,"linkedcanvasquiz",array("name"=>"linkedcanvasquiz", "editor_height"=>"150")); 
			
			global $wpdb;
			$questions = $wpdb->get_results("select * from " . $wpdb->prefix . "postmeta where meta_key like '%canvasQuizQuestion_%' and post_id = " . $_GET['post']);
			foreach($questions as $data){
				$post = get_post($data->meta_value);
				?><p><a href="<?PHP echo network_admin_url("post.php?post=" . $post->ID . "&action=edit"); ?>"><?PHP echo $post->post_title; ?></a></p><?PHP
			}
		}
		
		function save_post($post_id){
			$post = get_post($post_id);
			if($post->post_type=="linkedcanvasquiz"){
				if(isset($_POST['linkedcanvasquiz'])){
				remove_action( 'save_post', array( $this, 'save_post' ) );
					wp_update_post(
						array(
							"ID" => $post_id,
							"post_content" => $_POST['linkedcanvasquiz']
						)
					);
					add_action( 'save_post', array( $this, 'save_post' ) );
				}
			}
		}
	
	}
	
	$MOLIEquizPostEditor = new MOLIEquizPostEditor();
	