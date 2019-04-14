<?php
# Upload functions

# Splits a file into basic name and extension
function fname_split($file){
 if (strstr($file,'.')){
  preg_match('/(^.+)\.(.*$)/',$file,$matches);
  list(,$basic_name,$ext_name)=$matches;
 } else {
  $basic_name=$file;
 }
 $basic_name=preg_replace('/(\[\d+\])+$/','',$basic_name);
 return array($basic_name,$ext_name);
}

# Creates the field for edit-upload file

function file_field($myvar,$path,$val,$flag=""){
 global $$myvar;
 echo "<input type=\"text\" name=\"$myvar\" value=\"",textencode($val),"\" class=\"inline_short\" />\n";
 if ($val!='' && $flag == "") echo "<a href=\"#\" onclick=\"window.open('$path","$val','','width=500,height=400,scrollbars,resizable')\"><img src=\"images/view.gif\" border=\"0\"></a> ";
 echo "<input type=\"hidden\" name=\"old","$myvar\" value=\"",textencode($val),"\">\n";
 echo "<input type=\"file\" name=\"file","$myvar\">\n";
}

# uploads a file
# - Needs: fname_split
function file_upload ($file_orig_type,$myvar,$path,&$status,$max_size=''){ 
 global $HTTP_POST_FILES,$HTTP_POST_VARS;
 $old_name=$_POST['old'.$myvar];
 $file_name=$_POST[$myvar];
 $real_name=$_FILES['file'.$myvar]['name'];
 $temp_name=$_FILES['file'.$myvar]['tmp_name'];
 $file_size=$_FILES['file'.$myvar]['size'];
 $file_type=$_FILES['file'.$myvar]['type'];

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
	  	$images_extentions_array = array("pdf","doc","dox","txt");	  }elseif($file_orig_type == "pdf"){	  	$type_of_file_must_be = "application";	  	$images_extentions_array = array("pdf");
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
	  		$status.="Only documents is allowed (.pdf .doc .docx .txt)";	  	}elseif($file_orig_type == "pdf"){	  		$status.="Only documents is allowed (.pdf)";
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
?>