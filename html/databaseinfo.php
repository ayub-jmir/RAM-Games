<?php

	// For accessing database:
	$db = "aftaba_ant";
	$hn = "localhost";
	$un = "aftaba_root";
	$pw = "Ayub321";
	
	// For salting passwords:
	$pre_salt = '@$%!';
	$post_salt = '&5h?*';
	
	// To establish the connection on each page
//	$conn = new mysqli($hn, $un, $pw, $db);
$conn = mysqli_connect("$hn", "$un", "$pw", "$db");
	if ($conn->connect_error) {
		die($conn->connect_error);
	}
	
	// A function that fully sanitizes and hashes a password input
	/*
	function sanitize($password) {
		echo $password;
		$hashed_password = hash('ripemd128', "$pre_salt$password$post_salt");
		return $conn->real_escape_string($hashed_password);
	} */
/*
-- To log into database:
--Database:	poelvoo_fp
--Host:	localhost
--Username:	poelvoo_fp
--Password:	mypassword
*/

?>
