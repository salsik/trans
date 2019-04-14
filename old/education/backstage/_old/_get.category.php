<?php

if( !defined('isAjaxRequest') ) {
	exit;
}

$keyword = $_GET['q'];

$keywordSQL = mysql_real_escape_string($keyword);
$keywordSQL = str_replace(' ', '% %', $keywordSQL);

$strSQL = "SELECT * FROM (
	(SELECT 'category' as `table`, category.id, category.title FROM category WHERE category.title LIKE '%$keywordSQL%')
	UNION
	(SELECT 'category_sub' as `table`, category_sub.id, CONCAT(category.title, ' / ', category_sub.title) as title 
		FROM category_sub 
		LEFT JOIN category ON(category.id=category_sub.cat_id)
		WHERE category_sub.title LIKE '%$keywordSQL%')
	) cats ORDER BY cats.title ASC
";
$strSQL = "SELECT * FROM (
	(SELECT 'category' as `table`, category.id, category.title, category.title as category_title, '' as category_sub_title FROM category )
	UNION
	(SELECT 'category_sub' as `table`, category_sub.id, CONCAT(category.title, ' / ', category_sub.title) as title , '' as category_title, category_sub.title as category_sub_title
		FROM category_sub 
		LEFT JOIN category ON(category.id=category_sub.cat_id)
		)
	) cats 
	WHERE cats.category_title LIKE '%$keywordSQL%'
		OR cats.category_sub_title LIKE '%$keywordSQL%'
	ORDER BY cats.title ASC
";


//die($strSQL);
$objRS = mysql_query($strSQL);
echo mysql_error();
if( $objRS && mysql_num_rows($objRS) )
{
	while ($row=mysql_fetch_object($objRS))
	{
		$id = ($row->table == 'category') ? $row->id*-1 : $row->id;
		echo "{$row->title}|{$id}\n";
	}
}
exit;
