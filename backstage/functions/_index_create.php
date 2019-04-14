<?php 

if( defined('index_table')) {
	if( !$index_id ) {
		if($action == 'addexe' || $isAddAction) {
			$index_id = mysql_insert_id();
		} else {
			$index_id = intval( $ids[$i] );
		}
	}
	
	$IndeX = 0;
	if( mysql_query("DELETE FROM `".index_table."` WHERE index_id='{$index_id}' ") ) {
		$Index = $index[$i];
		if(!is_array($Index)) {
			$Index = array();
		}
		foreach($Index as $cat=>$catIndex) {

			if($catIndex=='yes') {
				$IndeX++;
				
				
				$sql= "INSERT INTO `".index_table."` SET
					`index_id`='".sqlencode(trime( $index_id ))."'
					, `cat_id`='".sqlencode(trime( $cat ))."'
					";

				if( defined('index_region')) {
					$_region_id = intval( $region_id[ $i ] );
					$sql .= " , `region_id`='".sqlencode(trime( $_region_id ))."' ";
				}
				
				$qq = mysql_query( $sql );
				if(!$qq) {
					$warningMsg[-1] = 'Some records faced problems while indexing it\'s categories (inserting)!';
				}
			}
		}

//		if( !$IndeX ) {
//			$Errors[] = "Missing Categories!!";
//		}
	} else {
		$warningMsg[-1] = 'Some records faced problems while indexing it\'s categories!';
//		$warningMsg[$j] = mysql_error();
//		$oldrecord[$j]=$i;
//		$j++;
//		$flag=0;
//die( mysql_error() );
	}
}