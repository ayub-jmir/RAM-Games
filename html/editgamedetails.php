<?php
	session_start();
	require_once 'databaseinfo.php';

	// I need to keep track of the current and previously visited pages
	$last_page = (isset($_SESSION['this_page'])) ? $_SESSION['this_page'] : '';
	$this_page = 'editgamedetails.php';
	
	// Prevent updates to this and last page if browser refreshed/page reloaded:
	if ($this_page !== $last_page) {
		$_SESSION['last_page'] = $last_page;
		$_SESSION['this_page'] = $this_page;
	}
	
	// You need to be logged in and registered as an admin to access this page
	if (!$_SESSION['login'] || !$_SESSION['isAdmin']) {
		header("Location: login.php");
	}
	
	
	// Function that validates price inputs:
	function validate_price($price) {
		$output = '';
		// Verify format of price:
		if (!is_numeric($price)) {
			$output .= "The price must be a number!<br>";
		}
		elseif ($price < 0) {
			$output .= "Prices cannot be negative!<br>";
		}
		elseif ($price >= 1000) {
			$output .= "Game price cannot exceed $999.99!<br>";
		}
		return $output;
	}
	
	// Function that updates a field (if not empty and changed from original value)
	function update_field($attribute, $new_value, $old_value, $game_id, $conn) {
		if ($new_value && ($new_value !== $old_value)) {
			$query = "UPDATE Games SET $attribute = ? WHERE Game_ID = ?";
			$stmt = $conn->prepare($query);
			$stmt->bind_param('si', $new_value, $game_id);
			
			return ($stmt->execute()) ? "<span class='green'>Successfully updated " . strtoupper($attribute) . " from \"$old_value\" to \"$new_value\"</span><br>"
				: "Failed to update " . strtoupper($attribute) . " from \"$old_value\" to \"$new_value\"<br>";
		}	
		return '';
	}
	
	// Gather game_id from $_POST:
	$game_id = (isset($_POST['game-id'])) ? $_POST['game-id'] : 0;
	
	// Redirect user if page reached "accidentally":
	if ($game_id === 0) {
		header("Location: editgame.php");
	}
	
	// Gather information about game in question:
	$main_query = "SELECT * FROM Games WHERE Game_ID = $game_id";
	$main_result = $conn->query($main_query);
	
	if (!$main_result) {
		die("Database connection error occurred: " . $conn->error . "<br>");
	}
	
	elseif ($main_result->num_rows !== 1) {
		die("Database contains an error. Ask administrator to fix games database.<br>");
	}
	
	$main_result->data_seek(0);
	
	// $main_row contains all the stored information about the game in an associative array
	$main_row = $main_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
  
    <!-- This website uses the Bootstrap default webpage layout as a starting point -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>RAM Games - Edit Game Details</title>

    <!-- Bootstrap -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
	
	<!-- My own CSS -->
	<link href="../css/styles.css" rel="stylesheet">
	
	<!-- "Fira Sans" font from Google Fonts -->
	<link href="https://fonts.googleapis.com/css?family=Fira+Sans:600,900" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  
	<?php

	// To 'refill' data when form is posted
	$output = '';
	$prices = array();	
	//$game_id = $_POST['game-id'];
	//$game_name = (isset($_POST['add-game-name'])) ? $_POST['add-game-name'] : '';
	//$game_description = (isset($_POST['add-game-description'])) ? $_POST['add-game-description'] : '';
	//$game_publisher = (isset($_POST['add-game-publisher'])) ? $_POST['add-game-publisher'] : '';
	
	// Release date form defaults to today unless already set
	//$date = (isset($_POST['add-game-release-date'])) ? $_POST['add-game-release-date'] : date('Y-m-d');
	
	//$successful_insert = false;
	

	if (isset($_POST['game-info-change'])) {
	  
		$game_id = $_POST['game-id'];

		// Prevent HTML and SQL injection:
		$description = $conn->real_escape_string(htmlspecialchars($_POST['game-info-description']));
		$publisher = $conn->real_escape_string(htmlspecialchars($_POST['game-info-publisher']));
		$name = $conn->real_escape_string(htmlspecialchars($_POST['game-info-name']));

		// Update fields if necessary:
		$output .= update_field('Name', $name, $main_row['Name'], $game_id, $conn);
		$output .= update_field('Publisher', $publisher, $main_row['Publisher'], $game_id, $conn);
		$output .= update_field('Description', $description, $main_row['Description'], $game_id, $conn);
		$output .= update_field('Genre_ID', $_POST['game-info-genre'], $main_row['Genre_ID'], $game_id, $conn);
		$output .= update_field('Rating', $_POST['game-info-rating'], $main_row['Rating'], $game_id, $conn);
		$output .= update_field('Release_Date', $_POST['game-info-date'], $main_row['Release_Date'], $game_id, $conn);
		
		// Update fields for form re-rendering:
		$main_result = $conn->query($main_query);
	
		if (!$main_result) {
			die("Database connection error occurred: " . $conn->error . "<br>");
		}
		
		elseif ($main_result->num_rows !== 1) {
			die("Database contains an error. Ask administrator to fix games database.<br>");
		}
		
		$main_result->data_seek(0);
		
		// $main_row contains all the stored information about the game in an associative array
		$main_row = $main_result->fetch_assoc();
			
	}
	

	elseif (isset($_POST['game-price-change'])) {
	  
		$prices = $_POST['prices'];
		$deleteList = (isset($_POST['deletePlatform']) || $_POST['deletePlatform'] != null) ? $_POST['deletePlatform'] : array();
		$game_id = $_POST['game-id'];
		
		$query = "UPDATE Game_Platforms SET Price = ? WHERE Platform_ID = ? AND Game_ID = ?";
		$query2 = "SELECT Platform_ID, Price, Platform_Name, Name FROM Games NATURAL JOIN Game_Platforms NATURAL JOIN Platforms WHERE Game_ID = $game_id ORDER BY Platform_ID";
		
		// METHODOLOGY: Iterate through $query2 result. If delete selected, delete it. If prices differ, change them. We order by Platform_ID on the gameplatformpricegenerator.php
		// file and this file to ensure that the platforms are in the right order when modifying/deleting.
		$result = $conn->query($query2);
	  
		if (!$result) {
			die("Database access failed: " . $conn->error);
		}

		$rows = $result->num_rows;
		$updated_row_count = 0;

		// Update prices if they exist in form and are different than stored price
		for ($i = 0; $i < $rows; ++$i) {
			$result->data_seek($i);
			$row = $result->fetch_array(MYSQLI_NUM);
			
			// If platform is in delete list
			if (!empty($deleteList) && in_array($row[0], $deleteList)) {
				$query3 = "DELETE FROM Game_Platforms WHERE Game_ID = ? AND Platform_ID = ?";
				$stmt = $conn->prepare($query3);
				$stmt->bind_param('is', $game_id, $row[0]);
				
				if ($stmt->execute()) {
					$output .= "$row[3] is no longer available for $row[2]!<br>";
					++$updated_row_count;
				}
				
				else {
					$output .= "Couldn't delete $row[2] version of $row[3] due to database error: ". $conn->error . "<br>";
				}
			}
			
			// Otherwise, if new price differs from existing price
			elseif ($prices[$i] && ($prices[$i] !== $row[1])) {
				$stmt = $conn->prepare($query);
				$stmt->bind_param('dsi', $prices[$i], $row[0], $game_id);
				
				if ($stmt->execute()) {
					$output .= "The price of $row[3] on $row[2] has been successfully updated to $$prices[$i]!<br>";
					++$updated_row_count;
				}
				
				else {
					$output .= "Couldn't change the price of $row[3] for $row[2] due to database error: " . $conn->error . "<br>";
					die ("Database access failed: " . $conn->error);
				}
			}
		}
		
		if ($updated_row_count === 0) {
			$output .= "<span class='red'>No prices were changed or platforms deleted!</span>";
		}  
	}
	
	elseif (isset($_POST['game-platform-add-submit'])) {
		
		$game_id = $_POST['game-id'];
		$platform_id = $_POST['game-platform-add-platform'];
		$price = $_POST['game-platform-add-price'];
		
		$successful_insert = false;
		
		// If price is valid, try to insert it:
		$output .= validate_price($price);
		if ($output === '') {
			$query = "INSERT INTO Game_Platforms (Game_ID, Platform_ID, Price) VALUES (?, ?, ?)";
			$stmt = $conn->prepare($query);
			$stmt->bind_param('isd', $game_id, $platform_id, $price);
			
			if ($stmt->execute()) {
				$successful_insert = true;
				$output .= "Successfully added $platform_id version of game for $$price!<br>";
			}
			
			else {
				$output .= "Couldn't add platform for game: " . $conn->error . "<br>";
			}
		}
	}
	
	elseif (isset($_POST['add-game-submit'])) {
		
		// First validate image:
		$image = $_FILES['add-game-image'];
		
		
		// Images over 10 MB disallowed:
		if ($image['size'] > 10485760) {
			$output .= "Image must not be over 10 MB in size!<br>";
		}
		
		if ($image['type'] !== 'image/jpeg') {
			$output .= "Image must be in JPG format!<br>";
		}
		
		$game_name = $_POST['add-game-name'];
		$game_genre = $_POST['add-game-genre'];
		$game_publisher = $_POST['add-game-publisher'];
		$date = $_POST['add-game-release-date'];
		$game_rating = $_POST['add-game-rating'];
		//$game_description = $_POST['add-game-description'];
		
		if (!$game_name) {
			$output .= "Game name must not be empty!<br>";
		}
		
		if (!$game_description) {
			$output .= "Game description must not be empty!<br>";
		}
		
		if (!$game_publisher) {
			$output .= "Game publisher must not be empty!<br>";
		}
		
		// Attempt to add to database and move file:
		if (strlen($output) === 0) {
			// Code from Stack Overflow to find the next autoincrement value for Games table
			$sql = "SHOW TABLE STATUS LIKE 'Games'";
			$result = $conn->query($sql);
			$row = $result->fetch_assoc();
			$next_game_id = $row['Auto_increment'];
			
			echo "Next ID: $next_game_id<br>File Uploaded Name: $image[name]<br>File Uploaded Temp Name:$image[tmp_name]<br>";
			
			if (!move_uploaded_file($image['tmp_name'], '../pictures/' . $next_game_id . ".jpg")) {
				$output .= "ERROR: File could not be uploaded!<br>";
			}
				
			else {
				// Attempt to add to database:
				$query = "INSERT INTO Games(Name, Genre_ID, Rating, Publisher, Description, Release_Date) VALUES (?, ?, ?, ?, ?, ?)";
				$stmt = $conn->prepare($query);
				$stmt->bind_param('ssssss', $game_name, $game_genre, $game_rating, $game_publisher, $game_description, $date);
				
				if ($stmt->execute()) {
					$output .= "<span class='bold green'>$game_name was inserted successfully!<br>";
				}
				
				else {
					$output .= "Error: Couldn't insert $game_name due to database error: " . $conn->error . "<br>";
				}
			}		
		}
	}
	
	
	/*
	
	// This section processes the form once submitted:
	elseif (isset($_POST['add-employee'])) {
		
		// First, check all fields are set, and 'sanitize' them:
		$fname = $conn->real_escape_string(htmlspecialchars($_POST['fname']));
		$lname = $conn->real_escape_string(htmlspecialchars($_POST['lname']));
		$address = $conn->real_escape_string(htmlspecialchars($_POST['address']));
		$city = $conn->real_escape_string(htmlspecialchars($_POST['city']));
		$country = $conn->real_escape_string($_POST['country']);
		$state = ($_POST['state']) ? $conn->real_escape_string($_POST['state']) : ''; // State may be empty, so we should check for this to be safe
		$email = $conn->real_escape_string(htmlspecialchars($_POST['email']));
		$password = $conn->real_escape_string(hash('ripemd128', $pre_salt . $_POST['password'] . $post_salt));

		
		if (!$fname) {
			$output .= 'Please enter a first name.<br>';
		}
		if (!$lname) {
			$output .= 'Please enter a last name.<br>';
		}
		if (!$address) {
			$output .= 'Please enter a street address.<br>';
		}
		if (!$city) {
			$output .= 'Please enter a city.<br>';
		}
		
		// It is impossible to fill out the form without selecting country and state (if applicable), so no need to check if those are missing
		
		if (!$email) {
			$output .= 'Please enter an email address.<br>';
		}

		// Check for email uniqueness in BOTH customers and employees (the same appearing in either table is forbidden):
		else {
			
			$query = "SELECT * FROM employees WHERE email = '$email'";		
			$result = $conn->query($query);
					
			if (!$result) {
				die ("Database access failed: " . $conn->error);
			}
			
			$rows = $result->num_rows;
			
			$query = "SELECT * FROM customers WHERE email = '$email'";
			$result = $conn->query($query);
					
			if (!$result) {
				die ("Database access failed: " . $conn->error);
			}
			
			$rows += $result->num_rows;
			
			if ($rows > 0) {
				$output .= "The email address you entered is already registered to another user. Please enter a different email address.<br>";
			}
		}		
		
		
		// Password must have at least 5 characters
		if (!$_POST['password'] || strlen($_POST['password']) < 5) {
			$output .= 'Please enter a password that has at least 5 characters.<br>';
		}
		
		// If $output is still empty, the form was submitted correctly
		if (strlen($output) === 0) {
			
			// INSERT INTO DATABASE:
			$query = "INSERT INTO employees(first_name, last_name, email, password, street_address, city, state_id, country_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
			$stmt = $conn->prepare($query);
			$stmt->bind_param('ssssssss', $fname, $lname, $email, $password, $address, $city, ($state === '') ? null : $state, $country);
			
			if ($stmt->execute()) {
				$output = "<span class='green'>The employee $fname $lname has been registered.</span>";			
			}
			
			else {
				die($stmt->error);
			}
		}
	}
	
	elseif (isset($_POST['employee-delete'])) {
		
		$query = "DELETE FROM employees WHERE employee_id = ?";
		
		if (count($_POST['delete']) === 0) {
			$output .= "<span class='red'>No employees selected for deletion!</span></br>";
		}
		
		foreach ($_POST['delete'] as $employee) {
			$stmt = $conn->prepare($query);
			$stmt->bind_param('i', $employee);
			
			if ($stmt->execute()) {
				$output .= "Employee $employee successfully deleted!<br>";
			}
			
			else {
				die ("Failed to delete employee $employee: " . $conn->error);
			}
		}
	} */

	?>
  
  
  <!-- HEADER -->
	<div class="header">
		<div class="header-img">
			<img class="logo" src="../pictures/Logomakr_8eWW9h.png" alt="Vacation Inn">
		</div>
		<div class="header-slogan">
			Site Management
		</div>
	</div>
	
	<!-- NAVBAR: Content adapted from W3Schools -->
	<nav class="navbar navbar-default employee-navbar">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#">Management Navigation</a>
			</div>
			<div class="collapse navbar-collapse" id="myNavbar">
				<ul class="nav navbar-nav">
					<li><a href="editgame.php">Edit Game</a></li>
					<li><a href="updateemployee.php">Update Account</a></li>
					<li><a href="reports.php">Reports</a></li>
				</ul>
				<ul class="nav navbar-nav navbar-right">			
					<!-- We don't need PHP to check for login here since employees MUST be logged in at all times -->
					<li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Log Out</a></li>
				</ul>
			</div>
		</div>
	</nav>
	
	
	<!-- BODY -->
	<div class="main">
	
	<!-- Form to update room types -->
	<h1 class="bold text-center">Update Information for <i><?php echo $main_row['Name']; ?></i></h1>
	<p class="text-center">Changes will not be saved for any fields left blank or unmodified from their original values.</p>
	<p id="game-info-error-message" class="red bold">
	<?php
	if (isset($_POST['game-info-change'])) {
		echo $output; 
		unset($_POST['game-info-change']);
	}	
	?>
	</p>
	
	<form class="form-horizontal form-render" action="editgamedetails.php" method="post" id="game-info-form">
	
		<div class="form-group">
		<label for="game-info-name" class="control-label col-sm-2">Name:</label>
			<div class="col-sm-10">
				<input class="form-control" type="text" value="<?php echo $main_row['Name']; ?>" id="game-info-name" name="game-info-name">
			</div>
		</div>
		
		<div class="form-group">
		<label for="game-info-publisher" class="control-label col-sm-2">Publisher:</label>
			<div class="col-sm-10">
				<input class="form-control" type="text" value="<?php echo $main_row['Publisher']; ?>" id="game-info-publisher" name="game-info-publisher">
			</div>
		</div>
	
		<div class="form-group">
			<label for="game-info-description" class="control-label col-sm-2">Description:</label>
			<div class="col-sm-10">
				<textarea class="form-control" rows="5" name="game-info-description" id="game-info-description"><?php echo $main_row['Description']; ?></textarea>
			</div>
		</div>
		
		<div class="form-group">
			<label for="game-info-genre" class="control-label col-sm-2">Genre:</label>
			<div class="col-sm-10">
				<select class="form-control" name="game-info-genre" id="game-info-genre">
				
				<?php
				
					$query = "SELECT Genre_ID, Genre_Name FROM Genres";
					$result = $conn->query($query);
					
					if (!$result) {
						die("Database error occurred " . $conn->error . "<br>");
					}
					
					$rows = $result->num_rows;
					
					for ($i = 0; $i < $rows; ++$i) {
						$result->data_seek($i);
						$row = $result->fetch_array(MYSQLI_NUM);
						
						// Select current genre by default:
						echo "<option value='$row[0]'" . (($row[0] === $main_row['Genre_ID']) ? " selected" : "") . ">$row[1]</option>";
					}
					
				
				?>
				
				</select>
			</div>
		</div>
		
		<div class="form-group">
			<label for="game-info-rating" class="control-label col-sm-2">ESRB Rating:</label>
			<div class="col-sm-10">
				<select class="form-control" name="game-info-rating" id="game-info-rating">
				
				<?php
				
					$query = "SELECT Rating, Rating_Name FROM Ratings";
					$result = $conn->query($query);
					
					if (!$result) {
						die("Database error occurred " . $conn->error . "<br>");
					}
					
					$rows = $result->num_rows;
					
					for ($i = 0; $i < $rows; ++$i) {
						$result->data_seek($i);
						$row = $result->fetch_array(MYSQLI_NUM);
						
						// Select current genre by default:
						echo "<option value='$row[0]'" . (($row[0] === $main_row['Rating']) ? " selected" : "") . ">$row[0] - $row[1]</option>";
					}
					
				?>
				
				</select>
			</div>
		</div>
		
		<div class="form-group">
			<label for="game-info-date" class="control-label col-sm-2">Release Date:</label>
			<div class="col-sm-10">
				<input class="form-control" type="date" id="game-info-date" name="game-info-date" value="<?php echo $main_row['Release_Date']; ?>">
			</div>
		</div>
		
		<input type="hidden" id="game-id" name="game-id" value="<?php echo $game_id; ?>">
		
		<div class="form-group"> 
			<div class="col-sm-12 text-center">
				<button type="submit" class="btn btn-default" name="game-info-change" id="game-info-change">Update Game</button>
			</div>
		</div>
	</form>
	
	
	
	<!-- This form changes the prices of games in the database or deletes platforms -->
	<h1 class="bold text-center">Update Prices and Platforms</h1>
	<p class="text-center">Use this form to change the individual prices of each game, or delete it for sale on a specific platform.
		If a price is left empty, its value won't be changed in the database.</p>
	<p id="game-price-error-message" class="green bold">
	<?php
	if (isset($_POST['game-price-change'])) {
		echo $output;
		unset($_POST['game-price-change']);
	}	
	?>
	</p>
	
	<form action="editgamedetails.php" method="post" id="game-price-form">
		<table class="table table-hover table-bordered table-render" id="game-price-table">
			<thead>
				<tr>
					<th>Platform</th>
					<th>Current Price</th>
					<th>New Price</th>
					<th>Delete Platform</th>
				</tr>
			</thead>
			<tbody>
			
			<?php
			
				$query = "SELECT Platform_Name, Price, Platform_ID FROM Games NATURAL JOIN Game_Platforms NATURAL JOIN Platforms WHERE Game_ID = $game_id ORDER BY Platform_ID";
				$result = $conn->query($query);


				if (!$result) {
					die("Database access failed: " . $conn->error);
				}
					
				$rows = $result->num_rows;

				for ($i = 0; $i < $rows; ++$i) {
					$result->data_seek($i);
					$row = $result->fetch_array(MYSQLI_NUM);
					
					echo "<tr>";
					
					for ($j = 0; $j < 2; ++$j) {
						echo "<td>$row[$j]</td>";
					}
					
					echo "<td><input type='text' name='prices[]' value='$row[1]' style='max-width: 25%;'></td>";
					echo "<td><input type='checkbox' name='deletePlatform[]' value='$row[2]'></td>";
					echo "</tr>";
				}
			
			?>
			
			</tbody>
		</table>
		<input type="hidden" id="gameId" name="game-id" value="<?php echo $game_id; ?>">
		<div class="form-group"> 
			<div class="col-sm-12 text-center">
				<button type="submit" class="btn btn-default" name="game-price-change" id="game-price-change">Update Prices and Platforms</button>
			</div>
		</div>
	</form>
	
	<!-- Form to add game platforms -->
	<h1 class="bold text-center">Add Game Platforms</h1>
	<p class="text-center">Use this form to add a new platform for this game.</p>
	<p id="game-platform-add-error-message" class="red bold">
	<?php
	if (isset($_POST['game-platform-add-submit'])) {
		echo ($successful_insert) ? "<span class='bold green'>$output</span>" : "<span class='bold red'>$output</span>"; 
		unset($_POST['game-platform-add-submit']);
	}	
	?>
	</p>
	
	<form class="form-horizontal form-render" action="editgamedetails.php" method="post" id="game-platform-add-form">
		
		<div class="form-group">
			<label id="game-platform-add-platform-label" class="control-label col-sm-2" for="game-platform-add-platform">New Platform:</label>
			<div class="col-sm-10">
				<select class="form-control" id="game-platform-add-platform" name="game-platform-add-platform">
				
				<?php
				
					$query = "SELECT Platform_ID, Platform_Name FROM Platforms WHERE Platform_ID NOT IN (SELECT Platform_ID FROM Game_Platforms WHERE Game_ID = $game_id) ORDER BY Platform_Name";
					$result = $conn->query($query);

					if (!$result) {
						die ("Database access failed: " . $conn->error);
					}

					$rows = $result->num_rows;

					for ($i = 0; $i < $rows; ++$i) {
						$result->data_seek($i);
						$row = $result->fetch_array(MYSQLI_NUM);
						echo "<option value='$row[0]'>$row[1]</option>";
					}
					
					echo $output;
				
				?>
				
				</select>
			</div>
		</div>
		
		<div class="form-group">
			<label for="game-platform-add-price" class="control-label col-sm-2">Price:</label>
			<div class="col-sm-10">
				<input type="text" class="form-control" rows="5" name="game-platform-add-price" id="game-platform-add-price">
			</div>
		</div>
		
		<input type="hidden" name="game-id" value="<?php echo $game_id; ?>">
		
		<input type="hidden" id="gameId2" name="gameId2" value="<?php echo $game_id; ?>">
		
		<div class="form-group"> 
			<div class="col-sm-12 text-center">
				<button type="submit" class="btn btn-default" name="game-platform-add-submit" id="game-platform-add-submit">Add Platform</button>
			</div>
		</div>
	</form>

	<?php

/*

	<!-- This form can be used to delete employees -->
	<h1 class="text-center bold">Delete Employees</h1>
	<p class="text-center">This form details every employee registered to use the management portion of this website. Should a management employee be terminated, retire,
	or otherwise leave the employment of Vacation Inn, use this form to instantly terminate their access to this website. You cannot use this form to delete yourself.</p>
	<p class="bold green" id="delete-employee-error-message">
	<?php
	if (isset($_POST['employee-delete'])) {
		echo $output;
		unset($_POST['employee-delete']);
	}
	?>
	<form id="dismiss-employees" class="table-render" method="post" action="management.php">
		<table class="table table-hover table-bordered" id="reservation-table">
			<thead>
				<tr>
					<th class="vanish">Employee No.</th>
					<th>First Name</th>
					<th>Last Name</th>
					<th class="vanish">Email</th>
					<th>Delete</th>
				</tr>
			</thead>
			<tbody>
			
			<?php
			
			$query = "SELECT employee_id, first_name, last_name, email FROM employees";
			$result = $conn->query($query);
			
			if (!$result) {
				die("Database access failed: " . $conn->error);
			}
			
			$rows = $result->num_rows;
			
			for ($i = 0; $i < $rows; ++$i) {
				
				echo "<tr>";
				
				$result->data_seek($i);
				$row = $result->fetch_array(MYSQLI_NUM);
				
				for ($j = 0; $j < 4; ++$j) {
					
					// Vanish class doesn't render certain (less important) fields for small screens
					echo ($j === 0 || $j === 3) ? "<td class='vanish'>$row[$j]</td>" : "<td>$row[$j]</td>";
				}
				
				// Echo delete checkbox, unless the employee is him/herself
				echo ($row[0] !== $_SESSION['customer_id']) ? "<td><input type='checkbox' name='delete[]' value='$row[0]'></td></tr>" : "<td></td></tr>";
			}
			
			?>
				
			</tbody>
		</table>
		<div class="form-group"> 
			<div class="col-sm-12 text-center">
				<button type="submit" class="btn btn-default" name="employee-delete" id="employee-delete">Delete Selected Employees</button>
			</div>
		</div>
		
	</form>
	*/
	?>
	
	
	</div> <!-- end of div with class .main -->

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../js/jquery-1.12.4.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>
	
  </body>
  

</html>