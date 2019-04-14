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

//sleep(1);

$limitation = '';

$limitationSchoolUser = " 
	SELECT users.id 
	FROM users, students 
	WHERE users.username <> ''
	WHERE students.school_id = '".isSchool."'
	AND (
		students.info_mobile = users.username
		OR students.info_father_mobile = users.username
		OR students.info_father_mobile = users.username
		)
";

$from = $_REQUEST['from'];

$keyword = $_GET['q'];

$keywordSQL = mysql_real_escape_string($keyword);
$keywordSQL = str_replace(' ', '% %', $keywordSQL);

$select = '';
$where = '';
$leftJoin = '';
$limit = 10;

switch($from)
{
	case 'classes_albums':
	case 'classes':
		
		$classes = get__classes( $_GET['school_id'] );
		
		if( $from == 'classes_albums' ) {
			$albums = get__albums( $_GET['school_id'] );
			
			echo json_encode( array(
				'classes' => $classes,
				'albums' => $albums,
			) );
		}
		else {
			echo json_encode( $classes );
		}
		exit;
		break;
	case 'albums':
		
		$data = get__albums( $_GET['school_id'] );
		
//	var_dump($data);
		echo json_encode( $data );
		exit;
		break;
	case 'students_list':

		if( isSchool ) {
			$limitation = " AND school_id='".isSchool."' ";
		}
		else if( !isAdmin ) {
			die();
		}
		
		$data = array();
		
		$class_id = intval( $_GET['class_id'] ); 
		
		$query = "SELECT id, title
			FROM students 
			WHERE class_id= '{$class_id}' {$limitation} 
			ORDER BY title ASC ";
		$q = mysql_query( $query );
		
		if( $q && mysql_num_rows( $q )) {
			while($row = mysql_fetch_assoc($q)) {
				$data[] = $row;
			}
		}
		
//	var_dump($data);
		echo json_encode( $data );
		exit;
		break;
		
		
	case 'schools':
		if( !isAdmin ) {
			die();
		}
		$table = $from;

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
	case 'students':
		if( isSchool ) {
			$limitation = " AND school_id='".isSchool."' ";
		}
		else if( !isAdmin ) {
			die();
		}
		
		$table = $from;
		$orderBy = " ORDER BY `{$table}`.full_name ASC";
		
		$sql = "
			`{$table}`.full_name LIKE '%$keywordSQL%' 
			OR `{$table}`.info_phone LIKE '%$keywordSQL%' 
			OR `{$table}`.info_mobile LIKE '%$keywordSQL%' 
			OR `{$table}`.info_email LIKE '%$keywordSQL%'
		";

		$where .= " AND ( $sql ) ";
		
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
	case 'users':
		if( isSchool ) {
			$limitation = " AND {$limitationSchoolUser} ";
		}
		else if( !isAdmin ) {
			die();
		}
		
		$table = $from;
		$orderBy = " ORDER BY `{$table}`.title ASC";
		
		$sql = "
			`{$table}`.title LIKE '%$keywordSQL%' 
			OR `{$table}`.username LIKE '%$keywordSQL%' 
		";

		$where .= " AND ( $sql ) ";
		
		$field1 = "title";
		
		break;
	default:
		die();
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


function get__classes( $id ) {

	if( isSchool ) {
		$data = get_school_classes( isSchool );
	}
	else if( isAdmin ) {
		$data = get_school_classes( $id );
	}
	else {
		$data = array();
	}
	
	return $data;
}


function get__albums( $id ) {

	if( isSchool ) {
		$data = get_school_albums( isSchool );
	}
	else if( isAdmin ) {
		$data = get_school_albums( $_GET['school_id'] );
	}
	else {
		$data = array();
	}
	
	return $data;
}