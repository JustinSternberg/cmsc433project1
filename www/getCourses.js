
	function getCourses() {
		var searchValue = document.querySelector("#course");
		var searchResults = document.querySelector("#courseResult");
		// clear the unordered list everytime the keyboard is pressed
		searchResults.innerHTML = "";
		var newDropDown = "";
		// only run this is the search box is not empty
		if(searchValue.value.length > 0) {
			searchValue.value = ((searchValue.value).toUpperCase()).trim();
			var filteredSearchValue = "";
			var newCourse = 0;
			var digitCount = 0;
			for(var i=0; i < searchValue.value.length; i++) {
				// only accept alphanumeric characters as valid input
				if(searchValue.value[i].match(/[\w\d,]/) && searchValue.value[i] != "_") {
					if(searchValue.value[i] == ",") {
						// if the character is a comma and the course either have 2-3digit postfix or a character (L, H, Y, etc.) postfix
						if(newCourse == 10 || (digitCount == 2 || digitCount == 3)) {
							filteredSearchValue += searchValue.value[i];
							// reset the counters
							newCourse = 0;
							digitCount = 0;
						}
					}
					else {
						// accept 3 or 4 prefix characters for courses
						if(isNaN(searchValue.value[i]) && (newCourse < 4)) {
							filteredSearchValue += searchValue.value[i];
							newCourse++;
						}
						// accept 2-3 digits after the character prefix -- so [A-Z]{3,4}[0-9]{2,3}
						else if(!isNaN(searchValue.value[i]) && (newCourse >= 3 && newCourse < 7) && digitCount < 3) {
							filteredSearchValue += searchValue.value[i];
							newCourse++;
							digitCount++;
						}
						// accept 1 character postfix like L for lab or H for honors
						else if(isNaN(searchValue.value[i]) && (digitCount >= 2 && digitCount <= 3) && newCourse < 10) {
							filteredSearchValue += searchValue.value[i];
							newCourse = 10;
						}
					}
				}
			}
			searchValue.value = filteredSearchValue;
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
					newValue += "\" href=\"./index.php?course=" + jsonParsed[i][0] + "&pr=1\" onclick=\"displayLoading();\" >" +
						jsonParsed[i][0] + " - " + jsonParsed[i][1] +  "</a>";
						
				}
				searchResults.innerHTML = newValue;
			}
			xhr.send(null);
			searchResults.innerHTML = newDropDown;
			// display the dropdown list of courses
			document.getElementById("courseResult").classList.add("show");
		}
		else {
			// hide the dropdown list of courses
			document.getElementById("courseResult").classList.remove("show");
		}
	}
	function display() {
		// get the course of the button that was clicked
		var course = this.id;
		// this will be the div layer containing more information about the course
		var divOut = document.querySelector("#overlay" + course);
		if(divOut.style.display == "block") {
			// if clicked and already displaying, then hide it
			divOut.style.display  = "none";
		}
		else {
			// if clicked and it's hidden, then display it
			divOut.style.display  = "block";
		}
	}
	function getSections() {
		// get the course ID that is needed to pull data from the UMBC course catalog - zerofilled 6 digit value
		var courseID = this.id;
		courseID = courseID.replace("section", "");
		var semester = document.querySelector("#semester").value;
		var lastSemesterID = document.querySelector("#lastSemesterID");
		//console.log("Looking up " + courseID);
		// this will hold the data for the sections for the course
		var sectionDiv = document.querySelector("#schedule" + courseID);
		if(sectionDiv.innerHTML == "" || lastSemesterID.value != semester) {
			lastSemesterID.value = semester;
			//console.log("it's empty");
			sectionDiv.innerHTML = "<img src=\"loadingBar.gif\" /><br/><br/>";
			// if there are not data, then try to retrieve the sections/sechedule of classes
			var courseSearchURL = "./sections.php?courseID=" + courseID + "&semester=" + semester;
			//console.log(courseSearchURL);
			// make a same orogin XMLHttpRequest to get the list of courses
			var xhr = new XMLHttpRequest();
			// GET Method is being used
			xhr.open("GET", courseSearchURL, true);
			xhr.onload = function() {
				// store the webpage's response. The search result of database query
				var resp = xhr.responseText;
				//console.log(resp);
				// convert it to an array that we can manipulate
				var jsonParsed = JSON.parse(resp);
				// make a list of the results
				sectionDiv.innerHTML = "";
				//var newValue = "<b>[ 2016 Fall Semester ]</b><br/>";
				var newValue = "<b>[ " + document.querySelector("#semester option[value=\"" + semester + "\"]").text + " Semester ]</b><br/><hr/>";
				// display all the sections for the course
				for (var i=0; i < jsonParsed.length; i++) {
					// display the information for each current section
					for(var j=0; j < jsonParsed[i].length; j++) {
						newValue += jsonParsed[i][j] + "<br/>";
					}
					newValue += "<hr/>";
				}
				if(jsonParsed.length == 0) {
					newValue += "There are no sections found for the course.<br/>";
				}
				sectionDiv.innerHTML = newValue + "<br/>";
			}
			xhr.send(null);
		}
		else {
			sectionDiv.innerHTML = "";
		}
	}
	function displayLoading() {
		// this will be the div layer containing more information about the course
		var divOut = document.querySelector("#loading");
		if(divOut.style.display == "block") {
			// if clicked and already displaying, then hide it
			divOut.style.display  = "none";
		}
		else {
			// if clicked and it's hidden, then display it
			divOut.style.display  = "block";
		}
	}
	