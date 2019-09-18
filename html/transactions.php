<?php
	// Server should keep session data for AT LEAST 1 hour
	ini_set('session.gc_maxlifetime', 3600);

	// Each client should remember their session id for EXACTLY 1 hour
	session_set_cookie_params(3600);
	session_start();
	require_once 'databaseinfo.php';

	// I need to keep track of the current and previously visited pages
	$last_page = (isset($_SESSION['this_page'])) ? $_SESSION['this_page'] : '';
	$this_page = 'transactions.php';
	
	// Prevent updates to this and last page if browser refreshed/page reloaded:
	if ($this_page !== $last_page) {
		$_SESSION['last_page'] = $last_page;
		$_SESSION['this_page'] = $this_page;
	}
	
	// User needs to be redirected to login if not logged in:
	// (The user shouldn't be able to reach this page without logging in anyway)
	if (!$_SESSION['login']) {
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
    <title>Ram Games - Transactions</title>

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
	$output = "";
	$userid = $_SESSION['customer_id'];
  ?>
  
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
					<li><a href="games.php">Games</a></li>
					<li><a href="cart.php">Cart</a></li>
					<li><a href="account.php">Account</a></li>
					<li><a href="library.php">Library</a></li>
					<li class="active"><a href="cart.php">Transactions</a></li>
					<?php 
						if(isset($_SESSION['isAdmin'])){
							echo "<li><a href='editgame.php'>Management</a></li>";
						}
					?>
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<?php
					if (isset($_SESSION['login'])) {
						echo '<li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Log Out</a></li>';
					} else {
						echo '<li><a href="signup.php"><span class="glyphicon glyphicon-user"></span> Sign Up</a></li>';
						echo '<li><a href="login.php"><span class="glyphicon glyphicon-log-in"></span> Log In</a></li>';
					}
					?>
				</ul>
			</div>
		</div>
	</nav>
	
	<div class="main">
	
	<?php
	
		if(isset($_POST['returngameid'])){
			$returngameid = $_POST['returngameid'];
			$returnplatformid = $_POST['returnplatformid'];
			$returntransid = $_POST['returntransid'];
			$returnname = $_POST['returnname'];
			
			$returnquery = "DELETE FROM Transactions WHERE Game_ID = '$returngameid' AND Platform_ID = '$returnplatformid' AND Transaction_ID = '$returntransid' AND UserID = '$userid'";
			$returnquery2 = "DELETE FROM Reviews WHERE Game_ID = '$returngameid' AND Platform_ID = '$returnplatformid' AND UserID = '$userid'";
			
			if(!($conn->query($returnquery))){
				die("Database access failed: " . $conn->error);
			} else {
				echo "<p>Succesfully returned " . $returnname . "!<p><br>";
			}
			
			if(!($conn->query($returnquery2))){
				die("Database access failed: " . $conn->error);
			}
		}
	?>
		
		<?php
			$query = "SELECT DISTINCT Transaction_ID FROM Transactions WHERE UserID = '$userid'";
			
			$result1 = $conn->query($query);

				
			if (!$result1) {
				die ("Database access failed: " . $conn->error);
			}
			
			$rows = $result1->num_rows;
			
			$query2 = "SELECT Transaction_ID, Name, Platform_Name, Amount, Date, Game_ID, Platform_ID FROM Transactions NATURAL JOIN Games NATURAL JOIN Platforms WHERE UserID = '$userid'";
			
			$result2 = $conn->query($query2);

			if (!$result2) {
				die ("Database access failed: " . $conn->error);
			}
			
			$rows2 = $result2->num_rows;
		?>
			
			<div id="game-selections">
				<h2><b>Your Transactions</b></h2>
				
		<?php 
			echo $output;
			
			$count = 0;
			for($m = 0; $m < $rows; $m++){
				$result1->data_seek($m);
				$trans = $result1->fetch_array(MYSQLI_NUM);
				
				$count += 1;
				$subtotal = 0;
				$tax = 0;
				$total_cost = 0;
				
				echo "<h3><b> Transaction " . $count . "</b></h3>";
				
				echo <<<_END
			<table class="table table-hover table-bordered">
				<thead>
					<tr>
						<th>Return</th>
						<th>Game</th>
						<th>Platform</th>
						<th>Date of Purchase</th>
						<th>Price Paid</th>
					</tr>
				</thead>
				<tbody>
_END;
			for($n = 0; $n < $rows2; $n++){
				$result2->data_seek($n);
				$info = $result2->fetch_array(MYSQLI_NUM);
				
				if ($trans[0] === $info[0]){
					echo <<<_END
					<tr>
						<td>
						<form method="post">
							<input type="hidden" name="returngameid" value="$info[5]">
							<input type="hidden" name="returnplatformid" value="$info[6]">
							<input type="hidden" name="returntransid" value="$info[0]">
							<input type="hidden" name="returnname" value="$info[1]">
							<button type="submit">Return</button>
						</form> </td>
						<td>$info[1]</td>
						<td>$info[2]</td>
						<td>$info[4]</td>
_END;
					echo "<td>$" . number_format(doubleval($info[3]), 2) . "</td>";
					$subtotal += doubleval($info[3]);
				}
				
			}
			$tax = $subtotal * .13;
			$total_cost = $subtotal + $tax;
		
			?>
						<tr>
							<td class="align-right" colspan="4">Subtotal:</td>
							<td>$<?php echo number_format($subtotal, 2); ?></td>
						</tr>
						<tr>
							<td class="align-right" colspan="4">Tax:</td>
							<td>$<?php echo number_format($tax, 2); ?></td>
						</tr>
						<tr>
							<td class="align-right" colspan="4">TOTAL COST:</td>
							<td>$<?php echo number_format($total_cost, 2); ?></td>
						</tr>
				</tbody>
			</table>
		<?php
			}
		?>
			</div>
	</div>
	

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../js/jquery-1.12.4.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>
	
	<!-- Personal script -->
	<script src="../js/reserve.js"></script>
  </body>
  
  <?php
	$result1->close();
	$result2->close();
	$conn->close();
  ?>
</html>