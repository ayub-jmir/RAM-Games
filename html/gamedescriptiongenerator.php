<?php

require_once 'databaseinfo.php';

$game_id = $_REQUEST['gameId'];
$output = '';

$query = "SELECT Description FROM Games WHERE Game_ID = $game_id";

$result = $conn->query($query);

if (!$result) {
	die("Database access failed: " . $conn->error);
}

$rows = $result->num_rows;

if ($rows !== 1) {
	$output .= "Our database has a serious error. Contact a site administrator.";
}

else {
	$result->data_seek(0);
	$row = $result->fetch_array(MYSQLI_NUM);

	$output .= $row[0];
}

echo $output;

?>