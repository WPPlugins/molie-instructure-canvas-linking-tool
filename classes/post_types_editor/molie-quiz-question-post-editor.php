<?PHP

	class MOLIEquizQuestionPostEditor{
	
		function __construct(){
			add_action("admin_head", array($this, "metabox"));
			add_action("save_post", array($this, "save_post"));
		}
	
		function metabox(){
	
			global $post;
		
			if(isset($_GET['action'])){
	
				if($_GET['action']=="edit" && $post->post_type=="linkedcanvasqa"){
					add_meta_box("linkedcanvasqameta",__("Edit course"),array($this,"editor"));
					wp_register_style( 'molie_admin_qa_editor_css', plugins_url() . '/molie-instructure-canvas-linking-tool/css/molie-admin-qa-editor.css', false, '1.0.0' );
					wp_enqueue_style( 'molie_admin_qa_editor_css' );
				}

			}

		}	
		
		function editor(){
		
			global $post;
			$course_id = get_post_meta($post->ID, "courseID", true);
			wp_editor($post->post_content,"linkedcanvasqa",array("name"=>"linkedcanvasqa","editor_height"=>100)); 
			$counter = 1;
			while(get_post_meta($post->ID, "id_" . $counter, true)!=""){
				?>
				<input type="hidden" size="100" value="" name="qa_id_<?PHP echo $counter; ?>" />
				<p><label><?PHP echo __("Answer"); ?> <?PHP echo $counter; ?></label></p>
				<p><textarea name="qa_answer_<?PHP echo $counter; ?>" ><?PHP echo get_post_meta($post->ID, "qa_answer_" . $counter, true); ?></textarea></p>
				<p><label><?PHP echo __("Feedback"); ?> <?PHP echo $counter; ?></label></p>
				<p><textarea name="qa_feedback_<?PHP echo $counter; ?>"><?PHP echo get_post_meta($post->ID, "qa_feedback_" . $counter, true); ?></textarea></p>
				<p><label><?PHP echo __("Score"); ?> <?PHP echo $counter; ?></label></p>
				<p><textarea name="qa_weight_<?PHP echo $counter; ?>"><?PHP echo get_post_meta($post->ID, "qa_weight_" . $counter, true); ?></textarea></p>
				<?PHP
				$counter++;
			}			
		}
		
		function save_post($post_id){
			$post = get_post($post_id);
			if($post->post_type=="linkedcanvasqa"){
				if(isset($_POST['linkedcanvasqa'])){
					$counter = 1;
				
					while(isset($_POST['qa_id_' . $counter])){
						update_post_meta($post_id, "qa_answer_" . $counter, $_POST['qa_answer_' . $counter]);
						update_post_meta($post_id, "qa_feedback_" . $counter, $_POST['qa_feedback_' . $counter]);
						update_post_meta($post_id, "qa_weight_" . $counter, $_POST['qa_weight_' . $counter]); 
						$counter++;
					}
				
					remove_action( 'save_post', array( $this, 'save_post' ) );
					wp_update_post(
						array(
							"ID" => $post_id,
							"post_content" => $_POST['linkedcanvasqa']
						)
					);
					add_action( 'save_post', array( $this, 'save_post' ) );
				}
				
			}
		}
	
	}
	
	$MOLIEquizQuestionPostEditor = new MOLIEquizQuestionPostEditor();
	