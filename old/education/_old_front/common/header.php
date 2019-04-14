<?php

	switch($WRAPPER)
	{
		default:
			$selected_class = $WRAPPER;
	}
	$selected_class = array("$selected_class" => ' active');
	
	$_SiteTitle = 'Advances & More';

	$Meta_title = ($Meta_title) ? $Meta_title : $TITLE;
	$Meta_title = ($Meta_title) ? $Meta_title : '';
	$Meta_title = ($Meta_title) ? "{$_SiteTitle} - {$Meta_title}" : "{$_SiteTitle}";
	$Meta_keywords = ($Meta_keywords) ? $Meta_keywords : '';
	$Meta_description = ($Meta_description) ? $Meta_description : '';
	$Meta_url = ($Meta_url) ? BASE_URL . $Meta_url : BASE_URL;
	$Meta_image = ($Meta_image) ? $Meta_image : BASE_URL.'images/logo.jpg';
	

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">

	<base href="<?php echo BASE_URL; ?>" />

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<meta name="description" content="<?php echo $Meta_description; ?>" />
	<meta name="keywords" content="<?php echo $Meta_keywords; ?>" />
	<meta property="og:title" content="<?php echo $Meta_title; ?>" />
	<meta property="og:description" content="<?php echo $Meta_description; ?>" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="<?php echo $Meta_url; ?>" />
	<meta property="og:image" content="<?php echo $Meta_image; ?>" />

	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo BASE_URL; ?>css/techram/stylesheet.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo BASE_URL; ?>fancybox/jquery.fancybox.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo BASE_URL; ?>css/styles.css" />
	<link rel="shortcut icon" href="<?php echo BASE_URL; ?>favicon.ico" type="image/x-icon" />

	<script type="text/javascript">var BASE_URL = '<?php echo BASE_URL; ?>';</script>
	<script type="text/javascript" src="<?php echo BASE_URL; ?>js/jquery-1.7.2.min.js"></script>
	<script type="text/javascript" src="<?php echo BASE_URL; ?>js/jquery.bvalidator.js"></script>
	<script type="text/javascript" src="<?php echo BASE_URL; ?>fancybox/jquery.fancybox.pack.js"></script>
	<script type="text/javascript" src="<?php echo BASE_URL; ?>js/scripts.js"></script>

<?php if( !$Account ) { ?>
	<script type="text/javascript" >
		$(document).ready(function(){
			$('#account .input').focus(function(){
				if($(this).val() == 'Username' || $(this).val() == 'Password') {
					$(this).val('');
				}
			});
			$('#account .input').blur(function(){
				if($(this).val() == '') {
					if(this.name == 'username' ) {
						$(this).val('Username');
					} else if(this.name == 'password') {
						$(this).val('Password');
					}
				}
			});
		});
		<?php 
			$q = mysql_query("SELECT * FROM slideshow ORDER BY rank DESC ");
			if( $q && mysql_num_rows( $q )) {
				while( $row = mysql_fetch_object( $q )) {
					?>$('<img src="uploads/slideshow/thumb/<?php echo $row->image; ?>" />').load();<?php 
				}
			}
		?>

	</script>
<?php } ?>
<?php if( $SlideShow ) { ?>
	<link rel="stylesheet" type="text/css" href="css/flexslider.css" />
	<script src="js/jquery.flexslider-min.js"></script>
	<script type="text/javascript" >
		$(document).ready(function(){
			$('.flexslider').flexslider({
				  animation: "slide",
				  controlsContainer: ".flexslider-container"
			  });
		});
		<?php 
			$q = mysql_query("SELECT * FROM slideshow ORDER BY rank DESC ");
			if( false && $q && mysql_num_rows( $q )) {
				while( $row = mysql_fetch_object( $q )) {
					?>$('<img src="uploads/slideshow/thumb/<?php echo $row->image; ?>" />').load();<?php 
				}
			}
		?>

	</script>
<?php } ?>

	<title><?php echo ($TITLE) ? $_SiteTitle .' - ' . $TITLE : $_SiteTitle; ?></title>
</head>
<body>
<div id="fb-root"></div>
<script>(function(d, s, id) {
var js, fjs = d.getElementsByTagName(s)[0];
if (d.getElementById(id)) return;
js = d.createElement(s); js.id = id;
js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=";
fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<div id="wrapper" class="wrapper_<?php echo $WRAPPER; ?>">
<div id="header">
	<div class="wrapper">
		<div id="logo">
			<a href="./"><img src="images/logo.png" border="0" /></a>
		</div>
		<div id="account">
			<img src="images/top-corner.png" style="float: left;" />
			<?php if( $Account ) { ?>
<!--				<div class="block">Hello <?php echo $Account['full_name']; ?></div>-->
				<div class="block<?php echo $selected_class['news']; ?>"><a href="News/"><?php echo $School['title']; ?> News</a></div>
				<div class="block<?php echo $selected_class['files']; ?>"><a href="Files/"><?php echo $School['title']; ?> Files</a></div>
				<div class="block<?php echo $selected_class['contact']; ?>"><a href="Contact/">Contact <?php echo $School['title']; ?></a></div>
				<div class="block<?php echo $selected_class['patient']; ?>"><a href="Patient/">Add Patient</a></div>
				<div class="block<?php echo $selected_class['quiz']; ?>"><a href="Quiz/"><?php echo $School['title']; ?> Quiz</a></div>
				<div class="block"><a href="Logout/">Log Out</a></div>
			<?php } else { ?>
				<div class="block">
					<form class="form" action="login/" method="post" >
						<label><a href="login/">Login</a></label>
						<input type="text" class="input" name="username" value="Username" />
						<input type="password" class="input" name="password" value="Password" />
						<input type="submit" class="submit" name="submit" value="GO!" />
					</form>
				</div>
			<?php } ?>
		</div>
		<div class="clear"></div>
	</div>
</div>
<div id="menu">
	<div class="wrapper">
	<ul>
		<li class="item<?php echo $selected_class['home']; ?>"><a class="list" href="./">Home</a></li>
		<li class="item<?php echo $selected_class['about-us']; ?>"><a class="list" href="About-Us/">About Us</a></li>
		<li class="item<?php echo $selected_class['services']; ?>"><a class="list" href="Services/">Services</a></li>
		<li class="item<?php echo $selected_class['site-news']; ?>"><a class="list" href="Site-News/">Advances & More News</a></li>
		<li class="item<?php echo $selected_class['contact-us']; ?>"><a class="list" href="Contact-Us/">Contact Us</a></li>
	</ul>
	</div>
</div>
<div id="body">
	<div class="wrapper">