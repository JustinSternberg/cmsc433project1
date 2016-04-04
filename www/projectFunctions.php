<?php

function studentInfo() {
	// for post http requests, redirect it as a get request
	if($_POST) {
		$_GET = $_POST;
	}
	$returnValue = array("", "", "", "", "", "", "", "");
	// get the data submitted by the form
	if(!empty($_GET["studentID"])) {
		$returnValue[0] = trim(htmlspecialchars(strtoupper($_GET["studentID"])));
		if(!preg_match("/^[A-Z]{2}[0-9]{5}/", $returnValue[0])) {
			// check if the student ID matches the pattern [A-Z]{2}[0-9]{5}
			$returnValue[0] = "";
			$returnValue[7] .= "Invalid student ID</br/>\n";
		}
	}
	if(!empty($_GET["firstname"])) {
		$returnValue[1] = htmlspecialchars($_GET["firstname"]);
		//str_replace("%20", " ", $returnValue[1]);
		if(!preg_match("/^[\w\s\d\.'\-]+$/", $returnValue[1])) {
			// check if the firstname matches the pattern
			$returnValue[1] = "";
			$returnValue[7] .= "Invalid firstname</br/>\n";
		}
	}
	if(!empty($_GET["lastname"])) {
		$returnValue[2] = htmlspecialchars($_GET["lastname"]);
		//str_replace("%20", " ", $returnValue[2]);
		if(!preg_match("/^[\w\s\d\.'\-]+$/", $returnValue[2])) {
			// check if the lastname matches the pattern
			$returnValue[2] = "";
			$returnValue[7] .= "Invalid lastname</br/>\n";
		}
	}
	if(!empty($_GET["email"])) {
		$returnValue[3] = htmlspecialchars($_GET["email"]);
		if(!preg_match("/^[\w\d\-]+\.?[\w\d\-]*@[\w\d\.\-]+\.[\w\d]+/", $returnValue[3])) {
			// check if the email matches the pattern
			$returnValue[3] = "";
			$returnValue[7] .= "Invalid email</br/>\n";
		}
	}
	if(!empty($_GET["phone"])) {
		$returnValue[4] = htmlspecialchars($_GET["phone"]);
		if(!preg_match("/^[0-9]{3}-[0-9]{3}-[0-9]{4}/", $returnValue[4])) {
			// check if the phone matches the pattern
			$returnValue[4] = "";
			$returnValue[7] .= "Invalid phone number</br/>\n";
		}
	}
	if(!empty($_GET["course"])) {
		$returnValue[5] = trim(htmlspecialchars(strtoupper($_GET["course"])));
		//if(!preg_match("/^[A-Za-z]{3,4}[0-9]{3}[A-Za-z0-9]?$/", $returnValue[5])) {
		if(!preg_match("/^([A-Za-z]{3,4}[0-9]{2,3}[A-Za-z]?\s?,?\s?)+$/", $returnValue[5])) {
			// check if the course matches the pattern
			$returnValue[5] = "";
			$returnValue[7] .= "Invalid course ID</br/>\n";
		}
	}
	if(!empty($_GET["pr"])) {
		$returnValue[6] = trim(htmlspecialchars(strtoupper($_GET["pr"])));
		if(!preg_match("/^[0-9]/", $returnValue[6])) {
			// check if the process (pr) matches the pattern
			$returnValue[6] = "";
			$returnValue[7] .= "Invalid process ID</br/>\n";
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
				if(array_key_exists($value1, $takencourses)) {
					// this is an OR statement so as long as 1 value is true, process it
					$allowed = true;
					//echo " found!";
					break;
				}
				else {
					// not a match could have a wildcard or AND statement
					// or just not a match
					if(strpos($value1, "_AND_")) {
						// echo " _AND_ FOUND <br/>";
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
					elseif(strpos($value1, "%")) {
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
	mysql_close($conn);
	return $allowed;
}
function sessionInfo() {
	// studentID, firstname, lastname, email, phone
	$returnValue = array("", "", "", "", "");
	// check if the session's last activity is more than 30 minutes, and then invalidate it
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
	// only create the table if there are elements in it
	if(count($arrayList) > 0) {
		echo "<div><table class=\"courseTable\" >";
		// display each element as a row in the table
		foreach ($arrayList as $value) {
			echo "<tr><td>" . $value[0] . " - " . $value[1] . "</td><td><a href=\"./index.php?course=" . $value[0] . "&pr=3\"><input type=\"button\" class=\"addIt\" value=\"Delete\" title=\"Delete " . $value[0] . " from the list of courses taken.\" /></a></td></tr>\n";
		}
		echo "</table></div>";
	}
}
function curl($url) {
	// Defining the basic cURL function
	$ch = curl_init();  // Initialising cURL
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_URL, $url);    // Setting cURL's URL option with the $url variable passed into the function
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // Setting cURL's option to return the webpage data
	$data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
	curl_close($ch);    // Closing cURL
	return $data;   // Returning the data from the function
}
function scrapeStudentInfo($studentID) {
	// firstname, lastname, email
	$returnValue = array();
	// get the scraped UMBC directory webpage
	$scraped_website = curl("http://www.umbc.edu/search/directory/?search=" . $studentID);
	// placeholders for specific student info
	$placeholder1 = "<div class=\"name\" itemprop=\"name\">";
	$placeholder2 = "</div>";
	$placeholder3 = "<a itemprop=\"email\" href=\"mailto:";
	$placeholder4 = "\">";
	$index1 = strpos($scraped_website, $placeholder1);
	// a record is found matching the studentID
	if($index1 > 0) {
		$index1 += strlen($placeholder1);
		// get the end index of the student's name
		$index2 = strpos($scraped_website, $placeholder2, $index1);
		// substring the student's name from the scraped webpage
		$temp = substr($scraped_website, $index1, $index2 - $index1);
		// set firstname from the beginning of the string up to the last space
		array_push($returnValue, substr($temp, 0, strrpos($temp, " ")));
		// set the lastname from the index of the last space to the end
		array_push($returnValue, substr($temp, strrpos($temp, " ") + 1));
		// matching email address
		$index3 = strpos($scraped_website, $placeholder3, $index1);
		$index3 += strlen($placeholder3);
		// get the end index of the email
		$index4 = strpos($scraped_website, $placeholder4, $index3);
		// set the email address from the substring of the scraped webpage
		array_push($returnValue, substr($scraped_website, $index3, $index4 - $index3));
		// echo $returnValue[0] . "," . $returnValue[1] . "," . $returnValue[2];
	}
	return $returnValue;
}
function exists($course, $arrayList) {
	// simplifying array_key_exists to check for both normal course, honor versions, and Y version
	if(array_key_exists($course, $arrayList) || array_key_exists($course."H", $arrayList) || array_key_exists($course."Y", $arrayList)) {
		// the key is in the array
		return true;
	}
	else {
		// key does not exist in the array
		return false;
	}
}
function getCourseSchedule($courseID) {
	// firstname, lastname, email
	$returnValue = array();
	$url = "https://highpoint-prd.ps.umbc.edu/app/catalog/coursesections/UMBC1/" . $courseID . "/1/2168/";
	$scraped_website = curl($url);
	// placeholders for specific student info
	$placeholder1 = "<div class=\"strong section-body\">";
	$placeholder2 = "</div>";
	$placeholder3 = "<div class=\"section-body\"  >";
	while(true) {
		$index1 = strpos($scraped_website, $placeholder1, $index1);
		if($index1 <= 0) {
			break;
		}
		// a course section is found
		$index1 += strlen($placeholder1);
		$index2 = strpos($scraped_website, $placeholder2, $index1);
		// substring the course section
		$current_section = array();
		// add the course section
		$section_name = substr($scraped_website, $index1, $index2 - $index1);
		array_push($current_section, $section_name);
		// do this 5 times to get the rest of the course section info
		// session type, day/time, room, instructor, and status
		for($i = 0; $i < 5; $i++) {
			// get the beginning index of the data
			$index3 = strpos($scraped_website, $placeholder3, $index1);
			$index3 += strlen($placeholder3);
			// get the end index of the data
			$index4 = strpos($scraped_website, $placeholder2, $index3);
			// add the section's data to the array
			array_push($current_section, substr($scraped_website, $index3, $index4 - $index3));
			$index1 = $index4;
		}
		// add an array in the main returnValue with an entry using section name as the key
		$returnValue[ $section_name ] = $current_section; 
	}
	// return the array containing all the sections
	return $returnValue;
}
?>