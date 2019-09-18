<?php
	// Server should keep session data for AT LEAST 1 hour
	ini_set('session.gc_maxlifetime', 3600);

	// Each client should remember their session id for EXACTLY 1 hour
	session_set_cookie_params(3600);
	session_start();
	require_once 'databaseinfo.php';

	// I need to keep track of the current and previously visited pages
	$last_page = (isset($_SESSION['this_page'])) ? $_SESSION['this_page'] : '';
	$this_page = 'library.php';
	
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
    <title>Ram Games - Library</title>

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
	$game = array();
	$row2 = array();
	$userid = $_SESSION['customer_id'];
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
					<li><a href="cart.php">Cart</a></li>
					<li><a href="account.php">Account</a></li>
					<li class="active"><a href="library.php">Library</a></li>
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
	
	<?php 
		if (isset($_POST['score'])){
			$score = $_POST['score'];
			$gameid = $_POST['gameid'];
			$gamename = $_POST['gamename'];
			$platid = $_POST['platid'];
			
			$query = "INSERT INTO Reviews(Game_ID, Score, UserID, Platform_ID) VALUES (?, ?, ?, ?)";
			$stmt = $conn->prepare($query);
			$stmt->bind_param('iiis', $gameid, $score, $userid, $platid);
			
			if (!($stmt->execute())){
				die($stmt->error);
			}
			
			$output .= "<p>Your rating for " . $gamename . " was successful!</p><br>";
		}
		
		if(isset($_POST['rateid'])){
			$rateid = $_POST['rateid'];
			$rateplat = $_POST['rateplatform'];
			$returnquery = "DELETE FROM Reviews WHERE Game_ID = '$rateid' AND Platform_ID = '$rateplat' AND UserID = '$userid'";
			
			if(!($conn->query($returnquery))){
				die("Database access failed: " . $conn->error);
			}
		}
	?>
	
	<div class="main">
			<div id="game-selections">
				<h3><b>Your Games Library</b></h3>
				<?php echo $output; ?>
				<table class="table table-hover table-bordered">
					<thead>
						<tr>
							<th class="vanish">Play</th>
							<th>Name</th>
							<th>Platform</th>
							<th>Date Purchased</th>
							<th>Your Rating</th>
						</tr>
					</thead>
					<tbody>
						<?php
			
						$query = "SELECT Game_ID, Name, Platform_Name, Date, Platform_ID FROM Transactions NATURAL JOIN Platforms NATURAL JOIN Games WHERE UserID = '$userid'";
						
						$result = $conn->query($query);
								
						if (!$result) {
							die ("Database access failed: " . $conn->error);
						}
						
						$rows = $result->num_rows;
						
						for ($i = 0; $i < $rows; ++$i){
							$result->data_seek($i);
							$game = $result->fetch_array(MYSQLI_NUM);
						
						
							echo <<<_END
								<tr>
									<td class="vanish">
									<form method="post">
										<input type="hidden" name="download">
										<button type="submit">Download</button>
									</form> </td>
									<td>$game[1]</td>
									<td>$game[2]</td>
									<td>$game[3]</td>
_END;
						
				$query = "SELECT Score FROM Reviews WHERE UserID = '$userid' AND Game_ID = '$game[0]'";
			
				$result2 = $conn->query($query);
					
				if (!$result2) {
					die ("Database access failed: " . $conn->error);
				}
				
				$result2->data_seek($i);
				$row2 = $result2->fetch_array(MYSQLI_NUM);
		
				if (!$row2[0]){
					echo <<<_END
						<td>
							<form action="library.php" method="post">
								<div class="form-group">
									<div class="col-sm-10">
										<select class="form-control" name="score">
											<option value="1" selected>1</option>
											<option value="2" selected>2</option>
											<option value="3" selected>3</option>
											<option value="4" selected>4</option>
											<option value="5" selected>5</option>
										</select>
										<input type='hidden' name='gameid' value='$game[0]'>
										<input type='hidden' name='userid' value='$userid'>
										<input type='hidden' name='gamename' value='$game[1]'>
										<input type='hidden' name='platid' value='$game[4]'>
										<button type="submit">Rate</button>
									</div>
								</div>
							</form>
						</td>
_END;

				} else {
					echo <<<_END
					<td>Your Rating: $row2[0]
						<form action="library.php" method="post">
							<input type='hidden' name='rateid' value='$game[0]'>
							<input type='hidden' name='rateplatform' value='$game[4]'>
							<button type="submit">Change Rating</button>
						</form>	
					</td>
_END;
				}
				?>
				
					</tr>
				<?php
						}
			?>
						
					</tbody>
				</table>
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
	$result->close();
	$conn->close();
  ?>
</html>