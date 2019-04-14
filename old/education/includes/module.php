<?php

function selected($value1, $value2, $value3 = true) {
	return ($value1 == $value2 && $value3) ? ' selected="SELECTED" ' : '';
}
function selected2($value1, $value2, $value3 = false) {
	return ($value1 == $value2 || $value3) ? ' selected="SELECTED" ' : '';
}
function checked($value1, $value2, $value3 = true) {
	return ($value1 == $value2 && $value3) ? ' checked="CHECKED" ' : '';
}
function checked2($value1, $value2, $value3 = true) {
	return ($value1 == $value2 || $value3) ? ' checked="CHECKED" ' : '';
}
function nor($value1, $value2, $isset = false) {
	if($isset) {
		return (isset($value1)) ? $value1 : $value2;
	} else {
		return (!empty($value1)) ? $value1 : $value2;
	}
}

function numberFormat($number, $decimals = 2, $formated = true)
{
	$number = floatval($number);
//	if($decimals === true ) return number_format($number);

	$number_array = explode('.', "$number");
	$number_array[1] = intval($number_array[1]);
	$d = ($number_array[1]) ? strlen("{$number_array[1]}") : 0;
	$decimals = min($decimals, $d);
	return number_format($number, $decimals);
}
function getDataByID($table, $id, $where = ''){
	$id = intval($id);
	$where = ($where) ? " AND $where " : '';
	$res1=mysql_query("SELECT * FROM `$table` WHERE id='{$id}' $where LIMIT 1");
	if( $res1 && mysql_num_rows($res1)) {
		return mysql_fetch_assoc($res1);
	}
	return false;
}
function getDataByIDs($table, $ids, $where = ''){
	if(!is_array( $ids )) {
		$ids = array( $ids );
	}
	$ids = array_map('intval', $ids);

	$return = array();
	$where = ($where) ? " AND $where " : '';
	$res1=mysql_query("SELECT * FROM `$table` WHERE id IN (".implode(',', $ids).") $where ");
	if( $res1 && mysql_num_rows($res1)) {
		while($row = mysql_fetch_assoc($res1)){
			$return[ $row['id'] ] = $row;
		}
	}
	return $return;
}
function getDataCount($table, $where = ''){
	$where = ($where) ? " WHERE $where " : '';
	$res1=mysql_query("SELECT count(*) as count FROM `$table` {$where} ");
	if( $res1 && mysql_num_rows($res1)) {
		return mysql_result($res1, 0, 0);
	}
	return false;
}


function getClassWeekDays($class, $view = false, $DESC = false ) {
	
	$start = strtotime( $class['date_start'] );
	$end = strtotime( $class['date_end'] );
	$today = time();
	$days = array();
	
	do {
		$day = strtolower(date('l', $start));
		
		$ok = ( !$view || ($view == 'new' && $start>=$today) || ($view == 'old' && $start<=$today) );

		if($class['week_' . $day] && $ok ) {
			$days[] = array(
				'day' => $day,
				'date' => date('Y-m-d', $start),
				'time' => $start,
				'start' => $class['week_' . $day .'_start'],
				'end' => $class['week_' . $day .'_end'],
			);
		}
		$start += 86400;
	} while( $start <= $end );

	if( $DESC ) {
		$days = array_reverse($days);
	}
	return $days;
}

function getRangeDays( $start, $end, $DESC = false ) {
	
	$days = array();
	
	do {
		$day = date('Y-m-d', $start);
		$days[$day] = $day;
		$start += 86400;
	} while( $start <= $end );

	if( $DESC ) {
		$days = array_reverse($days);
	}
	return $days;
}

function emptyVars() {
	$args = func_get_args();
	foreach($args as $arg) {
		if(!empty($arg)) {
			return false;
		}
	}
	return true;
}


function convert_number_to_words($number) {
   
    $hyphen      = '-';
    $conjunction = ' and ';
    $separator   = ', ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = array(
        0                   => 'zero',
        1                   => 'one',
        2                   => 'two',
        3                   => 'three',
        4                   => 'four',
        5                   => 'five',
        6                   => 'six',
        7                   => 'seven',
        8                   => 'eight',
        9                   => 'nine',
        10                  => 'ten',
        11                  => 'eleven',
        12                  => 'twelve',
        13                  => 'thirteen',
        14                  => 'fourteen',
        15                  => 'fifteen',
        16                  => 'sixteen',
        17                  => 'seventeen',
        18                  => 'eighteen',
        19                  => 'nineteen',
        20                  => 'twenty',
        30                  => 'thirty',
        40                  => 'fourty',
        50                  => 'fifty',
        60                  => 'sixty',
        70                  => 'seventy',
        80                  => 'eighty',
        90                  => 'ninety',
        100                 => 'hundred',
        1000                => 'thousand',
        1000000             => 'million',
        1000000000          => 'billion',
        1000000000000       => 'trillion',
        1000000000000000    => 'quadrillion',
        1000000000000000000 => 'quintillion'
    );
   
    if (!is_numeric($number)) {
        return false;
    }
   
    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . convert_number_to_words(abs($number));
    }
   
    $string = $fraction = null;
   
    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }
    if( $fraction == "0") {
    	$fraction = null;
    }
   
    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . convert_number_to_words($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= convert_number_to_words($remainder);
            }
            break;
    }
   
    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }
   
    return $string;
}
function echo_edit_thumb($img, $width, $height, $imgdir, $thdir, $id, $table, $img_field, $img_field_thumb)
{
	if( !$img )
	{
		return false;
	}
	
	echo "<a href='#' onclick=\"window.open('image_crop.php?id=$id&imf=$img_field&thf=$img_field_thumb&imgdir=$imgdir&thdir=$thdir&table=$table&thwidth=$width&thheight=$height','application_user_view','location=yes,status=yes,scrollbars=yes,width=1000,height=550,top=200,left=250,toolbar=no,titlebar=no,directories=no,menubar=no,resizable=yes');\"> Edit Thumb </a>";
}

//function search_in_fields($keyword, $fields )
//{
//	$keyword = preg_replace('/\s+/', '%', trim($keyword));
////	$keyword = mysql_real_escape_string( $keyword );
//
//	$fields = array_map('trim', explode(',', $fields));
//	if( count($fields) == 1)
//	{
//		return " {$fields[0]} LIKE '%$keyword%' ";
//	}
//
//	$fields = ''. implode(", ' ', ", $fields) .'';
//
//	return " CONCAT($fields) LIKE '%$keyword%' ";
//}

if( !function_exists( 'scale_image' ))
{
	function scale_image($filename, $width, $height = 0)
	{
		if( !is_file( $filename ))
		{
			return '&nbsp;';
		}
	
		$info = @getimagesize($filename);
		if( ! $info )
		{
			return '&nbsp;';
		}
	
		$ratio = $info[0] / $info[1];
		
		if( $info[0] > $width )
		{
			$info[0] = $width;
			$info[1] = intval( $width / $ratio );
		}
		
		if( $height > 0 && $info[1] > $height )
		{
			$info[1] = $height;
			$info[1] = intval( $height * $ratio );
		}
	
		return "<img src='$filename' border='0' width='$info[0]' height='$info[1]' >";
	}
}


function str_makerand ($minlength, $maxlength, $useupper, $usespecial, $usenumbers)
{
/*
Author: Peter Mugane Kionga-Kamau
http://www.pmkmedia.com

Description: string str_makerand(int $minlength, int $maxlength, bool $useupper, bool $usespecial, bool $usenumbers)
returns a randomly generated string of length between $minlength and $maxlength inclusively.

Notes:
- If $useupper is true uppercase characters will be used; if false they will be excluded.
- If $usespecial is true special characters will be used; if false they will be excluded.
- If $usenumbers is true numerical characters will be used; if false they will be excluded.
- If $minlength is equal to $maxlength a string of length $maxlength will be returned.
- Not all special characters are included since they could cause parse errors with queries.

Modify at will.
*/
$charset = "abcdefghijklmnopqrstuvwxyz";
if ($useupper) $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
if ($usenumbers) $charset .= "0123456789";
if ($usespecial) $charset .= "~@#$%^*()_+-={}|]["; // Note: using all special characters this reads: "~!@#$%^&*()_+`-={}|\\]?[\":;'><,./";
if ($minlength > $maxlength) $length = mt_rand ($maxlength, $minlength);
else $length = mt_rand ($minlength, $maxlength);
for ($i=0; $i<$length; $i++) $key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
return $key;
}
# get one field from md5
function getmdfield($id,$field,$table){
 $strSQL="select $field as thename from $table where MD5(id)='".$id."'";
 $objRS=mysql_query($strSQL);
 if ($row=mysql_fetch_object($objRS)) return $row->thename; else return "N/A";
}
# module.php
function CountRows($id,$where,$table){
 if (!isid($id)) $id=0;
 $strSQL="select COUNT(*) as thename from $table where $where=$id";
 $objRS=mysql_query($strSQL);
 if ($row=mysql_fetch_object($objRS)) return $row->thename; else return "0";	
}
# to show what escaped from sql injection (anti sqlencode)
function textencode($strng,$frontHTML='0'){
// $from=array('&amp;#','&amp; ','&amp;');
// $to=array('&#','&amp;','&amp; ');
// $temp=str_replace($from,$to, my_htmlspecialchars($strng));
 
 $temp = my_htmlspecialchars($strng);
 if(!$frontHTML)
	 return $temp;
 else 
	 return nl2br($temp);
}


#redirect to another page
function redirect($page){
 header("Location: $page");
 die('Redirected to $page');
}

# POST OR GET
 function getHTTP($name){
  global $HTTP_POST_VARS,$HTTP_GET_VARS;
  if ($_POST[$name]=="") return trime($_GET[$name]); else return trime($_POST[$name]);
 }

# Remove html
function rmhtml($string){
 $temp=preg_replace("/\<br\>/", "\r\n", $string);
 return preg_replace("/\<[^>]+\>/", "", $temp);
}
function summarizeHtml($text="",$words="20",$link="..."){
			preg_match('/^([^.!?\s]*[\.!?\s]+){0,'.$words.'}/', strip_tags($text), $abstract);
			return $abstract[0].$link;
	}
# Shows maximum word in a string, and adds a link to trancated string
function summarize($paragraph, $limit)
{
	$text = '';
	$words = 0;
	$tok = strtok($paragraph, ' ');
	while($tok)
	{
		$text .= " $tok";
		$words++;
		if($words >= $limit) {
			$text .="...";
			break;
		}
		$tok = strtok(' ');
	}

	return ltrim($text);
}


# Turns a string into a database name
function nameit($strng,$maxlen=100,$noname=array()){
 $temp = preg_replace('/[^\w]/','',$strng);
 $temp = substr($temp,0,$maxlen);
 $name=$temp;
 while (in_array($name,$noname)){
  $name=substr($temp,0,$maxlen-strlen(@++$counter)).$counter;
 }
 return $name;
}

# get one field
function getfield($id,$field,$table){
 if (!isid($id)) $id=0;
 $strSQL="select $field as thename from $table where id=$id";
 $objRS=mysql_query($strSQL);
 if ($row=mysql_fetch_object($objRS)) return $row->thename; else return "N/A";
}
# get one field updated
function getfieldupdated($id,$field,$table,$fieldToSelect){
 $strSQL="select $fieldToSelect as thename from $table where $field='$id'";
 $objRS=mysql_query($strSQL);
 if ($row=mysql_fetch_object($objRS)) return $row->thename; else return "N/A";
}
# gets fields from (table,condition,field1,...)
function getfields(){

 $table=func_get_arg(0);
 $condition=func_get_arg(1);
 for ($i=2;$i<func_num_args();$i++) $fields.=($fields==""?'':',').func_get_arg($i);
 
 $strSQL="select $fields from $table where $condition";
 $objRS=mysql_query($strSQL);
 if (!($row=mysql_fetch_object($objRS))) for ($i=2;$i<func_num_args();$i++) $row->{func_get_arg($i)}='N/A';
 
 return $row;
}


# conditional display
function condisplay ($var,$text){
 if (trim($var)!="" || $var===true) return $text; else return "";
}

# generate random code (not unique!!!)
function generatecode($int){
 while (strlen($temp)<$int){
  switch (rand(1,3)){
  case 1: $temp.=chr(rand(65,90)); break;
  case 2: $temp.=chr(rand(97,122)); break;
  case 3: $temp.=chr(rand(48,57)); break;
  }
  $temp=preg_replace('/0|o|O|l|I|L|i|1/','',$temp);
 }
 return $temp;
}

# verify if it's a valid id or not
function isid($string){
 if (!$string) return false;
 for ($i=0;$i<strlen($string);$i++){
  $temp=ord(substr($string,$i,1));
  if ($temp<48 || $temp>57) return false;
 }
 return true;
}

# to escape from sql injection
function sqlencode($strng){
	$strng = my_htmlspecialchars( $strng );
 return mysql_escape_string($strng);
// return addslashes($strng);
}
function my_htmlspecialchars($strng, $flag = NULL) {
	if( $flag === NULL ) {
		$flag = ENT_QUOTES;
	}
	return htmlspecialchars($strng, $flag, 'UTF-8', false);
}

# verify if it's a valid URL or not
function isurl($strng){
 return preg_match('/^http:\/\/[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/i',$strng);
}

# verify if it's a valid email address or not
function isemail($strng){
 return preg_match('/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/i',$strng);
}

# NO slash , NO spavce
function trime($strng){
 return stripslashes(trim($strng));
}
function trime_editor($strng){
 return stripslashes(trim($strng));
}
#prepare mail
function preparemail ($from="" , $message, &$mime_boundary, &$headers, &$final_message){
if ($from=="") $from=EMAIL_FROM;
    $mime_boundary  =  "<<<--==+X[".md5(time())."]";
    $headers = "From: ".$from."\r\n";
    $headers  .=  "MIME-Version:  1.0\r\n";
    $headers  .=  "Content-Type: multipart/mixed;\r\n";
    $headers  .=  "  boundary=\"".$mime_boundary."\"";     
    $final_message  .=  "This  is  a  multi-part message  in  MIME  format.\r\n";
    $final_message  .=  "\r\n";
    $final_message  .=  "--".$mime_boundary."\r\n";     
    $final_message  .=  "Content-Type:text/html; charset=\"iso-8859-1\"\r\n";
    $final_message  .=  "Content-Transfer-Encoding:  7bit\r\n";
    $final_message  .=  "\r\n";     
    $final_message  .= $message;;
    $final_message  .=  "\r\n";
    $final_message  .=  "--".$mime_boundary."\r\n"; 
}

#mail with attachment
function mail_attachment ($attachment, $mime_boundary, &$final_message){
   
    $fileatt = $attachment; // Path to the file                 
    $start=    strrpos($attachment, '/') == -1 ? strrpos($attachment, '//') : strrpos($attachment, '/')+1;
    $fileatt_name = substr($attachment, $start, strlen($attachment)); // Filename that will be used for the file as the     attachment
    $file = fopen($fileatt,'rb');
    $data = fread($file,filesize($fileatt));
    fclose($file);
    $data = chunk_split(base64_encode($data));
    $final_message  .=  "Content-Type:  application/octet-stream;\r\n";
    $final_message  .=  "  name=\"".$fileatt_name."\"\r\n";
    $final_message  .=  "Content-Transfer-Encoding: base64\r\n";
    $final_message  .=  "Content-Disposition:  attachment;\r\n";
    $final_message  .=  "  filename=\"".$fileatt_name."\"\r\n";
    $final_message  .=  "\r\n";
    $final_message  .=  $data;
    $final_message  .=  "\r\n";
    $final_message  .=  "--".$mime_boundary."\r\n"; 
}

#send mail
function sendmail($to,$subject,$message,$headers=""){
if($headers==""){
 $headers  = "MIME-Version: 1.0\r\n";
 $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
 $headers .= "From: info@ticklemybrain.com \r\n";
 }
 mail($to, $subject, $message, $headers);
}
//secure insert into database
function secure_insert($text="")
{
    $text_new = mysql_real_escape_string(htmlentities($text,ENT_QUOTES));
    return $text_new;
}

//random password generator
function get_random_string($valid_chars, $length)
{
    // start with an empty random string
    $random_string = "";

    // count the number of chars in the valid chars string so we know how many choices we have
    $num_valid_chars = strlen($valid_chars);

    // repeat the steps until we've created a string of the right length
    for ($i = 0; $i < $length; $i++)
    {
        // pick a random number from 1 up to the number of valid chars
        $random_pick = mt_rand(1, $num_valid_chars);

        // take the random character out of the string of valid chars
        // subtract 1 from $random_pick because strings are indexed starting at 0, and we started picking at 1
        $random_char = $valid_chars[$random_pick-1];

        // add the randomly-chosen char onto the end of our string so far
        $random_string .= $random_char;
    }

    // return our finished random string
    return $random_string;
}


function echo_SetOrder( $query = '', $id = 0, $p = 1 )
{
	?><center>
	<a href="?<?php echo $query;?>action=up&rank=<? echo $id; ?>&p=<?=$p?>"><img src="images/up.png" alt="Go Up" width="27" height="27" border="0" /></a>
<br />
	<a href="?<?php echo $query;?>action=down&rank=<? echo $id; ?>&p=<?=$p?>"><img src="images/down.png" alt="Go Down" width="27" height="27" border="0" /></a>
	</center><?php 
}




/*
You can simply use sendmail or this advance usage Method hereunder:
    $mime_boundary='';
    $headers='';
    $final_message='';
    $attach_message='';
   $to='to@example.com';
   $subject='letter title';
   $from='from@chi.com';
   $message='message content goes here';
   preparemail($from, $message,$mime_boundary,$headers,$final_message);// to initiate the header, the message and the mime


// U can repeat the next to steps to attach several files to the message
   $file_path='path/to/the/files/filename.ext';
   mail_attachment($file_path,$mime_boundary,$attach_message);

// When U finish send it normally
    $final_message .=$attach_message;
    sendmail($to, $subject, $final_message, $headers);
*/
