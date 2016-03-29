<?php
// start a session
session_start();
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
$course = $tempArray[5];
$pr = $tempArray[6];
$massCourse = $tempArray[7];
$errorMessage = $tempArray[8];

$validProcess = false;
// list of all courses the user have taken
$takencourses = array();
// this is the Computer Science Major worksheet - list of what needs to be taken
$csmcworksheet = array();
// this array will be populated by courses the user already have prerequisites/recommended to take
$recommended = array();
$worksheetCounter = array(0,0,0,0,0,0,0);
$worksheetCounterNames = array( array(), array(), array(), array(), array(), array(), array() );
$neededNames = array( array(), array(), array(), array(), array(), array(), array() );

// enter the student's information to the database
if($pr == "2") {
	// will only execute if all the student data is valid / not empty.
	if ($studentID != "" && $firstname != "" && $lastname != "" && $email != "" && $phone != "") {
		// check if the student is already in the database
		$sql2 = "SELECT studentid FROM students WHERE studentid = \"" . $studentID . "\" LIMIT 1";
		$result2 = mysql_query($sql2, $conn);
		// the student is already in the database
		if (mysql_num_rows($result2) > 0) {
			// update the student info in the database
			$sql3 = "UPDATE students SET firstname = \"" . $firstname . "\", lastname = \"" . $lastname . "\", email = \"" . $email . "\", phone = \"" . $phone . "\" WHERE studentid= \"" . $studentID . "\" ";
			mysql_query($sql3, $conn);	
		}
		else {
			// insert the student information to the database if the data passed is valid
			$sql1 = "INSERT into students (studentid, firstname, lastname, email, phone) values ( \"" . $studentID . "\", \"" . $firstname . "\", \"" . $lastname . "\", \"" . $email . "\", \"" . $phone . "\" )";
			mysql_query($sql1, $conn);	
		}
		$validProcess = true;
		// set session variables
		$_SESSION["studentID"] = $studentID;
		$_SESSION["firstname"] = $firstname;
		$_SESSION["lastname"] = $lastname;
		$_SESSION["email"] = $email;
		$_SESSION["phone"] = $phone;
	}
}
// logout the current user
if($pr == "4") {
	session_unset();
	session_destroy();
	session_start();
	$_SESSION["LAST_ACTIVITY"] = time();
}
// check if the student is already logged in, and the student info is stored in the session
if($validProcess == false) {
	$tempArray = sessionInfo();
	$studentID = $tempArray[0];
	$firstname = $tempArray[1];
	$lastname = $tempArray[2];
	$email = $tempArray[3];
	$phone = $tempArray[4];
	if($studentID != "") {
		$validProcess = true;
	}
}
// valid process - continue with the page request
if($validProcess == true) {
	// query the list of all the related courses for a computer science major
	$sql3 = "SELECT course, priority FROM cmscworksheet ORDER BY course ASC";
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
	$result4 = mysql_query($sql4, $conn);
	if (mysql_num_rows($result4) > 0) {
		while($row = mysql_fetch_assoc($result4)) {
			// save it in the takencourses array
			array_push($takencourses, $row["course"]);
		}
	}
	// adding taken courses to the database
	if($pr == "0" || $pr == "1") {
		
		
		// add the course to the student's academic progress
		if($course != "") {
			// first check if the Course is in the database of classes offered
			$sql5 = "SELECT course FROM courses WHERE course = \"" . $course . "\" ORDER BY course ASC LIMIT 1";
			$result5 = mysql_query($sql5, $conn);
			// the course submitted is in the database and is valid
			if (mysql_num_rows($result5) > 0) {
				// Check if the course that the student is trying to add is already in the course taken list
				if(in_array($course, $takencourses)) {
					$errorMessage .= "The course " . $course . " is already in the list of taken courses.<br/>\n";
				}
				else {
					// Check if the student meets the requirements for the course
					if(compareRequirements($course, $takencourses, $studentID)) {
						// everything checks out, and you can add the course to the list
						$sql6 = "INSERT INTO takencourses (studentid, course) VALUES (\"" . $studentID . "\", \"" . $course . "\")";
						mysql_query($sql6, $conn);
						// add the course that was just added to the takencourse array list
						array_push($takencourses, $course);
						$errorMessage .= "The course " . $course . " has been added to the list.<br/>\n";
					}
					else {
						// compareRequirements function returned false - not all requirements for the course being added has been met
						$errorMessage .= "You do not meet the requirements for " . $course . ".<br/>\n";
						// get the course requirement text for the specific course
						$sql7 = "SELECT required FROM courses where course = \"" . $course . "\"";
						$result7 = mysql_query($sql7, $conn);
						if (mysql_num_rows($result7) > 0) {
							$row2 = mysql_fetch_assoc($result7);
							if(trim($row2["required"]) != "") {
								$errorMessage .= $row2["required"] . "<br/>\n";
							}
						}
					}
				}
			}
			else {
				// error message from checking course against the database table courses
				$errorMessage .= "The course " . $course . " is not in the database.<br/>\n";
			}
		}
		else {
			$errorMessage .= $course . " is not a valid course number.<br/>\n"; 
		}
	}
	// deleting taken courses from the database
	if ($pr == "3") {
		// make sure the course id is valid
		if($course != "") {
			// add it to a queue of courses to be deleted
			$deleteList = array();
			array_push($deleteList, $course);
			// keep looping while there are courses in the list
			while(count($deleteList) > 0) {
				// delete it from the database
				$currentDelete = array_shift($deleteList);
				//echo "Deleting " . $currentDelete . "<br/>\n";
				$sql3 = "DELETE from takencourses where studentid = \"" . $studentID . "\" AND course = \"" . $currentDelete . "\" ";
				mysql_query($sql3, $conn);
				// delete it also from the current list of takencourses
				$takencourses = array_diff($takencourses, array($currentDelete));
				$errorMessage .= "The course " . $currentDelete . " has been deleted<br/>\n";
				$sql4 = "SELECT course FROM takencourses where studentid = \"" . $studentID . "\" ";
				$result4 = mysql_query($sql4, $conn);
				if (mysql_num_rows($result4) > 0) {
					while($row4 = mysql_fetch_assoc($result4)) {
						// check if the course fails to meet the requirements
						if(!compareRequirements($row4["course"], $takencourses, $studentID)) {
							// if the course is not already in the list of courses to delete
							if(!in_array($row4["course"], $deleteList)) {
								// add it to the queue of courses to delete
								array_push($deleteList, $row4["course"]);
							}
						}
					}
				}
			}
			
		}
	}
	
	// create a list of recommended courses to take
	foreach ($csmcworksheet as $value) {
		// the course is already in the taken list - no need to recommend
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
				// check science requirements - priority value 4
				if($value[1] == 4) {
					// 3 science courses and 1 lab
					if($worksheetCounter[4] >= 4) {
						continue;
					}
					if(!preg_match("/^[A-Z]{4}[0-9]{3}/", $value[0]) && $worksheetCounter[4] == 3 && !in_array("/^[A-Z]{3,4}[0-9]{3}L/", $takencourses) ) {
						continue;
					}
				}
				$sql5 = "SELECT coursename FROM courses where course = \"" . $value[0] . "\" LIMIT 1";
				$result5 = mysql_query($sql5, $conn);
				if (mysql_num_rows($result5) > 0) {
					while($row5 = mysql_fetch_assoc($result5)) {
						array_push($recommended, array($value[0], $value[1], $row5["coursename"]));
					}
				}
			}
		}
	}
	// get the counter for each CMSC worksheet priority category
	foreach ($csmcworksheet as $value3 ) {
		// check if the specific course or the honor version of the courses in CMSC worksheet are already taken
		if(in_array($value3[0], $takencourses) || in_array($value3[0]."H", $takencourses)) {
			// check what priority value it is
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
			// add the course for each priority
			array_push($worksheetCounterNames[$priority], $value3[0]);
			$takencourses = array_diff($takencourses, array($value3[0]));
		}
		else {
			array_push($neededNames[$value3[1]], $value3[0]);
		}
	}
	
}
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="styles.css" >
	<link rel="icon" type="image/png" href="./icon.png" />
	<title>CMSC433: Scripting Languages - Project 1</title>
<?php
if($validProcess == false) {
	echo "<script src=\"login.js\"></script>\n";
}
else {
	echo "<script src=\"getCourses.php?studentID=" . $studentID . "\"></script>\n";
}
?>
</head>
<body>
	<div id="wrapper" class="wrapper" >
		<div class="headerStripe" ></div>
		<div class="headercss" id="headercss">
			<div class="headernav" >
			<table width="100%"> 
			<tr>
				<td width="150">
					<a href=".?index.php"><img src="./retrievers.jpg" height="150" /></a>
				</td>
				<td width="700">
					<h2>
				<?php if($validProcess == false) { ?>
					CMSC 433: Scripting Languages - Project 1<br/>
					Members: Guansing, O'Malley, and Sternberg <br/>
					Instructor: Lupoli, Shawn
					</h2>
				</td>
				<td></td>
				<?php }
				else { ?>
					UMBC ID:&nbsp;<?php echo $studentID; ?><br/>
					Student Name: <?php echo $firstname . " " . $lastname; ?><br/>
					Email: <?php echo $email; ?><br/>
					<div class="dropdown">
					<form action="index.php" method="post">
						<input type="hidden" name="pr" value="1" />
						Search:<input type="text" id="course" name="course" class="searchBar" title="Search Course ID to add" autocomplete="off" maxlength="8" size="40" placeholder="Search Course ID to add" autofocus />
						<div id="courseResult" class="dropdown-content">
						</div>
					</form>
					</div>
					<script>
						document.querySelector("#course").addEventListener("keyup", getCourses);
						//document.querySelector("#course").addEventListener("click", getCourses);
						// when the user clicks elsewhere on the page, hide the dropdown menu
						window.onclick = function(event) {
							if (!event.target.matches('.course')) {
								document.getElementById("courseResult").classList.remove("show");
							}
						}
					</script>
					</h2>
				</td>
				<td class="logout">
				<br/>
				<a href="./index.php?pr=4">L O G O U T</a>
				</td>
				<?php } ?>
			</tr>
			</table>
			</div>
		</div>
		<div class="mainBody" id="mainBody">
		<?php if($validProcess == false) { ?>
			<br/><br/><br/><br/><br/><br/><br/><br/>
			<h2 class="errorMessage">
			<?php echo $errorMessage; ?>
			</h2>
			<h2>
			Project: Computer Science Major Worksheet<br/>
			</h2>
			<h3>
			Please enter your student information to be <br/>
			able to enter the courses you have taken, and<br/>
			view academic progress as well as the list of<br/>
			recommended courses to take.<br/>
			
			<form action="index.php" method="post" >
				<input type="hidden" name="pr" value="2" />
				<p>
				UMBC ID:&nbsp;&nbsp;<input class="loginInput" type="text" id="studentID" name="studentID" value="<?php echo $_GET["studentID"]; ?>" placeholder="AAXXXXX   A=char X=digit" maxlength="7" size="30" pattern="[A-Za-z]{2}[0-9]{5}" required autofocus />
				</p><p>
				Firstname:&nbsp;&nbsp;<input class="loginInput" type="text" id="firstname" name="firstname" value="<?php echo $_GET["firstname"]; ?>" placeholder="First name" maxlength="50" pattern="^[\w][\-\s\w\d\.']*" size="30" required />
				</p><p>
				Lastname:&nbsp;&nbsp;<input class="loginInput" type="text" id="lastname" name="lastname" value="<?php echo $_GET["lastname"]; ?>" placeholder="Last name" maxlength="50" pattern="^[\w][-\s\w\d\.']*" size="30" required />
				</p><p>
				Email:&nbsp;&nbsp;<input class="loginInput" type="text" id="email" name="email" value="<?php echo $_GET["email"]; ?>" placeholder="Email address" pattern="^[\w\d\-]+\.?[\w\d\-]*@[\w\d\.\-]+\.[\w\d]+" size="30" required />
				</p><p>
				Phone:&nbsp;&nbsp;<input class="loginInput" type="text" id="phone" name="phone" value="<?php echo $_GET["phone"]; ?>" placeholder="XXX-XXX-XXXX" maxlength="12" pattern="^[0-9]{3}-[0-9]{3}-[0-9]{4}" size="30" required />
				</p><br/>
				<input class="loginInput" type="submit" value="Submit" />
			</form>
			</h3>
			<script>
				document.querySelector("#studentID").addEventListener("keyup", getStudentInfo);
				document.querySelector("#phone").addEventListener("keyup", formatPhone);
				document.querySelector("#firstname").addEventListener("keyup", formatName);
				document.querySelector("#lastname").addEventListener("keyup", formatName);
				document.querySelector("#email").addEventListener("keyup", formatEmail);
			</script>
		<?php }
		else { ?>
		<br/><br/><br/><br/><br/><br/><br/><br/><br/>
			<div class="sidebar">
				<div class="recommendedLabel">RECOMMENDED COURSES</div>
				<?php
				foreach ($recommended as $val1) {?>
				<div class="divButton" name="<?php echo $val1[0]; ?>" id="<?php echo $val1[0]; ?>" ><?php echo $val1[0] . " - " . $val1[2]; ?></div>
				<div class="divInfo" id="div<?php echo $val1[0]; ?>" ></div>
				<script>
				document.querySelector("#<?php echo $val1[0]; ?>").addEventListener("click", display);
				</script>
				
				<?php
				}
				?>
				<br/>
			</div>
			<div class="studentDetails">
				
				<div class="errorMessage"><?php echo $errorMessage . "<br/>"; ?></div>
				
				Computer Science Major Requirements <br/><br/>
				Required Computer Science Courses<br/>
				<div class="progress">
					<span class="percent"><?php echo round(($worksheetCounter[1] / 11) * 100, 2) . "%"; ?></span>
					<div class="bar" style="width: <?php echo round(($worksheetCounter[1] / 11) * 100, 2) . "%"; ?>;" ></div>
				</div>
				<?php displayCourses($worksheetCounterNames[1]); ?>
				<br/>
				Required Math Courses<br/>
				<div class="progress">
					<span class="percent"><?php echo round(($worksheetCounter[2] / 3) * 100, 2) . "%"; ?></span>
					<div class="bar" style="width: <?php echo round(($worksheetCounter[2] / 3) * 100, 2) . "%"; ?>;" ></div>
				</div>
				<?php displayCourses($worksheetCounterNames[2]); ?>
				<br/>
				Required Statistics Course<br/>
				<div class="progress">
					<span class="percent"><?php echo round(($worksheetCounter[3] / 1) * 100, 2) . "%"; ?></span>
					<div class="bar" style="width: <?php echo round(($worksheetCounter[3] / 1) * 100, 2) . "%"; ?>;" ></div>
				</div>
				<?php displayCourses($worksheetCounterNames[3]); ?>
				<br/>
				Required Science Courses<br/>
				<div class="progress">
					<span class="percent"><?php echo round(($worksheetCounter[4] / 4) * 100, 2) . "%"; ?></span>
					<div class="bar" style="width: <?php echo round(($worksheetCounter[4] / 4) * 100, 2) . "%"; ?>;" ></div>
				</div>
				<?php displayCourses($worksheetCounterNames[4]); ?>
				<br/>
				Two Computer Science Elective Courses<br/>
				<div class="progress">
					<span class="percent"><?php echo round(($worksheetCounter[5] / 2) * 100, 2) . "%"; ?></span>
					<div class="bar" style="width: <?php echo round(($worksheetCounter[5] / 2) * 100, 2) . "%"; ?>;" ></div>
				</div>
				<?php displayCourses($worksheetCounterNames[5]); ?>
				<br/>
				Three Technical Elective Courses<br/>
				<div class="progress">
					<span class="percent"><?php echo round(($worksheetCounter[6] / 3) * 100, 2) . "%"; ?></span>
					<div class="bar" style="width: <?php echo round(($worksheetCounter[6] / 3) * 100, 2) . "%"; ?>;" ></div>
				</div>
				<?php displayCourses($worksheetCounterNames[6]); ?>
				<br/>
				
				<br/>
				<?php if(count($takencourses) > 0) {
					echo "Other Courses Taken<br/>\n";
					displayCourses($takencourses);
				} ?>
				<br/>
			
			</div>
		
		<?php }

mysql_close($conn);
$_SESSION["LAST_ACTIVITY"] = time();

?>
			
			<br/><br/>
		</div>
		
	</div>

</body>
</html>