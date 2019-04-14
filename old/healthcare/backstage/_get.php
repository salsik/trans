<?php

define('isAjaxRequest', true);

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s", time()-3600) . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

ob_start();

include "_top.php";

ob_clean();

$from = $_REQUEST['from'];

if($from == 'categories') {
	include '_get.category.php';
	exit;
}

$keyword = $_GET['q'];

$keywordSQL = mysql_real_escape_string($keyword);
$keywordSQL = str_replace(' ', '% %', $keywordSQL);

$select = '';
$where = '';
$leftJoin = '';
$limit = 10;

switch($from)
{
	case 'level_classes':
		if(!$table) {
			$table = 'classes';
		}
//	case 'categories':
//	case 'categories_sub':
//		if(!$table) {
//			$table = $from;
//		}
//
//		$orderBy = " ORDER BY `{$table}`.title ASC";
//
//		$sql = "
//			`{$table}`.title LIKE '%$keywordSQL%' 
//		";
//
//		$where .= " AND ( $sql ) ";
//		switch($from) {
//			case 'categories_sub':
//				if( $_REQUEST['category_id']) {
//					$where .= " AND categories_sub.parent_id = '".intval($_REQUEST['category_id'])."' ";
//				} else {
//					$where .= " AND FALSE ";
//				}
//				break;
//		}
//
//		$field1 = "title";
//		break;
		
	case 'resellers':
		if(!$table) {
			$table = $from;
		}

		$orderBy = " ORDER BY `{$table}`.title ASC";

		$sql = "
			`{$table}`.title LIKE '%$keywordSQL%' 
		";
//			OR `{$table}`.info_phone LIKE '%$keywordSQL%' 
//			OR `{$table}`.info_mobile LIKE '%$keywordSQL%' 
//			OR `{$table}`.info_email LIKE '%$keywordSQL%'

		$where .= " AND ( $sql ) ";

		$field1 = "title";
		break;
	case 'doctors':
		$table = $from;
		$orderBy = " ORDER BY `{$table}`.full_name ASC";
		
		$sql = "
			`{$table}`.full_name LIKE '%$keywordSQL%' 
			OR `{$table}`.info_phone LIKE '%$keywordSQL%' 
			OR `{$table}`.info_mobile LIKE '%$keywordSQL%' 
			OR `{$table}`.info_email LIKE '%$keywordSQL%'
		";

		$where .= " AND ( $sql ) ";
		
//		if( !isReseller ) {
//			$reseller_id = intval( $_REQUEST['reseller_id'] );
//			$resellers_id = explode(',', $_REQUEST['reseller_id']);
//			$resellers_id = array_map('trim', $resellers_id);
//			$resellers_id = array_map('intval', $resellers_id);
//			$_ids = array();
//			foreach($resellers_id as $v) {
//				if($v > 0 ) {
//					$_ids[$v] = $v;
//				}
//			}
//			if( count($_ids) > 1 ) {
//				$where .= " AND `{$table}`.reseller_id IN (".implode(',', $_ids).") ";
//			}
//			else if( count($_ids) == 1 ) {
//				$where .= " AND `{$table}`.reseller_id = ".array_shift($_ids)." ";
//			}
//		}
		
		$field1 = "full_name";
		
//		if($_REQUEST['action'] == 'payments' && $table == 'users')
//		{
//			$select .= " , (SELECT SUM(`payments`.amount) as payments FROM `payments` where `payments`.user_id = `users`.id ) as payments ";
//			$select .= " , ( SELECT SUM(CASE WHEN `registration`.reg_trial='1' THEN `registration`.reg_price_trial ELSE `registration`.reg_price END) FROM `registration` WHERE `registration`.user_id = users.id ) as topay ";
//			
//			
//			$field2 = 'payments';
//			$field3 = 'topay';
//		}

		break;
	default:
		die();
}

$limitation = '';

if( isReseller ) {
	switch($from)
	{
		case 'doctors':
			$f = substr($table, 0, -1);
			$limitation = " AND id IN (SELECT {$f}_id FROM {$table}_resellers WHERE reseller_id='".isReseller."' ) ";
			break;
		default:
			$from = '';
			die();
			break;
	}
}

$strSQL="SELECT `{$table}`.* $select FROM `{$table}` {$leftJoin} WHERE TRUE {$where} {$limitation} {$orderBy} LIMIT {$limit}";

$objRS = mysql_query($strSQL);

if( $objRS && mysql_num_rows($objRS) )
{
	while ($row=mysql_fetch_object($objRS))
	{
		echo $row->$field1 . "|$row->id";
		if($field2) {
			echo "|" .$row->$field2;
		}
		if($field3) {
			echo "|" .$row->$field3;
		}
		echo "\n";
	}
}
exit;
