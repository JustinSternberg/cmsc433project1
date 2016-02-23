<?php

$hostname = "localhost";
$database = "cmsc433";
$username = "jguansi1";
$password = "cmsc433";

// Create connection
$conn = new mysqli($hostname, $username, $password, $database);
// Check connection
if ($conn->connect_error) {
    // die("Connection failed: " . $conn->connect_error);
}

?>