var keepAlive = false;
var timeoutMinutes = 60;

function sessionTimeout(a) {
	if(keepAlive) {
		a = timeoutMinutes;
	}
	
	if(a == 1) {
		swal({
			title: 'Inactivity',
			text: 'Your session will timeout soon.',
			icon: 'warning',
			closeOnEsc: false,
			closeOnClickOutside: false,
			buttons: {
				stay: {
					text: 'I\'m still here!',
					value: 'stay',
				},
			},
		}).then((value) => {
			switch(value) {
				case "stay":
					setKeepAlive();
					break;
				default:
					break;
			}
		});
	}
	
	if (a > 0) {
		//call the function again after 1 minute delay
		window.setTimeout("sessionTimeout(" + --a + ")", 60000);
	} else {
		swal({
			title: 'Timed Out',
			text: 'You have been inactive for too long.',
			icon: 'info',
			closeOnEsc: false,
			closeOnClickOutside: false,
			buttons: {
				logout: {
					text: "Okay",
					value: "logout",
				},
			}
		}).then((value) => {
			try {
				setOverride();
			} catch(e) {}
			window.location = "logout.php";
		});
	}
}
sessionTimeout(timeoutMinutes);
		
function setKeepAlive() {
	keepAlive = true;
	var request = new XMLHttpRequest();
	request.open('GET', 'index.php?refresh_session=true', true);

	request.onerror = function() {
		swal('Error', 'Could not refresh session. Please save your work and reload the page.', 'error');
	};

	request.send();
}