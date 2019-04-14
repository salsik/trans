<?php 
	define('microTime', microtime( true ) );
	define("BASE_DIR", realpath( dirname(__FILE__) ) . '/' );

//ob_start(); 
session_start();

require_once "includes/config.php"; 
require_once "includes/conn.php";
require_once "_vars.php";
require_once "includes/module.php";
require_once "includes/functions_login_doctors.php";
require_once "_queries.php";
require_once "includes/image.php";


//$_MainCompanyName = 'Advances & More';
$_MainCompanyName = 'Prevision';
$_CompetitionWallLimit = 3;


require_once( BASE_DIR . "_functions.php");

if( ! (get_magic_quotes_gpc() == 0) )
{
	$_GET = @my_array_map('stripslashes', $_GET);
	$_POST = @my_array_map('stripslashes', $_POST);
	$_COOKIE = @my_array_map('stripslashes', $_COOKIE);
}

ob_clean();

if( !defined('isAjaxRequest') ) {

	$loginError = '';
	$Account = is_login( $loginError );
	if( $loginError )
	{
		$Account = array();
	}
}

if( $Account )
{
	$Account['doctor_reseller_id'] = 0;
	$Reseller = false;
	$sql = "SELECT resellers.*
		FROM resellers 
			LEFT JOIN doctors_resellers ON(doctors_resellers.reseller_id=resellers.id) 
		WHERE doctors_resellers.doctor_id='{$Account['id']}'
			AND resellers.status='active'
		GROUP BY resellers.id
		LIMIT 1";

	$q = mysql_query ($sql);

	if ( $q && mysql_num_rows($q) ) 
	{
		$Reseller = mysql_fetch_assoc ($q);
		$Account['doctor_reseller_id'] = $Reseller['id'];
	}
}
