<?php

$hostname = "studentdb-maria.gl.umbc.edu";
$database = "database";
$username = "username";
$password = "password";

// Create connection
$conn = mysql_connect($hostname, $username, $password, true);
if(!$conn) {
	echo "Could not connect to the database";
	exit;
}
if(!mysql_select_db($database, $conn)) {
	echo "Could not select database";
	exit;
}

?>