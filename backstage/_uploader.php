<?php
	
if (!function_exists("file_upload_direct"))
{
	function file_upload_direct ($file_orig_type,$myvar,$path,&$status,$max_size=''){ 
		 global $HTTP_POST_FILES,$HTTP_POST_VARS;
		 $old_name=$_POST['old'.$myvar];
		 $file_name=$_POST[$myvar];
		 $real_name=$_FILES[$myvar]['name'];
		 $temp_name=$_FILES[$myvar]['tmp_name'];
		 $file_size=$_FILES[$myvar]['size'];
		 $file_type=$_FILES[$myvar]['type'];
		
		 if (!$real_name){
		  if ($file_name!=$old_name)
		   if (file_exists($path.$old_name) and !file_exists($path.$file_name) and $file_name!='')
		    rename($path.$old_name,$path.$file_name);
		  return false;
		 } elseif (!is_uploaded_file($temp_name)){
		  $status.="File \"$real_name\" is not uploaded!<br>"; 
		  return false;
		 } elseif ($max_size !='' and $file_size>$max_size){
		  $status.="File \"$real_name ($file_size bytes)\" is larger than the maximum allowed of $max_size bytes.<br>";
		  return false;
		 } else {
		 	  $destination_file=str_replace(' ','_',$real_name);
			  list($basic_name,$ext_name)=fname_split($destination_file);
			  $ext_name = strtolower( $ext_name );
			  if($file_orig_type == "image"){
			  	$type_of_file_must_be = "image";
			  	$images_extentions_array = array("gif","png","jpeg","jpg","jpe","bmp");
			  }elseif($file_orig_type == "video"){
			  	$type_of_file_must_be = "video";
			  	$images_extentions_array = array("flv","mov","mpeg","mp4","avi");
			  }elseif($file_orig_type == "document"){
			  	$type_of_file_must_be = "application";
			  	$images_extentions_array = array("pdf","doc","dox","txt");
			  }elseif($file_orig_type == "media"){
			  	$type_of_file_must_be = "audio";
			  	$images_extentions_array = array("mp3","wav","wmv","ram","rm");
			  }
			  if(!in_array($ext_name,$images_extentions_array)){
			  	if($file_orig_type == "image"){
			  		$status.="Only images is allowed (.gif .png .jpeg .jpg .jpe .bmp)";
			  	}elseif($file_orig_type == "video"){
			  		$status.="Only video is allowed (.flv .mov .mpeg .mp4 .avi)";
			  	}elseif($file_orig_type == "document"){
			  		$status.="Only documents is allowed (.pdf .doc .docx .txt)";
			  	}elseif($file_orig_type == "media"){
			  		$status.="Only media is allowed (.mp3 .wav .wmv .ram .rm)";
			  	}
			  	return false;
			  }else{
		
			  	list($file_type_new) = explode("/",$file_type);
		
			  	  $flv_application = ( $file_type == 'application/octet-stream' && $ext_name == 'flv') ? true : false;
		
			  	  if($file_type_new != $type_of_file_must_be && !$flv_application){
			  	  	if($file_orig_type == "image"){
				  	  	$status.="Please insert a valid image";
			  	  	}elseif($file_orig_type == "video"){
				  	  	$status.="Please insert a valid video";
			  	  	}elseif($file_orig_type == "document"){
				  	  	$status.="Please insert a valid file";
			  	  	}elseif($file_orig_type == "media"){
				  	  	$status.="Please insert a valid media";
			  	  	}
					return false;
			  	  }else{
					  if (file_exists($path.$old_name)) @unlink($path.$old_name);
					  if (file_exists($path."thumbs/".$old_name)) @unlink($path."thumbs/".$old_name);
					  while (file_exists($path.$destination_file)) $destination_file=$basic_name.'['.++$i.']'.($ext_name!=''?".$ext_name":'');
					  $result['name']=$destination_file;
					  $result['size']=$file_size;
					  $result['ext_name']=$ext_name;
		
					  if (!move_uploaded_file($temp_name,$path.$destination_file)){
					   $status.="Error in moving the temp file \"$temp_name\" of \"$real_name ($file_size bytes)\"";
					   return false;
					  }
					  return $result;
			  	  }
			  }
		 }
		}
}

$FILES_FOLDER = "../uploads/common/";

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$editor1 = true;

ob_start();

include "_top.php";

ob_clean();

// Required: anonymous function reference number as explained above.
$funcNum = $_GET['CKEditorFuncNum'] ;

// Optional: instance name (might be used to load a specific configuration file or anything else).
$CKEditor = $_GET['CKEditor'] ;

// Optional: might be used to provide localized messages.
$langCode = $_GET['langCode'] ;

// ############################################################
// Check the $_FILES array and save the file. Assign the correct path to a variable ($url).
$url = '';
// Usually you will only assign something here if the file could not be uploaded.
$message = '';
// ############################################################

// $pic = movepic('upload', FILES_FOLDER, true );

$pic = file_upload_direct('image','upload',$FILES_FOLDER,$errorMsg );

//$aaa = implode(', ', $_FILES['upload'] );
// $file1=$result['name'];


if( $pic )
{
	echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction('$funcNum', '".$FILES_FOLDER.$pic['name']."', '');</script>";
}
else
{
	echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction('$funcNum', '', 'Error upload image.');</script>";
}
exit;



