<?php
// allow cross domain access for XMLHttpReuquest
// the JSON output from accessing the database
header('Access-Control-Allow-Origin: *');
// connect to the database
require "dbConnection.php";
// get the course prefix to search the database
$course = "";
if(!empty($_GET["course"])) {
	$course = trim(strtoupper($_GET["course"]));
}
// format the output as an array that JavaScript can parse
echo "[";
// Course prefix must be at least 1 character
if (strlen($course) > 0 ) {
	$sql = "SELECT course, coursename, description, requiredtext FROM courses WHERE course like '" . $course . "%' ORDER BY course ASC LIMIT 12 ";
	$result = mysql_query($sql, $conn);
	// get the results from the query
	$resultCount = mysql_num_rows($result);
	if ($resultCount > 0) {
		// output data of each row
		while($row = mysql_fetch_assoc($result)) {
			echo "[\"" . $row["course"] . "\",\"" . $row["coursename"] . "\",\"" . trim(preg_replace("/[\"]/","'", $row["description"])) . "\",\"" . trim(preg_replace("/[\"]/","'", $row["requiredtext"])) . "\"]";
			$resultCount--;
			if($resultCount > 0) {
				echo ",";
			}
		}
	}
}
echo "]";
// close the connection
mysql_close($conn);
?>