<?php

require_once ABSPATH."wp-content/plugins/deregister-plugin/metaboxes/SubscriptionMetaBox.php";

class Categories {

    /**
     * Includes scripts utilised by the search bar
     */
	function include_scripts() {
		wp_enqueue_script("category-addition", "/wp-content/plugins/deregister-plugin/js/category-addition.js", array("jquery"));
		wp_localize_script('category-addition', 'ajax_vars', array('ajax_url'=>admin_url('admin-ajax.php')));
		wp_enqueue_script("general", "/wp-content/plugins/deregister-plugin/js/general.js", array("jquery"));
		wp_localize_script('general', 'ajax_vars', array('ajax_url'=>admin_url('admin-ajax.php')));
	}

	function include_styles() {
		wp_enqueue_style("subscription_all_style", "/wp-content/plugins/deregister-plugin/css/main.css");
		wp_enqueue_style("subscription_categories", "/wp-content/plugins/deregister-plugin/css/categories.css");
	}

    /**
     * Creates the search HTML code
     * @return string: the HTML code
     */
	function create_categories() {
		$this->include_scripts();
		$this->include_styles();
		ob_start();
		?>
		<div class="category-wrapper">
			<div style="display: none;" class="category-loading-icon" id="category-loading-icon-id"><img src="/wp-content/plugins/deregister-plugin/gif/loader.gif"></div>
			<div class="category-container" id="addition-categories"></div>
		</div>
		<?php
		return ob_get_clean();
	}
}