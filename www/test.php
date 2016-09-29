<?php

$db;

try {
	$db = new PDO(
        sprintf("mysql:host=%s;port=%d;dbname=%s", "", 3306, ""),
        "", "", array(
            PDO::ATTR_EMULATE_PREPARES      => false,
            PDO::ATTR_ERRMODE               => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE    => PDO::FETCH_ASSOC
        )
    );
} catch (PDOException $e) {
	die("A required resource is not responding.");
}

$ranks = $db -> query("SELECT * FROM ThreadNecromancyRanks ORDER BY rank_points DESC");

?>

<!DOCTYPE html>

<html>

<head>
	<title>Thread Necromancy</title>
	<style type="text/css">
		body {
			font-family: Arial;
			font-size: 9pt;
		}
		
		table.ranks {
			border: 0px;
			border-collapse: collapse;
		}
		
		table.ranks tr td, table.ranks tr th {
			padding: 5px;
			text-align: center;
			width: 150px;
		}
		
		table.ranks tr td {
			font-size: 10pt;
		}
		
		table.ranks tr th {
			font-size: 14pt;
			font-weight: bold;
		}
	</style>
</head>

<body>
	<div style="font-size:24pt;">Thread Necromancy!</div>
	<div style="font-size:12pt;"><a href="http://forums.redbana.com/forum/audition/off-topic-corner/off-beat-cafe/off-beat-forum-games/2988411-thread-necromancy-scripted-forum-game">Go to Thread on Redbana Forums</a></div>

	<table class="ranks">
	<tr>
		<th>Rank</th>
		<th>Name</th>
		<th>Points</th>
		<th>Total Time</th>
	</tr>
	<?php
	
	$i = 1;
	foreach ($ranks as $r) {
		$k = $r["rank_name"];
		
		$v = $r["rank_points"];
		$points = $r["rank_points"];
	
		$months = floor($v / (3600 * 24 * 30));
		$v -= $months * 3600 * 24 * 30;
		$weeks = floor($v / (3600 * 24 * 7));
		$v -= $weeks * 3600 * 24 * 7;
		$days = floor($v / (3600 * 24));
		$v -= $days * 3600 * 24;
		$hours = floor($v / 3600);
		$v -= $hours * 3600;
		$minutes = floor($v / 60);
		$v -= $minutes * 60;
		
		$timeParts = array();
		
		if ($months > 1)
			array_push($timeParts, $months . " months");
		elseif ($months == 1)
			array_push($timeParts, $months . " month");
		if ($weeks > 1)
			array_push($timeParts, $weeks . " weeks");
		elseif ($weeks == 1)
			array_push($timeParts, $weeks . " week");
		if ($days > 1)
			array_push($timeParts, $days . " days");
		elseif ($days == 1)
			array_push($timeParts, $days . " day");
		if ($hours > 1)
			array_push($timeParts, $hours . " hours");
		elseif ($hours == 1)
			array_push($timeParts, $hours . " hour");
		if ($minutes > 1)
			array_push($timeParts, $minutes . " minutes");
		elseif ($minutes == 1)
			array_push($timeParts, $minutes . " minute");
		if ($v > 1)
			array_push($timeParts, $v . " seconds");
		elseif ($v == 1)
			array_push($timeParts, $v . " second");
			
		$timeString = "";
		if (sizeof($timeParts) > 2) {
			$timeString = $timeParts[0];
			for ($p = 1; $p < sizeof($timeParts) - 1; $p++)
				$timeString .= ", " . $timeParts[$p];
			$timeString .= ", and " . $timeParts[ sizeof($timeParts) - 1 ];
		} elseif (sizeof($timeParts) == 2) {
			$timeString = $timeParts[0] . " and " . $timeParts[1];
		} else
			$timeString = $timeParts[0];
		
		printf('<tr><td>%d</td><td>%s</td><td>%s</td><td>%s</td></tr>', $i, $k, number_format($points), $timeString);
		$i++;
	}

	?>
	</table>
</body>

</html>