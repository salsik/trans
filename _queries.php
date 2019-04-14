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
			$DoctorsNews = " OR news.id IN(SELECT news_id FROM news_doctors WHERE doctor_id='{$account['id']}') ";
			
			$_where = ($table=='news_details') ? " AND news.id = '{$id}' " : '';
			
			$_where .= " AND ( news.publish_date_time <= NOW() )   "; // date(news.publish_date_time) = '0000-00-00' OR
			
			$table = 'news';
			break;
			
		case 'medical':
		case 'medical_details':
			$index = 'medical_index';
			$index_reseller = 'medical_resellers';
			$index_reseller_id = 'medical_id';
			$DoctorsNews = " OR medical.id IN(SELECT medical_id FROM medical_doctors WHERE doctor_id='{$account['id']}') ";
			
			$_where = ($table=='medical_details') ? " AND medical.id = '{$id}' " : '';
			
			$_where .= " AND ( medical.publish_date_time <= NOW() )   "; // date(medical.publish_date_time) = '0000-00-00' OR
			
			$table = 'medical';
			break;
			
		case 'documents':
		case 'documents_details':
			$index = 'documents_index';
			$index_reseller = 'documents_resellers';
			$index_reseller_id = 'document_id';
			$DoctorsDocuments = " OR documents.is_public='1' ";

			$_where = ($table=='documents_details') ? " AND documents.id = '{$id}' " : '';
			$table = 'documents';
			break;
        case 'expert_articles':
        case 'expert_articles_details':
        $index = 'news_index';
        $index_reseller = 'news_resellers';
        $index_reseller_id = 'news_id';
        $DoctorsNews = " OR expert_articles.id IN(SELECT news_id FROM news_doctors WHERE doctor_id='{$account['id']}') ";

        $_where = ($table=='news_details') ? " AND expert_articles.id = '{$id}' " : '';

        $_where .= " AND ( expert_articles.publish_date_time <= NOW() )   "; // date(news.publish_date_time) = '0000-00-00' OR

        $table = 'expert_articles';

            break;
			
			
			
			case 'products':
			case 'product_details':
			    $index = 'news_index';
				
				
				$sql = 'select * from products where add_by_id= 0 or add_by_id=' .$account['doctor_reseller_id'] ;
				
				return $sql ;

			break;
			
			
		default:
			return ;
	}
	
	if( $where ) {
		$_where .= " AND $where";
	}

	$sql = "SELECT `$table`.* , ifNull(resellers.title, '".mysql_real_escape_string( $_MainCompanyName )."') as add_by
		FROM ( (`$table`, `$index`) 
		LEFT JOIN `$index_reseller` ON (`$index_reseller`.{$index_reseller_id} = `$table`.id)
		LEFT JOIN `resellers` ON (`resellers`.id = `$table`.add_by_id) )
		WHERE `$table`.status='active'
			AND `$index`.index_id=`$table`.id
			{$_where}
			AND (
				(`$index_reseller`.reseller_id ='{$account['doctor_reseller_id']}' {$DoctorsDocuments} )
				AND (
					`$index`.cat_id IN(
						SELECT cat_id
						FROM (
								SELECT cat_id FROM doctors_index WHERE index_id='{$account['id']}'
							UNION ALL 
								SELECT cat_id FROM resellers_index WHERE index_id ='{$account['doctor_reseller_id']}'
							) foo 
						GROUP BY cat_id
						HAVING COUNT(*) > 1
					)
					OR `$index`.cat_id = '-1'
				) 
			) {$DoctorsNews}
		GROUP BY `$table`.id
		";
	// UNION ALL : so we can user " HAVING() " in WHERE section
	return $sql;
}


function getAccountCategories($account) {
	$sql = "SELECT category.id as cat_id, category.title as cat_title
		FROM   (
				SELECT cat_id FROM doctors_index WHERE index_id='{$account['id']}'
			UNION ALL 
				SELECT cat_id FROM resellers_index WHERE index_id ='{$account['doctor_reseller_id']}'
			) foo 
			LEFT JOIN `category` ON (`category`.id = `foo`.cat_id) 
		GROUP BY category.id
		HAVING COUNT(*) > 1
		";
	// UNION ALL : so we can use " HAVING() " in WHERE section

	$data = array();
	$q = mysql_query( $sql );
	if( $q && mysql_num_rows( $q )) {
		while($row = mysql_fetch_assoc( $q )) {
			if( $row['cat_id'] ) {
				if( !$data[ $row['cat_id'] ]['id'] ) {
					$data[ $row['cat_id'] ] = array(
						'id' => $row['cat_id'],
						'title' => $row['cat_title'],
					);
				}
			}
		}
	}

	$data = array_values($data);
	
	return $data;
}

function getDoctorLogin($from, $username, $password = '') {
	$sqlJoin = '';
	$sqlJoinSelect = '';
	if( $from == 'test' ) {
//		$WHERE = " true ";
		$WHERE = " false ";
	} 
	else if( $from == 'login' ) {
		$WHERE = " doctors.email = '".mysql_real_escape_string( $username )."' AND doctors.password = '".md5( $password )."'  ";
	} 
	else {
//		$WHERE = " md5( concat(doctors.`id`, doctors.`email`, doctors.`password`, doctors_logins.`api_hash`) ) = '".mysql_real_escape_string( $username )."' ";
//		$WHERE .= " AND doctors_logins.api_hash <> ''  ";
		$WHERE = " doctors_logins.`api_hash` = '".mysql_real_escape_string( $username )."' ";
		$WHERE .= " AND doctors_logins.doctor_id=doctors.id ";
		$sqlJoin = " , doctors_logins ";
		$sqlJoinSelect = ' , doctors_logins.id as doctor_login_id , doctors_logins.time_update as doctor_login_time_update , doctors_logins.notifications, doctors_logins.source as doctor_login_source ';
	}

//	SELECT doctors.* , GROUP_CONCAT( DISTINCT doctors_resellers.reseller_id SEPARATOR ',' ) as doctors_resellers
	$sql = "SELECT doctors.*
		, doctors_resellers.reseller_id as doctor_reseller_id
		{$sqlJoinSelect}
		FROM ( doctors $sqlJoin )
			LEFT OUTER JOIN doctors_resellers ON(doctors_resellers.doctor_id=doctors.id) 
		WHERE {$WHERE}
		GROUP BY doctors.id
		LIMIT 1";
	
	return $sql;
}
function getBannersByZone( $zone, $private = false, $reseller_id=0 ) {
	
	global $account;

	$_where = '';
	$reseller_id = intval( $reseller_id );
	
	$_where_reseller .= " ( 
		(`banners_resellers`.reseller_id ='{$account['doctor_reseller_id']}' 
		AND `banners_resellers`.reseller_id ='{$reseller_id}' )
			OR `banners_resellers`.reseller_id ='-1' 
		) ";
	$_where_categories .= " (
			`banners_index`.cat_id IN(
				SELECT cat_id
				FROM (
						SELECT cat_id FROM doctors_index WHERE index_id='{$account['id']}'
					UNION ALL 
						SELECT cat_id FROM resellers_index WHERE index_id ='{$account['doctor_reseller_id']}'
					) foo 
				GROUP BY cat_id
				HAVING COUNT(*) > 1
			)
			OR `banners_index`.cat_id = '-1'
		) ";

	if( $private ) {
		if( $reseller_id > 0 ) { // is loggedin
			$_where .= " AND {$_where_reseller}  ";
			$_where .= " AND {$_where_categories} ";
		}
		else {
			$_where .= " AND FALSE ";
		}
	}
	else {
		if( $reseller_id > 0 ) { // is loggedin
//			$_where .= " AND ( `banners_resellers`.reseller_id IS NULL OR {$_where_reseller} ) ";
			$_where .= " AND {$_where_reseller}  ";
			$_where .= " AND {$_where_categories} ";
		}
		else {
			$_where .= " AND `banners_resellers`.reseller_id IS NULL ";
//			$_where .= " AND `banners_index`.cat_id = '-1' ";
		}
	}

	$sql = "SELECT * FROM (
		SELECT `banners`.*
		FROM  (`banners` LEFT JOIN `banners_index` ON( `banners_index`.index_id=`banners`.id ) ) 
		LEFT JOIN `banners_resellers` ON (`banners_resellers`.banner_id = `banners`.id)
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

//	$sql = "SELECT * FROM (
//		SELECT *, if(reseller_id>0, 1, 0) as for_reseller 
//		FROM banners
//		WHERE plan_zone = '".mysql_real_escape_string( $zone )."'
//			AND (
//				( `banners`.plan_end_date='0000-00-00' AND `banners`.plan_impressions<1 AND `banners`.plan_clicks<1 )
//				OR
//				( `banners`.plan_clicks > 0 AND `banners`.plan_clicks > `banners`.clicks)
//				OR
//				( `banners`.plan_impressions > 0 AND `banners`.plan_impressions > `banners`.impressions)
//				OR
//				( `banners`.plan_end_date <> '0000-00-00' AND `banners`.plan_end_date > CURDATE())
//			)
//			AND status = 'active'
//	) as banners ";
	return $sql;
}
function getQuestionsSql( $account, $id = false) {

	$sql = "SELECT * FROM (
		SELECT count(`replies`.id) as replies, ifnull(`replies`.time, questions.time) as last_reply_time, `replies`.`from` as last_reply_from, questions.*  
		FROM questions 
		LEFT JOIN ( SELECT id, `from`, question_id, time FROM `questions_replies` ORDER BY id DESC ) `replies` 
			ON(replies.question_id = `questions`.id )
		WHERE questions.doctor_id = '".mysql_real_escape_string( $account['id'] )."'
			AND questions.reseller_id = '".mysql_real_escape_string( $account['doctor_reseller_id'] )."'
			AND status = 'active'
			".( ($id !== false) ? " AND `questions`.id ='".mysql_real_escape_string($id)."' " : '' )."
	 	GROUP BY `questions`.id
	) as questions ";

	return $sql;
}
function getPollsSql( $account, $id = false) {
	
	global $account;

	$sql = "SELECT polls.* 
			, polls_index.option_id
			FROM (`polls` LEFT JOIN `polls_cat_index` ON( `polls_cat_index`.index_id=`polls`.id ) ) 
			LEFT JOIN polls_index ON(polls.id = polls_index.poll_id AND polls_index.doctor_id = '".mysql_real_escape_string( $account['id'] )."' )
			WHERE polls.status = 'active'
				AND polls.reseller_id = '".mysql_real_escape_string( $account['doctor_reseller_id'] )."'
				AND polls.date_from <= CURDATE()
				AND polls.date_to >= CURDATE()
			";
	
//	$sql .= " AND ( `polls`.reseller_id = '0' OR polls.reseller_id = '".mysql_real_escape_string( $account['doctor_reseller_id'] )."' ) ";
	$sql .= " AND polls.reseller_id = '".mysql_real_escape_string( $account['doctor_reseller_id'] )."' ";
	$sql .= " AND (
			`polls_cat_index`.cat_id IN(
				SELECT cat_id
				FROM (
						SELECT cat_id FROM doctors_index WHERE index_id='{$account['id']}'
					UNION ALL 
						SELECT cat_id FROM resellers_index WHERE index_id ='{$account['doctor_reseller_id']}'
					) foo 
				GROUP BY cat_id
				HAVING COUNT(*) > 1
			)
			OR `polls_cat_index`.cat_id = '-1'
		) ";
	
	if($id !== false) {
		$sql .= " AND `polls`.id ='".mysql_real_escape_string($id)."' ";
	}
	
	$sql .= "
	 		GROUP BY `polls`.id
	";

	return $sql;
}
function getCompetitionsSql( $account, $id = false) {
	
	global $account;

	$sql = "SELECT competitions.*, competitions_index.*
		FROM (`competitions` LEFT JOIN `competitions_cat_index` ON( `competitions_cat_index`.index_id=`competitions`.id ) )  
			LEFT JOIN competitions_index ON(competitions.id = competitions_index.competition_id AND competitions_index.doctor_id = '".mysql_real_escape_string( $account['id'] )."' )
			WHERE competitions.status = 'active'
				AND competitions.reseller_id = '".mysql_real_escape_string( $account['doctor_reseller_id'] )."'
			";
	
//	$sql .= " AND ( `competitions`.reseller_id = '0' OR competitions.reseller_id = '".mysql_real_escape_string( $account['doctor_reseller_id'] )."' ) ";
	$sql .= " AND competitions.reseller_id = '".mysql_real_escape_string( $account['doctor_reseller_id'] )."' ";
	$sql .= " AND (
			`competitions_cat_index`.cat_id IN(
				SELECT cat_id
				FROM (
						SELECT cat_id FROM doctors_index WHERE index_id='{$account['id']}'
					UNION ALL 
						SELECT cat_id FROM resellers_index WHERE index_id ='{$account['doctor_reseller_id']}'
					) foo 
				GROUP BY cat_id
				HAVING COUNT(*) > 1
			)
			OR `competitions_cat_index`.cat_id = '-1'
		) ";
	
	if($id !== false) {
		$sql .= " AND `competitions`.id ='".mysql_real_escape_string($id)."' ";
	}
	
	$sql .= "
 		GROUP BY `competitions`.id
	";

	return $sql;
}

function getCurrentCompetitionSql( $account, $thisMonth = false ) {
	
	global $account;
				
	$y = intval(date('Y'));
	$m = intval(date('m'));

	$sql = "SELECT competitions.*, competitions_index.option_id 
			FROM (`competitions` LEFT JOIN `competitions_cat_index` ON( `competitions_cat_index`.index_id=`competitions`.id ) ) 
			LEFT JOIN competitions_index ON(competitions.id = competitions_index.competition_id AND competitions_index.doctor_id = '".mysql_real_escape_string( $account['id'] )."' )
			WHERE competitions.status = 'active'
				AND competitions.year = '$y'
				AND competitions.month = '$m'
			";
	
//	$sql .= " AND ( `competitions`.reseller_id = '0' OR competitions.reseller_id = '".mysql_real_escape_string( $account['doctor_reseller_id'] )."' ) ";
	$sql .= " AND competitions.reseller_id = '".mysql_real_escape_string( $account['doctor_reseller_id'] )."' ";
	$sql .= " AND (
			`competitions_cat_index`.cat_id IN(
				SELECT cat_id
				FROM (
						SELECT cat_id FROM doctors_index WHERE index_id='{$account['id']}'
					UNION ALL 
						SELECT cat_id FROM resellers_index WHERE index_id ='{$account['doctor_reseller_id']}'
					) foo 
				GROUP BY cat_id
				HAVING COUNT(*) > 1
			)
			OR `competitions_cat_index`.cat_id = '-1'
		) ";
	
	return $sql;
}
function getCompetitionWallSql( $account, $competition ) {
				
	$sql = "SELECT doctors.* 
		FROM doctors, competitions_index
		WHERE competitions_index.doctor_id = doctors.id
			AND competitions_index.competition_id = '{$competition['id']}' 
			AND competitions_index.option_id = '{$competition['answer_id']}' 
			AND doctors.id IN (SELECT doctor_id FROM doctors_resellers WHERE reseller_id = '".mysql_real_escape_string( $account['doctor_reseller_id'] )."' )
		";
	
	return $sql;
}

