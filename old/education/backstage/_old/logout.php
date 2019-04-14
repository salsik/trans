<? session_start();
require "../includes/config.php";
require "../includes/module.php";
require_once "../includes/functions_login_session.php";
//  Developed by Roshan Bhattarai 
//  Visit http://roshanbh.com.np for this script and more.
//  This notice MUST stay intact for legal use

require "../includes/conn.php";
	
do_logout();
	
$msg="Logged out successfully";
header('location: login.php?nologin=1');
require "../includes/disconn.php";
?>
