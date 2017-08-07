<?PHP

	class MOLIEmenu{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_hide"));
		}
	
		function menu_hide(){
			
			global $submenu, $menu;
			
			if(isset($submenu["molie_mgmt"])){
				foreach($submenu["molie_mgmt"] as $index => $page){
					if($page[2]=="molie_mediamgmt"){
						unset($submenu["molie_mgmt"][$index]);
					}
				}
			}
			
			foreach($menu as $index => $menu_item){
				if($menu_item[0]=="Linked Canvas Assignments")
				{
					unset($menu[$index]);
				}
				if($menu_item[0]=="Linked Canvas Courses")
				{
					unset($menu[$index]);
				}
				if($menu_item[0]=="Linked Canvas Quiz")
				{
					unset($menu[$index]);
				}
				if($menu_item[0]=="Linked Canvas Quiz Answers")
				{
					unset($menu[$index]);
				}
				if($menu_item[0]=="Linked Canvas Discussions")
				{
					unset($menu[$index]);
				}
				if($menu_item[0]=="Linked Canvas Users")
				{
					unset($menu[$index]);
				}
				
			}
			
		}
	
	}
	
	$MOLIEmenu = new MOLIEmenu();
	