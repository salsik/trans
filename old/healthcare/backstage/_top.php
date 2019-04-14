<?php ob_start(); ?><!--<?php 
session_start();
require_once "../includes/config.php"; 
require_once "../includes/conn.php";
require_once "../_vars.php";
require_once "../includes/module.php";
require_once "../includes/functions_login_session.php";

$loginError = '';
$Admin = is_login( $loginError );
if( $loginError )
{
	header('location: login.php?'.$loginError.'=1');
	exit;
}
require "_admins.php";

define( 'IS_ADMIN', true );

//user->
require "../includes/mysql-to-excel.php";
require "../includes/image.php";
require "../includes/datetime.php";
require "../includes/pages.php";
require "../includes/files.php";

require "_functions.php";
require "_cron.php";

$action=getHTTP('action');
$menu=getHTTP('menu');
$sub=getHTTP('sub');
$keyword=getHTTP('keyword');
$p=getHTTP('p');
$id=getHTTP('id');
$ids=$_POST['ids'];
if (!is_array($ids)) $ids=$_GET['ids'];
if (!is_array($ids)) $ids=array();
$ids = array_map('intval', $ids);

$charset = 'UTF-8';
if( defined('charset') )
{
	$charset = charset;
}
@header('Content-Type: text/html; charset=' . $charset);

?>-->
<!doctype html>
<html lang="en">

<head>
	<meta charset="<?php echo $charset;?>"/>
	<title><?php echo PROJECT_NAME;?> Dashboard</title>

	<link rel="stylesheet" href="css/layout.css" type="text/css" />
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

<script type="text/javascript" src="js/fancybox/jquery.fancybox.js?v=2.0.6"></script>
<script type="text/javascript" src="js/fancybox/helpers/jquery.fancybox-media.js?v=1.0.0"></script>

<link rel="stylesheet" type="text/css" href="js/fancybox/jquery.fancybox.css?v=2.0.6" />


    <script src="js/tinymce/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: "textarea",theme: "modern",width: 680,height: 300, relative_urls: false,remove_script_host: false,
            plugins: [
                "advlist autolink link image lists charmap print preview hr anchor pagebreak",
                "searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking",
                "table contextmenu directionality emoticons paste textcolor responsivefilemanager code"
            ],
            toolbar1: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect",
            toolbar2: "| responsivefilemanager | link unlink anchor | image media | forecolor backcolor  | print preview code ",
            image_advtab: true ,

            external_filemanager_path:"/healthcare/backstage/filemanager/",
            filemanager_title:"Responsive Filemanager" ,
            external_plugins: { "filemanager" : "/healthcare/backstage/filemanager/plugin.min.js"}
        });</script>



    <script type="text/javascript">
$(document).ready(function(){
	$(".photoBox").fancybox({
		'transitionIn'	: 'elastic',
		'transitionOut'	: 'elastic'
	});
        var $textarea = $('textarea');
       // $textarea.ckeditor();
        $textarea.siblings('label').css('float', 'none');
	$('.lightBox').click(function(){
		var href = ($(this).is('input')) ? $(this).data('href'): this.href;

		$.fancybox.open({
			href: href,
			width: ($(window).width() - 60),
			type: 'iframe',
			padding: 5,
			autoSize: false,
            autoDimensions: false,
            fitToView: false,
            transitionIn  : 'elastic',
            transitionOut : 'elastic',
            margin: 30,
            hideOnOverlayClick: false,
            helpers     : { 
                overlay : {closeClick: false} // prevents closing when clicking OUTSIDE fancybox
            }
		});
		return false;
	});
});
</script>
<?php if( defined('Access_calendar') ) { ?>
    <link href="js/wdCalendar/css/dailog.css" rel="stylesheet" type="text/css" />
    <link href="js/wdCalendar/css/calendar.css" rel="stylesheet" type="text/css" /> 
    <link href="js/wdCalendar/css/dp.css" rel="stylesheet" type="text/css" />   
    <link href="js/wdCalendar/css/alert.css" rel="stylesheet" type="text/css" /> 
    <link href="js/wdCalendar/css/main.css" rel="stylesheet" type="text/css" /> 

    <script src="js/wdCalendar/Common.js" type="text/javascript"></script>

    <script src="js/wdCalendar/jquery.alert.js" type="text/javascript"></script>
    <script src="js/wdCalendar/jquery.ifrmdailog.js" defer="defer" type="text/javascript"></script>
    <script src="js/wdCalendar/wdCalendar_lang_US.js" type="text/javascript"></script>
    <script src="js/wdCalendar/jquery.calendar.js" type="text/javascript"></script>
<?php } ?>

<script src="js/jquery-ui-timepicker-addon.js" type="text/javascript"></script>
<link rel="stylesheet" href="css/jquery-ui-timepicker-addon.css" type="text/css" />

<link rel="stylesheet" href="css/colorpicker.css" type="text/css" />
<style>
<!--
.stick {
  -moz-border-radius: 0 0 0.5em 0.5em;
  -webkit-border-radius: 0 0 0.5em 0.5em;
  border-radius: 0 0 0.5em 0.5em;
  position: fixed;
  top: 0;
  z-index: 10000;
  }
  fieldset td {
  	vertical-align: top;
  }
-->
</style>
	<script src="js/hideshow.js" type="text/javascript"></script>
	<script src="js/jquery.tablesorter.min.js" type="text/javascript"></script>
	<script type="text/javascript" src="js/jquery.equalHeight.js"></script>
	<script type="text/javascript" src="js/colorpicker.js"></script>
    <script type="text/javascript" src="js/eye.js"></script>
    <script type="text/javascript" src="js/utils.js"></script>
    <script type="text/javascript" src="js/layout.js?ver=1.0.2"></script>
	<script type="text/javascript">

	var sticky = '.module header';
	var stickyTop = 0;
	var stickyWidth = 0;
	$(document).ready(function(){ 
		if($(sticky).get(0)) {
			stickyTop = $($(sticky).get(0)).offset().top;
			stickyWidth = $($(sticky).get(0)).width();
		}
	
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
		$(".datetimepicker").datetimepicker({
			controlType: 'select',
			hourMin: 6,
			hourMax: 21,
//			minDate: 0,
//			timeFormat: 'hh:mm tt',
			timeFormat: 'hh:mm tt',
			dateFormat: "yy-mm-dd"
		});

		

		//When page loads...
//		$(".tab_content").hide(); //Hide all content
//		$("ul.tabs li:first").addClass("active").show(); //Activate first tab
//		$(".tab_content:first").show(); //Show first tab content
	
		//On Click Event
		$("ul.tabs li").click(function() {
	
			$("ul.tabs li").removeClass("active"); //Remove any "active" class
			$(this).addClass("active"); //Add "active" class to selected tab
//			$(".tab_content").hide(); //Hide all tab content
	
//			var activeTab = $(this).find("a").attr("href"); //Find the href attribute value to identify the active tab + content
//			$(activeTab).fadeIn(); //Fade in the active ID content
			return false;
		});

		addNewColorAction( '.colorPicker' );
		$('.deletecolor').click(function(){
			$(this).parent().remove();
		}).css('cursor', 'pointer');

		sticky_relocate();
	});
	$(window).scroll(sticky_relocate);
	function sticky_relocate() {
		  var window_top = $(window).scrollTop();
		  if (window_top > stickyTop)
		    $($(sticky).get(0)).addClass('stick').width( stickyWidth );
		  else
		    $($(sticky).get(0)).removeClass('stick').width( 'auto' );
	  }
	function addNewColorAction( element )
	{
		$(element).ColorPicker({
			onSubmit: function(hsb, hex, rgb, el) {
				$(el).val(hex);
				$(el).ColorPickerHide();
			},
			onChange: function(hsb, hex, rgb, el) {
				$(el).val(hex);
			},
			onBeforeShow: function () {
				$(this).ColorPickerSetColor(this.value);
			}
		})
		.bind('keyup', function(){
			$(this).ColorPickerSetColor(this.value);
		});
	}
	function addNewColor( i )
	{
		var ColorDiv = $( $('#colorHolder-' + i).html() );
		ColorDiv.appendTo( $('#colorHolder-' + i).parent() );
		addNewColorAction( ColorDiv.find('.colorPicker').get(0) );
		$(ColorDiv.find('.deletecolor').get(0)).click(function(){
			$(this).parent().remove();
		}).css('cursor', 'pointer');
	}
    </script>
    <script type="text/javascript">
    $(window).load(function(){
        $('.column').equalHeight();
    });
    $(function(){
        $('.column').equalHeight();
    });
</script>
<style type="text/css">
.ui-multiselect-menu {
	position: absolute;
	display: none;
}
.ui-icon {
	display: inline-block;
	*display: inline;
	zoom: 1;
	float: right;
}
.ui-multiselect-menu .ui-widget-header li {
	float: left;
}
.ui-multiselect-menu .ui-widget-header li.ui-multiselect-close {
	float: right;
}
.ui-multiselect-menu .ui-multiselect-checkboxes .ui-corner-all {
    display: block;
    float: none !important;
    width: auto !important;
}

#main .module header h3.tabs_involved {
	width: 35%;
}
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
</head>


<body>
	<header id="header">
		<hgroup>
			<h1 class="site_title"><a href="index.php"><img height="50" src="images/logo.png" border="0" /></a></h1>
<!--			<h2 class="section_title">Welcome <?php echo ucwords( (isReseller) ? $Admin['title'] : $Admin['full_name'] ); ?></h2>-->
		</hgroup>
	</header> <!-- end of header bar -->
	
	<section id="secondary_bar">
		<div class="user">
		<p><?php echo ucwords( (isReseller) ? $Admin['title'] : $Admin['full_name'] ); ?> (<a href="logout.php">Logout</a>)</p>
			<a class="logout_user" href="logout.php" title="Logout">Logout</a>
		</div>
		<div class="breadcrumbs_container">
			<article class="breadcrumbs">
<?php include "_menu_top.php";?>
			</article>
		</div>
	</section><!-- end of secondary bar -->
	
	<aside id="sidebar" class="column">

<?php include "_menu.php";?>
		
		<footer>
			<hr />
			<p><strong>Copyright &copy; <?php echo date('Y'); ?> <?php echo PROJECT_NAME;?></strong></p>
			<p>Developed by <a href="http://www.techram.co/">TechRam</a></p>
		</footer>
	</aside><!-- end of sidebar -->
	
	<section id="main" class="column">
<?php 



