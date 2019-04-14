<?php

	if( !defined('BASE_DIR') ) die('Access Denied!');
	
	if( !$Account ) {
		redirectURL('login/');
	}
	
$perpage = 8;
$perpage_news = 4;

$_page = intval( $_GET['page']);
if( $_page < 1) {
	$_page = 1;
}

$id = intval( $_GET['id']);

$action = strtolower( $_GET['action'] );

if( $action != 'ask') {
	if( $id > 0 ) {
		$strSQL = getQuestionsSql($Account, $id);
		$q = mysql_query("$strSQL LIMIT 1");
		if( $q && mysql_num_rows( $q )) {
			$question = mysql_fetch_assoc( $q );
			
			
			$questionLink = "Contact/" .cleanTitleURL( $question['title'] ). "/{$question['id']}/";
		}
	}
}

if( $_SERVER['REQUEST_METHOD'] == 'POST') {
	if( $action == 'ask') {
		if( empty($_POST['question_title']) ) {
			$error = 'Missing Question Title!';
		}
		else if( empty($_POST['question_description']) ) {
			$error = 'Missing Question Details!';
		}
		else {
			$q=mysql_query("SELECT max(rank) as max FROM questions");
			$r = mysql_fetch_object($q);

			$q = mysql_query("INSERT INTO questions SET 
				reseller_id = '{$Reseller['id']}'
				, doctor_id = '{$Account['id']}'
				, title='".mysql_real_escape_string( $_POST['question_title'] )."'
				, description='".mysql_real_escape_string( $_POST['question_description'] )."'
				, status='active'
				, rank='".($r->max+1)."'
				, date='".date('Y-m-d')."'
				, time='".time()."'
			");
			if( !$q ) {
				$error = 'Unable to insert question in our system!';
//				$error = mysql_error();
			}
			else {
				$_insert_id = mysql_insert_id();
				
				$questionLink = "Contact/" .cleanTitleURL( $_POST['question_title'] ). "/{$_insert_id}/";
				
				header("Location: {$questionLink}?msg=added");
				exit;
			}
		}
	}
	else if( $question ) {
		if( empty($_POST['question_reply']) ) {
			$error = 'Missing Reply Details!';
		}
		else {
			$q=mysql_query("SELECT max(rank) as max FROM questions_replies");
			$r = mysql_fetch_object($q);

			$q = mysql_query("INSERT INTO questions_replies SET 
				question_id = '{$question['id']}'
				, `from` = ''
				, description='".mysql_real_escape_string( $_POST['question_reply'] )."'
				, rank='".($r->max+1)."'
				, date='".date('Y-m-d')."'
				, time='".time()."'
			");
			if( !$q ) {
				$error = 'Unable to insert question\'s reply in our system!';
//				$error = mysql_error();
			}
			else {
				header("Location: {$questionLink}?msg=updated");
				exit;
			}
		}
		
	}
}




	if( $question ) {
		$Title = $question['title'];
	} else {
		$Title = "Contact {$Reseller['title']}";
	}
	$WRAPPER = 'contact'; 
	include BASE_DIR . 'common/header.php';
?>
<div id="reseller-contact" class="news">

<?php if( $action == 'ask' ) { ?>
	<h2 class="main-title"><span>Contact</span> <?php echo $Reseller['title']; ?> / <span>Ask Question</span></h2>

<div class="clearfix">
	<div style="float: right;">
		<a href="Contact/">Questions</a>
	</div>
</div>

<div class="com_form">
		<div class="left">
			<form action="Contact/ask/" method="POST" class="form bValidator">
				<div class="errorHolder">
					<div class="errors"><?php echo $error; ?></div>
				</div>
			
				<?php $Emsg = 'Title'; ?>
				<input data-bvalidator="required" type="text" class="input" name="question_title" placeHolder="<?php echo $Emsg; ?>" value="<?php echo htmlspecialchars($_POST['question_title'], ENT_QUOTES)?>" />
				
				<?php $Emsg = 'Details'; ?>
				<textarea data-bvalidator="required" class="textarea" name="question_description" placeHolder="<?php echo $Emsg; ?>" ><?php echo htmlspecialchars($_POST['question_description'], ENT_QUOTES)?></textarea>
				
				<input type="submit" class="submit" name="submit" value="Send Question" />
				<div class="clear"></div>
			</form>
		</div>
		<div class="right">
			&nbsp;
		</div>
		<div class="clear"></div>
</div>

<?php } else if( $question ) { ?>
	<h2 class="main-title"><span>Contact</span> <?php echo $Reseller['title']; ?> / <span><?php echo $question['title']; ?></span></h2>

<div class="clearfix">
	<div style="float: right;">
		<a href="Contact/ask">Add question</a>
	</div>
</div>

<?php if($_GET['msg'] == 'added') { ?>
	<div class="msgBox">Your question added successfully!</div>
<?php } else if( $_GET['msg'] == 'updated' ) { ?>
	<div class="msgBox">Your question updated successfully!</div>
<?php } ?>

		<div class="detailsBox">
			<div class="titleBox">
				<span class="date"><?php echo date('Y-m-d', $question['time']); ?></span>
			</div>
			<div class="image">
				<img src="uploads/questions/<?php echo $question['image']; ?>" border="0" class="maxWidth">
			</div>
			<div class="description"><?php echo nl2br( $question['description'] ); ?></div>

			<div class="more">
				Replies
			</div>
		<?php 
			$q = mysql_query("SELECT * 
				FROM questions_replies 
				WHERE question_id='{$question['id']}' 
				ORDER BY time ASC
				");

			if( $q && mysql_num_rows($q)) {
		?>
			<div class="listings replies">
				<div class="items">
		<?php while($row = mysql_fetch_assoc($q)) { ?>
					<div class="item">
						<div class="details">
						<?php 
							if( $row['from'] == 'reseller') { 
								$from = getFromHolder($ResellersHolder, 'resellers', $row['reseller_id'], 'title');
							}
							else {
								$from = $Account['full_name'];
							}
						?>
							<div class="titleBox">
								<span class="title"><b><?php echo $from; ?></b> @<?php echo date('h:ia Y-m-d', $row['time']); ?></span>
							</div>
							<div class="description"><?php echo nl2br( $row['description'] ); ?></div>
						</div>
		
						<div class="clearfix"></div>
					</div>
		<?php } ?>
				</div>
			</div>
		<?php 
			}
		?>
		<div class="com_form">
			<div class="left">
				<form action="<?php echo $questionLink;?>" method="POST" class="form bValidator">
					<div class="errorHolder">
						<div class="errors"><?php echo $error; ?></div>
					</div>
					
					<?php $Emsg = 'Reply'; ?>
					<textarea data-bvalidator="required" class="textarea" name="question_reply" placeHolder="<?php echo $Emsg; ?>" ><?php echo htmlspecialchars($_POST['question_reply'], ENT_QUOTES)?></textarea>
					
					<input type="submit" class="submit" name="submit" value="Send Reply" />
					<div class="clear"></div>
				</form>
			</div>
			<div class="right">
				&nbsp;
			</div>
			<div class="clear"></div>
		</div>
		</div>
<?php } else { ?>
	<h2 class="main-title"><span>Contact</span> <?php echo $Reseller['title']; ?></h2>

<div class="clearfix">
	<div style="float: right;">
		<a href="Contact/">Questions</a>
		 - <a href="Contact/ask">Add question</a>
	</div>
</div>

	<div class="listings">
		<div class="items">
	<?php 
	
		$strSQL = getQuestionsSql($Account);

		$total = 0;
		$q = mysql_query( $strSQL );
		if( $q && mysql_num_rows($q)) {
			$total = mysql_num_rows( $q );
		}

		$pager = pager($_page, $total, $perpage);
		
		$offset = $pager['offset'];
		$limit = $pager['perpage'];
		
		$pagination = pager_link($pager, "Contact/page-");
	
		$q = mysql_query( "$strSQL ORDER BY questions.rank DESC LIMIT $offset, $limit " );
		if( $q && mysql_num_rows( $q )) {
			while( $row = mysql_fetch_object( $q )) {
				$link = "Contact/" .cleanTitleURL( $row->title ). "/{$row->id}";
				
				$last_update = 'Never';
				if( $row->replies > 0 ) {
					$last_update = date('Y-m-d h:ia', $row->last_reply_time);
					
					if( $row->last_reply_from != 'reseller') {
						$last_update .= " <b>By {$Account['full_name']} </b>";
					}
					$last_update .= ' | <b>Replies:</b> ' . $row->replies;
				}
				
				?>
				<div class="item">
					<div class="details">
						<div class="titleBox">
							<span class="title"><a href="<?php echo $link; ?>"><?php echo $row->title; ?></a></span>
							<span class="date"><?php echo date('Y-m-d', $row->time); ?></span>
						</div>
						<div class="description"><?php echo summarize_my_text($row->description, 16); ?></div>
						<div class="info"><b>Last Update:</b> <?php echo $last_update; ?></div>
					</div>
	
					<div class="clearfix"></div>
				</div>
				<?php 
			}
		}
	?>
			<div class="clear"></div>
		</div>
		
	<?php 
		if( $pagination ) {
			echo $pagination;
		}
	?>
	</div>
<?php } ?>
	<div class="clear"></div>
</div>
<?php
	include BASE_DIR . 'common/footer.php';
	
	function getFromHolder(&$Holder, $table, $id, $field = false) {
		if( !isset( $Holder[$table][$id] )) {
			$Holder[$table][$id] = getDataByID($table, $id);
		}
		
		if( $field ) {
			return $Holder[$table][$id][$field];
		}
		else {
			return $Holder[$table][$id];
		}
	}
	
	