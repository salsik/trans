<?php

define('THUMB_WIDTH', 482); // 218
define('THUMB_HEIGHT', 317); // 150

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include "_top.php";

if(!isTeacher) {
	redirect('index.php');
	die();
}

$file1 = $Admin['image'];

switch ($action):
	case "password":
		$password = $_POST['password'];
		$current_password = $_POST['current_password'];
		$confirm_password = $_POST['confirm_password'];
		
		$sql = array();
		$sql[] = " `password`='".md5($password)."' ";
		

		if( empty($current_password) ) {
			$errorMsg="Missing Current Password!!";
		}
		else if( empty($password) ) {
			$errorMsg="Missing New Password!!";
		}
		else if( MD5($current_password) != $Admin['password']) {
			$errorMsg="Missing Incorrect Current Password!!";
		}
		else if( $confirm_password != $password ) {
			$errorMsg="Missmatch Passwords!!";
		}
		if(!empty($errorMsg)){
			break;
		}
		
		$sql = implode(',', $sql);
		
		$strSQL = "UPDATE teachers SET {$sql} WHERE id='{$Admin['id']}' LIMIT 1";
//var_dump($Admin);
//echo $strSQL;
		$q = mysql_query($strSQL);
		if( $q ) {
			redirect('about-teacher.php?msg=updated');
			exit;
		}
		else{
			$errorMsg = 'Something went wrong!';
		}
	break;

//	case "image":
//		$file1 = $_POST['file1'];
//		
//		$sql = array();
//		
//		$image = '';
//		if ( $result=file_upload('image','file1','../uploads/teachers/', $errorMsg)){
//			 $image=$result['name'];
//			if(empty($errorMsg)){
//
//				$Rimage = new SimpleImage();
//				$Rimage->load('../uploads/teachers/'.$image);
//				if( $Rimage->getWidth() > THUMB_WIDTH ) {
//					$Rimage->resizeToWidth( THUMB_WIDTH );
//				}
//				$Rimage->save('../uploads/teachers/thumb/'.$image);							
//			}						 
//		}
//		if(!empty($errorMsg)){
//			break;
//		}
//
//		if(!$image) {
//			break;
//		}
//		if($image) {
//			$sql[] = " `image`='".sqlencode(trime( $image ))."' ";
//		}
//		
//		$sql = implode(',', $sql);
//		
//		$strSQL = "UPDATE schools SET {$sql} WHERE id='{$Admin['id']}' LIMIT 1";
////var_dump($Admin);
////echo $strSQL;
//		$q = mysql_query($strSQL);
//		if( $q ) {
//			redirect('about-teacher.php?msg=updated');
//			exit;
//		}
//		else{
//			$errorMsg = 'Something went wrong!';
//		}
//	break;
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
<input type="hidden" name="action" value="password"> 
<article class="module width_full">
	<header><h3>Teacher Password: <?php echo $Admin['full_name']; ?>
		<input type="submit" value="Save" class="alt_btn">
		</h3></header>
		<div class="module_content inline">
			<fieldset>
				<label>Current Password</label>
				<div style="margin-left: 210px;">
					<input type="password" name="current_password" />
				</div>
			</fieldset>
			<fieldset>
				<label>New Password</label>
				<div style="margin-left: 210px;">
					<input type="password" name="password" />
				</div>
			</fieldset>
			<fieldset>
				<label>Confirm New Password</label>
				<div style="margin-left: 210px;">
					<input type="password" name="confirm_password" />
				</div>
			</fieldset>
			<div class="clear"></div>
		</div>
	<footer></footer>
</article>
</form>
<?php if( false) { ?>
<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>" method="post" name="myform" enctype="multipart/form-data">
<input type="hidden" name="action" value="image"> 
<article class="module width_full">
	<header><h3>Teacher Photo: <?php echo $Admin['full_name']; ?>
		<input type="submit" value="Save" class="alt_btn">
		</h3></header>
		<div class="module_content inline">
			<fieldset>
				<label>Logo</label>
				<?php file_field('file1','../uploads/schools/',$file1);?>
			</fieldset>
			<div class="clear"></div>
		</div>
	<footer></footer>
</article>
</form>
<?php } ?>
<?php

include '_bottom.php';




