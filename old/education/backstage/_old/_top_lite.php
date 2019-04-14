<?php

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if( !defined( 'RMSTop' )){
	include "_top.php";
}
ob_clean();

?><!doctype html>
<html lang="en">

<head>
	<meta charset="<?php echo $charset;?>"/>
	<title><?php echo PROJECT_NAME;?> Dashboard</title>

	<link rel="stylesheet" href="css/layout.css" type="text/css" />
	<link rel="stylesheet" href="css/print.css" type="text/css" media="print" />
	
	<!--[if lt IE 9]>
	<link rel="stylesheet" href="css/ie.css" type="text/css" />
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
<script src="js/jquery-1.8.0.min.js" type="text/javascript"></script>
<script src="js/jquery-ui-1.10.3.custom.min.js" type="text/javascript"></script>
<script src="js/jquery.multiselect.min.js" type="text/javascript"></script>
<script src="js/jquery.multiselect.filter.js" type="text/javascript"></script>
<link rel="stylesheet" href="css/ui-lightness/jquery-ui-1.10.3.custom.min.css" type="text/css" />
<link rel="stylesheet" href="css/jquery.multiselect.filter.css" type="text/css" />


<link rel="stylesheet" href="css/colorpicker.css" type="text/css" />

<script src="js/jquery-ui-timepicker-addon.js" type="text/javascript"></script>
<link rel="stylesheet" href="css/jquery-ui-timepicker-addon.css" type="text/css" />

<link rel="stylesheet" href="css/colorpicker.css" type="text/css" />

	<script src="js/hideshow.js" type="text/javascript"></script>
	<script src="js/jquery.tablesorter.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="js/jquery.equalHeight.js"></script>
	<script type="text/javascript" src="js/colorpicker.js"></script>
    <script type="text/javascript" src="js/eye.js"></script>
    <script type="text/javascript" src="js/utils.js"></script>
    <script type="text/javascript" src="js/layout.js?ver=1.0.2"></script>
    
	<script type="text/javascript">
	$(document).ready(function(){ 

		var my_height = $(window).height()-60; 
		$("#sidebar").css("min-height",my_height+"px");
		
	
		$(".tablesorter").tablesorter(); 
		$(".datepicker").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: "yy-mm-dd"
		});
		$(".datepicker2").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: "dd/mm/yy"
		});
		$(".timepicker").timepicker({
//			controlType: 'select',
			timeFormat: 'hh:mm tt',
			defaultValue: '12:00 PM'
		});

		$('.lightBox').click(function(){
			newwindow=window.open(this.href,'_blank', "status=no,resizable=yes,toolbar=no,menubar=no,scrollbars=yes,location=no,directories=yes");
			if (window.focus) {newwindow.focus()}
			return false;
		});
		$('._top').click(function(){
			top.window.location.href = this.href;
			return false;
		});
	});

    </script>
<style type="text/css">
#trContainer td {
	vertical-align: top;
}
.inline fieldset input.colorPicker {
	width: 50px;
}
fieldset table label {
	height: auto;
}
.inline fieldset table input[type="text"], .inline fieldset table input[type="password"], .inline fieldset table textarea {
	margin-left: 0;
}
</style>
<?php require "_javascript.php"; ?>
<style type="text/css">
.alert_browse label {
	min-width: 90px;
	text-indent: 0;
	display: inline-block;
	text-align: right;
}
#ui-datepicker-div {
	z-index: 1010;
}

.input_short {
	width: 150px !important;
}
.input_mid {
	width: 402px !important;
}

.field label {
	width: auto !important;
	clear: both !important;
}

.leftBox {
	float: left;
	width: 670px;
}
.leftBox label{
	height: auto;
}
.leftBox label.list{
	width: auto;
	margin: 0;
	float: none;
}

.leftBox label .input_short,
.leftBox label select{
	margin: 0;
	float: none;
}
</style>
</head>
<body>
	<header id="header">
		<hgroup>
		<h1 class="site_title"><img style="margin-top: 6px;display: block;margin-left: 10px;" src="images/logo.png"/></h1>
		</hgroup>
	</header> <!-- end of header bar -->
	<section id="secondary_bar">
	</section><!-- end of secondary bar -->
<div style="clear:both;"></div>
	<section id="main" class="column" style="width: 100%">

