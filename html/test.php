<?php session_start();
	require_once 'databaseinfo.php';
	
	
		$plats = array();
		$query = "SELECT Platform_Name FROM Transactions NATURAL JOIN Platforms";
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
<html>
  <head>
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
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
        data.addColumn('string', 'Topping');
        data.addColumn('number', 'Slices');
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
        var options = {'title':'How Much Pizza I Ate Last Night',
                       'width':400,
                       'height':300};

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>
  </head>

  <body>
    <!--Div that will hold the pie chart-->
    <div id="chart_div"></div>
  </body>
</html>