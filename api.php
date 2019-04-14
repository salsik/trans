<?php

if (!( $_SERVER['REQUEST_METHOD'] == 'GET' || $_SERVER['REQUEST_METHOD'] == 'POST')) {
    exit;
}

$_SERVER['HTTP_HOST'] = 'www.giconnectonline.com';

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//makeLogFile( $_SERVER['REQUEST_URI'] );

define('isAjaxRequest', true);
define('M_API', true);

define('_login_update_time', 3600); // seconds

error_reporting(E_ALL ^ E_NOTICE);

require_once "_startup.php";

global $previsionDefaultImage;

$previsionDefaultImage = BASE_URL . 'jp_derma_logo.png';


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
if (is_array($row)) {
    foreach ($row as $k => $v) {
        $_REQUEST[$k] = $v;
    }
}

$_page = intval($_REQUEST['page']);
if ($_page < 1) {
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
if (strlen($_REQUEST["key"]) >= 32) {
    $sql = getDoctorLogin('key', $_REQUEST["key"]);

    $q = mysql_query($sql);
//var_dump( mysql_fetch_assoc ($q) );
//var_dump( mysql_error() );
    if ($q && mysql_num_rows($q)) {
        $account = mysql_fetch_assoc($q);
        $account['doctor_login_id'] = intval($account['doctor_login_id']);

        $reseller = getDataByID('resellers', $account['doctor_reseller_id'], " status='active' ");

        if (!$reseller) {
            $account = false;
        }
    }
}

if ($account && time() - $account['doctor_login_time_update'] > _login_update_time) {
    $sql = "UPDATE `doctors_logins`
		SET `time_update`='" . time() . "'
		WHERE id='{$account['doctor_login_id']}'
		LIMIT 1 ";
    $q = mysql_query($sql);
    if ($q) {
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

$_REQUEST['limit'] = intval($_REQUEST['limit']);

if ($account) {
    $Categories = getAccountCategories($account);
}

if ($action == 'site_news_details' && $_REQUEST['type'] == 'medical') {
    $action = 'medical_details';
}


$saved_action = $action ;

if($action =='notifications' || $action =='site_home_banners')
    $saved_action = 'home' ;

    

  mysql_query("INSERT INTO user_events SET 
             user_id='{$account['id']}'
            , action='$saved_action'
            , action_id='{$_REQUEST['id']}'
            , date='" . date('Y-m-d H:i:s') . "'
            , time='" . time() . "'
        ");

  



switch ($action) {
    case 'site_news_details':
        $_REQUEST['id'] = intval($_REQUEST['id']);

        $strSQL = "SELECT * FROM site_news WHERE id='{$_REQUEST['id']}' LIMIT 1";
        $q = mysql_query($strSQL);

        if (!$q) {
            $response['error'] = 'Unable to get site news details in our system!';
        } else {

            $link = '';
            if( $row['link'] != '' ){
                //if( $account['doctor_login_source'] == 'ios'){
                 //   $link = '<a style="color:#03F" onclick="window.open(\''.$row['link'].'\',\'_blank\');" >Read More...</a>';
                //}else{
                    $link = '<a style="color:#03F" href="#" onclick="window.open(\''.$row['link'].'\',\'_system\', \'location=yes\'); return false;" >Read More...</a>';
               // }
            }

            $response['ok'] = true;
            if (mysql_num_rows($q)) {
                $row = mysql_fetch_assoc($q);
                $response['data'] = array(
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'description' => $row['description']  . '<br><br>' . $link. '<br><br>',
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

        if ($_REQUEST['limit'] > 0) {
            $_limit = $_REQUEST['limit'];
        }

        $_offset = ( $_page - 1 ) * $_limit;

        if ($account && $action == 'site_news') {
            $medicalNews = true;

            if( isset($_REQUEST['word']) && $_REQUEST['word'] != ''){
                $word = $_REQUEST['word'];
                $whereStat = '`medical`.title LIKE "%'.$word.'%"';
                $strSQL = getAccessSql($account, 'medical', 0, $whereStat);
            }else{
                $strSQL = getAccessSql($account, 'medical');
            }

            //$strSQL = getAccessSql($account, 'medical');
            $strSQL = "$strSQL ORDER BY medical.rank DESC LIMIT $_offset, $_limit";

            $imageFolder = 'medical';
            $_error_msg = 'Unable to get medical news in our system!';
        } else {
            $medicalNews = false;

            if( isset($_REQUEST['word']) && $_REQUEST['word'] != ''){
                $word = $_REQUEST['word'];
                $strSQL = "SELECT * FROM site_news WHERE title LIKE '%$word%' ORDER BY site_news.rank DESC LIMIT $_offset, $_limit";
            }else{
               $strSQL = "SELECT * FROM site_news ORDER BY site_news.rank DESC LIMIT $_offset, $_limit";
            }

            $imageFolder = 'site_news';
            $_error_msg = 'Unable to get site news in our system!';
        }

        $q = mysql_query($strSQL);

        if (!$q) {
            $response['error'] = $_error_msg;
        } else {
            $response['ok'] = true;
            if (mysql_num_rows($q)) {
                while ($row = mysql_fetch_assoc($q)) {
                    $data = array(
                        'id' => $row['id'],
                        'title' => $row['title'],
                        'description' => summarize($row['description'], 10),
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
        if (!is_array($_REQUEST['types'])) {
            $_REQUEST['types'] = array();
        }

        $banners = array();
        foreach ($_REQUEST['types'] as $k => $v) {
            $banners[$v][] = $k;
        }

        foreach ($banners as $k => $v) {

            $_Banner = $_BannersZones[$k];

            if (!$_Banner) {
                continue;
            }

            $orderBy = " RAND() ";

            $_limit = count($v);

            $strSQL = getBannersByZone($k, $_Banner['private'], $reseller['id']);

            $strSQL = "$strSQL ORDER BY $orderBy LIMIT $_limit";

            $q = mysql_query($strSQL);

            if ($q && mysql_num_rows($q)) {
                while ($row = mysql_fetch_assoc($q)) {
                    mysql_query("UPDATE banners SET impressions=impressions+1 WHERE id='{$row['id']}' LIMIT 1");
                    $key = array_shift($v);

                    $maxWidth = ($_Banner['maxWidth']) ? ' class="maxWidth" ' : '';
                    $banner = '<img src="' . $_SITE_PATH_ . 'uploads/banners/thumb/' . $row['image'] . '" ' . $maxWidth . '/>';

                    if ($row['link']) {
//						$link = $row['link'];
                        $link = $_SITE_PATH_ . 'click.php?ad=' . base64_encode(json_encode(array(
                                    'id' => $row['id'],
                                    'source' => 'app',
                                    'time' => time(),
                        )));

                        //$onclick = 'if(deviceOS === \'Android\'){navigator.app.loadUrl(\'' . $link . '\',{openExternal:true});}else{window.open(\'' . $link . '\',\'_system\');}';
                        $onclick = 'window.open(\''.$link.'\',\'_system\', \'location=yes\'); return false;';

                        $banner = '<a href="' . $link . '" target="_blank" onclick="' . $onclick . '" >' . $banner . '</a>';
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

    case 'site_home_banners':

        $_Banner = $_BannersZones['home_banner'];

        $strSQL = getBannersByZone('home_banner', $_Banner['private'], $reseller['id']);
        $strSQL = "$strSQL ORDER BY RAND()";

        $q = mysql_query($strSQL);

        if (!$q) {
            $response['error'] = $_error_msg;
        } else {
            $response['ok'] = true;
            if (mysql_num_rows($q)) {
                while ($row = mysql_fetch_assoc($q)) {

                    $banner_path = $_SITE_PATH_ . 'uploads/banners/thumb/' . $row['image'];

                    $data = array(
                        'id' => $row['id'],
                        'slide' => $banner_path,
                    );

                    $response['data'][] = $data;
                }
            }
        }

        break;

    case 'is_login':
        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        $response = buildLoginResponse($_REQUEST["key"], $Categories, $account, $reseller,$_SITE_PATH_);
        break;

    case 'notifications':
        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        $data = getPushNotifications($account);

        $response = array();
        $response['notifications'] = $data['notifications'];
        $response['all'] = $data['all'];
        $response['news'] = $data['news'];
        $response['medical'] = $data['medical'];
        $response['consult'] = $data['consult'];
        $response['experts'] = $data['experts'];
        $response['products'] = $data['products'];
        
        break;

    case 'update_get_notifications':
        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        $res = getNotificationsUpdate($account, $_REQUEST["type"]);
        $response['ok'] = true;

        break;
    case 'update_consult_notifications':
        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        $res = getNotificationsConsultUpdate($account, $_REQUEST["id"]);
        $response['ok'] = true;

        break;

    case 'settings':
        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        $sql = "UPDATE doctors_logins SET 
				notifications='" . saveInsert($_REQUEST['notifications']) . "' 
			WHERE id='{$account['doctor_login_id']}'
			LIMIT 1
			";

        $q = mysql_query($sql);

        if ($q) {
            $response['ok'] = true;
        } else {
            $response['error'] = 'Unable to update your settings in our system!';
//			$response['error'] = mysql_error();
        }
        break;

    case 'logout':
        $response['ok'] = true;
        if (!$account) {
            break;
        }
        $sql = "DELETE FROM `doctors_logins`
			WHERE id='{$account['doctor_login_id']}'
			LIMIT 1 ";
        $q = mysql_query($sql);
        break;
    case 'categories':
        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        $response['ok'] = true;
        $response['data'] = $Categories;
        break;

    case 'news_details':
    case 'expert_articles_details':
    case 'medical_details':
    case 'documents_details':
	case 'product_details':
        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        switch ($action) {
            case 'news_details':
                $table = 'news';
                break;
            case 'medical_details':
                $table = 'medical';
                break;
            case 'documents_details':
                $table = 'documents';
                break;
            case 'expert_articles_details':
                $table = 'expert_articles';
                break;
            case 'product_details':
                $table = 'products';
                break;
        }

        $strSQL = getAccessSql($account, $action, $_REQUEST['id']);
        //$q = mysql_query("$strSQL LIMIT 1");
        $q = mysql_query($strSQL);

        if (!$q) {
            $response['error'] = 'Unable to get ' . $table . ' details in our system!';
        } else {
            $response['ok'] = true;
            if (mysql_num_rows($q)) {
                while ($row = mysql_fetch_assoc($q)) {

                    //$onclick = 'if(device.platform === \'Android\'){navigator.app.loadUrl(\''.$link.'\',{openExternal:true});}else{window.open(\''.$link.'\',\'_system\');}';
                    //$banner = '<a href="'.$link.'" target="_blank" onclick="'.$onclick.' return false;" >'.$banner.'</a>';
                 

                     if ($row['id'] == $_REQUEST['id']) {    
                    

                        $document = '';
                        if( $row['document'] != '' ){
                           // if( $account['doctor_login_source'] == 'ios'){
                             //   $document = '<a style="color:#03F" onclick="window.open(\''.$_SITE_PATH_ .'uploads/'.$table.'/'.$row['document'].'\',\'_blank\');" >View Full Document...</a>';
                           // }else{
                                $document = '<a style="color:#03F" href="#" onclick="window.open(\''.$_SITE_PATH_ . 'uploads/'.$table.'/'.$row['document'].'\',\'_system\', \'location=yes\'); return false;" >View Full Document</a>';
                           // }
                        }                  

                  
                        $link = '';
                        if( $row['link'] != '' ){
                           // if( $account['doctor_login_source'] == 'ios'){
                           //     $link = '<a style="color:#03F" onclick="window.open(\''.$row['link'].'\',\'_blank\');" >Read More...</a>';
                           // }else{
                                $link = '<a style="color:#03F" href="#" onclick="window.open(\''.$row['link'].'\',\'_system\', \'location=yes\'); return false;" >Read More...</a>';
                            //}
                        }

                        $response['data'] = array(
                            'id' => $row['id'],
                            'title' => $row['title'],
                            'description' => $row['description'] . '<br><br>' . $document. '<br><br>'. $link. '<br><br>',
                            'link' => $row['link'],
                            'add_by' => $row['add_by'],
                            'is_public' => $row['is_public'],
                            'date' => date('Y-m-d', $row['time']),
                            'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/' . $table . '/' . $row['image'] : $previsionDefaultImage,
                            'document' => ($row['document']) ? $_SITE_PATH_ . 'uploads/' . $table . '/' . $row['document'] : '',
                        );
                    }
                }
            }
        }
        break;

    case 'news':
    case 'expert_articles':
    case 'medical':
    case 'documents':
	case 'products' :
        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        $_limit = 100;
        $_offset = ( $_page - 1 ) * $_limit;

        if( isset($_REQUEST['word']) && $_REQUEST['word'] != ''){
            $word = $_REQUEST['word'];
            $whereStat = '`news`.title LIKE "%'.$word.'%"';
            $strSQL = getAccessSql($account, $action, 0, $whereStat);
        }else{
            $strSQL = getAccessSql($account, $action);
        }

        $q =  mysql_query("$strSQL ORDER BY {$action}.rank DESC LIMIT $_offset, $_limit");

		// thats to sort products Ascending
            if ($action=="products")
            $q =  mysql_query("$strSQL ORDER BY {$action}.rank ASC LIMIT $_offset, $_limit");


		//$response['sql']= "$strSQL ORDER BY {$action}.rank DESC LIMIT $_offset, $_limit";
		
        if (!$q) {
           // $response['error'] = 'Unable to get ' . $action . ' in our system!';
			$response['sql']= "$strSQL ORDER BY {$action}.rank DESC LIMIT $_offset, $_limit";
			$response['error'] = mysql_error();
        } else {
            $response['ok'] = true;
            if (mysql_num_rows($q)) {
                while ($row = mysql_fetch_assoc($q)) {
                    $img = '';
                    if ($row['image']) {
                        $img = $_SITE_PATH_ . 'uploads/' . $action . '/thumb/' . $row['image'];
                    } else if ($action == 'documents' && $row['document']) {
                        $img = getFileTypeIcon($row['document']);
                        $img = $_SITE_PATH_ . 'images/types/' . $img;
                    } else {
                        $img = $previsionDefaultImage;
                    }

                    $response['data'][] = array(
                        'id' => $row['id'],
                        'title' => $row['title'],
                        'description' => summarize($row['description'], 10),
                        'link' => $row['link'],
                        'add_by' => $row['add_by'],
                        'is_public' => $row['is_public'],
                        'date' => date('Y-m-d', $row['time']),
                        'image' => $img,
                        'document' => ($row['document']) ? $_SITE_PATH_ . 'uploads/' . $action . '/' . $row['document'] : '',
                    );
                }
            }
        }
        break;

    case 'polls':
        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        $_limit = 100;
        $_offset = ( $_page - 1 ) * $_limit;

        $strSQL = getPollsSql($account);
        $strSQL = "$strSQL ORDER BY polls.rank DESC LIMIT $_offset, $_limit";
        $q = mysql_query($strSQL);

//		$response['error'] = $strSQL;
        if (!$q) {
            $response['error'] = 'Unable to get polls in our system!';
            $response['error'] = mysql_error();
        } else {
            $response['ok'] = true;
            if (mysql_num_rows($q)) {
                while ($row = mysql_fetch_assoc($q)) {

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
        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        $strSQL = getPollsSql($account, $_REQUEST['poll_id']);
        $strSQL = "$strSQL LIMIT 1";
        $q = mysql_query($strSQL);

        if (!($q && mysql_num_rows($q))) {
            $response['error'] = 'Unable to get selected poll in our system!';
//			$response['error'] = "$strSQL " . mysql_error();
        } else {
            $response['ok'] = true;

            $poll = mysql_fetch_assoc($q);

            $response['poll'] = array(
                'id' => $poll['id'],
                'title' => $poll['title'],
                'option_id' => $poll['option_id'],
                'description' => $poll['description'],
                'image' => ($poll['image']) ? $_SITE_PATH_ . 'uploads/polls/thumb/' . $poll['image'] : $previsionDefaultImage,
            );
            // fix in app
            $response['poll'] = array($response['poll']);

            $q = mysql_query("SELECT * 
				FROM polls_options 
				WHERE poll_id='{$poll['id']}' 
				ORDER BY rank DESC");

            if ($q && mysql_num_rows($q)) {
                while ($row = mysql_fetch_assoc($q)) {
                    $response['data'][] = array(
                        'id' => $row['id'],
                        'poll_id' => $row['poll_id'],
                        'title' => $row['title'],
                        'selected' => ($row['id'] == $poll['option_id']) ? true : false,
                    );
                }
            }
        }
        break;

    case 'poll_option':
        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        $option_id = intval($_REQUEST['option_id']);

        $strSQL = getPollsSql($account, $_REQUEST['poll_id']);

        $q = mysql_query("$strSQL LIMIT 1");

        if (!($q && mysql_num_rows($q))) {
            $response['error'] = 'Unable to get selected poll in our system!';
//			$response['error'] = mysql_error();
            break;
        }

        $poll = mysql_fetch_assoc($q);

        if ($poll['option_id']) {
            $response['error'] = 'You already send answer for this poll.';
            break;
        }

        $q = mysql_query("SELECT * 
			FROM polls_options 
			WHERE poll_id='{$poll['id']}' 
				AND id='{$option_id}'
			LIMIT 1");

        if (!($q && mysql_num_rows($q))) {
            $response['error'] = 'Selected poll option not found!';
//			$response['error'] = mysql_error();
            break;
        }
        mysql_query("INSERT INTO polls_index SET 
			poll_id='{$poll['id']}' 
			, option_id='$option_id'
			, doctor_id='{$account['id']}'
			, date='" . date('Y-m-d') . "'
			, time='" . time() . "'
		");


        $response['ok'] = true;
        break;

    case 'competition':
        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        $strSQL = getCurrentCompetitionSql($account);
        $q = mysql_query("$strSQL LIMIT 1");

        if (!$q) {
            $response['error'] = 'Unable to get current competition in our system!';
//			$response['error'] = mysql_error();
        } else {
            $response['ok'] = true;
            if (!mysql_num_rows($q)) {
                $response['no_competition'] = true;
                $response['msg'] = 'No Competition available for this month.';
            } else {
                $competition = mysql_fetch_assoc($q);

                $_WallLimit = $competition['wall_of_fame'];
                if ($_WallLimit < 1) {
                    $_WallLimit = $_CompetitionWallLimit;
                }

                $response['competition'] = array(
                    'id' => $competition['id'],
                    'title' => $competition['title'],
                    'description' => $competition['description'],
                    'option_id' => $competition['option_id'],
                    'date' => date('Y-m-d', $competition['time']),
                    'image' => ($competition['image']) ? $_SITE_PATH_ . 'uploads/competitions/thumb/' . $competition['image'] : $previsionDefaultImage,
                );
                // fix in app
                $response['competition'] = array($response['competition']);


                $sql = getCompetitionWallSql($account, $competition);
                $q = mysql_query("$sql ORDER BY competitions_index.time ASC LIMIT $_WallLimit");
                if ($q && mysql_num_rows($q)) {
                       $doctor_index= 1;
                    while ($row = mysql_fetch_assoc($q)) {
                        $response['doctors'][] = array(
                            'id' => $row['id'],
                            'index' => $doctor_index++,
                            'full_name' => $row['full_name'],
                            'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/doctors/thumb/' . $row['image'] : $previsionDefaultImage,
                        );
                    }
                }

                $q = mysql_query("SELECT * 
					FROM competitions_options 
					WHERE competition_id='{$competition['id']}' 
					ORDER BY rank DESC");

                if ($q && mysql_num_rows($q)) {
                    while ($row = mysql_fetch_assoc($q)) {
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
        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        $competition_id = intval($_REQUEST['competition_id']);
        $option_id = intval($_REQUEST['option_id']);

        $strSQL = getCurrentCompetitionSql($account, $competition_id);
        $q = mysql_query("$strSQL LIMIT 1");

        if (!($q && mysql_num_rows($q))) {
            $response['error'] = 'Unable to get current competition in our system!';
//			$response['error'] = mysql_error();
            break;
        }

        $competition = mysql_fetch_assoc($q);

        $_WallLimit = $competition['wall_of_fame'];
        if ($_WallLimit < 1) {
            $_WallLimit = $_CompetitionWallLimit;
        }

        if ($competition['option_id']) {
            $response['error'] = 'You already send answered this quiz before.';
            break;
        }

        $q = mysql_query("SELECT * 
			FROM competitions_options 
			WHERE competition_id='{$competition['id']}' 
				AND id='$option_id'
			LIMIT 1");

        if (!($q && mysql_num_rows($q))) {
            $response['error'] = 'Selected competition option not found!';
//			$response['error'] = mysql_error();
            break;
        }
        mysql_query("INSERT INTO competitions_index SET 
			competition_id='{$competition['id']}' 
			, option_id='$option_id'
			, doctor_id='{$account['id']}'
			, date='" . date('Y-m-d') . "'
			, time='" . time() . "'
		");

        $response['ok'] = true;

        $sql = getCompetitionWallSql($account, $competition);
        $q = mysql_query("$sql ORDER BY competitions_index.time ASC LIMIT $_WallLimit");
        if ($q && mysql_num_rows($q)) {

              $doctor_index= 1;
            while ($row = mysql_fetch_assoc($q)) {
                $response['doctors'][] = array(
                    'id' => $row['id'],
                    'index' => $doctor_index++,
                    'full_name' => $row['full_name'],
                    'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/doctors/thumb/' . $row['image'] : $previsionDefaultImage,
                );
            }
        }
        break;



    case 'questions':
        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        $_limit = 100;
        $_offset = ( $_page - 1 ) * $_limit;

        $strSQL = getQuestionsSql($account);
        $q = mysql_query("$strSQL ORDER BY last_reply_time DESC LIMIT $_offset, $_limit");

        if (!$q) {
            $response['error'] = 'Unable to get questions in our system!';
//			$response['error'] = mysql_error();
        } else {
            $response['ok'] = true;
            if (mysql_num_rows($q)) {
                while ($row = mysql_fetch_assoc($q)) {

                    $response['data'][] = array(
                        'id' => $row['id'],
                        'title' => $row['title'],
                        'description' => $row['description'],
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
        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        $_limit = 100;
        $_offset = ( $_page - 1 ) * $_limit;

        $id = intval($_REQUEST['id']);

        $strSQL = getQuestionsSql($account, $id);
        $q = mysql_query("$strSQL LIMIT 1");

        if (!$q) {
            $response['error'] = 'Unable to get questions in our system!';
            $response['error'] = mysql_error();
        } else {
            $response['ok'] = true;
            if (mysql_num_rows($q)) {

                $question = mysql_fetch_assoc($q);
                if (!($_page > 1)) {
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
                $response['question'] = array($response['question']);

                $q = mysql_query("SELECT * 
					FROM questions_replies 
					WHERE question_id='{$question['id']}' 
					ORDER BY time ASC
					LIMIT $_offset, $_limit");

                if ($q && mysql_num_rows($q)) {
                    while ($row = mysql_fetch_assoc($q)) {

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
        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        if (empty($_REQUEST['question_title'])) {
            $response['error'] = 'Missing Question Title!';
        } else if (empty($_REQUEST['question_description'])) {
            $response['error'] = 'Missing Question Details!';
        }

        else {
            $q = mysql_query("SELECT max(rank) as max FROM questions");
            $r = mysql_fetch_object($q);

            $q = mysql_query("INSERT INTO questions SET 
				 reseller_id = '{$reseller['id']}'
				, doctor_id = '{$account['id']}'
				, title='" . saveInsert($_REQUEST['question_title']) . "'
				, description='" . saveInsert($_REQUEST['question_description']) . "'

				, name='" . saveInsert($_REQUEST['contact_name']) . "'
				, email='" . saveInsert($_REQUEST['contact_email']) . "'
				, phone='" . saveInsert($_REQUEST['contact_phone']) . "'
				, address='" . saveInsert($_REQUEST['contact_address']) . "'

				, status='active'
				, rank='" . ($r->max + 1) . "'
				, date='" . date('Y-m-d') . "'
				, time='" . time() . "'
			");
            if (!$q) {
                $response['error'] = 'Unable to insert question in our system!';
            } else {
				
				
				//$doc = getDataByID('doctors',$account['id']);
				
				$res1=mysql_query("SELECT * FROM `doctors` WHERE id='{$account['id']}' ");
				if( $res1 && mysql_num_rows($res1)) {
					$doc= mysql_fetch_assoc($res1);
				}
				
				
				//$tt = sendgridmail($doc['full_name'],$doc['info_email'] ,"iskandar.salama@gmail.com","Jamjoom contact request","<h1>".$_REQUEST['question_title']."</h1><br>".saveInsert($_REQUEST['question_description']));
			//	$tt = sendgridmail($doc['full_name'],$doc['info_email'] ,"mohamed.zewail@jamjoompharma.com","Jamjoom contact request","<h1>".$_REQUEST['question_title']."</h1><br>".saveInsert($_REQUEST['question_description']));

                $response['ok'] = true;
            }
        }
        break;
    case 'question_reply':
        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        $id = intval($_REQUEST['id']);
        if ($id < 1) {
            $id = intval($_REQUEST['question_id']);
        }

        $strSQL = getQuestionsSql($account, $id);
        $q = mysql_query("$strSQL LIMIT 1");

//$response['error'] = $strSQL;
        if (!($q && mysql_num_rows($q) )) {
            $response['error'] = 'Unable to get questions in our system!';
//			$response['error'] = mysql_error();
        } else if (empty($_REQUEST['question_reply'])) {
            $response['error'] = 'Missing Reply Field!';
//			$response['error'] = mysql_error();
        } else {
            $question = mysql_fetch_assoc($q);

            $q = mysql_query("SELECT max(rank) as max FROM questions_replies");
            $r = mysql_fetch_object($q);

            $q = mysql_query("INSERT INTO questions_replies SET 
				question_id = '{$question['id']}'
				, `from` = ''
				, description='" . saveInsert($_REQUEST['question_reply']) . "'
				, rank='" . ($r->max + 1) . "'
				, date='" . date('Y-m-d') . "'
				, time='" . time() . "'
			");
            if (!$q) {
                $response['error'] = 'Unable to insert question\'s reply in our system!';
//				$response['error'] = mysql_error();
            } else {
                $insert_id = mysql_insert_id();
                $response['ok'] = true;

                $response['reply'] = array(
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
        if ($account) {
            $response['error'] = $_error_already_login;
            break;
        }

        $error = '';
        $response = array();

        $username = $_REQUEST['email'];
        $password = $_REQUEST['password'];
        $regid = array();

        if (empty($username)) {
            $error = "Missing Username!!";
        } else if (empty($password)) {
            $error = "Missing Password!!";
        } else if (empty($_REQUEST['regid'])) {
            $error = "Missing registration ID, Please contact app support!!";
//			$error = print_r( $_REQUEST , true);
        } else {
            switch ($_REQUEST['source']) {
                case 'android':
                case 'ios':
                    break;
                default:
                    $error = "Missing source var, Please contact app support!!";
                    break;
            }
        }

        if (empty($error)) {
            $api_hash = md5(rand(111111, 999999) . time()) . time();

            $sql = getDoctorLogin('login', $username, $password);

            $q = mysql_query($sql);
//		echo mysql_error();
            if ($q && mysql_num_rows($q)) {
                $row = mysql_fetch_assoc($q);

                $reseller = getDataByID('resellers', $row['doctor_reseller_id'], " status='active' ");

                if ($reseller) {

                    $key = md5($row['id'] . $row['email'] . $row['password'] . $api_hash);

                    $q = mysql_query("REPLACE INTO doctors_logins SET 
						`api_hash` = '" . saveInsert($key) . "' 
						, `doctor_id`='{$row['id']}' 
						, `source`='" . saveInsert($_REQUEST['source']) . "'
						, `regid`='" . saveInsert($_REQUEST['regid']) . "'
						, `notifications` = '1'
						, `time`='" . time() . "'
						, `time_update`='" . time() . "'
					"); // INSERT
                    if ($q) {
                        $row['doctor_login_id'] = mysql_insert_id();
                        $row['notifications'] = '1';
                        $Categories = getAccountCategories($row);

                        $response = buildLoginResponse($key, $Categories, $row, $reseller,$_SITE_PATH_);
//						$response['notifications'] = getPushNotifications( $row );
                        $q = mysql_query("INSERT INTO doctors_logins_devices SET 
							`doctor_id`='{$row['id']}' 
							, `source`='" . saveInsert($_REQUEST['source']) . "'
							, `regid`='" . saveInsert($_REQUEST['regid']) . "'
							, time='" . time() . "'
							ON DUPLICATE KEY UPDATE time_updated='" . time() . "'
						");
                    }
                }
                if (!$response) {
                    $error = "Login Failed!!";
                }
            } else {
                $error = "Username or password is incorrect!!";
            }
        }

        if ($error) {
            $response['error'] = "$error";
        }
        break;

    case 'after_login':

        $error = '';
        $response = array();

        $first_name = $_REQUEST['first_name'];
        $last_name = $_REQUEST['last_name'];
        $email = $_REQUEST['email'];
        $mobile = $_REQUEST['mobile'];
        $hospital = $_REQUEST['hospital'];
        $specialization = $_REQUEST['specialization'];
		$city_selection = $_REQUEST['city_selection'];
      										  


        $ImageFileName=null ;





        $regid = array();

        if (empty($first_name)) {
            $error = "Missing first_name!!";
        } else if (empty($last_name)) {
            $error = "Missing last_name!!";
        } else if (empty($email)) {
            $error = "Missing email!!";
        }


 


          if( isset($_FILES['image']) && $_FILES['image']['size']>0 ){
                $rand = rand(10000, 99999);
                $uploadfilepath = 'uploads/doctors/';
                $fileName = $_FILES["image"]["name"];
                $ext = end((explode(".", $fileName)));
                $photoname = $rand.$fileName;
                $ext = strtolower($ext);


              if( $ext == 'png' || $ext == 'bmp' ||  $ext == 'jpg' || $ext == 'jpeg' || $ext = 'gif' ){
                  $ImageFileName = $photoname;

                  if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadfilepath.$photoname)) {
                      $phtoMsg = "File is valid, and was successfully uploaded.\n";

                      $Rimage = new SimpleImage();
                      $Rimage->load($uploadfilepath.$photoname);
                      if( $Rimage->getWidth() > 200 ) {
                          $Rimage->resizeToWidth( 200 );
                      }

                      //  $Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
                      $Rimage->save('uploads/doctors/thumb/'.$photoname);

                  } else {
                      $phtoMsg = "Possible file upload attack!\n";
                  }


                }else{
                  $phtoMsg = "Invalid image type";

                }

              if (empty($error)) {

                  $is_logged =1 ;// when we come here it should be our first login



                  $strSQL1 = "SELECT * FROM category 
                        JOIN resellers_index as resindex ON resindex.cat_id = category.id where resindex.index_id=".$reseller['id'] . 
                        " and category.title='{$city_selection}'";
       
                     $q1 = mysql_query($strSQL1);

                          
                
                
                     if (mysql_num_rows($q1)) {
                                $row = mysql_fetch_assoc($q1);
                        
                                $q2 = mysql_query("UPDATE doctors_index SET 
                                        `cat_id` = '{$row['id']}' 
                                        WHERE index_id = '{$account['id']}'
                                    "); // INSERT

                   
                   
                
                         }

               





                    $q = mysql_query("UPDATE doctors SET 
                                `first_name` = '{$first_name}' 
                                , `last_name`='{$last_name}' 
                                , `full_name`='{$first_name} {$last_name}'
                                , `info_mobile`='{$mobile}'
                                , `info_email` = '{$email}'
                                , `hospital`='{$hospital}'
                                , `specialization`='{$specialization}'
                                , `city`='{$city_selection}'
                                , `date`='{$bod}'
                                , image='{$ImageFileName}'
                                , is_logged_before='{$is_logged}'
                                WHERE id = '{$account['id']}'
                            "); // INSERT



                  $q = mysql_query($sql);

                  if (!$q) {
                      $response['error'] = $phtoMsg;
                  } else {
                      $response['ok'] = true;
                  }

                  $sql = getDoctorLogin('key', $_REQUEST["key"]);
                  $account = mysql_fetch_assoc($q);
                  $response = buildLoginResponse($_REQUEST["key"], $Categories, $account, $reseller,$_SITE_PATH_);
              }







        }

            else {



                if (empty($error)) {

                    $is_logged = 1;// when we come here it should be our first login



                 

                  $strSQL1 = "SELECT * FROM category 
                        JOIN resellers_index as resindex ON resindex.cat_id = category.id where resindex.index_id=".$reseller['id'] . 
                        " and category.title='{$city_selection}'";
       
                     $q1 = mysql_query($strSQL1);

                            
                
                
                     if (mysql_num_rows($q1)) {
                                $row = mysql_fetch_assoc($q1);
                        
                                $q2 = mysql_query("UPDATE doctors_index SET 
                                        `cat_id` = '{$row['id']}' 
                                        WHERE index_id = '{$account['id']}'
                                    "); // INSERT

                   
                   
                
                         }
 




                    $q = mysql_query("UPDATE doctors SET 
                                `first_name` = '{$first_name}' 
                                , `last_name`='{$last_name}' 
                                , `full_name`='{$first_name} {$last_name}'
                                , `info_mobile`='{$mobile}'
                                , `info_email` = '{$email}'
                                , `hospital`='{$hospital}'
                                , `specialization`='{$specialization}'
								, `city`='{$city_selection}'
                                , `date`='{$bod}'
                                , is_logged_before='{$is_logged}'
                                WHERE id = '{$account['id']}'
                            "); // INSERT


                    $q = mysql_query($sql);




                    if (!$q) {
                        $response['error'] = $phtoMsg;
                    } else {
                        $response['ok'] = true;
                    }


                    $sql = getDoctorLogin('key', $_REQUEST["key"]);
                    $account = mysql_fetch_assoc($q);
                    $response = buildLoginResponse($_REQUEST["key"], $Categories, $account, $reseller, $_SITE_PATH_);
                }


        }





        //back here



        break;
		
		
		
	  case 'forget_password':
    

		if ($account) {
            $response['error'] = $_error_already_login;
            break;
        }

        $error = '';
        $response = array();

        $username = $_REQUEST['email'];
        
        if (empty($username)) 
            $error = "Missing Username!!";
         

        if (empty($error)) {
            

            $sql = "SELECT * FROM `doctors` WHERE info_email='". $username . "'";
			
			 //$response['error'] = $sql;
			 
            $q = mysql_query($sql);
//		echo mysql_error();
            if ($q && mysql_num_rows($q)) {
                $row = mysql_fetch_assoc($q);
				
				  
					$to=$username;
					 
					$subject='Password Recovery';
					 
					$message='hello,your username is : '.$row['email'].' and password : '.$row['password_str'];
					
					

					//sendmail($to,$subject,$message,$headers=""){
					$tt = sendgridmail('Jamjoom Pharma','noreply@jamjoompharma.com',$to,$subject,$message);
					
					$response['mail'] = $tt;


				// send mail to $row->password and $row->username ... . 
				
				
				$response['ok'] = true;
                
            } else {
                $error = "This email is not included in our system !!";
            }
        }

        if ($error) {
            $response['error'] = "$error";
        }
		
		
		
        break;

	

    case 'experts':

        $userid = $account['id'];
        $_limit = 100;

        if ($_REQUEST['limit'] > 0) {
            $_limit = $_REQUEST['limit'];
        }

        $_offset = ( $_page - 1 ) * $_limit;


	// this to make each reseller view his own experts
       $strSQL = "SELECT doctors.* FROM doctors "
                . "JOIN doctors_resellers as expdoctors ON expdoctors.doctor_id = doctors.id"
                . " WHERE expdoctors.reseller_id = '{$reseller['id']}' "
                . " and expert = 1 and status = 'active' and id <> '$userid' ORDER BY doctors.time DESC LIMIT $_offset, $_limit";
        


       // $strSQL = "SELECT * FROM doctors WHERE expert = 1 and status = 'active' and id <> '$userid' ORDER BY doctors.time DESC LIMIT $_offset, $_limit";
        
        $_error_msg = 'Unable to get doctors in our system!';


        $q = mysql_query($strSQL);

        if (!$q) {
            $response['error'] = $_error_msg;
        } else {
            $response['ok'] = true;
            if (mysql_num_rows($q)) {
                while ($row = mysql_fetch_assoc($q)) {
                    
                    $notfSQL = "SELECT * FROM notifications WHERE user_id = '$userid' AND expert_id = '{$row['id']}' AND `show` = 1 AND `type` = 'consult'";
                    $n = mysql_query($notfSQL);
                    $exprtCount = 'hide';
                    if (mysql_num_rows($n)) {
                        while ($Nrow = mysql_fetch_assoc($n)) {
                             $exprtCount = 'pushConsultNotCounter';                           
                        }
                    }

                    $data = array(
                        'id' => $row['id'],
                        'name' => $row['first_name'].' '.$row['last_name'],
                        'description' => $row['brief'],
                        'notification' => $exprtCount,
                        'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/doctors/thumb/' . $row['image'] : $previsionDefaultImage,
                    );

                    $response['data'][] = $data;
                }
            }
        }
        break;


    case 'expert_details':
        $_REQUEST['id'] = intval($_REQUEST['id']);

        $strSQL = "SELECT * FROM doctors WHERE id='{$_REQUEST['id']}' LIMIT 1";
        $q = mysql_query($strSQL);

        if (!$q) {
            $response['error'] = 'Unable to get doctor details in our system!';
        } else {

            $response['ok'] = true;
            if (mysql_num_rows($q)) {
                $row = mysql_fetch_assoc($q);
                $response['data'] = array(
                    'id' => $row['id'],
                    'name' => $row['first_name'].' '.$row['last_name'],
                    'phone' => $row['info_phone'],
                    'mobile' => $row['info_mobile'],
                    'email' => $row['info_email'],
                    'description' => $row['description'],                    
                    'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/doctors/' . $row['image'] : $previsionDefaultImage,
                );
            }
        }
        break;

    case 'expert_link':
        $_REQUEST['id'] = intval($_REQUEST['id']);

        $expertid = $_REQUEST['id'];
        $userid = $account['id'];

        $response['ok'] = true;
        $response['data'] = array(
                    'expertid' => $expertid,
                    'userid' => $userid,                    
                );
        
        break;

    case 'expertinbox_link':
        $_REQUEST['userid'] = intval($_REQUEST['userid']);
        $_REQUEST['expertid'] = intval($_REQUEST['expertid']);
        $expertid = $_REQUEST['expertid'];
        $userid = $_REQUEST['userid'];

        $response['ok'] = true;
        $response['data'] = array(
                    'expertid' => $expertid,
                    'userid' => $userid,                    
                );
        
        break;

    case 'consulting_messages':
        $_REQUEST['id'] = intval($_REQUEST['id']);
        $userid = $account['id'];

        $_limit = 100;

        if ($_REQUEST['limit'] > 0) {
            $_limit = $_REQUEST['limit'];
        }

        $_offset = ( $_page - 1 ) * $_limit;

        $strSQL = "SELECT expert_consult_messages.*, expdoctors.full_name as expname, usrdoctors.full_name as usrname FROM expert_consult_messages "
                . "JOIN doctors as expdoctors ON expdoctors.id = expert_consult_messages.expert_id "
                . "JOIN doctors as usrdoctors ON usrdoctors.id = expert_consult_messages.user_id "
                . "WHERE expert_id='{$_REQUEST['id']}' AND user_id='{$userid}' ORDER BY created_at DESC LIMIT $_offset, $_limit";
        $q = mysql_query($strSQL);

        if (!$q) {
            $response['error'] = 'Unable to get messages in our system!';
        } else {

            $response['ok'] = true;
            if (mysql_num_rows($q)) {
                while ($row = mysql_fetch_assoc($q)) {
                    $name = ($row['sent_by'] == 1 ) ? $row['expname'] : $row['usrname'];
                    $data = array(
                        'id' => $row['id'],
                        'title' => $row['title'],
                        'description' => summarize($row['description'], 10),
                        'date' => $row['created_at'], 
                        'name' => $name,
                        'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/doctors/' . $row['image'] : $previsionDefaultImage,
                    );

                    $response['data'][] = $data;
                }
            }
        }
        break;

    case 'consult_message_details':
        $_REQUEST['id'] = intval($_REQUEST['id']);

        $strSQL = "SELECT * FROM expert_consult_messages WHERE id='{$_REQUEST['id']}' LIMIT 1";
        $q = mysql_query($strSQL);

        if (!$q) {
            $response['error'] = 'Unable to get messages details in our system!';
        } else {

            $response['ok'] = true;
            if (mysql_num_rows($q)) {
                $row = mysql_fetch_assoc($q);

                $image = '';
                if( $row['image'] != '' ){
                    if( $account['doctor_login_source'] == 'ios'){
                        $image = '<a style="color:#03F" onclick="window.open(\''.$_SITE_PATH_ . 'uploads/consult/' .$row['image'].'\',\'_blank\');" >View Photo...</a>';
                    }else{
                        $image = '<a style="color:#03F" href="#" onclick="window.open(\''.$_SITE_PATH_ . 'uploads/consult/' .$row['image'].'\',\'_system\', \'location=yes\'); return false;" >View Photo</a>';
                    }
                }
                $link = '';
                if( $row['file'] != '' ){
                    //if( $account['doctor_login_source'] == 'ios'){
                     //   $link = '<a style="color:#03F" onclick="window.open(\''.$_SITE_PATH_ . 'uploads/consult/' .$row['file'].'\',\'_blank\');" >View Document...</a>';
                  //  }else{
                        $link = '<a style="color:#03F" href="#" onclick="window.open(\''.$_SITE_PATH_ . 'uploads/consult/' .$row['file'].'\',\'_system\', \'location=yes\'); return false;" >View File</a>';
                    //}
                }

                $response['data'] = array(
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'date' => $row['created_at'],
                    'expertid' => $row['expert_id'],
                    'userid' => $row['user_id'],
                    'description' => $row['description'].'<br><br>'.$link.'<br><br>'.$image,
                );
            }
        }
        break;
    case 'consult_message_form':
        $_REQUEST['id'] = intval($_REQUEST['id']);

        $expertid = $_REQUEST['id'];
        $userid = $account['id'];

        $response['ok'] = true;
        $response['data'] = array(
                    'expertid' => $expertid,
                    'userid' => $userid,                    
                );
        break;

    case 'consult_send_message':




        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        if (empty($_REQUEST['message_title'])) {
            $response['error'] = 'Missing Title!';
        } else if (empty($_REQUEST['message_description'])) {
            $response['error'] = 'Missing Description!';
        } else {

            if( isset($_FILES['image']) ){
                $rand = rand(10000, 99999);
                $uploadfilepath = 'uploads/consult/';
                $fileName = $_FILES["image"]["name"];
                $ext = end((explode(".", $fileName)));
                $photoname = $rand.$fileName;
                if( $ext == 'pdf' ){
                    $PdfFileName = $photoname;
                    $ImageFileName = '';
                }else{
                    $PdfFileName = '';
                    $ImageFileName = $photoname;
                }
				
				if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadfilepath.$photoname)) {
                      $phtoMsg = "File is valid, and was successfully uploaded.\n";

                      $Rimage = new SimpleImage();
                      $Rimage->load($uploadfilepath.$photoname);
                      if( $Rimage->getWidth() > 200 ) {
                          $Rimage->resizeToWidth( 200 );
                      }

                      //  $Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
                      $Rimage->save('uploads/consult/thumb/'.$photoname);
					  
					  
                
                }
				else {
                    $phtoMsg = "Possible file upload attack!\n";
                }
            }

            if( isset($_REQUEST['userid']) ){
                $userid = $_REQUEST['userid'];
                $sendby = 1;
                $notuser = $_REQUEST['userid'];
            }else{
                $userid = $account['id'];
                $sendby = 2;
                $notuser = $_REQUEST['expertid'];
            }

            // come here


            if(isset($_REQUEST['mention_name']) ) {
                if ($_REQUEST['mention_name'] === "on")
                    $mention_name = 1;
                else
                    $mention_name = 0;
            }
            else
                $mention_name = 0;



            $q = mysql_query("INSERT INTO expert_consult_messages SET 
				expert_id = '{$_REQUEST['expertid']}'
				, user_id = '{$userid}'
				, title='" . saveInsert($_REQUEST['message_title']) . "'
				, description='" . saveInsert($_REQUEST['message_description']) . "'
                                , image='{$ImageFileName}'
                                , file='{$PdfFileName}'
                                , sent_by='{$sendby}'
                                ,mention='{$mention_name}'
                                ,status='0'
                                
			");
			
			
			// send email with info
			
			
		 

                $res1=mysql_query("SELECT * FROM `doctors` WHERE id='{$userid}'");
                if( $res1 && mysql_num_rows($res1)) {
                    $doc= mysql_fetch_assoc($res1);
                }   


            $message_text = "Hello,<br>there is a new consult request, from " . $doc['full_name']. " with the following informations:" //<br> please check it in Backend"
            ."<br>username: ".$doc['email']
            ."<br>email: ".$doc['info_email']
            ."<br>city: ".$doc['city']
            ."<br>hospital: ".$doc['hospital']
            ."<br>specialization: ".$doc['specialization']
            ."<br>please check it from Backend ";
            



			//sendgridmail('Jamjoom Pharma','noreply@jamjoompharma.com',"iskandar.salama@gmail.com","Consult request",$message_text." <br><H1>".$_REQUEST['message_title']."</H1><br>".$_REQUEST['message_description']);
			//sendgridmail('Jamjoom Pharma','noreply@jamjoompharma.com',"mohamed.zewail@jamjoompharma.com","Consult request",$message_text." <br><H1>".$_REQUEST['message_title']."</H1><br>".$_REQUEST['message_description']);
            $message_id = mysql_insert_id();
            $sql = "INSERT INTO `notifications` SET
                                            `user_id`='" .$notuser. "',
                                            `type` = 'consult',
                                            `news_id`='" . sqlencode(trime($message_id)) . "',
                                            `text`='New consulting request',
                                            `expert_id`='{$account['id']}'
                                            ";
            $qq = mysql_query($sql);
            if (!$q) {
                $response['error'] = 'Unable to insert question in our system!';
            } else {
                $response['ok'] = true;
            }
            include('push_notifications.php');
        }
        break;

    case 'inbox_link':

        if( $account['expert'] == 1 ){
            $userid = $account['id'];
            $response['ok'] = true;
            $response['data'] = array(
                        'userid' => $userid,                    
                    );
        }        
        break;

	  case 'consult_inbox':					 
     $userid = $account['id'];
        $_limit = 100;

        if ($_REQUEST['limit'] > 0) {
            $_limit = $_REQUEST['limit'];
        }

        $_offset = ( $_page - 1 ) * $_limit;

        $strSQL = "SELECT expert_consult_messages.*, doctors.full_name, doctors.image as docimage FROM expert_consult_messages JOIN doctors ON doctors.id = expert_consult_messages.user_id WHERE expert_consult_messages.status ='Approved' ORDER BY expert_consult_messages.created_at DESC LIMIT $_offset, 
				$_limit";
		
		 
                //. "WHERE user_id  = '{$userid}' GROUP BY expert_consult_messages.user_id ORDER BY expert_consult_messages.created_at DESC LIMIT $_offset, 
		 
                
				
		$response['sql']= $strSQL;
	
				
        $_error_msg = 'Unable to get messages in our system!';       

        $q = mysql_query($strSQL);

        if (!$q) {
            $response['error'] = $_error_msg;
        } else {
            $response['ok'] = true;
            if (mysql_num_rows($q)) {
                while ($row = mysql_fetch_assoc($q)) {
                    
                    $notfSQL = "SELECT * FROM notifications WHERE user_id = '{$account['id']}' AND expert_id = '{$row['user_id']}' AND `show` = 1 AND `type` = 'consult'";
					
					$response['sql2']= $notfSQL;
                    $n = mysql_query($notfSQL);
                    $exprtCount = 'hide';
                    if (mysql_num_rows($n)) {
                        while ($Nrow = mysql_fetch_assoc($n)) {
                             $exprtCount = 'pushConsultNotCounter';                           
                        }
                    }
					
                    
                    $data = array(
                        'id' => $row['id'],
                       // 'docname' => $row['full_name'],
						'docname' => ($row['mention']==1) ?  $row['full_name'] : "Unknown",
                        'expertid' => $row['expert_id'],
                        'userid' => ($row['mention']==1) ?  $row['user_id'] : null,
					//	'userid' => $row['user_id'],
                        'title' => $row['title'],
                        'description' => $row['description'],
                        'docimage' => ($row['image']) ? $_SITE_PATH_ . 'uploads/consult/thumb/' . $row['image'] : $previsionDefaultImage,
                        'date' => $row['created_at'],
                        'notification'=>$exprtCount
                    );

                    $response['data'][] = $data;
                }
            }
        }
        break;        
        
    case 'consult_inbox_details':
        $_REQUEST['userid'] = intval($_REQUEST['userid']);
        $_REQUEST['expertid'] = intval($_REQUEST['expertid']);

        $strSQL = "SELECT * FROM expert_consult_messages WHERE id='{$_REQUEST['id']}' LIMIT 1";
        $q = mysql_query($strSQL);

        if (!$q) {
            $response['error'] = 'Unable to get messages details in our system!';
        } else {
                        
            $response['ok'] = true;
            if (mysql_num_rows($q)) {
                $row = mysql_fetch_assoc($q);
                $response['data'] = array(
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'date' => $row['created_at'],
                    'expertid' => $row['expert_id'],
                    'description' => $row['description'],                    
                    'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/consult/' . $row['image'] : $previsionDefaultImage,
                );
            }
        }
        break;
    case 'consult_inbox_messages':
        $_REQUEST['userid'] = intval($_REQUEST['userid']);
        $_REQUEST['expertid'] = intval($_REQUEST['expertid']);
        $_REQUEST['articleid'] = intval($_REQUEST['articleid']);

        $_limit = 100;

        if ($_REQUEST['limit'] > 0) {
            $_limit = $_REQUEST['limit'];
        }

        $_offset = ( $_page - 1 ) * $_limit;

        /* $strSQL = "SELECT expert_consult_messages.*, expdoctors.full_name as expname, usrdoctors.full_name as usrname FROM expert_consult_messages "
                . "JOIN doctors as expdoctors ON expdoctors.id = expert_consult_messages.expert_id "
                . "JOIN doctors as usrdoctors ON usrdoctors.id = expert_consult_messages.user_id "
                . "WHERE expert_id='{$_REQUEST['expertid']}' AND user_id='{$_REQUEST['userid']}' ORDER BY created_at DESC LIMIT $_offset, $_limit";
        */
		
		$Question = getDataByID('expert_consult_messages', $_GET['question_id'], " TRUE $limitationQuestion");
		
		
		$strSQL = "SELECT consult_replies.*, expdoctors.full_name as expname, usrdoctors.full_name as usrname FROM expert_consult_messages "
				. "JOIN consult_replies  ON consult_replies.question_id = expert_consult_messages.id "
			    . "JOIN doctors as expdoctors ON expdoctors.id = consult_replies.doctor_id "
                . "JOIN doctors as usrdoctors ON usrdoctors.id = expert_consult_messages.user_id "
                . "WHERE expert_consult_messages.id='{$_REQUEST['articleid']}' ORDER BY created_at DESC LIMIT $_offset, $_limit";
                //. "WHERE expert_idexpert_id='{$_REQUEST['expertid']}' AND user_id='{$_REQUEST['userid']}' ORDER BY created_at DESC LIMIT $_offset, $_limit";
       
	   
	    $response['sql'] = $strSQL;
		
		$q = mysql_query($strSQL);
        
        if (!$q) {
            $response['error'] = 'Unable to get messages in our system!';
        } else {
                        
            $response['ok'] = true;
            if (mysql_num_rows($q)) {
                while ($row = mysql_fetch_assoc($q)) {
                    $name = ($row['mention_expert'] ) ? $row['expname'] : "An Expert";
                    $data = array(
                        'id' => $row['id'],
                        'title' => $row['title'],
                        'description' => summarize($row['description'], 10),
                        'date' => $row['created_at'], 
                        'name' => $name,
                        'image' => ($row['image']) ? $_SITE_PATH_ . 'uploads/doctors/' . $row['image'] : $previsionDefaultImage,
                    );

                    $response['data'][] = $data;
                }
            }            
        }
        break;
        
    case 'consult_inbox_message_details':
        $_REQUEST['id'] = intval($_REQUEST['id']);

        $strSQL = "SELECT * FROM consult_replies WHERE id='{$_REQUEST['id']}' LIMIT 1";
        $q = mysql_query($strSQL);

        if (!$q) {
            $response['error'] = 'Unable to get messages details in our system!';
        } else {
                        
            $response['ok'] = true;
            if (mysql_num_rows($q)) {
                $row = mysql_fetch_assoc($q);
                
                $image = '';
                if( $row['image'] != '' ){
                    if( $account['doctor_login_source'] == 'ios'){
                        $image = '<a style="color:#03F" onclick="window.open(\''.$_SITE_PATH_ . 'uploads/consult/' .$row['image'].'\',\'_blank\');" >View Photo...</a>';
                    }else{
                        $image = '<a style="color:#03F" href="#" onclick="window.open(\''.$_SITE_PATH_ . 'uploads/consult/' .$row['image'].'\',\'_system\', \'location=yes\'); return false;" >View Photo</a>';
                    }
                } 
                $link = '';
                if( $row['file'] != '' ){
                   // if( $account['doctor_login_source'] == 'ios'){
                       // $link = '<a style="color:#03F" onclick="window.open(\''.$_SITE_PATH_ . 'uploads/consult/' .$row['file'].'\',\'_blank\');" >View Document...</a>';
                   // }else{
                        $link = '<a style="color:#03F" href="#" onclick="window.open(\''.$_SITE_PATH_ . 'uploads/consult/' .$row['file'].'\',\'_system\', \'location=yes\'); return false;" >View File</a>';
                    //}
                }                                 
                
                $response['data'] = array(
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'date' => $row['created_at'],
                    'expertid' => $row['expert_id'],
                    'userid' => $row['user_id'],
                    'description' => $row['description'].'<br><br>'.$link.'<br><br>'.$image,
                );
            }
        }
        break;
        
    case 'change_password':
        if (!$account) {
            $response['error'] = $_error_must_login;
            break;
        }

        if (empty($_REQUEST['profile_email'])) {
            $response['error'] = 'Missing Email!';
        } else if (empty($_REQUEST['profile_password'])) {
            $response['error'] = 'Missing password!';
        }
        else {

            $q = mysql_query("UPDATE doctors SET 
				email = '" . saveInsert($_REQUEST['profile_email']) . "'
				, password = '" . md5(saveInsert($_REQUEST['profile_password'])) . "'
				, password_str='" . saveInsert($_REQUEST['profile_password']) . "'				
				 WHERE id='{$account['id']}'
			");
            if (!$q) {
                $response['error'] = 'Unable to update profile!';
            } else {
                $response['ok'] = true;
                $response['success'] = 'Your profile updated successfully';
            }
        }
        break;

    case 'contact_manager_info':


        $userid = $account['id'];

        $resSQL = "SELECT * FROM doctors_resellers WHERE doctor_id='$userid' LIMIT 1";
        $resQ = mysql_query($resSQL);
        if (!$resQ) {
            $response['error'] = 'Unable to get reseller information!';
        } else {

            if (mysql_num_rows($resQ)) {
                $row = mysql_fetch_assoc($resQ);
                $reseller_id = $row['reseller_id'];
            }
        }



        $strSQL = "SELECT * FROM resellers WHERE id='$reseller_id' LIMIT 1";
        $q = mysql_query($strSQL);

        if (!$q) {
            $response['error'] = 'Unable to get contact manager informations!';
        } else {

            $response['ok'] = true;
            if (mysql_num_rows($q)) {
                $row = mysql_fetch_assoc($q);




                $response['data'] = array(
                    'id' => $row['id'],
                    'name' => $row['contact_full_name'],
                    'phone' => $row['contact_phone'],
                    'mobile' => $row['contact_mobile'],
                    'email' => $row['contact_email'],
                    'address' => $row['contact_address'],
                    'image' => ($row['contact_mngr_img']) ? $_SITE_PATH_ . 'uploads/doctors/thumb/' . $row['contact_mngr_img'] : $previsionDefaultImage,
                    // 'image' => $previsionDefaultImage,
                );
            }
        }

        break;

          case 'get_countries':

 
        $strSQL = "SELECT * FROM regions";
        $q = mysql_query($strSQL);

        if (!$q) {
            $response['error'] = 'Unable to get country informations!';
        } else {

        
     
                
            $response['ok'] = true;
            if (mysql_num_rows($q)) {
                
                while ($row = mysql_fetch_assoc($q)) {
                




                $data = array(
                    'display' => $row['title'],
                    'value' => $row['title'],
                    );
                    
                $response['data'][] = $data;
            
            }
            
            }
        }

        break;



		  case 'get_cities':

 
        $strSQL = "SELECT * FROM category 
                        JOIN resellers_index as resindex ON resindex.cat_id = category.id where resindex.index_id=".$reseller['id'];
       
        $q = mysql_query($strSQL);

        if (!$q) {
            $response['error'] = 'Unable to get cities informations!';
        } else {

		
	 
				
            $response['ok'] = true;
            if (mysql_num_rows($q)) {
				
				while ($row = mysql_fetch_assoc($q)) {
                




                $data = array(
                    'display' => $row['title'],
                    'value' => $row['title'],
                    );
					
				$response['data'][] = $data;
			
			}
			
			}
        }

        break;


}
/*
  if( !$account ) {
  $response['error'] = $_error_must_login;
  break;
  }
 */
if (!$response) {
    if ($account) {

        switch ($action) {
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

if (!$response) {
    $response['error'] = 'Invalid action!';
}

if ($_REQUEST['debug']) {
    $response['debug'] = (array) $response['debug'];
    $response['debug']['request'] = $_REQUEST;
}

//var_dump( $response ); exit;

echo json_encode($response);
exit;

function buildLoginResponse($key, $Categories, $account, $reseller,$_SITE_PATH_) {

    global $previsionDefaultImage;

    $response['ok'] = true;
    $response['key'] = $key;
    $response['categories'] = $Categories;
    
    $profileForm = 0;
    if( $account['is_logged_before'] == 0 ){
         $account['is_logged_before'] = 1;
         $profileForm = 1;

        
    }

    $response['account'] = array(
        'id' => $account['id'],
        'first_name' => $account['first_name'],
        'last_name' => $account['last_name'],
        'full_name' => $account['full_name'],
        'email' => $account['email'],
        'profile_form' => $profileForm,
        'notifications' => $account['notifications'],
        'image' => ($account['image']) ? $_SITE_PATH_ . 'uploads/doctors/thumb/' . $account['image'] : $previsionDefaultImage,
    );

    $response['reseller'] = array(
        'id' => $reseller['id'],
        'title' => $reseller['title'],
        'logo' => ($reseller['image']) ? $_SITE_PATH_ . 'uploads/resellers/' . $reseller['image'] : $previsionDefaultImage,
        'about' => htmlspecialchars_decode($reseller['about']),
        'products' => htmlspecialchars_decode($reseller['products']),
    );

    return $response;
}

function getPushNotifications($account) {
    
    $strSQL = "SELECT *  FROM `notifications` WHERE `notifications`.show = 1 AND user_id ='".$account['id']."' group by user_id,type,news_id ORDER BY id DESC";
    $q = mysql_query($strSQL);

    $notifications = array();
    $allCount = 0;
    $newsCount = 0;
    $medicalCount = 0;
    $consultCount = 0;
    $expertsCount =0 ;
	$productsCount= 0 ;
	
	
	

    if (mysql_num_rows($q)) {
        while ($row = mysql_fetch_assoc($q)) {
			
			if ($row['type']=='news')
				$href= 'reseller-news-details';
			else if ($row['type']=='medical')
				$href= 'site-news-details';
			else if ($row['type']=='products')
				$href= 'product-news-details';
			else if ($row['type']=='expert_articles')
				$href= 'expert-articles-details';
			else if ($row['type']=='consult')
				$href= 'consult_inbox_message_details';
			
			
			
       
                $notifications[] = array(
                    'id' => $row['news_id'],
                    'message' => $row['text'],
                    'type' => $row['type'],
                    'href' => $href
                );
				
            

            $allCount++;
            if($row['type'] == 'news'){
                $newsCount++;
            }elseif($row['type'] == 'medical'){
                $medicalCount++;
              }elseif($row['type'] == 'expert_articles'){
                $expertsCount++;
            }
			elseif($row['type'] == 'products'){
                $productsCount++;
			}
			else{
                $consultCount++;
            }
        }
    }else{
		
			 
			
        $strSQL2 = "SELECT * FROM `notifications` WHERE user_id ='".$account['id']."' group by user_id,type,news_id ORDER BY id DESC LIMIT 10" ;
        $q2 = mysql_query($strSQL2);
        if (mysql_num_rows($q2)) {
            while ($row = mysql_fetch_assoc($q2)) {
				
				
				if ($row['type']=='news')
					$href= 'reseller-news-details';
				else if ($row['type']=='medical')
					$href= 'site-news-details';
				else if ($row['type']=='products')
					$href= 'product-news-details';
				else if ($row['type']=='expert_articles')
					$href= 'expert-articles-details';
				else if ($row['type']=='consult')
					$href= 'consult_inbox_message_details';
				
				
                $notifications[] = array(
                    'id' => $row['news_id'],
                    'message' => $row['text'],
                    'type' => $row['type'],
                    'href' => $href
                );
            }
        }
    }
    
    return array('notifications'=>$notifications, 'all'=>$allCount, 'news'=>$newsCount, 'medical'=>$medicalCount, 'consult'=>$consultCount,'experts'=>$expertsCount,'products'=>$productsCount);
    
//    $notifications = array();
//    $get_notifications = 0;
//    $tables = array(
//        'news' => array('field' => 'notification_news', 'ids' => array()),
//        'medical' => array('field' => 'notification_medical', 'ids' => array()),
//        'documents' => array('field' => 'notification_documents', 'ids' => array()),
//        'questions_replies' => array('field' => 'notification_replies', 'ids' => array()),
//    );
//    $sql = "SELECT doctors_logins.*, data_sync.id AS item_id, data_sync.table FROM doctors_logins "
//            . "JOIN data_sync ON doctors_logins.id = data_sync.login_id "
//            . "WHERE doctors_logins.doctor_id={$account['id']} AND notification_updated = '1'";
//    $q = mysql_query($sql);
//    if ($q && mysql_num_rows($q)) {
//        while ($row = mysql_fetch_assoc($q)) {
//            foreach ($tables as $name => $data) {
//                if ($row[$data['field']] > 0 && $row['table'] == $name) {
//                    if (!in_array($row['item_id'], $tables[$name]['ids'])) {
//                        $tables[$name]['ids'][] = $row['item_id'];
//                        $get_notifications = $row['get_notifications'];
//                    }
//                }
//            }
//        }
//
//        foreach ($tables as $name => $data) {
//            if ($data['ids']) {
//                $sql = 'SELECT * FROM ' . $name . ' WHERE id IN(' . implode(',', $data['ids']) . ')';
//                $q = mysql_query($sql);
//                if ($q && mysql_num_rows($q)) {
//                    while ($row = mysql_fetch_assoc($q)) {
//                        switch ($name) {
//                            case 'news':
//                                $notifications[] = array(
//                                    'id' => $row['id'],
//                                    'message' => $row['title'],
//                                    'time' => $row['time'],
//                                    'type' => 'news',
//                                    'href' => 'reseller-news-details'
//                                );
//                                break;
//                            case 'medical':
//                                $notifications[] = array(
//                                    'id' => $row['id'],
//                                    'message' => $row['title'],
//                                    'time' => $row['time'],
//                                    'type' => 'medical',
//                                    'href' => 'site-news-details'
//                                );
//                                break;
//                            case 'documents':
//                                $notifications[] = array(
//                                    'id' => $row['id'],
//                                    'message' => $row['title'],
//                                    'time' => $row['time'],
//                                    'type' => 'documents',
//                                    'href' => 'reseller-files'
//                                );
//                                break;
//                            case 'replies':
//                                $notifications[] = array(
//                                    'id' => $row['id'],
//                                    'message' => 'a replies',
//                                    'time' => $row['time'],
//                                    'type' => 'replies'
//                                );
//                                break;
//                        }
//                    }
//                }
//            }
//        }
//    }
//
//    usort($notifications, function($notification_1, $notification_2) {
//        if ($notification_1['time'] == $notification_2['time']) {
//            return 0;
//        }
//        return ($notification_1['time'] < $notification_2['time']) ? -1 : 1;
//    });
//
////    mysql_query("UPDATE doctors_logins SET
////			notification_updated=''
////			, notification_news =0
////			, notification_medical =0
////			, notification_documents =0
////			, notification_replies =0
////		WHERE id='{$account['doctor_login_id']}' LIMIT 1");
//
//    return array('get_notifications'=>$get_notifications, 'notifications'=>$notifications);
}

function getNotificationsUpdate($account, $type) {
    
    if( $type == 'all' ){
        $sql = "UPDATE `notifications` SET `show` = 0 WHERE `user_id` = {$account['id']}";
    }elseif( $type == 'news' ){
        $sql = "UPDATE `notifications` SET `show` = 0 WHERE type='news' AND `user_id` = {$account['id']}";
    }elseif( $type == 'medical' ){
        $sql = "UPDATE `notifications` SET `show` = 0 WHERE type='medical' AND `user_id` = {$account['id']}";
    }elseif( $type == 'consult' ){
        $sql = "UPDATE `notifications` SET `show` = 0 WHERE type='consult' AND `user_id` = {$account['id']}";
    }
     elseif( $type == 'expert_articles' ){
        $sql = "UPDATE `notifications` SET `show` = 0 WHERE type='expert_articles' AND `user_id` = {$account['id']}";
    }
	  elseif( $type == 'products' ){
        $sql = "UPDATE `notifications` SET `show` = 0 WHERE type='products' AND `user_id` = {$account['id']}";
    }
	


    $qqq = mysql_query($sql);
    return 'ok';
}


function getNotificationsConsultUpdate($account, $id) {
    
    $sql = "UPDATE `notifications` SET `show` = 0 WHERE `user_id` = {$account['id']} AND expert_id='{$id}'";    
    $qqq = mysql_query($sql);
    return 'ok';
}

function saveInsert($string) {
    $string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8', false);
    $string = mysql_real_escape_string($string);

    return $string;
}

function makeLogFile($text = '') {

    $fp = @fopen('api.txt', 'a');
    if ($fp) {
        $args = func_get_args();
        foreach ($args as $arg) {
            if (is_array($arg)) {
                fwrite($fp, json_encode($arg) . "\r\n");
            } else {
                fwrite($fp, "{$arg}\r\n");
            }
        }
        @fwrite($fp, "==========================\r\n");
        @fclose($fp);
    }
}
