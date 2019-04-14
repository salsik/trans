<?php

if( !defined('BASE_DIR') ) die('Access Denied!');

	
if( !$Account ) {
	include(BASE_DIR . 'controller/login.php');
	die();
}

if( ! preg_match("/[a-z0-9_-]+/i", $controller2 ) || !is_file(BASE_DIR . 'controller/account/account.'. $controller2 .'.php') )
{
	$controller = 'main';
}

include(BASE_DIR . 'controller/account/account.'. $controller .'.php');
