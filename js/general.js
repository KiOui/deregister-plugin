var LIST_COOKIE = "deregister_items";
var DETAILS_COOKIE = "deregister_details";

function create_item(name, id, price, can_email, can_letter) {

	var list = get_list();
	var alreadyAdded = in_list(list, id);

	var menulink = '<div class="menu-link"> \
						<input type="checkbox" class="normal-checkbox" onchange="toggle_checkbox(this, ' + id + ', ' + price + ',\x27' + name + '\x27, ' + can_email + ', ' + can_letter + ');" __checked__></input><p>__name__</p> \
						<div class="icons">__icon_email__ __icon_letter__</div>\
					</div>';

	if (alreadyAdded) {
		menulink = menulink.replace("__checked__", "checked");
	}
	else {
		menulink = menulink.replace("__checked__", "");
	}

	if (can_email) {
		menulink = menulink.replace("__icon_email__", "<i class='fas fa-at'></i>");
	}
	else {
		menulink = menulink.replace("__icon_email__", "");
	}

	if (can_letter) {
		menulink = menulink.replace("__icon_letter__", "<i class='far fa-envelope'></i>");
	}
	else {
		menulink = menulink.replace("__icon_letter__", "");
	}
	
	return menulink.replace("__name__", name);
}

function in_list(list, id) {
	for (var i = 0; i < list.length; i++) {
		if (list[i].id == id) {
			return true;
		}
	}
	return false;
}

function toggle_checkbox(checkbox, id, price, name, has_email, has_letter) {
	jQuery(function($) {
		var list = get_list();
		if (checkbox.checked) {
			if (!in_list(list, id)) {
				list.push({"id": id, "price": price, "name": name, "has_email": has_email, "has_letter": has_letter});
			}
		}
		else {
			var newlist = [];
			for (var i = 0; i < list.length; i++) {
				if (list[i].id != id) {
					newlist.push(list[i]);
				}
			}
			list = newlist;
		}
		set_list(list);
	});
	refresh_all();
}

function get_details() {
	var cookie = getCookie(DETAILS_COOKIE);
	try {
		var details = JSON.parse(cookie);
	}
	catch (error) {
		return {};
	}
	if (details == null) {
		return {};
	}
	else {
		return details;
	}
}

function set_details(details) {
	try {
		var string = JSON.stringify(details);
	}
	catch(error) {
		setCookie(DETAILS_COOKIE, "", 1);
	}
	setCookie(DETAILS_COOKIE, string, 1);
}

function get_list() {
	var cookie = getCookie(LIST_COOKIE);
	try {
		var list = JSON.parse(cookie);
	}
	catch(error) {
		return [];
	}
	if (list == null) {
		return [];
	}
	else {
		return list;
	}
}

function set_list(list) {
	try {
		var string = JSON.stringify(list);
	}
	catch(error) {
		setCookie(LIST_COOKIE, "", 1);
	}
	setCookie(LIST_COOKIE, string, 1);
}

function refresh_all() {
	if (typeof get_search === "function") {
		get_search();
	}
	if (typeof renew_list === "function") {
		renew_list();
	}
	if (typeof renew_categories == "function") {
		renew_categories();
	}
	if (typeof disable_buttons == "function") {
		disable_buttons();
	}
}

function get_price(id, callback /*, args */) {
	var args = Array.prototype.slice.call(arguments, 2);
	jQuery(function($) {
	var data = {
		'action': 'deregister_categories',
		'option': "price",
		'id': id
	}
	$.ajax({type: 'POST', url:ajax_vars.ajax_url, data, dataType:'json', asynch: true, success:
		function(returnedData) {
			if (returnedData.error) {
				console.log(returnedData.errormsg);
			}
			else {
				args.unshift(returnedData.price);
				callback.apply(this, args);
			}

		}}).fail(function() {
			console.log("Error while getting the price of " + id);
		});
	});
}

function get_post_details(id, callback) {
	var args = Array.prototype.slice.call(arguments, 1);
	jQuery(function($) {
		var data = {
			'action': 'deregister_categories',
			'option': "details",
			'id': id
		}
		$.ajax({type: 'POST', url:ajax_vars.ajax_url, data, dataType:'json', asynch: true, success:
				function(returnedData) {
					if (returnedData.error) {
						console.log(returnedData.errormsg);
					}
					else {
						args.unshift(returnedData.details);
						callback.apply(this, args);
					}

				}}).fail(function() {
			console.log("Error while getting the details of " + id);
		});
	});
}

function query_total_price(list, callback /*, args */) {
	var args = Array.prototype.slice.call(arguments, 2);
	list = JSON.stringify(list);
	jQuery(function($) {
		var data = {
			'action': 'deregister_categories',
			'option': 'total_price',
			'list': list
		}
		$.ajax({type: 'POST', url:ajax_vars.ajax_url, data, dataType:'json', asynch: true, success:
		function(returnedData) {
			if (returnedData.error) {
				console.log(returnedData.errormsg);
			}
			else {
				args.unshift(returnedData.total);
				callback.apply(this, args);
			}

		}}).fail(function() {
			console.log("Error while getting search results for query " + search);
		});
		return "";
	});
}

function query_id(id, callback /*, args */) {
	var args = Array.prototype.slice.call(arguments, 2);
	jQuery(function($) {
		var data = {
			'action': 'deregister_categories',
			'option': 'data',
			'id': id
		}
		$.ajax({type: 'POST', url:ajax_vars.ajax_url, data, dataType:'json', asynch: true, success:
		function(returnedData) {
			if (returnedData.error) {
				console.log(returnedData.errormsg);
			}
			else {
				if (returnedData.name != "") {
					args.unshift(returnedData.data.price);
					args.unshift(id);
					args.unshift(returnedData.data.name);
					callback.apply(this, args);
				}
				else {
					console.log("Error, id " + id + " does not exist.");
				}
			}

		}}).fail(function() {
			console.log("Error while getting search results for query " + search);
		});
	});
}

function setCookie(name,value,days) {
    var expires = "";
    value = encodeURI(value);
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return decodeURI(c.substring(nameEQ.length,c.length));
    }
    return "";
}

function eraseAll() {
	set_details({});
	set_list([]);
}

function eraseCookie(name) {   
    document.cookie = name+'=; path=/; domain=kanikervanaf.nl; Max-Age=-99999999;';  
}

jQuery(document).ready(function($) {
	refresh_all();
});
