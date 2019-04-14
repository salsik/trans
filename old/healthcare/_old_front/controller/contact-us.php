<?php

	if( !defined('BASE_DIR') ) die('Access Denied!');

//	$MapLink ="https://maps.google.com/maps/ms?t=h&amp;msa=0&amp;msid=213474457324656347812.0004dc083376419f0a015&amp;ie=UTF8&amp;ll=33.889406,35.58027&amp;spn=0.002333,0.005284&amp;output=embed";

	$Title = 'Contact Us';
	$WRAPPER = 'contact-us'; 
	include BASE_DIR . 'common/header.php';
?>
<div class="com_form contactus">
		<div class="left">
			<form action="#" method="POST" id="ContactUs" class="form bValidator">
				<?php $Emsg = 'Name'; ?>
				<input data-bvalidator="required" type="text" class="input" name="name" placeHolder="<?php echo $Emsg; ?>" />
				
				<?php $Emsg = 'Company Name'; ?>
				<input data-bvalidator="required" type="text" class="input" name="company_name" placeHolder="<?php echo $Emsg; ?>" />
				
				<?php $Emsg = 'Phone'; ?>
				<input data-bvalidator="required,number" type="text" class="input" name="phone" placeHolder="<?php echo $Emsg; ?>" />
				
				<?php $Emsg = 'Email'; ?>
				<input data-bvalidator="required,email" type="text" class="input" name="email" placeHolder="<?php echo $Emsg; ?>" />
				
				<?php $Emsg = 'How can we help you'; ?>
				<textarea data-bvalidator="required" class="textarea" name="message" placeHolder="<?php echo $Emsg; ?>" ></textarea>

				<input type="submit" class="submit" name="submit" value="Submit" />
				<div class="clear"></div>
			</form>
			<div class="loader">
				<img class="img" src="images/ajax-loader.gif" border="0" />
				<div class="msg"></div>
			</div>
		</div>
		<div class="right">
	<?php if( $MapLink ) { ?>
			<iframe width="406" height="270" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="<?php echo $MapLink; ?>"></iframe>
	<?php } ?>
		</div>
		<div class="clear"></div>

		<div class="left">
			<table class="info" width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="title">Phone:</td>
				<td>+961 9 9444441</td>
			</tr>
			<tr>
				<td class="title">Mobile:</td>
				<td>+961 71 417872</td>
			</tr>
			<tr>
				<td class="title">Office:</td>
				<td>Kassouba Street, Byblos, Lebanon</td>
			</tr>
			</table>
		</div>
		<div class="right">
			<table class="info" width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="title">E-mail:</td>
				<td>info@advancesandmore.com</td>
			</tr>
			</table>
		</div>
		<div class="clear"></div>
</div>
<?php

	include BASE_DIR . 'common/footer.php';