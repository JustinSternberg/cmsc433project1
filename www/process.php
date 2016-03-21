<?php

// connect to the database
require "dbConnection.php";
// get functions
require "projectFunctions.php";

// retrieve the get/post HTTP method requests
$tempArray = studentInfo();
$studentID = $tempArray[0];
$firstname = $tempArray[1];
$lastname = $tempArray[2];
$email = $tempArray[3];
$phone = $tempArray[4];
$courseSearch = $tempArray[5];
$course = $tempArray[6];
$pr = $tempArray[7];

$worksheetCounter = array(0,0,0,0,0,0,0);
$worksheetCounterNames = array( array(), array(), array(), array(), array(), array(), array() );
// for displaying error messages
$errorMessage = "";
$validProcess = false;
// list of all courses the user have taken
$takencourses = array();
// this is the Computer Science Major worksheet - list of what needs to be taken
$csmcworksheet = array();
// this array will be populated by courses the user already have prerequisites/recommended to take
$recommended = array();

// enter the student's information to the database
if($pr == "2") {
	// trying to decide whether to make phone optional or not
	if ($studentID != "" && $firstname != "" && $lastname != "" && $email != "") {
		// insert the student information to the database if the data passed is valid
		$sql1 = "INSERT into students (studentid, firstname, lastname, email, phone) values ( \"" . $studentID . "\", \"" . $firstname . "\", \"" . $lastname . "\", \"" . $email . "\", \"" . $phone . "\" )";
		// $conn->query($sql1);
		mysql_query($sql1, $conn);		
		$validProcess = true;
	}
}
// compare the student ID with the database
if($studentID != "") {
	$sql2 = "SELECT studentid, firstname, lastname, email, phone FROM students WHERE studentid = \"" . $studentID . "\" LIMIT 1";
	//$result2 = $conn->query($sql2);
	$result2 = mysql_query($sql2, $conn);
	// get the results from the query
	if (mysql_num_rows($result2) > 0) {
		// save the query data to the variables
		while($row = mysql_fetch_assoc($result2)) {
			$studentID = $row["studentid"];
			$firstname = $row["firstname"];
			$lastname = $row["lastname"];
			$email = $row["email"];
			$phone = $row["phone"];
		}
		$validProcess = true;
	}
}
if($validProcess == true) {
	// query the list of all the related courses for a computer science major
	$sql3 = "SELECT course, priority FROM cmscworksheet ORDER BY course ASC";
	//$result3 = $conn->query($sql3);
	$result3 = mysql_query($sql3, $conn);
	// get all the query results
	if (mysql_num_rows($result3) > 0) {
		while($row = mysql_fetch_assoc($result3)) {
			// save it in the csmcworksheet array
			array_push($csmcworksheet, array($row["course"], $row["priority"]));
		}
	}
	// query all the courses already taken by the user
	$sql4 =  "SELECT course FROM takencourses WHERE studentid = \"" . $studentID . "\" ORDER BY course ASC";
	//$result4 = $conn->query($sql4);
	$result4 = mysql_query($sql4, $conn);
	if (mysql_num_rows($result4) > 0) {
		while($row = mysql_fetch_assoc($result4)) {
			array_push($takencourses, $row["course"]);
		}
	}
	if($pr == "0" || $pr == "1") {
		// retrieve student information from the database
		$sql2 = "SELECT studentid, firstname, lastname, email FROM students WHERE studentid = \"" . $studentID . "\" LIMIT 1";
		//$result2 = $conn->query($sql2);
		$result2 = mysql_query($sql2, $conn);
		// get the results from the query
		if (mysql_num_rows($result2) > 0) {
			// save the query data to the variables
			while($row = mysql_fetch_assoc($result2)) {
				$studentID = $row["studentid"];
				$firstname = $row["firstname"];
				$lastname = $row["lastname"];
				$email = $row["email"];
			}
			$validProcess = true;
		}
		// time to add the Course to the student's academic progress
		if($validProcess == true && $course != "" && ($pr == "0" || $pr == "1")) {
			// first check if the Course is in the database of classes offered - - this will handle courses with H/Y/L/etc. postfixes
			$sql5 = "SELECT course FROM courses WHERE course = \"" . $course . "\" ORDER BY course ASC LIMIT 1";
			//$result5 = $conn->query($sql5);
			$result5 = mysql_query($sql5, $conn);
			// the course submitted is in the database and is valid
			if (mysql_num_rows($result5) > 0) {
				// Check if the course that the student is trying to add is already in the course taken list
				if(in_array($course, $takencourses)) {
					$errorMessage = "The course " . $course . " is already in the list of taken courses.";
				}
				else {
					// Check if the student meets the requirements for the course
					if(compareRequirements($course, $takencourses, $studentID)) {
						// everything checks out, and you can add the course to the list
						$sql6 = "INSERT INTO takencourses (studentid, course) VALUES (\"" . $studentID . "\", \"" . $course . "\")";
						//$conn->query($sql6);
						mysql_query($sql6, $conn);	
						array_push($takencourses, $course);
						$errorMessage = "The course " . $course . " has been added to the list.";
					}
					else {
						$errorMessage = "You do not meet the requirements for " . $course . ".";
						$sql7 = "SELECT required FROM courses where course = \"" . $course . "\"";
						$result7 = mysql_query($sql7, $conn);
						if (mysql_num_rows($result7) > 0) {
							$row2 = mysql_fetch_assoc($result7);
							$errorMessage = $errorMessage . "<br/>" . $row2["required"];
						}
					}
				}
			}
			else {
				// error message from checking course against the database table courses
				$errorMessage = "The course " . $course . " is not in the database.";
			}
		}
	}
	// deleteing taken corue from the db
	if (pr == "3") {
		//$sql3 = "delete from takencourses where studentid = \"" .$studentID . "\" AND course = \"" . $course . "\" ";
		//$affectedRecords = mysql_query($sql3, $conn);
		//// if there were records deleted from the takencourses table
		//if($affectedRecords > 0) {
		//	
		//}
		//$deleteArray = array();
		//array_push($deleteArray, $course);
		//while(len($deleteArray) > 0) {
		//	
		//}
	}
}

if($validProcess == false) {
	// close the connection
	//$conn->close();
	mysql_close($conn);
	// redirect back to the login page
	header("Location: index.php?studentID=" . $studentID . "&firstname=" . $firstname . "&lastname=" . $lastname . "&email=" . $email . "&phone=" . $phone . "&&errorMessage=");
	die();
}
else {
	// get the counter for each CMSC worksheet priority category
	foreach ($csmcworksheet as $value3 ) {
		// check if the specific course or the honor version of the courses in CMSC worksheet are already taken
		if(in_array($value3[0], $takencourses) || in_array($value3[0]."H", $takencourses)) {
			$priority = $value3[1];
			// if the 2 computer electives are already satisfied, it can count as a technical elective
			if($priority == 5 && $worksheetCounter[5] >= 2) {
				$priority = 6;
			}
			// if the technical electives are already satisfied, then just skip it and continue with the loop
			if($priority == 6 && $worksheetCounter[6] >= 3) {
				continue;
			}
			$worksheetCounter[$priority]++;
			array_push($worksheetCounterNames[$priority], $value3[0]);
		}
	}
	// create a list of recommended courses to take
	foreach ($csmcworksheet as $value) {
		if(in_array($value[0], $takencourses)) {
			continue;
		}
		else {
			// check if the user meets the requirements for each course in the cmscworksheet
			if(compareRequirements($value[0], $takencourses, $studentID)) {
				// check priority 1 requirements
				if(($value[0] == "CMSC345" || $value[0] == "CMSC447") && (in_array("CMSC345", $takencourses) || in_array("CMSC447", $takencourses))) {
					// either 345 ot 447, don't need the other if you already taken the other option
					continue;
				}
				if($value[1] == 6 && $worksheetCounter[6] >= 3) {
					// check if the technical electives requirement is satisfied, and if so skip recommending priority 6 courses
					continue;
				}
				// check science requirements
				if($value[1] == 4) {
					if($worksheetCounter[4] >= 4) {
						continue;
					}
					if(!preg_match("/^[A-Z]{4}[0-9]{3}/", $value[0]) && $worksheetCounter[4] == 3 && !in_array("/^[A-Z]{3,4}[0-9]{3}L/", $takencourses) ) {
						continue;
					}
				}
				array_push($recommended, array($value[0], $value[1]));
			}
		}
	}
	// close the connection
	//$conn->close();
	mysql_close($conn);
}

?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="styles.css" >
	<link rel="icon" type="image/png" href="./icon.png" />
	<title>CMSC433: Scripting Languages - Project 1</title>
	<script src="getCourses.php?studentID=<?php echo $studentID; ?>"></script>
</head>
<body>
	<div  id="wrapper" class="wrapper" >
	<div class="headerStripe" ></div>
		<div class="headercss" id="headercss">
			<div class="headernav" >
			<table> 
			<tr>
				<td>
					<img src="./retrievers.jpg" height="150" />
				</td>
				<td>
					<h2>
					UMBC ID:&nbsp;<?php echo $studentID; ?><br/>
					Student Name: <?php echo $firstname . " " . $lastname; ?><br/>
					Email: <?php echo $email; ?><br/>
					<div class="dropdown">
						Search:<input type="text" id="courseSearch" name="courseSearch" class="searchBar" title="Search Course ID to add" autocomplete="off" maxlength="8" size="40" placeholder="Search Course ID to add" autofocus />
						<div id="courseResult" class="dropdown-content">
						</div>
					</div>
					<script>
						document.querySelector("#courseSearch").addEventListener("keyup", getCourses);
						document.querySelector("#courseSearch").addEventListener("click", getCourses);
						// when the user clicks elsewhere on the page, hide the dropdown menu
						window.onclick = function(event) {
							if (!event.target.matches('.courseSearch')) {
								document.getElementById("courseResult").classList.remove("show");
							}
						}
					</script>
					</h2>
				</td>
			</tr>
			</table>
			</div>
			
		</div>
		
		<div class="mainBody" id="mainBody">
			<br/><br/><br/><br/><br/><br/><br/><br/><br/>
			<div class="sidebar">
				<div class="recommendedLabel">RECOMMENDED COURSES</div>
				<?php
				foreach ($recommended as $val1) {?>
				<div class="divButton" name="<?php echo $val1[0]; ?>" id="<?php echo $val1[0]; ?>" ><?php echo $val1[0]; ?></div>
				<div class="divInfo" id="div<?php echo $val1[0]; ?>" ></div>
				<script>document.querySelector("#<?php echo $val1[0]; ?>").addEventListener("click", display);</script>
				
				<?php
				}
				?>
			</div>
			<div class="studentDetails">
				
				<div class="errorMessage"><?php echo $errorMessage . "<br/>"; ?></div>
				
				Computer Science Major Requirements <br/><br/>
				Required Computer Science Courses<br/>
				<div class="progress">
					<span class="percent"><?php echo round(($worksheetCounter[1] / 11) * 100, 2) . "%"; ?></span>
					<div class="bar" style="width: <?php echo round(($worksheetCounter[1] / 11) * 100, 2) . "%"; ?>;" ></div>
				</div>
				<br/>
				Required Math Courses<br/>
				<div class="progress">
					<span class="percent"><?php echo round(($worksheetCounter[2] / 3) * 100, 2) . "%"; ?></span>
					<div class="bar" style="width: <?php echo round(($worksheetCounter[2] / 3) * 100, 2) . "%"; ?>;" ></div>
				</div>
				<br/>
				Required Statistics Course<br/>
				<div class="progress">
					<span class="percent"><?php echo round(($worksheetCounter[3] / 1) * 100, 2) . "%"; ?></span>
					<div class="bar" style="width: <?php echo round(($worksheetCounter[3] / 1) * 100, 2) . "%"; ?>;" ></div>
				</div>
				<br/>
				Required Science Courses<br/>
				<div class="progress">
					<span class="percent"><?php echo round(($worksheetCounter[4] / 4) * 100, 2) . "%"; ?></span>
					<div class="bar" style="width: <?php echo round(($worksheetCounter[4] / 4) * 100, 2) . "%"; ?>;" ></div>
				</div>
				<br/>
				Two Computer Science Elective Courses<br/>
				<div class="progress">
					<span class="percent"><?php echo round(($worksheetCounter[5] / 2) * 100, 2) . "%"; ?></span>
					<div class="bar" style="width: <?php echo round(($worksheetCounter[5] / 2) * 100, 2) . "%"; ?>;" ></div>
				</div>
				<br/>
				Three Technical Elective Courses<br/>
				<div class="progress">
					<span class="percent"><?php echo round(($worksheetCounter[6] / 3) * 100, 2) . "%"; ?></span>
					<div class="bar" style="width: <?php echo round(($worksheetCounter[6] / 3) * 100, 2) . "%"; ?>;" ></div>
				</div>
				<br/>
				
				<br/>
				Taken Courses:<br/>
				<?php
				echo "S size: " . count($takencourses) . "<br/>";
				foreach ($takencourses as $val1) {
					echo $val1 . "<br/>";
				}
				?>
			
			</div>
			
		
		

		</div>
	</div>
</body>
</html>
