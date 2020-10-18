var disableNoItems = document.getElementById('disable-button-no-items-id');
var disableNoDetails = document.getElementById('disable-button-no-details-id');
var disableNoAll = document.getElementById('disable-button-no-all-id');

function disable_buttons() {
	var itemlist = get_list();
	var details = get_details();
	if (disableNoItems) {
		if (itemlist.length > 0) {
			disableNoItems.style.display = "inline-block";
		}
		else {
			disableNoItems.style.display = "none";
		}
	}
	if (disableNoDetails) {
		if (details.email != undefined && details.first_name != undefined && details.email != "" && details.first_name != "") {
			disableNoDetails.style.display = "inline-block";
		}
		else {
			disableNoDetails.style.display = "none";
		}
	}
	if (disableNoAll) {
		if (itemlist.length > 0 && details.email != undefined && details.first_name != undefined && details.email != "" && details.first_name != "") {
			disableNoAll.style.display = "inline-block";
		}
		else {
			disableNoAll.style.display = "none";
		}
	}
}