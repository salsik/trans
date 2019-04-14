<?php

$token = $_GET['token'];

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

error_reporting( E_ALL ^ E_NOTICE );
require_once "_startup.php";

$account = false;

$access = false;
if ( strlen ( $token ) == 32 ) {

	$sql = "SELECT users.*
		FROM ( users , users_logins )
		WHERE MD5(users_logins.`api_hash`) = '".mysql_real_escape_string( $token )."'
			AND users_logins.user_id=users.id
		GROUP BY users.id
		LIMIT 1";
	
	$q = mysql_query($sql);

	if ( $q && mysql_num_rows($q) ) {
		$account = mysql_fetch_assoc ($q);
		
		$account = getStudentsAndSchools( $account );
	
		if( !$account['school'] ) {
			$account = false;
		}
	}
}

if( !$account ) {
	echo "Account not found";
	exit;
}

$_school = $account['schools'][ $_REQUEST['school_id'] ];
if( !$_school ) {
	echo "Missing School!";
	exit;
}

$document = array();

do {
	$where = ' TRUE ';
	$where .= " AND `schools`.id = '{$_school['id']}' ";

	$strSQL = getAccessSql($account, 'documents_details', $_REQUEST['id'], $where);

	$q = mysql_query("$strSQL LIMIT 1");

	if( !$q ) {
		echo 'Unable to get document details in our system!';
//		echo "{$strSQL} ". mysql_error();
		exit;
	}
	else if( !mysql_num_rows($q) ) {
		echo 'Document not found!';
		exit;
	}
	else {
		$document = mysql_fetch_assoc($q);
		
		$file= ($document['document']) ? 'uploads/documents/' . $document['document'] : '';
		if( !$file || !is_file($file) ) {
			echo 'File not found!';
			exit;
		}
		
		?>
		<div style="text-align:center">
			<h1 style="text-align:center">Click below link to download your file.</h1>
			<p>
				<a href="<?php echo $file; ?>">Click Here</a>
			</p>
			<p>
				check your downloaded file in the notification tray
			</p>
		</div>
		<?php 
		
	}
} while( false);
