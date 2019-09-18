<?php

	require_once 'databaseinfo.php';
	
	$platform = $_REQUEST['platform'];
	$genre = $_REQUEST['genre'];
	$order = $_REQUEST['order'];
	$name = $_REQUEST['name'];
	
	$output = '';

	
	// Now, to do the query that shows every game available
	$query = "SELECT Game_ID, Name, Genre_Name, Platform_Name, Publisher, Description, Release_Date, Price, Platform_ID, Rating_Name
				FROM Games NATURAL JOIN Genres NATURAL JOIN Game_Platforms NATURAL JOIN Platforms NATURAL JOIN Ratings";
	
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

	// And also order by the appropriate parameter:
	switch ($order) {
		case 0:
			$query .= " ORDER BY Platform_Name";
			break;
		case 1:
			$query .= " ORDER BY Price";
			break;
		case 2:
			$query .= " ORDER BY Price DESC";
			break;
		case 3:
			$query .= " ORDER BY Genre_Name";
			break;
	}

	$result = $conn->query($query);
	
	if (!$result) {
		die("Database access failed: " . $conn->error);
	}
	
	$rows = $result->num_rows;
	
	$output .= ($rows === 0) ? '' : "<form method='post' action='cart.php'><div class='row'>";
	
	
	// TODO: Display output in $output
	for ($i = 0; $i < $rows; ++$i) {
		$result->data_seek($i);
		$row = $result->fetch_array(MYSQLI_NUM);
		
		$ratingquery = "SELECT AVG(Score) FROM Reviews WHERE Game_ID = $row[0] AND Platform_ID = '$row[8]'";
		$ratingresult = $conn->query($ratingquery);
		$numrows = $ratingresult->num_rows;
		if($numrows > 0){
			$ratingresult->data_seek(0);
			$rating = $ratingresult->fetch_array(MYSQLI_NUM);
		}
		
		$output .= <<<_END
		<div class="col-sm-6">
			<div class="panel panel-primary">
				<div class="panel-heading">
					<div class="panel-title"><div class="left-align">$row[1]</div><div class="right-align">$$row[7]</div></div>
				</div>
				<div class="panel-body">
_END;

							
							$output .= "<img src='../pictures/$row[0].jpg' alt='Standard Room' class='game-image'>";
							$output .= "<p class='bold'>$row[3]</p><p>Description: $row[5]</p><p class='clear-left'>Publisher: $row[4]</p>";
							$output .= "<p class='clear-right'>Rating: $row[9]</p>";
							$output .= "<p class='clear-right'>Genre: $row[2]</p>";
							if ($rating[0] > 0){
								$output .= "<p class='clear-right'>Review Score: " . number_format($rating[0], 1) . "</p>";
							}
							$output .= <<<_END
							<div class="checkbox">
								<input type="checkbox" id="game$row[0]" class="checkbox" name="games[]" value="$row[0]%$row[1]%$row[3]%$row[7]%$row[8]">
									<label for="game$row[0]">Add Game To Cart</label>
								</button>
							</div>
			
_END;


		$output .= <<<_END
				</div>
			</div>
		</div>
_END;
		
		// A "row" ends on odd numbered entries
		if ($i % 2 == 1) {
			$output .= "</div><div class='row'>";
		}
		
		if ($i == $rows - 1) {
			$output .= "</div>";
		}
	}
	
	if (strlen($output) == 0) {
		$output = "<h3 class='text-center bold'>There are no games available</h3>";
	}
	
	$output .= ($rows === 0) ? '' : "<p class='text-center'><button type='submit' class='btn btn-success' id='add-to-cart' name='add-to-cart'>Add To Cart</button></p></form>";
	
	echo $output;
?>
