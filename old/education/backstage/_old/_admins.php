<?php

if( isReseller ) {

	switch($filename)
	{
		case 'index.php':
		case 'students.php':
		case 'students_import.php':
		case 'documents.php':
		case 'news.php':
		case 'banners.php':
		case 'competitions.php':
		case 'questions.php':
		case 'questions_replies.php':
		case '_get.php':

			break;
		default:
			ob_clean();
			if(defined('isAjaxRequest')) {
				die();
			}
		?><h4 class="alert_error">Authentication needed to access this section!<!-- <?php echo $filename; ?> --></h4><?php 
	
//		include '_bottom.php';
		exit;
	}
}