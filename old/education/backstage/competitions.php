<?php

define('THUMB_WIDTH', 482); // 218
define('THUMB_HEIGHT', 317); // 150

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

define('index_table', 'competitions_classes_index');
define('index_table_competitions', 'competitions_classes_index');

include "_top.php";

$limitation = '';
//$limitationStudent = ' true ';
if(isSchool) {
	$limitation = " AND competitions.school_id='".isSchool."' ";
//	$limitationStudent = " students.school_id = '".isSchool."' ";
}
else if( !isAdmin ) {
	die();
}

$AllowAdd = true;
$AllowImg = true;


$fieldsArray = array(
	'title', 'description', 'status',
	'wall_of_fame',
	'month', 'year', 
);

$queryStr = '';

include 'functions/_index_common_filters.php';


//$Student = getDataByID('students', $_GET['student_id'], $limitationStudent);
//if( $Student ) {
//	$queryStr .= "student_id={$Student['id']}&";
//}

//Define the maximum items while listine
$PageSize=10;
//Define the maximum items while adding
$max=1;

if($School) {
	?><h4 class="alert_info">School: <?php echo $School['title']; ?></h4><?php 
}
/*
if($Student) {
	?><h4 class="alert_info">Student: <?php echo $Student['full_name']; ?></h4><?php 
}
*/
switch ($action):

	case "add":
		if( !$AllowAdd ) {
			break;
		}
		$showing = "record";
	break;
	case "edit":
		//Get needed data from the DB
		$strSQL="select * from competitions where id IN(".implode(',',$ids).") $limitation order by rank DESC";
		$objRS=mysql_query($strSQL);
		$i=0;
		while ($row=mysql_fetch_object($objRS)){
			foreach($fieldsArray as $field) {
				${$field}[$i] = $row->$field;
			}
		
			$class_ids[$i] = array();
			$q = mysql_query("SELECT * FROM `".index_table."` WHERE index_id='{$row->id}' ");
			if($q && mysql_num_rows($q)) {
				while($indx = mysql_fetch_assoc($q)) {
					$class_ids[$i][] = $indx['class_id'];
				}
			}

			$school_id[$i] = $row->school_id;
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
	case "addexe":
		if( !$AllowAdd ) {
			break;
		}
	case "editexe":
		//Get new data from the FORM
		foreach($fieldsArray as $field) {
			${$field} = $_POST[$field];
		}
		$school_id = $_POST['school_id'];
		$class_ids = $_POST['class_ids'];
		$options = $_POST['options'];
		$answer = $_POST['answer'];

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

			$sql = array();
			foreach($fieldsArray as $field) {
				$sql[$field] = " `$field`='".sqlencode(trime(${$field}[$i]))."', ";
			}

			if( $action == 'addexe' && isSchool) {
				$sql['add_by_id']= " `add_by_id`='".isSchool."', ";
			}

			if( empty($title[$i]) ) {
				$Errors[] = "Missing Title!!";
//			} else if( empty($description[$i]) ) {
//				$Errors[] = "Missing Description!!";
			}
			
			$sqlCompetition = array();
			$sqlCompetition['month'] = intval( $month[$i] );
			$sqlCompetition['year'] = intval( $year[$i] );

//			if( $action == 'addexe' ) {
				if( isSchool) {
					$sql['school_id']= " `school_id`='".isSchool."', ";
					$sqlCompetition['school_id'] = isSchool;
				} else {
					$_school = getDataByID('schools', $school_id[$i]);
	
					if( !$_school) {
						$Errors[] = "Missing School!!";
					} else {
						$sql['school_id']= " `school_id`='{$_school['id']}', ";
						$sqlCompetition['school_id'] = $_school['id'];
					}
				}
//			}
			
			$year[$i] = intval( $year[$i] );
			$month[$i] = intval( $month[$i] );
			
			if( $year[$i] < date('Y')) {
				$Errors[] = "Invalid Year!!";
			} else if( $month[$i] < 1 || $month[$i] > 12) {
				$Errors[] = "Invalid Month!!";
			}
			
			if( !$Errors ) {
				$mktime= mktime(0,0,0, $month[$i], 1, $year[$i]);
				$sql['month_time']= " `month_time`='{$mktime}', ";
			}

			if( !is_array( $options[$i])) {
				$options[$i] = array();
			}
//var_dump($options);
//$Errors[] = "Missing Options!!";
//			$answer[$i] = (array) $answer[$i];
			if( $action == 'addexe') {
				$answered = 0;
				$answers = array();
				foreach($options[$i] as $k=>$option) {
					if( !empty( $option )) {
						$answers[$k] = array('title' => $option);

						if( $answer[$i] == $k) {
							$answers[$k]['selected'] = true;
							$answered = true;
						}
					}
				}
			
				if( !count($answers) ) {
					$Errors[] = "Missing Options!!";
				} else if( !$answered ) {
					$Errors[] = "Missing the correct answer!!";
				}
			}	
			
			include 'functions/_index_check_all.php';
			
			$strSQL = "competitions set " . implode('', $sql);
			
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/competitions/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){

							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/competitions/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT);
							$Rimage->save('../uploads/competitions/thumb/'.$file1[$i]);							
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
					if ( $result=file_upload('image','file1'.$i,'../uploads/competitions/',$errorMsg[$j])){
						 $file1[$i]=$result['name'];
						if(empty($errorMsg[$j])){
							
							$Rimage = new SimpleImage();
							$Rimage->load('../uploads/competitions/'.$file1[$i]);
							if( $Rimage->getWidth() > THUMB_WIDTH ) {
								$Rimage->resizeToWidth( THUMB_WIDTH );
							}
//							$Rimage->resize( THUMB_WIDTH, THUMB_HEIGHT );
							$Rimage->save('../uploads/competitions/thumb/'.$file1[$i]);							
						}							 
				    }
					if(!empty($errorMsg[$j])){// Upload Error
						$oldrecord[$j]=$i;
						$j++;
						$flag=0;
					}else{
						
						$q=mysql_query("SELECT max(rank) as max FROM competitions");
						$r = mysql_fetch_object($q);
						
						$strSQL ="insert into ".$strSQL." 
							image='".sqlencode(trime($file1[$i]))."'
							, rank='".($r->max+1)."'
							, date='".date('Y-m-d')."'
							, time='".time()."' ";
					}
				}
			endif;
			if($flag){
				$q = mysql_query($strSQL);
				if( !$q )
				{
					if( mysql_errno() == 1062) {
						$errorMsg[$j] = 'There is a competition in that month!!';
					} else {
						$errorMsg[$j] = 'Something went wrong!';
//						$errorMsg[$j] = mysql_error();
					}
					$oldrecord[$j]=$i;
					$j++;
					$flag=0;
				} else {
					if($action == 'addexe') {
						$competition_id = mysql_insert_id();
					} else {
						$competition_id = intval( $ids[$i] );
					}

					$index_id = $competition_id;
					include('functions/_index_create_all.php');

					if( $action == 'addexe') {
//						$competition_id = mysql_insert_id();

						$answers = array_reverse( $answers );

						$q=mysql_query("SELECT max(rank) as max FROM competitions_options");
						$r = mysql_fetch_object($q);
						$r->max++;
//var_dump($answers);
						foreach($answers as $k=>$option) {
							
							$q = mysql_query("INSERT INTO competitions_options SET 
								competition_id = '$competition_id'
								, `title`='".sqlencode(trime( $option['title'] ))."'
								, rank='".($r->max++)."'
								, date='".date('Y-m-d')."'
								, time='".time()."'
							");

							if( $q ) {
								$option_id = mysql_insert_id();

								if( $option['selected']) {
									mysql_query("UPDATE competitions SET answer_id = '$option_id' WHERE id='$competition_id' LIMIT 1");
								}
							}
//							else {
//								echo mysql_error();
//							}
						}
					}
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

		$strSQL="select * from competitions where id IN(".implode(',',$ids).") $limitation order by rank DESC";
		$objRS=mysql_query($strSQL);
		while ($row=mysql_fetch_object($objRS))
		{

			if($row->image != '') {
				@unlink('../uploads/competitions/'.$row->image);	
				@unlink('../uploads/competitions/thumb/'.$row->image);	
			}

			$strSQL="DELETE FROM competitions WHERE id = '".$row->id ."' LIMIT 1";
			if(!mysql_query($strSQL) ) {
				if(empty($errorMsg)) {
					$errorMsg="Some Records didn't affected!!";
				}
			} else {
				$strSQL="DELETE FROM competitions_options WHERE competition_id = '".$row->id ."' ";
				mysql_query($strSQL);
				$strSQL="DELETE FROM competitions_index WHERE competition_id = '".$row->id ."' ";
				mysql_query($strSQL);
				
				mysql_query("DELETE  FROM `".index_table."` WHERE index_id='{$row->id}' ");
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

		include 'functions/_auto_complete.php';
?>

<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>" method="post" name="myform" enctype="multipart/form-data">
<input type="hidden" name="action" value="<? echo $action; ?>exe"> 
<article class="module width_full">
	<header><h3>Quiz: <?php echo ucwords($action); ?> Record
		<input type="submit" value="Save" class="alt_btn">
		<input type="button" value="Cancel" onclick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>'">
		</h3></header>

		<?php for($i=0;$i<$max;$i++){ ?>
<?php 
		$_competition = array();
		if( $action == 'edit') {
			$_competition = getDataByID('competitions', $ids[$i], " TRUE $limitation ");
		}
?>
		<input type="hidden" name="ids[]" value="<? echo $ids[$oldrecord[$i]]; ?>">
		<?php if(!empty($msg)) { ?>
			<h4 class="alert_success"><?php echo $msg; ?></h4>
		<?php } else if(!empty($errorMsg[$i])) { ?>
			<h4 class="alert_error"><?php echo $errorMsg[$i]; ?></h4>
		<?php } else if(!empty($warningMsg[$i])) { ?>
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
					<input type="text" name="title[]" value="<? echo textencode($title[$oldrecord[$i]]); ?>" />
				</fieldset>

				<fieldset>
					<label>Description</label>
					<textarea name="description[]" rows="12" ><? echo textencode($description[$oldrecord[$i]]); ?></textarea>
				</fieldset>

		<?php if( !isSchool ) { ?>
				<fieldset>
					<label>School</label>

					<select name="school_id[<?php echo $i; ?>]" class="school_id" >
						<option value="" >-- Select --</option>
						<?php 
							$_index_school = 0;
							$sid = nor($school_id[$oldrecord[$i]], $School['id']);
							foreach ($Schools as $school) {
								$Selected = ( $school['id'] == $sid ) ? ' selected="selected" ' : '';
								if( $Selected ) {
									$_index_school = $school['id'];
								}
								?><option value="<?php echo $school['id'];?>" <?php echo $Selected; ?> ><?php echo $school['title'];?></option><?php 
							}
						?>
					</select>
				</fieldset>
			<?php } ?>
				<fieldset>
					<label>Classes</label>
					<div style="margin-left: 210px;">
						<?php include('functions/_index_table.php'); ?>
					</div>
				</fieldset>

				<fieldset>
					<label>Wall Of Fame</label>
					<input type="text" name="wall_of_fame[]" value="<? echo textencode($wall_of_fame[$oldrecord[$i]]); ?>" />
				</fieldset>


				<fieldset>
					<label>During</label>
					<table width="" border="0" cellpadding="5" cellspacing="0">
						<tr>
							<td>
								<b>Year:</b> <select name="year[]">
									<option value="" >--</option>
							<?php
								if( $action == 'add' && !isset($year[$oldrecord[$i]])) {
									$year[$oldrecord[$i]] = date('Y');
								}
								
								for($x=2013; $x<date('Y')+5; $x++) {
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
								if( $action == 'add' && !isset($month[$oldrecord[$i]])) {
									$month[$oldrecord[$i]] = date('m');
								}
								
								for($x=1; $x<13; $x++) {
									$selected = selected($x, $month[$oldrecord[$i]]);
									?><option value="<?php echo $x; ?>" <?php echo $selected; ?>><?php echo ($x<10)?'0'.$x : $x; ?></option><?php 
								}
							?>
								</select>
							</td>
						</tr>
					</table>
				</fieldset>
		<?php if( $action == 'add') { ?>
		<?php 
//		var_dump($options);
//		var_dump($answer);
//		
		?>
				<fieldset>
					<label>Answers</label>
					<table class="competitions_options" data-i="<?php echo $i; ?>" width="60%" border="0" cellpadding="5" cellspacing="0">
				<?php 
		
					if( !is_array( $options[$oldrecord[$i]] )) {
						$options[$oldrecord[$i]] = array();
					}
					
					$maxK = 0;
					$newK = 0;
					foreach($options[$oldrecord[$i]] as $k=>$v) {
						if( !empty( $v )) {
							$newK++;
							competitionRowOption($i, $answer[$oldrecord[$i]]==$k, $newK, $v);//$maxK+1
							$maxK = max($maxK, $k);
						}
					}
					
					competitionRowOption($i);

				?>
					</table>
				</fieldset>
		<?php } ?>
<?php if( $AllowImg ) { ?>
				<fieldset>
					<label>Image</label>
					<?php file_field('file1'.$i,'../uploads/competitions/',$file1[$oldrecord[$i]]);?>
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
$('.competitions_options').each(function(){
	var table = $(this);
	table.tableIndex = $('.tr', table).length + 1;
	table.i = $(table).data('i');
	competitionRowOptionFocus(table, table);
	_buildRemoveBtn(table);
});





function competitionRowOptionFocus(table, Element) {
	$('.option input', Element).focus(function(){
		var index = $('.option input', table).index(this);
		if( index+1 == $('.option input', table).length ) {
			var tr = $('.tr:last', table).clone();

			table.tableIndex++;
	
			tr.find('.option input').attr('name', 'options['+table.i+']['+table.tableIndex+']').val('');
			tr.find('.answer input').prop('checked', false).val(table.tableIndex);//.attr('name', 'answer['+table.i+']')
	
			table.append(tr);

			competitionRowOptionFocus(table, tr);
			_buildRemoveBtn(table);
		}
	});
}

function _buildRemoveBtn(table){
	table.find('.remove').empty();
	if( table.find('.tr').length > 1 ) {
		table.find('.remove').each(function(){
			$('<img src="images/delete.jpg" />').click(function(){
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


<?php if(!empty($msg)) { ?>
	<h4 class="alert_success"><?php echo $msg; ?></h4>
<?php } else if(!empty($errorMsg)) { ?>
	<h4 class="alert_error"><?php echo $errorMsg; ?></h4>
<?php } ?>


<div class="alert_browse">
	<form action="<? echo $filename; ?>" method="GET" >
		Find: <input name="keyword" value="<? echo my_htmlspecialchars($keyword, ENT_QUOTES); ?>" size="30" />
		Status: <select name="status">
			<option value="" >== Any ==</option>
			<option value="active" <?php echo selected($_GET['status'], 'active'); ?>>Active</option>
			<option value="inactive" <?php echo selected($_GET['status'], 'inactive'); ?>>Inactive</option>
		</select>
		

	<?php 
		include 'functions/_index_filter.php';
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
			
			$sql[] = " (competitions.title LIKE '%$keywordSQL%' OR competitions.description LIKE '%$keywordSQL%' ) ";
		}

		if(isSchool) {
			$sql[] = " competitions.school_id = '". isSchool ."' ";
		} else if($School) {
			$sql[] = " competitions.school_id = '{$School['id']}' ";
		}

		switch($_GET['status']) {
			case 'active':
				$sql[] = " competitions.status='active' ";
				break;
			case 'inactive':
//				$sql[] = " competitions.status='' ";
				$sql[] = " competitions.status<>'active' ";
				break;
		}

		if( $Class ) {
			$sql[] = " classes.id='{$Class['id']}' ";
		}
		
		$where = ( $sql ) ? " WHERE " . implode(' AND ', $sql ) : '';

//		$strSQL="SELECT competitions.*, schools.title as school_title
//		FROM competitions 
//		LEFT JOIN schools ON (schools.id=competitions.school_id)
//		$where";

		$strSQL="SELECT competitions.*, schools.title as school_title
		, GROUP_CONCAT( DISTINCT classes.title SEPARATOR '<~~>' ) as classes_titles
		, GROUP_CONCAT( DISTINCT competitions_classes_index.class_id SEPARATOR '<~~>' ) as classes_ids
		FROM competitions
		LEFT JOIN schools ON (schools.id=competitions.school_id)
		LEFT OUTER JOIN competitions_classes_index ON (competitions.id = competitions_classes_index.index_id)
		LEFT OUTER JOIN classes ON (competitions_classes_index.class_id = classes.id)
		
		$where
		GROUP BY competitions.id
		";

		
		if( $action == 'export' ) {
			include 'functions/_export.php';
			
			$excel = new export_excel("Competitions");
			$excel->addField('title', 'Title');
			$excel->addField('description', 'Description');
			if( !isSchool ) {
				$excel->addField('school_title', 'School');
			}

			$excel->addField('classes_titles', 'Classes', 'style_classes_titles', '--row--');

			$excel->addField('date', 'Date');
			
			$excel->addField('status', 'Status', 'ucwords');
			$excel->export( "$strSQL ORDER BY competitions.rank DESC");
			exit;
		}
		$objRS=mysql_query($strSQL);
//echo "{$strSQL} " . mysql_error();
		$total=@mysql_num_rows($objRS);
		$strSQL=makePages ($strSQL,$PageSize,$p, 'competitions.rank DESC');
		$objRS=mysql_query($strSQL);
		$count_all = @mysql_num_rows($objRS);
		
?>


<form action="<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=delete" method="post" name="del" id="del">
<article class="module width_full">
	<header><h3 class="tabs_involved">Quiz Manager</h3>
	<ul class="tabs">
<?php if( $AllowAdd ) { ?>
   		<li><a href="#add" onClick="window.location='<? echo $filename; ?>?<?php echo $queryStr; ?>p=<?=$p?>&action=add'" >Add New</a></li>
<?php } ?>
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
    			<th>Title</th> 
    	<?php if( !isSchool ) { ?>
    			<th>School</th> 
    	<?php } ?>
    			<th>Classes</th>
                <th>During</th>

                <th width="1">Added</th>
                <th width="1">Status</th>

                <th>Answers</th>
<?php if( $AllowImg ) { ?>
                <th width="1">Image</th>
<?php } ?>
			</tr> 
		</thead> 
		<tbody id="trContainer"> 

	<?php while ($row=mysql_fetch_object($objRS)){ ?>
<?php 

	$options = array();
	$sql = "SELECT * FROM competitions_options WHERE competition_id = '$row->id' ORDER BY rank DESC";
	$q = mysql_query( $sql );
	if( $q && mysql_num_rows( $q )) {
		while( $r = mysql_fetch_assoc( $q ) ) {
			$options[$r['id']] = $r;
		}
	}
?>
    <tr id="tr_<? echo $row->id; ?>">
		<td align="right"><input type="checkbox" name="ids[]" value="<? echo $row->id; ?>"></td>
		<td>
			<div><b><? echo $row->title ?></b></div>
			<div><?php if($row->description !=''){ echo summarize($row->description, 20); }?></div>
			<div>
				<a href="<? echo $filename; ?>?<?php echo $queryStr; ?>action=edit&ids[]=<? echo $row->id; ?>">Edit</a>
			</div>
		</td>
	<?php if( !isSchool ) { ?>
		<td>
			<div><b><? echo $row->school_title; ?></b></div>
		</td>
	<?php } ?>
		<td><?php echo style_classes_titles( $row->classes_titles, $row); ?></td>
	
		<td>
			<div><b><?php echo $row->month; ?> / <?php echo $row->year; ?></b></div>
		</td>
		<td>
			<div><? echo $row->date; ?></div>
		</td>
		<td align="center"><?php echo ( $row->status == 'active') ? 'Active' : 'Inactive'; ?></td>
		
		
		<td>
			<ul>
	<?php
		foreach($options as $option) {
			if( $option['id'] == $row->answer_id) {
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
	<?php if( $AllowImg ) { ?>
		<td align="center"><?php echo scale_image("../uploads/competitions/thumb/". $row->image, 100); ?></td>
	<?php } ?>
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



function competitionRowOption($i, $answer=false, $newK=false, $v='') {
	?><tr class="tr">
		<td class="answer" style="vertical-align: middle;" width="1"><input type="radio" name="answer[<?php echo $i; ?>]" value="<?php echo $newK; ?>" <?php echo checked($answer, true); ?> /></td>
		<td class="option"><input class="input" type="text" name="options[<?php echo $i; ?>][<?php echo $newK; ?>]" value="<? echo textencode($v); ?>" /></td>
		<td class="remove" width="1"></td>
	</tr><?php 
}


