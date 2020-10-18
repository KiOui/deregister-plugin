var selectionItemList = document.getElementById('deregister-item-list-id');
var selectionItemTotal = document.getElementById('deregister-item-total-id');

function append_item(name, id, price, has_email, has_letter) {
	jQuery(function($) {
		$("#deregister-item-list-id").append(create_item(name, id, price, has_email, has_letter));
	});
}

function create_basket(list) {
	selectionItemList.innerHTML = "";
	if (list.length == 0) {
		selectionItemList.innerHTML = "Er staan nog geen abonnementen in deze lijst, kies wat abonnementen uit om op te zeggen!";
		selectionItemTotal.innerHTML = "<p class='text-big'>Uw besparing</p>Minimaal bespaard bedrag per jaar:<p class='text-big newline'>€0,00</p><div class='border'></div>";
	}
	else {
		total = 0;
		selectionItemList.innerHTML = "U heeft de volgende abonnementen geselecteerd om op te zeggen:";
		for (var i = 0; i < list.length; i++) {
			append_item(list[i].name, list[i].id, list[i].price, list[i].has_email, list[i].has_letter);
			if (list[i].price != null && !isNaN(list[i].price)) {
				total = total + parseFloat(list[i].price);
			}
		}
		update_total_price(total);
	}
}

function get_total_price(list) {
	query_total_price(list, update_total_price);
}

function renew_list() {
	var list = get_list();
	create_basket(list);
}

function update_total_price(price) {
	if (price == null) {
		price = 0;
	}
	if (price > 0) {
		price = price.toFixed(2);
		price = price.toString().replace('.', ',');
		selectionItemTotal.innerHTML = "<p class='text-big'>Uw besparing</p>Minimaal bespaard bedrag per jaar:<p class='text-big newline'> €" + price + "</p><div class='border'></div>";
	}
	else {
		selectionItemTotal.innerHTML = "<p class='text-big'>Uw besparing</p>Minimaal bespaard bedrag per jaar:<p class='text-big newline'>€0,00</p><div class='border'></div>";
	}
}

