// script to continuously add classes till user stops
// credit to http://www.randomsnippets.com/2008/02/21/how-to-dynamically-add-form-elements-via-javascript/
var counter = 1; // number in list

var limit = 0; // limit of classes, 0 for infinite

function addInput(divName){

    if (counter == limit)  {
	    alert("You have reached the limit of adding " + counter + " inputs");
    }
    else {
	    var newdiv = document.createElement('div');
    	newdiv.innerHTML = "Class " + (counter + 1) + 
        " <br><input type='text' name='myInputs[]'>";
	    document.getElementById(divName).appendChild(newdiv);
      counter++;
    }

}
