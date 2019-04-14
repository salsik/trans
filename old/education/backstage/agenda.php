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
	$limitation = " AND agenda.school_id ='".isSchool."' ";
}
else if(isTeacher) {
	$limitation = " AND agenda.teacher_id ='".isTeacher."' ";
}
else {
	die();
}


$queryStr = '';

$Teacher = array();
$Teachers = array();

if(isSchool) {
	$q = mysql_query("SELECT teachers.* FROM `teachers` WHERE school_id ='".isSchool."' ORDER BY teachers.title ASC");
	if( $q && mysql_num_rows($q ) ) {
		while( $row = mysql_fetch_assoc( $q )) {
			$Teachers[ $row['id'] ] = $row;
		}
	}
	
	$Classes = get_school_classes( isSchool, true);
}
else {
	$Classes = get_teacher_classes( isTeacher, true);
}

	
if( $Teachers[ $_GET['teacher_id'] ] ) {
	$Teacher = $Teachers[ $_GET['teacher_id'] ];
	
	$queryStr .= "teacher_id={$_GET['teacher_id']}&";
}

if( $Classes[ $_GET['class_id'] ] ) {
	$Class = $Classes[ $_GET['class_id'] ];
	
	$queryStr .= "class_id={$_GET['class_id']}&";
}

$enableStatus = false;

if( $keyword ) {
	$queryStr .= "keyword=$keyword&";
}

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

//require_once('_lister.class.php');
//$sortableLister = new lister("_sort_reverse.php?filename=".$filename."&table=agenda");
//
//$rank=getfield(getHTTP('rank'),"rank","agenda");

if($Class) {
	?><h4 class="alert_info">Class: <?php echo $Class['title']; ?></h4><?php 
}

if($Teacher) {
	?><h4 class="alert_info">Teacher: <?php echo $Teacher['title']; ?></h4><?php 
}

switch ($action):
//	//case "down":
//		case "up": //reverse
//		$strSQL="select * from agenda WHERE rank='".($rank+1)."'" ;
//		$objRS=mysql_query($strSQL);
//		$total=mysql_num_rows($objRS);
//		if($total>0){
//			if ($row=mysql_fetch_object($objRS)){
//				$strSQLord="update agenda set rank='".($rank+1)."' WHERE rank='".$rank."'";
//				mysql_query($strSQLord);
//				$strSQLord="update agenda set rank='".$rank."' WHERE id='".$row->id."'";
//				mysql_query($strSQLord);
//			}
//		}
//		else{
//			$strSQL="select * from agenda WHERE rank >'".$rank."'";
//			$objRS=mysql_query($strSQL);
//			$total=mysql_num_rows($objRS);
//			if($total>0){
//				if ($row=mysql_fetch_object($objRS)){
//					$strSQLord="update agenda set rank='".($rank+1)."' where rank='".$rank."'";
//					mysql_query($strSQLord);
//					$strSQLord="update agenda set rank='".$rank."' where id='".$row->id."'";
//					mysql_query($strSQLord);
//				}
//			}
//		}
//	break;
//	//case "up":
//		case "down": //reverse
//		$strSQL="select * from agenda where rank='".($rank-1)."'";
//		$objRS=mysql_query($strSQL);
//		$total=mysql_num_rows($objRS);
//		if($total>0){
//			if ($row=mysql_fetch_object($objRS)){
//				$strSQLord="update agenda set rank='".($rank-1)."' where rank='".$rank."'";
//				mysql_query($strSQLord);
//				$strSQLord="update agenda set rank='".$rank."' where id='".$row->id."'";
//				mysql_query($strSQLord);
//			}
//		}
//		else{
//			$strSQL="select * from agenda where rank <'".$rank."'";
//			$objRS=mysql_query($strSQL);
//			$total=mysql_num_rows($objRS);
//			if($total>0){
//				if ($row=mysql_fetch_object($objRS)){
//					$strSQLord="update agenda set rank='".($rank-1)."' where rank='".$rank."'";
//					mysql_query($strSQLord);
//					$strSQLord="update agenda set rank='".$rank."' where id='".$row->id."'";
//					mysql_query($strSQLord);
//				}
//			}
//		}
//
//	break;

	case "add":
		$showing = "record";
	break;
	case "edit":
		//Get needed data from the DB
		$strSQL="select * from agenda where id IN(".implode(',',$ids).") {$limitation} order by date DESC";
		$objRS=mysql_query($strSQL);
		$i=0;
		while ($row=mysql_fetch_object($objRS)){
			$title[$i] = $row->title;
			$description[$i] = $row->description;
			$status[$i] = $row->status;
			
			$date[$i] = $row->date;
			$class_id[$i] = $row->class_id;
			$teacher_id[$i] = $row->teacher_id;
			$app_notification[$i] = $row->app_notification;
			
			
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
		$status = $_POST['status'];

		$date = $_POST['date'];
		$class_id = $_POST['class_id'];
		$teacher_id = $_POST['teacher_id'];
		$app_notification = $_POST['app_notification'];

		//Counter for errors array according to the number of records.
		$j=0;
		//Array of "Errored" records.
		$oldrecord=array();
		//Start the loop over the records
		for($i=0;$i<sizeof($ids);$i++){
			$file1[$i] = $_POST['file1'.$i];
			
			//Set the flag to one in order to verify the conditions later
			$flag=1;
			
			$strSQL="agenda set 
				title='".sqlencode(trime($title[$i]))."',
				description='".sqlencode(trime($description[$i]))."',
				status='".sqlencode(trime($status[$i]))."',
				date='".sqlencode(trime($date[$i]))."',
			";
			if( $action == 'addexe' ) {
				$strSQL .= " app_notification='".sqlencode(trime($app_notification[$i]))."', ";
			}
			
			
			$Errors = array();
			
			if( $Classes[ $class_id[$i] ]) {
				$strSQL .= " class_id='".sqlencode(trime($class_id[$i]))."', ";
			}
			else {
				$Errors[] = "Missing Class!!";
			}
			
			$strSQL .= " school_id='".sqlencode(trime( $Admin['school_id'] ))."', ";
			if( isTeacher ) {
				$strSQL .= " teacher_id='".sqlencode(trime( isTeacher ))."', ";
			}
			else {
				if( $Teachers[ $teacher_id[$i] ] ) {
					$strSQL .= " teacher_id='".sqlencode(trime( $teacher_id[$i] ))."', ";
				}
				else {
					$Errors[] = "Missing Teacher!!";
				}
			}

			if( empty($description[$i]) ) {
				$Errors[] = "Missing Agenda!!";
			}
			
			$file1[$i] = "";

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
//					if ( $result=file_upload('image','file1'.$i,'../uploads/agenda/',$errorMsg[$j])){
//						 $file1[$i]=$result['name'];
//						if(empty($errorMsg[$j])){
//
//							$Rimage = new SimpleImage();
//							$Rimage->load('../uploads/agenda/'.$file1[$i]);
//							if( $Rimage->getWidth() > THUMB_WIDTH ) {
//								$Rimage->resizeToWidth( THUMB_WIDTH );
//							}
////							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
//							$Rimage->save('../uploads/agenda/thumb/'.$file1[$i]);							
//						}						 
//					}
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
				if (empty($description[$i])){
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
//					if ( $result=file_upload('image','file1'.$i,'../uploads/agenda/',$errorMsg[$j])){
//						 $file1[$i]=$result['name'];
//						if(empty($errorMsg[$j])){
//							
//							$Rimage = new SimpleImage();
//							$Rimage->load('../uploads/agenda/'.$file1[$i]);
//							if( $Rimage->getWidth() > THUMB_WIDTH ) {
//								$Rimage->resizeToWidth( THUMB_WIDTH );
//							}
////							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT );
//							$Rimage->save('../uploads/agenda/thumb/'.$file1[$i]);							
//						}							 
//				    }
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						
						$q=mysql_query("SELECT max(rank) as max FROM agenda");
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
					setUpdatedRow('agenda', $insert_id, $_action);
				}
				else {
					$errorMsg[$j] = 'Something went wrong!';
//					$errorMsg[$j] = "{$strSQL} " . mysql_error();
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

		$strSQL="select * from agenda where id IN(".implode(',',$ids).") {$limitation} order by date DESC";
		$objRS=mysql_query($strSQL);
		while ($row=mysql_fetch_object($objRS))
		{
			if($row->image != '') {
				@unlink('../uploads/agenda/'.$row->image);	
				@unlink('../uploads/agenda/thumb/'.$row->image);	
			}

			$strSQL="DELETE FROM agenda WHERE id = '".$row->id ."' LIMIT 1";
			if(!mysql_query($strSQL) ) {
				if( empty($errorMsg) ) {
					$errorMsg="Some Records didn't affected!!";
				}
			}
			else {
				setUpdatedRow('agenda', $row->id, 'delete');
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
?>

<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>" method="post" name="myform" enctype="multipart/form-data">
<input type="hidden" name="action" value="<? echo $action; ?>exe"> 
<article class="module width_full">
	<header><h3>Agenda: <?php echo ucwords($action); ?> Record
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
<?php 

	if( !$date[$oldrecord[$i]] ) {
		$date[$oldrecord[$i]] = date('Y-m-d');
	}

?>
<?php if( $enableStatus ) { ?>
				<fieldset>
					<label>Status</label>
					<select name="status[]">
							<option value="active" <?php echo selected($status[$oldrecord[$i]], 'active'); ?>> Active </option>
							<option value="" <?php echo selected($status[$oldrecord[$i]], '', isset($status[$oldrecord[$i]])); ?>> Inactive </option>
					</select>
				</fieldset>
<?php } ?>
			<?php if( false ) { ?>
				<fieldset>
					<label>Title</label>
					<input type="text" name="title[]" value="<? echo textencode($title[$oldrecord[$i]]); ?>" />
				</fieldset>
			<?php } ?>
				<fieldset>
					<label>Date</label>
					<div style="margin-left: 210px;">
						<input type="text" class="datepicker input_short" name="date[]" value="<? echo textencode($date[$oldrecord[$i]]); ?>" />
	
						<div class="clear"></div>
						<br />
						<label style="width: auto;">
						<?php 
							$falgs = '';
							$falgs .= ( $app_notification[$oldrecord[$i]] ) ? ' checked="CHECKED" ' : '';
							$falgs .= ( $action == 'edit' ) ? ' disabled="DISABLED" ' : '';
						?>
							<input type="checkbox" name="app_notification[<?php echo $i; ?>]" value="1" <?php echo $falgs; ?> />
							Send this agenda in push notification.
						</label>
						<div class="clear"></div>
					</div>
				</fieldset>
				<fieldset>
					<label>Agenda</label>
					<textarea name="description[]" rows="12" ><? echo textencode($description[$oldrecord[$i]]); ?></textarea>
				</fieldset>
			<?php if( isSchool ) { ?>
				<fieldset>
					<label>Teacher</label>
					<select name="teacher_id[]">
						<option value="">-- Select --</option>
					<?php 
						$sid = nor($teacher_id[$oldrecord[$i]], $Teacher['id']);
						foreach ($Teachers as $teacher) {
							$Selected = ( $teacher['id'] == $sid ) ? ' selected="selected" ' : '';
							?><option value="<?php echo $teacher['id'];?>" <?php echo $Selected; ?> ><?php echo $teacher['title'];?></option><?php 
						}
					?>
					</select>
				</fieldset>
			<?php } ?>
				<fieldset>
					<label>Class</label>
					<select name="class_id[]">
						<option value="">-- Select --</option>
					<?php 
						$sid = nor($class_id[$oldrecord[$i]], $Class['id']);
						foreach ($Classes as $_class) {
							$Selected = ( $_class['id'] == $sid ) ? ' selected="selected" ' : '';
							?><option value="<?php echo $_class['id'];?>" <?php echo $Selected; ?> ><?php echo $_class['title'];?></option><?php 
						}
					?>
					</select>
				</fieldset>

			<?php if( false ) { ?>
				<fieldset>
					<label>Image</label>
					<?php file_field('file1'.$i,'../uploads/agenda/',$file1[$oldrecord[$i]]);?>
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
?>

<?php if(!empty($msg)) { ?>
	<h4 class="alert_success"><?php echo $msg; ?></h4>
<?php } else if(!empty($errorMsg)) { ?>
	<h4 class="alert_error"><?php echo $errorMsg; ?></h4>
<?php } ?>


<div class="alert_browse">
	<form action="<? echo $filename; ?>" method="GET" >
		Find: <input name="keyword" value="<? echo my_htmlspecialchars($keyword, ENT_QUOTES); ?>" size="30" />
<?php if( $enableStatus ) { ?>
		Status: <select name="status">
			<option value="" >== Any ==</option>
			<option value="active" <?php echo selected($_GET['status'], 'active'); ?>>Active</option>
			<option value="inactive" <?php echo selected($_GET['status'], 'inactive'); ?>>Inactive</option>
		</select>
<?php } ?>

	<?php if( isSchool ) { ?>
		Teacher: <select name="teacher_id">
			<option value="">-- Select --</option>
		<?php 
			foreach ($Teachers as $teacher) {
				$Selected = ( $teacher['id'] == $Teacher['id'] ) ? ' selected="selected" ' : '';
				?><option value="<?php echo $teacher['id'];?>" <?php echo $Selected; ?> ><?php echo $teacher['title'];?></option><?php 
			}
		?>
		</select>
	<?php } ?>
	
	Class: <select name="class_id">
		<option value="">-- Select --</option>
	<?php 
		foreach ($Classes as $_class) {
			$Selected = ( $_class['id'] == $Class['id'] ) ? ' selected="selected" ' : '';
			?><option value="<?php echo $_class['id'];?>" <?php echo $Selected; ?> ><?php echo $_class['title'];?></option><?php 
		}
	?>
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
			
			$sql[] = " (agenda.title LIKE '%$keywordSQL%' OR agenda.description LIKE '%$keywordSQL%' ) ";
		}
		
		$sql[] = " TRUE {$limitation} ";
		
		if( $Teacher ) {
			$sql[] = " agenda.teacher_id='{$Teacher['id']}' ";
		}
		if( $Class ) {
			$sql[] = " agenda.class_id='{$Class['id']}' ";
		}
		
		
	if( $enableStatus ) {
		switch($_GET['status']) {
			case 'active':
				$sql[] = " agenda.status='active' ";
				break;
			case 'inactive':
//				$sql[] = " agenda.status='' ";
				$sql[] = " agenda.status<>'active' ";
				break;
		}
	}
		$where = ( $sql ) ? " WHERE " . implode(' AND ', $sql ) : '';

		$strSQL="SELECT agenda.* 
		, classes.title as class_title
		, teachers.title as teacher_name
		FROM agenda 
		LEFT JOIN classes ON (classes.id=agenda.class_id)
		LEFT OUTER JOIN teachers ON (teachers.id=agenda.teacher_id)

		$where";

		
		if( $action == 'export' ) {
			include 'functions/_export.php';
			
			$excel = new export_excel("Agenda");
//			$excel->addField('title', 'Title');
			
			$excel->addField('date', 'Date');
			$excel->addField('class_title', 'Class');

			if( isSchool ) {
				$excel->addField('teacher_name', 'Teacher', 'ucwords');
			}
			$excel->addField('description', 'Agenda');
			
			if( $enableStatus ) {
				$excel->addField('status', 'Status', 'ucwords');
			}
			$excel->export( "$strSQL ORDER BY agenda.date DESC");
			exit;
		}
		$objRS=mysql_query($strSQL);
//echo "$strSQL " . mysql_error();
		$total=@mysql_num_rows($objRS);
		$strSQL=makePages ($strSQL,$PageSize,$p, 'agenda.date desc');
		$objRS=mysql_query($strSQL);
		$count_all = @mysql_num_rows($objRS);
		
?>


<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=delete" method="post" name="del" id="del">
<article class="module width_full">
	<header><h3 class="tabs_involved">Agenda Manager</h3>
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
    			
    			<th width="1">Date</th> 
    			
    			<th>Class</th> 
    		<?php if( isSchool ) { ?>
    			<th>Teacher</th> 
    		<?php } ?>
    			
<?php if( $enableStatus ) { ?>
                <th width="1">Status</th>
<?php } ?>
                <th width="1">Image</th>
			</tr> 
		</thead> 
		<tbody id="trContainer" class="sortable"> 

	<?php while ($row=mysql_fetch_object($objRS)){ ?>
    <tr id="tr_<? echo $row->id; ?>">
		<td align="right"><input type="checkbox" name="ids[]" value="<? echo $row->id; ?>"></td>
		<td>
			<div><b><? // echo $row->title ?></b></div>
			<div><?php echo $row->description; ?></div>
			<br />
		<?php if( $row->app_notification ) { ?>
			<div><b>Sent in push notification</b></div>
		<?php } ?>
			<div>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>action=edit&ids[]=<? echo $row->id; ?>">Edit Agenda</a>
			</div>
		</td>
		<td align="center"><? echo $row->date; ?></td>
		
		<td>
			<div><a href="<? echo $filename; ?>?<?php echo $queryStr; ?>class_id=<? echo $row->class_id; ?>"><?php echo $row->class_title; ?></a></div>
		</td>
    <?php if( isSchool ) { ?>
		<td>
			<div><a href="<? echo $filename; ?>?<?php echo $queryStr; ?>teacher_id=<? echo $row->teacher_id; ?>"><?php echo $row->teacher_name; ?></a></div>
		</td>
	<?php } ?>
		
		
<?php if( $enableStatus ) { ?>
		<td align="center"><?php echo ( $row->status == 'active') ? 'Active' : 'Inactive'; ?></td>
<?php } ?>
		<td align="center"><?php echo scale_image("../uploads/agenda/thumb/". $row->image, 100); ?></td>

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
