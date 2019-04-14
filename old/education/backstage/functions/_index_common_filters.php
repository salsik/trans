<?php

$Class = array();
$Classes = array();

$School = array();
$Schools = array();

if( $keyword ) {
	$queryStr .= "keyword=$keyword&";
}

switch($_GET['status']) {
	case 'active':
	case 'inactive':
		$queryStr .= "status={$_GET['status']}&";
		break;
	default:
		$_GET['status'] == '';
		break;
}

if( isAdmin ) {
	$School = getDataByID('schools', $_GET['school_id']);
	if( $School ) {
		$queryStr .= "school_id={$School['id']}&";
	}

	if($action=='add') {
		$q = mysql_query("SELECT schools.* FROM `schools` WHERE schools.status='active' ORDER BY schools.title ASC");
	} else {
		$q = mysql_query("SELECT schools.* FROM `schools` ORDER BY schools.title ASC");
	}
	if( $q && mysql_num_rows($q ) )
	{
		while( $row = mysql_fetch_assoc( $q ))
		{
			$Schools[ $row['id'] ] = $row;
		}
	}
}


$_GET['class_id'] = intval( $_GET['class_id']);

if( $_GET['class_id'] > 0 ) {
	if( isSchool ) {
		$Class = getDataByID('classes', $_GET['class_id'], " classes.school_id='".$Admin['school_id']."' ");
	}
	else if( isTeacher ) {
		$Class = getDataByID('classes', $_GET['class_id'], " classes.school_id='".$Admin['school_id']."' ");
	}
	else if( $School ) {
		$Class = getDataByID('classes', $_GET['class_id'], " classes.school_id='".$School['id']."' ");
	}
}

if( isSchool ) {
	$Classes = get_school_classes( $Admin['school_id'], true);
}
else if( isTeacher ) {
	$Classes = get_school_classes( $Admin['school_id'], true);
}
else if( $School ) {
	$Classes = get_school_classes( $School['id'], true);
}