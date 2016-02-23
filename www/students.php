<?php
// allow cross domain access for XMLHttpReuquest
// the JSON output from accessing the database
header('Access-Control-Allow-Origin: *');
// connect to the database
require "dbConnection.php";
// get the student id
$studentid = "";
if(!empty($_GET["studentid"])) {
	$studentid = trim(strtoupper($_GET["studentid"]));
}
// format the output as an array that JavaScript can parse
echo "[";
if (strlen($studentid) == 7) {
	$sql = "SELECT studentid, firstname, lastname, email, phone FROM students WHERE studentid = '" . $studentid . "' ";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			echo "\"" . $row["studentid"] . "\",\"" . $row["firstname"] . "\",\"" . $row["lastname"] . "\",\"" . $row["email"] . "\",\"" . $row["phone"] . "\"";
		}
	}
}
echo "]";
// close the connection
$conn->close();
?>