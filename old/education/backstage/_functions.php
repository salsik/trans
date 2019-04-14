<?php

function fixLinkProtocol( $url ) {
	if($url && is_string($url)) {
		$str = strtolower($url);
		if(strpos($str, 'http://')===0) {
			// Ignore
		} else if(strpos($str, 'https://')===0) {
			// Ignore
		} else if(strpos($str, 'ftp://')===0) {
			// Ignore
		} else {
			$url = "http://" . $url;
		}
	}
	
	return $url;
}

function is_date( $string ) {
	
	$date_pattern = "/^[0-9]{4}-[01]?[0-9]-[0123]?[0-9]$/";
	if( preg_match($date_pattern, $string)) {
		return true;
	}
	return false;
}

function is_time( $string ) {
	$time_pattern = "/^([012]?[0-9]):([0-5]?[0-9])\s*(am|pm)?$/i";
	if( preg_match($time_pattern, $string, $matches)) {
		$h = intval($matches[1]);
		$m = intval($matches[2]);
		$a = strtolower($matches[3]);
		return array(
			'H' => ($a=='pm' && $h < 12) ? $h + 12 : $h,
			'h' => ($h>12) ? $h - 12 : $h,
			'm' => $m,
			'h' => $a,
		);
	}
	return false;
}

function get_school_classes( $id, $row_id = false ) {
	return get_school_data('classes', $id, $row_id);
}
function get_teacher_classes( $id, $row_id = false ) {
	return get_school_data('classes', $id, $row_id, 'teachers_index');
}
function get_school_albums( $id, $row_id = false ) {
	return get_school_data('gallery_category', $id, $row_id);
}

function get_school_data($table, $id, $row_id = false, $index_table = '' ) {

	$dataArray = array();
	
	$id = intval( $id );
	$index_id = intval( $index_id );
	$sql = "SELECT * FROM `{$table}` WHERE school_id = '{$id}' ORDER BY rank DESC";
	
	switch($table) {
		case 'classes':
			if( $index_table ) {
				$sql = "SELECT `classes`.* 
					FROM `classes`, `{$index_table}`
					WHERE `{$index_table}`.class_id=`classes`.id
						AND `{$index_table}`.index_id='{$id}'
					ORDER BY `classes`.rank DESC";
			}
			break;
		case 'gallery_category':
			break;
		default:
			return array();
	}


	$q = mysql_query( $sql );
//echo "$sql " . mysql_error();
	if( $q && mysql_num_rows($q ) ) {
		while( $row = mysql_fetch_assoc( $q )) {
			
			$data = array(
				'id' => $row['id'],
				'title' => $row['title'],
			);
			if( $row_id ) {
				$dataArray[ $row['id'] ] = $data;
			}
			else {
				$dataArray[] = $data;
			}
		}
	}
	
	return $dataArray;
}
function style_titles($titles, $html = false) {
	
	if( !is_array($titles)) {
		$titles = explode('<~~>', $titles);
	}
	
	$return = '';
	
	foreach($titles as $title) {
		$return .= ($html) ? "<b>$title</b>\r\n" : "[$title]\r\n";
	}
	
	$return = trim($return);

	if($html) {
		return nl2br( $return );
	} else {
		return $return;
	}
}

function style_students_titles($titles, $row = NULL) {
	return style_category_titles($titles, $row, 'All Students', 'students_ids');
}

function style_classes_titles($titles, $row = NULL) {
	return style_category_titles($titles, $row, 'All Classes', 'classes_ids');
}

function style_category_titles($titles, $row = NULL, $def = 'All', $field = 'ids') {
	
	$html = (is_array($row)) ? false : true;

	$return = '';
	
	$_ids = (is_array($row)) ? $row[ $field ] : $row->$field;
	$_ids = explode('<~~>', $_ids);
	foreach($_ids as $k=>$v) {
		if( $v == '-1') {
			$return .= ($html) ? "<b>[{$def}]</b>\r\n" : "[{$def}]\r\n";
			break;
		}
	}
	
	if( !is_array($titles)) {
		$titles = explode('<~~>', $titles);
	}

	foreach($titles as $k=>$v) {
		$return .= ($html) ? "<b>{$v}</b>\r\n" : "{$v}\r\n";
	}
	
	$return = trim($return);

	if($html) {
		return nl2br( $return );
	} else {
		return $return;
	}
}

function get_title_from_array($id = 0, $arr = array()) {
	
	$array = $arr;
	
	$array = (array) $array;
	
	return $array[$id]['title'];
}

function style_news_category($news_cat_id = 0) {
	global $newsCategories;
	return get_title_from_array($news_cat_id, $newsCategories);
}
function style_documents_category($document_cat_id = 0) {
	global $documentsCategories;
	return get_title_from_array($document_cat_id, $documentsCategories);
}

function style_news_students($_students, $html = false) {
	return _style_group_titles($_students, $html);
}
function style_documents_students($_students, $html = false) {
	return _style_group_titles($_students, $html);
}



function _style_group_titles($data, $html = false) {

	if( !is_array($data)) {
		$data = explode(',', $data);
	}
	$data = array_map('trim', $data);
	
	$return = '';
	$array = array();
	
	foreach($data as $_data) {
		if( $html==='array') {
			$array[$id] = $_data;
		} else {
			$return .= ($html) ? "- $_data\r\n" : "$_data\r\n";
		}
	}

	if( $html==='array') {
		return $array;
	}
	$return = trim($return);

	if($html) {
		return nl2br( $return );
	} else {
		return $return;
	}
}

function _style_group_ids($_ids, $data, $html = false) {

	if( !is_array($_ids)) {
		$_ids = explode(',', $_ids);
	}
	if( !is_array($data)) {
		$data = array();
	}
	
	$return = '';
	$array = array();
	
	foreach($_ids as $id) {
		if($data[ $id ]) {
			$Str = $data[ $id ]['title'];
			if( $html==='array') {
				$array[$id] = $Str;
			} else {
				$return .= ($html) ? "- $Str\r\n" : "$Str\r\n";
			}
		}
	}

	if( $html==='array') {
		return $array;
	}
	$return = trim($return);

	if($html) {
		return nl2br( $return );
	} else {
		return $return;
	}
}


function setUpdatedRow($table, $id, $action = '', $catid = 0) {
	if( !setUpdatedTable($table, $action ) ) {
		return false;
	}
	
	$id = intval( $id );
	$catid = intval( $catid );

	$deleted = ($action=='delete') ? " , `status`='deleted' " : '';
	$deleted = '';
	mysql_query ("REPLACE INTO data_updates SET
		`time` = '".time()."'
		, `table` = '$table'
		, `id`='$id'
		, `catid`='{$catid}'
		$deleted
	");
//	mysql_query ("UPDATE data_sync SET `updated` = '1' WHERE `table` = '$table' AND `id`='$id' "); //  LIMIT 1
}

function setUpdatedRowSql($table, $sql, $action = '', $catid = 0) {
	if( !setUpdatedTable($table, $action ) ) {
		return false;
	}
	$catid = intval( $catid );

	$deleted1 = ($action=='delete') ? " , `status` " : '';
	$deleted2 = ($action=='delete') ? " , 'deleted' " : '';
	$deleted1 = '';
	$deleted2 = '';

	mysql_query ("REPLACE INTO data_updates (`time`, `table`, `id`, `catid` $deleted1)
		SELECT '".time()."', '$table', id, '{$catid}' $deleted2 FROM `$table` WHERE $sql
	");
//	mysql_query ("UPDATE data_sync SET `updated` = '1' WHERE `table` = '$table' AND `id` IN ( SELECT `id` FROM `$table` WHERE $sql ) ");
}

function setUpdatedTable($table, $action ) {
	switch($table) {
		case 'news':
		case 'education_news':
		case 'documents':
		case 'gallery':
		case 'videos':
		case 'agenda':
//		case 'questions':
		case 'questions_replies':
			break;
		default:
			return false;
	}
	switch($action) {
		case 'add':
//		case 'edit':
//		case 'delete':
			break;
		default:
			return false;
	}
	
	return TRUE;
}




function students_limit() {
	
	global $Admin;
	
	if( isSchool && $Admin['plan_students'] > 0) {
		$count = getDataCount('students', " `school_id` = '".isSchool."' ");
		if( $count >= $Admin['plan_students'] ) {
			return true;
		}
	}
	
	return false;
}