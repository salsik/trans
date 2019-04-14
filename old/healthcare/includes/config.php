<?php


# Configuration Goes Here
$filename=basename($_SERVER['PHP_SELF']);

// ONLINE OR NOT
switch($_SERVER['SERVER_NAME'])
{
	case '192.168.50.249':
	case '127.0.0.1':
	case 'localhost':
		define('ONLINE', false);
		break;
	default:
		define('ONLINE', true);
		
}

if (ONLINE){
 #online 

	$dbServer="localhost";
	$dbUser="previsionpro";
	$dbPass="A8w1K2m2";
	$dbDatabase="cme";
        
        $_SERVER['HTTP_HOST'] = 'www.previsionpro.com/healthcare';

//	if( !$_SERVER['HTTP_HOST'] )
//	{
//	 	$_SERVER['HTTP_HOST'] = 'www.previsionpro.com/healthcare';
//	}

	define('BASE_URL', 'http://'.$_SERVER['HTTP_HOST'].'/'); // with the last slash

	function today(){ return mktime(gmdate("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));}
} else {
 #offline

	$dbServer="localhost";
	$dbUser="root";
	$dbPass="root";
	$dbDatabase="cme";

	if( !$_SERVER['HTTP_HOST'] )
	{
	 	$_SERVER['HTTP_HOST'] = '127.0.0.1';
	}

	define('BASE_URL', 'http://'.$_SERVER['HTTP_HOST'].'/techram/medical/'); // with the last slash

	function today(){ return mktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y"));}
}


define("BASE_EMAIL_FROM_name", "Prevision");
define("BASE_EMAIL_FROM", "info@prevision.me");
define("BASE_EMAIL_CONTACT", "info@prevision.me");
define("BASE_EMAIL", "info@prevision.me");

# Program Variables
define('WEBADMIN_VER','3.7');

# Person to be contacted manually or automatically when an error occurs
define('EMAIL_ADMIN','iskandar.salama@gmail.com');      
define('EMAIL_TRACKING','iskandar.salama@gmail.com');   

# Project
define('COOKIE','vis'); //Project Identity
define('PROJECT_NAME','Prevision Pro LCME');
define('PROJECT_VER','1.0');

#Used in the "from" field in all e-mails sent from this e-mail
define('EMAIL_FROM','iskandar.salama@gmail.com'); 
define('EMAIL_FROM_2','iskandar.salama@gmail.com'); 

# Session Time
define('SESSION_TIME','7200000000'); //2 hours

# Session Cookies
define('SEED_1','lskgh30967043sljdhtgth489hg802380gh93890g8hQWA8HD280HH80HDC2LAGHD-02356-2395239U5023U50');
define('SEED_2','skgh30967043sLjdht3562395239U5o2gt3h42200089hg802adfgdsgh9389offfWAdd8222PHdsH80HDC2LAGHD023562395239U5o23U50');
