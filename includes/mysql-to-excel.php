<?php

//$DB_TBLName is the table name
//set $Use_Tite = 1 to generate title, 0 not to use title
//if this parameter is included ($w=1), file returned will be in word format ('.doc')
//if parameter is not included, file returned will be in excel format ('.xls')

function mysql_excel($DB_TBLName,$dbDatabase,$Use_Title=1,$w=0){
	$sql = "Select * from $DB_TBLName";
	//define date for title
	$now_date = humandate(date('Y-m-d H:i'));
	//define title for .doc or .xls file
	$title = "Dump For Table $DB_TBLName from Database ".$dbDatabase." on $now_date";
	//execute query
	$result = mysql_query($sql);
	if (isset($w) && ($w==1))
	{
		$file_type = "msword";
		$file_ending = "doc";
	}else {
		$file_type = "vnd.ms-excel";
		$file_ending = "xls";
	}
//header info for browser: determines file type ('.doc' or '.xls')
header("Content-Type: application/$file_type");
header("Content-Disposition: attachment; filename=database_dump.$file_ending");
header("Pragma: no-cache");
header("Expires: 0");

	/*	Start of Formatting for Word or Excel	*/
	
	if (isset($w) && ($w==1)) //check for $w again
	{
		/*	FORMATTING FOR WORD DOCUMENTS ('.doc')   */
		//create title with timestamp:
		if ($Use_Title == 1)
		{
			echo("$title\n\n");
		}
		//define separator (defines columns in excel & tabs in word)
		$sep = "\n"; //new line character
		while($row = mysql_fetch_row($result))
		{
			//set_time_limit(60); // HaRa
			$schema_insert = "";
			for($j=0; $j<mysql_num_fields($result);$j++)
			{
			//define field names
			$field_name = mysql_field_name($result,$j);
			//will show name of fields
			$schema_insert .= "$field_name:\t";
				if(!isset($row[$j])) {
					$schema_insert .= "NULL".$sep;
					}
				elseif ($row[$j] != "") {
					$schema_insert .= "$row[$j]".$sep;
					}
				else {
					$schema_insert .= "".$sep;
					}
			}
			$schema_insert = str_replace($sep."$", "", $schema_insert);
			$schema_insert .= "\t";
			print(trim($schema_insert));
			//end of each mysql row
			//creates line to separate data from each MySQL table row
			print "\n----------------------------------------------------\n";
		}
	}else{
		/*	FORMATTING FOR EXCEL DOCUMENTS ('.xls')   */
		//create title with timestamp:
		if ($Use_Title == 1)
		{
			echo("$title\n");
		}
		//define separator (defines columns in excel & tabs in word)
		$sep = "\t"; //tabbed character
		//start of printing column names as names of MySQL fields
		for ($i = 0; $i < mysql_num_fields($result); $i++)
		{
			echo mysql_field_name($result,$i) . "\t";
		}
		print("\n");
		//end of printing column names
		//start while loop to get data
		while($row = mysql_fetch_row($result))
		{
			//set_time_limit(60); // HaRa
			$schema_insert = "";
			for($j=0; $j<mysql_num_fields($result);$j++)
			{
				if(!isset($row[$j]))
					$schema_insert .= "NULL".$sep;
				elseif ($row[$j] != "")
					$schema_insert .= "$row[$j]".$sep;
				else
					$schema_insert .= "".$sep;
			}
			$schema_insert = str_replace($sep."$", "", $schema_insert);
			$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
			$schema_insert .= "\t";
			print(trim($schema_insert));
			print "\n";
		}
	}
}



function mysql_excel_fields($DB_SQL, $Use_Title = '', $FileName = '', $word = false )
{
	//execute query
	$result = mysql_query( $DB_SQL );
	if ( $word )
	{
		$file_type = "msword";
		$file_ending = "doc";
	}else {
		$file_type = "vnd.ms-excel";
		$file_ending = "xls";
	}
	
	if( !$FileName )
	{
		$FileName = 'database_dump_' . date('Y_m_d_H_i', time());
	}
//header info for browser: determines file type ('.doc' or '.xls')
header("Content-Type: application/$file_type");
header("Content-Disposition: attachment; filename=\"{$FileName}.{$file_ending}\"");
header("Pragma: no-cache");
header("Expires: 0");

	/*	Start of Formatting for Word or Excel	*/
	
	if ( $word ) //check for $word again
	{
		/*	FORMATTING FOR WORD DOCUMENTS ('.doc')   */
		//create title with timestamp:
		if ( $Use_Title )
		{
			echo("$Use_Title\n\n");
		}
		//define separator (defines columns in excel & tabs in word)
		$sep = "\n"; //new line character
		$sep_replace = " "; //new line character
		while($row = mysql_fetch_row($result))
		{
			//set_time_limit(60); // HaRa
			$schema_insert = "";
			for($j=0; $j<mysql_num_fields($result);$j++)
			{
			//define field names
			$field_name = mysql_field_name($result, $j);
			//will show name of fields
			$schema_insert .= "$field_name:\t";
				if(!isset($row[$j])) {
					$schema_insert .= "NULL".$sep;
				}
				elseif ($row[$j] != "") {
//					$schema_insert .= str_replace($sep, $sep_replace, "$row[$j]").$sep;
					$schema_insert .= "$row[$j]".$sep;
				}
				else {
					$schema_insert .= "".$sep;
				}
			}
			$schema_insert = str_replace($sep."$", "", $schema_insert);
			$schema_insert .= "\t";
			print(trim($schema_insert));
			//end of each mysql row
			//creates line to separate data from each MySQL table row
			print "\n----------------------------------------------------\n";
		}
	}else{
		/*	FORMATTING FOR EXCEL DOCUMENTS ('.xls')   */
		//create title with timestamp:
		if ( $Use_Title )
		{
//			echo("$Use_Title\n");
		}
		//define separator (defines columns in excel & tabs in word)
		$sep = "\t"; //tabbed character
		//start of printing column names as names of MySQL fields
		for ($i = 0; $i < mysql_num_fields($result); $i++)
		{
			echo mysql_field_name($result,$i) . "\t";
		}
		print("\n");
		//end of printing column names
		//start while loop to get data
		while($row = mysql_fetch_row($result))
		{
			//set_time_limit(60); // HaRa
			$schema_insert = "";
			for($j=0; $j<mysql_num_fields($result);$j++)
			{
				if(!isset($row[$j]))
					$schema_insert .= "NULL".$sep;
				elseif ($row[$j] != "")
					$schema_insert .= "$row[$j]".$sep;
				else
					$schema_insert .= "".$sep;
			}
			$schema_insert = str_replace($sep."$", "", $schema_insert);
			$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
			$schema_insert .= "\t";
			print(trim($schema_insert));
			print "\n";
		}
	}
}