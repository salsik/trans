<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
ob_start();
require "../includes/config.php";
require "../includes/conn.php";
require "../includes/module.php";
require_once "../includes/functions_login_session.php";
$id=$_REQUEST['id'];
$table=$_REQUEST['table'];
$image_field= $_REQUEST['imf'];
$thumb_field= $_REQUEST['thf'];
$max_file = "3"; 							// Maximum file size in MB
$max_width = "500";							// Max width allowed for the large image
$thumb_width = $_REQUEST['thwidth'];						// Width of thumbnail image
$thumb_height = $_REQUEST['thheight'];						// Height of thumbnail image

if($image_field != ''){
	$image_dir= $_REQUEST['imgdir'];
	$large_image_name = $image_dir.getfield($id,$image_field,$table) ;// New name of the large image (append the timestamp to the filename)
	}
if($thumb_field != ''){
	$upload_dir = $_REQUEST['thdir'];; 				// The directory for the images to be saved in
	$thumb_image_name = $upload_dir.getfield($id,$thumb_field,$table) ;   // New name of the thumbnail image (append the timestamp to the filename)
	}
	

// Only one of these image types should be allowed for upload
$allowed_image_types = array('image/pjpeg'=>"jpg",'image/jpeg'=>"jpg",'image/jpg'=>"jpg",'image/png'=>"png",'image/x-png'=>"png",'image/gif'=>"gif");
$allowed_image_ext = array_unique($allowed_image_types); // do not change this
$image_ext = "";	// initialise variable, do not change this.
foreach ($allowed_image_ext as $mime_type => $ext) {
    $image_ext.= strtoupper($ext)." ";
}


##########################################################################################################
# IMAGE FUNCTIONS																						 #
# You do not need to alter these functions																 #
##########################################################################################################
function resizeImage($image,$width,$height,$scale) {
	list($imagewidth, $imageheight, $imageType) = getimagesize($image);
	$imageType = image_type_to_mime_type($imageType);
	$newImageWidth = ceil($width * $scale);
	$newImageHeight = ceil($height * $scale);
	$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
	switch($imageType) {
		case "image/gif":
			$source=imagecreatefromgif($image); 
			break;
	    case "image/pjpeg":
		case "image/jpeg":
		case "image/jpg":
			$source=imagecreatefromjpeg($image); 
			break;
	    case "image/png":
		case "image/x-png":
			$source=imagecreatefrompng($image); 
			break;
  	}
	imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$width,$height);
	
	switch($imageType) {
		case "image/gif":
	  		imagegif($newImage,$image); 
			break;
      	case "image/pjpeg":
		case "image/jpeg":
		case "image/jpg":
	  		imagejpeg($newImage,$image,90); 
			break;
		case "image/png":
		case "image/x-png":
			imagepng($newImage,$image);  
			break;
    }
	
	chmod($image, 0777);
	return $image;
}
//You do not need to alter these functions
function resizeThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale){
	
	list($imagewidth, $imageheight, $imageType) = getimagesize($image);
	$imageType = image_type_to_mime_type($imageType);
	
	$newImageWidth = ceil($width * $scale);
	$newImageHeight = ceil($height * $scale);
	$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
	switch($imageType) {
		case "image/gif":
			$source=imagecreatefromgif($image); 
			break;
	    case "image/pjpeg":
		case "image/jpeg":
		case "image/jpg":
			$source=imagecreatefromjpeg($image); 
			break;
	    case "image/png":
		case "image/x-png":
			$source=imagecreatefrompng($image); 
			break;
  	}
	imagecopyresampled($newImage,$source,0,0,$start_width,$start_height,$newImageWidth,$newImageHeight,$width,$height);
	switch($imageType) {
		case "image/gif":
	  		imagegif($newImage,$thumb_image_name); 
			break;
      	case "image/pjpeg":
		case "image/jpeg":
		case "image/jpg":
	  		imagejpeg($newImage,$thumb_image_name,90); 
			break;
		case "image/png":
		case "image/x-png":
			imagepng($newImage,$thumb_image_name);  
			break;
    }

	chmod($thumb_image_name, 0777);
	return $thumb_image_name;
}
//You do not need to alter these functions
function getHeight($image) {
	$size = getimagesize($image);
	$height = $size[1];
	return $height;
}
//You do not need to alter these functions
function getWidth($image) {
	$size = getimagesize($image);
	$width = $size[0];
	return $width;
}
//Check to see if any images with the same name already exist
if (file_exists($large_image_name)){
	if(file_exists($thumb_image_name)){
		$thumb_photo_exists = "<img src=\"".$thumb_image_name.$_SESSION['user_file_ext']."\" alt=\"Thumbnail Image\"/>";
	}else{
		$thumb_photo_exists = "";
	}
   	$large_photo_exists = "<img src=\"".$large_image_name."\" alt=\"Large Image\"/>";
} else {
   	$large_photo_exists = "";
	$thumb_photo_exists = "";
}

if (isset($_POST["upload_thumbnail"]) && strlen($large_photo_exists)>0){
	//Get the new coordinates to crop the image.
	$x1 = $_POST["x1"];
	$y1 = $_POST["y1"];
	$x2 = $_POST["x2"];
	$y2 = $_POST["y2"];
	$w = $_POST["w"];
	$h = $_POST["h"];
	
	
	//Scale the image to the thumb_width set above
	$scale = $thumb_width/$w;
	$cropped = resizeThumbnailImage($thumb_image_name, $large_image_name,$w,$h,$x1,$y1,$scale);

	//Reload the page again to view the thumbnail
	header("location:".$_SERVER["PHP_SELF"]."?flag=flagSent&id=".$id."&imf=".$image_field."&thf=".$thumb_field."&imgdir=".$image_dir."&thdir=".$upload_dir."&table=".$table."&thwidth=".$thumb_width."&thheight=".$thumb_height);
	exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Crop</title>
	<script type="text/javascript" src="../common/js/jquery.js"></script>
	<script type="text/javascript" src="../common/js/jquery.imgareaselect.min.js"></script>
</head>
<body>
<?php
//Only display the javacript if an image has been uploaded
if(strlen($large_photo_exists)>0){
	$current_large_image_width = getWidth($large_image_name);
	$current_large_image_height = getHeight($large_image_name);?>
<script type="text/javascript">
function preview(img, selection) { 

	var scaleX = <?php echo $thumb_width;?> / selection.width; 
	var scaleY = <?php echo $thumb_height;?> / selection.height; 
	$('#thumbnail + div > img').css({ 
		width: Math.round(scaleX * <?php echo $current_large_image_width;?>) + 'px', 
		height: Math.round(scaleY * <?php echo $current_large_image_height;?>) + 'px',
		marginLeft: '-' + Math.round(scaleX * selection.x1) + 'px', 
		marginTop: '-' + Math.round(scaleY * selection.y1) + 'px' 
	});
	$('#x1').val(selection.x1);
	$('#y1').val(selection.y1);
	$('#x2').val(selection.x2);
	$('#y2').val(selection.y2);
	$('#w').val(selection.width);
	$('#h').val(selection.height);
} 

$(document).ready(function () { 
	$('#save_thumb').click(function() {
		var x1 = $('#x1').val();
		var y1 = $('#y1').val();
		var x2 = $('#x2').val();
		var y2 = $('#y2').val();
		var w = $('#w').val();
		var h = $('#h').val();
		if(x1=="" || y1=="" || x2=="" || y2=="" || w=="" || h==""){
			alert("You must make a selection first");
			return false;
		}else{
			return true;
		}
	});
}); 

$(window).load(function () { 	 
	$('#thumbnail').imgAreaSelect({ show:true,x1:0,y1:0,x2:<?php echo $thumb_width;?>,y2:<?php echo $thumb_height;?>,aspectRatio: '1:<?php echo $thumb_height/$thumb_width;?>', onSelectChange: preview }); 
});

function CloseCropBox()
{
	if (window.opener && !window.opener.closed)
	{
		var Link = window.opener.location.href;
		if( Link[ Link.length -1 ] == '#' )
		{
			Link = Link.substr(0, Link.length -1 );
		}
		window.opener.location.replace( Link );
	}
	window.close();
}
	
</script>
<?php }

		if(strlen($large_photo_exists)>0){?>
            <div align="left" style="padding-bottom:10px">
			<form name="thumbnail" action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
				<input type="hidden" name="x1" value="" id="x1" />
				<input type="hidden" name="y1" value="" id="y1" />
				<input type="hidden" name="x2" value="" id="x2" />
				<input type="hidden" name="y2" value="" id="y2" />
				<input type="hidden" name="w" value="" id="w" />
				<input type="hidden" name="h" value="" id="h" />
                <input type="hidden" name="id" value="<? echo $id; ?>" id="id" />
                <input type="hidden" name="imf" value="<? echo $image_field; ?>" id="imf" />
                <input type="hidden" name="thf" value="<? echo $thumb_field; ?>" id="thf" />
                <input type="hidden" name="imgdir" value="<? echo $image_dir; ?>" id="imgdir" />
                <input type="hidden" name="thdir" value="<? echo $upload_dir; ?>" id="thdir" />
                <input type="hidden" name="table" value="<? echo $table; ?>" id="table" />
                <input type="hidden" name="thwidth" value="<? echo $thumb_width; ?>" id="thwidth" />
                <input type="hidden" name="thheight" value="<? echo $thumb_height; ?>" id="thheight" />
				<input type="submit" name="upload_thumbnail" value="Save Thumbnail" id="save_thumb" />
                <input type="button" name="close" value="Close" onclick="CloseCropBox();" />
			</form>
            </div>



		<span style=" font-size:11px; font-weight:bold">Click and drag to preview the Thumbnail</span>
		<div align="center">

			<img src="<?php echo $large_image_name;?>" style="float: left; margin-right: 10px;" id="thumbnail" alt="Create Thumbnail" />

<div style="border:1px #e5e5e5 solid; float:left; position:relative; overflow:hidden; width:<?php echo $thumb_width;?>px; height:<?php echo $thumb_height;?>px;">        
				<img src="<?php echo $large_image_name;?>" style="position: relative;" alt="Thumbnail Preview" />

            </div>
            <div style="position:absolute; left:<?php echo getWidth($large_image_name)+19;?>px; top: 40px;"><span style="color:#F00; font-size:11px; font-weight:bold">Selection Preview</span></div>
		<div style="position:absolute; left:<?php echo getWidth($large_image_name)+19;?>px; top:<?php echo $thumb_height+40+30;?>px" align="left">
 <span style="color:#F00; font-weight:bold; font-size:11px;">Thumbnail</span><br />
            <img src="<?php echo $thumb_image_name;?>?<?php echo rand();?>" alt="Thumbnail Image"/>
            <br />
          
            <br />

		</div>
			<br style="clear:both;"/>
		</div>
	<?php 	} ?>
</body>
</html>