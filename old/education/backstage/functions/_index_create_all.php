<?php 

if( !function_exists('get__classes')) {
	
	function get__classes( $id ) {
		
		global $Admin;
	
		if( isSchool ) {
			$data = get_school_classes( isSchool );
		}
		else if( isTeacher ) {
			$data = get_school_classes( $Admin['school_id'] );
		}
		else if( isAdmin ) {
			$data = get_school_classes( $id );
		}
		else {
			$data = array();
		}
		
		return $data;
	}
}

if( defined('index_table') ) {

	if( !$index_id ) {
		if($action == 'addexe' || $isAddAction) {
			$index_id = mysql_insert_id();
		} else {
			$index_id = intval( $ids[$i] );
		}
	}
	
	$index_id = intval( $index_id );
	
	if( ! mysql_query("DELETE FROM `".index_table."` WHERE index_id='{$index_id}' ") ) {
		$warningMsg[-1] = 'Unable to update classes indexing!';
	}
	else if( defined('index_students_table') && ! mysql_query("DELETE FROM `".index_students_table."` WHERE `".index_students_field."`='{$index_id}'  ") ) {
		$warningMsg[-1] = 'Unable to update students indexing!';
	}
	else {
//		$indexClasses = $class_ids[$i];
		$indexClasses = $class_ids[$i];
		$indexStudents = $student_ids[$i];
	
		if( !is_array( $indexClasses )) {
			$indexClasses = array();
		}
		if( !is_array( $indexStudents )) {
			$indexStudents = array();
		}
		
		$_allClasses = ( in_array('-1', $indexClasses)) ? true : false;
		$_allStudents = (defined('index_students_table') && in_array('-1', $indexStudents)) ? true : false;
		
		if( $_allClasses ) {
		
			$sql = "INSERT INTO `".index_table."` SET
				`index_id`='".sqlencode(trime( $index_id ))."'
				, `class_id`='-1'
				";
			$qq = mysql_query( $sql );
			if(!$qq) {
				$warningMsg[-2] = 'Some records faced problems while indexing it\'s students!';
			}
		}
		
		if( defined('index_students_table') && $_allStudents ) {
		
			$sql= "INSERT INTO `".index_students_table."` SET
				`".index_students_field."`='".sqlencode(trime( $index_id ))."'
				, `student_id`='-1'
				";
			$qq = mysql_query( $sql );
			if(!$qq) {
				$warningMsg[-3] = 'Some records faced problems while indexing it\'s students!';
			}
		}

		$_classes = get__classes( $school_id[$i] );
//print_r($_classes);
//print_r($indexClasses);
		foreach($_classes as $_class ) {
			if( !in_array($_class['id'], $indexClasses) ) {
				continue;
			}

			$sql = "INSERT INTO `".index_table."` SET
				`index_id`='".sqlencode(trime( $index_id ))."'
				, `class_id`='".sqlencode(trime( $_class['id'] ))."'
				";
			$qq = mysql_query( $sql );
			if(!$qq) {
				$warningMsg[-2] = 'Some records faced problems while indexing it\'s students!';
			}

			if( !defined('index_students_table') ) {
				continue;
			}
			
			if( isSchool ) {
				$_limitation = " AND school_id='".isSchool."' ";
			}
			else if( isAdmin ) {
				$_limitation = "";
			}
			else {
				$_limitation = " AND FALSE ";
			}
	
			$_query = "SELECT id, title
				FROM students 
				WHERE class_id= '{$_class['id']}' {$_limitation} ";
			$qqq = mysql_query( $_query );
			
			if( $qqq && mysql_num_rows( $qqq )) {
				while($r = mysql_fetch_assoc( $qqq )) {
					if(in_array($r['id'], $indexStudents)) {
					
						$sql= "INSERT INTO `".index_students_table."` SET
							`".index_students_field."`='".sqlencode(trime( $index_id ))."'
							, `student_id`='".sqlencode(trime( $r['id'] ))."'
							";
						$qq = mysql_query( $sql );
						if(!$qq) {
							$warningMsg[-3] = 'Some records faced problems while indexing it\'s students!';
						}
					}
				}
			}
		}
	}
}
