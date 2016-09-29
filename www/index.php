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

$board = filter_input(INPUT_GET, "b", FILTER_VALIDATE_INT, array(
	"options" => array(
		"min_range" => 1,
		"max_range" => 2
	)
));
if (is_null($board) || $board === FALSE)
	$board = 1;

$ranks;

switch ($board) {
	case 1: // Total Points
		$ranks = $db -> query("SELECT post_name, SUM(post_points) AS points FROM ThreadNecromancyPosts GROUP BY post_name ORDER BY points DESC");
		break;
		
	case 2: // This month
		$startTime = mktime(0, 0, 0, (int) date("n"), 1, (int) date("Y"));
		$ranks = $db -> prepare("SELECT post_name, SUM(post_points) AS points FROM ThreadNecromancyPosts WHERE post_time>=? GROUP BY post_name ORDER BY points DESC");
		$ranks -> execute(array($startTime));
		break;
}

function pointsToLevel($points) {
	return floor(pow($points, 1/3.5));
}

function pointsNeededForLevel($level) {
	return pow($level, 3.5) - 1;
}

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
		
		.levelmeter {
			border: 1px solid #000000;
			position: relative;
			margin: auto;
			margin-top: 3px;
			width: 100px;
			height: 12px;
		}
		
		.levelmeterbar {
			position: absolute;
			left: 0px;
			top: 0px;
			height: 12px;
			background-color: #000;
		}
		
		.levelmetertext {
			position: absolute;
			left: 0px;
			top: 0px;
			width: 100px;
			height: 12px;
			text-align: center;
			font-size: 8pt;
			color: #666;
			text-shadow: 1px 1px 1px #666;
		}
		
		.levelmeternext {
			margin-top: 3px;
			font-size: 9pt;
		}
	</style>
</head>

<body>
	<div style="font-size:24pt;">Thread Necromancy!</div>
	<div style="font-size:12pt;"><a href="http://forums.redbana.com/forum/audition/off-topic-corner/off-beat-cafe/off-beat-forum-games/2988411-thread-necromancy-scripted-forum-game">Go to Thread on Redbana Forums</a></div>

	<div style="margin:20px;">
		<a href="?b=1">Total Points</a> | <a href="?b=2">Points This Month</a>
	</div>
	
	<table class="ranks">
	<?php
	
	switch ($board) {
	
	case 1:
	print '<tr><th>Rank</th><th>Name</th><th>Level</th><th>Points</th></tr>';
	$i = 1;
	foreach ($ranks as $r) {
		$lv = pointsToLevel(($r["points"] <= 0) ? 1 : $r["points"]);
		$thisLvPoints = pointsNeededForLevel($lv);
		$nextLvPoints = pointsNeededForLevel($lv + 1);
		$gap = $nextLvPoints - $thisLvPoints;
		$haveOfGap = ($r["points"] <= 0) ? 0 : $r["points"] - $thisLvPoints;
		
		printf('<tr><td>%d</td><td>%s</td><td>Lv. %d<div class="levelmeter"><div class="levelmeterbar" style="width:%f%%;"></div><div class="levelmetertext">%.2f%%</div></div><div class="levelmeternext">Next Level at %s</div></td><td>%s</td></tr>', $i, $r["post_name"], $lv, ($haveOfGap/$gap)*100, ($haveOfGap/$gap)*100, number_format(ceil($nextLvPoints)), number_format($r["points"]));
		$i++;
	}
	break;
	
	case 2:
	print '<tr><th>Rank</th><th>Name</th><th>Points This Month</th></tr>';
	$i = 1;
	foreach ($ranks as $r) {
		printf('<tr><td>%d</td><td>%s</td><td>%s</td></tr>', $i, $r["post_name"], number_format($r["points"]));
		$i++;
	}
	break;
	}

	?>
	</table>
</body>

</html>