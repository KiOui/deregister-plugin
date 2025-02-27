var firstname_select = document.getElementById('firstname');
var lastname_select = document.getElementById('lastname');
var address_select = document.getElementById('address');
var postalcode_select = document.getElementById('postalcode');
var residence_select = document.getElementById('residence');
var email_select = document.getElementById('email');

var cookie_name = "details";

function update_cookie(first_name, second_name, address, postal_code, residence, email) {
	var details = {'first_name': first_name, 'second_name': second_name, 'address': address, 'postal_code': postal_code, 'residence': residence, 'email': email};
	set_details(details);
}

function update() {
	update_cookie(firstname_select.value, lastname_select.value, address_select.value, postalcode_select.value, residence_select.value, email_select.value);
	if (typeof disable_buttons == "function") {
		disable_buttons();
	}
}

function putback_details() {
	var cookie = get_details();
	if (cookie.first_name != undefined) {
		firstname_select.value = cookie.first_name;
	}
	if (cookie.first_name != undefined) {
		lastname_select.value = cookie.second_name;
	}
	if (cookie.first_name != undefined) {
		address_select.value = cookie.address;
	}
	if (cookie.first_name != undefined) {
		postalcode_select.value = cookie.postal_code;
	}
	if (cookie.first_name != undefined) {
		residence_select.value = cookie.residence;
	}
	if (cookie.first_name != undefined) {
		email_select.value = cookie.email;
	}
}

function register_keyup() {
	jQuery(function($) {
		$('#firstname').keyup(function() {
			update();
		});
		$('#lastname').keyup(function() {
			update();
		});
		$('#address').keyup(function() {
			update();
		});
		$('#postalcode').keyup(function() {
			update();
		});
		$('#residence').keyup(function() {
			update();
		});
		$('#email').keyup(function() {
			update();
		});
	});
}

jQuery(document).ready(function($) {
	register_keyup();
	putback_details();
});