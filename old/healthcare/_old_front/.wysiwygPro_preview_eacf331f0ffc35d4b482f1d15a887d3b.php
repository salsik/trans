<?php
if ($_GET['randomId'] != "Fz5OqqGcunudmtykMndShSkb47uGmuXGnYenlOLJQtgTFcDClubNIjF8PrXWJap9") {
    echo "Access Denied";
    exit();
}

// display the HTML code:
echo stripslashes($_POST['wproPreviewHTML']);

?>  
