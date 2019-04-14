<?php 


function getAccessSql($account, $table, $id = 0, $where = '') {
	global $_MainCompanyName;
	$id = intval($id);
	
	switch($table) {
		case 'news':
		case 'news_details':
			$index = 'news_index';
			$index_reseller = 'news_resellers';
			$index_reseller_id = 'news_id';
			$StudentsNews = " OR news.id IN(SELECT news_id FROM news_students WHERE student_id='{$account['id']}') ";
			$StudentsDocuments = "";
			$StudentsGallery = "";
			$StudentsVideos = "";

			$_where = ($table=='news_details') ? " AND news.id = '{$id}' " : '';
			$table = 'news';
			break;
			
		case 'documents':
		case 'documents_details':
			$index = 'documents_index';
			$index_reseller = 'documents_resellers';
			$index_reseller_id = 'document_id';
			$StudentsNews = "";
			$StudentsDocuments = " OR documents.is_public='1' ";
			$StudentsGallery = "";
			$StudentsVideos = "";

			$_where = ($table=='documents_details') ? " AND documents.id = '{$id}' " : '';
			$table = 'documents';
			break;
			
		case 'gallery':
		case 'gallery_details':
			$index = 'gallery_index';
			$index_reseller = 'gallery_resellers';
			$index_reseller_id = 'gallery_id';
			$StudentsNews = "";
			$StudentsDocuments = "";
			$StudentsGallery = " OR gallery.is_public='1' ";
			$StudentsVideos = "";

			$_where = ($table=='gallery_details') ? " AND gallery.id = '{$id}' " : '';
			$table = 'gallery';
			break;
			
		case 'videos':
		case 'videos_details':
			$index = 'videos_index';
			$index_reseller = 'videos_resellers';
			$index_reseller_id = 'video_id';
			$StudentsNews = "";
			$StudentsDocuments = "";
			$StudentsGallery = "";
			$StudentsVideos = " OR videos.is_public='1' ";

			$_where = ($table=='videos_details') ? " AND videos.id = '{$id}' " : '';
			$table = 'videos';
			break;
			
		default:
			return ;
	}
	
	if( $where ) {
		$_where .= " AND $where";
	}
	
//				(`$index_reseller`.reseller_id IN({$account['students_resellers']}) {$StudentsDocuments} )
//								SELECT cat_id, sub_id FROM resellers_index WHERE index_id IN({$account['students_resellers']})
	$sql = "SELECT `$table`.* , ifNull(resellers.title, '".mysql_real_escape_string( $_MainCompanyName )."') as add_by
		FROM ( (`$table`, `$index`) 
		LEFT JOIN `$index_reseller` ON (`$index_reseller`.{$index_reseller_id} = `$table`.id)
		LEFT JOIN `resellers` ON (`resellers`.id = `$table`.add_by_id) )
		WHERE `$table`.status='active'
			AND `$index`.index_id=`$table`.id
			{$_where}
			AND (
				(`$index_reseller`.reseller_id ='{$account['student_reseller_id']}' {$StudentsDocuments} {$StudentsGallery} {$StudentsVideos} )
				AND (
					CONCAT(`$index`.cat_id, ':', `$index`.sub_id) IN(
						SELECT CONCAT(cat_id, ':', sub_id) 
						FROM (
								SELECT cat_id, sub_id FROM students_index WHERE index_id='{$account['id']}'
							UNION ALL 
								SELECT cat_id, sub_id FROM resellers_index WHERE index_id ='{$account['student_reseller_id']}'
							) foo 
						GROUP BY cat_id, sub_id 
						HAVING COUNT(*) > 1
					)
				) 
			) {$StudentsNews}
		GROUP BY `$table`.id
		";
	// UNION ALL : so we can user " HAVING() " in WHERE section
	return $sql;
}


//				SELECT cat_id, sub_id FROM resellers_index WHERE index_id IN({$account['students_resellers']})
function getAccountCategories($account) {
	$sql = "SELECT category.id as cat_id, category.title as cat_title, category_sub.id as sub_id, category_sub.title as sub_title 
		FROM   (
				SELECT cat_id, sub_id FROM students_index WHERE index_id='{$account['id']}'
			UNION ALL 
				SELECT cat_id, sub_id FROM resellers_index WHERE index_id ='{$account['student_reseller_id']}'
			) foo 
			LEFT JOIN `category` ON (`category`.id = `foo`.cat_id) 
			LEFT JOIN `category_sub` ON (`category_sub`.id = `foo`.sub_id)
		GROUP BY category.id, category_sub.id
		HAVING COUNT(*) > 1
		";
	// UNION ALL : so we can user " HAVING() " in WHERE section

	$sub = array();
	$data = array();
	$q = mysql_query( $sql );
	if( $q && mysql_num_rows( $q )) {
		while($row = mysql_fetch_assoc( $q )) {
			if( $row['cat_id'] ) {
				if( !$data[ $row['cat_id'] ]['id'] ) {
					$data[ $row['cat_id'] ] = array(
						'id' => $row['cat_id'],
						'title' => $row['cat_title'],
						'sub' => array(),
					);
				}
				if( $row['sub_id'] && !$s[$row['sub_id']] ) {
					$sub[$row['sub_id']] = true;
					$data[ $row['cat_id'] ]['sub'][] = array(
						'id' => $row['sub_id'],
						'title' => $row['sub_title'],
					);
				}
			}
		}
	}

	$data = array_values($data);
	
	return $data;
}

function getStudentLogin($from, $username, $password = '') {
	$sqlJoin = '';
	$sqlJoinSelect = '';
	if( $from == 'test' ) {
//		$WHERE = " true ";
		$WHERE = " false ";
	} 
	else if( $from == 'login' ) {
		$WHERE = " students.email = '".mysql_real_escape_string( $username )."' AND students.password = '".md5( $password )."'  ";
	} 
	else {
//		$WHERE = " md5( concat(students.`id`, students.`email`, students.`password`, students_logins.`api_hash`) ) = '".mysql_real_escape_string( $username )."' ";
//		$WHERE .= " AND students_logins.api_hash <> ''  ";
		$WHERE = " students_logins.`api_hash` = '".mysql_real_escape_string( $username )."' ";
		$WHERE .= " AND students_logins.student_id=students.id ";
		$sqlJoin = " , students_logins ";
		$sqlJoinSelect = ' , students_logins.id as student_login_id , students_logins.time_update as student_login_time_update ';
	}

//	SELECT students.* , GROUP_CONCAT( DISTINCT students_resellers.reseller_id SEPARATOR ',' ) as students_resellers
	$sql = "SELECT students.*, students_resellers.reseller_id as student_reseller_id {$sqlJoinSelect}
		FROM ( students $sqlJoin )
			LEFT OUTER JOIN students_resellers ON(students_resellers.student_id=students.id) 
		WHERE {$WHERE}
		GROUP BY students.id
		LIMIT 1";
	
	return $sql;
}
function getBannersByZone( $zone ) {
	$sql = "SELECT * FROM (
		SELECT *, if(reseller_id>0, 1, 0) as for_reseller 
		FROM banners
		WHERE plan_zone = '".mysql_real_escape_string( $zone )."'
			AND (
				( plan_end_date='0000-00-00' AND plan_impressions<1 AND plan_clicks<1 )
				OR
				( plan_clicks > 0 AND plan_clicks > clicks)
				OR
				( plan_impressions > 0 AND plan_impressions > impressions)
				OR
				( plan_end_date <> '0000-00-00' AND plan_end_date > CURDATE())
			)
			AND status = 'active'
	) as banners ";
	return $sql;
}
function getQuestionsSql( $account, $id = false) {

	$sql = "SELECT * FROM (
		SELECT count(`replies`.id) as replies, ifnull(`replies`.time, questions.time) as last_reply_time, `replies`.`from` as last_reply_from, questions.*  
		FROM questions 
		LEFT JOIN ( SELECT id, `from`, question_id, time FROM `questions_replies` ORDER BY id DESC ) `replies` 
			ON(replies.question_id = `questions`.id )
		WHERE questions.student_id = '".mysql_real_escape_string( $account['id'] )."'
			AND questions.reseller_id = '".mysql_real_escape_string( $account['student_reseller_id'] )."'
			AND status = 'active'
			".( ($id !== false) ? " AND `questions`.id ='".mysql_real_escape_string($id)."' " : '' )."
	 	GROUP BY `questions`.id
	) as questions ";

	return $sql;
}
function getCompetitionsSql( $account, $id = false) {

	$sql = "SELECT * FROM competitions 
			LEFT JOIN competitions_index ON(competitions.id = competitions_index.competition_id AND competitions_index.student_id = '".mysql_real_escape_string( $account['id'] )."' )
			WHERE competitions.status = 'active'
				AND competitions.reseller_id = '".mysql_real_escape_string( $account['student_reseller_id'] )."'
			";
	if($id !== false) {
		$sql .= " AND `competitions`.id ='".mysql_real_escape_string($id)."' ";
	}
	
	$sql .= "
	 		GROUP BY `competitions`.id
	";

	return $sql;
}
function getCurrentCompetitionSql( $account, $thisMonth = false ) {
				
	$y = intval(date('Y'));
	$m = intval(date('m'));

	$sql = "SELECT competitions.*, competitions_index.option_id FROM competitions 
			LEFT JOIN competitions_index ON(competitions.id = competitions_index.competition_id AND competitions_index.student_id = '".mysql_real_escape_string( $account['id'] )."' )
			WHERE competitions.status = 'active'
				AND competitions.reseller_id = '".mysql_real_escape_string( $account['student_reseller_id'] )."'
				AND competitions.year = '$y'
				AND competitions.month = '$m'
			";
	
	return $sql;
}
function getCompetitionWallSql( $account, $competition ) {
				
	$sql = "SELECT students.* 
		FROM students, competitions_index
		WHERE competitions_index.student_id = students.id
			AND competitions_index.competition_id = '{$competition['id']}' 
			AND competitions_index.option_id = '{$competition['answer_id']}' 
			AND students.id IN (SELECT student_id FROM students_resellers WHERE reseller_id = '".mysql_real_escape_string( $account['student_reseller_id'] )."' )
		";
	
	return $sql;
}

