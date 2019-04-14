<?php


function explode_cat_titles($cat_titles) {
	
	$array = array();
	$cat_titles = explode('<~~>', $cat_titles);
	foreach($cat_titles as $cat_title) {
		$cat_title = explode('<~>', $cat_title);
		if($cat_title[1]) {
			$array[ $cat_title[0] ][] = $cat_title[1];
		} else if( !$array[ $cat_title[0] ] ) {
			$array[ $cat_title[0] ] = array();
		}
	}
	
	return $array;
}
function style_cat_titles($cat_titles, $html = false) {
	
	if( !is_array($cat_titles)) {
		$cat_titles = explode_cat_titles($cat_titles);
	}
	
	$return = '';
	
	foreach($cat_titles as $k=>$v) {
		$return .= ($html) ? "<b>$k</b>\r\n" : "[$k]\r\n";
		if($v) {
			$return .= implode(' - ', $v)."\r\n";
		}
//		foreach($v as $kk=>$vv) {
//			$return .= "- $vv\r\n";
//		}
	}
	
	$return = trim($return);

	if($html) {
		return nl2br( $return );
	} else {
		return $return;
	}
}

function style_news_resellers($_resellers, $resellers, $html = false)
{
	return _style_group_ids($_resellers, $resellers, $html);
}
function style_documents_resellers($_resellers, $resellers, $html = false)
{
	return _style_group_ids($_resellers, $resellers, $html);
}
function style_gallery_resellers($_resellers, $resellers, $html = false)
{
	return _style_group_ids($_resellers, $resellers, $html);
}
function style_videos_resellers($_resellers, $resellers, $html = false)
{
	return _style_group_ids($_resellers, $resellers, $html);
}
function style_students_resellers($_resellers, $resellers, $html = false) {
	return _style_group_ids($_resellers, $resellers, $html);
}
function style_news_students($_students, $html = false) {
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


function setUpdatedRow($table, $id, $action = '') {
	if( !setUpdatedTable($table, $action ) ) {
		return false;
	}

	$deleted = ($action=='delete') ? " , `status`='deleted' " : '';
	$deleted = '';
	mysql_query ("REPLACE INTO data_updates SET
		`time` = '".time()."'
		, `table` = '$table'
		, `id`='$id'
		$deleted
	");
//	mysql_query ("UPDATE data_sync SET `updated` = '1' WHERE `table` = '$table' AND `id`='$id' "); //  LIMIT 1
}

function setUpdatedRowSql($table, $sql, $action = '') {
	if( !setUpdatedTable($table, $action ) ) {
		return false;
	}

	$deleted1 = ($action=='delete') ? " , `status` " : '';
	$deleted2 = ($action=='delete') ? " , 'deleted' " : '';
	$deleted1 = '';
	$deleted2 = '';

	mysql_query ("REPLACE INTO data_updates (`time`, `table`, `id` $deleted1)
		SELECT '".time()."', '$table', id $deleted2 FROM `$table` WHERE $sql
	");
//	mysql_query ("UPDATE data_sync SET `updated` = '1' WHERE `table` = '$table' AND `id` IN ( SELECT `id` FROM `$table` WHERE $sql ) ");
}

function setUpdatedTable($table, $action ) {
	switch($table) {
		case 'news':
		case 'documents':
		case 'gallery':
		case 'videos':
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

