<?php 


function setSectionCategories($school_id, $section, $ids) {
	$ids = (array) $ids;
	$school_id = intval($school_id);
	switch($section) {
		case 'news_category':
		case 'documents_category':
			$table = $section.'_index';

			mysql_query("DELETE FROM `{$table}` WHERE school_id='{$school_id}'");
			foreach($ids as $id) {
				$category = getDataById($section, $id);
				if($category) {
					$sql = "INSERT INTO `{$table}` SET cat_id='{$category['id']}', school_id='{$school_id}' ";
					mysql_query($sql);
				}
			}
			return true;
	}
	return false;
}
function isSectionCategory($school_id, $section, $id) {
	$id = intval($id);
	$school_id = intval($school_id);
	switch($section) {
		case 'news_category':
		case 'documents_category':
			$table = $section.'_index';
			$category = getDataById($section, $id);
			if($category) {
				$sql = "SELECT * FROM `{$table}` WHERE cat_id='{$category['id']}', school_id='{$school_id}' ";
				$q = mysql_query($sql);
				if( $q && mysql_num_rows($q)) {
					return true;
				}
			}
	}
	return false;
}

function getSectionsCategoriesFromIndex($section, $school_ids) {
	$categories = array();
	$where = (is_numeric($school_ids)) ? " t2.school_id = '".intval($school_ids)."' " : " t2.school_id IN( {$school_ids} ) ";
	switch($section) {
		case 'news_category':
		case 'documents_category':
			$table = $section.'_index';
			$sql = "SELECT t1.*, t2.school_id
				FROM `{$section}` as t1
				INNER JOIN `{$table}` as t2 ON t2.cat_id=t1.id 
				WHERE {$where}
				ORDER BY t1.rank DESC
				";
			$q = mysql_query($sql);
			if( $q && mysql_num_rows($q)) {
				while($row = mysql_fetch_assoc($q)) {
					$categories[ $row['id'] ] = array(
						'id' => $row['id'],
						'title' => $row['title'],
						'school_id' => $row['school_id'],
					);;
				}
			}
	}
	return $categories;
}
function getSectionsCategories( $table, $school_ids) {
	
	$categories = array();

	$sql = "SELECT `{$table}`.* 
		FROM `{$table}` 
		WHERE school_id IN( {$school_ids} )
		ORDER BY `{$table}`.rank DESC";
	
	$q = mysql_query( $sql );
	if( $q && mysql_num_rows($q ) ) {
		while( $row = mysql_fetch_assoc( $q )) {
			$categories[$row['id'] ] = array(
				'id' => $row['id'],
				'title' => $row['title'],
				'school_id' => $row['school_id'],
			);
		}
	}
	
	return $categories;
}


function parse_youtube_url( $url, $onlyID = false ) {
	
	$link = '';
	$video_id = '';
	
	if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
	    $video_id = $match[1];
	    $link = 'http://www.youtube.com/watch?v=' . $video_id;
	}
	if( $onlyID ) {
		return $video_id;
	}
	return $link;
}

