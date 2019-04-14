<?php

$AccessAllowed = false;

if( isSchool ) {

	switch($filename) {
		case 'index.php':
		case '_get.php':

		case 'classes.php':
		case 'teachers.php':

		case 'slides.php':
		case 'students.php':
		case 'gallery.php':
		case 'videos.php':
		case 'documents.php':
		case 'news.php':
		case 'banners.php':
		case 'competitions.php':
		case 'questions.php':
		case 'questions_replies.php':
		case 'agenda.php':
		case 'polls.php':
		case 'polls_options.php':
		case 'gallery_category.php':
			
		case 'about-us.php':
			$AccessAllowed = true;
			break;
	}
}
else if( isTeacher ) {

	switch($filename) {
		case 'index.php':
		case 'agenda.php':
		case 'about-teacher.php':
			$AccessAllowed = true;
			break;
//		default:
//			die($filename);
	}
}
else if( isAdmin ) {

	switch($filename) {
		case 'agenda.php':
			$AccessAllowed = false;
			break;
		default:
			$AccessAllowed = true;
			break;
	}
}

if( $AccessAllowed ) {
	
}
else {
	ob_clean();
	if(defined('isAjaxRequest')) {
		die();
	}
	?>
		<h4 class="alert_error">Authentication needed to access this section!</h4>
		<a href="./">Dashboard</a>
		
		<!-- <?php echo $filename; ?> -->
	<?php 
	
//	include '_bottom.php';
	exit;
}



