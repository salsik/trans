<?php

# Date and Time Functions Go Here

//Difference between 2 dates (dates are given in seconds, interval is used as unit) 
function datediff($interval,$date1,$date2){

 switch($interval){
 case 'Y': // Year
 case 'y':
  return ceil(($date2-$date1)/31536000); break;
 case 'm': // Month 
 case 'n':
  return ceil(($date2-$date1)/2592000); break;
 case 'd': // Day 
 case 'j':
  return ceil(($date2-$date1)/86400); break;
 case 'H': // Hour 
 case 'h':
 case 'g':
 case 'G':
  return ceil(($date2-$date1)/3600); break;
 case 'i': // Minute 
  return ceil(($date2-$date1)/60); break;
 case 's': // Second
  return $date2-$date1; break;
 }
}


# Take date/datetime variables and return a MySQL string 'YYYY-MM-DD HH:MM:SS' 
function dateserial($year,$month="01",$day="01",$hour="00",$minute="00",$second="00"){
 if ($year=="") return "";
 $temp = $year."-".(strlen($month)<2?"0":"").$month."-".(strlen($day)<2?"0":"").$day;
 if ($hour!="00" or $minute!="00" or $second!="00") $temp.= " ".timeserial($hour,$minute,$second);
 return $temp;
}

# Take time variables and return a MySQL string 'HH:MM:SS' 
function timeserial($hour,$minute="00",$second="00"){
 if ($hour=="") return "";
 return (strlen($hour)<2?"0":"").$hour.":".(strlen($minute)<2?"0":"").$minute.":".(strlen($second)<2?"0":"").$second;
}


# Take datetime format and return timestamp(14) 
function timetots($timestamp){
 return mktime(substr($timestamp,11,2),substr($timestamp,14,2),substr($timestamp,17,2),substr($timestamp,5,2),substr($timestamp,8,2),substr($timestamp,0,4));
}

# Take timestamp(14) format and return datetime 
function tstotime($timestamp){
 return date("Y-m-d H:i:s", $timestamp);
}

# Take month number and boolean for abbreviation and returns full (default) or abriviated month name
function monthname($month_number,$abr=false){
 switch($month_number){
  case 1: return $abr?'Jan':'January'; break;
  case 2: return $abr?'Feb':'February'; break;
  case 3: return $abr?'Mar':'March'; break;
  case 4: return $abr?'Apr':'April'; break;
  case 5: return $abr?'May':'May'; break;
  case 6: return $abr?'Jun':'June'; break;
  case 7: return $abr?'Jul':'July'; break;
  case 8: return $abr?'Aug':'August'; break;
  case 9: return $abr?'Sep':'September'; break;
  case 10: return $abr?'Oct':'October'; break;
  case 11: return $abr?'Nov':'November'; break;
  case 12: return $abr?'Dec':'December'; break;
 }
}

# Adds an interval to a date variable, timestamp(14) format.
function dateadd($interval,$number,$date){
 switch($interval){
 case 'Y': // Year
 case 'y':
  return mktime(date("H",$date),date("i",$date),date("s",$date),date("m",$date),date("d",$date),date("Y",$date)+$number); break;
 case 'm': // Month 
 case 'n':
  return mktime(date("H",$date),date("i",$date),date("s",$date),date("m",$date)+$number,date("d",$date),date("Y",$date)); break;
 case 'd': // Day 
 case 'j':
  return mktime(date("H",$date),date("i",$date),date("s",$date),date("m",$date),date("d",$date)+$number,date("Y",$date)); break;
 case 'H': // Hour 
 case 'h':
 case 'g':
 case 'G':
  return mktime(date("H",$date)+$number,date("i",$date),date("s",$date),date("m",$date),date("d",$date),date("Y",$date)); break;
 case 'i': // Minute 
  return mktime(date("H",$date),date("i",$date)+$number,date("s",$date),date("m",$date),date("d",$date),date("Y",$date)); break;
 case 's': // Second
  return mktime(date("H",$date),date("i",$date),date("s",$date)+$number,date("m",$date),date("d",$date),date("Y",$date)); break;
 }
}

# Returns the format 'dd MMM yyyy'
function humandate($d){
	if ( $d != "" && !empty($d) && $d != '0000-00-00 00:00:00' && $d != '0000-00-00'){ 
		$t = split(' ',$d);
		$date=$t[0];
		$time=$t[1];
		$n=split('-',$date);
		
		return date("d M Y",mktime(0,0,0,$n[1],$n[2],$n[0]))." ".substr($time,0,5);
	}
	return false;
}

# Take timestamp(14) format and return the difference from now in weeks, days, hours, minutes and seconds 
function timepassed($time)
{
	$timestring = '';
	$time = time()-$time;

	$weeks = $time/604800;
	$days = ($time%604800)/86400;
	$hours = (($time%604800)%86400)/3600;
	$minutes = ((($time%604800)%86400)%3600)/60;
	$seconds = (((($time%604800)%86400)%3600)%60);
	if(floor($weeks)) $timestring .= floor($weeks)." weeks ";
	if(floor($days)) $timestring .= floor($days)." days ";
	if(floor($hours)) $timestring .= floor($hours)." hours ";
	if(floor($minutes)) $timestring .= floor($minutes)." minutes ";
	if(!floor($minutes)&&!floor($hours)&&!floor($days)) $timestring .= floor($seconds)." seconds ";
	return $timestring;
}

# Returns time in micro-seconds
function getmicrotime()
{
	list($usec, $sec) = explode(' ',microtime());
	return ((float)$usec + (float)$sec);
}

function isbisextile($year){
	if ((($year%4==0)&&($year%100!=0))||($year%400==0))
		return true;
	else
		return false;
}

?>