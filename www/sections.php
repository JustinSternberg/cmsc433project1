<?php
session_start();
require "projectFunctions.php"; // get functions
header('Content-Type: application/json'); // output type is json format

// begin - need this to to convert JSON to Javascript array
echo "[";

// only allow access if the studentID session variable is set -- logged in
if(isset($_SESSION["studentID"]) && $_SESSION["studentID"] != "") {
	if($_POST) {
		$_GET = $_POST; // will work with either get or set methods
	}
	$courseID = "";
	if(!empty($_GET["courseID"])) {
		$courseID = trim(htmlspecialchars($_GET["courseID"]));
		if(!preg_match("/^[0-9]{6}$/", $courseID)) {
		// check if the courseID is a valid  6 digit value - ZEROFILLED
			$courseID = "";
		}
	}
	// valid UMBC courseID 
	if($courseID != "") {
		$sections = getCourseSchedule($courseID);
		$counter = 1;
		// loop through all the sections
		foreach($sections as $section) {
			echo "\n[";
			$toSix = 1;
			// course section, session type, day/time, room, instructor, and status
			foreach($section as $info) {
				echo "\"" . $info . "\"";
				// add a comma if there is a next info about the current section
				if($toSix < 6) {
					echo ",";
				}
				$toSix++;
			}
			echo "]";
			// add a comma if there is a next section
			if($counter < count($sections)) {
				echo ",";
			}
			$counter++;
		}
	}
}
// ending bracket for JSON to javascript format
echo "\n]";
?>