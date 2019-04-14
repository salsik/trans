<?php

define('THUMB_WIDTH', 482); // 218
define('THUMB_HEIGHT', 317); // 150

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include "_top.php";

$limitation = '';
$limitationImport = '';
if(isReseller) {
	$limitation = " AND id IN (SELECT student_id FROM students_resellers WHERE reseller_id='".isReseller."' ) ";
	$limitationImport = " AND reseller_id='".isReseller."' ";
}

define('index_table', 'students_index');
$fieldsArray = array(
	'first_name', 'last_name', 'description', 'status', 'email', 'password', 
	'info_phone', 'info_mobile', 'info_email',  'info_address', 
);
$csvOptions = array(
	'delimiter' => ';',
	'fields' => array(
		'first_name' => 'First name',
		'last_name' => 'Last Name',
		'email' => 'Email',
		'info_phone' => 'Phone',
		'info_mobile' => 'Mobile',
		'info_address' => 'Address',
	),
);

$relatedArray = array(
//	'students' => " students ",
//	'news' => " news ",
//	'documents' => " documents ",
//	'banners' => " banners ",
);


$queryStr = '';
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

$_catTitle = '';
$_GET['cat_id'] = intval( $_GET['cat_id'] );
$cat_id = 0;
if( $_GET['cat_id'] > 0 ) {
	$Sub = getDataByID('category_sub', $_GET['cat_id']);
	if( $Sub ) {
		$queryStr .= "cat_id={$Sub['id']}&";
		$cat_id = $Sub['cat_id'];
		$_catTitle = " / {$Sub['title']}";
	}
} else {
	$cat_id = abs( $_GET['cat_id'] );
}

if( $cat_id > 0 ) {
	$Category = getDataByID('category', $cat_id);
	if( $Category ) {
		$queryStr .= "cat_id=-{$Category['id']}&";
		$_catTitle = "{$Category['title']}{$_catTitle}";
	}
}

//	WHERE category.status='active' 
$SubCategories = array();
$Categories = array();
$q = mysql_query("SELECT category.* FROM `category` ORDER BY category.rank DESC");
if( $q && mysql_num_rows($q ) )
{
	while( $row = mysql_fetch_assoc( $q ))
	{
		$Categories[$row['id'] ] = $row;
	}
}

if( !isReseller ) {
	$Reseller = getDataByID('resellers', $_GET['reseller_id']);
	if( $Reseller ) {
		$queryStr .= "reseller_id={$Reseller['id']}&";
	}

	$Resellers = array();
	if($action=='add') {
		$q = mysql_query("SELECT resellers.* FROM `resellers` WHERE resellers.status='active' ORDER BY resellers.title ASC");
	} else {
		$q = mysql_query("SELECT resellers.* FROM `resellers` ORDER BY resellers.title ASC");
	}
	if( $q && mysql_num_rows($q ) )
	{
		while( $row = mysql_fetch_assoc( $q ))
		{
			$Resellers[ $row['id'] ] = $row;
		}
	}
}


$_GET['import_id'] = intval($_GET['import_id']);
if( $_GET['import_id'] > 0 ) {
	$queryStr .= "import_id={$_GET['import_id']}&";
}
switch($action) {
	case 'import':
		$queryStr .= "action={$action}&";
}

//Define the maximum items while listine
$PageSize=10;
//Define the maximum items while adding
$max=1;

require_once('_lister.class.php');
$sortableLister = new lister("_sort_reverse.php?filename=".$filename."&table=students");

$rank=getfield(getHTTP('rank'),"rank","students");


if($Reseller) {
	?><h4 class="alert_info">School: <?php echo $Reseller['title']; ?></h4><?php 
}

if($Sub) {
	?><h4 class="alert_info">Class: <?php echo $Category['title']; ?>/<?php echo $Sub['title']; ?></h4><?php 
} else if($Category) {
	?><h4 class="alert_info">Class: <?php echo $Category['title']; ?></h4><?php 
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

		include 'functions/csv_parser.php';

		$reseller_id = $_POST['reseller_id'];
		$password_keyword = $_POST['password_keyword'];

		$Errors = array();
		
		if( !isReseller ) {
			$_reseller = getDataByID('resellers', $reseller_id);
		}
		
		if( empty($password_keyword) ) {
			$Errors[] = "Missing Password Keyword!!";
		} else if( !isReseller && !$_reseller ) {
			$Errors[] = "Missing School!!";
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

			if( isReseller ) {
				$sql['reseller_id']= " `reseller_id`='".isReseller."', ";
				$sql['add_by_id']= " `add_by_id`='".isReseller."', ";
			} else {
				$sql['reseller_id']= " `reseller_id`='".$_reseller['id']."', ";
			}

			$strSQL = "students_import set " . implode('', $sql);
		
			$q=mysql_query("SELECT max(rank) as max FROM students_import");
			$r = mysql_fetch_object($q);

			$strSQL ="insert into ".$strSQL." date = '".date('Y-m-d')."', rank='".($r->max+1)."', time='".time()."' ";
			
			$q = mysql_query($strSQL);
			if( $q ) {
				$import_id = mysql_insert_id();
//				$msg="Record(s) imported successfully!!";

//				$showing = "import";
			} else {
				$Errors[] = 'Something went wronge!';
				$Errors[] = mysql_error();
				
//				$showing = "import";
			}
		}
		
		if( $Errors ) {
			$errorMsg = array_shift($Errors);
			$showing = "import";
			break;
		} else {
			$CSVFileName = $_FILES['import_file']['tmp_name'];
			$CSVFile = @file_get_contents($CSVFileName);

			$CsvFileParser = new CsvFileParser();
			$CsvFileParser->delimiter = $csvOptions['delimiter'];
			$CsvFileParser->ParseFromString( $CSVFile );
			$CSVFile = $CsvFileParser->data;
		
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

			ob_clean();
			header("Location: {$filename}?{$queryStr}action=importjob&import_id={$import_id}");
			exit;
		}
	break;

	case "importjob":
	case "importjobexe":
		//Get needed data from the DB
		$import_id = intval( $_GET['import_id']);
		$strSQL="select * from students_import where id ='$import_id' $limitationImport LIMIT 1";
		$objRS=mysql_query($strSQL);
		if( !($objRS && mysql_num_rows($objRS))) {
			$errorMsg = "Selected import session not found!";
//			$errorMsg = mysql_error();
			$showing = "import";
			break;
		}
		$import = mysql_fetch_assoc($objRS);
		$showing = "importjob";
		
		if( $action == 'importjob') {
			break;
		}
		
		$i = 0;
		$index = $_POST['index'];
		$status = $_POST['status'];


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
					if( isReseller) {
						$sql['add_by_id']= " `add_by_id`='".isReseller."', ";
					}

					$sql['full_name']= " `full_name`='".sqlencode(trime( $data['first_name'] .' ' . $data['last_name']))."', ";
					$sql['password']= " `password`='".md5( $pass )."', ";
					$sql['password_str'] = " `password_str`='".sqlencode(trime( $pass ))."', ";
					$sql['imported']= " `imported`='1', ";
					$sql['status'] = " `status`='".sqlencode(trime( $status[0] ))."', ";
				
					$strSQL = "students set " . implode('', $sql);

					$Errors = array();
					
					if( empty( $data['first_name'] ) ) {
						$err = "Missing First Name!!";
						break;
					} else if( empty( $data['last_name'] ) ) {
						$err = "Missing Last Name!!";
						break;
					} else if( !isemail( $data['email'] ) ) {
						$err = "Missing or Invalid Student's Login E-mail!!";
						break;
					}
			
					if( isReseller && $Admin['plan_students'] > 0) {
						$count = getDataCount('students_resellers', " `reseller_id` = '".isReseller."' ");
						if( $count >= $Admin['plan_students'] ) {
							$err = "You reached your plan's maximum allowed students (max: {$Admin['plan_students']})!!";
							break;
						}
					}
					
					$q=mysql_query("SELECT max(rank) as max FROM students");
					$r = mysql_fetch_object($q);

					$strSQL ="insert into ".$strSQL." rank='".($r->max+1)."', time='".time()."' ";
				
					$q = mysql_query($strSQL);
					if( $q ) {
						$doc_id = mysql_insert_id();
						$isAddAction = true;

						$index_id = $doc_id;
						include('functions/_index_create.php');

						$sql= "INSERT INTO `students_resellers` SET
							`student_id`='".sqlencode(trime( $doc_id ))."'
							, `reseller_id`='".sqlencode(trime( $import['reseller_id'] ))."'
							";
						$qq = mysql_query( $sql );
						if(!$qq) {
							$warningMsg[-2] = 'Some records faced problems while indexing it!';
						}
					} else {
						if( mysql_errno() == 1062) {
							$err = 'E-mail (login) already exists!';
							break;
						} else {
							$err = 'Something went wronge while inserting the student! ';
							$err .= mysql_error();
							break;
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
		
		$action = 'importjob';
		if( $errorMsg ) {
			$showing = "importjob";
			break;
		}

		$msg="Record(s) importeded successfully!!";
		$showing = "";

		break;
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
		
			$index[$i] = array();
			$q = mysql_query("SELECT * FROM `".index_table."` WHERE index_id='{$row->id}' ");
			if($q && mysql_num_rows($q)) {
				while($indx = mysql_fetch_assoc($q)) {
					$index[$i][ $indx['cat_id'] ][ $indx['sub_id'] ] = 'yes';
				}
			}

			if( ! isReseller ){
				$resellers[$i] = array();
				$q = mysql_query("SELECT * FROM `students_resellers` WHERE student_id='{$row->id}' ");
				if($q && mysql_num_rows($q)) {
					while($indx = mysql_fetch_assoc($q)) {
						$resellers[$i][] = $indx['reseller_id'];
					}
				}
			}
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
		
		$index = $_POST['index'];
		$resellers = $_POST['resellers'];

		//Counter for errors array according to the number of records.
		$j=0;
		//Array of "Errored" records.
		$oldrecord=array();
		//Start the loop over the records
		for($i=0;$i<sizeof($ids);$i++){
			$file1[$i] = $_POST['file1'.$i];
			//Set the flag to one in order to verify the conditions later
			$flag=1;
			
			$sql = array();
			foreach($fieldsArray as $field) {
				$sql[$field] = " `$field`='".sqlencode(trime(${$field}[$i]))."', ";
			}
			$sql['full_name']= " `full_name`='".sqlencode(trime( $first_name[$i] .' ' . $last_name[$i]))."', ";
			
			if( $action == 'addexe' || $password[$i]) {
				$sql['password']= " `password`='".md5($password[$i])."', ";
				$sql['password_str']= " `password_str`='', ";
			} else {
				unset( $sql['password'] );
			}
			if( $action == 'addexe' && isReseller) {
				$sql['add_by_id']= " `add_by_id`='".isReseller."', ";
			}

			$strSQL = "students set " . implode('', $sql);
			$Errors = array();
			
			if( !isReseller ) {
				$_resellers = getDataByIDs('resellers', $resellers[$i]);
			}
			
			if( empty($first_name[$i]) ) {
				$Errors[] = "Missing First Name!!";
			} else if( empty($last_name[$i]) ) {
				$Errors[] = "Missing Last Name!!";
			} else if( !isemail($email[$i]) ) {
				$Errors[] = "Missing or Invalid Student's Login E-mail!!";
			} else if( $action == 'addexe' && empty($password[$i]) ) {
				$Errors[] = "Missing Student's Password!!";
			} else if( !isReseller && !$_resellers ) {
				$Errors[] = "Missing Schools!!";
			} else if( !empty($info_email[$i]) && !isemail($info_email[$i]) ) {
				$Errors[] = "Invalid Student's E-mail!!";
			}
			
			if( $action == 'addexe' && isReseller && $Admin['plan_students'] > 0) {
				$count = getDataCount('students_resellers', " `reseller_id` = '".isReseller."' ");
				if( $count >= $Admin['plan_students'] ) {
					$Errors[] = "You reached your plan's maximum allowed students (max: {$Admin['plan_students']})!!";
				}
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

					$index_id = $doc_id;
					include('functions/_index_create.php');
				
					if( isReseller ) {
						if( $action == 'addexe' ) {
							$sql= "INSERT INTO `students_resellers` SET
								`student_id`='".sqlencode(trime( $doc_id ))."'
								, `reseller_id`='".sqlencode(trime( isReseller ))."'
								";
							$qq = mysql_query( $sql );
							if(!$qq) {
								$warningMsg[-2] = 'Some records faced problems while indexing it!';
							}
						}
					} else {
						if( mysql_query("DELETE FROM `students_resellers` WHERE student_id='{$doc_id}' ") ) {
							
							foreach($_resellers as $reseller) {
								$sql= "INSERT INTO `students_resellers` SET
									`student_id`='".sqlencode(trime( $doc_id ))."'
									, `reseller_id`='".sqlencode(trime( $reseller['id'] ))."'
									";
								$qq = mysql_query( $sql );
								if(!$qq) {
									$warningMsg[-2] = 'Some records faced problems while indexing it\'s schools!';
								}
							}
						} else {
							$warningMsg[-2] = 'Some records faced problems while indexing it\'s schools!';
						}
					}
				} else {
					if( mysql_errno() == 1062) {
						$errorMsg[$j] = 'E-mail (login) already exists!';
					} else {
						$errorMsg[$j] = 'Something went wronge!';
					}
//					$errorMsg[$j] = mysql_error();
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
//			foreach($relatedArray as $tpl=>$eMsg) {
//				$q = mysql_query("SELECT * FROM `$tpl` WHERE student_id = '".$row->id ."' LIMIT 1");
//				if( $q && mysql_num_rows($q)) {
//					if( empty($errorMsg) ) {
//						$errorMsg = "Some Records didn't affected, You may need to delete/update all {$eMsg} related to the deleted student!!";
//					}
//					continue 2;
//				}
//			}
			if( isReseller ) {
				$q = mysql_query("DELETE * FROM `students_resellers` WHERE reseller_id = '".isReseller."' AND student_id='{$row->id}' ");
				continue;
			}

			if($row->image != '') {
				@unlink('../uploads/students/'.$row->image);	
				@unlink('../uploads/students/thumb/'.$row->image);	
			}

			$strSQL="DELETE FROM students WHERE id = '{$row->id}' LIMIT 1";
			if(!mysql_query($strSQL) && empty($errorMsg)) {
				$errorMsg="Some Records didn't affected!!";
			} else {
				mysql_query("DELETE * FROM `".index_table."` WHERE index_id='{$row->id}' ");
				mysql_query("DELETE * FROM `students_resellers` WHERE student_id='{$row->id}' ");
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

		if( isReseller && $Admin['plan_students'] > 0) {
			$count = getDataCount('students_resellers', " `reseller_id` = '".isReseller."' ");
			if( $count >= $Admin['plan_students'] ) {
				?><h4 class="alert_warning">You reached your plan's maximum allowed students (max: <?php echo $Admin['plan_students']; ?>)!!</h4><?php 
			}
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
									<tr>
										<td><label>Password Keyword</label></td>
										<td><input type="text" name="password_keyword" value="<? echo textencode($password_keyword); ?>" /></td>
									</tr>
								<?php if( !isReseller ) { ?>
										<?php 
											$ResellerID = nor($reseller_id, $Reseller['id']);
											$reseller = getDataByID('resellers', $ResellerID);
										?>
									<tr>
										<td><label>School</label></td>
										<td><span class="autocomplete" data-link="_get.php?from=resellers">
												<input class="input_short auto_name" type="text" name="reseller_name" value="<?php echo trim( $reseller['title']); ?>" />
												<input class="input auto_id" type="hidden" name="reseller_id" value="<?php echo $reseller['id']; ?>" />
											</span></td>
									</tr>
								<?php } ?>
								</table>
							</td>
							<td class="levels">
								<ul>
									<li>Only .CVS files are allowed to be uploaded.</li>
									<li>CVS delimiter is: <?php echo $csvOptions['delimiter']; ?></li>
									<li>CVS Fields should be in this order:
										<ol>
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
		<footer><h3>Imported Files</h3></footer>

		<div class="module_content inline">
<?php 
	$importJobs = array();
	$import_id = intval( $_GET['import_id']);

	$strSQL="SELECT students_import.*, resellers.title as reseller_title
		FROM students_import_rows, students_import 
			LEFT JOIN resellers ON(resellers.id = students_import.reseller_id)
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
		$ResellerID = $Reseller['id'];
		$reseller = getDataByID('resellers', $ResellerID);
	?>
		<input type="hidden" name="action" value="import">
		Pending Records: <select name="import_id">
			<option value="" >== Select ==</option>
		<?php
			foreach($importJobs as $importJob ){
				$selected = selected($importJob['id'], $import_id);
				if( isReseller || $reseller) {
					$title = "{$importJob['title']} [{$importJob['date']}]";
				} else {
					$title = "{$importJob['reseller_title']} / {$importJob['title']} [{$importJob['date']}]";
				}
		?>
			<option value="<?php echo $importJob['id']; ?>" <?php echo $selected; ?>><?php echo $title; ?></option>
		<?php } ?>
		</select>
<?php if( !isReseller ) { ?>
		School: <span class="autocomplete" data-link="_get.php?from=resellers">
			<input class="input_short auto_name" type="text" name="reseller_name" value="<?php echo trim( $reseller['title']); ?>" />
			<input class="input auto_id" type="hidden" name="reseller_id" value="<?php echo $reseller['id']; ?>" />
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
    			<th>Full Name</th>
    			<th>Last Error</th>
    			<th>Import Session</th>
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
		<td align="left"><?php echo $row['title']; ?></td>
		<td align="left"><?php echo $row['last_error']; ?></td>
		<td align="left"><a href="<?php echo $filename; ?>?<?php echo $queryStr; ?>action=importjob&import_id=<?php echo $row['import_id']; ?>">Import Session</a></td>
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

		if( isReseller && $Admin['plan_students'] > 0) {
			$count = getDataCount('students_resellers', " `reseller_id` = '".isReseller."' ");
			if( $count >= $Admin['plan_students'] ) {
				?><h4 class="alert_warning">You reached your plan's maximum allowed students (max: <?php echo $Admin['plan_students']; ?>)!!</h4><?php 
			}
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
							<td>
								<label>Password Keyword:
									<br /><?php echo $import['password_keyword']; ?>
								</label>
							</td>
					<?php if( !isReseller ) { ?>
							<?php 
								$reseller = getDataByID('resellers', $import['reseller_id']);
							?>
							<td>
								<label>School:
								<br /><?php echo $reseller['title']; ?>
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
					<label>Student's Classes</label>
					<?php include('functions/_index_table.php'); ?>
				</fieldset>
		</div>
	<footer></footer>
</article>
</form>

<?php
		break;
	case "record":

		if( $action == 'add' && isReseller && $Admin['plan_students'] > 0) {
			$count = getDataCount('students_resellers', " `reseller_id` = '".isReseller."' ");
			if( $count >= $Admin['plan_students'] ) {
				?><h4 class="alert_warning">You reached your plan's maximum allowed students (max: <?php echo $Admin['plan_students']; ?>)!!</h4><?php 
			}
		}
			
?>

<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>" method="post" name="myform" enctype="multipart/form-data">
<input type="hidden" name="action" value="<? echo $action; ?>exe"> 
<article class="module width_full">
	<header><h3>Students: <?php echo ucwords($action); ?> Record
		<input type="submit" value="Save" class="alt_btn">
		<input type="button" value="Cancel" onclick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>'">
		</h3></header>

		<?php for($i=0;$i<$max;$i++){ ?>
		<input type="hidden" name="ids[]" value="<? echo $ids[$i]; ?>">
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
								<label></label>
							</td>
						</tr>
					</table>
				</fieldset>

				<fieldset>
					<label>Student's Info</label>
					<table width="60%" border="0" cellpadding="5" cellspacing="0">
				<?php if( !isReseller ) { ?>
						<tr>
							<td>
								<label>School<br>
								<select name="resellers[<?php echo $i; ?>][]" multiple="multiple" class="resellers_select">
						<?php 
								if(!is_array($resellers[$oldrecord[$i]])) {
									$resellers[$oldrecord[$i]] = array();
								}
								foreach ($Resellers as $reseller) {
									$Selected = ( in_array($reseller['id'], $resellers[$oldrecord[$i]] ) ) ? ' selected="selected" ' : '';
									?><option value="<?php echo $reseller['id'];?>" <?php echo $Selected; ?> ><?php echo $reseller['title'];?></option><?php 
								}
						?>
								</select>
								</label>
							</td>
							<td>
								<label></label>
							</td>
							<td>
								<label></label>
							</td>
						</tr>
				<?php } ?>
						<tr>
							<td>
								<label>Phone
									<br /><input type="text" name="info_phone[]" value="<? echo textencode($info_phone[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label>Mobile
									<br /><input type="text" name="info_mobile[]" value="<? echo textencode($info_mobile[$oldrecord[$i]]); ?>" />
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
				</fieldset>

				<fieldset>
					<label>Classes</label>
					<?php include('functions/_index_table.php');; ?>
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

<?php if( !isReseller) { ?>
	<script type="text/javascript">
	<!--
	$(".resellers_select").multiselect({
		header: "Select Schools!",
		noneSelectedText: 'Select Schools'
	}).multiselectfilter();
	//-->
	</script>
<?php } ?>
<?php
	break;
	
	default:
		include 'functions/_auto_complete.php';
		
		if( $action == 'print_passwords') {
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
<?php if( $action == 'print_passwords') { ?>
<input type="hidden" name="action" value="print_passwords">
<?php } ?>
		Find: <input name="keyword" value="<? echo htmlspecialchars($keyword, ENT_QUOTES); ?>" size="30" />
		Status: <select name="status">
			<option value="" >== Any ==</option>
			<option value="active" <?php echo selected($_GET['status'], 'active'); ?>>Active</option>
			<option value="inactive" <?php echo selected($_GET['status'], 'inactive'); ?>>Inactive</option>
		</select>
	<?php 
		$catID = ($Sub) ? $Sub['id'] : ( ($Category) ? $Category['id'] *-1 : '');
	?>
		Class: <span class="autocomplete" data-link="_get.php?from=categories">
			<input class="input_short auto_name" type="text" name="cat_name" value="<?php echo $_catTitle; ?>" />
			<input class="input auto_id" type="hidden" name="cat_id" value="<?php echo $catID; ?>" />
		</span>
<?php if( !isReseller ) { ?>
	<?php 
		$ResellerID = $Reseller['id'];
		$reseller = getDataByID('resellers', $ResellerID);
	?>
		School: <span class="autocomplete" data-link="_get.php?from=resellers">
			<input class="input_short auto_name" type="text" name="reseller_name" value="<?php echo trim( $reseller['title']); ?>" />
			<input class="input auto_id" type="hidden" name="reseller_id" value="<?php echo $reseller['id']; ?>" />
		</span>
<?php } ?>
		<input type="submit" value="Browse" />
	</form>
</div>
<?php 
		$sql = array();
		if( !empty($keyword) )
		{
			$keywordSQL = mysql_real_escape_string($keyword);
			$keywordSQL = str_replace(' ', '% %', $keywordSQL);
			
			$sql[] = " (students.full_name LIKE '%$keywordSQL%' OR students.description LIKE '%$keywordSQL%' ) ";
		}

		if(isReseller) {
			$sql[] = " students_resellers.reseller_id = '". isReseller ."' ";
		} else if($Reseller) {
			$sql[] = " students_resellers.reseller_id = '{$Reseller['id']}' ";
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
		if( $Sub ) {
			$sql[] = " catSub.sub_id='{$Sub['id']}' ";
		} else if( $Category ) {
//			$sql[] = " catSub.sub_id='0' AND catSub.cat_id='{$Category['id']}' ";
			$sql[] = " catSub.cat_id='{$Category['id']}' ";
		}
		
		if( $action == 'print_passwords') {
			$sql[] = " students.password_str<>'' ";
		}
		
		

		$where = ( $sql ) ? " WHERE " . implode(' AND ', $sql ) : '';

		$strSQL="SELECT students.*, GROUP_CONCAT( DISTINCT students_resellers.reseller_id SEPARATOR ',' ) as students_resellers, GROUP_CONCAT( DISTINCT catSub.title SEPARATOR '<~~>' ) as cat_titles
		FROM ( students
		LEFT JOIN students_index ON (students.id = students_index.index_id)
		LEFT JOIN (
			SELECT category.id as cat_id, 0 as sub_id, category.title as title FROM category 
			UNION
			SELECT category.id as cat_id, category_sub.id as sub_id, concat_ws('<~>', category.title, category_sub.title) as title
			FROM category 
			LEFT JOIN category_sub ON ( category.id = category_sub.cat_id)
		) as catSub ON (catSub.sub_id = students_index.sub_id AND catSub.sub_id = students_index.sub_id)
		) 
		LEFT JOIN students_resellers ON (students.id = students_resellers.student_id)

		$where
		GROUP BY students.id
		";

		
		if( $action == 'export' ) {
			include 'functions/_export.php';
			
			$excel = new export_excel("Students");
			$excel->addField('full_name', 'Full Name');
			$excel->addField('description', 'Notes');

			if( !isReseller ) {
				$excel->addField('students_resellers', 'Schools', 'style_students_resellers', $Resellers);
			}

			$excel->addField('cat_titles', 'Classes', 'style_cat_titles');

			$excel->addField('info_phone', 'Phone');
			$excel->addField('info_email', 'Email');
			$excel->addField('info_mobile', 'Mobile');
			
			$excel->addField('status', 'Status', 'ucwords');
			$excel->export( "$strSQL ORDER BY students.rank DESC");
			exit;
		}

		$objRS=mysql_query($strSQL);
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
<?php if( $action == 'print_passwords') { ?>
	<ul class="tabs">
		<li><a id="print_passwords" href="#Print" onClick="printDiv(this, 'printBox'); return false;" >Print List</a></li>
		<li><a href="#Export" onClick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action='" >Students List</a></li>
	</ul>
<?php } else { ?>
	<ul class="tabs">
		<li><a href="#Export" onClick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=export'" >Export List</a></li>
		<li><a href="#Print_Passwords" onClick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=print_passwords'" >Print Unchanged Passwords</a></li>
	</ul>
	<ul class="tabs">
		<li><a href="#Import" onClick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=import'" >Import</a></li>
	</ul>
<?php } ?>
	</header>
	<div class="tab_container">
<?php if( $action == 'print_passwords') { ?>
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
    			<th>Email</th>
    			<th>Info</th>
			<?php if( !isReseller ) { ?>
    			<th>Schools</th>
			<?php } ?>
    			<th width="350">Classes</th>
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
			<b><? echo $row->email; ?></b>
		</td>
		<td>
		<?php if( $row->info_phone ) { ?>
			<div><b>Phone:</b> <? echo $row->info_phone; ?></div>
		<?php } ?>
		<?php if( $row->info_mobile ) { ?>
			<div><b>Mobile:</b> <? echo $row->info_mobile; ?></div>
		<?php } ?>
		<?php if( $row->info_email ) { ?>
			<div><b>Email:</b> <? echo $row->info_email; ?></div>
		<?php } ?>
		</td>
	<?php if( !isReseller ) { ?>
		<td><?php 
			$_resellers = style_students_resellers($row->students_resellers, $Resellers, 'array');
			foreach($_resellers as $k=>$v) {
				?><div>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>reseller_id=<? echo $k; ?>"><?php echo $v; ?></a>
			</div><?php 
			}
		?>
		</td>
	<?php } ?>
		<td>
			<div>
				<?php echo style_cat_titles( $row->cat_titles, true); ?>
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
