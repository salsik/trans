<?php

	if( !defined('BASE_DIR') ) die('Access Denied!');
	
$perpage = 8;
$perpage_news = 4;

$_page = intval( $_GET['page']);
$id = intval( $_GET['id']);

	if( $id > 0 ) {
		$q = mysql_query("SELECT * FROM site_news WHERE id='$id' LIMIT 1");
		if( $q && mysql_num_rows( $q )) {
			$news = mysql_fetch_assoc( $q );
//			include BASE_DIR . 'controller/site-news.details.php';
//			exit;
		}
	}



	if( $news ) {
		$Title = $news['title'];
	} else {
		$Title = 'Advances & More News';
	}
	$WRAPPER = 'site-news'; 
	include BASE_DIR . 'common/header.php';
?>
<div id="site-news" class="news">
<?php if( $news ) { ?>
	<h2 class="main-title">Advances & More News / <span><?php echo $news['title']; ?></span></h2>
<?php } else { ?>
	<h2 class="main-title">Advances & More News</h2>
<?php } ?>


	<div class="listings">
<?php if( $news ) { ?>
		<div class="details">
			<div class="image">
				<img src="uploads/site_news/<?php echo $news['image']; ?>" border="0" />
			</div>
			<div class="description">
				<?php echo $news['description']; ?>
			</div>
		</div>
		<div class="more">
			MORE NEWS
		</div>
<?php } ?>

		<div class="items">
	<?php 
	
		$sql = " ";
	
		if( $news ) {
			$sql .= " AND id <> '{$news['id']}' ";
			
			$offset = 0;
			$limit = $perpage_news;
		}
		else {
			$q = mysql_query("SELECT count(*) FROM site_news ");
			$total = mysql_result( $q, 0, 0 );
			
			$pager = pager($_page, $total, $perpage);
			
			$offset = $pager['offset'];
			$limit = $pager['perpage'];
			
			$pagination = pager_link($pager, "Site-News/page-");
		}
	
		$q = mysql_query( "SELECT * FROM site_news WHERE true $sql ORDER BY rank DESC LIMIT $offset, $limit " );
		if( $q && mysql_num_rows( $q )) {
			while( $row = mysql_fetch_object( $q )) {
				$link = "Site-News/" .cleanTitleURL( $row->title ). "/{$row->id}";
				?>
				<div class="item">
					<div class="image">
						<a href="<?php echo $link; ?>"><img src="uploads/site_news/thumb/<?php echo $row->image; ?>" border="0" /></a>
					</div>
					<div class="title"><a href="<?php echo $link; ?>"><?php echo $row->title; ?></a></div>
					<div class="description">
						<?php echo summarize_my_text($row->description, 16); ?>
						 <a href="<?php echo $link; ?>">read more</a>
					</div>
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
	<div class="clear"></div>
</div>
<?php

	include BASE_DIR . 'common/footer.php';