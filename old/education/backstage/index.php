<?php 

ob_start();

include "_top.php";

?>

<h4 class="alert_info">Welcome <?php echo (isAdmin || isSchoolAdmin) ? $Admin['full_name'] : $Admin['title']; ?>.</h4>

<?php 

include "_bottom.php";
