<?php session_start();
//  Developed by Roshan Bhattarai 
//  Visit http://roshanbh.com.np for this script and more.
//  This notice MUST stay intact for legal use
require_once "../includes/config.php"; 
require_once "../includes/conn.php";
require_once "../includes/module.php";
require_once "../includes/functions_login_session.php";
//Connect to database from here


$username = $_POST['user_name'];
$password = $_POST['password'];
$submit = $_POST['submit'];
$username=htmlspecialchars(addslashes($username),ENT_QUOTES);
$password=htmlspecialchars(addslashes($password),ENT_QUOTES);
$submit=htmlspecialchars(addslashes($submit),ENT_QUOTES);
if (!empty($_GET['logout']) || !empty($_GET['nosess']) )
{
	require "../includes/conn.php";

	do_logout();

	if( !empty($_GET['logout']) )
	{
		$msg="Logged out successfully";
	}
	else
	{
		$msg = "Your session has been expired.";
		echo "no";
	}
	$username = "";
	$password = "";
	require "../includes/disconn.php";
}
if ($username!=""){
	if ($password!=""){ 
		 require "../includes/conn.php";

		 $row = do_login($username, $password, $theError);

		 if( !$row )
		 {
			 //echo $row;
			$msg = "Invalid Username/Password.";
			echo "no";
		 	$error_flag = 1;
		 }
		 else
		 {
		 	$timing_now = time();

			echo "yes";
			exit;
		 }
		 require "../includes/disconn.php";
	}
	else{
		 $msg = "Enter password.";
		 echo "no";
		 $username = "";
		 $password = "";
	}
}elseif ($submit!=""){
	$msg = "Enter Username.";
	echo "no";
	$username = "";
	$password = "";
}elseif (!empty($_GET['nologin'])){
	$msg = "Please log in first.";
	echo "no";
	$username = "";
	$password = "";
}

?>