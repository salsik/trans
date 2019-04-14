<?php

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

error_reporting( E_ALL ^ E_NOTICE );
require_once "_startup.php";
require_once( BASE_DIR . "_emails.php");

$restTime = 6 * 3600;

// =======================================================

$time = time();
//$time = mktime(5,61,0, 5, 5, 2014);

$date = date('Y-m-d', $time);

$nowTime = (int) date('Hi', $time);
$activeTime = (int) date('Hi', $time - $restTime);

if( $activeTime > $nowTime) {
	echo "It's rest time";
	exit;
}

$agendaLastSent = $time - ((24 * 3600) - $restTime);

$sql = "SELECT classes.* 
	FROM classes, schools
	WHERE classes.school_id = schools.id
		AND schools.status='active'
		AND schools.agenda_send_time < {$nowTime}
		AND schools.agenda_send_time > 1
		AND classes.agenda_last_sent < {$agendaLastSent}
	ORDER BY classes.agenda_last_sent ASC
";

$timeStr1 = date('h:ia', $agendaLastSent);
$timeStr2 = date('h:ia', time());

echo "<h1>Agenda of {$date} [{$timeStr1} - {$timeStr2}]</h1>";

$q = mysql_query($sql);
if( !$q ) {
	echo "MySQL Error: " . mysql_error();
}
else if( !mysql_num_rows($q)) {
	echo "<b>No classes need to send agenda to.</b>";
}
else {
	while($class = mysql_fetch_assoc($q)) {
		$school = getDataByID('schools', $class['school_id']);
		
		echo "<div>- Class: <b>{$class['title']}</b>, School: <b>{$school['title']}</b></div>";

		$sql = "SELECT agenda.*, teachers.title as teacher_name
			FROM agenda 
			LEFT OUTER JOIN teachers ON (teachers.id=agenda.teacher_id)
			WHERE agenda.class_id='{$class['id']}'
				AND agenda.date='{$date}'
			";

		$qq = mysql_query($sql);
//		echo mysql_error();
		if( $qq && mysql_num_rows($qq)) {
			
			$agenda = '<table width="100%" border="1">';
			$agenda .= '<tr>';
			$agenda .= '<th width="150">Teacher</th>';
			$agenda .= '<th>Agenda</th>';
			$agenda .= '<th width="80">Date</th>';
			$agenda .= '</tr>';
			while($row = mysql_fetch_assoc($qq)) {
				$row['description'] = nl2br($row['description']);

				$agenda .= "<tr>";
				$agenda .= "<td>{$row['teacher_name']}</td>";
				$agenda .= "<td>{$row['description']}</td>";
				$agenda .= "<td>{$row['date']}</td>";
				$agenda .= "</tr>";
			}
			$agenda .= '</table>';
//echo $agenda;
			$sql = "SELECT students.*
				FROM students 
				WHERE students.status='active'
					AND students.class_id='{$class['id']}'
					AND (
						students.info_email<>''
					)
				";
//						OR students.info_email<>''
//						OR students.info_email<>''
	
			$qq = mysql_query($sql);
			if( $qq && mysql_num_rows($qq)) {
				while($row = mysql_fetch_assoc($qq)) {
					if(is_Email($row['info_email'])) {
						send_student_agenda($row, $class, $school, $agenda, $date);
						
						echo "<div>--- Agenda sent to \"{$row['full_name']}\" ({$row['info_email']})</div>";
					}
					else {
						echo "<div>--- Student \"{$row['full_name']}\" has invalid email address ({$row['info_email']})</div>";
					}
				}
			}
			else {
				echo "<div>--- No students with email address available to send the agenda to.</div>";
			}
		}
		else {
			echo "<div>--- No agenda available</div>";
		}
		
		
		$sql = "UPDATE `classes` set classes.agenda_last_sent = '".$time."' WHERE id = '{$class['id']}' LIMIT 1";
		mysql_query($sql);
	}
}





echo "<br />";
echo "<div>EOF</div>";
