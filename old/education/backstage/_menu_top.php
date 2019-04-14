
<a href="./">Dashboard</a>

<?php if( isAdmin ) { ?>

<div class="breadcrumb_divider"></div>
<a href="site_news.php">Prevision News</a>

<div class="breadcrumb_divider"></div>
<a href="education_news.php">Education News</a>

<div class="breadcrumb_divider"></div>
<a href="schools.php">Schools</a>

<?php } ?>

<?php if(isSchool || isTeacher) { ?>
<div class="breadcrumb_divider"></div>
<a href="agenda.php">Agenda</a>

<?php } ?>


<?php if(isAdmin || isSchool) { ?>
<div class="breadcrumb_divider"></div>
<a href="students.php">Students</a>

<div class="breadcrumb_divider"></div>
<a href="documents.php">Documents</a>

<div class="breadcrumb_divider"></div>
<a href="gallery.php">Gallery</a>

<div class="breadcrumb_divider"></div>
<a href="videos.php">Videos</a>

<div class="breadcrumb_divider"></div>
<a href="news.php">School News</a>

<div class="breadcrumb_divider"></div>
<a href="questions.php">Questions</a>

<div class="breadcrumb_divider"></div>
<a href="competitions.php">Quiz</a>

<div class="breadcrumb_divider"></div>
<a href="polls.php">Polls</a>

<div class="breadcrumb_divider"></div>
<a href="slides.php">Slides</a>

<div class="breadcrumb_divider"></div>
<a href="banners.php">Banners</a>


<?php } ?>

<?php if( isAdmin ) { ?>

<div class="breadcrumb_divider"></div>
<a href="users.php">Users</a>

<?php } ?>