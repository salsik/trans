<?php

	if( !defined('BASE_DIR') ) die('Access Denied!');

	$Title = 'Our Services';
	$WRAPPER = 'services'; 
	include BASE_DIR . 'common/header.php';
	
	$Services = array();
	$Services[] = array(
		'title' => 'Healthcare Firms',
		'image' => 'slides/services/1.jpg',
		'description' => 'Healthcare firms are facing tense changes in customer behavior, market variations and demands. “Advances and More” cooperates with healthcare companies to provide patients with better value and innovation and minimize the cost of operating systems. We also assist pharmaceutical manufacturers to become more successful in daily changing markets—which mean ensuring their products contributing to favorable and demonstrable outcomes and streamline costs in the face of increasing price pressure',
	);
	
	
	$Services[] = array(
		'title' => 'Medical technologies',
		'image' => 'slides/services/2.jpg',
		'description' => '“Advances and More” helps medical technology clients to develop strategies to encourage cost-effective improvements and focus on the needs of a wider range of constituents.',
	);
	$Services[] = array(
		'title' => 'Financial services',
		'image' => 'slides/services/3.jpg',
		'description' => 'From finance to banking and auditing to wealth management,” Advances and More” has dedicated financial services groups serving all premium areas of the financial services industry.',
	);
	
	$Services[] = array(
		'title' => 'Media',
		'image' => 'slides/services/4.jpg',
		'description' => 'The media industry is facing major digital transformation such as improvements in technology. “Advances and More” has extensive expertise within the media industry. We cooperate with our clients by facing the challenges—and benefiting from it.',
	);
	
	$Services[] = array(
		'title' => 'Social & Public Sector',
		'image' => 'slides/services/5.jpg',
		'description' => 'The nonprofit and higher education sectors play a major role in the global economy and in the lives of people across the world. “Advances and More” mixes the top of our private-sector skills with a deep knowledge of the social and public sectors to effect long-lasting change and carry out continuing results for our customers.',
	);
	
	$Services[] = array(
		'title' => 'Higher Education',
		'image' => 'slides/services/6.jpg',
		'description' => '“Advances and More” has a big experience in assisting higher educational institutions to address strategic issues such as performance progress, working efficiency, cost reduction, and growth plan.',
	);
	
	$Services[] = array(
		'title' => 'Technology',
		'image' => 'slides/services/7.jpg',
		'description' => 'Technology, running software, hardware and technology service businesses, need lasting vision and compliance. “Advances and More” expertise helps technology firms participate and succeed in an industry where innovation and challenge go hand in hand.',
	);
	$Services[] = array(
		'title' => 'Airlines and Transportation ',
		'image' => 'slides/services/8.jpg',
		'description' => '“Advances and More” cooperates with transportation firms to take fast and influential actions concerning innovative strategies, pricing decisions and unstable increasing costs.',
	);
	
	$Services[] = array(
		'title' => 'Consumer Products',
		'image' => 'slides/services/9.jpg',
		'description' => 'The consumer products firms are facing crucial challenges related to shopper behavior, and emerging markets. “Advances and More” assists to enhance incomes and create lasting value for premium customer products firms.',
	);
	
	$Services[] = array(
		'title' => 'Telecommunications',
		'image' => 'slides/services/10.jpg',
		'description' => 'The telecommunications companies are dealing with important challenges concerning the choice of taking part in evolving markets, while guaranteeing that their major businesses are potentially working. “Advances and More” helps telecom companies penetrate new markets, enhance performance and manage costs.',
	);
	
	$Services[] = array(
		'title' => 'Environments',
		'image' => 'slides/services/11.jpg',
		'description' => 'Critical obstacles are threatening the worldwide ecosystem. “Advances and More” collaborates with the green governmental and nongovernmental organizations to come up with specialized strategies and expert solutions that reduce the ecological risks.',
	);
	
	$Services[] = array(
		'title' => 'Marketing & Brand Strategy',
		'image' => 'slides/services/12.jpg',
		'description' => 'Effective marketing programs enhance both incomes and profits. “Advances and More” coordinates with highly skilled companies to elaborate these programs to target specialized customer segments and come up with the desired image by enriching the brand.',
	);
	
	$Services[] = array(
		'title' => 'Travel',
		'image' => 'slides/services/13.jpg',
		'description' => '“Advances and More” practiced strategies help the travel and transportation sectors to participate in evolving markets and to change the challenges into growth benefits and useful opportunities.',
	);
	
?>
<div id="services">

	<h2 class="main-title">Services</h2>
	
	
	
	<div class="listings">
		<div class="items">
	<?php foreach($Services as $Service) { ?>
			<div class="item">
				<div class="image">
					<img src="<?php echo $Service['image']; ?>" border="0" />
				</div>
				<div class="title"><?php echo $Service['title']; ?></div>
				<div class="description">
					<?php echo $Service['description']; ?>
				</div>
			</div>
	<?php } ?>
		</div>
	</div>

	<div class="clear"></div>
</div>
<?php

	include BASE_DIR . 'common/footer.php';