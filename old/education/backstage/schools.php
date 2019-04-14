<?php

define('THUMB_WIDTH', 482); // 218
define('THUMB_HEIGHT', 317); // 150

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include "_top.php";

$fieldsArray = array(
	'title', 'description', 'status', 'email', 'password', 
	'info_phone', 'info_mobile', 'info_email', 
	'contact_first_name', 'contact_last_name', 'contact_phone', 'contact_mobile', 'contact_email', 'contact_send_email',
	'plan_students', 'plan_expired', 'agenda_send_time_str',
);

$relatedArray = array(
	'students' => " students ",

	'news' => " news ",
	'documents' => " documents ",

	'videos' => " videos ",
	'gallery' => " gallery ",

	'gallery_category' => " gallery albums ",

	'agenda' => " agenda ",
	'teachers' => " teachers ",
	'classes' => " classes ",

	'banners' => " banners ",
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

//Define the maximum items while listine
$PageSize=10;
//Define the maximum items while adding
$max=1;

require_once('_lister.class.php');
$sortableLister = new lister("_sort_reverse.php?filename=".$filename."&table=schools");

$rank=getfield(getHTTP('rank'),"rank","schools");

switch ($action):
	//case "down":
		case "up": //reverse
		$strSQL="select * from schools WHERE rank='".($rank+1)."'" ;
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update schools set rank='".($rank+1)."' WHERE rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update schools set rank='".$rank."' WHERE id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from schools WHERE rank >'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update schools set rank='".($rank+1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update schools set rank='".$rank."' where id='".$row->id."'";
					mysql_query($strSQLord);
				}
			}
		}
	break;
	//case "up":
		case "down": //reverse
		$strSQL="select * from schools where rank='".($rank-1)."'";
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update schools set rank='".($rank-1)."' where rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update schools set rank='".$rank."' where id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from schools where rank <'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update schools set rank='".($rank-1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update schools set rank='".$rank."' where id='".$row->id."'";
					mysql_query($strSQLord);
				}
			}
		}

	break;


	case "add":
		$showing = "record";
	break;
	case "edit":
		//Get needed data from the DB
		$strSQL="select * from schools where id IN(".implode(',',$ids).") order by rank DESC";
		$objRS=mysql_query($strSQL);
		$i=0;
		while ($row=mysql_fetch_object($objRS)){
		
			foreach($fieldsArray as $field) {
				${$field}[$i] = $row->$field;
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
		$confirm_password = $_POST['confirm_password'];
		$news_category = $_POST['news_category'];
		$documents_category = $_POST['documents_category'];
		
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
			
			$sql = array();
			foreach($fieldsArray as $field) {
				$sql[$field] = " `$field`='".sqlencode(trime(${$field}[$i]))."', ";
			}
			$sql['contact_full_name']= " `contact_full_name`='".sqlencode(trime( $contact_first_name[$i] .' ' . $contact_last_name[$i]))."', ";
			
			if( $action == 'addexe' || $password[$i]) {
				$sql['password']= " `password`='".md5($password[$i])."', ";
				if($confirm_password[$i] != $password[$i]) {
					$Errors[] = "Missmatch passwords";
				}
			} else {
				unset( $sql['password'] );
			}
			
			if( $agenda_send_time_str[$i] ) {
				$_agenda_time = is_time( $agenda_send_time_str[$i] );
				if($_agenda_time) {
					$agenda_send_time = sprintf("%02d%02d", $_agenda_time['H'], $_agenda_time['m']);
					$sql['agenda_send_time_str']= " `agenda_send_time_str`='".sqlencode(trime($agenda_send_time_str[$i]))."', ";
					//$sql['agenda_send_time']= " `agenda_send_time`='".sqlencode(trime( $agenda_send_time ))."', ";
				}
			}
			else {
				$_agenda_time = true;
				$sql['agenda_send_time_str']= " `agenda_send_time_str`='', ";
				//$sql['agenda_send_time']= " `agenda_send_time`='0', ";
			}
		
			$emails = explode(',', $contact_email[$i]);
			$_contact_email = array();
			foreach($emails as $_email) {
				$_email = trim($_email);
				if(!isEmail($_email)) {
					$_contact_email = false;
					break;
				}
				$_contact_email[] = $_email;
			}
			if( $_contact_email ) {
				$_contact_email = implode(', ', $_contact_email);
				$sql['contact_email']= " `contact_email`='".sqlencode(trime($_contact_email))."', ";
			}

			$strSQL = "schools set " . implode('', $sql);

			if( empty($title[$i]) ) {
				$Errors[] = "Missing Title!!";
			} else if( !empty($info_email[$i]) && !isemail($info_email[$i]) ) {
				$Errors[] = "Invalid School E-mail!!";
			} else if( !isemail($email[$i]) ) {
				$Errors[] = "Missing or Invalid School's Login E-mail!!";
			} else if( $action == 'addexe' && empty($password[$i]) ) {
				$Errors[] = "Missing School's Password!!";
			} else if( empty($contact_first_name[$i]) ) {
				$Errors[] = "Missing Contact's First Name!!";
			} else if( empty($contact_last_name[$i]) ) {
				$Errors[] = "Missing Contact's Last Name!!";
			}
			else if( !$_contact_email ) {
				$Errors[] = "Missing/Invalid School Contact Emails!";
			}
			else if(!$_agenda_time) {
				$Errors[] = "Invalid Agenda Time!!";
			}
			else if( !isemail($contact_send_email[$i]) ) {
				$Errors[] = "Missing/Invalid School Send Email!";
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/schools/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){

							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/schools/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
							$Rimage->save('../uploads/schools/thumb/'.$file1[$i]);							
						}						 
					}
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						$strSQL ="update ".$strSQL." image='".sqlencode(trime($file1[$i]))."' where id=".$ids[$i];
					}
				}
			else:
				//Conditions and Queries while adding
				if (empty($title[$i])){
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/schools/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){
							
							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/schools/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT );
							$Rimage->save('../uploads/schools/thumb/'.$file1[$i]);							
						}							 
				    }
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						
						$q=mysql_query("SELECT max(rank) as max FROM schools");
						$r = mysql_fetch_object($q);
						
						$strSQL ="insert into ".$strSQL." image='".sqlencode(trime($file1[$i]))."', rank='".($r->max+1)."', time='".time()."' ";
					}
				}
			endif;
			if($flag){
				$q = mysql_query($strSQL);
				if( $q ) {
					$_school_id = ($action=="editexe") ? $ids[$i] : mysql_insert_id();
					
					setSectionCategories($_school_id, 'news_category', $news_category[$i]);
					setSectionCategories($_school_id, 'documents_category', $documents_category[$i]);
					
//					$index_id = mysql_insert_id();
//					include('functions/_index_create_all.php');
				} else {
					if( mysql_errno() == 1062) {
						$errorMsg[$j] = 'E-mail (login) already exists!';
					} else {
						$errorMsg[$j] = 'Something went wrong!';
                                                //$errorMsg[$j] = $strSQL.' '. mysql_error();
					}
//					$errorMsg[$j] = $strSQL.' '. mysql_error();
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
		$strSQL="select * from schools where id IN(".implode(',',$ids).") order by rank DESC";
		$objRS=mysql_query($strSQL);
		while ($row=mysql_fetch_object($objRS))
		{
			foreach($relatedArray as $tpl=>$eMsg) {
				$q = mysql_query("SELECT * FROM `$tpl` WHERE school_id = '".$row->id ."' LIMIT 1");
				if( $q && mysql_num_rows($q)) {
					if( empty($errorMsg) ) {
						$errorMsg = "Some Records didn't affected, You may need to delete/update all {$eMsg} related to the deleted school!!";
					}
					continue 2;
				}
			}

			if($row->image != '') {
				@unlink('../uploads/schools/'.$row->image);	
				@unlink('../uploads/schools/thumb/'.$row->image);	
			}

			$strSQL="DELETE FROM schools WHERE id = '{$row->id}' LIMIT 1";
		
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
	case "record":
		$newsCategories = array();
		$_q = mysql_query("SELECT * FROM `news_category` ORDER BY rank DESC");
		if($_q && mysql_num_rows($_q)) {
			while($cat = mysql_fetch_assoc($_q)) {
				$newsCategories[$cat['id']] = $cat['title'];
			}
		}
		$documentsCategories = array();
		$_q = mysql_query("SELECT * FROM `documents_category` ORDER BY rank DESC");
		if($_q && mysql_num_rows($_q)) {
			while($cat = mysql_fetch_assoc($_q)) {
				$documentsCategories[$cat['id']] = $cat['title'];
			}
		}
?>

<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>" method="post" name="myform" enctype="multipart/form-data">
<input type="hidden" name="action" value="<? echo $action; ?>exe"> 
<article class="module width_full">
	<header><h3>Schools: <?php echo ucwords($action); ?> Record
		<input type="submit" value="Save" class="alt_btn">
		<input type="button" value="Cancel" onclick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>'">
		</h3></header>

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
					<label>Title</label>
					<input type="text" name="title[]" value="<? echo textencode($title[$oldrecord[$i]]); ?>" />
				</fieldset>
				<fieldset>
					<label>Abbreviation</label>
					<input type="text" name="abbreviation[]" value="<? echo textencode($abbreviation[$oldrecord[$i]]); ?>" />
				</fieldset>


				<fieldset>
					<label>School's Login</label>
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
				<fieldset>
					<label>School's Info</label>
					<table width="60%" border="0" cellpadding="5" cellspacing="0">
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
					</table>
				</fieldset>
			<?php 
					$_id = ($action=='add') ? 0 : $ids[$oldrecord[$i]];
					$schoolNewsCategories = getSectionsCategoriesFromIndex('news_category', $_id);
					$schoolDocumentsCategories = getSectionsCategoriesFromIndex('documents_category', $_id);
			?>
				<fieldset>
					<label>School's Categories</label>
					<div style="margin-left: 210px;" >
						<div style="width:49%; float:left;">
							<h3>News categories</h3>
					<?php foreach($newsCategories as $k=>$v) { ?>
							<label style="float: none; width:auto;height:auto;">
								<input type="checkbox" name="news_category[<?php echo $i; ?>][]" value="<?php echo $k; ?>" <?php echo ($schoolNewsCategories[$k]) ? ' checked="CHECKED" ': '';?> >
								<?php echo $v; ?>
							</label>
					<?php } ?>
						</div>
						<div style="width:49%; float:left;">
							<h3>Documents categories</h3>
					<?php foreach($documentsCategories as $k=>$v) { ?>
							<label style="float: none; width:auto;height:auto;">
								<input type="checkbox" name="documents_category[<?php echo $i; ?>][]" value="<?php echo $k; ?>" <?php echo ($schoolDocumentsCategories[$k]) ? ' checked="CHECKED" ': '';?> >
								<?php echo $v; ?>
							</label>
					<?php } ?>
						</div>
						<div class="clear"></div>
					</div>
				</fieldset>
				
				
				
				
				
				
				<fieldset>
					<label>School's Plan</label>
					<table width="60%" border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td>
								<label>Students
									<br /><input type="text" name="plan_students[]" value="<? echo textencode($plan_students[$oldrecord[$i]]); ?>" />
									<br />[ZERO] = unlimited
								</label>
							</td>
							<td>
								<label>Expired Date
									<br /><input type="text" class="datepicker" name="plan_expired[]" value="<? echo textencode($plan_expired[$oldrecord[$i]]); ?>" />
									<br />[ZERO] = unlimited
								</label>
							</td>
							<td>
								<label>Agenda E-mail Time
									<br /><input type="text" class="timepicker" name="agenda_send_time_str[]" value="<? echo textencode($agenda_send_time_str[$oldrecord[$i]]); ?>" />
									<br />[EMPTY] = disable
								</label>
							</td>
						</tr>
					</table>
				</fieldset>

				<fieldset>
					<label>Contact's Info</label>
					<table width="60%" border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td>
								<label>First Name
									<br /><input type="text" name="contact_first_name[]" value="<? echo textencode($contact_first_name[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label>Last Name
									<br /><input type="text" name="contact_last_name[]" value="<? echo textencode($contact_last_name[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label>School Send E-mail
									<br /><input type="text" name="contact_send_email[]" value="<? echo textencode($contact_send_email[$oldrecord[$i]]); ?>" />
								</label>
							</td>
						</tr>
						<tr>
							<td>
								<label>Contact School E-mails
									<br /><input type="text" name="contact_email[]" value="<? echo textencode($contact_email[$oldrecord[$i]]); ?>" />
									<br />Separate email addresses with comma ( , )
								</label>
							</td>
							<td>
								<label>Phone
									<br /><input type="text" name="contact_phone[]" value="<? echo textencode($contact_phone[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label>Mobile
									<br /><input type="text" name="contact_mobile[]" value="<? echo textencode($contact_mobile[$oldrecord[$i]]); ?>" />
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
					<label>Logo</label>
					<?php file_field('file1'.$i,'../uploads/schools/',$file1[$oldrecord[$i]]);?>
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
		Find: <input name="keyword" value="<? echo my_htmlspecialchars($keyword, ENT_QUOTES); ?>" size="30" />
		Status: <select name="status">
			<option value="" >== Any ==</option>
			<option value="active" <?php echo selected($_GET['status'], 'active'); ?>>Active</option>
			<option value="inactive" <?php echo selected($_GET['status'], 'inactive'); ?>>Inactive</option>
		</select>

		<input type="submit" value="Browse" />
	</form>
</div>
<?php 
		$sql = array();
		if( !empty($keyword) )
		{
			$keywordSQL = mysql_real_escape_string($keyword);
			$keywordSQL = str_replace(' ', '% %', $keywordSQL);
			
			$sql[] = " (schools.title LIKE '%$keywordSQL%' OR schools.description LIKE '%$keywordSQL%' ) ";
		}

//		if($Category) {
//			$sql[] = " schools.cat_id = '{$Category['id']}' ";
//		}
		switch($_GET['status']) {
			case 'active':
				$sql[] = " schools.status='active' ";
				break;
			case 'inactive':
//				$sql[] = " schools.status='' ";
				$sql[] = " schools.status<>'active' ";
				break;
		}

		$where = ( $sql ) ? " WHERE " . implode(' AND ', $sql ) : '';

		$strSQL="SELECT schools.*
		FROM schools

		$where
		GROUP BY schools.id
		";

		
		if( $action == 'export' ) {
			include 'functions/_export.php';
			
			$excel = new export_excel("Schools");
			$excel->addField('title', 'Title');
			$excel->addField('description', 'Description');
			
			$excel->addField('contact_full_name', 'Contact\'s name');
			$excel->addField('contact_phone', 'Contact\'s Phone');
			$excel->addField('contact_mobile', 'Contact\'s Mobile');
			$excel->addField('contact_email', 'Contact\'s Emails');
			
			$excel->addField('info_phone', 'School\'s Phone');
			$excel->addField('info_email', 'School\'s Email');
			$excel->addField('info_mobile', 'School\'s Mobile');

			
			$excel->addField('id', 'Total Students', 'count_students_in_school');
			$excel->addField('id', 'Total Users', 'count_users_in_school');
			
			
			$excel->addField('status', 'Status', 'ucwords');
			$excel->export( "$strSQL ORDER BY schools.rank DESC");
			exit;
		}

		$objRS=mysql_query($strSQL);
		$total=@mysql_num_rows($objRS);
		$strSQL=makePages ($strSQL,$PageSize,$p, 'schools.rank desc');
		$objRS=mysql_query($strSQL);
		$count_all = @mysql_num_rows($objRS);
		
?>


<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=delete" method="post" name="del" id="del">
<article class="module width_full">
	<header><h3 class="tabs_involved">Schools Manager</h3>
	<ul class="tabs">
   		<li><a href="#add" onClick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=add'" >Add New</a></li>
   		<li><a href="#edit" onClick="edit('<?=$menu?>&<?php echo $queryStr; ?>p=<?=$p?>')" >Edit</a></li>
    	<li><a href="#delete" onClick="conf()">Delete</a></li>
	</ul>
	<ul class="tabs">
		<li><a href="#Export" onClick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=export'" >Export List</a></li>
	</ul>
	</header>
	<div class="tab_container">
		<table class="tablesorter" cellspacing="0"> 
		<thead> 
			<tr> 
   				<th width="1"><input type="checkbox" onClick="checkall()" name="main"></th>
                <th width="1">Logo</th>
    			<th>Title</th> 
    			<th>Info</th>
    			<th>Contact</th>
    			<th>Classes</th>
    			<th width="1">Total Students</th>
    			<th width="1">Total Users</th>
                <th width="1">Status</th>
                <th width="1">Rank</th>
			</tr> 
		</thead> 
		<tbody id="trContainer" class="sortable"> 

	<?php while ($row=mysql_fetch_object($objRS)){ ?>
    <tr id="tr_<? echo $row->id; ?>">
		<td align="right"><input type="checkbox" name="ids[]" value="<? echo $row->id; ?>"></td>
		<td align="center"><?php echo scale_image("../uploads/schools/thumb/". $row->image, 100); ?></td>
		<td>
			<div><b><? echo $row->title ?></b> <?php echo ($row->abbreviation) ? " ({$row->abbreviation})": ''; ?></div>
			<div><?php if($row->description !=''){ echo summarize($row->description, 20); }?></div>
			<div>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>action=edit&ids[]=<? echo $row->id; ?>">Edit School</a>
			</div>
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
		<td>
			<div><b><?php $row->contact_first_name; ?> <?php $row->contact_last_name; ?></b></div>
		<?php if( $row->contact_phone ) { ?>
			<div><b>Phone:</b> <? echo $row->contact_phone; ?></div>
		<?php } ?>
		<?php if( $row->contact_mobile ) { ?>
			<div><b>Mobile:</b> <? echo $row->contact_mobile; ?></div>
		<?php } ?>
		<?php if( $row->contact_email ) { ?>
			<div><b>Emails:</b> <? echo $row->contact_email; ?></div>
		<?php } ?>
		</td>
		<td>
			<div>
				<?php 
					$classes = get_school_classes($row->id);
					foreach($classes as $_class) {
					?>
						<div>- <a href="classes.php?school_id=<? echo $row->id; ?>"><?php echo $_class['title']; ?></a></div>
					<?php 
					}
				?>
				<br />
				<div><a href="classes.php?school_id=<?php echo $row->id; ?>&action=add"><u>Add Class</u></a></div>
			</div>
		</td>
		<td align="center"><?php echo count_students_in_school( $row->id ); ?></td>
		<td align="center"><?php echo count_users_in_school( $row->id ); ?></td>
		<td align="center"><?php echo ( $row->status == 'active') ? 'Active' : 'Inactive'; ?></td>

		<td><?php echo_SetOrder( $queryStr, $row->id, $p ); ?></td>
	</tr>
	<?php }?>
			</tbody> 
			</table>
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

function count_students_in_school( $school_id ) {
	$school_id = intval($school_id);
	$q = mysql_query("SELECT count(*) as count FROM students WHERE school_id='{$school_id}' ");
	if( $q && mysql_num_rows( $q )) {
		$t = mysql_fetch_assoc( $q );
		return $t['count'];
	}
	
	return 0;
}

function count_users_in_school( $school_id ) {
	$school_id = intval($school_id);
	$q = mysql_query("SELECT count(*) as count , users.*
		FROM users, students 
		WHERE students.school_id='{$school_id}' 
			AND (
				students.info_mobile = users.username
				OR students.info_father_mobile = users.username
				OR students.info_mother_mobile = users.username
			)
		GROUP BY students.school_id
		");
	if( $q && mysql_num_rows( $q )) {
		$t = mysql_fetch_assoc( $q );
		return $t['count'];
	}
	
	return 0;
}




