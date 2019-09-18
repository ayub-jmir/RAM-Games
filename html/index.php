<?php
	// Server should keep session data for AT LEAST 1 hour
	ini_set('session.gc_maxlifetime', 3600);

	// Each client should remember their session id for EXACTLY 1 hour
	session_set_cookie_params(3600);
	session_start();
	require_once 'databaseinfo.php';
	
	// I need to keep track of the current and previously visited pages
	$last_page = (isset($_SESSION['this_page'])) ? $_SESSION['this_page'] : '';
	$this_page = 'index.php';
	
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
    <title>RAM Games - Welcome!</title>

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
			<img class="logo" src="../pictures/Logomakr_8eWW9h.png" alt="RAM Games">
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
				<div id="navlabel">
					<a class="navbar-brand">Site Navigation</a>
				</div>
			</div>
			<div class="collapse navbar-collapse" id="myNavbar">
				<ul class="nav navbar-nav">
					<li class="active"><a href="index.php">Home</a></li>
					<li><a href="games.php">Games</a></li>
					<li><a href="cart.php">Cart</a></li>
					<li><a href="account.php">Account</a></li> 
					<li><a href="library.php">Library</a></li>
					<li><a href="transactions.php">Transactions</a></li>
					<?php 
						if(isset($_SESSION['isAdmin'])){
							echo "<li><a href='editgame.php'>Management</a></li>";;
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
	
	
	<!-- BODY -->
	<div class="main">
	<h1 class="bold text-center">Welcome to RAM Games!</h1>
	<p class="text-center">RAM Games has been Canada's best website for downloading games since 2017. Whether you play on PS4, Xbox One, PC, Switch, or 3DS, you're sure to find what you're looking for at RAM Games</p> 
	
	<!-- This following section implements Bootstrap Carousel code from W3Schools -->
	<div id="myCarousel" class="carousel slide" data-ride="carousel">
		<!-- Indicators -->
		<ol class="carousel-indicators">
			<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
			<li data-target="#myCarousel" data-slide-to="1"></li>
			<li data-target="#myCarousel" data-slide-to="2"></li>
			<!-- <li data-target="#myCarousel" data-slide-to="3"></li>
			<li data-target="#myCarousel" data-slide-to="4"></li> -->
		</ol>

		<!-- Wrapper for slides -->
		<div class="carousel-inner">
			<div class="item active">
				<img src="../pictures/xbox-one-controller.jpg" alt="Ram Games is the Place to Play">
				<div class="carousel-caption">
					<h3>Keep on Playing</h3>
					<p>Spend more time playing and less time shopping with RAM Games!</p>
				</div>
			</div>

			<div class="item">
				<img src="../pictures/pubg.jpg" alt="PUBG coming soon to Xbox One">
				<div class="carousel-caption">
					<h3>PUBG Coming Soon to Xbox One</h3>
					<p>Preorder PlayerUnknown's Battlegrounds for Xbox One Today!</p>
				</div>
			</div>

			<div class="item">
				<img src="../pictures/video-game-collection.jpg" alt="Lots of games in stock!">
				<div class="carousel-caption">
					<h3>We have TONS of games!</h3>
					<p>You're guaranteed to find what you're looking for at RAM Games!</p>
				</div>
			</div>
			
			<!--
			<div class="item">
				<img src="../pictures/hotel-restaurant.jpg" alt="Our Full-Service Restaurant">
				<div class="carousel-caption">
					<h3>Hungry?</h3>
					<p>Whether you're staying at Vacation Inn or not, make sure to try our restaurant! Free daily breakfast for guests!</p>
				</div>
			</div>
			
			<div class="item">
				<img src="../pictures/indoor-pool.jpg" alt="Our Indoor Pool">
				<div class="carousel-caption">
					<h3>Indoor Pool</h3>
					<p>If it's too cool outside to visit our lovely beach, relax in our indoor pool instead!</p>
				</div>
			</div>
			-->
		</div>

		<!-- Left and right controls -->
		<a class="left carousel-control" href="#myCarousel" data-slide="prev">
			<span class="glyphicon glyphicon-chevron-left"></span>
			<span class="sr-only">Previous</span>
		</a>
		<a class="right carousel-control" href="#myCarousel" data-slide="next">
			<span class="glyphicon glyphicon-chevron-right"></span>
			<span class="sr-only">Next</span>
		</a>
	</div>
	</div>
	
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../js/jquery-1.12.4.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>
	
</html>
