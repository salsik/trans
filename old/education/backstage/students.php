<?php

define('THUMB_WIDTH', 482); // 218
define('THUMB_HEIGHT', 317); // 150

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include "_top.php";

$_print_passwords = false;

$students_limit_message = "You reached your plan's maximum allowed students (max: {$Admin['plan_students']})!!";

$limitation = '';
$limitationImport = '';
if(isSchool) {
	$limitation = " AND school_id='".isSchool."' ";
	$limitationImport = " AND school_id='".isSchool."' ";
}
else if( !isAdmin ) {
	die();
}

$fieldsArray = array(
	'first_name', 'last_name', 'description', 'status', 'email', 'password', 
	'info_address', 'info_phone', 'info_mobile', 'info_email', 

	'info_father_name', 'info_father_phone', 'info_father_mobile', 'info_father_email', 
	'info_mother_name', 'info_mother_phone', 'info_mother_mobile', 'info_mother_email', 
);

$numberCheck = array(
	'info_phone' => "invalid student's phone number!",
	'info_mobile' => "invalid student's mobile number!",

	'info_father_phone' => "invalid father's phone number!",
	'info_father_mobile' => "invalid father's mobile number!",

	'info_mother_phone' => "invalid mother's phone number!",
	'info_mother_mobile' => "invalid mother's mobile number!",
);
$emailCheck = array(
	'info_email' => "invalid student's email address!",

	'info_father_email' => "invalid father's email address!",

	'info_mother_email' => "invalid mother's email address!",
);




$csvOptions = array(
	'delimiter' => ';',
	'fields' => array(
		'first_name' => 'First name',
		'last_name' => 'Last Name',
//		'email' => 'Email',
		'info_mobile' => 'Mobile (login)',
		'info_phone' => 'Phone',
		'info_email' => 'Email Address',
		'info_address' => 'Address',

		'info_father_name' => "Father's Name",
		'info_father_mobile' => "Father's Mobile (login)",
		'info_father_phone' => "Father's Phone",
		'info_father_email' => "Father's Email address",

		'info_mother_name' => "Mother's Name",
		'info_mother_mobile' => "Mother's Mobile (login)",
		'info_mother_phone' => "Mother's Phone",
		'info_mother_email' => "Mother's Email address",
	),
);



$queryStr = '';

include 'functions/_index_common_filters.php';

$_GET['import_id'] = intval($_GET['import_id']);
if( $_GET['import_id'] > 0 ) {
	$queryStr .= "import_id={$_GET['import_id']}&";
}
if( $_GET['status'] ) {
	$queryStr .= "status={$_GET['status']}&";
}
$_GET['class_id'] = intval($_GET['class_id']);
if( $_GET['class_id'] > 0 ) {
	$queryStr .= "class_id={$_GET['class_id']}&";
}
//$_GET['school_id'] = intval($_GET['school_id']);
//if( $_GET['school_id'] > 0 ) {
//	$queryStr .= "school_id={$_GET['school_id']}&";
//}




switch($action) {
	case 'import':
		$queryStr .= "action={$action}&";
}

//Define the maximum items while listine
$PageSize=10;
//Define the maximum items while adding
$max=10;

require_once('_lister.class.php');
$sortableLister = new lister("_sort_reverse.php?filename=".$filename."&table=students");

$rank=getfield(getHTTP('rank'),"rank","students");


if($School) {
	?><h4 class="alert_info">School: <?php echo $School['title']; ?></h4><?php 
}

if($Class) {
	?><h4 class="alert_info">Class: <?php echo $Class['title']; ?></h4><?php 
}

switch ($action):
	//case "down":
		case "up": //reverse
		$strSQL="select * from students WHERE rank='".($rank+1)."'" ;
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update students set rank='".($rank+1)."' WHERE rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update students set rank='".$rank."' WHERE id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from students WHERE rank >'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update students set rank='".($rank+1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update students set rank='".$rank."' where id='".$row->id."'";
					mysql_query($strSQLord);
				}
			}
		}
	break;
	//case "up":
		case "down": //reverse
		$strSQL="select * from students where rank='".($rank-1)."'";
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update students set rank='".($rank-1)."' where rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update students set rank='".$rank."' where id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from students where rank <'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update students set rank='".($rank-1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update students set rank='".$rank."' where id='".$row->id."'";
					mysql_query($strSQLord);
				}
			}
		}

	break;

	case "import":
		$showing = "import";
	break;
	case "importexe":
//	case 'importjob':
	case "importjobexe":

		if( $action == 'importexe') {
			include 'functions/csv_parser.php';
	
			$school_id = $_POST['school_id'];
			$password_keyword = $_POST['password_keyword'];
	
			$Errors = array();

			$class_id = intval( $_POST['class_id'] );
			
			if( isSchool ) {
				$schoolID = isSchool;
			}
			else {
				$_school = getDataByID('schools', $school_id);
				$schoolID = $_school['id'];
			}
			
			if( !isSchool && !$_school ) {
				$Errors[] = "Missing School!!";
			}
//			else if( empty($password_keyword) ) {
//				$Errors[] = "Missing Password Keyword!!";
//			} 

			
			$classes = get_school_classes( $schoolID , true);
			if( !$classes[ $class_id ] ) {
				$Errors[] = "Missing Class!!";
			}

			if( !isset($_FILES['import_file']['error']) )
			{
				$Errors[] = "Missing Import File!!";
			}
			else if($_FILES['import_file']['error'])
			{
				switch( $_FILES['import_file']['error'] ) {
					case '1':
						$Errors[] = "The uploaded file exceeds the upload_max_filesize!";
						break;
					case '2':
						$Errors[] = "The uploaded file exceeds the MAX_FILE_SIZE directive!";
						break;
					case '3':
						$Errors[] = "The uploaded file was only partially uploaded!";
						break;
					case '4':
						$Errors[] = "No file was uploaded!";
						break;
					case '6':
						$Errors[] = "Missing a temporary folder!";
						break;
					case '7':
						$Errors[] = "Failed to write file to disk!";
						break;
					case '8':
						$Errors[] = "A PHP extension stopped the file upload!";
						break;
					default:
						$Errors[] = "Error when upload the file!!";
						break;
				}
			}
			else if( strtolower( substr($_FILES['import_file']['name'], -4) ) != '.csv' )
			{
				$Errors[] = "Only .CSV files are allowed!!";
			}

			if( empty( $Errors )) {
				$sql = array();
				
				$sql['title']= " `title`='".sqlencode(trime( $_FILES['import_file']['name'] ))."', ";
				$sql['password_keyword']= " `password_keyword`='".sqlencode(trime( $password_keyword ))."', ";
	
				if( isSchool ) {
					$sql['school_id']= " `school_id`='".isSchool."', ";
					$sql['add_by_id']= " `add_by_id`='".isSchool."', ";
				} else {
					$sql['school_id']= " `school_id`='".$_school['id']."', ";
				}
	
				$strSQL = "students_import set " . implode('', $sql);
			
				$q=mysql_query("SELECT max(rank) as max FROM students_import");
				$r = mysql_fetch_object($q);
	
				$strSQL ="insert into ".$strSQL." date = '".date('Y-m-d')."', rank='".($r->max+1)."', time='".time()."' ";
				
				$q = mysql_query($strSQL);
				if( $q ) {
					$import_id = mysql_insert_id();
//					$msg="Record(s) imported successfully!!";
	
//					$showing = "import";
				} else {
					$Errors[] = 'Something went wrong!';
					$Errors[] = mysql_error();
					
//					$showing = "import";
				}
			}

			if( empty( $Errors )) {
				$CSVFileName = $_FILES['import_file']['tmp_name'];
				$CSVFileData = @file_get_contents($CSVFileName);
				
				$CSVFile = getRecordsFromCSV($CSVFileData, ';');
				
				if( !findTwoFieldsInFirstRow( $CSVFile[0] ) ) {
					$CSVFile = getRecordsFromCSV($CSVFileData, ',');
				
					if( !findTwoFieldsInFirstRow( $CSVFile[0] ) ) {
						$Errors[] = "Invalid CSV File format! no fields found in first record.";
					}
				}
			}

			if( $Errors ) {
				$errorMsg = array_shift($Errors);
				$showing = "import";
				break;
			}

			$Fields = $csvOptions['fields'];
			
			$rowCount = 0;
			foreach($CSVFile as $rowKey => $data)
			{
				$rowCount++;
				if( !array_filter($data) ) {
					continue;
				}

				$row = array();
				foreach($Fields as $k=>$v)
				{
					$row[$k] = array_shift($data);
				}

				$q = mysql_query("INSERT INTO students_import_rows SET 
					import_id = '$import_id'
					, title = '".mysql_real_escape_string(trime("{$row['first_name']} {$row['last_name']}"))."'
					, data = '".mysql_real_escape_string(json_encode($row))."'
					, row_id = '".sqlencode(trime( $rowCount ))."'
					, date = '".date('Y-m-d')."'
					, time = '".time()."'
				");
			}
				
//			$action = 'importjobexe-inner';
			$action = 'importjobexe';
			$_GET['import_id'] = $import_id;
	
//				ob_clean();
//				header("Location: {$filename}?{$queryStr}action=importjob&import_id={$import_id}");
//				exit;
		}
		
		$_doImport = false;

		if( $action == 'importjob' || $action == 'importjobexe') {
			$action = 'importjobexe';
			//Get needed data from the DB
			$import_id = intval( $_GET['import_id']);
			$strSQL="select * from students_import where id ='$import_id' $limitationImport LIMIT 1";
			$objRS=mysql_query($strSQL);
			if( !($objRS && mysql_num_rows($objRS))) {
				$errorMsg = "Selected import session not found!";
//				$errorMsg = mysql_error();
				$showing = "import";
				break;
			}
			$import = mysql_fetch_assoc($objRS);
//			$showing = "importjob";
			$showing = "import";
			
			$_doImport = true;
		}
		
		if( !$_doImport ) {
			break;
		}

		$i = 0;
		$import['class_id'] = $classes[ $class_id ]['id'];
		
		$objRS = mysql_query("SELECT * FROM students_import_rows WHERE import_id='{$import['id']}' ");
		if($objRS && mysql_num_rows($objRS)) {
			while($row = mysql_fetch_assoc($objRS)) {
				
				$err = '';
				$pass = $import['password_keyword'] . rand(111, 999);
				do{
					$data = @json_decode($row['data'], true);
					if( !is_array($data)) {
						$err = 'DATA field is invalid!!';
						break;
					}

					$sql = array();
					foreach($csvOptions['fields'] as $k=>$v) {
						$sql[$k] = " `$k`='".sqlencode(trime($data[ $k ]))."', ";
					}
					if( isSchool) {
						$sql['add_by_id']= " `add_by_id`='".isSchool."', ";
					}
				
					if(empty($data['info_mobile'])) {
						$sql['info_mobile']= " `info_mobile`=NULL, ";
					}
					
					$_full_name = $data['first_name'] .' ' . $data['last_name'];

					$sql['full_name']= " `full_name`='".sqlencode(trime( $_full_name ))."', ";
					$sql['title']= " `title`='".sqlencode(trime( $_full_name ))."', ";
					$sql['password']= " `password`='".md5( $pass )."', ";
					$sql['password_str'] = " `password_str`='".sqlencode(trime( $pass ))."', ";
					$sql['imported']= " `imported`='1', ";
					$sql['status'] = " `status`='active', ";
					
					$sql['school_id'] = " `school_id`='".sqlencode(trime( $import['school_id'] ))."', ";
					$sql['class_id'] = " `class_id`='".sqlencode(trime( $import['class_id'] ))."', ";
					
					$strSQL = "students set " . implode('', $sql);

					$Errors = array();
					
					if( empty( $data['first_name'] ) ) {
						$err = "Missing First Name!!";
						break;
					} 
					else if( empty( $data['last_name'] ) ) {
						$err = "Missing Last Name!!";
						break;
					} 
//					else if( !isemail( $data['email'] ) ) {
//						$err = "Missing or Invalid Student's Login E-mail!!";
//						break;
//					}
				
					foreach($numberCheck as $k=>$v) {
						
						$data[ $k ] = clearMobileNumberForLogin( $data[ $k ] );

						$val = $data[ $k ];
		
						if( !empty($val) && !is_numeric($val) ) {
							$err = $v;
							break;
						}
					}

					foreach($emailCheck as $k=>$v) {
						$val = $data[ $k ];
		
						if( !empty($val) && !isemail($val) ) {
							$err = $v;
							break;
						}
					}

					if( $err ) {
						break;
					}

					if( students_limit() ) {
						$err = $students_limit_message;
					}
					
					$q=mysql_query("SELECT max(rank) as max FROM students");
					$r = mysql_fetch_object($q);

					$strSQL ="insert into ".$strSQL." rank='".($r->max+1)."', time='".time()."' ";
				
					$q = mysql_query($strSQL);
					if( $q ) {
						$doc_id = mysql_insert_id();
						$isAddAction = true;

//						$index_id = $doc_id;
//						include('functions/_index_create_all.php');

					} else {
						if(mysql_errno() == 1062) {
							$err = 'Student\'s phone number already exists for other student! ';
							$err .= mysql_error();
						}
						else {
							$err = 'Something went wrong while inserting the student! ';
							$err .= mysql_error();
						}
					}
				} while(false);
				
				if($err) {
					@mysql_query("UPDATE students_import_rows SET 
						last_error='".sqlencode(trime( $err ))."'
					WHERE id='{$row['id']}' LIMIT 1");
					
					$link = "{$filename}?{$queryStr}action=import&import_id={$import['id']}&p={$p}";
					$errorMsg = 'Some records can\'t be imported!, for details you may review <a href="'.$link.'">import logs</a>.';
				} else {
					@mysql_query("DELETE FROM students_import_rows WHERE id='{$row['id']}' LIMIT 1");
				}
			} // END While
		}
		
		if( $errorMsg ) {
			$showing = "importjob";
			$showing = "import";
			break;
		}

		$msg="Record(s) importeded successfully!!";
		$showing = "";
		$showing = "import";
	break;

//	case "importjob":
//	case "importjobexe":
//		//Get needed data from the DB
//		$import_id = intval( $_GET['import_id']);
//		$strSQL="select * from students_import where id ='$import_id' $limitationImport LIMIT 1";
//		$objRS=mysql_query($strSQL);
//		if( !($objRS && mysql_num_rows($objRS))) {
//			$errorMsg = "Selected import session not found!";
////			$errorMsg = mysql_error();
//			$showing = "import";
//			break;
//		}
//		$import = mysql_fetch_assoc($objRS);
//		$showing = "importjob";
//		
//		if( $action == 'importjob') {
//			break;
//		}
//		
//		$action = 'importjob';
//		
//		$i = 0;
//		$status = $_POST['status'];
//		$class_id = $_POST['class_id'];
//		
//		$classes = get_school_classes( $import['school_id'] , true);
//		if( !$classes[ $class_id[0] ] ) {
//			$errorMsg = "Missing Class!";
//			break;
//		}
//		$import['class_id'] = $classes[ $class_id[0] ]['id'];
//		
//		$objRS = mysql_query("SELECT * FROM students_import_rows WHERE import_id='{$import['id']}' ");
//		if($objRS && mysql_num_rows($objRS)) {
//			while($row = mysql_fetch_assoc($objRS)) {
//				
//				$err = '';
//				$pass = $import['password_keyword'] . rand(111, 999);
//				do{
//					$data = @json_decode($row['data'], true);
//					if( !is_array($data)) {
//						$err = 'DATA field is invalid!!';
//						break;
//					}
//
//					$sql = array();
//					foreach($csvOptions['fields'] as $k=>$v) {
//						$sql[$k] = " `$k`='".sqlencode(trime($data[ $k ]))."', ";
//					}
//					if( isSchool) {
//						$sql['add_by_id']= " `add_by_id`='".isSchool."', ";
//					}
//					
//					$_full_name = $data['first_name'] .' ' . $data['last_name'];
//
//					$sql['full_name']= " `full_name`='".sqlencode(trime( $_full_name ))."', ";
//					$sql['title']= " `title`='".sqlencode(trime( $_full_name ))."', ";
//					$sql['password']= " `password`='".md5( $pass )."', ";
//					$sql['password_str'] = " `password_str`='".sqlencode(trime( $pass ))."', ";
//					$sql['imported']= " `imported`='1', ";
//					$sql['status'] = " `status`='".sqlencode(trime( $status[0] ))."', ";
//					
//					$sql['school_id'] = " `school_id`='".sqlencode(trime( $import['school_id'] ))."', ";
//					$sql['class_id'] = " `class_id`='".sqlencode(trime( $import['class_id'] ))."', ";
//					
//					$strSQL = "students set " . implode('', $sql);
//
//					$Errors = array();
//					
//					if( empty( $data['first_name'] ) ) {
//						$err = "Missing First Name!!";
//						break;
//					} 
//					else if( empty( $data['last_name'] ) ) {
//						$err = "Missing Last Name!!";
//						break;
//					} 
////					else if( !isemail( $data['email'] ) ) {
////						$err = "Missing or Invalid Student's Login E-mail!!";
////						break;
////					}
//				
//					foreach($numberCheck as $k=>$v) {
//						$val = $data[ $k ];
//		
//						if( !empty($val) && !is_numeric($val) ) {
//							$err = $v;
//							break;
//						}
//					}
//					foreach($emailCheck as $k=>$v) {
//						$val = $data[ $k ];
//		
//						if( !empty($val) && !isemail($val) ) {
//							$err = $v;
//							break;
//						}
//					}
//
//					if( $err ) {
//						break;
//					}
//
//					if( students_limit() ) {
//						$err = $students_limit_message;
//					}
//					
//					$q=mysql_query("SELECT max(rank) as max FROM students");
//					$r = mysql_fetch_object($q);
//
//					$strSQL ="insert into ".$strSQL." rank='".($r->max+1)."', time='".time()."' ";
//				
//					$q = mysql_query($strSQL);
//					if( $q ) {
//						$doc_id = mysql_insert_id();
//						$isAddAction = true;
//
////						$index_id = $doc_id;
////						include('functions/_index_create_all.php');
//
//					} else {
//						$err = 'Something went wrong while inserting the student! ';
//						$err .= mysql_error();
//					}
//				} while(false);
//				
//				if($err) {
//					@mysql_query("UPDATE students_import_rows SET 
//						last_error='".sqlencode(trime( $err ))."'
//					WHERE id='{$row['id']}' LIMIT 1");
//					
//					$link = "{$filename}?{$queryStr}action=import&import_id={$import['id']}&p={$p}";
//					$errorMsg = 'Some records can\'t be imported!, for details you may review <a href="'.$link.'">import logs</a>.';
//				} else {
//					@mysql_query("DELETE FROM students_import_rows WHERE id='{$row['id']}' LIMIT 1");
//				}
//			} // END While
//		}
//		
//		if( $errorMsg ) {
//			$showing = "importjob";
//			break;
//		}
//
//		$msg="Record(s) importeded successfully!!";
//		$showing = "";
//
//		break;
	case "add":
		$showing = "record";
	break;
	case "edit":
		//Get needed data from the DB
		$strSQL="select * from students where id IN(".implode(',',$ids).") $limitation order by rank DESC";
		$objRS=mysql_query($strSQL);
		$i=0;
		while ($row=mysql_fetch_object($objRS)){
		
			foreach($fieldsArray as $field) {
				${$field}[$i] = $row->$field;
			}
			$school_id[ $i ] = $row->school_id;
			$class_id[ $i ] = $row->class_id;
		
			$file1[$i] = $row->image;
			//Set the OLD records Positions's depending on the QUERY result
			$oldrecord[$i]=$i;
			$ids[$i] = $row->id;
			$i++;
		}
		//Set the maximum items to the maximum number of "to edit" records
		$max=$i;
		$showing = "record";
	break;
	case "editexe":
	case "addexe":
		//Get new data from the FORM
		foreach($fieldsArray as $field) {
			${$field} = $_POST[$field];
		}
		
		$school_id = $_POST['school_id'];
		$class_id = $_POST['class_id'];
		$confirm_password = $_POST['confirm_password'];

		//Counter for errors array according to the number of records.
		$j=0;
		//Array of "Errored" records.
		$oldrecord=array();
		//Start the loop over the records
		for($i=0;$i<sizeof($ids);$i++){
			$file1[$i] = $_POST['file1'.$i];
			//Set the flag to one in order to verify the conditions later
			$flag=1;
			
			$Errors = array();

			if( isSchool ) {
				$schoolid = isSchool;
			}
			else {
				$_school = getDataByID('schools', $school_id[$i]);
				if( $_school ) {
					$schoolid = $_school['id'];
				}
				else {
					$schoolid = 0;
				}
			}
			
			$sql = array();
			foreach($fieldsArray as $field) {
				$sql[$field] = " `$field`='".sqlencode(trime(${$field}[$i]))."', ";
			}
			$_full_name = $first_name[$i] .' ' . $last_name[$i];
			$sql['full_name']= " `full_name`='".sqlencode(trime( $_full_name ))."', ";
			$sql['title']= " `title`='".sqlencode(trime( $_full_name ))."', ";
			
			if( $action == 'addexe' || $password[$i]) {
				$sql['password']= " `password`='".md5($password[$i])."', ";
				$sql['password_str']= " `password_str`='', ";
				if($confirm_password[$i] != $password[$i]) {
					$Errors[] = "Missmatch passwords";
				}
			} else {
				unset( $sql['password'] );
			}
			
			if(empty($info_mobile[$i])) {
				$sql['info_mobile']= " `info_mobile`=NULL, ";
			}
			
			$strSQL = "students set " . implode('', $sql);

			if( $action == 'addexe' && isSchool) {
				$strSQL .= " `add_by_id`='".isSchool."', ";
			}
			
			if( $schoolid ) {
				$strSQL .= " `school_id`='{$schoolid}', ";
				
				$classes = get_school_classes($schoolid, true);

				if( $classes[ $class_id[$i] ] ) {
					$cid = $classes[ $class_id[$i] ]['id'];
					
					$strSQL .= " `class_id`='{$cid}', ";
				}
				else {
					$Errors[] = "Missing Class!!";
				}
			}
			
			
			if( empty($first_name[$i]) ) {
				$Errors[] = "Missing First Name!!";
			} 
			else if( empty($last_name[$i]) ) {
				$Errors[] = "Missing Last Name!!";
			} 
//			else if( !isemail($email[$i]) ) {
//				$Errors[] = "Missing or Invalid Student's Login E-mail!!";
//			} 
//			else if( $action == 'addexe' && empty($password[$i]) ) {
//				$Errors[] = "Missing Student's Password!!";
//			}
			else if( !$schoolid ) {
				$Errors[] = "Missing School!!";
			} 
//			else if( !empty($info_email[$i]) && !isemail($info_email[$i]) ) {
//				$Errors[] = "Invalid Student's E-mail!!";
//			}

			foreach($numberCheck as $k=>$v) {
				$val = $$k;
				$val = $val[$i];

				if( !empty($val) && !is_numeric($val) ) {
					$Errors[] = $v;
				}
			}
			foreach($emailCheck as $k=>$v) {
				$val = $$k;
				$val = $val[$i];

				if( !empty($val) && !isemail($val) ) {
					$Errors[] = $v;
				}
			}

			if( $action == 'addexe' && students_limit() ) {
				$Errors[] = $students_limit_message;
			}

			if ($action=="editexe"):
				//Conditions and Queries while editing
				if ( $Errors ){
					//Set the error message
					$errorMsg[$j]=array_shift($Errors);
					//Set the OLD Bad records Positions's depending on the POST result
					$oldrecord[$j]=$i;
					$j++;
					//Set the flag to zero if conditions not verified to avoid executing the query
					$flag=0;
				}else {
					if ( $result=file_upload('image','file1'.$i,'../uploads/students/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){

							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/students/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
							$Rimage->save('../uploads/students/thumb/'.$file1[$i]);							
						}						 
					}
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						$strSQL ="update ".$strSQL." image='".sqlencode(trime($file1[$i]))."' where id='".$ids[$i]."' $limitation ";
					}
				}
			else:
				//Conditions and Queries while adding
				if (empty($first_name[$i])){
					//Do nothing for empty records but Set the flag to zero
					$flag=0;
				}else if ( $Errors ){
					//Set the error message
					$errorMsg[$j]=array_shift($Errors);
					//Set the OLD Bad records Positions's depending on the POST result
					$oldrecord[$j]=$i;
					$j++;
					//Set the flag to zero if conditions not verified to avoid executing the query
					$flag=0;
				}else{
					if ( $result=file_upload('image','file1'.$i,'../uploads/students/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){
							
							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/students/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT );
							$Rimage->save('../uploads/students/thumb/'.$file1[$i]);							
						}							 
				    }
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						
						$q=mysql_query("SELECT max(rank) as max FROM students");
						$r = mysql_fetch_object($q);

						$strSQL ="insert into ".$strSQL." image='".sqlencode(trime($file1[$i]))."', rank='".($r->max+1)."', time='".time()."' ";
					}
				}
			endif;
			if($flag){
				$q = mysql_query($strSQL);
				if( $q ) {
					if($action == 'addexe') {
						$doc_id = mysql_insert_id();
					} else {
						$doc_id = intval( $ids[$i] );
					}

//					$index_id = $doc_id;
//					include('functions/_index_create_all.php');
				
				} else {
					if(mysql_errno() == 1062) {
						$errorMsg[$j] = 'Student\'s phone number already exists for other student! ';
					}
					else {
						$errorMsg[$j] = 'Something went wrong!';
//						$errorMsg[$j] = mysql_error();
					}
					$oldrecord[$j]=$i;
					$j++;
					$flag=0;
				}
			}
		}
		$action = substr ($action,0,strlen($action)-3);
		//Test Conditions, if the not verified stay in the add FORM else go back to listing
//		if($j>0){
		if($errorMsg && array_filter($errorMsg)){
			//Set the maximum items to the maximum number of "Errored" records
			$max=$j;
			$showing = "record";
		}
		else
			$msg="Record(s) ".$action."ed successfully!!";
	break;
	case "delete":
		// TODO deleting Student by company?
		$strSQL="select * from students where id IN(".implode(',',$ids).") $limitation order by rank DESC";
		$objRS=mysql_query($strSQL);
		while ($row=mysql_fetch_object($objRS))
		{
			if($row->image != '') {
				@unlink('../uploads/students/'.$row->image);	
				@unlink('../uploads/students/thumb/'.$row->image);	
			}

			$strSQL="DELETE FROM students WHERE id = '{$row->id}' LIMIT 1";
		
			if(!mysql_query($strSQL) ) {
				if( empty($errorMsg) ) {
					$errorMsg="Some Records didn't affected!!";
				}
			}
			else {
				mysql_query("DELETE  FROM `".index_table."` WHERE index_id='{$row->id}' ");
			}
		}

		if(empty($errorMsg)) {
			$msg="Record(s) deleted successfully!!";
		}
	break;
endswitch;
?>



<?
switch ($showing):
	case "import":

		if( students_limit() ) {
			?><h4 class="alert_warning"><?php echo $students_limit_message; ?></h4><?php
		}
			
		include 'functions/_auto_complete.php';
?>
<style>
<!--
ul.levels, ol.levels, .levels ul, .levels ol {
	padding-left: 30px;
}
-->
</style>
		<?php if(!empty($msg)) { ?>
			<h4 class="alert_success"><?php echo $msg; ?></h4>
		<?php } else if(!empty($errorMsg)) { ?>
			<h4 class="alert_error"><?php echo $errorMsg; ?></h4>
		<?php } ?>
<script type="text/javascript">
<!--
	function createClassesSelectBox( Selector, Classes, Selected ) {

		var Element = $(Selector);
		Element.empty();

		$('<option value="" >-- Select --</option>').appendTo( Element );

		$.each(Classes, function(i, v){
			var option = $('<option />');

			if( v.id == Selected ) {
				option.prop('selected', true);
			}

			option.text(v.title);
			option.val(v.id);
			option.appendTo( Element );
		});
	}

<?php if(!isSchool) { ?>
	$(document).ready(function(){
		$(".school_id").change(function(){

			var id = $(this).val();

			if( !id ) {
				return ;
			}

			var box = $(this).parents('.school_class_filter:first');
			var loader = $('<div class="loader" ></div>');
			var classesBox = $('.class-select-box:first', box);

//			classesBox.hide();
//			alert( classesBox.attr('class') );

			box.append( loader );

			$.get('_get.php?from=classes&school_id='+id, function(response){
				response = jQuery.parseJSON(response);

				loader.remove();

				createClassesSelectBox(classesBox, response ); // , Selected
			});
		});
	});
<?php } ?>

//-->
</script>
<article class="module width_full">
<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>" method="post" name="myform" enctype="multipart/form-data">
<input type="hidden" name="action" value="importexe"> 
	<header><h3>Students: Import Records
		<input type="submit" value="Import" class="alt_btn">
		<input type="button" value="Cancel" onclick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>action=&p=<?=$p?>'">
		</h3></header>
		<div class="module_content inline">
				<fieldset>
					<table width="100%" border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td width="50%">
								<table width="100%" border="0" cellpadding="5" cellspacing="15">
									<tr>
										<td width="1"><label>Student's Import File</label></td>
										<td><input type="file" name="import_file" /></td>
									</tr>
								<?php if( false ) { ?>
									<tr>
										<td><label>Password Keyword</label></td>
										<td><input type="text" name="password_keyword" value="<? echo textencode($password_keyword); ?>" /></td>
									</tr>
								<?php } ?>
									<tr>
										<td><label>School/Class</label></td>
										<td>
								<span class="school_class_filter">
				<?php if( !isSchool ) { ?>
								<?php 
								
								$_index_school = 0;
								
								if( !isSchool ) { 
									?>
									<select name="school_id" class="school_id" >
										<option value="" >-- Select --</option>
										<?php 
											$_index_school = 0;
											$sid = nor($_REQUEST['school_id'], $School['id']);
											foreach ($Schools as $school) {
												$Selected = ( $school['id'] == $sid ) ? ' selected="selected" ' : '';
												if( $Selected ) {
													$_index_school = $school['id'];
												}
												?><option value="<?php echo $school['id'];?>" <?php echo $Selected; ?> ><?php echo $school['title'];?></option><?php 
											}
										?>
									</select>
									<?php 
								
								}
								?>
				<?php } ?>
									<select name="class_id" class="class-select-box class_id" ></select>
								</span>
							<?php 
								$_school_id = 0;
								if( isSchool ) { 
									$_school_id = isSchool;
								}
								else {
									$_school = getDataByID('schools', $_REQUEST['school_id']);
									$_school_id = $_school['id'];
								}
								
								if( $_school_id ) {
									$_classes = get_school_classes( $_school_id );
									
									?>
									<script type="text/javascript">
									createClassesSelectBox( 
										'.class_id',
										<?php echo json_encode( $_classes); ?> , 
										<?php echo intval( $class_id ); ?> 
										);
									</script>
									<?php 
								}
							
							?>
										</td>
									</tr>
								</table>
							</td>
							<td class="levels">
								<ul>
									<li>Only .CVS files are allowed to be uploaded.</li>
									<li>CVS delimiter is: <?php echo $csvOptions['delimiter']; ?></li>
									<li>CVS Fields should be in this order:
										<ol type="a">
									<?php foreach($csvOptions['fields'] as $k=>$v) { ?>
										<li><?php echo $v; ?></li>
									<?php } ?>
										</ol>
									</li>
								</ul>
							</td>
						</tr>
					</table>
				</fieldset>
				<div class="clear"></div>
		</div>
</form>

		<footer><h3>Imported Logs</h3></footer>

		<div class="module_content inline">
<?php 
	$importJobs = array();
	$import_id = intval( $_GET['import_id']);

	$strSQL="SELECT students_import.*, schools.title as school_title
		FROM students_import_rows, students_import 
			LEFT JOIN schools ON(schools.id = students_import.school_id)
		WHERE students_import.id = students_import_rows.import_id 
		$limitationImport 
		GROUP BY students_import.id
		ORDER BY time DESC";
	$objRS=mysql_query($strSQL);
	if( $objRS && mysql_num_rows($objRS) ) {
		while($row = mysql_fetch_assoc($objRS)) {
			$importJobs[ $row['id'] ] = $row;
		}
	}
?>
<div class="alert_browse">
	<form action="<? echo $filename; ?>" method="GET" >
	<?php 
		$SchoolID = $School['id'];
		$school = getDataByID('schools', $SchoolID);
	?>
		<input type="hidden" name="action" value="import">
		Imported File: <select name="import_id">
			<option value="" >== Select ==</option>
		<?php
			foreach($importJobs as $importJob ){
				$selected = selected($importJob['id'], $import_id);
				if( isSchool || $school) {
					$title = "{$importJob['title']} [{$importJob['date']}]";
				} else {
					$title = "{$importJob['school_title']} / {$importJob['title']} [{$importJob['date']}]";
				}
		?>
			<option value="<?php echo $importJob['id']; ?>" <?php echo $selected; ?>><?php echo $title; ?></option>
		<?php } ?>
		</select>
<?php if( !isSchool ) { ?>
		School: <span class="autocomplete" data-link="_get.php?from=schools">
			<input class="input_short auto_name" type="text" name="school_name" value="<?php echo trim( $school['title']); ?>" />
			<input class="input auto_id" type="hidden" name="school_id" value="<?php echo $school['id']; ?>" />
		</span>
<?php } ?>
		<input type="submit" value="Browse" />
	</form>
</div>

				<fieldset>
<?php 
	if( $importJobs[ $import_id ] )
	{
		$strSQL="SELECT * FROM students_import_rows WHERE import_id IN (".implode(',', array_keys($importJobs)).") AND import_id='$import_id' ";

		
		$objRS=mysql_query($strSQL);
		$total=@mysql_num_rows($objRS);
		$strSQL=makePages ($strSQL,$PageSize,$p, 'time DESC');
		$objRS=mysql_query($strSQL);
		$count_all = @mysql_num_rows($objRS);
?>
		<table class="tablesorter" cellspacing="0"> 
		<thead> 
			<tr>
    			<th>Row ID / Full Name</th>
    			<th>Last Error</th>
<!--    			<th>Import Session</th>-->
			</tr> 
		</thead> 
		<tbody id="trContainer"> 
	<?php if( $objRS && !mysql_num_rows($objRS)) { ?>
    <tr>
		<td colspan="10">All records imported!</td>
	</tr>
	<?php }?>
	<?php while ($row=mysql_fetch_assoc($objRS)){ ?>
    <tr>
		<td align="left"><?php echo $row['row_id']; ?> / <?php echo $row['title']; ?></td>
		<td align="left"><?php echo $row['last_error']; ?></td>
<!--		<td align="left"><a href="<?php echo $filename; ?>?<?php echo $queryStr; ?>action=importjob&import_id=<?php echo $row['import_id']; ?>">Import Session</a></td>-->
	</tr>
	<?php }?>
			</tbody> 
			</table>
<?php } ?>
				</fieldset>
				<div class="clear"></div>
		</div>
		<footer>
<?php if( $importJobs[ $import_id ] ) { ?>
			<div class="submit_link">
				<?=dispPages ($total,$PageSize,$p, $queryStr)?>
			</div>
<?php } ?>
		</footer>
</article>

<?php
		break;
	case "importjob":

		if( students_limit() ) {
			?><h4 class="alert_warning"><?php echo $students_limit_message; ?></h4><?php
		}
		
		
		$Count = 0;
		$q = mysql_query("SELECT count(*) as count FROM students_import_rows WHERE import_id='{$import['id']}' ");
		if($q && mysql_num_rows($q)) {
			$Count = mysql_result($q, 0, 0);
		}
		
		if( !$Count ) {
			?><h4 class="alert_warning">No records left to be imported in this session!!</h4><?php 
		}
?>
		<?php if(!empty($msg)) { ?>
			<h4 class="alert_success"><?php echo $msg; ?></h4>
		<?php } else if(!empty($errorMsg)) { ?>
			<h4 class="alert_error"><?php echo $errorMsg; ?></h4>
		<?php } ?>
<?php 
	$i = 0;
	$oldrecord = array(0);
?>
<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&import_id=<?php echo $import['id']; ?>" method="post" name="myform" enctype="multipart/form-data">
<input type="hidden" name="action" value="importjobexe"> 
<article class="module width_full">
	<header><h3>Students: Import Records
		<input type="submit" value="Import" class="alt_btn">
		<input type="button" value="Cancel" onclick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>action=import&import_id=<?php echo $import['id']; ?>&p=<?=$p?>'">
		</h3></header>
		<div class="module_content inline">
				<fieldset>
					<label>Import Info</label>
					<table width="60%" border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td>
								<label>File:
									<br /><?php echo $import['title']; ?>
								</label>
							</td>
						<?php if( false ) { ?>
							<td>
								<label>Password Keyword:
									<br /><?php echo $import['password_keyword']; ?>
								</label>
							</td>
						<?php } ?>
					<?php if( !isSchool ) { ?>
							<?php 
								$school = getDataByID('schools', $import['school_id']);
							?>
							<td>
								<label>School:
								<br /><?php echo $school['title']; ?>
								</label>
							</td>
					<?php } else { ?>
							<td>
								<label></label>
							</td>
					<?php } ?>
						</tr>
						
						<tr>
							<td>
								<label><?php echo $Count; ?> Records</label>
							</td>
							<td>
								<label></label>
							</td>
							<td>
								<label></label>
							</td>
						</tr>
					</table>
				</fieldset>
				<div class="clear"></div>

				<fieldset>
					<label>Student's Status</label>
					<select name="status[]">
							<option value="active" <?php echo selected($status[$oldrecord[$i]], 'active'); ?>> Active </option>
							<option value="" <?php echo selected($status[$oldrecord[$i]], '', isset($status[$oldrecord[$i]])); ?>> Inactive </option>
					</select>
				</fieldset>

				<fieldset>
					<label>Student's Class</label>
					<select name="class_id[]" >
						<option value="" >-- Select --</option>
						<?php 
							$_classes = get_school_classes( $import['school_id'] );
							foreach ($_classes as $_class) {
								$Selected = ( $_class['id'] == $class_id[$oldrecord[$i]] ) ? ' selected="selected" ' : '';
								?><option value="<?php echo $_class['id'];?>" <?php echo $Selected; ?> ><?php echo $_class['title'];?></option><?php 
							}
						?>
					</select>
				</fieldset>
		</div>
	<footer></footer>
</article>
</form>

<?php
		break;
	case "record":

		if( $action == 'add' && students_limit() ) {
			?><h4 class="alert_warning"><?php echo $students_limit_message; ?></h4><?php
		}
		
?>

<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>" method="post" name="myform" enctype="multipart/form-data">
<input type="hidden" name="action" value="<? echo $action; ?>exe"> 
<article class="module width_full">
	<header><h3>Students: <?php echo ucwords($action); ?> Record
		<input type="submit" value="Save" class="alt_btn">
		<input type="button" value="Cancel" onclick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>'">
		</h3></header>
<script type="text/javascript">
<!--
	function createClassesSelectBox( Selector, Classes, Selected ) {

		var Element = $(Selector);
		Element.empty();

		$('<option value="" >-- Select --</option>').appendTo( Element );

		$.each(Classes, function(i, v){
			var option = $('<option />');

			if( v.id == Selected ) {
				option.prop('selected', true);
			}

			option.text(v.title);
			option.val(v.id);
			option.appendTo( Element );
		});
	}

<?php if(!isSchool) { ?>
	$(document).ready(function(){
		$(".school_id").change(function(){

			var id = $(this).val();

			if( !id ) {
				return ;
			}

			var box = $(this).parents('.school_class_filter:first');
			var loader = $('<div class="loader" ></div>');
			var classesBox = $('.class-select-box:first', box);

//			classesBox.hide();
//			alert( classesBox.attr('class') );

			box.append( loader );

			$.get('_get.php?from=classes&school_id='+id, function(response){
				response = jQuery.parseJSON(response);

				loader.remove();

				createClassesSelectBox(classesBox, response ); // , Selected
			});
		});
	});
<?php } ?>

//-->
</script>
		<?php for($i=0;$i<$max;$i++){ ?>
		<input type="hidden" name="ids[]" value="<? echo $ids[$oldrecord[$i]]; ?>">
		<?php if(!empty($msg)) { ?>
			<h4 class="alert_success"><?php echo $msg; ?></h4>
		<?php } else if(!empty($errorMsg[$i])) { ?>
			<h4 class="alert_error"><?php echo $errorMsg[$i]; ?></h4>
		<?php } else if(!empty($warningMsg[$i])) { ?>
			<h4 class="alert_warning"><?php echo $warningMsg[$i]; ?></h4>
		<?php } ?>
		<div class="module_content inline">
				<fieldset>
					<label>Status</label>
					<select name="status[]">
							<option value="active" <?php echo selected($status[$oldrecord[$i]], 'active'); ?>> Active </option>
							<option value="" <?php echo selected($status[$oldrecord[$i]], '', isset($status[$oldrecord[$i]])); ?>> Inactive </option>
					</select>
				</fieldset>

				<fieldset>
					<label>Full Name</label>
					<table width="60%" border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td>
								<label>First Name
									<br /><input type="text" name="first_name[]" value="<? echo textencode($first_name[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label>Last Name
									<br /><input type="text" name="last_name[]" value="<? echo textencode($last_name[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label></label>
							</td>
						</tr>
					</table>
				</fieldset>

		<?php if( false ) {  ?>
				<fieldset>
					<label>Student's Login</label>
					<table width="60%" border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td>
								<label>E-mail (Login)
									<br /><input type="text" name="email[]" value="<? echo textencode($email[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label>Password
									<br /><input type="password" name="password[]"  />
								<?php if($action=='edit'){ ?>
									<br />Leave blank if you don't wish to change the password
								<?php } ?>
								</label>
							</td>
							<td>
								<label>Confirm Password
									<br /><input type="password" name="confirm_password[]"  />
								</label>
							</td> 
						</tr>
					</table>
				</fieldset>
			<?php } ?>

				<fieldset>
					<label>Student's Info</label>
					<table width="60%" border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td colspan="3">

								<span class="school_class_filter">
				<?php if( !isSchool ) { ?>
								<?php 
								
								$_index_school = 0;
								
								if( !isSchool ) { 
									?>
									School: <select name="school_id[]" class="school_id" >
										<option value="" >-- Select --</option>
										<?php 
											$_index_school = 0;
											$sid = nor($school_id[$oldrecord[$i]], $School['id']);
											foreach ($Schools as $school) {
												$Selected = ( $school['id'] == $sid ) ? ' selected="selected" ' : '';
												if( $Selected ) {
													$_index_school = $school['id'];
												}
												?><option value="<?php echo $school['id'];?>" <?php echo $Selected; ?> ><?php echo $school['title'];?></option><?php 
											}
										?>
									</select>
									<?php 
								
								}
								?>
				<?php } ?>
									Class: <select name="class_id[]" class="class-select-box class_id_<?php echo $i; ?>" ></select>
								</span>
							<?php 
								$_school_id = 0;
								if( isSchool ) { 
									$_school_id = isSchool;
								}
								else if( $_index_school ) { 
									$_school_id = $_index_school;
								}
								
								if( $_school_id ) {
									$_classes = get_school_classes( $_school_id );
									
									?>
									<script type="text/javascript">
									createClassesSelectBox( 
										'.class_id_<?php echo $i; ?>',
										<?php echo json_encode( $_classes); ?> , 
										<?php echo intval( $class_id[$oldrecord[$i]] ); ?> 
										);
									</script>
									<?php 
								}
							
							?>
							</td>
						</tr>
						<tr>
							<td>
								<label>Mobile (login)
									<br /><input type="text" name="info_mobile[]" value="<? echo textencode($info_mobile[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label>Phone
									<br /><input type="text" name="info_phone[]" value="<? echo textencode($info_phone[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label>Email
									<br /><input type="text" name="info_email[]" value="<? echo textencode($info_email[$oldrecord[$i]]); ?>" />
								</label>
							</td>
						</tr>
						<tr>
							<td>
								<label>Address
									<br /><input type="text" name="info_address[]" value="<? echo textencode($info_address[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label></label>
							</td>
							<td>
								<label></label>
							</td>
						</tr>
					</table>
					<div class="clear"></div>
				</fieldset>


				<fieldset>
					<label>Father's Info</label>
					<table width="60%" border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td colspan="3">
								<label>Father's Name</label>
								<div class="clear"></div>
								<input type="text" name="info_father_name[]" value="<? echo textencode($info_father_name[$oldrecord[$i]]); ?>" />
								<div class="clear"></div>
							</td>
						</tr>
						<tr>
							<td>
								<label>Mobile (login)
									<br /><input type="text" name="info_father_mobile[]" value="<? echo textencode($info_father_mobile[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label>Phone
									<br /><input type="text" name="info_father_phone[]" value="<? echo textencode($info_father_phone[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label>Email
									<br /><input type="text" name="info_father_email[]" value="<? echo textencode($info_father_email[$oldrecord[$i]]); ?>" />
								</label>
							</td>
						</tr>
					</table>
					

					<label>Mother's Info</label>
					<table width="60%" border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td colspan="3">
								<label>Mother's Name</label>
								<div class="clear"></div>
								<input type="text" name="info_mother_name[]" value="<? echo textencode($info_mother_name[$oldrecord[$i]]); ?>" />
								<div class="clear"></div>
							</td>
						</tr>
						<tr>
							<td>
								<label>Mobile (login)
									<br /><input type="text" name="info_mother_mobile[]" value="<? echo textencode($info_mother_mobile[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label>Phone
									<br /><input type="text" name="info_mother_phone[]" value="<? echo textencode($info_mother_phone[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label>Email
									<br /><input type="text" name="info_mother_email[]" value="<? echo textencode($info_mother_email[$oldrecord[$i]]); ?>" />
								</label>
							</td>
						</tr>
					</table>
					
					
					
					
					
					
				</fieldset>

				<fieldset>
					<label>Notes</label>
					<textarea name="description[]" rows="12" ><? echo textencode($description[$oldrecord[$i]]); ?></textarea>
				</fieldset>
				<fieldset>
					<label>Image</label>
					<?php file_field('file1'.$i,'../uploads/students/',$file1[$oldrecord[$i]]);?>
				</fieldset>
				<div class="clear"></div>
		</div>
	<footer></footer>
                    <?php } ?>





</article>
</form>

<?php
	break;
	
	default:
		include 'functions/_auto_complete.php';
		if( $_print_passwords && $action == 'print_passwords') {
			$PageSize = 10*1000;
			include 'functions/iframe_print.php';
?>
<script type="text/javascript">
<!--
$(document).ready(function(){
	$('#print_passwords').trigger('click');
});
//-->
</script>
<style>
<!--
#printBox td{
	border: 1px solid #000000;
}
-->
</style>
<?php 
		}
?>


<?php if(!empty($warningMsg) && is_array($warningMsg)) { ?>
	<h4 class="alert_warning"><?php echo array_shift($warningMsg); ?></h4>
<?php } else if(!empty($warningMsg)) { ?>
	<h4 class="alert_warning"><?php echo $warningMsg; ?></h4>
<?php } else if(!empty($msg)) { ?>
	<h4 class="alert_success"><?php echo $msg; ?></h4>
<?php } else if(!empty($errorMsg)) { ?>
	<h4 class="alert_error"><?php echo $errorMsg; ?></h4>
<?php } ?>


<div class="alert_browse">
	<form action="<? echo $filename; ?>" method="GET" >
<?php if( $_print_passwords && $action == 'print_passwords') { ?>
<input type="hidden" name="action" value="print_passwords">
<?php } ?>
		Find: <input name="keyword" value="<? echo my_htmlspecialchars($keyword, ENT_QUOTES); ?>" size="30" />
		Status: <select name="status">
			<option value="" >== Any ==</option>
			<option value="active" <?php echo selected($_GET['status'], 'active'); ?>>Active</option>
			<option value="inactive" <?php echo selected($_GET['status'], 'inactive'); ?>>Inactive</option>
		</select>
		

	<?php 
		include 'functions/_index_filter.php';
	?>

		<input type="submit" value="Browse" />
	</form>
</div>
<?php 
		$sql = array();
		if( !empty($keyword) )
		{
			$keywordSQL = mysql_real_escape_string($keyword);
			$keywordSQL = str_replace(' ', '% %', $keywordSQL);
			
			$sql[] = " (
				students.full_name LIKE '%$keywordSQL%' 
				OR students.description LIKE '%$keywordSQL%' 
				
				OR students.info_email LIKE '%$keywordSQL%' 
				OR students.info_mobile LIKE '%$keywordSQL%' 
				OR students.info_phone LIKE '%$keywordSQL%' 
				
				OR students.info_father_email LIKE '%$keywordSQL%' 
				OR students.info_father_mobile LIKE '%$keywordSQL%' 
				OR students.info_father_phone LIKE '%$keywordSQL%' 
				
				OR students.info_mother_email LIKE '%$keywordSQL%' 
				OR students.info_mother_mobile LIKE '%$keywordSQL%' 
				OR students.info_mother_phone LIKE '%$keywordSQL%' 
			) ";
		}

		if(isSchool) {
			$sql[] = " students.school_id = '". isSchool ."' ";
		} else if($School) {
			$sql[] = " students.school_id = '{$School['id']}' ";
		}

		if( $Class ) {
			$sql[] = " students.class_id='{$Class['id']}' ";
		}
		
		switch($_GET['status']) {
			case 'active':
				$sql[] = " students.status='active' ";
				break;
			case 'inactive':
//				$sql[] = " students.status='' ";
				$sql[] = " students.status<>'active' ";
				break;
		}
		
		if( $_print_passwords && $action == 'print_passwords') {
			$sql[] = " students.password_str<>'' ";
		}
		
		
		$where = ( $sql ) ? " WHERE " . implode(' AND ', $sql ) : '';

		$strSQL="SELECT students.*, classes.title as class_title
		FROM students
		LEFT JOIN classes ON(students.class_id = classes.id)
		$where
		GROUP BY students.id
		";

		if( $action == 'export' ) {
			include 'functions/_export.php';
			
			$excel = new export_excel("Students");
			$excel->addField('full_name', 'Full Name');
			$excel->addField('description', 'Notes');

			if( !isSchool ) {
				$excel->addField('school_id', 'School', 'get_title_from_array', $Schools);
			}

			$excel->addField('class_title', 'Class');

			$excel->addField('info_mobile', 'Mobile');
			$excel->addField('info_phone', 'Phone');
			$excel->addField('info_email', 'Email');
			$excel->addField('info_address', 'Address');
			

			$excel->addField('info_father_name', 'Father Name');
			$excel->addField('info_father_mobile', 'Father Mobile');
			$excel->addField('info_father_phone', 'Father Phone');
			$excel->addField('info_father_email', 'Father Email');

			$excel->addField('info_mother_name', 'Mother Name');
			$excel->addField('info_mother_mobile', 'Mother Mobile');
			$excel->addField('info_mother_phone', 'Mother Phone');
			$excel->addField('info_mother_email', 'Mother Email');
			
			$excel->addField('status', 'Status', 'ucwords');
			$excel->export( "$strSQL ORDER BY students.rank DESC");
			exit;
		}

		$objRS=mysql_query($strSQL);
//echo "$strSQL " . mysql_error();
		$total=@mysql_num_rows($objRS);
		$strSQL=makePages ($strSQL,$PageSize,$p, 'students.rank desc');
		$objRS=mysql_query($strSQL);
		$count_all = @mysql_num_rows($objRS);
		
?>


<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=delete" method="post" name="del" id="del">
<article class="module width_full">
	<header><h3 class="tabs_involved">Students Manager</h3>
	<ul class="tabs">
   		<li><a href="#add" onClick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=add'" >Add New</a></li>
   		<li><a href="#edit" onClick="edit('<?=$menu?>&<?php echo $queryStr; ?>p=<?=$p?>')" >Edit</a></li>
    	<li><a href="#delete" onClick="conf()">Delete</a></li>
	</ul>
<?php if( $_print_passwords && $action == 'print_passwords') { ?>
	<ul class="tabs">
		<li><a id="print_passwords" href="#Print" onClick="printDiv(this, 'printBox'); return false;" >Print List</a></li>
		<li><a href="#Export" onClick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action='" >Students List</a></li>
	</ul>
<?php } else { ?>
	<ul class="tabs">
		<li><a href="#Export" onClick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=export'" >Export List</a></li>
	<?php if( $_print_passwords ) { ?>
		<li><a href="#Print_Passwords" onClick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=print_passwords'" >Print Unchanged Passwords</a></li>
	<?php } ?>
	</ul>
	<ul class="tabs">
		<li><a href="#Import" onClick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=import'" >Import</a></li>
	</ul>
<?php } ?>
	</header>
	<div class="tab_container">
<?php if( $_print_passwords && $action == 'print_passwords') { ?>
<div id="printBox" >
<table class="tablesorter" cellspacing="0" border="1">
    <tr>
<?php
	$TDs = 0;
	while ($row=mysql_fetch_object($objRS)){
?>
		<td>
			<div><b>Name</b>: <? echo $row->full_name; ?></div>
			<div><b>Email</b>: <? echo $row->email; ?></div>
			<div><b>Password</b>: <? echo $row->password_str; ?></div>
		</td>
<?php 
		$TDs++;
		if(!($TDs%2)) {
			echo "</tr><tr>"; 
		}
	}
?>
	</tr>
	</table>
</div>
<?php } else { ?>
		<table class="tablesorter" cellspacing="0"> 
		<thead> 
			<tr> 
   				<th width="1"><input type="checkbox" onClick="checkall()" name="main"></th>
                <th width="1">Image</th>
    			<th>Full Name</th>
    			<th>Info</th>
    			<th>Father</th>
    			<th>Mother</th>
			<?php if( !isSchool ) { ?>
    			<th>School</th>
			<?php } ?>
    			<th>Class</th>
                <th width="1">Status</th>
                <th width="1">Rank</th>
			</tr> 
		</thead> 
		<tbody id="trContainer" class="sortable"> 

	<?php while ($row=mysql_fetch_object($objRS)){ ?>
    <tr id="tr_<? echo $row->id; ?>">
		<td align="right"><input type="checkbox" name="ids[]" value="<? echo $row->id; ?>"></td>
		<td align="center"><?php echo scale_image("../uploads/students/thumb/". $row->image, 100); ?></td>
		<td>
			<div><b><? echo $row->full_name; ?></b></div>
			<div><?php if($row->description !=''){ echo summarize($row->description, 20); }?></div>
			<div>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>action=edit&ids[]=<? echo $row->id; ?>">Edit Student</a>
			</div>
		</td>
		<td>
		<?php if( $row->info_mobile ) { ?>
			<div><b>Mobile:</b> <? echo $row->info_mobile; ?></div>
		<?php } ?>
		<?php if( $row->info_phone ) { ?>
			<div><b>Phone:</b> <? echo $row->info_phone; ?></div>
		<?php } ?>
		<?php if( $row->info_email ) { ?>
			<div><b>Email:</b> <? echo $row->info_email; ?></div>
		<?php } ?>
		</td>
		<td>
		<?php if( $row->info_father_name ) { ?>
			<div><b>Father's&nbsp;Name:</b>&nbsp;<? echo $row->info_father_name; ?></div>
		<?php } ?>
		<?php if( $row->info_father_mobile ) { ?>
			<div><b>Father's&nbsp;Mobile:</b>&nbsp;<? echo $row->info_father_mobile; ?></div>
		<?php } ?>
		<?php if( $row->info_father_phone ) { ?>
			<div><b>Father's&nbsp;Phone:</b>&nbsp;<? echo $row->info_father_phone; ?></div>
		<?php } ?>
		<?php if( $row->info_father_email ) { ?>
			<div><b>Father's&nbsp;Email:</b>&nbsp;<? echo $row->info_father_email; ?></div>
		<?php } ?>
		</td>
		<td>
		<?php if( $row->info_mother_name ) { ?>
			<div><b>Mother's&nbsp;Name:</b>&nbsp;<? echo $row->info_mother_name; ?></div>
		<?php } ?>
		<?php if( $row->info_mother_mobile ) { ?>
			<div><b>Mother's&nbsp;Mobile:</b>&nbsp;<? echo $row->info_mother_mobile; ?></div>
		<?php } ?>
		<?php if( $row->info_mother_phone ) { ?>
			<div><b>Mother's&nbsp;Phone:</b>&nbsp;<? echo $row->info_mother_phone; ?></div>
		<?php } ?>
		<?php if( $row->info_mother_email ) { ?>
			<div><b>Mother's&nbsp;Email:</b>&nbsp;<? echo $row->info_mother_email; ?></div>
		<?php } ?>
		</td>

	<?php if( !isSchool ) { ?>
		<td>
			<div>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>school_id=<? echo $row->school_id; ?>"><?php echo $Schools[ $row->school_id ]['title']; ?></a>
			</div>
		</td>
	<?php } ?>
		<td>
			<div>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>school_id=<? echo $row->school_id; ?>&class_id=<? echo $row->class_id; ?>"><?php echo $row->class_title; ?></a>
			</div>
		</td>
		<td align="center"><?php echo ( $row->status == 'active') ? 'Active' : 'Inactive'; ?></td>

		<td><?php echo_SetOrder( $queryStr, $row->id, $p ); ?></td>
	</tr>
	<?php }?>
			</tbody> 
			</table>
<?php } ?>
		</div><!-- end of .tab_container -->
		<footer>
			<div class="submit_link">
				<?=dispPages ($total,$PageSize,$p, $queryStr)?>
			</div>
		</footer>
	</article><!-- end of content manager article -->
</form>
<?php
	break;
endswitch; 

include '_bottom.php';


function getRecordsFromCSV($csv, $delimiter) {
	$CsvFileParser = new CsvFileParser();
	$CsvFileParser->delimiter = $delimiter;
	$CsvFileParser->ParseFromString( $csv );
	return $CsvFileParser->data;
}

function findTwoFieldsInFirstRow( $row ) {
	
	if( is_array( $row )) {
		if( count($row) >= 2 ){
			return true;
		}
	}
	return false;
}

function clearMobileNumberForLogin( $number ) {
	$number = trim($number);
	$number = str_replace('-', '', $number);
	$number = str_replace("+", '', $number);
	$number = str_replace('*', '', $number);
	$number = str_replace('/', '', $number);
	$number = str_replace('\\', '', $number);
	$number = str_replace('(', '', $number);
	$number = str_replace(')', '', $number);
	$number = str_replace('=', '', $number);
	$number = str_replace("#", '', $number);
	$number = str_replace(".", '', $number);
	$number = str_replace("\s", '', $number);
	$number = str_replace("\t", '', $number);
	$number = str_replace("\r", '', $number);
	$number = str_replace("\n", '', $number);
	
	return $number;
}