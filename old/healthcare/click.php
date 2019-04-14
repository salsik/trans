<?php

ob_start();

error_reporting( E_ALL ^ E_NOTICE );
require_once "_startup.php";


$link = '';
do {
	if( !$_GET['ad'] ) {
		$error = 'No "ad" Var!';
		break;
	}

	$ad = @base64_decode( $_GET['ad'] );
	if( !$ad ) {
		$error = 'Invalid "ad" var (not encoded with base64)!';
		break;
	}

	$json = @json_decode( $ad, true );
	if( !$json || !is_array($json) ) {
		$error = 'Invalid "ad" var (not json array)!';
		break;
	}

	// other info in "json" can be used. like: $json['source'], $json['time']

	$json['id'] = intval( $json['id'] );

//	AND (
//		( plan_end_date='0000-00-00' AND plan_impressions<1 AND plan_clicks<1 )
//		OR
//		( plan_clicks > 0 AND plan_clicks > clicks)
//		OR
//		( plan_impressions > 0 AND plan_impressions > impressions)
//		OR
//		( plan_end_date <> '0000-00-00' AND plan_end_date > CURDATE())
//	)
	$strSQL = "SELECT * 
		FROM banners
		WHERE id='{$json['id']}'
			AND status = 'active'
		LIMIT 1";

	$q = mysql_query( $strSQL );

	if( $q && mysql_num_rows($q)) {
		$row = mysql_fetch_assoc($q);
		
		$link = $row['link'];

		mysql_query("UPDATE banners SET clicks=clicks+1 WHERE id='{$row['id']}' LIMIT 1");
	}

} while( false );


if( !$link ) {
	$link = BASE_URL;
}

ob_clean();

header("Location: " . $link);
exit;

