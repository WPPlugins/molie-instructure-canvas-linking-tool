<?PHP

	class MOLIEhelp{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
		}
		
		function menu_create(){
			add_submenu_page( "molie_mgmt", __("Using the tool"), __("Using the tool"), 'edit_linkedcanvascourse', "molie_help", array($this,"help"));
		}
		
		function help(){
			?>
				<h1><?PHP echo __("Using this tool"); ?></h1>
				<p>
					<?PHP echo __("Log into Canvas and visit"); ?> <a href="https://canvas.instructure.com/profile/settings"> <?PHP echo __("the settings page"); ?> </a>
				</p>
			<?PHP
		}

	}
	
	$MOLIEhelp = new MOLIEhelp();
	