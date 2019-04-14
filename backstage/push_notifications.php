<?php

require_once "../includes/config.php"; 
require_once "../includes/conn.php";

include '../_push_notifications/fcm.php';
include '../_push_notifications/iospush.php';

//AIzaSyBXU1XGQdiQM1h_kID65GNkCFsd6NCQqf8
$configs = array(
    'android' => array(
        'server_key' => 'AIzaSyAwvKL0OMsaXVCAiQIoKCOM0QkxfUs5t8c', // Server Key // will be used when sending requests to GCM server
    ),
    'ios' => array(
        'apns_cert' => 'apns-prod.pem', // local certificate file
        'passphrase' => 'Sdl~acu7K3oX',
        'pconnect' => true,
    ),
);

//$GCM = new GCM( $configs['android']['server_key'] );
$FCM = new FCM();
$IOS = new IOS();

$strSQL = "SELECT *  FROM `notifications` WHERE `notifications`.sent = 0  group by user_id,type,news_id";
$q = mysql_query($strSQL);

if (mysql_num_rows($q)) {
    while($row = mysql_fetch_assoc($q)) {
        $rows[] = $row;
    }
    foreach( $rows as $Prow){
        if( $Prow['type'] == 'news'){
            $title = 'News';
        }else{
            $title = 'Medical News';
        }
        $data = array();
        $data['title'] = "Derma Connect - ".$title;
        $data['message'] = $Prow['text'];
        $registatoin_id = array();
        
        $iosArr = array($Prow['text'], 'site-news-details');
        
        $sqlLog = "SELECT *  FROM `doctors_logins` WHERE doctor_id = ".$Prow['user_id']; 
        $g = mysql_query($sqlLog);
        if (mysql_num_rows($g)) {
            while ($Grow = mysql_fetch_assoc($g)) { 
                if( $Grow['source'] == 'android'){
                    $registatoin_id[] = $Grow['regid'];        
                }else{
                    $result = $IOS->send_push($Grow['regid'], $iosArr);                    
                }                        
            }
            
            $sqlU = 'UPDATE `notifications` SET sent = 1 WHERE id = '.$Prow['id'];
            $q = mysql_query($sqlU);
            
            $result = $FCM->sendMessage($data, $registatoin_id);
        }
    }
}