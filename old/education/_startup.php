<?php 
$timezone = 'Asia/Beirut';

if(function_exists('date_default_timezone_set')) {
	@date_default_timezone_set($timezone) ;
} else {
	@putenv('TZ='.$timezone) ;
}

	define('microTime', microtime( true ) );

	define("BASE_DIR", realpath( dirname(__FILE__) ) . '/' );

ob_start();
//session_start();
if( !defined('M_API')) {
	@session_start();
}

require_once "includes/config.php"; 
require_once "includes/conn.php";
require_once "_settings.php";
require_once "includes/module.php";
if( !defined('M_API')) {
	require_once "includes/functions_login_students.php";
}
require_once "_queries.php";


require_once( BASE_DIR . "_functions.php");
require_once( BASE_DIR . "_common_functions.php");

if( ! (get_magic_quotes_gpc() == 0) )
{
	$_GET = @my_array_map('stripslashes', $_GET);
	$_POST = @my_array_map('stripslashes', $_POST);
	$_COOKIE = @my_array_map('stripslashes', $_COOKIE);
}

ob_clean();

//if( !defined('isAjaxRequest') ) {
//
//	$loginError = '';
//	$Account = is_login( $loginError );
//	if( $loginError )
//	{
//		$Account = array();
//	}
//}

//if( $Account )
//{
//	$Account['student_school_id'] = 0;
//	$School = getDataByID('schools', $Account['school_id'], " status='active' ");
//
//	if ( $School ) 
//	{
//		$Account['student_school_id'] = $School['id'];
//	}
//}
