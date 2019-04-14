<?php

define('isAjaxRequest', true);
define('_push_notifications', true);

error_reporting(E_ALL ^ E_NOTICE);
require_once "_startup.php";

include '_push_notifications/gcm.php';
include '_push_notifications/apns.php';
//include '_queries.php';

echo "<pre>";

$configs = $_push_notifications_settings;

$sync = array();
$updates = array();

$testingCalculating = false;
$saveNotifications = true;
$allowPushNotification = true;
$allowPushNotificationAndroid = true;
$allowPushNotificationIOS = true;

/*
 * getDataSql() will give us a query that returns row_id and user_id (just allowed ids to the user).
 * */
$tables = array(
    'news' => array(
        'sql' => getDataSql('news'),
        'collapse' => 'news',
    ),
    'education_news' => array(
        'sql' => getDataSql('education_news', 'news_cat_id'),
        'collapse' => 'education_news',
    ),
    'documents' => array(
        'sql' => getDataSql('documents', 'document_cat_id'),
        'collapse' => 'documents',
    ),
    'gallery' => array(
        'sql' => getDataSql('gallery', 'gallery_cat_id'),
        'collapse' => 'gallery',
    ),
    'videos' => array(
        'sql' => getDataSql('videos'),
        'collapse' => 'videos',
    ),
    'agenda' => array(
        'sql' => "SELECT agenda.id as row_id, users.id as user_id, students.school_id, agenda.app_notification
			FROM agenda, students, users
			
			WHERE TRUE
				AND agenda.pushed=''
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
        'collapse' => 'agenda',
    ),
    'questions_replies' => array(
        'sql' => "SELECT questions_replies.id as row_id, users.id as user_id, students.school_id
			FROM questions, questions_replies, students, users
			WHERE TRUE
				AND questions_replies.pushed=''
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
        'collapse' => 'replies',
    ),
);

foreach ($tables as $table => $data) {

    $sql = $data['sql'];
    echo "<h1>{$table}</h1>\r\n<pre>";

    $q = mysql_query($sql);
    if ($q && mysql_num_rows($q)) {
        $total = mysql_num_rows($q);

        echo "<div>Data: {$total}</div>";

        while ($row = mysql_fetch_assoc($q)) {

            $collapse = $data['collapse'];

            if (!$updates[$row['user_id']]) {
                $updates[$row['user_id']] = array();
            }
            if (!$updates[$row['user_id']][$collapse]) {
                $updates[$row['user_id']][$collapse] = array();
            }
            if (!$updates[$row['user_id']][$collapse][$row['school_id']]) {
                $updates[$row['user_id']][$collapse][$row['school_id']] = array();
            }

            $updates[$row['user_id']][$collapse][$row['school_id']]['value'] ++;

            switch ($collapse) {
                case 'gallery':
                case 'education_news':
//				case 'news':
                case 'documents':
                    if ($row['catid']) {
                        $updates[$row['user_id']][$collapse][$row['school_id']]['catid'] .= ',' . $row['catid'];
                    }
            }

            if (isset($row['app_notification']) && !$row['app_notification']) {
                $updates[$row['user_id']][$collapse][$row['school_id']]['less'] ++;
            }

            if (!$testingCalculating) {
                @mysql_query("UPDATE `" . mysql_real_escape_string($table) . "` SET pushed='1' ");
            }
        }
    } else if (!$q) {
        echo "<div style='color: red;'>";
        echo "<h2>MySQL Error: </h2>";
        echo mysql_error();
        echo "</div>";
    } else {
        echo "<div>No Data</div>";
    }
}

//print_r($updates);

$GCM = new GCM($configs['android']['server_key']);

$APNS = new APNS($configs['ios']['apns_cert'], $configs['ios']['pconnect']);
$APNS->setTestMode(true);
$APNS->setDebug(true);
if ($configs['ios']['apns_cert']) {
    $APNS->setPassphrase($configs['ios']['passphrase']);
}

echo "<h2>Push Notifications</h2>";
foreach ($updates as $user_id => $collapses) {

    $sql = "SELECT * FROM users_logins WHERE user_id = '{$user_id}' ";

    $q = mysql_query($sql);

    if (!$q) {
        echo "<div style='color: red;'>";
        echo "MySQL Error: ";
        echo mysql_error();
        echo "</div>";
    } else if (!mysql_num_rows($q)) {
        echo "<div>No Users</div>";
    } else {
        while ($row = mysql_fetch_assoc($q)) {
            $messageValues = array();
            $notifications = array();
            foreach ($collapses as $collapse => $schools) {
                foreach ($schools as $school_id => $data) {
                    $notifications[$school_id]['update'] .= " , `notification_{$collapse}`=`notification_{$collapse}`+{$data['value']} ";
                    $notifications[$school_id]['insert'] .= " , `notification_{$collapse}`='{$data['value']}' ";

                    if ($data['catid']) {
                        $notifications[$school_id]['update'] .= " , `notification_{$collapse}_ids`=CONCAT(`notification_{$collapse}_ids`, '{$data['catid']}') ";
                        $notifications[$school_id]['insert'] .= " , `notification_{$collapse}_ids`='{$data['catid']}' ";
                    }

                    $v = (int) $data['value'];
                    $v -= (int) $data['less'];
                    if ($v > 0) {
                        $messageValues[$collapse] += $v;
                    }
                }
            }

            foreach ($notifications as $school_id => $notification) {

                $sql = "INSERT INTO `users_logins_notification` SET 
					notification_updated='1' 
					, login_id='{$row['id']}'
					, school_id='{$school_id}'
					{$notification['insert']}
					ON DUPLICATE KEY UPDATE notification_updated='1' {$notification['update']}";
                if ($saveNotifications) {
                    $qqq = mysql_query($sql);
                }
            }

            $message = array();
            foreach ($messageValues as $collapse => $v) {
                switch ($collapse) {
                    case 'documents':
                        $message[] = ($v > 1) ? $v . ' new files' : 'a new file';
                        break;
                    case 'agenda':
                        $message[] = ($v > 1) ? $v . ' new agenda' : 'a new agenda';
                        break;
                    case 'education_news':
                        $message[] = ($v > 1) ? $v . ' new education news' : 'a new education news';
                        break;
                    case 'news':
                        $message[] = ($v > 1) ? $v . ' new news' : 'a new news';
                        break;
                }
            }
            $message = implode(', ', $message);

            $push = $allowPushNotification;
            $push = (!$message) ? false : $push;
            $push = (!$row['notifications']) ? false : $push;

            if ($push) {
                if ($allowPushNotificationAndroid && $row['source'] == 'android') {

                    if ($message) {

                        echo "<div>{$user_id}: {$message}</div>";

                        $push = array();
                        $push['title'] = $push_notifications_title;
                        $push['message'] = "You have $message";

                        $result = $GCM->send_notification($row['regid'], $push);

                        echo "<div>Result:\r\n";
                        print_r($result);
                        echo "</div>\r\n";

                    }
                }
                if ($allowPushNotificationIOS && $row['source'] == 'ios') {

                    $badge = 0;
                    foreach ($collapses as $collapse => $data) {
                        $badge += $data['value'];
                    }

                    $push = array();
                    $push['alert'] = 'You have new updates!!';
                    $push['badge'] = 1;

                    $result = $APNS->send_notification($row['regid'], $push);

                    if ($APNS->error) {
                        echo "<div style='color: red;'>Error: {$APNS->error}</div>";
                    }
                }
            }
        }
    }
}

function getDataSql($table, $catid = '') {

    $allSchools = "";
    $newsWhere = "";
    $appNotification = "";

    if ($catid) {
        $catid = " , `{$table}`.`{$catid}` as catid ";
    }

    switch ($table) {
        case 'news':
            $table = 'news';
            $field_id = 'news_id';
            $appNotification = ", {$table}.app_notification ";
            $newsWhere .= " AND news.publish_date_time <= NOW() "; // date(news.publish_date_time) = '0000-00-00' OR
            break;
        case 'education_news':
            $table = 'education_news';
            $field_id = 'news_id';
            $appNotification = ", {$table}.app_notification ";
            $newsWhere .= " AND education_news.publish_date_time <= NOW() "; // date(education_news.publish_date_time) = '0000-00-00' OR
            $allSchools = " {$table}.school_id='-1' OR ";
            break;
        case 'documents':
            $appNotification = ", {$table}.app_notification ";
            $table = 'documents';
            $field_id = 'document_id';
            break;
        case 'gallery':
            $table = 'gallery';
            $field_id = 'gallery_id';
            break;
        case 'videos':
            $table = 'videos';
            $field_id = 'video_id';
            break;
    }

    $sql = "SELECT {$table}.id as row_id, {$table}.title as row_title, users.id as user_id, students.school_id {$appNotification} {$catid}
		FROM {$table}, {$table}_index, students, users
		
		WHERE TRUE
			AND `{$table}`.pushed=''
			AND users.username <> ''
			AND (
				users.username = students.info_mobile
				OR users.username = students.info_father_mobile
				OR users.username = students.info_mother_mobile
			)
			AND ( {$allSchools} (
				{$table}.id = {$table}_index.index_id
				AND {$table}.school_id = students.school_id
				AND (
					{$table}_index.class_id = students.class_id
					OR {$table}_index.class_id = '-1'
					)
				)
			)
			{$newsWhere}
		
		GROUP BY {$table}.id, students.id
	";
    
    switch ($table) {
        case 'education_news':
        case 'news':
        case 'documents':
//			SELECT {$table}_students.{$field_id} as row_id, {$table}_students.student_id 
            $sql = "
				SELECT {$table}_students.{$field_id} as row_id, {$table}.title as row_title, users.id as user_id, students.school_id {$appNotification} {$catid}
				FROM students, {$table}, {$table}_students , users
				WHERE TRUE
					AND `{$table}`.pushed=''
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
