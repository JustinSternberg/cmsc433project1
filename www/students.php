<?php
header('Content-Type: application/json');
// connect to the database
require "dbConnection.php";
// get the functions
require "projectFunctions.php";
// get the student id
$studentID = "";
if(!empty($_GET["studentID"])) {
	$studentID = trim(strtoupper($_GET["studentID"]));
}
// format the output as an array that JavaScript can parse
echo "[";
if (strlen($studentID) == 7) {
	$sql = "SELECT studentid, firstname, lastname, email, phone FROM students WHERE studentid = '" . $studentID . "' ";
	$result = mysql_query($sql);
	// if the studentID is in the database
	if (mysql_num_rows($result) > 0) {
		// output data of each row
		while($row = mysql_fetch_assoc($result)) {
			echo "\"" . $row["studentid"] . "\",\"" . $row["firstname"] . "\",\"" . $row["lastname"] . "\",\"" . $row["email"] . "\",\"" . $row["phone"] . "\"";
		}
	}
	else {
		// if the studentID is not in the database, then search the UMBC directory
		$info = scrapeStudentInfo($studentID);
		// must be exactly 3 elements: firstname, lastname and email
		if(count($info) == 3) {
			echo "\"" . $studentID . "\",\"" . $info[0] . "\",\"" . $info[1] . "\",\"" . $info[2] . "\",\"\"";
		}
	}
}
echo "]";
// close the connection
mysql_close($conn);
?>