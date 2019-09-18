<?php

	require_once 'databaseinfo.php';
	
	$platform = $_REQUEST['platform'];
	$genre = $_REQUEST['genre'];
	$order = $_REQUEST['order'];
	$name = $_REQUEST['name'];
	
	$output = '';

	
	// Now, to do the query that shows every game available (we also need games that haven't yet been assigned to platforms,
	// so outer joins are necessary here)
	$query = "SELECT DISTINCT Game_ID, Name
				FROM Game_Platforms RIGHT JOIN Games USING (Game_ID) LEFT JOIN Genres USING (Genre_ID)";
	
	$check = 1;
	if (!($platform === 'ALL' && $genre === 'ALL')) {
		$query .= " WHERE ";
		$check = 0;
		
		if ($platform != 'ALL'){
			$query .= "Platform_ID = '$platform'";
			if ($genre !='ALL'){
				$query .= " AND Genre_ID =  '$genre'";
			}
		} else {
			$query .= "Genre_ID = '$genre'";
		}
	}
	if ($check === 1 && $name !== ""){
		$query .= " WHERE";
	}
	
	if ($name !== ""){
		$name = strtolower($name);
		
		if ($check === 1){
		$query .= " LOWER(Name) LIKE '%$name%'";
		} else {
			$query .= " AND LOWER(Name) LIKE '%$name%'";
		}
	}

	// And also order results by name alphabetically:
	$query .= " ORDER BY Name";

	$result = $conn->query($query);
	
	if (!$result) {
		die("Database access failed: " . $conn->error);
	}
	
	$rows = $result->num_rows;
	
	// If results exist, create a form that returns all games that result from this search
	if ($rows !== 0) {
		$output .= <<<_END
		<form method="post" action="editgamedetails.php" class="form-horizontal form-render">
			<div class="form-group">
				<label for="game-id" class="control-label col-sm-2">Select Game to Edit:</label>
				<div class="col-sm-10">
					<select name="game-id" id="game-id" class="form-control">
_END;
	}
	
	
	// Display games that meet criteria in select box:
	for ($i = 0; $i < $rows; ++$i) {
		$result->data_seek($i);
		$row = $result->fetch_array(MYSQLI_NUM);
		$output .= "<option value='$row[0]'>$row[1]</option>";
	}

	if (strlen($output) === 0) {
		$output = "<h3 class='text-center bold'>There are no games available to edit with those parameters.</h3>";
	}
	else {
		$output .= <<<_END
					</select>
				</div>
			</div>
			<div class="form-group"> 
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" class="btn btn-default" name="submit-search" id="submit-search">Edit this game</button>
				</div>
			</div>
		</form>
_END;
	}
	
	echo $output;
?>
