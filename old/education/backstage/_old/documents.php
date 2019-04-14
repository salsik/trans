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
if(isReseller) {
	$limitation = " AND id IN (SELECT document_id FROM documents_resellers WHERE reseller_id='".isReseller."' ) ";
}

define('index_table', 'documents_index');
$fieldsArray = array(
	'title', 'description', 'status', 'is_public', 
);

$relatedArray = array(
//	'students' => " students ",
//	'news' => " news ",
//	'documents' => " documents ",
//	'banners' => " documents ",
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

//Define the maximum items while listine
$PageSize=10;
//Define the maximum items while adding
$max=1;

require_once('_lister.class.php');
$sortableLister = new lister("_sort_reverse.php?filename=".$filename."&table=documents");

$rank=getfield(getHTTP('rank'),"rank","documents");


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
		echo mysql_error();
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
				$q = mysql_query("SELECT * FROM `documents_resellers` WHERE document_id='{$row->id}' ");
				if($q && mysql_num_rows($q)) {
					while($indx = mysql_fetch_assoc($q)) {
						$resellers[$i][] = $indx['reseller_id'];
					}
				}
			}
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
		$resellers = $_POST['resellers'];

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

			$strSQL = "documents set " . implode('', $sql);
			$Errors = array();
			
			if( !isReseller ) {
				$_resellers = getDataByIDs('resellers', $resellers[$i]);
			}
			
			if( empty($title[$i]) ) {
				$Errors[] = "Missing Title!!";
			} else if( !isReseller && !$_resellers ) {
				$Errors[] = "Missing Schools!!";
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

					if( isReseller ) {
						if( $action == 'addexe' ) {
							$sql= "INSERT INTO `documents_resellers` SET
								`document_id`='".sqlencode(trime( $doc_id ))."'
								, `reseller_id`='".sqlencode(trime( isReseller ))."'
								";
							$qq = mysql_query( $sql );
							if(!$qq) {
								$warningMsg[-2] = 'Some records faced problems while indexing it!';
							}
						}
					} else {
						if( mysql_query("DELETE FROM `documents_resellers` WHERE document_id='{$doc_id}' ") ) {
							
							foreach($_resellers as $reseller) {
								$sql= "INSERT INTO `documents_resellers` SET
									`document_id`='".sqlencode(trime( $doc_id ))."'
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
		$strSQL="select * from documents where id IN(".implode(',',$ids).") $limitation order by rank DESC";
		$objRS=mysql_query($strSQL);
		while ($row=mysql_fetch_object($objRS))
		{
//			foreach($relatedArray as $tpl=>$eMsg) {
//				$q = mysql_query("SELECT * FROM `$tpl` WHERE document_id = '".$row->id ."' LIMIT 1");
//				if( $q && mysql_num_rows($q)) {
//					if( empty($errorMsg) ) {
//						$errorMsg = "Some Records didn't affected, You may need to delete/update all {$eMsg} related to the deleted document!!";
//					}
//					continue 2;
//				}
//			}
			if( isReseller ) {
				$q = mysql_query("DELETE * FROM `documents_resellers` WHERE reseller_id = '".isReseller."' AND document_id='{$row->id}' ");
				continue;
			}
		
			if($row->image != '') {
				@unlink('../uploads/documents/'.$row->image);	
				@unlink('../uploads/documents/thumb/'.$row->image);	
			}
			if($row->document != '') {
				@unlink('../uploads/documents/'.$row->document);	
			}

			$strSQL="DELETE FROM documents WHERE id = '{$row->id}' LIMIT 1";
			if(!mysql_query($strSQL) && empty($errorMsg)) {
				$errorMsg="Some Records didn't affected!!";
			} else {

				setUpdatedRow('documents', $row->id, 'delete');
				mysql_query("DELETE * FROM `".index_table."` WHERE index_id='{$row->id}' ");
				mysql_query("DELETE * FROM `documents_resellers` WHERE document_id='{$row->id}' ");
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
					<input type="text" name="title[]" value="<? echo textencode($title[$oldrecord[$i]]); ?>" />
				</fieldset>
				<fieldset>
					<label>Document</label>
					<?php file_field('file2'.$i,'../uploads/documents/',$file2[$oldrecord[$i]]);?>
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
					<label>Public File</label>
					<input type="checkbox" name="is_public[]" value="1" <?php echo checked($is_public[$oldrecord[$i]], '1'); ?> /> This is public file, any student can view it.
				</fieldset>

				<fieldset>
					<label>Classes</label>
					<?php include('functions/_index_table.php'); ?>
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
			
			$sql[] = " (documents.title LIKE '%$keywordSQL%' OR documents.description LIKE '%$keywordSQL%' ) ";
		}

		if(isReseller) {
			$sql[] = " documents_resellers.reseller_id = '". isReseller ."' ";
		} else if($Reseller) {
			$sql[] = " documents_resellers.reseller_id = '{$Reseller['id']}' ";
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
		if( $Sub ) {
			$sql[] = " catSub.sub_id='{$Sub['id']}' ";
		} else if( $Category ) {
//			$sql[] = " catSub.sub_id='0' AND catSub.cat_id='{$Category['id']}' ";
			$sql[] = " catSub.cat_id='{$Category['id']}' ";
		}

		$where = ( $sql ) ? " WHERE " . implode(' AND ', $sql ) : '';

		$strSQL="SELECT documents.*, GROUP_CONCAT( DISTINCT documents_resellers.reseller_id SEPARATOR ',' ) as documents_resellers, GROUP_CONCAT( DISTINCT catSub.title SEPARATOR '<~~>' ) as cat_titles
		FROM ( documents
		LEFT JOIN documents_index ON (documents.id = documents_index.index_id)
		LEFT JOIN (
			SELECT category.id as cat_id, 0 as sub_id, category.title as title FROM category 
			UNION
			SELECT category.id as cat_id, category_sub.id as sub_id, concat_ws('<~>', category.title, category_sub.title) as title
			FROM category 
			LEFT JOIN category_sub ON ( category.id = category_sub.cat_id)
		) as catSub ON (catSub.sub_id = documents_index.sub_id AND catSub.sub_id = documents_index.sub_id)
		) 
		LEFT JOIN documents_resellers ON (documents.id = documents_resellers.document_id)

		$where
		GROUP BY documents.id
		";

		
		if( $action == 'export' ) {
			include 'functions/_export.php';
			
			$excel = new export_excel("Documents");
			$excel->addField('title', 'Title');
			$excel->addField('description', 'Notes');

			if( !isReseller ) {
				$excel->addField('documents_resellers', 'Schools', 'style_documents_resellers', $Resellers);
			}

			$excel->addField('cat_titles', 'Classes', 'style_cat_titles');

			$excel->addField('status', 'Status', 'ucwords');
			$excel->export( "$strSQL ORDER BY documents.rank DESC");
			exit;
		}

		$objRS=mysql_query($strSQL);
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
			<?php if( !isReseller ) { ?>
    			<th>Schools</th>
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

	<?php if( !isReseller ) { ?>
		<td><?php 
			$_resellers = style_documents_resellers($row->documents_resellers, $Resellers, 'array');
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
