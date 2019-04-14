<?
require "../includes/config.php";
require "../includes/conn.php";
require "../includes/module.php";
require_once "../includes/functions_login_session.php";
ob_start();
session_start();

$loginError = '';
$loginUser = is_login( $loginError );
if( $loginError )
{
	header('location: login.php?'.$loginError.'=1');
	exit;
}

$msg="";
if(empty($msg)):
$i=0;
$ids=array();
$order=array();
$table=$_GET['table'];
$langID=$_GET['langID'];
$archive=$_GET['archive'];
$sect=$_GET['sect'];
$tr_container = $_POST[trContainer];
foreach ($tr_container as $varname => $varvalue) {
	$varvalue = (int)$varvalue;
	if( $varvalue < 1 ) continue;
	$ids[$i]=$varvalue;
	$order[$i]=(0-getfield($ids[$i],"rank",$table));
	$i++;
}
$WHERE_TO_ADD = "";
if(!empty($langID)){
	$WHERE_TO_ADD .= "AND langID='$langID'";
}
if(!empty($archive)){
	$WHERE_TO_ADD .= "AND archive='$archive'";
}
if(!empty($sect)){
	$WHERE_TO_ADD .= "AND sect='$sect'";
}
$q=mysql_query("SELECT max(rank) as max FROM ".$table." where id IN(".implode(',',$ids).") $WHERE_TO_ADD");
$r = mysql_fetch_object($q);
$maxrank=$r->max;
$size=$i;
for($i=0;$i<$size;$i++){
	$order[$i]+=$maxrank;
}
if($tr_container){
	for($i=0;$i<$size;$i++){
		$strSQL="select * from  ".$table."  where id='".$ids[$i]."' $WHERE_TO_ADD";
		$objRS=mysql_query($strSQL);
		if ($row=mysql_fetch_object($objRS)){
			if(($order[$i]-$i)!=0){
				$strSQLord="update  ".$table."  set rank='".($row->rank+$order[$i]-$i)."' where id=".$row->id;
				mysql_query($strSQLord);
			}
		}
	}
}
echo '<span class="alert"><b>The order has been updated successfully !</b></span>';
else:
echo '<span class="alert">'.$msg.'</span>';
endif;
require '../includes/disconn.php';
?>