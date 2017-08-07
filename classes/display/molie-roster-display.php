<?PHP

	class MOLIErosterDisplay{
	
		function __construct(){
			add_filter('the_content', array($this, 'roster_display'));
		}
		
		function roster_display($content){
			global $post;
			if(get_post_meta($post->ID, "CanvasRoster", true)!=""){
				$users = get_posts( array("post_type" => 'linkedcanvasuser', "posts_per_page" => -1));
				foreach($users as $user){
					?><div style="width: 40%; display: inline-block; vertical-align:top">
						<a href="<?PHP echo $user->guid; ?>">
						<p><?PHP echo $user->post_title; ?></p>
						</a>
					</div><?PHP
				}
			}else{
				return $content;
			}
		}
	
	}
	
	$MOLIErosterDisplay = new MOLIErosterDisplay();
	