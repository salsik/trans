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
if(isSchool) {
	$limitation = " AND school_id ='".isSchool."' ";
}
else if( !isAdmin ) {
	die();
}

define('index_table', 'teachers_index');

$enableStatus = true;

$relatedArray = array(
	'agenda' => " agenda ",
);

$queryStr = '';

include 'functions/_index_common_filters.php';

if( $enableStatus ) {
	switch($_GET['status']) {
		case 'active':
		case 'inactive':
			$queryStr .= "status={$_GET['status']}&";
			break;
		default:
			$_GET['status'] == '';
			break;
	}
}

//Define the maximum items while listine
$PageSize=10;
//Define the maximum items while adding
$max=10;

require_once('_lister.class.php');
$sortableLister = new lister("_sort_reverse.php?filename=".$filename."&table=teachers");

$rank=getfield(getHTTP('rank'),"rank","teachers");


if($School) {
	?><h4 class="alert_info">School: <?php echo $School['title']; ?></h4><?php 
}

if($Class) {
	?><h4 class="alert_info">Class: <?php echo $Class['title']; ?></h4><?php 
}

switch ($action):
	//case "down":
		case "up": //reverse
		$strSQL="select * from teachers WHERE rank='".($rank+1)."'" ;
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update teachers set rank='".($rank+1)."' WHERE rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update teachers set rank='".$rank."' WHERE id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from teachers WHERE rank >'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update teachers set rank='".($rank+1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update teachers set rank='".$rank."' where id='".$row->id."'";
					mysql_query($strSQLord);
				}
			}
		}
	break;
	//case "up":
		case "down": //reverse
		$strSQL="select * from teachers where rank='".($rank-1)."'";
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update teachers set rank='".($rank-1)."' where rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update teachers set rank='".$rank."' where id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from teachers where rank <'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update teachers set rank='".($rank-1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update teachers set rank='".$rank."' where id='".$row->id."'";
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
		$strSQL="select * from teachers where id IN(".implode(',',$ids).") {$limitation} order by rank DESC";
		$objRS=mysql_query($strSQL);
		$i=0;
		while ($row=mysql_fetch_object($objRS)){
			$title[$i] = $row->title;
			$description[$i] = $row->description;
			
			$email[$i] = $row->email;
			$address[$i] = $row->address;
			$mobile[$i] = $row->mobile;
			
			$status[$i] = $row->status;
		
			$class_ids[$i] = array();
			$q = mysql_query("SELECT * FROM `".index_table."` WHERE index_id='{$row->id}' ");
			if($q && mysql_num_rows($q)) {
				while($indx = mysql_fetch_assoc($q)) {
					$class_ids[$i][ $indx['class_id'] ] = $indx['class_id'];
				}
			}
			
			$school_id[$i] = $row->school_id;

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
		$title = $_POST['title'];
		$description = $_POST['description'];
			
		$email = $_POST['email'];
		$password = $_POST['password'];
		$address = $_POST['address'];
		$mobile = $_POST['mobile'];

		$status = $_POST['status'];
		
		$class_ids = $_POST['class_ids'];
		
		$school_id = $_POST['school_id'];
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

			$strSQL="teachers set 
				title='".sqlencode(trime($title[$i]))."',
				description='".sqlencode(trime($description[$i]))."',

				email='".sqlencode(trime($email[$i]))."',
				mobile='".sqlencode(trime($mobile[$i]))."',
				address='".sqlencode(trime($address[$i]))."',
				
				status='".sqlencode(trime($status[$i]))."',
			";

			if( $action == 'addexe' || $password[$i]) {
				$strSQL .= " `password`='".md5($password[$i])."', ";
				if($confirm_password[$i] != $password[$i]) {
					$Errors[] = "Missmatch passwords";
				}
			}

			if( $action == 'addexe' && isSchool) {
				$strSQL .= " `add_by_id`='".isSchool."', ";
			}
			
			if( $schoolid ) {
				$strSQL .= " `school_id`='{$schoolid}', ";
			}

			if( empty($title[$i]) ) {
				$Errors[] = "Missing Title!!";
			} 
			else if( !$schoolid ) {
				$Errors[] = "Missing School!!";
			} 
			else if( !isemail($email[$i]) ) {
				$Errors[] = "Missing or Invalid Teacher's Login E-mail!!";
			} 
			else if( $action == 'addexe' && empty($password[$i]) ) {
				$Errors[] = "Missing Teacher's Password!!";
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/teachers/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){

							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/teachers/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
							$Rimage->save('../uploads/teachers/thumb/'.$file1[$i]);							
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/teachers/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){
							
							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/teachers/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT );
							$Rimage->save('../uploads/teachers/thumb/'.$file1[$i]);							
						}							 
				    }
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						$q=mysql_query("SELECT max(rank) as max FROM teachers");
						$r = mysql_fetch_object($q);

						$strSQL ="insert into ".$strSQL." image='".sqlencode(trime($file1[$i]))."', rank='".($r->max+1)."', time='".time()."' ";
					}
				}
			endif;
			if($flag){
				$q = mysql_query($strSQL);
				if( $q ) {
					if($action == 'addexe') {
						$insert_id = mysql_insert_id();
					} else {
						$insert_id = intval( $ids[$i] );
					}
					$_action = ( $action == 'addexe' ) ? 'add' : 'edit';
					setUpdatedRow('teachers', $insert_id, $_action);

					$index_id = $insert_id;
//print_r($_POST);
					include('functions/_index_create_all.php');

				} else {
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

		$strSQL="select * from teachers where id IN(".implode(',',$ids).") $limitation order by rank DESC";
		$objRS=mysql_query($strSQL);
		while ($row=mysql_fetch_object($objRS))
		{
			foreach($relatedArray as $tpl=>$eMsg) {
				$q = mysql_query("SELECT * FROM `$tpl` WHERE teacher_id = '".$row->id ."' LIMIT 1");
				if( $q && mysql_num_rows($q) ) {
					if( empty($errorMsg) ) {
						$errorMsg = "Some Records didn't affected, You may need to delete/update all {$eMsg} related to the deleted teacher!!";
					}
					continue 2;
				}
			}

			if($row->image != '') {
				@unlink('../uploads/teachers/'.$row->image);	
				@unlink('../uploads/teachers/thumb/'.$row->image);	
			}

			$strSQL="DELETE FROM teachers WHERE id = '".$row->id ."' LIMIT 1";
			if(!mysql_query($strSQL) ) {
				if( empty($errorMsg) ) {
					$errorMsg="Some Records didn't affected!!";
				}
			} else {

				setUpdatedRow('teachers', $row->id, 'delete');
				mysql_query("DELETE  FROM `".index_table."` WHERE index_id='{$row->id}' ");
			}
		}

		if(empty($errorMsg)) {
			$msg="Record(s) deleted successfully!!";
		}
	break;
endswitch;
?>



<?php
switch ($showing):
	case "record":
?>

<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>" method="post" name="myform" enctype="multipart/form-data">
<input type="hidden" name="action" value="<? echo $action; ?>exe"> 
<article class="module width_full">
	<header><h3>Teachers: <?php echo ucwords($action); ?> Record
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

<?php if( $enableStatus ) { ?>
				<fieldset>
					<label>Status</label>
					<select name="status[]">
							<option value="active" <?php echo selected($status[$oldrecord[$i]], 'active'); ?>> Active </option>
							<option value="" <?php echo selected($status[$oldrecord[$i]], '', isset($status[$oldrecord[$i]])); ?>> Inactive </option>
					</select>
				</fieldset>
<?php } ?>

				<fieldset>
					<label>Title</label>
					<input type="text" name="title[]" value="<? echo textencode($title[$oldrecord[$i]]); ?>" />
				</fieldset>

			<?php 
			
				include 'functions/_index_school_fields.php';
			
			?>

				<fieldset>
					<label>Teacher's Login</label>
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
					<label>Teacher's Info</label>
					<table width="60%" border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td>
								<label>Mobile
									<br /><input type="text" name="mobile[]" value="<? echo textencode($mobile[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label>Address
									<br /><input type="text" name="address[]" value="<? echo textencode($address[$oldrecord[$i]]); ?>" />
								</label>
							</td>
						</tr>
					</table>
				</fieldset>

				<fieldset>
					<label>Description</label>
					<textarea name="description[]" rows="12" ><? echo textencode($description[$oldrecord[$i]]); ?></textarea>
				</fieldset>

				<fieldset>
					<label>Image</label>
					<?php file_field('file1'.$i,'../uploads/teachers/',$file1[$oldrecord[$i]]);?>
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
	<form action="<?php echo $filename; ?>" method="GET" >
		Find: <input name="keyword" value="<? echo my_htmlspecialchars($keyword, ENT_QUOTES); ?>" size="30" />
<?php if( $enableStatus ) { ?>
		Status: <select name="status">
			<option value="" >== Any ==</option>
			<option value="active" <?php echo selected($_GET['status'], 'active'); ?>>Active</option>
			<option value="inactive" <?php echo selected($_GET['status'], 'inactive'); ?>>Inactive</option>
		</select>
<?php } ?>
		
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
			
			$sql[] = " (teachers.title LIKE '%$keywordSQL%' OR teachers.description LIKE '%$keywordSQL%' ) ";
		}
	if( $enableStatus ) {
		switch($_GET['status']) {
			case 'active':
				$sql[] = " teachers.status='active' ";
				break;
			case 'inactive':
//				$sql[] = " teachers.status='' ";
				$sql[] = " teachers.status<>'active' ";
				break;
		}
	}

		if(isSchool) {
			$sql[] = " teachers.school_id = '". isSchool ."' ";
		} else if($School) {
			$sql[] = " teachers.school_id = '{$School['id']}' ";
		}

		if( $Class ) {
			$sql[] = " classes.id='{$Class['id']}' ";
		}
		
		$where = ( $sql ) ? " WHERE " . implode(' AND ', $sql ) : '';

		$strSQL="SELECT teachers.*
		, GROUP_CONCAT( DISTINCT classes.title SEPARATOR '<~~>' ) as classes_titles
		, GROUP_CONCAT( DISTINCT classes.id SEPARATOR '<~~>' ) as classes_ids
		FROM teachers
		LEFT JOIN teachers_index ON (teachers.id = teachers_index.index_id)
		LEFT OUTER JOIN classes ON (teachers_index.class_id = classes.id)
		
		$where
		GROUP BY teachers.id
		";
		
		if( $action == 'export' ) {
			include 'functions/_export.php';
			
			$excel = new export_excel("Teachers");
			$excel->addField('title', 'Title');
			$excel->addField('description', 'Description');
			if( !isSchool ) {
				$excel->addField('school_id', 'School', 'get_title_from_array', $Schools);
			}

			$excel->addField('classes_titles', 'Classes', 'style_titles');
			
		if( $enableStatus ) {
			$excel->addField('status', 'Status', 'ucwords');
		}

			$excel->export( "$strSQL ORDER BY teachers.rank DESC");
			exit;
		}
		$objRS=mysql_query($strSQL);
		$total=@mysql_num_rows($objRS);
		$strSQL=makePages ($strSQL,$PageSize,$p, 'teachers.rank desc');
		$objRS=mysql_query($strSQL);
		$count_all = @mysql_num_rows($objRS);
		
?>


<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=delete" method="post" name="del" id="del">
<article class="module width_full">
	<header><h3 class="tabs_involved">Teachers Manager</h3>
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
    			<th>Title</th> 
			<?php if( !isSchool ) { ?>
    			<th>School</th>
			<?php } ?>
    			<th>Classes</th>
                <th>Info</th>
<?php if( $enableStatus ) { ?>
                <th width="1">Status</th>
<?php } ?>
                <th width="1">Image</th>
                <th width="1">Rank</th>
			</tr> 
		</thead> 
		<tbody id="trContainer" class="sortable"> 

	<?php while ($row=mysql_fetch_object($objRS)){ ?>
    <tr id="tr_<?php echo $row->id; ?>">
		<td align="right"><input type="checkbox" name="ids[]" value="<? echo $row->id; ?>"></td>
		<td>
			<div><b><? echo $row->title ?></b></div>
			<div><?php if($row->description !=''){ echo summarize($row->description, 20); }?></div>
			<div>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>action=edit&ids[]=<? echo $row->id; ?>">Edit Teacher</a>
			</div>
		</td>
		<?php if( !isSchool ) { ?>
    		<td><a href="<? echo $filename; ?>?<?php echo $queryStr; ?>school_id=<? echo $row->school_id; ?>"><?php echo $Schools[ $row->school_id ]['title']; ?></a></td>
		<?php } ?>
		<td>
			<div>
				<?php echo style_titles( $row->classes_titles, true); ?>
			</div>
		</td>
		<td>
			<div><b>E-mail&nbsp;(Login):</b>&nbsp;<?php echo $row->email; ?></div>
			<div><b>Mobile:</b>&nbsp;<?php echo $row->mobile; ?></div>
			<div><b>Address:</b>&nbsp;<?php echo $row->address; ?></div>
		</td>
<?php if( $enableStatus ) { ?>
		<td align="center"><?php echo ( $row->status == 'active') ? 'Active' : 'Inactive'; ?></td>
<?php } ?>
		<td align="center"><?php echo scale_image("../uploads/teachers/thumb/". $row->image, 100); ?></td>

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
