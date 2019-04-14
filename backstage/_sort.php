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
$flag=$_GET['flag'];
$flag2=$_GET['flag2'];
$flag3=$_GET['flag3'];
$tr_container = array_reverse($_POST[trContainer]);
foreach ($tr_container as $varname => $varvalue) {
	$varvalue = (int)$varvalue;
	if( $varvalue < 1 ) continue;
	$ids[$i]=$varvalue;
	$order[$i]=(0-getfield($ids[$i],"rank",$table));
	$i++;
}
if(!empty($flag)){
	$WHERE_TO_ADD = "AND year='$flag'";
}
if(!empty($flag2)){
	$WHERE_TO_ADD = "AND cat='$flag2'";
}
if(!empty($flag3)){
	$WHERE_TO_ADD = "AND comu_id='$flag3'";
}
//print_r($ids);
//print("<br />");
//print_r($order);
$q=mysql_query("SELECT max(rank) as max FROM ".$table." where id IN(".implode(',',$ids).") $WHERE_TO_ADD");
$r = mysql_fetch_object($q);
$maxrank=$r->max;
//print("<br />");
//print($maxrank);
//print("<br />");
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
//				print($strSQLord."<br />");
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