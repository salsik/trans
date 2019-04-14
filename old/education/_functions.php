<?php 

function _sendSMSrequest($action, $options = array()) {

	$options = (array) $options;
	$options['user'] = _SMS_USERNAME;
//	$options['pass'] = md5(_SMS_USERNAME.md5(_SMS_PASSWORD));
	$options['pass'] = _SMS_PASSWORD;
	$options['action'] = $action;
	
//	$url = _SMS_API .'?'. http_build_query( $options );
	$postdata = http_build_query( $options );
	
	$opts = array('http' =>
	    array(
	        'method'  => 'POST',
	        'header'  => 'Content-type: application/x-www-form-urlencoded',
	        'content' => $postdata
	    ),
	);
	
	$return = array();
	do {

		$context  = stream_context_create($opts);
		$result = @file_get_contents( _SMS_API , false, $context);

//	echo " $result ";
		$response = @json_decode($result, true);
		if(!is_array( $response )) {
			$return['error'] = 'Unknown SMS API response!';
			break;
		}
		$return = $response;
		
		if( $return['error'] ) {
			break;
		}

		$return['ok'] = true;

	} while(false);

	$return['ok'] = ( $return['error'] ) ? false : true;
	return $return;
}

function my_array_map($func, $array)
{
	foreach($array as $k=>$v)
	{
		$array[$k] = (is_array($v)) ? $v : $func($v);
	}
	return $array;
}


function clearHTML($str)
{
	return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8', false);
}

function redirectURL($url = '')
{
	header("Location: ".BASE_URL."$url");
	exit;
}


function is_Email($email)
{
	//return preg_match('/^[a-z_][a-z0-9\._-]*@([a-z0-9_-]+\.)+[a-z]{2,5}$/i', $email);
	return preg_match('/^[a-z0-9][a-z0-9\._-]*@([a-z0-9_-]+\.)+[a-z]+$/i', $email);
}

function clearPost()
{
	$_POST = my_array_map('clearHTML', $_POST);
}


# Shows maximum word in a string, and adds a link to trancated string
function summarize_my_text($paragraph, $limit,$link="")
{
	$text = '';
	$words = 0;
	$tok = strtok($paragraph, ' ');
	while($tok)
	{
		$text .= " $tok";
		$words++;
		if($words >= $limit) {
			$text .= " ...";
			break;
		}
		$tok = strtok(' ');
	}
	$text .= $link;
	return ltrim($text);
}



function cleanTitleURL( $str )
{
//	$str = strtolower("$str");
	$str = "$str";
	
	$str = preg_replace("/[^ءاأإآئؤبتثجحخدذرزسشصضطظعغفقكلمنهويىةa-z0-9_\s-]/i", " ", $str );
	$str = preg_replace("/\s+/", "-", trim( $str ) );

	return $str;
}
function pager($page, $total, $perpage)
{
	$pager = array();
	$pager['total'] = $total;
	$pager['perpage'] = intval($perpage);
	$pager['page'] = intval($page );
	$pager['pages'] = @ceil( $pager['total'] / $pager['perpage'] );

	//$page = intval($page);
	if( $pager['page'] < 1) $pager['page'] = 1;
	if( $pager['page'] > $pager['pages']) $pager['page'] = $pager['pages'];

	$pager['offset'] = ($pager['page'] - 1) * $pager['perpage'];

	return $pager;
}

function pager_link($pager, $link1, $link2 = '')
{
	$pager_count = 5;

	$lang = array();
	$lang['first'] = '<div class="previous"><a href="%1$s">First</a></div>';
	$lang['prev'] = '<div class="previous"><a href="%1$s">Previous</a></div>';
//	$lang['prev1'] = '<div class="previous"><span>Previous</span></div>';
	$lang['prev_dot'] = '<div class="previous"><span>...</span></div>';
	
	$lang['link'] = '<div class="page"><a href="%1$s">%2$s</a></div>';
	$lang['current'] = '<div class="page selected"><span>%1$s</span></div>';
	
	$lang['next_dot'] = '<div class="next"><span>...</span></div>';
	$lang['next'] = '<div class="next"><a href="%1$s">Next</a></div>';
//	$lang['next1'] = '<div class="next"><span>Next</span></div>';
	$lang['last'] = '<div class="next"><a href="%1$s">Last</a></div>';
	
	//$lang['holder'] = '<div class="pagination"><span class="page_no">Page %1$s of %2$s</span><ul class="pag_list">%3$s</ul></div>';
	$lang['holder'] = '<div class="pages">%3$s</div>';


	$ceil = ceil($pager_count / 2) - 1;
	$page = $pager['page'] - $ceil;

	if( $page < 1  ) $page = 1;
	if( $pager['pages'] >= $pager_count && $page + $pager_count > $pager['pages'] )
	{
		$page = $pager['pages'] - $pager_count + 1;
	}

	$ii = 1;

	$str = '';

	if( $pager['pages'] > 1 )
	{
		if( $pager['page'] - 1 < 1 )
		{
			$str .= $lang['prev1'];
		}
		else
		{
			$str .= sprintf($lang['first'], $link1 . "1" . $link2 );
			$str .= sprintf($lang['prev'], $link1 . ($pager['page'] - 1) . $link2 );
		}

		if( $page > 1 )
		{
			$str .= $lang['prev_dot'];
		}
	
		//$page = '';
		while($page <= $pager['pages'] && $ii <= $pager_count)
		{
			if($page == $pager['page'])
			{
				$str .= sprintf($lang['current'], $pager['page'] );
			}
			else
			{
				$str .= sprintf($lang['link'], $link1 . $page . $link2, $page );
			}
	
			$page++;
			$ii++;
		}
	
		if( $page < $pager['pages'] )
		{
			$str .= $lang['next_dot'];
		}

		if( $pager['page'] + 1 > $pager['pages'] )
		{
			$str .= $lang['next1'];
		}
		else
		{
			$str .= sprintf($lang['next'], $link1 . ($pager['page'] + 1) . $link2 );
			$str .= sprintf($lang['last'], $link1 . $pager['pages'] . $link2 );
		}
	}

	return sprintf($lang['holder'], $pager['page'], $pager['pages'], $str);
}

function getFileTypeIcon($type)
{
	$type = strtoupper($type);
	
	$type = explode('.', $type);
	$type = array_pop($type);
	
	switch($type)
	{
		case 'AC3':
		case 'ACE':
		case 'ADE':
		case 'ADP':
		case 'AI':
		case 'AIFF':
		case 'AU':
		case 'AVI':
		case 'BAT':
		case 'BIN':
		case 'BMP':
		case 'BUP':
		case 'CAB':
		case 'CAT':
		case 'CHM':
		case 'CSS':
		case 'CUE':
		case 'DAT':
		case 'DCR':
		case 'DER':
		case 'DIC':
		case 'DIVX':
		case 'DIZ':
		case 'DLL':
		case 'DOC':
		case 'DOCX':
		case 'DOS':
		case 'DVD':
		case 'DWG':
		case 'DWT':
		case 'EMF':
		case 'EXC':
		case 'FON':
		case 'GIF':
		case 'HLP':
		case 'HTML':
		case 'IFO':
		case 'INF':
		case 'INI':
		case 'INS':
		case 'IP':
		case 'ISO':
		case 'ISP':
		case 'JAVA':
		case 'JFIF':
		case 'JPEG':
		case 'JPG':
		case 'LOG':
		case 'M4A':
		case 'MID':
		case 'MMF':
		case 'MMM':
		case 'MOV':
		case 'MOVIE':
		case 'MP2':
		case 'MP2V':
		case 'MP3':
		case 'MP4':
		case 'MPE':
		case 'MPEG':
		case 'MPG':
		case 'MPV2':
		case 'NFO':
		case 'PDD':
		case 'PDF':
		case 'PHP':
		case 'PNG':
		case 'PPT':
		case 'PPTX':
		case 'PSD':
		case 'RAR':
		case 'REG':
		case 'RTF':
		case 'SCP':
		case 'THEME':
		case 'TIF':
		case 'TIFF':
		case 'TLB':
		case 'TTF':
		case 'TXT':
		case 'UIS':
		case 'URL':
		case 'VBS':
		case 'VCR':
		case 'VOB':
		case 'WAV':
		case 'WBA':
		case 'WMA':
		case 'WMV':
		case 'WPL':
		case 'WRI':
		case 'WTX':
		case 'XLS':
		case 'XLSX':
		case 'XML':
		case 'XSL':
		case 'ZAP':
		case 'ZIP':
			return $type.'.png';
			break;
		default:
			return 'Default.png';
	}
}