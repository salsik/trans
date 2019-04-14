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

define('index_table', 'gallery_index');
define('index_filter_by', 'albums');

$fieldsArray = array(
	'title', 'description', 'status', 
	'gallery_cat_id',
);

$galleryCategories = array();

$queryStr = '';

include 'functions/_index_common_filters.php';


$sid = 0;

if( isSchool ) {
	$sid = isSchool;
}
else if( $School ) {
	$sid = $School['id'];
}

if( $sid ) {
	$q = mysql_query("SELECT gallery_category.* FROM `gallery_category` WHERE school_id='{$sid}' ORDER BY gallery_category.rank DESC");
	if( $q && mysql_num_rows($q ) ) {
		while( $row = mysql_fetch_assoc( $q )) {
			$galleryCategories[$row['id'] ] = $row;
		}
	}
}

if( !$_GET['gallery_cat_id'] ) {
	$_GET['gallery_cat_id'] = $_GET['album_id'];
}

$Album = array();

$_GET['gallery_cat_id'] = intval( $_GET['gallery_cat_id'] );
if( $galleryCategories[ $_GET['gallery_cat_id'] ] ) {
	
	$Album = $galleryCategories[ $_GET['gallery_cat_id'] ];
	
	$queryStr .= "gallery_cat_id={$_GET['gallery_cat_id']}&";
}
else {
	$_GET['gallery_cat_id'] = 0;
}


//Define the maximum items while listine
$PageSize=10;
//Define the maximum items while adding
$max=10;

require_once('_lister.class.php');
$sortableLister = new lister("_sort_reverse.php?filename=".$filename."&table=gallery");

$rank=getfield(getHTTP('rank'),"rank","gallery");


if($School) {
	?><h4 class="alert_info">School: <?php echo $School['title']; ?></h4><?php 
}

if($Class) {
	?><h4 class="alert_info">Class: <?php echo $Class['title']; ?></h4><?php 
}

if($Album) {
	?><h4 class="alert_info">Album: <?php echo $Album['title']; ?></h4><?php 
}

switch ($action):
	//case "down":
		case "up": //reverse
		$strSQL="select * from gallery WHERE rank='".($rank+1)."'" ;
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update gallery set rank='".($rank+1)."' WHERE rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update gallery set rank='".$rank."' WHERE id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from gallery WHERE rank >'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update gallery set rank='".($rank+1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update gallery set rank='".$rank."' where id='".$row->id."'";
					mysql_query($strSQLord);
				}
			}
		}
	break;
	//case "up":
		case "down": //reverse
		$strSQL="select * from gallery where rank='".($rank-1)."'";
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update gallery set rank='".($rank-1)."' where rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update gallery set rank='".$rank."' where id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from gallery where rank <'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update gallery set rank='".($rank-1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update gallery set rank='".$rank."' where id='".$row->id."'";
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
		$strSQL="select * from gallery where id IN(".implode(',',$ids).") $limitation order by rank DESC";
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
//	var_dump( $_POST );
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
			
			$sql = array();
			foreach($fieldsArray as $field) {
				$sql[$field] = " `$field`='".sqlencode(trime(${$field}[$i]))."', ";
			}

			$strSQL = "gallery set " . implode('', $sql);
		
			if( $action == 'addexe' && isSchool) {
				$strSQL .= " `add_by_id`='".isSchool."', ";
			}
			
			$Errors = array();
			
			if( $schoolid ) {
				$strSQL .= " `school_id`='{$schoolid}', ";
				
				$albums = get_school_albums($schoolid, true);

				if( $albums[ $gallery_cat_id[$i] ] ) {
					$cid = $albums[ $gallery_cat_id[$i] ]['id'];
					
//					$strSQL .= " `gallery_cat_id`='{$cid}', ";
				}
				else {
					$Errors[] = "Missing Album!!";
				}
			}

			if( empty($title[$i]) ) {
				$Errors[] = "Missing Title!!";
			} else if( !$schoolid ) {
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/gallery/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){

							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/gallery/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
							$Rimage->save('../uploads/gallery/thumb/'.$file1[$i]);							
						}						 
					}
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else if(empty( $file1[$i] )){// No File Uploaded
						$errorMsg[$j] = "No Photo uploaded!!";
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/gallery/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){

							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/gallery/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
							$Rimage->save('../uploads/gallery/thumb/'.$file1[$i]);							
						}						 
					}
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else if(empty( $file1[$i] )){// No File Uploaded
						$errorMsg[$j] = "No Photo uploaded!!";
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						$q=mysql_query("SELECT max(rank) as max FROM gallery");
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
					setUpdatedRow('gallery', $doc_id, $_action, $gallery_cat_id[$i]);

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
		$strSQL="select * from gallery where id IN(".implode(',',$ids).") $limitation order by rank DESC";
		$objRS=mysql_query($strSQL);
		while ($row=mysql_fetch_object($objRS))
		{
			if($row->image != '') {
				@unlink('../uploads/gallery/'.$row->image);	
				@unlink('../uploads/gallery/thumb/'.$row->image);	
			}

			$strSQL="DELETE FROM gallery WHERE id = '{$row->id}' LIMIT 1";
			if(!mysql_query($strSQL) ) {
				if( empty($errorMsg) ) {
					$errorMsg="Some Records didn't affected!!";
				}
			}else {

				setUpdatedRow('gallery', $row->id, 'delete');
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
	<header><h3>Gallery: <?php echo ucwords($action); ?> Record
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

				<?php include('functions/_index_school_fields.php'); ?>

				<fieldset>
					<label>Photo</label>
					<?php file_field('file1'.$i,'../uploads/gallery/',$file1[$oldrecord[$i]]);?>
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
			
			$sql[] = " (gallery.title LIKE '%$keywordSQL%' OR gallery.description LIKE '%$keywordSQL%' ) ";
		}
		
		if(isSchool) {
			$sql[] = " gallery.school_id = '". isSchool ."' ";
		} else if($School) {
			$sql[] = " gallery.school_id = '{$School['id']}' ";
		}

		switch($_GET['status']) {
			case 'active':
				$sql[] = " gallery.status='active' ";
				break;
			case 'inactive':
//				$sql[] = " gallery.status='' ";
				$sql[] = " gallery.status<>'active' ";
				break;
		}
		
		if( $Album ) {
			$sql[] = " gallery.gallery_cat_id='{$_GET['gallery_cat_id']}' ";
		}
		
		if( $Class ) {
			$sql[] = " classes.id='{$Class['id']}' ";
		}

		$where = ( $sql ) ? " WHERE " . implode(' AND ', $sql ) : '';

		$strSQL="SELECT gallery.*
		, gallery_category.title as gallery_cat_title
		, GROUP_CONCAT( DISTINCT classes.title SEPARATOR '<~~>' ) as classes_titles
		, GROUP_CONCAT( DISTINCT gallery_index.class_id SEPARATOR '<~~>' ) as classes_ids
		FROM gallery
		LEFT JOIN gallery_index ON (gallery.id = gallery_index.index_id)
		LEFT OUTER JOIN classes ON (gallery_index.class_id = classes.id)
		LEFT OUTER JOIN gallery_category ON (gallery.gallery_cat_id = gallery_category.id)

		$where
		GROUP BY gallery.id
		";

		
		if( $action == 'export' ) {
			include 'functions/_export.php';
			
			$excel = new export_excel("Gallery");
			$excel->addField('title', 'Title');
			$excel->addField('description', 'Notes');


			if( !isSchool ) {
				$excel->addField('school_id', 'School', 'get_title_from_array', $Schools);
			}

			$excel->addField('classes_titles', 'Classes', 'style_classes_titles', '--row--');

			$excel->addField('gallery_cat_title', 'Album');
			
			$excel->addField('status', 'Status', 'ucwords');
			$excel->export( "$strSQL ORDER BY gallery.rank DESC");
			exit;
		}

		$objRS=mysql_query($strSQL);
		$total=@mysql_num_rows($objRS);
		$strSQL=makePages ($strSQL,$PageSize,$p, 'gallery.rank desc');
		$objRS=mysql_query($strSQL);
		$count_all = @mysql_num_rows($objRS);
		
//		echo mysql_error();
?>


<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=delete" method="post" name="del" id="del">
<article class="module width_full">
	<header><h3 class="tabs_involved">Gallery Manager</h3>
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
                <th>Album</th>
                <th width="1">Photo</th>
                <th width="1">Status</th>
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
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>action=edit&ids[]=<? echo $row->id; ?>">Edit Photo</a>
			</div>
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
				<?php echo style_classes_titles( $row->classes_titles, $row); ?>
			</div>
		</td>
		
		<td>
			<div>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>school_id=<? echo $row->school_id; ?>&gallery_cat_id=<?php echo $row->gallery_cat_id; ?>"><?php echo $row->gallery_cat_title; ?></a>
			</div>
		</td>
		

<td align="center"><?php echo scale_image("../uploads/gallery/thumb/". $row->image, 300); ?>
	<br /><?php echo_edit_thumb($row->image, THUMB_WIDTH, THUMB_HEIGHT, "../uploads/gallery/", "../uploads/gallery/thumb/", $row->id, 'gallery', 'image', 'image'); ?></td>


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
