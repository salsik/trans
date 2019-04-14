<?php
define('THUMB_WIDTH', 800); // 218
define('THUMB_HEIGHT', 90); // 150

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

define('index_table', 'banners_index');

include "_top.php";

$limitation = '';
if (isReseller) {
    //$limitation = " AND banners.reseller_id='" . isReseller . "' ";
}

$fieldsArray = array(
    'title', 'description', 'link', 'status',
    'plan_clicks', 'plan_impressions', 'plan_end_date', 'plan_zone',
//	'clicks', 'impressions', 
    'date',
);

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

if (!$_BannersZones[$_GET['zone']]) {
    $_GET['zone'] = '';
}
$queryStr .= "zone={$_GET['zone']}&";

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
$sortableLister = new lister("_sort_reverse.php?filename=" . $filename . "&table=banners");

$rank = getfield(getHTTP('rank'), "rank", "banners");

if ($Reseller) {
    ?><h4 class="alert_info">Reseller: <?php echo $Reseller['title']; ?></h4><?php
}

switch ($action):
    //case "down":
    case "up": //reverse
        $strSQL = "select * from banners WHERE rank='" . ($rank + 1) . "'";
        $objRS = mysql_query($strSQL);
        $total = mysql_num_rows($objRS);
        if ($total > 0) {
            if ($row = mysql_fetch_object($objRS)) {
                $strSQLord = "update banners set rank='" . ($rank + 1) . "' WHERE rank='" . $rank . "'";
                mysql_query($strSQLord);
                $strSQLord = "update banners set rank='" . $rank . "' WHERE id='" . $row->id . "'";
                mysql_query($strSQLord);
            }
        } else {
            $strSQL = "select * from banners WHERE rank >'" . $rank . "'";
            $objRS = mysql_query($strSQL);
            $total = mysql_num_rows($objRS);
            if ($total > 0) {
                if ($row = mysql_fetch_object($objRS)) {
                    $strSQLord = "update banners set rank='" . ($rank + 1) . "' where rank='" . $rank . "'";
                    mysql_query($strSQLord);
                    $strSQLord = "update banners set rank='" . $rank . "' where id='" . $row->id . "'";
                    mysql_query($strSQLord);
                }
            }
        }
        break;
    //case "up":
    case "down": //reverse
        $strSQL = "select * from banners where rank='" . ($rank - 1) . "'";
        $objRS = mysql_query($strSQL);
        $total = mysql_num_rows($objRS);
        if ($total > 0) {
            if ($row = mysql_fetch_object($objRS)) {
                $strSQLord = "update banners set rank='" . ($rank - 1) . "' where rank='" . $rank . "'";
                mysql_query($strSQLord);
                $strSQLord = "update banners set rank='" . $rank . "' where id='" . $row->id . "'";
                mysql_query($strSQLord);
            }
        } else {
            $strSQL = "select * from banners where rank <'" . $rank . "'";
            $objRS = mysql_query($strSQL);
            $total = mysql_num_rows($objRS);
            if ($total > 0) {
                if ($row = mysql_fetch_object($objRS)) {
                    $strSQLord = "update banners set rank='" . ($rank - 1) . "' where rank='" . $rank . "'";
                    mysql_query($strSQLord);
                    $strSQLord = "update banners set rank='" . $rank . "' where id='" . $row->id . "'";
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
        $strSQL = "select * from banners where id IN(" . implode(',', $ids) . ") $limitation order by rank DESC";
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

            if (isReseller) {
                $resellers[$i] = array();
                $q = mysql_query("SELECT * FROM `banners_resellers` WHERE banner_id='{$row->id}' ");
                if ($q && mysql_num_rows($q)) {
                    while ($indx = mysql_fetch_assoc($q)) {
                        $resellers[$i][] = $indx['reseller_id'];
                    }
                }
            }
//var_dump($resellers);
//			$reseller_id[$i] = $row->reseller_id;
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

            $link[$i] = fixLinkProtocol($link[$i]);

            $sql = array();
            foreach ($fieldsArray as $field) {
                $sql[$field] = " `$field`='" . sqlencode(trime(${$field}[$i])) . "', ";
            }

            if ($action == 'addexe' && isReseller) {
                $sql['add_by_id'] = " `add_by_id`='" . isReseller . "', ";
            }

            if ($action == 'addexe' && isReseller) {
                $sql['reseller_id'] = " `reseller_id`='" . isReseller . "', ";
            }
//			if( !isReseller ) {
//				$_reseller = getDataByID('resellers', $reseller_id[$i]);
//
//				if( !$_reseller) {
////					$Errors[] = "Missing Reseller!!";
//					$sql['reseller_id']= " `reseller_id`='0', ";
//				} else {
//					$sql['reseller_id']= " `reseller_id`='{$_reseller['id']}', ";
//				}
//			}

            $_resellers = array();
            if (!isReseller) {
                $_resellers = getDataByIDs('resellers', $resellers[$i]);
            }

            $strSQL = "banners set " . implode('', $sql);

            $bannerZone = $_BannersZones[$plan_zone[$i]];
//var_dump($bannerZone);
            if (empty($title[$i])) {
                $Errors[] = "Missing Title!!";
            }
//			else if( empty($link[$i]) ) {
//				$Errors[] = "Missing Link!!";
//			} 
            else if (!$bannerZone || ( isReseller && !$bannerZone['reseller'] )) {
                $Errors[] = "Missing Ad Zone!!";
            } else if ((!isReseller && !$_resellers) && $bannerZone['private']) {
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
                    if ($result = file_upload('image', 'file1' . $i, '../uploads/banners/', $errorMsg[$j])) {
                        $file1[$i] = $result['name'];
                        if (empty($errorMsg[$j])) {

                            $Rimage = new SimpleImage();
                            $Rimage->load('../uploads/banners/' . $file1[$i]);
                            if ($bannerZone['resize'] == 'width' && $Rimage->getWidth() > $bannerZone['width']) {
                                $Rimage->resizeToWidth($bannerZone['width']);
                            } else if ($bannerZone['resize'] == 'height' && $Rimage->getHeight() > $bannerZone['height']) {
                                $Rimage->resizeToHeight($bannerZone['height']);
                            } else {
                                $Rimage->resize($bannerZone['width'], $bannerZone['height']);
                            }

//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
                            $Rimage->save('../uploads/banners/thumb/' . $file1[$i]);
                        }
                    }
                    if (!empty($errorMsg[$j])) {// Upload Error
                        $oldrecord[$j] = $i;
                        $j++;
                        $flag = 0;
                    } else if (empty($file1[$i])) {// No File Uploaded
                        $errorMsg[$j] = "No File uploaded!!";
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
                    if ($result = file_upload('image', 'file1' . $i, '../uploads/banners/', $errorMsg[$j])) {
                        $file1[$i] = $result['name'];
                        if (empty($errorMsg[$j])) {
                            
                            $Rimage = new SimpleImage();
                            $Rimage->load('../uploads/banners/' . $file1[$i]);
                            if ($bannerZone['resize'] == 'width' && $Rimage->getWidth() > $bannerZone['width']) {
                                $Rimage->resizeToWidth($bannerZone['width']);
                            } else if ($bannerZone['resize'] == 'height' && $Rimage->getHeight() > $bannerZone['height']) {
                                $Rimage->resizeToHeight($bannerZone['height']);
                            } else {
                                $Rimage->resize($bannerZone['width'], $bannerZone['height']);
                            }
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT );
                            $Rimage->save('../uploads/banners/thumb/' . $file1[$i]);
                        }
                    } 
                    if (!empty($errorMsg[$j])) {// Upload Error
                        $oldrecord[$j] = $i;  
                        $j++;  
                        $flag = 0;
                    } else if (empty($file1[$i])) {// No File Uploaded
                        $errorMsg[$j] = "No File uploaded!!";
                        $oldrecord[$j] = $i; 
                        $j++;  
                        $flag = 0;
                    } else {                        
                        $q = mysql_query("SELECT max(rank) as max FROM banners");
                        $r = mysql_fetch_object($q);

                        $strSQL = "insert into " . $strSQL . " image='" . sqlencode(trime($file1[$i])) . "', rank='" . ($r->max + 1) . "', time='" . time() . "' ";
                    }
                }
            endif;
            if ($flag) {
                $q = mysql_query($strSQL);
                if ($q) {
                    if ($action == 'addexe') {
                        $banner_id = mysql_insert_id();
                    } else {
                        $banner_id = intval($ids[$i]);
                    }

                    $index_id = $banner_id;
                    include('functions/_index_create.php');

                    if (isReseller) {
                        if ($action == 'addexe') {
                            $sql = "INSERT INTO `banners_resellers` SET
								`banner_id`='" . sqlencode(trime($banner_id)) . "'
								, `reseller_id`='" . sqlencode(trime(isReseller)) . "'
								";
                            $qq = mysql_query($sql);
                            if (!$qq) {
                                $warningMsg[-2] = 'Some records faced problems while indexing it!';
                            }
                        }
                    } else {
                        $qqDelete = mysql_query("DELETE FROM `banners_resellers` WHERE banner_id='{$banner_id}' ");
//						if( $qqDelete ) {

                        foreach ($_resellers as $reseller) {
                            $sql = "INSERT INTO `banners_resellers` SET
									`banner_id`='" . sqlencode(trime($banner_id)) . "'
									, `reseller_id`='" . sqlencode(trime($reseller['id'])) . "'
									";
                            $qq = mysql_query($sql);
                            if (!$qq) {
                                $warningMsg[-2] = 'Some records faced problems while indexing it\'s resellers!';
                            }
                        }
//						} else {
//							$warningMsg[-2] = 'Some records faced problems while indexing it\'s resellers!';
//						}
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

        $strSQL = "select * from banners where id IN(" . implode(',', $ids) . ") $limitation order by rank DESC";
        $objRS = mysql_query($strSQL);
        while ($row = mysql_fetch_object($objRS)) {

            if ($row->image != '') {
                @unlink('../uploads/banners/' . $row->image);
                @unlink('../uploads/banners/thumb/' . $row->image);
            }

            $strSQL = "DELETE FROM banners WHERE id = '" . $row->id . "' LIMIT 1";
            if (!mysql_query($strSQL)) {
                if (empty($errorMsg)) {
                    $errorMsg = "Some Records didn't affected!!";
                }
            } else {
                mysql_query("DELETE  FROM `" . index_table . "` WHERE index_id='{$row->id}' ");
                mysql_query("DELETE  FROM `banners_resellers` WHERE banner_id='{$row->id}' ");
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
                <header><h3>Banners: <?php echo ucwords($action); ?> Record
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
                            <label>Link</label>
                            <input type="text" name="link[]" value="<?php echo textencode($link[$oldrecord[$i]]); ?>" />
                        </fieldset>


            <?php if (!isReseller) { ?>
                            <fieldset>
                                <label>Reseller</label>
                                <select name="resellers[<?php echo $i; ?>][]" multiple="multiple" class="resellers_select">
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
                            <label>Banner Categories</label>
                                <?php include('functions/_index_table.php');?>
                        </fieldset>
                        <fieldset>
                            <label>Plan</label>
                            <table width="60%" border="0" cellpadding="5" cellspacing="0">
                                <tr>
                                    <td>
                                        <label>plan_clicks
                                            <br /><input type="text" name="plan_clicks[]" value="<?php echo textencode($plan_clicks[$oldrecord[$i]]); ?>" />
                                        </label>
                                    </td>
                                    <td>
                                        <label>Impressions
                                            <br /><input type="text" name="plan_impressions[]" value="<?php echo textencode($plan_impressions[$oldrecord[$i]]); ?>" />
                                        </label>
                                    </td>
                                    <td>
                                        <label>End Date
                                            <br /><input type="text" class="datepicker" name="plan_end_date[]" value="<?php echo textencode($plan_end_date[$oldrecord[$i]]); ?>" />
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label>Ad Zone</label>
                                        <br /><select name="plan_zone[]">
                                            <option value="">-- Select -- </option>
            <?php
            foreach ($_BannersZones as $k => $zone) {
                if (!isReseller || $zone['reseller']) {
                    $selected = selected($plan_zone[$oldrecord[$i]], $k);

                    $_t = "{$zone['title']} ( {$zone['width']} X {$zone['height']})";
                    ?><option value="<?php echo $k; ?>" <?php echo $selected; ?>><?php echo $_t; ?></option><?php
                }
            }
            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <label></label>
                                    </td>
                                    <td>
                                        <label></label>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>

                        <fieldset>
                            <label>Notes</label>
                            <textarea name="description[]" rows="12" ><?php echo textencode($description[$oldrecord[$i]]); ?></textarea>
                        </fieldset>

                        <fieldset>
                            <label>Banner&nbsp;(<?php echo THUMB_WIDTH; ?>&nbsp;X&nbsp;<?php echo THUMB_HEIGHT; ?>)</label>
            <?php file_field('file1' . $i, '../uploads/banners/', $file1[$oldrecord[$i]]); ?>
                        </fieldset>
                        <div class="clear"></div>
                    </div>
                    <footer></footer>
        <?php } ?>
            </article>
        </form>

        <?php if (!isReseller) { ?>
            <script type="text/javascript">
            <!--
                $(".resellers_select").multiselect({
                    header: "Select Resellers!",
                    noneSelectedText: 'Select Resellers'
                }).multiselectfilter();
            //-->
            </script>
                <?php } ?>

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
                <input type="submit" value="Browse" style="float: right;" />
                <div >
                    Find: <input name="keyword" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES); ?>" size="30" />
                    Status: <select name="status">
                        <option value="" >== Any ==</option>
                        <option value="active" <?php echo selected($_GET['status'], 'active'); ?>>Active</option>
                        <option value="inactive" <?php echo selected($_GET['status'], 'inactive'); ?>>Inactive</option>
                    </select>
                    Ad Zone: <select name="zone">
                        <option value="">-- Select -- </option>
        <?php
        foreach ($_BannersZones as $k => $zone) {
            if (!isReseller || $zone['reseller']) {
                $selected = selected($_GET['zone'], $k);


                $_t = "{$zone['title']} ( {$zone['width']} X {$zone['height']})";
                ?><option value="<?php echo $k; ?>" <?php echo $selected; ?>><?php echo $_t; ?></option><?php
            }
        }
        ?>
                    </select>
                </div>
                <div>
                        <?php if (!isReseller) { ?>
                        Reseller: <span class="autocomplete" data-link="_get.php?from=resellers">
                            <input class="input_short auto_name" type="text" name="reseller_name" value="<?php echo trim($Reseller['title']); ?>" />
                            <input class="input auto_id" type="hidden" name="reseller_id" value="<?php echo $Reseller['id']; ?>" />
                        </span>
                        <?php } ?>
                </div>
            </form>
        </div>
                        <?php
                        $sql = array();
                        if (!empty($keyword)) {
                            $keywordSQL = mysql_real_escape_string($keyword);
                            $keywordSQL = str_replace(' ', '% %', $keywordSQL);

                            $sql[] = " (banners.title LIKE '%$keywordSQL%' OR banners.description LIKE '%$keywordSQL%' ) ";
                        }

//		if(isReseller) {
//			$sql[] = " banners.reseller_id = '". isReseller ."' ";
//		} else if($Reseller) {
//			$sql[] = " banners.reseller_id = '{$Reseller['id']}' ";
//		}

                        if (isReseller) {
                            $sql[] = " banners_resellers.reseller_id = '" . isReseller . "' ";
                        } else if ($Reseller) {
                            $sql[] = " banners_resellers.reseller_id = '{$Reseller['id']}' ";
                        }

                        switch ($_GET['status']) {
                            case 'active':
                                $sql[] = " banners.status='active' ";
                                break;
                            case 'inactive':
//				$sql[] = " banners.status='' ";
                                $sql[] = " banners.status<>'active' ";
                                break;
                        }

                        if ($_GET['zone']) {
                            $sql[] = " banners.plan_zone = '" . mysql_real_escape_string($_GET['zone']) . "' ";
                        }

                        $where = ( $sql ) ? " WHERE " . implode(' AND ', $sql) : '';

                        $strSQL = "SELECT banners.*
			, GROUP_CONCAT( DISTINCT banners_resellers.reseller_id SEPARATOR ',' ) as banners_resellers
			, GROUP_CONCAT( DISTINCT category.title SEPARATOR '<~~>' ) as cat_titles
			, GROUP_CONCAT( DISTINCT banners_index.cat_id SEPARATOR '<~~>' ) as cat_ids
		FROM ( ( banners
		LEFT JOIN banners_index ON (banners.id = banners_index.index_id)
		LEFT JOIN category ON (category.id = banners_index.cat_id )
		) 
		LEFT JOIN banners_resellers ON (banners.id = banners_resellers.banner_id) )
		$where
		GROUP BY banners.id
		";

//		$strSQL="SELECT banners.*, resellers.title as reseller_title
//		FROM banners 
//		LEFT JOIN resellers ON (resellers.id=banners.reseller_id)
//		$where";


                        if ($action == 'export') {
                            include 'functions/_export.php';

                            $excel = new export_excel("Banners");
                            $excel->addField('title', 'Title');
                            $excel->addField('description', 'Description');
                            if (!isReseller) {
//				$excel->addField('reseller_title', 'Reseller');
                                $excel->addField('banners_resellers', 'Resellers', 'style_medical_resellers', $Resellers);
                            }

                            $excel->addField('cat_titles', 'Categories', 'style_category_titles', '--row--');

                            $excel->addField('plan_zone', 'Ad Zone', 'style_banner_name', $_BannersZones);
                            $excel->addField('plan_clicks', 'Plan Clicks');
                            $excel->addField('plan_impressions', 'Plan Impressions');
                            $excel->addField('plan_end_date', 'Plan End Date');


                            $excel->addField('clicks', 'Clicks');
                            $excel->addField('impressions', 'Impressions');

                            $excel->addField('status', 'Status', 'ucwords');
                            $excel->export("$strSQL ORDER BY banners.rank DESC");
                            exit;
                        }
                        $objRS = mysql_query($strSQL);
//echo "$strSQL " . mysql_error();
                        $total = @mysql_num_rows($objRS);
                        $strSQL = makePages($strSQL, $PageSize, $p, 'banners.rank desc');
                        $objRS = mysql_query($strSQL);
                        $count_all = @mysql_num_rows($objRS);
                        ?>


        <form action="<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>&action=delete" method="post" name="del" id="del">
            <article class="module width_full">
                <header><h3 class="tabs_involved">Banners Manager</h3>
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
                                <th>Title</th> 
                                <th>Link</th> 
        <?php if (!isReseller) { ?>
                                    <th>Resellers</th> 
        <?php } ?>
                                <th width="350">Categories</th>
                                <th>Ad Zone</th>
                                <th width="75">Plan Clicks</th>
                                <th width="120">Plan Impressions</th>
                                <th width="75">Plan End</th>

                                <th width="1">Clicks</th>
                                <th width="1">Impressions</th>

                                <th width="1">Status</th>
                                <th width="1">Image</th>
                                <th width="1">Rank</th>
                            </tr> 
                        </thead> 
                        <tbody id="trContainer" class="sortable"> 

        <?php while ($row = mysql_fetch_object($objRS)) { ?>
                                <tr id="tr_<?php echo $row->id; ?>">
                                    <td align="right"><input type="checkbox" name="ids[]" value="<?php echo $row->id; ?>"></td>
                                    <td>
                                        <div><b><?php echo $row->title ?></b></div>
                                        <div><?php if ($row->description != '') {
                echo summarize($row->description, 20);
            } ?></div>
                                        <div>
                                            <a href="<?php echo $filename; ?>?<?php echo $queryStr; ?>action=edit&ids[]=<?php echo $row->id; ?>">Edit Banner</a>
                                        </div>
                                    </td>
                                    <td>
                                        <div><b><?php echo $row->link; ?></b></div>
                                    </td>
                                <?php if (!isReseller) { ?>
                                        <td><?php
                                    $_resellers = style_medical_resellers($row->banners_resellers, $Resellers, 'array');
                                    foreach ($_resellers as $k => $v) {
                                        ?><div>
                                                    <a href="<?php echo $filename; ?>?<?php echo $queryStr; ?>reseller_id=<?php echo $k; ?>"><?php echo $v; ?></a>
                                                </div><?php
                                    }
                                    ?>
                                        </td>
            <?php } ?>
                                    <td>
                                        <div>
                                    <?php echo style_category_titles($row->cat_titles, $row); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?php echo $_BannersZones[$row->plan_zone]['title']; ?></div>
                                    </td>


                                    <td align="center"><?php echo $row->plan_clicks; ?></td>
                                    <td align="center"><?php echo $row->plan_impressions; ?></td>
                                    <td align="center"><?php echo ($row->plan_end_date == '0000-00-00') ? '-' : $row->plan_end_date; ?></td>

                                    <td align="center"><?php echo $row->clicks; ?></td>
                                    <td align="center"><?php echo $row->impressions; ?></td>



                                    <td align="center"><?php echo ( $row->status == 'active') ? 'Active' : 'Inactive'; ?></td>
                                    <td align="center"><?php echo scale_image("../uploads/banners/thumb/" . $row->image, 100); ?></td>

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

function style_banner_name($banner, $banners) {
    return $banners[$banner]['title'];
}
