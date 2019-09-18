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
	// (The user shouldn't be able to reach this page without logging in anyway)
	if (!isset($_SESSION['login'])) {
		header("Location: login.php");
	}
	
	$error_message = '';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
  
    <!-- This website uses the Bootstrap default webpage layout as a starting point -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>RAM Games - Order Confirmation</title>

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
	
	// Fake initializations so these variables are in scope at this level
	$result = '';
	$query = '';
	$rows = 0;
	$row = array();
	$payment = $_POST['credit-card-type'];
	$gids = $_POST['gids'];
	$platforms = $_POST['platforms'];
	$prices = $_POST['prices'];
	$customer_id = $conn->real_escape_string($_SESSION['customer_id']);
	$today = date('Y-m-d', time());
	$output = '';
	$error = false;
	
	// Prevent duplicate insertions upon refreshing page:
	if (!isset($_SESSION['refresh'])) {
		$_SESSION['refresh'] = false;
	}
	
	if (!$_SESSION['refresh']) {
		// Reserve each room, one at a time:
		for ($i = 0; $i < count($gids); $i++) {
			
			
			if ((count($gids) > 1) && ($i === 1)){
				$query = "SELECT Transaction_ID FROM Transactions WHERE Game_ID = '$gids[0]' AND Platform_ID = '$platforms[0]' AND UserID = '$customer_id'";
				$result = $conn->query($query);
			
				if (!$result) {
					die("Database access failed: " . $conn->error);
				}
			
				if($result->num_rows) {

					// Log in the employee and redirect to management.php:
					$result->data_seek(0);
					$row = $result->fetch_array(MYSQLI_NUM);
					$transaction = $row[0];
				}
			}
			
			if ($i === 0) {
				$query = "INSERT INTO Transactions(Game_ID, Platform_ID, UserID, Date, Payment_Type, Amount) VALUES(?, ?, ?, ?, ?, ?)";
				$stmt = $conn->prepare($query);
				$stmt->bind_param('isissd', $gids[$i], $platforms[$i], $customer_id, $today, $payment, $prices[$i]);
			} else {
				$query = "INSERT INTO Transactions(Transaction_ID, Game_ID, Platform_ID, UserID, Date, Payment_Type, Amount) VALUES(?, ?, ?, ?, ?, ?, ?)";
				$stmt = $conn->prepare($query);
				$stmt->bind_param('iisissd', $transaction, $gids[$i], $platforms[$i], $customer_id, $today, $payment, $prices[$i]);
			}
			
			
			if ($stmt->execute()) {
				$output .= "Order Successful! Enjoy Your Games!<br>";	
				unset($_SESSION['cart']);
				unset($_SESSION['id-array']);
			}
			else {
				$output .= "There was an error trying to place your order Please try again later.<br>";
				$error = true;
				die($stmt->error);
			}
		}
		$_SESSION['refresh'] = true;
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
					<?php 
						if(isset($_SESSION['isAdmin'])){
							echo "<li><a href='management.php'>Management</a></li>";
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
	<?php
	
	if ($error) {
		echo "<h1 class='bold text-center'>Order Failed</h1>";
		echo "<p>$output</p>";
	}
		
	else {
		
	?>
		<img src="/../pictures/order.png" alt="Nice Buy!" style="display: block; margin: auto;">
		<p class='bold text-center'>You have successfully placed your order! Enjoy Your Game!</p>
		<p class='text-center'><a href='transactions.php'><button type='button' class='btn btn-default' id='to-reservations' name='to-reservations'>View My Transactions</button></a></p>
		
	<?php
	
	} // end else
		
	?>
	</div>
	

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../js/jquery-1.12.4.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>
	
	<!-- Personal script -->
  </body>
  
<?php
    if (isset($result)){
		$result->close();
	}
	if (isset($conn)){
		$conn->close();
	}
?>
</html>