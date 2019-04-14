<?php

define('THUMB_WIDTH', 482); // 218
define('THUMB_HEIGHT', 317); // 150

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include "_top.php";

include 'functions/_auto_complete.php';

$limitation = '';
$limitationQuestion = '';
$limitation2 = '';
if(isReseller) {
	
	 
	//$limitation = " AND consult_replies.question_id IN(SELECT id FROM questions WHERE reseller_id='".isReseller."') ";
	//$limitationQuestion = " AND reseller_id='".isReseller."' ";
}

$AllowAdd = true;

$fieldsArray = array(
	'description','doctor_id','mention_expert',
);

$queryStr = '';
if( $keyword ) {
	$queryStr .= "keyword=$keyword&";
}

$Question = getDataByID('expert_consult_messages', $_GET['question_id'], " TRUE $limitationQuestion");

//var_dump($Question);
if( $Question ) {
	$queryStr .= "question_id={$Question['id']}&";
	$limitation2 = " AND consult_replies.question_id='{$Question['id']}' ";
	

	$Reseller = getDataByID('resellers', $Question['reseller_id']);
	$Doctor = getDataByID('doctors', $Question['doctor_id']);
	 

	
}


//Define the maximum items while listine
$PageSize=10;
//Define the maximum items while adding
$max=1;

if(!$Question) {
	?><h4 class="alert_error">Question not found!!</h4><?php 
	exit;
}
else {
	?><h4 class="alert_info">Question: <?php echo $Question['title']; ?></h4><?php 
}

 
switch ($action):

	case "add":
		if( !$AllowAdd ) {
			break;
		}
		$showing = "record";
	break;
	case "edit":
		//Get needed data from the DB
		$strSQL="select * from consult_replies where id IN(".implode(',',$ids).") $limitation $limitation2 order by id DESC";
		$objRS=mysql_query($strSQL);
		$i=0;
		while ($row=mysql_fetch_object($objRS)){
			foreach($fieldsArray as $field) {
			//	${$field}[$i] = $row->$field;
				//echo "row:" . $row->field;
				;
			}
			
			 
			$doctor_id[$i] = $row->doctor_id;
			$mention_expert[$i] = $row->mention_expert;
			$description[$i] = $row->description;
			

			$file1[$i] = $row->image;
			$file2[$i] = $row->file;
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
		$fieldsArrr = [];
		foreach($_POST[$field] as $field) {
			$fieldsArrr['doctor_id'] = $field;
		}
		//echo 'okkk '.$fieldsArrr['doctor_id'];

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

			$Errors = array();
			
			$sql = array();
			//var_dump($fieldsArray);
			/*foreach($fieldsArray as $field) {
				//echo ${$field}[$i];
				$sql[$field] = " `$field`='".trime(${$field}[$i])."', ";
				//echo "\nsql". $sql[$field];
			}*/
			$sql['doctor_id'] = 'doctor_id='.$_POST['doctor_id'].' ,';
			$sql['description'] = 'description=\''.$_POST['description'][0].'\' ,';
			$sql['mention_expert'] = 'mention_expert=\''.$_POST['mention_expert'][0].'\' ,';
			//echo $sql['description'];
			if( $action == 'addexe' ) {
				$sql['question_id']= " `question_id`='{$Question['id']}', ";
			}
			
			 
			
			$strSQL = "consult_replies set " . implode('', $sql);

			if( empty($description[$i]) ) {
				$Errors[] = "Missing Reply!!";
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/consult/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){

							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/consult/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
							$Rimage->save('../uploads/consult/thumb/'.$file1[$i]);							
						}						 
					}


                    if ($result = file_upload('document', 'file2' . $i, '../uploads/consult/', $errorMsg[$j])) { // document
                        $file2[$i] = $result['name'];
                    }

					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						 
						$strSQL ="update ".$strSQL." image='".sqlencode(trime($file1[$i])) . "', file='" . sqlencode(trime($file2[$i])) . "' where id='" . $ids[$i] . "' $limitation $limitation2 ";
					}
				}
			else:
				//Conditions and Queries while adding
//				if (empty($title[$i])){
//					//Do nothing for empty records but Set the flag to zero
//					$flag=0;
//				}else 
				if ( $Errors ){
					//Set the error message
					$errorMsg[$j]=array_shift($Errors);
					//Set the OLD Bad records Positions's depending on the POST result
					$oldrecord[$j]=$i;
					$j++;
					//Set the flag to zero if conditions not verified to avoid executing the query
					$flag=0;
				}else{
					if ( $result=file_upload('image','file1'.$i,'../uploads/consult/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){
							
							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/consult/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT );
							$Rimage->save('../uploads/consult/thumb/'.$file1[$i]);							
						}							 
				    }
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						
						$q=mysql_query("SELECT max(rank) as max FROM consult_replies");
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
				
				
				//echo $strSQL;
				$q = mysql_query($strSQL);
				if( $q ) {
					if($action == 'addexe') {
						$_insert_id = mysql_insert_id();
					} else {
						$_insert_id = intval( $ids[$i] );
					}
					$_action = ( $action == 'addexe' ) ? 'add' : 'edit';
					setUpdatedRow('consult_replies', $_insert_id, $_action);
					
										
					// send  apush notification to all users

					 
					 $_doctors = getAllData('doctors');
						foreach ($_doctors as $doctor) {
                                $sql = "INSERT INTO `notifications` SET
                                            `user_id`='" . sqlencode(trime($doctor['id'])) . "',
                                            `type` = 'consult',
                                            `news_id`='" . sqlencode(trime($_insert_id)) . "',
                                             `text`='New consulting request'
                                            ";
										
											
											
                                $qq = mysql_query($sql);
                            }
							
							
						  include('push_notifications.php');
						  
					
					
					
					
				}
				else{
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

		$strSQL="select * from consult_replies where id IN(".implode(',',$ids).") $limitation $limitation2 order by id DESC";
		$objRS=mysql_query($strSQL);
		while ($row=mysql_fetch_object($objRS))
		{
			if($row->image != '') {
				@unlink('../uploads/consult/'.$row->image);	
				@unlink('../uploads/consult/thumb/'.$row->image);	
			}

			$strSQL="DELETE FROM consult_replies WHERE id = '".$row->id ."' LIMIT 1";
			if(!mysql_query($strSQL) ) {
				if(empty($errorMsg)) {
					$errorMsg="Some Records didn't affected!!";
				}
			} else {
				setUpdatedRow('consult_replies', $row->id, 'delete');
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
	<header><h3>Question Replies: <?php echo ucwords($action); ?> Record
		<input type="submit" value="Save" class="alt_btn">
		<input type="button" value="Cancel" onclick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>'">
		</h3></header>

		<?php for($i=0;$i<$max;$i++){ ?>
		
		 <?php
            $_question = array();
            if ($action == 'edit') {
                $_question = getDataByID('consult_replies', $ids[$i], " TRUE $limitation $limitation2 ");
            }
            ?>
		
		
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
					<label>Question</label>
					<?php echo $Question['title']; ?>
				</fieldset>
				
				 <?php
            if ($action == 'edit') {
					$Doctor = getDataByID('doctors', $_question['doctor_id']);
              
            } else {
                $doctID = nor($doctor_id[$oldrecord[$i]], $_GET['doctor_id'], true);
			//echo $doctID;
				$Doctor = getDataByID('doctors', $doctID, $limitationDoctor);
            }
            ?>
			
            	
				
				<fieldset>
				<label>Expert</label> <span class="autocomplete" data-link="_get.php?from=doctors&where=expert=1">
				
                    <input class="input_short auto_name" type="text" name="doctor_name" value="<?php echo($Doctor['full_name']); ?>" />
                    <input class="input auto_id" type="hidden" name="doctor_id" value="<?php echo $Doctor['id']; ?>" />
                </span>
				</fieldset>
				
				
                                    <label style="width: auto;">
									<?php
									$flags = '';
									$flags .= ( $mention_expert[$oldrecord[$i]] ) ? ' checked="CHECKED" ' : '';
									//$flags .= ( $action == 'edit' ) ? ' disabled="DISABLED" ' : '';
									?>

                                        <input type="checkbox" name="mention_expert[<?php echo $i; ?>]" value="1" <?php echo $flags; ?> />
											Mention Expert Name in Reply.
                                    </label>
									
									
				 
				
				<fieldset>
					<label>Reply</label>
					<textarea name="description[]" rows="12" ><?php echo (($description[$oldrecord[$i]])); ?></textarea>
				</fieldset>

				<fieldset>
                           <label>Document</label>
                                <?php file_field('file2' . $i, '../uploads/consult/', $file2[$oldrecord[$i]]); ?>
                   </fieldset>

				<fieldset>
					<label>Image</label>
					<?php

					 
					file_field('file1'.$i,'../uploads/consult/',$file1[$oldrecord[$i]]);?>
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
		<input type="hidden" name="question_id" value="<?php echo $Question['id']; ?>" />
		Find: <input name="keyword" value="<? echo htmlspecialchars($keyword, ENT_QUOTES); ?>" size="30" />

		<input type="submit" value="Browse" />
	</form>
</div>
<?php } ?>
<?php 
		$sql = array();
//		if( !empty($keyword) )
//		{
//			$keywordSQL = mysql_real_escape_string($keyword);
//			$keywordSQL = str_replace(' ', '% %', $keywordSQL);
//			
//			//consult_replies.title LIKE '%$keywordSQL%' OR
//			$sql[] = " ( consult_replies.description LIKE '%$keywordSQL%' ) ";
//		}

		$where = ( $sql ) ? " AND " . implode(' AND ', $sql ) : '';

		$strSQL="SELECT consult_replies.*
		FROM consult_replies 
		WHERE TRUE $where

		$limitation $limitation2
		";

		if( $action == 'export' ) {
			include 'functions/_export.php';
			
			$excel = new export_excel("Questions Replies: " .$Question['title']);
			$excel->addField('from', 'From', 'style_reply_from');
			$excel->addField('description', 'Reply');
			$excel->addField('date', 'Date');
			
			$excel->export( "$strSQL ORDER BY consult_replies.id DESC");
			exit;
		}
		$objRS=mysql_query("$strSQL ORDER BY consult_replies.id desc");
		$total=@mysql_num_rows($objRS);
//echo mysql_error();
?>

<style>
<!--
.theQuestion td {
	background: #E2F6C5;

}
-->
</style>
<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=delete" method="post" name="del" id="del">
<article class="module width_full">
	<header><h3 class="tabs_involved">Question Replies: <?php echo $Question['title']; ?></h3>
	<ul class="tabs">
<?php if( $AllowAdd ) { ?>
   		<li><a href="#add" onClick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=add'" >Add Reply</a></li>
<?php } ?>
   		<li><a href="#edit" onClick="edit('<?=$menu?>&<?php echo $queryStr; ?>p=<?=$p?>')" >Edit</a></li>
    	<li><a href="#delete" onClick="conf()">Delete</a></li>
	</ul>
	
	</header>
	<div class="tab_container">
		<table class="tablesorter" cellspacing="0"> 
		<tbody id="trContainer"> 
		<tr class="theQuestion">
	   		<td valign="top" width="1"><input type="checkbox" onClick="checkall()" name="main"></td>
			<td>
				<?php if( $Question['image'] ) { ?>
					<div style="float: right;">
						<?php echo scale_image("../uploads/consult/thumb/". $Question['image'], 300); ?>
					</div>
				<?php }?>
				<div>
					<b>Question: <?php echo $Question['title']; ?></b>
				</div>
				<div>
					<b>Date:</b> <?php echo $Question['date']; ?>
				</div>
				<br />
				<div><?php echo $Question['description']; ?></div>
			</td>
		</tr>
	<?php while ($row=mysql_fetch_object($objRS)){ ?>
<?php 

//var_dump($row);


	$strSQL1 = "SELECT doctors.full_name as doctor_full_name
		FROM  consult_replies 
		LEFT JOIN doctors ON doctors.id=consult_replies.doctor_id
		where consult_replies.doctor_id = ". $row->doctor_id;
	
	 $q1 = mysql_query($strSQL1);
	 
	 $fromName = "";
	 if($row2=mysql_fetch_object($q1))
		 $fromName = $row2->doctor_full_name;
	 /*
	 var_dump(mysql_fetch_object($q1));
	 die;
            if ($q1 && mysql_num_rows($q1)) {
                
				$row1 = mysql_fetch_object($q1);
				$from = $row1->doctor_full_name;
				
				echo "frm". $from ;
            }
			
			if($row2 = mysql_fetch_object($q1))
				$from = $row2->doctor_full_name;
				
			
			//$row = mysql_fetch_object($objRS)
			
			//$from = mysql_fetch_object($q1);
			
			//echo $q1 ;
			echo "from" . $from ;
			*/
		
	//$from = $row->doctor_id;
	
	//echo $strSQL1;

?>
    <tr id="tr_<? echo $row->id; ?>">
		<td valign="top" width="1" align="right"><input type="checkbox" name="ids[]" value="<? echo $row->id; ?>"></td>
		<td>
		
			<?php
               include 'functions/_auto_complete.php';
                   


			if( $row->image ) { ?>
				<div style="float: right;">
					<?php echo scale_image("../uploads/consult/thumb/". $row->image, 300); ?>
				</div>
			<?php }?>
			<div>
				<b>Reply From:</b><?php echo $fromName; ?>
			</div>
			<div>
				<b>Date:</b> <?php echo $row->date; ?>
			</div>
			<br />
			<div><?php echo $row->description; ?></div>
			<div>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>action=edit&ids[]=<? echo $row->id; ?>">Edit</a>
			</div>
		</td>
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

function style_reply_from($from) {
	global $Reseller, $Doctor;
	
	return ($from=='reseller') ? $Reseller['title']: $Doctor['full_name'];
	
}



