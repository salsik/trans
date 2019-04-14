<?php

define('_SMS_API', 'http://sbx.techram.co/sms/api.php');
define('_SMS_USERNAME', 'prevision');
define('_SMS_PASSWORD', 'AEZtN6hQNdVT6');

//$_DefaultAddedByTitle = 'Advances & More';
$_DefaultAddedByTitle = 'Prevision';
$_CompetitionWallLimit = 3;

$_BannersZones = array(
	'top_banner' => array(
		'title' => 'Public Top Banner',
		'width' => '800',
		'height' => '90',
//		'resize' => 'resize',
		'resize' => 'width',
		'school' => false,
		'private' => false,
		'maxWidth' => true, // true: max width 100% in app, false: use image width
	),
	'bottom_banner' => array(
		'title' => 'Public Bottom Banner',
		'width' => '800',
		'height' => '90',
//		'resize' => 'resize',
		'resize' => 'width',
		'school' => false,
		'private' => false,
		'maxWidth' => true, // true: max width 100% in app, false: use image width
	),
	'listings_banner' => array(
		'title' => 'Public Listings Banner',
		'width' => '800',
		'height' => '90',
//		'resize' => 'resize',
		'resize' => 'width',
		'school' => false,
		'private' => false,
		'maxWidth' => true, // true: max width 100% in app, false: use image width
	),
	'home_banner' => array(
		'title' => 'Home Banner',
		'width' => '800',
		'height' => '90',
//		'resize' => 'resize',
		'resize' => 'width',
		'school' => false,
		'private' => false,
		'maxWidth' => true, // true: max width 100% in app, false: use image width
	),
	'top_banner_private' => array(
		'title' => 'School Top Banner',
		'width' => '800',
		'height' => '90',
//		'resize' => 'resize',
		'resize' => 'width',
		'school' => false,
		'private' => true,
		'maxWidth' => true, // true: max width 100% in app, false: use image width
	),
	'bottom_banner_private' => array(
		'title' => 'School Bottom Banner',
		'width' => '800',
		'height' => '90',
//		'resize' => 'resize',
		'resize' => 'width',
		'school' => false,
		'private' => true,
		'maxWidth' => true, // true: max width 100% in app, false: use image width
	),
	'listings_banner_private' => array(
		'title' => 'School Listings Banner',
		'width' => '800',
		'height' => '90',
//		'resize' => 'resize',
		'resize' => 'width',
		'school' => false,
		'private' => true,
		'maxWidth' => true, // true: max width 100% in app, false: use image width
	),
);

/*
 * https://code.google.com/apis/console
 * Create new project
 * get project id which will be used as SENDER ID
 * go to "Services" on the left panel and turn on "Google Cloud Messaging for Android".
 * go to "API Access" and note down the "API Key". This API key will be used when sending requests to GCM server
 */
// https://code.google.com/apis/console/#project:556462580546

$push_notifications_title = 'Prevision Education';
$_push_notifications_settings = array(
	'android' => array(
//		'project_number' => '', // will be used as SENDER ID
//		'project_id' => '',
//		'android_key' => 'AIzaSyDTpW8qvZJkBLv3K3XyVt38P4zQSepkJPU',
//		'browser_key' => 'AIzaSyDSQYV0ikg9TEI3eVFeZwI-HG24rdK_SMI', // Browser Key // will be used when sending requests to GCM server
		'server_key' => 'AIzaSyBqEGWR3ybEKF_KGKnkYuZdg1Lx0PRXxkA', // Server Key // will be used when sending requests to GCM server

//		'server_key' => 'AIzaSyBe5SIEJhiXsEnUIqpBbbSY7nttJqhlKuU', // Android Key // will be used when sending requests to GCM server
//		'server_key' => 'AIzaSyA63UhroF053dcZmXHxZxufyhH_F3bv3bE', // test key for my project
	),
	'ios' => array(
//		'apns_host' => 'gateway.sandbox.push.apple.com',
//		'apns_post' => 2195,
//		'apns_cert' => 'apns-dev.pem', // local certificate file
//		'passphrase' => '123456',
		'apns_cert' => 'apns-prod.pem', // local certificate file
		'passphrase' => '',
		'pconnect' => true,
	),
);