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
		}
	}
	if(!empty($_GET["firstname"])) {
		$returnValue[1] = $_GET["firstname"];
		str_replace("%20", " ", $returnValue[1]);
		if(!preg_match("/^[\w][\w\d\s\.\-']*/", $returnValue[1])) {
			// check if the firstname matches the pattern
			$returnValue[1] = "";
		}
	}
	if(!empty($_GET["lastname"])) {
		$returnValue[2] = $_GET["lastname"];
		str_replace("%20", " ", $returnValue[2]);
		if(!preg_match("/^[\w][\w\d\s\.\-']*/", $returnValue[2])) {
			// check if the lastname matches the pattern
			$returnValue[2] = "";
		}
	}
	if(!empty($_GET["email"])) {
		$returnValue[3] = $_GET["email"];
		if(!preg_match("/^[\w\d\-]+\.?[\w\d\-]*@[\w\d\.\-]+\.[\w\d]+/", $returnValue[3])) {
			// check if the email matches the pattern
			$returnValue[3] = "";
		}
	}
	if(!empty($_GET["phone"])) {
		$returnValue[4] = $_GET["phone"];
		if(!preg_match("/^[0-9]{3}-[0-9]{3}-[0-9]{4}/", $returnValue[4])) {
			// check if the phone matches the pattern
			$returnValue[4] = "";
		}
	}
	if(!empty($_GET["courseSearch"])) {
		$returnValue[5] = trim(strtoupper($_GET["courseSearch"]));
		if(!preg_match("/^[A-Za-z0-9]*$/", $returnValue[5])) {
			// check if the courseSearch matches the pattern
			$returnValue[5] = "";
		}
	}
	if(!empty($_GET["course"])) {
		$returnValue[6] = trim(strtoupper($_GET["course"]));
		if(!preg_match("/^[A-Za-z]{3,4}[0-9]{3}[A-Za-z0-9]?$/", $returnValue[6])) {
			// check if the course matches the pattern
			$returnValue[6] = "";
		}
	}
	if(!empty($_GET["pr"])) {
		$returnValue[7] = trim(strtoupper($_GET["pr"]));
		if(!preg_match("/^[0-9]/", $returnValue[7])) {
			// check if the process (pr) matches the pattern
			$returnValue[7] = "";
		}
	}
	if(!empty($_GET["sessionID"])) {
		$returnValue[8] = $_GET["sessionID"];
		if(!preg_match("/^[A-Za-z0-9]*$/", $returnValue[8])) {
			// check if the firstname matches the pattern
			$returnValue[8] = "";
		}
	}
	return $returnValue;
}
function compareRequirements($course, $takencourses, $studentID) {
	require "dbConnection.php";
	$required = array();
	$allowed = true;
	//echo "<br/>Validating: " . $course;
	$sql1 = "SELECT required FROM requirement WHERE course like \"" . $course . "\"";
	//$result1 = $conn->query($sql1);
	$result1 = mysql_query($sql1);
	// get the results from the query
	if (mysql_num_rows($result1) > 0) {
		// save the query data to the variable
		// each iteration is considered an AND statement
		while($row = mysql_fetch_assoc($result1)) {
			//echo "<br/>";
			if($allowed == false) {
				// if the previous iteration already yielded a false
				// then there is no point checking the rest of the AND statement
				//echo "False - exit out of loop";
				return false;
				break 2;
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
					// not a match coulde have a wildcard, and statement
					// or just not a match
					if(strpos("_AND_", $value1)) {
						echo " _AND_ FOUND <br/>";
						// there is an AND statement that needs to be tested
						$tempArray = explode("_AND_", $value1);
						foreach ($tempArray as $value2) {
							// check the the course is not in the taken courses list
							if(in_array($value2, $takencourses)) {
								$allowed = true;
								// exit out of the loop since this is an AND statement
							}
							else {
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

?>