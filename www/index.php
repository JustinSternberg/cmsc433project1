<?php

// get functions
require "projectFunctions.php";

$tempArray = studentInfo();
$studentID = $tempArray[0];
$firstname = $tempArray[1];
$lastname = $tempArray[2];
$email = $tempArray[3];
$phone = $tempArray[4];
$errorMessage = "";

?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="styles.css" >
	<link rel="icon" type="image/png" href="./icon.png" />
	<title>CMSC433: Scripting Languages - Project 1</title>
	<script src="login.js"></script>
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
					CMSC 433: Scripting Languages - Project 1<br/>
					Members: Guansing, O'Malley, and Sternberg <br/>
					Instructor: Lupoli, Shawn
					</h2>
				</td>
			</tr>
			</table>
			</div>
			
		</div>
		
		<div class="mainBody" id="mainBody">
			<br/><br/><br/><br/><br/><br/><br/>
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
			
			<form action="process.php" method="post" >
				<input type="hidden" name="pr" value="2" />
				<p>
				UMBC ID:&nbsp;&nbsp;<input class="loginInput" type="text" id="studentID" name="studentID" value="<?php echo $studentID; ?>" placeholder="AAXXXXX   A=char X=digit" maxlength="7" size="30" pattern="[A-Za-z]{2}[0-9]{5}" required autofocus />
				</p><p>
				Firstname:&nbsp;&nbsp;<input class="loginInput" type="text" id="firstname" name="firstname" value="<?php echo $firstname; ?>" placeholder="First name" maxlength="50" pattern="^[\w][\-\s\w\d\.']*" size="30" required />
				</p><p>
				Lastname:&nbsp;&nbsp;<input class="loginInput" type="text" id="lastname" name="lastname" value="<?php echo $lastname; ?>" placeholder="Last name" maxlength="50" pattern="^[\w][-\s\w\d\.']*" size="30" required />
				</p><p>
				Email:&nbsp;&nbsp;<input class="loginInput" type="text" id="email" name="email" value="<?php echo $email; ?>" placeholder="Email address" pattern="^[\w\d\-]+\.?[\w\d\-]*@[\w\d\.\-]+\.[\w\d]+" size="30" required />
				</p><p>
				Phone:&nbsp;&nbsp;<input class="loginInput" type="text" id="phone" name="phone" value="<?php echo $phone; ?>" placeholder="XXX-XXX-XXXX" maxlength="12" pattern="^[0-9]{3}-[0-9]{3}-[0-9]{4}" size="30" required />
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
		</div>
	</div>
</body>
</html>
