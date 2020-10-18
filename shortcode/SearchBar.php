<?php

require_once ABSPATH."wp-content/plugins/deregister-plugin/metaboxes/SubscriptionMetaBox.php";

class SearchBar {

    /**
     * Includes scripts utilised by the search bar
     */
	function include_scripts() {
		wp_enqueue_script("search", "/wp-content/plugins/deregister-plugin/js/search.js", array("jquery"));
		wp_localize_script('search', 'ajax_vars', array('ajax_url'=>admin_url('admin-ajax.php')));
		wp_enqueue_script("general", "/wp-content/plugins/deregister-plugin/js/general.js", array("jquery"));
		wp_localize_script('general', 'ajax_vars', array('ajax_url'=>admin_url('admin-ajax.php')));
	}

	function include_styles() {
		wp_enqueue_style("subscription_all_style", "/wp-content/plugins/deregister-plugin/css/main.css");
		wp_enqueue_style("subscription_searchbar", "/wp-content/plugins/deregister-plugin/css/searchbar.css");
	}

    /**
     * Creates the search HTML code
     * @return string: the HTML code
     */
	function create_search() {
		$this->include_scripts();
		$this->include_styles();
		ob_start();
		?>
		<div class="searchbar">
		  <input id="searchfor" type="text" value="" maxlength="75" autocorrect="off" spellcheck="false" autocomplete="off" autocapitalize="off" placeholder="Zoek een abonnement..." class="search">
		</div>
		<div class="selection-list">
			<div class="selection-container" id="selection-container-id" style="display: none;">
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}