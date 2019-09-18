<?php
session_start();
require_once 'databaseinfo.php';

	// I need to keep track of the current and previously visited pages
$last_page = (isset($_SESSION['this_page'])) ? $_SESSION['this_page'] : '';
$this_page = 'updateemployee.php';

	// Prevent updates to this and last page if browser refreshed/page reloaded:
if ($this_page !== $last_page) {
	$_SESSION['last_page'] = $last_page;
	$_SESSION['this_page'] = $this_page;
}

	// You need to be logged in to access this page
if (!$_SESSION['login']) {
	header("Location: login.php");
}

if(!isset($_SESSION['isAdmin'])){
		header('Location: index.php');
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
	<title>RAM Games - Update Information</title>

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
	$email = '';
	$admin='';
	$output = '';
	
	if (isset($_POST['submit'])) {
		
		// $email = $conn->real_escape_string(htmlspecialchars($_POST['username']));
		
		unset($_POST['submit']);
		
		
		if (!$_POST['username']) {
			echo 'Please enter your username or password.<br>';
		}
		$email = $_POST['username'];
		// Now verify they match the database information:
		if ($email) {
			$result = $conn->query("UPDATE Users SET Admin=1 WHERE Username='${email}' OR Email='${email}'");
			if (!$result) die("Database access failed: " . $conn->error);
			}
		}
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
						<li><a href='editgame.php'>Edit Game</a></li>
						<li><a href='addgame.php'>Add Game</a></li>
						<li class="active"><a href="updateemployee.php">Update Account</a></li>
						<li><a href="reports.php">Reports</a></li>
						<li><a href='index.php'>Clientside</a></li>
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

			<h1 class="bold text-center">Update Account</h1>
			<form action="updateemployee.php" method="post">
			<div class="form-group">

				<input type="username" class="form-control" id="username" name="username" placeholder="Enter email or username">
				<button id="submit" type="submit" name="submit">Update</button>
			</div>
			</form>
		</div>

	</body>
	</html>

