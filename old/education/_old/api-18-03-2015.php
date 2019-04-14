<?php

//$_SERVER['HTTP_HOST'] = 'education.prevision.me';

if ( !( $_SERVER['REQUEST_METHOD']=='GET' || $_SERVER['REQUEST_METHOD']=='POST') ){
	exit;
}
//sleep(1);

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//makeLogFile( $_SERVER['REQUEST_URI'] );

$_should_login_error = "You should login first!";
$_already_login_error = "You already signed in!";

define('isAjaxRequest', true);
define('M_API', true);

define('_login_update_time', 3600 ); // seconds

//foreach($_GET as $k=>$v) {
//	$_REQUEST[$k] = $v;
//}
//foreach($_POST as $k=>$v) {
//	$_REQUEST[$k] = $v;
//}

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

error_reporting( E_ALL ^ E_NOTICE );
require_once "_startup.php";

$previsionDefaultImage = BASE_URL . 'prevision-education.png';

//$_SITE_PATH_ = 'http://127.0.0.1/techram/medical/';
$_SITE_PATH_ = BASE_URL;

//$_REQUEST = $_REQUEST;
$action = $_REQUEST['action'];
$response = array();

// authentication check
$key = substr($_REQUEST["key"], 0, 32);
$key = "$key";

$account = false;

$access = false;
if ( strlen ( $_REQUEST["key"] ) >= 32 )
{
	$sql = getUserLogin('key', $_REQUEST["key"]);

	$q = mysql_query ($sql);
	
	if ( $q && mysql_num_rows($q) ) 
	{
		$account = mysql_fetch_assoc ($q);
		
		$account = getStudentsAndSchools( $account );

		if( $account['school'] ) {
			//
		}
		else {
			$account = false;
		}
	}
}

if( $account && time() - $account['user_login_time_update'] > _login_update_time) {
	$sql = "UPDATE `users_logins`
		SET `time_update`='".time()."'
		WHERE id='{$account['user_login_id']}'
		LIMIT 1 ";
	$q = mysql_query($sql);
	if( $q ) {
		$account['user_login_time_update'] = time();
	}
}


$galleryCategories = array();
$newsCategories = getsectionsCategories( 'news_category' );
$documentsCategories = getsectionsCategories( 'documents_category' );

if( $account ) {
	$galleryCategories = getsectionsCategories( 'gallery_category', " school_id IN( {$account['schoolIDs']} ) ");
}

//// TODO Test - delete me
//	$sql = getStudentLogin('test', '');
//	$q = mysql_query ($sql);
//	if ( $q && mysql_num_rows($q) ) 
//	{
//		$account = mysql_fetch_assoc ($q);
//	}
//	$action = 'documents';

$_REQUEST['limit'] = intval( $_REQUEST['limit']);

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
	case 'site_news_home':

		$_limit = 100;
		
		if( $_REQUEST['limit'] > 0) {
			$_limit = $_REQUEST['limit'];
		}
		
		$_offset = ( $_page-1 ) * $_limit;

		$where = '';
		
		$strSQL = "SELECT * 
			FROM site_news 
			WHERE true {$where} 
			ORDER BY site_news.rank DESC 
			LIMIT $_offset, $_limit
		";
		$q = mysql_query( $strSQL );

		if( !$q ) {
			$response['error'] = 'Unable to get site news in our system!';
		}
		else {
			$response['ok'] = true;
			if( mysql_num_rows($q)) {
				while($row = mysql_fetch_assoc($q)) {
					$response['data'][] = array(
						'id' => $row['id'],
						'title' => $row['title'],
					
						'description' => summarize($row['description'], 20),
						'link' => $row['link'],
						'date' => date('Y-m-d', $row['time']),
						'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/site_news/thumb/' . $row['image'] : $previsionDefaultImage,
					);
				}
			}
		}
		break;


	case 'banners':

		$response['ok'] = true;
		if( !is_array($_REQUEST['types']) ) {
			$_REQUEST['types'] = array();
		}
		
		$_school = $account['schools'][ $_REQUEST['school_id'] ];
		$_school = ($_school) ? $_school : array();

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

			$strSQL = getBannersByZone( $k, $_Banner['private'], $_school['id']);

			$strSQL = "$strSQL ORDER BY $orderBy LIMIT $_limit";
			
//			$strSQL .= " WHERE TRUE ";
//
//			if( $_Banner['private'] ) {
//
//				$_school_id = intval($_REQUEST['school_id'] );
//
//				if( $_school_id > 0 && $account['schools'][ $_school_id ] ) {
//					$strSQL .= " AND school_id = '{$_school_id}' ";
//				}
//				else {
//					$strSQL .= " AND FALSE ";
//				}
//			}
//			else {
//				$strSQL .= " AND school_id = 0 ";
//			}
//			
//			$strSQL = "$strSQL ORDER BY $orderBy LIMIT $_limit";

			$q = mysql_query( $strSQL );

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
		
	case 'about':
		
		if( !$account ) {
			$error = $_should_login_error;
			break;
		}

		$_school = $account['schools'][ $_REQUEST['school_id'] ];
		if( ! $_school ) {
			$response['error'] = "Missing School!";
			break;
		}

		$response['ok'] = true;
		
		$response['title'] = $_school['title'];
		$response['description'] = $_school['description'];
		
		$response['data'] = array(
			'title' => $_school['title'],
			'description' => $_school['description'],
		);
//	var_dump($account['school']);
		break;
	case 'agenda':
	
		if( !$account ) {
			$error = $_should_login_error;
			break;
		}

		$_school = $account['schools'][ $_REQUEST['school_id'] ];
		if( ! $_school ) {
			$response['error'] = "Missing School!";
			break;
		}

		$_student = $account['students'][ $_REQUEST['student_id'] ];
		if( ! $_student ) {
			$response['error'] = "Missing Student!";
			break;
		}

//		$selectClasses = " SELECT id FROM classes WHERE classes.school_id ='{$_school['id']}' AND classes.id IN ( {$account['classIDs']} ) ";
		
		$_limit = 100;
		$_offset = ( $_page-1 ) * $_limit;

		$where = " TRUE ";

//		$where .= " AND agenda.school_id='{$account['school_id']}' ";
//		$where .= " AND agenda.class_id='{$account['class_id']}' ";
//		$where .= " AND agenda.school_id IN ( {$account['schoolIDs']} ) ";
//		$where .= " AND agenda.class_id IN ( {$selectClasses} ) ";
		$where .= " AND agenda.class_id = '{$_student['class_id']}' ";

//		if( $_school ) {
//			$where .= " AND agenda.school_id ='{$_school['id']}' ";
//		}
		
//		$where .= " AND agenda.status='active' ";
		
		$strSQL = "SELECT agenda.* 
		, classes.title as class_title
		, teachers.title as teacher_name
		, DATE_FORMAT(date, '%W %d-%m-%Y') as title
		FROM agenda 
		LEFT JOIN classes ON (classes.id=agenda.class_id)
		LEFT OUTER JOIN teachers ON (teachers.id=agenda.teacher_id)

		WHERE $where
		";
		
		$sql = "$strSQL ORDER BY agenda.date DESC LIMIT $_offset, $_limit";

		$q = mysql_query( $sql );
//	echo "$sql " .mysql_error();
//var_dump( $sql );

		if( !$q ) {
			$response['error'] = 'Unable to get agenda in our system!';
//			$response['error'] = mysql_error();
		}
		else {
			$response['ok'] = true;

			if( mysql_num_rows($q)) {
				while($row = mysql_fetch_assoc($q)) {
//		var_dump($row);

					$data = array(
						'id' => $row['id'],
						'title' => $row['title'],
						'description' => $row['description'],
						'school_id' => $row['school_id'],
						'class_title' => $row['class_title'],
						'teacher_name' => $row['teacher_name'],
						'date' => $row['date'],
					);

					$response['data'][] = $data;
				}
			}
		}
		break;
	case 'news_details':
	case 'education_news_details':
	case 'documents_details':
	case 'videos_details':
	case 'gallery_details':

		if( !$account ) {
			$error = $_should_login_error;
			break;
		}

//echo $_REQUEST['school_id'];
//echo "<br>->";
//print_r($account['schools'][ $_REQUEST['school_id'] ]);

		$_school = $account['schools'][ $_REQUEST['school_id'] ];
		if( ! $_school ) {
			$response['error'] = "Missing School!";
			break;
		}
		
		switch($action) {
			case 'news_details':
				$table = 'news';
				break;
			case 'education_news_details':
				$table = 'education_news';
				break;
			case 'documents_details':
				$table = 'documents';
				break;
			case 'videos_details':
				$table = 'videos';
				break;
			case 'gallery_details':
				$table = 'gallery';
				break;
		}
		
		$where = ' TRUE ';
		if( $table == 'education_news') {
			$where .= " AND ( `schools`.id = '{$_school['id']}' OR education_news.school_id='-1' ) ";
		}
		else {
			$where .= " AND `schools`.id = '{$_school['id']}' ";
		}
		
		$strSQL = getAccessSql($account, $action, $_REQUEST['id'], $where);

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
					'youtubeLink' => $row['youtubeLink'],
//					'school_id' => $row['school_id'],
					'date' => date('Y-m-d', $row['time']),
					'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/'.$table.'/' . $row['image'] : $previsionDefaultImage,
					'video' => $row['video'],
				);
				
				if($response['data']['link']!=''){
					$response['data']['description']=$response['data']['description'].'<br><br> Related Link: <br><a style="color:#03F" onclick="if(device.platform === \'Android\'){navigator.app.loadUrl(\''.$response['data']['link'].'\',{openExternal:true});}else{window.open(\''.$response['data']['link'].'\',\'_system\');}" return false>'.$response['data']['link'].'</a>';
					}
				if($response['data']['youtubeLink']!=''){
					$response['data']['description']=$response['data']['description'].'<br><br> Youtube Link: <br><a style="color:#03F" onclick="if(device.platform === \'Android\'){navigator.app.loadUrl(\''.$response['data']['youtubeLink'].'\',{openExternal:true});}else{window.open(\''.$response['data']['youtubeLink'].'\',\'_system\');}" return false>'.$response['data']['youtubeLink'].'</a>';
					}					
				
				if( $table = 'videos' ) {
					
					$youtube_id = parse_youtube_url( $row['video'], true );
					
					$response['data']['youtube_id'] = $youtube_id;
				}
				elseif( $table == 'news') {
					$response['data']['news_cat_id'] = $row['news_cat_id'];
					$response['data']['news_cat_title'] = $newsCategories[ $row['news_cat_id'] ]['title'];
				}
				elseif( $table == 'education_news') {
					$response['data']['news_cat_id'] = $row['news_cat_id'];
					$response['data']['news_cat_title'] = $newsCategories[ $row['news_cat_id'] ]['title'];
				}
				else if( $table == 'documents') {
					$response['data']['document_cat_id'] = $row['document_cat_id'];
					$response['data']['document_cat_title'] = $documentsCategories[ $row['document_cat_id'] ]['title'];
					
					$response['data']['document'] = ($row['document']) ? $_SITE_PATH_ . 'uploads/'.$table.'/' . $row['document'] : '';
				}
				else if( $table == 'gallery') {
					$response['data']['album_id'] = $row['gallery_cat_id'];
					$response['data']['album_title'] = $galleryCategories[ $row['gallery_cat_id'] ]['title'];
				}
			}
		}
		break;
			
	case 'news':
	case 'education_news':
	case 'documents':
	case 'videos':
	case 'gallery':

		if( !$account ) {
			$error = $_should_login_error;
			break;
		}

		$_school = $account['schools'][ $_REQUEST['school_id'] ];
		if( ! $_school ) {
			$response['error'] = "Missing School!";
			break;
		}
			
		$_limit = 100;
		$_offset = ( $_page-1 ) * $_limit;
		
		$table = $action;
		
		$where = ' TRUE ';
		if( $table == 'education_news') {
			$where .= " AND ( `schools`.id = '{$_school['id']}' OR education_news.school_id='-1' ) ";
		}
		else {
			$where .= " AND `schools`.id = '{$_school['id']}' ";
		}
//		$where .= " AND `{$table}`.school_id = `schools`.id ";
//	var_dump($_REQUEST);
//		if( $table == 'news') {
//			$news_cat_id = intval( $_REQUEST['news_cat_id'] );
//			if( $news_cat_id > 0 ) {
//				$where .= " AND news.news_cat_id = '{$news_cat_id}' ";
//			}
//		}
//		else 
		if( $table == 'education_news') {
			$news_cat_id = intval( $_REQUEST['news_cat_id'] );
			if( $news_cat_id > 0 ) {
				$where .= " AND education_news.news_cat_id = '{$news_cat_id}' ";
			}
		}
		else if( $table == 'documents') {
			$document_cat_id = intval( $_REQUEST['document_cat_id'] );
			if( $document_cat_id > 0 ) {
				$where .= " AND documents.document_cat_id = '{$document_cat_id}' ";
			}
		}
		else if( $table == 'gallery') {
			$album_id = intval( $_REQUEST['album_id'] );
			if( $album_id > 0 ) {
				$where .= " AND gallery.gallery_cat_id = '{$album_id}' ";
			}
		}

//		$_school = $account['schools'][ $_REQUEST['school_id'] ];
//		if( $_school ) {
//			$where .= " AND `{$table}`.school_id ='{$_school['id']}' ";
//		}

//		var_dump($where);
		$strSQL = getAccessSql($account, $action, 0, $where);
//	var_dump($strSQL);

		$_sql = "$strSQL ORDER BY {$table}.rank DESC LIMIT $_offset, $_limit";
//echo $_sql;
		$q = mysql_query( $_sql );

//		$response['sql'] = "{$_sql} " . mysql_error();
		if( !$q ) {
			$response['error'] = 'Unable to get '.$table.' in our system!';
//			$response['error'] = "{$_sql} " . mysql_error();
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
					
					$data = array(
						'id' => $row['id'],
						'title' => $row['title'],
//						'school_id' => $row['school_id'],
						'description' => summarize($row['description'], 20),
						'link' => $row['link'],
						'date' => date('Y-m-d', $row['time']),
						'image' => $img,
						'document' => ($row['document']) ? $_SITE_PATH_ . 'uploads/'.$action.'/' . $row['document'] : '',
						'video' => $row['video'],
					);
					
//					if( $table == 'news') {
//						$data['news_cat_id'] = $row['news_cat_id'];
//						$data['news_cat_title'] = $newsCategories[ $row['news_cat_id'] ]['title'];
//					}
//					else 
					if( $table == 'education_news') {
						$data['news_cat_id'] = $row['news_cat_id'];
						$data['news_cat_title'] = $newsCategories[ $row['news_cat_id'] ]['title'];
					}
					else if( $table == 'documents') {
						$data['document_cat_id'] = $row['document_cat_id'];
						$data['document_cat_title'] = $documentsCategories[ $row['document_cat_id'] ]['title'];
					}
					else if( $table == 'gallery') {
						$data['album_id'] = $row['gallery_cat_id'];
						$data['album_title'] = $galleryCategories[ $row['gallery_cat_id'] ]['title'];
					}
//var_dump($row);
					$response['data'][] = $data;
				}
			}
		}
		
//	var_dump( $response );
		break;
		
	case 'polls':
		if( !$account ) {
			$response['error'] = $_error_must_login;
			break;
		}
		
		$_school = $account['schools'][ $_REQUEST['school_id'] ];
		if( ! $_school ) {
			$response['error'] = "Missing School!";
			break;
		}
				
		$_limit = 100;
		$_offset = ( $_page-1 ) * $_limit;
		
		$strSQL = getPollsSql( $account, $_school['id'] );
		$strSQL = "$strSQL ORDER BY polls.rank DESC LIMIT $_offset, $_limit";
//echo $strSQL;
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
		
		$_school = $account['schools'][ $_REQUEST['school_id'] ];
		if( ! $_school ) {
			$response['error'] = "Missing School!";
			break;
		}

		$strSQL = getPollsSql( $account, $_school['id'], $_REQUEST['poll_id'] );
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
		
		$_school = $account['schools'][ $_REQUEST['school_id'] ];
		if( ! $_school ) {
			$response['error'] = "Missing School!";
			break;
		}
		
		$option_id = intval( $_REQUEST['option_id']);

		$strSQL = getPollsSql( $account, $_school['id'], $_REQUEST['poll_id'] );
		
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
		
		if( !$_REQUEST['test'] ) {
			mysql_query("INSERT INTO polls_index SET 
				poll_id='{$poll['id']}' 
				, option_id='$option_id'
				, user_id='{$account['id']}'
				, date='".date('Y-m-d')."'
				, time='".time()."'
			");
		}

		$response['ok'] = true;
		break;
		
	case 'competition':
			
		if( !$account ) {
			$error = $_should_login_error;
			break;
		}
		
		$_school = $account['schools'][ $_REQUEST['school_id'] ];
		if( ! $_school ) {
			$error = "Missing School!";
			break;
		}

		$strSQL = getCurrentCompetitionSql( $account, $_school['id'] );
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
				$sql = "$sql ORDER BY competitions_index.time ASC LIMIT $_WallLimit";

				$q = mysql_query( $sql);
//		echo "{$sql} " . mysql_error();
				if( $q && mysql_num_rows($q)) {
					while($row = mysql_fetch_assoc($q)) {
						$response['users'][] = array(
							'id' => $row['id'],
							'full_name' => $row['title'],
							'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/users/thumb/' . $row['image'] : $previsionDefaultImage,
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
			$error = $_should_login_error;
			break;
		}

		$_school = $account['schools'][ $_REQUEST['school_id'] ];
		if( ! $_school ) {
			$response['error'] = "Missing School!";
			break;
		}
		
		$competition_id = intval( $_REQUEST['competition_id']);
		$option_id = intval( $_REQUEST['option_id']);
		
		$strSQL = getCurrentCompetitionSql( $account,$_school['id'], $competition_id );
		$q = mysql_query("$strSQL LIMIT 1");

		if( !$q ) {
			$response['error'] = 'Unable to get current competition in our system!';
//			$response['error'] = mysql_error();
			break;
		}
		
		if( mysql_num_rows($q)) {
			$competition = mysql_fetch_assoc($q);
		}
		else {
			$response['error'] = 'Selected competition not found!';
			break;
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
		
		if( !$_REQUEST['test'] ) {
			mysql_query("INSERT INTO competitions_index SET 
				competition_id='{$competition['id']}' 
				, option_id='$option_id'
				, user_id='{$account['id']}'
				, date='".date('Y-m-d')."'
				, time='".time()."'
			");
		}

		$response['ok'] = true;

		$sql = getCompetitionWallSql( $account, $competition );
		$q = mysql_query("$sql ORDER BY competitions_index.time ASC LIMIT $_CompetitionWallLimit");
		if( $q && mysql_num_rows($q)) {
			while($row = mysql_fetch_assoc($q)) {
				$response['users'][] = array(
					'id' => $row['id'],
					'full_name' => $row['title'],
					'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/users/thumb/' . $row['image'] : $previsionDefaultImage,
				);
			}
		}
		break;

//	case 'competitions':
//			
//		if( !$account ) {
//			$error = $_should_login_error;
//			break;
//		}
//				
//		$_limit = 10;
//		$_offset = ( $_page-1 ) * $_limit;
//		
//		$strSQL = getQuestionsSql($account, false, true);
//		$q = mysql_query("$strSQL ORDER BY competitions.rank DESC LIMIT $_offset, $_limit");
//
//		if( !$q ) {
//			$response['error'] = 'Unable to get competitions in our system!';
////			$response['error'] = mysql_error();
//		}
//		else {
//			$response['ok'] = true;
//			if( mysql_num_rows($q)) {
//				while($row = mysql_fetch_assoc($q)) {
//					$start = mktime(0,0,0, $row['month'], $row['day_start'], $row['year']);
//					$end = mktime(0,0,0, $row['month'], $row['day_end'], $row['year'])+86400-1;
//
//					$response['data'][] = array(
//						'id' => $row['id'],
//						'title' => $row['title'],
//						'description' => summarize($row['description'], 20),
//						'date' => date('Y-m-d', $row['time']),
//						'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/competitions/thumb/' . $row['image'] : $previsionDefaultImage,
//					);
//				}
//			}
//		}
//		break;
	case 'questions':
			
		if( !$account ) {
			$error = $_should_login_error;
			break;
		}

		$_school = $account['schools'][ $_REQUEST['school_id'] ];
		if( ! $_school ) {
			$response['error'] = "Missing School!";
			break;
		}
				
		$_limit = 100;
		$_offset = ( $_page-1 ) * $_limit;
		
		$where = ' TRUE ';
		$where .= " AND questions.school_id = '{$_school['id']}' ";

		$strSQL = getQuestionsSql($account, false, $where);
		
		$sql = "$strSQL ORDER BY last_reply_time DESC LIMIT $_offset, $_limit";
		
		$q = mysql_query( $sql );

		if( !$q ) {
			$response['error'] = 'Unable to get questions in our system!';
//			$response['error'] = mysql_error();
//			$response['error'] = $sql;
		}
		else {
			$response['ok'] = true;
			if( mysql_num_rows($q)) {
				while($row = mysql_fetch_assoc($q)) {

					$response['data'][] = array(
						'id' => $row['id'],
						'title' => $row['title'],
						'school_id' => $row['school_id'],
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
			$error = $_should_login_error;
			break;
		}

		$_school = $account['schools'][ $_REQUEST['school_id'] ];
		if( ! $_school ) {
			$response['error'] = "Missing School!";
			break;
		}
				
		$_limit = 100;
		$_offset = ( $_page-1 ) * $_limit;
		
		$id = intval($_REQUEST['id']);
		
		$where = ' TRUE ';
		$where .= " AND questions.school_id = '{$_school['id']}' ";

		$strSQL = getQuestionsSql($account, $id, $where);
		
		$q = mysql_query("$strSQL LIMIT 1");

		if( !$q ) {
			$response['error'] = 'Unable to get questions in our system!';
//			$response['error'] = mysql_error();
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
						'school_id' => $question['school_id'],

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
			$error = $_should_login_error;
			break;
		}

		$_school = $account['schools'][ $_REQUEST['school_id'] ];
		if( ! $_school ) {
			$response['error'] = "Missing School!";
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
				
//				school_id = '{$school['id']}'
			$q = mysql_query("INSERT INTO questions SET 
				school_id = '{$_school['id']}' 
				, user_id = '{$account['id']}'
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
			$error = $_should_login_error;
			break;
		}

		$_school = $account['schools'][ $_REQUEST['school_id'] ];
		if( ! $_school ) {
			$response['error'] = "Missing School!";
			break;
		}

		$id = intval($_REQUEST['id']);
		if( $id < 1) {
			$id = intval($_REQUEST['question_id']);
		}
		
		$where = ' TRUE ';
		$where .= " AND questions.school_id = '{$_school['id']}' ";

		$strSQL = getQuestionsSql($account, $id, $where);
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
					'image' => $previsionDefaultImage,
				);
			}
		}
		break;
		
	case 'register':
			
		if( $account ) {
			$error = $_already_login_error;
			break;
		}

		$error = '';
		$response = array();

		$username = $_REQUEST['mobile'];
		
		if( empty( $_REQUEST['full_name'] ) ) {
			$error = "Missing Full Name!!";
		}
		else if( empty($username) ) {
			$error = "Missing Mobile Number!!";
		}
		else if( !validateMobileNumber( $username ) ) {
			$error = "Invalid Mobile Number!!";
		}
		else if( !empty( $_REQUEST['phone'] ) && !is_numeric( $_REQUEST['phone'] ) ) {
			$error = "Invalid Phone Number!!";
		}
		else if( !empty( $_REQUEST['email'] ) && !isemail( $_REQUEST['email'] ) ) {
			$error = "Invalid Email Address!!";
		}
				
		// Check if account already registered
		if( empty( $error ) ) {
			
			$sql = "SELECT * FROM users WHERE username='".saveInsert( $username )."' LIMIT 1";
			
			$q = mysql_query( $sql );
					
//		$error = "$sql" . mysql_error();
			if( !$q ) {
				$error = 'Unable to check account availability in our system!';
			}
			else {
				if( mysql_num_rows($q)) {
					$error = 'Mobile number already registered in our system!';
				}
			}
		}

		if( empty( $error ) ) {

			$rank = 0;
			$q=mysql_query("SELECT max(rank) as max FROM users");
			if( $q ) {
				$r = mysql_fetch_object($q);
				$rank = $r->max;
			}

			$password = createRandomPassword( 5 );

			$sql = "INSERT INTO users SET 
				username='".saveInsert( $username )."' 
				, password='".md5( $password )."' 
				, password_str='".saveInsert( $password )."' 
				
				, title='".saveInsert( $_REQUEST['full_name'] )."' 
				, mobile='".saveInsert( $_REQUEST['mobile'] )."' 
				, phone='".saveInsert( $_REQUEST['phone'] )."' 
				, email='".saveInsert( $_REQUEST['email'] )."' 
				, address='".saveInsert( $_REQUEST['address'] )."' 
				
				, status='active'
				, time='".time()."' 
				, rank='".saveInsert( $rank )."' 
				";
					
			$q = mysql_query ($sql);
//		echo "$sql " . mysql_error();
			if ( !$q  ) {
				$error = 'Unable to register account in our system!';
			}
			else {
				
				$stat = _sendAccountPassword($username, $password );
//					var_dump($stat);
//						if( $stat['error'] ) {
//							$error = $stat['error'];
//						}
			}
		}
		
		if( $error ) {
			$response['error'] = "$error";
		}
		else {
			$response['ok'] = true;
		}

		break;

		
	case 'login':
			
//		if( $account ) {
//			$response['error'] = $_already_login_error;
//			break;
//		}
		$error = '';
		$response = array();

		$username = $_REQUEST['mobile'];
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
			$response['error'] = "Missing Mobile Number!!";
			break;
		}
		else if( !validateMobileNumber( $username ) ) {
			$response['error'] = "Invalid Mobile Number!!";
			break;
		}

		else if( empty($password) ) {
			$response['error'] = "Missing Password!!";
			break;
		}
		else if( empty( $_REQUEST['regid'] ) ) {
			$response['error'] = "Missing registration ID, Please contact app support!!";
//			$response['error'] = print_r( $_REQUEST , true);
			break;
		}
		else {
			switch( $_REQUEST['source'] ) {
				case 'android':
				case 'ios':
					break;
				default:
					$response['error'] = "Missing source var, Please contact app support!!";
					break;
			}
		}
		if( $response['error'] ) {
			break;
		}
		
		$api_hash = md5( rand(111111, 999999) .time() ) .time();

		$sql = getUserLogin('login', $username, $password );

		$q = mysql_query ($sql);

//	$response['error'] = print_r($_REQUEST, true);
//	break;

		if ( !$q  ) {
			$response['error'] = "Username or password not valid!!!";
			break;
		}
//			$response['error'] = print_r($_REQUEST, true);
//			break;
		
		if( !mysql_num_rows($q) ) {
			$response['error'] = "Username or password is incorrect!! ";
//			$response['error'] .= $params;
			break;
		}
		
		
		$row = mysql_fetch_assoc ($q);
//	var_dump( $row );
		$row = getStudentsAndSchools( $row );
//	var_dump( $row );

		if( !$row['schools'] ) {
			// if he have a login but don't have any student or active school, we will send him error message telling him that.
			$response['error'] = "There is no active students/schools for your account!";
			break;
		}

		$galleryCategories = getsectionsCategories( 'gallery_category', " school_id IN( {$row['schoolIDs']} ) ");

		$key = md5($row['id'] . $row['username'] . $row['password'] . $api_hash);

		$q = mysql_query("REPLACE INTO users_logins SET 
			`api_hash` = '".saveInsert( $key )."' 
			, `user_id`='{$row['id']}' 
			, `source`='".saveInsert( $_REQUEST['source'] )."'
			, `regid`='".saveInsert( $_REQUEST['regid'] )."'
			, `notifications` = '1'
			, `time`='".time()."'
			, `time_update`='".time()."'
		"); // INSERT
		
		if( !$q ) {
			$response['error'] = "Login Failed!!";
			break;
		}
		
		$row['user_login_id'] = mysql_insert_id();
		$row['notifications'] = '1';

		$response = buildLoginResponse($key, $row);

		$q = mysql_query("INSERT INTO users_logins_devices SET 
			`user_id`='{$row['id']}' 
			, `source`='".saveInsert( $_REQUEST['source'] )."'
			, `regid`='".saveInsert( $_REQUEST['regid'] )."'
			, time='".time()."'
			ON DUPLICATE KEY UPDATE time_updated='".time()."'
		");
	
		break;
		
	case 'pwd':

		$error = '';
		$response = array();

		$username = $_REQUEST['mobile'];
		
		if( empty($username) ) {
			$error = "Missing Mobile Number!!";
		}
		else if( !validateMobileNumber( $username ) ) {
			$error = "Invalid Mobile Number!!";
		}

		if( empty( $error ) ) {
		
			$sql = "SELECT * FROM users WHERE username='".saveInsert( $username )."' LIMIT 1";
			
			$q = mysql_query( $sql );
			
			if( !$q ) {
				$error = 'Unable to find account in our system!';
			}
			else {
				if( !mysql_num_rows($q)) {
					$error = 'Mobile number is not registered in our system!';
				}
				else {
					$response['ok'] = true;

					$row = mysql_fetch_assoc( $q );

					 _sendAccountPassword( $row['username'], $row['password_str'] );
				}
			}

			if( $error ) {
				$response['error'] = "$error";
			}
		}
		break;
	case 'is_login':

		$response = array();

		if( $account ) {
			$response = buildLoginResponse($_REQUEST["key"], $account);
		}
		else {
			$response['ok'] = true;
			$response['loggedin'] = false;
//			$response['news_categories'] = array_values( $newsCategories );
		}
		break;
	case 'notifications':
		
		if( !$account ) {
			$error = $_should_login_error;
			break;
		}

		$_school = $account['schools'][ $_REQUEST['school_id'] ];
		if( ! $_school ) {
			$error = "Missing School!";
			break;
		}

		$response = array();
		$response['notifications'] = getPushNotifications( $account, $_school['id'] );
		
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
			$error = $_should_login_error;
			break;
		}
		
		$sql = "UPDATE users_logins SET 
				notifications='".saveInsert( $_REQUEST['notifications'] )."' 
			WHERE id='{$account['user_login_id']}'
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
		if( $account ) {
			$sql = "DELETE FROM `users_logins`
				WHERE id='{$account['user_login_id']}'
				LIMIT 1 ";
			$q = mysql_query($sql);
//		echo $sql;
		}
		$response['ok'] = true;
		break;
}

 // TODO
if( !$response['error'] && $error ) {
	$response['error'] = "$error";
}

if( !$response ) {
	$response['error'] = 'Invalid action!';
}

if( $response['error'] ) {
	$response['ok'] = false;
}
else {
	$response['ok'] = true;
}

if( $_REQUEST['debug'] ) {
	$response['debug'] = (array) $response['debug'];
	$response['debug']['request'] = $_REQUEST;
}

//makeLogFile($params, $_REQUEST, $response);

echo json_encode($response);
exit;


function buildLoginResponse($key, $account) {
	
	global $newsCategories, $documentsCategories, $galleryCategories;
	
	$response = array();
	$response['ok'] = true;
	$response['key'] = $key;
	$response['loggedin'] = true;

	$response['categories_news'] = array_values( $newsCategories );
	$response['categories_documents'] = array_values( $documentsCategories );
	$response['categories_gallery'] = array_values( $galleryCategories );

	$response['account'] = array(
		'id' => $account['id'],
//		'first_name' => $account['first_name'],
//		'last_name' => $account['last_name'],
		'full_name' => $account['title'],
		'email' => $account['email'],
		'notifications' => $account['notifications'],
		'mobile' => $account['mobile'],
		'image' => ($account['image']) ? $_SITE_PATH_ . 'uploads/users/thumb/' . $account['image'] : $previsionDefaultImage,
	);

	$response['schools'] = array();
	if( is_array( $account['schools'] )) {
		foreach( $account['schools'] as $school) {
			$response['schools'][] = array(
				'id' => $school['id'],
				'title' => $school['title'],
				'logo' => ($school['image']) ? $_SITE_PATH_ . 'uploads/schools/' . $school['image'] : $previsionDefaultImage,
			);
		}
	}

	$response['students'] = array();
	if( is_array( $account['students'] )) {
		foreach( $account['students'] as $student) {
			$response['students'][] = array(
				'id' => $student['id'],
				'first_name' => $student['first_name'],
				'last_name' => $student['last_name'],
				'full_name' => $student['full_name'],
				'photo' => ($student['image']) ? $_SITE_PATH_ . 'uploads/students/' . $student['image'] : $previsionDefaultImage,
			);
		}
	}
	
	return $response;
}

function getPushNotifications( $account, $school_id = 0 ) {

	$school_id = intval( $school_id );

	$notifications = array();
	$sql = "SELECT * 
		FROM users_logins_notification
		WHERE login_id='{$account['user_login_id']}' 
			AND school_id = '{$school_id}' 
		LIMIT 1";
	$q = mysql_query( $sql );
	if( $q && mysql_num_rows($q)) {
		$row = mysql_fetch_assoc($q);
	
		if( $row['notification_news'] > 0) {
			$notifications['news'] = $row['notification_news'];
		}

		$ids = getCategoriesNotifications( $row['notification_education_news_ids'] );
		if( $ids ) {
			$notifications['education_news'] = $ids;
		}

//		if( $row['notification_documents'] > 0) {
//			$notifications['documents'] = $row['notification_documents'];
//		}
		$ids = getCategoriesNotifications( $row['notification_documents_ids'] );
		if( $ids ) {
			$notifications['documents'] = $ids;
		}
		
		if( $row['notification_gallery'] > 0) {
			$notifications['gallery'] = $row['notification_gallery'];
		}
		if( $row['notification_videos'] > 0) {
			$notifications['videos'] = $row['notification_videos'];
		}
		if( $row['notification_agenda'] > 0) {
			$notifications['agenda'] = $row['notification_agenda'];
		}
		if( $row['notification_replies'] > 0) {
			$notifications['replies'] = $row['notification_replies'];
		}

		mysql_query( "UPDATE users_logins_notification SET 
			notification_updated=''
			, notification_news =0
			, notification_education_news =0
			, notification_education_news_ids =''
			, notification_documents =0
			, notification_documents_ids =''
			, notification_gallery =0
			, notification_videos =0
			, notification_agenda =0
			, notification_replies =0
			WHERE login_id='{$account['user_login_id']}' 
				AND school_id = '{$school_id}' 
			LIMIT 1
		" );
//		echo mysql_error();
	}
	else {
//		echo mysql_error();
	}

	return $notifications;
}

function getCategoriesNotifications( $str ) {


//	$list = array(1, 0);
//	$id = array_shift($list);
//	var_dump( $id );
//	$id = array_shift($list);
//	var_dump( $id );
//	$id = array_shift($list);
//	var_dump( $id );
//	die();

	$ids = array();
	$list = array_map('intval', explode(',', "{$str}" ));
	
	while(($id = array_shift($list)) !== NULL) {
		if( $id > 0 ) {
			if( !$ids[ $id ] ) {
				$ids[ $id ] = array(
					'cat_id' => $id,
					'count' => 0,
				);
			}
			$ids[ $id ]['count']++;
		}
	}
	
	return array_values( $ids );	
}

function saveInsert($string) {
	$string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8', false);
	$string = mysql_real_escape_string( $string );
	
	return $string;
}


function createRandomPassword( $len ) {
	
	$str1 = "abcdefghijklmnopqrstuvwxyz";
	$str2 = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$str3 = "1234567890";
	
	$land = $str1 . $str3;
	
	$max = strlen( $land ) -1;
	
	$len = intval( $len );
	if( $len < 1 ) {
		$len = 5;
	}
	
	$password = '';
	for( $i=0; $i<$len; $i++) {
		$x = rand(0, $max);
		
		$password .= $land[ $x ];
	}
	
	return $password;
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

function _sendAccountPassword( $mobile, $password, $tag = '' ) {
	$sms_sender_id = "Prevision";
	$message = "Your password for Prevision Education app is: {$password}";

	$options = array(
		'senderid' => $sms_sender_id,
		'to' => $mobile,
		'message' => $message,
	);

	if( $tag ) {
		$options['tag'] = $tag;
	}

	return _sendSMSrequest('send', $options);
}


function getsectionsCategories( $table, $where = '') {
	
	$_where = ( $where ) ? " WHERE {$where} " : '';
	
	$array = array();

	$sql = "SELECT `{$table}`.* 
		FROM `{$table}` 
		{$_where}
		ORDER BY `{$table}`.rank DESC";
	
	$q = mysql_query( $sql );
//echo mysql_error();
	if( $q && mysql_num_rows($q ) ) {
		while( $row = mysql_fetch_assoc( $q )) {
			$data = 
			$array[$row['id'] ] = array(
				'id' => $row['id'],
				'title' => $row['title'],
				'school_id' => $row['school_id'],
			);
		}
	}
	
	return $array;
}

function validateMobileNumber( $mobile ) {
	//if( preg_match("#^03([0-9]{6})$#", $mobile)) {
	//	return true;
	//}
	//if( preg_match("#^7([0-9]{7})$#", $mobile)) {
	//	return true;
//	}
//	if( preg_match("^\+[1-9]{1}[0-9]{7,11}$", $mobile)){
//		return true;
	//}
	if( is_numeric($mobile) ) {
			return true;
		}
	
	return false;
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