<?php 

if( defined('index_table')) {

	if( !$index_id ) {
		if($action == 'addexe' || $isAddAction) {
			$index_id = mysql_insert_id();
		} else {
			$index_id = intval( $ids[$i] );
		}
	}

	if( isSchool ) {
		$indexClasses = get_school_classes( $Admin['school_id'], true);
	}
	else if( isTeacher ) {
		$indexClasses = get_school_classes( $Admin['school_id'], true);
	}
	else if( isAdmin ) {
		$indexClasses = get_school_classes( $school_id[$i], true);
	}
	else if( $School ) {
		$indexClasses = array();
	}
//var_dump($indexClasses);
	$indexClassesIDs = array_keys( $indexClasses );
	
	$delSQL = "DELETE FROM `".index_table."` WHERE index_id='{$index_id}' ";
	$delQuery = mysql_query( $delSQL );
	
//	if( $indexClassesIDs ) {
//		$delSQL = "DELETE FROM `".index_table."` WHERE index_id='{$index_id}' AND class_id IN( ". implode(',', $indexClassesIDs)." ) ";
//		$delQuery = mysql_query( $delSQL );
//	
////		var_dump(  "$delSQL " . mysql_error() );
////		var_dump(  $delQuery );
//	}
//	else {
//		$delQuery = NULL;
//	}
	
//	if( $indexClassesIDs && $delQuery ) {
//	if( $indexClassesIDs && $delQuery !== false ) {
	if( $indexClassesIDs ) {

		$Index = $_POST['class_ids'][$i];
//		if( is_array($_POST['class_id']) ) {
//			$Index = $_POST['class_id'][$i];
//		}
//		else {
//			$Index = $_POST['class_ids'][$i];
//		}
//		$Index = $index[$i];
//var_dump($_POST);
//var_dump($Index);
		if(!is_array($Index)) {
			$Index = array();
		}
//var_dump( $indexClasses );
//var_dump( $_POST );
//var_dump( $Index );

		foreach($Index as $classID) {
			
			if( $indexClasses[ $classID ] ) {
				$sqlIndex= "INSERT INTO `".index_table."` SET
					`index_id`='".sqlencode(trime( $index_id ))."'
					, `class_id`='".sqlencode(trime( $classID ))."'
					";
//			echo $sqlIndex;
				$qIndex = mysql_query( $sqlIndex );
				if(!$qIndex) {
					$warningMsg[-1] = 'Some records faced problems while indexing it\'s classes (inserting)!';
//die( mysql_error() );
				}
			}
		}
	} 
	else {
//		$warningMsg[-1] = 'Some records faced problems while indexing it\'s classes!';
//		$warningMsg[$j] = mysql_error();
//		$oldrecord[$j]=$i;
//		$j++;
//		$flag=0;
//die( mysql_error() );
	}
}