<?php
define('THUMB_WIDTH', 482); // 218
define('THUMB_HEIGHT', 317); // 150

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include "_top.php";

$resellerDeletePermanent = true;

$limitation = '';
$limitationDoctors = '';
$limitationDoctorsNews = '';
if (isReseller) {
    //$limitation = " AND id IN (SELECT news_id FROM news_resellers WHERE reseller_id='" . isReseller . "' ) ";
//	$limitation .= " AND prevision_news <> '1' ";

   // $limitationDoctors = " id IN (SELECT doctor_id FROM doctors_resellers WHERE reseller_id='" . isReseller . "' ) ";
   // $limitationDoctorsNews = " AND $limitationDoctors ";
}

define('index_table', 'news_index');
$fieldsArray = array(
    'title', 'description', 'status', 'date', 'link',
    'app_notification', 'publish_date_time',
);
//ALTER TABLE `news` ADD `publish_date_time` DATETIME NOT NULL AFTER `app_notification` ;
$relatedArray = array(
//	'doctors' => " doctors ",
//	'news' => " news ",
//	'documents' => " documents ",
//	'banners' => " banners ",
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

//	WHERE category.status='active' 
$Categories = array();
if (isReseller) {
    $q = mysql_query("SELECT category.* FROM `category` JOIN `resellers_index` ON `resellers_index`.cat_id = `category`.id WHERE `resellers_index`.index_id = '" . isReseller . "' ORDER BY category.rank DESC");
}else{
    $q = mysql_query("SELECT category.* FROM `category` ORDER BY category.rank DESC");
}
if ($q && mysql_num_rows($q)) {
    while ($row = mysql_fetch_assoc($q)) {
        $Categories[$row['id']] = $row;
    }
}

if (!isReseller) {
    $Reseller = getDataByID('resellers', $_GET['reseller_id']);
    if ($Reseller) {
        $queryStr .= "reseller_id={$Reseller['id']}&";
    }

    $Resellers = array();
    if ($action == 'add') {
        $q = mysql_query("SELECT resellers.* FROM `resellers` WHERE resellers.status='active' ORDER BY resellers.title ASC");
    } else {
        $q = mysql_query("SELECT resellers.* FROM `resellers` ORDER BY resellers.title ASC");
    }
    if ($q && mysql_num_rows($q)) {
        while ($row = mysql_fetch_assoc($q)) {
            $Resellers[$row['id']] = $row;
        }
    }
}

//Define the maximum items while listine
$PageSize = 10;
//Define the maximum items while adding
$max = 1;

require_once('_lister.class.php');
$sortableLister = new lister("_sort_reverse.php?filename=" . $filename . "&table=news");

$rank = getfield(getHTTP('rank'), "rank", "news");


if ($Reseller) {
    ?><h4 class="alert_info">Reseller: <?php echo $Reseller['title']; ?></h4><?php
}

if ($Category) {
    ?><h4 class="alert_info">Category: <?php echo $Category['title']; ?></h4><?php
}

switch ($action):
    //case "down":
    case "up": //reverse
        $strSQL = "select * from news WHERE rank='" . ($rank + 1) . "'";
        $objRS = mysql_query($strSQL);
        $total = mysql_num_rows($objRS);
        if ($total > 0) {
            if ($row = mysql_fetch_object($objRS)) {
                $strSQLord = "update news set rank='" . ($rank + 1) . "' WHERE rank='" . $rank . "'";
                mysql_query($strSQLord);
                $strSQLord = "update news set rank='" . $rank . "' WHERE id='" . $row->id . "'";
                mysql_query($strSQLord);
            }
        } else {
            $strSQL = "select * from news WHERE rank >'" . $rank . "'";
            $objRS = mysql_query($strSQL);
            $total = mysql_num_rows($objRS);
            if ($total > 0) {
                if ($row = mysql_fetch_object($objRS)) {
                    $strSQLord = "update news set rank='" . ($rank + 1) . "' where rank='" . $rank . "'";
                    mysql_query($strSQLord);
                    $strSQLord = "update news set rank='" . $rank . "' where id='" . $row->id . "'";
                    mysql_query($strSQLord);
                }
            }
        }
        break;
    //case "up":
    case "down": //reverse
        $strSQL = "select * from news where rank='" . ($rank - 1) . "'";
        $objRS = mysql_query($strSQL);
        $total = mysql_num_rows($objRS);
        if ($total > 0) {
            if ($row = mysql_fetch_object($objRS)) {
                $strSQLord = "update news set rank='" . ($rank - 1) . "' where rank='" . $rank . "'";
                mysql_query($strSQLord);
                $strSQLord = "update news set rank='" . $rank . "' where id='" . $row->id . "'";
                mysql_query($strSQLord);
            }
        } else {
            $strSQL = "select * from news where rank <'" . $rank . "'";
            $objRS = mysql_query($strSQL);
            $total = mysql_num_rows($objRS);
            if ($total > 0) {
                if ($row = mysql_fetch_object($objRS)) {
                    $strSQLord = "update news set rank='" . ($rank - 1) . "' where rank='" . $rank . "'";
                    mysql_query($strSQLord);
                    $strSQLord = "update news set rank='" . $rank . "' where id='" . $row->id . "'";
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
        $strSQL = "select * from news where id IN(" . implode(',', $ids) . ") $limitation order by rank DESC";
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
            $doctors[$i] = array();
            $q = mysql_query("SELECT * FROM `news_doctors` WHERE news_id='{$row->id}' ");
            if ($q && mysql_num_rows($q)) {
                while ($indx = mysql_fetch_assoc($q)) {
                    $doctors[$i][] = $indx['doctor_id'];
                }
            }

            if (!isReseller) {
                $resellers[$i] = array();
                $q = mysql_query("SELECT * FROM `news_resellers` WHERE news_id='{$row->id}' ");
                if ($q && mysql_num_rows($q)) {
                    while ($indx = mysql_fetch_assoc($q)) {
                        $resellers[$i][] = $indx['reseller_id'];
                    }
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
        $resellers = $_POST['resellers'];
        $doctors = $_POST['doctors'];

        //Counter for errors array according to the number of records.
        $j = 0;
        //Array of "Errored" records.
        $oldrecord = array();
        //Start the loop over the records
        for ($i = 0; $i < sizeof($ids); $i++) {

            $insertDoctors = false;

            $file1[$i] = $_POST['file1' . $i];
            $file2[$i] = $_POST['file2' . $i];
            //Set the flag to one in order to verify the conditions later
            $flag = 1;

            // fix publish time
            $am = 'am';
            if (stripos($publish_date_time[$i], 'pm') !== false) {
                $am = 'pm';
            }
            $tmp = explode(' ', $publish_date_time[$i]);

            $_publish_time = $tmp[1];
            $_publish_time = explode(':', $_publish_time);

            $_publish_time[0] = intval($_publish_time[0]);
            $_publish_time[1] = intval($_publish_time[1]);

            if ($am == 'pm') {
                $_publish_time[0] += 12;
            }

            $_publish_time = "{$_publish_time[0]}:{$_publish_time[1]}:00";

            $publish_date_time[$i] = "{$tmp[0]} {$_publish_time}";

            $link[$i] = fixLinkProtocol($link[$i]);

            $sql = array();
            foreach ($fieldsArray as $field) {
                $sql[$field] = " `$field`='" . trime(${$field}[$i]) . "', ";
            }

            if ($action == 'addexe' && isReseller) {
                $sql['add_by_id'] = " `add_by_id`='" . isReseller . "', ";
            }

            if ($action != 'addexe') {
                unset($sql['app_notification']);
            }

            $strSQL = "expert_articles set " . implode('', $sql);

            $Errors = array();

            $_resellers = array();
            if (!isReseller) {
                $_resellers = getDataByIDs('resellers', $resellers[$i]);
            }
            $_doctors = getDataByIDs('doctors', $doctors[$i], $limitationDoctors);


            if (empty($title[$i])) {
                $Errors[] = "Missing Title!!";
            } else if (!isReseller && !$_resellers) {
                $Errors[] = "Missing Resellers!!";
            }

            include('functions/_index_check.php');

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
                    if ($result = file_upload('image', 'file1' . $i, '../uploads/expert_articles/', $errorMsg[$j])) {
                        $file1[$i] = $result['name'];
                        if (empty($errorMsg[$j])) {

                            $Rimage = new SimpleImage();
                            $Rimage->load('../uploads/expert_articles/' . $file1[$i]);
                            if ($Rimage->getWidth() > THUMB_WIDTH) {
                                $Rimage->resizeToWidth(THUMB_WIDTH);
                            }
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
                            $Rimage->save('../uploads/expert_articles/thumb/' . $file1[$i]);
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
                    if ($result = file_upload('image', 'file1' . $i, '../uploads/expert_articles/', $errorMsg[$j])) {
                        $file1[$i] = $result['name'];
                        if (empty($errorMsg[$j])) {

                            $Rimage = new SimpleImage();
                            $Rimage->load('../uploads/expert_articles/' . $file1[$i]);
                            if ($Rimage->getWidth() > THUMB_WIDTH) {
                                $Rimage->resizeToWidth(THUMB_WIDTH);
                            }
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
                            $Rimage->save('../uploads/expert_articles/thumb/' . $file1[$i]);
                        }
                    }
                    if (!empty($errorMsg[$j])) {// Upload Error
                        $oldrecord[$j] = $i;
                        $j++;
                        $flag = 0;
                    } else {
                        $q = mysql_query("SELECT max(rank) as max FROM expert_articles");
                        $r = mysql_fetch_object($q);

                        $strSQL = "insert into " . $strSQL . " image='" . sqlencode(trime($file1[$i])) . "', rank='" . ($r->max + 1) . "', time='" . time() . "' ";
                    }
                }
            endif;
            if ($flag) {
                $q = mysql_query($strSQL);
                if ($q) {
                    if ($action == 'addexe') {
                        $news_id = mysql_insert_id();
                    } else {
                        $news_id = intval($ids[$i]);
                    }
                    $_action = ( $action == 'addexe' ) ? 'add' : 'edit';
                    setUpdatedRow('expert_articles', $news_id, $_action);

                    $index_id = $news_id;
                    include('functions/_index_create.php');


                    $sql = "DELETE FROM `news_doctors` WHERE news_id='{$news_id}' AND doctor_id IN ( SELECT id FROM doctors WHERE true {$limitationDoctorsNews} )  ";
                    if (mysql_query($sql)) {
                        foreach ($_doctors as $doctor) {
                            $sql = "INSERT INTO `news_doctors` SET
								`news_id`='" . sqlencode(trime($news_id)) . "'
								, `doctor_id`='" . sqlencode(trime($doctor['id'])) . "'
								";
                            $qq = mysql_query($sql);
                            if (!$qq) {
                                $warningMsg[-3] = 'Some records faced problems while indexing it\'s doctors!';
                            } else {
                                $insertDoctors = true;
                            }
                        }
                    } else {
                        $warningMsg[-3] = 'Some records faced problems while indexing it\'s doctors!';
//						echo $sql;
//							echo mysql_error();
//							die();
                    }
                    if (isReseller) {
                        if (!$insertDoctors) {
                            if ($action == 'addexe') {
                                $sql = "INSERT INTO `news_resellers` SET
									`news_id`='" . sqlencode(trime($news_id)) . "'
									, `reseller_id`='" . sqlencode(trime(isReseller)) . "'
									";
                                $qq = mysql_query($sql);
                                if (!$qq) {
                                    $warningMsg[-2] = 'Some records faced problems while indexing it!';
                                }
                            }
                        }                   
                        
                    } else {
                        $qqDelete = mysql_query("DELETE FROM `news_resellers` WHERE news_id='{$news_id}' ");
                        if (!$insertDoctors) {
//						if( $qqDelete ) {

                            foreach ($_resellers as $reseller) {
                                $sql = "INSERT INTO `news_resellers` SET
										`news_id`='" . sqlencode(trime($news_id)) . "'
										, `reseller_id`='" . sqlencode(trime($reseller['id'])) . "'
										";
                                $qq = mysql_query($sql);
                                if (!$qq) {
                                    $warningMsg[-2] = 'Some records faced problems while indexing it\'s resellers!';
                                }
                            }
//							} else {
//								$warningMsg[-2] = 'Some records faced problems while indexing it\'s resellers!';
//							}
                        }
                    }

                    //add to new notification table
                    if ( $action == 'addexe' && $_POST['app_notification'][0] == 1 ) {
                        if( count($_doctors) > 0 ){
                            foreach ($_doctors as $doctor) {
                                $sql = "INSERT INTO `notifications` SET
                                            `user_id`='" . sqlencode(trime($doctor['id'])) . "',
                                            `type` = 'expert_articles',
                                            `news_id`='" . sqlencode(trime($news_id)) . "',
                                            `text`='" . sqlencode(trime($_POST['title'][0])) . "'
                                            ";
                                $qq = mysql_query($sql);
                            }
                        }elseif (isReseller){
                            foreach ($_POST['index'][0] as $ckey => $cvalue) {
                                $strSQL = "SELECT * FROM doctors_resellers JOIN doctors_index ON index_id = doctor_id WHERE cat_id = $ckey AND reseller_id = ".isReseller;
                                $q = mysql_query($strSQL);
                                if (mysql_num_rows($q)) {
                                    while ($row = mysql_fetch_assoc($q)) {
                                        $sql = "INSERT INTO `notifications` SET
                                                `user_id`='" . sqlencode(trime($row['doctor_id'])) . "',
                                                `type` = 'expert_articles',
                                                `news_id`='" . sqlencode(trime($news_id)) . "',
                                                `text`='" . $_POST['title'][0] . "'
                                                ";
                                        $qq = mysql_query($sql);
                                    }
                                }
                            }
                        }else{
                            foreach ($_resellers as $resellers) {
                                foreach ($_POST['index'][0] as $ckey => $cvalue) {
                                    $strSQL = "SELECT * FROM doctors_resellers JOIN doctors_index ON index_id = doctor_id WHERE cat_id = $ckey AND reseller_id = ".$resellers['id'];
                                    $q = mysql_query($strSQL);
                                    if (mysql_num_rows($q)) {
                                        while ($row = mysql_fetch_assoc($q)) {
                                            $sql = "INSERT INTO `notifications` SET
                                                    `user_id`='" . sqlencode(trime($row['doctor_id'])) . "',
                                                    `type` = 'expert_articles',
                                                    `news_id`='" . sqlencode(trime($news_id)) . "',
                                                    `text`='" . $_POST['title'][0] . "'
                                                    ";
                                             $qq = mysql_query($sql);
                                        }
                                    }
                                }
                            }
                        }
                        include('push_notifications.php');
                        
                    }
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
        // TODO deleting File by company?
        $strSQL = "select * from expert_articles where id IN(" . implode(',', $ids) . ") $limitation order by rank DESC";
        $objRS = mysql_query($strSQL);
        while ($row = mysql_fetch_object($objRS)) {
            if (isReseller && !$resellerDeletePermanent) {
                $q = mysql_query("DELETE  FROM `news_resellers` WHERE reseller_id = '" . isReseller . "' AND news_id='{$row->id}' ");
                continue;
            }

            if ($row->image != '') {
                @unlink('../uploads/expert_articles/' . $row->image);
                @unlink('../uploads/expert_articles/thumb/' . $row->image);
            }

            $strSQL = "DELETE FROM expert_articles WHERE id = '{$row->id}' LIMIT 1";
            if (!mysql_query($strSQL)) {
                if (empty($errorMsg)) {
                    $errorMsg = "Some Records didn't affected!!";
                }
            } else {
                setUpdatedRow('expert_articles', $row->id, 'delete');
                mysql_query("DELETE  FROM `" . index_table . "` WHERE index_id='{$row->id}' ");
                mysql_query("DELETE  FROM `news_resellers` WHERE news_id='{$row->id}' ");
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
        <script type="text/javascript">
        <!--
            function ac_select_doctor($autocomplete, data) {
                $autocomplete.find('.remove').trigger('click');

                var $i = $autocomplete.data('i');
                var parent = $autocomplete.parents('table:first');
                var list = parent.find('.auto_list');
                if (list.find('.select-doc-' + data[1]).length > 0) {
                    list.find('.select-doc-' + data[1] + ' input[type="checkbox"]').prop('checked', true);
                    return false;
                }

                var html = '<label class="select-doc select-doc-' + data[1] + '" >\
                        <input type="checkbox" name="doctors[' + $i + '][]" value="' + data[1] + '" checked="CHECKED" />\
                        <span class="list_title" > ' + data[0] + '</span>\
                </label>';
                $(html).appendTo(list);
            }
            function ac_doctor($autocomplete, url) {
                return "_get.php?from=doctors&reseller_id=" + $('#reseller_id').val();
            }
        //-->
        </script>
        <style>
                <!--
                .select-doc {
                    width: 165px !important;
                }
                .input_date_time {
                    width: 120px !important;
                }
                -->
            </style>
            <form action="<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>" method="post" name="myform" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $action; ?>exe"> 
                <article class="module width_full">
                    <header><h3>Companies News: <?php echo ucwords($action); ?> Record
                            <input type="submit" value="Save" class="alt_btn">
                            <input type="button" value="Cancel" onclick="window.location = '<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>'">
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
                                <div style="margin-left: 210px;">
                                    <input type="text" name="title[]" value="<? echo textencode($title[$oldrecord[$i]]); ?>" />
                                    <div class="clear"></div>

                                    <label style="width: auto;">
            <?php
            $falgs = '';
            $falgs .= ( $app_notification[$oldrecord[$i]] ) ? ' checked="CHECKED" ' : '';
            $falgs .= ( $action == 'edit' ) ? ' disabled="DISABLED" ' : '';
            ?>

                                        <input type="checkbox" name="app_notification[<?php echo $i; ?>]" value="1" <?php echo $falgs; ?> />
                                        Send this expert_articles in push notification.
                                    </label>
                                    <div class="clear"></div>
                            </fieldset>
            <?php
            // fix publish time
            $publish_date_time[$oldrecord[$i]] = fixDateTime($publish_date_time[$oldrecord[$i]]);
            ?>
                            <fieldset>
                                <label>Publish Date/Time</label>
                                <input class="datetimepicker input_date_time" type="text" name="publish_date_time[]" value="<?php echo textencode($publish_date_time[$oldrecord[$i]]); ?>" />
                            </fieldset>
                            <fieldset>
                                <label>Description</label>
                                <textarea name="description[]" rows="12" ><?php echo textencode($description[$oldrecord[$i]]); ?></textarea>
                            </fieldset>
                            <fieldset>
                                <label>Related Link</label>
                                <input type="text" name="link[]" value="<?php echo textencode($link[$oldrecord[$i]]); ?>" />
                            </fieldset>

                            <?php if (!isReseller) { ?>
                                <fieldset>
                                    <label>Reseller</label>
                                    <select name="resellers[<?php echo $i; ?>][]" multiple="multiple" class="resellers_select" id="reseller_id">
                <?php
                if (!is_array($resellers[$oldrecord[$i]])) {
                    $resellers[$oldrecord[$i]] = array();
                }
                foreach ($Resellers as $reseller) {
                    $Selected = ( in_array($reseller['id'], $resellers[$oldrecord[$i]]) ) ? ' selected="selected" ' : '';
                    ?><option value="<?php echo $reseller['id']; ?>" <?php echo $Selected; ?> ><?php echo $reseller['title']; ?></option><?php
                }
                ?>
                                    </select>
                                </fieldset>
                            <?php } ?>
                            <fieldset>
                                <label>Doctors</label>
                                <table width="60%" border="0" cellpadding="5" cellspacing="0">
                                    <tr>
                                        <td width="33%">
                                            <div><span class="autocomplete" data-link="_get.php?from=doctors" data-select="ac_select_doctor" data-get="ac_doctor" data-i="<?php echo $i; ?>">
                                                    <input class="input_short auto_name" type="text" name="doctor_name" value="" />
                                                    <input class="input auto_id" type="hidden" name="doctor_id" value="" />
                                                </span></div>
                                        </td>
                                        <td class="auto_list">
                                    <?php
                                    if (!is_array($doctors[$oldrecord[$i]])) {
                                        $doctors[$oldrecord[$i]] = array();
                                    }
                                    $docts = getDataByIDs('doctors', $doctors[$oldrecord[$i]], $limitationDoctors);
                                    foreach ($docts as $doct) {
                                        echo <<<EOF
	<label class="select-doc select-doc-{$doct['id']}" >
		<input type="checkbox" name="doctors[{$i}][]" value="{$doct['id']}" checked="CHECKED" />
		<span class="list_title" > {$doct['full_name']}</span>
	</label>
EOF;
                                    }
                                    ?>
                                        </td>
                                    </tr>
                                </table>
                            </fieldset>

                            <fieldset>
                                <label>News Categories</label>
                                            <?php include('functions/_index_table.php');
                                            ; ?>
                            </fieldset>

                            <fieldset>
                                <label>Image (Min&nbsp;Width:&nbsp;<?php echo THUMB_WIDTH; ?>px)</label>
                                            <?php file_field('file1' . $i, '../uploads/expert_articles/', $file1[$oldrecord[$i]]); ?>
                            </fieldset>
                            <div class="clear"></div>
                        </div>
                        <footer></footer>
        <?php } ?>
                </article>
            </form>

                            <?php if (!isReseller) { ?>
                <script type="text/javascript">

                    $(".resellers_select").multiselect({
                        header: "Select Resellers!",
                        noneSelectedText: 'Select Resellers'
                    }).multiselectfilter();
                    //--    >
            	</script>
                    <?php } ?>
                    <?php
                    break;

                default:
                    include 'functions/_auto_complete.php';
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
                        Category: <span class="autocomplete" data-link="_get.php?from=categories">
                            <input class="input_short auto_name" type="text" name="cat_name" value="<?php echo $Category['title']; ?>" />
                            <input class="input auto_id" type="hidden" name="cat_id" value="<?php echo $Category['id']; ?>" />
                        </span>
        <?php if (!isReseller) { ?>
                            Reseller: <span class="autocomplete" data-link="_get.php?from=resellers">
                                <input class="input_short auto_name" type="text" name="reseller_name" value="<?php echo trim($Reseller['title']); ?>" />
                                <input class="input auto_id" type="hidden" name="reseller_id" value="<?php echo $Reseller['id']; ?>" />
                            </span>
        <?php } ?>
                        <input type="submit" value="Browse" />
                    </form>
                </div>
        <?php
        $sql = array();
        if (!empty($keyword)) {
            $keywordSQL = mysql_real_escape_string($keyword);
            $keywordSQL = str_replace(' ', '% %', $keywordSQL);

            $sql[] = " (expert_articles.title LIKE '%$keywordSQL%' OR expert_articles.description LIKE '%$keywordSQL%' ) ";
        }

        if (isReseller) {
            $sql[] = " news_resellers.reseller_id = '" . isReseller . "' OR doctors_resellers.reseller_id = '" . isReseller . "' ";
        } else if ($Reseller) {
            $sql[] = " news_resellers.reseller_id = '{$Reseller['id']}' ";
        }

        switch ($_GET['status']) {
            case 'active':
                $sql[] = " expert_articles.status='active' ";
                break;
            case 'inactive':
//				$sql[] = " expert_articles.status='' ";
                $sql[] = " expert_articles.status<>'active' ";
                break;
        }

        if ($Category) {
            $sql[] = " category.id='{$Category['id']}' ";
        }

        $where = ( $sql ) ? " WHERE " . implode(' AND ', $sql) : '';

        $strSQL = "SELECT expert_articles.*
			, GROUP_CONCAT( DISTINCT doctors.full_name SEPARATOR ',' ) as doctors_full_name
			, GROUP_CONCAT( DISTINCT news_resellers.reseller_id SEPARATOR ',' ) as news_resellers
			, GROUP_CONCAT( DISTINCT category.title SEPARATOR '<~~>' ) as cat_titles
			, GROUP_CONCAT( DISTINCT news_index.cat_id SEPARATOR '<~~>' ) as cat_ids
		FROM ( ( expert_articles
		LEFT JOIN news_index ON (expert_articles.id = news_index.index_id)
		LEFT JOIN category ON (category.id = news_index.cat_id )
		) 
		LEFT JOIN news_resellers ON (expert_articles.id = news_resellers.news_id) )
		LEFT JOIN news_doctors ON (expert_articles.id = news_doctors.news_id)
		LEFT JOIN doctors ON ( doctors.id = news_doctors.doctor_id )
                LEFT JOIN doctors_resellers ON ( doctors_resellers.doctor_id = news_doctors.doctor_id )
		$where
		GROUP BY expert_articles.id
		";
//		doctors.id = doctors_resellers.doctor_id AND
//		LEFT JOIN doctors_resellers ON (doctors_resellers.reseller_id = news_resellers.reseller_id )
//		LEFT JOIN doctors_resellers ON (doctors_resellers.reseller_id = news_resellers.reseller_id )
//		LEFT JOIN doctors ON (doctors.id = doctors_resellers.doctor_id AND news_doctors.doctor_id )
        
        if ($action == 'export') {
            include 'functions/_export.php';

            $excel = new export_excel("Companies News");
            $excel->addField('title', 'Title');
            $excel->addField('description', 'Notes');

            if (!isReseller) {
                $excel->addField('news_resellers', 'Resellers', 'style_news_resellers', $Resellers);
            }
            $excel->addField('doctors_full_name', 'Doctors', 'style_news_doctors');

            $excel->addField('cat_titles', 'Categories', 'style_category_titles', '--row--');

            $excel->addField('status', 'Status', 'ucwords');
            $excel->export("$strSQL ORDER BY expert_articles.rank DESC");
            exit;
        }

        $objRS = mysql_query($strSQL);
//echo "$strSQL " . mysql_error();
        $total = @mysql_num_rows($objRS);
        $strSQL = makePages($strSQL, $PageSize, $p, 'expert_articles.rank desc');
        $objRS = mysql_query($strSQL);
        $count_all = @mysql_num_rows($objRS);
        ?>


                <form action="<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>&action=delete" method="post" name="del" id="del">
                    <article class="module width_full">
                        <header><h3 class="tabs_involved">Companies News Manager</h3>
                            <ul class="tabs">
                                <li><a href="#add" onClick="window.location = '<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>&action=add'" >Add New</a></li>
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
                                        <th width="1">Image</th>
                                        <th>Title</th>
        <?php if (!isReseller) { ?>
                                            <th>Resellers</th>
        <?php } ?>
                                        <th>Doctors</th>
                                        <th width="350">Categories</th>
                                        <th width="1">Status</th>
                                        <th width="1">Rank</th>
                                    </tr> 
                                </thead> 
                                <tbody id="trContainer" class="sortable"> 

        <?php while ($row = mysql_fetch_object($objRS)) { ?>
                                        <tr id="tr_<?php echo $row->id; ?>">
                                            <td align="right"><input type="checkbox" name="ids[]" value="<?php echo $row->id; ?>"></td>
                                            <td align="center"><?php echo scale_image("../uploads/expert_articles/thumb/" . $row->image, 100); ?></td>
                                            <td>
                                                <div><b><?php echo $row->title; ?></b></div>
                                                <div><?php if ($row->description != '') {
                echo summarize($row->description, 20);
            } ?></div>
                                                <div>
                                                    <a href="<?php echo $filename; ?>?<?php echo $queryStr; ?>action=edit&ids[]=<?php echo $row->id; ?>">Edit News</a>
                                                </div>
            <?php if ($row->app_notification) { ?>
                                                    <div><b>Sent in push notification</b></div>
            <?php } ?>
                                            </td>

                                        <?php if (!isReseller) { ?>
                                                <td><?php
                            $_resellers = style_news_resellers($row->news_resellers, $Resellers, 'array');
                            foreach ($_resellers as $k => $v) {
                                ?><div>
                                                            <a href="<?php echo $filename; ?>?<?php echo $queryStr; ?>reseller_id=<?php echo $k; ?>"><?php echo $v; ?></a>
                                                        </div><?php
                            }
                            ?>
                                                </td>
                                                <?php } ?>
                                            <td><?php
                                                $_doctors = style_news_doctors($row->doctors_full_name, true);
                                                echo $_doctors;
                                                ?>
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

function fixDateTime($date_time) {

    // fix publish time
    $tmp = explode(' ', $date_time);

    $_publish_time = $tmp[1];

    $_publish_time = explode(':', $_publish_time);
    if (count($_publish_time) > 2) {
        $am = 'am';
        $_publish_time[0] = intval($_publish_time[0]);
        if ($_publish_time[0] > 12) {
            $_publish_time[0] -= 12;
            $am = 'pm';
        }
        $_publish_time[1] = intval($_publish_time[1]);

        if ($_publish_time[0] < 10) {
            $_publish_time[0] = '0' . $_publish_time[0];
        }
        if ($_publish_time[1] < 10) {
            $_publish_time[1] = '0' . $_publish_time[1];
        }

        $_publish_time = "{$_publish_time[0]}:{$_publish_time[1]} {$am}";
    } else {
        $_publish_time = $tmp[1];
    }

    $date_time = "{$tmp[0]} {$_publish_time}";


    return $date_time;
}
