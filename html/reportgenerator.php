<?php

/* NOTES:
1. For the purposes of these calculations, a 'stay' includes all days from the start date to one day before the end date. If the end date were included,
   it would count one too many days for each stay.
2. A week begins on Sunday. Reservations that begin prior to Sunday and end after Sunday have a pro-rated amount of time allocated to the week
   before and after the Sunday.
3. Revenue calculations exclude tax collected on reservations (that revenue goes to the government, not Vacation Inn).
4. Our reports look at last week, this week, and the week after by default.
5. This file generates the data in JSON-compatible arrays, to allow the data to be easily rendered in charts using JavaScript.
*/

require_once 'databaseinfo.php';

$output = ''; // Output needs to be in scope at this level, so it is defined as empty here.

// Calculate revenue/occupancy based by rooms
$type = $_REQUEST['type']; // Report of occupancy rate ('o') or revenue ('r')? [DEFAULT IS OCCUPANCY RATE]
$group = $_REQUEST['group']; // Group results by room ('r'), or report for the whole hotel cumulatively ('t')? [DEFAULT IS WHOLE HOTEL]

// Get current week:
$today = date('w'); // Finds day of week, from Sunday (0) to Saturday (6)

// Get Sunday of this week, last week, and next week:
$current_week = date('Y-m-d', strtotime("-$today day"));
$last_week = date('Y-m-d', strtotime("$current_week -1 week"));
$next_week = date('Y-m-d', strtotime("$current_week +1 week"));
$week_after = date('Y-m-d', strtotime("$next_week +1 week"));


// Create arrays based on these dates:	
if ($group === 'r') {
	
	// Arrays to hold data based on these weeks. These are associative arrays for the 'room' grouping option.
	$rooms = array();
	$last = array();
	$current = array();
	$next = array();
	
	// Query to get every room number (yes, I know there are 6 rooms from 101-106,
	// but I have to do this "officially"):
	$query = "SELECT room_number FROM rooms ORDER BY room_number";
	$result = $conn->query($query);

	if (!$result) {
		die("Error connecting to database: " . $conn->error);
	}

	$rows = $result->num_rows;
	$total_rooms = $rows;

	for ($i = 0; $i < $rows; ++$i) {
		$result->data_seek($i);
		$row = $result->fetch_array(MYSQLI_NUM);
		$last[$row[0]] = 0;
		$current[$row[0]] = 0;
		$next[$row[0]] = 0;
		$rooms[] = $row[0];
	}
	
	
	// Calculate occupancy/revenue for last week, by room:
	for ($i = $last_week; $i != $current_week; $i = date('Y-m-d', strtotime("$i +1 day"))) {
		$query = "SELECT room_number, price FROM reservations WHERE start_date <= '$i' AND end_date > '$i'";
		$result = $conn->query($query);
		
		if (!$result) {
			die("Error connecting to database: " . $conn->error);
		}

		$rows = $result->num_rows;
		
		for ($j = 0; $j < $rows; ++$j) {
			$result->data_seek($j);
			$row = $result->fetch_array(MYSQLI_NUM);
			$last[$row[0]] += ($type === 'r') ? $row[1] : 1;
		}
	}
	
	// Calculate occupancy/revenue for this week, by room:
	for ($i = $current_week; $i != $next_week; $i = date('Y-m-d', strtotime("$i +1 day"))) {
		$query = "SELECT room_number, price FROM reservations WHERE start_date <= '$i' AND end_date > '$i'";
		$result = $conn->query($query);
		
		if (!$result) {
			die("Error connecting to database: " . $conn->error);
		}

		$rows = $result->num_rows;
		
		for ($j = 0; $j < $rows; ++$j) {
			$result->data_seek($j);
			$row = $result->fetch_array(MYSQLI_NUM);
			$current[$row[0]] += ($type === 'r') ? $row[1] : 1;
		}
	}
	
	// Calculate occupancy/revenue for next week, by room:
	for ($i = $next_week; $i != $week_after; $i = date('Y-m-d', strtotime("$i +1 day"))) {
		$query = "SELECT room_number, price FROM reservations WHERE start_date <= '$i' AND end_date > '$i'";
		$result = $conn->query($query);
		
		if (!$result) {
			die("Error connecting to database: " . $conn->error);
		}

		$rows = $result->num_rows;
		
		for ($j = 0; $j < $rows; ++$j) {
			$result->data_seek($j);
			$row = $result->fetch_array(MYSQLI_NUM);
			$next[$row[0]] += ($type === 'r') ? $row[1] : 1;
		}
	}
	
	// Generate report:
	$room_count = count($rooms);
	$output .= '[["Room", "Last Week", "This Week", "Next Week"], ';
	
	for ($i = 0; $i < $room_count; ++$i) {
		$output .= '["Room ' . $rooms[$i] . '", ' . $last[$rooms[$i]] . ', ' . $current[$rooms[$i]] . ', ' . $next[$rooms[$i]];
		$output .= ($i === $room_count - 1) ? ']]' : '], ';
	}
}

else {
	
	// Variables to track totals by week
	$last = 0;
	$current = 0;
	$next = 0;
	
	// Calculate totals for last week:
	for ($i = $last_week; $i != $current_week; $i = date('Y-m-d', strtotime("$i +1 day"))) {
		$query = "SELECT " . (($type === 'r') ? "SUM(price) " : "COUNT(*) ") .  "FROM reservations WHERE start_date <= '$i' AND end_date > '$i'";
		$result = $conn->query($query);
		
		if (!$result) {
			die("Error connecting to database: " . $conn->error);
		}

		$rows = $result->num_rows;
		
		if ($rows !== 1) {
			die("A serious database error has occurred. Please fix the database.");
		}
		
		$result->data_seek(0);
		$row = $result->fetch_array(MYSQLI_NUM);
		$last += $row[0];
	}
	
	// Calculate totals for this week:
	for ($i = $current_week; $i != $next_week; $i = date('Y-m-d', strtotime("$i +1 day"))) {
		$query = "SELECT " . (($type === 'r') ? "SUM(price) " : "COUNT(*) ") .  "FROM reservations WHERE start_date <= '$i' AND end_date > '$i'";
		$result = $conn->query($query);
		
		if (!$result) {
			die("Error connecting to database: " . $conn->error);
		}

		$rows = $result->num_rows;
		
		if ($rows !== 1) {
			die("A serious database error has occurred. Please fix the database.");
		}
		
		$result->data_seek(0);
		$row = $result->fetch_array(MYSQLI_NUM);
		$current += $row[0];
	}
	
	// Calculate totals for next week:
	for ($i = $next_week; $i != $week_after; $i = date('Y-m-d', strtotime("$i +1 day"))) {
		$query = "SELECT " . (($type === 'r') ? "SUM(price) " : "COUNT(*) ") .  "FROM reservations WHERE start_date <= '$i' AND end_date > '$i'";
		$result = $conn->query($query);
		
		if (!$result) {
			die("Error connecting to database: " . $conn->error);
		}

		$rows = $result->num_rows;
		
		if ($rows !== 1) {
			die("A serious database error has occurred. Please fix the database.");
		}
		
		$result->data_seek(0);
		$row = $result->fetch_array(MYSQLI_NUM);
		$next += $row[0];
	}
	
	// Output the results:
	$output .= '[["Week", ';
	$output .= ($type === 'r') ? '"Total Revenue"], ' : '"Total Occupancy"], ';
	$output .= '["Last Week", ' . $last . '], ';
	$output .= '["This Week", ' . $current . '], ';
	$output .= '["Next Week", ' . $next . ']]';
	
}

echo $output;


?>