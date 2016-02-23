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
		$sql1 = "INSERT ignore into students set studentid = \"" . $studentID . "\", firstname = \"" . $firstname . "\", lastname = \"" . $lastname . "\", email = \"" . $email . "\", phone = \"" . $phone . "\"";
		$conn->query($sql1);	
		$validProcess = true;
	}
}
// compare the student ID with the database
if($studentID != "") {
	$sql2 = "SELECT studentid, firstname, lastname, email, phone FROM students WHERE studentid = \"" . $studentID . "\" LIMIT 1";
	$result2 = $conn->query($sql2);
	// get the results from the query
	if ($result2->num_rows > 0) {
		// save the query data to the variables
		while($row = $result2->fetch_assoc()) {
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
	$result3 = $conn->query($sql3);
	// get all the query results
	if ($result3->num_rows > 0) {
		while($row = $result3->fetch_assoc()) {
			// save it in the csmcworksheet array
			array_push($csmcworksheet, array($row["course"], $row["priority"]));
		}
	}
	// query all the courses already taken by the user
	$sql4 =  "SELECT course FROM takencourses WHERE studentid = \"" . $studentID . "\" ORDER BY course ASC";
	$result4 = $conn->query($sql4);
	if ($result4->num_rows > 0) {
		while($row = $result4->fetch_assoc()) {
			array_push($takencourses, $row["course"]);
		}
	}
	if($pr == "0" || $pr == "1") {
		// retrieve student information from the database
		$sql2 = "SELECT studentid, firstname, lastname, email FROM students WHERE studentid = \"" . $studentID . "\" LIMIT 1";
		$result2 = $conn->query($sql2);
		// get the results from the query
		if ($result2->num_rows > 0) {
			// save the query data to the variables
			while($row = $result2->fetch_assoc()) {
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
			$result5 = $conn->query($sql5);
			// the course submitted is in the database and is valid
			if ($result5->num_rows > 0) {
				// Check if the course that the student is trying to add is already in the course taken list
				if(in_array($course, $takencourses)) {
					$errorMessage = "The course selected is already in the taken courses list.";
				}
				else {
					// Check if the student meets the requirements for the course
					if(compareRequirements($course, $takencourses, $studentID)) {
						// everything checks out, and you can add the course to the list
						$sql6 = "INSERT INTO takencourses (studentid, course) VALUES (\"" . $studentID . "\", \"" . $course . "\")";
						$conn->query($sql6);
						array_push($takencourses, $course);
					}
					else {
						$errorMessage = "You do not meet the requirements for " . $course;
					}
				}
			}
			else {
				// error message from checking course against the database table courses
				$errorMessage = "Course selected is not in the database.";
			}
		}
	}
}

if($validProcess == false) {
	// close the connection
	$conn->close();
	// redirect back to the login page
	header("Location: index.php?studentID=" . $studentID . "&firstname=" . $firstname . "&lastname=" . $lastname . "&email=" . $email . "&phone=" . $phone . "&&errorMessage=");
	die();
}
else {
	foreach ($csmcworksheet as $value) {
		if(in_array($value[0], $takencourses)) {
			continue;
		}
		else {
			if(compareRequirements($value[0], $takencourses, $studentID)) {
				// check science requirements
				array_push($recommended, array($value[0], $value[1]));
			}
		}
	}
	// close the connection
	$conn->close();
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
			<br/><br/><br/><br/><br/><br/><br/>
			<h2 class="errorMessage">
				<?php echo $errorMessage . "<br/>"; ?>
			</h2>
			
			
			Computer Science Major Requirements <br/><br/>
	<?php 
	//////// #####################################
	//////// #####################################
	// work in progress
	$worksheetCounter = array(0,0,0,0,0,0,0);
	foreach ($csmcworksheet as $value3 ) {
		if(in_array($value3[0], $takencourses) || in_array($value3[0]."H", $takencourses)) {
			$worksheetCounter[$value3[1]]++;
		}
	}
	?>
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
			Required Statistics Courses<br/>
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
			Two Computer Science Electives Courses<br/>
			<div class="progress">
				<span class="percent"><?php echo round(($worksheetCounter[5] / 2) * 100, 2) . "%"; ?></span>
				<div class="bar" style="width: <?php echo round(($worksheetCounter[5] / 2) * 100, 2) . "%"; ?>;" ></div>
			</div>
			<br/>
			Three Computer Science Electives Courses<br/>
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
			<br/>
			Recommended:<br/>
	<?php
	echo "Size: " . count($recommended) . "<br/>";
	foreach ($recommended as $val1) {
		echo "<a href =\"./process.php?studentID=" .$studentID . "&course=" . $val1[0] . "&pr=1\" >" . $val1[0] ."</a><br/>";
	}
	?>

		
		

		</div>
	</div>
</body>
</html>
