<?PHP

	class MOLIEtoken{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
		}
		
		function menu_create(){
			add_submenu_page( "molie_mgmt", __("Getting your Token"), __("Getting your Token"), 'edit_linkedcanvascourse', "molie_token", array($this,"token"));
		}
		
		function token(){
			?>
				<h1><?PHP echo __("Link a Course to WordPress"); ?></h1>
				<p>
					<?PHP echo __("Log into Canvas and visit"); ?> <a href="https://canvas.instructure.com/profile/settings"> <?PHP echo __("the settings page"); ?> </a>
				</p>
				<p>
					<?PHP echo __("Scroll down till you see"); ?><br />
					<img src="<?PHP echo plugins_url(); ?>/molie/img/NewAccessToken.png" />
				</p>
				<p>
					<?PHP echo __("Click on 'New Access Token'"); ?>	
				</p>
				<p>
					<?PHP echo __("A pop up window will appear"); ?><br />
					<img src="<?PHP echo plugins_url(); ?>/molie/img/NewAccessTokenPopUp.png" /><br />
					<?PHP echo __("Enter a reason, it can be a simple sentence explaining what you are doing. You can leave date blank"); ?><br />
					<?PHP echo __("Once you've entered a reason, click on 'Generate Token'"); ?><br />
				</p>
				<p>
					<?PHP echo __("The screen will update to show the following"); ?><br />
					<img src="<?PHP echo plugins_url(); ?>/molie/img/NewAccessTokenDisplay.png" /><br />
					<?PHP echo __("Note down the token at the top, as you won't be able to get it again. The token is in the area highlighted with a red border."); ?><br />
				</p>
			<?PHP
		}

	}
	
	$MOLIEtoken = new MOLIEtoken();
	