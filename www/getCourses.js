
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
						else if(isNaN(searchValue.value[i]) && (newCourse >= 5 && newCourse <= 7)) {
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
					newValue += "\" href=\"./index.php?course=" + jsonParsed[i][0] + "&pr=1\" >" +
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