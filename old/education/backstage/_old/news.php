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
$limitationStudents = '';
$limitationStudentsNews = '';
if(isReseller) {
	$limitation = " AND id IN (SELECT news_id FROM news_resellers WHERE reseller_id='".isReseller."' ) ";
	$limitationStudents = " id IN (SELECT student_id FROM students_resellers WHERE reseller_id='".isReseller."' ) ";
	$limitationStudentsNews = " AND $limitationStudents ";
}

define('index_table', 'news_index');
$fieldsArray = array(
	'title', 'description', 'status', 'date', 
	'app_notification', 'news_cat_id',
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
$newsCategories = array();

$q = mysql_query("SELECT category.* FROM `category` ORDER BY category.rank DESC");
if( $q && mysql_num_rows($q ) )
{
	while( $row = mysql_fetch_assoc( $q ))
	{
		$Categories[$row['id'] ] = $row;
	}
}

$q = mysql_query("SELECT news_category.* FROM `news_category` ORDER BY news_category.rank DESC");
if( $q && mysql_num_rows($q ) ) {
	while( $row = mysql_fetch_assoc( $q )) {
		$newsCategories[$row['id'] ] = $row;
	}
}


$_GET['news_cat_id'] = intval( $_GET['news_cat_id'] );
if( $newsCategories[ $_GET['news_cat_id'] ] ) {
	$queryStr .= "news_cat_id={$_GET['news_cat_id']}&";
}
else {
	$_GET['news_cat_id'] = 0;
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

//Define the maximum items while listine
$PageSize=10;
//Define the maximum items while adding
$max=1;

require_once('_lister.class.php');
$sortableLister = new lister("_sort_reverse.php?filename=".$filename."&table=news");

$rank=getfield(getHTTP('rank'),"rank","news");


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
		$strSQL="select * from news WHERE rank='".($rank+1)."'" ;
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update news set rank='".($rank+1)."' WHERE rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update news set rank='".$rank."' WHERE id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from news WHERE rank >'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update news set rank='".($rank+1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update news set rank='".$rank."' where id='".$row->id."'";
					mysql_query($strSQLord);
				}
			}
		}
	break;
	//case "up":
		case "down": //reverse
		$strSQL="select * from news where rank='".($rank-1)."'";
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update news set rank='".($rank-1)."' where rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update news set rank='".$rank."' where id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from news where rank <'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update news set rank='".($rank-1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update news set rank='".$rank."' where id='".$row->id."'";
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
		$strSQL="select * from news where id IN(".implode(',',$ids).") $limitation order by rank DESC";
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
			$students[$i] = array();
			$q = mysql_query("SELECT * FROM `news_students` WHERE news_id='{$row->id}' ");
			if($q && mysql_num_rows($q)) {
				while($indx = mysql_fetch_assoc($q)) {
					$students[$i][] = $indx['student_id'];
				}
			}

			if( ! isReseller ){
				$resellers[$i] = array();
				$q = mysql_query("SELECT * FROM `news_resellers` WHERE news_id='{$row->id}' ");
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
		$students = $_POST['students'];

		//Counter for errors array according to the number of records.
		$j=0;
		//Array of "Errored" records.
		$oldrecord=array();
		//Start the loop over the records
		for($i=0;$i<sizeof($ids);$i++){
			$file1[$i] = $_POST['file1'.$i];
			$file2[$i] = $_POST['file2'.$i];
			//Set the flag to one in order to verify the conditions later
			$flag=1;
			
			$sql = array();
			foreach($fieldsArray as $field) {
				$sql[$field] = " `$field`='".sqlencode(trime(${$field}[$i]))."', ";
			}

			if( $action == 'addexe' && isReseller) {
				$sql['add_by_id']= " `add_by_id`='".isReseller."', ";
			}

			if( $action != 'addexe' ) {
				unset( $sql['app_notification'] );
			}
			
			$strSQL = "news set " . implode('', $sql);

			$Errors = array();
		
			if( !isReseller ) {
				$_resellers = getDataByIDs('resellers', $resellers[$i]);
			}
			$_students = getDataByIDs('students', $students[$i], $limitationStudents);
			
		
			if( empty($title[$i]) ) {
				$Errors[] = "Missing Title!!";
			}
			else if( !isReseller && !$_resellers ) {
				$Errors[] = "Missing Schools!!";
			}
			else if( ! $newsCategories[ $news_cat_id[ $i ] ] ) {
				$Errors[] = "Missing News Category!!";
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/news/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){

							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/news/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
							$Rimage->save('../uploads/news/thumb/'.$file1[$i]);							
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/news/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){

							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/news/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
							$Rimage->save('../uploads/news/thumb/'.$file1[$i]);							
						}						 
					}
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						$q=mysql_query("SELECT max(rank) as max FROM news");
						$r = mysql_fetch_object($q);

						$strSQL ="insert into ".$strSQL." image='".sqlencode(trime($file1[$i]))."', rank='".($r->max+1)."', time='".time()."' ";
					}
				}
			endif;
			if($flag){
				$q = mysql_query($strSQL);
				if( $q ) {
					if($action == 'addexe') {
						$news_id = mysql_insert_id();
					} else {
						$news_id = intval( $ids[$i] );
					}
					$_action = ( $action == 'addexe' ) ? 'add' : 'edit';
					setUpdatedRow('news', $news_id, $_action);

					$index_id = $news_id;
					include('functions/_index_create.php');

					if( isReseller ) {
						if( $action == 'addexe' ) {
							$sql= "INSERT INTO `news_resellers` SET
								`news_id`='".sqlencode(trime( $news_id ))."'
								, `reseller_id`='".sqlencode(trime( isReseller ))."'
								";
							$qq = mysql_query( $sql );
							if(!$qq) {
								$warningMsg[-2] = 'Some records faced problems while indexing it!';
							}
						}
					} else {
						if( mysql_query("DELETE FROM `news_resellers` WHERE news_id='{$news_id}' ") ) {
							
							foreach($_resellers as $reseller) {
								$sql= "INSERT INTO `news_resellers` SET
									`news_id`='".sqlencode(trime( $news_id ))."'
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

					if( mysql_query("DELETE FROM `news_students` WHERE news_id='{$news_id}' AND student_id IN ( SELECT id FROM students WHERE true {$limitationStudents} )  ") ) {
						
						foreach($_students as $student) {
							$sql= "INSERT INTO `news_students` SET
								`news_id`='".sqlencode(trime( $news_id ))."'
								, `student_id`='".sqlencode(trime( $student['id'] ))."'
								";
							$qq = mysql_query( $sql );
							if(!$qq) {
								$warningMsg[-3] = 'Some records faced problems while indexing it\'s students!';
							}
						}
					} else {
						$warningMsg[-3] = 'Some records faced problems while indexing it\'s students!';
					}
					
				} else {
					$errorMsg[$j] = 'Something went wronge!';
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
		$strSQL="select * from news where id IN(".implode(',',$ids).") $limitation order by rank DESC";
		$objRS=mysql_query($strSQL);
		while ($row=mysql_fetch_object($objRS))
		{
//			foreach($relatedArray as $tpl=>$eMsg) {
//				$q = mysql_query("SELECT * FROM `$tpl` WHERE news_id = '".$row->id ."' LIMIT 1");
//				if( $q && mysql_num_rows($q)) {
//					if( empty($errorMsg) ) {
//						$errorMsg = "Some Records didn't affected, You may need to delete/update all {$eMsg} related to the deleted news!!";
//					}
//					continue 2;
//				}
//			}
			if( isReseller ) {
				$q = mysql_query("DELETE * FROM `news_resellers` WHERE reseller_id = '".isReseller."' AND news_id='{$row->id}' ");
				continue;
			}
		
			if($row->image != '') {
				@unlink('../uploads/news/'.$row->image);	
				@unlink('../uploads/news/thumb/'.$row->image);	
			}

			$strSQL="DELETE FROM news WHERE id = '{$row->id}' LIMIT 1";
			if(!mysql_query($strSQL) && empty($errorMsg)) {
				$errorMsg="Some Records didn't affected!!";
			} else {
				setUpdatedRow('news', $row->id, 'delete');
				mysql_query("DELETE * FROM `".index_table."` WHERE index_id='{$row->id}' ");
				mysql_query("DELETE * FROM `news_resellers` WHERE news_id='{$row->id}' ");
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
<script type="text/javascript">
<!--
function ac_select_student($autocomplete, data) {
	$autocomplete.find('.remove').trigger('click');

	var $i = $autocomplete.data('i');
	var parent = $autocomplete.parents('table:first');
	var list = parent.find('.auto_list');
	if( list.find('.select-doc-' + data[1]).length > 0 ) {
		list.find('.select-doc-' + data[1]+' input[type="checkbox"]').prop('checked', true);
		return false;
	}

	var html = '<label class="select-doc select-doc-' + data[1]+'" >\
		<input type="checkbox" name="students['+$i+'][]" value="' + data[1]+'" checked="CHECKED" />\
		<span class="list_title" > '+data[0]+'</span>\
	</label>';
	$(html).appendTo(list);
}
//-->
</script>
<style>
<!--
.select-doc {
    width: 165px !important;
}
-->
</style>
<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>" method="post" name="myform" enctype="multipart/form-data">
<input type="hidden" name="action" value="<? echo $action; ?>exe"> 
<article class="module width_full">
	<header><h3>News: <?php echo ucwords($action); ?> Record
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
					<label>Title</label>
					<div style="margin-left: 210px;">
						<input type="text" name="title[]" value="<? echo textencode($title[$oldrecord[$i]]); ?>" />
						<div class="clear"></div>
						
						<label style="width: auto;">
						<?php 
							$falgs = '';
							$falgs .= ( $app_notification[$oldrecord[$i]] ) ? ' checked="CHECKED" ' : '';
							$falgs .= ( $action == 'edit' ) ? ' disabled="DISABLED" ' : '';
						?>
						
							<input type="checkbox" name="app_notification[<?php echo $i; ?>]" value="1" <?php echo $falgs; ?> />
							Send this news in push notification.
						</label>
						<div class="clear"></div>
				</fieldset>
				<fieldset>
					<label>News Category</label>
					<select name="news_cat_id[]">
						<option value="" >== Select ==</option>
				<?php foreach($newsCategories as $newsCategory) { ?>
						<option value="<?php echo $newsCategory['id']; ?>" <?php echo selected($news_cat_id[$oldrecord[$i]], $newsCategory['id']); ?>><?php echo $newsCategory['title']; ?></option>
				<?php } ?>
					</select>
				</fieldset>
				

				<fieldset>
					<label>Description</label>
					<textarea name="description[]" rows="12" ><? echo textencode($description[$oldrecord[$i]]); ?></textarea>
				</fieldset>

			<?php if( !isReseller ) { ?>
				<fieldset>
					<label>School</label>
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
				</fieldset>
			<?php } ?>
				<fieldset>
					<label>Students</label>
					<table width="60%" border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td width="33%">
								<div><span class="autocomplete" data-link="_get.php?from=students" data-select="ac_select_student" data-i="<?php echo $i; ?>">
									<input class="input_short auto_name" type="text" name="student_name" value="" />
									<input class="input auto_id" type="hidden" name="student_id" value="" />
								</span></div>
							</td>
							<td class="auto_list">
							<?php 
								if( !is_array($students[$oldrecord[$i]])) {
									$students[$oldrecord[$i]] = array();
								}
								$docts = getDataByIDs('students', $students[$oldrecord[$i]], $limitationStudents );
								foreach($docts as $doct) {
									echo <<<EOF
	<label class="select-doc select-doc-{$doct['id']}" >
		<input type="checkbox" name="students[{$i}][]" value="{$doct['id']}" checked="CHECKED" />
		<span class="list_title" > {$doct['full_name']}</span>
	</label>
EOF;
								}
							?>
							</td>
						</tr>
					</table>
				</fieldset>

				<fieldset>
					<label>Classes</label>
					<?php include('functions/_index_table.php');; ?>
				</fieldset>

				<fieldset>
					<label>Image</label>
					<?php file_field('file1'.$i,'../uploads/news/',$file1[$oldrecord[$i]]);?>
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
		<div>
			Find: <input name="keyword" value="<? echo htmlspecialchars($keyword, ENT_QUOTES); ?>" size="30" />
			<input type="submit" value="Browse" style="float: right;" />
		</div>
		<div>
			Status: <select name="status">
				<option value="" >== Any ==</option>
				<option value="active" <?php echo selected($_GET['status'], 'active'); ?>>Active</option>
				<option value="inactive" <?php echo selected($_GET['status'], 'inactive'); ?>>Inactive</option>
			</select>
		
			Category: <select name="news_cat_id">
				<option value="" >== Any ==</option>
			<?php foreach($newsCategories as $newsCategory) { ?>
				<option value="<?php echo $newsCategory['id']; ?>" <?php echo selected($_GET['news_cat_id'], $newsCategory['id']); ?>><?php echo $newsCategory['title']; ?></option>
			<?php } ?>
			</select>
		</div>
		<div>
<?php if( !isReseller ) { ?>
		School: <span class="autocomplete" data-link="_get.php?from=resellers">
			<input class="input_short auto_name" type="text" name="reseller_name" value="<?php echo trim( $Reseller['title']); ?>" />
			<input class="input auto_id" type="hidden" name="reseller_id" value="<?php echo $Reseller['id']; ?>" />
		</span>
<?php } ?>
	<?php 
		$catID = ($Sub) ? $Sub['id'] : ( ($Category) ? $Category['id'] *-1 : '');
	?>
		Class: <span class="autocomplete" data-link="_get.php?from=categories">
			<input class="input_short auto_name" type="text" name="cat_name" value="<?php echo $_catTitle; ?>" />
			<input class="input auto_id" type="hidden" name="cat_id" value="<?php echo $catID; ?>" />
		</span>
		</div>
	</form>
</div>
<?php 
		$sql = array();
		if( !empty($keyword) )
		{
			$keywordSQL = mysql_real_escape_string($keyword);
			$keywordSQL = str_replace(' ', '% %', $keywordSQL);
			
			$sql[] = " (news.title LIKE '%$keywordSQL%' OR news.description LIKE '%$keywordSQL%' ) ";
		}

		if(isReseller) {
			$sql[] = " news_resellers.reseller_id = '". isReseller ."' ";
		} else if($Reseller) {
			$sql[] = " news_resellers.reseller_id = '{$Reseller['id']}' ";
		}

		switch($_GET['status']) {
			case 'active':
				$sql[] = " news.status='active' ";
				break;
			case 'inactive':
//				$sql[] = " news.status='' ";
				$sql[] = " news.status<>'active' ";
				break;
		}
		if( $Sub ) {
			$sql[] = " catSub.sub_id='{$Sub['id']}' ";
		} else if( $Category ) {
//			$sql[] = " catSub.sub_id='0' AND catSub.cat_id='{$Category['id']}' ";
			$sql[] = " catSub.cat_id='{$Category['id']}' ";
		}
		
		if( $_GET['news_cat_id'] > 0 ) {
			$sql[] = " news.news_cat_id='{$_GET['news_cat_id']}' ";
		}

		$where = ( $sql ) ? " WHERE " . implode(' AND ', $sql ) : '';

		$strSQL="SELECT news.*
			, GROUP_CONCAT( DISTINCT students.full_name SEPARATOR ',' ) as students_full_name
			, GROUP_CONCAT( DISTINCT news_resellers.reseller_id SEPARATOR ',' ) as news_resellers
			, GROUP_CONCAT( DISTINCT catSub.title SEPARATOR '<~~>' ) as cat_titles
		FROM ( ( news
		LEFT JOIN news_index ON (news.id = news_index.index_id)
		LEFT JOIN (
			SELECT category.id as cat_id, 0 as sub_id, category.title as title FROM category 
			UNION
			SELECT category.id as cat_id, category_sub.id as sub_id, concat_ws('<~>', category.title, category_sub.title) as title
			FROM category 
			LEFT JOIN category_sub ON ( category.id = category_sub.cat_id)
		) as catSub ON (catSub.sub_id = news_index.sub_id AND catSub.sub_id = news_index.sub_id)
		) 
		LEFT JOIN news_resellers ON (news.id = news_resellers.news_id) )
		LEFT JOIN news_students ON (news.id = news_students.news_id)
		LEFT JOIN students_resellers ON (students_resellers.reseller_id = news_resellers.reseller_id )
		LEFT JOIN students ON (students.id = students_resellers.student_id AND students.id = news_students.student_id )
		$where
		GROUP BY news.id
		";
//		LEFT JOIN students_resellers ON (students_resellers.reseller_id = news_resellers.reseller_id )
//		LEFT JOIN students ON (students.id = students_resellers.student_id AND news_students.student_id )

		if( $action == 'export' ) {
			include 'functions/_export.php';
			
			$excel = new export_excel("News");
			$excel->addField('title', 'Title');
			$excel->addField('description', 'Notes');

			if( !isReseller ) {
				$excel->addField('news_resellers', 'Schools', 'style_news_resellers', $Resellers);
			}
			$excel->addField('students_full_name', 'Students', 'style_news_students');

			$excel->addField('cat_titles', 'Classes', 'style_cat_titles');

			$excel->addField('status', 'Status', 'ucwords');
			$excel->export( "$strSQL ORDER BY news.rank DESC");
			exit;
		}

		$objRS=mysql_query($strSQL);
		$total=@mysql_num_rows($objRS);
		$strSQL=makePages ($strSQL,$PageSize,$p, 'news.rank desc');
		$objRS=mysql_query($strSQL);
		$count_all = @mysql_num_rows($objRS);
		
//		echo mysql_error();
?>


<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=delete" method="post" name="del" id="del">
<article class="module width_full">
	<header><h3 class="tabs_involved">News Manager</h3>
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
                <th width="1">Image</th>
    			<th>Title</th>
			<?php if( !isReseller ) { ?>
    			<th>Schools</th>
			<?php } ?>
    			<th>Category</th>
    			<th>Students</th>
    			<th width="350">Classes</th>
                <th width="1">Status</th>
                <th width="1">Rank</th>
			</tr> 
		</thead> 
		<tbody id="trContainer" class="sortable"> 

	<?php while ($row=mysql_fetch_object($objRS)){ ?>
    <tr id="tr_<? echo $row->id; ?>">
		<td align="right"><input type="checkbox" name="ids[]" value="<? echo $row->id; ?>"></td>
		<td align="center"><?php echo scale_image("../uploads/news/thumb/". $row->image, 100); ?></td>
		<td>
			<div><b><? echo $row->title; ?></b></div>
			<div><?php if($row->description !=''){ echo summarize($row->description, 20); }?></div>
			<div>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>action=edit&ids[]=<? echo $row->id; ?>">Edit News</a>
			</div>
		<?php if( $row->app_notification ) { ?>
			<div><b>Sent in push notification</b></div>
		<?php } ?>
		</td>

	<?php if( !isReseller ) { ?>
		<td><?php 
			$_resellers = style_news_resellers($row->news_resellers, $Resellers, 'array');
			foreach($_resellers as $k=>$v) {
				?><div>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>reseller_id=<? echo $k; ?>"><?php echo $v; ?></a>
			</div><?php 
			}
		?>
		</td>
	<?php } ?>

		<td><?php 
			if( $newsCategories[ $row->news_cat_id ] ) {
				?><a href="<? echo $filename; ?>?<?php echo $queryStr; ?>news_cat_id=<? echo $row->news_cat_id; ?>"><?php echo $newsCategories[ $row->news_cat_id ]['title']; ?></a><?php 
			}
		?>
		</td>
	
		<td><?php 
			$_students = style_news_students($row->students_full_name, true);
			echo $_students;
		?>
		</td>
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
