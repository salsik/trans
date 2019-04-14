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
	$limitation = " AND banners.school_id='".isSchool."' ";
}
else if( !isAdmin ) {
	die();
}

$fieldsArray = array(
	'title', 'description', 'link', 'status',
	'plan_clicks', 'plan_impressions', 'plan_end_date', 'plan_zone', 
//	'clicks', 'impressions', 
	'date', 
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

if( !$_BannersZones[ $_GET['zone'] ] ) {
	$_GET['zone'] = '';
}
$queryStr .= "zone={$_GET['zone']}&";

if( !isSchool ) {
	$School = getDataByID('schools', $_GET['school_id']);
	if( $School ) {
		$queryStr .= "school_id={$School['id']}&";
	}

	$Schools = array();
	if($action=='add') {
		$q = mysql_query("SELECT schools.* FROM `schools` WHERE schools.status='active' ORDER BY schools.title ASC");
	} else {
		$q = mysql_query("SELECT schools.* FROM `schools` ORDER BY schools.title ASC");
	}
	if( $q && mysql_num_rows($q ) )
	{
		while( $row = mysql_fetch_assoc( $q ))
		{
			$Schools[ $row['id'] ] = $row;
		}
	}
}
//Define the maximum items while listine
$PageSize=10;
//Define the maximum items while adding
$max=10;

require_once('_lister.class.php');
$sortableLister = new lister("_sort_reverse.php?filename=".$filename."&table=banners");

$rank=getfield(getHTTP('rank'),"rank","banners");

if($School) {
	?><h4 class="alert_info">School: <?php echo $School['title']; ?></h4><?php 
}

switch ($action):
	//case "down":
		case "up": //reverse
		$strSQL="select * from banners WHERE rank='".($rank+1)."'" ;
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update banners set rank='".($rank+1)."' WHERE rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update banners set rank='".$rank."' WHERE id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from banners WHERE rank >'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update banners set rank='".($rank+1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update banners set rank='".$rank."' where id='".$row->id."'";
					mysql_query($strSQLord);
				}
			}
		}
	break;
	//case "up":
		case "down": //reverse
		$strSQL="select * from banners where rank='".($rank-1)."'";
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update banners set rank='".($rank-1)."' where rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update banners set rank='".$rank."' where id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from banners where rank <'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update banners set rank='".($rank-1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update banners set rank='".$rank."' where id='".$row->id."'";
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
		$strSQL="select * from banners where id IN(".implode(',',$ids).") $limitation order by rank DESC";
		$objRS=mysql_query($strSQL);
		$i=0;
		while ($row=mysql_fetch_object($objRS)){
			foreach($fieldsArray as $field) {
				${$field}[$i] = $row->$field;
			}
		
			if( ! isSchool ){
				$schools[$i] = array();
				$q = mysql_query("SELECT * FROM `banners_schools` WHERE banner_id='{$row->id}' ");
				if($q && mysql_num_rows($q)) {
					while($indx = mysql_fetch_assoc($q)) {
						$schools[$i][] = $indx['school_id'];
					}
				}
			}

//			$school_id[$i] = $row->school_id;
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
//		$school_id = $_POST['school_id'];
		$schools = $_POST['schools'];
//var_dump($_POST);
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
			
			$link[$i] = fixLinkProtocol( $link[$i] );
			
			$sql = array();
			foreach($fieldsArray as $field) {
				$sql[$field] = " `$field`='".sqlencode(trime(${$field}[$i]))."', ";
			}

			if( $action == 'addexe' && isSchool) {
				$sql['add_by_id']= " `add_by_id`='".isSchool."', ";
			}
			
			if( $action == 'addexe' && isSchool) {
				$sql['school_id']= " `school_id`='".isSchool."', ";
			}
//			if( !isSchool ) {
//				$_school = getDataByID('schools', $school_id[$i]);
//
//				if( !$_school) {
////					$Errors[] = "Missing School!!";
//					$sql['school_id']= " `school_id`='0', ";
//				} else {
//					$sql['school_id']= " `school_id`='{$_school['id']}', ";
//				}
//			}

			$_schools = array();
			if( !isSchool ) {
				$_schools = getDataByIDs('schools', $schools[$i]);
			}

			$strSQL = "banners set " . implode('', $sql);

			$bannerZone = $_BannersZones[ $plan_zone[$i] ];

			if( empty($title[$i]) ) {
				$Errors[] = "Missing Title!!";
			}
//			else if( empty($link[$i]) ) {
//				$Errors[] = "Missing Link!!";
//			}
			else if( !$bannerZone || ( isSchool && !$bannerZone['school'] ) ) {
				$Errors[] = "Missing Ad Zone!!";
			}
			else if( (!isSchool && !$_schools) && $bannerZone['private']) {
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/banners/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){

							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/banners/'.$file1[$i]);
							if( $bannerZone['resize'] == 'width' && $Rimage->getWidth() > $bannerZone['width'] ) {
								$Rimage->resizeToWidth( $bannerZone['width'] );
							} else if( $bannerZone['resize'] == 'height' && $Rimage->getHeight() > $bannerZone['height'] ) {
								$Rimage->resizeToHeight( $bannerZone['height'] );
							} else {
								$Rimage->resize( $bannerZone['width'], $bannerZone['height'] );
							}

//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
							$Rimage->save('../uploads/banners/thumb/'.$file1[$i]);							
						}						 
					}
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else if(empty( $file1[$i] )){// No File Uploaded
						$errorMsg[$j] = "No File uploaded!!";
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/banners/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){
							
							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/banners/'.$file1[$i]);
							if( $bannerZone['resize'] == 'width' && $Rimage->getWidth() > $bannerZone['width'] ) {
								$Rimage->resizeToWidth( $bannerZone['width'] );
							} else if( $bannerZone['resize'] == 'height' && $Rimage->getHeight() > $bannerZone['height'] ) {
								$Rimage->resizeToHeight( $bannerZone['height'] );
							} else {
								$Rimage->resize( $bannerZone['width'], $bannerZone['height'] );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT );
							$Rimage->save('../uploads/banners/thumb/'.$file1[$i]);							
						}							 
				    }
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else if(empty( $file1[$i] )){// No File Uploaded
						$errorMsg[$j] = "No File uploaded!!";
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						
						$q=mysql_query("SELECT max(rank) as max FROM banners");
						$r = mysql_fetch_object($q);
						
						$strSQL ="insert into ".$strSQL." image='".sqlencode(trime($file1[$i]))."', rank='".($r->max+1)."', time='".time()."' ";
					}
				}
			endif;

			if($flag){
				$q = mysql_query($strSQL);
				if( $q ) {
					if($action == 'addexe') {
						$banner_id = mysql_insert_id();
					} else {
						$banner_id = intval( $ids[$i] );
					}

					if( isSchool ) {
						if( $action == 'addexe' ) {
							$sql= "INSERT INTO `banners_schools` SET
								`banner_id`='".sqlencode(trime( $banner_id ))."'
								, `school_id`='".sqlencode(trime( isSchool ))."'
								";
							$qq = mysql_query( $sql );
							if(!$qq) {
								$warningMsg[-2] = 'Some records faced problems while indexing it!';
							}
						}
					}
					else {
						$qqDelete = mysql_query("DELETE FROM `banners_schools` WHERE banner_id='{$banner_id}' ");
//						if( $qqDelete ) {
							
							foreach($_schools as $school) {
								$sql= "INSERT INTO `banners_schools` SET
									`banner_id`='".sqlencode(trime( $banner_id ))."'
									, `school_id`='".sqlencode(trime( $school['id'] ))."'
									";
								$qq = mysql_query( $sql );
								if(!$qq) {
									$warningMsg[-2] = 'Some records faced problems while indexing it\'s schools!';
								}
							}
//						} else {
//							$warningMsg[-2] = 'Some records faced problems while indexing it\'s schools!';
//						}
					}
				}
				else {
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

		$strSQL="select * from banners where id IN(".implode(',',$ids).") $limitation order by rank DESC";
		$objRS=mysql_query($strSQL);
		while ($row=mysql_fetch_object($objRS))
		{

			if($row->image != '') {
				@unlink('../uploads/banners/'.$row->image);	
				@unlink('../uploads/banners/thumb/'.$row->image);	
			}

			$strSQL="DELETE FROM banners WHERE id = '".$row->id ."' LIMIT 1";
			if(!mysql_query($strSQL) ) {
				if( empty($errorMsg) ) {
					$errorMsg="Some Records didn't affected!!";
				}
			} else {
				mysql_query("DELETE  FROM `banners_schools` WHERE banner_id='{$row->id}' ");
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
	<header><h3>Banners: <?php echo ucwords($action); ?> Record
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
					<label>Link</label>
					<input type="text" name="link[]" value="<? echo textencode($link[$oldrecord[$i]]); ?>" />
				</fieldset>
			<?php if( !isSchool ) { ?>
				<fieldset>
					<label>School</label>
					<select name="schools[<?php echo $i; ?>][]" multiple="multiple" class="schools_select">
						<?php 
							if(!is_array($schools[$oldrecord[$i]])) {
								$schools[$oldrecord[$i]] = array();
							}
							foreach ($Schools as $school) {
								$Selected = ( in_array($school['id'], $schools[$oldrecord[$i]] ) ) ? ' selected="selected" ' : '';
								?><option value="<?php echo $school['id'];?>" <?php echo $Selected; ?> ><?php echo $school['title'];?></option><?php 
							}
						?>
					</select>
				</fieldset>
			<?php } ?>
				<fieldset>
					<label>Plan</label>
					<table width="60%" border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td>
								<label>plan_clicks
									<br /><input type="text" name="plan_clicks[]" value="<? echo textencode($plan_clicks[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label>Impressions
									<br /><input type="text" name="plan_impressions[]" value="<? echo textencode($plan_impressions[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label>End Date
									<br /><input type="text" class="datepicker" name="plan_end_date[]" value="<? echo textencode($plan_end_date[$oldrecord[$i]]); ?>" />
								</label>
							</td>
						</tr>
						<tr>
							<td>
								<label>Ad Zone</label>
									<br /><select name="plan_zone[]">
										<option value="">-- Select -- </option>
								<?php 
									foreach($_BannersZones as $k=>$zone) {
										if( !isSchool || $zone['school'] ) {
											$selected = selected($plan_zone[$oldrecord[$i]], $k);
											
											$_t = "{$zone['title']} ( {$zone['width']} X {$zone['height']})";
											
											
											?><option value="<?php echo $k; ?>" <?php echo $selected; ?>><?php echo $_t; ?></option><?php
										} 
									}
								?>
								</select>
							</td>
							<td>
								<label></label>
							</td>
							<td>
								<label></label>
							</td>
						</tr>
					</table>
				</fieldset>

				<fieldset>
					<label>Notes</label>
					<textarea name="description[]" rows="12" ><? echo textencode($description[$oldrecord[$i]]); ?></textarea>
				</fieldset>

				<fieldset>
					<label>Banner</label>
					<?php file_field('file1'.$i,'../uploads/banners/',$file1[$oldrecord[$i]]);?>
				</fieldset>
				<div class="clear"></div>
		</div>
	<footer></footer>
                    <?php } ?>
</article>
</form>

<?php if( !isSchool) { ?>
	<script type="text/javascript">
	<!--
	$(".schools_select").multiselect({
		header: "Select Schools!",
		noneSelectedText: 'Select Schools'
	}).multiselectfilter();
	//-->
	</script>
<?php } ?>

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
		Status: <select name="status">
			<option value="" >== Any ==</option>
			<option value="active" <?php echo selected($_GET['status'], 'active'); ?>>Active</option>
			<option value="inactive" <?php echo selected($_GET['status'], 'inactive'); ?>>Inactive</option>
		</select>
		Ad Zone: <select name="zone">
			<option value="">-- Select -- </option>
	<?php 
		foreach($_BannersZones as $k=>$zone) {
			if( !isSchool || $zone['school'] ) {
				$selected = selected($_GET['zone'], $k);
				
											
				$_t = "{$zone['title']} ( {$zone['width']} X {$zone['height']})";
				
				?><option value="<?php echo $k; ?>" <?php echo $selected; ?>><?php echo $_t; ?></option><?php
			} 
		}
	?>
		</select>
<?php if( !isSchool ) { ?>
		School: <span class="autocomplete" data-link="_get.php?from=schools">
			<input class="input_short auto_name" type="text" name="school_name" value="<?php echo trim( $School['title']); ?>" />
			<input class="input auto_id" type="hidden" name="school_id" value="<?php echo $School['id']; ?>" />
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
			
			$sql[] = " (banners.title LIKE '%$keywordSQL%' OR banners.description LIKE '%$keywordSQL%' ) ";
		}

		if(isSchool) {
			$sql[] = " banners_schools.school_id = '". isSchool ."' ";
		} else if($School) {
			$sql[] = " banners_schools.school_id = '{$School['id']}' ";
		}

		switch($_GET['status']) {
			case 'active':
				$sql[] = " banners.status='active' ";
				break;
			case 'inactive':
//				$sql[] = " banners.status='' ";
				$sql[] = " banners.status<>'active' ";
				break;
		}
		
		if($_GET['zone']) {
			$sql[] = " banners.plan_zone = '". mysql_real_escape_string($_GET['zone']) ."' ";
		}
		
		$where = ( $sql ) ? " WHERE " . implode(' AND ', $sql ) : '';

		$strSQL="SELECT banners.*
			, GROUP_CONCAT( DISTINCT banners_schools.school_id SEPARATOR ',' ) as banners_schools
		FROM banners 
		LEFT OUTER JOIN banners_schools ON (banners.id = banners_schools.banner_id)
		$where
		
		GROUP BY banners.id
		";

		
		if( $action == 'export' ) {
			include 'functions/_export.php';
			
			$excel = new export_excel("Banners");
			$excel->addField('title', 'Title');
			$excel->addField('description', 'Description');
			if( !isSchool ) {
//				$excel->addField('school_title', 'School');
				$excel->addField('banners_schools', 'Schools', 'style_list_schools', $Schools);
			}
			$excel->addField('plan_zone', 'Ad Zone', 'style_banner_name', $_BannersZones);
			
			$excel->addField('plan_clicks', 'Plan Clicks');
			$excel->addField('plan_impressions', 'Plan Impressions');
			$excel->addField('plan_end_date', 'Plan End Date');
				
				
			$excel->addField('clicks', 'Clicks');
			$excel->addField('impressions', 'Impressions');

			$excel->addField('status', 'Status', 'ucwords');
			$excel->export( "$strSQL ORDER BY banners.rank DESC");
			exit;
		}
		$objRS=mysql_query($strSQL);
		$total=@mysql_num_rows($objRS);
		$strSQL=makePages ($strSQL,$PageSize,$p, 'banners.rank desc');
		$objRS=mysql_query($strSQL);
		$count_all = @mysql_num_rows($objRS);
		
?>


<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=delete" method="post" name="del" id="del">
<article class="module width_full">
	<header><h3 class="tabs_involved">Banners Manager</h3>
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
    			<th>Link</th> 
    	<?php if( !isSchool ) { ?>
    			<th>Schools</th> 
    	<?php } ?>
                <th>Ad Zone</th>
                <th width="75">Plan Clicks</th>
                <th width="120">Plan Impressions</th>
                <th width="75">Plan End</th>

                <th width="1">Clicks</th>
                <th width="1">Impressions</th>

                <th width="1">Status</th>
                <th width="1">Image</th>
                <th width="1">Rank</th>
			</tr> 
		</thead> 
		<tbody id="trContainer" class="sortable"> 

	<?php while ($row=mysql_fetch_object($objRS)){ ?>
    <tr id="tr_<? echo $row->id; ?>">
		<td align="right"><input type="checkbox" name="ids[]" value="<? echo $row->id; ?>"></td>
		<td>
			<div><b><? echo $row->title ?></b></div>
			<div><?php if($row->description !=''){ echo summarize($row->description, 20); }?></div>
			<div>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>action=edit&ids[]=<? echo $row->id; ?>">Edit Banner</a>
			</div>
		</td>
		<td>
			<div><b><?php echo $row->link; ?></b></div>
		</td>
	<?php if( !isSchool ) { ?>
		<td><?php 
			$_schools = style_list_schools($row->banners_schools, $Schools, 'array');
			foreach($_schools as $k=>$v) {
				?><div>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>school_id=<? echo $k; ?>"><?php echo $v; ?></a>
			</div><?php 
			}
		?>
		</td>
	<?php } ?>
		<td>
			<div><?php echo $_BannersZones[ $row->plan_zone ]['title']; ?></div>
		</td>


		<td align="center"><?php echo $row->plan_clicks; ?></td>
		<td align="center"><?php echo $row->plan_impressions; ?></td>
		<td align="center"><?php echo ($row->plan_end_date == '0000-00-00') ? '-' : $row->plan_end_date; ?></td>
		
		<td align="center"><?php echo $row->clicks; ?></td>
		<td align="center"><?php echo $row->impressions; ?></td>



		<td align="center"><?php echo ( $row->status == 'active') ? 'Active' : 'Inactive'; ?></td>
		<td align="center"><?php echo scale_image("../uploads/banners/thumb/". $row->image, 100); ?></td>

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


function style_banner_name($banner, $banners) {
	return $banners[ $banner ]['title'];
}

function style_list_schools($_ids, $data, $html = false)
{

	if( !is_array($_ids)) {
		$_ids = explode(',', $_ids);
	}
	if( !is_array($data)) {
		$data = array();
	}
	
	$return = '';
	$array = array();
	
	foreach($_ids as $id) {
		if($data[ $id ]) {
			$Str = $data[ $id ]['title'];
			if( $html==='array') {
				$array[$id] = $Str;
			} else {
				$return .= ($html) ? "- $Str\r\n" : "$Str\r\n";
			}
		}
	}

	if( $html==='array') {
		return $array;
	}
	$return = trim($return);

	if($html) {
		return nl2br( $return );
	} else {
		return $return;
	}
}