var isIE = false;
var req;
var messageHash = -1;
var targetId = -1;
var centerCell;
var size=40;
var increment = 100/size;
var url="";
function pollTaskmaster() {
	Request();
	req.onreadystatechange = processPollRequest;
	req.send(null);
}

function Request() {
	if (window.XMLHttpRequest) {
		req = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		isIE = true;
		req = new ActiveXObject("Microsoft.XMLHTTP");
	}
	req.open("GET", url+"?sid="+Math.random(), true);
}

function initRequest() {
	if (window.XMLHttpRequest) {
		req2 = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		isIE = true;
		req2 = new ActiveXObject("Microsoft.XMLHTTP");
	}
	req2.open("GET", "COW.php?="+ Math.random(), true);
}


/*function initRequest() {
  req2 = false;

  if (window.XMLHttpRequest) { 
  req2 = new XMLHttpRequest();
  if (req2.overrideMimeType) {
  req2.overrideMimeType('text/xml');
  }
  } else if (window.ActiveXObject) { 
  try {
  req2 = new ActiveXObject("Msxml2.XMLHTTP");
  } catch (e) {
  try {
  req2 = new ActiveXObject("Microsoft.XMLHTTP");
  } catch (e) {}
  }
  }

  if (!req2) {
  alert('Giving up :( Cannot create an XMLHTTP instance');
  return false;
  }
  req2.onreadystatechange = null;
  req2.open('GET', "COW2.php", true);
  req2.send(null);

  }*/


function submitTask(str) {
	url = str+"/progress.xml";
	var bttn = window.document.getElementById("runbutton");
	bttn.disabled = true;
	initRequest();
	req2.send(null);
/*	 set callback function*/
	Request();
	req.onreadystatechange = processInitialRequest;
	req.send(null);
}

function processPollRequest() {
	if (req.readyState == 4) {
		if (req.status == 200) {
			var item = req.responseXML.getElementsByTagName("message")[0];
			var message = item.firstChild.nodeValue;
				var statusitem = req.responseXML.getElementsByTagName("status")[0];
				var statusmessage = statusitem.firstChild.nodeValue;
			if(message<0){
				alert(statusmessage);
				Error_handle();
			}
			showProgress(message);
			messageHash = message;           
				var idiv = window.document.getElementById("task_id");
				//alert(statusmessage);
				idiv.innerHTML = statusmessage;
		} else {
//			window.status = "No Update for " + targetId;
		}
//		window.status = "Processing requestId=" + targetId + " value=" + messageHash;    
		if (messageHash < 100) {
			setTimeout("pollTaskmaster()", 5000);
		} else {
			setTimeout("complete()", 2500);

		}
	}
}

function Error_handle() {
	alert("Please click \"Contact us\" link on the left page to sent an e-mail to contact us. We will redirect to the home page after you click \"OK\".");
	location.href='overview.html';
}

function complete() {

	if (req2.readyState == 4 ) {
		var idiv = window.document.getElementById("progress");
		idiv.innerHTML = "Complete";
//		window.status = "Task Complete";
		var bttn = window.document.getElementById("runbutton");
		bttn.disabled = false;
		location.href='download.php';
	}else{
		setTimeout("complete()", 1000);
	}
}

/* callback function for intial request to schedule a task*/
function processInitialRequest() {
	if (req.readyState == 4) {
		if (req.status == 200) {
			var item = req.responseXML.getElementsByTagName("message")[0];
			var message = item.firstChild.nodeValue;
			/* the initial requests gets the targetId */
			//targetId = message;
			//messageHash = 0;
			window.status = "";
			createProgressBar();
			//alert(message);
			showProgress(message);
		}
			var statusitem = req.responseXML.getElementsByTagName("status")[0];
				//alert(statusitem);
				var statusmessage = statusitem.firstChild.nodeValue;
				var idiv = window.document.getElementById("task_id");
//				alert(statusmessage);
				idiv.innerHTML = statusmessage;
		/* do the initial poll in 2 seconds*/
		setTimeout("pollTaskmaster()", 2000);
	}
}

/* create the progress bar*/
function createProgressBar() {
	var centerCellName;
	var tableText = "";
	for (x = 0; x < size; x++) {
		tableText += "<td id=\"progress_" + x + "\" width=\"10\" height=\"10\" bgcolor=\"blue\"/>";
		if (x == (size/2)) {
			centerCellName = "progress_" + x;
		}
	}
	var idiv = window.document.getElementById("progress");
	idiv.innerHTML = "<table with=\"100\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr>" + tableText + "</tr></table>";
	centerCell = window.document.getElementById(centerCellName);
}

/* show the current percentage*/
function showProgress(percentage) {
	var percentageText = "";
	if (percentage < 10) {
		percentageText = "&nbsp;" + percentage;
	} else {
		percentageText = percentage;
	}
	centerCell.innerHTML = "<font color=\"white\">" + percentageText + "%</font>";
	var tableText = "";
	for (x = 0; x < size; x++) {
		var cell = window.document.getElementById("progress_" + x);
		if ((cell) && percentage/x < increment) {
			cell.style.backgroundColor = "blue";
		} else {
			cell.style.backgroundColor = "red";
		}      
	}
}
