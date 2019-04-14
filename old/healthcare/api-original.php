<?php

if ( !( $_SERVER['REQUEST_METHOD']=='GET' || $_SERVER['REQUEST_METHOD']=='POST') ){
	exit;
}

$_SERVER['HTTP_HOST'] = 'healthcare.prevision.me';

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//makeLogFile( $_SERVER['REQUEST_URI'] );

define('isAjaxRequest', true);
define('M_API', true);

define('_login_update_time', 3600 ); // seconds

error_reporting( E_ALL ^ E_NOTICE );

require_once "_startup.php";

$previsionDefaultImage = BASE_URL . 'prevision-healthcare.png';

//sleep(1);

//foreach($_GET as $k=>$v) {
//	$_REQUEST[$k] = $v;
//}
//foreach($_POST as $k=>$v) {
//	$_REQUEST[$k] = $v;
//}

// ALTER TABLE `competitions` ADD `wall_of_fame` INT( 10 ) NOT NULL AFTER `answer_id` ;

$params = @file_get_contents("php://input");
$row = @json_decode($params, true);
if(is_array($row)) {
	foreach($row as $k=>$v) {
		$_REQUEST[$k] = $v;
	}
}

$_page = intval( $_REQUEST['page'] );
if( $_page < 1) {
	$_page = 1;
}

$_error_must_login = "You should login first!";
$_error_already_login = "You already logged-in!";


//$_SITE_PATH_ = 'http://127.0.0.1/techram/medical/';
$_SITE_PATH_ = BASE_URL;

//$_REQUEST = $_REQUEST;
$action = $_REQUEST['action'];
$response = array();

// authentication check
$key = substr($_REQUEST["key"], 0, 32);
$key = "$key";

$account = false;
$reseller = false;

$access = false;
if ( strlen ( $_REQUEST["key"] ) >= 32 )
{
	$sql = getDoctorLogin('key', $_REQUEST["key"]);

	$q = mysql_query ($sql);
//var_dump( mysql_fetch_assoc ($q) );
//var_dump( mysql_error() );
	if ( $q && mysql_num_rows($q) ) 
	{
		$account = mysql_fetch_assoc ($q);
		$account['doctor_login_id'] = intval($account['doctor_login_id']);
		
		$reseller = getDataByID('resellers', $account['doctor_reseller_id'], " status='active' ");
		
		if( !$reseller ) {
			$account = false;
		}
	}
}

if( $account && time() - $account['doctor_login_time_update'] > _login_update_time) {
	$sql = "UPDATE `doctors_logins`
		SET `time_update`='".time()."'
		WHERE id='{$account['doctor_login_id']}'
		LIMIT 1 ";
	$q = mysql_query($sql);
	if( $q ) {
		$account['doctor_login_time_update'] = time();
	}
}



//// TODO Test - delete me
//	$sql = getDoctorLogin('test', '');
//	$q = mysql_query ($sql);
//	if ( $q && mysql_num_rows($q) ) 
//	{
//		$account = mysql_fetch_assoc ($q);
//	}
//	$action = 'documents';

$_REQUEST['limit'] = intval( $_REQUEST['limit']);

if( $account ) {
	$Categories = getAccountCategories($account);
}

if( $action == 'site_news_details' && $_REQUEST['type'] == 'medical') {
	$action = 'medical_details';
}


switch( $action ) {
	case 'site_news_details':
		$_REQUEST['id'] = intval( $_REQUEST['id'] );
		
		$strSQL = "SELECT * FROM site_news WHERE id='{$_REQUEST['id']}' LIMIT 1";
		$q = mysql_query( $strSQL );

		if( !$q ) {
			$response['error'] = 'Unable to get site news details in our system!';
		}
		else {
			$response['ok'] = true;
			if( mysql_num_rows($q)) {
				$row = mysql_fetch_assoc($q);
				$response['data'] = array(
					'id' => $row['id'],
					'title' => $row['title'],
					'description' => $row['description'],
					'link' => $row['link'],
					'date' => date('Y-m-d', $row['time']),
					'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/site_news/' . $row['image'] : $previsionDefaultImage,
				);
			}
		}
		break;
	case 'site_news':
	case 'site_home_news':
		
		$_limit = 100;
		
		if( $_REQUEST['limit'] > 0) {
			$_limit = $_REQUEST['limit'];
		}
		
		$_offset = ( $_page-1 ) * $_limit;
		
		if( $account && $action == 'site_news') {
			$medicalNews = true;
			
			$strSQL = getAccessSql($account, 'medical');
			$strSQL = "$strSQL ORDER BY medical.rank DESC LIMIT $_offset, $_limit";
			
			$imageFolder = 'medical';
			$_error_msg = 'Unable to get medical news in our system!';
		}
		else {
			$medicalNews = false;

			$strSQL = "SELECT * FROM site_news ORDER BY site_news.rank DESC LIMIT $_offset, $_limit";
			
			$imageFolder = 'site_news';
			$_error_msg = 'Unable to get site news in our system!';
		}

		$q = mysql_query( $strSQL );

		if( !$q ) {
			$response['error'] = $_error_msg;
		}
		else {
			$response['ok'] = true;
			if( mysql_num_rows($q)) {
				while($row = mysql_fetch_assoc($q)) {
					$data = array(
						'id' => $row['id'],
						'title' => $row['title'],
						'description' => summarize($row['description'], 20),
						'link' => $row['link'],
						'type' => ($medicalNews) ? 'medical' : 'site',
						'date' => date('Y-m-d', $row['time']),
						'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/' . $imageFolder . '/thumb/' . $row['image'] : $previsionDefaultImage,
					);
					
					$response['data'][] = $data;
				}
			}
		}
		break;


	case 'banners':

		$response['ok'] = true;
		if( !is_array($_REQUEST['types']) ) {
			$_REQUEST['types'] = array();
		}

		$banners = array();
		foreach($_REQUEST['types'] as $k=>$v) {
			$banners[$v][] = $k;
		}

		foreach($banners as $k=>$v) {
			
			$_Banner = $_BannersZones[ $k ];
			
			if( !$_Banner ) {
				continue;
			}

			$orderBy = " RAND() ";

			$_limit = count($v);

			$strSQL = getBannersByZone( $k, $_Banner['private'], $reseller['id'] );

			$strSQL = "$strSQL ORDER BY $orderBy LIMIT $_limit";
//	echo $strSQL;
//		$strSQL = "$strSQL ";
//		$response = $_REQUEST;
//		$response['account'] = $account;

			$q = mysql_query( $strSQL );
//echo "$strSQL " . mysql_error();
			if( $q && mysql_num_rows($q)) {
				while($row = mysql_fetch_assoc($q)) {
					mysql_query("UPDATE banners SET impressions=impressions+1 WHERE id='{$row['id']}' LIMIT 1");
					$key = array_shift( $v );
					
					$maxWidth = ($_Banner['maxWidth']) ? ' class="maxWidth" ' : '';
					$banner = '<img src="'.$_SITE_PATH_ . 'uploads/banners/thumb/' . $row['image'].'" '.$maxWidth.'/>';

					if( $row['link'] ) {
//						$link = $row['link'];
						$link = $_SITE_PATH_ . 'click.php?ad='.base64_encode(json_encode(array(
							'id' => $row['id'],
							'source' => 'app',
							'time' => time(),
						)));

						$onclick = 'if(device.platform === \'Android\'){navigator.app.loadUrl(\''.$link.'\',{openExternal:true});}else{window.open(\''.$link.'\',\'_system\');}';

						$banner = '<a href="'.$link.'" target="_blank" onclick="'.$onclick.' return false;" >'.$banner.'</a>';
					}

					$response['banners'][$key] = array(
						'id' => $row['id'],
						'title' => $row['title'],
						'description' => $row['description'],
						'date' => date('Y-m-d', $row['time']),
						'banner' => $banner,
					);
				}
			}
		}

		break;
		
	case 'is_login':
		if( !$account ) {
			$response['error'] = $_error_must_login;
			break;
		}

		$response = buildLoginResponse($_REQUEST["key"], $Categories, $account, $reseller);
		break;
	
	case 'notifications':
		if( !$account ) {
			$response['error'] = $_error_must_login;
			break;
		}

		$response = array();
		$response['notifications'] = getPushNotifications( $account );
		
		if( false ) {
			$fp = @fopen('notifications_logs.txt', 'a');
			if( $fp ) {
				$array = $response['notifications'];
				$array['account_id'] = $account['id'];
				$json = json_encode( $array );
				$json = date('Y-m-d h:i:sa') . " $json\r\n";
				fwrite($fp, $json);
				fclose();
			}
		}
		break;
	
	case 'settings':
		if( !$account ) {
			$response['error'] = $_error_must_login;
			break;
		}
		
		$sql = "UPDATE doctors_logins SET 
				notifications='".saveInsert( $_REQUEST['notifications'] )."' 
			WHERE id='{$account['doctor_login_id']}'
			LIMIT 1
			";
		
		$q = mysql_query( $sql );

		if( $q ) {
			$response['ok'] = true;
		}
		else {
			$response['error'] = 'Unable to update your settings in our system!';
//			$response['error'] = mysql_error();
		}
		break;
	
	case 'logout':
		$response['ok'] = true;
		if( !$account ) {
			break;
		}
		$sql = "DELETE FROM `doctors_logins`
			WHERE id='{$account['doctor_login_id']}'
			LIMIT 1 ";
		$q = mysql_query($sql);
		break;
	case 'categories':
		if( !$account ) {
			$response['error'] = $_error_must_login;
			break;
		}

		$response['ok'] = true;
		$response['data'] = $Categories;
		break;

	case 'news_details':
	case 'medical_details':
	case 'documents_details':
		if( !$account ) {
			$response['error'] = $_error_must_login;
			break;
		}
				
		switch($action) {
			case 'news_details':
				$table = 'news';
				break;
			case 'medical_details':
				$table = 'medical';
				break;
			case 'documents_details':
				$table = 'documents';
				break;
		}
				
		$strSQL = getAccessSql($account, $action, $_REQUEST['id']);
		$q = mysql_query("$strSQL LIMIT 1");
	
		if( !$q ) {
			$response['error'] = 'Unable to get '.$table.' details in our system!';
		}
		else {
			$response['ok'] = true;
			if( mysql_num_rows($q)) {
				$row = mysql_fetch_assoc($q);
				$response['data'] = array(
					'id' => $row['id'],
					'title' => $row['title'],
					'description' => $row['description'],
					'link' => $row['link'],
					'add_by' => $row['add_by'],
					'is_public' => $row['is_public'],
					'date' => date('Y-m-d', $row['time']),
					'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/'.$table.'/' . $row['image'] : $previsionDefaultImage,
					'document' => ($row['document']) ? $_SITE_PATH_ . 'uploads/'.$table.'/' . $row['document'] : '',
				);
			}
		}
		break;
				
	case 'news':
	case 'medical':
	case 'documents':
		if( !$account ) {
			$response['error'] = $_error_must_login;
			break;
		}
		
		$_limit = 100;
		$_offset = ( $_page-1 ) * $_limit;
		
		$strSQL = getAccessSql($account, $action);
		$q = mysql_query("$strSQL ORDER BY {$action}.rank DESC LIMIT $_offset, $_limit");

		if( !$q ) {
			$response['error'] = 'Unable to get '.$action.' in our system!';
//			$response['error'] = mysql_error();
		}
		else {
			$response['ok'] = true;
			if( mysql_num_rows($q)) {
				while($row = mysql_fetch_assoc($q)) {
					
					$img = '';
					if( $row['image'] ) {
						$img = $_SITE_PATH_ . 'uploads/'.$action.'/thumb/' . $row['image'];
					} 
					else if( $action == 'documents' && $row['document'] ) {
						$img = getFileTypeIcon($row['document']);
						$img = $_SITE_PATH_ . 'images/types/' . $img;
					}
					else {
						$img = $previsionDefaultImage;
					}

					$response['data'][] = array(
						'id' => $row['id'],
						'title' => $row['title'],
						'description' => summarize($row['description'], 20),
						'link' => $row['link'],
						'add_by' => $row['add_by'],
						'is_public' => $row['is_public'],
						'date' => date('Y-m-d', $row['time']),
						'image' => $img,
						'document' => ($row['document']) ? $_SITE_PATH_ . 'uploads/'.$action.'/' . $row['document'] : '',
					);
				}
			}
		}
		break;
		
	case 'polls':
		if( !$account ) {
			$response['error'] = $_error_must_login;
			break;
		}
				
		$_limit = 10;
		$_offset = ( $_page-1 ) * $_limit;
		
		$strSQL = getPollsSql( $account );
		$strSQL = "$strSQL ORDER BY polls.rank DESC LIMIT $_offset, $_limit";
		$q = mysql_query( $strSQL );

//		$response['error'] = $strSQL;
		if( !$q ) {
			$response['error'] = 'Unable to get polls in our system!';
			$response['error'] = mysql_error();
		}
		else {
			$response['ok'] = true;
			if( mysql_num_rows($q)) {
				while($row = mysql_fetch_assoc($q)) {
					
					$response['data'][] = array(
						'id' => $row['id'],
						'title' => $row['title'],
						'description' => summarize($row['description'], 20),
						'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/polls/thumb/' . $row['image'] : $previsionDefaultImage,
					);
				}
			}
		}
		break;
	case 'poll':
		if( !$account ) {
			$response['error'] = $_error_must_login;
			break;
		}

		$strSQL = getPollsSql( $account, $_REQUEST['poll_id'] );
		$strSQL = "$strSQL LIMIT 1";
		$q = mysql_query($strSQL);

		if( ! ($q && mysql_num_rows($q)) ) {
			$response['error'] = 'Unable to get selected poll in our system!';
//			$response['error'] = "$strSQL " . mysql_error();
		}
		else {
			$response['ok'] = true;

			$poll = mysql_fetch_assoc($q);
			
			$response['poll'] = array(
				'id' => $poll['id'],
				'title' => $poll['title'],
				'option_id' => $poll['option_id'],
				'description' => summarize($poll['description'], 20),
				'image' => ($poll['image']) ? $_SITE_PATH_ . 'uploads/polls/thumb/' . $poll['image'] : $previsionDefaultImage,
			);
			// fix in app
			$response['poll'] = array( $response['poll'] );

			$q = mysql_query("SELECT * 
				FROM polls_options 
				WHERE poll_id='{$poll['id']}' 
				ORDER BY rank DESC");

			if( $q && mysql_num_rows($q)) {
				while($row = mysql_fetch_assoc($q)) {
					$response['data'][] = array(
						'id' => $row['id'],
						'poll_id' => $row['poll_id'],
						'title' => $row['title'],
						'selected' => ($row['id']==$poll['option_id']) ? true : false,
					);
				}
			}
		}
		break;

	case 'poll_option':
		if( !$account ) {
			$response['error'] = $_error_must_login;
			break;
		}
		
		$option_id = intval( $_REQUEST['option_id']);

		$strSQL = getPollsSql( $account, $_REQUEST['poll_id'] );
		
		$q = mysql_query("$strSQL LIMIT 1");

		if( !($q && mysql_num_rows($q)) ) {
			$response['error'] = 'Unable to get selected poll in our system!';
//			$response['error'] = mysql_error();
			break;
		}

		$poll = mysql_fetch_assoc($q);
				
		if( $poll['option_id'] ) {
			$response['error'] = 'You already send answer for this poll.';
			break;
		}

		$q = mysql_query("SELECT * 
			FROM polls_options 
			WHERE poll_id='{$poll['id']}' 
				AND id='{$option_id}'
			LIMIT 1");

		if( !($q && mysql_num_rows($q)) ) {
			$response['error'] = 'Selected poll option not found!';
//			$response['error'] = mysql_error();
			break;
		}
		mysql_query("INSERT INTO polls_index SET 
			poll_id='{$poll['id']}' 
			, option_id='$option_id'
			, doctor_id='{$account['id']}'
			, date='".date('Y-m-d')."'
			, time='".time()."'
		");

		$response['ok'] = true;
		break;

	case 'competition':
		if( !$account ) {
			$response['error'] = $_error_must_login;
			break;
		}

		$strSQL = getCurrentCompetitionSql( $account );
		$q = mysql_query("$strSQL LIMIT 1");

		if( !$q ) {
			$response['error'] = 'Unable to get current competition in our system!';
//			$response['error'] = mysql_error();
		}
		else {
			$response['ok'] = true;
			if( !mysql_num_rows($q)) {
				$response['no_competition'] = true;
				$response['msg'] = 'No Competition available for this month.';
			} else {
				$competition = mysql_fetch_assoc($q);
				
				$_WallLimit = $competition['wall_of_fame'];
				if( $_WallLimit < 1 ) {
					$_WallLimit = $_CompetitionWallLimit;
				}

				$response['competition'] = array(
					'id' => $competition['id'],
					'title' => $competition['title'],
					'description' => summarize($competition['description'], 20),
					'option_id' => $competition['option_id'],
					'date' => date('Y-m-d', $competition['time']),
					'image' => ($competition['image']) ? $_SITE_PATH_ . 'uploads/competitions/thumb/' . $competition['image'] : $previsionDefaultImage,
				);
				// fix in app
				$response['competition'] = array( $response['competition'] );
			

				$sql = getCompetitionWallSql( $account, $competition );
				$q = mysql_query("$sql ORDER BY competitions_index.time ASC LIMIT $_WallLimit");
				if( $q && mysql_num_rows($q)) {
					while($row = mysql_fetch_assoc($q)) {
						$response['doctors'][] = array(
							'id' => $row['id'],
							'full_name' => $row['full_name'],
							'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/doctors/thumb/' . $row['image'] : $previsionDefaultImage,
						);
					}
				}

				$q = mysql_query("SELECT * 
					FROM competitions_options 
					WHERE competition_id='{$competition['id']}' 
					ORDER BY rank DESC");

				if( $q && mysql_num_rows($q)) {
					while($row = mysql_fetch_assoc($q)) {
						$response['data'][] = array(
							'id' => $row['id'],
							'quiz_id' => $row['competition_id'],
							'competition_id' => $row['competition_id'],
							'selected' => ($row['id'] == $competition['option_id']) ? true : false,
							'title' => $row['title']
						);
					}
				}
			}
		}
		break;

	case 'competition_option':
		if( !$account ) {
			$response['error'] = $_error_must_login;
			break;
		}
		
		$competition_id = intval( $_REQUEST['competition_id']);
		$option_id = intval( $_REQUEST['option_id']);
		
		$strSQL = getCurrentCompetitionSql( $account, $competition_id);
		$q = mysql_query("$strSQL LIMIT 1");

		if( !($q && mysql_num_rows($q)) ) {
			$response['error'] = 'Unable to get current competition in our system!';
//			$response['error'] = mysql_error();
			break;
		}

		$competition = mysql_fetch_assoc($q);
				
		$_WallLimit = $competition['wall_of_fame'];
		if( $_WallLimit < 1 ) {
			$_WallLimit = $_CompetitionWallLimit;
		}

		if( $competition['option_id'] ) {
			$response['error'] = 'You already send answered this quiz before.';
			break;
		}
			
		$q = mysql_query("SELECT * 
			FROM competitions_options 
			WHERE competition_id='{$competition['id']}' 
				AND id='$option_id'
			LIMIT 1");

		if( !($q && mysql_num_rows($q)) ) {
			$response['error'] = 'Selected competition option not found!';
//			$response['error'] = mysql_error();
			break;
		}
		mysql_query("INSERT INTO competitions_index SET 
			competition_id='{$competition['id']}' 
			, option_id='$option_id'
			, doctor_id='{$account['id']}'
			, date='".date('Y-m-d')."'
			, time='".time()."'
		");

		$response['ok'] = true;

		$sql = getCompetitionWallSql( $account, $competition );
		$q = mysql_query("$sql ORDER BY competitions_index.time ASC LIMIT $_WallLimit");
		if( $q && mysql_num_rows($q)) {
			while($row = mysql_fetch_assoc($q)) {
				$response['doctors'][] = array(
					'id' => $row['id'],
					'full_name' => $row['full_name'],
					'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/doctors/thumb/' . $row['image'] : $previsionDefaultImage,
				);
			}
		}
		break;
		

		
	case 'questions':
		if( !$account ) {
			$response['error'] = $_error_must_login;
			break;
		}
				
		$_limit = 10;
		$_offset = ( $_page-1 ) * $_limit;
		
		$strSQL = getQuestionsSql($account);
		$q = mysql_query("$strSQL ORDER BY last_reply_time DESC LIMIT $_offset, $_limit");

		if( !$q ) {
			$response['error'] = 'Unable to get questions in our system!';
//			$response['error'] = mysql_error();
		}
		else {
			$response['ok'] = true;
			if( mysql_num_rows($q)) {
				while($row = mysql_fetch_assoc($q)) {

					$response['data'][] = array(
						'id' => $row['id'],
						'title' => $row['title'],
						'description' => summarize($row['description'], 20),
						'replies' => $row['replies'],
						'date' => date('Y-m-d', $row['time']),

						'name' => $row['name'],
						'email' => $row['email'],
						'phone' => $row['phone'],
						'address' => $row['address'],

						'last_reply_from' => $row['last_reply_from'],
						'last_reply_date' => ($row['last_reply_time']) ? date('Y-m-d', $row['last_reply_time']) : '',
						'last_reply_time' => ($row['last_reply_time']) ? date('h:ia', $row['last_reply_time']) : '',
						'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/questions/thumb/' . $row['image'] : $previsionDefaultImage,
					);
				}
			}
		}
		break;
	case 'questions_replies':
		if( !$account ) {
			$response['error'] = $_error_must_login;
			break;
		}
		
		$_limit = 10;
		$_offset = ( $_page-1 ) * $_limit;
		
		$id = intval($_REQUEST['id']);
		
		$strSQL = getQuestionsSql($account, $id);
		$q = mysql_query("$strSQL LIMIT 1");

		if( !$q ) {
			$response['error'] = 'Unable to get questions in our system!';
			$response['error'] = mysql_error();
		}
		else {
			$response['ok'] = true;
			if( mysql_num_rows($q)) {

				$question = mysql_fetch_assoc($q);
				if( !($_page > 1) ) {
					$response['question'] = array(
						'id' => $question['id'],
						'title' => $question['title'],
						'description' => $question['description'],
						'date' => date('Y-m-d', $question['time']),

						'name' => $question['name'],
						'email' => $question['email'],
						'phone' => $question['phone'],
						'address' => $question['address'],
					
						'image' => ($question['image']) ? $_SITE_PATH_ . 'uploads/questions/' . $question['image'] : $previsionDefaultImage,
					);
				}
				// fix in app
				$response['question'] = array( $response['question'] );
				
				$q = mysql_query("SELECT * 
					FROM questions_replies 
					WHERE question_id='{$question['id']}' 
					ORDER BY time ASC
					LIMIT $_offset, $_limit");

				if( $q && mysql_num_rows($q)) {
					while($row = mysql_fetch_assoc($q)) {

						$response['data'][] = array(
							'id' => $row['id'],
							'description' => $row['description'],
							'from' => $row['from'],
							'date' => date('Y-m-d', $row['time']),
							'time' => date('h:ia', $row['time']),
							'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/questions/thumb/' . $row['image'] : $previsionDefaultImage,
						);
					}
				}
			}
		}
		break;
	case 'question_add':
		if( !$account ) {
			$response['error'] = $_error_must_login;
			break;
		}
		
		if( empty($_REQUEST['question_title']) ) {
			$response['error'] = 'Missing Question Title!';
		}
		else if( empty($_REQUEST['question_description']) ) {
			$response['error'] = 'Missing Question Details!';
		}
//		else if( empty($_REQUEST['contact_name']) ) {
//			$response['error'] = 'Missing Contact Name!';
//		}
//		else if( empty($_REQUEST['contact_email']) ) {
//			$response['error'] = 'Missing Contact Email!';
//		}
//		else if( !isemail($_REQUEST['contact_email']) ) {
//			$response['error'] = 'Invalid Contact Email!';
//		}
//		else if( empty($_REQUEST['contact_phone']) ) {
//			$response['error'] = 'Missing Contact Phone!';
//		}
//		else if( !is_numeric($_REQUEST['contact_phone']) ) {
//			$response['error'] = 'Invalid Contact Phone!';
//		}
//		else if( empty($_REQUEST['contact_address']) ) {
//			$response['error'] = 'Missing Contact Address!';
//		}
		else {
			$q=mysql_query("SELECT max(rank) as max FROM questions");
			$r = mysql_fetch_object($q);
				
			$q = mysql_query("INSERT INTO questions SET 
				reseller_id = '{$reseller['id']}'
				, doctor_id = '{$account['id']}'
				, title='".saveInsert( $_REQUEST['question_title'] )."'
				, description='".saveInsert( $_REQUEST['question_description'] )."'

				, name='".saveInsert( $_REQUEST['contact_name'] )."'
				, email='".saveInsert( $_REQUEST['contact_email'] )."'
				, phone='".saveInsert( $_REQUEST['contact_phone'] )."'
				, address='".saveInsert( $_REQUEST['contact_address'] )."'

				, status='active'
				, rank='".($r->max+1)."'
				, date='".date('Y-m-d')."'
				, time='".time()."'
			");
			if( !$q ) {
				$response['error'] = 'Unable to insert question in our system!';
//				$response['error'] = mysql_error();
			}
			else {
				$response['ok'] = true;
			}
		}
		break;
	case 'question_reply':
		if( !$account ) {
			$response['error'] = $_error_must_login;
			break;
		}

		$id = intval($_REQUEST['id']);
		if( $id < 1) {
			$id = intval($_REQUEST['question_id']);
		}

		$strSQL = getQuestionsSql($account, $id);
		$q = mysql_query("$strSQL LIMIT 1");

//$response['error'] = $strSQL;
		if( !($q && mysql_num_rows($q) ) ) {
			$response['error'] = 'Unable to get questions in our system!';
//			$response['error'] = mysql_error();
		}
		else if( empty($_REQUEST['question_reply']) ) {
			$response['error'] = 'Missing Reply Field!';
//			$response['error'] = mysql_error();
		}
		else {
			$question = mysql_fetch_assoc($q);

			$q=mysql_query("SELECT max(rank) as max FROM questions_replies");
			$r = mysql_fetch_object($q);
				
			$q = mysql_query("INSERT INTO questions_replies SET 
				question_id = '{$question['id']}'
				, `from` = ''
				, description='".saveInsert( $_REQUEST['question_reply'] )."'
				, rank='".($r->max+1)."'
				, date='".date('Y-m-d')."'
				, time='".time()."'
			");
			if( !$q ) {
				$response['error'] = 'Unable to insert question\'s reply in our system!';
//				$response['error'] = mysql_error();
			}
			else {
				$insert_id = mysql_insert_id();
				$response['ok'] = true;

				$response['reply']= array(
					'id' => $insert_id,
					'description' => $_REQUEST['question_reply'],
					'from' => '',
					'date' => date('Y-m-d'),
					'time' => date('h:ia'),
					'image' => '',
				);
			}
		}
		break;
		
		
		
	case 'login':
		if( $account ) {
			$response['error'] = $_error_already_login;
			break;
		}
		
		$error = '';
		$response = array();

		$username = $_REQUEST['email'];
		$password = $_REQUEST['password'];
		$regid = array();
			
		// TODO testing
//		if( empty( $_REQUEST['regid'] ) ) {
//			$_REQUEST['regid'] = $username;
//		}
//		if( empty( $_REQUEST['source'] ) ) {
//			$_REQUEST['source'] = 'android';
//		}

		if( empty($username) ) {
			$error = "Missing Username!!";
		}
		else if( empty($password) ) {
			$error = "Missing Password!!";
		}
		else if( empty( $_REQUEST['regid'] ) ) {
			$error = "Missing registration ID, Please contact app support!!";
//			$error = print_r( $_REQUEST , true);
		}
		else {
			switch( $_REQUEST['source'] ) {
				case 'android':
				case 'ios':
					break;
				default:
					$error = "Missing source var, Please contact app support!!";
					break;
			}
		}
		
		if( empty( $error ) ) {
			$api_hash = md5( rand(111111, 999999) .time() ) .time();
	
			$sql = getDoctorLogin('login', $username, $password );
	
			$q = mysql_query ($sql);
//		echo mysql_error();
			if ( $q && mysql_num_rows($q) ) {
				$row = mysql_fetch_assoc ($q);
	
				$reseller = getDataByID('resellers', $row['doctor_reseller_id'], " status='active' ");
	
				if( $reseller ) {
	
					$key = md5($row['id'] . $row['email'] . $row['password'] . $api_hash);
	
					$q = mysql_query("REPLACE INTO doctors_logins SET 
						`api_hash` = '".saveInsert( $key )."' 
						, `doctor_id`='{$row['id']}' 
						, `source`='".saveInsert( $_REQUEST['source'] )."'
						, `regid`='".saveInsert( $_REQUEST['regid'] )."'
						, `notifications` = '1'
						, `time`='".time()."'
						, `time_update`='".time()."'
					"); // INSERT
					if( $q ) {
						$row['doctor_login_id'] = mysql_insert_id();
						$row['notifications'] = '1';
						$Categories = getAccountCategories( $row );
	
						$response = buildLoginResponse($key, $Categories, $row, $reseller);
//						$response['notifications'] = getPushNotifications( $row );

						$q = mysql_query("INSERT INTO doctors_logins_devices SET 
							`doctor_id`='{$row['id']}' 
							, `source`='".saveInsert( $_REQUEST['source'] )."'
							, `regid`='".saveInsert( $_REQUEST['regid'] )."'
							, time='".time()."'
							ON DUPLICATE KEY UPDATE time_updated='".time()."'
						");
					}
				}
				if( !$response ) {
					$error = "Login Failed!!";
				}
			}
			else {
				$error = "Username or password is incorrect!!";
			}
		}
		
		if( $error ) {
			$response['error'] = "$error";
		}
		break;
		
}
/*
		if( !$account ) {
			$response['error'] = $_error_must_login;
			break;
		}
*/
if( !$response ) {
	if( $account ) {

		switch( $action ) {
//			case 'competitions':
//				
//				$_limit = 10;
//				$_offset = ( $_page-1 ) * $_limit;
//				
//				$strSQL = getCompetitionsSql($account, false, true);
//				$q = mysql_query("$strSQL ORDER BY competitions.rank DESC LIMIT $_offset, $_limit");
//	
//				if( !$q ) {
//					$response['error'] = 'Unable to get competitions in our system!';
////					$response['error'] = mysql_error();
//				}
//				else {
//					$response['ok'] = true;
//					if( mysql_num_rows($q)) {
//						while($row = mysql_fetch_assoc($q)) {
//							$start = mktime(0,0,0, $row['month'], $row['day_start'], $row['year']);
//							$end = mktime(0,0,0, $row['month'], $row['day_end'], $row['year'])+86400-1;
//
//							$response['data'][] = array(
//								'id' => $row['id'],
//								'title' => $row['title'],
//								'description' => summarize($row['description'], 20),
//								'date' => date('Y-m-d', $row['time']),
//								'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/competitions/thumb/' . $row['image'] : $previsionDefaultImage,
//							);
//						}
//					}
//				}
//				break;
		}
	}
}

if( !$response ) {
	$response['error'] = 'Invalid action!';
}

if( $_REQUEST['debug'] ) {
	$response['debug'] = (array) $response['debug'];
	$response['debug']['request'] = $_REQUEST;
}

//var_dump( $response ); exit;

echo json_encode($response);
exit;


function buildLoginResponse($key, $Categories, $account, $reseller) {
	$response['ok'] = true;
	$response['key'] = $key;
	$response['categories'] = $Categories;

	$response['account'] = array(
		'id' => $account['id'],
		'first_name' => $account['first_name'],
		'last_name' => $account['last_name'],
		'full_name' => $account['full_name'],
		'email' => $account['email'],
		'notifications' => $account['notifications'],
		'image' => ($account['image']) ? $_SITE_PATH_ . 'uploads/doctors/thumb/' . $account['image'] : $previsionDefaultImage,
	);

	$response['reseller'] = array(
		'id' => $reseller['id'],
		'title' => $reseller['title'],
		'logo' => ($reseller['image']) ? $_SITE_PATH_ . 'uploads/resellers/' . $reseller['image'] : $previsionDefaultImage,
	);
	
	return $response;
}


function getPushNotifications( $account ) {
	$notifications = array();
	$sql = "SELECT * FROM doctors_logins WHERE id='{$account['doctor_login_id']}' LIMIT 1";
	$q = mysql_query( $sql );
	if( $q && mysql_num_rows($q)) {
		$row = mysql_fetch_assoc($q);
	
		if( $row['notification_news'] > 0) {
			$notifications['news'] = $row['notification_news'];
		}
		if( $row['notification_medical'] > 0) {
			$notifications['medical'] = $row['notification_medical'];
		}
		if( $row['notification_documents'] > 0) {
			$notifications['documents'] = $row['notification_documents'];
		}
		if( $row['notification_replies'] > 0) {
			$notifications['replies'] = $row['notification_replies'];
		}

		mysql_query( "UPDATE doctors_logins SET 
			notification_updated=''
			, notification_news =0
			, notification_medical =0
			, notification_documents =0
			, notification_replies =0
		WHERE id='{$account['doctor_login_id']}' LIMIT 1
		" );
	}

	return $notifications;
}




function saveInsert($string) {
	$string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8', false);
	$string = mysql_real_escape_string( $string );
	
	return $string;
}


function makeLogFile($text = '') {

	$fp = @fopen('api.txt', 'a');
	if( $fp ) {
		$args = func_get_args();
		foreach($args as $arg) {
			if(is_array($arg)) {
				fwrite($fp, json_encode($arg)."\r\n");
			}
			else {
				fwrite($fp, "{$arg}\r\n");
			}
		}
		@fwrite($fp, "==========================\r\n");
		@fclose($fp);
	}
}