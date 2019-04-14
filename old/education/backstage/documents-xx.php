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

define('index_table', 'documents_index');

$fieldsArray = array(
	'title', 'description', 'status', 'is_public', 
);

$queryStr = '';

include 'functions/_index_common_filters.php';



//Define the maximum items while listine
$PageSize=10;
//Define the maximum items while adding
$max=10;

require_once('_lister.class.php');
$sortableLister = new lister("_sort_reverse.php?filename=".$filename."&table=documents");

$rank=getfield(getHTTP('rank'),"rank","documents");


if($School) {
	?><h4 class="alert_info">School: <?php echo $School['title']; ?></h4><?php 
}

if($Class) {
	?><h4 class="alert_info">Class: <?php echo $Class['title']; ?></h4><?php 
}

switch ($action):
	//case "down":
		case "up": //reverse
		$strSQL="select * from documents WHERE rank='".($rank+1)."'" ;
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update documents set rank='".($rank+1)."' WHERE rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update documents set rank='".$rank."' WHERE id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from documents WHERE rank >'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update documents set rank='".($rank+1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update documents set rank='".$rank."' where id='".$row->id."'";
					mysql_query($strSQLord);
				}
			}
		}
	break;
	//case "up":
		case "down": //reverse
		$strSQL="select * from documents where rank='".($rank-1)."'";
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update documents set rank='".($rank-1)."' where rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update documents set rank='".$rank."' where id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from documents where rank <'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update documents set rank='".($rank-1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update documents set rank='".$rank."' where id='".$row->id."'";
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
		$strSQL="select * from documents where id IN(".implode(',',$ids).") $limitation order by rank DESC";
		$objRS=mysql_query($strSQL);
		$i=0;
//		echo mysql_error();
		while ($row=mysql_fetch_object($objRS)){
		
			foreach($fieldsArray as $field) {
				${$field}[$i] = $row->$field;
			}
			$index[$i] = array();
			$q = mysql_query("SELECT * FROM `".index_table."` WHERE index_id='{$row->id}' ");
			if($q && mysql_num_rows($q)) {
				while($indx = mysql_fetch_assoc($q)) {
					$index[$i][ $indx['class_id'] ] = $indx['class_id'];
				}
			}
			
			$school_id[ $i ] = $row->school_id;

			$file1[$i] = $row->image;
			$file2[$i] = $row->document;
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
		$school_id = $_POST['school_id'];

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
		

			$strSQL = "documents set " . implode('', $sql);

			if( $action == 'addexe' && isSchool) {
				$strSQL .= " `add_by_id`='".isSchool."', ";
			}
			
			if( $schoolid ) {
				$strSQL .= " `school_id`='{$schoolid}', ";
			}

			$Errors = array();

			if( empty($title[$i]) ) {
				$Errors[] = "Missing Title!!";
			} else if( !$schoolid ) {
				$Errors[] = "Missing School!!";
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
					if ( $result=file_upload('document','file2'.$i,'../uploads/documents/',$errorMsg[$j])){ // document
						 $file2[$i]=$result['name'];
					}
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else if(empty( $file2[$i] )){// No File Uploaded
						$errorMsg[$j] = "No Document uploaded!!";
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						$strSQL ="update ".$strSQL." image='".sqlencode(trime($file1[$i]))."', document='".sqlencode(trime($file2[$i]))."' where id='".$ids[$i]."' $limitation ";
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
					if ( $result=file_upload('document','file2'.$i,'../uploads/documents/',$errorMsg[$j])){ // document
						 $file2[$i]=$result['name'];
				    }
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else if(empty( $file2[$i] )){// No File Uploaded
						$errorMsg[$j] = "No Document uploaded!!";
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						$q=mysql_query("SELECT max(rank) as max FROM documents");
						$r = mysql_fetch_object($q);

						$strSQL ="insert into ".$strSQL." image='".sqlencode(trime($file1[$i]))."', document='".sqlencode(trime($file2[$i]))."', rank='".($r->max+1)."', time='".time()."' ";
					}
				}
			endif;
			if($flag){
				$q = mysql_query($strSQL);
//		echo "$strSQL " . mysql_error();
				if( $q ) {
					if($action == 'addexe') {
						$doc_id = mysql_insert_id();
					} else {
						$doc_id = intval( $ids[$i] );
					}
					$_action = ( $action == 'addexe' ) ? 'add' : 'edit';
					setUpdatedRow('documents', $doc_id, $_action);

					$index_id = $doc_id;
					include('functions/_index_create.php');

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
		$strSQL="select * from documents where id IN(".implode(',',$ids).") $limitation order by rank DESC";
		$objRS=mysql_query($strSQL);
		while ($row=mysql_fetch_object($objRS))
		{
			if($row->image != '') {
				@unlink('../uploads/documents/'.$row->image);	
				@unlink('../uploads/documents/thumb/'.$row->image);	
			}
			if($row->document != '') {
				@unlink('../uploads/documents/'.$row->document);	
			}

			$strSQL="DELETE FROM documents WHERE id = '{$row->id}' LIMIT 1";
			if(!mysql_query($strSQL) ) {
				if( empty($errorMsg) ) {
					$errorMsg="Some Records didn't affected!!";
				}
			}
			else {

				setUpdatedRow('documents', $row->id, 'delete');
				mysql_query("DELETE * FROM `".index_table."` WHERE index_id='{$row->id}' ");
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
	<header><h3>Documents: <?php echo ucwords($action); ?> Record
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
					<label>Document</label>
					<?php file_field('file2'.$i,'../uploads/documents/',$file2[$oldrecord[$i]]);?>
				</fieldset>
				<fieldset>
					<label>Public File</label>
					<input type="checkbox" name="is_public[]" value="1" <?php echo checked($is_public[$oldrecord[$i]], '1'); ?> /> This is public file, any student can view it.
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
			
			$sql[] = " (documents.title LIKE '%$keywordSQL%' OR documents.description LIKE '%$keywordSQL%' ) ";
		}

		if(isSchool) {
			$sql[] = " documents.school_id = '". isSchool ."' ";
		} else if($School) {
			$sql[] = " documents.school_id = '{$School['id']}' ";
		}

		switch($_GET['status']) {
			case 'active':
				$sql[] = " documents.status='active' ";
				break;
			case 'inactive':
//				$sql[] = " documents.status='' ";
				$sql[] = " documents.status<>'active' ";
				break;
		}
		if( $Class ) {
			$sql[] = " classes.id='{$Class['id']}' ";
		}

		$where = ( $sql ) ? " WHERE " . implode(' AND ', $sql ) : '';

		$strSQL="SELECT documents.*
		, GROUP_CONCAT( DISTINCT classes.title SEPARATOR '<~~>' ) as classes_titles
		, GROUP_CONCAT( DISTINCT classes.id SEPARATOR '<~~>' ) as classes_ids
		FROM documents
		LEFT JOIN documents_index ON (documents.id = documents_index.index_id)
		LEFT OUTER JOIN classes ON (documents_index.class_id = classes.id)
		$where
		GROUP BY documents.id
		";

		if( $action == 'export' ) {
			include 'functions/_export.php';

			$excel = new export_excel("Documents");
			$excel->addField('title', 'Title');
			$excel->addField('description', 'Notes');
		
			if( !isSchool ) {
				$excel->addField('school_id', 'School', 'get_title_from_array', $Schools);
			}

			$excel->addField('classes_titles', 'Classes', 'style_titles');

			$excel->addField('status', 'Status', 'ucwords');
			$excel->export( "$strSQL ORDER BY documents.rank DESC");
			exit;
		}

		$objRS=mysql_query($strSQL);
//	echo "$strSQL " . mysql_error();
		$total=@mysql_num_rows($objRS);
		$strSQL=makePages ($strSQL,$PageSize,$p, 'documents.rank desc');
		$objRS=mysql_query($strSQL);
		$count_all = @mysql_num_rows($objRS);
		
//		echo mysql_error();
?>


<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=delete" method="post" name="del" id="del">
<article class="module width_full">
	<header><h3 class="tabs_involved">Documents Manager</h3>
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
    			<th width="350">Classes</th>
                <th width="1">Document</th>
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
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>action=edit&ids[]=<? echo $row->id; ?>">Edit Document</a>
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
				<?php echo style_titles( $row->classes_titles, true); ?>
			</div>
		</td>

		<td align="center"><?php 
			if($row->document) {
				echo "<a href=\"#\" onclick=\"window.open('../uploads/documents/{$row->document}','','width=500,height=400,scrollbars,resizable')\"><img src=\"images/view.gif\" border=\"0\"></a> ";
			}
		?></td>

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
