<?php

require_once 'databaseinfo.php';
			
$game_id = $_REQUEST['gameId'];
$output = '';
$query = "SELECT Platform_Name, Price, Platform_ID FROM Games NATURAL JOIN Game_Platforms NATURAL JOIN Platforms WHERE Game_ID = $game_id ORDER BY Platform_ID";
$result = $conn->query($query);


if (!$result) {
	$output .= "Database access failed: " . $conn->error;
	//die ("Database access failed: " . $conn->error);
}
	
$rows = $result->num_rows;

for ($i = 0; $i < $rows; ++$i) {
	$result->data_seek($i);
	$row = $result->fetch_array(MYSQLI_NUM);
	
	$output .= "<tr>";
	
	for ($j = 0; $j < 2; ++$j) {
		$output .= "<td>$row[$j]</td>";
	}
	
	$output .= "<td><input type='text' name='prices[]' value='$row[1]' style='max-width: 30%;'></td>";
	$output .= "<td><input type='checkbox' name='deletePlatform[]' value='$row[2]'></td>";
	$output .= "</tr>";
}

echo $output;

?>