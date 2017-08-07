<?PHP

	/*
		Plugin Name: M.O.L.I.E - Canvas to WordPress Open Course Tool
		Description: Linking a course between Instructure Canvas and WordPress to help create open courses
		Author: pgogy
		Version: 0.3
	*/
	
	class MOLIE{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
		}
	
		
		function menu_create(){
			add_menu_page( __("M.O.L.I.E"), __("M.O.L.I.E"), "edit_linkedcanvascourse", "molie_mgmt", array($this,"mgmt"));
		}
		
		function mgmt(){
			?>
				<h1>M.O.L.I.E</h1>
				<p>
					<?PHP echo __("Start by click on getting your token in the menu"); ?>
				</p>
			<?PHP
		}	
		
	}
	
	$MOLIE = new MOLIE();
	
	require_once("classes/molie-help.php");
	require_once("classes/molie-token.php");
	require_once("classes/molie-guide.php");	
	require_once("classes/ingest/molie-link.php");
	require_once("classes/molie-course-status.php");
	require_once("classes/tinyMCE/molie-tinyMCE.php");
	require_once("classes/ajax/molie-course-ajax.php");
	require_once("classes/ajax/molie-file-upload.php");
	require_once("classes/ajax/molie-post-update.php");
	require_once("classes/ajax/molie-course-place.php");
	require_once("classes/ajax/molie-post-link.php");
	require_once("classes/ajax/molie-post-unlink.php");
	require_once("classes/ajax/molie-file-ajax.php");
	require_once("classes/ajax/molie-roster-ajax.php");
	require_once("classes/ajax/molie-quiz-ajax.php");
	require_once("classes/ajax/molie-assignment-ajax.php");
	require_once("classes/ajax/molie-choose-ajax.php");
	require_once("classes/ajax/molie-discussion-ajax.php");
	require_once("classes/ajax/molie-course-check-ajax.php");
	require_once("classes/post_types/molie-course-post.php");
	require_once("classes/post_types/molie-assignment-post.php");
	require_once("classes/post_types/molie-user-post.php");
	require_once("classes/post_types/molie-quiz-post.php");
	require_once("classes/post_types/molie-discussion-post.php");
	require_once("classes/post_types/molie-quiz-answer-post.php");
	require_once("classes/post_types/molie-course-delete.php");
	require_once("classes/post_types/molie-item-delete.php");
	require_once("classes/post_types_editor/molie-course-post-editor.php");
	require_once("classes/post_types_editor/molie-quiz-post-editor.php");
	require_once("classes/post_types_editor/molie-quiz-post-display.php");
	require_once("classes/post_types_editor/molie-quiz-question-post-editor.php");
	require_once("classes/post_types_editor/molie-linked-post-editor.php");
	require_once("classes/post_types_editor/molie-linked-post-pre-editor.php");
	require_once("classes/post_types_editor/molie-hide-roster-post.php");
	require_once("classes/post_types_editor/molie-hide-calendar-post.php");
	require_once("classes/page_save/molie-post-save-linked-post.php");
	require_once("classes/media/molie-media-upload.php");
	//require_once("classes/media/molie-post-save-image-handler.php");
	require_once("classes/links/molie-post-save-links-handler.php");
	require_once("classes/display/molie-post-image-display.php");
	require_once("classes/display/molie-post-link-display.php");
	require_once("classes/display/molie-post-link-display-add-css.php");
	require_once("classes/display/molie-roster-display.php");
	require_once("classes/roles/molie-admin-roles.php");
	require_once("classes/mgmt/molie-course-poll.php");
	require_once("classes/mgmt/molie-post-filter.php");
	require_once("classes/mgmt/molie-quiz-filter.php");
	require_once("classes/mgmt/molie-HTML-filter.php");
	require_once("classes/mgmt/molie-media-mgmt.php");
	require_once("classes/molie-your-courses.php");	
	require_once("classes/ingest/molie-choose.php");
	require_once("classes/ingest/molie-files.php");
	require_once("classes/ingest/molie-quiz.php");
	require_once("classes/ingest/molie-discussion.php");
	require_once("classes/ingest/molie-assignments.php");
	require_once("classes/ingest/molie-roster.php");
	require_once("classes/ingest/molie-calendar.php");
	require_once("classes/molie-menu.php");	
	