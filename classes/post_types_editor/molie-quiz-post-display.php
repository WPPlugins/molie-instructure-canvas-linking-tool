<?PHP

	class MOLIEquizPostDisplay{
	
		function __construct(){
			add_filter("the_content", array($this, "display"));
			add_action("wp_enqueue_scripts", array($this, "admin_scripts_and_styles"));
		}
	
		function admin_scripts_and_styles(){
			wp_enqueue_script( 'molie-admin-quiz-display', plugins_url() . '/molie-instructure-canvas-linking-tool/js/molie-admin-quiz-display.js', array( 'jquery' ) );
		}
		
		function display($content){
		
			global $post;
			
			echo $content;
			
			global $wpdb;
			$questions = $wpdb->get_results("select * from " . $wpdb->prefix . "postmeta where meta_key like '%canvasQuizQuestion_%' and post_id = " . $post->ID);
			$q_counter = 1;
			foreach($questions as $data){
				$post = get_post($data->meta_value);
				?><p><strong><?PHP echo $post->post_content; ?></strong></h3><?PHP
				$counter = 1;
				while(get_post_meta($post->ID, "qa_id_" . $counter, true)!=""){
					?>
					<p class="canvasQuestion" counter="<?PHP echo $q_counter; ?>" weight="<?PHP echo addslashes(get_post_meta($post->ID, "qa_weight_" . $counter, true)); ?>" feedback="<?PHP echo addslashes(get_post_meta($post->ID, "qa_feedback_" . $counter, true)); ?>"><?PHP echo get_post_meta($post->ID, "qa_answer_" . $counter, true); ?></p>
					<?PHP
					$counter++;
				}
				if($counter!=1){
					?><span><?PHP echo __("Click on the right answer"); ?></span><?PHP
				}
				?><p id='feedback_<?PHP echo $q_counter++; ?>'></p><?PHP
			}
		}
		
	}
	
	$MOLIEquizPostDisplay = new MOLIEquizPostDisplay();
	