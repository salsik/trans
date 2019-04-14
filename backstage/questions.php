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
    $limitation = " AND questions.reseller_id='" . isReseller . "' ";
    $limitationDoctor = " doctors.id IN (SELECT doctor_id FROM doctors_resellers WHERE reseller_id = '" . isReseller . "' )";
}

$AllowAdd = false;
$AllowImg = false;


$fieldsArray = array(
    'title', 'description', 'status',
    'name', 'email', 'phone', 'address',
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
    case 'active':
    case 'inactive':
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
    $queryStr .= "doctor_id={$Doctor['id']}&";
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
        $strSQL = "select * from questions where id IN(" . implode(',', $ids) . ") $limitation order by id DESC";
        $objRS = mysql_query($strSQL);
        $i = 0;
        while ($row = mysql_fetch_object($objRS)) {
            foreach ($fieldsArray as $field) {
                ${$field}[$i] = $row->$field;
            }

            $reseller_id[$i] = $row->reseller_id;
            $doctor_id[$i] = $row->doctor_id;
            $file1[$i] = $row->image;
            //Set the OLD records Positions's depending on the QUERY result
            $oldrecord[$i] = $i;
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
                    $sql['reseller_id'] = " `reseller_id`='" . isReseller . "', ";
                } else {
                    $_reseller = getDataByID('resellers', $reseller_id[$i]);

                    if (!$_reseller) {
                        $Errors[] = "Missing Reseller!!";
                        //					$sql['reseller_id']= " `reseller_id`='0', ";
                    } else {
                        $sql['reseller_id'] = " `reseller_id`='{$_reseller['id']}', ";
                    }
                }

                $_doctor = getDataByID('doctors', $doctor_id[$i], $limitationDoctor);

                if (!$_doctor) {
                    $Errors[] = "Missing Doctor!!";
                } else {
                    $sql['doctor'] = " `doctor_id`='{$_doctor['id']}', ";
                }
            }

            $strSQL = "questions set " . implode('', $sql);

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
                    if ($result = file_upload('image', 'file1' . $i, '../uploads/questions/', $errorMsg[$j])) {
                        $file1[$i] = $result['name'];
                        if (empty($errorMsg[$j])) {

                            $Rimage = new SimpleImage();
                            $Rimage->load('../uploads/questions/' . $file1[$i]);
                            if ($Rimage->getWidth() > THUMB_WIDTH) {
                                $Rimage->resizeToWidth(THUMB_WIDTH);
                            }
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
                            $Rimage->save('../uploads/questions/thumb/' . $file1[$i]);
                        }
                    }
                    if (!empty($errorMsg[$j])) {// Upload Error
                        $oldrecord[$j] = $i;
                        $j++;
                        $flag = 0;
                    } else {
                        $strSQL = "update " . $strSQL . " image='" . sqlencode(trime($file1[$i])) . "' where id='" . $ids[$i] . "' $limitation ";
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
                    if ($result = file_upload('image', 'file1' . $i, '../uploads/questions/', $errorMsg[$j])) {
                        $file1[$i] = $result['name'];
                        if (empty($errorMsg[$j])) {

                            $Rimage = new SimpleImage();
                            $Rimage->load('../uploads/questions/' . $file1[$i]);
                            if ($Rimage->getWidth() > THUMB_WIDTH) {
                                $Rimage->resizeToWidth(THUMB_WIDTH);
                            }
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT );
                            $Rimage->save('../uploads/questions/thumb/' . $file1[$i]);
                        }
                    }
                    if (!empty($errorMsg[$j])) {// Upload Error
                        $oldrecord[$j] = $i;
                        $j++;
                        $flag = 0;
                    } else {

                        $q = mysql_query("SELECT max(rank) as max FROM questions");
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
                    $errorMsg[$j] = 'Something went wronge!';
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

        $strSQL = "select * from questions where id IN(" . implode(',', $ids) . ") $limitation order by id DESC";
        $objRS = mysql_query($strSQL);
        while ($row = mysql_fetch_object($objRS)) {

            if ($row->image != '') {
                @unlink('../uploads/questions/' . $row->image);
                @unlink('../uploads/questions/thumb/' . $row->image);
            }

            $strSQL = "DELETE FROM questions WHERE id = '" . $row->id . "' LIMIT 1";
            if (!mysql_query($strSQL)) {
                if (empty($errorMsg)) {
                    $errorMsg = "Some Records didn't affected!!";
                }
            } else {
                setUpdatedRow('questions', $row->id, 'delete');
                setUpdatedRow('setUpdatedRowSql', " question_id = '" . $row->id . "' ", 'delete');
                $strSQL = "DELETE FROM questions_replies WHERE question_id = '" . $row->id . "' ";
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
                $_question = getDataByID('questions', $ids[$i], " TRUE $limitation $limitation2 ");
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
                                <option value="active" <?php echo selected($status[$oldrecord[$i]], 'active'); ?>> Active </option>
                                <option value="" <?php echo selected($status[$oldrecord[$i]], '', isset($status[$oldrecord[$i]])); ?>> Inactive </option>
                            </select>
                        </fieldset>

                        <fieldset>
                            <label>Details</label>
                            <table width="60%" border="0" cellpadding="5" cellspacing="0">
                                <tr>
            <?php if (!isReseller) { ?>
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
                                            <?php } else { ?>
                                        <td><label></label></td>
            <?php } ?>
                                    <td>
                                        <label>Doctor
                                            <br />
            <?php
            if ($action == 'edit') {
                $doctor = getDataByID('doctors', $_question['doctor_id']);
            } else {
                $doctID = nor($doctor_id[$oldrecord[$i]], $_GET['doctor_id'], true);
                $doctor = getDataByID('doctors', $doctID, $limitationDoctor);
            }
            ?>
                                            <span class="autocomplete" data-link="_get.php?from=doctors">
                                                <input class="input_short auto_name" type="text" name="doctor_name[]" value="<?php echo trim($doctor['full_name']); ?>" />
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
                            <label>Contact Name</label>
                            <input type="text" name="name[]" value="<?php echo textencode($name[$oldrecord[$i]]); ?>" />
                        </fieldset>
                        <fieldset>
                            <label>Contact Email</label>
                            <input type="text" name="email[]" value="<?php echo textencode($email[$oldrecord[$i]]); ?>" />
                        </fieldset>
                        <fieldset>
                            <label>Contact Phone</label>
                            <input type="text" name="phone[]" value="<?php echo textencode($phone[$oldrecord[$i]]); ?>" />
                        </fieldset>
                        <fieldset>
                            <label>Contact Address</label>
                            <input type="text" name="address[]" value="<?php echo textencode($address[$oldrecord[$i]]); ?>" />
                        </fieldset>

                        <fieldset>
                            <label>Description</label>
                            <textarea name="description[]" rows="12" ><?php echo ($description[$oldrecord[$i]]); ?></textarea>
                        </fieldset>
            <?php if ($AllowImg) { ?>
                            <fieldset>
                                <label>Image</label>
                <?php file_field('file1' . $i, '../uploads/questions/', $file1[$oldrecord[$i]]); ?>
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
                    <option value="active" <?php echo selected($_GET['status'], 'active'); ?>>Active</option>
                    <option value="inactive" <?php echo selected($_GET['status'], 'inactive'); ?>>Inactive</option>
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

        if (isReseller) {
            $sql[] = " questions.reseller_id = '" . isReseller . "' ";
        } else if ($Reseller) {
            $sql[] = " questions.reseller_id = '{$Reseller['id']}' ";
        }
        if ($Doctor) {
            $sql[] = " questions.doctor_id = '{$Doctor['id']}' ";
        }
        switch ($_GET['status']) {
            case 'active':
                $sql[] = " questions.status='active' ";
                break;
            case 'inactive':
//				$sql[] = " questions.status='' ";
                $sql[] = " questions.status<>'active' ";
                break;
        }

        $where = ( $sql ) ? " WHERE " . implode(' AND ', $sql) : '';

        $strSQL = "SELECT questions.*, resellers.title as reseller_title, doctors.full_name as doctor_full_name
		FROM ( questions 
		LEFT JOIN resellers ON (resellers.id=questions.reseller_id) )
		LEFT JOIN doctors ON (doctors.id=questions.doctor_id)
		$where";


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
        $strSQL = makePages($strSQL, $PageSize, $p, 'questions.id desc');
        $objRS = mysql_query($strSQL);
        $count_all = @mysql_num_rows($objRS);
        ?>


        <form action="<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>&action=delete" method="post" name="del" id="del">
            <article class="module width_full">
                <header><h3 class="tabs_involved">Questions Manager</h3>
                    <ul class="tabs">
        <?php if ($AllowAdd) { ?>
                            <li><a href="#add" onClick="window.location = '<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>&action=add'" >Add New</a></li>
        <?php } ?>
                        <li><a href="#edit" onClick="edit('<?= $menu ?>&<?php echo $queryStr; ?>p=<?= $p ?>')" >Edit</a></li>
                        <li><a href="#delete" onClick="conf()">Delete</a></li>
                    </ul>
                    <ul class="tabs">
                      <!--  <li><a href="#Export" onClick="window.location = '<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>&action=export'" >Export List</a></li>  -->
                    </ul>
                </header>
                <div class="tab_container">
                    <table class="tablesorter" cellspacing="0"> 
                        <thead> 
                            <tr> 
                                <th width="1"><input type="checkbox" onClick="checkall()" name="main"></th>
                                <th>Title</th> 
        <?php if (!isReseller) { ?>
                                    <th>Reseller</th> 
        <?php } ?>
                                <th>Doctor</th>
                                <th>Contact</th>

                                <th width="1">Date</th>
                                <th width="1">Status</th>

                                <th></th>
                                <?php if ($AllowImg) { ?>
                                    <th width="1">Image</th>
                                <?php } ?>
                            </tr> 
                        </thead> 
                        <tbody id="trContainer"> 

        <?php while ($row = mysql_fetch_object($objRS)) { ?>
            <?php
            $replies = array();
            $sql = "SELECT count(*) FROM questions_replies WHERE question_id = '$row->id' ";
            $sql = "SELECT count(*) as count, foo.* FROM (SELECT * FROM `questions_replies` WHERE question_id = '$row->id' ORDER BY `time` DESC) as foo group by foo.question_id ";
            $q = mysql_query($sql);
            if ($q && mysql_num_rows($q)) {
                $replies = mysql_fetch_assoc($q);
            }
            ?>
                                <tr id="tr_<?php echo $row->id; ?>">
                                    <td align="right"><input type="checkbox" name="ids[]" value="<?php echo $row->id; ?>"></td>
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
                                        <td>
                                            <div><b><?php echo $row->reseller_title; ?></b></div>
                                        </td>
            <?php } ?>

                                    <td>
                                        <div><b><?php echo $row->doctor_full_name; ?></b></div>
                                    </td>
                                    <td>
                                        <div><b>Name:</b> <?php echo ($row->name) ? $row->name : 'N/A'; ?></div>
                                        <div><b>Email:</b> <?php echo ($row->email) ? $row->email : 'N/A'; ?></div>
                                        <div><b>Phone:</b> <?php echo ($row->phone) ? $row->phone : 'N/A'; ?></div>
                                        <div><b>Address:</b> <?php echo ($row->address) ? $row->address : 'N/A'; ?></div>
                                    </td>
                                    <td>
                                        <div><?php echo $row->date; ?></div>
                                    </td>
                                    <td align="center"><?php echo ( $row->status == 'active') ? 'Active' : 'Inactive'; ?></td>


                                    <td>
                                     <!--   <div><a 
                                                href="questions_replies.php?question_id=<?php echo $row->id; ?>">View&nbsp;Replies&nbsp;(<?php echo intval($replies['count']); ?>)</a>&nbsp;-&nbsp;<a 
                                                href="questions_replies.php?question_id=<?php echo $row->id; ?>&action=add">Add&nbsp;Reply</a>
                                        </div> -->
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
                                        <td align="center"><?php echo scale_image("../uploads/questions/thumb/" . $row->image, 100); ?></td>
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
