<?php
// get functions
//require "projectFunctions.php";
// retrieve the get/post HTTP method requests
//$tempArray = studentInfo();
//$studentID = $tempArray[0];

?>
	function getCourses() {
		var searchValue = document.querySelector("#course");
		var searchResults = document.querySelector("#courseResult");
		// clear the unordered list everytime the keyboard is pressed
		searchResults.innerHTML = "";
		var newDropDown = "";
		// only run this is the search box is not empty
		if(searchValue.value.length > 0) {
			searchValue.value = ((searchValue.value).toUpperCase()).trim();
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
				var newValue = "";
				for (var i=0; i < jsonParsed.length; i++) {
					newValue += "<a title=\"" + jsonParsed[i][0] + " - " + jsonParsed[i][1] + "&#10;&#10;" + jsonParsed[i][2];
					if((jsonParsed[i][3]).trim() != "") {
						newValue += "&#10;&#10;Requirements: " + jsonParsed[i][3];
					}
					newValue += "\" href=\"./index.php?course=" + jsonParsed[i][0] + "&pr=1\" >" +
						jsonParsed[i][0] + " - " + jsonParsed[i][1] +  "</a>";
						
				}
				searchResults.innerHTML = newValue;
			}
			xhr.send(null);
			searchResults.innerHTML = newDropDown;
			document.getElementById("courseResult").classList.add("show");
		}
		else {
			document.getElementById("courseResult").classList.remove("show");
		}
	}
	function display() {
		var course = this.id;
		var divOut = document.querySelector("#div" + course);
		if (divOut.innerHTML != "") {
			divOut.innerHTML = "";
		}
		else {
			var courseSearchURL = "./courses.php?course=" + course;
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
				if(jsonParsed.length > 0) {
					var newValue = jsonParsed[0][0] + " - " + jsonParsed[0][1] + "<br/><br/>" + jsonParsed[0][2];
					if((jsonParsed[0][3]).trim() != "") {
						newValue += "<br/><br/>Requirements: " +  jsonParsed[0][3];
					}
					divOut.insertAdjacentHTML('afterbegin', "<br/>" + newValue +
					"<br/><br/><a href=\"./index.php?course=" + course + "&pr=1\" ><input type=\"button\"class=\"addIt\"  value=\"Add it\" /></a><br/><br/>"
					);
				}
			}
			xhr.send(null);
		}
	}