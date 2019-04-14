<?php

# Session Time
define('SESSION_TIME','72000'); //2 hours

# Session Cookies
define('SEED_1','lskgh30967043sljdhtgth489hg802380gh93890g8hQWA8HD280HH80HDC2LAGHD-02356-2395239U5023U50');
define('SEED_2','skgh30967043sLjdht3562395239U5o2gt3h42200089hg802adfgdsgh9389offfWAdd8222PHdsH80HDC2LAGHD023562395239U5o23U50');

function is_login( &$error = '' )
{
	$page = strtolower( basename( $_SERVER['PHP_SELF'] ));
	if ($_SESSION['adminAuth']=="" || $_SESSION['adminAuthSpecial']=="")
	{
		$error = 'nologin';
		return false;
	}

//	@mysql_query("SET NAMES 'cp1256' ");

	$sesstime = 0;
	$logintime = 0;
	$user_id = 0;

	
	
	$table = 'admins';
	$objRS = mysql_query( "SELECt * FROM `$table` WHERE sessid='".mysql_real_escape_string($_SESSION['adminAuth'])."' LIMIT 1" );

	if ( ($user = @mysql_fetch_assoc($objRS)) ) {
		define('isAdmin', $user['id']);
		$userExist=1;
	}else {	
	$table = 'schools';
	$objRS = mysql_query( "SELECt * FROM `$table` WHERE sessid='".mysql_real_escape_string($_SESSION['adminAuth'])."' LIMIT 1" );	
		if ( ($user = @mysql_fetch_assoc($objRS)) ) {
				$user['school_id'] = $user['id'];
				define('isSchool', $user['id']);
				$userExist=1;
			}else{
						$table = 'school_admins';
		$objRS = mysql_query( "SELECt * FROM `$table` WHERE sessid='".mysql_real_escape_string($_SESSION['adminAuth'])."' LIMIT 1" );
		
				if(($user = @mysql_fetch_assoc($objRS))){
					$user['school_id'] = $user['id'];
					//define('isSchool', $user['id']);
					define('isSchoolAdmin', $user['id']);
					$userExist=1;
			}else{
				$table = 'teachers';
				$objRS = mysql_query( "SELECt * FROM `$table` WHERE sessid='".mysql_real_escape_string($_SESSION['adminAuth'])."' LIMIT 1" );
				if ( ($user = @mysql_fetch_assoc($objRS)) ) {
				define('isTeacher', $user['id']);
				$userExist=1;
					}else{
						$userExist=0;
						}
				}
			}
		}
		

		if($userExist == 0){
				$error = 'nouser';
				return false;
			}
	define('isSchool', 0);
	define('isSchoolAdmin', 0);
	define('isTeacher', 0);
	define('isAdmin', 0);

	$time_now = time();
	if($time_now > $user['sesstime'] )
	{
		$error = 'nosess';
		return false;
	}

	$cookie_left_value = $_COOKIE['adminAuthLeft'];
	$cookie_right_value = $_COOKIE['adminAuthRight'];

	$adminAuthSpecialSession = md5( $cookie_left_value . $user['logintime'] . $cookie_right_value );

	if($_SESSION['adminAuthSpecial'] != $adminAuthSpecialSession)
	{
		$error = 'nosess';
		return false;
	}

	$sessTimeNew = time() + SESSION_TIME;

	@mysql_query( "UPDATE `$table` SET sesstime='$sessTimeNew' WHERE id='{$user['id']}'" );

	return $user;
}

function do_login($username, $password, &$error = '')
{
	$table = 'admins';
	$objRS = mysql_query("SELECT * FROM `$table` where username = '".mysql_real_escape_string($username)."' AND password='".mysql_real_escape_string(md5($password))."' LIMIT 1");
	if (!$row = mysql_fetch_assoc($objRS))
	{
		$table = 'schools';
		$objRS = mysql_query("SELECT * FROM `$table` where email = '".mysql_real_escape_string($username)."' AND password='".mysql_real_escape_string(md5($password))."' LIMIT 1");
		if (!($row = mysql_fetch_assoc($objRS)) )
		{
			$table = 'teachers';
			$objRS = mysql_query("SELECT * FROM `$table` where email = '".mysql_real_escape_string($username)."' AND password='".mysql_real_escape_string(md5($password))."' LIMIT 1");
			if (!($row = mysql_fetch_assoc($objRS)) )
			{
		$table = 'school_admins';
	$objRS = mysql_query("SELECT * FROM `$table` where username = '".mysql_real_escape_string($username)."' AND password='".mysql_real_escape_string(md5($password))."' LIMIT 1");			
				if (!($row = mysql_fetch_assoc($objRS)) )
				{
				$error = 'nouser';
				//$error = $table;
				return false;
				}
			}
		}
	}

	$timing_now = time();

	preg_match("/<address>(.*?)<\/address>/", $_SERVER['SERVER_SIGNATURE'], $SERVER_SIGNATURE_array);
	$SERVER_SIGNATURE = str_replace(" ", "", $SERVER_SIGNATURE_array[1]);
	$SERVER_SIGNATURE = $SERVER_SIGNATURE . $timing_now;

	$sessId2_p1 = $_SERVER['REMOTE_ADDR'] . ":" . $_SERVER['REMOTE_PORT'] . "-" . $timing_now . "-".$_SERVER['SERVER_ADDR'] . ":" . $_SERVER['SERVER_PORT'];

	$SEED_1 = SEED_1;
	$SEED_2 = SEED_2;
	
	$SEED_1_value = '';
	$SEED_2_value = '';

	for($i=1; $i<=10; $i++)
	{
		$random = rand(0, strlen( SEED_1 ));
		$SEED_1_value .= substr( SEED_1, $random, 1);

		$random = rand(0, strlen( SEED_2 ));
		$SEED_2_value .= substr( SEED_2, $random, 1);
	}

	$sessId2_p1 = $SEED_1_value . sha1( $sessId2_p1 );
	$sessId2_p2 = md5( $SERVER_SIGNATURE ) . $SEED_2_value;
	
	// set the cookies //
	setcookie("adminAuthLeft", $sessId2_p1, 0, '/');
	setcookie("adminAuthRight", $sessId2_p2, 0, '/');

	$sessId2 = $sessId2_p1 . $timing_now . $sessId2_p2;
	$_SESSION['adminAuthSpecial'] = md5( $sessId2 );

	$last_id_admin_logger = mysql_insert_id();
	$sessTime = $timing_now + SESSION_TIME;
	$sessId = md5( $last_id_admin_logger . time() . $row['username'] . time() . $row['password'] );

	$_SESSION['adminAuth'] = $sessId;

	$q = @mysql_query( "UPDATE `$table` SET sessid='$sessId', sesstime='$sessTime', logintime='$timing_now' WHERE id='$row[id]' LIMIT 1" );

	return $row;
}


function do_logout()
{
	if( empty( $_SESSION['adminAuth'] ))
	{
		return true;
	}
	
	$objRS2=mysql_query( "UPDATE admins SET sessid='',sesstime='0',logintime='0' WHERE sessid='".mysql_real_escape_string( $_SESSION['adminAuth'] )."' " );
	$objRS2=mysql_query( "UPDATE schools SET sessid='',sesstime='0',logintime='0' WHERE sessid='".mysql_real_escape_string( $_SESSION['adminAuth'] )."' " );
	$objRS2=mysql_query( "UPDATE teachers SET sessid='',sesstime='0',logintime='0' WHERE sessid='".mysql_real_escape_string( $_SESSION['adminAuth'] )."' " );
	session_destroy();
	// remove cookies //
	setcookie("adminAuthLeft","",today()-3600*24*14,'/');
	setcookie("adminAuthRight","",today()-3600*24*14,'/');
	return true;
}

if( !function_exists('isid') )
{
	function isid($string)
	{
		$string = "$string";
		if(!$string)
		{
			return false;
		}
		for ($i=0;$i<strlen($string);$i++)
		{
			$temp=ord(substr($string,$i,1));
			if ($temp<48 || $temp>57)
			{
				return false;
			}
		}
		return true;
	}
}
