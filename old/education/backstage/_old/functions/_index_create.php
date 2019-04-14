<?php 

if( defined('index_table')) {
	if( !$index_id ) {
		if($action == 'addexe' || $isAddAction) {
			$index_id = mysql_insert_id();
		} else {
			$index_id = intval( $ids[$i] );
		}
	}
	
	if( mysql_query("DELETE FROM `".index_table."` WHERE index_id='{$index_id}' ") ) {
		$Index = $index[$i];
		if(!is_array($Index)) {
			$Index = array();
		}
		foreach($Index as $cat=>$catIndex) {
			if(!is_array($catIndex)) {
				$catIndex = array();
			}
			foreach($catIndex as $sub=>$subIndex) {
				if($subIndex=='yes') {
					$sql= "INSERT INTO `".index_table."` SET
						`index_id`='".sqlencode(trime( $index_id ))."'
						, `cat_id`='".sqlencode(trime( $cat ))."'
						, `sub_id`='".sqlencode(trime( $sub ))."'
						";
					$qq = mysql_query( $sql );
					if(!$qq) {
						$warningMsg[-1] = 'Some records faced problems while indexing it\'s classes (inserting)!';
					}
				}
			}
		}
	} else {
		$warningMsg[-1] = 'Some records faced problems while indexing it\'s classes!';
//		$warningMsg[$j] = mysql_error();
//		$oldrecord[$j]=$i;
//		$j++;
//		$flag=0;
//die( mysql_error() );
	}
}