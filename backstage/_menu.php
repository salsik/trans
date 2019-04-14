		<ul>
	        <li class="icn_tags"><a href="./">Dashboard</a></li>
		</ul>

		<h3>Companies Files</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="documents.php">View Companies Files</a></li>
			<li class="icn_new_article"><a href="documents.php?action=add">Add New Company File</a></li>
		</ul>

		<h3>Companies News</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="news.php">View Companies News</a></li>
			<li class="icn_new_article"><a href="news.php?action=add">Add New Company News</a></li>
		</ul>

        <h3>Expert Articles</h3>
        <ul class="toggle">
            <li class="icn_folder"><a href="expert_articles2.php">View Expert Articles</a></li>
            <li class="icn_new_article"><a href="expert_articles2.php?action=add">Add New Article</a></li>
        </ul>


        <h3>Products</h3>
        <ul class="toggle">
            <li class="icn_folder"><a href="products.php">View Products</a></li>
            <li class="icn_new_article"><a href="products.php?action=add">Add New Product</a></li>
        </ul>



		<h3>Doctors</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="doctors.php">View Doctors</a></li>
			<li class="icn_new_article"><a href="doctors.php?action=add">Add New Doctor</a></li>
			<li class="icn_new_article"><a href="expert_requests.php">View Expert Requests</a></li>

		</ul>
		<h3>Questions</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="questions.php">View Questions</a></li>
<!--			<li class="icn_new_article"><a href="questions.php?action=add">Add New Question</a></li>-->
		</ul>

		<h3>Companies Quiz</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="competitions.php">View Companies Quiz</a></li>
			<li class="icn_new_article"><a href="competitions.php?action=add">Add New Company Quiz</a></li>
		</ul>

		<h3>Companies Polls</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="polls.php">View Companies Polls</a></li>
			<li class="icn_new_article"><a href="polls.php?action=add">Add New Company Poll</a></li>
		</ul>

		<h3>Banners</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="banners.php">View Banners</a></li>
			<li class="icn_new_article"><a href="banners.php?action=add">Add New Banner</a></li>
		</ul>
		
<?php if( !isReseller ){ ?>
		<h3>Medical News</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="medical_news.php">View Medical News</a></li>
			<li class="icn_new_article"><a href="medical_news.php?action=add">Add New Medical News</a></li>
		</ul>
		
		<h3>Prevision News</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="site_news.php">View Prevision News</a></li>
			<li class="icn_new_article"><a href="site_news.php?action=add">Add New Prevision News</a></li>
		</ul>

		<h3>Companies</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="resellers.php">View Companies</a></li>
			<li class="icn_new_article"><a href="resellers.php?action=add">Add New Company</a></li>
		</ul>

		<h3>Regions</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="regions.php">View Regions</a></li>
			<li class="icn_new_article"><a href="regions.php?action=add">Add New Region</a></li>
		</ul>

		<h3>Categories</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="category.php">View Categories</a></li>
			<li class="icn_new_article"><a href="category.php?action=add">Add New Category</a></li>
		</ul>
<!--		<h3>Sub Categories</h3>-->
<!--		<ul class="toggle">-->
<!--			<li class="icn_folder"><a href="category_sub.php">View Sub Categories</a></li>-->
<!--			<li class="icn_new_article"><a href="category_sub.php?action=add">Add New Sub Category</a></li>-->
<!--		</ul>-->
<?php } ?>

<?php if( !isReseller ) { ?>
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
