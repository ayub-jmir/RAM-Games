<?php
	// Server should keep session data for AT LEAST 1 hour
	ini_set('session.gc_maxlifetime', 3600);

	// Each client should remember their session id for EXACTLY 1 hour
	session_set_cookie_params(3600);
	session_start();
	require_once 'databaseinfo.php';

	// I need to keep track of the current and previously visited pages
	$last_page = (isset($_SESSION['this_page'])) ? $_SESSION['this_page'] : '';
	$this_page = 'games.php';
	
	// Prevent updates to this and last page if browser refreshed/page reloaded:
	if ($this_page !== $last_page) {
		$_SESSION['last_page'] = $last_page;
		$_SESSION['this_page'] = $this_page;
	}
	
	// User needs to be redirected to login if not logged in:
	if (!$_SESSION['login']) {
		header("Location: login.php");
	}
	
	// This session variable is used to prevent duplicate room insertions on a different page.
	// It needs to be unset for the user to make more room reservations.
	if (isset($_SESSION['refresh'])) {
		unset($_SESSION['refresh']);
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
    <title>RAM Games - Game Listings</title>

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
	<nav class="navbar navbar-inverse">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="#">Site Navigation</a>
			</div>
			<div class="collapse navbar-collapse" id="myNavbar">
				<ul class="nav navbar-nav">
					<li><a href="index.php">Home</a></li>
					<li class="active"><a href="games.php">Games</a></li>
					<li><a href="cart.php">Cart</a></li>
					<li><a href="account.php">Account</a></li> 
					<li><a href="library.php">Library</a></li>
					<li><a href="transactions.php">Transactions</a></li>
					<?php 
						if(isset($_SESSION['isAdmin'])){
							echo "<li><a href='editgame.php'>Management</a></li>";
						}
					?>
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
		
		<p id="error-message" class="bold red"></p>
		
			<div id="reselect-games">
				<button type='button' class='btn btn-primary' id='new-search' name='new-search'>New Search</button></p>
			</div>
		
		
		<div class="form-horizontal" id="game-form" class="form-render">
		
			<div class="form-group">
				<label id="category-label" class="control-label col-sm-2" for="name">Name:</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="name" id="name" placeholder="Search for a game title">
				</div>
			</div>
		
		
			<div class="form-group">
					<label id="category-label" class="control-label col-sm-2" for="Genre">Genre:</label>
					<div class="col-sm-10">
						<select class="form-control" id="genre" name="genre">
					
					<?php	
					$query = "SELECT Genre_ID, Genre_Name FROM Genres ORDER BY Genre_Name";
					$result = $conn->query($query);
					
					if (!$result) {
						die ("Database access failed: " . $conn->error);
					}
					
					$rows = $result->num_rows;
					echo "<option value='ALL' selected>All</option>";
					
					for ($i = 0; $i < $rows; ++$i) {
						$result->data_seek($i);
						$row = $result->fetch_array(MYSQLI_NUM);
						
						if ($row[0] == 'AA') {
							echo "<option value='AA'>Action-Adventure</option>";
						} else {
							echo "<option value='$row[0]'>$row[1]</option>";
						}
					}
					?>
						</select>
					</div>
				</div> 

			<div class="form-group">
					<label id="platform-label" class="control-label col-sm-2" for="platform">Platform:</label>
					<div class="col-sm-10">
						<select class="form-control" id="platform" name="platform">
					
					<?php	
					$query = "SELECT Platform_ID, Platform_Name FROM Platforms ORDER BY Platform_Name";
					$result = $conn->query($query);
					
					if (!$result) {
						die ("Database access failed: " . $conn->error);
					}
					
					$rows = $result->num_rows;
					echo "<option value='ALL' selected>All</option>";				

					for ($i = 0; $i < $rows; ++$i) {
						$result->data_seek($i);
						$row = $result->fetch_array(MYSQLI_NUM);
						
						if ($row[0] == '3DS') {
							echo "<option value='3DS'>3DS</option>";
						} else {
							echo "<option value='$row[0]'>$row[1]</option>";
						}
					}
					?>
						</select>
					</div>
				</div> 

			<div class="form-group">
				<label class="control-label col-sm-2" for="order">Order Results By:</label>
				<div class="col-sm-10">
					<select class="form-control" id="order" name="order">
						<option value="0">Platform</option>
						<option value="1">Price (lowest first)</option>
						<option value="2">Price (highest first)</option>
						<option value="3">Genre</option>
					</select>
				</div>
			</div>

			<div class="form-group"> 
				<div class="col-sm-offset-4 col-sm-8">
					<button type="button" class="btn btn-default" name="submit-search" id="submit-search">Submit</button>
				</div>
			</div>

		</div>
		
		<div id="game-content">
		
		</div>
		
	
		
	</div>
	

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../js/jquery-1.12.4.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>
	
	<!-- Personal script -->
	<script src="../js/games.js"></script>
  </body>
  
  <?php
	$result->close();
	$conn->close();
  ?>
</html>
