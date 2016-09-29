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

$input = filter_input_array(array(
	"name" => FILTER_DEFAULT
));

$posts = $db -> query("SELECT ThreadNecromancyPosts.*, MD5(ThreadNecromancyPosts.post_name) FROM ThreadNecromancyPosts ORDER BY post_time ASC");

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
	
	<div style="font-size:10pt;">Posts by ??</div>

	<table class="ranks">
	<tr>
		<th>Post Time</th>
		<th>Previous Post Time</th>
		<th>Points Earned</th>
	</tr>
	<?php
	
	$i = 1;
	foreach ($ranks as $r) {
		printf('<tr><td>%d</td><td>%s</td><td>%s</td></tr>', $i, $r["rank_name"], number_format($r["rank_points"]));
		$i++;
	}

	?>
	</table>
</body>

</html>