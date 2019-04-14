<?php

define('THUMB_WIDTH', 482); // 218
define('THUMB_HEIGHT', 317); // 150

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include "_top.php";

if(!isSchool) {
	redirect('index.php');
	die();
}

$description = $Admin['description'];
$agenda_send_time_str = $Admin['agenda_send_time_str'];
$contact_email = $Admin['contact_email'];
$contact_send_email = $Admin['contact_send_email'];

$file1 = $Admin['image'];

switch ($action):
	case "editexe":
		
		$description = $_POST['description'];
		$agenda_send_time_str = $_POST['agenda_send_time_str'];
		$contact_email = $_POST['contact_email'];
		$contact_send_email = $_POST['contact_send_email'];
		
		$file1 = $_POST['file1'];
		
		$sql = array();
		$sql[] = " `description`='".sqlencode(trime( $description ))."' ";
		$sql[] = " `contact_send_email`='".sqlencode(trime( $contact_send_email ))."' ";
		
		$emails = explode(',', $contact_email);
		$_contact_email = array();
		foreach($emails as $_email) {
			$_email = trim($_email);
			if(!isEmail($_email)) {
				$_contact_email = false;
				break;
			}
			$_contact_email[] = $_email;
		}
		if( $_contact_email ) {
			$_contact_email = implode(', ', $_contact_email);
			$sql[]= " `contact_email`='".sqlencode(trime($_contact_email))."' ";
		}

		if( $agenda_send_time_str ) {
			$_agenda_time = is_time( $agenda_send_time_str );
			if($_agenda_time) {
				$agenda_send_time = sprintf("%02d%02d", $_agenda_time['H'], $_agenda_time['m']);
				$sql[]= " `agenda_send_time_str`='".sqlencode(trime($agenda_send_time_str))."' ";
				$sql[]= " `agenda_send_time`='".sqlencode(trime( $agenda_send_time ))."' ";
			}
		}
		else {
			$_agenda_time = true;
			$sql[]= " `agenda_send_time_str`='' ";
			$sql[]= " `agenda_send_time`='0' ";
		}

		if( empty($description) ) {
			$errorMsg="Missing Description!!";
			break;
		}
		else if(!$_agenda_time) {
			$errorMsg = "Invalid Agenda Time!!";
		}
		else if( !$_contact_email ) {
			$errorMsg = "Missing/Invalid School Contact Emails!";
		}
		else if( !isemail($contact_send_email) ) {
			$errorMsg = "Missing/Invalid School Send Email!";
		}
		
		
		
		
		$image = '';
		if ( $result=file_upload('image','file1','../uploads/schools/', $errorMsg)){
			 $image=$result['name'];
			if(empty($errorMsg)){

				$Rimage = new SimpleImage();
				$Rimage->load('../uploads/schools/'.$image);
				if( $Rimage->getWidth() > THUMB_WIDTH ) {
					$Rimage->resizeToWidth( THUMB_WIDTH );
				}
				$Rimage->save('../uploads/schools/thumb/'.$image);							
			}						 
		}
		if(!empty($errorMsg)){
			break;
		}
		if($image) {
			$sql[] = " `image`='".sqlencode(trime( $image ))."' ";
		}
		
		$sql = implode(',', $sql);
		
		$strSQL = "UPDATE schools SET {$sql} WHERE id='{$Admin['id']}' LIMIT 1";
//var_dump($Admin);
//echo $strSQL;
		$q = mysql_query($strSQL);
		if( $q ) {
			redirect('about-us.php?msg=updated');
			exit;
		}
		else{
			$errorMsg = 'Something went wrong!';
		}
	break;
endswitch;

if($_GET['msg'] == 'updated') {
	$msg="Record(s) updated successfully!!";
}

?>

<?php if(!empty($msg)) { ?>
	<h4 class="alert_success"><?php echo $msg; ?></h4>
<?php } else if(!empty($errorMsg)) { ?>
	<h4 class="alert_error"><?php echo $errorMsg; ?></h4>
<?php } ?>

<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>" method="post" name="myform" enctype="multipart/form-data">
<input type="hidden" name="action" value="editexe"> 
<article class="module width_full">
	<header><h3>About School: <?php echo $Admin['title']; ?>
		<input type="submit" value="Save" class="alt_btn">
		<input type="button" value="Cancel" onclick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>'">
		</h3></header>

		<div class="module_content inline">
			<fieldset>
				<label>About Us</label>
				<textarea name="description" rows="12" ><? echo textencode($description); ?></textarea>
			</fieldset>
			<fieldset>
				<label>Agenda E-mail Time</label>
				<div style="margin-left: 210px;">
					<input type="text" class="timepicker" name="agenda_send_time_str" value="<? echo textencode($agenda_send_time_str); ?>" />
					<br style="clear:both" />[EMPTY] = disable
				</div>
			</fieldset>
			<fieldset>
				<label>Contact School E-mails</label>
				<div style="margin-left: 210px;">
					<input type="text" name="contact_email" value="<? echo textencode($contact_email); ?>" />
					<br style="clear:both" />Separate email addresses with comma ( , )
				</div>
			</fieldset>
			
			<fieldset>
				<label>School Send E-mail Address</label>
				<div style="margin-left: 210px;">
					<input type="text" name="contact_send_email" value="<? echo textencode($contact_send_email); ?>" />
				</div>
			</fieldset>
			
			<fieldset>
				<label>Logo</label>
				<?php file_field('file1','../uploads/schools/',$file1);?>
			</fieldset>
			<div class="clear"></div>
		</div>
	<footer></footer>
</article>
</form>

<?php

include '_bottom.php';




