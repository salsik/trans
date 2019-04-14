<?php
define('THUMB_WIDTH', 482); // 218
define('THUMB_HEIGHT', 317); // 150

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

define('index_table', 'competitions_cat_index');
define('index_table_competitions', 'competitions_cat_index');

include "_top.php";

$limitation = '';
$limitationDoctor = ' true ';
if (isReseller) {
    $limitation = " AND competitions.reseller_id='" . isReseller . "' ";
    $limitationDoctor = " doctors.id IN (SELECT doctor_id FROM doctors_resellers WHERE reseller_id = '" . isReseller . "' )";
}

$AllowAdd = true;
$AllowImg = true;

$allowOptionOnEdit = false;

$fieldsArray = array(
    'title', 'description', 'status',
    'wall_of_fame',
    'month', 'year',
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

$Doctor = getDataByID('doctors', $_GET['doctor_id'], $limitationDoctor);
if ($Doctor) {
    $queryStr .= "doctor_id={$Doctor['id']}&";
}

//Define the maximum items while listine
$PageSize = 10;
//Define the maximum items while adding
$max = 7;

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
        $strSQL = "select * from competitions where id IN(" . implode(',', $ids) . ") $limitation order by rank DESC";
        $objRS = mysql_query($strSQL);
        $i = 0;
        while ($row = mysql_fetch_object($objRS)) {
            foreach ($fieldsArray as $field) {
                ${$field}[$i] = $row->$field;
            }

            if ($allowOptionOnEdit) {
                $ii = 0;
                $options[$i] = array();
                $answer[$i] = array();
                $q = mysql_query("SELECT * FROM  `competitions_options` WHERE competition_id = '{$row->id}' ORDER BY rank DESC ");
                if ($q && mysql_num_rows($q)) {
                    while ($option = mysql_fetch_assoc($q)) {

                        if ($option['id'] == $row->answer_id) {
                            $answer[$i] = $ii;
                        }
                        $options[$i][$ii] = $option['title'];

                        $ii++;
                    }
                }
            }

            $index[$i] = array();
            $q = mysql_query("SELECT * FROM `" . index_table . "` WHERE index_id='{$row->id}' ");
            if ($q && mysql_num_rows($q)) {
                while ($indx = mysql_fetch_assoc($q)) {
                    $index[$i][$indx['cat_id']] = 'yes';
                }
            }

            $reseller_id[$i] = $row->reseller_id;
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

        $index = $_POST['index'];
        $reseller_id = $_POST['reseller_id'];
        $options = $_POST['options'];
        $answer = $_POST['answer'];

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

            if (empty($title[$i])) {
                $Errors[] = "Missing Title!!";
            }
//			else if( empty($description[$i]) ) {
//				$Errors[] = "Missing Description!!";
//			}

            $sqlCompetition = array();
            $sqlCompetition['month'] = intval($month[$i]);
            $sqlCompetition['year'] = intval($year[$i]);

//			if( $action == 'addexe' ) {
            if (isReseller) {
                $sql['reseller_id'] = " `reseller_id`='" . isReseller . "', ";
                $sqlCompetition['reseller_id'] = intval(isReseller);
            } else {
                $_reseller = getDataByID('resellers', $reseller_id[$i]);

                if (!$_reseller) {
                    $Errors[] = "Missing Reseller!!";
                } else {
                    $sql['reseller_id'] = " `reseller_id`='{$_reseller['id']}', ";
                    $sqlCompetition['reseller_id'] = intval($_reseller['id']);
                }
            }
//			}

            $year[$i] = intval($year[$i]);
            $month[$i] = intval($month[$i]);

            if ($year[$i] < 2000) {
                $Errors[] = "Invalid Year!!";
            } else if ($month[$i] < 1 || $month[$i] > 12) {
                $Errors[] = "Invalid Month!!";
            }

            if (!$Errors) {
                $mktime = mktime(0, 0, 0, $month[$i], 1, $year[$i]);
                $sql['month_time'] = " `month_time`='{$mktime}', ";
            }

            if (!is_array($options[$i])) {
                $options[$i] = array();
            }

//			$answer[$i] = (array) $answer[$i];
            if ($allowOptionOnEdit || $action == 'addexe') {
                $answered = 0;
                $answers = array();
                foreach ($options[$i] as $k => $option) {
                    if (!empty($option)) {
                        $answers[$k] = array('title' => $option);
                        if ($answer[$i] == $k) {
                            $answers[$k]['selected'] = true;
                            $answered = true;
                        }
                    }
                }

                if (!count($answers)) {
                    $Errors[] = "Missing Options!!";
                } else if (!$answered) {
                    $Errors[] = "Missing the correct answer!!";
                }
            }

            include('functions/_index_check.php');

            $strSQL = "competitions set " . implode('', $sql);

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
                    if ($result = file_upload('image', 'file1' . $i, '../uploads/competitions/', $errorMsg[$j])) {
                        $file1[$i] = $result['name'];
                        if (empty($errorMsg[$j])) {

                            $Rimage = new SimpleImage();
                            $Rimage->load('../uploads/competitions/' . $file1[$i]);
                            if ($Rimage->getWidth() > THUMB_WIDTH) {
                                $Rimage->resizeToWidth(THUMB_WIDTH);
                            }
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
                            $Rimage->save('../uploads/competitions/thumb/' . $file1[$i]);
                        }
                    }
                    if (!empty($errorMsg[$j])) {// Upload Error
                        $oldrecord[$j] = $i;
                        $j++;
                        $flag = 0;
                    } else {
                        $strSQL = "update " . $strSQL . " image='" . trime($file1[$i]) . "' where id='" . $ids[$i] . "' $limitation ";
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
                    if ($result = file_upload('image', 'file1' . $i, '../uploads/competitions/', $errorMsg[$j])) {
                        $file1[$i] = $result['name'];
                        if (empty($errorMsg[$j])) {

                            $Rimage = new SimpleImage();
                            $Rimage->load('../uploads/competitions/' . $file1[$i]);
                            if ($Rimage->getWidth() > THUMB_WIDTH) {
                                $Rimage->resizeToWidth(THUMB_WIDTH);
                            }
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT );
                            $Rimage->save('../uploads/competitions/thumb/' . $file1[$i]);
                        }
                    }
                    if (!empty($errorMsg[$j])) {// Upload Error
                        $oldrecord[$j] = $i;
                        $j++;
                        $flag = 0;
                    } else {

                        $q = mysql_query("SELECT max(rank) as max FROM competitions");
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
                if (!$q) {
                    if (mysql_errno() == 1062) {
                        $errorMsg[$j] = 'There is a competition in that month!!';
                    } else {
                        $errorMsg[$j] = 'Something went wronge!';
//						$errorMsg[$j] = mysql_error();
                    }
                    $oldrecord[$j] = $i;
                    $j++;
                    $flag = 0;
                } else {
                    if ($action == 'addexe') {
                        $competition_id = mysql_insert_id();
                    } else {
                        $competition_id = intval($ids[$i]);
                    }

                    $index_id = $competition_id;
                    include('functions/_index_create.php');

                    if ($action == 'addexe') { // $allowOptionOnEdit || 
//						$competition_id = mysql_insert_id();
                        $answers = array_reverse($answers);

                        $q = mysql_query("SELECT max(rank) as max FROM competitions_options");
                        $r = mysql_fetch_object($q);
                        $r->max++;

                        foreach ($answers as $k => $option) {

                            $q = mysql_query("INSERT INTO competitions_options SET 
								competition_id = '$competition_id'
								, `title`='" . sqlencode(trime($option['title'])) . "'
								, rank='" . ($r->max++) . "'
								, date='" . date('Y-m-d') . "'
								, time='" . time() . "'
							");

                            if ($q) {
                                $option_id = mysql_insert_id();

                                if ($option['selected']) {
                                    mysql_query("UPDATE competitions SET answer_id = '$option_id' WHERE id='$competition_id' LIMIT 1");
                                }
                            }
                        }
                    }
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

        $strSQL = "select * from competitions where id IN(" . implode(',', $ids) . ") $limitation order by rank DESC";
        $objRS = mysql_query($strSQL);
        while ($row = mysql_fetch_object($objRS)) {

            if ($row->image != '') {
                @unlink('../uploads/competitions/' . $row->image);
                @unlink('../uploads/competitions/thumb/' . $row->image);
            }

            $strSQL = "DELETE FROM competitions WHERE id = '" . $row->id . "' LIMIT 1";
            if (!mysql_query($strSQL)) {
                if (empty($errorMsg)) {
                    $errorMsg = "Some Records didn't affected!!";
                }
            } else {
                $strSQL = "DELETE FROM competitions_options WHERE competition_id = '" . $row->id . "' ";
                mysql_query($strSQL);
                $strSQL = "DELETE FROM competitions_index WHERE competition_id = '" . $row->id . "' ";
                mysql_query($strSQL);

                mysql_query("DELETE  FROM `" . index_table . "` WHERE index_id='{$row->id}' ");
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
        <style>
            <!--
            .input_short {
                width: 150px !important;
            }
            -->
        </style>
        <form action="<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>" method="post" name="myform" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?php echo $action; ?>exe"> 
            <article class="module width_full">
                <header><h3>Quiz: <?php echo ucwords($action); ?> Record
                        <input type="submit" value="Save" class="alt_btn">
                        <input type="button" value="Cancel" onclick="window.location = '<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>'">
                    </h3></header>

        <?php for ($i = 0; $i < $max; $i++) { ?>
            <?php
            $_competition = array();
            if ($action == 'edit') {
                $_competition = getDataByID('competitions', $ids[$i], " TRUE $limitation ");
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
                            <label>Title</label>
                            <input type="text" name="title[]" value="<?php echo textencode($title[$oldrecord[$i]]); ?>" />
                        </fieldset>

                        <fieldset>
                            <label>Description</label>
                            <textarea name="description[]" rows="12" ><?php echo textencode($description[$oldrecord[$i]]); ?></textarea>
                        </fieldset>


            <?php if (!isReseller) { ?>
                            <fieldset>
                                <label>Reseller</label>
                                <select name="reseller_id[]">
                <?php
                $ResellerID = nor($reseller_id[$oldrecord[$i]], $_GET['reseller_id'], true);
                foreach ($Resellers as $reseller) {
                    $Selected = ( $reseller['id'] == $ResellerID ) ? ' selected="selected" ' : '';
                    ?><option value="<?php echo $reseller['id']; ?>" <?php echo $Selected; ?> ><?php echo $reseller['title']; ?></option><?php
                }
                ?>
                                </select>
                            </fieldset>
            <?php } ?>

                        <fieldset>
                            <label>Quiz Categories</label>
                                <?php include('functions/_index_table.php');
                                ; ?>
                        </fieldset>

                        <fieldset>
                            <label>Wall Of Fame</label>
                            <input type="text" name="wall_of_fame[]" value="<?php echo textencode($wall_of_fame[$oldrecord[$i]]); ?>" />
                        </fieldset>

                        <fieldset>
                            <label>During</label>
                            <table width="" border="0" cellpadding="5" cellspacing="0">
                                <tr>
                                    <td>
                                        <b>Year:</b> <select name="year[]">
                                            <option value="" >--</option>
            <?php
            if ($action == 'add' && !isset($year[$oldrecord[$i]])) {
                $year[$oldrecord[$i]] = date('Y');
            }

            for ($x = 2013; $x < date('Y') + 5; $x++) {
                $selected = selected($x, $year[$oldrecord[$i]]);
                ?><option value="<?php echo $x; ?>" <?php echo $selected; ?>><?php echo $x; ?></option><?php
            }
            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <b>Month:</b> <select name="month[]">
                                            <option value="" >--</option>
                                            <?php
                                            if ($action == 'add' && !isset($month[$oldrecord[$i]])) {
                                                $month[$oldrecord[$i]] = date('m');
                                            }

                                            for ($x = 1; $x < 13; $x++) {
                                                $selected = selected($x, $month[$oldrecord[$i]]);
                                                ?><option value="<?php echo $x; ?>" <?php echo $selected; ?>><?php echo ($x < 10) ? '0' . $x : $x; ?></option><?php
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
                                            <?php if ($allowOptionOnEdit || $action == 'add') { ?>
                                                <?php
//		var_dump($options);
//		var_dump($answer);
//		
                                                ?>
                            <fieldset>
                                <label>Answers</label>
                                <table class="competitions_options" data-i="<?php echo $i; ?>" width="60%" border="0" cellpadding="5" cellspacing="0">
                            <?php
                            if (!is_array($options[$oldrecord[$i]])) {
                                $options[$oldrecord[$i]] = array();
                            }

                            $newK = 0;
                            $maxK = 0;
                            foreach ($options[$oldrecord[$i]] as $k => $v) {
                                if (!empty($v)) {
                                    $newK++;
                                    competitionRowOption($i, $answer[$oldrecord[$i]] == $k, $newK, $v); // $maxK+1
                                    $maxK = max($maxK, $k);
                                }
                            }

                            competitionRowOption($i);
                            ?>
                                </table>
                            </fieldset>
                                <?php } ?>
                                <?php if ($AllowImg) { ?>
                            <fieldset>
                                <label>Image</label>
                                    <?php file_field('file1' . $i, '../uploads/competitions/', $file1[$oldrecord[$i]]); ?>
                            </fieldset>
                                <?php } ?>
                        <div class="clear"></div>
                    </div>
                    <footer></footer>
        <?php } ?>
            </article>
        </form>
        <style>
            <!--
            .competitions_options .input {
                width: 95% !important;
            }
            -->
        </style>
        <script type="text/javascript">
        <!--
            var tableIndex = {};
            $('.competitions_options').each(function () {
                var table = $(this);
                table.tableIndex = $('.tr', table).length + 1;
                table.i = $(table).data('i');
                competitionRowOptionFocus(table, table);
                _buildRemoveBtn(table);
            });





            function competitionRowOptionFocus(table, Element) {
                $('.option input', Element).focus(function () {
                    var index = $('.option input', table).index(this);
                    if (index + 1 == $('.option input', table).length) {
                        var tr = $('.tr:last', table).clone();

                        table.tableIndex++;

                        tr.find('.option input').attr('name', 'options[' + table.i + '][' + table.tableIndex + ']').val('');
                        tr.find('.answer input').prop('checked', false).val(table.tableIndex);//.attr('name', 'answer['+table.i+']')

                        table.append(tr);

                        competitionRowOptionFocus(table, tr);
                        _buildRemoveBtn(table);
                    }
                });
            }

            function _buildRemoveBtn(table) {
                table.find('.remove').empty();
                if (table.find('.tr').length > 1) {
                    table.find('.remove').each(function () {
                        $('<img src="images/delete.jpg" />').click(function () {
                            $(this).parents('tr:first').remove();
                            _buildRemoveBtn(table);
                        }).css('cursor', 'pointer').appendTo(this);
                    });
                }
            }

        //-->
        </script>
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

                <input type="submit" value="Browse" />
            </form>
        </div>
        <?php
        $sql = array();
        if (!empty($keyword)) {
            $keywordSQL = mysql_real_escape_string($keyword);
            $keywordSQL = str_replace(' ', '% %', $keywordSQL);

            $sql[] = " (competitions.title LIKE '%$keywordSQL%' OR competitions.description LIKE '%$keywordSQL%' ) ";
        }

        if (isReseller) {
            $sql[] = " competitions.reseller_id = '" . isReseller . "' ";
        } else if ($Reseller) {
            $sql[] = " competitions.reseller_id = '{$Reseller['id']}' ";
        }

        switch ($_GET['status']) {
            case 'active':
                $sql[] = " competitions.status='active' ";
                break;
            case 'inactive':
//				$sql[] = " competitions.status='' ";
                $sql[] = " competitions.status<>'active' ";
                break;
        }

        $where = ( $sql ) ? " WHERE " . implode(' AND ', $sql) : '';

//		$strSQL="SELECT competitions.*, resellers.title as reseller_title
//		FROM competitions 
//		LEFT JOIN resellers ON (resellers.id=competitions.reseller_id)
//		$where";

        $strSQL = "SELECT competitions.*, resellers.title as reseller_title
			, GROUP_CONCAT( DISTINCT category.title SEPARATOR '<~~>' ) as cat_titles
			, GROUP_CONCAT( DISTINCT competitions_cat_index.cat_id SEPARATOR '<~~>' ) as cat_ids
		FROM ( competitions
			LEFT JOIN competitions_cat_index ON (competitions.id = competitions_cat_index.index_id)
			LEFT JOIN category ON (category.id = competitions_cat_index.cat_id )
		) 
		LEFT JOIN resellers ON (resellers.id = competitions.reseller_id)
		$where
		GROUP BY competitions.id
		";

        if ($action == 'export') {
            include 'functions/_export.php';

            $excel = new export_excel("Competitions");
            $excel->addField('title', 'Title');
            $excel->addField('description', 'Description');
            if (!isReseller) {
                $excel->addField('reseller_title', 'Reseller');
            }

            $excel->addField('cat_titles', 'Categories', 'style_category_titles', '--row--');

            $excel->addField('date', 'Date');

            $excel->addField('status', 'Status', 'ucwords');
            $excel->export("$strSQL ORDER BY competitions.rank DESC");
            exit;
        }
        $objRS = mysql_query($strSQL);
//echo "{$strSQL} " . mysql_error();
        $total = @mysql_num_rows($objRS);
        $strSQL = makePages($strSQL, $PageSize, $p, 'competitions.rank DESC');
        $objRS = mysql_query($strSQL);
        $count_all = @mysql_num_rows($objRS);
        ?>


        <form action="<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>&action=delete" method="post" name="del" id="del">
            <article class="module width_full">
                <header><h3 class="tabs_involved">Quiz Manager</h3>
                    <ul class="tabs">
        <?php if ($AllowAdd) { ?>
                            <li><a href="#add" onClick="window.location = '<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>&action=add'" >Add New</a></li>
        <?php } ?>
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
                        <?php if (!isReseller) { ?>
                                    <th>Reseller</th> 
        <?php } ?>
                                <th width="350">Categories</th>
                                <th>During</th>

                                <th width="1">Added</th>
                                <th width="1">Status</th>

                                <th>Answers</th>
        <?php if ($AllowImg) { ?>
                                    <th width="1">Image</th>
        <?php } ?>
                            </tr> 
                        </thead> 
                        <tbody id="trContainer"> 

        <?php while ($row = mysql_fetch_object($objRS)) { ?>
            <?php
            $options = array();
            $sql = "SELECT * FROM competitions_options WHERE competition_id = '$row->id' ORDER BY rank DESC";
            $q = mysql_query($sql);
            if ($q && mysql_num_rows($q)) {
                while ($r = mysql_fetch_assoc($q)) {
                    $options[$r['id']] = $r;
                }
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
                                        <div>
            <?php echo style_category_titles($row->cat_titles, $row); ?>
                                        </div>
                                    </td>

                                    <td>
                                        <div><b><?php echo $row->month; ?> / <?php echo $row->year; ?></b></div>
                                    </td>
                                    <td>
                                        <div><?php echo $row->date; ?></div>
                                    </td>
                                    <td align="center"><?php echo ( $row->status == 'active') ? 'Active' : 'Inactive'; ?></td>


                                    <td>
                                        <ul>
            <?php
            foreach ($options as $option) {
                if ($option['id'] == $row->answer_id) {
                    ?><li><b><?php echo $option['title']; ?></b></li><?php
                } else {
                    ?><li><?php echo $option['title']; ?></li><?php
                }
            }
            ?>
                                        </ul>
                                        <a href="competitions_options.php?competition_id=<?php echo $row->id; ?>&action=edit&ids[]=<?php echo implode('&ids[]=', array_keys($options)); ?>">Edit</a>
                                        - <a href="competitions_options.php?competition_id=<?php echo $row->id; ?>">Arrange</a>
                                    </td>
                                            <?php if ($AllowImg) { ?>
                                        <td align="center"><?php echo scale_image("../uploads/competitions/thumb/" . $row->image, 100); ?></td>
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

                        function competitionRowOption($i, $answer = false, $newK = false, $v = '') {
                            ?><tr class="tr">
        <td class="answer" style="vertical-align: middle;" width="1"><input type="radio" name="answer[<?php echo $i; ?>]" value="<?php echo $newK; ?>" <?php echo checked($answer, true); ?> /></td>
        <td class="option"><input class="input" type="text" name="options[<?php echo $i; ?>][<?php echo $newK; ?>]" value="<?php echo textencode($v); ?>" /></td>
        <td class="remove" width="1"></td>
    </tr><?php
            }
            