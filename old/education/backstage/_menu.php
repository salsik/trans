		<ul>
	        <li class="icn_tags"><a href="./">Dashboard</a></li>
		</ul>

<?php if( isAdmin ){ ?>
		<h3>Prevision News</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="site_news.php">View Prevision News</a></li>
			<li class="icn_new_article"><a href="site_news.php?action=add">Add New Prevision News</a></li>
		</ul>

		<h3>Education News</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="education_news.php">View Education News</a></li>
			<li class="icn_new_article"><a href="education_news.php?action=add">Add New Education News</a></li>
		</ul>

<?php } ?>

<?php if( isAdmin || isSchool ) { ?>
		<h3>Students</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="students.php">View Students</a></li>
			<li class="icn_new_article"><a href="students.php?action=add">Add New Student</a></li>
		</ul>
<?php } ?>

<?php if( isSchool || isTeacher ) { ?>
		<h3>Agenda</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="agenda.php">View Agenda</a></li>
			<li class="icn_new_article"><a href="agenda.php?action=add">Add New Agenda</a></li>
		</ul>

<?php } ?>

<?php if( isAdmin || isSchool ) { ?>
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

		<h3>School Polls</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="polls.php">View School Polls</a></li>
			<li class="icn_new_article"><a href="polls.php?action=add">Add New School Polls</a></li>
		</ul>

		<h3>Banners</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="banners.php">View Banners</a></li>
			<li class="icn_new_article"><a href="banners.php?action=add">Add New Banner</a></li>
		</ul>
		
		<h3>Classes</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="classes.php">View Classes</a></li>
			<li class="icn_new_article"><a href="classes.php?action=add">Add New Class</a></li>
		</ul>

		<h3>Teachers</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="teachers.php">View Teachers</a></li>
			<li class="icn_new_article"><a href="teachers.php?action=add">Add New Teacher</a></li>
		</ul>

		<h3>Slides</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="slides.php">View Slides</a></li>
			<li class="icn_new_article"><a href="slides.php?action=add">Add New Slide</a></li>
		</ul>
<?php } ?>

<?php if( isAdmin ) { ?>
		<h3>Schools</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="schools.php">View Schools</a></li>
			<li class="icn_new_article"><a href="schools.php?action=add">Add New School</a></li>
		</ul>

		<h3>News Categories</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="news_category.php">View News Categories</a></li>
			<li class="icn_new_article"><a href="news_category.php?action=add">Add New News Category</a></li>
		</ul>

		<h3>Documents Categories</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="documents_category.php">View Documents Categories</a></li>
			<li class="icn_new_article"><a href="documents_category.php?action=add">Add New Documents Category</a></li>
		</ul>
<?php } ?>

<?php if( isAdmin || isSchool ) { ?>
		<h3>Gallery Albums</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="gallery_category.php">View Gallery Albums</a></li>
			<li class="icn_new_article"><a href="gallery_category.php?action=add">Add New Gallery Albums</a></li>
		</ul>
<?php } ?>

<?php if( isAdmin ) { ?>
		<h3>Users</h3>
		<ul class="toggle">
			<li class="icn_folder"><a href="users.php">View Users</a></li>
			<li class="icn_new_article"><a href="users.php?action=add">Add New User</a></li>
		</ul>
<?php } ?>

<?php if( isSchool ) { ?>
		<h3>This School</h3>
		<ul class="toggle">
			<li class="icn_new_article"><a href="about-us.php">School Profile</a></li>
		</ul>
<?php } ?>
<?php if( isTeacher ) { ?>
		<h3>Profile</h3>
		<ul class="toggle">
			<li class="icn_new_article"><a href="about-teacher.php">Edit my profile</a></li>
		</ul>

<?php } ?>
		
<?php if( isAdmin ) { ?>
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