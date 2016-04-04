<?php

$hostname = "studentdb-maria.gl.umbc.edu";
//$hostname = "localhost";
$database = "jguansi1";
$username = "jguansi1";
$password = "cmsc433";

// Create connection
//$conn = new mysqli($hostname, $username, $password, $database);
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