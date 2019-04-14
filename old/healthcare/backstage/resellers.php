<?php
define('THUMB_WIDTH', 482); // 218
define('THUMB_HEIGHT', 317); // 150

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include "_top.php";

define('index_table', 'resellers_index');
define('index_region', true);

$fieldsArray = array(
    'title', 'description', 'status', 'email', 'password',
    'region_id',
    'info_phone', 'info_mobile', 'info_email',
    'contact_first_name', 'contact_last_name', 'contact_phone', 'contact_mobile', 'contact_email',
    'plan_doctors', 'plan_expired',
);
$relatedArray = array(
    'doctors' => " doctors ",
    'news' => " news ",
    'documents' => " documents ",
    'banners' => " banners ",
);


$queryStr = '';
if ($keyword) {
    $queryStr .= "keyword=$keyword&";
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


$_GET['cat_id'] = intval($_GET['cat_id']);
if ($_GET['cat_id'] > 0) {
    $Category = getDataByID('category', $_GET['cat_id']);
    if ($Category) {
        $queryStr .= "cat_id={$Category['id']}&";
    }
}

$Regions = array();
$q = mysql_query("SELECT regions.* FROM `regions` ORDER BY regions.rank DESC");
if ($q && mysql_num_rows($q)) {
    while ($row = mysql_fetch_assoc($q)) {
        $Regions[$row['id']] = $row;
    }
}

if ($Regions[$_GET['region_id']]) {
    $Region = $Regions[$_GET['region_id']];
}


//	WHERE category.status='active' 
$Categories = array();
$q = mysql_query("SELECT category.* FROM `category` ORDER BY category.rank DESC");
if ($q && mysql_num_rows($q)) {
    while ($row = mysql_fetch_assoc($q)) {
        $Categories[$row['id']] = $row;
    }
}

//Define the maximum items while listine
$PageSize = 10;
//Define the maximum items while adding
$max = 1;

require_once('_lister.class.php');
$sortableLister = new lister("_sort_reverse.php?filename=" . $filename . "&table=resellers");

$rank = getfield(getHTTP('rank'), "rank", "resellers");


if ($Category) {
    ?><h4 class="alert_info">Category: <?php echo $Category['title']; ?></h4><?php
}

switch ($action):
    //case "down":
    case "up": //reverse
        $strSQL = "select * from resellers WHERE rank='" . ($rank + 1) . "'";
        $objRS = mysql_query($strSQL);
        $total = mysql_num_rows($objRS);
        if ($total > 0) {
            if ($row = mysql_fetch_object($objRS)) {
                $strSQLord = "update resellers set rank='" . ($rank + 1) . "' WHERE rank='" . $rank . "'";
                mysql_query($strSQLord);
                $strSQLord = "update resellers set rank='" . $rank . "' WHERE id='" . $row->id . "'";
                mysql_query($strSQLord);
            }
        } else {
            $strSQL = "select * from resellers WHERE rank >'" . $rank . "'";
            $objRS = mysql_query($strSQL);
            $total = mysql_num_rows($objRS);
            if ($total > 0) {
                if ($row = mysql_fetch_object($objRS)) {
                    $strSQLord = "update resellers set rank='" . ($rank + 1) . "' where rank='" . $rank . "'";
                    mysql_query($strSQLord);
                    $strSQLord = "update resellers set rank='" . $rank . "' where id='" . $row->id . "'";
                    mysql_query($strSQLord);
                }
            }
        }
        break;
    //case "up":
    case "down": //reverse
        $strSQL = "select * from resellers where rank='" . ($rank - 1) . "'";
        $objRS = mysql_query($strSQL);
        $total = mysql_num_rows($objRS);
        if ($total > 0) {
            if ($row = mysql_fetch_object($objRS)) {
                $strSQLord = "update resellers set rank='" . ($rank - 1) . "' where rank='" . $rank . "'";
                mysql_query($strSQLord);
                $strSQLord = "update resellers set rank='" . $rank . "' where id='" . $row->id . "'";
                mysql_query($strSQLord);
            }
        } else {
            $strSQL = "select * from resellers where rank <'" . $rank . "'";
            $objRS = mysql_query($strSQL);
            $total = mysql_num_rows($objRS);
            if ($total > 0) {
                if ($row = mysql_fetch_object($objRS)) {
                    $strSQLord = "update resellers set rank='" . ($rank - 1) . "' where rank='" . $rank . "'";
                    mysql_query($strSQLord);
                    $strSQLord = "update resellers set rank='" . $rank . "' where id='" . $row->id . "'";
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
        $strSQL = "select * from resellers where id IN(" . implode(',', $ids) . ") order by rank DESC";
        $objRS = mysql_query($strSQL);
        $i = 0;
        while ($row = mysql_fetch_object($objRS)) {

            foreach ($fieldsArray as $field) {
                ${$field}[$i] = $row->$field;
            }

            $index[$i] = array();
            $q = mysql_query("SELECT * FROM `" . index_table . "` WHERE index_id='{$row->id}' ");
            if ($q && mysql_num_rows($q)) {
                while ($indx = mysql_fetch_assoc($q)) {
                    $index[$i][$indx['cat_id']] = 'yes';
                }
            }

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
    case "editexe":
    case "addexe":
        //Get new data from the FORM
        foreach ($fieldsArray as $field) {
            ${$field} = $_POST[$field];
        }

        $index = $_POST['index'];

        //Counter for errors array according to the number of records.
        $j = 0;
        //Array of "Errored" records.
        $oldrecord = array();
        //Start the loop over the records
        for ($i = 0; $i < sizeof($ids); $i++) {
            $file1[$i] = $_POST['file1' . $i];
            //Set the flag to one in order to verify the conditions later
            $flag = 1;

            $sql = array();
            foreach ($fieldsArray as $field) {
                $sql[$field] = " `$field`='" . sqlencode(trime(${$field}[$i])) . "', ";
            }
            $sql['contact_full_name'] = " `contact_full_name`='" . sqlencode(trime($contact_first_name[$i] . ' ' . $contact_last_name[$i])) . "', ";

            if ($action == 'addexe' || $password[$i]) {
                $sql['password'] = " `password`='" . md5($password[$i]) . "', ";
            } else {
                unset($sql['password']);
            }
//			if( $action == 'addexe' && isReseller) {
//				$sql['add_by_id']= " `add_by_id`='".isReseller."', ";
//			}

            $strSQL = "resellers set " . implode('', $sql);

            $Errors = array();
            if (empty($title[$i])) {
                $Errors[] = "Missing Title!!";
            } else if (!$Regions[$region_id[$i]]) {
                $Errors[] = "Missing Region/Country!!";
            } else if (!empty($info_email[$i]) && !isemail($info_email[$i])) {
                $Errors[] = "Invalid Reseller E-mail!!";
            } else if (!isemail($email[$i])) {
                $Errors[] = "Missing or Invalid Reseller's Login E-mail!!";
            } else if ($action == 'addexe' && empty($password[$i])) {
                $Errors[] = "Missing Reseller's Password!!";
            } else if (empty($contact_first_name[$i])) {
                $Errors[] = "Missing Contact's First Name!!";
            } else if (empty($contact_last_name[$i])) {
                $Errors[] = "Missing Contact's Last Name!!";
            } else if (!empty($contact_email[$i]) && !isemail($contact_email[$i])) {
                $Errors[] = "Invalid Contact's E-mail!!";
            }

            if ($title[$i]) {
                $sql = "SELECT * FROM resellers WHERE title = '" . sqlencode(trime($title[$i])) . "' ";
                if ($action == "editexe") {
                    $sql .= " AND id <> '" . intval($ids[$i]) . "' ";
                }
                $sql .= " LIMIT 1";

                $qq = mysql_query($sql);
                if ($qq && mysql_num_rows($qq)) {
                    $Errors[] = "Reseller Title already exists in the system!!";
                }
            }

            include('functions/_index_check.php');

            include('functions/_reseller_region_categories.php');

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
                    if ($result = file_upload('image', 'file1' . $i, '../uploads/resellers/', $errorMsg[$j])) {
                        $file1[$i] = $result['name'];
                        if (empty($errorMsg[$j])) {

                            $Rimage = new SimpleImage();
                            $Rimage->load('../uploads/resellers/' . $file1[$i]);
                            if ($Rimage->getWidth() > THUMB_WIDTH) {
                                $Rimage->resizeToWidth(THUMB_WIDTH);
                            }
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
                            $Rimage->save('../uploads/resellers/thumb/' . $file1[$i]);
                        }
                    }
                    if (!empty($errorMsg[$j])) {// Upload Error
                        $oldrecord[$j] = $i;
                        $j++;
                        $flag = 0;
                    } else {
                        $strSQL = "update " . $strSQL . " image='" . sqlencode(trime($file1[$i])) . "' where id=" . $ids[$i];
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
                    if ($result = file_upload('image', 'file1' . $i, '../uploads/resellers/', $errorMsg[$j])) {
                        $file1[$i] = $result['name'];
                        if (empty($errorMsg[$j])) {

                            $Rimage = new SimpleImage();
                            $Rimage->load('../uploads/resellers/' . $file1[$i]);
                            if ($Rimage->getWidth() > THUMB_WIDTH) {
                                $Rimage->resizeToWidth(THUMB_WIDTH);
                            }
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT );
                            $Rimage->save('../uploads/resellers/thumb/' . $file1[$i]);
                        }
                    }
                    if (!empty($errorMsg[$j])) {// Upload Error
                        $oldrecord[$j] = $i;
                        $j++;
                        $flag = 0;
                    } else {

                        $q = mysql_query("SELECT max(rank) as max FROM resellers");
                        $r = mysql_fetch_object($q);

                        $strSQL = "insert into " . $strSQL . " image='" . sqlencode(trime($file1[$i])) . "', rank='" . ($r->max + 1) . "', time='" . time() . "' ";
                    }
                }
            endif;
            if ($flag) {
                $q = mysql_query($strSQL);
                if ($q) {
                    $index_id = mysql_insert_id();
                    include('functions/_index_create.php');
                } else {
                    if (mysql_errno() == 1062) {
                        $errorMsg[$j] = 'E-mail (login) already exists!';
                    } else {
                        $errorMsg[$j] = 'Something went wronge!';
                    }
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
        $strSQL = "select * from resellers where id IN(" . implode(',', $ids) . ") order by rank DESC";
        $objRS = mysql_query($strSQL);
        while ($row = mysql_fetch_object($objRS)) {
            foreach ($relatedArray as $tpl => $eMsg) {
                $q = mysql_query("SELECT * FROM `$tpl` WHERE reseller_id = '" . $row->id . "' LIMIT 1");
                if ($q && mysql_num_rows($q)) {
                    if (empty($errorMsg)) {
                        $errorMsg = "Some Records didn't affected, You may need to delete/update all {$eMsg} related to the deleted reseller!!";
                    }
                    continue 2;
                }
            }

            if ($row->image != '') {
                @unlink('../uploads/resellers/' . $row->image);
                @unlink('../uploads/resellers/thumb/' . $row->image);
            }

            $strSQL = "DELETE FROM resellers WHERE id = '{$row->id}' LIMIT 1";
            if (!mysql_query($strSQL)) {
                if (empty($errorMsg)) {
                    $errorMsg = "Some Records didn't affected!!";
                }
            } else {
                mysql_query("DELETE  FROM `" . index_table . "` WHERE index_id='{$row->id}' ");
                mysql_query("DELETE  FROM `doctors_resellers` WHERE reseller_id='{$row->id}' ");
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
        ?>

        <form action="<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>" method="post" name="myform" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?php echo $action; ?>exe"> 
            <article class="module width_full">
                <header><h3>Resellers: <?php echo ucwords($action); ?> Record
                        <input type="submit" value="Save" class="alt_btn">
                        <input type="button" value="Cancel" onclick="window.location = '<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>'">
                    </h3></header>

        <?php for ($i = 0; $i < $max; $i++) { ?>
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
                            <label>Title</label>
                            <input type="text" name="title[]" value="<?php echo textencode($title[$oldrecord[$i]]); ?>" />
                        </fieldset>

                        <fieldset>
                            <label>Region/Country</label>
                            <select name="region_id[]">
                                <option value="">-- Select --</option>
            <?php
            $_region_id = ($action == 'add' && !$region_id[$oldrecord[$i]]) ? $Region['id'] : $region_id[$oldrecord[$i]];
            ?>
            <?php foreach ($Regions as $region) { ?>
                                    <option value="<?php echo $region['id']; ?>" <?php echo selected($region['id'], $_region_id); ?>><?php echo $region['title']; ?></option>
            <?php } ?>
                            </select>
                        </fieldset>

                        <fieldset>
                            <label>Reseller's Login</label>
                            <table width="60%" border="0" cellpadding="5" cellspacing="0">
                                <tr>
                                    <td>
                                        <label>E-mail (Login)
                                            <br /><input type="text" name="email[]" value="<?php echo textencode($email[$oldrecord[$i]]); ?>" />
                                        </label>
                                    </td>
                                    <td>
                                        <label>Password
                                            <br /><input type="password" name="password[]"  />
                                <?php if ($action == 'edit') { ?>
                                                <br />Leave blank if you don't wish to change the password
            <?php } ?>
                                        </label>
                                    </td>
                                    <td>
                                        <label></label>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
                        <fieldset>
                            <label>Reseller's Info</label>
                            <table width="60%" border="0" cellpadding="5" cellspacing="0">
                                <tr>
                                    <td>
                                        <label>Phone
                                            <br /><input type="text" name="info_phone[]" value="<?php echo textencode($info_phone[$oldrecord[$i]]); ?>" />
                                        </label>
                                    </td>
                                    <td>
                                        <label>Mobile
                                            <br /><input type="text" name="info_mobile[]" value="<?php echo textencode($info_mobile[$oldrecord[$i]]); ?>" />
                                        </label>
                                    </td>
                                    <td>
                                        <label>Email
                                            <br /><input type="text" name="info_email[]" value="<?php echo textencode($info_email[$oldrecord[$i]]); ?>" />
                                        </label>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
                        <fieldset>
                            <label>Reseller's Plan</label>
                            <table width="60%" border="0" cellpadding="5" cellspacing="0">
                                <tr>
                                    <td>
                                        <label>Doctors
                                            <br /><input type="text" name="plan_doctors[]" value="<?php echo textencode($plan_doctors[$oldrecord[$i]]); ?>" />
                                            <br />[ZERO] = unlimited
                                        </label>
                                    </td>
                                    <td>
                                        <label>Expired Date
                                            <br /><input type="text" class="datepicker" name="plan_expired[]" value="<?php echo textencode($plan_expired[$oldrecord[$i]]); ?>" />
                                            <br />[ZERO] = unlimited
                                        </label>
                                    </td>
                                    <td>
                                        <label></label>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>

                        <fieldset>
                            <label>Contact's Info</label>
                            <table width="60%" border="0" cellpadding="5" cellspacing="0">
                                <tr>
                                    <td>
                                        <label>First Name
                                            <br /><input type="text" name="contact_first_name[]" value="<?php echo textencode($contact_first_name[$oldrecord[$i]]); ?>" />
                                        </label>
                                    </td>
                                    <td>
                                        <label>Last Name
                                            <br /><input type="text" name="contact_last_name[]" value="<?php echo textencode($contact_last_name[$oldrecord[$i]]); ?>" />
                                        </label>
                                    </td>
                                    <td>
                                        <label></label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label>Phone
                                            <br /><input type="text" name="contact_phone[]" value="<?php echo textencode($contact_phone[$oldrecord[$i]]); ?>" />
                                        </label>
                                    </td>
                                    <td>
                                        <label>Mobile
                                            <br /><input type="text" name="contact_mobile[]" value="<?php echo textencode($contact_mobile[$oldrecord[$i]]); ?>" />
                                        </label>
                                    </td>
                                    <td>
                                        <label>E-mail
                                            <br /><input type="text" name="contact_email[]" value="<?php echo textencode($contact_email[$oldrecord[$i]]); ?>" />
                                        </label>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>

                        <fieldset>
                            <label>Categories</label>
            <?php include('functions/_index_table.php');
            ; ?>
                        </fieldset>

                        <fieldset>
                            <label>Notes</label>
                            <textarea name="description[]" rows="12" ><?php echo textencode($description[$oldrecord[$i]]); ?></textarea>
                        </fieldset>
                        <fieldset>
                            <label>Logo (Min&nbsp;Width:&nbsp;<?php echo THUMB_WIDTH; ?>px)</label>
            <?php file_field('file1' . $i, '../uploads/resellers/', $file1[$oldrecord[$i]]); ?>
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


                        <?php if (!empty($warningMsg) && is_array($warningMsg)) { ?>
            <h4 class="alert_warning"><?php echo array_shift($warningMsg); ?></h4>
        <?php } else if (!empty($warningMsg)) { ?>
            <h4 class="alert_warning"><?php echo $warningMsg; ?></h4>
                <?php } else if (!empty($msg)) { ?>
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

                Region/Country: <select name="region_id">
                    <option value="">-- Any --</option>
        <?php foreach ($Regions as $region) { ?>
                        <option value="<?php echo $region['id']; ?>" <?php echo selected($region['id'], $Region['id']); ?>><?php echo $region['title']; ?></option>
        <?php } ?>
                </select>
                Category: <span class="autocomplete" data-link="_get.php?from=categories">
                    <input class="input_short auto_name" type="text" name="cat_name" value="<?php echo $Category['title']; ?>" />
                    <input class="input auto_id" type="hidden" name="cat_id" value="<?php echo $Category['title']; ?>" />
                </span>
                <input type="submit" value="Browse" />
            </form>
        </div>
        <?php
        $sql = array();
        if (!empty($keyword)) {
            $keywordSQL = mysql_real_escape_string($keyword);
            $keywordSQL = str_replace(' ', '% %', $keywordSQL);

            $sql[] = " (resellers.title LIKE '%$keywordSQL%' OR resellers.description LIKE '%$keywordSQL%' ) ";
        }

//		if($Category) {
//			$sql[] = " resellers.cat_id = '{$Category['id']}' ";
//		}
        switch ($_GET['status']) {
            case 'active':
                $sql[] = " resellers.status='active' ";
                break;
            case 'inactive':
//				$sql[] = " resellers.status='' ";
                $sql[] = " resellers.status<>'active' ";
                break;
        }

        if ($Category) {
            $sql[] = " category.id='{$Category['id']}' ";
        }

        if ($Region) {
            $sql[] = " resellers.region_id='{$Region['id']}' ";
        }

        $where = ( $sql ) ? " WHERE " . implode(' AND ', $sql) : '';

        $strSQL = "SELECT resellers.*
		, GROUP_CONCAT( DISTINCT category.title SEPARATOR '<~~>' ) as cat_titles
		, GROUP_CONCAT( DISTINCT resellers_index.cat_id SEPARATOR '<~~>' ) as cat_ids
		FROM resellers
		LEFT JOIN resellers_index ON (resellers.id = resellers_index.index_id)
		LEFT JOIN category ON (category.id = resellers_index.cat_id )

		$where
		GROUP BY resellers.id
		";


        if ($action == 'export') {
            include 'functions/_export.php';

            $excel = new export_excel("Resellers");
            $excel->addField('title', 'Title');
            $excel->addField('description', 'Description');

            $excel->addField('cat_titles', 'Categories', 'style_category_titles', '--row--');

            $excel->addField('contact_full_name', 'Contact\'s name');
            $excel->addField('contact_phone', 'Contact\'s Phone');
            $excel->addField('contact_mobile', 'Contact\'s Mobile');
            $excel->addField('contact_email', 'Contact\'s Email');

            $excel->addField('info_phone', 'Reseller\'s Phone');
            $excel->addField('info_email', 'Reseller\'s Email');
            $excel->addField('info_mobile', 'Reseller\'s Mobile');

            $excel->addField('status', 'Status', 'ucwords');
            $excel->export("$strSQL ORDER BY resellers.rank DESC");
            exit;
        }

        $objRS = mysql_query($strSQL);
        $total = @mysql_num_rows($objRS);
        $strSQL = makePages($strSQL, $PageSize, $p, 'resellers.rank desc');
        $objRS = mysql_query($strSQL);
        $count_all = @mysql_num_rows($objRS);
        ?>


        <form action="<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>&action=delete" method="post" name="del" id="del">
            <article class="module width_full">
                <header><h3 class="tabs_involved">Resellers Manager</h3>
                    <ul class="tabs">
                        <li><a href="#add" onClick="window.location = '<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>&action=add'" >Add New</a></li>
                        <li><a href="#edit" onClick="edit('<?= $menu ?>&<?php echo $queryStr; ?>p=<?= $p ?>')" >Edit</a></li>
                        <li><a href="#delete" onClick="conf()">Delete</a></li>
                    </ul>
                    <ul class="tabs">
                        <li><a href="#Export" onClick="window.location = '<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>&action=export'" >Export List</a></li>
                    </ul>
                </header>
                <div class="tab_container">
                    <table class="tablesorter" cellspacing="0"> 
                        <thead> 
                            <tr> 
                                <th width="1"><input type="checkbox" onClick="checkall()" name="main"></th>
                                <th width="1">Logo</th>
                                <th>Title</th> 
                                <th>Region/Country</th>
                                <th>Info</th>
                                <th>Contact</th>
                                <th>Categories</th>
                                <th width="1">Status</th>
                                <th width="1">Rank</th>
                            </tr> 
                        </thead> 
                        <tbody id="trContainer" class="sortable"> 

        <?php while ($row = mysql_fetch_object($objRS)) { ?>
                                <tr id="tr_<?php echo $row->id; ?>">
                                    <td align="right"><input type="checkbox" name="ids[]" value="<?php echo $row->id; ?>"></td>
                                    <td align="center"><?php echo scale_image("../uploads/resellers/thumb/" . $row->image, 100); ?></td>
                                    <td>
                                        <div><b><?php echo $row->title ?></b></div>
                                        <div><?php if ($row->description != '') {
                echo summarize($row->description, 20);
            } ?></div>
                                        <div>
                                            <a href="<?php echo $filename; ?>?<?php echo $queryStr; ?>action=edit&ids[]=<?php echo $row->id; ?>">Edit Reseller</a>
                                        </div>
                                    </td>
                                    <td>
            <?php echo $Regions[$row->region_id]['title']; ?>
                                    </td>
                                    <td>
                                <?php if ($row->info_phone) { ?>
                                            <div><b>Phone:</b> <?php echo $row->info_phone; ?></div>
            <?php } ?>
            <?php if ($row->info_mobile) { ?>
                                            <div><b>Mobile:</b> <?php echo $row->info_mobile; ?></div>
            <?php } ?>
            <?php if ($row->info_email) { ?>
                                            <div><b>Email:</b> <?php echo $row->info_email; ?></div>
            <?php } ?>
                                    </td>
                                    <td>
                                        <div><b><?php $row->contact_first_name; ?> <?php $row->contact_last_name; ?></b></div>
                                        <?php if ($row->contact_phone) { ?>
                                            <div><b>Phone:</b> <?php echo $row->contact_phone; ?></div>
            <?php } ?>
                                        <?php if ($row->contact_mobile) { ?>
                                            <div><b>Mobile:</b> <?php echo $row->contact_mobile; ?></div>
                                        <?php } ?>
                                        <?php if ($row->contact_email) { ?>
                                            <div><b>Email:</b> <?php echo $row->contact_email; ?></div>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div>
                                        <?php echo style_category_titles($row->cat_titles, $row); ?>
                                        </div>
                                    </td>
                                    <td align="center"><?php echo ( $row->status == 'active') ? 'Active' : 'Inactive'; ?></td>

                                    <td><?php echo_SetOrder($queryStr, $row->id, $p); ?></td>
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
