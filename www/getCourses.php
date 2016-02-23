<?php
// get functions
require "projectFunctions.php";
// retrieve the get/post HTTP method requests
$tempArray = studentInfo();
$studentID = $tempArray[0];

?>
	function getCourses() {
		var searchValue = document.querySelector("#courseSearch");
		var searchResults = document.querySelector("#courseResult");
		// clear the unordered list everytime the keyboard is pressed
		searchResults.innerHTML = "";
		var newDropDown = "";
		// only run this is the search box is not empty
		if(searchValue.value.length > 0) {
			searchValue.value = ((searchValue.value).toUpperCase()).trim();
			searchResults.innerHTML = "";
			// url address of the php page that provides JSON data
			var courseSearchURL = "./courses.php?course=" + searchValue.value;
			// make a same orogin XMLHttpRequest to get the list of courses
			var xhr = new XMLHttpRequest();
			// GET Method is being used
			xhr.open("GET", courseSearchURL, true);
			xhr.onload = function() {
				// store the webpage's response. The search result of database query
				var resp = xhr.responseText;
				// convert it to an array that we can manipulate
				var jsonParsed = JSON.parse(resp);
				// make a list of the results
				for (var i=0; i < jsonParsed.length; i++) {
					searchResults.insertAdjacentHTML('afterbegin',"<a title=\"" + jsonParsed[i][0] + " - " + jsonParsed[i][1] + "&#10;&#10;" +
						jsonParsed[i][2] + "\" href=\"./process.php?studentID=<?php echo $studentID;  ?>&courseSearch=" + searchValue.value + "&course=" + jsonParsed[i][0] + "&pr=1\" >" +
						jsonParsed[i][0] + " - " + jsonParsed[i][1] +  "</a>");
				}
			}
			xhr.send(null);
			searchResults.innerHTML = newDropDown;
			document.getElementById("courseResult").classList.add("show");
		}
		else {
			document.getElementById("courseResult").classList.remove("show");
		}
	}