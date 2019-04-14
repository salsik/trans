<?php

	if( !defined('BASE_DIR') ) die('Access Denied!');
	
	if( $Account ) {
		redirectURL('');
	}
	
	define('loginComtroller', true);
	
	$error = array();
	include_once BASE_DIR . 'login.ajax.php';
	$error = array_shift( $error );

	$Title = 'Login';
	$WRAPPER = 'login'; 
	include BASE_DIR . 'common/header.php';
?>
<div class="com_form login">
		<div class="left">
			<form action="#" method="POST" id="LoginForm" class="form bValidator">
				<div class="errorHolder">
					<div class="errors"><?php echo $error; ?></div>
				</div>
			
				<?php $Emsg = 'Email Address'; ?>
				<input data-bvalidator="required,email" type="text" class="input" name="username" placeHolder="<?php echo $Emsg; ?>" value="<?php echo htmlspecialchars($_POST['username'], ENT_QUOTES)?>" />
				
				<?php $Emsg = 'Password'; ?>
				<input data-bvalidator="required" type="password" class="input" name="password" placeHolder="<?php echo $Emsg; ?>" />
				
				<input type="submit" class="submit" name="submit" value="Login" />
				<div class="clear"></div>
			</form>
			<div class="loader">
				<img class="img" src="images/ajax-loader.gif" border="0" />
				<div class="msg"></div>
			</div>
		</div>
		<div class="right">
			&nbsp;
		</div>
		<div class="clear"></div>
</div>
<?php

	include BASE_DIR . 'common/footer.php';