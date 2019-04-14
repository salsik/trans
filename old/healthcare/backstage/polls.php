<?php
define('THUMB_WIDTH', 482); // 218
define('THUMB_HEIGHT', 317); // 150

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

define('index_table', 'polls_cat_index');

include "_top.php";

$limitation = '';
//$limitationDoctor = ' true ';
if (isReseller) {
    $limitation = " AND polls.reseller_id='" . isReseller . "' ";
//	$limitationDoctor = " doctors.id IN (SELECT doctor_id FROM doctors_resellers WHERE reseller_id = '".isReseller."' )";
}

$AllowAdd = true;
$AllowImg = true;

$allowOptionOnEdit = false;


$fieldsArray = array(
    'title', 'description', 'status',
    'date_from', 'date_to',
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

if (is_date($_GET['date_from'])) {
    $queryStr .= "date_from={$_GET['date_from']}&";
} else {
    $_GET['date_from'] = '';
}

if (is_date($_GET['date_to'])) {
    $queryStr .= "date_to={$_GET['date_to']}&";
} else {
    $_GET['date_to'] = '';
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

//$Doctor = getDataByID('doctors', $_GET['doctor_id'], $limitationDoctor);
//if( $Doctor ) {
//	$queryStr .= "doctor_id={$Doctor['id']}&";
//}
//Define the maximum items while listine
$PageSize = 10;
//Define the maximum items while adding
$max = 1;

if ($Reseller) {
    ?><h4 class="alert_info">Reseller: <?php echo $Reseller['title']; ?></h4><?php
}
/*
  if($Doctor) {
  ?><h4 class="alert_info">Doctor: <?php echo $Doctor['full_name']; ?></h4><?php
  }
 */
switch ($action):

    case "add":
        if (!$AllowAdd) {
            break;
        }
        $showing = "record";
        break;
    case "edit":
        //Get needed data from the DB
        $strSQL = "select * from polls where id IN(" . implode(',', $ids) . ") $limitation order by rank DESC";
        $objRS = mysql_query($strSQL);
        $i = 0;
        while ($row = mysql_fetch_object($objRS)) {
            foreach ($fieldsArray as $field) {
                ${$field}[$i] = $row->$field;
            }

            if ($allowOptionOnEdit) {
                $ii = 0;
                $options[$i] = array();
                $q = mysql_query("SELECT * FROM  `polls_options` WHERE poll_id = '{$row->id}' ORDER BY rank DESC ");
                if ($q && mysql_num_rows($q)) {
                    while ($option = mysql_fetch_assoc($q)) {

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
//			if( $action == 'addexe' ) {
            if (isReseller) {
                $sql['reseller_id'] = " `reseller_id`='" . isReseller . "', ";
            } else {
                $_reseller = getDataByID('resellers', $reseller_id[$i]);

                if (!$_reseller) {
                    $Errors[] = "Missing Reseller!!";
                } else {
                    $sql['reseller_id'] = " `reseller_id`='{$_reseller['id']}', ";
                }
            }
//			}


            if (!is_date($date_from[$i])) {
                $Errors[] = "Invalid Start Date!!";
            } else if (!is_date($date_to[$i])) {
                $Errors[] = "Invalid End Date!!";
            }

            if (!is_array($options[$i])) {
                $options[$i] = array();
            }

            if ($allowOptionOnEdit || $action == 'addexe') {
                $answers = array();
                foreach ($options[$i] as $k => $option) {
                    if (!empty($option)) {
                        $answers[$k] = array('title' => $option);
                    }
                }

                if (!count($answers)) {
                    $Errors[] = "Missing Options!!";
                }
            }

            include('functions/_index_check.php');

            $strSQL = "polls set " . implode('', $sql);

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
                    if ($result = file_upload('image', 'file1' . $i, '../uploads/polls/', $errorMsg[$j])) {
                        $file1[$i] = $result['name'];
                        if (empty($errorMsg[$j])) {

                            $Rimage = new SimpleImage();
                            $Rimage->load('../uploads/polls/' . $file1[$i]);
                            if ($Rimage->getWidth() > THUMB_WIDTH) {
                                $Rimage->resizeToWidth(THUMB_WIDTH);
                            }
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
                            $Rimage->save('../uploads/polls/thumb/' . $file1[$i]);
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
                    if ($result = file_upload('image', 'file1' . $i, '../uploads/polls/', $errorMsg[$j])) {
                        $file1[$i] = $result['name'];
                        if (empty($errorMsg[$j])) {

                            $Rimage = new SimpleImage();
                            $Rimage->load('../uploads/polls/' . $file1[$i]);
                            if ($Rimage->getWidth() > THUMB_WIDTH) {
                                $Rimage->resizeToWidth(THUMB_WIDTH);
                            }
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT );
                            $Rimage->save('../uploads/polls/thumb/' . $file1[$i]);
                        }
                    }
                    if (!empty($errorMsg[$j])) {// Upload Error
                        $oldrecord[$j] = $i;
                        $j++;
                        $flag = 0;
                    } else {

                        $q = mysql_query("SELECT max(rank) as max FROM polls");
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
                    $errorMsg[$j] = 'Something went wronge!';
//					$errorMsg[$j] = mysql_error();
                    $oldrecord[$j] = $i;
                    $j++;
                    $flag = 0;
                } else {
                    if ($action == 'addexe') {
                        $poll_id = mysql_insert_id();
                    } else {
                        $poll_id = intval($ids[$i]);
                    }

                    $index_id = $poll_id;
                    include('functions/_index_create.php');

                    if ($action == 'addexe') { // $allowOptionOnEdit || 
                        $poll_id = mysql_insert_id();
						
						if ($poll_id==0)
						{
							$myQ= mysql_query("SELECT id,reseller_id FROM polls ORDER BY ID DESC limit 1");
							$r = mysql_fetch_object($myQ);
							$poll_id=(int)$r->id;
							$res_id=(int)$r->reseller_id;
						
						}
						 

                        $answers = array_reverse($answers);

                        $q = mysql_query("SELECT max(rank) as max FROM polls_options");
                        $r = mysql_fetch_object($q);
                        $r->max++;

                        foreach ($answers as $k => $option) {

                            $q = mysql_query("INSERT INTO polls_options SET 
								poll_id = '$poll_id'
								, `title`='" . sqlencode(trime($option['title'])) . "'
								, rank='" . ($r->max++) . "'
								, date='" . date('Y-m-d') . "'
								, time='" . time() . "'
							");

//							if( $q ) {
//								$option_id = mysql_insert_id();
//							}
                        }
						
						// resellers transplantation
						
						$transplantation = false;
						
						
						if (isReseller==108 || $res_id ==108)
							$transplantation= true;
						 
							
						//var_dump($res_id);
						if ($transplantation==true)
						{
							
							$dataToSend = array();
									
							$data1= array('type'=>'poll', 'id'=>$poll_id, 'title'=>  $_POST['title'][0] );
							array_push($dataToSend, $data1);
								
							$myJSON = json_encode($dataToSend);
							
							
							$apiUrl = "http://link-program.com/prevision/notification";
							
							$curl = curl_init();
							curl_setopt($curl, CURLOPT_URL, $apiUrl);
							curl_setopt($curl, CURLOPT_POST, 1);
							curl_setopt($curl, CURLOPT_POSTFIELDS,$myJSON);  //Post Fields
							curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($curl, CURLOPT_HTTPHEADER, array(
							'Accept: application/json',
							'X-App-Key: preprogujIZZCdvuurW',//You have to use this Application key
							'Content-Type: application/json',
							'Content-Length: ' . strlen($myJSON)
							
							));
							
							$response = curl_exec($curl);
							$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
							
							
							 
								
						 
			   
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

        $strSQL = "select * from polls where id IN(" . implode(',', $ids) . ") $limitation order by rank DESC";
        $objRS = mysql_query($strSQL);
        while ($row = mysql_fetch_object($objRS)) {

            if ($row->image != '') {
                @unlink('../uploads/polls/' . $row->image);
                @unlink('../uploads/polls/thumb/' . $row->image);
            }

            $strSQL = "DELETE FROM polls WHERE id = '" . $row->id . "' LIMIT 1";
            if (!mysql_query($strSQL)) {
                if (empty($errorMsg)) {
                    $errorMsg = "Some Records didn't affected!!";
                }
            } else {
                $strSQL = "DELETE FROM polls_options WHERE poll_id = '" . $row->id . "' ";
                mysql_query($strSQL);
                $strSQL = "DELETE FROM polls_index WHERE poll_id = '" . $row->id . "' ";
                mysql_query($strSQL);

                mysql_query("DELETE  FROM `" . index_table . "` WHERE index_id='{$row->id}' ");
            }
        }

        if (empty($errorMsg)) {
            $msg = "Record(s) deleted successfully!!";
        }
        break;

    case 'results':

        $Poll = getDataByID('polls', $_GET['poll_id'], " TRUE {$limitation} ");
        if ($Poll) {

            if ($_REQUEST['do'] == 'export') {

                $results = get_poll_results($Poll['id']);

                export_poll_results($Poll, $results['options']);
                exit;
            }

            $showing = "results";
        } else {
            $errorMsg = "Selected poll not available!!";
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
                <header><h3>Poll: <?php echo ucwords($action); ?> Record
                        <input type="submit" value="Save" class="alt_btn">
                        <input type="button" value="Cancel" onclick="window.location = '<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>'">
                    </h3></header>

        <?php for ($i = 0; $i < $max; $i++) { ?>
            <?php
            $_poll = array();
            if ($action == 'edit') {
                $_poll = getDataByID('polls', $ids[$i], " TRUE $limitation ");
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
                            <label>Poll Categories</label>
            <?php include('functions/_index_table.php');
            ; ?>
                        </fieldset>

                        <fieldset>
                            <label>During</label>
                            <div style="margen-left: 210px;">
                                <label style="height: auto;">From Date:
                                    <br /><input type="text" class="datepicker" name="date_from[]" value="<?php echo textencode($date_from[$oldrecord[$i]]); ?>" />
                                </label>
                                <label style="height: auto;">To Date:
                                    <br /><input type="text" class="datepicker" name="date_to[]" value="<?php echo textencode($date_to[$oldrecord[$i]]); ?>" />
                                </label>
                                <div class="clear"></div>
                            </div>
                        </fieldset>
                            <?php if ($allowOptionOnEdit || $action == 'add') { ?>
                            <fieldset>
                                <label>Answers</label>
                                <table class="polls_options" data-i="<?php echo $i; ?>" width="60%" border="0" cellpadding="5" cellspacing="0">
                <?php
                if (!is_array($options[$oldrecord[$i]])) {
                    $options[$oldrecord[$i]] = array();
                }

                $maxK = 0;
                foreach ($options[$oldrecord[$i]] as $k => $v) {
                    if (!empty($v)) {
                        pollRowOption($i, $answer[$oldrecord[$i]] == $k, $maxK + 1, $v);
                        $maxK = max($maxK, $k);
                    }
                }

                pollRowOption($i);
                ?>
                                </table>
                            </fieldset>
                                <?php } ?>
                                <?php if ($AllowImg) { ?>
                            <fieldset>
                                <label>Image</label>
                                    <?php file_field('file1' . $i, '../uploads/polls/', $file1[$oldrecord[$i]]); ?>
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
            .polls_options .input {
                width: 95% !important;
            }
            -->
        </style>
        <script type="text/javascript">
        <!--
            var tableIndex = {};
            $('.polls_options').each(function () {
                var table = $(this);
                table.tableIndex = $('.tr', table).length + 1;
                table.i = $(table).data('i');
                pollRowOptionFocus(table, table);
                _buildRemoveBtn(table);
            });

            function pollRowOptionFocus(table, Element) {
                $('.option input', Element).focus(function () {
                    var index = $('.option input', table).index(this);
                    if (index + 1 == $('.option input', table).length) {
                        var tr = $('.tr:last', table).clone();

                        table.tableIndex++;

                        tr.find('.option input').val('').attr('name', 'options[' + table.i + '][' + table.tableIndex + ']');
                        tr.find('.answer input').prop('checked', false).val(table.tableIndex);

                        table.append(tr);

                        pollRowOptionFocus(table, tr);
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


    case 'results':

        $results = get_poll_results($Poll['id']);

        $colors = array('ed0e0e', 'bc4ca0', '861ce8', '264262', '8ed1cf', '10d65f', '8cf510', 'bb980c', 'cf441f', 'ba9696',);

        $_colors = array();
        ?>
        <h4 class="alert_info">Poll: <?php echo $Poll['title']; ?></h4>
        <style>
            <!--
            .resultBarBox {
                width: 95%;
                margin: 0 1%;
                border: 1px solid #000000;
            }
            .resultBar {
                line-height: 30px;
                height: 30px;
            }
            -->
        </style>
        <article class="module width_full">
            <header><h3 class="tabs_involved">Poll Results</h3>
                <ul class="tabs">
                    <li><a href="#Export" onClick="window.location = '<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>&action=results&poll_id=<?php echo $Poll['id']; ?>&do=export'" >Export Results</a></li>
                </ul>
            </header>
            <div class="tab_container">
                <table class="tablesorter" cellspacing="0"> 
                    <thead> 
                        <tr>
                            <th width="270">Option</th> 
                            <th width="50">Votes</th>
                            <th>&nbsp;</th>
                        </tr> 
                    </thead>
                    <tbody id="trContainer"> 
                        <tr>
                            <td colspan="20" style="background: #c9f0d2;">
                                <div><b><?php echo $Poll['title']; ?></b></div>
                                <div><?php echo $Poll['description']; ?></div>
                            </td>
                        </tr>
        <?php foreach ($results['options'] as $option) { ?>
            <?php
            if (!$_colors) {
                shuffle($colors);
                $_colors = $colors;
            }
            $color = array_shift($_colors);
            ?>
                            <tr>
                                <td>
                                    <div><b><?php echo $option['title']; ?></b></div>
                                </td>
                                <td align="center">
                                    <div><b><?php echo $option['total']; ?></b></div>
                                    <div><?php echo $option['percent']; ?>%</div>
                                </td>
                                <td>
                                    <div class="resultBarBox" style="border-color:#<?php echo $color; ?>;">
                                        <div class="resultBar" style="background:#<?php echo $color; ?>; width:<?php echo $option['percent']; ?>%;">&nbsp;</div>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody> 
                </table>
            </div><!-- end of .tab_container -->
            <footer></footer>
        </article><!-- end of content manager article -->
        </form>
        <?php
        $queryStr .= "action=results&poll_id={$Poll['id']}&";
//var_dump($queryStr);
        $strSQL = "SELECT doctors.*, polls_options.title as option_title
			FROM doctors, polls_options, polls_index
			WHERE polls_options.poll_id='{$Poll['id']}' 
				AND polls_index.option_id = polls_options.id
				AND polls_index.doctor_id = doctors.id
				
			";

        $objRS = mysql_query($strSQL);
//echo mysql_error();
        $total = @mysql_num_rows($objRS);
        $strSQL = makePages($strSQL, $PageSize, $p, 'polls_index.time ASC');
        $objRS = mysql_query($strSQL);
        $count_all = @mysql_num_rows($objRS);
        ?>

        <article class="module width_full">
            <header><h3 class="tabs_involved">Poll Participant</h3></header>
            <div class="tab_container">
                <table class="tablesorter" cellspacing="0"> 
                    <thead> 
                        <tr> 
                            <th>Doctor</th> 
                            <th>Answer</th>
                            <th>Date/Time</th>
                        </tr> 
                    </thead> 
                    <tbody id="trContainer"> 

        <?php while ($row = mysql_fetch_object($objRS)) { ?>
                            <tr id="tr_<?php echo $row->id; ?>">
                                <td>
                                    <div><b><?php echo $row->full_name ?></b></div>
                                </td>
                                <td><?php echo $row->option_title; ?></td>
                                <td>
                                    <div><?php echo date("Y-m-d h:ia", $row->time); ?></div>
                                </td>
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
        <?php if (!isReseller) { ?>
                        Reseller: <span class="autocomplete" data-link="_get.php?from=resellers">
                            <input class="input_short auto_name" type="text" name="reseller_name" value="<?php echo trim($Reseller['title']); ?>" />
                            <input class="input auto_id" type="hidden" name="reseller_id" value="<?php echo $Reseller['id']; ?>" />
                        </span>
        <?php } ?>
                </div>
                <div>
                    Date From: <input name="date_from" class="datepicker" value="<?php echo htmlspecialchars($_GET['date_from'], ENT_QUOTES); ?>" />
                    To: <input name="date_to" class="datepicker" value="<?php echo htmlspecialchars($_GET['date_to'], ENT_QUOTES); ?>" />
                </div>
            </form>
        </div>
        <?php
        $sql = array();
        if (!empty($keyword)) {
            $keywordSQL = mysql_real_escape_string($keyword);
            $keywordSQL = str_replace(' ', '% %', $keywordSQL);

            $sql[] = " (polls.title LIKE '%$keywordSQL%' OR polls.description LIKE '%$keywordSQL%' ) ";
        }

        if (isReseller) {
            $sql[] = " polls.reseller_id = '" . isReseller . "' ";
        } else if ($Reseller) {
            $sql[] = " polls.reseller_id = '{$Reseller['id']}' ";
        }

        if ($_GET['date_from']) {
            $sql[] = " polls.date_from >= '{$_GET['date_from']}' ";
        }

        if ($_GET['date_to']) {
            $sql[] = " polls.date_to <= '{$_GET['date_to']}' ";
        }

        switch ($_GET['status']) {
            case 'active':
                $sql[] = " polls.status='active' ";
                break;
            case 'inactive':
//				$sql[] = " polls.status='' ";
                $sql[] = " polls.status<>'active' ";
                break;
        }

        $where = ( $sql ) ? " WHERE " . implode(' AND ', $sql) : '';

//		$strSQL="SELECT polls.*, resellers.title as reseller_title
//		FROM polls 
//		LEFT JOIN resellers ON (resellers.id=polls.reseller_id)
//		$where";

        $strSQL = "SELECT polls.*, resellers.title as reseller_title
			, GROUP_CONCAT( DISTINCT category.title SEPARATOR '<~~>' ) as cat_titles
			, GROUP_CONCAT( DISTINCT polls_cat_index.cat_id SEPARATOR '<~~>' ) as cat_ids
		FROM ( polls
			LEFT JOIN polls_cat_index ON (polls.id = polls_cat_index.index_id)
			LEFT JOIN category ON (category.id = polls_cat_index.cat_id )
		) 
		LEFT JOIN resellers ON (resellers.id = polls.reseller_id)
		$where
		GROUP BY polls.id
		";

        if ($action == 'export') {
            include 'functions/_export.php';

            $excel = new export_excel("Polls");
            $excel->addField('title', 'Title');
            $excel->addField('description', 'Description');
            if (!isReseller) {
                $excel->addField('reseller_title', 'Reseller');
            }

            $excel->addField('cat_titles', 'Categories', 'style_category_titles', '--row--');

            $excel->addField('date_from', 'From');
            $excel->addField('date_to', 'To');
            $excel->addField('date', 'Added');

            $excel->addField('status', 'Status', 'ucwords');
            $excel->export("$strSQL ORDER BY polls.rank DESC");
            exit;
        }
        $objRS = mysql_query($strSQL);
        $total = @mysql_num_rows($objRS);
        $strSQL = makePages($strSQL, $PageSize, $p, 'polls.rank DESC');
        $objRS = mysql_query($strSQL);
        $count_all = @mysql_num_rows($objRS);
        ?>


        <form action="<?php echo $filename; ?>?<?php echo $queryStr; ?>p=<?= $p ?>&action=delete" method="post" name="del" id="del">
            <article class="module width_full">
                <header><h3 class="tabs_involved">Poll Manager</h3>
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
            $sql = "SELECT * FROM polls_options WHERE poll_id = '$row->id' ORDER BY rank DESC";
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
                                        <div><b>From:</b> <?php echo $row->date_from; ?></div>
                                        <div><b>To:</b> <?php echo $row->date_to; ?></div>
                                    </td>
                                    <td>
                                        <div><?php echo $row->date; ?></div>
                                    </td>
                                    <td align="center"><?php echo ( $row->status == 'active') ? 'Active' : 'Inactive'; ?></td>


                                    <td>
                                        <ul>
                                    <?php
                                    foreach ($options as $option) {
                                        ?><li>- <b><?php echo $option['title']; ?></b></li><?php
                                    }
                                    $option_ids = implode('&ids[]=', array_keys($options));
                                    ?>
                                        </ul>
                                        <a href="polls_options.php?poll_id=<?php echo $row->id; ?>&action=edit&ids[]=<?php echo $option_ids; ?>">Edit</a>
                                        - <a href="polls_options.php?poll_id=<?php echo $row->id; ?>">Arrange</a>
                                        - <a href="<?php echo $filename; ?>?<?php echo $queryStr; ?>action=results&poll_id=<?php echo $row->id; ?>">Results</a>
                                    </td>
            <?php if ($AllowImg) { ?>
                                        <td align="center"><?php echo scale_image("../uploads/polls/thumb/" . $row->image, 100); ?></td>
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

function pollRowOption($i, $answer = false, $k = false, $v = '') {
    ?><tr class="tr">
        <td class="option"><input class="input" type="text" name="options[<?php echo $i; ?>][<?php echo $k; ?>]" value="<?php echo textencode($v); ?>" /></td>
        <td class="remove" width="1"></td>
    </tr><?php
                    }

                    function get_poll_results($poll_id) {
                        $options = array();
                        $total = 0;

                        $poll_id = intval($poll_id);

                        $sql = "SELECT count(polls_index.option_id) as total, polls_options.* 
		FROM polls_options
			LEFT JOIN polls_index ON(polls_options.id = polls_index.option_id)
		WHERE polls_options.poll_id='{$poll_id}' 
		GROUP BY polls_options.id
		";

                        $q = mysql_query($sql);

                        if ($q && mysql_num_rows($q)) {
                            while ($row = mysql_fetch_assoc($q)) {
                                $options[$row['id']] = $row;
                                $total += $row['total'];
                            }
                        }

                        foreach ($options as $k => $v) {
                            if ($total == 0) {
                                $percent = 0;
                            } else {
                                $percent = round(($v['total'] / $total) * 100, 2);
                            }

                            $options[$k]['percent'] = $percent;
                            $options[$k]['percent_str'] = $percent . '%';
                        }

                        return array(
                            'total' => $total,
                            'options' => $options,
                        );
                    }

                    function export_poll_results($poll, $results) {

                        include_once 'functions/_export.php';

                        $excel = new export_excel("Poll Result: " . $poll['title']);
                        $excel->addField('title', 'Option');
                        $excel->addField('total', 'Votes');
                        $excel->addField('percent_str', 'Percent');

                        ob_clean();
                        $excel->export($results);
                        exit;
                    }
                    