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
$limitationDoctor = ' true ';
if (isReseller) {
    //$limitation = " AND questions.reseller_id='" . isReseller . "' ";
    //$limitationDoctor = " doctors.id IN (SELECT doctor_id FROM doctors_resellers WHERE reseller_id = '" . isReseller . "' )";
}

$AllowAdd = false;
$AllowImg = false;


$fieldsArray = array(
    'title', 'description', 'status',
    
);

$queryStr = '';
if ($keyword) {
    $queryStr .= "keyword=$keyword&";
}

$_GET['contact'] = trim($_GET['contact']);
if ($_GET['contact']) {
    $queryStr .= "contact={$_GET['contact']}&";
}
switch ($_GET['status']) {
    case 'Approved':
    case 'Disapproved':
        $queryStr .= "status={$_GET['status']}&";
        break;
    default:
        $_GET['status'] == '';
        break;
}

if (!isReseller) {
    $Reseller = getDataByID('resellers', $_GET['reseller_id']);
    if ($Reseller) {
        $queryStr .= "reseller_id={$Reseller['id']}&";
    }
}

$Doctor = getDataByID('doctors', $_GET['doctor_id'], $limitationDoctor);
if ($Doctor) {
    $queryStr .= "user_id={$Doctor['id']}&";
}

//Define the maximum items while listine
$PageSize = 10;
//Define the maximum items while adding
$max = 1;

if ($Reseller) {
    ?><h4 class="alert_info">Reseller: <?php echo $Reseller['title']; ?></h4><?php
}
if ($Doctor) {
    ?><h4 class="alert_info">Doctor: <?php echo $Doctor['full_name']; ?></h4><?php
}

switch ($action):

    case "add":
        if (!$AllowAdd) {
            break;
        }
        $showing = "record";
        break;
    case "edit":
        //Get needed data from the DB
        $strSQL = "select * from expert_consult_messages where id IN(" . implode(',', $ids) . ") $limitation order by id DESC";
     //   $strSQL = "select * from expert_consult_messages where  mention =1 order by id DESC";

        $objRS = mysql_query($strSQL);
        $i = 0;
        while ($row = mysql_fetch_object($objRS)) {
            foreach ($fieldsArray as $field) {
                ${$field}[$i] = $row->$field;
				
            }

            $reseller_id[$i] = $row->reseller_id;
            $doctor_id[$i] = $row->user_id;
            $file1[$i] = $row->image;
            //Set the OLD records Positions's depending on the QUERY result
            $oldrecord[$i] = $i;
			//echo $oldrecord[$i] ." ,,,";
            $ids[$i] = $row->id;
            $i++;
        }
        //Set the maximum items to the maximum number of "to edit" records
        $max = $i;
        $showing = "record";
        break;
    case "addexe":
        if (!$AllowAdd) {
            break;
        }
    case "editexe":
        //Get new data from the FORM
        foreach ($fieldsArray as $field) {
            ${$field} = $_POST[$field];
        }
        $reseller_id = $_POST['reseller_id'];
        $doctor_id = $_POST['doctor_id'];

        //Counter for errors array according to the number of records.
        $j = 0;
        //Array of "Errored" records.
        $oldrecord = array();
        //Start the loop over the records
        for ($i = 0; $i < sizeof($ids); $i++) {
            $file1[$i] = $_POST['file1' . $i];
            //Set the flag to one in order to verify the conditions later
            $flag = 1;

            $Errors = array();

            $sql = array();
            foreach ($fieldsArray as $field) {
                $sql[$field] = " `$field`='" . trime(${$field}[$i]) . "', ";
            }

            if ($action == 'addexe' && isReseller) {
                $sql['add_by_id'] = " `add_by_id`='" . isReseller . "', ";
            }

            if ($action == 'addexe' || true) {
                if (isReseller) {
                   // $sql['reseller_id'] = " `reseller_id`='" . isReseller . "', ";
                } else {
                    $_reseller = getDataByID('resellers', $reseller_id[$i]);

                    if ($_reseller) {
                       // $sql['reseller_id'] = " `reseller_id`='{$_reseller['id']}', ";
                       
                    } 
                }

                $_doctor = getDataByID('doctors', $doctor_id[$i], $limitationDoctor);

                if (!$_doctor) {
                    $Errors[] = "Missing Doctor!!";
                } else {
                    $sql['doctor'] = " `user_id`='{$_doctor['id']}', ";
                }
            }

			//echo implode('', $sql);
            $strSQL = "expert_consult_messages set " . implode('', $sql);

            if (empty($title[$i])) {
                $Errors[] = "Missing Title!!";
            } else if (empty($description[$i])) {
                $Errors[] = "Missing Description!!";
            }

            if ($action == "editexe"):
                //Conditions and Queries while editing
                if ($Errors) {
                    //Set the error message
                    $errorMsg[$j] = array_shift($Errors);
                    //Set the OLD Bad records Positions's depending on the POST result
                    $oldrecord[$j] = $i;
                    $j++;
                    //Set the flag to zero if conditions not verified to avoid executing the query
                    $flag = 0;
                } else {
                    if ($result = file_upload('image', 'file1' . $i, '../uploads/consult/', $errorMsg[$j])) {
                        $file1[$i] = $result['name'];
                        if (empty($errorMsg[$j])) {

                            $Rimage = new SimpleImage();
                            $Rimage->load('../uploads/consult/' . $file1[$i]);
                            if ($Rimage->getWidth() > THUMB_WIDTH) {
                                $Rimage->resizeToWidth(THUMB_WIDTH);
                            }
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
                            $Rimage->save('../uploads/consult/thumb/' . $file1[$i]);
                        }
                    }
                    if (!empty($errorMsg[$j])) {// Upload Error
                        $oldrecord[$j] = $i;
                        $j++;
                        $flag = 0;
                    } else {
					
                        $strSQL = "update " . $strSQL . " image='" . sqlencode(trime($file1[$i])) . "' where id='" . $ids[$i] . "' $limitation ";
					//	echo $strSQL;
					}
                }
            else:
                //Conditions and Queries while adding
                if (empty($title[$i])) {
                    //Do nothing for empty records but Set the flag to zero
                    $flag = 0;
                } else if ($Errors) {
                    //Set the error message
                    $errorMsg[$j] = array_shift($Errors);
                    //Set the OLD Bad records Positions's depending on the POST result
                    $oldrecord[$j] = $i;
                    $j++;
                    //Set the flag to zero if conditions not verified to avoid executing the query
                    $flag = 0;
                } else {
                    if ($result = file_upload('image', 'file1' . $i, '../uploads/consult/', $errorMsg[$j])) {
                        $file1[$i] = $result['name'];
                        if (empty($errorMsg[$j])) {

                            $Rimage = new SimpleImage();
                            $Rimage->load('../uploads/consult/' . $file1[$i]);
                            if ($Rimage->getWidth() > THUMB_WIDTH) {
                                $Rimage->resizeToWidth(THUMB_WIDTH);
                            }
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT );
                            $Rimage->save('../uploads/consult/thumb/' . $file1[$i]);
                        }
                    }
                    if (!empty($errorMsg[$j])) {// Upload Error
                        $oldrecord[$j] = $i;
                        $j++;
                        $flag = 0;
                    } else {

                        $q = mysql_query("SELECT max(rank) as max FROM expert_consult_messages");
                        $r = mysql_fetch_object($q);

                        $strSQL = "insert into " . $strSQL . " 
							image='" . sqlencode(trime($file1[$i])) . "'
							, rank='" . ($r->max + 1) . "'
							, date='" . date('Y-m-d') . "'
							, time='" . time() . "' ";
                    }
                }
            endif;
            if ($flag) {
				
				//echo $strSQL;
                $q = mysql_query($strSQL);
                if ($q) {
                    if ($action == 'addexe') {
                        $_insert_id = mysql_insert_id();
                    } else {
                        $_insert_id = intval($ids[$i]);
                    }
                    $_action = ( $action == 'addexe' ) ? 'add' : 'edit';
                    setUpdatedRow('questions', $_insert_id, $_action);
                } else {
                    $errorMsg[$j] = 'Something went wrong !';
//					$errorMsg[$j] = mysql_error();
                    $oldrecord[$j] = $i;
                    $j++;
                    $flag = 0;
                }
            }
        }
        $action = substr($action, 0, strlen($action) - 3);
        //Test Conditions, if the not verified stay in the add FORM else go back to listing
//		if($j>0){
        if ($errorMsg && array_filter($errorMsg)) {
            //Set the maximum items to the maximum number of "Errored" records
            $max = $j;
            $showing = "record";
        } else
            $msg = "Record(s) " . $action . "ed successfully!!";
        break;
    case "delete":

        $strSQL = "select * from expert_consult_messages where id IN(" . implode(',', $ids) . ") $limitation order by id DESC";
        $objRS = mysql_query($strSQL);
        while ($row = mysql_fetch_object($objRS)) {

            if ($row->image != '') {
                @unlink('../uploads/consult/' . $row->image);
                @unlink('../uploads/consult/thumb/' . $row->image);
            }

            $strSQL = "DELETE FROM expert_consult_messages WHERE id = '" . $row->id . "' LIMIT 1";
            if (!mysql_query($strSQL)) {
                if (empty($errorMsg)) {
                    $errorMsg = "Some Records didn't affected!!";
                }
            } else {
                setUpdatedRow('expert_consult_messages', $row->id, 'delete');
                setUpdatedRow('setUpdatedRowSql', " question_id = '" . $row->id . "' ", 'delete');
                $strSQL = "DELETE FROM expert_consult_messages WHERE question_id = '" . $row->id . "' ";
                mysql_query($strSQL);
            }
        }

        if (empty($errorMsg)) {
            $msg = "Record(s) deleted successfully!!";
        }
        break;
endswitch;
?>



<?php
switch ($showing):
    case "record":

        include 'functions/_auto_complete.php';
        ?>

        <form action="<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>" method="post" name="myform" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?php echo $action; ?>exe"> 
            <article class="module width_full">
                <header><h3>Questions: <?php echo ucwords($action); ?> Record
                        <input type="submit" value="Save" class="alt_btn">
                        <input type="button" value="Cancel" onclick="window.location = '<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>'">
                    </h3></header>

        <?php for ($i = 0; $i < $max; $i++) { ?>
            <?php
            $_question = array();
            if ($action == 'edit') {
                $_question = getDataByID('expert_consult_messages', $ids[$i], " TRUE $limitation $limitation2 ");
            }
            ?>
                    <input type="hidden" name="ids[]" value="<?php echo $ids[$i]; ?>">
                    <?php if (!empty($msg)) { ?>
                        <h4 class="alert_success"><?php echo $msg; ?></h4>
                    <?php } else if (!empty($errorMsg[$i])) { ?>
                        <h4 class="alert_error"><?php echo $errorMsg[$i]; ?></h4>
                    <?php } else if (!empty($warningMsg[$i])) { ?>
                        <h4 class="alert_warning"><?php echo $warningMsg[$i]; ?></h4>
                    <?php } ?>
                    <div class="module_content inline">

                        <fieldset>
                            <label>Status</label>
                            <select name="status[]">
                                <option value="Approved" <?php echo selected($status[$oldrecord[$i]], 'Approved'); ?>> Approved </option>
                                <option value="" <?php echo selected($status[$oldrecord[$i]], '', isset($status[$oldrecord[$i]])); ?>> Disapproved </option>
                            </select>
                        </fieldset>
						
						


                        <fieldset>
                            <label>Details</label>
                            <table width="60%" border="0" cellpadding="5" cellspacing="0">
                                <tr>
            <?php /*if (!isReseller) { ?>
                                        <td>
                                            <label>Reseller
                                                <br />
                <?php
				
				
                if ($action == 'edit') {
                    $reseller = getDataByID('resellers', $_question['reseller_id']);
                } else {
                    $ResellerID = nor($reseller_id[$oldrecord[$i]], $_GET['reseller_id'], true);
                    $reseller = getDataByID('resellers', $ResellerID);
                }
                ?>
                                                <span class="autocomplete" data-link="_get.php?from=resellers">
                                                    <input class="input_short auto_name" type="text" name="reseller_name[]" value="<?php echo trim($reseller['title']); ?>" />
                                                    <input class="input auto_id" type="hidden" name="reseller_id[]" value="<?php echo $reseller['id']; ?>" />
                                                </span>
                                            </label>
                                        </td>
                                            <?php } else{ ?>
                                        <td><label></label></td>
            <?php } */  ?>
			
			
                                    <td>
                                        <label>Doctor
                                            <br />
            <?php
            if ($action == 'edit') {
                $doctor = getDataByID('doctors', $_question['user_id']);
            } else {
                $doctID = nor($doctor_id[$oldrecord[$i]], $_GET['doctor_id'], true);
				//echo $doctID;
                $doctor = getDataByID('doctors', $doctID, $limitationDoctor);
            }
            ?>
			
                                            <span class="" data-link="_get.php?from=doctors">
                                                <input class="input_short auto_name" type="text" name="doctor_name[]" value="<?php echo trim($doctor['full_name']); ?>" readonly />
                                                <input class="input auto_id" type="hidden" name="doctor_id[]" value="<?php echo $doctor['id']; ?>" />
                                            </span>
                                        </label>
                                    </td>
                                    <td><label></label></td>
                                </tr>
                            </table>
                        </fieldset>

                        <fieldset>
                            <label>Title</label>
                            <input type="text" name="title[]" value="<?php echo textencode($title[$oldrecord[$i]]); ?>" />
                        </fieldset>

                       
                        <fieldset>
                            <label>Description</label>
                            <textarea name="description[]" rows="12" ><?php echo ($description[$oldrecord[$i]]); ?></textarea>
                        </fieldset>
						
						
						  <fieldset>
                                <img src ="<?php echo ("../uploads/consult/" . $file1[$oldrecord[$i]]); ?>"></img>
                                            
                            </fieldset>
						
						
						
						
						  <fieldset>
                                <label>Image (Min&nbsp;Width:&nbsp;<?php echo THUMB_WIDTH; ?>px)</label>
                                            <?php file_field('file1' . $i, '../uploads/consult/', $file1[$oldrecord[$i]]); ?>
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


        <?php if (!empty($msg)) { ?>
            <h4 class="alert_success"><?php echo $msg; ?></h4>
        <?php } else if (!empty($errorMsg)) { ?>
            <h4 class="alert_error"><?php echo $errorMsg; ?></h4>
        <?php } ?>


        <div class="alert_browse">
            <form action="<?php echo $filename; ?>" method="GET" >
                Find: <input name="keyword" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES); ?>" size="30" />
                Status: <select name="status">
                    <option value="" >== Any ==</option>
                    <option value="Approved" <?php echo selected($_GET['status'], 'Approved'); ?>>Approved</option>
                    <option value="Disapproved" <?php echo selected($_GET['status'], 'Disapproved'); ?>>Disapproved</option>
                </select>
        <?php if (!isReseller) { ?>
                    Reseller: <span class="autocomplete" data-link="_get.php?from=resellers">
                        <input class="input_short auto_name" type="text" name="reseller_name" value="<?php echo trim($Reseller['title']); ?>" />
                        <input class="input auto_id" type="hidden" name="reseller_id" value="<?php echo $Reseller['id']; ?>" />
                    </span>
        <?php } ?>

                Doctor: <span class="autocomplete" data-link="_get.php?from=doctors">
                    <input class="input_short auto_name" type="text" name="doctor_name" value="<?php echo trim($Doctor['full_name']); ?>" />
                    <input class="input auto_id" type="hidden" name="doctor_id" value="<?php echo $Doctor['id']; ?>" />
                </span>
                <input type="submit" value="Browse" />
            </form>
        </div>
        <?php
        $sql = array();
        if (!empty($keyword)) {
            $keywordSQL = $keyword;
            $keywordSQL = mysql_real_escape_string($keywordSQL);
            $keywordSQL = str_replace(' ', '% %', $keywordSQL);

            $sql[] = " (questions.title LIKE '%$keywordSQL%' OR questions.description LIKE '%$keywordSQL%' ) ";
        }

        if ($_GET['contact']) {

            $contactSQL = $_GET['contact'];
            $contactSQL = mysql_real_escape_string($contactSQL);
            $contactSQL = str_replace(' ', '% ', $contactSQL);

            $sql[] = " (questions.name LIKE '%$contactSQL%' 
				OR questions.phone LIKE '%$contactSQL%' 
				OR questions.address LIKE '%$contactSQL%' 
				OR questions.email LIKE '%$contactSQL%' 
				) ";
        }

        
        if ($Doctor) {
            $sql[] = " questions.doctor_id = '{$Doctor['id']}' ";
        }
         switch ($_GET['status']) {
            case 'Approved':
                $sql[] = " news.status='Approved' ";
                break;
            case 'Disapproved':
//				$sql[] = " news.status='' ";
                $sql[] = " news.status<>'Approved' ";
                break;
        }

        $where = ( $sql ) ? " WHERE " . implode(' AND ', $sql) : '';

        $strSQL = "SELECT questions.*, resellers.title as reseller_title, doctors.full_name as doctor_full_name
		FROM ( questions 
		LEFT JOIN resellers ON (resellers.id=questions.reseller_id) )
		LEFT JOIN doctors ON (doctors.id=questions.doctor_id)
		$where";


         $strSQL = "select expert_consult_messages.* , doctors.full_name as doctor_full_name from 
			expert_consult_messages 
		 	LEFT JOIN doctors ON (doctors.id=expert_consult_messages.user_id)";
		    	
				//where  mention =1 ";

			
        if ($action == 'export') {
            include 'functions/_export.php';

            $excel = new export_excel("Questions");
            $excel->addField('title', 'Title');
            $excel->addField('description', 'Description');
            if (!isReseller) {
                $excel->addField('reseller_title', 'Reseller');
            }
            $excel->addField('doctor_full_name', 'Doctor');

            $excel->addField('name', 'Contact Name');
            $excel->addField('email', 'Contact Email');
            $excel->addField('phone', 'Contact Phone');
            $excel->addField('address', 'Contact Address');

            $excel->addField('date', 'Date');

            $excel->addField('status', 'Status', 'ucwords');
            $excel->export("$strSQL ORDER BY questions.id DESC");
            exit;
        }
        $objRS = mysql_query($strSQL);
        $total = @mysql_num_rows($objRS);
	
        $strSQL = makePages($strSQL, $PageSize, $p, 'expert_consult_messages.id desc');
		
			 
        $objRS = mysql_query($strSQL);
        $count_all = @mysql_num_rows($objRS);
        ?>


        <form action="<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>&action=delete" method="post" name="del" id="del">
            <article class="module width_full">
                <header><h3 class="tabs_involved">Expert Requests Manager</h3>
                    <ul class="tabs">
        <?php if ($AllowAdd) { ?>
                            <li><a href="#add" onClick="window.location = '<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>&action=add'" >Add New</a></li>
        <?php } ?>
                        <li><a href="#edit" onClick="edit('<?= $menu ?>&<?php echo $queryStr; ?>p=<?= $p ?>')" >Edit</a></li>
                        <li><a href="#delete" onClick="conf()">Delete</a></li>
                    </ul>
                   
                </header>
                <div class="tab_container">
                    <table class="tablesorter" cellspacing="0"> 
                        <thead> 
                            <tr> 
                                <th width="1"><input type="checkbox" onClick="checkall()" name="main"></th>
								<th width="1">Image</th>
                                <th>Title</th> 
                                <?php if (!isReseller) { ?>
                                    
                                <?php } ?>
                                <th>Sender</th>
                                 

                                <th width="1">Date</th>
                                <th width="1">Status</th>

                                <th>Replies</th>
                                <?php if ($AllowImg) { ?>
                                    <th width="1">Image</th>
                                <?php } ?>
                            </tr> 
                        </thead> 
                        <tbody id="trContainer"> 

        <?php 
		
		 
		
		while ($row = mysql_fetch_object($objRS)) { ?>
            <?php
			
			
            $replies = array();
            $sql = "SELECT count(*) FROM consult_replies WHERE question_id = '$row->id' ";
            $sql = "SELECT count(*) as count, foo.* FROM (SELECT * FROM `consult_replies` WHERE question_id = '$row->id' ORDER BY `time` DESC) as foo group by foo.question_id ";
            $q = mysql_query($sql);
            if ($q && mysql_num_rows($q)) {
                $replies = mysql_fetch_assoc($q);
            }
            ?>
                                <tr id="tr_<?php echo $row->id; ?>">
                                    <td align="right"><input type="checkbox" name="ids[]" value="<?php echo $row->id; ?>"></td>
									
									     <td align="center"><?php echo scale_image("../uploads/consult/thumb/" . $row->image, 100); ?></td>
									
									<td>
                                        <div><b><?php echo $row->title ?></b></div>
                                        <div><?php if ($row->description != '') {
                        echo summarize($row->description, 20);
                    } ?></div>
                                        <div>
                                            <a href="<?php echo $filename; ?>?<?php echo $queryStr; ?>action=edit&ids[]=<?php echo $row->id; ?>">Edit</a>
                                        </div>
                                    </td>
            <?php if (!isReseller) { ?>
                                        
            <?php } ?>

                                    <td>
                                        <div><b><?php echo $row->doctor_full_name; ?></b></div>
								<div><?php echo ( $row->mention == "1") ? 'Mention Name' : 'Did not mention Name'; ?> </div>
                           																																																																																																			
                                    </td>
                                    
                                    <td>
                                        <div><?php echo $row->created_at; ?></div>
                                    </td>
									
                                    <td align="center"><?php echo ( $row->status == "Approved") ? 'Approved' : 'Not Approved'; ?></td>


                                    <td>
                                        <div><a 
                                                href="consult_replies.php?question_id=<?php echo $row->id; ?>">View&nbsp;Replies&nbsp;(<?php echo intval($replies['count']); ?>)</a>&nbsp;-&nbsp;<a 
                                                href="consult_replies.php?question_id=<?php echo $row->id; ?>&action=add">Add&nbsp;Reply</a>
                                        </div>
            <?php if ($replies) { ?>
                                            <br />
                                            <div><b>-- Last Reply --</b></div>
                                            <div><b>Date: </b><?php echo $replies['date']; ?></div>
                                            <div><b>From: </b><?php echo ($replies['from'] == 'reseller') ? "$row->reseller_title" : "$row->doctor_full_name"; ?></div>
                    <!--			<div><b><?php echo $replies['title'] ?></b></div>-->
                                            <div><?php echo summarize($replies['description'], 20); ?></div>
            <?php } ?>
                                    </td>
                                        <?php if ($AllowImg) { ?>
                                        <td align="center"><?php echo scale_image("../uploads/consult/thumb/" . $row->image, 100); ?></td>
            <?php } ?>
                                </tr>
        <?php } ?>
                        </tbody> 
                    </table>
                </div><!-- end of .tab_container -->
                <footer>
                    <div class="submit_link">
                                <?= dispPages($total, $PageSize, $p, $queryStr) ?>
                    </div>
                </footer>
            </article><!-- end of content manager article -->
        </form>
        <?php
        break;
endswitch;

include '_bottom.php';
