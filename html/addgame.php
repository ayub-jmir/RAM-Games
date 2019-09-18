<?php
	// Server should keep session data for AT LEAST 1 hour
	ini_set('session.gc_maxlifetime', 3600);

	// Each client should remember their session id for EXACTLY 1 hour
	session_set_cookie_params(3600);
	session_start();
	require_once 'databaseinfo.php';

	// I need to keep track of the current and previously visited pages
	$last_page = (isset($_SESSION['this_page'])) ? $_SESSION['this_page'] : '';
	$this_page = 'addgame.php';
	
	// Prevent updates to this and last page if browser refreshed/page reloaded:
	if ($this_page !== $last_page) {
		$_SESSION['last_page'] = $last_page;
		$_SESSION['this_page'] = $this_page;
	}
	
	// User needs to be redirected to login if not logged in:
	if (!$_SESSION['login']) {
		header("Location: login.php");
	}
	
	// Only employees allowed here!
	if (!$_SESSION['isAdmin']) {
		header("Location: index.php");
	}
	
	// This session variable is used to prevent duplicate room insertions on a different page.
	// It needs to be unset for the user to make more room reservations.
	if (isset($_SESSION['refresh'])) {
		unset($_SESSION['refresh']);
	}
	
	// When form is submitted
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
?>


<!DOCTYPE html>
<html lang="en">
  <head>
  
    <!-- This website uses the Bootstrap default webpage layout as a starting point -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>RAM Games - Add Game</title>

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
  
  
  <!-- HEADER -->
	<div class="header">
		<div class="header-img">
			<img class="logo" src="../pictures/Logomakr_8eWW9h.png" alt="Ram Games">
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
					<li><a href='editgame.php'>Edit Game</a></li>
					<li class="active"><a href='addgame.php'>Add Game</a></li>
					<li><a href="updateemployee.php">Update Account</a></li>
					<li><a href="reports.php">Reports</a></li>
					<li><a href='index.php'>Clientside</a></li>
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<?php
					
					if (!$_SESSION['login']) {
						echo '<li><a href="signup.php"><span class="glyphicon glyphicon-user"></span> Sign Up</a></li>';
						echo '<li><a href="login.php"><span class="glyphicon glyphicon-log-in"></span> Log In</a></li>';
					} else {
						echo '<li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Log Out</a></li>';
					}
					
					?>
				</ul>
			</div>
		</div>
	</nav>
	
	<div class="main">

	<h1 class="bold text-center">Add New Games</h1>
	<p class="text-center">This form can be used to add new games to the database. This form does <b>not</b> list games for sale. To add a game for sale, use the "Add Platform"
	form to <a href="editgame.php">edit</a> a game, setting prices independently for each game platform the game is available on.</p>
	
	<p class="bold red" id="add-game-error-message">
	<?php
	
	if (isset($_POST['add-game-submit'])) {
		echo $output;
		unset($_POST['add-game-submit']);
	}
	?>
	</p>
	
	<!-- Form adapted from W3Schools -->
	<?php echo <<<_END
	<form class="form-horizontal form-render" action="management.php" method="post" id="add-game-form" enctype="multipart/form-data">
		<div class="form-group">
			<label class="control-label col-sm-2" for="add-game-name">Game Title:</label>
			<div class="col-sm-10">
				<input type="text" class="form-control" id="add-game-name" name="add-game-name" placeholder="Enter name of game" value="$game_name">
			</div>
		</div>
		<div class="form-group">
			<label for="add-game-description" class="control-label col-sm-2">Description:</label>
			<div class="col-sm-10">
				<textarea class="form-control" rows="5" name="add-game-description" id="add-game-description">$game_description</textarea>
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2" for="add-game-genre">Genre:</label>
			<div class="col-sm-10">
			<select class="form-control" id="add-game-genre" name="add-game-genre">
_END;
			// Use PHP to populate select list
			$query = "SELECT Genre_ID, Genre_Name FROM Genres ORDER BY Genre_Name";
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
			
			echo <<<_END
			</select>
			</div>
		</div>
		
		<div class="form-group">
			<label class="control-label col-sm-2" for="add-game-rating">ESRB Rating:</label>
			<div class="col-sm-10">
			<select class="form-control" id="add-game-rating" name="add-game-rating">
_END;
			// Use PHP to populate select list
			$query = "SELECT Rating, Rating_Name FROM Ratings";
			$result = $conn->query($query);
			
			if (!$result) {
				die ("Database access failed: " . $conn->error);
			}
			
			$rows = $result->num_rows;
			
			for ($i = 0; $i < $rows; ++$i) {
				$result->data_seek($i);
				$row = $result->fetch_array(MYSQLI_NUM);
				
				echo "<option value='$row[0]'>$row[0] - $row[1]</option>";
			}
			
			echo <<<_END
			</select>
			</div>
		</div>
		
		<div class="form-group">
			<label class="control-label col-sm-2" for="add-game-publisher">Publisher:</label>
			<div class="col-sm-10">
				<input type="text" class="form-control" id="add-game-publisher" name="add-game-publisher" placeholder="Enter game publisher" value="$game_publisher">
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2" for="add-game-release-date">Release Date:</label>
			<div class="col-sm-10"> 
				<input type="date" class="form-control" id="add-game-release-date" name="add-game-release-date" value="
_END;
					echo $date . '">';
					echo <<<_END
			</div>
		</div>
		<div class="form-group">
			<label class="control-label col-sm-2" for="add-game-image">Game Cover Image:</label>
			<div class="col-sm-10">
				<input type="file" class="form-control" id="add-game-image" name="add-game-image" accept="image/jpeg" placeholder="Must be in JPG format" required>
				<input type="hidden" name="MAX_FILE_SIZE" value="10485760">
			</div>
		</div>
		<div class="form-group"> 
			<div class="col-sm-12 text-center">
				<button type="submit" class="btn btn-default" name="add-game-submit" id="add-game-submit">Add Game To Database</button>
			</div>
		</div>
	</form>
_END;
?>
	</div>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../js/jquery-1.12.4.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>
	
	<!-- Personal script -->
	<script src="../js/editgame.js"></script>
  </body>
  
  <?php
	$result->close();
	$conn->close();
  ?>
</html>