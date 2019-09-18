<?php
	// Server should keep session data for AT LEAST 1 hour
	ini_set('session.gc_maxlifetime', 3600);

	// Each client should remember their session id for EXACTLY 1 hour
	session_set_cookie_params(3600);
	session_start();
	require_once 'databaseinfo.php';

	// I need to keep track of the current and previously visited pages
	$last_page = (isset($_SESSION['this_page'])) ? $_SESSION['this_page'] : '';
	$this_page = 'cart.php';
	
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
	
	// If user didn't select a game or the cart is empty
	if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])){
		if (!isset($_POST['games']) || empty($_POST['games'])) {
			$error_message .= 'Error: No games selected. Please select at lease one <a href="games.php"></a> to continue.<br>';
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
    <title>Ram Games - Game Cart</title>

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
	
	if (isset($_POST['games'])){
		$games = $_POST['games'];
	}
	
	if (isset($_POST['remove'])){
		$remove = $_POST['remove'];
	}
	
	$price = array();
	$platforms = array();
	$names = array();
	$gameids = array();
	$pid = array();
	$output = "";
	
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
					<li class="active"><a href="cart.php">Cart</a></li>
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
		
		if ($error_message) {
			echo "<p id='error-message' class='bold red'>$error_message</p>";
		}
		
		else {
		
		?>
			
			<?php
			$userid = $_SESSION['customer_id'];
			$gameidents = array();
			if (isset($_SESSION['id-array'])){
				$gameidents = $_SESSION['id-array'];
			}
				
			if (isset($_POST['games'])){
				$check = count($games);
				for ($g = 0; $g < $check; $g++) {
					$info = split("%", $games[$g]);
					$query = "SELECT * FROM Transactions WHERE Platform_ID = '$info[4]' AND UserID = '$userid' AND Game_ID = '$info[0]'";
			
					$result = $conn->query($query);
						
					if (!$result) {
						die ("Database access failed: " . $conn->error);
					}
				
					$rows = $result->num_rows;
					
				
					if ($rows > 0) {
						$output .= "<p id='error-message' class='bold red'>You already own the game $info[1] on $info[2]</p><br>";
						unset($games[$g]);
						unset($gameidents[$info[0]]);
					}
				}
			}
				
				foreach ($games as $game) {
					if (isset($game)){
						$info = split("%", $game);
						if (!isset($_SESSION['cart'][$info[0]][$info[4]])){
							$_SESSION['cart'][$info[0]][$info[4]] = $game;
							if (!in_array($info[0], $gameidents)){
								$gameidents[] = $info[0];
							}
						} else 
						{
							$output .= "<p id='error-message' class='bold red'>$info[1] was already in your cart!</p><br>";	
						}
					}
				}
			
			$_SESSION['id-array'] = $gameidents;
			
			//Then parse each one by tokenizing on % so we can fill the cart for user
			foreach ($gameidents as $id){
				foreach ($_SESSION['cart'][$id] as $x){
						$info = split("%", $x);
						if (isset($remove) && ($x === $remove)){
							unset($_SESSION['cart'][$info[0]][$info[4]]);
							unset($remove);
						} else {
							$gameids[] = $info[0];
							$names[] = $info[1];
							$platforms[] = $info[2];
							$price[] = $info[3];
							$pid[] = $info[4];
						}
				}
			}

		?>
			
			<div id="game-selections">
				<h3><b>Your Game Cart</b></h3>
				<?php echo $output ?>
				<table class="table table-hover table-bordered">
					<thead>
						<tr>
							<th>Remove From Cart</th>
							<th>Game</th>
							<th>Platform</th>
							<th>Price</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$count = count($gameids);
						for($i = 0; $i < $count; $i++){			
							echo <<<_END
								<tr>
									<td>
									<form action="cart.php" method="post">
										<input type="hidden" name="remove" value="$gameids[$i]%$names[$i]%$platforms[$i]%$price[$i]%$pid[$i]">
										<button type="submit">Remove</button>
									</form> </td>
									<td>$names[$i]</td>
									<td>$platforms[$i]</td>
_END;
						echo "<td>$" . number_format($price[$i], 2) . "</td></tr>";
						}
						
						// Calculate Subtotal and Taxes:
						$subtotal = 0;
						foreach ($price as $x) {
							$subtotal += $x;
						}
						
						$tax = $subtotal * .13;
						
						$total_cost = $subtotal + $tax;
						
						?>
						<tr>
							<td class="align-right" colspan="3">Subtotal:</td>
							<td>$<?php echo number_format($subtotal, 2); ?></td>
						</tr>
						<tr>
							<td class="align-right" colspan="3">Tax:</td>
							<td>$<?php echo number_format($tax, 2); ?></td>
						</tr>
						<tr>
							<td class="align-right" colspan="3">TOTAL COST:</td>
							<td>$<?php echo number_format($total_cost, 2); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<!-- TODO: Customer Information Here -->
			<div id="customer-info">
				<h3><b>Customer Information</b></h3>
				
				<?php
				$query = "SELECT UserID, First_Name, Last_Name, Email, Address, City, State_ID, Country_Name FROM Users NATURAL JOIN Countries WHERE UserID = $_SESSION[customer_id]";
				$result = $conn->query($query);
				$rows = $result->num_rows;
				
				if ($rows !== 1) {
					echo "<p class='red bold'>A serious error has occurred</p>";
					return;
				}
				
				$result->data_seek(0);
				$row = $result->fetch_array(MYSQLI_NUM);
				
				$fname = $row[1];
				$lname = $row[2];
				$email = $row[3];
				$address = $row[4];
				$city = $row[5];
				$state = ($row[6]) ? $row[6] : '';
				$country = $row[7];
					
				echo "<p><span class='bold'>Address:</span><br>$fname $lname<br>$address<br>$city" . (($state) ? ", $state" : '') . "<br>$country<br><br><span class='bold'>Email:</span><br>$email</p>";
				
				?>
				
				<form class="form-horizontal" id="change-address-form" action="account.php" method="post">
				<div class="form-group"> 
					<div class="col-sm-8">
						<button type="submit" class="btn btn-default" name="change-address" id="change-address">Update Customer Info</button>
					</div>
				</div>
				</form>
			
			</div>
			
			<!-- This form isn't actually necessary. It's too dangerous to store credit card info in a database (even salted),
			     and there isn't a way to "decrypt" it even if we did (e.g., for a refund). Also, this form is "validated" using
				 JavaScript instead of PHP. This form only exists to make the customer actually feel like s/he's paying for something. -->
			<h3><b>Payment Information</b></h3>
			<p>Please fill out your credit card information below and press the button below to confirm your order.</p>
			
			<p id="error-message" class="bold red"></p>
			
			<form class="form-horizontal form-render" id="payment-form" action="confirmation.php" method="post">
				<div class="form-group">
					<label class="control-label col-sm-4" for="credit-card-type">Credit Card Type:</label>
					<div class="col-sm-6">
						<select class="form-control" id="credit-card-type" name="credit-card-type">
							<option value="mastercard" selected>MasterCard</option>
							<option value="visa">Visa</option>
							<option value="americanexpress">American Express</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4" for="credit-card">Credit Card Number:</label>
					<div class="col-sm-6">
						<input type="number" class="form-control" id="credit-card" name="credit-card" placeholder="Enter credit card number" required>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4" for="expiration-month">Expiration Date (Month):</label>
					<div class="col-sm-6">
						<select class="form-control" id="expiration-month" name="expiration-month" required>
							<?php
							
							for ($i = 1; $i <= 12; ++$i) {
								echo ($i < 10) ? "<option value='0$i'>0$i</option>" : "<option value='$i'>$i</option>";
							}
							
							?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4" for="expiration-year">Expiration Date (Year):</label>
					<div class="col-sm-6">
						<select class="form-control" id="expiration-year" name="expiration-year" required>
							<?php
							
							$current_year = date('Y', time());
							
							for ($i = 0; $i < 6; ++$i) {
								echo "<option value='" . ($current_year + $i) . "'>" . ($current_year + $i) . "</option>";
							}
							
							?>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label col-sm-4" for="credit-card-csc">Card Security Code:</label>
					<div class="col-sm-6">
						<input type="number" class="form-control" id="credit-card-csc" name="credit-card-csc" placeholder="Enter 3 digits on back of credit card" required>
					</div>
				</div>
				
				<!-- Hidden form inputs for preserving data after submission -->
				<?php
				
				foreach ($gameids as $gid) {
					echo "<input type='hidden' name='gids[]' value='$gid'>";
				}
				
				foreach ($price as $pri) {
					echo "<input type='hidden' name='prices[]' value='$pri'>";
				}
				
				foreach ($pid as $pform) {
					echo "<input type='hidden' name='platforms[]' value='$pform'>";
				}
				
				?>
				<input type="hidden" name="total-cost" value="$total_cost">
				<div class="form-group"> 
					<div class="col-sm-12 text-center">
						<button type="submit" class="btn btn-default" name="submit" id="submit">Confirm Payment</button>
					</div>
				</div>
			</div>
		
		<?php
		
		} // end else
			
		?>
		
	</div>
	

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../js/jquery-1.12.4.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>
	
	<!-- Personal script -->
	<script src="../js/reserve.js"></script>
  </body>
  
  <?php
	$result->close();
	$conn->close();
  ?>
</html>