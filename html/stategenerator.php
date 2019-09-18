<?php
	require_once 'databaseinfo.php';
	
	// Check if Canada or U.S.
	$country = $_REQUEST['country'];
	$state = (isset($_REQUEST['state'])) ? $_REQUEST['state'] : 'ON';
	
	// We want to find all states/provinces in that country
	$query = "SELECT State_ID, State_Name FROM States WHERE Country_ID = '$country' ORDER BY State_Name";
	$result = $conn->query($query);
	$rows = $result->num_rows;
	
	$return_text = '';
	
	for ($i = 0; $i < $rows; ++$i) {
		$result->data_seek($i);
		$row = $result->fetch_array(MYSQLI_NUM);
		
		// Ontario should be the default option
		$return_text .= ($row[0] == $state) ? "<option value='$row[0]' selected>$row[1]</option>" : "<option value='$row[0]'>$row[1]</option>";
	}
	
	echo $return_text;
?>