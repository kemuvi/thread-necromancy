<?php

$startTime = time();

date_default_timezone_set("America/New_York");

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

function pointsToLevel($points) {
	return floor(pow($points, 1/3.5));
}

function pointsNeededForLevel($level) {
	return pow($level, 3.5) - 1;
}

$points = array();
$ytdPoints = array();

$postsStmt = $db -> query("(SELECT post_name, post_time FROM ThreadNecromancyPosts) UNION (SELECT post_name, post_time FROM ThreadNecromancyDeleted) ORDER BY post_time ASC");
$posts = $postsStmt -> fetchAll();
$numPosts = sizeof($posts);

$ytdPostsStmt = $db -> prepare("(SELECT post_name, post_time FROM ThreadNecromancyPosts WHERE post_time<?) UNION (SELECT post_name, post_time FROM ThreadNecromancyDeleted WHERE post_time<?) ORDER BY post_time ASC");
$ytdPostsStmt -> execute(array(time() - (3600 * 24), time() - (3600 * 24)));
$ytdPosts = $ytdPostsStmt -> fetchAll();
$numYtdPosts = sizeof($ytdPosts);

// Update current points.
for ($i = 1; $i < $numPosts; $i++) {
	$curr = $posts[$i];
	if ($curr["post_name"] == NULL)
		continue;
	
	$prev = $posts[$i - 1];
	$pts = $curr["post_time"] - $prev["post_time"];
	
	if (array_key_exists($curr["post_name"], $points))
		$points[ $curr["post_name"] ] += $pts;
	else
		$points[ $curr["post_name"] ] = $pts;
		
	$stmt = $db -> prepare("UPDATE ThreadNecromancyPosts SET post_points=? WHERE post_name=? AND post_time=?");
	$stmt -> execute(array($pts, $curr["post_name"], $curr["post_time"]));
}

// Update ytd points.
for ($i = 1; $i < $numYtdPosts; $i++) {
	$curr = $ytdPosts[$i];
	if ($curr["post_name"] == NULL)
		continue;
	
	$prev = $ytdPosts[$i - 1];
	if (array_key_exists($curr["post_name"], $ytdPoints))
		$ytdPoints[ $curr["post_name"] ] += ($curr["post_time"] - $prev["post_time"]);
	else
		$ytdPoints[ $curr["post_name"] ] = ($curr["post_time"] - $prev["post_time"]);
}

// Update ranks.
// Current points.
$db -> beginTransaction();
$db -> query("DELETE FROM ThreadNecromancyRanks");
foreach ($points as $k => $v) {
	$userYtdPoints = array_key_exists($k, $ytdPoints) ? $ytdPoints[$k] : 0;
	$stmt = $db -> prepare("INSERT INTO ThreadNecromancyRanks(rank_name, rank_points, rank_ytd_points) VALUES (?,?,?)");
	$stmt -> execute(array($k, $v, $userYtdPoints));
}
$db -> commit();

$allRanks = $db -> query("SELECT * FROM ThreadNecromancyRanks ORDER BY rank_points DESC LIMIT 50") -> fetchAll();
$allYtdRanks = $db -> query("SELECT * FROM ThreadNecromancyRanks ORDER BY rank_ytd_points DESC LIMIT 50") -> fetchAll();

$ytdRanksByName = array();

$count = 1;
foreach ($allYtdRanks as $r) {
	$ytdRanksByName[ $r["rank_name"] ] = $count;
	$count++;
}

$im = imagecreatetruecolor(450, 1050);
$white = imagecolorallocate($im, 255, 255, 255);
$black = imagecolorallocate($im, 0, 0, 0);
$green = imagecolorallocate($im, 0, 255, 0);
$red = imagecolorallocate($im, 255, 0, 0);
$yellow = imagecolorallocate($im, 255, 255, 0);
$forumBkg = imagecolorallocate($im, 235, 244, 249);
$rowBkgs = array(
	imagecolorallocate($im, 215, 224, 229),
	imagecolorallocate($im, 195, 204, 209)
);
	
imagefilledrectangle($im, 0, 0, 450, 1050, $forumBkg);
		
$y = 5;
$count = 1;
foreach ($allRanks as $r) {
	imagefilledrectangle($im, 0, $y, 450, $y + 20, $rowBkgs[$count % 2]);
	
	imagestring($im, 4, 1, $y, $count, $black);
	if (array_key_exists($r["rank_name"], $ytdRanksByName)) {
		if ($ytdRanksByName[ $r["rank_name"] ] > $count) {
			// Went up
			imagefilledpolygon($im, array(25, $y + 10, 35, $y + 10, 30, $y + 5), 3, $green);
		} elseif ($ytdRanksByName[ $r["rank_name"] ] == $count) {
			// Stayed the same
			imagefilledrectangle($im, 25, $y + 6, 35, $y + 9, $yellow);
		} else {
			// Went down
			imagefilledpolygon($im, array(25, $y + 5, 35, $y + 5, 30, $y + 10), 3, $red);
		}
	} else {
		// Went up
		imagefilledpolygon($im, array(25, $y + 10, 35, $y + 10, 30, $y + 5), 3, $green);
	}
	imagestring($im, 4, 45, $y, $r["rank_name"], $black);
	imagestring($im, 4, 280, $y, number_format($r["rank_points"]), $black);
	imagestring($im, 4, 360, $y, "Lv. " . pointsToLevel($r["rank_points"]), $black);
	
	// Level bar
	$xGap = 50;
	$lv = pointsToLevel($r["rank_points"]);
	$thisLvPoints = pointsNeededForLevel($lv);
	$nextLvPoints = pointsNeededForLevel($lv + 1);
	$gap = $nextLvPoints - $thisLvPoints;
	$haveOfGap = $r["rank_points"] - $thisLvPoints;
	imagefilledrectangle($im, 360, $y + 15, 360 + ($xGap * ($haveOfGap/$gap)), $y + 16, $black);
	
	$y += 20;
			
	$count++;
}

imagestring($im, 2, 5, 1030, "Last Updated: " . date("n/j/Y g:ia") . " Audition Time", $black); 

imagepng($im, "scoreboard.png", 0);
imagedestroy($im);

print '<pre>';
print_r($allRanks);
print_r($allYtdRanks);
print '</pre>';

print "Finished in " . (time() - $startTime) . " seconds.";

?>