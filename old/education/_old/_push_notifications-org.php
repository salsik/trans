<?php

define('isAjaxRequest', true);
define('_push_notifications', true);


error_reporting( E_ALL ^ E_NOTICE );
require_once "_startup.php";

include '_push_notifications/gcm.php';
include '_push_notifications/apns.php';
//include '_queries.php';

echo "<pre>";

$configs = $_push_notifications_settings;

//$rand = md5( rand(1, 99999) ) . time();
//$account = array(
//	'id' => '--'.$rand.'--id--'.$rand.'--',
//	'student_school_id' => '--'.$rand.'--student_school_id--'.$rand.'--',
//);

$sync = array();
$updates = array(
	'android' => array(),
	'ios' => array(),
);

$tables = array(
	'news' => array(
		'sql' => getDataSql( 'news' ),
		'collapse' => 'news',
	),
	'education_news' => array(
		'sql' => getDataSql( 'education_news' ),
		'collapse' => 'education_news',
	),
	'documents' => array(
		'sql' => getDataSql( 'documents' ),
		'collapse' => 'documents',
	),
	'gallery' => array(
		'sql' => getDataSql( 'gallery' ),
		'collapse' => 'gallery',
	),
	'videos' => array(
		'sql' => getDataSql( 'videos' ),
		'collapse' => 'videos',
	),
	'agenda' => array(
		'sql' => "SELECT agenda.id as row_id, users.id as user_id
			FROM agenda, students, users
			
			WHERE TRUE
				AND users.username <> ''
				AND (
					users.username = students.info_mobile
					OR users.username = students.info_father_mobile
					OR users.username = students.info_mother_mobile
				)
				AND agenda.school_id = students.school_id
				AND agenda.class_id = students.class_id
			GROUP BY agenda.id, users.id
		",
		'collapse' => 'replies',
	),
	'questions_replies' => array(
		'sql' => "SELECT questions_replies.id as row_id, users.id as user_id
			FROM questions, questions_replies, students, users
			WHERE TRUE
				AND users.username <> ''
				AND (
					users.username = students.info_mobile
					OR users.username = students.info_father_mobile
					OR users.username = students.info_mother_mobile
				)
				AND questions_replies.question_id = questions.id
				AND questions.user_id = users.id
			GROUP BY questions_replies.id, users.id
		",
//				AND questions.school_id=students.school_id
		'collapse' => 'replies',
	),
);

foreach($tables as $table => $data) {

	// , users_logins.user_id
	// , data_sync.id as data_sync_id
	// count(*) as count, 
	$sql = "
		SELECT users_logins.id as login_id, users_logins.source, users_logins.regid, users_logins.notifications
		, data_updates.id, data_updates.catid
		FROM ( data_updates, users_logins, ( {$data['sql']} ) as data_index )
			LEFT OUTER JOIN data_sync ON(
				data_sync.id=data_updates.id 
				AND data_sync.`table`=data_updates.`table`
				AND data_sync.`login_id`=users_logins.`id`
			)
		WHERE data_updates.`table` = '{$table}'
			AND data_updates.id = data_index.row_id
			AND users_logins.user_id = data_index.user_id
			AND (
				data_sync.id IS NULL
				OR data_sync.time < data_updates.time
			)
	";
//	GROUP BY users_logins.source, users_logins.regid
//	$sql = $data['sql'];

	echo "<h1>{$table}</h1>\r\n";
//	echo "<div>{$sql}</div>";

	$q = mysql_query($sql);
	if( $q && mysql_num_rows($q)) {
		$total = mysql_num_rows($q);
		while( $row = mysql_fetch_assoc( $q )) {
//			var_dump( $row );
//			echo "\r\n";
//			print_r( $row );
//			echo "\r\n";
			
			if($row['notifications']) {
				$updates[ $row['source'] ][ $row['regid'] ][ $data['collapse'] ]++;
//				$updates[ $row['source'] ][ $row['regid'] ][ $data['collapse'] ] = $row['count'];
			}

			$notification_updated = " notification_updated='1' ";
			$notification_updated .= " , `notification_{$data['collapse']}`=`notification_{$data['collapse']}`+1 ";
			
			switch($data['collapse']) {
				case 'education_news':
				case 'news':
				case 'documents':
					$notification_updated .= " , `notification_{$data['collapse']}_ids`=CONCAT(`notification_{$data['collapse']}_ids`, ',{$row['catid']}') ";
			}

			$sql = "UPDATE `users_logins` SET 
				
				$notification_updated
				
				WHERE id='{$row['login_id']}' 
				LIMIT 1";
			$qqq = mysql_query($sql);
//echo "$sql ";
//echo mysql_error();
//echo "".mysql_affected_rows();
			
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
	
//	$GCM->setTimeLife( 86400 * 7 ); // week
//	$GCM->setDelay( false );
//	$GCM->setCollapse('');

	reset( $updates['android'] );
	while (list($deviceToken, $update) = each( $updates['android'] )) {
//	    echo "$deviceToken => $update<br>\n";
//		var_dump( $deviceToken );

//		echo "\r\n";
//		echo "\r\n";
//		echo "\r\n";
//		echo "Data:\r\n";
//		print_r( $update );
		
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
				case 'gallery':
					$message .= ( $message ) ? ', ' : '';
					$message .= ($v>1) ? $v.' new photos' : 'a new photo';
					break;
				case 'agenda':
					$message .= ( $message ) ? ', ' : '';
					$message .= ($v>1) ? $v.' new agenda' : 'a new agenda';
					break;
				case 'videos':
					$message .= ( $message ) ? ', ' : '';
					$message .= ($v>1) ? $v.' new videos' : 'a new video';
					break;
				case 'news':
					$message .= ( $message ) ? ', ' : '';
					$message .= ($v>1) ? $v.' new news' : 'a new news';
					break;
			}
		}

		$update['title'] = "Prevision";
		$update['message'] = "You have $message";
		
//		$update = array("message" => 'Test test');

		$result = $GCM->send_notification($deviceToken, $update);
		
//		echo "Result:\r\n";
//		print_r( $result );

//		var_dump( $GCM->error );
//		var_dump( $GCM->headers );
		
		

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

function getDataSql( $table ) {
	
	switch($table) {
		case 'news':
			$table = 'news';
			$field_id = 'news_id';
			$newsWhere = " AND news.app_notification='1' ";
			$newsWhere .= " AND ( news.publish_date_time <= NOW() ) "; // date(news.publish_date_time) = '0000-00-00' OR
			break;
		case 'education_news':
			$table = 'education_news';
			$field_id = 'news_id';
			$newsWhere = " AND education_news.app_notification='1' ";
			$newsWhere .= " AND ( education_news.publish_date_time <= NOW() ) "; // date(education_news.publish_date_time) = '0000-00-00' OR
			break;
		case 'documents':
			$table = 'documents';
			$field_id = 'document_id';
			$newsWhere = "";
			break;
		case 'gallery':
			$table = 'gallery';
			$field_id = 'gallery_id';
			$newsWhere = "";
			break;
		case 'videos':
			$table = 'videos';
			$field_id = 'video_id';
			$newsWhere = "";
			break;
	}
	
	// , students
	// AND students.id = students_index.index_id
//	$sql = "SELECT {$table}_schools.{$field_id} as row_id, students_schools.student_id
//		FROM {$table}, {$table}_schools, students_schools, (
//			SELECT index_id, CONCAT(cat_id, ':', sub_id) as concat FROM {$table}_index
//		) as {$table}_index, students_index, schools_index
//		
//		WHERE TRUE
//			AND {$table}.id = {$table}_index.index_id
//			
//			{$newsWhere}
//		
//			AND students_schools.school_id={$table}_schools.school_id
//	
//			AND students_schools.student_id=students_index.index_id
//			AND {$table}_schools.{$field_id}={$table}_index.index_id
//		
//			AND CONCAT(`students_index`.cat_id, ':', `students_index`.sub_id) = {$table}_index.concat
//			AND CONCAT(`schools_index`.cat_id, ':', `schools_index`.sub_id) = {$table}_index.concat
//		GROUP BY {$table}_schools.{$field_id}, students_schools.student_id
//	";

	$sql = "SELECT {$table}.id as row_id, users.id as user_id
		FROM {$table}, {$table}_index, students, users
		
		WHERE TRUE
			AND users.username <> ''
			AND (
				users.username = students.info_mobile
				OR users.username = students.info_father_mobile
				OR users.username = students.info_mother_mobile
			)
			AND {$table}.id = {$table}_index.index_id
			AND {$table}.school_id = students.school_id
			AND {$table}_index.class_id = students.class_id
			
			{$newsWhere}
		
		GROUP BY {$table}.id, students.id
	";

	switch ( $table ) {
		case 'education_news':
		case 'news':
		case 'documents':
//			SELECT {$table}_students.{$field_id} as row_id, {$table}_students.student_id 
			$sql = "
				SELECT {$table}_students.{$field_id} as row_id, users.id as user_id
				FROM students, {$table}, {$table}_students , users
				WHERE TRUE
					AND (
						users.username = students.info_mobile
						OR users.username = students.info_father_mobile
						OR users.username = students.info_mother_mobile
					)
					AND students.id = {$table}_students.student_id
					AND {$table}.id = {$table}_students.{$field_id}
				UNION {$sql}
			";
			
			break;
	}
	
	return $sql;
}
