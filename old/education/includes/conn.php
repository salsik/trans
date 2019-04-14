<?php




$objConn = mysql_connect($dbServer,$dbUser,$dbPass) or die( ( defined('isAjaxRequest') ) ? '' : "Could not connect to database. Reason: ".mysql_error() );
mysql_select_db($dbDatabase) or die( ( defined('isAjaxRequest') ) ? '' : "Could not select database. Reason: ".mysql_error() );

mysql_query("SET NAMES 'UTF8';");
mysql_set_charset('utf8',$objConn); 
