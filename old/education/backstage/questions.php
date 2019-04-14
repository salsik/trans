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
$limitationUser = ' true ';
if(isSchool) {
	$limitation = " AND questions.school_id='".isSchool."' ";
//	$limitationUser = " users.school_id = '".isSchool."' ";
	$limitationUser = " users.id IN (
		SELECT users.id 
		FROM users, students 
		WHERE users.username <> ''
		WHERE students.school_id = '".isSchool."'
		AND (
			students.info_mobile = users.username
			OR students.info_father_mobile = users.username
			OR students.info_father_mobile = users.username
			)
	)
	";
}
else if( !isAdmin ) {
	die();
}

$AllowAdd = false;
$AllowImg = false;


$fieldsArray = array(
	'title', 'description', 'status',
	'name', 'email', 'phone', 'address',
);

$queryStr = '';
if( $keyword ) {
	$queryStr .= "keyword=$keyword&";
}

$_GET['contact'] = trim( $_GET['contact'] );
if( $_GET['contact'] ) {
	$queryStr .= "contact={$_GET['contact']}&";
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

if( !isSchool ) {
	$School = getDataByID('schools', $_GET['school_id']);
	if( $School ) {
		$queryStr .= "school_id={$School['id']}&";
	}
}

$User = getDataByID('users', $_GET['user_id'], $limitationUser);
if( $User ) {
	$queryStr .= "user_id={$User['id']}&";
}

//Define the maximum items while listine
$PageSize=10;
//Define the maximum items while adding
$max=1;

if($School) {
	?><h4 class="alert_info">School: <?php echo $School['title']; ?></h4><?php 
}
if($User) {
	?><h4 class="alert_info">User: <?php echo $User['title']; ?></h4><?php 
}

switch ($action):

	case "add":
		if( !$AllowAdd ) {
			break;
		}
		$showing = "record";
	break;
	case "edit":
		//Get needed data from the DB
		$strSQL="select * from questions where id IN(".implode(',',$ids).") $limitation order by id DESC";
		$objRS=mysql_query($strSQL);
		$i=0;
		while ($row=mysql_fetch_object($objRS)){
			foreach($fieldsArray as $field) {
				${$field}[$i] = $row->$field;
			}

			$school_id[$i] = $row->school_id;
			$user_id[$i] = $row->user_id;
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
	case "addexe":
		if( !$AllowAdd ) {
			break;
		}
	case "editexe":
		//Get new data from the FORM
		foreach($fieldsArray as $field) {
			${$field} = $_POST[$field];
		}
		$school_id = $_POST['school_id'];
		$user_id = $_POST['user_id'];

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

			if( $action == 'addexe' && isSchool) {
				$sql['add_by_id']= " `add_by_id`='".isSchool."', ";
			}
			
			if( $action == 'addexe' || true) {
				if( isSchool) {
					$sql['school_id']= " `school_id`='".isSchool."', ";
				} else {
					$_school = getDataByID('schools', $school_id[$i]);
	
					if( !$_school) {
						$Errors[] = "Missing School!!";
	//					$sql['school_id']= " `school_id`='0', ";
					} else {
						$sql['school_id']= " `school_id`='{$_school['id']}', ";
					}
				}
			
				$_user = getDataByID('users', $user_id[$i], $limitationUser);

				if( !$_user) {
					$Errors[] = "Missing User!!";
				} else {
					$sql['user']= " `user_id`='{$_user['id']}', ";
				}
			}

			$strSQL = "questions set " . implode('', $sql);

			if( empty($title[$i]) ) {
				$Errors[] = "Missing Title!!";
			} else if( empty($description[$i]) ) {
				$Errors[] = "Missing Description!!";
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/questions/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){

							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/questions/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
							$Rimage->save('../uploads/questions/thumb/'.$file1[$i]);							
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/questions/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){
							
							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/questions/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT );
							$Rimage->save('../uploads/questions/thumb/'.$file1[$i]);							
						}							 
				    }
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						
						$q=mysql_query("SELECT max(rank) as max FROM questions");
						$r = mysql_fetch_object($q);
						
						$strSQL ="insert into ".$strSQL." 
							image='".sqlencode(trime($file1[$i]))."'
							, rank='".($r->max+1)."'
							, date='".date('Y-m-d')."'
							, time='".time()."' ";
					}
				}
			endif;
			if($flag){
				$q = mysql_query($strSQL);
				if( $q ) {
					if($action == 'addexe') {
						$_insert_id = mysql_insert_id();
					} else {
						$_insert_id = intval( $ids[$i] );
					}
					$_action = ( $action == 'addexe' ) ? 'add' : 'edit';
					setUpdatedRow('questions', $_insert_id, $_action);

				}
				else{
					$errorMsg[$j] = 'Something went wrong!';
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

		$strSQL="select * from questions where id IN(".implode(',',$ids).") $limitation order by id DESC";
		$objRS=mysql_query($strSQL);
		while ($row=mysql_fetch_object($objRS))
		{

			if($row->image != '') {
				@unlink('../uploads/questions/'.$row->image);	
				@unlink('../uploads/questions/thumb/'.$row->image);	
			}

			$strSQL="DELETE FROM questions WHERE id = '".$row->id ."' LIMIT 1";
			if(!mysql_query($strSQL) ) {
				if(empty($errorMsg)) {
					$errorMsg="Some Records didn't affected!!";
				}
			} else {
				setUpdatedRow('questions', $row->id, 'delete');
				setUpdatedRow('setUpdatedRowSql', " question_id = '".$row->id ."' ", 'delete');
				$strSQL="DELETE FROM questions_replies WHERE question_id = '".$row->id ."' ";
				mysql_query($strSQL);
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

		include 'functions/_auto_complete.php';
?>

<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>" method="post" name="myform" enctype="multipart/form-data">
<input type="hidden" name="action" value="<? echo $action; ?>exe"> 
<article class="module width_full">
	<header><h3>Questions: <?php echo ucwords($action); ?> Record
		<input type="submit" value="Save" class="alt_btn">
		<input type="button" value="Cancel" onclick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>'">
		</h3></header>

		<?php for($i=0;$i<$max;$i++){ ?>
<?php 
		
		
		$_question = array();
		if( $action == 'edit') {
			$_question = getDataByID('questions', $ids[$i], " TRUE $limitation $limitation2 ");
		}

?>
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
					<label>Details</label>
					<table width="60%" border="0" cellpadding="5" cellspacing="0">
						<tr>
				<?php if( !isSchool ) { ?>
							<td>
								<label>School
								<br />
			<?php
					if( $action == 'edit') {
						$school = getDataByID('schools', $_question['school_id'] );
					} else {
						$SchoolID = nor( $school_id[$oldrecord[$i]], $_GET['school_id'], true);
						$school = getDataByID('schools', $SchoolID);
					}
			?>
					<span class="autocomplete" data-link="_get.php?from=schools">
						<input class="input_short auto_name" type="text" name="school_name[]" value="<?php echo trim( $school['title']); ?>" />
						<input class="input auto_id" type="hidden" name="school_id[]" value="<?php echo $school['id']; ?>" />
					</span>
								</label>
							</td>
				<?php } else { ?>
							<td><label></label></td>
				<?php } ?>
							<td>
								<label>User
								<br />
			<?php
					if( $action == 'edit') {
						$user = getDataByID('users', $_question['user_id'] );
					} else {
						$doctID = nor( $user_id[$oldrecord[$i]], $_GET['user_id'], true);
						$user = getDataByID('users', $doctID, $limitationUser );
					}
					?>
					<span class="autocomplete" data-link="_get.php?from=users">
						<input class="input_short auto_name" type="text" name="user_name[]" value="<?php echo trim( $user['title']); ?>" />
						<input class="input auto_id" type="hidden" name="user_id[]" value="<?php echo $user['id']; ?>" />
					</span>
								</label>
							</td>
							<td><label></label></td>
						</tr>
					</table>
				</fieldset>

				<fieldset>
					<label>Title</label>
					<input type="text" name="title[]" value="<? echo textencode($title[$oldrecord[$i]]); ?>" />
				</fieldset>

				<fieldset>
					<label>Contact Name</label>
					<input type="text" name="name[]" value="<? echo textencode($name[$oldrecord[$i]]); ?>" />
				</fieldset>
				<fieldset>
					<label>Contact Email</label>
					<input type="text" name="email[]" value="<? echo textencode($email[$oldrecord[$i]]); ?>" />
				</fieldset>
				<fieldset>
					<label>Contact Phone</label>
					<input type="text" name="phone[]" value="<? echo textencode($phone[$oldrecord[$i]]); ?>" />
				</fieldset>
				<fieldset>
					<label>Contact Address</label>
					<input type="text" name="address[]" value="<? echo textencode($address[$oldrecord[$i]]); ?>" />
				</fieldset>
				
				<fieldset>
					<label>Description</label>
					<textarea name="description[]" rows="12" ><? echo textencode($description[$oldrecord[$i]]); ?></textarea>
				</fieldset>
<?php if( $AllowImg ) { ?>
				<fieldset>
					<label>Image</label>
					<?php file_field('file1'.$i,'../uploads/questions/',$file1[$oldrecord[$i]]);?>
				</fieldset>
<?php } ?>
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
?>


<?php if(!empty($msg)) { ?>
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
<?php if( !isSchool ) { ?>
		School: <span class="autocomplete" data-link="_get.php?from=schools">
			<input class="input_short auto_name" type="text" name="school_name" value="<?php echo trim( $School['title']); ?>" />
			<input class="input auto_id" type="hidden" name="school_id" value="<?php echo $School['id']; ?>" />
		</span>
<?php } ?>

		User: <span class="autocomplete" data-link="_get.php?from=users">
			<input class="input_short auto_name" type="text" name="user_name" value="<?php echo trim( $user['title']); ?>" />
			<input class="input auto_id" type="hidden" name="user_id" value="<?php echo $user['id']; ?>" />
		</span>
		<input type="submit" value="Browse" />
	</form>
</div>
<?php 
		$sql = array();
		if( !empty($keyword) )
		{
			$keywordSQL = $keyword;
			$keywordSQL = mysql_real_escape_string($keywordSQL);
			$keywordSQL = str_replace(' ', '% %', $keywordSQL);
			
			$sql[] = " (questions.title LIKE '%$keywordSQL%' OR questions.description LIKE '%$keywordSQL%' ) ";
		}
		
		if( $_GET['contact'] ) {
			
			$contactSQL = $_GET['contact'];
			$contactSQL = mysql_real_escape_string($contactSQL);
			$contactSQL = str_replace(' ', '% ', $contactSQL);
			
			$sql[] = " (questions.name LIKE '%$contactSQL%' 
				OR questions.phone LIKE '%$contactSQL%' 
				OR questions.address LIKE '%$contactSQL%' 
				OR questions.email LIKE '%$contactSQL%' 
				) ";
		}

		if(isSchool) {
			$sql[] = " questions.school_id = '". isSchool ."' ";
		} 
		else if($School) {
			$sql[] = " questions.school_id = '{$School['id']}' ";
		}
		if($User) {
			$sql[] = " questions.user_id = '{$User['id']}' ";
		}
		switch($_GET['status']) {
			case 'active':
				$sql[] = " questions.status='active' ";
				break;
			case 'inactive':
//				$sql[] = " questions.status='' ";
				$sql[] = " questions.status<>'active' ";
				break;
		}
		
		$where = ( $sql ) ? " WHERE " . implode(' AND ', $sql ) : '';

		$strSQL="SELECT questions.*
			, schools.title as school_title
			, users.title as user_full_name, users.username as user_username
		FROM ( questions 
		LEFT JOIN schools ON (schools.id=questions.school_id) )
		LEFT JOIN users ON (users.id=questions.user_id)
		$where";

		
		if( $action == 'export' ) {
			include 'functions/_export.php';
			
			$excel = new export_excel("Questions");
			$excel->addField('title', 'Title');
			$excel->addField('description', 'Description');
			if( !isSchool ) {
				$excel->addField('school_title', 'School');
			}
			$excel->addField('user_full_name', 'User');
			
			$excel->addField('name', 'Contact Name');
			$excel->addField('email', 'Contact Email');
			$excel->addField('phone', 'Contact Phone');
			$excel->addField('address', 'Contact Address');
			
			$excel->addField('date', 'Date');
			
			$excel->addField('status', 'Status', 'ucwords');
			$excel->export( "$strSQL ORDER BY questions.id DESC");
			exit;
		}
		$objRS=mysql_query($strSQL);
		$total=@mysql_num_rows($objRS);
		$strSQL=makePages ($strSQL,$PageSize,$p, 'questions.id desc');
		$objRS=mysql_query($strSQL);
		$count_all = @mysql_num_rows($objRS);
		
?>


<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=delete" method="post" name="del" id="del">
<article class="module width_full">
	<header><h3 class="tabs_involved">Questions Manager</h3>
	<ul class="tabs">
<?php if( $AllowAdd ) { ?>
   		<li><a href="#add" onClick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=add'" >Add New</a></li>
<?php } ?>
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
    			<th>Title</th> 
    	<?php if( !isSchool ) { ?>
    			<th>School</th> 
    	<?php } ?>
                <th>User</th>
                <th>Contact</th>
                <th>Students</th>

                <th width="1">Date</th>
                <th width="1">Status</th>

                <th>Replies</th>
<?php if( $AllowImg ) { ?>
                <th width="1">Image</th>
<?php } ?>
			</tr> 
		</thead> 
		<tbody id="trContainer"> 

	<?php while ($row=mysql_fetch_object($objRS)){ ?>
<?php 

	$replies = array();
	$sql = "SELECT count(*) FROM questions_replies WHERE question_id = '$row->id' ";
	$sql = "SELECT count(*) as count, foo.* FROM (SELECT * FROM `questions_replies` WHERE question_id = '$row->id' ORDER BY `time` DESC) as foo group by foo.question_id ";
	$q = mysql_query( $sql );
	if( $q && mysql_num_rows( $q )) {
		$replies = mysql_fetch_assoc( $q );
	}
	
	$relatedStudents = array();
	$user_username = mysql_real_escape_string($row->user_username);
	$sql = "SELECT * 
		FROM students 
		WHERE ( students.info_mobile = '{$user_username}'
			OR students.info_father_mobile = {$user_username}
			OR students.info_father_mobile = {$user_username}
			)
		";
	$q = mysql_query( $sql );
//echo mysql_error();
	if( $q && mysql_num_rows( $q )) {
		while($student = mysql_fetch_assoc( $q ) ) {
			$relatedStudents[] = $student;
		}
	}
?>
    <tr id="tr_<? echo $row->id; ?>">
		<td align="right"><input type="checkbox" name="ids[]" value="<? echo $row->id; ?>"></td>
		<td>
			<div><b><? echo $row->title ?></b></div>
			<div><?php if($row->description !=''){ echo summarize($row->description, 20); }?></div>
			<div>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>action=edit&ids[]=<? echo $row->id; ?>">Edit</a>
			</div>
		</td>
	<?php if( !isSchool ) { ?>
		<td>
			<div><b><? echo $row->school_title; ?></b></div>
		</td>
	<?php } ?>
	
		<td>
			<div><b><? echo $row->user_full_name; ?></b></div>
		</td>
		<td>
			<div><b>Name:</b> <?php echo ($row->name) ? $row->name : 'N/A'; ?></div>
			<div><b>Email:</b> <?php echo ($row->email) ? $row->email : 'N/A'; ?></div>
			<div><b>Phone:</b> <?php echo ($row->phone) ? $row->phone : 'N/A'; ?></div>
			<div><b>Address:</b> <?php echo ($row->address) ? $row->address : 'N/A'; ?></div>
		</td>
		<td>
	<?php 
		if($relatedStudents) {
			foreach($relatedStudents as $student) {
				$_class = getDataByID('classes', $student['class_id']);
				?><div>- <? echo $student['full_name']; ?> [<?php echo $_class['title']; ?>]</div><?php 
			}
		}
	?>
			
		</td>
		
		
		<td>
			<div><? echo $row->date; ?></div>
		</td>
		<td align="center"><?php echo ( $row->status == 'active') ? 'Active' : 'Inactive'; ?></td>
		
		
		<td>
			<div><a 
				href="questions_replies.php?question_id=<?php echo $row->id; ?>">View&nbsp;Replies&nbsp;(<? echo intval($replies['count']); ?>)</a>&nbsp;-&nbsp;<a 
				href="questions_replies.php?question_id=<?php echo $row->id; ?>&action=add">Add&nbsp;Reply</a>
			 </div>
	<?php if( $replies ) { ?>
			<br />
			<div><b>-- Last Reply --</b></div>
			<div><b>Date: </b><?php echo $replies['date']; ?></div>
			<div><b>From: </b><?php echo ($replies['from'] == 'school') ? "$row->school_title": "$row->user_full_name"; ?></div>
<!--			<div><b><?php echo $replies['title'] ?></b></div>-->
			<div><?php echo summarize($replies['description'], 20);?></div>
	<?php } ?>
		</td>
	<?php if( $AllowImg ) { ?>
		<td align="center"><?php echo scale_image("../uploads/questions/thumb/". $row->image, 100); ?></td>
	<?php } ?>
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
