<?php
				
	if( !defined('BASE_DIR') ) die('Access Denied!');
	
	if( !$Account ) {
		redirectURL('login/');
	}
	
	$error = '';
	$msg = '';
	$competitionWall = array();
	$competitionOptions = array();
	
	$strSQL = getCurrentCompetitionSql( $Account );
	$q = mysql_query("$strSQL LIMIT 1");

	if( !$q ) {
		$error = 'Unable to get current competition in our system!';
//		$msg = mysql_error();
	}
	else if( !mysql_num_rows($q) ) {
		$error = 'No Competition available for this month.';
	}
	else {
		$competition = mysql_fetch_assoc($q);
		$competitionImg = ($competition['image']) ? 'uploads/competitions/thumb/' . $competition['image'] : '';
		$competitionDate = date('Y-m-d', $competition['time']);

		$sql = getCompetitionWallSql( $Account, $competition );
		$q = mysql_query("$sql ORDER BY competitions_index.time ASC LIMIT $_CompetitionWallLimit");
		if( $q && mysql_num_rows($q)) {
			while($row = mysql_fetch_assoc($q)) {
				$row['photo'] = ($row['image']) ? 'uploads/doctors/thumb/' . $row['image'] : '';

				$competitionWall[] = $row;
			}
		}

		$q = mysql_query("SELECT * 
			FROM competitions_options 
			WHERE competition_id='{$competition['id']}' 
			ORDER BY rank DESC");

		if( $q && mysql_num_rows($q)) {
			while($row = mysql_fetch_assoc($q)) {
				$competitionOptions[ $row['id'] ] = $row;
			}
		}
	}

	if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
		
		if( $competition && !$competition['option_id'] ) {
			$option_id = intval( $_POST['option'] );
		
			if( !$competitionOptions[ $option_id ] ) {
				$error = 'Selected quiz answer not found!';
			}
			else {
				$q = mysql_query("INSERT INTO competitions_index SET 
					competition_id='{$competition['id']}' 
					, option_id='$option_id'
					, doctor_id='{$Account['id']}'
					, date='".date('Y-m-d')."'
					, time='".time()."'
				");
				if( !$q ) {
					$error = 'Unable to save your answer in our system!';
	//				$error = mysql_error();
				}
				else {
					header("Location: Quiz/?msg=answered");
					exit;
				}
			}
		}
	}

	if( $competition ) {
		$Title = $competition['title'];
	} else {
		$Title = "{$Reseller['title']} Quiz";
	}
	$WRAPPER = 'quiz'; 
	include BASE_DIR . 'common/header.php';
?>
<div id="reseller-quiz" class="news">

	<h2 class="main-title"><?php echo $Reseller['title']; ?> <span>Quiz</span></h2>
	
	<?php if( $msg ) { ?>
		<div class="msgBox"><?php echo $msg; ?></div>
	<?php } else if( $_GET['msg'] == 'answered') { ?>
		<div class="msgBox">Thank you for answering.</div>
	<?php } ?>


	<?php if( !$competition ) { ?>
	<div class="content1">
		<div class="detailsBox">
			<div class="titleBox">
				<span class="title">No Competition available for this month.</span>
			</div>
		</div>
	</div>
	<?php } else { ?>
	<div class="content1">
		<div class="detailsBox">
			<div class="titleBox">
				<span class="title"><?php echo $competition['title']; ?></span>
			</div>
			<div class="description"><?php echo nl2br( $competition['description'] ); ?></div>
				<br /><br />
			&nbsp;
		</div>
	</div>

<div class="com_form">
		<div class="left">
		<form action="Quiz/" method="post" >
			<div class="errorHolder">
				<div class="errors"><?php echo $error; ?></div>
			</div>
			<div class="items listings">
			<?php 
				$disabled = ( $competition['option_id'] ) ? ' disabled="disabled" ' : '';
				foreach($competitionOptions as $option) { 
					$checked = ( $competition['option_id'] == $option['id'] ) ? ' checked="CHECKED" ' : '';
			?>
				<label class="item">
					<span class="title"><input <?php echo $disabled . $checked; ?> type="radio" class="option" name="option" value="<?php echo $option['id']; ?>" /> <?php echo $option['title']; ?></span>
				</label>
			<?php } ?></div>
			<br />
		<?php if( !$competition['option_id'] ) { ?>
			<div>
				<input type="submit" class="submit" name="submit" value="Send" />
				<div class="clear"></div>
			</div>
		<?php } ?>
		</form>
		</div>
		<div class="right">
			&nbsp;
		</div>
		<div class="clear"></div>
</div>


		<?php if( $competition['option_id'] && $competitionWall ) { ?>

		<br />
	<div class="wall">
		<h2 class="main-title">Wall <span>of Fame</span></h2>
	
		<div class="content">
			<div class="items doctors">
		<?php foreach($competitionWall as $doctor) { ?>
				<div class="item">
					<div class="image">
						<img src="<?php echo $doctor['photo']; ?>">
					</div>
					<div class="details">
						<div class="titleBox">
							<span class="title"><?php echo $doctor['full_name']; ?></span>
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
		<?php }?>
			</div>
		</div>
	</div>
		<?php } ?>
	<?php } ?>


	<div class="clear"></div>
</div>
<?php
	include BASE_DIR . 'common/footer.php';
	
