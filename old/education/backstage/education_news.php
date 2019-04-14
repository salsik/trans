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
$limitationStudentsList = '';
if(isSchool) {
	$limitation = " AND school_id='".isSchool."' ";
	$limitationStudents = " school_id='".isSchool."' ";
	$limitationStudentsList = " AND $limitationStudents ";
}
else if( !isAdmin ) {
	die();
}

if( !isAdmin ) {
	die();
}

define('index_table', 'education_news_index');
define('index_students_table', 'education_news_students');
define('index_students_field', 'news_id');

$fieldsArray = array(
	'title', 'description', 'status', 'date', 'link', 'youtubeLink',
	'app_notification', 'publish_date_time', 'news_cat_id', 'is_arabic',
);



$queryStr = '';

include 'functions/_index_common_filters.php';

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

//Define the maximum items while listine
$PageSize=10;
//Define the maximum items while adding
$max=10;

require_once('_lister.class.php');
$sortableLister = new lister("_sort_reverse.php?filename=".$filename."&table=education_news");

$rank=getfield(getHTTP('rank'),"rank","education_news");


if($School) {
	?><h4 class="alert_info">School: <?php echo $School['title']; ?></h4><?php 
}

if($Class) {
	?><h4 class="alert_info">Class: <?php echo $Class['title']; ?></h4><?php 
}

switch ($action):
	//case "down":
		case "up": //reverse
		$strSQL="select * from education_news WHERE rank='".($rank+1)."'" ;
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update education_news set rank='".($rank+1)."' WHERE rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update education_news set rank='".$rank."' WHERE id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from education_news WHERE rank >'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update education_news set rank='".($rank+1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update education_news set rank='".$rank."' where id='".$row->id."'";
					mysql_query($strSQLord);
				}
			}
		}
	break;
	//case "up":
		case "down": //reverse
		$strSQL="select * from education_news where rank='".($rank-1)."'";
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update education_news set rank='".($rank-1)."' where rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update education_news set rank='".$rank."' where id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from education_news where rank <'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update education_news set rank='".($rank-1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update education_news set rank='".$rank."' where id='".$row->id."'";
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
		$strSQL="select * from education_news where id IN(".implode(',',$ids).") $limitation order by rank DESC";
		$objRS=mysql_query($strSQL);
		$i=0;
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
			$student_ids[$i] = array();
			$q = mysql_query("SELECT * FROM `education_news_students` WHERE news_id='{$row->id}' ");
			if($q && mysql_num_rows($q)) {
				while($indx = mysql_fetch_assoc($q)) {
					$student_ids[$i][] = $indx['student_id'];
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
		$student_ids = $_POST['student_ids'];

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
			
			// fix publish time
			$am = 'am';
			if( stripos($publish_date_time[ $i ], 'pm') !== false) {
				$am = 'pm';
			}
			$tmp = explode(' ', $publish_date_time[ $i ]);
			
			$_publish_time = $tmp[ 1 ];
			$_publish_time = explode(':', $_publish_time);
			
			$_publish_time[0] = intval( $_publish_time[0] );
			$_publish_time[1] = intval( $_publish_time[1] );

			if( $am == 'pm' ) {
				$_publish_time[0] += 12;
			}
			
			$_publish_time = "{$_publish_time[0]}:{$_publish_time[1]}:00";
			
			$publish_date_time[ $i ] = "{$tmp[0]} {$_publish_time}";

			if( isSchool ) {
				$schoolid = isSchool;
			}
			else {
				$_school = getDataByID('schools', $school_id[$i]);
				if( $_school ) {
					$schoolid = $_school['id'];
				}
				else if( $school_id[$i] == -1 ) {
					$schoolid = -1;
				}
				else {
					$schoolid = 0;
				}
			}
			
			$link[$i] = fixLinkProtocol( $link[$i] );
			$youtubeLink[$i] = fixLinkProtocol( $youtubeLink[$i] );
			
			$sql = array();
			foreach($fieldsArray as $field) {
				$sql[$field] = " `$field`='".sqlencode(trime(${$field}[$i]))."', ";
			}

			if( $action != 'addexe' ) {
				unset( $sql['app_notification'] );
			}
			
			$strSQL = "education_news set " . implode('', $sql);

			if( $action == 'addexe' && isSchool) {
				$strSQL .= " `add_by_id`='".isSchool."', ";
			}
			
			if( $schoolid != 0 ) {
				$strSQL .= " `school_id`='{$schoolid}', ";
			}

			$Errors = array();
		
			$_students = getDataByIDs('students', $students[$i], $limitationStudents);
			
		
			if( empty($title[$i]) ) {
				$Errors[] = "Missing Title!!";
			}
			else if( $schoolid == 0 ) {
				$Errors[] = "Missing School!!";
			}
			else if( ! $newsCategories[ $news_cat_id[ $i ] ] ) {
				$Errors[] = "Missing News Category!!";
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/education_news/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){

							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/education_news/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
							$Rimage->save('../uploads/education_news/thumb/'.$file1[$i]);							
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/education_news/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){

							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/education_news/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
							$Rimage->save('../uploads/education_news/thumb/'.$file1[$i]);							
						}						 
					}
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						$q=mysql_query("SELECT max(rank) as max FROM education_news");
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
					setUpdatedRow('education_news', $news_id, $_action, $news_cat_id[ $i ]);

					$index_id = $news_id;
					include('functions/_index_create_all.php');
				} else {
					$errorMsg[$j] = 'Something went wrong!';
					//$errorMsg[$j] = mysql_error();
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
		$strSQL="select * from education_news where id IN(".implode(',',$ids).") $limitation order by rank DESC";
		$objRS=mysql_query($strSQL);
		while ($row=mysql_fetch_object($objRS))
		{
			if($row->image != '') {
				@unlink('../uploads/education_news/'.$row->image);	
				@unlink('../uploads/education_news/thumb/'.$row->image);	
			}

			$strSQL="DELETE FROM education_news WHERE id = '{$row->id}' LIMIT 1";
			if(!mysql_query($strSQL) ) {
				if( empty($errorMsg) ) {
					$errorMsg="Some Records didn't affected!!";
				}
			}
			else {
				setUpdatedRow('education_news', $row->id, 'delete');
				mysql_query("DELETE  FROM `".index_table."` WHERE index_id='{$row->id}' ");
				mysql_query("DELETE  FROM `education_news_students` WHERE news_id='{$row->id}' ");
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
	<header><h3>Education News: <?php echo ucwords($action); ?> Record
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
					<label>Arabic Text</label>
					<select name="is_arabic[]">
							<option value="1" <?php echo selected($is_arabic[$oldrecord[$i]], '1'); ?>> Yes </option>
							<option value="" <?php echo selected($is_arabic[$oldrecord[$i]], '', isset($is_arabic[$oldrecord[$i]])); ?>> No </option>
					</select>
				</fieldset>
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
			<?php 
		
				// fix publish time
				$publish_date_time[ $oldrecord[$i] ] = fixDateTime( $publish_date_time[ $oldrecord[$i] ] );
				
			?>
				<fieldset>
					<label>Publish Date/Time</label>
					<input class="datetimepicker input_date_time" type="text" name="publish_date_time[]" value="<? echo textencode($publish_date_time[$oldrecord[$i]]); ?>" />
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
				<fieldset>
					<label>Related Link</label>
					<input type="text" name="link[]" value="<? echo textencode($link[$oldrecord[$i]]); ?>" />
				</fieldset>
				<fieldset>
					<label>youtube Link</label>
					<input type="text" name="youtubeLink[]" value="<? echo textencode($youtubeLink[$oldrecord[$i]]); ?>" />
				</fieldset>

			<?php if( !isSchool ) { ?>
<?php 
				$sid = nor($school_id[$oldrecord[$i]], $School['id']);
?>
				<fieldset>
					<label>School</label>
					<select name="school_id[<?php echo $i; ?>]" class="school_id" >
						<option value="" >-- Select --</option>
						<option value="-1" <?php echo ( $sid == -1 ) ? ' selected="selected" ' : ''; ?> >--- ALL SCHOOLS ---</option>
						<?php 
							$_index_school = 0;
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
					<label>Students</label>
					<div style="margin-left: 210px; overflow: hidden;" class="students_list_box">
						<?php include('functions/_index_table_students.php'); ?>
						<div class="clear"></div>
					</div>
				</fieldset>

				<fieldset>
					<label>Image</label>
					<?php file_field('file1'.$i,'../uploads/education_news/',$file1[$oldrecord[$i]]);?>
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
		<div>
			Find: <input name="keyword" value="<? echo my_htmlspecialchars($keyword, ENT_QUOTES); ?>" size="30" />
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
		<?php 
			include 'functions/_index_filter.php';
		?>
		</div>
	</form>
</div>
<?php 
		$sql = array();
		if( !empty($keyword) )
		{
			$keywordSQL = mysql_real_escape_string($keyword);
			$keywordSQL = str_replace(' ', '% %', $keywordSQL);
			
			$sql[] = " (education_news.title LIKE '%$keywordSQL%' OR education_news.description LIKE '%$keywordSQL%' ) ";
		}

		if(isSchool) {
			$sql[] = " education_news.school_id = '". isSchool ."' ";
		} else if($School) {
			$sql[] = " education_news.school_id = '{$School['id']}' ";
		}

		switch($_GET['status']) {
			case 'active':
				$sql[] = " education_news.status='active' ";
				break;
			case 'inactive':
//				$sql[] = " education_news.status='' ";
				$sql[] = " education_news.status<>'active' ";
				break;
		}
		
		if( $Class ) {
			$sql[] = " classes.id='{$Class['id']}' ";
		}
		
		if( $_GET['news_cat_id'] > 0 ) {
			$sql[] = " education_news.news_cat_id='{$_GET['news_cat_id']}' ";
		}

		$where = ( $sql ) ? " WHERE " . implode(' AND ', $sql ) : '';

		$strSQL="SELECT education_news.*
		, GROUP_CONCAT( DISTINCT classes.title SEPARATOR '<~~>' ) as classes_titles
		, GROUP_CONCAT( DISTINCT education_news_index.class_id SEPARATOR '<~~>' ) as classes_ids
		
		, GROUP_CONCAT( DISTINCT students.full_name SEPARATOR ',' ) as students_full_name
		, GROUP_CONCAT( DISTINCT education_news_students.student_id SEPARATOR '<~~>' ) as students_ids
		
		FROM (education_news
		LEFT JOIN education_news_index ON (education_news.id = education_news_index.index_id)
		LEFT OUTER JOIN classes ON (education_news_index.class_id = classes.id) 
		)
		LEFT JOIN education_news_students ON (education_news.id = education_news_students.news_id)
		LEFT JOIN students ON (students.id = education_news_students.student_id AND students.school_id = education_news.school_id )

		$where
		GROUP BY education_news.id
		";

		if( $action == 'export' ) {
			include 'functions/_export.php';
			
			$excel = new export_excel("Education News");
			$excel->addField('title', 'Title');
			$excel->addField('description', 'Notes');
			$excel->addField('news_cat_id', 'Category', 'style_news_category');
			

			if( !isSchool ) {
				$Schools2 = $Schools;
				$Schools2[-1] = array('id'=>-1, 'title'=>'[ALL SCHOOLS]');
				$excel->addField('school_id', 'School', 'get_title_from_array', $Schools2);
			}

			$excel->addField('classes_titles', 'Classes', 'style_classes_titles', '--row--');
			
			$excel->addField('students_full_name', 'Students', 'style_students_titles', '--row--');

			$excel->addField('status', 'Status', 'ucwords');
			$excel->export( "$strSQL ORDER BY education_news.rank DESC");
			exit;
		}

		$objRS=mysql_query($strSQL);
		$total=@mysql_num_rows($objRS);
		$strSQL=makePages ($strSQL,$PageSize,$p, 'education_news.rank desc');
		$objRS=mysql_query($strSQL);
		$count_all = @mysql_num_rows($objRS);
		
//		echo mysql_error();
?>


<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=delete" method="post" name="del" id="del">
<article class="module width_full">
	<header><h3 class="tabs_involved">Education News Manager</h3>
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
			<?php if( !isSchool ) { ?>
    			<th>School</th>
			<?php } ?>
    			<th>Category</th>
    			<th>Classes</th>
    			<th>Students</th>
                <th width="1">Status</th>
                <th width="1">Rank</th>
			</tr> 
		</thead> 
		<tbody id="trContainer" class="sortable"> 

	<?php while ($row=mysql_fetch_object($objRS)){ ?>
    <tr id="tr_<? echo $row->id; ?>">
		<td align="right"><input type="checkbox" name="ids[]" value="<? echo $row->id; ?>"></td>
		<td align="center"><?php echo scale_image("../uploads/education_news/thumb/". $row->image, 100); ?></td>
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

	<?php if( !isSchool ) { ?>
		<td>
			<div>
		<?php if($row->school_id == -1) { ?>
				<b>[ALL&nbsp;SCHOOLS]</b>
		<?php } else { ?>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>school_id=<? echo $row->school_id; ?>"><?php echo $Schools[ $row->school_id ]['title']; ?></a>
			<?php } ?>
			</div>
		</td>
	<?php } ?>

		<td><?php 
			if( $newsCategories[ $row->news_cat_id ] ) {
				?><a href="<? echo $filename; ?>?<?php echo $queryStr; ?>news_cat_id=<? echo $row->news_cat_id; ?>"><?php echo $newsCategories[ $row->news_cat_id ]['title']; ?></a><?php 
			}
		?>
		</td>

		<td><?php echo style_classes_titles( $row->classes_titles, $row); ?></td>
	
		<td><?php echo style_students_titles($row->students_full_name, $row); ?></td>

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


function fixDateTime( $date_time ) {
	
	// fix publish time
	$tmp = explode(' ', $date_time );
	
	$_publish_time = $tmp[ 1 ];

	$_publish_time = explode(':', $_publish_time);
	if( count( $_publish_time ) > 2 ) {
		$am = 'am';
		$_publish_time[0] = intval( $_publish_time[0] );
		if( $_publish_time[0] > 12 ) {
			$_publish_time[0] -= 12;
			$am = 'pm';
		}
		$_publish_time[1] = intval( $_publish_time[1] );
	
		if( $_publish_time[0] < 10 ) {
			$_publish_time[0] = '0'.$_publish_time[0];
		}
		if( $_publish_time[1] < 10 ) {
			$_publish_time[1] = '0'.$_publish_time[1];
		}

		$_publish_time = "{$_publish_time[0]}:{$_publish_time[1]} {$am}";
	}
	else {
		$_publish_time = $tmp[ 1 ];
	}
	
	$date_time = "{$tmp[0]} {$_publish_time}";
	
	
	return $date_time;
}
