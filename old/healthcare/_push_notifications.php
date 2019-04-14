<?php

define('isAjaxRequest', true);
define('_push_notifications', true);


error_reporting( E_ALL ^ E_NOTICE );
require_once "_startup.php";

include '_push_notifications/gcm.php';
include '_push_notifications/apns.php';
//include '_queries.php';

echo "<pre>";

$configs = array(
	'android' => array(
//		'project_number' => '155185723272', // will be used as SENDER ID
//		'project_id' => 't3ch-pr3v1si0n',
//		'browser_key' => 'AIzaSyBHyC8aauRaI4JxTqK2MkD4N3r61HsgaO0', // Browser Key // will be used when sending requests to GCM server
//		'server_key' => 'AIzaSyBqEGWR3ybEKF_KGKnkYuZdg1Lx0PRXxkA', // Server Key // will be used when sending requests to GCM server
                'server_key' => 'AIzaSyBXU1XGQdiQM1h_kID65GNkCFsd6NCQqf8',

//		'server_key' => 'AIzaSyBe5SIEJhiXsEnUIqpBbbSY7nttJqhlKuU', // Android Key // will be used when sending requests to GCM server
//		'server_key' => 'AIzaSyA63UhroF053dcZmXHxZxufyhH_F3bv3bE', // test key for my project
	),
	'ios' => array(
//		'apns_host' => 'gateway.sandbox.push.apple.com',
//		'apns_post' => 2195,
//		'apns_cert' => 'apns-dev.pem', // local certificate file
//		'passphrase' => '123456',
		'apns_cert' => 'apns-prod.pem', // local certificate file
		'passphrase' => 'Sdl~acu7K3oX',
		'pconnect' => true,
	),
);

/*
 * https://code.google.com/apis/console
 * Create new project
 * get project id which will be used as SENDER ID
 * go to "Services" on the left panel and turn on "Google Cloud Messaging for Android".
 * go to "API Access" and note down the "API Key". This API key will be used when sending requests to GCM server
 */
// https://code.google.com/apis/console/#project:460866929976


$rand = md5( rand(1, 99999) ) . time();
$account = array(
	'id' => '--'.$rand.'--id--'.$rand.'--',
	'doctor_reseller_id' => '--'.$rand.'--doctor_reseller_id--'.$rand.'--',
);

$sync = array();
$updates = array(
	'android' => array(),
	'ios' => array(),
);


$tables = array(
	'news' => array(
		'sql' => getNewsDocumentsSql( 'news' ),
		'collapse' => 'news',
	),
	'medical' => array(
		'sql' => getNewsDocumentsSql( 'medical' ),
		'collapse' => 'medical',
	),
	'documents' => array(
		'sql' => getNewsDocumentsSql( 'documents' ),
		'collapse' => 'documents',
	),
	'questions_replies' => array(
		'sql' => "SELECT questions_replies.id as row_id, doctors_resellers.doctor_id
			FROM questions, questions_replies, doctors_resellers
			WHERE TRUE
				AND questions_replies.question_id = questions.id
				AND questions.doctor_id = doctors_resellers.doctor_id
				AND questions.reseller_id=doctors_resellers.reseller_id
		",
		'collapse' => 'replies',
	),
);

foreach($tables as $table => $data) {
	
	if( !$data['sql'] ) {
		continue;
	}

	$sql = "
		SELECT doctors_logins.id as login_id, doctors_logins.source, doctors_logins.regid, doctors_logins.notifications
		, data_updates.id
		FROM ( data_updates, doctors_logins, ( {$data['sql']} ) as data_index )
			LEFT OUTER JOIN data_sync ON(
				data_sync.id=data_updates.id 
				AND data_sync.`table`=data_updates.`table`
				AND data_sync.`login_id`=doctors_logins.`id`
			)
		WHERE data_updates.`table` = '{$table}'
			AND data_updates.id = data_index.row_id
			AND doctors_logins.doctor_id = data_index.doctor_id
			AND (
				data_sync.id IS NULL
				OR data_sync.time < data_updates.time
			)
		GROUP BY doctors_logins.id
	";

//	GROUP BY doctors_logins.source, doctors_logins.regid
//	$sql = $data['sql'];

	echo "<h1>{$table}</h1>\r\n";
        
	$q = mysql_query($sql);
	if( $q && mysql_num_rows($q)) {
		$total = mysql_num_rows($q);
		while( $row = mysql_fetch_assoc( $q )) {

			if($row['notifications']) {
				$updates[ $row['source'] ][ $row['regid'] ][ $data['collapse'] ]++;
			}

			$sql = "UPDATE `doctors_logins` SET notification_updated='1', get_notifications=get_notifications+1, `notification_{$data['collapse']}`=`notification_{$data['collapse']}`+1 WHERE id='{$row['login_id']}' LIMIT 1";
			$qqq = mysql_query($sql);

			// TODO comment this for testing
			$sync[] = array(
				'table' => $table,
				'id' => $row['id'],
				'login_id' => $row['login_id'],
			);
		}
		echo "<div>Data: {$total}</div>";
		foreach($updates as $k=>$v) {
			$total = count($v);
			echo "<div>{$k}: {$total}</div>";
		}
	}
	else if( !$q ) {
		echo "<div style='color: red;'>";
		echo "<h2>MySQL Error: </h2>";
		echo mysql_error();
		echo "</div>";
	}
	else {
		echo "<div>No Data</div>";
	}
}

if( $updates['android'] ) {

	$GCM = new GCM( $configs['android']['server_key'] );

	reset( $updates['android'] );
	while (list($deviceToken, $update) = each( $updates['android'] )) {
		
            $message = '';
            foreach( $update as $k => $v ) {
                    switch($k) {
                            case 'replies':
                                    $message .= ( $message ) ? ', ' : '';
                                    $message .= ($v>1) ? $v.' new messages' : 'a new message';
                                    break;
                            case 'documents':
                                    $message .= ( $message ) ? ', ' : '';
                                    $message .= ($v>1) ? $v.' new files' : 'a new file';
                                    break;
                            case 'medical':
                                    $message .= ( $message ) ? ', ' : '';
                                    $message .= ($v>1) ? $v.' new medical news' : 'a new medical news';
                                    break;
                            case 'news':
                                    $message .= ( $message ) ? ', ' : '';
                                    $message .= ($v>1) ? $v.' new news' : 'a new news';
                                    break;
                    }
            }

            $update['title'] = "Prevision Pro";
            $update['message'] = "You have $message";

            $result = $GCM->send_notification($deviceToken, $update);
		
	    unset( $updates['android'][ $deviceToken ] );
	    reset( $updates['android'] );
	}
}

// TODO Test
//$updates['ios'] = array();

if( $updates['ios'] ) {

	$APNS = new APNS( $configs['ios']['apns_cert'], $configs['ios']['pconnect'] );
	
	$APNS->setTestMode( true );
	$APNS->setDebug( true );
	if( $configs['ios']['apns_cert'] ) {
		$APNS->setPassphrase( $configs['ios']['passphrase'] );
	}

	reset( $updates['ios'] );
	while (list($deviceToken, $update) = each( $updates['ios'] )) {
//	    echo "$deviceToken => $update<br>\n";
//		var_dump( $deviceToken );
		echo "\r\n";
		echo "\r\n";
		echo "\r\n";
		echo "Data:\r\n";
		
		$badge = array_sum( $update );

		$update['alert'] = 'You have new updates!!';
		$update['badge'] = 1;
//		$update['badge'] = $badge;
//		$update['sound'] = 'default';
		print_r( $update );

		$result = $APNS->send_notification($deviceToken, $update);
		
		if( $APNS->error ) {
			echo "<div style='color: red;'>Error: {$APNS->error}</div>";
		}
		echo "<div>deviceToken: {$deviceToken}</div>";
		echo "Result:\r\n";
		print_r( $result );
//		var_dump( $GCM->error );
//		var_dump( $GCM->headers );

		unset( $updates['ios'][ $deviceToken ] );
	    reset( $updates['ios'] );
	}
}

if( $sync ) {
	while($update = array_shift($sync)) {
		@mysql_query("REPLACE INTO `data_sync` SET
			`table` = '".mysql_real_escape_string( $update['table'] )."'
			, `id` = '".mysql_real_escape_string( $update['id'] )."'
			, `login_id` = '".mysql_real_escape_string( $update['login_id'] )."'
			, `time` = '".time()."'
		");
	}
}
//var_dump( $updates );
/*
    $registatoin_ids = array($deviceToken);
    $message = array("product" => "shirt");
 
    $result = $GCM->send_notification($registatoin_ids, $message);
*/

function getNewsDocumentsSql( $table ) {
	
	$newsWhere = "";
	
	switch($table) {
		case 'news':
			$field = 'news';
			$newsWhere .= " AND news.app_notification='1' ";
			$newsWhere .= " AND ( news.publish_date_time <= NOW() ) "; // date(news.publish_date_time) = '0000-00-00' OR
			break;
		case 'medical':
			$field = 'medical';
			$newsWhere .= " AND medical.app_notification='1' ";
			$newsWhere .= " AND ( medical.publish_date_time <= NOW() ) "; // date(medical.publish_date_time) = '0000-00-00' OR
			break;
		case 'documents':
			$field = 'document';
			break;
		default:
			return '';
	}

	// , doctors
	// AND doctors.id = doctors_index.index_id
	$sql = "SELECT {$table}_resellers.{$field}_id as row_id, doctors_resellers.doctor_id
		FROM {$table}, {$table}_resellers, doctors_resellers, {$table}_index, doctors_index, resellers_index
		
		WHERE TRUE
			AND {$table}.id = {$table}_index.index_id
			
			{$newsWhere}
		
			AND doctors_resellers.reseller_id={$table}_resellers.reseller_id
	
			AND doctors_resellers.doctor_id=doctors_index.index_id
			AND {$table}_resellers.{$field}_id={$table}_index.index_id
		
			AND (
				( `doctors_index`.cat_id = {$table}_index.cat_id AND `resellers_index`.cat_id = {$table}_index.cat_id )
				OR {$table}_index.cat_id = '-1'
			)
		GROUP BY {$table}_resellers.{$field}_id, doctors_resellers.doctor_id
	";

	if( $table == 'news' || $table == 'medical' ) {
		$sql = "
			SELECT {$table}_doctors.{$field}_id as row_id, {$table}_doctors.doctor_id 
			FROM doctors, {$table}, {$table}_doctors 
			WHERE TRUE
				AND {$table}.id = {$table}_doctors.{$field}_id
				AND doctors.id = {$table}_doctors.doctor_id
			UNION {$sql}
		";
	}
	
	return $sql;
}