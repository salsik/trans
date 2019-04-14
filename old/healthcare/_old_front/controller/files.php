<?php

	if( !defined('BASE_DIR') ) die('Access Denied!');
	
	if( !$Account ) {
		redirectURL('login/');
	}
	
$perpage = 20;
$perpage_file = 4;

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

//	if( $id > 0 ) {
//		$strSQL = getAccessSql($Account, 'documents_details', $id);
//		$q = mysql_query("$strSQL LIMIT 1");
//		if( $q && mysql_num_rows( $q )) {
//			$file = mysql_fetch_assoc( $q );
//		}
//	}
	
	if( $file ) {
		$Title = $file['title'];
	} else {
		$Title = "{$Reseller['title']} Files";
	}
	$WRAPPER = 'files'; 
	include BASE_DIR . 'common/header.php';
?>
<div id="reseller-files" class="news">
<?php if( $file ) { ?>
	<h2 class="main-title"><span><?php echo $Reseller['title']; ?></span> Files / <span><?php echo $file['title']; ?></span></h2>
<?php } else { ?>
	<h2 class="main-title"><span><?php echo $Reseller['title']; ?></span> Files</h2>
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
<?php if( $file ) { ?>
		<div class="details">
			<div class="image">
				<img src="uploads/documents/<?php echo $file['image']; ?>" border="0" />
			</div>
			<div class="description">
				<?php echo $file['description']; ?>
			</div>
		</div>
		<div class="more">
			MORE FILES
		</div>
<?php } ?>

		<div class="items">
	<?php 
	
		$where = "";

		if( $file ) {
			$strSQL = getAccessSql($Account, 'documents', 0, " documents.id <> '{$file['id']}' $where ");
			
			$offset = 0;
			$limit = $perpage_file;
		}
		else {
			if( $cat_id ) {
//				$where = " AND documents.id IN(SELECT index_id FROM documents_index WHERE cat_id='{$cat_id}') ";
				$where = " AND documents_index.cat_id='{$cat_id}' ";
			}
			else if( $sub_id ) {
//				$where = " AND documents.id IN(SELECT index_id FROM documents_index WHERE sub_id='{$sub_id}') ";
				$where = " AND documents_index.sub_id='{$sub_id}' ";
			}

			$strSQL = getAccessSql($Account, 'documents', '', " TRUE $where");

			$total = 0;
			$q = mysql_query( $strSQL );
			if( $q && mysql_num_rows($q)) {
				$total = mysql_num_rows( $q );
			}

			$pager = pager($_page, $total, $perpage);
			
			$offset = $pager['offset'];
			$limit = $pager['perpage'];
			
			$pagination = pager_link($pager, "Files/page-");
		}
	
		$q = mysql_query( "$strSQL ORDER BY documents.rank DESC LIMIT $offset, $limit " );
		if( $q && mysql_num_rows( $q )) {
			while( $row = mysql_fetch_object( $q )) {
				$link = "Files/" .cleanTitleURL( $row->title ). "/{$row->id}";

				$image = '';
				if( $row->image ) {
					$image = 'uploads/documents/thumb/' . $row->image;
				} else if( $row->document ) {
					$image = getFileTypeIcon($row->document);
					$image = 'images/types/' . $image;
				}
				
				?>
				<div class="item">
					<div class="image">
						<img src="<?php echo $image; ?>" border="0" />
					</div>
					<div class="details">
						<div class="titleBox">
							<span class="title"><?php echo $row->title; ?></span>
							<span class="date"><?php echo date('Y-m-d', $row->time); ?></span>
							<span class="by">By: <?php echo $row->add_by; ?></span>
							<div class="clear"></div>
						</div>
					<?php if($row->document) { ?>
						<div class="link"><a href="uploads/documents/<?php echo $row->document; ?>" target="_blank">Download</a></div>
					<?php } ?>
					</div>
	
					<div class="clearfix"></div>
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