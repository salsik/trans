		<ul>
	        <li class="icn_tags"><a href="./">Dashboard</a></li>
		</ul>
<?php if( !isReseller ){ ?>
		<h3>Site News</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="site_news.php">View Site News</a></li>
			<li class="icn_new_article"><a href="site_news.php?action=add">Add New Site News</a></li>
		</ul>
<?php } ?>

		<h3>Students</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="students.php">View Students</a></li>
			<li class="icn_new_article"><a href="students.php?action=add">Add New Student</a></li>
		</ul>
		<h3>Questions</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="questions.php">View Questions</a></li>
<!--			<li class="icn_new_article"><a href="questions.php?action=add">Add New Question</a></li>-->
		</ul>

		<h3>School Documents</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="documents.php">View Documents</a></li>
			<li class="icn_new_article"><a href="documents.php?action=add">Add New Document</a></li>
		</ul>

		<h3>School Gallery</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="gallery.php">View Gallery</a></li>
			<li class="icn_new_article"><a href="gallery.php?action=add">Add New Photo</a></li>
		</ul>

		<h3>School Videos</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="videos.php">View Videos</a></li>
			<li class="icn_new_article"><a href="videos.php?action=add">Add New Video</a></li>
		</ul>

		<h3>School News</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="news.php">View School News</a></li>
			<li class="icn_new_article"><a href="news.php?action=add">Add New School News</a></li>
		</ul>

		<h3>School Quiz</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="competitions.php">View School Quiz</a></li>
			<li class="icn_new_article"><a href="competitions.php?action=add">Add New School Quiz</a></li>
		</ul>

		<h3>Banners</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="banners.php">View Banners</a></li>
			<li class="icn_new_article"><a href="banners.php?action=add">Add New Banner</a></li>
		</ul>

<?php if( !isReseller ) { ?>


		<h3>Schools</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="resellers.php">View Schools</a></li>
			<li class="icn_new_article"><a href="resellers.php?action=add">Add New School</a></li>
		</ul>
		
		<h3>Classes</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="category.php">View Classes</a></li>
			<li class="icn_new_article"><a href="category.php?action=add">Add New Class</a></li>
		</ul>

		<h3>Sub Classes</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="category_sub.php">View Sub Classes</a></li>
			<li class="icn_new_article"><a href="category_sub.php?action=add">Add New Sub Class</a></li>
		</ul>

		<h3>News Categories</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="news_category.php">View News Categories</a></li>
			<li class="icn_new_article"><a href="news_category.php?action=add">Add New News Category</a></li>
		</ul>

		<h3>Admin</h3>
		<ul class="toggle">
			<li class="icn_security"><a href="admins.php">Admins Manager</a></li>
			<li class="icn_jump_back"><a href="logout.php">Logout</a></li>
		</ul>
<?php } else { ?>
		<h3>Admin</h3>
		<ul class="toggle">
			<li class="icn_jump_back"><a href="logout.php">Logout</a></li>
		</ul>
<?php } ?>