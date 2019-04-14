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
	$limitation = " AND school_id ='".isSchool."' ";
	die();
}
else if( !isAdmin ) {
	die();
}

if( !isAdmin ) {
	$action = '';
}


$enableStatus = true;

$queryStr = '';

include 'functions/_index_common_filters.php';


//Define the maximum items while listine
$PageSize=10;
//Define the maximum items while adding
$max=10;

if($School) {
	?><h4 class="alert_info">School: <?php echo $School['title']; ?></h4><?php 
}

if($Class) {
	?><h4 class="alert_info">Class: <?php echo $Class['title']; ?></h4><?php 
}

switch ($action):

	case "add":
		$showing = "record";
	break;
	case "edit":
		//Get needed data from the DB
		$strSQL="select * from users where id IN(".implode(',',$ids).") {$limitation} order by rank DESC";
		$objRS=mysql_query($strSQL);
		$i=0;
		while ($row=mysql_fetch_object($objRS)){
			$title[$i] = $row->title;
			$description[$i] = $row->description;
			$status[$i] = $row->status;
			
			$mobile[$i] = $row->mobile;
			$phone[$i] = $row->phone;
			$email[$i] = $row->email;
			$address[$i] = $row->address;
			
			$username[$i] = $row->username;

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
		$title = $_POST['title'];
		$description = $_POST['description'];
		$status = $_POST['status'];

		$mobile = $_POST['mobile'];
		$phone = $_POST['phone'];
		$email = $_POST['email'];
		$address = $_POST['address'];
		$username = $_POST['username'];
		$password = $_POST['password'];
		$confirm_password = $_POST['confirm_password'];

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



		//$password = $_POST['password'];
					
			$strSQL="users set 
				title='".sqlencode(trime($title[$i]))."',
				description='".sqlencode(trime($description[$i]))."',
				status='".sqlencode(trime($status[$i]))."',
				
				mobile='".sqlencode(trime($mobile[$i]))."',
				phone='".sqlencode(trime($phone[$i]))."',
				email='".sqlencode(trime($email[$i]))."',
				address='".sqlencode(trime($address[$i]))."',
				username='".sqlencode(trime($username[$i]))."',
			";

			if( $action == 'addexe' && isSchool) {
				$strSQL .= " `add_by_id`='".isSchool."', ";
			}
		
			if( $action == 'addexe' || $password[$i]) {
				$strSQL .= " `password`='".md5($password[$i])."', ";
				$strSQL .= " `password_str`='".sqlencode(trime($password[$i]))."', ";
				if($confirm_password[$i] != $password[$i]) {
					$Errors[] = "Missmatch passwords";
				}
			}
			
			if( empty($title[$i]) ) {
				$Errors[] = "Missing Name!!";
			} 
			else if( !is_numeric( $username[$i] )) {
				$Errors[] = "Invalid username. Username should be numeric!!";
			} 
			else if( $action == 'addexe' && !$password[$i]) {
				$Errors[] = "Missing Password!!";
			}
			else if( !empty($email[$i]) && !isemail($email[$i]) ) {
				$Errors[] = "Invalid E-mail!!";
			}
			else if( !empty($mobile[$i]) && !is_numeric($mobile[$i]) ) {
				$Errors[] = "Invalid Mobile Number!!";
			}
			else if( !empty($phone[$i]) && !is_numeric($phone[$i]) ) {
				$Errors[] = "Invalid Phone Number!!";
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/users/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){

							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/users/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
							$Rimage->save('../uploads/users/thumb/'.$file1[$i]);							
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/users/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){
							
							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/users/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT );
							$Rimage->save('../uploads/users/thumb/'.$file1[$i]);							
						}							 
				    }
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						$q=mysql_query("SELECT max(rank) as max FROM users");
						$r = mysql_fetch_object($q);

						$strSQL ="insert into ".$strSQL." image='".sqlencode(trime($file1[$i]))."', rank='".($r->max+1)."', time='".time()."' ";
					}
				}
			endif;
			if($flag){
				$q = mysql_query($strSQL);
				if( !$q )
				{
					if( mysql_errno() == 1062) {
						$errorMsg[$j] = 'Username already exists!';
					}
					else {
						$errorMsg[$j] = 'Something went wrong!';
						$errorMsg[$j] = mysql_error();
					}
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

		$strSQL="select * from users where id IN(".implode(',',$ids).") $limitation order by rank DESC";
		$objRS=mysql_query($strSQL);
//	echo "$strSQL " . mysql_error();
		if($objRS) {
			while ($row=mysql_fetch_object($objRS))
			{
				if($row->image != '') {
					@unlink('../uploads/users/'.$row->image);	
					@unlink('../uploads/users/thumb/'.$row->image);	
				}
	
				$strSQL="DELETE FROM users WHERE id = '".$row->id ."' LIMIT 1";
				if(!mysql_query($strSQL) ) {
					if( empty($errorMsg) ) {
						$errorMsg="Some Records didn't affected!!";
					}
				}
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
	<header><h3>Users: <?php echo ucwords($action); ?> Record
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

<?php if( $enableStatus ) { ?>
				<fieldset>
					<label>Status</label>
					<select name="status[]">
							<option value="active" <?php echo selected($status[$oldrecord[$i]], 'active'); ?>> Active </option>
							<option value="" <?php echo selected($status[$oldrecord[$i]], '', isset($status[$oldrecord[$i]])); ?>> Inactive </option>
					</select>
				</fieldset>
<?php } ?>

				<fieldset>
					<label>Full name</label>
					<input type="text" name="title[]" value="<? echo textencode($title[$oldrecord[$i]]); ?>" />
				</fieldset>


				<fieldset>
					<label>User's Login</label>
					<table width="60%" border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td>
								<label>Username (Numeric)
									<br /><input type="text" name="username[]" value="<? echo textencode($username[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label>Password
									<br /><input type="password" name="password[]"  />
								<?php if($action=='edit'){ ?>
									<br />Leave blank if you don't wish to change the password
								<?php } ?>
								</label>
							</td>
							<td>
								<label>Confirm Password
									<br /><input type="password" name="confirm_password[]"  />
								</label>
							</td> 
						</tr>
					</table>
				</fieldset>


				<fieldset>
					<label>User's Info</label>
					<table width="60%" border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td>
								<label>Mobile
									<br /><input type="text" name="mobile[]" value="<? echo textencode($mobile[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label>Phone
									<br /><input type="text" name="phone[]" value="<? echo textencode($phone[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label>Email
									<br /><input type="text" name="email[]" value="<? echo textencode($email[$oldrecord[$i]]); ?>" />
								</label>
							</td>
						</tr>
						<tr>
							<td>
								<label>Address
									<br /><input type="text" name="address[]" value="<? echo textencode($address[$oldrecord[$i]]); ?>" />
								</label>
							</td>
							<td>
								<label></label>
							</td>
							<td>
								<label></label>
							</td>
						</tr>
					</table>
					<div class="clear"></div>
				</fieldset>

				<fieldset>
					<label>Details</label>
					<textarea name="description[]" rows="12" ><? echo textencode($description[$oldrecord[$i]]); ?></textarea>
				</fieldset>

				<fieldset>
					<label>Image</label>
					<?php file_field('file1'.$i,'../uploads/users/',$file1[$oldrecord[$i]]);?>
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

//		include 'functions/_auto_complete.php';
?>


<?php if(!empty($msg)) { ?>
	<h4 class="alert_success"><?php echo $msg; ?></h4>
<?php } else if(!empty($errorMsg)) { ?>
	<h4 class="alert_error"><?php echo $errorMsg; ?></h4>
<?php } ?>


<div class="alert_browse">
	<form action="<? echo $filename; ?>" method="GET" >
		Find: <input name="keyword" value="<? echo my_htmlspecialchars($keyword, ENT_QUOTES); ?>" size="30" />
<?php if( $enableStatus ) { ?>
		Status: <select name="status">
			<option value="" >== Any ==</option>
			<option value="active" <?php echo selected($_GET['status'], 'active'); ?>>Active</option>
			<option value="inactive" <?php echo selected($_GET['status'], 'inactive'); ?>>Inactive</option>
		</select>
<?php } ?>
		

	<?php 
	//	include 'functions/_index_filter.php';
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
			
			$sql[] = " (users.title LIKE '%$keywordSQL%' 
				OR users.description LIKE '%$keywordSQL%' 
				OR users.username LIKE '%$keywordSQL%' 
				OR users.mobile LIKE '%$keywordSQL%' 
				OR users.phone LIKE '%$keywordSQL%' 
				OR users.email LIKE '%$keywordSQL%' 
				) ";
		}
	if( $enableStatus ) {
		switch($_GET['status']) {
			case 'active':
				$sql[] = " users.status='active' ";
				break;
			case 'inactive':
//				$sql[] = " users.status='' ";
				$sql[] = " users.status<>'active' ";
				break;
		}
	}

//		if(isSchool) {
//			$sql[] = " users.school_id = '". isSchool ."' ";
//		} 
//		else if($School) {
//			$sql[] = " users.school_id = '{$School['id']}' ";
//		}


		$where = ( $sql ) ? " WHERE " . implode(' AND ', $sql ) : '';

		$strSQL="SELECT users.* FROM users $where";

		
		if( $action == 'export' ) {
			include 'functions/_export.php';
			
			$excel = new export_excel("Users");
			$excel->addField('title', 'Full Name');
			
			
			$excel->addField('mobile', 'Mobile');
			$excel->addField('phone', 'Phone');
			$excel->addField('email', 'E-mail');
			$excel->addField('address', 'Address');
			
			$excel->addField('username', 'Username');
			$excel->addField('description', 'Details');

			if( $enableStatus ) {
				$excel->addField('status', 'Status', 'ucwords');
			}

			$excel->export( "$strSQL ORDER BY users.rank DESC");
			exit;
		}
		$objRS=mysql_query($strSQL);
		$total=@mysql_num_rows($objRS);
		$strSQL=makePages ($strSQL,$PageSize,$p, 'users.rank desc');
		$objRS=mysql_query($strSQL);
		$count_all = @mysql_num_rows($objRS);
		
?>


<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=delete" method="post" name="del" id="del">
<article class="module width_full">
	<header><h3 class="tabs_involved">Users Manager</h3>
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
    			<th>Full Name</th>

    			<th>Username</th>
    			<th>Contact</th>
    			
		<?php if( $enableStatus ) { ?>
                <th width="1">Status</th>
		<?php } ?>
                <th width="1">Image</th>
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
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>action=edit&ids[]=<? echo $row->id; ?>">Edit user</a>
			</div>
		</td>

		<td>
			<div><b><? echo $row->username; ?></b></div>
		</td>

		<td>
		<?php if( $row->mobile ) { ?>
			<div><b>Mobile:</b> <? echo $row->mobile; ?></div>
		<?php } ?>
		<?php if( $row->phone ) { ?>
			<div><b>Phone:</b> <? echo $row->phone; ?></div>
		<?php } ?>
		<?php if( $row->email ) { ?>
			<div><b>Email:</b> <? echo $row->email; ?></div>
		<?php } ?>
		</td>

<?php if( $enableStatus ) { ?>
		<td align="center"><?php echo ( $row->status == 'active') ? 'Active' : 'Inactive'; ?></td>
<?php } ?>
		<td align="center"><?php echo scale_image("../uploads/users/thumb/". $row->image, 100); ?></td>

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
