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
$cat_id = intval( $_GET['cat_id']);
$sub_id = intval( $_GET['sub_id']);

if( $cat_id < 0 ) {
	$sub_id = abs( $cat_id );
	$cat_id = 0;
}
else if( $cat_id > 0 ) {
	$sub_id = 0;
}
else if( $sub_id < 0 ) {
	$cat_id = abs( $sub_id );
	$sub_id = 0;
}

//$Categories = array();
//$q = mysql_query("SELECT id, title FROM `category` ORDER BY category.rank DESC");
//if( $q && mysql_num_rows($q) ) {
//	while( $row = mysql_fetch_assoc($q)) {
//		$Categories[$row['id'] ] = $row;
//	}
//}
//$SubCategories = array();
//$q = mysql_query("SELECT id, title, cat_id FROM category_sub ORDER BY rank DESC");
//if( $q && mysql_num_rows($q) ) {
//	while( $row = mysql_fetch_assoc($q)) {
//		$SubCategories[ $row['cat_id'] ][ $row['id'] ] = $row;
//	}
//}
	$Categories = getAccountCategories($Account);

	if( $id > 0 ) {
		$strSQL = getAccessSql($Account, 'news_details', $id);
		$q = mysql_query("$strSQL LIMIT 1");
		if( $q && mysql_num_rows( $q )) {
			$news = mysql_fetch_assoc( $q );
		}
	}
	
	if( $news ) {
		$Title = $news['title'];
	} else {
		$Title = "{$Reseller['title']} News";
	}
	$WRAPPER = 'news'; 
	include BASE_DIR . 'common/header.php';
?>
<div id="reseller-news" class="news">
<?php if( $news ) { ?>
	<h2 class="main-title"><span><?php echo $Reseller['title']; ?></span> News / <span><?php echo $news['title']; ?></span></h2>
<?php } else { ?>
	<h2 class="main-title"><span><?php echo $Reseller['title']; ?></span> News</h2>
<?php } ?>


<form action="" method="get" >
<select name="cat_id">
<option value="">-- Select --</option>
<?php 
	foreach($Categories as $category) {
		$selected = selected($category['id'], $cat_id);
		?><option class="level1" value="<?php echo $category['id']; ?>" <?php echo $selected; ?> ><?php echo $category['title']; ?></option><?php 

		if( $category['sub'] ) {
			foreach($category['sub'] as $sub) {
				$selected = selected($sub['id'], $sub_id);
				?><option class="level2" value="<?php echo $sub['id']*-1; ?>" <?php echo $selected; ?> ><?php echo $sub['title']; ?></option><?php 
			}
		}
	}
?>
</select>

<input type="submit" value="Submit" />
</form>

	<div class="listings">
<?php if( $news ) { ?>
		<div class="details">
			<div class="image">
				<img src="uploads/news/<?php echo $news['image']; ?>" border="0" />
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
	
		$where = "";

		if( $news ) {
			$where = " AND news_index.cat_id IN (SELECT cat_id FROM news_index WHERE index_id='{$news['id']}' ) ";

//			$sql .= " AND id <> '$news->id' ";
			$strSQL = getAccessSql($Account, 'news', 0, " news.id <> '{$news['id']}' $where ");
//		echo $strSQL;
			$offset = 0;
			$limit = $perpage_news;
		}
		else {
			if( $cat_id ) {
//				$where = " AND news.id IN(SELECT index_id FROM news_index WHERE cat_id='{$cat_id}') ";
				$where = " AND news_index.cat_id='{$cat_id}' ";
			}
			else if( $sub_id ) {
//				$where = " AND news.id IN(SELECT index_id FROM news_index WHERE sub_id='{$sub_id}') ";
				$where = " AND news_index.sub_id='{$sub_id}' ";
			}
		
			$strSQL = getAccessSql($Account, 'news', '', " TRUE $where");

			$total = 0;
			$q = mysql_query( $strSQL );
//	var_dump( $where );
//	echo mysql_error();
//echo $strSQL;
			if( $q && mysql_num_rows($q)) {
				$total = mysql_num_rows( $q );
			}

			$pager = pager($_page, $total, $perpage);
			
			$offset = $pager['offset'];
			$limit = $pager['perpage'];
			
			$pagination = pager_link($pager, "News/page-");
		}
	
		$q = mysql_query( "$strSQL ORDER BY news.rank DESC LIMIT $offset, $limit " );
		if( $q && mysql_num_rows( $q )) {
			while( $row = mysql_fetch_object( $q )) {
				$link = "News/" .cleanTitleURL( $row->title ). "/{$row->id}";
				?>
				<div class="item">
					<div class="image">
						<a href="<?php echo $link; ?>"><img src="uploads/news/thumb/<?php echo $row->image; ?>" border="0" /></a>
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