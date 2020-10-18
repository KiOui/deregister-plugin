<?php

class ItemList {

    /**
     * Includes scripts utilised by the basket
     */
	function include_scripts() {
		wp_enqueue_script("buttons", "/wp-content/plugins/deregister-plugin/js/buttons.js", array("jquery"));
		wp_localize_script('buttons', 'ajax_vars', array('ajax_url'=>admin_url('admin-ajax.php')));
		wp_enqueue_script("basket", "/wp-content/plugins/deregister-plugin/js/basket.js", array("jquery"));
		wp_enqueue_script("general", "/wp-content/plugins/deregister-plugin/js/general.js", array("jquery"));
		wp_localize_script('general', 'ajax_vars', array('ajax_url'=>admin_url('admin-ajax.php')));
		wp_localize_script('basket', 'ajax_vars', array('ajax_url'=>admin_url('admin-ajax.php')));
		#wp_enqueue_script("scroll-basket", "/wp-content/plugins/deregister-plugin/js/scroll-basket.js", array("jquery"));
	}

	function include_styles() {
		wp_enqueue_style("subscription_all_style", "/wp-content/plugins/deregister-plugin/css/main.css");
		wp_enqueue_style("subscription_list", "/wp-content/plugins/deregister-plugin/css/list.css");
	}

    /**
     * Creates the search HTML code
     * @return string: the HTML code
     */
	function create_list() {
		$this->include_scripts();
		$this->include_styles();
		ob_start();
		?>
		<div class="deregister-item-total" id="deregister-item-total-id"></div>
		<div class="deregister-item-list" id="deregister-item-list-id"></div>
		<?php
		return ob_get_clean();
	}
}