<?php
session_start();
header('Content-Type: application/json');

// begin - need this to to convert JSON to Javascript array
echo "[";

// only allow access if the studentID session variable is set -- logged in
if(isset($_SESSION["studentID"]) && $_SESSION["studentID"] != "") {
	if($_POST) {
		$_GET = $_POST; // will work with either get or set methods
	}
	require "dbConnection.php"; // connect to the database
	// get the course prefix to search the database
	$course = "";
	if(!empty($_GET["course"])) {
		$course = trim(strtoupper($_GET["course"]));
	}
	// Course prefix must be at least 1 character
	if (strlen($course) > 0 ) {
		$sql = "SELECT course, coursename, description, requiredtext FROM courses WHERE course like '" . $course . "%' ORDER BY course ASC LIMIT 12 ";
		$result = mysql_query($sql, $conn);
		// get the results from the query
		$resultCount = mysql_num_rows($result);
		if ($resultCount > 0) {
			// output data of each row
			while($row = mysql_fetch_assoc($result)) {
				echo "\n\n[\"" . $row["course"] . "\",\"" . $row["coursename"] . "\",\"" . trim(preg_replace("/[\"]/","'", $row["description"])) . "\",\"" . trim(preg_replace("/[\"]/","'", $row["requiredtext"])) . "\"]";
				$resultCount--;
				if($resultCount > 0) {
					echo ",";
				}
			}
		}
	}
	// close the connection
	mysql_close($conn);
}
// ending bracket for JSON to javascript format
echo "\n\n]";

?>