<?php
	session_start();
	require_once 'databaseinfo.php';

	// I need to keep track of the current and previously visited pages
	$last_page = (isset($_SESSION['this_page'])) ? $_SESSION['this_page'] : '';
	$this_page = 'reports.php';
	
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
    <title>RAM Games - Reports</title>

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
	
	<!-- Google Charts Script -->
	<script src="https://www.gstatic.com/charts/loader.js"></script>
	
	<?php 
		$plats = array();
		$query = "SELECT DISTINCT Platform_Name FROM Transactions NATURAL JOIN Platforms";
		$result = $conn->query($query);
		$numrows = $result->num_rows;
		$numplats = $numrows;
		
		for ($i = 0; $i < $numrows; $i++){
			$result->data_seek($i);
			$curr = $result->fetch_array(MYSQLI_NUM);
			$plats[] = $curr[0];
		}
		
		$counts = array();
		
		for ($i = 0; $i < $numplats; $i++){
			$query = "SELECT COUNT(*) FROM Transactions NATURAL JOIN Platforms WHERE Platform_Name = '$plats[$i]'";
			$result = $conn->query($query);
			
			$result->data_seek(0);
			$curr = $result->fetch_array(MYSQLI_NUM);
			$counts[] = $curr[0];
		}
	?>
			
	<script type="text/javascript">

	  // Load the Visualization API and the corechart package.
	  google.charts.load('current', {'packages':['corechart']});

	  // Set a callback to run when the Google Visualization API is loaded.
	  google.charts.setOnLoadCallback(drawChart);

	  // Callback that creates and populates a data table,
	  // instantiates the pie chart, passes in the data and
	  // draws it.
	  function drawChart() {

		// Create the data table.
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Platform');
		data.addColumn('number', 'Sales Numbers');
		data.addRows([
		<?php
		for ($i = 0; $i < $numplats; $i++){
			echo "['" . $plats[$i] . "', " . $counts[$i] . "]";
			if ($i !== $numplats - 1){
				echo ", ";
			}
		}
			echo "]);";
		?>
		// Set chart options
		var options = {'title':'Total Sales By Platform',
					   'width':400,
					   'height':300};

		// Instantiate and draw our chart, passing in some options.
		var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
		chart.draw(data, options);
		}
	</script>
		
	<?php 
		$genres = array();
		$query2 = "SELECT DISTINCT Genre_Name FROM Transactions NATURAL JOIN Genres";
		$result2 = $conn->query($query2);
		$numrows2 = $result2->num_rows;
		$numgenres = $numrows2;
		
		for ($i = 0; $i < $numgenres; $i++){
			$result2->data_seek($i);
			$curr2 = $result2->fetch_array(MYSQLI_NUM);
			$genres[] = $curr2[0];
		}
		
		$counts2 = array();
		
		for ($i = 0; $i < $numgenres; $i++){
			$query2 = "SELECT COUNT(*) FROM Transactions NATURAL JOIN Genres NATURAL JOIN Games WHERE Genre_Name = '$genres[$i]'";
			$result2 = $conn->query($query2);
			
			$result2->data_seek(0);
			$curr2 = $result2->fetch_array(MYSQLI_NUM);
			$counts2[] = $curr2[0];
		}
	?>
	
	<script type="text/javascript">

	  // Load the Visualization API and the corechart package.
	  google.charts.load('current', {'packages':['corechart']});

	  // Set a callback to run when the Google Visualization API is loaded.
	  google.charts.setOnLoadCallback(drawChart);

	  // Callback that creates and populates a data table,
	  // instantiates the pie chart, passes in the data and
	  // draws it.
	  function drawChart() {

		// Create the data table.
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Platform');
		data.addColumn('number', 'Sales Numbers');
		data.addRows([
		<?php
		for ($i = 0; $i < $numgenres; $i++){
			echo "['" . $genres[$i] . "', " . $counts2[$i] . "]";
			if ($i !== $numgenres - 1){
				echo ", ";
			}
		}
			echo "]);";
		?>
		// Set chart options
		var options = {'title':'Total Sales By Genre',
					   'width':400,
					   'height':300};

		// Instantiate and draw our chart, passing in some options.
		var chart = new google.visualization.PieChart(document.getElementById('chart_div2'));
		chart.draw(data, options);
		}
	</script>
	
  </head>
  <body>
  
  <!-- HEADER -->
	<div class="header">
		<div class="header-img">
			<img class="logo" src="../pictures/Logomakr_8eWW9h.png">
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
					<li><a href="updateemployee.php">Update Account</a></li>
					<li class="active"><a href="reports.php">Reports</a></li>
					<li><a href='index.php'>Clientside</a></li>
				</ul>
				<ul class="nav navbar-nav navbar-right">			
					<!-- We don't need PHP to check for login here since employees MUST be logged in at all times -->
					<li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Log Out</a></li>
				</ul>
			</div>
		</div>
	</nav>
	
	<div class="main">
	<h1 class="text-center bold">Reports</h1>
	<div class="form-render" id="game-form">
		<div id="chart_div"></div>
	</div>
	<div class="form-render" id="game-form">
		<div id="chart_div2"></div>
	</div>
	</div> <!-- end of div with class .main -->

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../js/jquery-1.12.4.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="../js/bootstrap.min.js"></script>
	
  </body>
  
  <?php
	$result->close();
	$conn->close();
  ?>
</html>