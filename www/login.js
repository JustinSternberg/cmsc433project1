
	function getStudentInfo() {
		var studentID = document.querySelector("#studentID").value;
		studentID = (studentID.toUpperCase()).trim();
		var newValue = "";
		for (var i=0; i < studentID.length; i++) {
			if(studentID[i].match(/[A-Za-z]/) && newValue.length < 2) { 
				newValue += studentID[i];
			}
			else if(studentID[i].match(/[0-9]/) && newValue.length >= 2 && newValue.length < 7) {
				newValue += studentID[i];
			}
		}
		studentID = newValue;
		document.querySelector("#studentID").value = studentID;
		var firstname = document.querySelector("#firstname");
		var lastname = document.querySelector("#lastname");
		var email = document.querySelector("#email");
		var phone = document.querySelector("#phone");
		var searchResults = document.querySelector("#courseResults");
		// only run if student id's length is 7
		if(studentID.length == 7) {
			// url address of the php page that provides JSON data
			var studentSearchURL = "./students.php?studentID=" + studentID;
			// make a same orogin XMLHttpRequest to get the list of courses
			var xhr = new XMLHttpRequest();
			// GET Method is being used
			xhr.open("GET", studentSearchURL, true);
			xhr.onload = function() {
				// store the webpage's response. The data result of database query
				var resp = xhr.responseText;
				// convert it to an array that we can manipulate
				var jsonParsed = JSON.parse(resp);
				// make a list of the results
				if(jsonParsed.length == 5) {
					firstname.value = jsonParsed[1];
					lastname.value = jsonParsed[2];
					email.value = jsonParsed[3];
					phone.value = jsonParsed[4];
				}
			}
			xhr.send(null);
		}
	}
	function formatName() {
		var name = this.value;
		var newName = "";
		for(var i=0; i < name.length; i++) {
			if(name[i].match(/[\w\d\s\.'\-]/)) {
				newName += name[i];
			}
		}
		this.value = newName;
	}
	function formatPhone() {
		var phone = document.querySelector("#phone").value;
		var newPhone = "";
		// make sure that the phone format is applied
		for(var i=0; i < phone.length; i++) {
			// if the character is a digit
			if( !isNaN(phone[i]) ) {
					if(i == 3 || i == 7) {
						// if the number is 1234 it will be formatted to 123-4
						newPhone += "-";
					}
					newPhone += phone[i];
			}
			else {
				// if the character is a dash and on index 3 or 7, keep it
				if(phone[i] == "-" && (i == 3 || i == 7)) {
					newPhone += phone[i];
				}
			}
		}
		document.querySelector("#phone").value = newPhone;
	}