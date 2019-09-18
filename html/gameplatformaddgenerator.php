<?php

	require_once 'databaseinfo.php';
	$game_id = $_REQUEST['gameId'];
	
	$output = '';
				
	$query = "SELECT Platform_ID, Platform_Name FROM Platforms WHERE Platform_ID NOT IN (SELECT Platform_ID FROM Game_Platforms WHERE Game_ID = $game_id) ORDER BY Platform_Name";
	$result = $conn->query($query);

	if (!$result) {
		die ("Database access failed: " . $conn->error);
	}

	$rows = $result->num_rows;

	for ($i = 0; $i < $rows; ++$i) {
		$result->data_seek($i);
		$row = $result->fetch_array(MYSQLI_NUM);
		$output .= "<option value='$row[0]'>$row[1]</option>";
	}
	
	echo $output;

?>