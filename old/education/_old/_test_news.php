<?php


require_once "_startup.php";

$catid = 8;
$schoolid = 18;

$time = time();
$sql = "insert into education_news set `title`='Education World Test - {$time}', `description`='Test Test', `status`='active', `date`='', `app_notification`='1', `publish_date_time`='0:0:00', `news_cat_id`='{$catid}', `school_id`='{$schoolid}', image='', rank='48', time='{$time}' ";


$q = mysql_query($sql);

if( $q ) {
	$id = mysql_insert_id();
	
	$sql = "REPLACE INTO data_updates SET `time` = '{$time}', `table` = 'education_news', `id`='{$id}', `catid`='{$catid}' ";
	$q = mysql_query($sql);
	
	$sql = "INSERT INTO `education_news_index` SET `index_id`='{$id}', `class_id`='-1' ";
	$q = mysql_query($sql);
	
	echo "New education news added.";
}
else {
	echo "Error while inserting new education news. ";
	echo mysql_error();
}