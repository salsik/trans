<?php require_once "../includes/config.php"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>

	<title>Login to <?php echo PROJECT_NAME;?> Admin Panel</title>

	<meta name="generator" content="reefless_admin" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="loginstyle/css/aStyle.css" type="text/css" rel="stylesheet" />
	<link href="loginstyle/css/login.css" type="text/css" rel="stylesheet" />

<link rel=StyleSheet href="Scripts/golden-admin.css" type="text/css" media=screen>
<script src="Scripts/jquery.js" type="text/javascript" language="javascript"></script>
<script language="javascript">
//  Developed by Roshan Bhattarai 
//  Visit http://roshanbh.com.np for this script and more.
//  This notice MUST stay intact for legal use

$(document).ready(function()
{
	$("#login_form").submit(function()
	{
		//remove all the class add the messagebox classes and start fading
		$("#msgbox").removeClass().addClass('messagebox').text('Validating....').fadeIn(1000);
		//check the username exists or not from ajax
		$.post("ajax_login.php",{ user_name:$('#username').val(),password:$('#password').val(),rand:Math.random() } ,function(data)
        { //alert(data);exit;

           

            if(data=='yes') //if correct login detail
		  {
		  	$("#msgbox").fadeTo(200,0.1,function()  //start fading the messagebox
			{ 
			  //add message and change the class of the box and start fading
			  $(this).html('Logging in.....').addClass('messageboxok').fadeTo(900,1,
              function()
			  { 
			  	 //redirect to secure page
				 document.location='index.php';
			  });
			});
		  }
		  else 
		  {
		  	$("#msgbox").fadeTo(200,0.1,function() //start fading the messagebox
			{ 
			  //add message and change the class of the box and start fading
			  $(this).html('Invalid Credentials...').addClass('messageboxerror').fadeTo(900,1);
			});		
          }
				
        });
 		return false; //not to post the  form physically
	});
});
</script>
<style type="text/css">
.top {
margin-bottom: 15px;
}
.buttondiv {
margin-top: 10px;
}
.messagebox{
	position:absolute;
	width:100px;
	margin-left:0px;
	border:1px solid #c93;
	background:#ffc;
	padding:3px;
}
.messageboxok{
	position:absolute;
	width:auto;
	margin-left:0px;
	border:1px solid #349534;
	background:#C9FFCA;
	padding:3px;
	font-weight:bold;
	color:#008000;
	
}
.messageboxerror{
	position:absolute;
	width:auto;
	margin-left:0px;
	border:1px solid #CC0000;
	background:#F7CBCA;
	padding:3px;
	font-weight:bold;
	color:#CC0000;
}

</style>
<link href="Scripts/golden-admin.css" rel="stylesheet" type="text/css">
</head>
<body>

<!-- top nav bar 
<table class="sTable" cellpadding="0" cellspacing="0">
<tr>
	<td id="tnb_left_part"></td>
	<td id="tnb_center_part">
		<div id="tnb_center_part_l" class="white_10_bold">
			<?php // echo PROJECT_NAME;?> Admin Panel
		</div>
	</td>
	<td id="tnb_right_part"></td>
</tr>
</table>
<!-- top nav bar end -->

<!-- main container -->
<table class="mc_table" cellpadding="0" cellspacing="0">
<!--
<tr>
	<td id="mc_top_left_corner"></td>
	<td id="mc_top_center"></td>
	<td id="mc_top_right_corner"></td>
</tr>
<tr>
	<td id="mc_middle_left_corner"></td>
		-->
	<td  align="center">

		<div id="login_block">
			<div id="login_block_inner">
				<a title="Visit Website" href="../"><div id="logo"></div></a>
				<form method="post" action="" id="login_form">
				<table class="sTable">
				<tr>
					<td class="login_td_left">Username:</td>
					<td align="left"><input class="login_input_text" maxlength="25" type="text" id="username" name="username" value="" /></td>
				</tr>
				<tr>
					<td class="login_td_left">Password:</td>
					<td align="left"><input class="login_input_text" maxlength="25" type="password" id="password" name="password" value="" /></td>
				</tr>
				<tr>
					<td></td>
					<td align="left">
						<input id="submit" class="login_input_button" type="submit" name="Login" value="Log in" />
						<span class="login_load" id="login_load"><span id="msgbox" style="display:none"></span></span>
					</td>
				</tr>
				<tr>
					<td></td>
					<td align="left">
						<div class="login_notify" id="login_notify">Username or Password is wrong</div>
					</td>
				</tr>
								</table>
				</form>
			</div>
		</div>
	</td>
	<!--<td id="mc_middle_right_corner"></td>-->
</tr>
<!--
<tr>
	<td id="mc_bottom_left_corner"></td>
	<td id="mc_bottom_center"></td>
	<td id="mc_bottom_right_corner"></td>
</tr>-->
</table>
<!-- main container end -->

<!--<table class="sTable" cellpadding="0" cellspacing="0">
<tr>
	<td id="cr_td">
		<div class="cr_text">&copy; <?php echo PROJECT_NAME;?></div>
	</td>
</tr>
</table>-->


</body>
</html>