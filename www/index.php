<?php
session_start(); // start a session
require "dbConnection.php"; // connect to the database
require "projectFunctions.php"; // get functions
$tempArray = studentInfo(); // retrieve the get/post HTTP method requests
$studentID = $tempArray[0];
$firstname = $tempArray[1];
$lastname = $tempArray[2];
$email = $tempArray[3];
$phone = $tempArray[4];
$course = $tempArray[5];
$pr = $tempArray[6];
$errorMessage = $tempArray[7];
$validProcess = false;
$takencourses = array(); // list of all courses the user have taken
$csmcworksheet = array(); // this is the Computer Science Major worksheet
$recommended = array(); // this array will be populated by courses the user already have prerequisites/recommended to take
$worksheetCounter = array(0,0,0,0,0,0,0);
$worksheetCounterNames = array( array(), array(), array(), array(), array(), array(), array() );
$semesters = array();

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
			// new entry -- insert the student information to the database if the data passed is valid
			$sql1 = "INSERT into students (studentid, firstname, lastname, email, phone) values ( \"" . $studentID . "\", \"" . $firstname . "\", \"" . $lastname . "\", \"" . $email . "\", \"" . $phone . "\" )";
			mysql_query($sql1, $conn);	
		}
		$validProcess = true;
		$_SESSION["studentID"] = $studentID; // set session variables
		$_SESSION["firstname"] = $firstname; // this will be used to keep user logged in
		$_SESSION["lastname"] = $lastname;
		$_SESSION["email"] = $email;
		$_SESSION["phone"] = $phone;
	}
}
// logout the current user / invalidate the session
if($pr == "4") {
	session_unset();
	session_destroy();
	session_start();
	$_SESSION["LAST_ACTIVITY"] = time();
}
// check if the student is already logged in, and the student info is stored in the session
if($validProcess == false) {
	$tempArray = sessionInfo(); // retrieve session variables
	$studentID = $tempArray[0]; // these will be empty strings if the user is not logged in
	$firstname = $tempArray[1];
	$lastname = $tempArray[2];
	$email = $tempArray[3];
	$phone = $tempArray[4];
	// the session variables are set -- user is logged in and process is valid
	if($studentID != "") {
		$validProcess = true;
	}
}
// valid process - continue with the page request
if($validProcess == true) {
	// query the list of all the related courses for a computer science major
	$sql3 = "SELECT cmscworksheet.course, cmscworksheet.priority, courses.coursename, courses.credit, courses.requiredtext, courses.description, courses.courseid FROM cmscworksheet, courses WHERE cmscworksheet.course = courses.course ORDER BY cmscworksheet.priority ASC, cmscworksheet.course ASC, courses.credit DESC ";
	$result3 = mysql_query($sql3, $conn);
	// get all the query results
	if (mysql_num_rows($result3) > 0) {
		while($row = mysql_fetch_assoc($result3)) {
			// save it in the csmcworksheet array
			$cmscworksheet[ $row["course"] ] = array( $row["course"], $row["priority"], $row["coursename"], $row["credit"], $row["requiredtext"], $row["description"], $row["courseid"] ); 
		}
	}
	// query all the courses already taken by the user
	$sql4 =  "SELECT takencourses.course, courses.coursename, courses.credit FROM takencourses, courses WHERE takencourses.studentid = \"" . $studentID . "\" AND takencourses.course = courses.course ORDER BY takencourses.course ASC";
	$result4 = mysql_query($sql4, $conn);
	if (mysql_num_rows($result4) > 0) {
		while($row = mysql_fetch_assoc($result4)) {
			// save it in the takencourses array
			$takencourses[ $row["course"] ] = array( $row["course"], $row["coursename"], $row["credit"] );
		}
	}
	// adding taken courses to the database
	if($pr == "1") {
		// split the course text from form into an array of courses to add
		$addCourse = explode(",", $course);
		sort($addCourse); // sort the courses in ascending order to try and add lower level courses first
		// loop through each course that needs to be added
		foreach($addCourse as $course1) {
			// make sure the split course is not empty
			if($course1 != "") {
				// first check if the Course is in the database of classes offered
				$sql5 = "SELECT coursename, requiredtext, credit FROM courses WHERE course = \"" . $course1 . "\" ORDER BY course ASC LIMIT 1";
				$result5 = mysql_query($sql5, $conn);
				// the course submitted is in the database and is valid
				if (mysql_num_rows($result5) > 0) {
					$row1 = mysql_fetch_assoc($result5);
					// retrieve course data that will be used by takencourses list / display message
					$cur_coursename = $row1["coursename"];
					$cur_requiredtext = trim($row1["requiredtext"]);
					$cur_credit = $row1["credit"];
					// Check if the course that the student is trying to add is already in the course taken list
					if(array_key_exists($course1, $takencourses)) {
						$errorMessage .= "The course " . $course1 . " is already in the list of taken courses.<br/>\n";
					}
					else {
						// Check if the student meets the requirements for the course
						if(compareRequirements($course1, $takencourses, $studentID)) {
							// everything checks out, and you can add the course to the list
							$sql6 = "INSERT INTO takencourses (studentid, course) VALUES (\"" . $studentID . "\", \"" . $course1 . "\")";
							mysql_query($sql6, $conn);
							// append the course that was just added to the takencourses array list
							$takencourses[$course1] = array($course1, $cur_coursename, $cur_credit);
							$errorMessage .= "The course " . $course1 . " has been added to the list.<br/>\n";
						}
						else {
							// compareRequirements function returned false -- not all requirements for the course being added has been met
							$errorMessage .= "You do not meet the requirements for " . $course1 . ".<br/>\n";
							if($cur_requiredtext != "") {
								// list the course requirement text for the specific course
								$errorMessage .= "<span class=\"errorMessage2\" >" . $cur_requiredtext . "</span><br/>\n";
							}
						}
					}
				}
				else {
					// error message from checking course against the database table courses
					$errorMessage .= "The course " . $course1 . " is not in the database.<br/>\n";
				}
			}
		}
	}
	// deleting taken courses from the database
	if ($pr == "3") {
		// make sure the course id is valid
		if($course != "") {
			$deleteList = array();
			array_push($deleteList, $course); // add it to a queue of courses to be deleted
			// keep looping while there are courses in the list
			while(count($deleteList) > 0) {
				$currentDelete = array_shift($deleteList); // pop the front element of the array -- Queue of courses to delete
				$sql3 = "DELETE from takencourses where studentid = \"" . $studentID . "\" AND course = \"" . $currentDelete . "\" ";
				mysql_query($sql3, $conn); // delete it from the database
				unset($takencourses[$currentDelete]); // delete it also from the list of takencourses
				$errorMessage .= "The course " . $currentDelete . " has been deleted<br/>\n";
				// check the dependencies of the courses left in the takencourses table
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
	// get the list of semesters 
	$sqlSemester = "SELECT id, semester from semesters ORDER BY id DESC LIMIT 4";
	$resultSemester = mysql_query($sqlSemester, $conn);
	if (mysql_num_rows($resultSemester) > 0) {
		while($rowSemester = mysql_fetch_assoc($resultSemester)) {
			// add it to the array of semesters - last 4 semesters
			$semesters[ $rowSemester["id"] ] = array($rowSemester["id"], $rowSemester["semester"] );
		}
	}
	// science course sequence
	$sequence = array("BIOL100", "BIOL301", "BIOL141", "BIOL142", "CHEM101", "CHEM102", "PHYS121", "PHYS122");
	$whichSequence = 0;
	$newCount = 0;
	$newScienceList = array();
	// get the counter for each CMSC worksheet priority category
	foreach($cmscworksheet as $value3) {
		// check if the specific course or the honor version of the course in CMSC worksheet is already taken
		if(exists($value3[0], $takencourses)) {
			// check what priority value it is
			$priority = $value3[1];
			if($priority == 4) {
				// add the science course sequences to newScienceList list -- all other science courses to the worksheetCounterNames array
				if(in_array($value3[0], $sequence)) {
					if($whichSequence > 0 && $newCount >= 12) {
						continue; // the science sequence is fulfilled and there are already 12 credits
					}
					else {
						if(exists($value3[0], $newScienceList)) {
							continue; // was already added by searching the sequence pair
						}
						$whatIndex = array_search($value3[0], $sequence);
						// figure out if the science sequence is fulfilled. Example CHEM101 (index = 4) and CHEM102 (index = CHEM101 + 1)
						if($whichSequence == 0 && $whatIndex % 2 == 0 && exists($sequence[$whatIndex + 1], $takencourses) ) {
							
							$whichSequence = $whatIndex + 1; // set and add the sequence courses
							$newScienceList[ $value3[0] ] = $takencourses[ $value3[0] ]; // the first course in the sequence
							$newScienceList[ $sequence[$whatIndex + 1] ] = $takencourses[ $sequence[$whatIndex + 1] ]; // the 2nd course in the sequence
							$newCount += $value3[3] + $takencourses[ $sequence[$whatIndex + 1] ][2]; // add credit count of the sequence
						}
						else{
							if($whichSequence == 0 && $newCount + $value3[3] > 8) {
								continue; // keep only 2 of the individual science courses -- need room to add 1 to finish the sequence
							}
							else {
								$newScienceList[ $value3[0] ] = $takencourses[ $value3[0] ]; // add to the science courses list
								$newCount += $value3[3]; // update credit count
							}
						}
					}
					continue; // do not add these to the list of science courses that do not complete the sequence requirement
				}
			}
			else {
				if($priority == 5 && $worksheetCounter[5] >= 2) {
					$priority = 6; // if the 2 computer electives are already satisfied, it can count as a technical elective
				}
				if($priority == 6 && $worksheetCounter[6] >= 3) {
					continue; // if the technical electives are already satisfied, then just skip it and continue with the loop
				}
				$worksheetCounter[$priority]++;
			}
			// leave priority = 0 (considered general electives) on the main list of taken courses
			if($priority > 0) {
				// save it to the corresponding worksheetCounterNames array based on priority index =  array(course, coursename, credit)
				$worksheetCounterNames[$priority][ $value3[0] ] = array($value3[0], $value3[2], $value3[3]);
			}
		}
	}
	// check to make sure the user have the science sequence done and move courses exceeding the 12 credit to the other courses taken
	foreach($worksheetCounterNames[4] as $current_course) {
		if($newCount >= 12) {
			break;
		}
		elseif($whichSequence == 0 && $newCount + $current_course[2] > 8) {
			break;
		}
		else {
			$newScienceList[ $current_course[0] ] = $current_course; // add the science course to the list
			$newCount += $current_course[2]; // update the credit count
		}
	}
	$worksheetCounterNames[4] = $newScienceList; // set the science category array list as the reworked list
	if($newCount > 12) { $newCount = 12; } // for bar graph purposes -- max is 12 credit 
	$worksheetCounter[4] = $newCount; // adjust / set the counter for science courses
	
	// create a list of recommended courses to take
	$generalElectives = array(); // priority zero (0) to end of recommended array list
	foreach($cmscworksheet as $value) {
		if(array_key_exists($value[0], $takencourses) || array_key_exists($value[0]."H", $takencourses)) {
			// the course is already in the taken list - no need to recommend it
			continue;
		}
		else {
			// check if the user meets the requirements for each course in the cmscworksheet
			if(compareRequirements($value[0], $takencourses, $studentID)) {
				// check priority 1 requirements
				if(($value[0] == "CMSC345" || $value[0] == "CMSC447") && (array_key_exists("CMSC345", $takencourses) || array_key_exists("CMSC447", $takencourses))) {
					continue; // either 345 ot 447, don't need the other if you already taken the other option
				}
				if($value[1] == 5 && $worksheetCounter[5] >= 2 && $worksheetCounter[6] >= 3) {
					continue; // check if the 2 computer science & technical electives are satisfied, and if so skip recommending priority 5 courses
				}
				if($value[1] == 6 && $worksheetCounter[6] >= 3) {
					continue; // check if the technical electives requirement is satisfied, and if so skip recommending priority 6 courses
				}
				// check science requirements - priority value 4
				if($value[1] == 4) {
					// 3 science courses and 1 lab
					if($worksheetCounter[4] >= 12) {
						continue;
					}
					elseif($whichSequence == 0 && !in_array($value[0], $sequence) && $worksheetCounter[4] + $value[3] >= 9) {
						// do not recommend science courses that will not fulfill science sequence if it exceeds 9 credits
						continue;
						
					}
				}
				// add the info for the course as an array: course, priority, coursename, credit, requiredtext, description, courseid
				if($value[1] > 0) {
					$recommended[$value[0]] = $value;
				}
				else {
					// this is a workaround to add general electives at the end since they are searched first ORDERED by priority ASC
					$generalElectives[$value[0]] = $value;
				}
			}
		}
	}
	$recommended = array_merge($recommended, $generalElectives);
	// take out the courses in takencourses that were categorized into the CMSC worksheet based on priority
	// this is done separately to ensure consistency with science courses and technical electives
	foreach ($worksheetCounterNames as $categories) {
		// these are priorities 0 to N (zero for general electives in the cmscworksheet, 1 for requires cmsc courses, etc.)
		foreach ($categories as $removeThis) {
			// these are the courses that need to be removed from takencourses array list (no duplicates)
			unset($takencourses[ $removeThis[0] ]);
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" type="text/css" href="styles.css" >
	<link rel="icon" type="image/png" href="./icon.png" />
	<title>CMSC433: Scripting Languages - Project 1</title>
<?php
if($validProcess == false) {
	// load login.js when the user is not logged in
	echo "<script src=\"login.js\"></script>\n";
}
else {
	/// load getCourses.php to validate the search bar and to handle the recommended courses div info
	echo "<script src=\"getCourses.js\"></script>\n";
}
?>
</head>
<body>
	<div id="loading" class="loading" >
		<div class="loadingContent" id="loadingContent" ><img src="loadingAnimation.gif" width="300" /></div>
	</div>
	<div id="wrapper" class="wrapper" >
		<div class="headerStripe" ></div>
		<div class="headercss" id="headercss">
			<div class="headernav" >
				<div><span class="logo" ><a alt="CMSC433: Scripting Languages - Project 1" title="CMSC433: Scripting Languages - Project 1" href=".?index.php"><img src="./retrievers.jpg" height="150" /></a></span>
				<span class="navTableCell" >
					<h2>
				<?php
				// this is the top navigation bar of the webpage
				if($validProcess == false) { ?>
					CMSC 433 - Scripting Languages<br/>
					Project 1: CMSC Worksheet<br/>
					Members:<br/>
					Guansing, O'Malley, and Sternberg <br/>
					Instructor: Prof. Lupoli<br/>
					</h2>
				</span>
				
				<?php }
				else { ?>
					UMBC ID:&nbsp;<?php echo $studentID; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Phone:&nbsp;<?php echo $phone; ?>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<a class="logoutText" title="Not <?php echo $firstname . " " . $lastname; ?>?&#10;Sign out and login with your student ID." href="./index.php?pr=4">[  Logout  ]</a><br/>
					Firstname: <?php echo $firstname; ?><br/>
					Lastname: <?php echo $lastname; ?><br/>
					Email: <?php echo $email; ?><br/>
					<form action="index.php" method="post" onsubmit="displayLoading()" >
						<input type="hidden" name="pr" value="1" />
						<div class="dropdown">
							<span><input type="text" id="course" name="course" class="searchBar" title="Add a course by selecting from the drop-down list (will work for single course searches) OR enter the course ID. If adding multiple courses at a time, then the courses need to be comma separated." autocomplete="off" pattern="^([A-Za-z]{3,4}[0-9]{2,3}[A-Za-z]?\s?,?\s?)+$" placeholder="Enter Course ID(s) to add" required /><input type="image" src="search1.png" class="imageSubmit" value="" title="Search for courses to add" /></span>
							<div id="courseResult" class="dropdown-content">
							</div>
						</div>
					</form>
					<script>
						document.querySelector("#course").addEventListener("keyup", getCourses);
						// when the user clicks elsewhere on the page, hide the dropdown menu
						window.onclick = function(event) {
							if (!event.target.matches('.course')) {
								document.getElementById("courseResult").classList.remove("show");
							}
						}
					</script>
					</h2>
				</span>
				<?php } ?>
				</div>
			</div>
		</div>
		<div class="mainBody" id="mainBody">
		<?php if($validProcess == false) { ?>
			<br/><br/><br/><br/><br/><br/><br/><br/>
			<h2 class="errorMessage">
			<?php echo "<br/>" . $errorMessage; ?>
			</h2>
			<h2>
			Project: Worksheet for Computer Science Majors<br/>
			</h2>
			<h3>
			Please enter your student information.<br/>
			This site allows you to view your academic progress, and<br/>
			list recommended courses based on courses taken.<br/>
			<form action="index.php" method="post" >
				<input type="hidden" name="pr" value="2" />
				<p>
				UMBC ID:&nbsp;&nbsp;<input class="loginInput" type="text" id="studentID" name="studentID" title="Please enter your student ID. The format needs to be 2 characters followed by 5 digits (AAXXXXX)."  value="<?php echo htmlspecialchars($_GET["studentID"]); ?>" placeholder="AAXXXXX   A=char X=digit" maxlength="7" size="30" pattern="[A-Za-z]{2}[0-9]{5}" required autofocus />
				</p><p>
				Firstname:&nbsp;&nbsp;<input class="loginInput" type="text" id="firstname" name="firstname" title="Please enter your firstname. Only alphanumeric characters, space, dash, period, and a single quote (apostrophe) are allowed." value="<?php echo htmlspecialchars($_GET["firstname"]); ?>" placeholder="First name" maxlength="50" pattern="^[\w][\-\s\w\d\.']*" size="30" required />
				</p><p>
				Lastname:&nbsp;&nbsp;<input class="loginInput" type="text" id="lastname" name="lastname" title="Please enter your lastname. Only alphanumeric characters, space, dash, period, and a single quote (apostrophe) are allowed." value="<?php echo htmlspecialchars($_GET["lastname"]); ?>" placeholder="Last name" maxlength="50" pattern="^[\w][-\s\w\d\.']*" size="30" required />
				</p><p>
				Email:&nbsp;&nbsp;<input class="loginInput" type="text" id="email" name="email" title="Please enter your email address." value="<?php echo htmlspecialchars($_GET["email"]); ?>" placeholder="Email address" pattern="^[\w\d\-]+\.?[\w\d\-]*@[\w\d\.\-]+\.[\w\d]+" size="30" required />
				</p><p>
				Phone:&nbsp;&nbsp;<input class="loginInput" type="text" id="phone" name="phone" title="Please enter your phone number. It is a 10 digit number separated by dashes in this format XXX-XXX-XXXX." value="<?php echo htmlspecialchars($_GET["phone"]); ?>" placeholder="XXX-XXX-XXXX" maxlength="12" pattern="^[0-9]{3}-[0-9]{3}-[0-9]{4}" size="30" required />
				</p>
				<input class="addIt" type="submit" value="Submit" />
			</form>
			</h3>
			<script>
				document.querySelector("#studentID").addEventListener("keyup", getStudentInfo);
				document.querySelector("#phone").addEventListener("keyup", formatPhone);
				document.querySelector("#firstname").addEventListener("keyup", formatName);
				document.querySelector("#lastname").addEventListener("keyup", formatName);
			</script>
		<?php }
		else { ?>
		<br/><br/><br/><br/><br/><br/><br/><br/><br/>
			<div class="sidebar">
				<div class="recommendedLabel">RECOMMENDED COURSES</div>
				<div class="recommendedLabel">
					<input type="hidden" name="lastSemesterID" id="lastSemesterID" value="" />
					Semester&nbsp;&nbsp;<select name="semester" id="semester" class="semesters" title="Select which semester to search for sections/dates for a given course." >
						<?php
						foreach($semesters as $semester) {
							echo "<option class=\"semesters\" value=\"" . $semester[0] . "\">" . $semester[1] . "</option>\n";
						}
						?>
					</select>
				</div>
				<?php
				$priorityText = array("General Elective Courses", "Required Computer Science Courses", "Required Math Courses", "Required Statistics Course", "Science Courses", "Computer Science Elective Courses", "Technical Elective Courses");
				// general elective do't have max value indicated, priority 1-3 & 5-6 have number of courses, and priority 4 have max credits counted
				$priorityMax = array(0, 11, 3, 1, 12, 2, 3);
				$lastCategory = -1;
				foreach ($recommended as $val1) {
					if($lastCategory != $val1[1] ) {
						$lastCategory = $val1[1];
						?><div class="recommendedLabel"><?php echo $priorityText[ $val1[1] ]; ?></div>
					<?php }
				?>
				<div class="divButton" id="<?php echo $val1[0]; ?>" title="Click to view more details on the course <?php echo $val1[0]; ?>." ><?php echo $val1[0] . " - " . $val1[2]; ?></div>
					<div class="divOverlay" id="overlay<?php echo $val1[0]; ?>" >
						<div class="divInfo" id="div<?php echo $val1[0]; ?>" >
						<br/><?php
						// display the course and coursename
						echo "<b>" . $val1[0] . " - " . $val1[2]; 
						if($val1[3] > 0) {
							// display the course credit if it's included
							echo " (Credits: " . $val1[3] . ")";
						}
						echo "<br/>[ " . $priorityText[ $val1[1] ] . " ]</b><br/><br/>\n";
						// display the course description
						echo $val1[5]. "<br/><br/>\n";
						if(trim($val1[4]) != "") {
							// display the course's requirements (text only) if it's in the database
							echo "Requirements: " . $val1[4] . "\n"; 
						} ?>
						<br/><br/>
						<div id="schedule<?php echo $val1[6]; ?>" class="courseSchedule" ></div>
						<a href="./index.php?course=<?php echo $val1[0]; ?>&pr=1" ><input type="button" class="addIt" title="<?php echo $val1[0] . " - " . $val1[2]; ?>&#10;&#10;ADD to the list of taken courses." value="Add it" /></a>
						&nbsp;&nbsp;<input type="button" id="section<?php echo $val1[6]; ?>" class="viewSections" title="View available sections for the course <?php echo $val1[0]; ?>" value="Sections" />
						<br/><br/>
						</div>
						
					</div>
					<script>
					document.querySelector("#<?php echo $val1[0]; ?>").addEventListener("click", display);
					document.querySelector("#section<?php echo $val1[6]; ?>").addEventListener("click", getSections);
					</script>
					
				<?php
				}
				?>
				<br/>
			</div>
			<div class="studentDetails">
				<div class="errorMessage"><?php echo $errorMessage . "<br/>"; ?></div>
				Computer Science Major Requirements <br/><br/>
				<?php
				// skip priority 0 or general elective courses since it will be part of other courses taken
				for($i = 1; $i < count($priorityText); $i++) { ?>
					<?php echo $i . ". " . $priorityText[$i]; ?><br/>
					<div class="progress">
						<span class="percent"><?php echo round(($worksheetCounter[$i] / $priorityMax[$i] ) * 100, 2) . "%"; ?></span>
						<div class="bar" style="width: <?php echo round(($worksheetCounter[$i] / $priorityMax[$i] ) * 100, 2) . "%"; ?>;" ></div>
					</div>
					<?php displayCourses($worksheetCounterNames[$i]); ?>
					<br/>
				<?php } ?>
				<?php if(count($takencourses) > 0) {
					echo "Other Courses Taken<br/>\n";
					displayCourses($takencourses);
				} ?>
				<br/>
			</div>
			<script>
				// get all the elements that uses the class addIt
				var allAddit = document.querySelectorAll(".addIt");
				for(var i=0; i < allAddit.length; i++) {
					// add a click actionListener to each element with the addIt class
					allAddit[i].addEventListener("click", displayLoading);
				}
			</script>
		<?php }
// close the db connection
mysql_close($conn);
// set the last activity time of the session
$_SESSION["LAST_ACTIVITY"] = time();
?>
			<br/><br/>
		</div>
	</div>
</body>
</html>