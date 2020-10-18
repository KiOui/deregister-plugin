var REMEMBER = 29;
var ITEM_NAME = "items";
var LIST_COOKIE = "deregister_items";
var CATEGORY_CONTAINER = "#addition-categories";
var CATEGORY_LOADER = "category-loading-icon-id";

var last_loaded_id = undefined;
var last_loaded = undefined;

function create_category(key, category_data, category_id, do_onclick) {
	let basis = '<div class="category"> \
					<h2>__category__</h2> \
					__onclick__\
					__menulink__ \
				</div>';
	if (do_onclick) {
		let click_function = '<a onclick="go_to_category(__category_id__)">Meer ' + key + '...</a>';
		click_function = click_function.replace("__category_id__", category_id);
		basis = basis.replace("__onclick__", click_function);
	}
	else {
		basis = basis.replace("__onclick__", "");
	}
	let category = basis.replace("__category__", key);
	for (let i = 0; i < category_data.length; i++) {
		let item = create_item(category_data[i][0], category_data[i][1], category_data[i][2], category_data[i][3], category_data[i][4]);
		item += "__menulink__";
		category = category.replace("__menulink__", item);
	}
	return category.replace("__menulink__", "");
}

function go_to_category(category_id) {
	let path = window.location.pathname;
	if (category_id === -1) {
		window.history.pushState("string", "Category " + category_id, path);
	}
	else {
		window.history.pushState("string", "Category " + category_id, path + '?category=' + category_id);
	}
	renew_categories();
}

function create_sub_category(category_name, category_id) {
	let basis = '<li><a class="category-link" onclick="go_to_category(__category_id__)">__category__</a></li>';
	basis = basis.replace("__category_id__", category_id);
	return basis.replace("__category__", category_name);
}

function get_category_details(category_id) {
	jQuery(function($) {
		enable_loader();
		let data = {
			'action': "deregister_categories",
			'option': "category",
			'show-category': true,
			'category': category_id,
		};
		$.ajax({type: 'POST', url:ajax_vars.ajax_url, data, dataType:'json', asynch: true, success:
				function(returnedData){
					if (returnedData.error) {
						disable_loader();
						$(CATEGORY_CONTAINER).html("Error while loading content. " + returnedData.errormsg);
					}
					else {
						last_loaded_id = category_id;
						last_loaded = returnedData;
						disable_loader();
						build_list();
					}
				}}).fail(function(){
					disable_loader();
					console.log("POST request failed");
		});
	});
}

function enable_loader() {
	document.getElementById(CATEGORY_LOADER).style.display = 'flex';
}

function disable_loader() {
	document.getElementById(CATEGORY_LOADER).style.display = "none";
}

function load_categories() {
	jQuery(function($) {
		enable_loader();
		let data = {
			'action': "deregister_categories",
			'option': "topfive",
			'maximum': '5'
		};
		$.ajax({
			type: 'POST', url: ajax_vars.ajax_url, data, dataType: 'json', asynch: true, success:
				function (returnedData) {
					if (returnedData.error) {
						$(CATEGORY_CONTAINER).html("Error while loading content. " + returnedData.errormsg);
						disable_loader();
					} else {
						last_loaded_id = 0;
						last_loaded = returnedData;
						disable_loader();
						build_list();
					}
				}
		}).fail(function () {
			$(CATEGORY_CONTAINER).html("Error while loading content. Please reload this page.");
			disable_loader();
		});
	}); 
}

function create_back_button(parent_categories) {
	if (parent_categories.length > 0) {
		let basis = '<div class="flex-back-row"> \
			<div class="flex-back-row-button">__back_button__</div>\
			<div class="flex-back-row-navigation">__back_navigation__</div>\
			</div>';
		basis = basis.replace('__back_button__', '<button onclick="window.history.back()">Terug</button>');
		let navigation_row = '<a class="navigation-link" onclick="go_to_category(-1)">Start</a> > ';
		for (let i = parent_categories.length - 2; i >= 0; i--) {
			navigation_row += '<a class="navigation-link" onclick="go_to_category(' + parent_categories[i].id + ')">' + parent_categories[i].name + '</a> > ';
		}
		navigation_row += '<a>' + parent_categories[parent_categories.length - 1].name + '</a>';
		return basis.replace('__back_navigation__', navigation_row);
	}
	else {
		return "";
	}
}

function build_list() {
	jQuery(function($) {
		enable_loader();
		let new_category_container = "";

		if (last_loaded_id === 0) {
			new_category_container += "<div class='category-list'>";
			for (let key in last_loaded.items) {
				new_category_container += create_category(key, last_loaded.items[key].top, last_loaded.items[key].id, true);
			}
		}
		else {
			new_category_container += create_back_button(last_loaded.parents);
			new_category_container += "<h2>Subcategorieën</h2>";

			if (last_loaded.categories.length > 0) {
				new_category_container += "<ul class='subcategory_list'>";
				for (let key in last_loaded.categories) {
					new_category_container += create_sub_category(last_loaded.categories[key].name, last_loaded.categories[key].id);
				}
				new_category_container += "</ul>";
			} else {
				new_category_container += "<p>Deze categorie heeft geen subcategorieën</p>";
			}
			new_category_container += "<div class='category-list'>";
			new_category_container += create_category(last_loaded.name, last_loaded.items, last_loaded.id, false);
		}
		new_category_container += "</div>";
		$(CATEGORY_CONTAINER).html(new_category_container);
		disable_loader();
	});
}

function getParameterByName(name, url) {
	if (!url) url = window.location.href;
	name = name.replace(/[\[\]]/g, '\\$&');
	var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
		results = regex.exec(url);
	if (!results) return null;
	if (!results[2]) return '';
	return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

function renew_categories() {
	let category_to_load = getParameterByName('category');
	category_to_load = parseInt(category_to_load, 10);

	if (isNaN(category_to_load)) {
		category_to_load = 0;
	}

	if (category_to_load === last_loaded_id) {
		build_list();
	}
	else if (category_to_load === 0) {
		load_categories();
	}
	else {
		get_category_details(category_to_load);
	}

}

window.addEventListener('popstate', function (event) {
	renew_categories();
});