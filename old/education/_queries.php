<?php 


function getAccessSql($account, $table, $id = 0, $where = '') {
	global $_DefaultAddedByTitle;
	$id = intval($id);
	
	$allSchools = "";
	switch($table) {
		case 'news':
		case 'news_details':
			$index = 'news_index';

			$targetedStudents = " AND news.id IN(SELECT news_id FROM news_students WHERE student_id IN( {$account['studentIDs']} ) OR student_id ='-1' ) ";
			$_where = ($table=='news_details') ? " AND news.id = '{$id}' " : '';
			
			$_where .= " AND ( news.publish_date_time <= NOW() )   "; // date(news.publish_date_time) = '0000-00-00' OR
			
			$table = 'news';
			break;
		case 'education_news':
		case 'education_news_details':
			$index = 'education_news_index';

			$targetedStudents = " AND education_news.id IN(SELECT news_id FROM education_news_students WHERE student_id IN( {$account['studentIDs']} ) OR student_id ='-1' ) ";
			$_where = ($table=='education_news_details') ? " AND education_news.id = '{$id}' " : '';
			
			$_where .= " AND ( education_news.publish_date_time <= NOW() )   "; // date(education_news.publish_date_time) = '0000-00-00' OR

			$allSchools = " education_news.school_id='-1' OR ";
			
			$table = 'education_news';
			break;
			
		case 'documents':
		case 'documents_details':
			$index = 'documents_index';

			$targetedStudents = " AND documents.id IN(SELECT document_id FROM documents_students WHERE student_id IN( {$account['studentIDs']} ) OR student_id ='-1' ) ";
			$_where = ($table=='documents_details') ? " AND documents.id = '{$id}' " : '';

			$table = 'documents';
			break;
			
		case 'gallery':
		case 'gallery_details':
			$index = 'gallery_index';

			$targetedStudents = "";
			$_where = ($table=='gallery_details') ? " AND gallery.id = '{$id}' " : '';

			$table = 'gallery';
			break;
			
		case 'videos':
		case 'videos_details':
			$index = 'videos_index';

			$targetedStudents = "";
			$_where = ($table=='videos_details') ? " AND videos.id = '{$id}' " : '';

			$table = 'videos';
			break;
			
		default:
			return ;
	}
	
	if( $where ) {
		$_where .= " AND $where";
	}
	
	$isPublic = '';
	
//	$selectClasses = " SELECT id FROM classes WHERE classes.school_id IN( {$account['schoolIDs']} ) AND classes.id IN ( {$account['classIDs']} ) ";
	$selectClasses = " SELECT id FROM classes WHERE classes.id IN ( {$account['classIDs']} ) ";
	
//	, ifNull(schools.title, '".mysql_real_escape_string( $_DefaultAddedByTitle )."') as add_by
//		LEFT JOIN `schools` ON (`schools`.id = `$table`.add_by_id) )
// , ifNull(schools.title, '') as add_by
	$sql = "SELECT `$table`.* 
		FROM (`$table`, `$index`) 
		LEFT JOIN `schools` ON (`schools`.id = `$table`.school_id)
		WHERE `$table`.status='active'
			{$_where}
			
			AND ( {$allSchools} ( 
				`$table`.school_id IN( {$account['schoolIDs']} )
				AND `$index`.index_id=`$table`.id
				AND (
					`$index`.class_id = '-1'
					OR ( `$index`.class_id IN( {$account['classIDs']} ) {$targetedStudents} )
					)
				)
			)
		GROUP BY `$table`.id
		";
//  OR ( `$index`.class_id IN( {$selectClasses} ) {$targetedStudents} )
	return $sql;
}


function getUserLogin($from, $username, $password = '') {
	$sqlJoin = '';
	$sqlJoinSelect = '';
	if( $from == 'test' ) {
//		$WHERE = " true ";
		$WHERE = " false ";
	} 
	else if( $from == 'login' ) {
		$WHERE = " users.username = '".mysql_real_escape_string( $username )."' AND users.password = '".md5( $password )."'  ";
	} 
	else {
//		$WHERE = " md5( concat(users.`id`, users.`username`, users.`password`, users_logins.`api_hash`) ) = '".mysql_real_escape_string( $username )."' ";
//		$WHERE .= " AND users_logins.api_hash <> ''  ";
		$WHERE = " users_logins.`api_hash` = '".mysql_real_escape_string( $username )."' ";
		$WHERE .= " AND users_logins.user_id=users.id ";
		$sqlJoin = " , users_logins ";
		$sqlJoinSelect = ' , users_logins.id as user_login_id , users_logins.time_update as user_login_time_update, users_logins.notifications ';
	}

	$sql = "SELECT users.* {$sqlJoinSelect}
		FROM ( users $sqlJoin )
		WHERE {$WHERE}
		GROUP BY users.id
		LIMIT 1";
	
	return $sql;
}


//function getStudentLogin($from, $username, $password = '') {
//	$sqlJoin = '';
//	$sqlJoinSelect = '';
//	if( $from == 'test' ) {
////		$WHERE = " true ";
//		$WHERE = " false ";
//	} 
//	else if( $from == 'login' ) {
//		$WHERE = " students.email = '".mysql_real_escape_string( $username )."' AND students.password = '".md5( $password )."'  ";
//	} 
//	else {
////		$WHERE = " md5( concat(students.`id`, students.`email`, students.`password`, students_logins.`api_hash`) ) = '".mysql_real_escape_string( $username )."' ";
////		$WHERE .= " AND students_logins.api_hash <> ''  ";
//		$WHERE = " students_logins.`api_hash` = '".mysql_real_escape_string( $username )."' ";
//		$WHERE .= " AND students_logins.student_id=students.id ";
//		$sqlJoin = " , students_logins ";
//		$sqlJoinSelect = ' , students_logins.id as student_login_id , students_logins.time_update as student_login_time_update ';
//	}
//
//	$sql = "SELECT students.*, students.school_id as student_school_id {$sqlJoinSelect}
//		FROM ( students $sqlJoin )
//		WHERE {$WHERE}
//		GROUP BY students.id
//		LIMIT 1";
//	
//	return $sql;
//}
function getBannersByZone( $zone, $private = false, $school_id=0 ) {
	
	global $account;

	$_where = '';
	$school_id = intval( $school_id );
	
	$_where_school .= " ( `banners_schools`.school_id ='{$school_id}' OR `banners_schools`.school_id ='-1' ) ";

	if( $private ) {
		if( $school_id > 0 ) { // is loggedin
			$_where .= " AND {$_where_school}  ";
		}
		else {
			$_where .= " AND FALSE ";
		}
	}
	else {
		if( $school_id > 0 ) { // is loggedin
//			$_where .= " AND ( `banners_schools`.school_id IS NULL OR {$_where_school} ) ";
			$_where .= " AND {$_where_school}  ";
		}
		else {
			$_where .= " AND `banners_schools`.school_id IS NULL ";
		}
	}

	$sql = "SELECT * FROM (
		SELECT `banners`.*
		FROM  `banners` 
		LEFT JOIN `banners_schools` ON (`banners_schools`.banner_id = `banners`.id)
		WHERE `banners`.status='active'
			AND `banners`.plan_zone = '".mysql_real_escape_string( $zone )."'
			AND (
				( `banners`.plan_end_date='0000-00-00' AND `banners`.plan_impressions<1 AND `banners`.plan_clicks<1 )
				OR
				( `banners`.plan_clicks > 0 AND `banners`.plan_clicks > `banners`.clicks)
				OR
				( `banners`.plan_impressions > 0 AND `banners`.plan_impressions > `banners`.impressions)
				OR
				( `banners`.plan_end_date <> '0000-00-00' AND `banners`.plan_end_date > CURDATE())
			)
			{$_where}
		GROUP BY `banners`.id
		) as bannersTable
		";
	
	return $sql;
}
function getQuestionsSql( $account, $id = false, $where = "") {
	
	$_where = "";
	if( $where ) {
		$_where .= " AND {$where} ";
	}
	if( $id !== false ) {
		$_where .= " AND `questions`.id ='".intval($id)."' ";
	}

	$sql = "SELECT * FROM (
		SELECT count(`replies`.id) as replies, ifnull(`replies`.time, questions.time) as last_reply_time, `replies`.`from` as last_reply_from, questions.*  
		FROM questions 
		LEFT JOIN ( SELECT id, `from`, question_id, time FROM `questions_replies` ORDER BY id DESC ) as `replies` 
			ON(replies.question_id = `questions`.id )
		WHERE questions.user_id = '".mysql_real_escape_string( $account['id'] )."'
			AND questions.school_id IN( {$account['schoolIDs']} )
			AND status = 'active'
			{$_where}
	 	GROUP BY `questions`.id
	) as questions ";

	return $sql;
}

function getPollsSql( $account, $school_id, $id = false) {
	
	$school_id = intval( $school_id );
	
	global $account;

	$selectClasses = " SELECT id FROM classes WHERE classes.school_id = '{$school_id}' AND classes.id IN ( {$account['classIDs']} ) ";

	$sql = "SELECT polls.* 
			, polls_index.option_id
			FROM (`polls` LEFT JOIN `polls_classes_index` ON( `polls_classes_index`.index_id=`polls`.id ) ) 
			LEFT JOIN polls_index ON(polls.id = polls_index.poll_id AND polls_index.user_id = '".mysql_real_escape_string( $account['id'] )."' )
			WHERE polls.status = 'active'
				AND polls.date_from <= CURDATE()
				AND polls.date_to >= CURDATE()
			";
	
//	$sql .= " AND ( polls.school_id = '0' OR polls.school_id = '{$school_id}' ) ";
	$sql .= " AND polls.school_id = '{$school_id}' ";
	$sql .= " AND ( `polls_classes_index`.class_id IN( {$selectClasses} ) OR `polls_classes_index`.class_id = '-1' ) ";

	if($id !== false) {
		$id = intval( $id );
		$sql .= " AND `polls`.id ='{$id}' ";
	}
	
	$sql .= "
	 		GROUP BY `polls`.id
	";

	return $sql;
}

function getCompetitionsSql( $account, $id = false) {

	$sql = "SELECT * FROM competitions 
			LEFT JOIN competitions_index ON(competitions.id = competitions_index.competition_id AND competitions_index.user_id = '".mysql_real_escape_string( $account['id'] )."' )
			WHERE competitions.status = 'active'
				AND competitions.school_id IN( {$account['schoolIDs']} )
			";
	if($id !== false) {
		$id = intval( $id );
		$sql .= " AND `competitions`.id ='".mysql_real_escape_string($id)."' ";
	}
	
	$sql .= "
	 		GROUP BY `competitions`.id
	";

	return $sql;
}
function getCurrentCompetitionSql( $account, $school_id, $competition_id = 0 ) {
				
	global $account;
	
	$school_id = intval( $school_id );
	$competition_id = intval( $competition_id ); 

	$selectClasses = " SELECT id FROM classes WHERE classes.school_id = '{$school_id}' AND classes.id IN ( {$account['classIDs']} ) ";
	
	$y = intval(date('Y'));
	$m = intval(date('m'));

	$_where = "";

	if( $competition_id > 0 ) {
		$_where .= " AND competitions.id = '{$competition_id}'";
	}
	
	$sql = "SELECT competitions.*, competitions_index.option_id 
			FROM (`competitions` LEFT JOIN `competitions_classes_index` ON( `competitions_classes_index`.index_id=`competitions`.id ) ) 
			LEFT JOIN competitions_index ON(competitions.id = competitions_index.competition_id AND competitions_index.user_id = '".mysql_real_escape_string( $account['id'] )."' )
			WHERE competitions.status = 'active'
				AND competitions.year = '$y'
				AND competitions.month = '$m'
				{$_where}
			";

//	$sql .= " AND ( competitions.school_id = '0' OR competitions.school_id = '{$school_id}' ) ";
	$sql .= " AND competitions.school_id = '{$school_id}' ";
	$sql .= " AND ( `competitions_classes_index`.class_id IN( {$selectClasses} ) OR `competitions_classes_index`.class_id = '-1' ) ";
	
	return $sql;
}

function getCompetitionWallSql( $account, $competition ) {
				
	$sql = "SELECT users.* 
		FROM users, competitions_index
		WHERE competitions_index.user_id = users.id
			AND competitions_index.competition_id = '{$competition['id']}' 
			AND competitions_index.option_id = '{$competition['answer_id']}' 
		";

	return $sql;
}


function getStudentsAndSchools( $account ) {
	
	$username = $account['username'];
	
	$account['students'] = array();
	$account['classes'] = array();
	$account['schools'] = array();
	$account['school'] = array();

	$sql = "SELECT * 
		FROM students 
		WHERE status='active'
			AND (
				students.info_mobile = '". mysql_real_escape_string( $username )."'
				OR students.info_father_mobile = '". mysql_real_escape_string( $username )."'
				OR students.info_mother_mobile = '". mysql_real_escape_string( $username )."'
			)
		";
	$students_class_id = array();
	$students_school_id = array();
	$q = mysql_query( $sql );
	if( $q && mysql_num_rows( $q )) {
		while( $row = mysql_fetch_assoc( $q )) {
			$account['students'][ $row['id'] ] = array(
				'id' => $row['id'],
				'full_name' => $row['full_name'],
				'first_name' => $row['first_name'],
				'last_name' => $row['last_name'],
				'class_id' => $row['class_id'],
				'school_id' => $row['school_id'],
				'image' => $row['image'],
			
				'info_mobile' => $row['info_mobile'],
				'info_address' => $row['info_address'],
				'info_email' => $row['info_email'], 
			);
//	var_dump( $row );
			$students_class_id[] = $row['class_id'];
			$students_school_id[] = $row['school_id'];
		}
	}

	if( $students_school_id ) {
		$sql = "SELECT * 
			FROM schools 
			WHERE status='active'
			AND id IN(". implode(',', $students_school_id) .") ";
		$q = mysql_query( $sql );
		if( $q && mysql_num_rows( $q )) {
			while( $row = mysql_fetch_assoc( $q )) {
				$account['schools'][ $row['id'] ] = array(
					'id' => $row['id'],
					'title' => $row['title'],
					'description' => $row['description'],
					'contact_first_name' => $row['contact_first_name'],
					'contact_email' => $row['contact_email'],
					'contact_send_email' => $row['contact_send_email'],
					'image' => $row['image'],
				);
				
				if( !$account['school'] ) {
					$account['school'] = $row;
				}
				
			}
		}
	}
	if( $students_class_id && $account['schools']) {
		$sql = "SELECT * 
			FROM classes 
			WHERE id IN(". implode(',', $students_class_id) .") ";
		$q = mysql_query( $sql );
		if( $q && mysql_num_rows( $q )) {
			while( $row = mysql_fetch_assoc( $q )) {
				$account['classes'][ $row['id'] ] = array(
					'id' => $row['id'],
					'title' => $row['title'],
				);
			}
		}
	}
//var_dump( $account['schools'] );
	if( $account['classes'] ) {
		$ids = array_keys( $account['classes'] );
		
		$account['classIDs'] = implode(',', $ids);
	}
	if( $account['schools'] ) {
		$ids = array_keys( $account['schools'] );
		
		$account['schoolIDs'] = implode(',', $ids);
	}
	if( $account['students'] ) {
		$ids = array_keys( $account['students'] );
		
		$account['studentIDs'] = implode(',', $ids);
	}
	
	return $account;
}