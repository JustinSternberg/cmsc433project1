<?php

function studentInfo() {
	// for post http requests, redirect it as a get request
	if($_POST) {
		$_GET = $_POST;
	}
	$returnValue = array("", "", "", "", "", "", "", "", "");
	// get the data submitted by the form
	if(!empty($_GET["studentID"])) {
		$returnValue[0] = trim(strtoupper($_GET["studentID"]));
		if(!preg_match("/^[A-Z]{2}[0-9]{5}/", $returnValue[0])) {
			// check if the student ID matches the pattern [A-Z]{2}[0-9]{5}
			$returnValue[0] = "";
			$returnValue[8] .= "Invalid student ID</br/>\n";
		}
	}
	if(!empty($_GET["firstname"])) {
		$returnValue[1] = $_GET["firstname"];
		//str_replace("%20", " ", $returnValue[1]);
		if(!preg_match("/^[\w\s\d\.'\-]+$/", $returnValue[1])) {
			// check if the firstname matches the pattern
			$returnValue[1] = "";
			$returnValue[8] .= "Invalid firstname</br/>\n";
		}
	}
	if(!empty($_GET["lastname"])) {
		$returnValue[2] = $_GET["lastname"];
		//str_replace("%20", " ", $returnValue[2]);
		if(!preg_match("/^[\w\s\d\.'\-]+$/", $returnValue[2])) {
			// check if the lastname matches the pattern
			$returnValue[2] = "";
			$returnValue[8] .= "Invalid lastname</br/>\n";
		}
	}
	if(!empty($_GET["email"])) {
		$returnValue[3] = $_GET["email"];
		if(!preg_match("/^[\w\d\-]+\.?[\w\d\-]*@[\w\d\.\-]+\.[\w\d]+/", $returnValue[3])) {
			// check if the email matches the pattern
			$returnValue[3] = "";
			$returnValue[8] .= "Invalid email</br/>\n";
		}
	}
	if(!empty($_GET["phone"])) {
		$returnValue[4] = $_GET["phone"];
		if(!preg_match("/^[0-9]{3}-[0-9]{3}-[0-9]{4}/", $returnValue[4])) {
			// check if the phone matches the pattern
			$returnValue[4] = "";
			$returnValue[8] .= "Invalid phone number</br/>\n";
		}
	}
	if(!empty($_GET["course"])) {
		$returnValue[5] = trim(strtoupper($_GET["course"]));
		if(!preg_match("/^[A-Za-z]{3,4}[0-9]{3}[A-Za-z0-9]?$/", $returnValue[5])) {
			// check if the course matches the pattern
			$returnValue[5] = "";
			$returnValue[8] .= "Invalid course ID</br/>\n";
		}
	}
	if(!empty($_GET["pr"])) {
		$returnValue[6] = trim(strtoupper($_GET["pr"]));
		if(!preg_match("/^[0-9]/", $returnValue[6])) {
			// check if the process (pr) matches the pattern
			$returnValue[6] = "";
			$returnValue[8] .= "Invalid process ID</br/>\n";
		}
	}
	if(!empty($_GET["massCourse"])) {
		$returnValue[7] = trim(strtoupper($_GET["course"]));
		if(!preg_match("/^([A-Za-z]{3,4}[0-9]{3}[A-Za-z0-9]?\s?,?\s?)+$/", $returnValue[7])) {
			// check if the course matches the pattern
			$returnValue[7] = "";
		}
	}
	return $returnValue;
}
function compareRequirements($course, $takencourses, $studentID) {
	require "dbConnection.php";
	$required = array();
	$allowed = true;
	// get the requirements for the course
	$sql1 = "SELECT required FROM requirement WHERE course like \"" . $course . "\"";
	$result1 = mysql_query($sql1);
	// get the results from the query
	if (mysql_num_rows($result1) > 0) {
		// save the query data to the variable
		// each iteration is considered an AND statement
		while($row = mysql_fetch_assoc($result1)) {
			// the previous requirement failed - exit out of the loop
			if($allowed == false) {
				// if the previous iteration already yielded a false
				// then there is no point checking the rest of the AND statement
				//echo "False - exit out of loop";
				return false;
				break;
			}
			$tempString = $row["required"];
			// split the strings - using a comma as delimeter
			$required = explode(",", $tempString);
			// loop through each OR statement
			foreach ($required as $value1) {
				//echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $value1;
				// check if the listed required courses are met
				if(in_array($value1, $takencourses)) {
					// this is an OR statement so as long as 1 value is true, process it
					$allowed = true;
					//echo " found!";
					break;
				}
				else {
					// not a match could have a wildcard or AND statement
					// or just not a match
					if(strpos("_AND_", $value1)) {
						echo " _AND_ FOUND <br/>";
						// there is an AND statement that needs to be tested
						$tempArray = explode("_AND_", $value1);
						foreach ($tempArray as $value2) {
							// check the the course is not in the taken courses list
							if(in_array($value2, $takencourses)) {
								$allowed = true;
							}
							else {
								// exit out of the loop since this is an AND statement
								$allowed = false;
								break;
							}
						}
					}
					elseif(strpos($value1, '%')) {
						// there is a wildcard used - check the courses taken if there is a match
						// check the takencourses table if there is a match
						//echo "Validating a course with a wildcard: " . $value1 . "<br/>";
						$sql2 = "SELECT course FROM takencourses WHERE studentid = \"" . $studentID . "\" AND course like \"" . $value1 . "%\" ORDER BY course ASC";
						//$result2 = $conn->query($sql2);
						$result2 = mysql_query($sql2);
						if (mysql_num_rows($result2) > 0) {
							//echo "There are records found<br/>";
							$allowed = true;
							break;
						}
						else {
							$allowed = false;
						}
					}
					else {
						// not a match
						$allowed = false;
					}
					$allowed = false;
				}
			}
		}
	}
	// close the connection
	//$conn->close();
	mysql_close($conn);
	return $allowed;
}
function sessionInfo() {
	// studentID, firstname, lastname, email, phone
	$returnValue = array("", "", "", "", "");
	// check if the session is more than 30 minutes
	if(time() - $_SESSION["LAST_ACTIVITY"] > 1800) {
		session_unset();
		session_destroy();
	}
	// update last activity timestamp
	//$_SESSION["LAST_ACTIVITY"] = time();
	// check if the student info is stored in the session
	if(isset($_SESSION["studentID"])) {
		$returnValue[0] = $_SESSION["studentID"];
		$returnValue[1] = $_SESSION["firstname"];
		$returnValue[2] = $_SESSION["lastname"];
		$returnValue[3] = $_SESSION["email"];
		$returnValue[4] = $_SESSION["phone"];
	}
	// return the values
	return $returnValue;
}
function displayCourses($arrayList) {
	echo "<div><table class=\"courseTable\" >";
	foreach ($arrayList as $value) {
		echo "<tr><td width=\"100\">TAKEN</td><td width=\"145\">" . $value . "</td><td><a href=\"./index.php?course=" . $value . "&pr=3\"><input type=\"button\" class=\"addIt\" value=\"REMOVE\" /></a></td></tr>\n";
	}
	echo "</table></div>";
}
?>