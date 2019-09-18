$(document).ready(function() {
	
	// To load Google Charts
	google.charts.load('current', {packages: ['corechart', 'bar']});
	google.charts.setOnLoadCallback(drawChart);
	
	$('#group').change(function() {
		drawChart();
	});
	
	$('#type').change(function() {
		drawChart();
	});
	
	// Google charts don't automatically resize, so this will fix that problem, according to
	// https://stackoverflow.com/questions/25523294/best-practice-for-responsive-google-charts
	$(window).resize(function() {
		drawChart();
	});
	
	function drawChart() {
		
		var graphType = $('#type').val();
		var graphGroup = $('#group').val();
		
		$.get('reportgenerator.php?type=' + graphType + '&group=' + graphGroup, function(data, status) {
			
			var title = (graphType === 'r') ? 'Weekly Hotel Revenue' : 'Weekly Hotel Occupancy';
			title += (graphGroup === 'r') ? ' by Room' : '';
			$('#chart-title').text(title);
			
			var chartData = google.visualization.arrayToDataTable(eval(data));
			var options = {"legend" : {"position" : "bottom"}, "chartArea" : {"width" : "80%", "height" : 200, "left" : 75}, "fontName" : "Fira Sans",
				"titleTextStyle" : {"fontSize" : 20}};
			
			var chart = new google.visualization.BarChart(document.getElementById('chart'));
			chart.draw(chartData, options);
		});
	
	}
	
});