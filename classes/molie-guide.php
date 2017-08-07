<?PHP

	class MOLIEguide{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
		}
	
		function menu_create(){
			add_submenu_page( "molie_mgmt", __("Guidance"), __("Guidance"), 'edit_linkedcanvascourse', "molie_guide", array($this,"guide"));
		}
		
		function guide(){
			?><h2>Guidance on this tool</h2><?PHP
		}
	
	}
	
	$MOLIEguide = new MOLIEguide();
	