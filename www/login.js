
	function getStudentInfo() {
		var studentID = document.querySelector("#studentID").value;
		studentID = (studentID.toUpperCase()).trim();
		var newValue = "";
		for (var i=0; i < studentID.length; i++) {
			if(studentID[i].match(/[A-Za-z]/) && newValue.length < 2) {
				// must start with 2 characters
				newValue += studentID[i];
			}
			else if(studentID[i].match(/[0-9]/) && newValue.length >= 2 && newValue.length < 7) {
				// after the first 2 characters, it must be followed by 5 digits
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
		// iterate through each character of the firstname's/lastname's value
		for(var i=0; i < name.length; i++) {
			if(name[i].match(/[\w\d\s\.'\-]/)) {
				// if the character is any of the acceptable values, then append it
				newName += name[i];
			}
		}
		this.value = newName;
	}
	function formatEmail() {
		var email = this.value;
		var newEmail = "";
		// iterate throgh each character of the email value
		for(var i=0; i < email.length; i++) {
			if(email[i].match(/[\w\d\.\-@]/)) {
				if(newEmail.indexOf("@") > 0 && email[i] == "@") {
					continue; // there can only be 1 @ sign
				}
				// if the character is any of the acceptable values, then append it
				newEmail += email[i];
			}
		}
		this.value = newEmail;
	}
	function formatPhone() {
		var phone = this.value;
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
		this.value = newPhone;
	}