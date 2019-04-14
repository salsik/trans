<?php 

if( !function_exists('get__classes')) {
	
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
}

if( defined('index_table')) {
	
//	$indexClasses = $class_ids[$i];
	$indexClasses = $class_ids[$i];
	$indexStudents = $student_ids[$i];
	
	$myClasses = array();

	if( !is_array( $indexClasses )) {
		$indexClasses = array();
	}
	if( !is_array( $indexStudents )) {
		$indexStudents = array();
	}
	
	$_allClasses = ( in_array('-1', $indexClasses)) ? true : false;

	if( isAdmin && $school_id[$i] == -1) {
		// Ignore all
	}
	else {
		if( !$_allClasses ) {
		
			$_allStudents = (defined('index_students_table') && in_array('-1', $indexStudents)) ? true : false;
		
			$_classes = get__classes( $school_id[$i] );
	
			$classesCount = 0;
			foreach($_classes as $_class ) {
				if( !in_array($_class['id'], $indexClasses) ) {
					continue;
				}
		
				$classesCount++;
				
				$myClasses[] = $_class;
				
				if( $_allStudents ) {
					continue;
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
		
				$studentsCount = 0;
			
				$_query = "SELECT id, title
					FROM students 
					WHERE class_id= '{$_class['id']}' {$_limitation} ";
				$qqq = mysql_query( $_query );
				
				
				if( !$qqq ) {
					$Errors[] = "Unable to check students in selected class/classes";
//					$Errors[] = mysql_error();
				}
				else if( mysql_num_rows( $qqq )) {
					while($r = mysql_fetch_assoc( $qqq )) {
						if(in_array($r['id'], $indexStudents)) {
							$studentsCount++;
						}
					}
				}
				
				if( !$studentsCount ) {
					$Errors[] = "You should select students in \"{$_class['title']}\".";
				}
			}
			if( !$classesCount ) {
				$Errors[] = "You should select classes.";
			}
		}
	

		do {
			if( $Errors ) {
				break;
			}
	
			if( !defined('index_table_competitions')) {
				break;
			}
	
			$sqlCompetition = (array) $sqlCompetition;
			if( !$sqlCompetition['school_id'] || !$sqlCompetition['month'] || !$sqlCompetition['year'] ) {
				break;
			}
			
			$sqlCompetition['school_id'] = intval( $sqlCompetition['school_id'] );
			$sqlCompetition['month'] = intval( $sqlCompetition['month'] );
			$sqlCompetition['year'] = intval( $sqlCompetition['year'] );
	
			$editID = ($action == 'addexe') ? 0 : intval( $ids[$i] );
			$_checkSql = ($action == 'editexe') ? " AND id <> '".intval( $ids[$i] )."' " : '';
			
			$query = "SELECT * FROM `competitions_classes_index` WHERE index_id IN (
				SELECT id FROM `competitions` 
					WHERE school_id = '{$sqlCompetition['school_id']}' 
					AND month = '{$sqlCompetition['month']}' 
					AND year = '{$sqlCompetition['year']}' 
					{$_checkSql}
			) ";
		
			if( $_allClasses ) {
				
				$_query = "{$query} LIMIT 1";
	
				$qq = mysql_query( $_query );
				if( $qq && mysql_num_rows($qq)) {
					$Errors[] = "Can't set \"All Classes\" for this quiz, you already have quiz for this month!";
					break;
				}
			}
			else {
				
				$_query = "{$query} AND class_id='-1' LIMIT 1";
	
				$qq = mysql_query( $_query );
				if( $qq && mysql_num_rows($qq)) {
					$Errors[] = "Can't save this quiz, you already have quiz set for \"All Classes\" in this month!";
					break;
				}
	
				foreach($myClasses as $_class) {
					$_query = "{$query} AND class_id = '{$_class['id']}' LIMIT 1";
	
					$qq = mysql_query( $_query );
					if( $qq && mysql_num_rows($qq)) {
						$Errors[] = "Can't set \"{$_class['title']}\" for this quiz, you already have quiz for this class in this month!";
						break;
					}
				}
			}
		} while( false );

	}
}
