<?php
	session_start();
	require_once 'databaseinfo.php';

	// I need to keep track of the current and previously visited pages
	$last_page = (isset($_SESSION['this_page'])) ? $_SESSION['this_page'] : '';
	$this_page = 'login.php';
	
	// Prevent updates to this and last page if browser refreshed/page reloaded:
	if ($this_page !== $last_page) {
		$_SESSION['last_page'] = $last_page;
		$_SESSION['this_page'] = $this_page;
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
    <title>RAM Games - Login</title>

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
	$password = '';
	$admin='';
	$output = '';
	
	if (isset($_POST['submit'])) {
		
		$email = $conn->real_escape_string(htmlspecialchars($_POST['email']));
		$password = $conn->real_escape_string(hash('ripemd128', $pre_salt . $_POST['password'] . $post_salt));
				
		unset($_POST['submit']);
		
		// Verify email and password are entered
		if (!$email) {
			$output .= 'Please enter your email address.<br>';
		}
		
		if (!$_POST['password']) {
			$output .= 'Please enter your password.<br>';
		}
		
		// Now verify they match the database information:
		if ($email && $_POST['password']) {
			
			// First, check for employees with this access info
			$check = filter_var($email, FILTER_VALIDATE_EMAIL);
			if (!$check){
				$query = "SELECT UserID FROM Users WHERE Username = '$email' AND Password = '$password'";
				$queryB = "SELECT Admin FROM Users WHERE Username = '$email'";
			} else {
				$query = "SELECT UserID FROM Users WHERE Email = '$email' AND Password = '$password'";
				$queryB = "SELECT Admin FROM Users WHERE Email = '$email'";
			}
			
			$result = $conn->query($query);
			
			if (!$result) {
				die("Database access failed: " . $conn->error);
			}
			
			if($result->num_rows === 1) {

				// Log in the employee and redirect to management.php:
				$result->data_seek(0);
				$row = $result->fetch_array(MYSQLI_NUM);
				
				$_SESSION['customer_id'] = $row[0]; 
				$_SESSION['login'] = true;
				$result->close();
				$ResultB = $conn->query($queryB);
				$ResultB->data_seek(0);
				$row = $ResultB->fetch_array(MYSQLI_NUM);

				if($row[0]) {
					$_SESSION['isAdmin'] = true;
				}

				if ($_SESSION['last_page'] === 'login.php') {
					header("Location: index.php");
				}
				
				// Otherwise, redirect to last page user was browsing
				header("Location: $_SESSION[last_page]");

			}
			
			// Otherwise, login credentials are invalid:
			else {
				$output .= "Your username/email or password are invalid.<br>";
			}
			
			
			
		}		
	}
	
  ?>
  
  <!-- HEADER -->
	<div class="header">
		<div class="header-img">
			<img class="logo" src="../pictures/Logomakr_8eWW9h.png" alt="Vacation Inn">
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
					<li><a href="games.php">Games</a></li>
					<li><a href="cart.php">Cart</a></li>
					<li><a href="account.php">Account</a></li>
					<li><a href="library.php">Library</a></li>
					<li><a href="transactions.php">Transactions</a></li>
					<?php if(isset($_SESSION['isAdmin']))
						echo "<li><a href='management.php'>Management</a></li>";
					?>
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<?php
					
					if (isset($_SESSION['login'])) {
						echo '<li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Log Out</a></li>';
					} else {
						echo '<li><a href="signup.php"><span class="glyphicon glyphicon-user"></span> Sign Up</a></li>';
						echo '<li class="active"><a href="login.php"><span class="glyphicon glyphicon-log-in"></span> Log In</a></li>';
					}
					
					?>
				</ul>
			</div>
		</div>
	</nav>
	
	<div class="main">
	
	<?php

	
	// It shouldn't be possible to reach this page while logged in, but just in case...
	if (isset($_SESSION['login'])) {
		echo "<h1 class='bold text-center'>You are already logged in</h1><p class='text-center'>You shouldn't have been able to get to this page</p>";
	}
	
	else {
		
		// Echo any error messages
		echo "<span class='bold red'>$output</span>";
		
		echo <<<_END
		<h1 class="bold text-center">Log In</h1>
		<p class="bold text-center">Enter your username and password to log in</p>
		<form class="form-horizontal form-render" action="login.php" method="post">
			<div class="form-group">
				<label class="control-label col-sm-2" for="email">Username or Email:</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="email" name="email" placeholder="Enter username or email address" value="$email">
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-sm-2" for="password">Password:</label>
				<div class="col-sm-10">
					<input type="password" class="form-control" id="password" name="password" placeholder="Enter password">
				</div>
			</div>
			<div class="form-group"> 
				<div class="col-sm-12 text-center">
					<button type="submit" class="btn btn-default" name="submit" id="submit">Submit</button>
				</div>
			</div>
		</form>
_END;
	}
	
	if (!isset($_SESSION['login'])) {
		echo <<<_END
			<p class="text-center"><span class="bold">Not yet a member?</span>&nbsp;Sign up <a href="signup.php">here</a></p>
_END;
	}
	
	?>
	
	</div>
	</div> <!-- end of div with class .main -->

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../js/jquery-1.12.4.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>
	<script src="../js/signup.js"></script>
  </body>
  
  <?php
	if (isset($result)) {
		$result->close();
	}
	$conn->close();
  ?>
</html>
