<?php 


ob_start();

include "_top.php";



	
?>

<h4 class="alert_info">Welcome <?php echo (isReseller) ? $Admin['title'] : $Admin['full_name']; ?>.</h4>




<?php 

include "_bottom.php";
