let DEREGISTER_REQUEST_FORM_REQUESTED_SUBSCRIPTION = 'deregister_request_form_requested_subscription';
let DEREGISTER_REQUEST_FORM_EMAIL = 'deregister_request_form_email';
let DEREGISTER_REQUEST_FORM_NAME = 'deregister_request_form_name';
let DEREGISTER_REQUEST_FORM_POST_EDITOR = 'deregister_request_form_editor';
let DEREGISTER_REQUEST_FORM_LOADER = 'deregister-loading-icon-id';

function deregister_request_enable_loader() {
	document.getElementById(DEREGISTER_REQUEST_FORM_LOADER).style.display = 'flex';
}

function deregister_request_disable_loader() {
	document.getElementById(DEREGISTER_REQUEST_FORM_LOADER).style.display = 'none';
}

function deregister_request_form_send_captcha(token) {
	jQuery(function($) {
		deregister_request_enable_loader();
		let data = {
			'action': "submit_request_form",
			'requested_subscription': document.getElementById(DEREGISTER_REQUEST_FORM_REQUESTED_SUBSCRIPTION).value,
			'post_editor': tinymce.get(DEREGISTER_REQUEST_FORM_POST_EDITOR).getContent(),
			'name': document.getElementById(DEREGISTER_REQUEST_FORM_NAME).value,
			'email': document.getElementById(DEREGISTER_REQUEST_FORM_EMAIL).value,
			'token': token,
			'action-captcha': deregister_request_form_vars.action,
		};
		$.ajax({type: 'POST', url:ajax_vars.ajax_url, data, dataType:'json', asynch: true, success:
				function(returnedData){
					if (returnedData.error) {
						window.alert(returnedData.errormsg);
						deregister_request_disable_loader();
					}
					else {
						let path = window.location.pathname;
						window.history.pushState("string", "Email sending succeeded", path + '?sent=true');
						location.reload();
						deregister_request_disable_loader();
					}
				}}).fail(function(){
					deregister_request_disable_loader();
					console.log("The server returned an error code.");
		});
	});
}

function deregister_request_form_send() {
	jQuery(function($) {
		deregister_request_enable_loader();
		let data = {
			'action': "submit_request_form",
			'requested_subscription': document.getElementById(DEREGISTER_REQUEST_FORM_REQUESTED_SUBSCRIPTION).value,
			'post_editor': tinymce.get(DEREGISTER_REQUEST_FORM_POST_EDITOR).getContent(),
			'name': document.getElementById(DEREGISTER_REQUEST_FORM_NAME).value,
			'email': document.getElementById(DEREGISTER_REQUEST_FORM_EMAIL).value,
		};
		$.ajax({type: 'POST', url:ajax_vars.ajax_url, data, dataType:'json', asynch: true, success:
				function(returnedData){
					if (returnedData.error) {
						window.alert(returnedData.errormsg);
						deregister_request_disable_loader();
					}
					else {
						let path = window.location.pathname;
						window.history.pushState("string", "Email sending succeeded", path + '?sent=true');
						location.reload();
						deregister_request_disable_loader();
					}
				}}).fail(function(){
					deregister_request_disable_loader();
					console.log("The server returned an error code.");
		});
	});
}