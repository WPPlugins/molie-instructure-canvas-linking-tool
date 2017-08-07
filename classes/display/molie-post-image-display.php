<?PHP

	class MOLIEpostImageDisplay{
	
		function __construct(){
			add_filter('the_content', array($this, 'change_pictures'));
		}
		
		function get_image($img_attrs){
			global $wpdb;
			$image_post = $wpdb->get_results("select post_id from " . $wpdb->prefix . "postmeta where meta_value = '" . $img_attrs[6] . "," . $img_attrs[8] . "'");
			return wp_get_attachment_url( $image_post[0]->post_id ); 
		}
		
		function get_src($content){
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML($content);
			$query = "//img"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query); 
			foreach ($result as $node) {
				$img_url = $node->getAttribute("data-api-endpoint");
				$src = $node->getAttribute("src");
				$img_attrs = explode("/", $img_url);
				$new_img_url = $this->get_image($img_attrs);
				if($new_img_url!=""){
					$content = str_replace($src,$new_img_url,$content);
				}
			}
			return $content;
		}
	
		function change_pictures($the_content){
			global $post;
			if(get_post_meta($post->ID, "CanvasLinked", true)==true){
				$the_content = $this->get_src($the_content);
			}
			return $the_content;
		}
	
	}
	
	$MOLIEpostImageDisplay = new MOLIEpostImageDisplay();
	