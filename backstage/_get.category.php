<?php

if( !defined('isAjaxRequest') ) {
	exit;
}

$keyword = $_GET['q'];

$keywordSQL = mysql_real_escape_string($keyword);
$keywordSQL = str_replace(' ', '% %', $keywordSQL);

$strSQL = "SELECT id, title 
	FROM category 
	WHERE category.title LIKE '%$keywordSQL%' 
	ORDER BY category.title ASC
	";

//die($strSQL);
$objRS = mysql_query($strSQL);
echo mysql_error();
if( $objRS && mysql_num_rows($objRS) )
{
	while ($row=mysql_fetch_assoc($objRS)) {
		echo "{$row['title']}|{$row['id']}\n";
	}
}
exit;
