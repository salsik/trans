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
$limitationPoll = '';
$limitation2 = '';
if(isReseller) {
	$limitation = " AND polls_options.poll_id IN(SELECT id FROM polls WHERE reseller_id='".isReseller."') ";
	$limitationPoll = " AND reseller_id='".isReseller."' ";
}

$AllowAdd = true;

$fieldsArray = array(
	'title',
);

$queryStr = '';
if( $keyword ) {
	$queryStr .= "keyword=$keyword&";
}

$Poll = getDataByID('polls', $_GET['poll_id'], " TRUE $limitationPoll");
if( $Poll ) {
	$queryStr .= "poll_id={$Poll['id']}&";
	$limitation2 = " AND polls_options.poll_id='{$Poll['id']}' ";
	
	$Reseller = getDataByID('resellers', $Poll['reseller_id']);
}


//Define the maximum items while listine
$PageSize=10;
//Define the maximum items while adding
$max=10;

if(!$Poll) {
	?><h4 class="alert_error">Poll not found!!</h4><?php 
	exit;
}
else {
	?><h4 class="alert_info">
		Poll: <?php echo $Poll['title']; ?>
		<span style="display: block; font-size: 12px; line-height: 27px; font-weight: normal;">
			<a href="polls.php?action=edit&ids[]=<?php echo $Poll['id']; ?>">Edit Poll</a>
			 - <a href="polls.php?action=results&poll_id=<?php echo $Poll['id']; ?>">View Results</a>
		</span>
	</h4><?php 
}

require_once('_lister.class.php');
$sortableLister = new lister("_sort_reverse.php?filename=".$filename."&table=polls_options");

$rank=getfield(getHTTP('rank'),"rank","polls_options");

switch ($action):
	//case "down":
		case "up": //reverse
		$strSQL="select * from polls_options WHERE rank='".($rank+1)."'" ;
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update polls_options set rank='".($rank+1)."' WHERE rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update polls_options set rank='".$rank."' WHERE id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from polls_options WHERE rank >'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update polls_options set rank='".($rank+1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update polls_options set rank='".$rank."' where id='".$row->id."'";
					mysql_query($strSQLord);
				}
			}
		}
	break;
	//case "up":
		case "down": //reverse
		$strSQL="select * from polls_options where rank='".($rank-1)."'";
		$objRS=mysql_query($strSQL);
		$total=mysql_num_rows($objRS);
		if($total>0){
			if ($row=mysql_fetch_object($objRS)){
				$strSQLord="update polls_options set rank='".($rank-1)."' where rank='".$rank."'";
				mysql_query($strSQLord);
				$strSQLord="update polls_options set rank='".$rank."' where id='".$row->id."'";
				mysql_query($strSQLord);
			}
		}
		else{
			$strSQL="select * from polls_options where rank <'".$rank."'";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			if($total>0){
				if ($row=mysql_fetch_object($objRS)){
					$strSQLord="update polls_options set rank='".($rank-1)."' where rank='".$rank."'";
					mysql_query($strSQLord);
					$strSQLord="update polls_options set rank='".$rank."' where id='".$row->id."'";
					mysql_query($strSQLord);
				}
			}
		}

	break;

	case "add":
		if( !$AllowAdd ) {
			break;
		}
		$showing = "record";
	break;
	case "edit":
		//Get needed data from the DB
		$strSQL="select * from polls_options where id IN(".implode(',',$ids).") $limitation $limitation2 order by rank DESC";
		$objRS=mysql_query($strSQL);
		$i=0;
		while ($row=mysql_fetch_object($objRS)){
			foreach($fieldsArray as $field) {
				${$field}[$i] = $row->$field;
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
	case "addexe":
		if( !$AllowAdd ) {
			break;
		}
	case "editexe":
		//Get new data from the FORM
		foreach($fieldsArray as $field) {
			${$field} = $_POST[$field];
		}

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
			
			if( $action == 'addexe' ) {
				$sql['poll_id']= " `poll_id`='{$Poll['id']}', ";
			}

			$strSQL = "polls_options set " . implode('', $sql);

			if( empty($title[$i]) ) {
				$Errors[] = "Missing Option Title!!";
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/polls/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){

							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/polls/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
							$Rimage->save('../uploads/polls/thumb/'.$file1[$i]);							
						}						 
					}
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						$strSQL ="update ".$strSQL." image='".sqlencode(trime($file1[$i]))."' where id='".$ids[$i]."' $limitation $limitation2 ";
					}
				}
			else:
				//Conditions and Queries while adding
				if (empty($title[$i])){
					//Do nothing for empty records but Set the flag to zero
					$flag=0;
				}else 
				if ( $Errors ){
					//Set the error message
					$errorMsg[$j]=array_shift($Errors);
					//Set the OLD Bad records Positions's depending on the POST result
					$oldrecord[$j]=$i;
					$j++;
					//Set the flag to zero if conditions not verified to avoid executing the query
					$flag=0;
				}else{
					if ( $result=file_upload('image','file1'.$i,'../uploads/polls/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){
							
							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/polls/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT );
							$Rimage->save('../uploads/polls/thumb/'.$file1[$i]);							
						}							 
				    }
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						
						$q=mysql_query("SELECT max(rank) as max FROM polls_options");
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
				if( !$q )
				{
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

		$strSQL="select * from polls_options where id IN(".implode(',',$ids).") $limitation $limitation2 order by rank DESC";
		$objRS=mysql_query($strSQL);
		while ($row=mysql_fetch_object($objRS))
		{
			if($row->image != '') {
				@unlink('../uploads/polls/'.$row->image);	
				@unlink('../uploads/polls/thumb/'.$row->image);	
			}

			$strSQL="DELETE FROM polls_options WHERE id = '".$row->id ."' LIMIT 1";
			if(!mysql_query($strSQL) ) {
				if(empty($errorMsg)) {
					$errorMsg="Some Records didn't affected!!";
				}
			} else {
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
	<header><h3>Poll Options: <?php echo ucwords($action); ?> Record
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
					<label>Option Title</label>
					<input type="text" name="title[]" value="<? echo textencode($title[$oldrecord[$i]]); ?>" />
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
?>


<?php if(!empty($msg)) { ?>
	<h4 class="alert_success"><?php echo $msg; ?></h4>
<?php } else if(!empty($errorMsg)) { ?>
	<h4 class="alert_error"><?php echo $errorMsg; ?></h4>
<?php } ?>

<?php if( false ) { ?>
<div class="alert_browse">
	<form action="<? echo $filename; ?>" method="GET" >
		<input type="hidden" name="poll_id" value="<?php echo $Poll['id']; ?>" />
		Find: <input name="keyword" value="<? echo htmlspecialchars($keyword, ENT_QUOTES); ?>" size="30" />

		<input type="submit" value="Browse" />
	</form>
</div>
<?php } ?>
<?php 
		$sql = array();
		if( !empty($keyword) )
		{
			$keywordSQL = mysql_real_escape_string($keyword);
			$keywordSQL = str_replace(' ', '% %', $keywordSQL);
			
			//polls_options.title LIKE '%$keywordSQL%' OR
			$sql[] = " ( polls_options.title LIKE '%$keywordSQL%' ) ";
		}

		$where = ( $sql ) ? " AND " . implode(' AND ', $sql ) : '';

		$strSQL="SELECT polls_options.*
		FROM polls_options 
		WHERE TRUE $where

		$limitation $limitation2
		";

		$objRS=mysql_query("$strSQL ORDER BY polls_options.rank DESC");
		$total=@mysql_num_rows($objRS);
//echo mysql_error();
?>


<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=delete" method="post" name="del" id="del">
<article class="module width_full">
	<header><h3 class="tabs_involved">Poll Options</h3>
	<ul class="tabs">
<?php if( $AllowAdd ) { ?>
   		<li><a href="#add" onClick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=add'" >Add New</a></li>
<?php } ?>
   		<li><a href="#edit" onClick="edit('<?=$menu?>&<?php echo $queryStr; ?>p=<?=$p?>')" >Edit</a></li>
    	<li><a href="#delete" onClick="conf()">Delete</a></li>
	</ul>
	</header>
	<div class="tab_container">
		<table class="tablesorter" cellspacing="0"> 
		<thead> 
			<tr> 
   				<th width="1"><input type="checkbox" onClick="checkall()" name="main"></th>
    			<th>Title</th> 
                <th width="1">Added</th>
                <th width="1">Rank</th>
			</tr> 
		</thead> 
		<tbody id="trContainer" class="sortable"> 

	<?php while ($row=mysql_fetch_object($objRS)){ ?>
    <tr id="tr_<? echo $row->id; ?>">
		<td align="right"><input type="checkbox" name="ids[]" value="<? echo $row->id; ?>"></td>
		<td>
			<div><b><? echo $row->title ?></b></div>
			<div>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>action=edit&ids[]=<? echo $row->id; ?>">Edit</a>
			</div>
		</td>
	
		<td>
			<div><? echo $row->date; ?></div>
		</td>

		<td><?php echo_SetOrder( $queryStr, $row->id, $p ); ?></td>
	</tr>
	<?php }?>
			</tbody> 
			</table>
		</div><!-- end of .tab_container -->
		<footer></footer>
	</article><!-- end of content manager article -->
</form>
<?php
	break;
endswitch; 

include '_bottom.php';

