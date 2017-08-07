<?PHP

	class MOLIEpostLinkDisplayAddCSS{
	
		function __construct(){
			add_action("wp_enqueue_scripts", array($this, "scripts_and_styles"));
		}
		
		function scripts_and_styles(){
			global $post;
			if(get_post_meta($post->ID, "CanvasLinked", true)==true){
				$url = get_post_meta($post->ID,"postHTMLURL",true);
				$this->domDocument = new DOMDocument();
				@$this->domDocument->loadHTML(file_get_contents($url));
				$query = "//link"; 
				$xpath = new DOMXPath($this->domDocument); 
				$result = $xpath->query($query); 
				foreach ($result as $node) {
					if($node->getAttribute("rel")=="stylesheet"){
						wp_register_style( $node->getAttribute("href"), $node->getAttribute("href"), false, null );
						wp_enqueue_style( $node->getAttribute("href") );
					}
				}
			}
		}
	
	}
	
	$MOLIEpostLinkDisplayAddCSS = new MOLIEpostLinkDisplayAddCSS();
	