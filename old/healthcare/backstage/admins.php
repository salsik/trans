<?php
include "_top.php";

//Define the maximum items while listine
$PageSize=15;
//Define the maximum items while adding
$max=$PageSize;

$allowEmployee = false;

switch ($action):
	case "add":
		$showing = "record";
	break;
	case "edit":
		//Get needed data from the DB
		$strSQL="select * from admins where id IN(".implode(',',$ids).") order by id DESC";
		$objRS=mysql_query($strSQL);
		$i=0;
		while ($row=mysql_fetch_object($objRS)){
			$username[$i] = $row->username;
			$full_name[$i] = $row->full_name;
			$is_employee[$i] = $row->is_employee;
			$password[$i]= '';
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
		$username = $_POST['username'];
		$full_name = $_POST['full_name'];
		$password = $_POST['password'];
		$is_employee = $_POST['is_employee'];
		//Counter for errors array according to the number of records.
		$j=0;
		//Array of "Errored" records.
		$oldrecord=array();
		//Start the loop over the records
		for($i=0;$i<sizeof($ids);$i++){
			//Set the flag to one in order to verify the conditions later
			$flag=1;
			
			$password[$i] = trime($password[$i]) ;

			$strSQL="admins set 
				username='".sqlencode(trime($username[$i]))."'
				, full_name='".sqlencode(trime($full_name[$i]))."'
				, is_employee='".sqlencode(trime($is_employee[$i]))."'
			";
			if( !empty( $password[$i]) ){
				$strSQL .="
					, password='".sqlencode(md5( $password[$i] ))."'
				";
			}

			if ($action=="editexe"):
				//Conditions and Queries while editing
				if (empty($username[$i])){
					//Set the error message
					$errorMsg[$j]="Missing username!!";
					//Set the OLD Bad records Positions's depending on the POST result
					$oldrecord[$j]=$i;
					$j++;
					//Set the flag to zero if conditions not verified to avoid executing the query
					$flag=0;
				}else if (empty($full_name[$i])){
					//Set the error message
					$errorMsg[$j]="Missing Full Name!!";
					//Set the OLD Bad records Positions's depending on the POST result
					$oldrecord[$j]=$i;
					$j++;
					//Set the flag to zero if conditions not verified to avoid executing the query
					$flag=0;
				}else{
							$strSQL ="update ".$strSQL." where id='".$ids[$i]."' LIMIT 1";
				}
			else:
				//Conditions and Queries while adding
				if (empty($username[$i])){
					//Do nothing for empty records but Set the flag to zero
					$flag=0;
				}else if (empty($full_name[$i])){
					//Set the error message
					$errorMsg[$j]="Missing Full Name!!";
					//Set the OLD Bad records Positions's depending on the POST result
					$oldrecord[$j]=$i;
					$j++;
					//Set the flag to zero if conditions not verified to avoid executing the query
					$flag=0;
				}
				else if (empty($password[$i])){
					//Set the error message
					$errorMsg[$j]="Missing password!!";
					//Set the OLD Bad records Positions's depending on the POST result
					$oldrecord[$j]=$i;
					$j++;
					//Set the flag to zero if conditions not verified to avoid executing the query
					$flag=0;
				}
				else{
					$strSQL ="insert into ".$strSQL ." ";					
				}
			endif;
			if($flag){
				mysql_query($strSQL);
				if ( mysql_errno() == 1062 ){
					//Set the error message
					$errorMsg[$j]="Username(s) already exists!!";
					//Set the OLD Bad records Positions's depending on the POST result
					$oldrecord[$j]=$i;
					$j++;
					//Set the flag to zero if conditions not verified to avoid executing the query
					$flag=0;
				}
			}
		}
		$action = substr ($action,0,strlen($action)-3);
		//Test Conditions, if the not verified stay in the add FORM else go back to listing
		if($j>0){
			//Set the maximum items to the maximum number of "Errored" records
			$max=$j;
			$showing = "record";
		}
		else
			$msg="Record(s) ".$action."ed successfully!!";
	break;
	case "delete":
		foreach ($ids as $ids_item){	
			$strSQL="delete from admins where id = ".$ids_item;
			if(!mysql_query($strSQL))
				$errorMsg="Some Records didn't affected!!";
		}
		if(empty($errorMsg))
			$msg="Record(s) deleted successfully!!";
	break;

	case "deletenotif":

			$strSQL="TRUNCATE TABLE notifications ";
			if(!mysql_query($strSQL))
				$errorMsg="Cannot reset notifications !!";
		
		if(empty($errorMsg))
			$msg="Notifications have been reset successfully!!";


	break;
endswitch;




switch ($showing):
	case "record":?>
<form action="<? echo $filename; ?>?menu=<?=$menu?>&sub=<?=$sub?>&p=<?=$p?>" method="post" name="myform" enctype="multipart/form-data">
<input type="hidden" name="action" value="<? echo $action; ?>exe"> 
<article class="module width_full">
	<header><h3>Admins: <?php echo ucwords($action); ?> Record
		<input type="submit" value="Save" class="alt_btn">
		<input type="button" value="Cancel" onclick="window.location='<? echo $filename; ?>?menu=<?=$menu?>&sub=<?=$sub?>&p=<?=$p?>'">
		</h3></header>

		<?php for($i=0;$i<$max;$i++){ ?>
		<input type="hidden" name="ids[]" value="<? echo $ids[$i]; ?>">
		<?php if(!empty($errorMsg[$i])) { ?>
			<h4 class="alert_error"><?php echo $errorMsg[$i]; ?></h4>
		<?php } ?>
		<div class="module_content inline">

				<fieldset>
					<label>Username</label>
					<input type="text" name="username[]" value="<? echo textencode($username[$oldrecord[$i]]); ?>" />
				</fieldset>
				<fieldset>
					<label>Full Name</label>
					<input type="text" name="full_name[]" value="<? echo textencode($full_name[$oldrecord[$i]]); ?>" />
				</fieldset>
				<fieldset>
					<label>Password</label>
					<input type="password" name="password[]"  />
				</fieldset>
<?php if($allowEmployee) { ?>
				<fieldset>
					<label>Role</label>
					<select name="is_employee[]">
						<option value="">Administrator</option>
						<option value="2" <?php echo selected($is_employee[$oldrecord[$i]], '2'); ?>>Manager</option>
						<option value="1" <?php echo selected($is_employee[$oldrecord[$i]], '1'); ?>>Employee</option>
					</select>
				</fieldset>
<?php } ?>
				<div class="clear"></div>
		</div>
	<footer></footer>
			<?php } ?>
</article>
</form>
<?php 
	break;
	default:
		if(!empty($keyword)){
			$keywordSQL = mysql_real_escape_string($keyword);
			$keywordSQL = str_replace(' ', '% %', $keywordSQL);
			
			$strSQL="select * from admins where (username like '%$keywordSQL%' OR full_name like '%$keywordSQL%') order by username ASC";
		}
		else{
			$strSQL="select * from admins order by username ASC";
			$objRS=mysql_query($strSQL);
			$total=mysql_num_rows($objRS);
			$strSQL=makePages ($strSQL,$PageSize,$p);
		}
		$objRS=mysql_query($strSQL);
		$count_all = mysql_num_rows($objRS);
		?>

<?php if(!empty($msg)) { ?>
	<h4 class="alert_success"><?php echo $msg; ?></h4>
<?php } else if(!empty($errorMsg)) { ?>
	<h4 class="alert_error"><?php echo $errorMsg; ?></h4>
<?php } ?>
<form action="<? echo $filename; ?>?menu=<?=$menu?>&sub=<?=$sub?>&p=<?=$p?>&action=delete" method="post" name="del" id="del">
<article class="module width_full">
	<header><h3 class="tabs_involved">Admins Manager</h3>
	<ul class="tabs">
   		<li><a href="#add" onClick="window.location='<? echo $filename; ?>?menu=<?=$menu?>&sub=<?=$sub?>&p=<?=$p?>&action=add'" >Add New</a></li>
   		<li><a href="#edit" onClick="edit('<?=$menu?>&sub=<?=$sub?>&p=<?=$p?>')" >Edit</a></li>
    	<li><a href="#delete" onClick="conf()">Delete</a></li>
    	<li><a href="#deleteNotif" onClick="conf_notif()">Delete All Notifications</a></li>
	</ul>
	</header>
	<div class="tab_container">
		<table class="tablesorter" cellspacing="0"> 
		<thead> 
			<tr> 
   				<th width="1"><input type="checkbox" onClick="checkall()" name="main"></th>
    			<th>Username</th> 
    			<th>Full Name</th> 
<?php if( $allowEmployee ) { ?>
    			<th>Role</th> 
<?php } ?>
			</tr> 
		</thead> 
		<tbody id="trContainer"> 

<?php while ($row=mysql_fetch_object($objRS)){ ?>

                    <tr id="tr_<? echo $row->id; ?>">
                    <td align="right"><input type="checkbox" name="ids[]" value="<? echo $row->id; ?>"></td>
                    <td><a href="<? echo $filename; ?>?menu=<?=$menu?>&sub=<?=$sub?>&action=edit&ids[]=<? echo $row->id; ?>"><? echo $row->username ?></a></td>
                    <td><?php echo $row->full_name ?></td>
<?php if($allowEmployee) { ?>
                    <td><?php echo ($row->is_employee == '2') ? 'Manager' : ( ($row->is_employee) ? 'Employee' : 'Administrator'); ?></td>
<?php } ?>
                  </tr>

<?php }?>
			</tbody> 
			</table>
		</div><!-- end of .tab_container -->
		<footer>
			<div class="submit_link">
				<?=dispPages ($total,$PageSize,$p,'menu='.$menu.'&sub='.$sub)?>
			</div>
		</footer>
	</article><!-- end of content manager article -->
</form>
<?php
	break;
endswitch; 



		
		
include '_bottom.php';

