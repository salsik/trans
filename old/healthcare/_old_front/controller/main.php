<?php 

if( !defined('BASE_DIR') ) die('Access Denied!');

$SlideShow = true;

$Title = '';
$WRAPPER = 'home'; 
include BASE_DIR . 'common/header.php';
?>
<div class="homepage">

	<div id="sliders" >
		<div class="flexslider-container">
			<div class="flexslider">
		    <ul class="slides">
		    	<li><a href="Services/"><img src="slides/1.jpg" /></a></li>
		    	<li><a href="Services/"><img src="slides/2.jpg" /></a></li>
		    	<li><a href="Services/"><img src="slides/3.jpg" /></a></li>
		    	<li><a href="Services/"><img src="slides/4.jpg" /></a></li>
		    	<li><a href="Services/"><img src="slides/5.jpg" /></a></li>
		    	<li><a href="Services/"><img src="slides/6.jpg" /></a></li>
		    	<li><a href="Services/"><img src="slides/7.jpg" /></a></li>
		    	<li><a href="Services/"><img src="slides/8.jpg" /></a></li>
		    	<li><a href="Services/"><img src="slides/9.jpg" /></a></li>
		    	<li><a href="Services/"><img src="slides/10.jpg" /></a></li>
		    	<li><a href="Services/"><img src="slides/11.jpg" /></a></li>
		    	<li><a href="Services/"><img src="slides/12.jpg" /></a></li>
		    	<li><a href="Services/"><img src="slides/13.jpg" /></a></li>
		    </ul>
		  </div>
	 	</div>
	</div>
	<div class="clear"></div>
	

<div>
	<h2 class="main-title">SERVICES</h2>
	<div class="services-list">
	<?php 
	
		$Services = array();
		$Services[] = array(
			'title' => 'Medical technologies',
			'image' => 'slides/services-main/2.jpg',
		);
		$Services[] = array(
			'title' => 'Financial services',
			'image' => 'slides/services-main/3.jpg',
		);
		
		$Services[] = array(
			'title' => 'Media',
			'image' => 'slides/services-main/4.jpg',
		);
		
		$Services[] = array(
			'title' => 'Social & Public Sector',
			'image' => 'slides/services-main/5.jpg',
		);
		
		$Services[] = array(
			'title' => 'Higher Education',
			'image' => 'slides/services-main/6.jpg',
		);
		
		$Services[] = array(
			'title' => 'Technology',
			'image' => 'slides/services-main/7.jpg',
		);
		
		$x = 0;
		foreach( $Services as $Service ) {
			$x++;
			$class = ($x%2) ? 'left' : 'right';
	?>
		<div class="service-list <?php echo $class; ?>">
			<a href="Services/"><img alt="<?php echo $Service['title']; ?>" title="<?php echo $Service['title']; ?>" src="<?php echo $Service['image']; ?>" /></a>
		</div>
	<?php } ?>
	
	
	
		<div class="more">
			<a href="Services/">More Services</a>
		</div>
	</div>


</div>

	
	
	
	
	
	
	
</div>
<?php 
include BASE_DIR . 'common/footer.php';