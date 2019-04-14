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
	$limitation = " AND school_id='".isSchool."' ";
}
else if( !isAdmin ) {
	die();
}

define('index_table', 'videos_index');

$fieldsArray = array(
	'title', 'description', 'status', 
	'video',
);


$queryStr = '';

include 'functions/_index_common_filters.php';

//Define the maximum items while listine
$PageSize=10;
//Define the maximum items while adding
$max=10;

require_once('_lister.class.php');
$sortableLister = new lister("_sort_reverse.php?filename=".$filename."&table=videos");

$rank=getfield(getHTTP('rank'),"rank","videos");


if($School) {
	?><h4 class="alert_info">School: <?php echo $School['title']; ?></h4><?php 
}

if($Class) {
	?><h4 class="alert_info">Class: <?php echo $Class['title']; ?></h4><?php 
}

switch ($action):
	//case "down":
		case "up": //reverse
		$strSQL="select * from videos WHERE rank='".($rank+1)."'" ;
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update videos set rank='".($rank+1)."' WHERE rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update videos set rank='".$rank."' WHERE id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from videos WHERE rank >'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update videos set rank='".($rank+1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update videos set rank='".$rank."' where id='".$row->id."'";
					mysql_query($strSQLord);
				}
			}
		}
	break;
	//case "up":
		case "down": //reverse
		$strSQL="select * from videos where rank='".($rank-1)."'";
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update videos set rank='".($rank-1)."' where rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update videos set rank='".$rank."' where id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from videos where rank <'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update videos set rank='".($rank-1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update videos set rank='".$rank."' where id='".$row->id."'";
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
		$strSQL="select * from videos where id IN(".implode(',',$ids).") $limitation order by rank DESC";
		$objRS=mysql_query($strSQL);
		$i=0;
		echo mysql_error();
		while ($row=mysql_fetch_object($objRS)){
		
			foreach($fieldsArray as $field) {
				${$field}[$i] = $row->$field;
			}
			$class_ids[$i] = array();
			$q = mysql_query("SELECT * FROM `".index_table."` WHERE index_id='{$row->id}' ");
			if($q && mysql_num_rows($q)) {
				while($indx = mysql_fetch_assoc($q)) {
					$class_ids[$i][] = $indx['class_id'];
				}
			}

			$school_id[ $i ] = $row->school_id;

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
		$class_ids = $_POST['class_ids'];

		//Counter for errors array according to the number of records.
		$j=0;
		//Array of "Errored" records.
		$oldrecord=array();
		//Start the loop over the records
		for($i=0;$i<sizeof($ids);$i++){
			$file1[$i] = $_POST['file1'.$i];
			//Set the flag to one in order to verify the conditions later
			$flag=1;

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
			
			$Errors = array();
			$v = parse_youtube_url( $video[ $i ] );
			if( $v ) {
				$video[ $i ] = $v;
			}
			else {
				$Errors[] = "Invalid youtube link!";
			}
			
			$sql = array();
			foreach($fieldsArray as $field) {
				$sql[$field] = " `$field`='".sqlencode(trime(${$field}[$i]))."', ";
			}

			$strSQL = "videos set " . implode('', $sql);

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
			
			include 'functions/_index_check_all.php';

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
					if ( $result=file_upload('image','file1'.$i,'../uploads/videos/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){

							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/videos/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
							$Rimage->save('../uploads/videos/thumb/'.$file1[$i]);							
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/videos/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){

							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/videos/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
							$Rimage->save('../uploads/videos/thumb/'.$file1[$i]);							
						}						 
					}
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						$q=mysql_query("SELECT max(rank) as max FROM videos");
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
					$_action = ( $action == 'addexe' ) ? 'add' : 'edit';
					setUpdatedRow('videos', $doc_id, $_action);

					$index_id = $doc_id;
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
		// TODO deleting File by company?
		$strSQL="select * from videos where id IN(".implode(',',$ids).") $limitation order by rank DESC";
		$objRS=mysql_query($strSQL);
		while ($row=mysql_fetch_object($objRS))
		{
			if($row->image != '') {
				@unlink('../uploads/videos/'.$row->image);	
				@unlink('../uploads/videos/thumb/'.$row->image);	
			}

			$strSQL="DELETE FROM videos WHERE id = '{$row->id}' LIMIT 1";
		
			if(!mysql_query($strSQL) ) {
				if( empty($errorMsg) ) {
					$errorMsg="Some Records didn't affected!!";
				}
			}
			else {

				setUpdatedRow('videos', $row->id, 'delete');
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

?>

<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>" method="post" name="myform" enctype="multipart/form-data">
<input type="hidden" name="action" value="<? echo $action; ?>exe"> 
<article class="module width_full">
	<header><h3>Videos: <?php echo ucwords($action); ?> Record
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
					<label>Youtube</label>
					<input type="text" name="video[]" value="<? echo textencode($video[$oldrecord[$i]]); ?>" />
				</fieldset>

			<?php if( !isSchool ) { ?>
				<fieldset>
					<label>School</label>

					<select name="school_id[<?php echo $i; ?>]" class="school_id" >
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
				</fieldset>
			<?php } ?>
				<fieldset>
					<label>Classes</label>
					<div style="margin-left: 210px;">
						<?php include('functions/_index_table.php'); ?>
					</div>
				</fieldset>

				<fieldset>
					<label>Notes</label>
					<textarea name="description[]" rows="12" ><? echo textencode($description[$oldrecord[$i]]); ?></textarea>
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
	<form action="<? echo $filename; ?>" method="GET" >
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
			
			$sql[] = " (videos.title LIKE '%$keywordSQL%' OR videos.description LIKE '%$keywordSQL%' ) ";
		}

		if(isSchool) {
			$sql[] = " videos.school_id = '". isSchool ."' ";
		} else if($School) {
			$sql[] = " videos.school_id = '{$School['id']}' ";
		}

		if( $Class ) {
			$sql[] = " classes.id='{$Class['id']}' ";
		}

		switch($_GET['status']) {
			case 'active':
				$sql[] = " videos.status='active' ";
				break;
			case 'inactive':
//				$sql[] = " videos.status='' ";
				$sql[] = " videos.status<>'active' ";
				break;
		}

		$where = ( $sql ) ? " WHERE " . implode(' AND ', $sql ) : '';


		$strSQL="SELECT videos.*
		, GROUP_CONCAT( DISTINCT classes.title SEPARATOR '<~~>' ) as classes_titles
		, GROUP_CONCAT( DISTINCT videos_index.class_id SEPARATOR '<~~>' ) as classes_ids
		FROM videos
		LEFT JOIN videos_index ON (videos.id = videos_index.index_id)
		LEFT OUTER JOIN classes ON (videos_index.class_id = classes.id)
		
		$where
		GROUP BY videos.id
		";

		
		if( $action == 'export' ) {
			include 'functions/_export.php';
			
			$excel = new export_excel("Videos");
			$excel->addField('title', 'Title');
			$excel->addField('description', 'Description');


			if( !isSchool ) {
				$excel->addField('school_id', 'School', 'get_title_from_array', $Schools);
			}

			$excel->addField('classes_titles', 'Classes', 'style_classes_titles', '--row--');

			$excel->addField('status', 'Status', 'ucwords');
			$excel->export( "$strSQL ORDER BY videos.rank DESC");
			exit;
		}

		$objRS=mysql_query($strSQL);
		$total=@mysql_num_rows($objRS);
		$strSQL=makePages ($strSQL,$PageSize,$p, 'videos.rank desc');
		$objRS=mysql_query($strSQL);
		$count_all = @mysql_num_rows($objRS);
		
//		echo mysql_error();
?>


<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=delete" method="post" name="del" id="del">
<article class="module width_full">
	<header><h3 class="tabs_involved">Videos Manager</h3>
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
                <th width="1">Video Link</th>
                <th width="1">Status</th>
                <th width="1">image</th>
                <th width="1">Rank</th>
			</tr> 
		</thead> 
		<tbody id="trContainer" class="sortable"> 

	<?php while ($row=mysql_fetch_object($objRS)){ ?>
    <tr id="tr_<? echo $row->id; ?>">
		<td align="right"><input type="checkbox" name="ids[]" value="<? echo $row->id; ?>"></td>
		<td>
			<div><b><? echo $row->title; ?></b></div>
			<div><?php if($row->description !=''){ echo summarize($row->description, 20); }?></div>
			<div>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>action=edit&ids[]=<? echo $row->id; ?>">Edit Video</a>
			</div>
		</td>


	<?php if( !isSchool ) { ?>
		<td>
			<div>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>school_id=<? echo $row->school_id; ?>"><?php echo $Schools[ $row->school_id ]['title']; ?></a>
			</div>
		</td>
	<?php } ?>
		<td><?php echo style_classes_titles( $row->classes_titles, $row); ?></td>

		<td align="center"><a href="<?php echo $row->video; ?>" target="_blank"><?php echo $row->video; ?></a></td>

		<td align="center"><?php echo ( $row->status == 'active') ? 'Active' : 'Inactive'; ?></td>

<td align="center"><?php echo scale_image("../uploads/videos/thumb/". $row->image, 300); ?>
	<br /><?php echo_edit_thumb($row->image, THUMB_WIDTH, THUMB_HEIGHT, "../uploads/videos/", "../uploads/videos/thumb/", $row->id, 'videos', 'image', 'image'); ?></td>


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



